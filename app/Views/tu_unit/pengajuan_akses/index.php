<?= $this->extend('layouts/main'); ?>

<?= $this->section('css') ?>
<style>
    /* Styling native select agar smooth dan seragam dengan project */
    #pegawai_user_id {
        font-size: .875rem;
        color: #111827;
        border-color: #d1d5db;
        border-radius: 12px !important;
        padding: .5rem .75rem;
        transition: border-color .2s ease, box-shadow .2s ease;
        cursor: pointer;
        background-color: #ffffff;
    }
    #pegawai_user_id:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,.1);
        outline: none;
    }
    /* Dropzone nota dinas */
    .dropzone-nota {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 20px 16px;
        text-align: center;
        cursor: pointer;
        background: #f9fafb;
        transition: border-color .2s ease, background .2s ease;
    }
    .dropzone-nota:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    .dropzone-nota.valid {
        border-color: #10b981;
        background: #f0fdf4;
    }
    .dropzone-nota.invalid {
        border-color: #ef4444;
        background: #fef2f2;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content'); ?>
<!-- Header -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0" style="font-weight:700;color:#111827;">Pengelolaan Akses TU Unit</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">
            <i class="fas fa-users me-1"></i> Kelola Penugasan Staf TU Unit Kerja
        </p>
    </div>
    <a href="<?= base_url('tu-unit/dashboard') ?>" class="btn btn-outline-secondary px-4 rounded-pill">
        <i class="fas fa-arrow-left me-1"></i> Dashboard
    </a>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius:12px;" role="alert">
        <i class="fas fa-check-circle me-1"></i> <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius:12px;" role="alert">
        <i class="fas fa-exclamation-circle me-1"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
    <div class="card-body p-4 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1" style="font-weight:600;color:#111827;">Daftar Usulan Penugasan</h5>
            <p class="text-muted mb-0" style="font-size:.85rem;">Daftar Pengajuan Penugasan Staf TU Unit Kerja</p>
        </div>
        <button type="button" class="btn btn-success px-4 rounded-pill d-inline-flex align-items-center justify-content-center" style="background-color: #10b981; border-color: #10b981; font-weight: 600; color: white; gap: 6px;" data-bs-toggle="modal" data-bs-target="#modalUsulan"><i class="fas fa-user-plus"></i> Usulkan Staf Baru</button>
    </div>
</div>

<div class="card shadow-sm" style="border-radius:16px; border: 1px solid #e5e7eb;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 15%;">Tanggal Usulan</th>
                        <th style="width: 22%;">Nama Staf</th>
                        <th style="width: 18%;">Alasan Pengajuan</th>
                        <th class="text-center" style="width: 10%;">Nota Dinas</th>
                        <th class="text-center" style="width: 12%;">Status</th>
                        <th class="text-center">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pengajuanList)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-folder-open mb-2" style="font-size:2rem;color:#cbd5e1;"></i>
                                <p class="text-muted mb-0" style="font-size:.85rem;">Belum ada riwayat usulan akses.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($pengajuanList as $row): ?>
                            <tr>
                                <td style="font-size:.82rem;font-weight:600;"><?= $no++ ?></td>
                                <td style="font-size:.82rem;color:#6b7280;"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                <td style="font-size:.82rem;">
                                    <strong style="color:#111827;">
                                    <?php 
                                    $nama = trim(($row['gelar_depan'] ? $row['gelar_depan'].' ' : '') . $row['nama_pegawai'] . ($row['gelar_belakang'] ? ', '.$row['gelar_belakang'] : ''));
                                    echo esc($nama ?: 'User ID: '.$row['pegawai_user_id']);
                                    ?>
                                    </strong>
                                </td>
                                <td style="font-size:.82rem;color:#374151;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    <?= esc($row['alasan_usulan'] ?? '-') ?>
                                </td>
                                <!-- Kolom Nota Dinas (sebagai link file) -->
                                <td class="text-center">
                                    <?php if (!empty($row['nota_dinas'])): ?>
                                        <a href="<?= base_url('tu-unit/pengajuan-akses/nota-dinas/' . $row['id']) ?>"
                                           target="_blank"
                                           title="Buka Nota Dinas"
                                           style="display:inline-flex;align-items:center;gap:4px;text-decoration:none;color:#dc2626;font-size:.8rem;font-weight:500;">
                                            <i class="fas fa-file-pdf" style="font-size:1.1rem;"></i>
                                            <span style="font-size:.75rem;">PDF</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:.8rem;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($row['status'] === 'PENDING'): ?>
                                        <span class="status-badge" style="background:#fef3c7;color:#d97706;padding:4px 10px;border-radius:99px;font-size:.7rem;font-weight:600;"><i class="fas fa-clock me-1"></i> Menunggu</span>
                                    <?php elseif ($row['status'] === 'APPROVED'): ?>
                                        <span class="status-badge" style="background:#d1fae5;color:#059669;padding:4px 10px;border-radius:99px;font-size:.7rem;font-weight:600;"><i class="fas fa-check me-1"></i> Disetujui</span>
                                    <?php elseif ($row['status'] === 'REJECTED'): ?>
                                        <span class="status-badge" style="background:#fee2e2;color:#dc2626;padding:4px 10px;border-radius:99px;font-size:.7rem;font-weight:600;"><i class="fas fa-times me-1"></i> Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'PENDING'): ?>
                                        <!-- PENDING: belum ada keterangan -->
                                        <span style="font-size:.82rem;color:#9ca3af;">—</span>
                                    <?php elseif ($row['status'] === 'APPROVED'): ?>
                                        <!-- APPROVED: tampilkan narasi persetujuan (hijau) -->
                                        <span style="font-size:.82rem;color:#059669;white-space:normal;display:inline-block;text-align:justify;">
                                            <?= esc($row['alasan_penolakan'] ?? '—') ?>
                                        </span>
                                    <?php elseif ($row['status'] === 'REJECTED'): ?>
                                        <!-- REJECTED: tampilkan alasan penolakan/pencabutan (merah) -->
                                        <span style="font-size:.82rem;color:#dc2626;white-space:normal;display:inline-block;text-align:justify;">
                                            <?= esc($row['alasan_penolakan'] ?? '—') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Usulan -->
<div class="modal fade" id="modalUsulan" tabindex="-1" aria-labelledby="modalUsulanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 10px 40px rgba(0,0,0,.12);">
            <!-- enctype wajib ada untuk upload file -->
            <form action="<?= base_url('tu-unit/pengajuan-akses/store') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="modal-header pb-0 pt-4 px-4" style="border-bottom:1px solid #f3f4f6;">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalUsulanLabel" style="color:#111827;font-size:1rem;">Usulkan Staf Menjadi TU Unit</h5>
                        <p class="text-muted mb-0" style="font-size:.78rem;margin-top:2px;">Pengajuan akan direview oleh TU Persuratan</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pt-3 pb-2">

                    <!-- 1. Pilih Pegawai -->
                    <div class="mb-3">
                        <label for="pegawai_user_id" class="form-label" style="font-size:.8rem;font-weight:600;color:#374151;">
                            Pilih Pegawai <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" id="pegawai_user_id" name="pegawai_user_id" required>
                            <option value="">-- Pilih Pegawai --</option>
                            <?php foreach ($kandidatPegawai as $pegawai): ?>
                                <?php 
                                $nama = trim(($pegawai['gelar_depan'] ? $pegawai['gelar_depan'].' ' : '') . $pegawai['nama'] . ($pegawai['gelar_belakang'] ? ', '.$pegawai['gelar_belakang'] : ''));
                                $namaTampil = esc($nama ?: $pegawai['username_ldap']);
                                ?>
                                <option value="<?= $pegawai['username_ldap'] ?>"><?= $namaTampil ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 2. Nota Dinas Penugasan (WAJIB) -->
                    <div class="mb-3">
                        <label class="form-label d-flex align-items-center gap-1" style="font-size:.8rem;font-weight:600;color:#374151;">
                            <i class="fas fa-file-pdf" style="color:#1e3a8a;"></i>
                            Nota Dinas Penugasan <span class="text-danger">*</span>
                        </label>

                        <!-- Dropzone area -->
                        <div id="dropzone-nota-dinas" class="dropzone-nota"
                             onclick="document.getElementById('nota_dinas').click()">
                            <i class="fas fa-cloud-upload-alt" style="font-size:1.4rem;color:#6b7280;"></i>
                            <p class="mb-0 mt-1" style="font-size:.82rem;color:#6b7280;">
                                Klik untuk memilih file PDF
                            </p>
                            <small style="color:#9ca3af;font-size:.75rem;">Format: PDF &bull; Maks. 1 MB</small>
                        </div>

                        <!-- Input file tersembunyi -->
                        <input type="file" id="nota_dinas" name="nota_dinas" accept="application/pdf" class="d-none">

                        <!-- Preview nama file -->
                        <div id="preview-nota-dinas" class="d-none mt-2 px-3 py-2 rounded d-flex align-items-center gap-2"
                             style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px !important;">
                            <i class="fas fa-file-pdf" style="color:#10b981;font-size:1rem;"></i>
                            <span id="nama-file-nota" style="font-size:.82rem;color:#065f46;flex:1;word-break:break-all;"></span>
                            <button type="button" onclick="hapusFileNotaDinas()" class="btn-close" style="font-size:.55rem;flex-shrink:0;" aria-label="Hapus"></button>
                        </div>

                        <!-- Pesan error validasi file -->
                        <div id="error-nota-dinas" class="d-none mt-1">
                            <small class="text-danger d-flex align-items-center gap-1" id="pesan-error-nota">
                                <i class="fas fa-exclamation-circle"></i> <span></span>
                            </small>
                        </div>
                    </div>

                    <!-- 3. Alasan Pengajuan (Opsional) -->
                    <div class="mb-2">
                        <label for="alasan_usulan" class="form-label" style="font-size:.8rem;font-weight:600;color:#374151;">
                            Alasan Pengajuan
                            <span class="fw-normal" style="color:#9ca3af;">(Opsional)</span>
                        </label>
                        <textarea class="form-control" id="alasan_usulan" name="alasan_usulan" rows="2"
                                  placeholder="Contoh: Ditugaskan sebagai PIC kearsipan unit..."
                                  style="resize:none;"></textarea>
                    </div>

                    <!-- Info nota dinas -->
                    <div class="px-3 py-2 rounded d-flex align-items-start gap-2 mt-1"
                         style="background:#eff6ff;border:1px solid #dbeafe;border-radius:10px !important;">
                        <i class="fas fa-info-circle mt-1" style="color:#1e3a8a;font-size:.8rem;flex-shrink:0;"></i>
                        <small style="color:#1e3a8a;font-size:.75rem;line-height:1.5;">
                            Nota Dinas Penugasan wajib dilampirkan sebagai bukti resmi penugasan yang akan diverifikasi oleh TU Persuratan.
                        </small>
                    </div>

                </div>

                <div class="modal-footer pt-3 pb-4 px-4" style="border-top:1px solid #f3f4f6;">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" id="btnKirimUsulan" class="btn btn-success px-4 rounded-pill d-inline-flex align-items-center justify-content-center" style="background-color: #10b981; border-color: #10b981; font-weight: 600; color: white; gap: 6px;"><i class="fas fa-paper-plane"></i> Kirim Usulan</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {
    // Reset semua field saat modal ditutup
    $('#modalUsulan').on('hidden.bs.modal', function () {
        $('#pegawai_user_id').val('');
        $('#alasan_usulan').val('');
        hapusFileNotaDinas();
    });

    // Validasi wajib nota dinas sebelum form disubmit
    $('form').on('submit', function (e) {
        const inputFile = document.getElementById('nota_dinas');
        if (!inputFile.files || inputFile.files.length === 0) {
            e.preventDefault();
            // Tampilkan error pada dropzone
            const dropzone = document.getElementById('dropzone-nota-dinas');
            const errorEl  = document.getElementById('error-nota-dinas');
            const pesanEl  = errorEl.querySelector('span');
            dropzone.classList.remove('valid');
            dropzone.classList.add('invalid');
            pesanEl.textContent = 'Nota Dinas Penugasan wajib dilampirkan.';
            errorEl.classList.remove('d-none');
            // Scroll ke dropzone
            dropzone.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});

// Validasi file saat user memilih (cek tipe PDF dan ukuran maks 1MB)
document.getElementById('nota_dinas').addEventListener('change', function () {
    const file      = this.files[0];
    const dropzone  = document.getElementById('dropzone-nota-dinas');
    const errorEl   = document.getElementById('error-nota-dinas');
    const pesanEl   = errorEl.querySelector('span');
    const previewEl = document.getElementById('preview-nota-dinas');
    const namaEl    = document.getElementById('nama-file-nota');

    // Reset state
    errorEl.classList.add('d-none');
    previewEl.classList.add('d-none');
    dropzone.classList.remove('valid', 'invalid');

    if (!file) return;

    // Validasi tipe file: harus PDF
    if (file.type !== 'application/pdf') {
        pesanEl.textContent = 'File harus berformat PDF.';
        errorEl.classList.remove('d-none');
        dropzone.classList.add('invalid');
        this.value = '';
        return;
    }

    // Validasi ukuran: maks 1 MB
    if (file.size > 1 * 1024 * 1024) {
        const ukuranMB = (file.size / 1024 / 1024).toFixed(2);
        pesanEl.textContent = `Ukuran file (${ukuranMB} MB) melebihi batas 1 MB.`;
        errorEl.classList.remove('d-none');
        dropzone.classList.add('invalid');
        this.value = '';
        return;
    }

    // File valid — tampilkan preview dan ubah dropzone jadi hijau
    namaEl.textContent = file.name;
    previewEl.classList.remove('d-none');
    dropzone.classList.add('valid');
});

// Fungsi hapus file yang dipilih
function hapusFileNotaDinas() {
    document.getElementById('nota_dinas').value = '';
    document.getElementById('preview-nota-dinas').classList.add('d-none');
    document.getElementById('error-nota-dinas').classList.add('d-none');
    const dropzone = document.getElementById('dropzone-nota-dinas');
    dropzone.classList.remove('valid', 'invalid');
}
</script>
<?= $this->endSection() ?>

