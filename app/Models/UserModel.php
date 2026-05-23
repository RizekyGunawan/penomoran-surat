<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UserModel
 *
 * Mengelola data user sistem penomoran surat.
 * role_id = 1 → Admin
 * role_id = 2 → Pegawai (default)
 */
class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $useSoftDeletes = true;

    protected $afterInsert = ['logInsert'];
    protected $afterUpdate = ['logUpdate'];
    protected $afterDelete = ['logDelete'];

    protected function logInsert(array $data)
    {
        if (isset($data['id'])) {
            $audit = new \App\Models\AuditLogModel();
            $audit->logAction('CREATE', $this->table, (string) $data['id'], null, $data['data'] ?? []);
        }
        return $data;
    }

    protected function logUpdate(array $data)
    {
        if (isset($data['id'])) {
            $audit = new \App\Models\AuditLogModel();
            $id = is_array($data['id']) ? implode(',', $data['id']) : (string) $data['id'];
            $audit->logAction('UPDATE', $this->table, $id, null, $data['data'] ?? []);
        }
        return $data;
    }

    protected function logDelete(array $data)
    {
        if (isset($data['id'])) {
            $audit = new \App\Models\AuditLogModel();
            $id = is_array($data['id']) ? implode(',', $data['id']) : (string) $data['id'];
            $audit->logAction('DELETE', $this->table, $id);
        }
        return $data;
    }

    protected $allowedFields = [
        'pegawai_id',
        'unit_kerja_id',
        'username_ldap',
        'password',
        'role_id',
        'is_active',
        'username_m365',
        'deleted_at',
    ];

    protected $validationRules = [
        'username_ldap' => 'required|max_length[100]',
        'role_id' => 'required|in_list[1,2,3,4]',
        'is_active' => 'in_list[0,1]',
    ];

    protected $validationMessages = [
        'username_ldap' => [
            'required' => 'Username wajib diisi.',
            'max_length' => 'Username maksimal 100 karakter.',
        ],
        'role_id' => [
            'required' => 'Role wajib dipilih.',
            'in_list' => 'Role tidak valid.',
        ],
    ];

    /**
     * Label role untuk ditampilkan di UI.
     */
    public static function getRoleLabel(int $roleId): string
    {
        return match ($roleId) {
            1 => 'Admin',
            2 => 'Pegawai',
            3 => 'TU Unit',
            4 => 'TU Persuratan',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Ambil user terpaginasi.
     * Sort: Admin (role_id=1) dulu, lalu A-Z username_ldap.
     *
     * @param  int         $page    Halaman saat ini (mulai 1)
     * @param  int         $perPage Jumlah per halaman
     * @param  string|null $search  Filter username_ldap
     * @param  string      $tab     Kategori tab (pegawai, tu-unit, tu-persuratan, admin)
     * @return array{data: array, total: int, totalPages: int, currentPage: int, perPage: int}
     */
    public function getPaginated(int $page = 1, int $perPage = 20, ?string $search = null, string $tab = 'pegawai'): array
    {
        $builder = $this->builder();

        $builder->select('
            users.*, 
            uk.nama_unit_kerja as nama_unit_kerja_tu,
            master_users.unit_kerja_id as master_unit_kerja_id,
            master_pegawai.nama as nama_lengkap,
            master_pegawai.gelar_depan,
            master_pegawai.gelar_belakang
        ');
        $builder->join('kemenkopmk_db.unit_kerja uk', 'uk.id = users.unit_kerja_id', 'left');
        $builder->join('kemenkopmk_db.users master_users', 'master_users.username_ldap = users.username_ldap', 'left');
        $builder->join('kemenkopmk_db.pegawai master_pegawai', 'master_pegawai.id = master_users.pegawai_id', 'left');

        if ($tab === 'pegawai') {
            $builder->where('users.role_id', 2);
        } elseif ($tab === 'tu-unit') {
            $builder->where('users.role_id', 3);
            
            // Tambahkan kolom pengusul khusus tab ini
            $builder->select('
                pa.pengusul_nama,
                pa.pengusul_gelar_depan,
                pa.pengusul_gelar_belakang
            ');
            
            // Join untuk mencari siapa pengusulnya jika dia diangkat via pengajuan
            $builder->join('(
                SELECT 
                    p_akses.pegawai_user_id, 
                    mp.nama as pengusul_nama, 
                    mp.gelar_depan as pengusul_gelar_depan, 
                    mp.gelar_belakang as pengusul_gelar_belakang
                FROM pengajuan_akses p_akses
                JOIN users u_pengusul ON u_pengusul.id = p_akses.pengusul_user_id
                JOIN kemenkopmk_db.users ku_pengusul ON ku_pengusul.username_ldap = u_pengusul.username_ldap
                JOIN kemenkopmk_db.pegawai mp ON mp.id = ku_pengusul.pegawai_id
                WHERE p_akses.status = "APPROVED"
            ) as pa', 'pa.pegawai_user_id = users.id', 'left');
        } elseif ($tab === 'tu-persuratan') {
            $builder->where('users.role_id', 4);
        } elseif ($tab === 'admin') {
            $builder->where('users.role_id', 1);
        }

        if (!empty($search)) {
            $builder->like('users.username_ldap', $search);
        }

        $total = $builder->countAllResults(false);
        $totalPages = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        // Sort: Superadmin naik pertama, lalu Admin biasa, lalu A-Z
        $users = $builder
            ->orderBy("(CASE WHEN users.username_ldap = 'superadmin' THEN 0 ELSE 1 END)", 'ASC')
            ->orderBy('users.role_id', 'ASC')        // 1=Admin naik ke atas
            ->orderBy('users.username_ldap', 'ASC')  // lalu alfabet
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        // Dapatkan mapping dari child ke parent group label untuk tampilan yang lebih rapi (misal: "Deputi 1..." atau "Biro Umum...")
        $ukModel = new \App\Models\UnitKerjaModel();
        $groupedUk = $ukModel->getGroupedActive();
        $mapUk = [];
        foreach ($groupedUk as $g) {
            $mapUk[$g['id']] = $g['nama_unit_kerja'];
            if (isset($g['child_ids']) && is_array($g['child_ids'])) {
                foreach ($g['child_ids'] as $cid) {
                    $mapUk[$cid] = $g['nama_unit_kerja'];
                }
            }
        }

        $unitKerjaService = new \App\Services\UnitKerjaService();
        foreach ($users as &$user) {
            $user['role_label'] = self::getRoleLabel((int) ($user['role_id'] ?? 2));

            // Prioritaskan mendapatkan nama unit INDUK dari ID master DB (hierarki Kemenko PMK)
            $masterUnitId = (int) ($user['master_unit_kerja_id'] ?? 0);
            $namaInduk = '';
            
            if ($masterUnitId > 0) {
                $namaInduk = $unitKerjaService->getNamaUnitInduk($masterUnitId);
            }
            
            if (!empty($namaInduk)) {
                $user['nama_unit_kerja'] = $namaInduk;
            } else {
                // Fallback 1: Gunakan mapUk dari local DB jika unit_kerja_id ada
                $uid = $user['unit_kerja_id'] ?? null;
                if ($uid && isset($mapUk[$uid])) {
                    $user['nama_unit_kerja'] = $mapUk[$uid];
                } else {
                    // Fallback 2: Local nama_unit_kerja_tu
                    if (!empty($user['nama_unit_kerja_tu'])) {
                        $user['nama_unit_kerja'] = $user['nama_unit_kerja_tu'];
                    } else {
                        // Fallback Terakhir: Cari berdasarkan pegawai_id (akan menampilkan nama unit langsung, misal "Bagian TU")
                        $user['nama_unit_kerja'] = $unitKerjaService->getByPegawaiId($user['pegawai_id'] ?? null);
                    }
                }
            }
        }

        return [
            'data' => $users,
            'total' => $total,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'perPage' => $perPage,
        ];
    }

    /**
     * Toggle status is_active (0 ↔ 1) untuk user tertentu.
     *
     * @param  int $id
     * @return bool
     */
    public function toggleStatus(int $id): bool
    {
        $user = $this->find($id);
        if (!$user) {
            return false;
        }

        $newStatus = ((int) $user['is_active'] === 1) ? 0 : 1;

        return $this->update($id, ['is_active' => $newStatus]);
    }
}
