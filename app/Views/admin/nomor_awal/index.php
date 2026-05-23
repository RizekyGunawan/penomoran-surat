<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
    // Buka modal otomatis jika ada error validasi dari store()
    $hasModalErrors = ! empty(session('modal_errors'));
    $modalOldInput  = session('modal_old') ?? [];
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0" style="font-weight:700;color:#111827;">Konfigurasi Nomor Awal</h1>
    </div>
    <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalTambahConfig">
        <i class="fas fa-plus me-1"></i> Tambah / Edit Konfigurasi
    </button>
</div>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Beranda</a></li>
        <li class="breadcrumb-item">Admin</li>
        <li class="breadcrumb-item active">Nomor Awal</li>
    </ol>
</nav>

<!-- Card -->
<div class="card shadow card-table">
    <div class="card-body p-4">

        <?php if (empty($configs)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-1"></i>
                Belum ada konfigurasi nomor awal. Semua penomoran surat akan mengikuti urutan default database (selalu dimulai dari 1 jika kosong).
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="custom-table" style="min-width: 600px;">
                    <thead>
                        <tr>
                            <th style="width:50px;">No</th>
                            <th>Jenis Dokumen</th>
                            <th class="th-center" style="width:100px;">Tahun</th>
                            <th class="th-center" style="width:150px;">Nomor Awal</th>
                            <th class="th-center" style="width:100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($configs as $i => $cfg): ?>
                            <tr>
                                <td class="text-muted" style="font-size:.85rem;"><?= $i + 1 ?></td>
                                <td>
                                    <span style="font-weight:600;color:#1f2937;"><?= esc($cfg['jenis_dokumen']) ?></span>
                                </td>
                                <td class="td-center">
                                    <span class="badge bg-light text-dark border"><?= esc($cfg['tahun']) ?></span>
                                </td>
                                <td class="td-center">
                                    <span style="font-size:1.1rem;font-weight:700;color:#059669;">
                                        <?= number_format($cfg['nomor_awal']) ?>
                                    </span>
                                </td>
                                <td class="td-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <!-- Tombol Hapus memicu Modal -->
                                        <button type="button"
                                                class="action-btn text-danger"
                                                title="Hapus Konfigurasi"
                                                onclick="confirmDelete(
                                                    '<?= base_url('admin/nomor-awal/delete/' . $cfg['id']) ?>',
                                                    '<?= esc($cfg['jenis_dokumen']) ?>',
                                                    '<?= esc($cfg['tahun']) ?>'
                                                )">
                                            <i class="fas fa-trash-alt" style="font-size:.72rem;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif ?>

    </div>
</div>

<!-- ==========================================
     MODAL — Tambah / Edit Konfigurasi
=========================================== -->
<div class="modal fade" id="modalTambahConfig" tabindex="-1" aria-labelledby="modalTambahConfigLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content" style="border:none;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.15);">

            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:1.25rem 1.5rem;">
                <div>
                    <h5 class="modal-title mb-0" id="modalTambahConfigLabel" style="font-weight:700;color:#111827;">
                        <i class="fas fa-cog me-2" style="color:#6d28d9;font-size:.95rem;"></i>Set Nomor Awal
                    </h5>
                    <p class="text-muted mb-0" style="font-size:.76rem;margin-top:2px;">Jika kombinasi sudah ada, data akan diupdate.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <form method="POST" action="<?= base_url('admin/nomor-awal/store') ?>" id="formTambahConfig">
                <?= csrf_field() ?>
                <div class="modal-body" style="padding:1.5rem;">

                    <?php if ($hasModalErrors): ?>
                        <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:.8rem;border-radius:10px;">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            <strong>Perbaiki kesalahan berikut:</strong>
                            <ul class="mb-0 mt-1 ps-3">
                                <?php foreach (session('modal_errors') as $err): ?>
                                    <li><?= esc($err) ?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    <?php endif ?>

                    <!-- Jenis Dokumen -->
                    <div class="mb-3">
                        <label for="m_jenis_dokumen" class="form-label fw-semibold" style="font-size:.82rem;color:#374151;">
                            Jenis Dokumen <span class="text-danger">*</span>
                        </label>
                        <select id="m_jenis_dokumen" name="jenis_dokumen" class="form-select" required>
                            <option value="">— Pilih Jenis Dokumen —</option>
                            <?php foreach ($jenisDokumenList as $jenis): ?>
                                <?php $selected = ($modalOldInput['jenis_dokumen'] ?? '') === $jenis ? 'selected' : ''; ?>
                                <option value="<?= esc($jenis) ?>" <?= $selected ?>><?= esc($jenis) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div class="row">
                        <!-- Tahun -->
                        <div class="col-md-6 mb-3">
                            <label for="m_tahun" class="form-label fw-semibold" style="font-size:.82rem;color:#374151;">
                                Tahun <span class="text-danger">*</span>
                            </label>
                            <input
                                type="number"
                                id="m_tahun"
                                name="tahun"
                                class="form-control"
                                min="2000"
                                max="2100"
                                value="<?= esc($modalOldInput['tahun'] ?? $currentYear) ?>"
                                required
                            >
                        </div>

                        <!-- Nomor Awal -->
                        <div class="col-md-6 mb-3">
                            <label for="m_nomor_awal" class="form-label fw-semibold" style="font-size:.82rem;color:#374151;">
                                Nomor Awal <span class="text-danger">*</span>
                            </label>
                            <input
                                type="number"
                                id="m_nomor_awal"
                                name="nomor_awal"
                                class="form-control"
                                min="1"
                                value="<?= esc($modalOldInput['nomor_awal'] ?? '1') ?>"
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="form-text" style="font-size:.75rem;">
                        <i class="fas fa-info-circle text-primary me-1"></i>
                        Contoh: Jika diset <strong>50</strong>, maka surat pertama yang dibuat akan mendapat nomor urut 50. Konfigurasi <strong>diabaikan</strong> kalau nomor surat terakhir di database sudah melebihi 50.
                    </div>

                </div>

                <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:1rem 1.5rem;gap:.5rem;">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- ==========================================
     MODAL — Konfirmasi Hapus
=========================================== -->
<form method="POST" id="formDeleteConfig" style="display:none;">
    <?= csrf_field() ?>
</form>

<div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content" style="border:none;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.15);">
            <div class="modal-header" style="border-bottom:none;padding:1.5rem 1.5rem 0.5rem;">
                <h5 class="modal-title mb-0" style="font-weight:700;color:#111827;">
                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>Hapus Konfigurasi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body" style="padding:1rem 1.5rem;">
                <p class="mb-0 text-muted" style="font-size:.9rem;" id="pesanHapus"></p>
                <p class="mb-0 text-muted mt-2" style="font-size:.85rem;">Penomoran surat selanjutnya akan kembali mengikuti urutan default yang ada di database.</p>
            </div>
            <div class="modal-footer" style="border-top:none;padding:1rem 1.5rem 1.5rem;gap:.5rem;">
                <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm px-4" id="btnProsesHapus" style="background:#dc2626;color:#fff;border-radius:8px;">Hapus</button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     SCRIPTS
=========================================== -->
<script>
// Buka modal otomatis jika validasi backend gagal
<?php if ($hasModalErrors): ?>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('modalTambahConfig')).show();
    });
<?php endif ?>

// Konfirmasi hapus
function confirmDelete(url, jenis, tahun) {
    var modal = new bootstrap.Modal(document.getElementById('modalConfirmDelete'));
    document.getElementById('pesanHapus').innerHTML = 'Yakin ingin menghapus konfigurasi nomor awal untuk <strong>' + jenis + '</strong> tahun <strong>' + tahun + '</strong>?';
    
    document.getElementById('btnProsesHapus').onclick = function() {
        var form = document.getElementById('formDeleteConfig');
        form.action = url;
        modal.hide();
        form.submit();
    };
    
    modal.show();
}
</script>

<?= $this->endSection() ?>
