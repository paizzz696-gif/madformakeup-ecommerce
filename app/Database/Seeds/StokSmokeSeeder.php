<?php

namespace App\Database\Seeds;

use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use App\Models\StockMovementModel;
use CodeIgniter\Database\Seeder;

/**
 * SMOKE TEST STOK — Tahap 4.4-a
 * Jalankan: php spark db:seed StokSmokeSeeder
 * Self-cleaning: bikin rantai kategori -> produk -> varian sementara,
 * tes catatMasuk/catatKeluar, lalu hapus semua.
 */
class StokSmokeSeeder extends Seeder
{
    public function run()
    {
        $kategoriModel = new CategoryModel();
        $produkModel   = new ProductModel();
        $varianModel   = new ProductVariantModel();
        $stokModel     = new StockMovementModel();

        $pass = 0;
        $fail = 0;
        $cek  = function (string $nama, bool $ok) use (&$pass, &$fail): void {
            echo $ok ? "  [PASS] {$nama}\n" : "  [FAIL] {$nama}\n";
            if ($ok) { $pass++; } else { $fail++; }
        };

        // 1) Rantai data sementara
        $kategoriModel->insert([
            'category_name' => 'SMOKE Kategori Stok',
            'slug'          => 'smoke-kategori-stok-' . uniqid(),
            'description'   => 'Sementara — smoke test stok.',
            'is_active'     => 1,
        ]);
        $kategoriId = (int) $kategoriModel->getInsertID();

        $produkModel->insert([
            'category_id' => $kategoriId,
            'name'        => 'SMOKE Produk Stok',
            'slug'        => $produkModel->buatSlug('SMOKE Produk Stok'),
            'description' => 'Sementara — smoke test stok.',
            'is_active'   => 1,
        ]);
        $produkId = (int) $produkModel->getInsertID();

        $varianModel->insert([
            'product_id'     => $produkId,
            'sku'            => 'SMOKE-STK-' . uniqid(),
            'shade'          => 'Smoke Shade',
            'size'           => '5g',
            'price'          => 10000,
            'stock_quantity' => 0,
            'is_active'      => 1,
        ]);
        $varianId = (int) $varianModel->getInsertID();

        echo "Smoke test stok (varian sementara #{$varianId}):\n";

        // 2) Masuk 5 -> sisa 5
        $cek('catatMasuk 5 -> sisa 5', $stokModel->catatMasuk($varianId, 5) === 5);

        // 3) Keluar 2 -> sisa 3
        $cek('catatKeluar 2 -> sisa 3', $stokModel->catatKeluar($varianId, 2) === 3);

        // 4) Keluar melebihi stok -> ditolak, stok tetap
        $sisa = $stokModel->catatKeluar($varianId, 10);
        $cek('catatKeluar 10 (stok 3) -> false + pesan error', $sisa === false && $stokModel->lastError !== '');

        // 5) qty 0 -> ditolak
        $cek('catatMasuk 0 -> false', $stokModel->catatMasuk($varianId, 0) === false);

        // 6) Stok di DB beneran = 3
        $varian = $varianModel->find($varianId);
        $cek('stock_quantity di tabel = 3', $varian !== null && (int) $varian['stock_quantity'] === 3);

        // 7) Riwayat: cuma 2 movement valid (in +5, out -2)
        $riwayat = $stokModel->riwayatVarian($varianId);
        $cek('riwayat movement = 2 baris', count($riwayat) === 2);
        $cek('quantity bertanda (out = -2)', isset($riwayat[0]) && (int) $riwayat[0]['quantity'] === -2);

        // 8) Cleanup: varian dulu (movement kehapus otomatis via FK CASCADE — bukti SHOW CREATE)
        $varianModel->delete($varianId);
        $produkModel->delete($produkId);
        $kategoriModel->delete($kategoriId);

        echo $fail === 0
            ? "\nSEMUA PASS ({$pass}/" . ($pass + $fail) . ") — StockMovementModel siap.\n"
            : "\nADA YANG GAGAL ({$fail}/" . ($pass + $fail) . ") — paste output ini ke chat, jangan diterusin.\n";
    }
}