<?= $this->extend('layouts/storefront') ?>
<?= $this->section('content') ?>

<?php
 $adaGambar = static function (?string $path): bool {
    return $path !== null && $path !== '' && file_exists(FCPATH . 'uploads/' . $path);
};
?>

<div class="container py-4">
    <h1 class="h3 mb-1"><i class="bi bi-bag"></i> Keranjang Belanja</h1>
    <p class="text-muted small mb-4"><?= $jumlahItem ?> barang di keranjangmu.</p>

    <?php if ($items === []): ?>
        <div class="text-center bg-light rounded border py-5 my-3">
            <i class="bi bi-bag-x display-4 text-secondary"></i>
            <h4 class="mt-3">Keranjang kamu masih kosong</h4>
            <p class="text-muted mb-4">Yuk pilih produk makeup favoritmu dulu.</p>
            <a href="<?= site_url('catalog') ?>" class="btn btn-dark px-4">
                <i class="bi bi-grid"></i> Belanja Sekarang
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-muted small">
                                <th>Produk</th>
                                <th>Harga</th>
                                <th style="min-width:130px;">Jumlah</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <?php
                                    $labelVarian = trim((string) ($item['shade'] ?? ''));
                                    if ($labelVarian === '') { $labelVarian = trim((string) ($item['size'] ?? '')); }
                                    if ($labelVarian === '') { $labelVarian = $item['sku']; }

                                    $stok        = (int) $item['stock_quantity'];
                                    $subtotal    = (float) $item['price'] * (int) $item['quantity'];
                                    $tidakDijual = (int) $item['is_active'] !== 1;
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if ($adaGambar($item['image_path'])): ?>
                                                <img src="<?= base_url('uploads/' . $item['image_path']) ?>" width="64" height="64"
                                                     class="rounded border" style="object-fit:cover;" alt="<?= esc($item['nama_produk']) ?>">
                                            <?php else: ?>
                                                <div class="d-flex align-items-center justify-content-center bg-light rounded border" style="width:64px;height:64px;">
                                                    <i class="bi bi-image text-secondary"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-semibold"><?= esc($item['nama_produk']) ?></div>
                                                <div class="small text-muted">Shade: <?= esc($labelVarian) ?></div>
                                                <div class="small text-muted">SKU: <?= esc($item['sku']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Rp <?= number_format((float) $item['price'], 0, ',', '.') ?></td>
                                    <td>
                                        <form action="<?= site_url('keranjang/' . $item['cart_item_id'] . '/qty') ?>" method="post" class="d-flex gap-1" style="max-width:150px;">
                                            <?= csrf_field() ?>
                                            <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>"
                                                   min="1" max="<?= $stok > 0 ? $stok : (int) $item['quantity'] ?>"
                                                   class="form-control form-control-sm text-center" required>
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Perbarui jumlah">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        </form>
                                        <?php if ($tidakDijual): ?>
                                            <div class="small text-danger mt-1"><i class="bi bi-x-circle"></i> Varian tidak dijual lagi.</div>
                                        <?php elseif ($stok <= 0): ?>
                                            <div class="small text-danger mt-1"><i class="bi bi-x-circle"></i> Stok habis.</div>
                                        <?php elseif ((int) $item['quantity'] > $stok): ?>
                                            <div class="small text-danger mt-1"><i class="bi bi-exclamation-triangle"></i> Stok tinggal <?= $stok ?> pcs.</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-semibold">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                    <td class="text-end">
                                        <form action="<?= site_url('keranjang/' . $item['cart_item_id'] . '/hapus') ?>" method="post"
                                              onsubmit="return confirm('Hapus item ini dari keranjang?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus item">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Ringkasan</h5>
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Total barang</span><span><?= $jumlahItem ?> pcs</span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-3">
                            <span>Jenis item</span><span><?= count($items) ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold">Total</span>
                            <span class="fs-5 fw-bold">Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                        <a href="<?= site_url('checkout') ?>" class="btn btn-dark w-100">
                            <i class="bi bi-credit-card"></i> Lanjut ke Checkout
                        </a>
                        <p class="small text-muted mt-2 mb-0">
                            <i class="bi bi-info-circle"></i> Pembayaran di demo ini berstatus simulasi.
                        </p>
                    </div>
                </div>
                <a href="<?= site_url('catalog') ?>" class="btn btn-link btn-sm text-decoration-none mt-2 px-0">
                    <i class="bi bi-arrow-left"></i> Lanjut belanja
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>