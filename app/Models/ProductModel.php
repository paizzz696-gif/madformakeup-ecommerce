<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * MODEL PRODUK — Tahap 4.1 (disesuaikan skema asli)
 * Skema: products(product_id PK, category_id FK, name, slug, description, is_active, created_at, updated_at)
 * PENTING: products GAK punya kolom price — harga ada di product_variants!
 * "Harga mulai dari" dihitung dari MIN(price) varian aktif → method hargaMulai().
 */
class ProductModel extends Model
{
    protected $table          = 'products';
    protected $primaryKey     = 'product_id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $allowedFields  = ['category_id', 'name', 'slug', 'description', 'is_active'];

    protected $validationRules = [
        'category_id' => 'required|is_natural_no_zero',
        'name'        => 'required|min_length[2]|max_length[150]',
        'slug'        => 'permit_empty|alpha_dash|max_length[160]|is_unique[products.slug,product_id,{product_id}]',
        'description' => 'permit_empty|string',
        'is_active'   => 'permit_empty|in_list[0,1]',
    ];

    public function buatSlug(string $name, int $exceptId = 0): string
    {
        helper('url');

        $slug = url_title($name, '-', true);
        $base = $slug;
        $i    = 1;

        while ($this->where('slug', $slug)->where('product_id !=', $exceptId)->countAllResults() > 0) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function withKategori(): self
    {
        $this->select('products.*, categories.category_name AS kategori_nama')
             ->join('categories', 'categories.category_id = products.category_id', 'left');

        return $this;
    }

    public function hanyaAktif(): self
    {
        $this->where('products.is_active', 1);

        return $this;
    }

    /**
     * Harga termurah dari varian aktif — buat badge "Mulai Rp xx" di katalog (4.2).
     */
    public function hargaMulai(int $productId): ?float
    {
        $row = $this->db->table('product_variants')
                        ->select('MIN(price) AS harga')
                        ->where('product_id', $productId)
                        ->where('is_active', 1)
                        ->get()
                        ->getRowArray();

        return $row ? (float) $row['harga'] : null;
    }
}