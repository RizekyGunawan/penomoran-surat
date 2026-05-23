<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KlasifikasiArsipModel;

class KlasifikasiArsipController extends BaseController
{
    protected $klasifikasiArsipModel;

    public function __construct()
    {
        $this->klasifikasiArsipModel = new KlasifikasiArsipModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Kelola Kode Klasifikasi Arsip',
            // Sort by kode ASC for better readability
            'klasifikasi_arsip' => $this->klasifikasiArsipModel->orderBy('kode', 'ASC')->findAll(),
        ];

        return view('admin/klasifikasi_arsip/index', $data);
    }

    public function store()
    {
        if (!$this->validate($this->klasifikasiArsipModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors()['kode'] ?? 'Pastikan form diisi dengan benar.');
        }

        $this->klasifikasiArsipModel->save([
            'kode' => $this->request->getPost('kode'),
            'kode_induk' => $this->request->getPost('kode_induk') ?: null,
            'uraian' => $this->request->getPost('uraian'),
            'fungsi' => $this->request->getPost('fungsi'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ]);

        return redirect()->to('/admin/klasifikasi-arsip')->with('success', 'Kode klasifikasi berhasil ditambahkan.');
    }

    public function update($id)
    {
        $rules = [
            'kode' => "required|max_length[20]|is_unique[klasifikasi_arsip.kode,id,{$id}]",
            'fungsi' => 'required|in_list[fasilitatif,substantif]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors()['kode'] ?? 'Validasi gagal.');
        }

        $this->klasifikasiArsipModel->save([
            'id' => $id,
            'kode' => $this->request->getPost('kode'),
            'kode_induk' => $this->request->getPost('kode_induk') ?: null,
            'uraian' => $this->request->getPost('uraian'),
            'fungsi' => $this->request->getPost('fungsi'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ]);

        return redirect()->to('/admin/klasifikasi-arsip')->with('success', 'Kode klasifikasi berhasil diperbarui.');
    }

    public function toggle($id)
    {
        $klasifikasi = $this->klasifikasiArsipModel->find($id);
        if ($klasifikasi) {
            $newStatus = $klasifikasi['is_active'] ? 0 : 1;
            $this->klasifikasiArsipModel->save([
                'id' => $id,
                'is_active' => $newStatus
            ]);
            $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->to('/admin/klasifikasi-arsip')->with('success', "Kode klasifikasi berhasil $statusText.");
        }
        return redirect()->to('/admin/klasifikasi-arsip')->with('error', 'Data tidak ditemukan.');
    }
}
