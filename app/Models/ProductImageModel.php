<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * MODEL GAMBAR PRODUK — Tahap 4.1 + 4.4-d
 * Skema: product_images(image_id PK, product_id FK, image_path, is_primary, sort_order, created_at)
 * $updatedField tetap kosong (tabel cuma nulis created_at, sesuai bukti seeder).
 * Baru di 4.4-d: jadikanUtama() (transaction 2 langkah) + urutanBerikutnya().
 */
class ProductImageModel extends Model
{
    protected $table          = 'product_images';
    protected $primaryKey     = 'image_id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $updatedField   = ''; // tabel gak punya updated_at (sesuai bukti seeder)
    protected $allowedFields  = ['product_id', 'image_path', 'is_primary', 'sort_order'];

    protected $validationRules = [
        'product_id' => 'required|is_natural_no_zero',
        'image_path' => 'required|max_length[255]',
        'is_primary' => 'permit_empty|in_list[0,1]',
        'sort_order' => 'permit_empty|is_natural',
    ];

    /**
     * Semua gambar satu produk, urut sort_order lalu image_id.
     * Dipakai halaman admin kelola gambar + detail produk publik.
     */
    public function untukProduk(int $productId): array
    {
        return $this->where('product_id', $productId)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('image_id', 'ASC')
                    ->findAll();
    }

    /**
     * Gambar utama satu produk (is_primary = 1, paling depan).
     * Return null kalau produk belum punya gambar utama.
     */
    public function utamaProduk(int $productId): ?array
    {
        return $this->where('product_id', $productId)
                    ->where('is_primary', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->first();
    }

    /**
     * [4.4-d] Angkat satu gambar jadi UTAMA dengan aman:
     * langkah 1: semua gambar produk ini is_primary = 0
     * langkah 2: gambar yang ditunjuk is_primary = 1
     * Dibungkus transaction biar gak mungkin keadaan "dua utama"
     * kalau proses kepotong di tengah jalan.
     */
    public function jadikanUtama(int $productId, int $imageId): bool
    {
        $this->db->transStart();

        $this->db->table($this->table)
                 ->where('product_id', $productId)
                 ->update(['is_primary' => 0]);

        $this->db->table($this->table)
                 ->where('image_id', $imageId)
                 ->where('product_id', $productId) // pengaman: harus milik produk ini
                 ->update(['is_primary' => 1]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * [4.4-d] Nomor urut berikutnya buat gambar baru (terakhir + 1)
     * → gambar baru selalu nongol paling bawah galeri.
     */
    public function urutanBerikutnya(int $productId): int
    {
        $row = $this->where('product_id', $productId)
                    ->selectMax('sort_order')
                    ->first();

        return (int) ($row['sort_order'] ?? 0) + 1;
    }
}