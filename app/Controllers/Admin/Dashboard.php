<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * DASHBOARD ADMIN — data ringkasan toko.
 * 4 angka utama sama seperti sebelumnya (users/products/variants/orders),
 * revamp UI menambah 3 panel data nyata: stok kritis, produk terbaru,
 * dan produk aktif tanpa gambar. Semua query di sini, view cuma nampilin.
 */
class Dashboard extends BaseController
{
    public function index()
    {
        $db = db_connect();

        // ---- Angka utama (nama variabel dipertahankan biar kompatibel) ----
        $data = [
            'total_users'    => $db->table('users')->countAll(),
            'total_products' => $db->table('products')->countAll(),
            'total_variants' => $db->table('product_variants')->countAll(),
            'total_orders'   => $db->table('orders')->countAll(),
        ];

        // ---- [revamp] Jumlah varian AKTIF yang stoknya habis → sinyal restock ----
        $data['total_stok_kritis'] = $db->table('product_variants')
            ->where('is_active', 1)
            ->where('stock_quantity <=', 0)
            ->countAllResults();

        // ---- [revamp] Detail varian kritis (6 teratas), link langsung ke halaman varian ----
        $data['stok_kritis'] = $db->table('product_variants v')
            ->select('v.variant_id, v.sku, v.shade, v.size, v.stock_quantity, p.product_id, p.name AS product_name')
            ->join('products p', 'p.product_id = v.product_id')
            ->where('v.is_active', 1)
            ->where('v.stock_quantity <=', 0)
            ->orderBy('p.name', 'ASC')
            ->limit(6)
            ->get()
            ->getResultArray();

        // ---- [revamp] 5 produk terbaru ----
        $data['produk_terbaru'] = $db->table('products')
            ->select('product_id, name, slug, is_active, created_at')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        // ---- [revamp] Produk aktif yang belum punya gambar sama sekali ----
        $data['tanpa_gambar'] = $db->table('products p')
            ->select('p.product_id, p.name')
            ->join('product_images i', 'i.product_id = p.product_id', 'left')
            ->where('p.is_active', 1)
            ->where('i.image_id IS NULL', null, false)
            ->orderBy('p.name', 'ASC')
            ->limit(6)
            ->get()
            ->getResultArray();

        return view('admin/dashboard', $data);
    }
}