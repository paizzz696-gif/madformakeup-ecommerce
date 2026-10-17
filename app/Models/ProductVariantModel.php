<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * MODEL VARIAN PRODUK — Tahap 4.1 (disesuaikan skema asli)
 * Skema: product_variants(variant_id PK, product_id FK, sku, shade, size, price, stock_quantity, is_active, created_at, updated_at)
 * Catatan arsitektur: setiap perubahan stok WAJIB tercatat di stock_movements.
 * Method stok (kurangi/tambah) sengaja masuk Tahap 4.4 bareng StockMovementModel.
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
        'product_id'     => 'required|is_natural_no_zero',
        'sku'            => 'required|alpha_numeric_punct|max_length[50]|is_unique[product_variants.sku,variant_id,{variant_id}]',
        'shade'          => 'permit_empty|max_length[100]',
        'size'           => 'permit_empty|max_length[50]',
        'price'          => 'required|decimal',
        'stock_quantity' => 'permit_empty|is_natural',
        'is_active'      => 'permit_empty|in_list[0,1]',
    ];

    public function untukProduk(int $productId): array
    {
        return $this->where('product_id', $productId)
                    ->orderBy('shade', 'ASC')
                    ->findAll();
    }

    public function cariDiProduk(int $variantId, int $productId): ?array
    {
        return $this->where('variant_id', $variantId)
                    ->where('product_id', $productId)
                    ->first();
    }
}