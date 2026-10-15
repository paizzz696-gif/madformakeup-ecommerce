<?= $this->extend('layouts/storefront') ?>
<?= $this->section('content') ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h5 mb-4">Akun Saya</h1>
                    <p class="mb-1"><strong>Nama:</strong> <?= esc($user['name']) ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?= esc($user['email']) ?></p>
                    <p class="mb-1"><strong>No. HP:</strong> <?= esc($user['phone'] ?? '-') ?></p>
                    <p class="mb-1"><strong>Role:</strong> <?= esc($user['role']) ?></p>
                    <p class="mb-0"><strong>Terdaftar:</strong> <?= esc($user['created_at']) ?></p>
                </div>
            </div>
            <p class="text-muted small mt-3">Keranjang, checkout, dan riwayat pesanan menyusul di Tahap 5–6.</p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>