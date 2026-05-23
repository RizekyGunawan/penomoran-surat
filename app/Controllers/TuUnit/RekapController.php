<?php

namespace App\Controllers\TuUnit;

use App\Controllers\BaseController;
use App\Models\PenomoranModel;
use App\Models\UnitKerjaModel;

/**
 * RekapController (TU Unit)
 *
 * Menampilkan daftar lengkap surat dari pegawai di unit kerjanya,
 * dengan filter dan pagination.
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
        $unitKerjaId  = (int) session('user.unit_kerja_id');
        $unitKerja    = $unitKerjaId ? $this->unitKerjaModel->getHierarchyGroup($unitKerjaId) : null;

        $tahun        = $this->request->getGet('tahun') ?? date('Y');
        $jenisDokumen = $this->request->getGet('jenis');
        $status       = $this->request->getGet('status');
        $search       = $this->request->getGet('search');
        $startDate    = $this->request->getGet('start_date');
        $endDate      = $this->request->getGet('end_date');
        $page         = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage      = 15;

        // Selalu filter unit_kerja_id menggunakan kumpulan id dalam hirarki induk (Biro/Deputi/Inspektorat)
        $filterUkIds = $unitKerja ? $unitKerja['child_ids'] : [$unitKerjaId];
        $filters = [
            'unit_kerja_id' => $filterUkIds,
            'tahun'         => $tahun,
        ];
        if (!empty($jenisDokumen)) { $filters['jenis']      = $jenisDokumen; }
        if (!empty($status))       { $filters['status']     = $status; }
        if (!empty($search))       { $filters['search']     = $search; }
        if (!empty($startDate))    { $filters['start_date'] = $startDate; }
        if (!empty($endDate))      { $filters['end_date']   = $endDate; }

        if ($this->request->getGet('export') === 'excel') {
            return $this->exportExcel($filters, $unitKerja);
        }

        // Pakai pola yang sama seperti Penomoran::index() dan TuPersuratan\RekapController.
        // getLettersWithPagination() return array of rows, bukan ['data' => ...].
        $penomoranData = $this->penomoranModel->getLettersWithPagination($perPage, $filters);
        $pager         = $this->penomoranModel->pager;

        return view('tu_unit/rekap', [
            'title'           => 'Rekap Surat Unit',
            'unitKerja'       => $unitKerja,
            'penomoran'       => $penomoranData,
            'pagination'      => [
                'current_page' => $pager->getCurrentPage(),
                'total_pages'  => $pager->getPageCount(),
                'total_records'=> $pager->getTotal(),
                'per_page'     => $perPage,
            ],
            'tahun'           => $tahun,
            'jenisDokumen'    => $jenisDokumen ?? '',
            'status'          => $status ?? '',
            'search'          => $search ?? '',
            'startDate'       => $startDate ?? '',
            'endDate'         => $endDate ?? '',
            'jenisDokumenList'=> $this->penomoranModel->getJenisDokumenList(),
            'tahunList'       => range(date('Y'), date('Y') - 4),
        ]);
    }

    /**
     * Export data rekap ke format CSV (bisa dibuka di Excel)
     */
    protected function exportExcel(array $filters, $unitKerja)
    {
        $data = $this->penomoranModel->getFilteredLetters($filters);

        $ukName = $unitKerja ? preg_replace('/[^A-Za-z0-9\-]/', '_', $unitKerja['nama_unit_kerja']) : 'Unit';
        $filename = 'Rekap_Surat_' . $ukName . '_' . date('Ymd_His') . '.csv';
        
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
                 $row['TANGGAL'] ? date('d/m/Y', strtotime($row['TANGGAL'])) : '-',
                 $row['STATUS'] ?? 'AKTIF'
             ]);
        }
        
        fclose($out);
        exit();
    }
}
