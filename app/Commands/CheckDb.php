<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckDb extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'command:name';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = '';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'command:name [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    public function run(array $params)
    {
        $dbLokal = \Config\Database::connect(); // penomoran_db
        $dbMaster = \Config\Database::connect('kemenkopmk');

        try {
            // 1. Ambil data kandidat dari db Master
            $query = $dbMaster->table('users u')
                ->select('u.username_ldap, p.nama, uk.id as uk_id, uk.nama_unit_kerja, parent.id as parent_id, parent.nama_unit_kerja as parent_name')
                ->join('pegawai p', 'p.id = u.pegawai_id')
                ->join('unit_kerja uk', 'uk.id = p.unit_kerja_id')
                ->join('unit_kerja parent', 'parent.id = uk.parent_id', 'left')
                ->where('u.username_ldap !=', 'superadmin')
                ->where('p.status_pegawai', 1)
                ->groupStart()
                    ->like('uk.nama_unit_kerja', 'Tata Usaha')
                    ->orLike('uk.nama_unit_kerja', 'Biro Umum')
                    ->orLike('uk.nama_unit_kerja', 'Sekretariat')
                ->groupEnd()
                ->get()
                ->getResultArray();

            $countPusat = 0;
            $countUnit = 0;

            foreach ($query as $row) {
                $username = $row['username_ldap'];
                $unitName = strtolower($row['nama_unit_kerja']);
                $parentName = strtolower($row['parent_name'] ?? '');

                // Exception (Pengecualian) untuk theophanie.solin
                // Aturan user: dia adalah TU Unit (Biro Digitalisasi - ID 6)
                if ($username === 'theophanie.solin') {
                    $dbLokal->table('users')
                        ->where('username_ldap', $username)
                        ->update([
                            'role_id' => 3, // TU Unit
                            'unit_kerja_id' => 6 // Biro Digitalisasi dan Pengelolaan Informasi
                        ]);
                    $countUnit++;
                    echo "Update Exception [TU UNIT] : $username (Biro Digitalisasi)\n";
                    continue;
                }

                // Logika Penentuan TU Persuratan
                if (str_contains($unitName, 'biro umum') || str_contains($parentName, 'biro umum') || str_contains($unitName, 'tata usaha pimpinan') || str_contains($unitName, 'sekretariat kementerian')) {
                    $dbLokal->table('users')
                        ->where('username_ldap', $username)
                        ->update([
                            'role_id' => 4, // TU Persuratan
                            'unit_kerja_id' => null // TU Persuratan tidak diikat unit kerja tertentu
                        ]);
                    $countPusat++;
                    echo "Update [TU PERSURATAN] : $username\n";
                } 
                // Logika Penentuan TU Unit (Jika Parent adalah Deputi atau Biro)
                else if (str_contains($parentName, 'deputi') || str_contains($parentName, 'biro') || str_contains($parentName, 'inspektorat')) {
                    $dbLokal->table('users')
                        ->where('username_ldap', $username)
                        ->update([
                            'role_id' => 3, // TU Unit
                            'unit_kerja_id' => $row['parent_id'] // Mengikat ke Parent Unit (Misal ID Deputi)
                        ]);
                    $countUnit++;
                    echo "Update [TU UNIT]  : $username (Parent: {$row['parent_name']})\n";
                }
            }

            echo "\n=== PROSES SELESAI ===\n";
            echo "Total diupdate menjadi TU Persuratan: $countPusat\n";
            echo "Total diupdate menjadi TU Unit: $countUnit\n";

        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}
