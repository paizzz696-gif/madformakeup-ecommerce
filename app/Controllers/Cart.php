<?php

namespace App\Controllers;

use App\Models\CartModel;
use App\Models\CartItemModel;
use App\Models\ProductVariantModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Cart extends BaseController
{
    /**
     * [5.2] Tambah varian (shade spesifik) ke keranjang user yang sedang login.
     * Varian yang sama ditambah lagi -> quantity digabung, bukan baris baru.
     */
    public function tambah(): RedirectResponse
    {
        $aturan = [
            'variant_id' => [
                'label' => 'Varian',
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required'           => 'Pilih shade/varian dulu.',
                    'is_natural_no_zero' => 'Varian tidak valid.',
                ],
            ],
            'quantity' => [
                'label' => 'Jumlah',
                'rules' => 'required|is_natural_no_zero|less_than_equal_to[99]',
                'errors' => [
                    'required'           => 'Jumlah belum diisi.',
                    'is_natural_no_zero' => 'Jumlah minimal 1.',
                    'less_than_equal_to' => 'Jumlah maksimal 99 per varian.',
                ],
            ],
        ];

        if (! $this->validateData($this->request->getPost(), $aturan)) {
            $errors = $this->validator->getErrors();
            return redirect()->back()->with('error', reset($errors));
        }

        $userId    = (int) session()->get('user_id');
        $variantId = (int) $this->request->getPost('variant_id');
        $jumlah    = (int) $this->request->getPost('quantity');

        $varian = (new ProductVariantModel())->find($variantId);
        if ($varian === null) {
            return redirect()->back()->with('error', 'Varian tidak ditemukan.');
        }

        if ((int) $varian['is_active'] !== 1) {
            return redirect()->back()->with('error', 'Varian ini sedang tidak dijual.');
        }

        $label = $this->labelVarian($varian);

        $stok = (int) $varian['stock_quantity'];
        if ($stok <= 0) {
            return redirect()->back()->with('error', 'Stok "' . $label . '" sedang habis.');
        }

        $cart      = (new CartModel())->keranjangAktif($userId);
        $itemModel = new CartItemModel();
        $existing  = $itemModel->itemUntuk((int) $cart['cart_id'], $variantId);

        $sudahDiKeranjang = $existing !== null ? (int) $existing['quantity'] : 0;
        $totalDiminta     = $sudahDiKeranjang + $jumlah;

        if ($totalDiminta > $stok) {
            $pesan = 'Stok "' . $label . '" tidak cukup — tersedia ' . $stok . ' pcs';
            $pesan .= $sudahDiKeranjang > 0
                ? ' (di keranjang kamu sudah ada ' . $sudahDiKeranjang . ').'
                : '.';
            return redirect()->back()->with('error', $pesan);
        }

        if ($existing !== null) {
            $itemModel->update((int) $existing['cart_item_id'], ['quantity' => $totalDiminta]);
        } else {
            $itemModel->insert([
                'cart_id'    => (int) $cart['cart_id'],
                'variant_id' => $variantId,
                'quantity'   => $jumlah,
            ]);
        }

        return redirect()->back()->with(
            'success',
            '"' . $label . '" masuk keranjang — total ' . $totalDiminta . ' pcs.'
        );
    }

    /**
     * [5.3] Halaman keranjang belanja.
     */
    public function index(): string
    {
        $cart = (new CartModel())->keranjangAktif((int) session()->get('user_id'));
        $itemModel = new CartItemModel();
        $items = $itemModel->isiKeranjang((int) $cart['cart_id']);

        $total = 0.0;
        $jumlahItem = 0;
        foreach ($items as $item) {
            $total      += (float) $item['price'] * (int) $item['quantity'];
            $jumlahItem += (int) $item['quantity'];
        }

        return view('cart/index', [
            'title'      => 'Keranjang Belanja',
            'items'      => $items,
            'total'      => $total,
            'jumlahItem' => $jumlahItem,
        ]);
    }

    /**
     * [5.3] Ubah jumlah satu item keranjang (dengan cek kepemilikan + stok).
     */
    public function ubahQty(int $cartItemId): RedirectResponse
    {
        $jumlah = (int) $this->request->getPost('quantity');
        if ($jumlah < 1 || $jumlah > 99) {
            return redirect()->to('keranjang')->with('error', 'Jumlah harus antara 1 sampai 99.');
        }

        $itemModel = new CartItemModel();
        $item = $itemModel->find($cartItemId);
        if ($item === null) {
            throw new PageNotFoundException('Item keranjang tidak ditemukan.');
        }

        $cart = (new CartModel())->find((int) $item['cart_id']);
        if ($cart === null || (int) $cart['user_id'] !== (int) session()->get('user_id') || $cart['status'] !== 'active') {
            throw new PageNotFoundException('Item keranjang tidak ditemukan.');
        }

        $varian = (new ProductVariantModel())->find((int) $item['variant_id']);
        if ($varian === null || (int) $varian['is_active'] !== 1) {
            return redirect()->to('keranjang')->with('error', 'Varian ini sudah tidak dijual — hapus dari keranjang ya.');
        }

        $stok = (int) $varian['stock_quantity'];
        if ($jumlah > $stok) {
            return redirect()->to('keranjang')->with('error', 'Stok "' . $this->labelVarian($varian) . '" cuma ' . $stok . ' pcs.');
        }

        $itemModel->update($cartItemId, ['quantity' => $jumlah]);

        return redirect()->to('keranjang')->with('success', 'Jumlah diperbarui.');
    }

    /**
     * [5.3] Hapus satu item dari keranjang (dengan cek kepemilikan).
     */
    public function hapus(int $cartItemId): RedirectResponse
    {
        $itemModel = new CartItemModel();
        $item = $itemModel->find($cartItemId);
        if ($item === null) {
            throw new PageNotFoundException('Item keranjang tidak ditemukan.');
        }

        $cart = (new CartModel())->find((int) $item['cart_id']);
        if ($cart === null || (int) $cart['user_id'] !== (int) session()->get('user_id') || $cart['status'] !== 'active') {
            throw new PageNotFoundException('Item keranjang tidak ditemukan.');
        }

        $itemModel->delete($cartItemId);

        return redirect()->to('keranjang')->with('success', 'Item dihapus dari keranjang.');
    }

    /**
     * Label varian buat pesan: shade -> size -> SKU.
     */
    private function labelVarian(array $varian): string
    {
        $label = trim((string) ($varian['shade'] ?? ''));
        if ($label === '') { $label = trim((string) ($varian['size'] ?? '')); }
        if ($label === '') { $label = (string) $varian['sku']; }
        return $label;
    }
}