<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderItemModel extends Model
{
    protected $table         = 'order_items';
    protected $primaryKey    = 'order_item_id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';  // tabel order_items gak punya updated_at
    protected $updatedField  = '';
    protected $allowedFields = [
        'order_id', 'variant_id', 'product_name', 'shade', 'sku',
        'price', 'quantity', 'subtotal',
    ];

    /**
     * Semua item milik satu pesanan.
     */
    public function itemPesanan(int $orderId): array
    {
        return $this->where('order_id', $orderId)
                    ->orderBy('order_item_id', 'ASC')
                    ->findAll();
    }
}