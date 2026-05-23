<?php

namespace App\Controllers\TuPersuratan;

use App\Controllers\BaseController;
use App\Models\PenomoranModel;
use App\Models\UnitKerjaModel;

/**
 * DashboardController (TU Persuratan)
 *
 * Dashboard agregat surat dari SEMUA unit kerja.
 */
class DashboardController extends BaseController
{
    protected PenomoranModel $penomoranModel;
    protected UnitKerjaModel $unitKerjaModel;

    public function __construct()
    {
        $this->penomoranModel = new PenomoranModel();
        $this->unitKerjaModel = new UnitKerjaModel();
    }

    public function index()
    {
        $tahun = (int) ($this->request->getGet('tahun') ?? date('Y'));

        // Statistik global (semua unit)
        $statistics = $this->penomoranModel->getAdminStatistics();

        // Rekap per unit kerja
        $rekapPerUnit = $this->unitKerjaModel->getRekapPerUnit($tahun);

        // 10 surat terbaru dari semua unit
        $recentLetters = $this->penomoranModel
                              ->where('TAHUN', $tahun)
                              ->orderBy('CREATED_AT', 'DESC')
                              ->limit(10)
                              ->findAll();

        return view('tu_persuratan/dashboard', [
            'title'        => 'Dashboard Persuratan',
            'tahun'        => $tahun,
            'statistics'   => $statistics,
            'rekapPerUnit' => $rekapPerUnit,
            'recentLetters'=> $recentLetters,
            'tahunList'    => range(date('Y'), date('Y') - 4),
        ]);
    }
}
