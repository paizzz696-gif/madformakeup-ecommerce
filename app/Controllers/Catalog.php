<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use App\Models\ProductImageModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * KATALOG PUBLIK — Tahap 4.2 (listing) + 4.3 (detail)
 * Route: GET catalog            -> index()  : listing + search + filter
 * Route: GET product/(:segment) -> detail() : detail produk by slug
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

    /**
     * [4.3] Halaman detail produk by slug.
     * Produk harus is_active = 1; slug ga ketemu -> 404 beneran.
     */
    public function detail(string $slug): string
    {
        $productModel = new ProductModel();
        $variantModel = new ProductVariantModel();
        $imageModel   = new ProductImageModel();

        $produk = $productModel->withKategori()
                               ->hanyaAktif()
                               ->where('products.slug', $slug)
                               ->first();

        if ($produk === null) {
            throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan: ' . $slug);
        }

        $pid = (int) $produk['product_id'];

        $data = [
            'produk'     => $produk,
            'varianList' => $variantModel->aktifUntukProduk($pid),
            'gambarList' => $imageModel->untukProduk($pid),
            'hargaMulai' => $productModel->hargaMulai($pid),
            'title'      => $produk['name'] . ' — Mad For Makeup',
        ];

        return view('catalog/detail', $data);
    }
}