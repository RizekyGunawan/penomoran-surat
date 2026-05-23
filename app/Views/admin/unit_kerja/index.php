<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
    $hasModalErrors = !empty(session('modal_errors'));
    $modalOld       = session('modal_old') ?? [];
    // Sertakan row yang sudah dihapus soft untuk tampilan toggle
    $all            = $unitKerjaList;
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0" style="font-weight:700;color:#111827;">Kelola Unit Kerja</h1>
    <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalTambahUK">
        <i class="fas fa-plus me-1"></i> Tambah Unit Kerja
    </button>
</div>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Beranda</a></li>
        <li class="breadcrumb-item">Admin</li>
        <li class="breadcrumb-item active">Unit Kerja</li>
    </ol>
</nav>

<!-- Tabel -->
<div class="card shadow card-table">
    <div class="card-body p-4">
        <?php if (empty($unitKerjaList)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-1"></i> Belum ada data unit kerja.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width:46px;">No</th>
                            <th>Nama Unit Kerja</th>
                            <th class="text-center" style="width:100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unitKerjaList as $i => $uk): ?>
                        <?php $deleted = !empty($uk['deleted_at']); ?>
                        <tr class="<?= $deleted ? 'opacity-50' : '' ?>">
                            <td class="text-muted" style="font-size:.78rem;"><?= $i + 1 ?></td>
                            <td>
                                <span style="font-weight:500;color:#111827;"><?= esc($uk['nama_unit_kerja']) ?></span>
                                <?php if ($deleted): ?>
                                    <span class="badge bg-secondary ms-1" style="font-size:.65rem;">Nonaktif</span>
                                <?php endif ?>
                                <?php if (!empty($uk['parent_id']) && $uk['parent_id'] > 0): ?>
                                    <span class="text-muted" style="font-size:.72rem;"> (sub-unit)</span>
                                <?php endif ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="action-btn text-primary" title="Edit"
                                            onclick="openEditModal(<?= htmlspecialchars(json_encode($uk), ENT_QUOTES) ?>)">
                                        <i class="fas fa-pen" style="font-size:.72rem;"></i>
                                    </button>
                                    <form method="POST" action="<?= base_url('admin/unit-kerja/toggle/' . $uk['id']) ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit"
                                                class="action-btn <?= $deleted ? 'text-success' : 'text-warning' ?>"
                                                title="<?= $deleted ? 'Aktifkan' : 'Nonaktifkan' ?>">
                                            <i class="fas <?= $deleted ? 'fa-check' : 'fa-ban' ?>" style="font-size:.72rem;"></i>
                                        </button>
                                    </form>
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

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambahUK" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content" style="border:none;border-radius:16px;">
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:1.25rem 1.5rem;">
                <h5 class="modal-title" style="font-weight:700;color:#111827;">
                    <i class="fas fa-building me-2" style="color:#6d28d9;"></i>Tambah Unit Kerja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= base_url('admin/unit-kerja/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-body" style="padding:1.5rem;">
                    <?php if ($hasModalErrors): ?>
                        <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:.8rem;border-radius:10px;">
                            <ul class="mb-0 ps-3">
                                <?php foreach (session('modal_errors') as $err): ?>
                                    <li><?= esc($err) ?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    <?php endif ?>
                    <div>
                        <label class="form-label fw-semibold" style="font-size:.82rem;">
                            Nama Unit Kerja <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama_unit_kerja" class="form-control"
                               placeholder="Contoh: Bagian Kepegawaian"
                               value="<?= esc($modalOld['nama_unit_kerja'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:1rem 1.5rem;">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEditUK" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content" style="border:none;border-radius:16px;">
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:1.25rem 1.5rem;">
                <h5 class="modal-title" style="font-weight:700;color:#111827;">
                    <i class="fas fa-pen me-2" style="color:#6d28d9;"></i>Edit Unit Kerja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formEditUK">
                <?= csrf_field() ?>
                <div class="modal-body" style="padding:1.5rem;">
                    <div>
                        <label class="form-label fw-semibold" style="font-size:.82rem;">
                            Nama Unit Kerja <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama_unit_kerja" id="editNama" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:1rem 1.5rem;">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="fas fa-save me-1"></i> Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($hasModalErrors): ?>
<script>
    document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('modalTambahUK')).show());
</script>
<?php endif ?>

<script>
function openEditModal(uk) {
    document.getElementById('editNama').value    = uk.nama_unit_kerja;
    document.getElementById('formEditUK').action = '/admin/unit-kerja/update/' + uk.id;
    new bootstrap.Modal(document.getElementById('modalEditUK')).show();
}
</script>

<?= $this->endSection() ?>
