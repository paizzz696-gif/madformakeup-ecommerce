<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table         = 'orders';
    protected $primaryKey    = 'order_id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'order_code', 'user_id', 'cart_id', 'receiver_name', 'phone',
        'address', 'notes', 'total_amount', 'payment_method',
        'payment_status', 'status',
    ];

    /**
     * Cari pesanan berdasarkan kode + pastikan pemiliknya user ini.
     * (ownership check — user lain tidak bisa melihat pesanan orang)
     */
    public function cariKode(string $kode, int $userId): ?array
    {
        $row = $this->where('order_code', $kode)
                    ->where('user_id', $userId)
                    ->first();

        return $row ?: null;
    }

    /**
     * Bikin kode pesanan unik, contoh: MFM-251017-9F3A
     */
    public function buatKode(): string
    {
        do {
            $kode = 'MFM-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $ada  = $this->where('order_code', $kode)->countAllResults();
        } while ($ada > 0);

        return $kode;
    }
}