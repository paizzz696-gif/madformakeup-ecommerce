<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->db->table('users')->insertBatch([
            [
                'name'          => 'Admin Development',
                'email'         => 'admin@madformakeup.local',
                // Kredensial khusus lingkungan pengembangan lokal, aman di repository
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'phone'         => '081200000001',
                'role'          => 'admin',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'name'          => 'Customer Demo',
                'email'         => 'customer@madformakeup.local',
                'password_hash' => password_hash('customer123', PASSWORD_DEFAULT),
                'phone'         => '081200000002',
                'role'          => 'customer',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ]);
    }
}