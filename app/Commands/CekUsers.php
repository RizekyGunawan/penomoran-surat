<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Command untuk mengecek lebih lengkap: users dengan role dan UK
 */
class CekUsers extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'app:cek-users';
    protected $description = 'Menampilkan semua users lokal dengan role dan unit kerja';

    public function run(array $params)
    {
        $dbLokal = \Config\Database::connect();

        CLI::write("\n=== SEMUA USERS LOKAL (penomoran_db.users) ===", 'green');
        $users = $dbLokal->table('users')
            ->select('id, username_ldap, role_id, unit_kerja_id')
            ->where('role_id !=', 1) // Bukan admin/superadmin
            ->orderBy('unit_kerja_id', 'ASC')
            ->get()
            ->getResultArray();

        CLI::write("Total users (non-admin): " . count($users));
        CLI::write(str_pad('ID', 6) . str_pad('ROLE', 8) . str_pad('UK_ID', 8) . 'USERNAME');
        CLI::write(str_repeat('-', 70));
        foreach ($users as $u) {
            CLI::write(
                str_pad($u['id'], 6) .
                str_pad($u['role_id'], 8) .
                str_pad($u['unit_kerja_id'] ?? '-', 8) .
                $u['username_ldap']
            );
        }

        // Hitung berdasarkan role
        CLI::write("\n=== RINGKASAN ROLE ===", 'yellow');
        $roles = $dbLokal->table('users')
            ->select('role_id, COUNT(*) as jumlah')
            ->groupBy('role_id')
            ->get()
            ->getResultArray();
        foreach ($roles as $r) {
            $namaRole = match((int)$r['role_id']) {
                1 => 'Admin/Superadmin',
                2 => 'Pegawai',
                3 => 'TU Unit',
                4 => 'TU Persuratan',
                default => 'Tidak diketahui'
            };
            CLI::write("Role {$r['role_id']} ({$namaRole}): {$r['jumlah']} users");
        }
    }
}
