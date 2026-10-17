<?= $this->extend('layouts/storefront') ?>
<?= $this->section('content') ?>

<?php
    // Gambar yang filenya beneran ada di uploads/ (placeholder icon kalau kosong)
    $gambarOk = [];
    foreach ($gambarList as $g) {
        if (file_exists(FCPATH . 'uploads/' . $g['image_path'])) {
            $gambarOk[] = $g;
        }
    }

    // Gambar utama: yang is_primary = 1, kalau ga ada pakai yang pertama
    $utama = null;
    foreach ($gambarOk as $g) {
        if ((int) $g['is_primary'] === 1) { $utama = $g; break; }
    }
    if ($utama === null && $gambarOk !== []) {
        $utama = $gambarOk[0];
    }

    $deskripsi = trim((string) ($produk['description'] ?? ''));
?>

<div class="container py-4">
    <a href="<?= site_url('catalog') ?>" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left"></i> Kembali ke katalog
    </a>

    <div class="row g-4 mt-1">
        <!-- Kolom gambar -->
        <div class="col-12 col-md-5">
            <?php if ($utama !== null): ?>
                <img id="mfm-img-utama" src="<?= base_url('uploads/' . $utama['image_path']) ?>"
                     class="img-fluid rounded border" alt="<?= esc($produk['name']) ?>"
                     style="aspect-ratio:1;object-fit:cover;width:100%;">
            <?php else: ?>
                <div class="d-flex align-items-center justify-content-center bg-light rounded border" style="aspect-ratio:1;">
                    <i class="bi bi-image text-secondary fs-1"></i>
                </div>
            <?php endif; ?>

            <?php if (count($gambarOk) > 1): ?>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <?php foreach ($gambarOk as $g): ?>
                        <img src="<?= base_url('uploads/' . $g['image_path']) ?>" width="72" height="72"
                             class="rounded border" style="object-fit:cover;cursor:pointer;"
                             alt="Miniatur <?= esc($produk['name']) ?>"
                             onclick="document.getElementById('mfm-img-utama').src=this.src;">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Kolom info -->
        <div class="col-12 col-md-7">
            <span class="badge bg-secondary-subtle text-secondary-emphasis mb-2"><?= esc($produk['kategori_nama'] ?? 'Umum') ?></span>
            <h1 class="h3 mb-2"><?= esc($produk['name']) ?></h1>

            <?php if ($hargaMulai !== null): ?>
                <p class="fs-4 fw-bold mb-1">Mulai Rp <?= number_format($hargaMulai, 0, ',', '.') ?></p>
            <?php else: ?>
                <p class="fs-5 text-muted mb-1">Harga menyusul</p>
            <?php endif; ?>

            <p class="text-muted small">
                <?php if ($varianList !== []): ?>
                    <i class="bi bi-layers"></i> <?= count($varianList) ?> varian aktif
                <?php else: ?>
                    Belum ada varian aktif
                <?php endif; ?>
            </p>

            <h6 class="text-uppercase text-muted small">Deskripsi</h6>
            <?php if ($deskripsi !== ''): ?>
                <p><?= nl2br(esc($deskripsi)) ?></p>
            <?php else: ?>
                <p class="text-muted"><em>Belum ada deskripsi.</em></p>
            <?php endif; ?>

            <h6 class="text-uppercase text-muted small mt-4">Varian</h6>
            <?php if ($varianList === []): ?>
                <p class="text-muted"><em>Varian produk ini belum tersedia.</em></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr class="text-muted small">
                                <th>Shade</th>
                                <th>Isi</th>
                                <th>SKU</th>
                                <th>Harga</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($varianList as $v): ?>
                                <?php $stok = (int) $v['stock_quantity']; ?>
                                <tr>
                                    <td><?= $v['shade'] !== null && $v['shade'] !== '' ? esc($v['shade']) : '&mdash;' ?></td>
                                    <td><?= $v['size'] !== null && $v['size'] !== '' ? esc($v['size']) : '&mdash;' ?></td>
                                    <td class="small text-muted"><?= esc($v['sku']) ?></td>
                                    <td>Rp <?= number_format((float) $v['price'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php if ($stok > 0): ?>
                                            <span class="badge bg-success-subtle text-success-emphasis">Tersedia (<?= $stok ?>)</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger-emphasis">Habis</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <p class="small text-muted mt-3 mb-0">
                <i class="bi bi-info-circle"></i>
                Keranjang &amp; pembelian dalam aplikasi ini berstatus simulasi — menyusul di tahap berikutnya.
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>