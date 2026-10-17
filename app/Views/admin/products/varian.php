<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<a href="<?= site_url('admin/products') ?>" class="small text-decoration-none">&larr; Kembali ke daftar produk</a>

<h1 class="h4 mt-2 mb-1">Kelola Varian</h1>
<p class="text-muted small mb-3">
    Produk: <strong><?= esc($produk['name']) ?></strong> &middot; /product/<?= esc($produk['slug']) ?>
</p>

<?php $flashErrors = session()->getFlashdata('errors') ?? []; ?>
<?php if ($flashErrors !== []): ?>
    <div class="alert alert-danger py-2">
        <ul class="mb-0 small">
            <?php foreach ($flashErrors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="alert alert-warning small py-2 px-3">
    <i class="bi bi-info-circle"></i> Stok <strong>nggak bisa diedit langsung</strong> — tiap perubahan dicatat
    lewat form &ldquo;Catat&rdquo; di tiap varian, biar semua kecatat di riwayat stok (stock_movements).
</div>

<?php if ($varianList === []): ?>
    <div class="text-center text-muted py-5 bg-white rounded border mb-4">
        <i class="bi bi-tags" style="font-size:2rem"></i>
        <p class="mt-2 mb-0">Belum ada varian. Tambahkan varian pertama lewat form di bawah.</p>
    </div>
<?php else: ?>
    <?php foreach ($varianList as $v): ?>
        <?php $vid = (int) $v['variant_id']; ?>
        <div class="card mb-3 <?= (int) $v['is_active'] === 1 ? '' : 'opacity-75' ?>">
            <div class="card-body">

                <?= form_open('admin/products/' . $produk['product_id'] . '/varian/' . $vid) ?>
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">SKU</label>
                        <input type="text" name="sku" value="<?= esc($v['sku']) ?>" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Shade</label>
                        <input type="text" name="shade" value="<?= esc($v['shade'] ?? '') ?>" class="form-control form-control-sm">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Isi / Size</label>
                        <input type="text" name="size" value="<?= esc($v['size'] ?? '') ?>" class="form-control form-control-sm">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Harga (Rp)</label>
                        <input type="number" name="price" value="<?= esc($v['price']) ?>" min="0" step="any" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="is_active" class="form-select form-select-sm">
                            <option value="1" <?= (int) $v['is_active'] === 1 ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= (int) $v['is_active'] === 0 ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2 d-grid">
                        <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-check-lg"></i> Simpan</button>
                    </div>
                </div>
                <?= form_close() ?>

                <div class="d-flex align-items-center gap-2 mt-3">
                    <span class="small">Stok sekarang: <strong class="fs-6"><?= (int) $v['stock_quantity'] ?></strong></span>
                    <?php if ((int) $v['stock_quantity'] <= 0): ?>
                        <span class="badge bg-danger-subtle text-danger-emphasis">Habis</span>
                    <?php endif; ?>
                </div>

                <?= form_open('admin/products/' . $produk['product_id'] . '/varian/' . $vid . '/stok') ?>
                <div class="row g-2 align-items-end mt-1">
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Arah</label>
                        <select name="arah" class="form-select form-select-sm">
                            <option value="in">Masuk (+)</option>
                            <option value="out">Keluar (&minus;)</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Jumlah</label>
                        <input type="number" name="qty" min="1" value="1" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-12 col-md">
                        <label class="form-label small text-muted mb-1">Catatan</label>
                        <input type="text" name="notes" class="form-control form-control-sm" maxlength="255" placeholder="mis. restock dari supplier / stok rusak / dipakai foto produk">
                    </div>
                    <div class="col-6 col-md-2 d-grid">
                        <button type="submit" class="btn btn-sm btn-outline-dark"><i class="bi bi-journal-plus"></i> Catat</button>
                    </div>
                </div>
                <?= form_close() ?>

                <?php $riwayat = $riwayatMap[$vid] ?? []; ?>
                <?php if ($riwayat !== []): ?>
                    <details class="mt-3">
                        <summary class="small text-muted" style="cursor:pointer;">
                            Riwayat stok (<?= count($riwayat) ?> terakhir)
                        </summary>
                        <ul class="small text-muted mt-2 mb-0 ps-3">
                            <?php foreach ($riwayat as $r): ?>
                                <?php $q = (int) $r['quantity']; ?>
                                <li>
                                    <?= esc($r['created_at'] ?? '') ?> &mdash;
                                    <span class="badge bg-<?= $q >= 0 ? 'success' : 'danger' ?>-subtle text-<?= $q >= 0 ? 'success' : 'danger' ?>-emphasis">
                                        <?= $q >= 0 ? '+' : '' ?><?= $q ?>
                                    </span>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis"><?= esc($r['movement_type'] ?? '') ?></span>
                                    <?= esc($r['notes'] ?? '') !== '' ? esc($r['notes']) : '<em>tanpa catatan</em>' ?>
                                    &mdash; oleh <?= esc($r['dicatat_oleh'] ?? 'sistem') ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </details>
                <?php endif; ?>

            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="card border-dark mb-4">
    <div class="card-header bg-dark text-white"><i class="bi bi-plus-lg"></i> Tambah varian baru</div>
    <div class="card-body">
        <?= form_open('admin/products/' . $produk['product_id'] . '/varian') ?>
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">SKU <span class="text-danger">*</span></label>
                <input type="text" name="sku" value="<?= old('sku') ?>" class="form-control form-control-sm" required>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Shade</label>
                <input type="text" name="shade" value="<?= old('shade') ?>" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Isi / Size</label>
                <input type="text" name="size" value="<?= old('size') ?>" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Harga (Rp) <span class="text-danger">*</span></label>
                <input type="number" name="price" value="<?= old('price') ?>" min="0" step="any" class="form-control form-control-sm" required>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Stok awal</label>
                <input type="number" name="stok_awal" value="<?= old('stok_awal', '0') ?>" min="0" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="is_active" class="form-select form-select-sm">
                    <option value="1" selected>Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-dark mt-3"><i class="bi bi-plus-lg"></i> Tambah Varian</button>
        <?= form_close() ?>
    </div>
</div>

<?= $this->endSection() ?>