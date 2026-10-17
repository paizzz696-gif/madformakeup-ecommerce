<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use App\Models\ProductImageModel;

/**
 * SMOKE TEST TAHAP 4.1 — uji 4 model ujung-ke-ujung.
 * Insert → baca → update → hapus. SELF-CLEANING, gak nyisain data.
 * Jalanin: php spark db:seed ModelSmokeSeeder
 */
class ModelSmokeSeeder extends Seeder
{
    public function run(): void
    {
        $cat  = new CategoryModel();
        $prod = new ProductModel();
        $var  = new ProductVariantModel();
        $img  = new ProductImageModel();

        // ---------- INSERT ----------
        $catId = $cat->insert([
            'category_name' => 'ZZ-Test-Smoke',
            'slug'          => 'zz-test-smoke',
            'description'   => 'Data uji smoke test',
            'is_active'     => 1,
        ], true);
        echo '✓ insert kategori' . PHP_EOL;

        $prodId = $prod->insert([
            'category_id' => $catId,
            'name'        => 'ZZ-Test-Produk',
            'slug'        => 'zz-test-produk',
            'description' => 'Data uji smoke test',
            'is_active'   => 1,
        ], true);
        echo '✓ insert produk' . PHP_EOL;

        $varId = $var->insert([
            'product_id'     => $prodId,
            'sku'            => 'ZZ-TEST-001',
            'shade'          => 'Test Shade',
            'size'           => '1 pcs',
            'price'          => 10000,
            'stock_quantity' => 5,
            'is_active'      => 1,
        ], true);
        echo '✓ insert varian' . PHP_EOL;

        $img->insert([
            'product_id' => $prodId,
            'image_path' => 'products/zz-test.jpg',
            'is_primary' => 1,
            'sort_order' => 0,
        ]);
        echo '✓ insert gambar' . PHP_EOL;

        // ---------- READ + relasi ----------
        $p = $prod->withKategori()->hanyaAktif()->find($prodId);
        echo (($p !== null && ($p['kategori_nama'] ?? '') === 'ZZ-Test-Smoke') ? '✓' : '✗') . ' join kategori' . PHP_EOL;

        // ---------- UPDATE ----------
        $var->update($varId, ['stock_quantity' => 9]);
        $cek = $var->find($varId);
        echo (((int) $cek['stock_quantity'] === 9) ? '✓' : '✗') . ' update stok varian' . PHP_EOL;

        $harga = $prod->hargaMulai($prodId);
        echo (((float) $harga === 10000.0) ? '✓' : '✗') . ' hargaMulai() = ' . var_export($harga, true) . PHP_EOL;

        // ---------- CLEANUP ----------
        $img->where('product_id', $prodId)->delete();
        $var->delete($varId);
        $prod->delete($prodId);
        $cat->delete($catId);
        echo '✓ cleanup — data uji dihapus semua' . PHP_EOL;

        echo PHP_EOL . 'SMOKE TEST SELESAI — semua ✓ = models siap commit!' . PHP_EOL;
    }
}