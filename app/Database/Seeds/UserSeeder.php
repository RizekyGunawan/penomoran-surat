<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 1. Matikan pengecekan active keys sesaat (foreign key checks) jika perlu
        // tapi di sini langsung insert / update (replace)
        $db = \Config\Database::connect();
        
        $data = [
            'username_ldap' => 'superadmin',
            'role_id'       => 1, // Admin
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        // Cari apakah akun 'superadmin' khusus sudah ada
        $existing = $db->table('users')->where('username_ldap', 'superadmin')->get()->getRow();

        if ($existing) {
            $db->table('users')->where('id', $existing->id)->update([
                'role_id'   => 1,
                'is_active' => 1,
                'updated_at'=> date('Y-m-d H:i:s'),
            ]);
            echo "Akun 'superadmin' berhasil diperbarui di database.\n";
        } else {
            $db->table('users')->insert($data);
            echo "Akun 'superadmin' khusus berhasil ditambahkan ke database.\n";
        }
        
        // Menghapus akun 'admin' lama jika ada
        $db->table('users')->where('username_ldap', 'admin')->where('pegawai_id', null)->delete();
    }
}
