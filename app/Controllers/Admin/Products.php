<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use App\Models\StockMovementModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * CRUD PRODUK (ADMIN) — Tahap 4.4-b + 4.4-c
 * Route (group admin, filter 'admin'):
 *   GET  admin/products                          -> index()        : list semua produk + search
 *   GET  admin/products/baru                     -> baru()         : form tambah
 *   POST admin/products                          -> simpan()
 *   GET  admin/products/(:num)/edit              -> edit($id)
 *   POST admin/products/(:num)                   -> update($id)
 *   POST admin/products/(:num)/toggle            -> toggle($id)    : aktifkan/arsipkan
 *   [4.4-c]
 *   GET  admin/products/(:num)/varian            -> varian($pid)             : kelola varian + stok
 *   POST admin/products/(:num)/varian            -> varianSimpan($pid)       : tambah varian baru
 *   POST admin/products/(:num)/varian/(:num)     -> varianUpdate($pid,$vid)  : edit data varian (TANPA stok)
 *   POST admin/products/(:num)/varian/(:num)/stok -> varianStok($pid,$vid)   : catat stok masuk/keluar
 *
 * Gambar dikelola menyusul di 4.4-d.
 * "Hapus" produk = arsip (is_active 0), bukan hard delete — biar history
 * varian, gambar, dan stock_movements tetap utuh (FK aman).
 * [4.4-c] Stok varian TIDAK BOLEH diedit langsung lewat update() — cuma lewat
 * catatMasuk/catatKeluar biar tiap perubahan kecatat di stock_movements.
 */
class Products extends BaseController
{
    public function index(): string
    {
        $q = trim((string) $this->request->getGet('q'));

        $productModel = new ProductModel();
        $productModel->withKategori(); // admin lihat SEMUA produk, termasuk nonaktif

        if ($q !== '') {
            $productModel->like('products.name', $q);
        }

        $produk = $productModel->orderBy('products.created_at', 'DESC')
                               ->paginate(15);
        $pager = $productModel->pager;
        $pager->only(['q']);

        $ids = array_column($produk, 'product_id');

        $data = [
            'q'           => $q,
            'produk'      => $produk,
            'pager'       => $pager,
            'gambarUtama' => $productModel->gambarUtamaBanyak($ids),
            'hargaMulai'  => $productModel->hargaMulaiBanyak($ids),
            'varianInfo'  => (new ProductVariantModel())->ringkasVarianBanyak($ids),
            'title'       => 'Produk — Admin Mad For Makeup',
        ];

        return view('admin/products/index', $data);
    }

    public function baru(): string
    {
        return view('admin/products/form', [
            'mode'         => 'baru',
            'produk'       => null,
            'kategoriList' => (new CategoryModel())->getAktif(),
            'title'        => 'Tambah Produk — Admin Mad For Makeup',
        ]);
    }

    public function simpan(): RedirectResponse
    {
        $productModel = new ProductModel();
        $name         = trim((string) $this->request->getPost('name'));

        $data = [
            'category_id' => (int) $this->request->getPost('category_id'),
            'name'        => $name,
            'slug'        => $productModel->buatSlug($name),
            'description' => trim((string) $this->request->getPost('description')),
            'is_active'   => (int) $this->request->getPost('is_active') === 1 ? 1 : 0,
        ];

        if ($productModel->insert($data) === false) {
            return redirect()->back()
                             ->with('error', 'Periksa lagi form-nya, ada isian yang belum bener.')
                             ->withInput()
                             ->with('errors', $productModel->errors());
        }

        return redirect()->to(site_url('admin/products/' . $productModel->getInsertID() . '/edit'))
                         ->with('success', 'Produk dibuat. Lanjut: tambahkan varian & gambar.');
    }

    public function edit(int $productId): string
    {
        $produk = (new ProductModel())->withKategori()
                                      ->where('products.product_id', $productId)
                                      ->first();

        if ($produk === null) {
            throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan: #' . $productId);
        }

        return view('admin/products/form', [
            'mode'         => 'edit',
            'produk'       => $produk,
            'kategoriList' => (new CategoryModel())->getAktif(),
            'title'        => 'Edit Produk — Admin Mad For Makeup',
        ]);
    }

    public function update(int $productId): RedirectResponse
    {
        $productModel = new ProductModel();

        $produk = $productModel->find($productId);
        if ($produk === null) {
            throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan: #' . $productId);
        }

        $name = trim((string) $this->request->getPost('name'));

        $data = [
            'category_id' => (int) $this->request->getPost('category_id'),
            'name'        => $name,
            'slug'        => $productModel->buatSlug($name, $productId), // slug sendiri dikecualikan
            'description' => trim((string) $this->request->getPost('description')),
            'is_active'   => (int) $this->request->getPost('is_active') === 1 ? 1 : 0,
        ];

        if ($productModel->update($productId, $data) === false) {
            return redirect()->back()
                             ->with('error', 'Periksa lagi form-nya, ada isian yang belum bener.')
                             ->withInput()
                             ->with('errors', $productModel->errors());
        }

        return redirect()->to(site_url('admin/products/' . $productId . '/edit'))
                         ->with('success', 'Perubahan produk tersimpan.');
    }

    public function toggle(int $productId): RedirectResponse
    {
        $productModel = new ProductModel();

        $produk = $productModel->find($productId);
        if ($produk === null) {
            throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan: #' . $productId);
        }

        $baru = ((int) $produk['is_active'] === 1) ? 0 : 1;

        $productModel->update($productId, ['is_active' => $baru]);

        return redirect()->back()->with('success', $baru === 1 ? 'Produk diaktifkan.' : 'Produk diarsipkan (sembunyi dari toko).');
    }

    // ==================== [4.4-c] Kelola varian & stok ====================

    public function varian(int $productId): string
    {
        $produk = (new ProductModel())->find($productId);
        if ($produk === null) {
            throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan: #' . $productId);
        }

        $variantModel  = new ProductVariantModel();
        $movementModel = new StockMovementModel();

        $varianList = $variantModel->untukProduk($productId);

        // Riwayat stok per varian (5 terakhir) buat <details> di tiap kartu.
        // Loop kecil ini sengaja: varian per produk biasanya < 10, jadi nggak
        // berdampak — beda sama daftar produk yang wajib batch (anti N+1).
        $riwayatMap = [];
        foreach ($varianList as $v) {
            $riwayatMap[(int) $v['variant_id']] = $movementModel->riwayatVarian((int) $v['variant_id'], 5);
        }

        return view('admin/products/varian', [
            'produk'     => $produk,
            'varianList' => $varianList,
            'riwayatMap' => $riwayatMap,
            'title'      => 'Kelola Varian — Admin Mad For Makeup',
        ]);
    }

    public function varianSimpan(int $productId): RedirectResponse
    {
        $productModel = new ProductModel();
        if ($productModel->find($productId) === null) {
            throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan: #' . $productId);
        }

        $variantModel = new ProductVariantModel();

        // stok_awal BUKAN kolom model — dicatat sebagai movement 'in', bukan
        // langsung nempel ke stock_quantity. Riwayat harus jujur dari hari pertama.
        $stokAwal = (int) $this->request->getPost('stok_awal');
        if ($stokAwal < 0) {
            $stokAwal = 0;
        }

        $data = [
            'product_id'     => $productId,
            'sku'            => trim((string) $this->request->getPost('sku')),
            'shade'          => trim((string) $this->request->getPost('shade')),
            'size'           => trim((string) $this->request->getPost('size')),
            'price'          => (string) $this->request->getPost('price'),
            'stock_quantity' => 0, // selalu mulai 0; yang > 0 dicatat lewat movement
            'is_active'      => (int) $this->request->getPost('is_active') === 1 ? 1 : 0,
        ];

        if ($variantModel->insert($data) === false) {
            return redirect()->back()
                             ->with('error', 'Varian gagal disimpan — periksa isian (SKU harus unik).')
                             ->withInput()
                             ->with('errors', $variantModel->errors());
        }

        $variantId = (int) $variantModel->getInsertID();
        $userId    = session()->get('user_id');
        $userId    = $userId !== null ? (int) $userId : null;

        if ($stokAwal > 0) {
            $movementModel = new StockMovementModel();
            $ok = $movementModel->catatMasuk(
                $variantId,
                $stokAwal,
                'admin',
                $productId,
                'Stok awal varian baru',
                $userId
            );
            if (! $ok) {
                return redirect()->to(site_url('admin/products/' . $productId . '/varian'))
                                 ->with('error', 'Varian dibuat, tapi stok awal gagal dicatat: ' . $movementModel->lastError);
            }
        }

        return redirect()->back()->with(
            'success',
            'Varian baru ditambahkan' . ($stokAwal > 0 ? ' (stok awal ' . $stokAwal . ' kecatat di riwayat).' : '.')
        );
    }

    public function varianUpdate(int $productId, int $variantId): RedirectResponse
    {
        $variantModel = new ProductVariantModel();

        $varian = $variantModel->cariDiProduk($variantId, $productId);
        if ($varian === null) {
            throw PageNotFoundException::forPageNotFound('Varian tidak ditemukan di produk ini.');
        }

        $data = [
            // variant_id ikut dikirim HANYA buat ngisi placeholder {variant_id}
            // di rule is_unique SKU — biar SKU milik varian ini sendiri nggak
            // dianggap bentrok. Kolom ini nggak bakal ke-SET (nggak ada di allowedFields).
            'variant_id' => $variantId,
            'sku'        => trim((string) $this->request->getPost('sku')),
            'shade'      => trim((string) $this->request->getPost('shade')),
            'size'       => trim((string) $this->request->getPost('size')),
            'price'      => (string) $this->request->getPost('price'),
            'is_active'  => (int) $this->request->getPost('is_active') === 1 ? 1 : 0,
            // CATATAN: stock_quantity sengaja TIDAK di-update di sini.
        ];

        if ($variantModel->update($variantId, $data) === false) {
            return redirect()->back()
                             ->with('error', 'Varian gagal disimpan — periksa isian (SKU harus unik).');
        }

        return redirect()->back()->with('success', 'Perubahan varian tersimpan.');
    }

    public function varianStok(int $productId, int $variantId): RedirectResponse
    {
        $variantModel = new ProductVariantModel();

        $varian = $variantModel->cariDiProduk($variantId, $productId);
        if ($varian === null) {
            throw PageNotFoundException::forPageNotFound('Varian tidak ditemukan di produk ini.');
        }

        $arah  = (string) $this->request->getPost('arah');
        $qty   = (int) $this->request->getPost('qty');
        $notes = trim((string) $this->request->getPost('notes'));

        if (! in_array($arah, ['in', 'out'], true)) {
            return redirect()->back()->with('error', 'Arah pergerakan stok nggak valid.');
        }

        $movementModel = new StockMovementModel();
        $userId        = session()->get('user_id');
        $userId        = $userId !== null ? (int) $userId : null;

        $ok = $arah === 'out'
            ? $movementModel->catatKeluar($variantId, $qty, 'admin', $productId, $notes, $userId)
            : $movementModel->catatMasuk($variantId, $qty, 'admin', $productId, $notes, $userId);

        if (! $ok) {
            return redirect()->back()
                             ->with('error', 'Stok gagal dicatat: ' . ($movementModel->lastError !== '' ? $movementModel->lastError : 'periksa jumlah.'));
        }

        $terbaru = $variantModel->find($variantId);

        return redirect()->back()->with(
            'success',
            'Stok ' . ($arah === 'out' ? 'keluar' : 'masuk') . ' ' . $qty . ' dicatat. Stok sekarang: ' . (int) ($terbaru['stock_quantity'] ?? 0) . '.'
        );
    }
}