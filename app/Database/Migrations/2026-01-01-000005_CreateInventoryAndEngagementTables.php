<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryAndEngagementTables extends Migration
{
    public function up(): void
    {
        // ===== stock_movements =====
        $this->forge->addField([
            'movement_id'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'variant_id'     => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'movement_type'  => ['type' => 'ENUM', 'constraint' => ['in', 'out', 'adjustment']],
            'quantity'       => ['type' => 'INT', 'constraint' => 10], // selalu positif; arah dari movement_type
            'reference_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'reference_id'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'notes'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by'     => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('movement_id', true);
        $this->forge->addKey(['reference_type', 'reference_id']); // untuk cek idempotensi pemulihan stok
        $this->forge->addForeignKey('variant_id', 'product_variants', 'variant_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'user_id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('stock_movements', true, ['ENGINE' => 'InnoDB']);

        // ===== wishlists =====
        $this->forge->addField([
            'wishlist_id'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'wishlist_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 'Favorit'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('wishlist_id', true);
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wishlists', true, ['ENGINE' => 'InnoDB']);

        // ===== wishlist_items =====
        $this->forge->addField([
            'wishlist_item_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'wishlist_id'      => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'product_id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('wishlist_item_id', true);
        $this->forge->addUniqueKey(['wishlist_id', 'product_id']);
        $this->forge->addForeignKey('wishlist_id', 'wishlists', 'wishlist_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'product_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wishlist_items', true, ['ENGINE' => 'InnoDB']);

        // ===== reviews (maks 1 review per order item, BR-15) =====
        $this->forge->addField([
            'review_id'     => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'product_id'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'order_item_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'rating'        => ['type' => 'TINYINT', 'constraint' => 1],
            'comment'       => ['type' => 'TEXT', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('review_id', true);
        $this->forge->addUniqueKey('order_item_id');
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('product_id', 'products', 'product_id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('order_item_id', 'order_items', 'order_item_id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('reviews', true, ['ENGINE' => 'InnoDB']);

        $this->db->query("ALTER TABLE `reviews` ADD CONSTRAINT `chk_reviews_rating_range` CHECK (`rating` BETWEEN 1 AND 5)");
    }

    public function down(): void
    {
        $this->forge->dropTable('reviews', true);
        $this->forge->dropTable('wishlist_items', true);
        $this->forge->dropTable('wishlists', true);
        $this->forge->dropTable('stock_movements', true);
    }
}