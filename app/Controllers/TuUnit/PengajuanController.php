<?php

namespace App\Controllers\TuUnit;

use App\Controllers\BaseController;
use App\Models\PengajuanAksesModel;
use App\Models\UserModel;
use App\Models\UnitKerjaModel;

class PengajuanController extends BaseController
{
    protected $pengajuanModel;
    protected $userModel;
    protected $unitKerjaModel;

    public function __construct()
    {
        $this->pengajuanModel = new PengajuanAksesModel();
        $this->userModel = new UserModel();
        $this->unitKerjaModel = new UnitKerjaModel();
    }

    public function index()
    {
        $userId      = session('user.id');
        $unitKerjaId = session('user.unit_kerja_id');

        // Mengambil daftar pengajuan oleh Kabag saat ini
        $pengajuanList = $this->pengajuanModel->getDaftarPengajuanByPengusul($userId);

        // Fallback: jika unit_kerja_id di session kosong (data lama sebelum perbaikan),
        // coba sinkronisasi dari database master (kemenkopmk_db)
        if (empty($unitKerjaId)) {
            $dbMaster   = \Config\Database::connect('kemenkopmk');
            $masterUser = $dbMaster->table('users')
                ->select('unit_kerja_id')
                ->where('username_ldap', session('user.username_ldap'))
                ->get()
                ->getRowArray();

            $unitKerjaIdMaster = $masterUser['unit_kerja_id'] ?? null;

            // Jika ditemukan di master, update tabel lokal dan session agar sinkron
            if (!empty($unitKerjaIdMaster)) {
                $this->userModel->update($userId, ['unit_kerja_id' => $unitKerjaIdMaster]);
                session()->set('user.unit_kerja_id', $unitKerjaIdMaster);
                $unitKerjaId = $unitKerjaIdMaster;
            }
        }

        if (empty($unitKerjaId)) {
            session()->setFlashdata('error', 'Unit Kerja Anda belum terdefinisi di sistem. Silakan hubungi Administrator untuk memperbarui data Unit Kerja Anda agar dapat mengusulkan staf.');
            return view('tu_unit/pengajuan_akses/index', [
                'title' => 'Kelola Akses Tim (TU Unit)',
                'pengajuanList' => $pengajuanList,
                'kandidatPegawai' => []
            ]);
        }

        // Mengambil child_ids dari hierarki unit kerja
        $hierarchy = $this->unitKerjaModel->getHierarchyGroup((int) $unitKerjaId);
        
        $allowedUnitKerjaIds = [$unitKerjaId];
        if ($hierarchy && !empty($hierarchy['child_ids'])) {
            $allowedUnitKerjaIds = array_merge($allowedUnitKerjaIds, $hierarchy['child_ids']);
        }

        // Cari kandidat pegawai:
        // 1. Berada dalam cakupan Unit Kerja Kabag
        // 2. Belum memiliki role Admin (1), TU Unit (3), atau TU Persuratan (4) di lokal
        // 3. Tidak sedang memiliki usulan pending
        $db = \Config\Database::connect();
        $builder = $db->table('kemenkopmk_db.users ku');
        $builder->select('ku.username_ldap, p.nama, p.gelar_depan, p.gelar_belakang, uk.nama_unit_kerja')
            ->join('kemenkopmk_db.pegawai p', 'p.id = ku.pegawai_id', 'left')
            ->join('kemenkopmk_db.unit_kerja uk', 'uk.id = ku.unit_kerja_id', 'left')
            ->join('users u', 'u.username_ldap = ku.username_ldap', 'left')
            ->join('pengajuan_akses pa', 'pa.pegawai_user_id = u.id AND pa.status = "PENDING"', 'left')
            ->whereIn('ku.unit_kerja_id', $allowedUnitKerjaIds)
            ->groupStart()
                ->where('u.role_id IS NULL')
                ->orWhere('u.role_id', 2) // Boleh jika role-nya Pegawai biasa
            ->groupEnd()
            ->where('ku.username_ldap !=', session('user.username_ldap'))
            ->where('pa.id IS NULL') // Tidak sedang PENDING usulannya
            ->orderBy('p.nama', 'ASC');

        $kandidatPegawai = $builder->get()->getResultArray();

        return view('tu_unit/pengajuan_akses/index', [
            'title' => 'Kelola Akses Tim (TU Unit)',
            'pengajuanList' => $pengajuanList,
            'kandidatPegawai' => $kandidatPegawai
        ]);
    }

    public function store()
    {
        $pegawaiLdap  = $this->request->getPost('pegawai_user_id'); // Berisi username_ldap dari dropdown
        $alasanUsulan   = $this->request->getPost('alasan_usulan');
        $pengusulUserId = session('user.id');

        if (!$pegawaiLdap) {
            return redirect()->back()->with('error', 'Silakan pilih pegawai yang akan diusulkan.');
        }

        // 1. Cari user di database lokal berdasarkan username_ldap
        $targetUser = $this->userModel->where('username_ldap', $pegawaiLdap)->first();
        
        $pegawaiUserId = null;

        if ($targetUser) {
            $pegawaiUserId = $targetUser['id'];
            
            // Cek apakah target sudah memiliki role TU Unit
            if ($targetUser['role_id'] == 3) {
                return redirect()->back()->with('error', 'Pegawai tersebut sudah memiliki hak akses TU Unit.');
            }

            // Cek apakah target memiliki role Admin (1) atau TU Persuratan (4)
            if (in_array($targetUser['role_id'], [1, 4])) {
                return redirect()->back()->with('error', 'Pegawai tersebut memiliki peran Admin / TU Persuratan dan tidak dapat ditugaskan menjadi TU Unit.');
            }
        } else {
            // User belum terdaftar di lokal, daftarkan terlebih dahulu sebagai Pegawai biasa (role_id = 2)
            $dbMaster = \Config\Database::connect('kemenkopmk');
            $masterUser = $dbMaster->table('users')
                ->select('pegawai_id, unit_kerja_id, username_ldap, username_m365')
                ->where('username_ldap', $pegawaiLdap)
                ->get()->getRowArray();

            if (!$masterUser) {
                return redirect()->back()->with('error', 'Data pegawai tidak ditemukan di database master LDAP.');
            }

            // Daftarkan ke users lokal
            $newUserData = [
                'pegawai_id'    => $masterUser['pegawai_id'] ?? null,
                'unit_kerja_id' => $masterUser['unit_kerja_id'] ?? null,
                'username_ldap' => $masterUser['username_ldap'],
                'role_id'       => 2, // Pegawai biasa
                'is_active'     => 1,
                'username_m365' => $masterUser['username_m365'] ?? null,
            ];

            if (!$this->userModel->insert($newUserData)) {
                return redirect()->back()->with('error', 'Gagal meregistrasikan pegawai secara lokal.');
            }

            $pegawaiUserId = $this->userModel->getInsertID();
        }

        // Cek double submission (usulan pending)
        $pending = $this->pengajuanModel->cekPendingUsulan($pegawaiUserId);
        if ($pending) {
            return redirect()->back()->with('error', 'Pegawai tersebut masih memiliki usulan akses yang sedang diproses (PENDING).');
        }

        // Proses upload Nota Dinas (WAJIB)
        $namaFileNotaDinas = null;
        $fileNotaDinas = $this->request->getFile('nota_dinas');

        if (!$fileNotaDinas || !$fileNotaDinas->isValid() || $fileNotaDinas->hasMoved()) {
            return redirect()->back()->with('error', 'Nota Dinas Penugasan wajib dilampirkan.');
        }

        // Validasi tipe file: harus PDF
        if ($fileNotaDinas->getMimeType() !== 'application/pdf') {
            return redirect()->back()->with('error', 'File Nota Dinas harus berformat PDF.');
        }

        // Validasi ukuran file: maksimal 1 MB
        if ($fileNotaDinas->getSizeByUnit('mb') > 1) {
            return redirect()->back()->with('error', 'Ukuran file Nota Dinas tidak boleh melebihi 1 MB.');
        }

        // Simpan file dengan nama unik ke folder uploads/nota_dinas/
        $namaFileUnik = $fileNotaDinas->getRandomName();
        $fileNotaDinas->move(FCPATH . 'uploads/nota_dinas/', $namaFileUnik);
        $namaFileNotaDinas = $namaFileUnik;

        $data = [
            'pegawai_user_id'  => $pegawaiUserId,
            'pengusul_user_id' => $pengusulUserId,
            'usulan_role_id'   => 3, // TU Unit
            'status'           => 'PENDING',
            'alasan_usulan'    => $alasanUsulan,
            'nota_dinas'       => $namaFileNotaDinas,
        ];

        if ($this->pengajuanModel->insert($data)) {
            return redirect()->back()->with('success', 'Usulan hak akses TU Unit berhasil diajukan dan menunggu persetujuan TU Persuratan.');
        }

        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengajukan usulan.');
    }

    /**
     * Menampilkan (streaming) file Nota Dinas ke browser.
     * Hanya bisa diakses oleh pengusul, target pegawai, Admin, atau TU Persuratan.
     */
    public function viewNotaDinas($id)
    {
        $pengajuan = $this->pengajuanModel->find($id);

        if (!$pengajuan || empty($pengajuan['nota_dinas'])) {
            return redirect()->back()->with('error', 'File Nota Dinas tidak ditemukan.');
        }

        // Validasi akses berdasarkan role atau kepemilikan data
        $userId = session('user.id');
        $roleId = session('user.role_id');

        $bolehAkses = in_array($roleId, [1, 4])                       // Admin & TU Persuratan
                   || $pengajuan['pengusul_user_id'] == $userId        // Pengusul (Kabag)
                   || $pengajuan['pegawai_user_id']  == $userId;       // Target pegawai

        if (!$bolehAkses) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke file ini.');
        }

        $pathFile = FCPATH . 'uploads/nota_dinas/' . $pengajuan['nota_dinas'];

        if (!file_exists($pathFile)) {
            return redirect()->back()->with('error', 'File tidak ditemukan di server.');
        }

        // Stream file PDF ke browser (buka inline, bukan download)
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="nota-dinas-' . $id . '.pdf"')
            ->setBody(file_get_contents($pathFile));
    }

    public function revoke($id)
    {
        $pengajuan = $this->pengajuanModel->find($id);

        // Pastikan hanya Kabag yang mengusulkan yang bisa mencabut (atau statusnya APPROVED)
        if (!$pengajuan || $pengajuan['pengusul_user_id'] != session('user.id') || $pengajuan['status'] !== 'APPROVED') {
            return redirect()->back()->with('error', 'Data pengajuan tidak valid atau Anda tidak memiliki akses untuk mencabutnya.');
        }

        // 1. Kembalikan Role User Target menjadi Pegawai Biasa (Role ID 2)
        $this->userModel->update($pengajuan['pegawai_user_id'], [
            'role_id' => 2 
        ]);

        // 2. Update status pengajuan
        $this->pengajuanModel->update($id, [
            'status' => 'REJECTED',
            'alasan_penolakan' => 'Akses ditarik kembali oleh Kabag Pengusul pada ' . date('d/m/Y H:i')
        ]);

        return redirect()->back()->with('success', 'Penugasan TU Unit untuk staf tersebut telah berhasil dicabut.');
    }
}
