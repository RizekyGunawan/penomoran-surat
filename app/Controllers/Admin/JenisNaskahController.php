<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\JenisNaskahModel;

class JenisNaskahController extends BaseController
{
    protected $jenisNaskahModel;

    public function __construct()
    {
        $this->jenisNaskahModel = new JenisNaskahModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Kelola Jenis Naskah',
            'jenis_naskah' => $this->jenisNaskahModel->orderBy('nama_jenis', 'ASC')->findAll(),
        ];

        return view('admin/jenis_naskah/index', $data);
    }

    public function store()
    {
        if (!$this->validate($this->jenisNaskahModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors()['nama_jenis']);
        }

        $this->jenisNaskahModel->save([
            'nama_jenis' => $this->request->getPost('nama_jenis'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ]);

        return redirect()->to('/admin/jenis-naskah')->with('success', 'Jenis Naskah berhasil ditambahkan.');
    }

    public function update($id)
    {
        $rules = [
            'nama_jenis' => "required|max_length[100]|is_unique[jenis_naskah.nama_jenis,id,{$id}]",
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors()['nama_jenis'] ?? 'Validasi gagal.');
        }

        $this->jenisNaskahModel->save([
            'id' => $id,
            'nama_jenis' => $this->request->getPost('nama_jenis'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ]);

        return redirect()->to('/admin/jenis-naskah')->with('success', 'Jenis Naskah berhasil diperbarui.');
    }

    public function toggle($id)
    {
        $jenis = $this->jenisNaskahModel->find($id);
        if ($jenis) {
            $newStatus = $jenis['is_active'] ? 0 : 1;
            $this->jenisNaskahModel->save([
                'id' => $id,
                'is_active' => $newStatus
            ]);
            $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->to('/admin/jenis-naskah')->with('success', "Jenis Naskah berhasil $statusText.");
        }
        return redirect()->to('/admin/jenis-naskah')->with('error', 'Data tidak ditemukan.');
    }
}
