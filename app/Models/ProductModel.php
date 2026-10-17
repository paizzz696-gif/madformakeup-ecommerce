<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * MODEL PRODUK — Tahap 4.1 (skema asli) + 4.2 (query katalog)
 * Skema: products(product_id PK, category_id FK, name, slug, description, is_active, created_at, updated_at)
 * PENTING: products GAK punya kolom price — harga ada di product_variants!
 * "Harga mulai dari" = MIN(price) varian aktif.
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
     * Harga termurah varian aktif untuk SATU produk.
     * (Dipakai ModelSmokeSeeder — jangan dihapus.)
     */
    public function hargaMulai(int $productId): ?float
    {
        $row = $this->db->table('product_variants')
                        ->select('MIN(price) AS harga')
                        ->where('product_id', $productId)
                        ->where('is_active', 1)
                        ->get()
                        ->getRowArray();

        // Produk tanpa varian aktif -> MIN(price) = NULL -> balikin null, JANGAN 0.0
        return ($row && $row['harga'] !== null) ? (float) $row['harga'] : null;
    }

    /**
     * [4.2] Peta [product_id => image_path] gambar utama untuk banyak produk sekaligus.
     * 1 query buat semua produk di halaman — hindari N+1 query.
     */
    public function gambarUtamaBanyak(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        $rows = $this->db->table('product_images')
                         ->select('product_id, image_path')
                         ->whereIn('product_id', $productIds)
                         ->where('is_primary', 1)
                         ->orderBy('sort_order', 'ASC')
                         ->orderBy('image_id', 'ASC')
                         ->get()
                         ->getResultArray();

        $map = [];
        foreach ($rows as $r) {
            $map[$r['product_id']] = $r['image_path'];
        }

        return $map;
    }

    /**
     * [4.2] Peta [product_id => harga termurah] varian aktif untuk banyak produk sekaligus.
     */
    public function hargaMulaiBanyak(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        $rows = $this->db->table('product_variants')
                         ->select('product_id, MIN(price) AS harga')
                         ->whereIn('product_id', $productIds)
                         ->where('is_active', 1)
                         ->groupBy('product_id')
                         ->get()
                         ->getResultArray();

        $map = [];
        foreach ($rows as $r) {
            $map[$r['product_id']] = (float) $r['harga'];
        }

        return $map;
    }
}