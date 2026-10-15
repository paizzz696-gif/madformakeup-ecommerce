<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('user_id')) {
            if (strtolower($request->getMethod()) === 'get') {
                session()->set('redirect_after_login', $request->getUri()->getPath());
            }
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
    }

    // CI 4.6: after() wajib menerima $response juga
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}