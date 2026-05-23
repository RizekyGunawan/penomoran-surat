<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\UnitKerjaModel;

/**
 * UserController (Admin)
 *
 * Menangani CRUD user oleh Admin:
 * - Daftar user dengan pencarian
 * - Tambah user baru
 * - Edit data user (username_ldap, role_id, is_active)
 * - Toggle status aktif/nonaktif
 *
 * Semua route dilindungi AdminFilter (role_id = 1).
 */
class UserController extends BaseController
{
    protected UserModel $userModel;
    protected UnitKerjaModel $unitKerjaModel;

    public function __construct()
    {
        $this->userModel       = new UserModel();
        $this->unitKerjaModel  = new UnitKerjaModel();
    }

    // ─────────────────────────────────────────────
    // Validasi Eligibilitas Role TU Persuratan
    // ─────────────────────────────────────────────

    /**
     * Cek apakah seorang pegawai berhak mendapat role TU Persuratan.
     *
     * Kriteria yang harus dipenuhi (salah satu):
     * 1. Kepala Biro (Eselon II.a) di Biro Umum dan Keuangan
     * 2. Kepala Bagian (Eselon III.a) di sub-unit Biro Umum dan Keuangan
     * 3. Jabatan Arsiparis (jabatan fungsional) di lingkungan Biro Umum dan Keuangan
     *
     * ID unit kerja yang diizinkan:
     * - 3  = Biro Umum dan Keuangan (induk)
     * - 43 = Bagian Protokol dan Tata Usaha Pimpinan
     * - 46 = Bagian Rumah Tangga
     *
     * @param  int $pegawaiId  ID pegawai dari kemenkopmk_db
     * @return bool  True jika memenuhi syarat, false jika tidak
     */
    private function cekEligibilitasTuPersuratan(int $pegawaiId): bool
    {
        // ID unit kerja yang berada di lingkungan Biro Umum dan Keuangan
        $unitKerjaDiizinkan = [3, 43, 46];

        $dbMaster = \Config\Database::connect('kemenkopmk');

        $pegawai = $dbMaster->query("
            SELECT p.id, j.eselon, j.nama_jabatan, uk.id AS unit_kerja_id
            FROM pegawai p
            LEFT JOIN jabatan j    ON j.id  = p.jabatan_id
            LEFT JOIN unit_kerja uk ON uk.id = p.unit_kerja_id
            WHERE p.id = ?
            LIMIT 1
        ", [$pegawaiId])->getRowArray();

        if (empty($pegawai)) {
            return false;
        }

        // Syarat 1: Unit kerja harus di lingkungan Biro Umum dan Keuangan
        $unitKerjaValid = in_array((int) $pegawai['unit_kerja_id'], $unitKerjaDiizinkan);

        if (!$unitKerjaValid) {
            return false;
        }

        // Syarat 2: Jabatan harus Kepala Biro, Kepala Bagian, atau Arsiparis
        $eselon        = $pegawai['eselon'] ?? '';
        $namaJabatan   = strtolower($pegawai['nama_jabatan'] ?? '');

        $adalahKepala    = in_array($eselon, ['II.a', 'III.a']);
        $adalahArsiparis = str_contains($namaJabatan, 'arsiparis');

        return $adalahKepala || $adalahArsiparis;
    }

    // ─────────────────────────────────────────────
    // Daftar User
    // ─────────────────────────────────────────────

    /**
     * GET /admin/users
     * Tampilkan daftar semua user dengan pagination dan opsional search.
     */
    public function index(): string
    {
        $search  = $this->request->getGet('search');
        $tab     = $this->request->getGet('tab') ?? 'pegawai'; // default tab: pegawai
        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = 20;

        // Validasi tab agar hanya menerima nilai yang diizinkan
        if (!in_array($tab, ['pegawai', 'tu-unit', 'tu-persuratan', 'admin'])) {
            $tab = 'pegawai';
        }

        $result = $this->userModel->getPaginated($page, $perPage, $search, $tab);

        $data = [
            'title'         => 'Admin - Manage User',
            'users'         => $result['data'],
            'search'        => $search ?? '',
            'activeTab'     => $tab,
            'pagination'    => $result,
            'unitKerjaList' => $this->unitKerjaModel->getGroupedActive(),
        ];

        return view('admin/users/index', $data);
    }

    // ─────────────────────────────────────────────
    // Tambah User
    // ─────────────────────────────────────────────

    /**
     * GET /admin/users/create
     * Tampilkan form tambah user.
     */
    public function create(): string
    {
        $data = [
            'title'         => 'Admin - Tambah User',
            'user'          => null,
            'isEdit'        => false,
            'validation'    => \Config\Services::validation(),
            'unitKerjaList' => $this->unitKerjaModel->getGroupedActive(),
        ];

        return view('admin/users/form', $data);
    }

    /**
     * POST /admin/users/store
     * Simpan user baru ke database (dipanggil dari modal di halaman index).
     */
    public function store()
    {
        $rules = [
            // Hapus is_unique agar admin bisa "menugaskan" role baru ke user yang sudah ada
            'username_ldap' => "required|max_length[100]",
            'role_id'       => 'required|in_list[1,2,3,4]',
            'is_active'     => 'permit_empty|in_list[0,1]',
        ];

        $messages = [];

        if (! $this->validate($rules, $messages)) {
            return redirect()->to('/admin/users')
                             ->with('modal_errors', $this->validator->getErrors())
                             ->with('modal_old', $this->request->getPost());
        }

        $roleId       = (int) $this->request->getPost('role_id');
        $unitKerjaId  = $this->request->getPost('unit_kerja_id');
        $usernameLdap = $this->request->getPost('username_ldap');

        // Ambil data pegawai dari kemenkopmk_db
        $dbMaster   = \Config\Database::connect('kemenkopmk');
        $masterUser = $dbMaster->table('users')->where('username_ldap', $usernameLdap)->get()->getRowArray();
        
        if (empty($masterUser)) {
            return redirect()->to('/admin/users')
                             ->with('modal_errors', [
                                 'username_ldap' => "Gagal menambahkan user. Username '{$usernameLdap}' tidak ditemukan."
                             ])
                             ->with('modal_old', $this->request->getPost());
        }

        $pegawaiId  = $masterUser['pegawai_id'] ?? null;

        // Validasi khusus: jika role yang dipilih adalah TU Persuratan (role_id = 4),
        // pastikan pegawai memenuhi kriteria jabatan dan unit kerja
        if ($roleId === 4 && $pegawaiId) {
            if (!$this->cekEligibilitasTuPersuratan((int) $pegawaiId)) {
                return redirect()->to('/admin/users')
                                 ->with('modal_errors', [
                                     'role_id' => 'Pegawai ini tidak memenuhi syarat sebagai TU Persuratan. '
                                                . 'Hanya Kepala Biro, Kepala Bagian, atau Arsiparis '
                                                . 'di lingkungan Biro Umum dan Keuangan yang diizinkan.',
                                 ])
                                 ->with('modal_old', $this->request->getPost());
            }
        }

        // Cek apakah user sudah terdaftar di database lokal
        $existingUser = $this->userModel->where('username_ldap', $usernameLdap)->first();

        $dataToSave = [
            'username_ldap' => $usernameLdap,
            'pegawai_id'    => $pegawaiId,
            'role_id'       => $roleId,
            // Simpan unit_kerja_id untuk TU Unit (3) dan TU Persuratan (4)
            'unit_kerja_id' => (in_array($roleId, [3, 4]) && !empty($unitKerjaId)) ? (int) $unitKerjaId : null,
            'is_active'     => $this->request->getPost('is_active') === '1' ? 1 : 0,
        ];

        if ($existingUser) {
            // Jika sudah ada (misal pernah login & otomatis terdaftar sbg Pegawai Biasa), kita UPDATE
            $this->userModel->skipValidation(true)->update($existingUser['id'], $dataToSave);
            $msg = 'User sudah terdaftar sebelumnya, hak akses berhasil diperbarui menjadi ' . \App\Models\UserModel::getRoleLabel($roleId) . '.';
        } else {
            // Jika belum ada, kita INSERT baru
            $this->userModel->skipValidation(true)->insert($dataToSave);
            $msg = 'User berhasil ditambahkan.';
        }

        return redirect()->to('/admin/users')->with('success', $msg);
    }

    // ─────────────────────────────────────────────
    // Edit User
    // ─────────────────────────────────────────────

    /**
     * GET /admin/users/edit/:id
     * Tampilkan form edit user.
     */
    public function edit(int $id)
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            return redirect()->to('/admin/users')
                             ->with('error', 'User tidak ditemukan.');
        }

        if ($user['username_ldap'] === 'superadmin') {
            return redirect()->to('/admin/users')
                             ->with('error', 'Akun Super Admin tidak dapat diedit.');
        }

        // Jika unit_kerja_id di tabel lokal kosong (misal hasil sinkronisasi SSO/Pengajuan Akses)
        // Coba deteksi unit kerja induk dari database master
        if (empty($user['unit_kerja_id'])) {
            $dbMaster = \Config\Database::connect('kemenkopmk');
            $masterUser = $dbMaster->table('users')
                ->select('unit_kerja_id')
                ->where('username_ldap', $user['username_ldap'])
                ->get()
                ->getRowArray();

            if (!empty($masterUser['unit_kerja_id'])) {
                // Cari ID parent group (induk) menggunakan UnitKerjaModel
                $group = $this->unitKerjaModel->getHierarchyGroup((int) $masterUser['unit_kerja_id']);
                if ($group) {
                    $user['unit_kerja_id'] = $group['id'];
                }
            }
        }

        $data = [
            'title'         => 'Admin - Edit User',
            'user'          => $user,
            'isEdit'        => true,
            'validation'    => \Config\Services::validation(),
            'unitKerjaList' => $this->unitKerjaModel->getGroupedActive(),
        ];

        return view('admin/users/form', $data);
    }

    /**
     * POST /admin/users/update/:id
     * Simpan perubahan data user.
     */
    public function update(int $id)
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            return redirect()->to('/admin/users')
                             ->with('error', 'User tidak ditemukan.');
        }

        if ($user['username_ldap'] === 'superadmin') {
            return redirect()->to('/admin/users')
                             ->with('error', 'Akun Super Admin tidak dapat diedit.');
        }

        $rules = [
            'username_ldap' => "required|max_length[100]|is_unique[users.username_ldap,id,{$id}]",
            'role_id'       => 'required|in_list[1,2,3,4]',
            'is_active'     => 'permit_empty|in_list[0,1]',
        ];

        $messages = [
            'username_ldap' => [
                'is_unique' => 'Username sudah digunakan oleh user lain.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()
                             ->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        // Cegah admin menonaktifkan dirinya sendiri
        $currentUserId = (int) session('user.id');
        $isActive      = $this->request->getPost('is_active') === '1' ? 1 : 0;

        if ($id === $currentUserId && $isActive === 0) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $roleId       = (int) $this->request->getPost('role_id');
        $unitKerjaId  = $this->request->getPost('unit_kerja_id');
        $usernameLdap = $this->request->getPost('username_ldap');

        // Ambil data pegawai dari kemenkopmk_db
        $dbMaster   = \Config\Database::connect('kemenkopmk');
        $masterUser = $dbMaster->table('users')->where('username_ldap', $usernameLdap)->get()->getRowArray();
        
        if (empty($masterUser)) {
            return redirect()->back()
                             ->withInput()
                             ->with('errors', [
                                 'username_ldap' => "Gagal memperbarui user. Username '{$usernameLdap}' tidak ditemukan."
                             ]);
        }

        $pegawaiId  = $masterUser['pegawai_id'] ?? null;

        // Validasi khusus: jika role yang dipilih adalah TU Persuratan (role_id = 4),
        // pastikan pegawai memenuhi kriteria jabatan dan unit kerja
        if ($roleId === 4 && $pegawaiId) {
            if (!$this->cekEligibilitasTuPersuratan((int) $pegawaiId)) {
                return redirect()->back()
                                 ->withInput()
                                 ->with('errors', [
                                     'role_id' => 'Pegawai ini tidak memenuhi syarat sebagai TU Persuratan. '
                                                . 'Hanya Kepala Biro, Kepala Bagian, atau Arsiparis '
                                                . 'di lingkungan Biro Umum dan Keuangan yang diizinkan.',
                                 ]);
            }
        }

        $this->userModel->skipValidation(true)->update($id, [
            'username_ldap' => $usernameLdap,
            'pegawai_id'    => $pegawaiId,
            'role_id'       => $roleId,
            // Simpan unit_kerja_id untuk TU Unit (3) dan TU Persuratan (4)
            'unit_kerja_id' => (in_array($roleId, [3, 4]) && !empty($unitKerjaId)) ? (int) $unitKerjaId : null,
            'is_active'     => $isActive,
        ]);

        return redirect()->to('/admin/users')
                         ->with('success', 'Data user berhasil diperbarui.');
    }

    // ─────────────────────────────────────────────
    // AJAX Search Pegawai (Select2)
    // ─────────────────────────────────────────────

    /**
     * GET /admin/users/searchPegawai
     * API untuk Select2 mencari pegawai dari kemenkopmk_db
     */
    public function searchPegawai()
    {
        $term = $this->request->getGet('q') ?? '';
        
        $dbMaster = \Config\Database::connect('kemenkopmk');
        $builder = $dbMaster->table('users');
        $builder->select('users.username_ldap, users.pegawai_id, pegawai.nama, pegawai.gelar_depan, pegawai.gelar_belakang, unit_kerja.nama_unit_kerja');
        $builder->join('pegawai', 'pegawai.id = users.pegawai_id', 'left');
        $builder->join('unit_kerja', 'unit_kerja.id = pegawai.unit_kerja_id', 'left');
        
        if (!empty($term)) {
            $builder->groupStart();
            $builder->like('pegawai.nama', $term);
            $builder->orLike('users.username_ldap', $term);
            $builder->groupEnd();
        }
        
        $builder->where('users.is_active', 1);
        $builder->limit(20);
        
        $results = $builder->get()->getResultArray();
        
        $data = [];
        foreach ($results as $row) {
            $namaLengkap = $row['nama'] ?? $row['username_ldap'];
            if (!empty($row['gelar_depan'])) {
                $namaLengkap = $row['gelar_depan'] . ' ' . $namaLengkap;
            }
            if (!empty($row['gelar_belakang'])) {
                $namaLengkap = $namaLengkap . ', ' . $row['gelar_belakang'];
            }
            
            $text = $namaLengkap . ' (' . $row['username_ldap'] . ')';
            if (!empty($row['nama_unit_kerja'])) {
                $text .= ' - ' . $row['nama_unit_kerja'];
            }
            
            $data[] = [
                'id'            => $row['username_ldap'],
                'text'          => $text,
                'pegawai_id'    => $row['pegawai_id'],
                'username_ldap' => $row['username_ldap']
            ];
        }
        
        return $this->response->setJSON(['results' => $data]);
    }

    // ─────────────────────────────────────────────
    // Toggle Status
    // ─────────────────────────────────────────────

    /**
     * POST /admin/users/toggle/:id
     * Toggle status aktif ↔ nonaktif user.
     */
    public function toggleStatus(int $id)
    {
        // Cegah admin me-toggle dirinya sendiri
        if ($id === (int) session('user.id')) {
            return redirect()->to('/admin/users')
                             ->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user = $this->userModel->find($id);

        if (! $user) {
            return redirect()->to('/admin/users')
                             ->with('error', 'User tidak ditemukan.');
        }

        if ($user['username_ldap'] === 'superadmin') {
            return redirect()->to('/admin/users')
                             ->with('error', 'Status aktif akun Super Admin tidak dapat diubah.');
        }

        $this->userModel->toggleStatus($id);

        $statusLabel = ((int) $user['is_active'] === 1) ? 'dinonaktifkan' : 'diaktifkan';

        return redirect()->to('/admin/users')
                         ->with('success', "User \"{$user['username_ldap']}\" berhasil {$statusLabel}.");
    }
}
