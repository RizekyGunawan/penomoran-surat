<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * NomorAwalModel
 *
 * Mengelola data konfigurasi nomor awal per jenis dokumen dan tahun.
 */
class NomorAwalModel extends Model
{
    protected $table            = 'nomor_awal_config';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'jenis_dokumen',
        'tahun',
        'nomor_awal',
    ];

    /**
     * Dapatkan nomor awal untuk jenis dokumen dan tahun tertentu.
     * Mengembalikan default 1 jika tidak ada konfigurasi.
     *
     * @param string $jenisDokumen
     * @param int|string $tahun
     * @return int
     */
    public function getNomorAwal(string $jenisDokumen, $tahun): int
    {
        $config = $this->where('jenis_dokumen', $jenisDokumen)
                       ->where('tahun', (int) $tahun)
                       ->first();

        return $config ? (int) $config['nomor_awal'] : 1;
    }

    /**
     * Ambil semua konfigurasi.
     */
    public function getAll(): array
    {
        return $this->orderBy('tahun', 'DESC')
                    ->orderBy('jenis_dokumen', 'ASC')
                    ->findAll();
    }

    /**
     * Simpan atau update konfigurasi.
     * Karena kombinasi (jenis_dokumen + tahun) harus unik, lakukan upsert manual.
     */
    public function setNomorAwal(string $jenisDokumen, int $tahun, int $nomorAwal): bool
    {
        $existing = $this->where('jenis_dokumen', $jenisDokumen)
                         ->where('tahun', $tahun)
                         ->first();

        if ($existing) {
            return $this->update($existing['id'], ['nomor_awal' => $nomorAwal]);
        }

        return $this->insert([
            'jenis_dokumen' => $jenisDokumen,
            'tahun'         => $tahun,
            'nomor_awal'    => $nomorAwal,
        ], false) !== false;
    }
}
