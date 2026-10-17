<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
// Tanggal & salam bahasa Indonesia — pakai array manual biar gak
// tergantung locale OS (Windows Laragon kadang gak punya id_ID).
 $namaHari   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
 $namaBulan  = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
 $tanggalIni = $namaHari[(int) date('w')] . ', ' . (int) date('j') . ' ' . $namaBulan[(int) date('n')] . ' ' . date('Y');

 $jam   = (int) date('G');
 $salam = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 19 ? 'Selamat sore' : 'Selamat malam'));
?>

<!-- HEADER SAMBUTAN -->
<div class="dash-hero mb-4">
    <div>
        <div class="dash-hero-salam"><?= $salam ?> 👋</div>
        <h1 class="h3 fw-bold mb-1">Halo, <?= esc(session()->get('name') ?? 'Admin') ?></h1>
        <p class="mb-0 dash-hero-sub"><?= $tanggalIni ?> &middot; Ringkasan toko Mad For Makeup hari ini</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= site_url('/') ?>" target="_blank" class="btn btn-outline-light">
            <i class="bi bi-shop"></i> Lihat Toko
        </a>
        <a href="<?= site_url('admin/products/baru') ?>" class="btn btn-light text-dark fw-semibold">
            <i class="bi bi-plus-lg"></i> Tambah Produk
        </a>
    </div>
</div>

<!-- KARTU STATISTIK -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="statx statx-blue">
            <div class="statx-icon"><i class="bi bi-people"></i></div>
            <div>
                <div class="statx-label">Total Pengguna</div>
                <div class="statx-value"><?= (int) $total_users ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="statx statx-pink">
            <div class="statx-icon"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="statx-label">Total Produk</div>
                <div class="statx-value"><?= (int) $total_products ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="statx statx-orange">
            <div class="statx-icon"><i class="bi bi-tags"></i></div>
            <div>
                <div class="statx-label">Total Varian</div>
                <div class="statx-value"><?= (int) $total_variants ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="statx statx-green">
            <div class="statx-icon"><i class="bi bi-bag-check"></i></div>
            <div>
                <div class="statx-label">Total Pesanan</div>
                <div class="statx-value"><?= (int) $total_orders ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- PANEL: PRODUK TERBARU -->
    <div class="col-lg-7">
        <div class="dash-panel h-100">
            <div class="dash-panel-head">
                <span><i class="bi bi-clock-history text-muted"></i> Produk Terbaru</span>
                <a href="<?= site_url('admin/products') ?>" class="small text-decoration-none">Lihat semua &rarr;</a>
            </div>
            <div class="dash-panel-body p-0">
                <?php if ($produk_terbaru === []): ?>
                    <div class="text-center text-muted py-4">Belum ada produk.</div>
                <?php else: ?>
                    <table class="table table-hover align-middle mb-0">
                        <tbody>
                        <?php foreach ($produk_terbaru as $p): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold small"><?= esc($p['name']) ?></div>
                                    <div class="text-muted" style="font-size:.75rem">/product/<?= esc($p['slug']) ?></div>
                                </td>
                                <td class="text-muted small"><?= date('d-m-Y', strtotime($p['created_at'])) ?></td>
                                <td>
                                    <?php if ((int) $p['is_active'] === 1): ?>
                                        <span class="badge bg-success-subtle text-success-emphasis">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis">Arsip</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= site_url('admin/products/' . $p['product_id'] . '/edit') ?>" class="btn btn-sm btn-outline-dark" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- PANEL: STOK KRITIS -->
    <div class="col-lg-5">
        <div class="dash-panel h-100">
            <div class="dash-panel-head">
                <span><i class="bi bi-exclamation-triangle text-danger"></i> Stok Kritis</span>
                <span class="badge text-bg-danger"><?= (int) $total_stok_kritis ?> varian</span>
            </div>
            <div class="dash-panel-body">
                <?php if ($stok_kritis === []): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-shield-check" style="font-size:2rem;"></i>
                        <p class="mt-2 mb-0">Aman! Semua varian aktif punya stok.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($stok_kritis as $s): ?>
                        <?php
                            $parts = [];
                            if (($s['shade'] ?? '') !== '') { $parts[] = 'Shade ' . $s['shade']; }
                            if (($s['size']  ?? '') !== '') { $parts[] = 'Size '  . $s['size']; }
                            $label = $parts === [] ? '—' : implode(' · ', $parts);
                        ?>
                        <a href="<?= site_url('admin/products/' . $s['product_id'] . '/varian') ?>" class="dash-kritis">
                            <div>
                                <div class="fw-semibold small text-dark"><?= esc($s['product_name']) ?></div>
                                <div class="text-muted" style="font-size:.75rem"><?= esc($label) ?> &middot; <?= esc($s['sku']) ?></div>
                            </div>
                            <span class="badge text-bg-danger">Stok <?= (int) $s['stock_quantity'] ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- PANEL: PRODUK AKTIF TANPA GAMBAR -->
<div class="dash-panel mb-4">
    <div class="dash-panel-head">
        <span><i class="bi bi-image text-muted"></i> Produk Aktif Belum Punya Gambar</span>
        <span class="badge text-bg-secondary"><?= count($tanpa_gambar) ?></span>
    </div>
    <div class="dash-panel-body">
        <?php if ($tanpa_gambar === []): ?>
            <div class="text-center text-muted py-3">
                <i class="bi bi-stars" style="font-size:1.6rem;"></i>
                <p class="mt-2 mb-0">Keren! Semua produk aktif sudah punya gambar.</p>
            </div>
        <?php else: ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($tanpa_gambar as $t): ?>
                    <a href="<?= site_url('admin/products/' . $t['product_id'] . '/gambar') ?>" class="dash-chip">
                        <i class="bi bi-plus-circle"></i> <?= esc($t['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>