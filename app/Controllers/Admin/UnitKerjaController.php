<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UnitKerjaModel;

/**
 * UnitKerjaController (Admin)
 *
 * CRUD master unit kerja.
 * Semua route dilindungi AdminFilter (role_id = 1).
 */
class UnitKerjaController extends BaseController
{
    protected UnitKerjaModel $unitKerjaModel;

    public function __construct()
    {
        $this->unitKerjaModel = new UnitKerjaModel();
    }

    /**
     * GET /admin/unit-kerja
     */
    public function index(): string
    {
        $unitKerjaList = $this->unitKerjaModel
                              ->orderBy('nama_unit_kerja', 'ASC')
                              ->findAll();

        return view('admin/unit_kerja/index', [
            'title'         => 'Admin - Kelola Unit Kerja',
            'unitKerjaList' => $unitKerjaList,
        ]);
    }

    /**
     * POST /admin/unit-kerja/store
     */
    public function store()
    {
        $rules = [
            'nama_unit_kerja' => 'required|max_length[150]|is_unique[unit_kerja.nama_unit_kerja]',
        ];

        $messages = [
            'nama_unit_kerja' => ['is_unique' => 'Nama unit kerja sudah ada.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->to('/admin/unit-kerja')
                             ->with('modal_errors', $this->validator->getErrors())
                             ->with('modal_old', $this->request->getPost());
        }

        $this->unitKerjaModel->skipValidation(true)->insert([
            'nama_unit_kerja' => trim($this->request->getPost('nama_unit_kerja')),
            'parent_id'       => (int) ($this->request->getPost('parent_id') ?: 0),
        ]);

        return redirect()->to('/admin/unit-kerja')
                         ->with('success', 'Unit kerja berhasil ditambahkan.');
    }

    /**
     * POST /admin/unit-kerja/update/:id
     */
    public function update(int $id)
    {
        $existing = $this->unitKerjaModel->find($id);
        if (! $existing) {
            return redirect()->to('/admin/unit-kerja')->with('error', 'Unit kerja tidak ditemukan.');
        }

        $rules = [
            'nama_unit_kerja' => "required|max_length[150]|is_unique[unit_kerja.nama_unit_kerja,id,{$id}]",
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                             ->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        $this->unitKerjaModel->skipValidation(true)->update($id, [
            'nama_unit_kerja' => trim($this->request->getPost('nama_unit_kerja')),
            'parent_id'       => (int) ($this->request->getPost('parent_id') ?: 0),
        ]);

        return redirect()->to('/admin/unit-kerja')
                         ->with('success', 'Unit kerja berhasil diperbarui.');
    }

    /**
     * POST /admin/unit-kerja/toggle/:id
     */
    public function toggle(int $id)
    {
        $uk = $this->unitKerjaModel->find($id);
        if (! $uk) {
            return redirect()->to('/admin/unit-kerja')->with('error', 'Unit kerja tidak ditemukan.');
        }

        // Soft-delete / restore (tabel menggunakan deleted_at, tanpa is_active)
        if ($uk['deleted_at']) {
            $this->unitKerjaModel->db->table('unit_kerja')->where('id', $id)->update(['deleted_at' => null]);
            $label = 'diaktifkan kembali';
        } else {
            $this->unitKerjaModel->delete($id);
            $label = 'dinonaktifkan (soft-delete)';
        }

        return redirect()->to('/admin/unit-kerja')
                         ->with('success', "Unit kerja \"{$uk['nama_unit_kerja']}\" berhasil {$label}.");
    }
}
