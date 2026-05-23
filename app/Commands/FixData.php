<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class FixData extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'db:fix-tnd';
    protected $description = 'Memperbaiki NOMOR_SURAT_LENGKAP yang mengandung XX atau ...';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('penomoran');

        // Cari yang NOMOR_SURAT_LENGKAP mengandung XX atau ...
        $records = $builder->like('NOMOR_SURAT_LENGKAP', 'XX')
            ->orLike('NOMOR_SURAT_LENGKAP', '...')
            ->get()->getResultArray();

        if (empty($records)) {
            CLI::write("Tidak ada data dengan XX atau ... yang perlu diproses.", "green");
            return;
        }

        CLI::write("Ditemukan " . count($records) . " data berformat rusak (mengandung XX). Memulai perbaikan...", "yellow");

        $count = 0;
        foreach ($records as $row) {
            $noPadded = str_pad($row['NO'], 3, '0', STR_PAD_LEFT);
            $jenis = $row['JENIS_DOKUMEN'];
            $tahun = $row['TAHUN'];
            $bulan = date('m', strtotime($row['TANGGAL']));
            $klasif = $row['KODE_KLASIFIKASI'] ?: 'TU.00.01';
            $jabatan = $row['KODE_JABATAN'] ?: '-';
            $sifat = strtoupper($row['SIFAT_SURAT'] ?: 'B');

            // Nota Dinas dihapus dari sistem — hanya SPT yang menggunakan format kode jabatan
            $jenisTndInternal = ['SPT'];
            if (in_array($jenis, $jenisTndInternal)) {
                // Format internal: {No}/{KodeJabatan}/{KodeKlasifikasi}/{Bulan}/{Tahun}
                $newNomorLengkap = "{$noPadded}/{$jabatan}/{$klasif}/{$bulan}/{$tahun}";
            } else {
                // Format eksternal: {Sifat}-{No}/{KodeKlasifikasi}/{Bulan}/{Tahun}
                $newNomorLengkap = "{$sifat}-{$noPadded}/{$klasif}/{$bulan}/{$tahun}";
            }

            $db->table('penomoran')
                ->where('NO', $row['NO'])
                ->where('JENIS_DOKUMEN', $row['JENIS_DOKUMEN'])
                ->where('TAHUN', $row['TAHUN'])
                ->update(['NOMOR_SURAT_LENGKAP' => $newNomorLengkap]);

            $count++;
        }

        CLI::write("Selesai! Berhasil memperbaiki {$count} format nomor surat lengkap.", "green");
    }
}
