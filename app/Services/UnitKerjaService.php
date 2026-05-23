<?php

namespace App\Services;

/**
 * UnitKerjaService
 *
 * Mengisolasi seluruh logika pencarian Unit Kerja dari berbagai
 * skema tabel/view database yang mungkin berbeda antar-instansi.
 *
 * Sebelumnya logika ini tersebar >180 baris di Penomoran::create()
 * dan Penomoran::edit(). Kini cukup dipanggil satu baris:
 *
 *   $unitKerja = (new UnitKerjaService())->getByPegawaiId($pegawaiId);
 */
class UnitKerjaService
{
    /** @var \CodeIgniter\Database\BaseConnection */
    protected $db;

    /**
     * Pemetaan unit_kerja_id → kode jabatan resmi untuk nomor surat.
     *
     * Kode ini digunakan pada format nomor TND (SPT & Nota Dinas).
     * Sumber: Permenko No. 1 Tahun 2024 + konfirmasi pengelola sistem.
     *
     * id=1  → Kemenko PMK (level Menko)
     * id=2  → Sekretariat Kementerian (Sesmenko)
     * id=3–7, 17 → Biro-Biro & Inspektorat di bawah Setmenko
     * id=8–12  → Deputi 1–5 (dan semua Asdep di bawahnya)
     * id=13–16 → Staf Ahli
     */
    public const UNIT_KODE_MAP = [
        1  => 'MENKO',      // Kemenko PMK
        2  => 'SES',        // Sekretariat Kementerian (Sesmenko)
        3  => 'ROUMKEU',    // Biro Umum dan Keuangan
        4  => 'MKKSDM',     // Biro Manajemen Kinerja, KS, dan SDM
        5  => 'KONPERS',    // Biro Komunikasi dan Persidangan
        6  => 'DIGI',       // Biro Digitalisasi dan Pengelolaan Informasi
        7  => 'HUKOR',      // Biro Hukum Organisasi dan Tata Laksana
        17 => 'INSP',       // Inspektorat
        8  => 'D1',         // Deputi 1 (Keluarga & Kependudukan)
        9  => 'D4',         // Deputi 4 (Penguatan Karakter & Jati Diri)
        10 => 'D2',         // Deputi 2 (Kesehatan)
        11 => 'D3',         // Deputi 3 (Pendidikan)
        12 => 'D5',         // Deputi 5 (Penanggulangan Bencana)
        13 => 'SAHLI',      // Staf Ahli Bidang Hukum
        14 => 'SAHLI',      // Staf Ahli Bidang Pembangunan Berkelanjutan
        15 => 'SAHLI',      // Staf Ahli Bidang SDM
        16 => 'SAHLI',      // Staf Ahli Bidang Ketahanan Sosial
    ];

    /**
     * Daftar ID unit kerja Deputi (Eselon 1).
     * Pegawai di bawah Deputi ini mendapatkan pilihan DROPDOWN pada Asal Surat.
     */
    private const DEPUTI_IDS = [8, 9, 10, 11, 12];

    /**
     * Daftar tabel kandidat tempat data pegawai mungkin tersimpan.
     * Urutan mencerminkan prioritas pencarian.
     */
    private const PEGAWAI_TABLES = [
        'pegawai', 'ms_pegawai', 'tbl_pegawai', 'm_pegawai',
        'employees', 'pegawai_master',
    ];

    /**
     * Daftar nama kolom kandidat untuk unit kerja.
     */
    private const UNIT_KERJA_COLUMNS = [
        'unit_kerja', 'unit', 'nama_unit', 'satker', 'opd',
        'sub_unit', 'unit_organisasi', 'nama_organisasi',
    ];

    /**
     * Daftar nama kolom kandidat pada pegawai_view untuk unit kerja.
     */
    private const VIEW_UNIT_KERJA_COLUMNS = [
        'nama_unit_kerja', 'unit_kerja', 'unit_kerja_es_2',
        'nama_unit', 'satker', 'opd',
    ];

    /**
     * Daftar nama kolom ID kandidat pada pegawai_view.
     */
    private const VIEW_ID_COLUMNS = ['id', 'pegawai_id', 'nip', 'nik'];

    public function __construct()
    {
        // Gunakan koneksi 'kemenkopmk' karena tabel pegawai dan unit_kerja
        // berada di kemenkopmk_db, bukan di penomoran_db (default)
        $this->db = \Config\Database::connect('kemenkopmk');
    }

    /**
     * Kembalikan nama unit kerja berdasarkan unit_kerja_id.
     *
     * Ini adalah cara PALING CEPAT dan ANDAL untuk mendapatkan nama unit kerja.
     * unit_kerja_id sudah tersimpan di session user sejak login,
     * sehingga tidak perlu mencari-cari ke tabel pegawai.
     *
     * @param  int|string|null $unitKerjaId  ID unit kerja dari session atau tabel users
     * @return string Nama unit kerja, atau string kosong jika tidak ditemukan.
     */
    public function getByUnitKerjaId($unitKerjaId): string
    {
        // Jika ID tidak ada, langsung kembalikan string kosong
        if (empty($unitKerjaId)) {
            return '';
        }

        try {
            // Query langsung ke tabel unit_kerja berdasarkan ID
            $row = $this->db
                ->table('unit_kerja')
                ->select('nama_unit_kerja')
                ->where('id', (int) $unitKerjaId)
                ->where('deleted_at', null)
                ->limit(1)
                ->get()
                ->getRowArray();

            return $row['nama_unit_kerja'] ?? '';

        } catch (\Throwable $e) {
            // Catat error tapi jangan tampilkan ke pengguna
            log_message('warning', '[UnitKerjaService] Gagal getByUnitKerjaId(' . $unitKerjaId . '): ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Kembalikan nama unit kerja INDUK (Biro / Deputi / Inspektorat).
     *
     * Menelusuri hierarki ke atas dari unit_kerja_id yang diberikan,
     * berhenti saat menemukan unit yang langsung di bawah Kemenko (id=1)
     * atau Sekretariat Kementerian (id=2).
     *
     * Contoh: "Bagian Tata Usaha" (parent → Inspektorat) → "Inspektorat"
     *
     * @param  int|null $unitKerjaId  unit_kerja_id dari tabel users
     * @return string Nama unit induk, atau string kosong jika tidak ditemukan.
     */
    public function getNamaUnitInduk(?int $unitKerjaId): string
    {
        if (empty($unitKerjaId)) {
            return '';
        }

        try {
            // Muat semua unit kerja ke memori untuk penelusuran hierarki
            $allUnits = $this->db
                ->table('unit_kerja')
                ->select('id, nama_unit_kerja, parent_id')
                ->where('deleted_at', null)
                ->get()
                ->getResultArray();

            // Buat index [id => row] untuk pencarian O(1)
            $index = [];
            foreach ($allUnits as $u) {
                $index[(int) $u['id']] = $u;
            }

            if (!isset($index[$unitKerjaId])) {
                return '';
            }

            // Telusuri hierarki ke atas sampai unit yang parent-nya ≤ 2
            // (langsung di bawah Kemenko PMK atau Sekretariat Kementerian)
            $effectiveId = $unitKerjaId;
            $currId      = $unitKerjaId;

            while (isset($index[$currId])) {
                $parentId = (int) ($index[$currId]['parent_id'] ?? 0);

                if ($parentId <= 2) {
                    // Sudah di level Biro/Deputi/Inspektorat
                    $effectiveId = $currId;
                    break;
                }

                // Naik satu level
                $effectiveId = $currId;
                $currId      = $parentId;
            }

            return $index[$effectiveId]['nama_unit_kerja'] ?? '';

        } catch (\Throwable $e) {
            log_message('warning', '[UnitKerjaService] Gagal getNamaUnitInduk(' . $unitKerjaId . '): ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Dapatkan konteks lengkap unit kerja untuk form penomoran surat.
     *
     * Method ini menelusuri hierarki organisasi dari posisi pegawai ke atas,
     * lalu menentukan perilaku field "Asal Surat":
     *  - 'fixed'    → field readonly, tidak bisa diubah (Biro, Inspektorat, Staf Ahli, dll)
     *  - 'dropdown' → field select, bisa pilih Asdep dalam satu Deputi yang sama
     *
     * Nilai yang dikembalikan:
     * [
     *   'nama'         => string  Nama unit kerja (untuk ditampilkan di Asal Surat),
     *   'kode'         => string  Kode jabatan (DIGI, D1, INSP, dll),
     *   'type'         => string  'fixed' atau 'dropdown',
     *   'options'      => array   Daftar pilihan [{id, nama}] jika type=dropdown,
     *   'selected_id'  => int     ID unit kerja yang saat ini terpilih,
     *   'parent_id'    => int     ID unit kerja induk (Deputi/Setmenko/Kemenko),
     * ]
     *
     * @param  int|null $unitKerjaId  unit_kerja_id dari session user
     * @return array
     */
    public function getUnitContext(?int $unitKerjaId): array
    {
        // Nilai default jika ID tidak tersedia atau tabel tidak bisa diakses
        $default = [
            'nama'        => '',
            'kode'        => '',
            'type'        => 'fixed',
            'options'     => [],
            'selected_id' => $unitKerjaId,
            'parent_id'   => null,
        ];

        if (empty($unitKerjaId)) {
            return $default;
        }

        try {
            // Muat seluruh tabel unit_kerja ke memori (data kecil, aman)
            // untuk penelusuran hierarki tanpa query berulang
            $allUnits = $this->db
                ->table('unit_kerja')
                ->select('id, nama_unit_kerja, parent_id')
                ->where('deleted_at', null)
                ->get()
                ->getResultArray();

            // Buat index [id => row] untuk pencarian O(1)
            $index = [];
            foreach ($allUnits as $u) {
                $index[(int) $u['id']] = $u;
            }

            // Jika unit tidak ditemukan, kembalikan default
            if (!isset($index[$unitKerjaId])) {
                return $default;
            }

            // ────────────────────────────────────────────────
            // Telusuri hierarki ke atas untuk cari "unit efektif"
            // Berhenti saat parent_id = 1 (Kemenko PMK) atau NULL
            // ────────────────────────────────────────────────
            $effectiveId = $unitKerjaId; // ID unit yang "bertanggung jawab"
            $currId      = $unitKerjaId;

            while (isset($index[$currId])) {
                $parentId = (int) ($index[$currId]['parent_id'] ?? 0);

                // Sudah sampai di unit langsung bawah Kemenko (parent=1)
                // atau unit langsung bawah Setmenko (parent=2)
                // → ini adalah unit efektif
                if ($parentId <= 2) {
                    $effectiveId = $currId;
                    break;
                }

                // Naik satu level
                $effectiveId = $currId;
                $currId      = $parentId;
            }

            $effectiveUnit  = $index[$effectiveId] ?? $index[$unitKerjaId];
            $effectiveParent = (int) ($effectiveUnit['parent_id'] ?? 0);

            // ────────────────────────────────────────────────
            // Tentukan kode jabatan dari UNIT_KODE_MAP
            // Gunakan effectiveId sebagai kunci utama,
            // fallback ke parent jika tidak ketemu
            // ────────────────────────────────────────────────
            $kode = self::UNIT_KODE_MAP[$effectiveId]
                 ?? self::UNIT_KODE_MAP[$effectiveParent]
                 ?? '';

            // ────────────────────────────────────────────────
            // Tentukan tipe: DROPDOWN jika induk Eselon 1 adalah Deputi
            // Semua pegawai Asdep + TU Deputi mendapat dropdown
            // ────────────────────────────────────────────────
            $isDeputi = in_array($effectiveParent, self::DEPUTI_IDS, true)
                     || in_array($effectiveId, self::DEPUTI_IDS, true);

            if ($isDeputi) {
                // Tentukan ID Deputi-nya (unit Eselon 1 yang jadi induk)
                $deputiId = in_array($effectiveId, self::DEPUTI_IDS, true)
                    ? $effectiveId
                    : $effectiveParent;

                // Ambil semua unit kerja di bawah Deputi ini (Asdep + TU)
                $siblings = array_values(array_filter($allUnits, function ($u) use ($deputiId) {
                    return (int) $u['parent_id'] === $deputiId;
                }));

                // Nama yang ditampilkan = nama unit yang dipilih saat ini (milik pegawai)
                $currentUnit = $index[$unitKerjaId] ?? $effectiveUnit;

                return [
                    'nama'        => $currentUnit['nama_unit_kerja'],
                    'kode'        => $kode,
                    'type'        => 'dropdown',
                    'options'     => $siblings,      // [{id, nama_unit_kerja, parent_id}]
                    'selected_id' => $unitKerjaId,
                    'parent_id'   => $deputiId,
                ];
            }

            // Fixed: Biro, Inspektorat, Staf Ahli, Sesmenko, Menko
            return [
                'nama'        => $effectiveUnit['nama_unit_kerja'],
                'kode'        => $kode,
                'type'        => 'fixed',
                'options'     => [],
                'selected_id' => $effectiveId,
                'parent_id'   => $effectiveParent,
            ];

        } catch (\Throwable $e) {
            log_message('warning', '[UnitKerjaService] Gagal getUnitContext(' . $unitKerjaId . '): ' . $e->getMessage());
            return $default;
        }
    }

    /**
     * Kembalikan nama unit kerja berdasarkan pegawai_id.
     *
     * Digunakan sebagai fallback jika unit_kerja_id tidak tersedia.
     * Urutan pencarian:
     *  1. Tabel pegawai (berbagai nama tabel kandidat)
     *  2. View `pegawai_view` (kolom kandidat unit kerja)
     *
     * @param  int|string $pegawaiId
     * @return string Unit kerja yang ditemukan, atau string kosong jika tidak ada.
     */
    public function getByPegawaiId($pegawaiId): string
    {
        if (empty($pegawaiId)) {
            return '';
        }

        // 1. Cari di tabel-tabel pegawai
        $unitKerja = $this->searchInPegawaiTables($pegawaiId);
        if (!empty($unitKerja)) {
            return $unitKerja;
        }

        // 2. Fallback: cari di pegawai_view
        return $this->searchInPegawaiView($pegawaiId);
    }

    // ─────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────

    /**
     * Iterasi tabel-tabel pegawai kandidat dan ekstrak unit kerja.
     * Jika tabel menyimpan unit_kerja_id (bukan nama langsung),
     * otomatis lakukan JOIN ke tabel unit_kerja untuk ambil namanya.
     */
    private function searchInPegawaiTables($pegawaiId): string
    {
        $existingTables = $this->db->listTables();

        foreach (self::PEGAWAI_TABLES as $tbl) {
            if (!in_array($tbl, $existingTables, true)) {
                continue;
            }

            try {
                $fields  = $this->db->getFieldNames($tbl);
                $builder = $this->db->table($tbl);
                $row     = null;

                // Coba kolom 'id' terlebih dahulu
                if (in_array('id', $fields, true)) {
                    $row = $builder->where('id', $pegawaiId)->get(1)->getRowArray();
                }

                // Fallback ke kolom 'pegawai_id'
                if (empty($row) && in_array('pegawai_id', $fields, true)) {
                    $row = $builder->where('pegawai_id', $pegawaiId)->get(1)->getRowArray();
                }

                if (!empty($row)) {
                    // Cek apakah tabel ini menyimpan unit_kerja_id (ID relasi)
                    // Jika ya, lakukan JOIN ke tabel unit_kerja untuk ambil nama
                    if (in_array('unit_kerja_id', $fields, true) && !empty($row['unit_kerja_id'])) {
                        $ukRow = $this->db
                            ->table('unit_kerja')
                            ->select('nama_unit_kerja')
                            ->where('id', (int) $row['unit_kerja_id'])
                            ->where('deleted_at', null)
                            ->limit(1)
                            ->get()
                            ->getRowArray();

                        if (!empty($ukRow['nama_unit_kerja'])) {
                            return $ukRow['nama_unit_kerja'];
                        }
                    }

                    // Coba ekstrak nama unit kerja dari kolom string (cara lama)
                    $unitKerja = $this->extractUnitKerjaFromRow($row, self::UNIT_KERJA_COLUMNS);
                    if (!empty($unitKerja)) {
                        return $unitKerja;
                    }
                }
            } catch (\Throwable $e) {
                log_message('warning', "[UnitKerjaService] Error searching table '{$tbl}': " . $e->getMessage());
            }
        }

        return '';
    }

    private function searchInPegawaiView($pegawaiId): string
    {
        $viewName = 'pegawai_view';

        try {
            // Bypass getFieldNames() yang sering gagal di MySQL View jika ada masalah definer/permission
            $rowForKeys = $this->db->query("SELECT * FROM {$viewName} LIMIT 1")->getRowArray();
            if (!$rowForKeys) {
                return ''; // View kosong atau tidak bisa diakses
            }
            $viewFields = array_keys($rowForKeys);

            $unitKerjaCol = $this->findFirstMatch($viewFields, self::VIEW_UNIT_KERJA_COLUMNS);

            if (empty($unitKerjaCol)) {
                return '';
            }

            $builder = $this->db->table($viewName);
            $row     = null;

            foreach (self::VIEW_ID_COLUMNS as $idCol) {
                if (in_array($idCol, $viewFields, true)) {
                    $row = $builder->where($idCol, $pegawaiId)->get(1)->getRowArray();
                    if (!empty($row)) {
                        break;
                    }
                }
            }

            return (!empty($row) && !empty($row[$unitKerjaCol])) ? $row[$unitKerjaCol] : '';

        } catch (\Throwable $e) {
            log_message('warning', "[UnitKerjaService] Error searching pegawai_view: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Ambil nilai pertama yang tersedia dari $row berdasarkan daftar $candidateColumns.
     *
     * @param  array<string, mixed> $row
     * @param  string[]             $candidateColumns
     */
    private function extractUnitKerjaFromRow(array $row, array $candidateColumns): string
    {
        foreach ($candidateColumns as $col) {
            if (!empty($row[$col])) {
                return $row[$col];
            }
        }
        return '';
    }

    /**
     * Temukan kolom pertama dari $candidates yang ada di $availableFields.
     *
     * @param  string[] $availableFields
     * @param  string[] $candidates
     */
    private function findFirstMatch(array $availableFields, array $candidates): string
    {
        foreach ($candidates as $col) {
            if (in_array($col, $availableFields, true)) {
                return $col;
            }
        }
        return '';
    }
}
