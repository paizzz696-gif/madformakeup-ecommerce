<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * [5.1] Keranjang belanja — satu baris berstatus 'active' per user.
 *
 * Bukti kolom (information_schema):
 * cart_id    int unsigned PK AI
 * user_id    int unsigned FK
 * status     enum('active','converted','abandoned')
 * created_at / updated_at datetime
 */
class CartModel extends Model
{
    protected $table          = 'carts';
    protected $primaryKey     = 'cart_id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $allowedFields  = ['user_id', 'status'];

    /**
     * Ambil keranjang 'active' milik user; buat baru kalau belum ada.
     * Dipakai semua fitur keranjang supaya user cuma punya SATU keranjang aktif.
     */
    public function keranjangAktif(int $userId): array
    {
        $keranjang = $this->where('user_id', $userId)
                          ->where('status', 'active')
                          ->first();

        if ($keranjang !== null) {
            return $keranjang;
        }

        $this->insert([
            'user_id' => $userId,
            'status'  => 'active',
        ], true);

        return $this->find((int) $this->db->insertID());
    }
}