<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Mad For Makeup — Demo Akademik') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/storefront.css') ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<div class="mfm-announce">MAD FOR MAKEUP — PROYEK AKADEMIK E-COMMERCE (DEMO LOKAL)</div>

<header class="mfm-header">
    <div class="container-xxl mfm-header-inner">
        <nav class="mfm-nav">
            <a href="<?= site_url('/') ?>">Beranda</a>
            <!-- Tahap 4: Shop All & kategori ditambahkan di sini -->
        </nav>
        <a href="<?= site_url('/') ?>" class="mfm-wordmark">MAD FOR MAKEUP</a>
        <div class="mfm-actions">
            <?php if (session()->get('user_id')): ?>
                <a href="<?= site_url('account') ?>" class="mfm-action" title="Akun saya">
                    <i class="bi bi-person"></i>
                    <span class="d-none d-lg-inline ms-1 small"><?= esc(session()->get('name')) ?></span>
                </a>
                <?= form_open('logout', ['class' => 'd-inline m-0']) ?>
                    <button type="submit" class="mfm-action btn bg-transparent border-0 p-0" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                <?= form_close() ?>
            <?php else: ?>
                <a href="<?= site_url('login') ?>" class="mfm-action" title="Masuk"><i class="bi bi-person"></i></a>
                <a href="<?= site_url('register') ?>" class="mfm-action small d-none d-md-inline">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="flex-grow-1">
    <?php if ($flash = session()->getFlashdata('success')): ?>
        <div class="container-xxl mt-3"><div class="alert alert-success mfm-alert"><?= esc($flash) ?></div></div>
    <?php endif; ?>
    <?php if ($flash = session()->getFlashdata('error')): ?>
        <div class="container-xxl mt-3"><div class="alert alert-danger mfm-alert"><?= esc($flash) ?></div></div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
</main>

<footer class="mfm-footer mt-5">
    <div class="container-xxl py-5">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <h6 class="mfm-footer-title">Produk</h6>
                <ul class="list-unstyled mfm-footer-links">
                    <li><a href="<?= site_url('/') ?>">Beranda</a></li>
                    <!-- Tahap 4: daftar kategori -->
                </ul>
            </div>
            <div class="col-6 col-md-3">
                <h6 class="mfm-footer-title">Akun</h6>
                <ul class="list-unstyled mfm-footer-links">
                    <li><a href="<?= site_url('login') ?>">Masuk</a></li>
                    <li><a href="<?= site_url('register') ?>">Daftar</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-3">
                <h6 class="mfm-footer-title">Tentang Proyek</h6>
                <ul class="list-unstyled mfm-footer-links">
                    <li>Tugas Besar Basis Data</li>
                    <li>CodeIgniter 4 + MySQL</li>
                </ul>
            </div>
            <div class="col-6 col-md-3">
                <h6 class="mfm-footer-title">Catatan</h6>
                <p class="small mb-0">Pembayaran dan pengiriman dalam aplikasi ini berstatus simulasi.</p>
            </div>
        </div>
        <hr class="mfm-footer-hr">
        <p class="small mb-0">© <?= date('Y') ?> Mad For Makeup — implementasi akademik, bukan situs resmi Mad For Makeup.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>