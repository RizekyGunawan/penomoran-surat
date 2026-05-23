<?php

namespace App\Controllers\TuUnit;

use App\Controllers\BaseController;
use App\Models\PenomoranModel;
use App\Models\UnitKerjaModel;

/**
 * DashboardController (TU Unit)
 *
 * Dashboard rekap surat milik pegawai di unit kerja TU yang login.
 * Hanya dapat melihat surat dari unit kerjanya sendiri.
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
        $unitKerjaId = (int) session('user.unit_kerja_id');
        $tahun       = (int) ($this->request->getGet('tahun') ?? date('Y'));

        // Info unit kerja TU yang sedang login (dapat grup hirarki/induknya)
        $unitKerja = $unitKerjaId ? $this->unitKerjaModel->getHierarchyGroup($unitKerjaId) : null;
        $filterUkIds = $unitKerja ? $unitKerja['child_ids'] : [$unitKerjaId];

        // Statistik surat unitnya (filter by unit_kerja_id array)
        $statistics = $this->getUnitStatistics($filterUkIds, $tahun);

        // 5 surat terbaru dari unit ini
        $recentLetters = $this->penomoranModel
                              ->whereIn('unit_kerja_id', $filterUkIds)
                              ->where('TAHUN', $tahun)
                              ->orderBy('NO', 'DESC')
                              ->limit(5)
                              ->findAll();

        // Rekap per jenis dokumen
        $rekapJenis = $this->getRekapPerJenis($filterUkIds, $tahun);

        return view('tu_unit/dashboard', [
            'title'         => 'Dashboard TU Unit',
            'unitKerja'     => $unitKerja,
            'tahun'         => $tahun,
            'statistics'    => $statistics,
            'recentLetters' => $recentLetters,
            'rekapJenis'    => $rekapJenis,
            'tahunList'     => range(date('Y'), date('Y') - 4),
        ]);
    }

    private function getUnitStatistics(array $unitKerjaIds, int $tahun): array
    {
        if (empty($unitKerjaIds)) return ['total' => 0, 'aktif' => 0, 'dibatalkan' => 0, 'bulan_ini' => 0];
        
        $db = \Config\Database::connect();
        
        // Buat placeholder untuk WHERE IN
        $placeholders = implode(',', array_fill(0, count($unitKerjaIds), '?'));
        
        // Bindings array: 1 untuk date, lalu unitKerjaIds, lalu tahun
        $bindings = [date('Y-m')];
        $bindings = array_merge($bindings, $unitKerjaIds);
        $bindings[] = $tahun;

        $row = $db->query("
            SELECT
                COUNT(*)  AS total,
                SUM(CASE WHEN STATUS IN ('AKTIF','DITERBITKAN') OR STATUS IS NULL THEN 1 ELSE 0 END) AS aktif,
                SUM(CASE WHEN STATUS = 'DIBATALKAN' THEN 1 ELSE 0 END) AS dibatalkan,
                SUM(CASE WHEN DATE_FORMAT(TANGGAL,'%Y-%m') = ? THEN 1 ELSE 0 END) AS bulan_ini
            FROM penomoran
            WHERE unit_kerja_id IN ($placeholders) AND TAHUN = ?
        ", $bindings)->getRowArray();

        return $row ?? ['total' => 0, 'aktif' => 0, 'dibatalkan' => 0, 'bulan_ini' => 0];
    }

    private function getRekapPerJenis(array $unitKerjaIds, int $tahun): array
    {
        if (empty($unitKerjaIds)) return [];
        
        $db = \Config\Database::connect();
        
        $placeholders = implode(',', array_fill(0, count($unitKerjaIds), '?'));
        $bindings = array_merge($unitKerjaIds, [$tahun]);
        
        return $db->query("
            SELECT JENIS_DOKUMEN, COUNT(*) AS total
            FROM penomoran
            WHERE unit_kerja_id IN ($placeholders) AND TAHUN = ?
            GROUP BY JENIS_DOKUMEN
            ORDER BY total DESC
        ", $bindings)->getResultArray();
    }
}
