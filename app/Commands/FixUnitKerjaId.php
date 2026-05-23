<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class FixUnitKerjaId extends BaseCommand
{
    protected $group = 'Maintenance';
    protected $name = 'db:fix-unit-kerja-id';
    protected $description = 'Fix existing records where unit_kerja_id is NULL by finding unit kerja id dynamically by name_unit_kerja.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('penomoran');
        $query = $builder->where('unit_kerja_id IS NULL')->get();
        $records = $query->getResultArray();

        $updatedCount = 0;
        foreach ($records as $row) {
            $unitName = trim($row['UNIT_KERJA'] ?? '');
            if (!empty($unitName)) {
                $ukRow = $db->table('unit_kerja')->where('nama_unit_kerja', $unitName)->get()->getRowArray();
                if ($ukRow) {
                    $db->table('penomoran')
                        ->where('NO', $row['NO'])
                        ->where('JENIS_DOKUMEN', $row['JENIS_DOKUMEN'])
                        ->where('TAHUN', $row['TAHUN'])
                        ->update(['unit_kerja_id' => $ukRow['id']]);
                    $updatedCount++;
                }
            }
        }

        CLI::write("Migration completed. Successfully updated {$updatedCount} out of " . count($records) . " records.", 'green');
    }
}
