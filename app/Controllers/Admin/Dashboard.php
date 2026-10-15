<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = db_connect();
        return view('admin/dashboard', [
            'total_users'    => $db->table('users')->countAll(),
            'total_products' => $db->table('products')->countAll(),
            'total_variants' => $db->table('product_variants')->countAll(),
            'total_orders'   => $db->table('orders')->countAll(),
        ]);
    }
}