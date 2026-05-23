<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNomorAwalConfigTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'jenis_dokumen' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'tahun' => [
                'type'       => 'INT',
                'constraint' => 4,
            ],
            'nomor_awal' => [
                'type'       => 'INT',
                'unsigned'   => true,
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

        $this->forge->addPrimaryKey('id');
        // Kombinasi jenis_dokumen dan tahun harus unik
        $this->forge->addUniqueKey(['jenis_dokumen', 'tahun']);
        
        $this->forge->createTable('nomor_awal_config');
    }

    public function down()
    {
        $this->forge->dropTable('nomor_awal_config');
    }
}
