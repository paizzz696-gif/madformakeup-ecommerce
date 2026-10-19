<?php

namespace App\Controllers;

use App\Models\CartModel;
use App\Models\CartItemModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Checkout extends BaseController
{
    protected CartModel $carts;
    protected CartItemModel $cartItems;
    protected OrderModel $orders;
    protected OrderItemModel $orderItems;

    public function __construct()
    {
        $this->carts      = new CartModel();
        $this->cartItems  = new CartItemModel();
        $this->orders     = new OrderModel();
        $this->orderItems = new OrderItemModel();
    }

    /**
     * [FIX] Ambil cart aktif TANPA membuat cart baru (anti phantom cart).
     * Kalau ada beberapa cart aktif, pilih yang benar-benar berisi item.
     */
    private function ambilKeranjang(int $userId): array
    {
        $carts = $this->carts->where('user_id', $userId)
                             ->where('status', 'active')
                             ->orderBy('cart_id', 'DESC')
                             ->findAll();

        foreach ($carts as $c) {
            $items = $this->cartItems->isiKeranjang((int) $c['cart_id']);
            if ($items !== []) {
                return ['cart' => $c, 'items' => $items];
            }
        }

        return ['cart' => $carts[0] ?? null, 'items' => []];
    }

    /**
     * [5.4] Halaman form checkout.
     */
    public function index()
    {
        $userId = (int) (session('user_id') ?? 0);
        $k      = $this->ambilKeranjang($userId);
        $cart   = $k['cart'];
        $items  = $k['items'];

        if ($cart === null || $items === []) {
            return redirect()->to(site_url('keranjang'))
                             ->with('error', 'Keranjang kamu masih kosong, nggak ada yang bisa di-checkout.');
        }

        $adaMasalah = false;
        foreach ($items as $it) {
            if ((int) $it['quantity'] > (int) $it['stock_quantity'] || (int) $it['is_active'] !== 1) {
                $adaMasalah = true;
            }
        }

        $total = 0.0;
        foreach ($items as $it) {
            $total += (float) $it['price'] * (int) $it['quantity'];
        }

        return view('checkout/index', [
            'items'      => $items,
            'total'      => $total,
            'adaMasalah' => $adaMasalah,
            'user'       => [
                'name'  => (string) (session('name') ?? ''),
                'email' => (string) (session('email') ?? ''),
            ],
        ]);
    }

    /**
     * [5.4] Simpan pesanan: orders + order_items + stok + status cart,
     * semuanya dalam SATU transaksi.
     */
    public function proses()
    {
        $userId = (int) (session('user_id') ?? 0);

        $aturan = [
            'receiver_name'  => 'required|min_length[3]|max_length[100]',
            'phone'          => 'required|regex_match[/^[0-9+()\-\s]{6,20}$/]',
            'address'        => 'required|min_length[10]|max_length[500]',
            'payment_method' => 'required|in_list[transfer,qris,cod]',
        ];

        // [FIX] Pesan validasi bahasa Indonesia
        $pesan = [
            'receiver_name'  => [
                'required'   => 'Nama penerima wajib diisi.',
                'min_length' => 'Nama penerima minimal 3 karakter.',
                'max_length' => 'Nama penerima maksimal 100 karakter.',
            ],
            'phone'          => [
                'required'    => 'No. HP / WhatsApp wajib diisi.',
                'regex_match' => 'Format No. HP belum benar. Contoh: 081234567890.',
            ],
            'address'        => [
                'required'   => 'Alamat lengkap wajib diisi.',
                'min_length' => 'Alamat terlalu pendek — tulis minimal 10 karakter.',
                'max_length' => 'Alamat maksimal 500 karakter.',
            ],
            'payment_method' => [
                'required' => 'Pilih salah satu metode pembayaran.',
                'in_list'  => 'Metode pembayaran tidak dikenal.',
            ],
        ];

        if (! $this->validate($aturan, $pesan)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $k     = $this->ambilKeranjang($userId);
        $cart  = $k['cart'];
        $items = $k['items'];

        if ($cart === null || $items === []) {
            return redirect()->to(site_url('keranjang'))->with('error', 'Keranjang kamu kosong.');
        }

        // Validasi ulang stok + status varian SEBELUM finalisasi
        foreach ($items as $it) {
            $label = trim((string) $it['shade']) !== '' ? $it['shade'] : $it['sku'];

            if ((int) $it['is_active'] !== 1) {
                return redirect()->back()->withInput()
                    ->with('error', 'Maaf, varian "' . $label . '" pada "' . $it['nama_produk'] . '" sudah tidak dijual. Hapus dulu dari keranjang ya.');
            }
            if ((int) $it['quantity'] > (int) $it['stock_quantity']) {
                return redirect()->back()->withInput()
                    ->with('error', 'Maaf, stok "' . $it['nama_produk'] . '" (' . $label . ') tinggal ' . (int) $it['stock_quantity'] . '. Kecilkan jumlahnya di keranjang ya.');
            }
        }

        $total = 0.0;
        foreach ($items as $it) {
            $total += (float) $it['price'] * (int) $it['quantity'];
        }

        $db = db_connect();
        $db->transBegin();

        try {
            // [FIX] Kunci baris cart + cek ulang status → anti double-submit
            $cek = $db->query(
                'SELECT status FROM carts WHERE cart_id = ? FOR UPDATE',
                [(int) $cart['cart_id']]
            )->getRowArray();

            if ($cek === null || $cek['status'] !== 'active') {
                throw new \RuntimeException('Keranjang ini sudah diproses menjadi pesanan. Cek halaman keranjang ya.');
            }

            $kode = $this->orders->buatKode();

            // 1) Simpan pesanan induk
            $this->orders->insert([
                'order_code'     => $kode,
                'user_id'        => $userId,
                'cart_id'        => (int) $cart['cart_id'],
                'receiver_name'  => trim((string) $this->request->getPost('receiver_name')),
                'phone'          => trim((string) $this->request->getPost('phone')),
                'address'        => trim((string) $this->request->getPost('address')),
                'notes'          => trim((string) $this->request->getPost('notes')) ?: null,
                'total_amount'   => $total,
                'payment_method' => (string) $this->request->getPost('payment_method'),
                'payment_status' => 'unpaid',
                'status'         => 'pending',
            ]);
            $orderId = (int) $this->orders->getInsertID();

            // 2) Simpan item (snapshot) + 3) kurangi stok atomik
            foreach ($items as $it) {
                $this->orderItems->insert([
                    'order_id'     => $orderId,
                    'variant_id'   => (int) $it['variant_id'],
                    'product_name' => (string) $it['nama_produk'],
                    'shade'        => (string) ($it['shade'] ?? ''),
                    'sku'          => (string) $it['sku'],
                    'price'        => (float) $it['price'],
                    'quantity'     => (int) $it['quantity'],
                    'subtotal'     => (float) $it['price'] * (int) $it['quantity'],
                ]);

                $db->table('product_variants')
                   ->where('variant_id', (int) $it['variant_id'])
                   ->where('stock_quantity >=', (int) $it['quantity'])
                   ->set('stock_quantity', 'stock_quantity - ' . (int) $it['quantity'], false)
                   ->update();

                if ($db->affectedRows() !== 1) {
                    throw new \RuntimeException('Maaf, stok "' . $it['nama_produk'] . '" (' . ($it['shade'] ?: $it['sku']) . ') baru saja habis.');
                }
            }

            // 4) Cart selesai dikonversi jadi pesanan
            $this->carts->update((int) $cart['cart_id'], ['status' => 'converted']);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('checkout/sukses/' . $kode))
                         ->with('success', 'Pesanan berhasil dibuat!');
    }

    /**
     * [5.4] Halaman sukses + detail pesanan (pemilik pesanan saja).
     */
    public function sukses(string $kode)
    {
        $order = $this->orders->cariKode($kode, (int) (session('user_id') ?? 0));
        if ($order === null) {
            throw PageNotFoundException::forPageNotFound('Pesanan tidak ditemukan.');
        }

        $items = $this->orderItems->itemPesanan((int) $order['order_id']);

        return view('checkout/sukses', [
            'order' => $order,
            'items' => $items,
        ]);
    }

    /**
     * [5.4] Simulasi pembayaran (pengganti payment gateway).
     */
    public function bayar(string $kode)
    {
        $order = $this->orders->cariKode($kode, (int) (session('user_id') ?? 0));
        if ($order === null) {
            throw PageNotFoundException::forPageNotFound('Pesanan tidak ditemukan.');
        }

        if ($order['payment_status'] === 'paid') {
            return redirect()->to(site_url('checkout/sukses/' . $kode))
                             ->with('error', 'Pesanan ini sudah dibayar.');
        }

        $this->orders->update((int) $order['order_id'], [
            'payment_status' => 'paid',
            'status'         => 'paid',
        ]);

        return redirect()->to(site_url('checkout/sukses/' . $kode))
                         ->with('success', 'Pembayaran (simulasi) berhasil — pesananmu masuk tahap penyiapan.');
    }
}