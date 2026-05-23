<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Header -->
<?php
$jenisBadgeColor = [
    'SPT' => ['bg' => '#dbeafe', 'text' => '#1e40af'],
    'Surat Dinas' => ['bg' => '#d1fae5', 'text' => '#065f46'],
    'Undangan Eksternal' => ['bg' => '#fef9c3', 'text' => '#92400e'],
    'Surat Edaran' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
    'Pengumuman' => ['bg' => '#ffedd5', 'text' => '#9a3412'],
    'Berita Acara' => ['bg' => '#e0f2fe', 'text' => '#075985'],
    'Surat Kuasa' => ['bg' => '#f1f5f9', 'text' => '#475569'],
];
?>
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0" style="font-weight:700;color:#111827;">Rekap Surat - Persuratan</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Semua surat dari seluruh unit kerja</p>
    </div>
    <a href="<?= base_url('tu-persuratan/dashboard') ?>" class="btn btn-outline-secondary btn-sm px-3">
        <i class="fas fa-arrow-left me-1"></i> Dashboard
    </a>
</div>

<!-- Filter -->
<div class="card shadow-sm mb-4" style="border-radius:16px; border: 1px solid #e5e7eb;">
    <div class="card-body p-3">
        <form method="GET">
            <!-- Baris 1: Filter tanggal, tahun, unit kerja, jenis, status -->
            <div class="row g-2 align-items-end mb-2">
                <!-- Tanggal -->
                <div class="col-12 col-md-auto">
                    <label class="form-label text-muted mb-1" style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Tanggal</label>
                    <div class="d-flex align-items-center gap-1">
                        <input type="date" name="start_date" class="form-control form-control-sm"
                            style="width:135px;" value="<?= esc($startDate ?? '') ?>" title="Dari tanggal">
                        <span class="text-muted px-1" style="font-size:.8rem;">–</span>
                        <input type="date" name="end_date" class="form-control form-control-sm"
                            style="width:135px;" value="<?= esc($endDate ?? '') ?>" title="Sampai tanggal">
                    </div>
                </div>

                <!-- Tahun -->
                <div class="col-6 col-md">
                    <label class="form-label text-muted mb-1" style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Tahun</label>
                    <select name="tahun" class="form-select form-select-sm w-100">
                        <option value="">Semua</option>
                        <?php foreach ($tahunList as $t): ?>
                            <option value="<?= $t ?>" <?= $t == $tahun ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <!-- Unit Kerja (eksklusif Persuratan) -->
                <div class="col-12 col-md">
                    <label class="form-label text-muted mb-1" style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Unit Kerja</label>
                    <select name="unit_kerja_id" class="form-select form-select-sm w-100">
                        <option value="">Semua Unit Kerja</option>
                        <?php foreach ($unitKerjaList as $uk):
                            $val = isset($uk['child_ids']) && is_array($uk['child_ids']) ? implode(',', $uk['child_ids']) : $uk['id'];
                        ?>
                            <option value="<?= esc($val) ?>" <?= $unitKerjaId == $val ? 'selected' : '' ?>>
                                <?= esc($uk['nama_unit_kerja']) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>

                <!-- Jenis Dokumen -->
                <div class="col-6 col-md">
                    <label class="form-label text-muted mb-1" style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Jenis Dokumen</label>
                    <select name="jenis" class="form-select form-select-sm w-100">
                        <option value="">Semua Jenis</option>
                        <?php foreach ($jenisDokumenList as $j): ?>
                            <option value="<?= esc($j) ?>" <?= $jenisDokumen === $j ? 'selected' : '' ?>><?= esc($j) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <!-- Status -->
                <div class="col-6 col-md">
                    <label class="form-label text-muted mb-1" style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Status</label>
                    <select name="status" class="form-select form-select-sm w-100">
                        <option value="">Semua Status</option>
                        <option value="DITERBITKAN" <?= $status === 'DITERBITKAN' ? 'selected' : '' ?>>Aktif</option>
                        <option value="DIBATALKAN"  <?= $status === 'DIBATALKAN'  ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                </div>
            </div>

            <!-- Baris 2: Search + Tombol aksi -->
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div style="position:relative; width:100%;">
                        <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:.8rem; line-height:1; pointer-events:none; z-index:5;">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search"
                               class="form-control form-control-sm"
                               placeholder="Cari nomor surat, perihal, nama pegawai…"
                               value="<?= esc($search) ?>"
                               style="padding-left:30px !important; font-size:.85rem;">
                    </div>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="<?= base_url('tu-persuratan/rekap') ?>" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="fas fa-undo me-1"></i> Reset
                    </a>
                    <button type="submit" name="export" value="excel"
                        class="btn btn-sm px-3 shadow-sm"
                        style="background-color:#107c41;border-color:#107c41;color:#fff;" title="Export ke Excel">
                        <i class="fas fa-file-excel me-1"></i> Export
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- Tabel -->
<div class="card shadow-sm" style="border-radius:16px; border: 1px solid #e5e7eb;">
    <div class="card-body p-4">
        <?php if (empty($penomoran)): ?>
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-1"></i> Tidak ada data yang sesuai filter.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nomor Surat</th>
                            <th class="text-center">Jenis</th>
                            <th>Perihal</th>
                            <th>Nama</th>
                            <th>Unit Kerja</th>
                            <th>Tanggal</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($penomoran as $s): ?>
                        <?php
                            $namaTampil = $s['nama_lengkap_pegawai'] ?? $s['NAMA'] ?? '-';
                            if (!empty($s['gelar_depan'])) $namaTampil = $s['gelar_depan'] . ' ' . $namaTampil;
                            if (!empty($s['gelar_belakang'])) $namaTampil .= ', ' . $s['gelar_belakang'];
                            $statusLabel = $s['STATUS'] ?? 'AKTIF';
                        ?>
                        <tr>
                            <td style="font-size:.82rem;font-weight:600;white-space:nowrap;max-width:180px;overflow:hidden;text-overflow:ellipsis;">
                                <a href="javascript:void(0)" class="text-decoration-none detail-surat-btn"
                                   style="color:#065f46; border-bottom: 1px dashed #065f46; transition: all 0.2s;"
                                   onmouseover="this.style.color='#044734'; this.style.borderBottomStyle='solid';"
                                   onmouseout="this.style.color='#065f46'; this.style.borderBottomStyle='dashed';"
                                   data-nomor="<?= esc($s['NOMOR_SURAT_LENGKAP'] ?? $s['NO']) ?>"
                                   data-jenis="<?= esc($s['JENIS_DOKUMEN']) ?>"
                                   data-perihal="<?= esc($s['PERIHAL'] ?? '-') ?>"
                                   data-nama="<?= esc($namaTampil) ?>"
                                   data-unit="<?= esc($s['UNIT_KERJA'] ?? '-') ?>"
                                   data-tanggal="<?= $s['TANGGAL'] ? date('d/m/Y', strtotime($s['TANGGAL'])) : '-' ?>"
                                   data-status="<?= esc($statusLabel) ?>"
                                   data-batal-alasan="<?= esc($s['ALASAN_PEMBATALAN'] ?? '') ?>"
                                   data-batal-tgl="<?= $s['TANGGAL_PEMBATALAN'] ? date('d/m/Y H:i', strtotime($s['TANGGAL_PEMBATALAN'])) : '' ?>"
                                   title="Klik untuk melihat detail surat">
                                    <?= esc($s['NOMOR_SURAT_LENGKAP'] ?? $s['NO']) ?>
                                </a>
                            </td>
                            <?php
                            $jenis_dok = $s['JENIS_DOKUMEN'] ?? '';
                            $colors = $jenisBadgeColor[$jenis_dok] ?? ['bg' => '#f1f5f9', 'text' => '#475569'];
                            ?>
                            <td class="text-center">
                                <span style="display:inline-block;width:135px;text-align:center;background:<?= $colors['bg'] ?>;color:<?= $colors['text'] ?>;font-size:.72rem;font-weight:600;padding:2px 8px;border-radius:99px;white-space:nowrap;">
                                    <?= esc($jenis_dok) ?>
                                </span>
                            </td>
                            <td style="font-size:.82rem;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <?= esc($s['PERIHAL'] ?? '-') ?>
                            </td>
                            <td style="font-size:.82rem;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= esc($namaTampil) ?>"><?= esc($namaTampil) ?></td>
                            <td style="font-size:.78rem;color:#6b7280;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= esc($s['UNIT_KERJA'] ?? '-') ?>"><?= esc($s['UNIT_KERJA'] ?? '-') ?></td>
                            <td style="font-size:.82rem;white-space:nowrap;">
                                <?= $s['TANGGAL'] ? date('d/m/Y', strtotime($s['TANGGAL'])) : '-' ?>
                            </td>
                            <td class="text-center">
                                <?php $st = $s['STATUS'] ?? 'AKTIF'; ?>
                                <span class="status-badge <?= $st === 'DIBATALKAN' ? 'cancelled' : 'published' ?>">
                                    <?= $st === 'DIBATALKAN' ? 'Dibatalkan' : 'Aktif' ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php $p = $pagination; ?>
            <div class="pagination-container mt-3">
                <span class="pagination-info-text">
                    Total <strong><?= number_format($p['total_records']) ?></strong> surat
                </span>
                <?php if ($p['total_pages'] > 1): ?>
                    <div class="numbered-pagination">
                        <?php
                            $window = 2; // ±2 pages from current
                            $currentPage = (int) $p['current_page'];
                            $totalPages = (int) $p['total_pages'];
                            $start = max(1, $currentPage - $window);
                            $end   = min($totalPages, $currentPage + $window);
                        ?>
                        
                        <?php if ($currentPage > 1): ?>
                            <a class="page-btn" href="?page=<?= $currentPage - 1 ?>&start_date=<?= urlencode($startDate??'') ?>&end_date=<?= urlencode($endDate??'') ?>&tahun=<?= $tahun ?>&unit_kerja_id=<?= $unitKerjaId ?>&jenis=<?= urlencode($jenisDokumen) ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>">&laquo;</a>
                        <?php endif ?>

                        <?php if ($start > 1): ?>
                            <a class="page-btn" href="?page=1&start_date=<?= urlencode($startDate??'') ?>&end_date=<?= urlencode($endDate??'') ?>&tahun=<?= $tahun ?>&unit_kerja_id=<?= $unitKerjaId ?>&jenis=<?= urlencode($jenisDokumen) ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>">1</a>
                            <?php if ($start > 2): ?><span class="page-btn text-muted" style="pointer-events:none;">...</span><?php endif; ?>
                        <?php endif; ?>

                        <?php for ($pg = $start; $pg <= $end; $pg++): ?>
                            <a class="page-btn <?= $pg === $currentPage ? 'active' : '' ?>"
                               href="?page=<?= $pg ?>&start_date=<?= urlencode($startDate??'') ?>&end_date=<?= urlencode($endDate??'') ?>&tahun=<?= $tahun ?>&unit_kerja_id=<?= $unitKerjaId ?>&jenis=<?= urlencode($jenisDokumen) ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>">
                                <?= $pg ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($end < $totalPages): ?>
                            <?php if ($end < $totalPages - 1): ?><span class="page-btn text-muted" style="pointer-events:none;">...</span><?php endif; ?>
                            <a class="page-btn" href="?page=<?= $totalPages ?>&start_date=<?= urlencode($startDate??'') ?>&end_date=<?= urlencode($endDate??'') ?>&tahun=<?= $tahun ?>&unit_kerja_id=<?= $unitKerjaId ?>&jenis=<?= urlencode($jenisDokumen) ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>"><?= $totalPages ?></a>
                        <?php endif; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <a class="page-btn" href="?page=<?= $currentPage + 1 ?>&start_date=<?= urlencode($startDate??'') ?>&end_date=<?= urlencode($endDate??'') ?>&tahun=<?= $tahun ?>&unit_kerja_id=<?= $unitKerjaId ?>&jenis=<?= urlencode($jenisDokumen) ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>">&raquo;</a>
                        <?php endif ?>
                    </div>
                <?php endif ?>
            </div>
        <?php endif ?>
    </div>
</div>

<!-- Modal Detail Surat -->
<div class="modal fade" id="modalDetailSurat" tabindex="-1" aria-labelledby="modalDetailSuratLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:16px; border:none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="modalDetailSuratLabel" style="color:#111827;">Detail Informasi Surat</h5>
                <span id="detailStatusBadge" class="ms-3 badge"></span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <div class="row g-4 mb-4">
                    <div class="col-sm-6">
                        <label class="text-muted mb-1" style="font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Nomor Surat</label>
                        <div id="detailNomor" class="fw-bold" style="font-size:1.1rem; color:#065f46;">-</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted mb-1" style="font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Tanggal Surat</label>
                        <div id="detailTanggal" class="fw-medium text-dark">-</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted mb-1" style="font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Jenis Dokumen</label>
                        <div id="detailJenis" class="fw-medium text-dark">-</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted mb-1" style="font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Pengaju / Unit Kerja</label>
                        <div id="detailNama" class="fw-medium text-dark">-</div>
                        <div id="detailUnit" class="text-muted mt-1" style="font-size:.85rem;">-</div>
                    </div>
                </div>

                <!-- Bagian Perihal (Full Width) -->
                <div class="p-3 mb-2 bg-light rounded-3 border">
                    <label class="text-muted mb-2" style="font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;"><i class="fas fa-align-left me-1"></i> Perihal Lengkap</label>
                    <p id="detailPerihal" class="mb-0 text-dark" style="font-size:.95rem; line-height:1.6;">-</p>
                </div>

                <!-- Blok Pembatalan (Disembunyikan secara default) -->
                <div id="detailBatalBlock" class="p-3 mt-3 rounded-3" style="display:none; background-color:#fef2f2; border: 1px solid #fca5a5;">
                    <div class="d-flex align-items-center mb-2" style="color:#b91c1c;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span style="font-size:.85rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Informasi Pembatalan</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-sm-4">
                            <span class="text-muted d-block" style="font-size:.8rem;">Tanggal Dibatalkan</span>
                            <strong id="detailBatalTgl" style="font-size:.85rem; color:#7f1d1d;">-</strong>
                        </div>
                        <div class="col-sm-8">
                            <span class="text-muted d-block" style="font-size:.8rem;">Alasan Pembatalan</span>
                            <span id="detailBatalAlasan" style="font-size:.85rem; color:#7f1d1d;">-</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4">
                <button type="button" class="btn btn-secondary px-4" style="border-radius:8px;" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const detailButtons = document.querySelectorAll('.detail-surat-btn');
    const modalDetail = new bootstrap.Modal(document.getElementById('modalDetailSurat'));

    detailButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Ambil data
            const nomor = this.getAttribute('data-nomor');
            const jenis = this.getAttribute('data-jenis');
            const perihal = this.getAttribute('data-perihal');
            const nama = this.getAttribute('data-nama');
            const unit = this.getAttribute('data-unit');
            const tanggal = this.getAttribute('data-tanggal');
            const status = this.getAttribute('data-status');
            const batalAlasan = this.getAttribute('data-batal-alasan');
            const batalTgl = this.getAttribute('data-batal-tgl');

            // Set data ke dalam modal
            document.getElementById('detailNomor').textContent = nomor;
            document.getElementById('detailJenis').textContent = jenis;
            document.getElementById('detailPerihal').textContent = perihal;
            document.getElementById('detailNama').textContent = nama;
            document.getElementById('detailUnit').textContent = unit;
            document.getElementById('detailTanggal').textContent = tanggal;

            // Handle badge status
            const badge = document.getElementById('detailStatusBadge');
            badge.textContent = status === 'DIBATALKAN' ? 'Dibatalkan' : 'Aktif';
            badge.className = 'ms-3 badge ' + (status === 'DIBATALKAN' ? 'bg-danger' : 'bg-success');

            // Handle blok batal
            const batalBlock = document.getElementById('detailBatalBlock');
            if (status === 'DIBATALKAN') {
                batalBlock.style.display = 'block';
                document.getElementById('detailBatalAlasan').textContent = batalAlasan || 'Tidak ada keterangan';
                document.getElementById('detailBatalTgl').textContent = batalTgl || '-';
            } else {
                batalBlock.style.display = 'none';
            }

            // Tampilkan modal
            modalDetail.show();
        });
    });
});
</script>
<?= $this->endSection() ?>
