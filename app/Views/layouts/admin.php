<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Admin — Mad For Makeup') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/admin.css') ?>" rel="stylesheet">
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand">MAD<span>.</span> ADMIN</div>
        <nav class="admin-nav">
            <!-- [4.4-b] Dashboard cuma aktif di /admin doang.
                 Dulu url_is('admin*') → dobel aktif kalau ada menu /admin/... lain. -->
            <a href="<?= site_url('admin') ?>" class="<?= url_is('admin') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <!-- [4.4-b] Menu baru: daftar produk + kelola varian & stok -->
            <a href="<?= site_url('admin/products') ?>" class="<?= url_is('admin/products*') ? 'active' : '' ?>">
                <i class="bi bi-box-seam"></i> Produk
            </a>
            <!-- Tahap 5: Pesanan & Stok | Tahap 6: Pembayaran & Pengiriman | Tahap 7: Laporan -->
        </nav>
        <div class="admin-sidebar-footer">
            <a href="<?= site_url('/') ?>"><i class="bi bi-shop"></i> Lihat Toko</a>
            <?= form_open('logout', ['class' => 'mt-2']) ?>
                <button type="submit" class="admin-logout"><i class="bi bi-box-arrow-right"></i> Logout</button>
            <?= form_close() ?>
        </div>
    </aside>
    <div class="admin-main d-flex flex-column">
        <header class="admin-topbar">
            <span class="small text-muted">Panel Admin</span>
            <span class="small">Admin: <strong><?= esc(session()->get('name')) ?></strong></span>
        </header>
        <main class="admin-content p-4 flex-grow-1">
            <?php if ($f = session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= esc($f) ?></div>
            <?php endif; ?>
            <?php if ($f = session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= esc($f) ?></div>
            <?php endif; ?>
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>