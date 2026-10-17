<?= $this->extend('layouts/storefront') ?>
<?= $this->section('content') ?>

<section class="mfm-hero">
    <div class="container">
        <h1>KATALOG PRODUK</h1>
        <p class="lead mb-0">Temukan produk yang kamu butuhkan. Cari, filter, temukan.</p>
    </div>
</section>

<div class="container py-4">
    <!-- Search + Filter -->
    <form method="get" action="<?= site_url('catalog') ?>" class="row g-2 align-items-center mb-4">
        <div class="col-12 col-md">
            <input type="text" name="q" value="<?= esc($q) ?>" class="form-control" placeholder="Cari nama produk...">
        </div>
        <div class="col-6 col-md-auto">
            <select name="kategori" class="form-select">
                <option value="">Semua kategori</option>
                <?php foreach ($kategoriList as $k): ?>
                    <option value="<?= esc($k['slug']) ?>" <?= $kategori === $k['slug'] ? 'selected' : '' ?>>
                        <?= esc($k['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-auto d-grid">
            <button type="submit" class="btn btn-dark"><i class="bi bi-search"></i> Cari</button>
        </div>
        <?php if ($q !== '' || $kategori !== ''): ?>
            <div class="col-12 col-md-auto">
                <a href="<?= site_url('catalog') ?>" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        <?php endif; ?>
    </form>

    <?php if ($produk === []): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-search" style="font-size:2rem"></i>
            <p class="mt-2 mb-0">Produk tidak ditemukan<?= $q !== '' ? ' untuk kata kunci "' . esc($q) . '"' : '' ?>.</p>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            <?php foreach ($produk as $p): ?>
                <?php
                    $pid  = $p['product_id'];
                    $img  = $gambarUtama[$pid] ?? null;
                    $imgOk = $img !== null && file_exists(FCPATH . 'uploads/' . $img);
                    $desc = strip_tags($p['description'] ?? '');
                ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <?php if ($imgOk): ?>
                            <img src="<?= base_url('uploads/' . $img) ?>" class="card-img-top" alt="<?= esc($p['name']) ?>" style="aspect-ratio:1;object-fit:cover;">
                        <?php else: ?>
                            <!-- placeholder: file gambar fisik baru ada di Tahap 4.4 (upload) -->
                            <div class="d-flex align-items-center justify-content-center bg-light" style="aspect-ratio:1;">
                                <i class="bi bi-image text-secondary fs-1"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-secondary-subtle text-secondary-emphasis align-self-start mb-2"><?= esc($p['kategori_nama'] ?? 'Umum') ?></span>
                            <h5 class="card-title mb-1"><?= esc($p['name']) ?></h5>
                            <p class="card-text small text-muted"><?= esc(mb_substr($desc, 0, 90)) ?><?= mb_strlen($desc) > 90 ? '...' : '' ?></p>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <?php if (isset($hargaMulai[$pid])): ?>
                                    <span class="fw-bold">Mulai Rp <?= number_format($hargaMulai[$pid], 0, ',', '.') ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">Harga menyusul</span>
                                <?php endif; ?>
                                <a href="<?= site_url('product/' . $p['slug']) ?>" class="btn btn-sm btn-outline-dark">Lihat</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>