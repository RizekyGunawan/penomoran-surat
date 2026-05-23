<?php

namespace App\Controllers\TuPersuratan;

use App\Controllers\BaseController;
use App\Models\PenomoranModel;
use App\Models\UnitKerjaModel;

/**
 * RekapController (TU Persuratan)
 *
 * Menampilkan rekap surat dari semua unit kerja,
 * dengan filter per unit, jenis, status, dan pencarian.
 */
class RekapController extends BaseController
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
        $tahun        = $this->request->getGet('tahun') ?? date('Y');
        $unitKerjaId  = $this->request->getGet('unit_kerja_id');
        $jenisDokumen = $this->request->getGet('jenis');
        $status       = $this->request->getGet('status');
        $search       = $this->request->getGet('search');
        $startDate    = $this->request->getGet('start_date');
        $endDate      = $this->request->getGet('end_date');
        $page         = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage      = 20;

        $filters = ['tahun' => $tahun];
        if (!empty($unitKerjaId))  { $filters['unit_kerja_id'] = $unitKerjaId; }
        if (!empty($jenisDokumen)) { $filters['jenis']          = $jenisDokumen; }
        if (!empty($status))       { $filters['status']         = $status; }
        if (!empty($search))       { $filters['search']         = $search; }
        if (!empty($startDate))    { $filters['start_date']     = $startDate; }
        if (!empty($endDate))      { $filters['end_date']       = $endDate; }

        if ($this->request->getGet('export') === 'excel') {
            return $this->exportExcel($filters);
        }

        // getLettersWithPagination() mengembalikan array of rows langsung (bukan ['data' => ...]).
        // Pager objek diambil terpisah via $model->pager — sama seperti pola di Penomoran::index().
        $penomoranData = $this->penomoranModel->getLettersWithPagination($perPage, $filters);
        $pager         = $this->penomoranModel->pager;

        return view('tu_persuratan/rekap', [
            'title'           => 'Rekap Surat - Persuratan',
            'penomoran'       => $penomoranData,
            'pagination'      => [
                'current_page' => $pager->getCurrentPage(),
                'total_pages'  => $pager->getPageCount(),
                'total_records'=> $pager->getTotal(),
                'per_page'     => $perPage,
            ],
            'tahun'           => $tahun,
            'unitKerjaId'     => $unitKerjaId ?? '',
            'jenisDokumen'    => $jenisDokumen ?? '',
            'status'          => $status ?? '',
            'search'          => $search ?? '',
            'startDate'       => $startDate ?? '',
            'endDate'         => $endDate ?? '',
            'unitKerjaList'   => $this->unitKerjaModel->getGroupedActive(),
            'jenisDokumenList'=> $this->penomoranModel->getJenisDokumenList(),
            'tahunList'       => range(date('Y'), date('Y') - 4),
        ]);
    }

    /**
     * Export data rekap ke format CSV (bisa dibuka di Excel)
     */
    protected function exportExcel(array $filters)
    {
        $data = $this->penomoranModel->getFilteredLetters($filters);

        $filename = 'Semua_Unit_Rekap_Surat_' . date('Ymd_His') . '.csv';
        
        // Atur header agar browser mendownload sebagai CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // Tambahkan BOM untuk dukungan UTF-8 di Excel
        fputs($out, "\xEF\xBB\xBF");
        
        // Tulis header kolom
        fputcsv($out, [
            'No', 
            'Nomor Surat Lengkap', 
            'Jenis Dokumen', 
            'Perihal', 
            'Nama Pegawai', 
            'Unit Kerja', 
            'Tanggal', 
            'Status'
        ]);

        // Tulis baris data
        foreach ($data as $row) {
             fputcsv($out, [
                 $row['NO'],
                 $row['NOMOR_SURAT_LENGKAP'] ?? '-',
                 $row['JENIS_DOKUMEN'],
                 $row['PERIHAL'],
                 $row['NAMA'],
                 $row['UNIT_KERJA'],
                 $row['TANGGAL'] ? date('d/m/Y', strtotime($row['TANGGAL'])) : '-',
                 $row['STATUS'] ?? 'AKTIF'
             ]);
        }
        
        fclose($out);
        exit();
    }
}
