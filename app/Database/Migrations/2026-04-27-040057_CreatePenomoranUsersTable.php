<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePenomoranUsersTable extends Migration
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
            'pegawai_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'unit_kerja_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'       => true,
                'null'       => true,
            ],
            'username_ldap' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 2, // 2 = Pegawai
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
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('users', true);
    }

    public function down()
    {
        $this->forge->dropTable('users', true);
    }
}
