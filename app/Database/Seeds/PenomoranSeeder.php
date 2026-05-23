<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\CLI\CLI;
use App\Services\UnitKerjaService;

/**
 * PenomoranSeeder
 *
 * Mengisi tabel penomoran dengan data dummy yang realistis.
 * Untuk setiap unit kerja yang memiliki user aktif, akan dibuatkan 10 surat.
 * Data pegawai, unit kerja, dan users diambil dari kemenkopmk_db (data asli).
 * Hasil seed disimpan ke penomoran_db (database utama penomoran).
 */
class PenomoranSeeder extends Seeder
{
    public function run()
    {
        // Koneksi ke database utama penomoran (untuk write)
        $dbPenomoran = \Config\Database::connect('default');

        // Koneksi ke database referensi (untuk baca users, pegawai, unit_kerja)
        $dbRef = \Config\Database::connect('kemenkopmk');

        CLI::write("Mengosongkan tabel penomoran...", "yellow");
        $dbPenomoran->table('penomoran')->truncate();

        // ===================================================
        // 1. Ambil data ASLI dari kemenkopmk_db
        // ===================================================

        // Ambil users aktif beserta nama pegawai & unit kerja
        $usersRaw = $dbRef->query("
            SELECT
                u.id              AS user_id,
                p.unit_kerja_id   AS unit_kerja_id,
                p.nama            AS nama_pegawai,
                uk.nama_unit_kerja AS unit_kerja_nama,
                uk.parent_id       AS parent_id
            FROM users u
            INNER JOIN pegawai  p  ON p.id   = u.pegawai_id
            INNER JOIN unit_kerja uk ON uk.id = p.unit_kerja_id
            WHERE u.deleted_at  IS NULL
              AND uk.deleted_at IS NULL
              AND u.is_active   = 1
              AND p.nama        IS NOT NULL
              AND p.unit_kerja_id IS NOT NULL
        ")->getResultArray();

        // Kelompokkan user berdasarkan unit kerja
        $usersByUnit = [];
        foreach ($usersRaw as $user) {
            $usersByUnit[$user['unit_kerja_id']][] = $user;
        }

        if (empty($usersByUnit)) {
            CLI::write("PERINGATAN: Tidak ada user dengan unit kerja valid. Menggunakan fallback.", "yellow");
            $usersByUnit[6] = [[
                'user_id' => 1,
                'unit_kerja_id' => 6,
                'nama_pegawai' => 'Admin Fallback',
                'unit_kerja_nama' => 'Biro Digitalisasi dan Pengelolaan Informasi',
                'parent_id' => 2
            ]];
        }

        CLI::write("Ditemukan " . count($usersByUnit) . " unit kerja dengan user aktif.", "cyan");

        // ===================================================
        // 2. Mapping kode jabatan dari UNIT_KODE_MAP
        // ===================================================

        $kodeMap = UnitKerjaService::UNIT_KODE_MAP;
        $deputiIds = [8, 9, 10, 11, 12];

        $getKode = function (int $unitId, int $parentId) use ($kodeMap, $deputiIds): string {
            if (isset($kodeMap[$unitId])) {
                return $kodeMap[$unitId];
            }
            if (isset($kodeMap[$parentId])) {
                return $kodeMap[$parentId];
            }
            return 'UMUM';
        };

        // ===================================================
        // 3. Data pendukung generate
        // ===================================================

        $jenisDokumenList = [
            'Surat Dinas',
            'Undangan Eksternal',
            'SPT',
            'Pengumuman',
            'Berita Acara',
            'Nota Dinas',
            'Surat Edaran',
            'Surat Kuasa',
        ];

        // Perihal acak yang realistis untuk instansi pemerintah
        $perihalList = [
            // Tata Usaha / Kepegawaian
            'Undangan Rapat Koordinasi Internal',
            'Permohonan Pemutakhiran Data Kepegawaian',
            'Penugasan Perjalanan Dinas Dalam Negeri',
            'Sertifikasi Kompetensi Teknis ASN',
            'Pemberitahuan Cuti Tahunan Pegawai',
            'Kenaikan Pangkat Reguler',
            
            // Pengawasan / Audit
            'Pemberitahuan Jadwal Audit Internal',
            'Tindak Lanjut Hasil Pemeriksaan BPK',
            'Laporan Hasil Pengawasan Semester',
            
            // Keuangan / BMN
            'Pelaporan Keuangan Triwulan',
            'Pengadaan Barang dan Inventaris Kantor',
            'Permintaan Persediaan Alat Tulis Kantor',
            'Revisi Daftar Isian Pelaksanaan Anggaran',
            
            // Perencanaan / Kebijakan
            'Sosialisasi Kebijakan Peraturan Terbaru',
            'Koordinasi Program Kerja Tahunan',
            'Monitoring dan Evaluasi Kinerja',
            'Penyusunan Rencana Strategis (Renstra)',
            'Penyusunan Laporan Akuntabilitas Kinerja (LAKIP)',
            
            // Teknis / Substantif (PMK)
            'Audiensi Bersama Stakeholder Eksternal',
            'Rapat Sinkronisasi Kebijakan Pengentasan Kemiskinan',
            'Evaluasi Program Penanganan Stunting',
            'Koordinasi Penanggulangan Bencana Daerah',
            'Peningkatan Kapasitas SDM Bidang Kesehatan',
            
            // Umum
            'Daftar Hadir dan Notulensi Rapat',
            'Forum Diskusi Perancangan Sistem Informasi',
            'Permohonan Peminjaman Ruang Rapat',
            'Pemeliharaan Jaringan dan Perangkat IT',
        ];

        $tndRules = [
            'Surat Dinas' => 'TU.00.01',
            'Undangan Eksternal' => 'HM.01',
            'SPT' => 'KP.01',
            'Pengumuman' => 'HM.02',
            'Berita Acara' => 'HK.03',
            'Nota Dinas' => 'TU.00.02',
            'Surat Edaran' => 'HM.02',
            'Surat Kuasa' => 'HK.04',
        ];

        $tndInternal = ['SPT', 'Nota Dinas'];
        $sifatList = ['B', 'B', 'B', 'B', 'T', 'R']; // 66% Biasa, 17% Terbatas, 17% Rahasia

        // ===================================================
        // 4. Generate 10 data per unit kerja
        // ===================================================

        $numberTracker = [];
        $batchData = [];
        $suratPerUnit = 10;
        $totalData = count($usersByUnit) * $suratPerUnit;

        CLI::write("Generating {$suratPerUnit} data penomoran per unit kerja (Total ~{$totalData})...", "yellow");

        foreach ($usersByUnit as $unitId => $usersInUnit) {
            for ($i = 0; $i < $suratPerUnit; $i++) {
                // Pilih user acak dari unit ini
                $user = $usersInUnit[array_rand($usersInUnit)];
                
                // Pilih jenis dokumen secara proporsional (SPT dan Nota Dinas lebih sering untuk internal)
                $jenis = $jenisDokumenList[array_rand($jenisDokumenList)];
                $tahun = 2026;

                $namaUnitKerja = $user['unit_kerja_nama'];
                $unitKerjaId = (int) $user['unit_kerja_id'];
                $parentId = (int) ($user['parent_id'] ?? 0);

                // Dapatkan kode jabatan
                $kodeJabatan = $getKode($unitKerjaId, $parentId);

                // Tanggal acak di 2026 (Jan - Apr)
                $month = str_pad(rand(1, 4), 2, '0', STR_PAD_LEFT);
                $day = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
                $hour = str_pad(rand(8, 16), 2, '0', STR_PAD_LEFT);
                $min = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);

                $tanggal = "{$tahun}-{$month}-{$day}";
                $createdAt = "{$tahun}-{$month}-{$day} {$hour}:{$min}:00";

                // Nomor urut per kombinasi jenis + tahun
                $trackerKey = "{$jenis}_{$tahun}";
                if (!isset($numberTracker[$trackerKey])) {
                    $numberTracker[$trackerKey] = 1;
                }
                $no = $numberTracker[$trackerKey]++;
                $noPadded = str_pad($no, 3, '0', STR_PAD_LEFT);

                // Kode klasifikasi & sifat surat
                $klasif = $tndRules[$jenis];
                $sifat = in_array($jenis, $tndInternal) ? 'B' : $sifatList[array_rand($sifatList)];

                // Format nomor surat lengkap
                if (in_array($jenis, $tndInternal)) {
                    // Internal: {No}/{KodeJabatan}/{KodeKlasifikasi}/{Bulan}/{Tahun}
                    $nomorLengkap = "{$noPadded}/{$kodeJabatan}/{$klasif}/{$month}/{$tahun}";
                } else {
                    // Eksternal: {Sifat}-{No}/{KodeKlasifikasi}/{Bulan}/{Tahun}
                    $nomorLengkap = "{$sifat}-{$noPadded}/{$klasif}/{$month}/{$tahun}";
                }

                // ~5% data berstatus DIBATALKAN
                $status = 'AKTIF';
                $alasanBatal = null;
                $tanggalBatal = null;
                $batalOleh = null;
                
                if (rand(1, 100) <= 5) {
                    $status = 'DIBATALKAN';
                    $alasanBatal = 'Revisi redaksional / Terdapat kesalahan pengetikan tanggal';
                    $tanggalBatal = date('Y-m-d H:i:s', strtotime($createdAt . ' + 1 days'));
                    $batalOleh = 1; // Admin (ID 1)
                }

                $perihal = $perihalList[array_rand($perihalList)] . ' Tahun ' . $tahun;

                $batchData[] = [
                    'NO' => $no,
                    'NOMOR_SURAT_LENGKAP' => $nomorLengkap,
                    'JENIS_DOKUMEN' => $jenis,
                    'TANGGAL' => $tanggal,
                    'UNIT_KERJA' => $namaUnitKerja,
                    'PERIHAL' => $perihal,
                    'NAMA' => $user['nama_pegawai'],
                    'USER_ID' => $user['user_id'],
                    'unit_kerja_id' => $unitKerjaId,
                    'TAHUN' => $tahun,
                    'STATUS' => $status,
                    'ALASAN_PEMBATALAN' => $alasanBatal,
                    'TANGGAL_PEMBATALAN' => $tanggalBatal,
                    'DIBATALKAN_OLEH' => $batalOleh,
                    'KODE_KLASIFIKASI' => $klasif,
                    'SIFAT_SURAT' => $sifat,
                    'KODE_JABATAN' => $kodeJabatan,
                    'CREATED_AT' => $createdAt,
                    'UPDATED_AT' => $createdAt,
                ];
            }
        }

        // Tulis semua data sekaligus (batch insert lebih cepat)
        if (!empty($batchData)) {
            $dbPenomoran->table('penomoran')->insertBatch($batchData);
        }

        CLI::write("✅ Selesai! " . count($batchData) . " data berhasil di-seed.", "green");
    }
}
