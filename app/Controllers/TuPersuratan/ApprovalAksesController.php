<?php

namespace App\Controllers\TuPersuratan;

use App\Controllers\BaseController;
use App\Models\PengajuanAksesModel;
use App\Models\UserModel;
use App\Services\UnitKerjaService;

class ApprovalAksesController extends BaseController
{
    protected $pengajuanModel;
    protected $userModel;

    public function __construct()
    {
        $this->pengajuanModel = new PengajuanAksesModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $pendingList = $this->pengajuanModel->getDaftarPendingApproval();
        $aktifList   = $this->pengajuanModel->getDaftarTUUnitAktif();

        // Ganti nama_unit_kerja dengan nama unit INDUK (Biro/Deputi/Inspektorat)
        // menggunakan penelusuran hierarki dari UnitKerjaService
        $unitKerjaService = new UnitKerjaService();

        foreach ($pendingList as &$row) {
            $unitKerjaId = (int) ($row['target_unit_kerja_id'] ?? 0);
            if ($unitKerjaId > 0) {
                $namaInduk = $unitKerjaService->getNamaUnitInduk($unitKerjaId);
                if (!empty($namaInduk)) {
                    $row['nama_unit_kerja'] = $namaInduk;
                }
            }
        }
        unset($row); // Putus referensi foreach

        foreach ($aktifList as &$row) {
            $unitKerjaId = (int) ($row['target_unit_kerja_id'] ?? 0);
            if ($unitKerjaId > 0) {
                $namaInduk = $unitKerjaService->getNamaUnitInduk($unitKerjaId);
                if (!empty($namaInduk)) {
                    $row['nama_unit_kerja'] = $namaInduk;
                }
            }
        }
        unset($row); // Putus referensi foreach

        return view('tu_persuratan/approval_akses/index', [
            'title'       => 'Persetujuan Hak Akses TU Unit',
            'pendingList' => $pendingList,
            'aktifList'   => $aktifList
        ]);
    }

    public function approve($id)
    {
        $pengajuan = $this->pengajuanModel->find($id);

        if (!$pengajuan || $pengajuan['status'] !== 'PENDING') {
            return redirect()->back()->with('error', 'Data pengajuan tidak valid atau sudah diproses.');
        }

        // Ambil data user target yang akan disetujui
        $targetUser  = $this->userModel->find($pengajuan['pegawai_user_id']);
        $unitKerjaId = $targetUser['unit_kerja_id'] ?? null;

        // Jika unit_kerja_id di lokal masih kosong, ambil dari database master (kemenkopmk_db)
        // Ini terjadi jika user didaftarkan tanpa unit_kerja_id, atau tidak ter-sinkronisasi
        if (empty($unitKerjaId) && !empty($targetUser['username_ldap'])) {
            $dbMaster   = \Config\Database::connect('kemenkopmk');
            $masterUser = $dbMaster->table('users')
                ->select('unit_kerja_id')
                ->where('username_ldap', $targetUser['username_ldap'])
                ->get()
                ->getRowArray();

            $unitKerjaId = $masterUser['unit_kerja_id'] ?? null;
        }

        // 1. Update role_id DAN unit_kerja_id sekaligus
        // unit_kerja_id wajib terisi agar user bisa mengakses halaman TU Unit
        $this->userModel->update($pengajuan['pegawai_user_id'], [
            'role_id'       => $pengajuan['usulan_role_id'], // 3 = TU Unit
            'unit_kerja_id' => $unitKerjaId,
        ]);

        // 2. Update status pengajuan + simpan narasi persetujuan
        $this->pengajuanModel->update($id, [
            'status'           => 'APPROVED',
            'reviewer_user_id' => session('user.id'),
            'alasan_penolakan' => 'Akses disetujui oleh TU Persuratan pada ' . date('d/m/Y H:i'),
        ]);

        return redirect()->back()->with('success', 'Pengajuan berhasil disetujui.');
    }

    public function reject($id)
    {
        $alasan = $this->request->getPost('alasan_penolakan');
        $pengajuan = $this->pengajuanModel->find($id);

        if (!$pengajuan || $pengajuan['status'] !== 'PENDING') {
            return redirect()->back()->with('error', 'Data pengajuan tidak valid atau sudah diproses.');
        }

        if (empty(trim($alasan))) {
            return redirect()->back()->with('error', 'Alasan penolakan wajib diisi.');
        }

        // Update Status Pengajuan menjadi REJECTED
        $this->pengajuanModel->update($id, [
            'status' => 'REJECTED',
            'reviewer_user_id' => session('user.id'),
            'alasan_penolakan' => $alasan
        ]);

        return redirect()->back()->with('success', 'Pengajuan telah ditolak.');
    }

    public function revoke($id)
    {
        $pengajuan = $this->pengajuanModel->find($id);

        if (!$pengajuan || $pengajuan['status'] !== 'APPROVED') {
            return redirect()->back()->with('error', 'Data pengajuan tidak valid atau belum disetujui.');
        }

        // 1. Kembalikan Role User Target menjadi Pegawai Biasa (Role ID 2)
        $this->userModel->update($pengajuan['pegawai_user_id'], [
            'role_id' => 2 
        ]);

        // 2. Kita ubah statusnya menjadi REJECTED atau kita beri catatan khusus,
        // namun lebih tepat jika kita update statusnya agar tidak lagi muncul di "Aktif"
        $this->pengajuanModel->update($id, [
            'status' => 'REJECTED',
            'alasan_penolakan' => 'Akses dicabut oleh TU Persuratan Pusat pada ' . date('d/m/Y H:i'),
            'reviewer_user_id' => session('user.id')
        ]);

        return redirect()->back()->with('success', 'Hak akses TU Unit telah berhasil dicabut.');
    }

    /**
     * Menampilkan file Nota Dinas untuk keperluan review approval.
     * Hanya TU Persuratan (role_id 4) yang bisa mengakses melalui route ini.
     */
    public function viewNotaDinas($id)
    {
        $pengajuan = $this->pengajuanModel->find($id);

        if (!$pengajuan || empty($pengajuan['nota_dinas'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Nota Dinas tidak ditemukan.');
        }

        $filePath = FCPATH . 'uploads/nota_dinas/' . $pengajuan['nota_dinas'];

        if (!file_exists($filePath)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File Nota Dinas tidak tersedia.');
        }

        // Stream file PDF ke browser
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="nota_dinas_' . $id . '.pdf"')
            ->setBody(file_get_contents($filePath));
    }
}
