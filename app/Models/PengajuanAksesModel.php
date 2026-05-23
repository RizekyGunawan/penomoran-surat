<?php

namespace App\Models;

use CodeIgniter\Model;

class PengajuanAksesModel extends Model
{
    protected $table            = 'pengajuan_akses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'pegawai_user_id',
        'pengusul_user_id',
        'usulan_role_id',
        'status',
        'alasan_usulan',
        'nota_dinas',       // Nama file nota dinas penugasan (PDF, opsional)
        'reviewer_user_id',
        'alasan_penolakan',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Cek apakah masih ada usulan PENDING untuk pegawai tertentu
     */
    public function cekPendingUsulan($pegawaiUserId)
    {
        return $this->where('pegawai_user_id', $pegawaiUserId)
                    ->where('status', 'PENDING')
                    ->first();
    }

    /**
     * Mendapatkan daftar pengajuan berdasarkan Kabag (Pengusul)
     * Ditambahkan nama pegawai dari database master
     */
    public function getDaftarPengajuanByPengusul($pengusulUserId)
    {
        return $this->select('pengajuan_akses.*, p.nama as nama_pegawai, p.gelar_depan, p.gelar_belakang')
            ->join('users u', 'u.id = pengajuan_akses.pegawai_user_id', 'left')
            ->join('kemenkopmk_db.users ku', 'ku.username_ldap = u.username_ldap', 'left')
            ->join('kemenkopmk_db.pegawai p', 'p.id = ku.pegawai_id', 'left')
            ->where('pengajuan_akses.pengusul_user_id', $pengusulUserId)
            ->orderBy('pengajuan_akses.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Mendapatkan daftar pengajuan yang menunggu persetujuan (Untuk TU Persuratan)
     */
    public function getDaftarPendingApproval()
    {
        return $this->select('
                pengajuan_akses.*, 
                p_target.nama as nama_pegawai, p_target.gelar_depan as target_gelar_depan, p_target.gelar_belakang as target_gelar_belakang,
                p_pengusul.nama as nama_pengusul, p_pengusul.gelar_depan as pengusul_gelar_depan, p_pengusul.gelar_belakang as pengusul_gelar_belakang,
                uk.nama_unit_kerja,
                ku_target.unit_kerja_id as target_unit_kerja_id
            ')
            // Join target pegawai
            ->join('users u_target', 'u_target.id = pengajuan_akses.pegawai_user_id', 'left')
            ->join('kemenkopmk_db.users ku_target', 'ku_target.username_ldap = u_target.username_ldap', 'left')
            ->join('kemenkopmk_db.pegawai p_target', 'p_target.id = ku_target.pegawai_id', 'left')
            // Join pengusul (Kabag)
            ->join('users u_pengusul', 'u_pengusul.id = pengajuan_akses.pengusul_user_id', 'left')
            ->join('kemenkopmk_db.users ku_pengusul', 'ku_pengusul.username_ldap = u_pengusul.username_ldap', 'left')
            ->join('kemenkopmk_db.pegawai p_pengusul', 'p_pengusul.id = ku_pengusul.pegawai_id', 'left')
            // Join unit kerja target - ambil dari database master (kemenkopmk_db)
            ->join('kemenkopmk_db.unit_kerja uk', 'uk.id = ku_target.unit_kerja_id', 'left')
            ->where('pengajuan_akses.status', 'PENDING')
            ->orderBy('pengajuan_akses.created_at', 'ASC')
            ->findAll();
    }

    /**
     * Mendapatkan daftar staf yang telah disetujui sebagai TU Unit (Aktif)
     */
    public function getDaftarTUUnitAktif()
    {
        return $this->select('
                pengajuan_akses.*, 
                p_target.nama as nama_pegawai, p_target.gelar_depan as target_gelar_depan, p_target.gelar_belakang as target_gelar_belakang,
                p_pengusul.nama as nama_pengusul, p_pengusul.gelar_depan as pengusul_gelar_depan, p_pengusul.gelar_belakang as pengusul_gelar_belakang,
                uk.nama_unit_kerja,
                ku_target.unit_kerja_id as target_unit_kerja_id,
                u_target.role_id as target_current_role
            ')
            ->join('users u_target', 'u_target.id = pengajuan_akses.pegawai_user_id', 'left')
            ->join('kemenkopmk_db.users ku_target', 'ku_target.username_ldap = u_target.username_ldap', 'left')
            ->join('kemenkopmk_db.pegawai p_target', 'p_target.id = ku_target.pegawai_id', 'left')
            ->join('users u_pengusul', 'u_pengusul.id = pengajuan_akses.pengusul_user_id', 'left')
            ->join('kemenkopmk_db.users ku_pengusul', 'ku_pengusul.username_ldap = u_pengusul.username_ldap', 'left')
            ->join('kemenkopmk_db.pegawai p_pengusul', 'p_pengusul.id = ku_pengusul.pegawai_id', 'left')
            ->join('kemenkopmk_db.unit_kerja uk', 'uk.id = ku_target.unit_kerja_id', 'left')
            ->where('pengajuan_akses.status', 'APPROVED')
            ->where('u_target.role_id', 3) // Hanya yang saat ini masih memegang role TU Unit
            ->orderBy('pengajuan_akses.updated_at', 'DESC')
            ->findAll();
    }
}
