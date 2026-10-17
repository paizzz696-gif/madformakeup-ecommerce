<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * MODEL KATEGORI — Tahap 4.1 (disesuaikan skema asli dari CatalogSeeder lu)
 * Skema: categories(category_id PK, category_name, slug, description, is_active, created_at, updated_at)
 */
class CategoryModel extends Model
{
    protected $table          = 'categories';
    protected $primaryKey     = 'category_id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $allowedFields  = ['category_name', 'slug', 'description', 'is_active'];

    protected $validationRules = [
        'category_name' => 'required|min_length[2]|max_length[100]',
        'slug'          => 'permit_empty|alpha_dash|max_length[120]|is_unique[categories.slug,category_id,{category_id}]',
        'description'   => 'permit_empty|string',
        'is_active'     => 'permit_empty|in_list[0,1]',
    ];

    public function buatSlug(string $name, int $exceptId = 0): string
    {
        helper('url');

        $slug = url_title($name, '-', true);
        $base = $slug;
        $i    = 1;

        while ($this->where('slug', $slug)->where('category_id !=', $exceptId)->countAllResults() > 0) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function getAktif(): array
    {
        return $this->where('is_active', 1)
                    ->orderBy('category_name', 'ASC')
                    ->findAll();
    }
}