<?php

/**
 * Penomoran Controller
 *
 * Menangani semua aksi yang berkaitan dengan pegawai/pengguna biasa
 * dalam sistem penomoran surat: melihat riwayat pengajuan, membuat,
 * mengedit, menghapus, dan membatalkan nomor surat milik sendiri.
 * 
 * Akses admin ditangani oleh PenomoranAdmin.
 * Endpoint AJAX ditangani oleh PenomoranAjax.
 *
 * @package App\Controllers
 * @see     PenomoranAdmin
 * @see     PenomoranAjax
 * @see     PenomoranModel
 */

namespace App\Controllers;

use App\Models\PenomoranModel;
use App\Services\UnitKerjaService;

class Penomoran extends BaseController
{
    /** @var PenomoranModel Instance model penomoran */
    protected $penomoranModel;

    /**
     * Inisialisasi model yang dibutuhkan oleh controller ini.
     */
    public function __construct()
    {
        $this->penomoranModel = new PenomoranModel();
    }

    /**
     * Menampilkan riwayat pengajuan nomor surat milik pengguna yang sedang login.
     * Mendukung filter (jenis, tahun, pencarian, tanggal, unit kerja, status)
     * dan pagination.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        // Ambil filter dan parameter pagination dari query string
        $jenisDokumen = $this->request->getGet('jenis');
        $tahun = $this->request->getGet('tahun');
        $search = $this->request->getGet('search');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $unitKerja = $this->request->getGet('unit_kerja');
        $status = $this->request->getGet('status');

        $page = $this->request->getGet('page') ?? 1;

        // Ambil per_page dengan validasi
        $perPage = $this->request->getGet('per_page') ?? 10;
        $allowedPerPage = [10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        $perPage = (int) $perPage;

        // Get USER_ID dari session
        $userId = session('user.id');
        // $roleId = session('user.role_id'); // Tidak lagi diperlukan untuk logika ini

        // Susun array filter
        $filters = [];
        $userFilters = []; // Hanya filter yang diinput user (untuk UI hasFilter)

        // SELALU filter berdasarkan User ID yang login
        // Halaman ini khusus untuk Riwayat Pengajuan User Pribadi
        $filters['user_id'] = $userId;

        if (!empty($jenisDokumen)) {
            $filters['jenis'] = $userFilters['jenis'] = $jenisDokumen;
        }
        if (!empty($tahun)) {
            $filters['tahun'] = $userFilters['tahun'] = $tahun;
        }
        if (!empty($search)) {
            $filters['search'] = $userFilters['search'] = $search;
        }
        if (!empty($startDate)) {
            $filters['start_date'] = $userFilters['start_date'] = $startDate;
        }
        if (!empty($endDate)) {
            $filters['end_date'] = $userFilters['end_date'] = $endDate;
        }
        if (!empty($unitKerja)) {
            $filters['unit_kerja'] = $userFilters['unit_kerja'] = $unitKerja;
        }
        if (!empty($status)) {
            $filters['status'] = $userFilters['status'] = $status;
        }

        // Ambil statistik
        $statistics = $this->penomoranModel->getStatistics($jenisDokumen, $tahun, $userId);

        // Ambil data dengan pagination
        $penomoranData = $this->penomoranModel->getLettersWithPagination($perPage, $filters);
        $pager = $this->penomoranModel->pager;

        // Ambil Daftar Unit Kerja untuk dropdown filter
        $unitKerjaList = $this->penomoranModel->getDistinctUnitKerja();

        // Set title berdasarkan filter
        $title = 'Data Penomoran Surat';
        if (!empty($jenisDokumen)) {
            $title .= ' - ' . $jenisDokumen;
        }
        if (!empty($tahun)) {
            $title .= ' (' . $tahun . ')';
        }

        $data = [
            'title' => $title,
            'penomoran' => $penomoranData,
            'jenisDokumen' => $jenisDokumen ?? '',
            'tahun' => $tahun ?? '',
            'search' => $search ?? '',
            'startDate' => $startDate ?? '',
            'endDate' => $endDate ?? '',
            'unitKerja' => $unitKerja ?? '',
            'status' => $status ?? '',
            'jenisDokumenList' => $this->penomoranModel->getJenisDokumenList(),
            'unitKerjaList' => $unitKerjaList,
            'currentUserId' => $userId,
            'hasFilter' => !empty($userFilters),
            'statistics' => $statistics,
            'pagination' => [
                'current_page' => $pager->getCurrentPage(),
                'total_pages' => $pager->getPageCount(),
                'total_records' => $pager->getTotal(),
                'per_page' => $perPage
            ]
        ];

        return view('penomoran/index', $data);
    }

    /**
     * Menampilkan form untuk membuat pengajuan nomor surat baru.
     * Secara otomatis mengisi data pengguna (nama, unit kerja) dari session dan DB.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function create()
    {
        $jenisDokumen = $this->request->getGet('jenis') ?? '';
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        // Get next NO untuk jenis dokumen ini (per tahun) - hanya jika jenis sudah dipilih
        $nextNo = !empty($jenisDokumen)
            ? $this->penomoranModel->getNextNo($jenisDokumen, $tahun)
            : 1;

        // Ambil info user dari session
        $userSession = session()->get('user') ?? [];
        $userName = $userSession['name'] ?? $userSession['username_ldap'] ?? '';
        $pegawaiId = $userSession['pegawai_id'] ?? null;

        // Ambil unit_kerja_id dari session (sudah disimpan saat login)
        $unitKerjaId = $userSession['unit_kerja_id'] ?? null;

        // Dapatkan konteks lengkap unit kerja:
        // - nama   : untuk Asal Surat
        // - kode   : untuk Kode Jabatan/Unit (otomatis)
        // - type   : 'fixed' atau 'dropdown'
        // - options: daftar Asdep jika type=dropdown
        $ukService = new UnitKerjaService();
        $unitContext = $ukService->getUnitContext($unitKerjaId ? (int) $unitKerjaId : null);

        // Fallback ke pencarian via pegawai_id jika unit_kerja_id kosong
        $unitKerja = $unitContext['nama'];
        if (empty($unitKerja)) {
            $unitKerja = $ukService->getByPegawaiId($pegawaiId);
            $unitContext['nama'] = $unitKerja;
        }

        $data = [
            'title' => 'Tambah Data Penomoran' . (!empty($jenisDokumen) ? ' - ' . $jenisDokumen : ''),
            'jenisDokumenList' => $this->penomoranModel->getJenisDokumenList(),
            'jenisDokumen' => $jenisDokumen,
            'tahun' => $tahun,
            'nextNo' => $nextNo,
            'userName' => $userName,
            'unitKerja' => $unitKerja,   // kompatibilitas backward
            'unitContext' => $unitContext,  // konteks lengkap untuk view cerdas
        ];

        return view('penomoran/form', $data);
    }

    /**
     * Memproses penyimpanan pengajuan nomor surat baru ke database.
     * Nomor urut (NO) digenerate otomatis berdasarkan jenis dokumen dan tahun.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function store()
    {
        $rules = $this->penomoranModel->getValidationRules();

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $jenisDokumen = $this->request->getPost('JENIS_DOKUMEN');
        $tahunPosted = $this->request->getPost('TAHUN');
        $tanggalInput = $this->request->getPost('TANGGAL');

        // Validasi Future Date (Tidak ada yang boleh membuat surat untuk tanggal masa depan)
        if (strtotime($tanggalInput) > strtotime(date('Y-m-d'))) {
            return redirect()->back()->withInput()->with('error', 'Tanggal surat tidak boleh melebihi hari ini.');
        }

        // Validasi Backdate (Hanya Admin yang bisa backdate)
        $isAdmin = session('user.role_id') == 1;
        if (!$isAdmin && strtotime($tanggalInput) < strtotime(date('Y-m-d'))) {
            return redirect()->back()->withInput()->with('error', 'Anda tidak diizinkan membuat surat dengan tanggal mundur (backdate). Silakan hubungi Admin/TU.');
        }

        $nextNo = $this->penomoranModel->getNextNo($jenisDokumen, $tahunPosted);

        $unitKerjaName = $this->request->getPost('UNIT_KERJA');

        $data = [
            'NO' => $nextNo,
            'JENIS_DOKUMEN' => $jenisDokumen,
            'TANGGAL' => $this->request->getPost('TANGGAL'),
            'UNIT_KERJA' => $unitKerjaName,
            'PERIHAL' => $this->request->getPost('PERIHAL'),
            'NAMA' => $this->request->getPost('NAMA'),
            'TAHUN' => $this->request->getPost('TAHUN'),
            'STATUS' => 'AKTIF',
        ];

        // Cari ID Unit Kerja dari String Namanya
        $ukModel = new \App\Models\UnitKerjaModel();
        $resolvedId = $ukModel->getIdByName($unitKerjaName);
        if ($resolvedId) {
            $data['unit_kerja_id'] = $resolvedId;
        } else {
            // Fallback (berjaga-jaga jika menggunakan sesi LDAP)
            $data['unit_kerja_id'] = session('user.unit_kerja_id') ?: null;
        }

        // Tambah USER_ID jika login
        if (session('isLoggedIn')) {
            $data['USER_ID'] = session('user.id');
        }

        if ($this->penomoranModel->insert($data)) {
            // Redirect dengan filter jika ada
            $redirectUrl = '/penomoran';
            if (!empty($jenisDokumen) && !empty($tahunPosted)) {
                $redirectUrl .= '?jenis=' . urlencode($jenisDokumen) . '&tahun=' . $tahunPosted;
            }
            return redirect()->to($redirectUrl)->with('success', 'Data penomoran berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data penomoran');
        }
    }

    /**
     * Menampilkan form untuk mengedit data penomoran yang sudah ada.
     * Hanya dapat mengedit surat milik sendiri (validasi ownership).
     *
     * @param  int|string $no           Nomor urut surat
     * @param  string     $jenisDokumen Jenis dokumen surat
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function edit($no, $jenisDokumen)
    {
        // Validasi ownership
        $userId = session('user.id');

        $penomoran = $this->penomoranModel
            ->where('NO', $no)
            ->where('JENIS_DOKUMEN', $jenisDokumen)
            ->where('USER_ID', $userId)
            ->first();

        if (!$penomoran) {
            return redirect()->to('/penomoran')
                ->with('error', 'Data tidak ditemukan atau Anda tidak memiliki akses');
        }

        if (isset($penomoran['STATUS']) && $penomoran['STATUS'] === 'DIBATALKAN') {
            return redirect()->to('/penomoran')
                ->with('error', 'Surat yang sudah dibatalkan tidak dapat diubah.');
        }

        // Dapatkan konteks lengkap unit kerja untuk form edit
        $unitKerjaId = session('user.unit_kerja_id') ?? null;
        $pegawaiId = session('user.pegawai_id') ?? null;
        $ukService = new UnitKerjaService();
        $unitContext = $ukService->getUnitContext($unitKerjaId ? (int) $unitKerjaId : null);

        // Jika unit kerja sudah tersimpan di record, pakai nama dari record tersebut
        // tetapi tetap gunakan konteks hierarki (kode, type, options) dari session
        $unitKerja = $penomoran['UNIT_KERJA'] ?? '';
        if (empty($unitKerja)) {
            // Tidak ada nama tersimpan → ambil dari konteks
            $unitKerja = $unitContext['nama'];
            if (empty($unitKerja)) {
                $unitKerja = $ukService->getByPegawaiId($pegawaiId);
                $unitContext['nama'] = $unitKerja;
            }
            $penomoran['UNIT_KERJA'] = $unitKerja;
        } else {
            // Nama sudah ada di record → pertahankan, update hanya nama di konteks
            $unitContext['nama'] = $unitKerja;
        }

        $data = [
            'title' => 'Edit Data Penomoran',
            'penomoran' => $penomoran,
            'jenisDokumenList' => $this->penomoranModel->getJenisDokumenList(),
            'unitKerja' => $unitKerja,
            'unitContext' => $unitContext,  // konteks lengkap untuk view cerdas
            'tahun' => $penomoran['TAHUN'] ?? date('Y'),
            'nextNo' => $penomoran['NO'] ?? '',
            'userName' => $penomoran['NAMA'] ?? (session('user.name') ?? session('user.username_ldap') ?? ''),
        ];

        return view('penomoran/form', $data);
    }

    /**
     * Memperbarui data penomoran di database.
     * Menggunakan composite primary key (NO, JENIS_DOKUMEN, TAHUN) untuk where clause.
     *
     * @param  int|string $no           Nomor urut surat
     * @param  string     $jenisDokumen Jenis dokumen surat
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function update($no, $jenisDokumen)
    {
        // Decode jenis dokumen dari URL
        $jenisDokumen = urldecode($jenisDokumen);

        $rules = $this->penomoranModel->getValidationRules();

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Ambil tahun dari request atau hidden input
        $tahun = $this->request->getPost('TAHUN');
        if (empty($tahun)) {
            $existing = $this->penomoranModel->where('NO', $no)
                ->where('JENIS_DOKUMEN', $jenisDokumen)
                ->first();
            $tahun = $existing['TAHUN'] ?? date('Y');
        } else {
            $existing = $this->penomoranModel->where('NO', $no)
                ->where('JENIS_DOKUMEN', $jenisDokumen)
                ->where('TAHUN', $tahun)
                ->first();
        }

        if (isset($existing['STATUS']) && $existing['STATUS'] === 'DIBATALKAN') {
            return redirect()->to('/penomoran')->with('error', 'Surat yang sudah dibatalkan tidak dapat diubah.');
        }

        $tanggalInput = $this->request->getPost('TANGGAL');
        
        // Validasi Future Date (Tidak ada yang boleh membuat surat untuk tanggal masa depan)
        if (strtotime($tanggalInput) > strtotime(date('Y-m-d'))) {
            return redirect()->back()->withInput()->with('error', 'Tanggal surat tidak boleh melebihi hari ini.');
        }

        $isAdmin = session('user.role_id') == 1;
        if (!$isAdmin && strtotime($tanggalInput) < strtotime(date('Y-m-d'))) {
            return redirect()->back()->withInput()->with('error', 'Anda tidak diizinkan membuat surat dengan tanggal mundur (backdate). Silakan hubungi Admin/TU.');
        }

        $data = [
            'TANGGAL' => $this->request->getPost('TANGGAL'),
            'UNIT_KERJA' => $this->request->getPost('UNIT_KERJA'),
            'PERIHAL' => $this->request->getPost('PERIHAL'),
            'NAMA' => $this->request->getPost('NAMA'),
            'NOMOR_SURAT_LENGKAP' => $this->request->getPost('NOMOR_SURAT_LENGKAP'),
        ];

        // Cek apakah ada perubahan status yang dikirim (misal re-aktivasi)
        // Tapi biasanya update hanya edit data, tidak ubah status kecuali tombol khusus

        // Update berdasarkan composite primary key (NO, JENIS_DOKUMEN, TAHUN)
        $updated = $this->penomoranModel
            ->where('NO', $no)
            ->where('JENIS_DOKUMEN', $jenisDokumen)
            ->where('TAHUN', $tahun)
            ->set($data)
            ->update();

        if ($updated) {
            // Redirect dengan filter yang sesuai
            $redirectUrl = '/penomoran';
            if (!empty($jenisDokumen) && !empty($tahun)) {
                $redirectUrl .= '?jenis=' . urlencode($jenisDokumen) . '&tahun=' . $tahun;
            }
            return redirect()->to($redirectUrl)->with('success', 'Data penomoran berhasil diperbarui');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data penomoran');
        }
    }

    /**
     * Menghapus data penomoran dari database.
     * Hanya dapat menghapus surat milik sendiri (validasi ownership via USER_ID).
     *
     * @param  int|string $no           Nomor urut surat
     * @param  string     $jenisDokumen Jenis dokumen surat
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete($no, $jenisDokumen, $tahun)
    {
        // Validasi ownership sebelum delete (pastikan composite key lengkap)
        $userId = session('user.id');

        if (
            $this->penomoranModel->where('NO', $no)
                ->where('JENIS_DOKUMEN', $jenisDokumen)
                ->where('TAHUN', $tahun)
                ->where('USER_ID', $userId)
                ->delete()
        ) {
            return redirect()->to('/penomoran')->with('success', 'Data penomoran berhasil dihapus');
        } else {
            return redirect()->to('/penomoran')->with('error', 'Gagal menghapus data atau Anda tidak memiliki akses');
        }
    }



    /**
     * Menampilkan halaman detail surat milik pengguna yang sedang login.
     * Jika surat berstatus DIBATALKAN, informasi pembatalan juga ditampilkan.
     *
     * @param  int|string $no           Nomor urut surat
     * @param  string     $jenisDokumen Jenis dokumen surat
     * @param  int|string $tahun        Tahun surat diterbitkan
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function detail($no, $jenisDokumen, $tahun)
    {
        $userId = session('user.id');

        $penomoran = $this->penomoranModel
            ->where('NO', $no)
            ->where('JENIS_DOKUMEN', $jenisDokumen)
            ->where('TAHUN', $tahun)
            ->where('USER_ID', $userId)
            ->first();

        if (!$penomoran) {
            return redirect()->to('/penomoran')
                ->with('error', 'Data tidak ditemukan atau Anda tidak memiliki akses');
        }

        // Cari data user pembatal via BaseController::getCancelledByUser()
        // (menggantikan ~60 baris duplikasi sebelumnya)
        $cancelledBy = null;
        if (($penomoran['STATUS'] ?? '') === 'DIBATALKAN' && !empty($penomoran['DIBATALKAN_OLEH'])) {
            $cancelledBy = $this->getCancelledByUser($penomoran['DIBATALKAN_OLEH']);
        }

        $data = [
            'title' => 'Detail Nomor Surat',
            'penomoran' => $penomoran,
            'cancelledBy' => $cancelledBy,
            'currentUserId' => session('user.id'),
        ];

        return view('penomoran/detail', $data);
    }


    /**
     * Menampilkan halaman konfirmasi pembatalan nomor surat.
     * Validasi: surat milik sendiri, belum dibatalkan, dan masih dalam batas waktu (H+3).
     *
     * @param  int|string $no           Nomor urut surat
     * @param  string     $jenisDokumen Jenis dokumen surat
     * @param  int|string $tahun        Tahun surat diterbitkan
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function cancel($no, $jenisDokumen, $tahun)
    {
        $userId = session('user.id');
        $roleId = session('user.role_id');
        $isAdmin = ($roleId == 1);

        // Validasi ownership
        $builder = $this->penomoranModel
            ->where('NO', $no)
            ->where('JENIS_DOKUMEN', $jenisDokumen)
            ->where('TAHUN', $tahun);

        if (!$isAdmin) {
            $builder->where('USER_ID', $userId);
        }

        $penomoran = $builder->first();

        if (!$penomoran) {
            return redirect()->to('/penomoran')
                ->with('error', 'Data tidak ditemukan atau Anda tidak memiliki akses');
        }

        // Cek apakah sudah dibatalkan
        if (isset($penomoran['STATUS']) && $penomoran['STATUS'] === 'DIBATALKAN') {
            return redirect()->to('/penomoran?jenis=' . urlencode($jenisDokumen) . '&tahun=' . $tahun)
                ->with('error', 'Nomor surat ini sudah dibatalkan sebelumnya');
        }

        // Cek Batas Waktu Pembatalan (H-3 dari CREATED_AT)
        // Gunakan timestamp CREATED_AT jika ada, jika tidak gunakan TANGGAL sebagai fallback (untuk data lama)
        $tanggalAcuan = !empty($penomoran['CREATED_AT']) ? date('Y-m-d', strtotime($penomoran['CREATED_AT'])) : $penomoran['TANGGAL'];
        $today = date('Y-m-d');
        $diff = (strtotime($today) - strtotime($tanggalAcuan)) / (60 * 60 * 24);

        if ($diff > 3) {
            return redirect()->to('/penomoran?jenis=' . urlencode($jenisDokumen) . '&tahun=' . $tahun)
                ->with('error', 'Maaf, pembatalan tidak dapat dilakukan. Surat sudah melebihi batas waktu pembatalan.');
        }

        $data = [
            'title' => 'Pembatalan Nomor Surat',
            'penomoran' => $penomoran
        ];

        return view('penomoran/cancel', $data);
    }

    /**
     * Memproses pengajuan pembatalan nomor surat.
     * Menerima input via POST (NO, JENIS_DOKUMEN, TAHUN, ALASAN_PEMBATALAN).
     * Hanya pemilik surat atau admin yang dapat membatalkan.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function cancelSubmit()
    {
        $userId = session('user.id');

        // Validasi input
        $rules = [
            'NO' => 'required|integer',
            'JENIS_DOKUMEN' => 'required',
            'TAHUN' => 'required|integer',
            'ALASAN_PEMBATALAN' => 'required|max_length[5000]'
        ];

        $messages = [
            'ALASAN_PEMBATALAN' => [
                'required' => 'Alasan pembatalan wajib diisi',
                'max_length' => 'Alasan pembatalan tidak boleh lebih dari 5000 karakter'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $no = $this->request->getPost('NO');
        $jenisDokumen = $this->request->getPost('JENIS_DOKUMEN');
        $tahun = $this->request->getPost('TAHUN');
        $alasan = $this->request->getPost('ALASAN_PEMBATALAN');

        $roleId = session('user.role_id');
        $isAdmin = ($roleId == 1);

        // Ambil data surat untuk cek tanggal
        $surat = $this->penomoranModel->where('NO', $no)
            ->where('JENIS_DOKUMEN', $jenisDokumen)
            ->where('TAHUN', $tahun)
            ->first();

        if ($surat) {
            // Gunakan CREATED_AT sebagai acuan, fallback ke TANGGAL
            $tanggalAcuan = !empty($surat['CREATED_AT']) ? date('Y-m-d', strtotime($surat['CREATED_AT'])) : $surat['TANGGAL'];
            $today = date('Y-m-d');
            $diff = (strtotime($today) - strtotime($tanggalAcuan)) / (60 * 60 * 24);

            if ($diff > 3) {
                return redirect()->back()
                    ->with('error', 'Maaf, pembatalan tidak dapat dilakukan. Surat sudah melebihi batas waktu pembatalan.');
            }
        } else {
            return redirect()->back()->with('error', 'Data surat tidak ditemukan.');
        }

        log_message('debug', '[Penomoran::cancelSubmit] UserID: ' . $userId . ', RoleID: ' . $roleId . ', IsAdmin: ' . ($isAdmin ? 'true' : 'false'));

        // Proses pembatalan
        if ($this->penomoranModel->cancelLetter($no, $jenisDokumen, $tahun, $userId, $alasan, $isAdmin)) {
            return redirect()->to('/penomoran?jenis=' . urlencode($jenisDokumen) . '&tahun=' . $tahun)
                ->with('success', 'Nomor surat berhasil dibatalkan');
        } else {
            return redirect()->back()
                ->with('error', 'Gagal membatalkan nomor surat atau Anda tidak memiliki akses');
        }
    }

    /**
     * Menampilkan halaman cetak dokumen surat sesuai formatnya.
     * Dibuka di tab baru; user mengisi isi surat lalu mencetak via browser.
     *
     * @param  int|string $no           Nomor urut surat
     * @param  string     $jenisDokumen Jenis dokumen
     * @param  int|string $tahun        Tahun surat
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function cetak($no, $jenisDokumen, $tahun)
    {
        $userId = session('user.id');
        $isAdmin = in_array(session('user.role'), ['admin', 'superadmin']);

        // Cari data penomoran — admin bisa lihat semua, pegawai hanya miliknya
        $query = $this->penomoranModel
            ->where('NO', $no)
            ->where('JENIS_DOKUMEN', $jenisDokumen)
            ->where('TAHUN', $tahun);

        if (!$isAdmin) {
            $query->where('USER_ID', $userId);
        }

        $penomoran = $query->first();

        if (!$penomoran) {
            return redirect()->to('/penomoran')
                ->with('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
        }

        // Mapping jenis dokumen → nama template view
        $templateMap = [
            'SPT'               => 'cetak/spt',
            'Surat Dinas'       => 'cetak/surat_dinas',
            'Undangan Eksternal'=> 'cetak/undangan_eksternal',
            'Pengumuman'        => 'cetak/pengumuman',
            'Berita Acara'      => 'cetak/berita_acara',
            'Surat Edaran'      => 'cetak/surat_edaran',
            'Surat Kuasa'       => 'cetak/surat_kuasa',
        ];

        $template = $templateMap[$jenisDokumen] ?? null;

        if (!$template) {
            return redirect()->to('/penomoran')
                ->with('error', 'Template cetak untuk jenis surat ini belum tersedia.');
        }

        // Cek apakah file view benar-benar ada sebelum di-render
        $viewFile = APPPATH . 'Views/penomoran/' . $template . '.php';
        if (!file_exists($viewFile)) {
            return redirect()->to('/penomoran')
                ->with('error', 'Template cetak untuk "' . $jenisDokumen . '" sedang dalam pengembangan.');
        }

        $data = [
            'penomoran' => $penomoran,
            'logoUrl' => base_url('images/LogoKemenko.png'),
        ];

        return view('penomoran/' . $template, $data);
    }

}
