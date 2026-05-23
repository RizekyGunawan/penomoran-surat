<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * AdminTuFilter
 *
 * Memastikan hanya user yang sudah login DAN memiliki role admin (1)
 * ATAU TU Persuratan (4) yang bisa mengakses route yang dilindungi filter ini.
 */
class AdminTuFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Cek apakah sudah login
        if (! $session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Cek apakah role_id = 1 (admin) atau 4 (TU Persuratan)
        $roleId = (int) $session->get('user.role_id');
        if (! in_array($roleId, [1, 4], true)) {
            $session->setFlashdata('error', 'Akses ditolak. Halaman ini hanya untuk Admin atau TU Persuratan.');
            return redirect()->to('/penomoran');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing
    }
}
