<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * CRUD PRODUK (ADMIN) — Tahap 4.4-b
 * Route (group admin, filter 'admin'):
 *   GET  admin/products               -> index()    : list semua produk + search
 *   GET  admin/products/baru          -> baru()     : form tambah
 *   POST admin/products               -> simpan()
 *   GET  admin/products/(:num)/edit   -> edit($id)
 *   POST admin/products/(:num)        -> update($id)
 *   POST admin/products/(:num)/toggle -> toggle($id) : aktifkan/arsipkan
 *
 * Varian & gambar dikelola di halaman edit (menyusul 4.4-c & 4.4-d).
 * "Hapus" produk = arsip (is_active 0), bukan hard delete — biar history
 * varian, gambar, dan stock_movements tetap utuh (FK aman).
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
}