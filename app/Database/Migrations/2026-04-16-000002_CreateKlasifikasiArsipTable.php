<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Membuat tabel referensi kode klasifikasi arsip.
 * Berdasarkan Permenko No. 2 Tahun 2023 — Matriks Klasifikasi Arsip.
 *
 * CATATAN KEAMANAN: Tabel ini BARU dan TIDAK menyentuh tabel yang sudah ada.
 * Semua data penomoran lama tetap utuh.
 */
class CreateKlasifikasiArsipTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'comment'    => 'Kode klasifikasi, contoh: KA.01.03',
            ],
            'kode_induk' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Kode induk/parent, contoh: KA.01 (induk dari KA.01.03)',
            ],
            'uraian' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Keterangan lengkap kode klasifikasi',
            ],
            'fungsi' => [
                'type'       => 'ENUM',
                'constraint' => ['fasilitatif', 'substantif'],
                'default'    => 'fasilitatif',
                'comment'    => 'fasilitatif=urusan internal, substantif=tugas pokok',
            ],
            'is_active' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode');
        $this->forge->addKey('kode_induk');

        $this->forge->createTable('klasifikasi_arsip', true);
    }

    public function down()
    {
        $this->forge->dropTable('klasifikasi_arsip', true);
    }
}
