<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Produk</h1>
    <a href="<?= site_url('admin/products/baru') ?>" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Tambah Produk</a>
</div>

<form method="get" action="<?= site_url('admin/products') ?>" class="row g-2 align-items-center mb-3">
    <div class="col-12 col-md-4">
        <input type="text" name="q" value="<?= esc($q) ?>" class="form-control" placeholder="Cari nama produk...">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i> Cari</button>
    </div>
    <?php if ($q !== ''): ?>
        <div class="col-auto">
            <a href="<?= site_url('admin/products') ?>" class="btn btn-outline-secondary">Reset</a>
        </div>
    <?php endif; ?>
</form>

<?php if ($produk === []): ?>
    <div class="text-center text-muted py-5">
        <i class="bi bi-box" style="font-size:2rem"></i>
        <p class="mt-2 mb-0">Belum ada produk<?= $q !== '' ? ' yang cocok dengan pencarian.' : '.' ?></p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle bg-white">
            <thead>
                <tr class="text-muted small">
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Harga mulai</th>
                    <th>Varian</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produk as $p): ?>
                    <?php
                        $pid   = (int) $p['product_id'];
                        $img   = $gambarUtama[$pid] ?? null;
                        $imgOk = $img !== null && file_exists(FCPATH . 'uploads/' . $img);
                        $info  = $varianInfo[$pid] ?? ['jumlah' => 0, 'stok' => 0];
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($imgOk): ?>
                                    <img src="<?= base_url('uploads/' . $img) ?>" width="42" height="42" class="rounded border" style="object-fit:cover;" alt="">
                                <?php else: ?>
                                    <span class="d-inline-flex align-items-center justify-content-center bg-light rounded border" style="width:42px;height:42px;">
                                        <i class="bi bi-image text-secondary"></i>
                                    </span>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-semibold"><?= esc($p['name']) ?></div>
                                    <div class="small text-muted">/product/<?= esc($p['slug']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= ($p['kategori_nama'] ?? '') !== '' ? esc($p['kategori_nama']) : '&mdash;' ?></td>
                        <td>
                            <?php if (isset($hargaMulai[$pid])): ?>
                                Rp <?= number_format($hargaMulai[$pid], 0, ',', '.') ?>
                            <?php else: ?>
                                <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $info['jumlah'] ?></td>
                        <td>
                            <?php if ($info['stok'] <= 0): ?>
                                <span class="badge bg-danger-subtle text-danger-emphasis">0</span>
                            <?php else: ?>
                                <?= $info['stok'] ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ((int) $p['is_active'] === 1): ?>
                                <span class="badge bg-success-subtle text-success-emphasis">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis">Arsip</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="<?= site_url('admin/products/' . $pid . '/edit') ?>" class="btn btn-sm btn-outline-dark" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <!-- [4.4-c] Kelola varian & stok -->
                                <a href="<?= site_url('admin/products/' . $pid . '/varian') ?>" class="btn btn-sm btn-outline-dark" title="Kelola varian & stok">
                                    <i class="bi bi-tags"></i>
                                </a>
                                <?= form_open('admin/products/' . $pid . '/toggle', ['class' => 'd-inline m-0']) ?>
                                    <button type="submit" class="btn btn-sm btn-outline-<?= (int) $p['is_active'] === 1 ? 'secondary' : 'success' ?>">
                                        <?= (int) $p['is_active'] === 1 ? 'Arsipkan' : 'Aktifkan' ?>
                                    </button>
                                <?= form_close() ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-3 d-flex justify-content-center">
        <?= $pager->links() ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>