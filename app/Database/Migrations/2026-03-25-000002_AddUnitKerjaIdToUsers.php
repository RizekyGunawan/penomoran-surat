<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * CATATAN: Migration ini di-skip (no-op) karena tabel `users`
 * berada di database terpisah (kemenkopmk_db), bukan di penomoran_db.
 * Kolom `unit_kerja_id` sudah ada di tabel users di kemenkopmk_db.
 * Modifikasi tabel lintas database tidak didukung oleh migration runner CI4.
 */
class AddUnitKerjaIdToUsers extends Migration
{
    public function up()
    {
        // No-op: tabel `users` ada di kemenkopmk_db (database eksternal).
        // Tidak dapat dimodifikasi dari migration penomoran_db ini.
    }

    public function down()
    {
        // No-op: tidak ada yang perlu di-rollback.
    }
}
