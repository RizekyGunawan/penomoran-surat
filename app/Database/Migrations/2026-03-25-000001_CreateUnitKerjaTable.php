<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUnitKerjaTable extends Migration
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
            'kode' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => false,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],
            'is_active' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('kode');
        $this->forge->createTable('unit_kerja', true); // true = IF NOT EXISTS
    }

    public function down()
    {
        $this->forge->dropTable('unit_kerja', true);
    }
}
