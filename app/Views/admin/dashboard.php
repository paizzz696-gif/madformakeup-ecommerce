<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h1 class="h4 mb-1">Dashboard</h1>
<p class="text-muted small">Menu katalog, pesanan, dan laporan menyusul di Tahap 4–7.</p>

<div class="row g-3">
    <div class="col-6 col-lg-3"><div class="stat-card"><div class="small text-muted">Total Pengguna</div><div class="stat-value"><?= (int) $total_users ?></div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card"><div class="small text-muted">Total Produk</div><div class="stat-value"><?= (int) $total_products ?></div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card"><div class="small text-muted">Total Varian</div><div class="stat-value"><?= (int) $total_variants ?></div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card"><div class="small text-muted">Total Pesanan</div><div class="stat-value"><?= (int) $total_orders ?></div></div></div>
</div>

<?= $this->endSection() ?>