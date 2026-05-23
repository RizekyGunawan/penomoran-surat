<?php

namespace App\Models;

use CodeIgniter\Model;

class PenomoranModel extends Model
{
    protected $table = 'penomoran';
    protected $primaryKey = ['NO', 'JENIS_DOKUMEN', 'TAHUN'];
    protected $useAutoIncrement = false; // Composite key tidak mendukung auto increment
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'NO',
        'NOMOR_SURAT_LENGKAP',
        'JENIS_DOKUMEN',
        'TANGGAL',
        'UNIT_KERJA',
        'PERIHAL',
        'NAMA',
        'USER_ID',
        'unit_kerja_id',
        'TAHUN',
        'STATUS',
        'ALASAN_PEMBATALAN',
        'TANGGAL_PEMBATALAN',
        'DIBATALKAN_OLEH',
        // Kolom TND (Permenko No. 1 Tahun 2024)
        'KODE_KLASIFIKASI',
        'SIFAT_SURAT',
        'KODE_JABATAN',
    ];


    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'CREATED_AT';
    protected $updatedField = 'UPDATED_AT';

    // Validation
    protected $validationRules = [
        'JENIS_DOKUMEN' => 'required|string|max_length[100]',
        'NOMOR_SURAT_LENGKAP' => 'permit_empty|string|max_length[255]',
        'TANGGAL' => 'required|valid_date',
        'UNIT_KERJA' => 'required|string|max_length[150]',
        'PERIHAL' => 'required|string',
        'NAMA' => 'required|string|max_length[150]',
        'TAHUN' => 'permit_empty|integer|exact_length[4]',
        // Kolom TND
        'KODE_KLASIFIKASI' => 'permit_empty|string|max_length[50]',
        'SIFAT_SURAT' => 'permit_empty|in_list[SR,R,T,B]',
        'KODE_JABATAN' => 'permit_empty|string|max_length[50]',
    ];

    protected $validationMessages = [
        'JENIS_DOKUMEN' => [
            'required' => 'Jenis dokumen harus diisi',
            'max_length' => 'Jenis dokumen maksimal 100 karakter',
        ],
        'TANGGAL' => [
            'required' => 'Tanggal surat harus diisi',
            'valid_date' => 'Format tanggal tidak valid',
        ],
        'UNIT_KERJA' => [
            'required' => 'Asal surat / Unit kerja harus diisi',
            'max_length' => 'Unit kerja maksimal 150 karakter',
        ],
        'PERIHAL' => [
            'required' => 'Perihal surat harus diisi',
        ],
        'NAMA' => [
            'required' => 'Nama pengaju harus diisi',
            'max_length' => 'Nama maksimal 150 karakter',
        ],
        'TAHUN' => [
            'integer' => 'Tahun harus berupa angka',
            'exact_length' => 'Tahun harus 4 digit',
        ],
        'SIFAT_SURAT' => [
            'in_list' => 'Sifat surat tidak valid. Pilih: SR, R, T, atau B',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = ['logInsert'];
    protected $beforeUpdate = [];
    protected $afterUpdate = ['logUpdate'];
    protected $beforeDelete = [];
    protected $afterDelete = ['logDelete'];

    protected function logInsert(array $data)
    {
        if (!isset($data['data']))
            return $data;
        $d = $data['data'];
        $idStr = ($d['NO'] ?? '?') . '|' . ($d['JENIS_DOKUMEN'] ?? '?') . '|' . ($d['TAHUN'] ?? '?');
        $audit = new \App\Models\AuditLogModel();
        $audit->logAction('BUAT', $this->table, $idStr, null, $d);
        return $data;
    }

    protected function logUpdate(array $data)
    {
        if (!isset($data['data']))
            return $data;
        $idStr = 'Custom Update (Batch)';
        if (isset($data['id'])) {
            $idStr = is_array($data['id']) ? implode('|', $data['id']) : $data['id'];
        } elseif (isset($data['data']['NO'], $data['data']['JENIS_DOKUMEN'], $data['data']['TAHUN'])) {
            // Fallback for custom logic matching
            $idStr = $data['data']['NO'] . '|' . $data['data']['JENIS_DOKUMEN'] . '|' . $data['data']['TAHUN'];
        }
        $audit = new \App\Models\AuditLogModel();
        $audit->logAction('UBAH', $this->table, $idStr, null, $data['data']);
        return $data;
    }

    protected function logDelete(array $data)
    {
        $idStr = 'Custom Delete';
        if (isset($data['id'])) {
            $idStr = is_array($data['id']) ? implode('|', $data['id']) : $data['id'];
        }
        $audit = new \App\Models\AuditLogModel();
        $audit->logAction('HAPUS', $this->table, $idStr, null, null);
        return $data;
    }

    /**
     * Mendapatkan nomor urut berikutnya untuk Jenis Dokumen tertentu
     * 
     * @param string $jenisDokumen Jenis dokumen yang akan dicari nomornya
     * @param int|null $tahun Tahun dokumen (opsional)
     * @return int Nomor urut berikutnya
     */
    public function getNextNo($jenisDokumen, $tahun = null)
    {
        $builder = $this->where('JENIS_DOKUMEN', $jenisDokumen);
        if ($tahun !== null) {
            $builder = $builder->where('TAHUN', $tahun);
        }

        $lastRecord = $builder->orderBy('NO', 'DESC')->first();

        $nextNoFromDb = $lastRecord ? (int) $lastRecord['NO'] + 1 : 1;

        // Cek konfigurasi nomor awal jika tahun ditentukan
        $configNomorAwal = 1;
        if ($tahun !== null) {
            $nomorAwalModel = new \App\Models\NomorAwalModel();
            $configNomorAwal = $nomorAwalModel->getNomorAwal($jenisDokumen, $tahun);
        }

        // Ambil yang paling besar antara urutan statis DB vs Konfigurasi
        return max($nextNoFromDb, $configNomorAwal);
    }

    /**
     * Mendapatkan semua data penomoran yang diurutkan
     * 
     * @return array Daftar semua data penomoran
     */
    public function getAllPenomoran()
    {
        return $this->orderBy('TAHUN', 'DESC')
            ->orderBy('NO', 'DESC')
            ->findAll();
    }

    /**
     * Mendapatkan data penomoran berdasarkan jenis dokumen
     * 
     * @param string $jenisDokumen Jenis dokumen yang dicari
     * @return array Daftar data penomoran sesuai jenis
     */
    public function getByJenisDokumen($jenisDokumen)
    {
        return $this->where('JENIS_DOKUMEN', $jenisDokumen)
            ->orderBy('NO', 'DESC')
            ->findAll();
    }

    /**
     * Mendapatkan daftar Unit Kerja yang unik/berbeda
     * 
     * @return array Daftar unit kerja
     */
    public function getDistinctUnitKerja()
    {
        $query = $this->db->query("SELECT DISTINCT UNIT_KERJA FROM penomoran WHERE UNIT_KERJA != '' AND UNIT_KERJA IS NOT NULL ORDER BY UNIT_KERJA ASC");
        return $query->getResultArray();
    }

    /**
     * Mengembalikan daftar jenis dokumen komprehensif (gabungan dari master data aktif & riwayat dari database).
     *
     * @return string[] Daftar nama jenis dokumen yang ada
     */
    public function getJenisDokumenList(): array
    {
        // 1. Ambil daftar Jenis Naskah yang aktif dari master tabel jenis_naskah
        $jenisNaskahModel = new \App\Models\JenisNaskahModel();
        $activeJenis = $jenisNaskahModel->where('is_active', 1)->findAll();
        $baseList = array_column($activeJenis, 'nama_jenis');

        // 2. Ambil jenis dokumen yang sudah tidak aktif / dihapus secara logis dari master
        $inactiveJenis = $jenisNaskahModel->where('is_active', 0)->findAll();
        $jenisYangDihapus = array_column($inactiveJenis, 'nama_jenis');
        
        // Tambahan hardcoded untuk jenis dokumen historis yang wajib di-hide jika tidak ada di tabel master
        $jenisYangDihapus[] = 'Nota Dinas';

        // 3. Ambil riwayat yang sudah pernah terekam di tabel penomoran agar data lama tetap dikenali
        $query = $this->db->query("SELECT DISTINCT JENIS_DOKUMEN FROM penomoran WHERE JENIS_DOKUMEN != '' AND JENIS_DOKUMEN IS NOT NULL");
        $dbFields = array_column($query->getResultArray(), 'JENIS_DOKUMEN');

        // Gabungkan riwayat DB dengan baseList (aktif), hilangkan duplikat, lalu buang yang ada di jenisYangDihapus
        $merged = array_unique(array_merge($baseList, $dbFields));
        $merged = array_values(array_diff($merged, $jenisYangDihapus));
        sort($merged);

        return $merged;
    }

    /**
     * Membatalkan nomor surat
     * 
     * @param int $no Nomor surat
     * @param string $jenisDokumen Jenis dokumen
     * @param int $tahun Tahun
     * @param string $userId ID User yang membatalkan
     * @param string $alasan Alasan pembatalan
     * @param bool $bypassOwnership Bypass pemeriksaan kepemilikan (untuk Admin)
     * @return bool True jika berhasil dibatalkan
     */
    public function cancelLetter($no, $jenisDokumen, $tahun, $userId, $alasan, $bypassOwnership = false)
    {
        // Gunakan Query Builder langsung untuk menghindari intervensi Model dengan composite key
        $builder = $this->db->table($this->table)
            ->where('NO', $no)
            ->where('JENIS_DOKUMEN', $jenisDokumen)
            ->where('TAHUN', $tahun);

        // Validasi ownership jika bukan admin (bypassOwnership = false)
        if (!$bypassOwnership) {
            $builder->where('USER_ID', $userId);
        }

        $builder->update([
            'STATUS' => 'DIBATALKAN',
            'ALASAN_PEMBATALAN' => $alasan,
            'TANGGAL_PEMBATALAN' => date('Y-m-d H:i:s'),
            'DIBATALKAN_OLEH' => $userId
        ]);

        $affected = $this->db->affectedRows() > 0;

        if ($affected) {
            $audit = new \App\Models\AuditLogModel();
            $idStr = $no . '|' . $jenisDokumen . '|' . $tahun;
            $audit->logAction('CANCEL', $this->table, $idStr, null, [
                'STATUS' => 'DIBATALKAN',
                'ALASAN_PEMBATALAN' => $alasan
            ]);
        }

        return $affected;
    }

    /**
     * Memeriksa apakah surat sudah dibatalkan
     * 
     * @param int $no Nomor surat
     * @param string $jenisDokumen Jenis dokumen
     * @param int $tahun Tahun
     * @return bool True jika status DIBATALKAN
     */
    public function isCancelled($no, $jenisDokumen, $tahun)
    {
        $record = $this->where('NO', $no)
            ->where('JENIS_DOKUMEN', $jenisDokumen)
            ->where('TAHUN', $tahun)
            ->first();

        return $record && $record['STATUS'] === 'DIBATALKAN';
    }

    /**
     * Mendapatkan statistik untuk kartu dashboard
     * 
     * @param string|null $jenisDokumen Filter jenis dokumen
     * @param int|null $tahun Filter tahun
     * @return array Data statistik
     */
    public function getStatistics($jenisDokumen = null, $tahun = null, $userId = null)
    {
        // Nama bulan dalam Bahasa Indonesia
        $namaBulan = [
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
            'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
            'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
            'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember',
        ];
        $currentMonth = date('Y-m');
        $labelBulan   = $namaBulan[date('F')] . ' ' . date('Y');

        // Filter dasar yang sama dipakai di semua query statistik
        $filterDasar = [
            'jenis'   => $jenisDokumen,
            'tahun'   => $tahun,
            'user_id' => $userId,
        ];

        // ── Hitung total keseluruhan ────────────────────────────
        $totalCreated   = $this->terapkanFilter($this->builder(), $filterDasar)->countAllResults();
        $totalPublished = $this->terapkanFilter($this->builder(), $filterDasar)->where('STATUS', 'AKTIF')->countAllResults();
        $totalCancelled = $this->terapkanFilter($this->builder(), $filterDasar)->where('STATUS', 'DIBATALKAN')->countAllResults();

        // ── Hitung total bulan berjalan ──────────────────────────
        $monthlyCreated = $this->terapkanFilter($this->builder(), $filterDasar)
            ->where('DATE_FORMAT(TANGGAL, "%Y-%m")', $currentMonth)
            ->countAllResults();

        $monthlyPublished = $this->terapkanFilter($this->builder(), $filterDasar)
            ->where('STATUS', 'AKTIF')
            ->where('DATE_FORMAT(TANGGAL, "%Y-%m")', $currentMonth)
            ->countAllResults();

        $monthlyCancelled = $this->terapkanFilter($this->builder(), $filterDasar)
            ->where('STATUS', 'DIBATALKAN')
            ->where('DATE_FORMAT(TANGGAL_PEMBATALAN, "%Y-%m")', $currentMonth)
            ->countAllResults();

        return [
            'total_created'    => $totalCreated,
            'total_published'  => $totalPublished,
            'total_cancelled'  => $totalCancelled,
            'monthly_created'  => $monthlyCreated,
            'monthly_published'=> $monthlyPublished,
            'monthly_cancelled'=> $monthlyCancelled,
            'current_month'    => $labelBulan,
        ];
    }

    /**
     * Mencari surat berdasarkan kata kunci
     * 
     * @param string $keyword Kata kunci pencarian
     * @param string|null $jenisDokumen Filter jenis dokumen
     * @param int|null $tahun Filter tahun
     * @return \CodeIgniter\Database\BaseBuilder Builder query
     */
    public function searchLetters($keyword, $jenisDokumen = null, $tahun = null)
    {
        $builder = $this->builder();

        // Cari di beberapa kolom
        if (!empty($keyword)) {
            $builder->groupStart()
                ->like('NO', $keyword)
                ->orLike('PERIHAL', $keyword)
                ->orLike('NAMA', $keyword)
                ->orLike('UNIT_KERJA', $keyword)
                ->orLike('NOMOR_SURAT_LENGKAP', $keyword)
                ->groupEnd();
        }

        // Apply filters
        if (!empty($jenisDokumen)) {
            $builder->where('JENIS_DOKUMEN', $jenisDokumen);
        }
        if (!empty($tahun)) {
            $builder->where('TAHUN', $tahun);
        }

        return $builder;
    }

    /**
     * Memusatkan semua logika penyaringan (filter) query ke satu tempat.
     *
     * Semua method yang butuh filter (getLettersWithPagination, getFilteredLetters,
     * getStatistics) memanggil method ini agar tidak ada duplikasi kode.
     *
     * @param object $builder  Query Builder CI4 yang sudah disiapkan
     * @param array  $filters  Array filter dengan kunci: search, jenis, tahun,
     *                         start_date, end_date, status, user_id, unit_kerja_id, unit_kerja
     * @return object Query Builder yang sudah diterapkan semua filter
     */
    private function terapkanFilter(object $builder, array $filters): object
    {
        // ── Filter pencarian kata kunci ──────────────────────────
        if (!empty($filters['search'])) {
            $keyword = $filters['search'];
            $builder->groupStart()
                ->like('penomoran.NO', $keyword)
                ->orLike('penomoran.PERIHAL', $keyword)
                ->orLike('penomoran.NAMA', $keyword)
                ->orLike('penomoran.UNIT_KERJA', $keyword)
                ->orLike('penomoran.NOMOR_SURAT_LENGKAP', $keyword)
                ->groupEnd();
        }

        // ── Filter jenis dokumen ─────────────────────────────────
        if (!empty($filters['jenis'])) {
            $builder->where('penomoran.JENIS_DOKUMEN', $filters['jenis']);
        }

        // ── Filter tanggal: rentang lebih prioritas dari tahun ───
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $builder->where('penomoran.TANGGAL >=', $filters['start_date']);
            $builder->where('penomoran.TANGGAL <=', $filters['end_date'] . ' 23:59:59');
        } elseif (!empty($filters['tahun'])) {
            $builder->where('penomoran.TAHUN', $filters['tahun']);
        }

        // ── Filter pemilik surat (user_id) ───────────────────────
        if (!empty($filters['user_id'])) {
            $builder->where('penomoran.USER_ID', $filters['user_id']);
        }

        // ── Filter status surat ──────────────────────────────────
        // Setelah standardisasi, STATUS hanya bernilai AKTIF atau DIBATALKAN.
        // Filter 'DITERBITKAN' dipertahankan sebagai alias untuk 'AKTIF'
        // agar tidak memutus kompatibilitas dengan kode yang sudah ada.
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'DITERBITKAN' || $filters['status'] === 'AKTIF') {
                $builder->where('penomoran.STATUS', 'AKTIF');
            } elseif ($filters['status'] === 'DIBATALKAN') {
                $builder->where('penomoran.STATUS', 'DIBATALKAN');
            }
            // Jika 'SEMUA' atau nilai lain, tidak ada filter status (tampilkan semua)
        }

        // ── Filter unit kerja berdasarkan ID ─────────────────────
        // Mendukung single ID (int/string) maupun multi-ID (array atau string "1,2,3")
        if (!empty($filters['unit_kerja_id'])) {
            $ukId = $filters['unit_kerja_id'];
            if (is_string($ukId) && strpos($ukId, ',') !== false) {
                $ukId = explode(',', $ukId);
            }
            if (is_array($ukId)) {
                $builder->whereIn('penomoran.unit_kerja_id', $ukId);
            } else {
                $builder->where('penomoran.unit_kerja_id', $ukId);
            }
        }

        // ── Filter unit kerja berdasarkan nama teks ──────────────
        if (!empty($filters['unit_kerja'])) {
            $builder->like('penomoran.UNIT_KERJA', $filters['unit_kerja']);
        }

        return $builder;
    }

    /**
     * Mendapatkan data surat dengan paginasi.
     *
     * Menggunakan terapkanFilter() untuk menghindari duplikasi logika
     * dengan getFilteredLetters().
     *
     * @param int   $perPage Jumlah data per halaman
     * @param array $filters Array filter (lihat terapkanFilter untuk daftar kunci)
     * @return array Data terpaginasi
     */
    public function getLettersWithPagination($perPage = 10, $filters = [])
    {
        $builder = $this->builder();

        // Ambil nama lengkap pegawai dari database master kemenkopmk (read-only, tidak ada perubahan data)
        $builder->select('penomoran.*, master_pegawai.nama as nama_lengkap_pegawai, master_pegawai.gelar_depan, master_pegawai.gelar_belakang');
        $builder->join('users u', 'u.id = penomoran.USER_ID', 'left');
        $builder->join('kemenkopmk_db.users master_users', 'master_users.username_ldap = u.username_ldap', 'left');
        $builder->join('kemenkopmk_db.pegawai master_pegawai', 'master_pegawai.id = master_users.pegawai_id', 'left');

        $this->terapkanFilter($builder, $filters);

        // PENTING: orderBy dan paginate harus dipanggil pada $builder (bukan $this)
        // agar JOIN di atas benar-benar ikut dieksekusi dalam query
        $builder->orderBy('penomoran.CREATED_AT', 'DESC');

        return $this->paginate($perPage);
    }

    /**
     * Mendapatkan semua data surat tanpa paginasi (untuk export/rekap).
     *
     * Menggunakan terapkanFilter() yang sama dengan getLettersWithPagination()
     * sehingga hasil filter selalu konsisten.
     *
     * @param array $filters Array filter (lihat terapkanFilter untuk daftar kunci)
     * @return array Data surat yang sudah tersaring
     */
    public function getFilteredLetters($filters = [])
    {
        $builder = $this->builder();
        $builder->select('penomoran.*, master_pegawai.nama as nama_lengkap_pegawai, master_pegawai.gelar_depan, master_pegawai.gelar_belakang');
        $builder->join('users u', 'u.id = penomoran.USER_ID', 'left');
        $builder->join('kemenkopmk_db.users master_users', 'master_users.username_ldap = u.username_ldap', 'left');
        $builder->join('kemenkopmk_db.pegawai master_pegawai', 'master_pegawai.id = master_users.pegawai_id', 'left');

        $builder = $this->terapkanFilter($builder, $filters);

        return $builder
            ->orderBy('penomoran.CREATED_AT', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Mendapatkan statistik untuk dashboard admin (semua user, tanpa filter).
     *
     * @return array Data statistik Admin
     */
    public function getAdminStatistics()
    {
        return $this->getStatistics();
    }
}
