<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUnitKerjaIdToPenomoran extends Migration
{
    public function up()
    {
        $this->forge->addColumn('penomoran', [
            'unit_kerja_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'USER_ID',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('penomoran', 'unit_kerja_id');
    }
}
