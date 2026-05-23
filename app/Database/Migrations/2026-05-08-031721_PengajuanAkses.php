<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PengajuanAkses extends Migration
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
            'pegawai_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'pengusul_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'usulan_role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 3, // Default 3 = TU Unit
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'PENDING', // PENDING, APPROVED, REJECTED
            ],
            'alasan_usulan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'reviewer_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'alasan_penolakan' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('pegawai_user_id');
        $this->forge->addKey('pengusul_user_id');
        $this->forge->addKey('status');
        
        // Memastikan tabel dibuat di penomoran_db
        $this->forge->createTable('pengajuan_akses');
    }

    public function down()
    {
        $this->forge->dropTable('pengajuan_akses');
    }
}
