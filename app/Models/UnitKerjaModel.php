<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UnitKerjaModel
 *
 * Menyesuaikan dengan struktur tabel `unit_kerja` yang sudah ada:
 * kolom: id, nama_unit_kerja, parent_id, created_at, updated_at, deleted_at
 *
 * Tidak ada kolom `kode` atau `is_active` di tabel existing.
 */
class UnitKerjaModel extends Model
{
    protected $table = 'unit_kerja';
    protected $DBGroup = 'kemenkopmk';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;           // pakai deleted_at
    protected $deletedField = 'deleted_at';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

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

    // Hanya kolom yang aman untuk di-insert/update
    protected $allowedFields = ['nama_unit_kerja', 'parent_id'];

    protected $validationRules = [
        'nama_unit_kerja' => 'required|max_length[150]',
    ];

    protected $validationMessages = [
        'nama_unit_kerja' => ['required' => 'Nama unit kerja wajib diisi.'],
    ];

    // Pemetaan Alias Deputi (Hardcoded sesuai hasil analisis awal)
    protected $deputiAliases = [
        8 => 'Deputi 1 - Bidang Koordinasi Peningkatan Kualitas Keluarga dan Kependudukan',
        10 => 'Deputi 2 - Bidang Koordinasi Peningkatan Kualitas Kesehatan',
        11 => 'Deputi 3 - Bidang Koordinasi Peningkatan Kualitas Pendidikan',
        9 => 'Deputi 4 - Bidang Koordinasi Bidang Penguatan Karakter dan Jati Diri Bangsa',
        12 => 'Deputi 5 - Bidang Koordinasi Penanggulangan Bencana dan Konflik Sosial',
    ];

    /**
     * Mendapatkan daftar unit kerja yang sudah di-"Roll-Up".
     * Digunakan untuk dropdown filter agar rapi.
     */
    public function getGroupedActive(): array
    {
        $all = $this->select('id, nama_unit_kerja, parent_id')
            ->where('deleted_at', null)
            ->orderBy('id', 'ASC')
            ->findAll();

        $groups = [];
        $index = [];

        // Buat index pencarian
        foreach ($all as $u) {
            $index[$u['id']] = $u;
        }

        foreach ($all as $u) {
            $id = $u['id'];
            $parentId = $u['parent_id'];

            // Tentukan Induk Teratas (Top-Level Parent) yang valid untuk pengelompokan
            // Biarkan Biro / Deputi / Inspektorat berada di Top Level
            $topParentId = $id;

            // Cek apakah node ini anak dari sesuatu, jika ya, cari induk tertingginya (kecuali ID 1 dan 2)
            $currId = $id;
            while (isset($index[$currId]) && $index[$currId]['parent_id'] > 2) {
                $currId = $index[$currId]['parent_id'];
                $topParentId = $currId;
            }

            // Khusus: Jika top parent adalah Deputi (8-12), gunakan Alias
            $finalName = $index[$topParentId]['nama_unit_kerja'];
            if (isset($this->deputiAliases[$topParentId])) {
                $finalName = $this->deputiAliases[$topParentId];
            }
            // Opsional: perjelas nama Biro dll jika ini adalah induk utama
            else if ($topParentId == $id) {
                $finalName = $u['nama_unit_kerja'];
            }

            // Jika "Bagian Tata Usaha", beri konteks dengan nama parent-nya agar unik
            if (trim($u['nama_unit_kerja']) === 'Bagian Tata Usaha') {
                $parentName = isset($index[$parentId]) ? $index[$parentId]['nama_unit_kerja'] : 'Tidak Terdefinisi';
                // Jika parent-nya adalah Deputi, gunakan Aliasnya
                if (isset($this->deputiAliases[$parentId])) {
                    $parentName = $this->deputiAliases[$parentId];
                }
                $finalName = 'Tata Usaha - ' . $parentName;

                // Tata usaha biasanya tetap mau berdiri sendiri di dropdown atau digulung ke induk? 
                // Untuk dropdown (getAllActive/getGroupedActive), kita daftarkan dia sebagai item unik jika perlu.
            }

            // Simpan Group. Kita satukan berdasarkan nama finalName agar dropdown bersih
            if (!isset($groups[$topParentId])) {
                $groups[$topParentId] = [
                    'id' => $topParentId, // Represntatif id (Parent)
                    'nama_unit_kerja' => $finalName,
                    'child_ids' => [] // Menyimpan semua id yang tergabung
                ];
            }
            $groups[$topParentId]['child_ids'][] = $id;
        }

        // Kembalikan dalam bentuk flat array seperti findAll()
        $result = array_values($groups);
        usort($result, function ($a, $b) {
            return strcmp($a['nama_unit_kerja'], $b['nama_unit_kerja']);
        });

        return $result;
    }

    /**
     * Semua unit kerja (raw). Tetap ada karena dipakai di Admin Panel.
     *
     * @return array
     */
    public function getAllActive(): array
    {
        return $this->select('id, nama_unit_kerja, parent_id')
            ->orderBy('nama_unit_kerja', 'ASC')
            ->findAll();
    }

    /**
     * Statistik surat per unit kerja (dipakai TU Persuratan).
     * Melakukan "Roll-Up" level PHP agar Asisten Deputi / Subbag tergabung ke Induk (Biro / Deputi).
     *
     * @return array  [['unit_kerja_id', 'nama_unit', 'total', 'aktif', 'dibatalkan', 'child_ids']]
     */
    public function getRekapPerUnit(?int $tahun = null): array
    {
        $tahun = $tahun ?? (int) date('Y');

        // Tarik rekap mentah / raw
        $sql = "
            SELECT
                uk.id               AS unit_kerja_id,
                uk.nama_unit_kerja  AS nama_unit,
                uk.parent_id        AS parent_id,
                COUNT(p.NO)         AS total,
                SUM(CASE WHEN p.NO IS NOT NULL AND p.STATUS IN ('AKTIF','DITERBITKAN') THEN 1 ELSE 0 END) AS aktif,
                SUM(CASE WHEN p.NO IS NOT NULL AND p.STATUS = 'DIBATALKAN'             THEN 1 ELSE 0 END) AS dibatalkan
            FROM unit_kerja uk
            LEFT JOIN penomoran_db.penomoran p ON p.unit_kerja_id = uk.id AND p.TAHUN = ?
            WHERE uk.deleted_at IS NULL
            GROUP BY uk.id, uk.nama_unit_kerja, uk.parent_id
            ORDER BY uk.id ASC
        ";
        $rawStats = $this->db->query($sql, [$tahun])->getResultArray();

        // Bangun relasi parent-child
        $index = [];
        foreach ($rawStats as $row) {
            $index[$row['unit_kerja_id']] = $row;
        }

        // Agregasi
        $aggregated = [];

        foreach ($rawStats as $row) {
            $id = $row['unit_kerja_id'];
            $parentId = $row['parent_id'];

            // Cari induk agregasi (biasanya Biro atau Deputi, level di bawah Sekretariat/Kemenko)
            // Kemenko = 1, Sesmenko = 2
            $topParentId = $id;
            $currId = $id;
            while (isset($index[$currId]) && $index[$currId]['parent_id'] > 2) {
                $currId = $index[$currId]['parent_id'];
                $topParentId = $currId;
            }

            // Inisiasi group jika belum ada
            if (!isset($aggregated[$topParentId])) {
                $baseParentInfo = isset($index[$topParentId]) ? $index[$topParentId] : $row;
                $finalName = $baseParentInfo['nama_unit'];

                // Alias untuk Deputi 1-5
                if (isset($this->deputiAliases[$topParentId])) {
                    $finalName = $this->deputiAliases[$topParentId];
                }

                $aggregated[$topParentId] = [
                    'unit_kerja_id' => $topParentId,
                    'nama_unit' => $finalName,
                    'total' => 0,
                    'aktif' => 0,
                    'dibatalkan' => 0,
                    'child_ids' => [] // Menyimpan array ID agar tombol detail bisa filter semua anak
                ];
            }

            // Tambahkan statistik
            $aggregated[$topParentId]['total'] += $row['total'];
            $aggregated[$topParentId]['aktif'] += $row['aktif'];
            $aggregated[$topParentId]['dibatalkan'] += $row['dibatalkan'];
            $aggregated[$topParentId]['child_ids'][] = $id;
        }

        // Return array of objects, urut berdasarkan hierarki: Sekretariat -> Biro/Inspektorat -> Deputi
        $result = array_values($aggregated);
        usort($result, function ($a, $b) {
            $getPriority = function($id) {
                if ($id == 2) return 1; // Sekretariat
                if (in_array($id, [3, 4, 5, 6, 7, 17])) return 2; // Biro & Inspektorat
                if (in_array($id, [8, 9, 10, 11, 12])) return 3; // Deputi
                return 4; // Lainnya
            };

            $pA = $getPriority($a['unit_kerja_id']);
            $pB = $getPriority($b['unit_kerja_id']);

            if ($pA == $pB) {
                return strcmp($a['nama_unit'], $b['nama_unit']);
            }

            return $pA <=> $pB;
        });

        // Filter / hapus Kemenko (1) atau Sesmenko (2) jika mereka bernilai 0
        // agar tidak membingungkan (karena biasanya surat diterbitkan di level Biro/Deputi)
        // Serta menyembunyikan Staf Ahli (13, 14, 15, 16) dari dashboard
        $filtered = array_filter($result, function ($v) {
            if (in_array($v['unit_kerja_id'], [1, 2]) && $v['total'] == 0) {
                return false;
            }
            if (in_array($v['unit_kerja_id'], [13, 14, 15, 16])) {
                return false;
            }
            return true;
        });

        return array_values($filtered);
    }

    /**
     * Mencari ID unit kerja berdasarkan nama string secara spesifik
     * Kasus tidak sensitif pada huruf besar/kecil.
     * 
     * @param string $name
     * @return int|null
     */
    public function getIdByName(string $name): ?int
    {
        $name = trim($name);
        if (empty($name)) {
            return null;
        }

        // Cari berdasarkan nama (case-insensitive search di MySQL)
        $row = $this->where('nama_unit_kerja', $name)->first();

        return $row ? (int) $row['id'] : null;
    }

    /**
     * Mendapatkan grup/hirarki unit kerja berdasarkan ID salah satu bagiannya.
     * Berguna agar TU Unit dapat melihat semua surat di lingkup Induknya (misal: Inspektorat).
     */
    public function getHierarchyGroup(int $id): ?array
    {
        $grouped = $this->getGroupedActive();
        foreach ($grouped as $group) {
            if ($group['id'] == $id || (isset($group['child_ids']) && in_array($id, $group['child_ids']))) {
                return $group;
            }
        }
        return null;
    }
}
