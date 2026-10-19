<?= $this->extend('layouts/storefront') ?>
<?= $this->section('content') ?>

<?php
 $metodeLabel = [
    'transfer' => 'Transfer Bank (BCA)',
    'qris'     => 'QRIS / e-Wallet',
    'cod'      => 'COD (Bayar di Tempat)',
];
 $statusLabel = [
    'pending'   => ['Menunggu Pembayaran', 'warning'],
    'paid'      => ['Dibayar — Sedang Disiapkan', 'info'],
    'shipped'   => ['Dikirim', 'primary'],
    'completed' => ['Selesai', 'success'],
    'cancelled' => ['Dibatalkan', 'secondary'],
];
[$statusTeks, $statusWarna] = $statusLabel[$order['status']] ?? ['Pending', 'secondary'];
?>

<div class="container py-5" style="max-width: 860px;">
    <div class="text-center mb-4">
        <div class="display-4 text-success"><i class="bi bi-check-circle-fill"></i></div>
        <h1 class="h3 fw-bold mt-2 mb-1">Pesanan Berhasil Dibuat!</h1>
        <p class="text-muted mb-2">Simpan kode pesanan ini buat cek status nanti ya.</p>
        <div class="badge rounded-pill bg-dark px-4 py-2 fs-6"><?= esc($order['order_code']) ?></div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <h5 class="fw-semibold mb-0">Detail Pesanan</h5>
                <span class="badge bg-<?= $statusWarna ?> px-3 py-2">
                    <?= $statusTeks ?> · <?= $order['payment_status'] === 'paid' ? 'Lunas' : 'Belum Bayar' ?>
                </span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr class="text-muted small">
                            <th>Produk</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= esc($it['product_name']) ?></div>
                                <small class="text-muted">
                                    <?= esc(trim((string) $it['shade']) !== '' ? $it['shade'] : $it['sku']) ?> · <?= esc($it['sku']) ?>
                                </small>
                            </td>
                            <td class="text-center"><?= (int) $it['quantity'] ?></td>
                            <td class="text-end">Rp <?= number_format($it['price'], 0, ',', '.') ?></td>
                            <td class="text-end">Rp <?= number_format($it['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-semibold">Total Bayar</td>
                            <td class="text-end fw-bold fs-5">Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <hr>
            <div class="row g-3">
                <div class="col-md-7">
                    <div class="text-muted small">Dikirim ke</div>
                    <div class="fw-semibold"><?= esc($order['receiver_name']) ?> · <?= esc($order['phone']) ?></div>
                    <div><?= esc($order['address']) ?></div>
                    <?php if (! empty($order['notes'])): ?>
                        <div class="text-muted fst-italic mt-1">Catatan: <?= esc($order['notes']) ?></div>
                    <?php endif ?>
                </div>
                <div class="col-md-5">
                    <div class="text-muted small">Metode Pembayaran</div>
                    <div class="fw-semibold"><?= $metodeLabel[$order['payment_method']] ?? esc($order['payment_method']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($order['payment_status'] === 'unpaid'): ?>
        <div class="card shadow-sm mb-4" style="border: 1.5px dashed #ffc107;">
            <div class="card-body p-4 text-center">
                <h5 class="fw-semibold">Simulasi Pembayaran</h5>
                <p class="text-muted mb-3">Di aplikasi beneran, di sini kamu bakal diarahkan ke payment gateway. Buat demo, tinggal klik tombol di bawah buat "membayar".</p>
                <form action="<?= site_url('checkout/' . $order['order_code'] . '/bayar') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success px-4 fw-semibold">
                        <i class="bi bi-wallet2 me-1"></i> Bayar Sekarang (Simulasi)
                    </button>
                </form>
            </div>
        </div>
    <?php endif ?>

    <div class="d-flex justify-content-center gap-2">
        <a href="<?= site_url('catalog') ?>" class="btn btn-dark px-4">Lanjut Belanja</a>
        <a href="<?= site_url('keranjang') ?>" class="btn btn-outline-dark px-4">Keranjang</a>
    </div>
</div>

<?= $this->endSection() ?>