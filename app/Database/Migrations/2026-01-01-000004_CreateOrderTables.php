<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrderTables extends Migration
{
    public function up(): void
    {
        // ===== orders =====
        $this->forge->addField([
            'order_id'          => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'order_number'      => ['type' => 'VARCHAR', 'constraint' => 30],
            'user_id'           => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'address_id'        => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'status'            => ['type' => 'ENUM', 'constraint' => [
                                    'pending_payment', 'confirmed', 'processing', 'shipped',
                                    'delivered', 'completed', 'cancelled'], 'default' => 'pending_payment'],
            'subtotal'          => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'shipping_cost'     => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'total_amount'      => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'recipient_snapshot'=> ['type' => 'VARCHAR', 'constraint' => 100],
            'phone_snapshot'    => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'address_snapshot'  => ['type' => 'TEXT'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('order_id', true);
        $this->forge->addUniqueKey('order_number');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('address_id', 'addresses', 'address_id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('orders', true, ['ENGINE' => 'InnoDB']);

        // ===== order_items (snapshot historis, BR-09) =====
        $this->forge->addField([
            'order_item_id'         => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'order_id'              => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'variant_id'            => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'product_name_snapshot' => ['type' => 'VARCHAR', 'constraint' => 150],
            'variant_snapshot'      => ['type' => 'VARCHAR', 'constraint' => 150],
            'unit_price'            => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'quantity'              => ['type' => 'INT', 'constraint' => 10],
            'subtotal'              => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('order_item_id', true);
        $this->forge->addForeignKey('order_id', 'orders', 'order_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('variant_id', 'product_variants', 'variant_id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('order_items', true, ['ENGINE' => 'InnoDB']);

        $this->db->query("ALTER TABLE `order_items` ADD CONSTRAINT `chk_order_items_qty_positive` CHECK (`quantity` > 0)");

        // ===== payments (status pembayaran terpisah dari status pesanan, BR-11) =====
        $this->forge->addField([
            'payment_id'      => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'order_id'        => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'payment_method'  => ['type' => 'VARCHAR', 'constraint' => 50],
            'amount'          => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'payment_status'  => ['type' => 'ENUM', 'constraint' => ['pending', 'success', 'failed', 'expired'], 'default' => 'pending'],
            'reference_number'=> ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'paid_at'         => ['type' => 'DATETIME', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('payment_id', true);
        $this->forge->addKey('payment_status');
        $this->forge->addForeignKey('order_id', 'orders', 'order_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('payments', true, ['ENGINE' => 'InnoDB']);

        // ===== shipments =====
        $this->forge->addField([
            'shipment_id'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'order_id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'courier_name'   => ['type' => 'VARCHAR', 'constraint' => 50],
            'tracking_number'=> ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'shipping_cost'  => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'status'         => ['type' => 'ENUM', 'constraint' => ['pending', 'shipped', 'in_transit', 'delivered'], 'default' => 'pending'],
            'shipped_at'     => ['type' => 'DATETIME', 'null' => true],
            'delivered_at'   => ['type' => 'DATETIME', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('shipment_id', true);
        $this->forge->addForeignKey('order_id', 'orders', 'order_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('shipments', true, ['ENGINE' => 'InnoDB']);

        // ===== order_status_logs (audit, BR-13) =====
        $this->forge->addField([
            'log_id'     => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'order_id'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'changed_by' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'old_status' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'new_status' => ['type' => 'VARCHAR', 'constraint' => 30],
            'notes'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('log_id', true);
        $this->forge->addForeignKey('order_id', 'orders', 'order_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('changed_by', 'users', 'user_id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('order_status_logs', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('order_status_logs', true);
        $this->forge->dropTable('shipments', true);
        $this->forge->dropTable('payments', true);
        $this->forge->dropTable('order_items', true);
        $this->forge->dropTable('orders', true);
    }
}