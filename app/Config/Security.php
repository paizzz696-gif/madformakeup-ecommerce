<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Security extends BaseConfig
{
    /**
     * 'cookie' dipertahankan (default): hash CSRF disimpan di cookie browser.
     */
    public string $csrfProtection = 'cookie';

    public bool $tokenRandomize = false;

    /**
     * Nama token & cookie DIGANTI dari default ('csrf_test_name') supaya
     * sisa cookie/token dari masa debugging otomatis tidak berlaku lagi
     * (slate bersih).
     */
    public string $tokenName = 'csrf_mfm';

    public string $headerName = 'X-CSRF-TOKEN';

    public string $cookieName = 'csrf_mfm_cookie';

    public int $expires = 7200;

    /**
     * DIUBAH dari true → false.
     * regenerate=true memutar token pada setiap POST sukses sehingga form
     * yang sudah terbuka menjadi basi dan memicu 403 saat disubmit.
     * Dengan false, token tetap WAJIB pada setiap POST (proteksi CSRF utuh),
     * hanya tidak dirotasi per-request.
     */
    public bool $regenerate = false;

    /**
     * DIUBAH menjadi true (juga di development) agar kegagalan CSRF
     * me-redirect kembali ke form dengan pesan error, bukan menampilkan
     * halaman exception penuh.
     */
    public bool $redirect = true;
}