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

<!-- Modal Konfirmasi Pembatalan (Auto Show) -->
<div class="modal fade show" id="modalPembatalan" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" style="display: block; background-color: rgba(0,0,0,0.5);">
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
                    <input type="hidden" name="NO" value="<?= esc($penomoran['NO']) ?>">
                    <input type="hidden" name="JENIS_DOKUMEN" value="<?= esc($penomoran['JENIS_DOKUMEN']) ?>">
                    <input type="hidden" name="TAHUN" value="<?= esc($penomoran['TAHUN']) ?>">
                    
                    <!-- Information Fields -->
                    <div class="text-start mb-3">
                        <!-- Nomor Surat -->
                        <div class="mb-3">
                            <label class="text-muted small mb-1">Nomor Surat</label>
                            <div class="p-2 bg-light rounded">
                                <strong><?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?></strong>
                            </div>
                        </div>
                        
                        <!-- Jenis Surat & Tanggal Surat (2 columns) -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="text-muted small mb-1">Jenis Surat</label>
                                <div class="p-2 bg-light rounded">
                                    <span class="small"><?= esc($penomoran['JENIS_DOKUMEN']) ?></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small mb-1">Tanggal Surat</label>
                                <div class="p-2 bg-light rounded">
                                    <span class="small"><?= formatDate($penomoran['TANGGAL']) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Alasan Pembatalan -->
                        <div class="mb-3">
                            <label for="ALASAN_PEMBATALAN" class="text-muted small mb-1">
                                Alasan Pembatalan<span class="text-danger">*</span>
                            </label>
                            <textarea 
                                class="form-control <?= session('errors.ALASAN_PEMBATALAN') ? 'is-invalid' : '' ?>" 
                                id="ALASAN_PEMBATALAN" 
                                name="ALASAN_PEMBATALAN" 
                                rows="4" 
                                required
                                placeholder="Jelaskan alasan pembatalan nomor surat ini"
                                style="border-radius: 8px;"><?= old('ALASAN_PEMBATALAN') ?></textarea>
                            
                            <?php if (session('errors.ALASAN_PEMBATALAN')): ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.ALASAN_PEMBATALAN') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Buttons -->
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" 
                                class="btn btn-danger flex-fill" 
                                style="border-radius: 8px;">
                            Batalkan
                        </button>
                        <a href="<?= base_url('penomoran/detail/' . $penomoran['NO'] . '/' . urlencode($penomoran['JENIS_DOKUMEN']) . '/' . $penomoran['TAHUN']) ?>" 
                           class="btn btn-outline-secondary flex-fill" 
                           style="border-radius: 8px;">
                            Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto show modal on page load
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('modalPembatalan'));
    modal.show();
});
</script>
<?= $this->endSection() ?>
