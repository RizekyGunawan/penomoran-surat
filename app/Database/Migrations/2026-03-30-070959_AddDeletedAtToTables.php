<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Menambahkan kolom deleted_at ke tabel yang membutuhkan soft delete.
 *
 * CATATAN: Tabel `users` dan `unit_kerja` berada di database eksternal
 * (kemenkopmk_db). Migration ini hanya meng-skip bagian tersebut
 * karena kolom deleted_at sudah ada di server lama.
 */
class AddDeletedAtToTables extends Migration
{
    public function up()
    {
        $fields = [
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        // Skip tabel `users` dan `unit_kerja` — keduanya ada di kemenkopmk_db (database eksternal)
        // dan sudah memiliki kolom deleted_at di sana.

        // Tambahkan deleted_at ke tabel lain di penomoran_db jika ada kebutuhan ke depan
        // (saat ini tidak ada tabel tambahan yang memerlukan soft delete di sini)
    }

    public function down()
    {
        // No-op: tidak ada perubahan yang dilakukan di up()
    }
}
