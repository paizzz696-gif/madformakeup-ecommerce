<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<?php
 $flashErrors = session()->getFlashdata('errors');
if (! is_array($flashErrors)) {
    $flashErrors = [];
}
 $pid = (int) $produk['product_id'];
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 mb-1 fw-bold">Gambar Produk</h1>
        <p class="text-muted mb-0">
            <?= esc($produk['name']) ?>
            &middot; <a href="<?= site_url('admin/products/' . $pid . '/varian') ?>">Kelola varian &amp; stok</a>
        </p>
    </div>
    <a href="<?= site_url('admin/products') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali ke daftar
    </a>
</div>

<?php if ($flashErrors !== []): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">
            <?php foreach ($flashErrors as $e): ?>
                <li><?= esc($e) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">

    <!-- KOLOM KIRI: FORM UPLOAD -->
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-cloud-arrow-up"></i> Upload gambar baru
            </div>
            <div class="card-body">
                <?= form_open_multipart('admin/products/' . $pid . '/gambar') ?>

                    <div class="mb-3">
                        <label for="gambar" class="form-label">File gambar <span class="text-danger">*</span></label>
                        <input type="file" name="gambar" id="gambar"
                               accept="image/jpeg,image/png,image/webp"
                               class="form-control <?= isset($flashErrors['gambar']) ? 'is-invalid' : '' ?>" required>
                        <div class="form-text">JPG / PNG / WEBP, maksimal 2 MB.</div>
                        <?php if (isset($flashErrors['gambar'])): ?>
                            <div class="invalid-feedback d-block"><?= esc($flashErrors['gambar']) ?></div>
                        <?php endif; ?>
                    </div>

                    <img id="previewGambar" src="" alt="" class="img-fluid rounded border d-none mb-3" style="max-height: 220px;">

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-upload"></i> Upload
                    </button>

                <?= form_close() ?>

                <div class="alert alert-info small mt-3 mb-0">
                    <i class="bi bi-info-circle"></i>
                    Gambar <strong>pertama</strong> yang diupload otomatis jadi gambar utama
                    (yang muncul di kartu katalog &amp; halaman detail).
                </div>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: GALERI GAMBAR TERPASANG -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-images"></i> Gambar terpasang</span>
                <span class="badge bg-secondary"><?= count($gambar) ?></span>
            </div>
            <div class="card-body">

                <?php if ($gambar === []): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-image" style="font-size: 3rem;"></i>
                        <p class="mb-0 mt-2">Belum ada gambar. Upload gambar pertama lewat form di sebelah kiri.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($gambar as $g): ?>
                            <?php
                                $adaFile = file_exists(FCPATH . 'uploads/' . $g['image_path']);
                                $utama   = (int) $g['is_primary'] === 1;
                                $gid     = (int) $g['image_id'];
                            ?>
                            <div class="col-sm-6 col-xl-4">
                                <div class="card h-100 <?= $utama ? 'border-primary' : '' ?>">
                                    <div class="position-relative">
                                        <?php if ($adaFile): ?>
                                            <img src="<?= base_url('uploads/' . $g['image_path']) ?>"
                                                 class="card-img-top" alt="<?= esc($produk['name']) ?>"
                                                 style="height: 180px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center bg-light" style="height: 180px;">
                                                <i class="bi bi-image text-muted" style="font-size: 2.5rem;"></i>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($utama): ?>
                                            <span class="badge bg-primary position-absolute top-0 start-0 m-2">
                                                <i class="bi bi-star-fill"></i> UTAMA
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body py-2 px-3">
                                        <div class="small text-muted text-truncate" title="<?= esc($g['image_path']) ?>">
                                            <i class="bi bi-file-earmark-image"></i> <?= esc($g['image_path']) ?>
                                        </div>
                                        <?php if (! $adaFile): ?>
                                            <div class="small text-danger">
                                                <i class="bi bi-exclamation-triangle"></i> File fisik hilang
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-white d-flex gap-2">
                                        <?php if ($utama): ?>
                                            <button type="button" class="btn btn-sm btn-primary flex-fill" disabled>
                                                <i class="bi bi-star-fill"></i> Utama
                                            </button>
                                        <?php else: ?>
                                            <?= form_open('admin/products/' . $pid . '/gambar/' . $gid . '/utama', ['class' => 'flex-fill']) ?>
                                                <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                                    <i class="bi bi-star"></i> Jadikan utama
                                                </button>
                                            <?= form_close() ?>
                                        <?php endif; ?>

                                        <?= form_open('admin/products/' . $pid . '/gambar/' . $gid . '/hapus', [
                                                'onsubmit' => "return confirm('Hapus gambar ini? Filenya juga ikut terhapus dari server.')",
                                            ]) ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?= form_close() ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
// Preview gambar sebelum upload (efek visual aja, gak ngaruh ke server)
document.getElementById('gambar')?.addEventListener('change', function () {
    const file    = this.files[0];
    const preview = document.getElementById('previewGambar');
    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
});
</script>

<?= $this->endSection() ?>