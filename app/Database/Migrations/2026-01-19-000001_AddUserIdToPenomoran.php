<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserIdToPenomoran extends Migration
{
    public function up()
    {
        // 1. Tambah kolom USER_ID
        $this->forge->addColumn('penomoran', [
            'USER_ID' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,  // NULL untuk data existing
                'after'      => 'NAMA',
            ],
        ]);

        // 2. Tambah index untuk performa query
        $this->db->query('CREATE INDEX idx_user_id ON penomoran(USER_ID)');

        // CATATAN: Foreign Key ke tabel `users` TIDAK dibuat di sini.
        // Tabel `users` berada di database terpisah (kemenkopmk_db),
        // dan MySQL tidak mendukung cross-database foreign key constraint.
        // Integritas relasi dijaga di level aplikasi (PHP/CodeIgniter),
        // bukan di level database.
    }

    public function down()
    {
        // Hapus index lalu hapus kolom (tidak ada FK yang perlu di-drop)
        $this->db->query('DROP INDEX idx_user_id ON penomoran');
        $this->forge->dropColumn('penomoran', 'USER_ID');
    }
}
