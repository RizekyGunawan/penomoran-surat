<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>
<!-- Header -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0" style="font-weight:700;color:#111827;">Persetujuan & Manajemen TU Unit</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">
            <i class="fas fa-user-shield me-1"></i> Kelola Usulan Akses dari TU Unit Kerja.
        </p>
    </div>
    <a href="<?= (int) session('user.role_id') === 1 ? base_url('admin/users') : base_url('tu-persuratan/dashboard') ?>" class="btn btn-outline-secondary btn-sm px-3">
        <i class="fas fa-arrow-left me-1"></i> <?= (int) session('user.role_id') === 1 ? 'Kembali' : 'Dashboard' ?>
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

<!-- Nav Tabs -->
<ul class="nav nav-tabs border-0 mb-3" id="approvalTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill px-4 fw-medium" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" style="color:#111827; background-color: transparent;">
            Menunggu Persetujuan
            <?php if (!empty($pendingList)): ?>
                <span class="badge bg-danger ms-1 rounded-pill"><?= count($pendingList) ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-4 fw-medium" id="aktif-tab" data-bs-toggle="tab" data-bs-target="#aktif" type="button" role="tab" style="color:#6b7280; background-color: transparent;">
            Daftar TU Unit Aktif
        </button>
    </li>
</ul>

<style>
    .nav-tabs .nav-link.active {
        background-color: #f1f5f9 !important;
        border: none !important;
        color: #0f172a !important;
    }
    .nav-tabs .nav-link {
        border: none !important;
    }
    .nav-tabs .nav-link:hover {
        color: #0f172a !important;
        background-color: #f8fafc;
    }
</style>

<div class="tab-content" id="approvalTabsContent">
    <!-- Tab: Menunggu Persetujuan -->
    <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
        <div class="card shadow-sm" style="border-radius:16px; border: 1px solid #e5e7eb;">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th style="width: 4%;">No</th>
                                <th style="width: 13%;">Tanggal Usulan</th>
                                <th style="width: 17%;">Nama Pengusul</th>
                                <th style="width: 13%;">Unit Kerja</th>
                                <th style="width: 17%;">Nama Staf</th>
                                <th style="width: 13%;">Alasan Pengajuan</th>
                                <th class="text-center" style="width: 9%;">Nota Dinas</th>
                                <th class="text-center" style="width: 14%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingList)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox mb-2" style="font-size:2rem;color:#cbd5e1;"></i>
                                        <p class="text-muted mb-0" style="font-size:.85rem;">Tidak ada usulan baru yang menunggu persetujuan.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($pendingList as $row): ?>
                                    <tr>
                                        <td style="font-size:.82rem;font-weight:600;"><?= $no++ ?></td>
                                        <td style="font-size:.82rem;color:#6b7280;"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                        <td style="font-size:.82rem;">
                                            <?php
                                            $namaPengusul = trim(($row['pengusul_gelar_depan'] ? $row['pengusul_gelar_depan'].' ' : '') . $row['nama_pengusul'] . ($row['pengusul_gelar_belakang'] ? ', '.$row['pengusul_gelar_belakang'] : ''));
                                            echo esc($namaPengusul);
                                            ?>
                                        </td>
                                        <td style="font-size:.82rem;color:#374151;">
                                            <?= esc($row['nama_unit_kerja'] ?? '-') ?>
                                        </td>
                                        <td style="font-size:.82rem;">
                                            <strong style="color:#111827;">
                                            <?php
                                            $namaPegawai = trim(($row['target_gelar_depan'] ? $row['target_gelar_depan'].' ' : '') . $row['nama_pegawai'] . ($row['target_gelar_belakang'] ? ', '.$row['target_gelar_belakang'] : ''));
                                            echo esc($namaPegawai);
                                            ?>
                                            </strong>
                                        </td>
                                        <!-- Kolom Alasan Pengajuan -->
                                        <td style="font-size:.82rem;color:#374151;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            <?= esc($row['alasan_usulan'] ?? '—') ?>
                                        </td>
                                        <!-- Kolom Nota Dinas (sebagai link file) -->
                                        <td class="text-center">
                                            <?php if (!empty($row['nota_dinas'])): ?>
                                                <a href="<?= base_url('tu-persuratan/approval-akses/nota-dinas/' . $row['id']) ?>"
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
                                        <!-- Kolom Aksi: Setujui (Hijau) + Tolak (Merah) -->
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <!-- Tombol Setujui (Hijau) -->
                                                <form action="<?= base_url('tu-persuratan/approval-akses/approve/' . $row['id']) ?>" method="POST"
                                                      onsubmit="return confirm('Setujui penugasan staf ini sebagai TU Unit?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="action-btn" title="Setujui"
                                                            style="color:#059669;border-color:#d1fae5;">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <!-- Tombol Tolak (Merah) -->
                                                <button type="button" class="action-btn text-danger" title="Tolak Usulan"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalTolak<?= $row['id'] ?>">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>

                                    </tr>

                                    <!-- Modal Tolak -->
                                    <div class="modal fade" id="modalTolak<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 10px 40px rgba(0,0,0,.12);">
                                                <form action="<?= base_url('tu-persuratan/approval-akses/reject/' . $row['id']) ?>" method="POST">
                                                    <?= csrf_field() ?>
                                                    <div class="modal-header pb-0 pt-4 px-4" style="border-bottom:1px solid #f3f4f6;">
                                                        <div>
                                                            <h5 class="modal-title fw-bold mb-0" style="color:#111827;font-size:1rem;">Tolak Usulan Penugasan</h5>
                                                            <p class="text-muted mb-0" style="font-size:.78rem;margin-top:2px;">Berikan alasan penolakan kepada Kabag pengusul</p>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body px-4 pt-3 pb-2">
                                                        <div class="px-3 py-2 rounded mb-3 d-flex align-items-start gap-2"
                                                             style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px !important;">
                                                            <i class="fas fa-user mt-1" style="color:#dc2626;font-size:.8rem;flex-shrink:0;"></i>
                                                            <div>
                                                                <strong style="font-size:.85rem;color:#7f1d1d;display:block;"><?= esc($namaPegawai) ?></strong>
                                                                <span style="font-size:.78rem;color:#991b1b;"><?= esc($row['nama_unit_kerja'] ?? '-') ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label" style="font-size:.8rem;font-weight:600;color:#374151;">
                                                                Alasan Penolakan <span class="text-danger">*</span>
                                                            </label>
                                                            <textarea class="form-control" name="alasan_penolakan" rows="3" required
                                                                      placeholder="Jelaskan alasan penolakan kepada Kabag pengusul..."
                                                                      style="resize:none;"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer pt-3 pb-4 px-4" style="border-top:1px solid #f3f4f6;">
                                                        <button type="button" class="btn btn-light px-4"
                                                                data-bs-dismiss="modal"
                                                                style="border-radius:10px;font-size:.875rem;color:#374151;border-color:#e5e7eb;">
                                                            Batal
                                                        </button>
                                                        <button type="submit" class="btn btn-danger px-4" style="border-radius:10px;font-size:.875rem;">
                                                            <i class="fas fa-times me-1"></i> Tolak Usulan
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Daftar TU Unit Aktif -->
    <div class="tab-pane fade" id="aktif" role="tabpanel" aria-labelledby="aktif-tab">
        <div class="card shadow-sm" style="border-radius:16px; border: 1px solid #e5e7eb;">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 22%;">Nama Staf</th>
                                <th style="width: 18%;">Unit Kerja</th>
                                <th style="width: 20%;">Diajukan Oleh</th>
                                <th style="width: 13%;">Tanggal Persetujuan</th>
                                <th class="text-center" style="width: 9%;">Nota Dinas</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($aktifList)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-users-slash mb-2" style="font-size:2rem;color:#cbd5e1;"></i>
                                        <p class="text-muted mb-0" style="font-size:.85rem;">Tidak ada staf TU Unit yang aktif dari jalur persetujuan.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($aktifList as $row): ?>
                                    <tr>
                                        <td style="font-size:.82rem;font-weight:600;"><?= $no++ ?></td>
                                        <td style="font-size:.82rem;">
                                            <strong style="color:#0f172a;">
                                            <?php
                                            $namaPegawai = trim(($row['target_gelar_depan'] ? $row['target_gelar_depan'].' ' : '') . $row['nama_pegawai'] . ($row['target_gelar_belakang'] ? ', '.$row['target_gelar_belakang'] : ''));
                                            echo esc($namaPegawai);
                                            ?>
                                            </strong>
                                        </td>
                                        <td style="font-size:.82rem;color:#374151;">
                                            <?= esc($row['nama_unit_kerja'] ?? '-') ?>
                                        </td>
                                        <td style="font-size:.82rem;">
                                            <?php
                                            $namaPengusul = trim(($row['pengusul_gelar_depan'] ? $row['pengusul_gelar_depan'].' ' : '') . $row['nama_pengusul'] . ($row['pengusul_gelar_belakang'] ? ', '.$row['pengusul_gelar_belakang'] : ''));
                                            echo esc($namaPengusul);
                                            ?>
                                        </td>
                                        <td style="font-size:.82rem;color:#6b7280;"><?= date('d/m/Y', strtotime($row['updated_at'])) ?></td>
                                        <!-- Kolom Nota Dinas -->
                                        <td class="text-center">
                                            <?php if (!empty($row['nota_dinas'])): ?>
                                                <a href="<?= base_url('tu-persuratan/approval-akses/nota-dinas/' . $row['id']) ?>"
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
                                            <button type="button" class="action-btn text-danger" data-bs-toggle="modal" data-bs-target="#modalRevoke<?= $row['id'] ?>" title="Cabut Akses">
                                                <i class="fas fa-ban"></i>
                                            </button>

                                            <!-- Modal Revoke -->
                                            <div class="modal fade" id="modalRevoke<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content" style="border-radius:16px; border:none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                                                        <form action="<?= base_url('tu-persuratan/approval-akses/revoke/' . $row['id']) ?>" method="POST">
                                                            <?= csrf_field() ?>
                                                            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                                                                <h5 class="modal-title fw-bold text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Cabut Hak Akses</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body px-4 py-4">
                                                                <p style="font-size:.9rem;color:#374151;">Anda akan mencabut peran <strong>TU Unit</strong> dari staf berikut:</p>
                                                                <div class="p-3 rounded mb-3" style="background-color:#fef2f2; border: 1px solid #fca5a5;">
                                                                    <strong class="d-block" style="color:#7f1d1d;"><?= esc($namaPegawai) ?></strong>
                                                                    <span style="font-size:.8rem;color:#991b1b;"><?= esc($row['nama_unit_kerja'] ?? '-') ?></span>
                                                                </div>
                                                                <p style="font-size:.85rem;color:#6b7280;margin-bottom:0;">Setelah dicabut, staf ini tidak akan bisa lagi mengakses modul TU Unit, membuat nomor surat, maupun melihat rekap surat unitnya.</p>
                                                            </div>
                                                            <div class="modal-footer border-top-0 px-4 pb-4">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px;">Batal</button>
                                                                <button type="submit" class="btn btn-danger" style="border-radius:8px;">Ya, Cabut Akses</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
