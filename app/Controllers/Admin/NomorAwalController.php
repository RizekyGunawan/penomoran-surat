<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NomorAwalModel;
use App\Models\PenomoranModel;

class NomorAwalController extends BaseController
{
    protected NomorAwalModel $nomorAwalModel;

    public function __construct()
    {
        $this->nomorAwalModel = new NomorAwalModel();
    }

    /**
     * GET /admin/nomor-awal
     * Tampilkan daftar konfigurasi nomor awal.
     */
    public function index()
    {
        // Daftar semua konfigurasi
        $configs = $this->nomorAwalModel->getAll();

        // Daftar dropdown Jenis Dokumen diambil dari PenomoranModel agar tersentralisasi
        $penomoranModel   = new PenomoranModel();
        $jenisDokumenList = $penomoranModel->getJenisDokumenList();

        $data = [
            'title'            => 'Admin - Konfigurasi Nomor Awal',
            'configs'          => $configs,
            'jenisDokumenList' => $jenisDokumenList,
            'currentYear'      => date('Y')
        ];

        return view('admin/nomor_awal/index', $data);
    }

    /**
     * POST /admin/nomor-awal/store
     * Simpan/Upsert konfigurasi.
     */
    public function store()
    {
        $rules = [
            'jenis_dokumen' => 'required|max_length[100]',
            'tahun'         => 'required|integer|exact_length[4]',
            'nomor_awal'    => 'required|integer|greater_than_equal_to[1]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                             ->withInput()
                             ->with('modal_errors', $this->validator->getErrors())
                             ->with('modal_old', $this->request->getPost());
        }

        $jenisDokumen = $this->request->getPost('jenis_dokumen');
        $tahun        = (int) $this->request->getPost('tahun');
        $nomorAwal    = (int) $this->request->getPost('nomor_awal');

        // Upsert manual di model
        $berhasil = $this->nomorAwalModel->setNomorAwal($jenisDokumen, $tahun, $nomorAwal);

        if ($berhasil) {
            return redirect()->to('/admin/nomor-awal')
                             ->with('success', "Konfigurasi nomor awal untuk {$jenisDokumen} tahun {$tahun} berhasil disimpan.");
        }

        return redirect()->back()
                         ->withInput()
                         ->with('error', 'Gagal menyimpan konfigurasi.');
    }

    /**
     * POST /admin/nomor-awal/delete/(:num)
     * Hapus konfigurasi tertentu.
     */
    public function delete(int $id)
    {
        $config = $this->nomorAwalModel->find($id);

        if (! $config) {
            return redirect()->to('/admin/nomor-awal')
                             ->with('error', 'Konfigurasi tidak ditemukan.');
        }

        $this->nomorAwalModel->delete($id);

        return redirect()->to('/admin/nomor-awal')
                         ->with('success', "Konfigurasi untuk {$config['jenis_dokumen']} tahun {$config['tahun']} berhasil dihapus (kembali ke default).");
    }
}
