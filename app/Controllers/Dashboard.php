<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        $user = session()->get('user');
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $jenisDokumenList = [
            'Surat Dinas',
            'Undangan Eksternal',
            'SPT',
            'Surat Kuasa',
            'Surat Keterangan',
            'Berita Acara',
            'Sertifikat',
            'Pengumuman',
            'Surat Edaran',
        ];

        $tahun = date('Y');

        return view('dashboard/index', [
            'title' => 'Dashboard',
            'user'  => $user,
            'jenisDokumenList' => $jenisDokumenList,
            'tahun' => $tahun,
        ]);
    }

    /**
     * Process jenis dokumen dan tahun selection
     */
    public function filterPenomoran()
    {
        $jenisDokumen = $this->request->getPost('jenis_dokumen');
        $tahun = $this->request->getPost('tahun');

        if (empty($jenisDokumen) || empty($tahun)) {
            return redirect()->back()->with('error', 'Jenis dokumen dan tahun harus dipilih');
        }

        // Redirect ke halaman penomoran dengan filter jenis dokumen dan tahun
        return redirect()->to('/penomoran?jenis=' . urlencode($jenisDokumen) . '&tahun=' . $tahun);
    }
}

