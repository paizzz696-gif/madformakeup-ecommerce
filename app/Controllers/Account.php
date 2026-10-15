<?php

namespace App\Controllers;

use App\Models\UserModel;

class Account extends BaseController
{
    public function index()
    {
        $user = (new UserModel())->find(session()->get('user_id'));
        return view('account/index', ['user' => $user]);
    }
}