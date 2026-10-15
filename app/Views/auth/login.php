<?= $this->extend('layouts/storefront') ?>
<?= $this->section('content') ?>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-brand">Mad.</div>
        <h1 class="auth-title">Sign in</h1>
        <p class="text-muted small">Masuk untuk berbelanja.</p>

        <?= view('partials/form_errors') ?>
        <?php if ($e = session()->getFlashdata('error')): ?>
            <div class="alert alert-danger py-2 small"><?= esc($e) ?></div>
        <?php endif; ?>

        <?= form_open('login') ?>
            <div class="mb-3">
                <label class="form-label small">Email</label>
                <input type="email" name="email" value="<?= old('email') ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label small">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-mfm">Masuk</button>
        <?= form_close() ?>

        <div class="demo-creds small mt-4">
            <strong>Akun demo:</strong><br>
            admin@madformakeup.local / admin123<br>
            customer@madformakeup.local / customer123
        </div>

        <p class="small text-center mt-3 mb-0">Belum punya akun? <a href="<?= site_url('register') ?>">Daftar</a></p>
    </div>
</div>

<?= $this->endSection() ?>