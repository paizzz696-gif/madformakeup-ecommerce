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
});