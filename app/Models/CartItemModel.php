<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * [5.1] Isi keranjang — level VARIAN (shade spesifik), sesuai desain.
 *
 * Bukti kolom (information_schema):
 * cart_item_id int unsigned PK AI
 * cart_id      int unsigned FK -> carts
 * variant_id   int unsigned FK -> product_variants
 * quantity     int
 * added_at     datetime (pengganti created_at — tabel ini gak punya created_at)
 */
class CartItemModel extends Model
{
    protected $table          = 'cart_items';
    protected $primaryKey     = 'cart_item_id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $createdField   = 'added_at'; // CI4 isi kolom ini otomatis saat insert
    protected $updatedField   = '';         // tabel gak punya updated_at
    protected $allowedFields  = ['cart_id', 'variant_id', 'quantity'];

    /**
     * Cari baris keranjang untuk varian tertentu — dipakai buat
     * MERGE kalau shade yang sama ditambahkan lagi (bukan baris baru).
     */
    public function itemUntuk(int $cartId, int $variantId): ?array
    {
        return $this->where('cart_id', $cartId)
                    ->where('variant_id', $variantId)
                    ->first();
    }

    /**
     * Isi keranjang lengkap: data varian + produk + gambar utama.
     * Dipakai halaman Keranjang (5.3).
     */
    public function isiKeranjang(int $cartId): array
    {
        return $this->select(
                'cart_items.cart_item_id, cart_items.variant_id, cart_items.quantity, cart_items.added_at,
                 pv.shade, pv.size, pv.sku, pv.price, pv.stock_quantity, pv.is_active,
                 p.product_id, p.name AS nama_produk,
                 img.image_path'
            )
            ->join('product_variants pv', 'pv.variant_id = cart_items.variant_id')
            ->join('products p', 'p.product_id = pv.product_id')
            ->join('product_images img', 'img.product_id = p.product_id AND img.is_primary = 1', 'left')
            ->where('cart_items.cart_id', $cartId)
            ->orderBy('cart_items.added_at', 'ASC')
            ->findAll();
    }

    /**
     * [5.3] Total pcs di keranjang aktif seorang user — buat badge di navbar.
     */
    public function jumlahItemUser(int $userId): int
    {
        $row = $this->select('COALESCE(SUM(cart_items.quantity), 0) AS total')
                    ->join('carts', 'carts.cart_id = cart_items.cart_id')
                    ->where('carts.user_id', $userId)
                    ->where('carts.status', 'active')
                    ->first();

        return (int) ($row['total'] ?? 0);
    }
}