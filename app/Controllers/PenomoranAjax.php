<?php

/**
 * PenomoranAjax Controller
 *
 * Menangani seluruh endpoint AJAX dalam proses penomoran surat.
 * Menggunakan mekanisme dua langkah (two-step):
 *   1. preview()  — validasi form dan simpan data sementara ke session
 *   2. finalize() — ambil data dari session dan simpan ke database
 *
 * @package App\Controllers
 * @see     PenomoranModel
 */

namespace App\Controllers;

use App\Models\PenomoranModel;

class PenomoranAjax extends BaseController
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
     * Menghasilkan nomor surat lengkap sesuai aturan TND (Permenko No. 1 Tahun 2024).
     *
     * Format per jenis dokumen:
     *  - SPT                   : {No}/{KodeJabatan}/{KodeKlasifikasi}/{Bulan}/{Tahun}
     *    Contoh: 17/ROUM/KA.01/03/2024
     *
     *  - Surat Dinas / lainnya : {Sifat}-{No}/{KodeKlasifikasi}/{Bulan}/{Tahun}
     *    Contoh: B-15/KA.01/03/2024 | R-10/KA.01/04/2024
     *
     * @param  string     $jenisDokumen  Jenis dokumen (misal: 'SPT', 'Surat Dinas')
     * @param  int|string $no            Nomor urut surat
     * @param  int|string $tahun         Tahun surat
     * @param  string     $kodeKlasifikasi Kode klasifikasi arsip (contoh: KA.01)
     * @param  string     $sifatSurat    Sifat surat: SR / R / T / B
     * @param  string     $kodeJabatan   Kode singkat unit (contoh: ROUM) — untuk SPT
     * @param  string|null $tanggal      Tanggal surat (Y-m-d) untuk ambil bulan
     * @return string Nomor surat lengkap sesuai TND
     */
    private function generateFormatContoh($jenisDokumen, $no, $tahun, $kodeKlasifikasi = '', $sifatSurat = 'B', $kodeJabatan = '', $tanggal = null)
    {
        // Ambil bulan dari tanggal surat (jika ada), fallback ke bulan sekarang
        $bulan = $tanggal ? date('m', strtotime($tanggal)) : date('m');

        // Nomor urut dengan padding 3 digit (contoh: 001, 017)
        $noPadded = str_pad($no, 3, '0', STR_PAD_LEFT);

        // Nilai default jika field kosong
        $kodeKlasifikasi = !empty($kodeKlasifikasi) ? $kodeKlasifikasi : '...';
        $kodeJabatan = !empty($kodeJabatan) ? strtoupper($kodeJabatan) : '...';
        $sifatSurat = !empty($sifatSurat) ? strtoupper($sifatSurat) : 'B';

        // Jenis dokumen yang menggunakan format internal per-unit (dengan kode jabatan)
        // Nota Dinas dihapus dari sistem — hanya SPT yang menggunakan format ini
        $jenisInternal = ['SPT'];

        if (in_array($jenisDokumen, $jenisInternal)) {
            // Format: {No}/{KodeJabatan}/{KodeKlasifikasi}/{Bulan}/{Tahun}
            // Contoh : 017/ROUM/KA.01/03/2024
            return "{$noPadded}/{$kodeJabatan}/{$kodeKlasifikasi}/{$bulan}/{$tahun}";
        } else {
            // Format: {Sifat}-{No}/{KodeKlasifikasi}/{Bulan}/{Tahun}
            // Contoh : B-017/KA.01/03/2024
            return "{$sifatSurat}-{$noPadded}/{$kodeKlasifikasi}/{$bulan}/{$tahun}";
        }
    }

    /**
     * Mengembalikan nomor urut berikutnya untuk jenis dokumen tertentu.
     * Dipanggil via AJAX saat pengguna memilih jenis surat di form.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface JSON {next_no: int}
     */
    public function getNextNoAjax()
    {
        $jenisDokumen = $this->request->getGet('jenis_dokumen');
        $tahun = $this->request->getGet('tahun') ?? null;
        $nextNo = $this->penomoranModel->getNextNo($jenisDokumen, $tahun);

        return $this->response->setJSON(['next_no' => $nextNo]);
    }

    /**
     * Langkah 1 proses penomoran — validasi form dan simpan data sementara ke session.
     * Mengembalikan data pratinjau (nomor, jenis, tanggal, dll.) untuk ditampilkan
     * di modal konfirmasi sebelum disimpan ke database.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface JSON {success, data, nomorSuratLengkap}
     */
    public function preview()
    {
        // Ambil aturan validasi dan kecualikan NOMOR_SURAT_LENGKAP untuk langkah 1
        $rules = $this->penomoranModel->getValidationRules();
        unset($rules['NOMOR_SURAT_LENGKAP']);
        // Kolom TND bersifat opsional — tidak wajib di step preview
        unset($rules['KODE_KLASIFIKASI'], $rules['SIFAT_SURAT'], $rules['KODE_JABATAN']);

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors(),
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash()
            ]);
        }

        $jenisDokumen = $this->request->getPost('JENIS_DOKUMEN');
        $tahun = $this->request->getPost('TAHUN');
        $tanggal = $this->request->getPost('TANGGAL');

        // Validasi Future Date (Tidak ada yang boleh membuat surat untuk tanggal masa depan)
        if (strtotime($tanggal) > strtotime(date('Y-m-d'))) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => ['TANGGAL' => 'Tanggal surat tidak boleh melebihi hari ini.']
            ]);
        }

        // Validasi Backdate (Hanya Admin yang bisa backdate)
        $isAdmin = session('user.role_id') == 1;
        if (!$isAdmin && strtotime($tanggal) < strtotime(date('Y-m-d'))) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => ['TANGGAL' => 'Tanggal mundur (backdate) tidak diizinkan. Hubungi Admin/TU.']
            ]);
        }

        $nextNo = $this->penomoranModel->getNextNo($jenisDokumen, $tahun);

        $unitKerjaName = (string) $this->request->getPost('UNIT_KERJA');

        // Cari ID Unit Kerja dari String Namanya
        $ukModel = new \App\Models\UnitKerjaModel();
        $resolvedId = $ukModel->getIdByName($unitKerjaName);
        $finalUnitKerjaId = $resolvedId ? $resolvedId : (session('user.unit_kerja_id') ?: null);

        // Ambil field TND dari form
        $kodeKlasifikasi = trim((string) $this->request->getPost('KODE_KLASIFIKASI'));
        $sifatSurat = strtoupper(trim((string) $this->request->getPost('SIFAT_SURAT'))) ?: 'B';
        $kodeJabatan = strtoupper(trim((string) $this->request->getPost('KODE_JABATAN')));

        // Simpan data sementara di session
        $tempData = [
            'NO' => $nextNo,
            'JENIS_DOKUMEN' => $jenisDokumen,
            'TANGGAL' => $tanggal,
            'UNIT_KERJA' => $unitKerjaName,
            'PERIHAL' => $this->request->getPost('PERIHAL'),
            'NAMA' => $this->request->getPost('NAMA'),
            'TAHUN' => $tahun,
            'STATUS' => 'AKTIF',
            // Ikat surat ke unit kerja pengaju berdasarkan namanya
            'unit_kerja_id' => $finalUnitKerjaId,
            // Kolom TND
            'KODE_KLASIFIKASI' => $kodeKlasifikasi ?: null,
            'SIFAT_SURAT' => $sifatSurat,
            'KODE_JABATAN' => $kodeJabatan ?: null,
        ];

        session()->set('temp_penomoran', $tempData);

        // Generate nomor surat lengkap otomatis sesuai TND
        $nomorSuratLengkap = $this->generateFormatContoh(
            $jenisDokumen,
            $nextNo,
            $tahun,
            $kodeKlasifikasi,
            $sifatSurat,
            $kodeJabatan,
            $tanggal
        );

        // Format tanggal untuk display
        $tanggalDisplay = $tanggal;
        if (!empty($tanggal)) {
            $timestamp = strtotime($tanggal);
            if ($timestamp !== false) {
                $tanggalDisplay = date('d F Y', $timestamp);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => array_merge($tempData, ['TANGGAL_DISPLAY' => $tanggalDisplay]),
            'nomorSuratLengkap' => $nomorSuratLengkap,
            // Kembalikan token baru ke client — karena $regenerate = true di Security.php,
            // token sudah di-rotate setelah request ini. Token lama di form sudah basi.
            'csrf_token' => csrf_token(),
            'csrf_hash' => csrf_hash(),
        ]);
    }

    /**
     * Langkah 2 proses penomoran — ambil data dari session dan simpan ke database.
     * Menggunakan Query Builder (bukan Model) karena tabel memiliki composite primary key.
     * Setelah berhasil disimpan, session sementara dihapus.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface JSON {success, message, redirect?}
     */
    public function finalize()
    {
        // Ambil data dari session
        $tempData = session('temp_penomoran');

        if (!$tempData) {
            log_message('error', 'Session temp_penomoran tidak ditemukan');
            return $this->response->setJSON([
                'success'    => false,
                'message'    => 'Sesi berakhir. Silakan isi form kembali.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Ambil nomor surat lengkap dari request (opsional - tidak wajib diisi)
        $nomorSuratLengkap = $this->request->getPost('NOMOR_SURAT_LENGKAP');

        // Tambahkan nomor surat lengkap ke data (bisa kosong)
        $tempData['NOMOR_SURAT_LENGKAP'] = $nomorSuratLengkap ?: null;

        // Tambahkan USER_ID dari session (BUKAN dari form!)
        $tempData['USER_ID'] = session('user.id');

        // Tambahkan Timestamp secara manual karena kita menggunakan Query Builder
        $now = date('Y-m-d H:i:s');
        $tempData['CREATED_AT'] = $now;
        $tempData['UPDATED_AT'] = $now;

        // Simpan ke database dengan penanganan error
        // CATATAN: Menggunakan Query Builder karena Model tidak mendukung composite key untuk insert
        try {
            $db = \Config\Database::connect();

            // Simpan menggunakan Query Builder (mendukung composite primary key)
            $inserted = $db->table('penomoran')->insert($tempData);

            if ($inserted) {
                // Hapus session
                session()->remove('temp_penomoran');

                // Set Flashdata untuk Toast
                session()->setFlashdata('success', 'Data penomoran berhasil ditambahkan');

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Data penomoran berhasil ditambahkan',
                    'redirect' => base_url('/penomoran?jenis=' . urlencode($tempData['JENIS_DOKUMEN']) . '&tahun=' . $tempData['TAHUN'])
                ]);
            } else {
                return $this->response->setJSON([
                    'success'    => false,
                    'message'    => 'Gagal menyimpan data penomoran',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Finalize error: ' . $e->getMessage());

            // Cek collision duplicate primary key (Race Condition / Nomor surat sudah digunakan)
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return $this->response->setJSON([
                    'success'     => false,
                    'isDuplicate' => true,
                    'message'     => 'Nomor Urut [' . $tempData['NO'] . '] sudah digunakan. Silakan kembali dan klik Generate ulang untuk mendapatkan nomor baru.',
                    'csrf_token'  => csrf_token(),
                    'csrf_hash'   => csrf_hash(),
                ]);
            }

            return $this->response->setJSON([
                'success'    => false,
                'message'    => 'Error: ' . $e->getMessage(),
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }
    }

    /**
     * Endpoint autocomplete kode klasifikasi arsip.
     * Dipanggil via GET dengan parameter ?q=<query>
     *
     * Mengembalikan JSON: [{ kode, uraian, fungsi }, ...]
     * Maksimal 15 hasil, diurutkan berdasarkan kode.
     */
    public function getKlasifikasiAjax()
    {
        $q = trim($this->request->getGet('q') ?? '');
        $db = \Config\Database::connect();

        $builder = $db->table('klasifikasi_arsip')
            ->select('kode, uraian, fungsi')
            ->where('is_active', 1)
            ->orderBy('kode', 'ASC')
            ->limit(15);

        if (!empty($q)) {
            $builder->groupStart()
                ->like('kode', $q, 'after')
                ->orLike('uraian', $q)
                ->groupEnd();
        }

        $results = $builder->get()->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'data' => $results,
        ]);
    }
}
