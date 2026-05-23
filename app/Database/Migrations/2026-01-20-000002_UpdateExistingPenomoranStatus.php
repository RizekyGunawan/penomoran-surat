<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateExistingPenomoranStatus extends Migration
{
    public function up()
    {
        // Update all existing records where STATUS is NULL or empty to 'AKTIF'
        $this->db->query("UPDATE penomoran SET STATUS = 'AKTIF' WHERE STATUS IS NULL OR STATUS = ''");
        
        echo "Updated existing penomoran records to STATUS = 'AKTIF'\n";
    }

    public function down()
    {
        // No rollback needed - this is a data migration
        echo "No rollback for data migration\n";
    }
}
