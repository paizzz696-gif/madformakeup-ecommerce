<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersAndAddresses extends Migration
{
    public function up(): void
    {
        // ===== users =====
        $this->forge->addField([
            'user_id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'phone'         => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'role'          => ['type' => 'ENUM', 'constraint' => ['customer', 'admin'], 'default' => 'customer'],
            'is_active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('user_id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users', true, ['ENGINE' => 'InnoDB']);

        // ===== addresses =====
        $this->forge->addField([
            'address_id'     => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id'        => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'recipient_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'phone'          => ['type' => 'VARCHAR', 'constraint' => 20],
            'province'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'city'           => ['type' => 'VARCHAR', 'constraint' => 100],
            'postal_code'    => ['type' => 'VARCHAR', 'constraint' => 10],
            'full_address'   => ['type' => 'TEXT'],
            'is_default'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('address_id', true);
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('addresses', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('addresses', true);
        $this->forge->dropTable('users', true);
    }
}