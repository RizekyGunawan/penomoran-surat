<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * TuUnitFilter
 *
 * Memastikan hanya user yang sudah login DAN berstatus TU Unit
 * (role_id = 3) yang dapat mengakses panel TU Unit.
 */
class TuUnitFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleId = (int) $session->get('user.role_id');

        // Admin (1) juga boleh mengakses untuk keperluan pengujian
        if ($roleId !== 3 && $roleId !== 1) {
            $session->setFlashdata('error', 'Akses ditolak. Halaman ini hanya untuk TU Unit Terkait.');
            
            $fallback = match($roleId) {
                4       => '/tu-persuratan/dashboard',
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
