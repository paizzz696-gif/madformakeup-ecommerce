<?php

declare(strict_types=1);

namespace Config;

use App\Filters\AdminFilter;
use App\Filters\AuthFilter;
use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'        => PerformanceMetrics::class,
        'performancemetrics' => PerformanceMetrics::class,
        'auth'          => AuthFilter::class,
        'admin'         => AdminFilter::class,
    ];

    public array $required = [
        'before' => [
            // 'forcehttps',
            // 'pagecache',
        ],
        'after' => [
            // 'performance',
            // 'toolbar',
        ],
    ];

    public array $globals = [
        'before' => [
            'csrf', // AKTIF GLOBAL: helper form menyisipkan token + hash/cookie dibuat di tiap GET
        ],
        'after' => [
            'toolbar',
        ],
    ];

    // Dikosongkan: csrf sudah global. Jangan isi 'csrf' di sini lagi (risiko eksekusi ganda).
    public array $methods = [];

    public array $filters = [];
}