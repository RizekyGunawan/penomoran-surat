<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Command untuk mengecek data master:
 * - Unit Kerja dari database kemenkopmk
 * - Users yang ada
 * - Struktur tabel penomoran
 */
class CekDataMaster extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'app:cek-master';
    protected $description = 'Menampilkan data master unit_kerja dan users untuk keperluan seeder';
    protected $usage       = 'app:cek-master [--limit=50]';
    protected $options     = [
        '--limit' => 'Batas jumlah data yang ditampilkan (default: 50)',
    ];

    public function run(array $params)
    {
        $dbLokal  = \Config\Database::connect();          // penomoran_db
        $dbMaster = \Config\Database::connect('kemenkopmk'); // kemenkopmk

        $limit = (int) CLI::getOption('limit') ?: 50;

        // === 1. Tampilkan Data Unit Kerja ===
        CLI::write("\n=== DATA UNIT KERJA (kemenkopmk.unit_kerja) ===", 'green');
        try {
            $unitKerja = $dbMaster->table('unit_kerja')
                ->select('id, nama_unit_kerja, parent_id')
                ->where('deleted_at IS NULL')
                ->orderBy('id', 'ASC')
                ->limit($limit)
                ->get()
                ->getResultArray();

            CLI::write(str_pad('ID', 6) . str_pad('PARENT', 8) . 'NAMA UNIT KERJA');
            CLI::write(str_repeat('-', 80));
            foreach ($unitKerja as $uk) {
                CLI::write(
                    str_pad($uk['id'], 6) .
                    str_pad($uk['parent_id'] ?? '-', 8) .
                    $uk['nama_unit_kerja']
                );
            }
        } catch (\Exception $e) {
            CLI::error("Gagal ambil unit_kerja: " . $e->getMessage());
        }

        // === 2. Tampilkan Users dari tabel lokal ===
        CLI::write("\n=== USERS LOKAL (penomoran_db.users) ===", 'green');
        try {
            $users = $dbLokal->table('users')
                ->select('id, username_ldap, role_id, unit_kerja_id')
                ->orderBy('id', 'ASC')
                ->limit(20)
                ->get()
                ->getResultArray();

            CLI::write(str_pad('ID', 6) . str_pad('ROLE', 8) . str_pad('UK_ID', 8) . 'USERNAME');
            CLI::write(str_repeat('-', 80));
            foreach ($users as $u) {
                CLI::write(
                    str_pad($u['id'], 6) .
                    str_pad($u['role_id'], 8) .
                    str_pad($u['unit_kerja_id'] ?? '-', 8) .
                    $u['username_ldap']
                );
            }
        } catch (\Exception $e) {
            CLI::error("Gagal ambil users: " . $e->getMessage());
        }

        // === 3. Tampilkan data penomoran yang sudah ada ===
        CLI::write("\n=== DATA PENOMORAN YANG SUDAH ADA ===", 'green');
        try {
            $existingData = $dbLokal->table('penomoran')
                ->select('NO, JENIS_DOKUMEN, TAHUN, UNIT_KERJA, PERIHAL, STATUS')
                ->orderBy('TAHUN', 'DESC')
                ->orderBy('NO', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();

            CLI::write("Total baris: " . $dbLokal->table('penomoran')->countAllResults());
            foreach ($existingData as $p) {
                CLI::write(
                    "#{$p['NO']} | {$p['JENIS_DOKUMEN']} | {$p['TAHUN']} | {$p['UNIT_KERJA']} | {$p['STATUS']}"
                );
                CLI::write("  -> " . $p['PERIHAL']);
            }
        } catch (\Exception $e) {
            CLI::error("Gagal ambil penomoran: " . $e->getMessage());
        }

        // === 4. Tampilkan struktur tabel penomoran ===
        CLI::write("\n=== STRUKTUR TABEL penomoran ===", 'green');
        try {
            $fields = $dbLokal->getFieldData('penomoran');
            foreach ($fields as $field) {
                CLI::write(
                    str_pad($field->name, 30) .
                    str_pad($field->type, 20) .
                    ($field->nullable ? 'NULL' : 'NOT NULL')
                );
            }
        } catch (\Exception $e) {
            CLI::error("Gagal ambil struktur: " . $e->getMessage());
        }
    }
}
