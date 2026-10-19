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

    // [5.2] Label varian: shade dulu, terus size, terus "Varian #id"
    $labelVarian = static function (array $v): string {
        $label = trim((string) ($v['shade'] ?? ''));
        if ($label === '') { $label = trim((string) ($v['size'] ?? '')); }
        if ($label === '') { $label = 'Varian #' . $v['variant_id']; }
        return $label;
    };

    // Varian pertama yang stoknya masih ada -> dipilih otomatis
    $pertamaTersedia = null;
    foreach ($varianList as $v) {
        if ((int) $v['stock_quantity'] > 0) { $pertamaTersedia = $v; break; }
    }
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

            <h6 class="text-uppercase text-muted small mt-4">Pilih Shade / Varian</h6>
            <?php if ($varianList === []): ?>
                <p class="text-muted"><em>Varian produk ini belum tersedia.</em></p>
            <?php else: ?>
                <form action="<?= site_url('keranjang/tambah') ?>" method="post">
                    <?= csrf_field() ?>

                    <!-- Chip shade: klik = pilih varian. Yang habis otomatis nonaktif + coret -->
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($varianList as $v): ?>
                            <?php $habis = (int) $v['stock_quantity'] <= 0; ?>
                            <label class="mfm-shade-option">
                                <input type="radio" name="variant_id" value="<?= (int) $v['variant_id'] ?>"
                                       data-label="<?= esc($labelVarian($v)) ?>"
                                       data-harga="<?= (float) $v['price'] ?>"
                                       data-stok="<?= (int) $v['stock_quantity'] ?>"
                                       <?= $habis ? 'disabled' : '' ?>
                                       <?= $pertamaTersedia !== null && (int) $pertamaTersedia['variant_id'] === (int) $v['variant_id'] ? 'checked' : '' ?>>
                                <span class="mfm-shade-chip">
                                    <span class="mfm-shade-nama"><?= esc($labelVarian($v)) ?></span>
                                    <span class="mfm-shade-sub">
                                        <?= trim((string) ($v['size'] ?? '')) !== '' ? esc($v['size']) . ' · ' : '' ?>Rp <?= number_format((float) $v['price'], 0, ',', '.') ?>
                                    </span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <!-- Info varian terpilih: di-update JS tiap ganti shade -->
                    <p class="small mt-2 mb-3" id="mfm-varian-info">
                        <?php if ($pertamaTersedia !== null): ?>
                            <strong><?= esc($labelVarian($pertamaTersedia)) ?></strong>
                            &mdash; Rp <?= number_format((float) $pertamaTersedia['price'], 0, ',', '.') ?>
                            &mdash; <span class="text-success">Tersedia (<?= (int) $pertamaTersedia['stock_quantity'] ?>)</span>
                        <?php else: ?>
                            Pilih varian di atas.
                        <?php endif; ?>
                    </p>

                    <?php if ($pertamaTersedia !== null): ?>
                        <div class="row g-2 align-items-center">
                            <div class="col-5 col-sm-4 col-md-3">
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" id="mfm-qty-kurang" aria-label="Kurangi">&minus;</button>
                                    <input type="number" name="quantity" id="mfm-qty" value="1" min="1" required class="form-control text-center">
                                    <button type="button" class="btn btn-outline-secondary" id="mfm-qty-tambah" aria-label="Tambah">+</button>
                                </div>
                            </div>
                            <div class="col-7 col-sm-8 col-md-9">
                                <button type="submit" class="btn btn-dark w-100" id="mfm-btn-keranjang">
                                    <i class="bi bi-bag-plus"></i> Tambah ke Keranjang
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger py-2 small mb-0">
                            <i class="bi bi-x-circle"></i> Semua varian sedang habis. Cek lagi nanti ya.
                        </div>
                    <?php endif; ?>
                </form>
            <?php endif; ?>

            <h6 class="text-uppercase text-muted small mt-4">Deskripsi</h6>
            <?php if ($deskripsi !== ''): ?>
                <p><?= nl2br(esc($deskripsi)) ?></p>
            <?php else: ?>
                <p class="text-muted"><em>Belum ada deskripsi.</em></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .mfm-shade-option { position: relative; }
    .mfm-shade-option input { position: absolute; opacity: 0; pointer-events: none; }
    .mfm-shade-chip {
        display: inline-flex; flex-direction: column; gap: 2px;
        border: 1px solid #dee2e6; border-radius: .75rem;
        padding: .45rem .85rem; cursor: pointer;
        transition: border-color .15s, background-color .15s, color .15s, box-shadow .15s;
    }
    .mfm-shade-option:hover .mfm-shade-chip { border-color: #adb5bd; }
    .mfm-shade-option input:checked + .mfm-shade-chip {
        border-color: #212529; background: #212529; color: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .18);
    }
    .mfm-shade-option input:disabled + .mfm-shade-chip {
        opacity: .45; cursor: not-allowed; text-decoration: line-through;
    }
    .mfm-shade-nama { font-weight: 600; font-size: .9rem; line-height: 1.2; }
    .mfm-shade-sub  { font-size: .78rem; opacity: .75; line-height: 1.2; }
</style>

<script>
(function () {
    'use strict';
    var radios = Array.prototype.slice.call(document.querySelectorAll('input[name="variant_id"]'));
    var info   = document.getElementById('mfm-varian-info');
    var qty    = document.getElementById('mfm-qty');
    var tombol = document.getElementById('mfm-btn-keranjang');

    if (radios.length === 0) { return; }

    var rupiah = function (angka) {
        return 'Rp ' + Number(angka).toLocaleString('id-ID');
    };

    // Efek klik shade: chip aktif jadi gelap + info nama/harga/stok berubah tanpa reload
    var terapkan = function (radio) {
        var stok = parseInt(radio.dataset.stok, 10);
        radios.forEach(function (r) {
            r.closest('.mfm-shade-option').classList.toggle('mfm-aktif', r.checked);
        });
        info.innerHTML =
            '<strong>' + radio.dataset.label + '</strong>' +
            ' &mdash; ' + rupiah(radio.dataset.harga) +
            (stok > 0
                ? ' &mdash; <span class="text-success">Tersedia (' + stok + ')</span>'
                : ' &mdash; <span class="text-danger">Habis</span>');
        if (tombol !== null) { tombol.disabled = stok <= 0; }
        if (qty !== null && stok > 0) {
            qty.max = stok;
            var n = parseInt(qty.value, 10);
            if (n > stok) { qty.value = stok; }
            if (n < 1 || isNaN(n)) { qty.value = 1; }
        }
    };

    radios.forEach(function (r) {
        r.addEventListener('change', function () { terapkan(r); });
    });

    var terpilih = document.querySelector('input[name="variant_id"]:checked');
    if (terpilih !== null) { terapkan(terpilih); }

    // Stepper qty + pengaman min/max
    var kurang = document.getElementById('mfm-qty-kurang');
    var tambah = document.getElementById('mfm-qty-tambah');
    if (tambah !== null) {
        tambah.addEventListener('click', function () {
            qty.stepUp();
            qty.dispatchEvent(new Event('change'));
        });
    }
    if (kurang !== null) {
        kurang.addEventListener('click', function () {
            qty.stepDown();
            qty.dispatchEvent(new Event('change'));
        });
    }
    if (qty !== null) {
        qty.addEventListener('change', function () {
            var maks  = qty.max ? parseInt(qty.max, 10) : 99;
            var nilai = parseInt(qty.value, 10);
            if (isNaN(nilai) || nilai < 1) { nilai = 1; }
            if (nilai > maks) { nilai = maks; }
            qty.value = nilai;
        });
    }
})();
</script>

<?= $this->endSection() ?>