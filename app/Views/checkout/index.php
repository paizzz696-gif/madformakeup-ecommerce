<?= $this->extend('layouts/storefront') ?>
<?= $this->section('content') ?>

<style>
    .mfm-pay-option {
        border: 1.5px solid #e9ecef;
        border-radius: .75rem;
        padding: .9rem 1.1rem;
        margin-bottom: .6rem;
        cursor: pointer;
        transition: border-color .15s ease, background-color .15s ease;
    }
    .mfm-pay-option:hover { border-color: #ced4da; }
    .mfm-pay-option:has(input:checked) {
        border-color: #212529;
        background-color: #f8f5f2;
    }
</style>

<div class="container py-5">
    <h1 class="h3 fw-bold mb-1">Checkout</h1>
    <p class="text-muted mb-4">Cek pesanan kamu, lengkapi alamat pengiriman, lalu buat pesanan.</p>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">Ada data yang belum benar:</div>
            <ul class="mb-0 ps-3">
                <?php foreach (session()->getFlashdata('errors') as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <?php if ($adaMasalah): ?>
        <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>Ada item yang stoknya udah berubah. Sesuaikan jumlahnya di halaman keranjang dulu ya.</div>
            <a href="<?= site_url('keranjang') ?>" class="btn btn-sm btn-outline-dark">Ke Keranjang</a>
        </div>
    <?php endif ?>

    <form action="<?= site_url('checkout/proses') ?>" method="post">
        <?= csrf_field() ?>
        <div class="row g-4">

            <!-- KIRI: form -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3"><i class="bi bi-truck me-2"></i>Data Pengiriman</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Penerima <span class="text-danger">*</span></label>
                                <input type="text" name="receiver_name" class="form-control" required maxlength="100"
                                       value="<?= esc(old('receiver_name', $user['name'])) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. HP / WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" required maxlength="20"
                                       placeholder="08xxxxxxxxxx" value="<?= esc(old('phone')) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap Pengiriman <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control" rows="3" required
                                      placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota, kode pos"><?= esc(old('address')) ?></textarea>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Catatan untuk Penjual (opsional)</label>
                            <textarea name="notes" class="form-control" rows="2"
                                      placeholder="Contoh: dibungkus rapi ya"><?= esc(old('notes')) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3"><i class="bi bi-credit-card me-2"></i>Metode Pembayaran</h5>

                        <div class="mfm-pay-option">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="payment_method" id="pay-transfer" value="transfer"
                                    <?= old('payment_method', 'transfer') === 'transfer' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="pay-transfer">
                                    <strong>Transfer Bank (BCA)</strong>
                                    <small class="d-block text-muted">Simulasi — a/n Mad For Makeup, 1234567890</small>
                                </label>
                            </div>
                        </div>

                        <div class="mfm-pay-option">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="payment_method" id="pay-qris" value="qris"
                                    <?= old('payment_method') === 'qris' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="pay-qris">
                                    <strong>QRIS / e-Wallet</strong>
                                    <small class="d-block text-muted">Simulasi — scan QR untuk membayar</small>
                                </label>
                            </div>
                        </div>

                        <div class="mfm-pay-option mb-0">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="payment_method" id="pay-cod" value="cod"
                                    <?= old('payment_method') === 'cod' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="pay-cod">
                                    <strong>COD — Bayar di Tempat</strong>
                                    <small class="d-block text-muted">Bayar tunai saat paket tiba</small>
                                </label>
                            </div>
                        </div>

                        <div class="form-text mt-2">
                            <i class="bi bi-info-circle me-1"></i>Demo ini pakai pembayaran simulasi — nggak ada uang beneran yang diproses.
                        </div>
                    </div>
                </div>
            </div>

            <!-- KANAN: ringkasan -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm position-sticky" style="top: 90px;">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3">Ringkasan Pesanan</h5>

                        <?php foreach ($items as $it): ?>
                            <div class="d-flex justify-content-between align-items-start border-bottom pb-2 mb-2">
                                <div class="me-2">
                                    <div class="fw-semibold"><?= esc($it['nama_produk']) ?></div>
                                    <small class="text-muted">
                                        <?= esc(trim((string) $it['shade']) !== '' ? $it['shade'] : $it['sku']) ?>
                                        · <?= (int) $it['quantity'] ?> pcs
                                    </small>
                                </div>
                                <div class="text-nowrap fw-semibold">Rp <?= number_format($it['price'] * $it['quantity'], 0, ',', '.') ?></div>
                            </div>
                        <?php endforeach ?>

                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Jenis item</span>
                            <span><?= count($items) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold">Total Bayar</span>
                            <span class="fw-bold fs-5">Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 py-2 fw-semibold" <?= $adaMasalah ? 'disabled' : '' ?>>
                            <i class="bi bi-bag-check me-1"></i> Buat Pesanan
                        </button>
                        <a href="<?= site_url('keranjang') ?>" class="btn btn-link w-100 mt-2 text-decoration-none">Kembali ke Keranjang</a>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<?= $this->endSection() ?>