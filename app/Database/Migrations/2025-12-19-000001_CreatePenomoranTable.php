<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePenomoranTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'NO' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'null'           => false,  // Bagian PRIMARY KEY — wajib NOT NULL
            ],
            'JENIS_DOKUMEN' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => false,
            ],
            'TANGGAL' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'UNIT_KERJA' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true,
            ],
            'PERIHAL' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'NAMA' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true,
            ],
            'TAHUN' => [
                'type'       => 'INT',
                'constraint' => 4,
                'null'       => false,  // Bagian PRIMARY KEY — wajib NOT NULL
                'default'    => 0,      // Nilai default agar data lama tetap aman
            ],
            'CREATED_AT' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'UPDATED_AT' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey(['NO', 'JENIS_DOKUMEN', 'TAHUN']);
        $this->forge->createTable('penomoran');
    }

    public function down()
    {
        $this->forge->dropTable('penomoran');
    }
}
