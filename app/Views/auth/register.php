<?= $this->extend('layouts/storefront') ?>
<?= $this->section('content') ?>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-brand">Mad.</div>
        <h1 class="auth-title">Buat Akun</h1>
        <p class="text-muted small">Daftar untuk mulai berbelanja.</p>

        <?= view('partials/form_errors') ?>

        <?= form_open('register') ?>
            <div class="mb-3">
                <label class="form-label small">Nama Lengkap</label>
                <input type="text" name="name" value="<?= old('name') ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label small">Email</label>
                <input type="email" name="email" value="<?= old('email') ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label small">No. HP (opsional)</label>
                <input type="text" name="phone" value="<?= old('phone') ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label small">Password (min. 8 karakter)</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label small">Ulangi Password</label>
                <input type="password" name="confirm" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-mfm">Daftar</button>
        <?= form_close() ?>

        <p class="small text-center mt-3 mb-0">Sudah punya akun? <a href="<?= site_url('login') ?>">Masuk</a></p>
    </div>
</div>

<?= $this->endSection() ?>