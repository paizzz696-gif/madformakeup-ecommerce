<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // Admin dipakai sebagai created_by pada stok awal (butuh UserSeeder dijalankan dulu)
        $admin   = $this->db->table('users')->where('role', 'admin')->orderBy('user_id')->get()->getRowArray();
        $adminId = $admin['user_id'] ?? null;

        $this->db->transStart();

        // ---------- Kategori ----------
        $categories = [
            ['category_name' => 'Lips',        'slug' => 'lips',        'description' => 'Lipstik, lip tint, dan lip care.'],
            ['category_name' => 'Face',        'slug' => 'face',        'description' => 'Concealer, cushion, skintint, dan blush.'],
            ['category_name' => 'Eyes',        'slug' => 'eyes',        'description' => 'Alat dan produk untuk area mata.'],
            ['category_name' => 'Merchandise', 'slug' => 'merchandise', 'description' => 'Pouch, mirror, dan merchandise lainnya.'],
        ];

        $categoryMap = [];
        foreach ($categories as $c) {
            $this->db->table('categories')->insert(array_merge($c, [
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
            $categoryMap[$c['slug']] = $this->db->insertID();
        }

        // ---------- Produk, varian, gambar, stok awal ----------
        $products = [
            [
                'category' => 'face', 'name' => 'Pearlfect Cover Silk-Fit Concealer',
                'slug' => 'pearlfect-cover-silk-fit-concealer',
                'description' => 'Concealer ringan finish silk-fit untuk kulit berjerawat, coverage buildable.',
                'image' => 'products/concealer-silk-fit.jpg',
                'variants' => [
                    ['sku' => 'MFM-CNSL-LB01', 'shade' => 'Light Beige',  'size' => '5 ml', 'price' => 119000, 'stock' => 25],
                    ['sku' => 'MFM-CNSL-NB02', 'shade' => 'Natural Buff', 'size' => '5 ml', 'price' => 119000, 'stock' => 30],
                    ['sku' => 'MFM-CNSL-WH03', 'shade' => 'Warm Honey',   'size' => '5 ml', 'price' => 119000, 'stock' => 20],
                ],
            ],
            [
                'category' => 'face', 'name' => 'Pearlfect Skin-Fit Cushion',
                'slug' => 'pearlfect-skin-fit-cushion',
                'description' => 'Cushion hasil skin-like, tahan lama, nyaman untuk kulit acne-prone.',
                'image' => 'products/skin-fit-cushion.jpg',
                'variants' => [
                    ['sku' => 'MFM-CSHN-PO01', 'shade' => 'Porcelain', 'size' => '15 g', 'price' => 89000, 'stock' => 18],
                    ['sku' => 'MFM-CSHN-IV02', 'shade' => 'Ivory',     'size' => '15 g', 'price' => 89000, 'stock' => 22],
                    ['sku' => 'MFM-CSHN-SD03', 'shade' => 'Sand',      'size' => '15 g', 'price' => 89000, 'stock' => 15],
                ],
            ],
            [
                'category' => 'face', 'name' => 'Long Lasting Glass Blush',
                'slug' => 'long-lasting-glass-blush',
                'description' => 'Blush cair efek glass glow, bertahan lama, mudah di-blend.',
                'image' => 'products/glass-blush.jpg',
                'variants' => [
                    ['sku' => 'MFM-BLSH-CQ01', 'shade' => 'Coco Quartz', 'size' => '4 ml', 'price' => 109000, 'stock' => 24],
                    ['sku' => 'MFM-BLSH-RD02', 'shade' => 'Rose Dust',   'size' => '4 ml', 'price' => 109000, 'stock' => 26],
                    ['sku' => 'MFM-BLSH-PF03', 'shade' => 'Peach Fizz',  'size' => '4 ml', 'price' => 109000, 'stock' => 21],
                ],
            ],
            [
                'category' => 'lips', 'name' => 'Ink Like Air Glasstick',
                'slug' => 'ink-like-air-glasstick',
                'description' => 'Lipstik stain ringan seperti tinta, hasil glass finish.',
                'image' => 'products/ink-like-glasstick.jpg',
                'variants' => [
                    ['sku' => 'MFM-GLST-MR01', 'shade' => 'Mad Red',     'size' => '3 g', 'price' => 109000, 'stock' => 28],
                    ['sku' => 'MFM-GLST-BM02', 'shade' => 'Brick Mauve', 'size' => '3 g', 'price' => 109000, 'stock' => 25],
                    ['sku' => 'MFM-GLST-DC03', 'shade' => 'Dark Cocoa',  'size' => '3 g', 'price' => 109000, 'stock' => 19],
                ],
            ],
            [
                'category' => 'face', 'name' => 'Pearl-Fect Cover Skintint',
                'slug' => 'pearl-fect-cover-skintint',
                'description' => 'Skintint 23 ml coverage medium dengan finish natural.',
                'image' => 'products/cover-skintint.jpg',
                'variants' => [
                    ['sku' => 'MFM-SKNT-LT01', 'shade' => 'Light 01',  'size' => '23 ml', 'price' => 149000, 'stock' => 20],
                    ['sku' => 'MFM-SKNT-MD02', 'shade' => 'Medium 02', 'size' => '23 ml', 'price' => 149000, 'stock' => 23],
                    ['sku' => 'MFM-SKNT-TN03', 'shade' => 'Tan 03',    'size' => '23 ml', 'price' => 149000, 'stock' => 17],
                ],
            ],
            [
                'category' => 'eyes', 'name' => 'Lash Lift Curler Pro',
                'slug' => 'lash-lift-curler-pro',
                'description' => 'Penjepit bulu mata stainless dengan lengkungan ergonomis.',
                'image' => 'products/lash-curler.jpg',
                'variants' => [
                    ['sku' => 'MFM-EYES-LC01', 'shade' => null, 'size' => '1 pcs', 'price' => 59000, 'stock' => 40],
                ],
            ],
            [
                'category' => 'merchandise', 'name' => 'Mad Pouch Mini',
                'slug' => 'mad-pouch-mini',
                'description' => 'Pouch mini untuk makeup esensial.',
                'image' => 'products/mad-pouch.jpg',
                'variants' => [
                    ['sku' => 'MFM-MRCH-PC01', 'shade' => null, 'size' => 'Free size', 'price' => 75000, 'stock' => 35],
                ],
            ],
        ];

        foreach ($products as $p) {
            $this->db->table('products')->insert([
                'category_id' => $categoryMap[$p['category']],
                'name'        => $p['name'],
                'slug'        => $p['slug'],
                'description' => $p['description'],
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $productId = $this->db->insertID();

            $this->db->table('product_images')->insert([
                'product_id' => $productId,
                'image_path' => $p['image'], // file gambar baru ditambahkan di Tahap 4
                'is_primary' => 1,
                'sort_order' => 0,
                'created_at' => $now,
            ]);

            foreach ($p['variants'] as $v) {
                $this->db->table('product_variants')->insert([
                    'product_id'     => $productId,
                    'sku'            => $v['sku'],
                    'shade'          => $v['shade'],
                    'size'           => $v['size'],
                    'price'          => $v['price'],
                    'stock_quantity' => $v['stock'],
                    'is_active'      => 1,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
                $variantId = $this->db->insertID();

                $this->db->table('stock_movements')->insert([
                    'variant_id'     => $variantId,
                    'movement_type'  => 'in',
                    'quantity'       => $v['stock'],
                    'reference_type' => 'initial_stock',
                    'reference_id'   => null,
                    'notes'          => 'Stok awal dari seeder',
                    'created_by'     => $adminId,
                    'created_at'     => $now,
                ]);
            }
        }

        $this->db->transComplete();
    }
}