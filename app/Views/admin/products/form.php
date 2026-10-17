<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
    $isEdit = ($mode ?? 'baru') === 'edit' && $produk !== null;
    $errors = session('errors') ?? [];
?>

<a href="<?= site_url('admin/products') ?>" class="text-decoration-none text-muted small">
    <i class="bi bi-arrow-left"></i> Kembali ke daftar produk
</a>

<h1 class="h4 mt-2"><?= $isEdit ? 'Edit Produk' : 'Tambah Produk' ?></h1>

<?php if ($errors !== []): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
                <li><?= esc($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width:720px">
    <div class="card-body">
        <?= form_open($isEdit ? 'admin/products/' . $produk['product_id'] : 'admin/products') ?>

        <div class="mb-3">
            <label class="form-label">Kategori <span class="text-danger">*</span></label>
            <select name="category_id" class="form-select" required>
                <option value="">— pilih kategori —</option>
                <?php foreach ($kategoriList as $k): ?>
                    <option value="<?= $k['category_id'] ?>" <?= (int) old('category_id', $produk['category_id'] ?? '') === (int) $k['category_id'] ? 'selected' : '' ?>>
                        <?= esc($k['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Nama produk <span class="text-danger">*</span></label>
            <input type="text" name="name" value="<?= esc(old('name', $produk['name'] ?? '')) ?>" class="form-control" maxlength="150" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control" rows="5"><?= esc(old('description', $produk['description'] ?? '')) ?></textarea>
        </div>

        <div class="mb-4">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-select" style="max-width:220px">
                <option value="1" <?= (int) old('is_active', $produk['is_active'] ?? 1) === 1 ? 'selected' : '' ?>>Aktif (tampil di toko)</option>
                <option value="0" <?= (int) old('is_active', $produk['is_active'] ?? 1) === 0 ? 'selected' : '' ?>>Arsip (sembunyi dari toko)</option>
            </select>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg"></i> Simpan</button>
            <a href="<?= site_url('admin/products') ?>" class="btn btn-outline-secondary">Batal</a>
        </div>

        <?= form_close() ?>
    </div>
</div>

<?php if ($isEdit): ?>
    <div class="small text-muted mt-3">
        Slug: <code><?= esc($produk['slug']) ?></code> (diulang otomatis dari nama, dijamin unik) &middot;
        Link toko: <a href="<?= site_url('product/' . $produk['slug']) ?>" target="_blank">/product/<?= esc($produk['slug']) ?></a>
        <br>Varian &amp; stok dikelola di halaman ini — bagian baru menyusul di 4.4-c. Upload gambar di 4.4-d.
    </div>
<?php endif; ?>

<?= $this->endSection() ?>