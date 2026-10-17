<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;

/**
 * KATALOG PUBLIK — Tahap 4.2
 * Listing produk aktif + search (q) + filter kategori (kategori).
 * Route: GET catalog
 */
class Catalog extends BaseController
{
    public function index(): string
    {
        $q        = trim((string) $this->request->getGet('q'));
        $kategori = trim((string) $this->request->getGet('kategori'));

        $categoryModel = new CategoryModel();
        $productModel  = new ProductModel();

        $productModel->withKategori()->hanyaAktif();

        // Search: cocokkan nama ATAU deskripsi
        if ($q !== '') {
            $productModel->groupStart()
                         ->like('products.name', $q)
                         ->orLike('products.description', $q)
                         ->groupEnd();
        }

        // Filter kategori via slug (URL-friendly)
        if ($kategori !== '') {
            $productModel->where('categories.slug', $kategori);
        }

        $produk = $productModel->orderBy('products.created_at', 'DESC')
                               ->paginate(9);
        $pager  = $productModel->pager;
        $pager->only(['q', 'kategori']); // query search/filter ikut ke halaman berikutnya

        $ids = array_column($produk, 'product_id');

        $data = [
            'q'            => $q,
            'kategori'     => $kategori,
            'kategoriList' => $categoryModel->getAktif(),
            'produk'       => $produk,
            'pager'        => $pager,
            'gambarUtama'  => $productModel->gambarUtamaBanyak($ids),
            'hargaMulai'   => $productModel->hargaMulaiBanyak($ids),
        ];

        return view('catalog/index', $data);
    }
}