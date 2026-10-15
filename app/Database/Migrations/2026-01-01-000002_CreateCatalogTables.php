<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCatalogTables extends Migration
{
    public function up(): void
    {
        // ===== categories =====
        $this->forge->addField([
            'category_id'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'category_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'slug'          => ['type' => 'VARCHAR', 'constraint' => 120],
            'description'   => ['type' => 'TEXT', 'null' => true],
            'is_active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('category_id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('categories', true, ['ENGINE' => 'InnoDB']);

        // ===== products (tanpa kolom stok agregat & harga — sesuai PRD, itu ada di varian) =====
        $this->forge->addField([
            'product_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'category_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 150],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 160],
            'description' => ['type' => 'TEXT', 'null' => true],
            'is_active'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('product_id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addForeignKey('category_id', 'categories', 'category_id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('products', true, ['ENGINE' => 'InnoDB']);

        // ===== product_variants =====
        $this->forge->addField([
            'variant_id'     => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'product_id'     => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'sku'            => ['type' => 'VARCHAR', 'constraint' => 50],
            'shade'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'size'           => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'price'          => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'stock_quantity' => ['type' => 'INT', 'constraint' => 10, 'default' => 0],
            'is_active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('variant_id', true);
        $this->forge->addUniqueKey('sku');
        $this->forge->addForeignKey('product_id', 'products', 'product_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('product_variants', true, ['ENGINE' => 'InnoDB']);

        // CHECK constraint ditegakkan MySQL 8 langsung di level database (mendukung AC-07)
        $this->db->query("ALTER TABLE `product_variants` ADD CONSTRAINT `chk_variant_price_nonneg` CHECK (`price` >= 0)");
        $this->db->query("ALTER TABLE `product_variants` ADD CONSTRAINT `chk_variant_stock_nonneg` CHECK (`stock_quantity` >= 0)");

        // ===== product_images =====
        $this->forge->addField([
            'image_id'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'product_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'image_path' => ['type' => 'VARCHAR', 'constraint' => 255],
            'is_primary' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'sort_order' => ['type' => 'INT', 'constraint' => 10, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('image_id', true);
        $this->forge->addForeignKey('product_id', 'products', 'product_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('product_images', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('product_images', true);
        $this->forge->dropTable('product_variants', true);
        $this->forge->dropTable('products', true);
        $this->forge->dropTable('categories', true);
    }
}