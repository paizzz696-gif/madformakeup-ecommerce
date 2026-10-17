<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * MODEL GAMBAR PRODUK — Tahap 4.1 (disesuaikan skema asli)
 * Skema: product_images(image_id PK, product_id FK, image_path, is_primary, sort_order, created_at)
 * Bukti dari seeder lu: tabel ini cuma nulis created_at → $updatedField dikosongin.
 * (Kalau SHOW CREATE TABLE ternyata ada updated_at, hapus baris $updatedField.)
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

    public function untukProduk(int $productId): array
    {
        return $this->where('product_id', $productId)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('image_id', 'ASC')
                    ->findAll();
    }

    public function utamaProduk(int $productId): ?array
    {
        return $this->where('product_id', $productId)
                    ->where('is_primary', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->first();
    }
}