<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * AdminFilter
 *
 * Memastikan hanya user yang sudah login DAN memiliki role admin
 * (role_id = 1) yang bisa mengakses route yang dilindungi filter ini.
 */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Cek apakah sudah login
        if (! $session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Cek apakah role_id = 1 (admin)
        if ((int) $session->get('user.role_id') !== 1) {
            $session->setFlashdata('error', 'Akses ditolak. Halaman ini hanya untuk Admin.');
            return redirect()->to('/penomoran');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing
    }
}
