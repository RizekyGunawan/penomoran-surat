<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migrasi ini sengaja kosong/no-op karena tabel unit_kerja yang ada
 * sudah memiliki kolom yang cukup (nama_unit_kerja, parent_id, deleted_at, created_at, updated_at).
 * Tidak perlu menambah kolom baru — UnitKerjaModel sudah disesuaikan
 * dengan struktur tabel existing.
 */
class AddIsActiveToUnitKerja extends Migration
{
    public function up()
    {
        // No-op: structure already compatible with existing table
    }

    public function down()
    {
        // No-op
    }
}
