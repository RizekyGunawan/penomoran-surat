<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;
use App\Libraries\SSOService;

class Auth extends BaseController
{
    public function login()
    {
        // Jika sudah login, langsung arahkan ke halaman sesuai role
        if (session()->get('isLoggedIn')) {
            return $this->redirectByRole();
        }

        // Cek apakah SSO sudah terkonfigurasi di .env
        // (SSO_URL atau SSO_IP_LOCAL harus diisi agar SSO bisa berjalan)
        $ssoBase = getenv('SSO_IP_LOCAL') ?: getenv('SSO_URL') ?: '';

        if (empty($ssoBase)) {
            // SSO belum dikonfigurasi (biasanya di environment lokal/development)
            // → Tampilkan form login manual seperti sebelumnya
            log_message('info', 'Auth::login — SSO tidak dikonfigurasi, gunakan form login manual.');
            return view('auth/login');
        }

        // SSO sudah dikonfigurasi → arahkan ke halaman login SSO
        $sso = new SSOService();
        $loginUrl = $sso->getLoginUrl();
        log_message('info', 'Auth::login — redirect ke SSO: ' . $loginUrl);
        return redirect()->to($loginUrl);
    }

    /**
     * Halaman login lokal — bypass SSO.
     * Method ini SELALU menampilkan form login LDAP,
     * tidak peduli apakah SSO dikonfigurasi atau tidak.
     *
     * Digunakan oleh admin/developer yang tidak memiliki akses SSO
     * tapi perlu masuk ke sistem (misal saat update/maintenance).
     * Diakses melalui tombol tersembunyi di halaman login utama.
     */
    public function loginLokal()
    {
        // Jika sudah login, langsung arahkan ke halaman sesuai role
        if (session()->get('isLoggedIn')) {
            return $this->redirectByRole();
        }

        // Langsung tampilkan form login lokal tanpa cek SSO
        return view('auth/login_lokal');
    }


    public function attempt(): RedirectResponse
    {
        $session = session();
        $username = $this->request->getPost('username_ldap');

        if (!$username) {
            $session->setFlashdata('error', 'Username wajib diisi');
            return redirect()->back()->withInput();
        }

        $dbMaster = \Config\Database::connect('kemenkopmk');
        $masterUser = $dbMaster->table('users')
            ->select('users.*, pegawai.nama, pegawai.gelar_depan, pegawai.gelar_belakang')
            ->join('pegawai', 'pegawai.id = users.pegawai_id', 'left')
            ->where('users.username_ldap', $username)
            ->get()->getRowArray();

        // JIKA TIDAK ADA DI MASTER DB, TOLAK LOGIN
        if (!$masterUser && $username !== 'superadmin') {
            $session->setFlashdata('error', 'Pengguna tidak ditemukan');
            return redirect()->back()->withInput();
        }

        $userModel = new UserModel();
        $user = $userModel->where('username_ldap', $username)->first();

        // CEK HAK AKSES ROLE
        // Sesuai arahan, sistem hanya bisa diakses oleh Admin (1), TU Unit (3), dan TU Persuratan (4).
        // Pegawai biasa (2) atau akun yang tidak terdaftar akan otomatis ditolak.
        if (!$user || !in_array((int)$user['role_id'], [1, 3, 4], true)) {
            $namaPegawai = $masterUser['nama'] ?? $username;
            return redirect()->to('/tidak-terdaftar?nama=' . urlencode($namaPegawai));
        }

        if (isset($user['is_active']) && (int) $user['is_active'] !== 1) {
            $session->setFlashdata('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi Admin untuk informasi lebih lanjut.');
            return redirect()->back()->withInput();
        }

        $pegawaiId = $user['pegawai_id'] ?? ($masterUser['pegawai_id'] ?? null);
        $unitKerjaId = $user['unit_kerja_id'] ?? ($masterUser['unit_kerja_id'] ?? null);

        $session->set([
            'isLoggedIn' => true,
            'user' => [
                'id'            => $user['id'] ?? null,
                'name'          => !empty($masterUser['nama']) 
                                   ? trim(($masterUser['gelar_depan'] ? $masterUser['gelar_depan'].' ' : '') . $masterUser['nama'] . ($masterUser['gelar_belakang'] ? ', '.$masterUser['gelar_belakang'] : '')) 
                                   : $user['username_ldap'],
                'username_ldap' => $user['username_ldap'],
                'role_id'       => $user['role_id'] ?? null,
                'pegawai_id'    => $pegawaiId,
                'unit_kerja_id' => $unitKerjaId,
            ],
        ]);

        return $this->redirectByRole();
    }

    /**
     * Redirect setelah login berdasarkan role.
     */
    private function redirectByRole(): RedirectResponse
    {
        $roleId = (int) session('user.role_id');

        return match ($roleId) {
            1 => redirect()->to('/admin/users'),
            3 => redirect()->to('/tu-unit/dashboard'),
            4 => redirect()->to('/tu-persuratan/dashboard'),
            default => redirect()->to('/penomoran'),
        };
    }

    public function logout(): RedirectResponse
    {
        // Destroy local session and redirect to SSO logout
        $sso = new SSOService();
        session()->destroy();

        if (empty($sso->getSsoBase())) {
            return redirect()->to('/login');
        }

        return redirect()->to($sso->getLogoutUrl());
    }

    /**
     * SSO callback handler.
     * Expects a 'token' GET parameter (SSO should redirect back with token).
     */
    public function ssoCallback()
    {
        $token = $this->request->getGet('token') ?? $this->request->getGet('access_token');
        if (empty($token)) {
            // If token missing, redirect to login (SSO will re-initiate)
            return redirect()->to('/login');
        }

        $sso = new SSOService();
        $userData = $sso->getUserData($token);
        if (! $userData || empty($userData['username'])) {
            session()->setFlashdata('error', 'SSO: gagal mendapatkan data pengguna');
            return redirect()->to('/login');
        }

        $username = $userData['username'];
        $userModel = new UserModel();
        $user = $userModel->where('username_ldap', $username)->first();

        // CEK HAK AKSES ROLE
        // Sesuai arahan, sistem hanya bisa diakses oleh Admin (1), TU Unit (3), dan TU Persuratan (4).
        // Pegawai biasa (2) atau akun yang tidak terdaftar akan otomatis ditolak.
        if (!$user || !in_array((int)$user['role_id'], [1, 3, 4], true)) {
            $namaPegawai = $userData['name'] ?? $userData['nama'] ?? $username;
            return redirect()->to('/tidak-terdaftar?nama=' . urlencode($namaPegawai));
        }

        if (isset($user['is_active']) && (int) $user['is_active'] !== 1) {
            session()->setFlashdata('error', 'Akun tidak aktif');
            return redirect()->to('/login');
        }

        // Fetch full name from master DB
        $dbMaster = \Config\Database::connect('kemenkopmk');
        $masterUser = $dbMaster->table('users')
            ->select('pegawai.nama, pegawai.gelar_depan, pegawai.gelar_belakang')
            ->join('pegawai', 'pegawai.id = users.pegawai_id', 'left')
            ->where('users.username_ldap', $username)
            ->get()->getRowArray();
            
        $fullName = $username;
        if ($masterUser && !empty($masterUser['nama'])) {
            $fullName = trim(($masterUser['gelar_depan'] ? $masterUser['gelar_depan'].' ' : '') . $masterUser['nama'] . ($masterUser['gelar_belakang'] ? ', '.$masterUser['gelar_belakang'] : ''));
        } elseif (!empty($userData['name']) || !empty($userData['nama'])) {
            $fullName = $userData['name'] ?? $userData['nama'];
        }

        // Create local session
        session()->set([
            'isLoggedIn' => true,
            'user'       => [
                'id'             => $user['id'] ?? null,
                'name'           => $fullName,
                'username_ldap'  => $user['username_ldap'],
                'role_id'        => $user['role_id'] ?? null,
                'pegawai_id'     => $user['pegawai_id'] ?? null,
                'unit_kerja_id'  => $user['unit_kerja_id'] ?? null,
            ],
        ]);

        return $this->redirectByRole();
    }

    /**
     * Menampilkan halaman informasi untuk pegawai yang belum terdaftar.
     * Halaman ini bisa diakses tanpa login, sebagai respons dari proses login
     * (baik via form maupun SSO) yang gagal karena akun belum ada di tabel lokal.
     */
    public function tidakTerdaftar()
    {
        // Jika sudah login, tidak perlu ke halaman ini
        if (session()->get('isLoggedIn')) {
            return $this->redirectByRole();
        }

        // Ambil nama pegawai dari query string (dikirim oleh attempt() atau ssoCallback())
        $namaPegawai = $this->request->getGet('nama') ?? '';

        return view('auth/tidak_terdaftar', [
            'namaPegawai' => esc($namaPegawai),
        ]);
    }
}
