<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ---------- Publik ----------
 $routes->get('/', 'Home::index');
 $routes->get('catalog', 'Catalog::index');
 $routes->get('product/(:segment)', 'Catalog::detail/$1');

// ---------- Autentikasi ----------
 $routes->get('register', 'Auth::register');
 $routes->post('register', 'Auth::attemptRegister');
 $routes->get('login', 'Auth::login');
 $routes->post('login', 'Auth::attemptLogin');
 $routes->post('logout', 'Auth::logout');

// ---------- Pelanggan (wajib login) ----------
 $routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('account', 'Account::index');

    // [5.2] Tambah ke keranjang
    $routes->post('keranjang/tambah', 'Cart::tambah');

    // [5.3] Halaman + aksi keranjang
    $routes->get('keranjang', 'Cart::index');
    $routes->post('keranjang/(:num)/qty', 'Cart::ubahQty/$1');
    $routes->post('keranjang/(:num)/hapus', 'Cart::hapus/$1');

    // [5.4] Checkout & pembuatan pesanan
    $routes->get('checkout', 'Checkout::index');
    $routes->post('checkout/proses', 'Checkout::proses');
    $routes->get('checkout/sukses/(:segment)', 'Checkout::sukses/$1');
    $routes->post('checkout/(:segment)/bayar', 'Checkout::bayar/$1');
});

// ---------- Admin (wajib login + role admin) ----------
 $routes->group('admin', ['filter' => 'admin', 'namespace' => 'App\Controllers\Admin'], static function (RouteCollection $routes) {
    $routes->get('/', 'Dashboard::index');

    // [4.4-b] CRUD produk
    $routes->get('products', 'Products::index');
    $routes->get('products/baru', 'Products::baru');
    $routes->post('products', 'Products::simpan');
    $routes->get('products/(:num)/edit', 'Products::edit/$1');
    $routes->post('products/(:num)', 'Products::update/$1');
    $routes->post('products/(:num)/toggle', 'Products::toggle/$1');

    // [4.4-c] Kelola varian & stok per produk
    $routes->get('products/(:num)/varian', 'Products::varian/$1');
    $routes->post('products/(:num)/varian', 'Products::varianSimpan/$1');
    $routes->post('products/(:num)/varian/(:num)', 'Products::varianUpdate/$1/$2');
    $routes->post('products/(:num)/varian/(:num)/stok', 'Products::varianStok/$1/$2');

    // [4.4-d] Kelola gambar produk
    $routes->get('products/(:num)/gambar', 'Products::gambar/$1');
    $routes->post('products/(:num)/gambar', 'Products::gambarUpload/$1');
    $routes->post('products/(:num)/gambar/(:num)/utama', 'Products::gambarUtama/$1/$2');
    $routes->post('products/(:num)/gambar/(:num)/hapus', 'Products::gambarHapus/$1/$2');
});