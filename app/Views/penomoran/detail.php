<?php
function formatDate($date)
{
    if (empty($date)) {
        return '-';
    }
    $months = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $months[date('m', $timestamp)];
    $year = date('Y', $timestamp);
    return "$day $month $year";
}
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <!-- Include Custom CSS -->

    <!-- Page Title -->
    <div class="row mb-3">
        <div class="col-md-12">
            <h1 class="h3 mb-0 text-gray-800">Penomoran Surat</h1>
        </div>
    </div>

    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Beranda</a></li>
                    <li class="breadcrumb-item">Semua Menu</li>
                    <li class="breadcrumb-item active" aria-current="page">Penomoran Surat</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <!-- Main Card -->
            <div class="card border" style="border-color: #e5e7eb !important; border-radius: 12px !important;">
                <div class="card-body p-4">
                    <!-- Title -->
                    <h5 class="mb-4">Detail Nomor Surat</h5>
                    
                    <?php if (isset($penomoran['STATUS']) && $penomoran['STATUS'] === 'DIBATALKAN'): ?>
                        <!-- Status Badge & Section Title -->
                        <div class="d-flex align-items-center mb-3">
                            <h5 class="mb-0 me-3 fw-bold" style="font-size: 1.1rem;">Detail Nomor Surat</h5>
                        </div>
                        
                        <div class="mb-3">
                            <span class="me-2 text-muted fw-bold small">Status Saat Ini :</span>
                            <span class="badge rounded-pill border border-danger text-danger bg-white px-3 py-1 fw-normal">
                                Dibatalkan
                            </span>
                        </div>

                        <!-- Cancelled Detail Grid -->
                        <div class="row g-3 mb-3">
                            <!-- Row 1: No Surat (Red Box), Jenis, Tanggal -->
                            <div class="col-md-4">
                                <div class="p-3 h-100" style="border: 1px solid #ef4444; border-radius: 8px; background-color: #fff1f2;">
                                    <label class="small mb-1 text-muted">Nomor Surat Lengkap Yang Dibatalkan</label>
                                    <div class="fw-bold fs-5 text-danger"><?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 h-100" style="background-color: #f8fafc; border-radius: 8px;">
                                    <label class="text-muted small mb-1">Jenis Surat</label>
                                    <div class="fw-medium text-dark"><?= esc($penomoran['JENIS_DOKUMEN']) ?></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 h-100" style="background-color: #f8fafc; border-radius: 8px;">
                                    <label class="text-muted small mb-1">Tanggal Surat</label>
                                    <div class="fw-medium text-dark"><?= formatDate($penomoran['TANGGAL']) ?></div>
                                </div>
                            </div>

                            <!-- Row 2: Unit Kerja (Wider), Pengajuan Oleh -->
                            <div class="col-md-8">
                                <div class="p-3 h-100" style="background-color: #f8fafc; border-radius: 8px;">
                                    <label class="text-muted small mb-1">Unit Kerja Yang Mengajukan</label>
                                    <div class="fw-medium text-dark"><?= esc($penomoran['UNIT_KERJA'] ?? '-') ?></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 h-100" style="background-color: #f8fafc; border-radius: 8px;">
                                    <label class="text-muted small mb-1">Pengajuan Oleh</label>
                                    <div class="fw-medium text-dark"><?= esc($penomoran['NAMA'] ?? '-') ?></div>
                                </div>
                            </div>
                            
                             <!-- Perihal (Full Width) -->
                            <div class="col-md-12">
                                <div class="p-3 h-100" style="background-color: #f8fafc; border-radius: 8px;">
                                    <label class="text-muted small mb-1">Perihal Surat</label>
                                    <div class="fw-medium text-dark"><?= esc($penomoran['PERIHAL'] ?? '-') ?></div>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- NOT Cancelled View -->
                        <!-- Active Number Box -->
                        <div class="p-3 mb-3" style="border: 2px solid #1e3a8a; border-radius: 10px; background-color: #f8fafc;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1" style="font-size: 0.8rem; color: #1e3a8a; font-weight: 500;">Nomor surat lengkap yang digunakan:</p>
                                    <h2 class="mb-0" id="detail-no-surat" style="font-size: 1.75rem; font-weight: 700; color: #1e3a8a;"><?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?></h2>
                                </div>
                                <button type="button" class="btn btn-primary rounded-pill btn-sm px-3" onclick="copyToClipboard('<?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?>')" style="background-color: #1e3a8a; border-color: #1e3a8a;">
                                    <i class="far fa-copy me-1"></i> Copy
                                </button>
                            </div>
                        </div>
                        
                        <!-- Status Badge -->
                        <div class="mb-3">
                            <span class="me-2 text-muted small">Status Saat Ini :</span>
                            <span class="badge" style="background-color: #d1fae5; color: #059669; padding: 0.35rem 0.65rem; border-radius: 20px; font-weight: 500;">
                                Diterbitkan
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if (!isset($penomoran['STATUS']) || $penomoran['STATUS'] !== 'DIBATALKAN'): ?>
                    <!-- Information Grid -->
                    <div class="row g-3">
                        <!-- Jenis Surat & Tanggal Surat -->
                        <div class="col-md-6">
                            <div class="p-3" style="border: 1px solid #e5e7eb; border-radius: 8px; background-color: white;">
                                <label class="text-muted small mb-1">Jenis Surat</label>
                                <div class="fw-medium"><?= esc($penomoran['JENIS_DOKUMEN']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3" style="border: 1px solid #e5e7eb; border-radius: 8px; background-color: white;">
                                <label class="text-muted small mb-1">Tanggal Surat</label>
                                <div class="fw-medium"><?= formatDate($penomoran['TANGGAL']) ?></div>
                            </div>
                        </div>

                        <!-- Unit Kerja & Pengajuan Oleh -->
                        <div class="col-md-6">
                            <div class="p-3" style="border: 1px solid #e5e7eb; border-radius: 8px; background-color: white;">
                                <label class="text-muted small mb-1">Unit Kerja Yang Mengajukan</label>
                                <div><?= esc($penomoran['UNIT_KERJA'] ?? '-') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3" style="border: 1px solid #e5e7eb; border-radius: 8px; background-color: white;">
                                <label class="text-muted small mb-1">Pengajuan Oleh</label>
                                <div><?= esc($penomoran['NAMA'] ?? '-') ?></div>
                            </div>
                        </div>

                        <!-- Perihal Surat (Full Width) -->
                        <div class="col-md-12">
                            <div class="p-3" style="border: 1px solid #e5e7eb; border-radius: 8px; background-color: white;">
                                <label class="text-muted small mb-1">Perihal Surat</label>
                                <div><?= esc($penomoran['PERIHAL'] ?? '-') ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 mt-4">
                        <a href="<?= base_url('penomoran?jenis=' . urlencode($penomoran['JENIS_DOKUMEN']) . '&tahun=' . $penomoran['TAHUN']) ?>" 
                           class="btn btn-outline-secondary rounded-pill">
                            Kembali
                        </a>
                        
                        <?php 
                        $isOwner = isset($penomoran['USER_ID']) && $penomoran['USER_ID'] == $currentUserId;
                        $isCancelled = isset($penomoran['STATUS']) && $penomoran['STATUS'] === 'DIBATALKAN';
                        ?>
                        
                        <?php if (!$isCancelled && $isOwner): ?>
                            <button type="button" 
                                    class="btn btn-danger ms-auto rounded-pill" 
                                    onclick="openCancelModal('<?= esc($penomoran['NO']) ?>', '<?= esc($penomoran['JENIS_DOKUMEN']) ?>', '<?= esc($penomoran['TANGGAL']) ?>', '<?= esc($penomoran['TAHUN']) ?>', '<?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? '') ?>')">
                                <i class="fas fa-trash me-1"></i> Batalkan Nomor
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-body text-center pt-5 pb-4 px-4">
                <!-- Icon -->
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #fee2e2; border-radius: 50%;">
                        <i class="fas fa-trash" style="font-size: 1.5rem; color: #dc2626;"></i>
                    </div>
                </div>
                
                <!-- Title -->
                <h5 class="mb-3" style="font-weight: 600;">Konfirmasi Pembatalan Nomor Surat</h5>
                
                <!-- Description -->
                <p class="text-muted mb-4" style="font-size: 0.875rem;">
                    Apakah anda yakin akan membatalkan pada nomor surat berikut?
                </p>
                
                <!-- Form -->
                <form action="<?= base_url('penomoran/cancel-submit') ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <!-- Hidden Fields -->
                    <input type="hidden" name="NO" id="cancelInputNo">
                    <input type="hidden" name="JENIS_DOKUMEN" id="cancelInputJenis">
                    <input type="hidden" name="TAHUN" id="cancelInputTahun">
                    
                    <!-- Information Fields -->
                    <div class="text-start mb-3">
                        <!-- Nomor Surat Box -->
                        <div class="mb-3 p-3 border rounded" style="background-color: white;">
                            <label class="d-block text-muted small mb-1">Nomor Surat</label>
                            <div class="fw-bold fs-5 text-dark" id="cancelShowNoText"></div>
                            <input type="hidden" name="NOMOR_SURAT_LENGKAP_INPUT" id="cancelInputNoLengkap">
                        </div>
                        
                        <!-- Jenis Surat & Tanggal Surat (2 columns) -->
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="p-3 border rounded h-100" style="background-color: white;">
                                    <label class="d-block text-muted small mb-1">Jenis Surat</label>
                                    <div class="fw-medium text-dark" id="cancelShowJenisText"></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 border rounded h-100" style="background-color: white;">
                                    <label class="d-block text-muted small mb-1">Tanggal Surat</label>
                                    <div class="fw-medium text-dark" id="cancelShowTanggalText"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Alasan Pembatalan -->
                        <div class="mb-3">
                            <label for="ALASAN_PEMBATALAN" class="form-label small fw-bold">
                                Alasan Pembatalan<span class="text-danger">*</span>
                            </label>
                            <textarea 
                                class="form-control" 
                                id="ALASAN_PEMBATALAN" 
                                name="ALASAN_PEMBATALAN" 
                                rows="4" 
                                required
                                placeholder="Jelaskan alasan pembatalan nomor surat ini"
                                style="border-radius: 8px; resize: none;"></textarea>
                        </div>
                    </div>
                    
                    <!-- Buttons -->
                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <button type="submit" 
                                class="btn btn-danger px-4 rounded-pill" 
                                style="min-width: 120px;">
                            Batalkan
                        </button>
                        <button type="button" 
                                data-bs-dismiss="modal"
                                class="btn btn-outline-secondary px-4 rounded-pill" 
                                style="min-width: 120px;">
                            Kembali
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Copy to Clipboard Script -->



<script>
function openCancelModal(no, jenis, tanggal, tahun, noLengkap = null) {
    document.getElementById('cancelInputNo').value = no;
    if(document.getElementById('cancelInputNoLengkap')) {
        document.getElementById('cancelInputNoLengkap').value = noLengkap;
    }
    document.getElementById('cancelInputJenis').value = jenis;
    document.getElementById('cancelInputTahun').value = tahun;
    
    // Populate text fields
    document.getElementById('cancelShowNoText').textContent = noLengkap ? noLengkap : no;
    document.getElementById('cancelShowJenisText').textContent = jenis;
    document.getElementById('cancelShowTanggalText').textContent = tanggal;
    
    var cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
    cancelModal.show();
}
</script>

<!-- Include Custom JavaScript -->
<script src="<?= base_url('js/penomoran-custom.js') ?>"></script>
<?= $this->endSection() ?>
