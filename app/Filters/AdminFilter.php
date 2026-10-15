<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Lapisan 1: wajib login
        if (! session()->get('user_id')) {
            if (strtolower($request->getMethod()) === 'get') {
                session()->set('redirect_after_login', $request->getUri()->getPath());
            }
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Lapisan 2: wajib role admin — diperiksa di server (AC-13)
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak. Halaman ini khusus admin.');
        }
    }

    // CI 4.6: after() wajib menerima $response juga
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}