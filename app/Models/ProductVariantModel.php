<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * MODEL VARIAN PRODUK — Tahap 4.1 + 4.3 (varian aktif) + 4.4-b (dukungan admin)
 * Skema: product_variants(variant_id PK, product_id FK, sku, shade, size, price, stock_quantity, is_active, created_at, updated_at)
 *
 * Catatan arsitektur stok (sejak 4.4-a):
 * - stock_quantity TIDAK PERNAH diubah langsung lewat update() di alur admin.
 * - Setiap perubahan stok WAJIB lewat StockMovementModel (catatMasuk/catatKeluar)
 *   biar kecatat di stock_movements (update stok + insert movement dalam 1 transaction).
 * - Varian juga TIDAK di-hard-delete: cukup is_active = 0, karena FK
 *   stock_movements -> product_variants ON DELETE CASCADE bakal nyapu riwayat stok
 *   kalau variannya dihapus — riwayat audit harus tetap utuh.
 */
class ProductVariantModel extends Model
{
    protected $table          = 'product_variants';
    protected $primaryKey     = 'variant_id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $allowedFields  = ['product_id', 'sku', 'shade', 'size', 'price', 'stock_quantity', 'is_active'];

    protected $validationRules = [
        // [FIX 4.4-c] WAJIB ada: placeholder {variant_id} di rule SKU butuh
        // field ini punya rule sendiri, kalau nggak CI lempar LogicException
        // pas insert varian baru. permit_empty = insert tetap lolos (variant_id
        // memang belum ada saat nambah); is_natural_no_zero = kalau ada
        // nilainya, harus >= 1.
        'variant_id'     => 'permit_empty|is_natural_no_zero',
        'product_id'     => 'required|is_natural_no_zero',
        'sku'            => 'required|alpha_numeric_punct|max_length[50]|is_unique[product_variants.sku,variant_id,{variant_id}]',
        'shade'          => 'permit_empty|max_length[100]',
        'size'           => 'permit_empty|max_length[50]',
        'price'          => 'required|decimal',
        'stock_quantity' => 'permit_empty|is_natural',
        'is_active'      => 'permit_empty|in_list[0,1]',
    ];

    /**
     * Semua varian satu produk (termasuk yang nonaktif) — buat halaman admin.
     * [4.4-b] Urutan: shade -> size -> price, konsisten sama aktifUntukProduk().
     */
    public function untukProduk(int $productId): array
    {
        return $this->where('product_id', $productId)
                    ->orderBy('shade', 'ASC')
                    ->orderBy('size', 'ASC')
                    ->orderBy('price', 'ASC')
                    ->findAll();
    }

    /**
     * [4.3] Varian AKTIF satu produk buat halaman publik.
     * Urut: shade -> size -> harga.
     */
    public function aktifUntukProduk(int $productId): array
    {
        return $this->where('product_id', $productId)
                    ->where('is_active', 1)
                    ->orderBy('shade', 'ASC')
                    ->orderBy('size', 'ASC')
                    ->orderBy('price', 'ASC')
                    ->findAll();
    }

    public function cariDiProduk(int $variantId, int $productId): ?array
    {
        return $this->where('variant_id', $variantId)
                    ->where('product_id', $productId)
                    ->first();
    }

    /**
     * [4.4-b] Ringkasan varian buat DAFTAR PRODUK admin (anti N+1).
     *
     * Input  : kumpulan product_id yang tampil di satu halaman daftar.
     * Output : map [product_id => ['jumlah' => n, 'stok' => x]]
     *
     * Nama key WAJIB 'jumlah' dan 'stok' — disamakan persis sama yang
     * dipakai view admin/products/index.php (fallback-nya:
     * ['jumlah' => 0, 'stok' => 0]).
     *
     * Dihitung dari varian AKTIF doang (yang bisa dijual).
     * Produk tanpa varian aktif = key-nya nggak ada -> view otomatis
     * pakai fallback 0/0 lewat `??`.
     * Satu query gabungan buat semua produk di halaman, bukan 1 query per baris.
     */
    public function ringkasVarianBanyak(array $productIds): array
    {
        $productIds = array_values(array_unique(array_map('intval', $productIds)));
        if ($productIds === []) {
            return [];
        }

        $rows = $this->db->table('product_variants')
            ->select('product_id, COUNT(*) AS jumlah, COALESCE(SUM(stock_quantity), 0) AS stok')
            ->whereIn('product_id', $productIds)
            ->where('is_active', 1)
            ->groupBy('product_id')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['product_id']] = [
                'jumlah' => (int) $row['jumlah'],
                'stok'   => (int) $row['stok'],
            ];
        }

        return $map;
    }
}