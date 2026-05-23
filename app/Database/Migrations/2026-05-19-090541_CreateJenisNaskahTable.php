<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJenisNaskahTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_jenis' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => false,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('jenis_naskah');
    }

    public function down()
    {
        $this->forge->dropTable('jenis_naskah');
    }
}
