<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * TuPersuratanFilter
 *
 * Memastikan hanya user yang sudah login DAN berstatus TU Persuratan
 * (role_id = 4) yang dapat mengakses panel TU Persuratan.
 */
class TuPersuratanFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleId = (int) $session->get('user.role_id');

        // Admin (1) juga boleh mengakses untuk keperluan pengujian
        if ($roleId !== 4 && $roleId !== 1) {
            $session->setFlashdata('error', 'Akses ditolak. Halaman ini hanya untuk Persuratan.');
            
            $fallback = match($roleId) {
                3       => '/tu-unit/dashboard',
                default => '/penomoran',
            };
            return redirect()->to($fallback);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing
    }
}
