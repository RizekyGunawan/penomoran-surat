<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Menambahkan kolom-kolom wajib sesuai Tata Naskah Dinas (TND)
 * berdasarkan Permenko No. 1 Tahun 2024.
 *
 * Kolom baru:
 * - KODE_KLASIFIKASI : Kode klasifikasi arsip (contoh: KA.01, TU.00.01)
 * - SIFAT_SURAT      : Klasifikasi keamanan (SR / R / T / B)
 * - KODE_JABATAN     : Singkatan unit/jabatan (contoh: ROUM, BIRO1) —
 *                      dipakai pada format nomor SPT & Nota Dinas
 */
class AddTndFieldsToPenomoran extends Migration
{
    public function up()
    {
        $this->forge->addColumn('penomoran', [
            'KODE_KLASIFIKASI' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'after'      => 'unit_kerja_id',  // Setelah kolom yang pasti sudah ada
                'comment'    => 'Kode klasifikasi arsip sesuai TND, contoh: KA.01',
            ],
            'SIFAT_SURAT' => [
                'type'       => 'VARCHAR',
                'constraint' => 5,
                'null'       => true,
                'default'    => 'B',
                'after'      => 'KODE_KLASIFIKASI',
                'comment'    => 'Klasifikasi keamanan: SR=Sangat Rahasia, R=Rahasia, T=Terbatas, B=Biasa',
            ],
            'KODE_JABATAN' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'after'      => 'SIFAT_SURAT',
                'comment'    => 'Kode singkat unit/jabatan, digunakan pada format SPT & Nota Dinas',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('penomoran', ['KODE_KLASIFIKASI', 'SIFAT_SURAT', 'KODE_JABATAN']);
    }
}
