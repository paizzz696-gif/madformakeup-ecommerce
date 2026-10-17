<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * MODEL PERGERAKAN STOK — Tahap 4.4-a
 * Skema (bukti SHOW CREATE TABLE):
 *   stock_movements(movement_id PK AUTO_INCREMENT, variant_id FK NOT NULL,
 *     movement_type ENUM('in','out','adjustment') NOT NULL, quantity INT NOT NULL,
 *     reference_type VARCHAR(30) NULL, reference_id INT UNSIGNED NULL,
 *     notes VARCHAR(255) NULL, created_by INT UNSIGNED NULL (FK users), created_at DATETIME NULL)
 *
 * ATURAN ARSITEKTUR (disepakati sejak 4.1):
 *   Stok varian TIDAK BOLEH diubah langsung (misal lewat form edit).
 *   Semua perubahan WAJIB lewat catatMasuk()/catatKeluar() di model ini,
 *   supaya update stok + catatan movement terjadi ATOMIK dalam 1 transaction.
 *
 * Konvensi quantity BERTANDA: 'in' positif, 'out' negatif.
 * Keuntungan: SUM(quantity) = perubahan stok neto (mudah buat laporan).
 */
class StockMovementModel extends Model
{
    protected $table          = 'stock_movements';
    protected $primaryKey     = 'movement_id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $updatedField   = ''; // tabel gak punya updated_at (bukti SHOW CREATE TABLE)
    protected $allowedFields  = [
        'variant_id', 'movement_type', 'quantity',
        'reference_type', 'reference_id', 'notes', 'created_by',
    ];

    protected $validationRules = [
        'variant_id'     => 'required|is_natural_no_zero',
        'movement_type'  => 'required|in_list[in,out,adjustment]',
        'quantity'       => 'required|integer',
        'reference_type' => 'permit_empty|max_length[30]',
        'reference_id'   => 'permit_empty|is_natural',
        'notes'          => 'permit_empty|max_length[255]',
        'created_by'     => 'permit_empty|is_natural_no_zero',
    ];

    /** Pesan error terakhir — dibaca controller kalau method balikin false. */
    public string $lastError = '';

    /**
     * Catat stok MASUK + tambahkan stock_quantity varian (1 transaction).
     * Return: sisa stok terbaru (int) kalau sukses, false kalau gagal.
     */
    public function catatMasuk(int $variantId, int $qty, string $referenceType = '', ?int $referenceId = null, string $notes = '', ?int $userId = null)
    {
        if ($qty <= 0) {
            $this->lastError = 'Jumlah stok masuk harus lebih dari 0.';
            return false;
        }

        return $this->gerakStok($variantId, $qty, 'in', $referenceType, $referenceId, $notes, $userId);
    }

    /**
     * Catat stok KELUAR + kurangi stock_quantity varian (1 transaction).
     * Gagal (false) kalau stok tidak cukup — stok tidak boleh minus.
     */
    public function catatKeluar(int $variantId, int $qty, string $referenceType = '', ?int $referenceId = null, string $notes = '', ?int $userId = null)
    {
        if ($qty <= 0) {
            $this->lastError = 'Jumlah stok keluar harus lebih dari 0.';
            return false;
        }

        return $this->gerakStok($variantId, -$qty, 'out', $referenceType, $referenceId, $notes, $userId);
    }

    /**
     * Inti masuk/keluar:
     *   1. baca stok varian,
     *   2. hitung stok baru (keluar ditolak kalau jadi minus),
     *   3. update product_variants.stock_quantity,
     *   4. insert baris stock_movements.
     * Semua dalam transaction: gagal di tengah = batal SEMUA (stok ga berubah, ga ada catatan).
     */
    private function gerakStok(int $variantId, int $delta, string $tipe, string $referenceType, ?int $referenceId, string $notes, ?int $userId)
    {
        $db = $this->db;

        $db->transBegin();

        try {
            $varian = $db->table('product_variants')
                         ->where('variant_id', $variantId)
                         ->get()
                         ->getRowArray();

            if ($varian === null) {
                $db->transRollback();
                $this->lastError = "Varian dengan id {$variantId} tidak ditemukan.";
                return false;
            }

            $baru = (int) $varian['stock_quantity'] + $delta;
            if ($baru < 0) {
                $db->transRollback();
                $this->lastError = 'Stok tidak cukup: sisa ' . (int) $varian['stock_quantity'] . ', minta keluar ' . (-$delta) . '.';
                return false;
            }

            // Update stok — sengaja lewat query builder (bukan validasi model),
            // karena pintu ubah stok memang CUMA method ini.
            $db->table('product_variants')
               ->where('variant_id', $variantId)
               ->update([
                   'stock_quantity' => $baru,
                   'updated_at'     => date('Y-m-d H:i:s'),
               ]);

            $ok = $this->insert([
                'variant_id'     => $variantId,
                'movement_type'  => $tipe,
                'quantity'       => $delta, // bertanda: in positif, out negatif
                'reference_type' => $referenceType !== '' ? $referenceType : null,
                'reference_id'   => $referenceId,
                'notes'          => $notes !== '' ? $notes : null,
                'created_by'     => $userId,
            ]);

            if ($ok === false) {
                $db->transRollback();
                $this->lastError = 'Data movement tidak valid: ' . implode(', ', $this->errors() ?: ['tidak diketahui']);
                return false;
            }

            $db->transCommit();

            return $baru;
        } catch (\Throwable $e) {
            $db->transRollback();
            $this->lastError = 'Gagal menyimpan pergerakan stok: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Riwayat movement 1 varian (terbaru dulu) + nama user pencatat.
     * Dipakai UI admin di 4.4-b.
     */
    public function riwayatVarian(int $variantId, int $limit = 20): array
    {
        return $this->select('stock_movements.*, users.name AS dicatat_oleh')
                    ->join('users', 'users.user_id = stock_movements.created_by', 'left')
                    ->where('stock_movements.variant_id', $variantId)
                    ->orderBy('stock_movements.movement_id', 'DESC')
                    ->findAll($limit);
    }
}