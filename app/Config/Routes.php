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
    $routes->get('products/(:num)/varian', 'Products::varian/$1');
 $routes->post('products/(:num)/varian', 'Products::varianSimpan/$1');
 $routes->post('products/(:num)/varian/(:num)', 'Products::varianUpdate/$1/$2');
 $routes->post('products/(:num)/varian/(:num)/stok', 'Products::varianStok/$1/$2');
});