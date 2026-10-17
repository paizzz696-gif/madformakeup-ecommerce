<?= $this->extend('layouts/storefront') ?>
<?= $this->section('content') ?>

<section class="mfm-hero">
    <div class="container">
        <h1>KOLEKSI KOSMETIK</h1>
        <p class="lead mb-3">Jelajahi semua produk, cari berdasarkan nama, atau saring per kategori.</p>
        <a href="<?= site_url('catalog') ?>" class="btn btn-dark px-4">
            Lihat Katalog <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
</section>

<?= $this->endSection() ?>