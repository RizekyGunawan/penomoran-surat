<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Standardisasi nilai kolom STATUS pada tabel penomoran.
 *
 * Masalah:
 *   Sebelumnya kolom STATUS bertipe VARCHAR dan berisi nilai yang tidak konsisten:
 *   - 'AKTIF'      → nilai standar yang benar
 *   - 'DITERBITKAN' → terminologi lama dari versi aplikasi sebelumnya (sama artinya dengan AKTIF)
 *   - NULL          → tidak pernah diisi saat INSERT awal
 *
 * Solusi:
 *   1. Samakan semua nilai yang berarti "aktif" menjadi 'AKTIF'
 *   2. Ubah tipe kolom menjadi ENUM agar nilai tidak valid tidak bisa masuk lagi
 */
class StandardisasiStatusSurat extends Migration
{
    public function up()
    {
        // Langkah 1: Ubah NULL menjadi 'AKTIF'
        // (NULL artinya surat belum pernah dibatalkan = masih aktif)
        $this->db->query("
            UPDATE penomoran
            SET STATUS = 'AKTIF'
            WHERE STATUS IS NULL
        ");

        // Langkah 2: Ubah 'DITERBITKAN' menjadi 'AKTIF'
        // (DITERBITKAN adalah terminologi lama yang bermakna sama dengan AKTIF)
        $this->db->query("
            UPDATE penomoran
            SET STATUS = 'AKTIF'
            WHERE STATUS = 'DITERBITKAN'
        ");

        // Langkah 3: Ubah tipe kolom menjadi ENUM agar nilai terjaga
        // Default 'AKTIF' agar INSERT baru tanpa STATUS tetap aman
        $this->forge->modifyColumn('penomoran', [
            'STATUS' => [
                'name'       => 'STATUS',
                'type'       => 'ENUM',
                'constraint' => ['AKTIF', 'DIBATALKAN'],
                'default'    => 'AKTIF',
                'null'       => false,
            ],
        ]);
    }

    public function down()
    {
        // Kembalikan ke VARCHAR jika perlu rollback
        $this->forge->modifyColumn('penomoran', [
            'STATUS' => [
                'name'       => 'STATUS',
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
        ]);
    }
}
