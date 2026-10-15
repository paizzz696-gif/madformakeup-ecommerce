<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function register()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/');
        }
        return view('auth/register');
    }

    public function attemptRegister()
    {
        $rules = [
            'name'     => 'required|min_length[3]|max_length[100]',
            'email'    => 'required|valid_email|max_length[150]|is_unique[users.email]',
            'phone'    => 'permit_empty|regex_match[/^[0-9+\-\s]{6,20}$/]',
            'password' => 'required|min_length[8]',
            'confirm'  => 'required|matches[password]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new UserModel())->insert([
            'name'          => trim($this->request->getPost('name')),
            'email'         => strtolower(trim($this->request->getPost('email'))),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'phone'         => $this->request->getPost('phone') ?: null,
            'role'          => 'customer', // role TIDAK PERNAH diambil dari input klien
            'is_active'     => 1,
        ]);

        return redirect()->to('/login')->with('success', 'Registrasi berhasil. Silakan login.');
    }

    public function login()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/');
        }
        return view('auth/login');
    }

    public function attemptLogin()
    {
        $rules = ['email' => 'required|valid_email', 'password' => 'required'];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = strtolower(trim($this->request->getPost('email')));
        $user  = (new UserModel())->where('email', $email)->first();

        // Pesan generik: jangan bocorkan apakah email ada atau password salah (TC-02)
        if ($user === null || ! password_verify($this->request->getPost('password'), $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Email atau password salah.');
        }

        if ((int) $user['is_active'] !== 1) {
            return redirect()->back()->withInput()->with('error', 'Akun Anda dinonaktifkan. Hubungi admin.');
        }

        session()->regenerate(); // cegah session fixation
        session()->set([
            'user_id' => $user['user_id'],
            'name'    => $user['name'],
            'email'   => $user['email'],
            'role'    => $user['role'],
        ]);

        $redirect = session()->get('redirect_after_login');
        if ($redirect) {
            session()->remove('redirect_after_login');
            return redirect()->to($redirect)->with('success', 'Selamat datang kembali, ' . $user['name'] . '!');
        }

        return $user['role'] === 'admin'
            ? redirect()->to('/admin')->with('success', 'Login admin berhasil.')
            : redirect()->to('/')->with('success', 'Selamat datang, ' . $user['name'] . '!');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/')->with('success', 'Anda telah logout.');
    }
}