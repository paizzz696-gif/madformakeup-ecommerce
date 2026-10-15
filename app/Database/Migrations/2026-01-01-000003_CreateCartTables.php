<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCartTables extends Migration
{
    public function up(): void
    {
        // ===== carts =====
        $this->forge->addField([
            'cart_id'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'status'     => ['type' => 'ENUM', 'constraint' => ['active', 'converted', 'abandoned'], 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('cart_id', true);
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('carts', true, ['ENGINE' => 'InnoDB']);

        // ===== cart_items =====
        $this->forge->addField([
            'cart_item_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'cart_id'      => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'variant_id'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'quantity'     => ['type' => 'INT', 'constraint' => 10, 'default' => 1],
            'added_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('cart_item_id', true);
        $this->forge->addUniqueKey(['cart_id', 'variant_id']); // BR-03: cegah duplikasi varian dalam 1 keranjang
        $this->forge->addForeignKey('cart_id', 'carts', 'cart_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('variant_id', 'product_variants', 'variant_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cart_items', true, ['ENGINE' => 'InnoDB']);

        $this->db->query("ALTER TABLE `cart_items` ADD CONSTRAINT `chk_cart_items_qty_positive` CHECK (`quantity` > 0)");
    }

    public function down(): void
    {
        $this->forge->dropTable('cart_items', true);
        $this->forge->dropTable('carts', true);
    }
}