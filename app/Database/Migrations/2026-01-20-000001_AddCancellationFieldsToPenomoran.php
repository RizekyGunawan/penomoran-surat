<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCancellationFieldsToPenomoran extends Migration
{
    public function up()
    {
        $this->forge->addColumn('penomoran', [
            'STATUS' => [
                'type'       => 'ENUM',
                'constraint' => ['AKTIF', 'DIBATALKAN'],
                'default'    => 'AKTIF',
                'null'       => false,
                'after'      => 'TAHUN'
            ],
            'ALASAN_PEMBATALAN' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'STATUS'
            ],
            'TANGGAL_PEMBATALAN' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'ALASAN_PEMBATALAN'
            ],
            'DIBATALKAN_OLEH' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'TANGGAL_PEMBATALAN'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('penomoran', ['STATUS', 'ALASAN_PEMBATALAN', 'TANGGAL_PEMBATALAN', 'DIBATALKAN_OLEH']);
    }
}
