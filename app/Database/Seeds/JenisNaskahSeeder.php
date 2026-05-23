<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JenisNaskahSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['nama_jenis' => 'Surat Dinas'],
            ['nama_jenis' => 'Undangan Eksternal'],
            ['nama_jenis' => 'SPT'],
            ['nama_jenis' => 'Surat Kuasa'],
            ['nama_jenis' => 'Surat Keterangan'],
            ['nama_jenis' => 'Berita Acara'],
            ['nama_jenis' => 'Sertifikat'],
            ['nama_jenis' => 'Pengumuman'],
            ['nama_jenis' => 'Surat Edaran'],
            // Historical (Tidak Aktif)
            ['nama_jenis' => 'Nota Dinas', 'is_active' => 0]
        ];

        // Add timestamps and default is_active
        foreach ($data as &$row) {
            if (!isset($row['is_active'])) {
                $row['is_active'] = 1;
            }
            $row['created_at'] = date('Y-m-d H:i:s');
            $row['updated_at'] = date('Y-m-d H:i:s');
        }

        $this->db->table('jenis_naskah')->insertBatch($data);
    }
}
