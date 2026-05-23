<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$p = $pagination;
$searchQs = $search ? '&search=' . urlencode($search) : '';
$globalNo = ($p['currentPage'] - 1) * $p['perPage'];
$showFrom = $p['total'] > 0 ? $globalNo + 1 : 0;
$showTo = min($globalNo + $p['perPage'], $p['total']);

// Buka modal otomatis jika ada error validasi dari store()
$hasModalErrors = !empty(session('modal_errors'));
$modalOldInput = session('modal_old') ?? [];
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0" style="font-weight:700;color:#111827;">Manage User</h1>
    </div>
    <button type="button" class="btn btn-success px-4 rounded-pill d-inline-flex align-items-center justify-content-center" style="background-color: #10b981; border-color: #10b981; font-weight: 600; color: white; gap: 6px;" data-bs-toggle="modal" data-bs-target="#modalTambahUser"><i class="fas fa-plus"></i> Tambah User</button>
</div>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Beranda</a></li>
        <li class="breadcrumb-item">Admin</li>
        <li class="breadcrumb-item active">Manage User</li>
    </ol>
</nav>

<!-- Card -->
<div class="card shadow card-table">
    <div class="card-body p-4">

        <!-- Tabs Manajemen User -->
        <ul class="nav nav-pills mb-4" style="gap: 5px;">
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'pegawai' ? 'active' : '' ?>"
                    href="<?= base_url('admin/users?tab=pegawai') ?>">
                    <i class="fas fa-users me-1"></i> Pegawai
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'tu-unit' ? 'active' : '' ?>"
                    href="<?= base_url('admin/users?tab=tu-unit') ?>">
                    <i class="fas fa-building me-1"></i> TU Unit
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'tu-persuratan' ? 'active' : '' ?>"
                    href="<?= base_url('admin/users?tab=tu-persuratan') ?>">
                    <i class="fas fa-landmark me-1"></i> TU Persuratan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'admin' ? 'active' : '' ?>"
                    href="<?= base_url('admin/users?tab=admin') ?>">
                    <i class="fas fa-shield-alt me-1"></i> Admin
                </a>
            </li>
        </ul>

        <!-- Search Bar -->
        <div class="search-container mb-4" style="margin-bottom:0!important;">
            <form method="GET" action="<?= base_url('admin/users') ?>" class="search-wrapper" style="width:380px;">
                <input type="hidden" name="tab" value="<?= esc($activeTab) ?>">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" class="search-input" placeholder="Cari Username…"
                    value="<?= esc($search) ?>">
            </form>

            <div class="d-flex align-items-center gap-2">
                <?php if ($search): ?>
                    <a href="<?= base_url('admin/users?tab=' . esc($activeTab)) ?>"
                        class="btn btn-outline-secondary px-4 rounded-pill">
                        <i class="fas fa-times me-1"></i> Reset
                    </a>
                <?php endif ?>
                <span class="text-muted" style="font-size:.8rem;white-space:nowrap;">
                    Total <strong><?= number_format($p['total']) ?></strong> user
                </span>
            </div>
        </div>

        <hr class="my-3" style="border-color:#f3f4f6;">

        <!-- Tabel -->
        <?php if (empty($users)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-1"></i>
                <?= $search
                    ? 'Tidak ada user yang cocok dengan pencarian "<strong>' . esc($search) . '</strong>".'
                    : 'Belum ada data user.' ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width:46px;">No</th>
                            <th>Username Pegawai</th>
                            <th>Unit Kerja</th>
                            <th style="width:160px;">Role</th>
                            <th class="text-center" style="width:110px;">Status</th>
                            <th class="text-center" style="width:80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $i => $u): ?>
                            <?php
                            $isSelf = ((int) $u['id'] === (int) session('user.id'));
                            $isSuperAdmin = ($u['username_ldap'] === 'superadmin');
                            $rowStyle = $isSuperAdmin ? 'background-color: #f9fafb; opacity: 0.85;' : '';
                            ?>
                            <tr style="<?= $rowStyle ?>; cursor:pointer;" onclick="if(!event.target.closest('.action-btn')) { var m = new bootstrap.Modal(document.getElementById('modalDetailUser<?= $u['id'] ?>')); m.show(); }">
                                <td class="text-muted" style="font-size:.78rem;">
                                    <?php if ($isSuperAdmin): ?>
                                        <i class="fas fa-thumbtack top-0 text-muted"
                                            style="font-size:.7rem; transform: rotate(45deg);"></i>
                                    <?php else: ?>
                                        <?= $globalNo + $i + 1 ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $namaLengkap = !empty($u['nama_lengkap']) ? esc($u['nama_lengkap']) : esc($u['username_ldap']);
                                    if (!empty($u['gelar_depan'])) {
                                        $namaLengkap = esc($u['gelar_depan']) . ' ' . $namaLengkap;
                                    }
                                    if (!empty($u['gelar_belakang'])) {
                                        $namaLengkap = $namaLengkap . ', ' . esc($u['gelar_belakang']);
                                    }
                                    ?>
                                    <div class="text-truncate"
                                        style="max-width: 250px; font-weight:<?= $isSuperAdmin ? '600' : '500' ?>;color:#111827; margin-bottom: 2px;" title="<?= esc($namaLengkap) ?>">
                                        <?= $namaLengkap ?>
                                    </div>
                                    <?php if (!empty($u['nama_lengkap'])): ?>
                                        <div style="font-size:.8rem;color:#6b7280;">
                                            <i class="fas fa-user-tag me-1" style="opacity:0.7;"></i><?= esc($u['username_ldap']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isSuperAdmin): ?>
                                        <span style="font-size:.8rem;color:#9ca3af;">—</span>
                                    <?php else: ?>
                                        <div style="font-size:.85rem;color:#374151;max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                                            title="<?= !empty($u['nama_unit_kerja']) ? esc($u['nama_unit_kerja']) : '' ?>">
                                            <?= !empty($u['nama_unit_kerja']) ? esc($u['nama_unit_kerja']) : '<span class="text-muted" style="font-style:italic;">Tidak Ada</span>' ?>
                                        </div>
                                    <?php endif ?>
                                </td>
                                <td>
                                    <?php
                                    $role = (int) $u['role_id'];
                                    $label = $u['role_label'] ?? 'Pegawai';
                                    ?>
                                    <?php if ($role === 1): ?>
                                        <span class="badge" style="background:#ede9fe;color:#6d28d9;font-weight:600;">
                                            <i class="fas fa-shield-alt me-1" style="font-size:.65rem;"></i>Admin
                                        </span>
                                    <?php elseif ($role === 3): ?>
                                        <span class="badge" style="background:#dbeafe;color:#1e3a8a;font-weight:600;">
                                            <i class="fas fa-building me-1" style="font-size:.65rem;"></i><?= esc($label) ?>
                                        </span>
                                        <?php if (!empty($u['pengusul_nama'])): ?>
                                            <?php $pengusulLengkap = trim(($u['pengusul_gelar_depan'] ? $u['pengusul_gelar_depan'] . ' ' : '') . $u['pengusul_nama'] . ($u['pengusul_gelar_belakang'] ? ', ' . $u['pengusul_gelar_belakang'] : '')); ?>
                                            <div class="text-truncate mt-1" style="max-width:140px; font-size:0.68rem; color:#6b7280; font-weight:500;" title="Diajukan oleh: <?= esc($pengusulLengkap) ?>">
                                                <i class="fas fa-user-edit me-1" style="opacity:0.6;"></i><?= esc($pengusulLengkap) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($role === 4): ?>
                                        <span class="badge" style="background:#fef08a;color:#854d0e;font-weight:600;">
                                            <i class="fas fa-envelope-open-text me-1"
                                                style="font-size:.65rem;"></i><?= esc($label) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge"
                                            style="background:#f3f4f6;color:#6b7280;font-weight:600;"><?= esc($label) ?></span>
                                    <?php endif ?>
                                </td>
                                <td class="text-center">
                                    <?php if ((int) ($u['is_active'] ?? 1) === 1): ?>
                                        <span class="status-badge published">Aktif</span>
                                    <?php else: ?>
                                        <span class="status-badge cancelled">Nonaktif</span>
                                    <?php endif ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <?php if ($u['username_ldap'] === 'superadmin'): ?>
                                            <span class="action-btn text-muted" title="Super Admin Permanen"
                                                style="cursor:default;">
                                                <i class="fas fa-shield-alt" style="font-size:.72rem;"></i>
                                            </span>
                                        <?php else: ?>
                                            <a href="<?= base_url('admin/users/edit/' . $u['id']) ?>"
                                                class="action-btn text-primary" title="Edit User">
                                                <i class="fas fa-pen" style="font-size:.72rem;"></i>
                                            </a>
                                            <?php if (!$isSelf): ?>
                                                <?php $isActive = (int) ($u['is_active'] ?? 1) === 1; ?>
                                                <button type="button"
                                                    class="action-btn <?= $isActive ? 'text-warning' : 'text-success' ?>"
                                                    title="<?= $isActive ? 'Nonaktifkan' : 'Aktifkan' ?>" onclick="confirmToggle(
                                                            '<?= base_url('admin/users/toggle/' . $u['id']) ?>',
                                                            '<?= esc($u['username_ldap']) ?>',
                                                            <?= $isActive ? 'true' : 'false' ?>
                                                        )">
                                                    <i class="fas <?= $isActive ? 'fa-ban' : 'fa-check' ?>"
                                                        style="font-size:.72rem;"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="action-btn text-muted" title="Tidak dapat mengubah status sendiri"
                                                    style="cursor:default;">
                                                    <i class="fas fa-lock" style="font-size:.72rem;"></i>
                                                </span>
                                            <?php endif ?>
                                        <?php endif ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <span class="pagination-info-text">
                    Menampilkan <strong><?= $showFrom ?>–<?= $showTo ?></strong>
                    dari <strong><?= number_format($p['total']) ?></strong> user
                </span>

                <?php if ($p['totalPages'] > 1): ?>
                    <?php $tabQs = '&tab=' . esc($activeTab); ?>
                    <div class="numbered-pagination">
                        <?php if ($p['currentPage'] > 1): ?>
                            <a href="<?= base_url('admin/users?page=' . ($p['currentPage'] - 1) . $searchQs . $tabQs) ?>"
                                class="page-arrow">
                                <i class="fas fa-chevron-left" style="font-size:.7rem;"></i>
                            </a>
                        <?php else: ?>
                            <button class="page-arrow" disabled><i class="fas fa-chevron-left"
                                    style="font-size:.7rem;"></i></button>
                        <?php endif ?>

                        <?php
                        $start = max(1, $p['currentPage'] - 2);
                        $end = min($p['totalPages'], $p['currentPage'] + 2);
                        ?>
                        <?php if ($start > 1): ?>
                            <a class="page-btn" href="<?= base_url('admin/users?page=1' . $searchQs . $tabQs) ?>">1</a>
                            <?php if ($start > 2): ?><span class="page-ellipsis">…</span><?php endif ?>
                        <?php endif ?>
                        <?php for ($pg = $start; $pg <= $end; $pg++): ?>
                            <a class="page-btn <?= $pg === $p['currentPage'] ? 'active' : '' ?>"
                                href="<?= base_url('admin/users?page=' . $pg . $searchQs . $tabQs) ?>"><?= $pg ?></a>
                        <?php endfor ?>
                        <?php if ($end < $p['totalPages']): ?>
                            <?php if ($end < $p['totalPages'] - 1): ?><span class="page-ellipsis">…</span><?php endif ?>
                            <a class="page-btn"
                                href="<?= base_url('admin/users?page=' . $p['totalPages'] . $searchQs . $tabQs) ?>"><?= $p['totalPages'] ?></a>
                        <?php endif ?>

                        <?php if ($p['currentPage'] < $p['totalPages']): ?>
                            <a href="<?= base_url('admin/users?page=' . ($p['currentPage'] + 1) . $searchQs . $tabQs) ?>"
                                class="page-arrow">
                                <i class="fas fa-chevron-right" style="font-size:.7rem;"></i>
                            </a>
                        <?php else: ?>
                            <button class="page-arrow" disabled><i class="fas fa-chevron-right"
                                    style="font-size:.7rem;"></i></button>
                        <?php endif ?>
                    </div>
                <?php endif ?>
            </div>
        <?php endif ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════════
     MODAL — Detail User
════════════════════════════════════════════════ -->
<?php if (!empty($users)): ?>
    <?php foreach ($users as $u): ?>
        <?php
        $namaLengkap = !empty($u['nama_lengkap']) ? esc($u['nama_lengkap']) : esc($u['username_ldap']);
        if (!empty($u['gelar_depan'])) {
            $namaLengkap = esc($u['gelar_depan']) . ' ' . $namaLengkap;
        }
        if (!empty($u['gelar_belakang'])) {
            $namaLengkap = $namaLengkap . ', ' . esc($u['gelar_belakang']);
        }
        $role = (int) $u['role_id'];
        $label = $u['role_label'] ?? 'Pegawai';
        ?>
        <div class="modal fade" id="modalDetailUser<?= $u['id'] ?>" tabindex="-1" aria-labelledby="modalDetailUserLabel<?= $u['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:450px;">
                <div class="modal-content" style="border:none;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.15);">
                    <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:1.25rem 1.5rem;">
                        <h5 class="modal-title mb-0" id="modalDetailUserLabel<?= $u['id'] ?>" style="font-weight:700;color:#111827;">
                            <i class="fas fa-id-badge me-2" style="color:#3b82f6;"></i>Detail Pengguna
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body" style="padding:1.5rem;">
                        <div class="text-center mb-4">
                            <div style="width:70px;height:70px;border-radius:50%;background:#eff6ff;color:#3b82f6;display:flex;align-items:center;justify-content:center;margin:0 auto 15px;font-size:2rem;font-weight:bold;">
                                <?= strtoupper(substr($namaLengkap, 0, 1)) ?>
                            </div>
                            <h5 style="font-weight:700;color:#111827;margin-bottom:4px;"><?= $namaLengkap ?></h5>
                            <div class="text-muted" style="font-size:0.85rem;"><i class="fas fa-user-tag me-1"></i><?= esc($u['username_ldap']) ?></div>
                        </div>

                        <div style="background:#f9fafb;border-radius:12px;padding:1.25rem;">
                            <div class="row mb-3">
                                <div class="col-4 text-muted" style="font-size:0.85rem;">Unit Kerja</div>
                                <div class="col-8 fw-semibold" style="font-size:0.85rem;color:#1f2937;">
                                    <?= !empty($u['nama_unit_kerja']) ? esc($u['nama_unit_kerja']) : '<span class="text-muted" style="font-style:italic;">Tidak Ada</span>' ?>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4 text-muted" style="font-size:0.85rem;">Role Akses</div>
                                <div class="col-8 fw-semibold" style="font-size:0.85rem;color:#1f2937;">
                                    <?= esc($label) ?>
                                </div>
                            </div>
                            <?php if ($role === 3 && !empty($u['pengusul_nama'])): ?>
                            <?php $pengusulLengkap = trim(($u['pengusul_gelar_depan'] ? $u['pengusul_gelar_depan'] . ' ' : '') . $u['pengusul_nama'] . ($u['pengusul_gelar_belakang'] ? ', ' . $u['pengusul_gelar_belakang'] : '')); ?>
                            <div class="row mb-3">
                                <div class="col-4 text-muted" style="font-size:0.85rem;">Diajukan Oleh</div>
                                <div class="col-8 fw-semibold" style="font-size:0.85rem;color:#1f2937;">
                                    <?= esc($pengusulLengkap) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-4 text-muted" style="font-size:0.85rem;">Status Akun</div>
                                <div class="col-8">
                                    <?php if ((int) ($u['is_active'] ?? 1) === 1): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1"><i class="fas fa-check-circle me-1"></i>Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1"><i class="fas fa-ban me-1"></i>Nonaktif</span>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════
     MODAL — Tambah User
════════════════════════════════════════════════ -->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-labelledby="modalTambahUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content" style="border:none;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.15);">

            <!-- Header -->
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:1.25rem 1.5rem;">
                <div>
                    <h5 class="modal-title mb-0" id="modalTambahUserLabel" style="font-weight:700;color:#111827;">
                        <i class="fas fa-user-plus me-2" style="color:#6d28d9;font-size:.95rem;"></i>Tambah User
                    </h5>
                    <p class="text-muted mb-0" style="font-size:.76rem;margin-top:2px;">Isi data pengguna baru</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <!-- Body -->
            <form method="POST" action="<?= base_url('admin/users/store') ?>" id="formTambahUser">
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

                    <!-- Username -->
                    <div class="mb-3" style="position:relative;">
                        <label for="m_username_ldap" class="form-label fw-semibold"
                            style="font-size:.82rem;color:#374151;">
                            Username <span class="text-danger">*</span>
                        </label>

                        <!-- Input teks biasa dengan suggestion otomatis -->
                        <input type="text" id="m_username_ldap" name="username_ldap" class="form-control"
                            placeholder="Ketik nama atau username..." autocomplete="off" required
                            value="<?= esc($modalOldInput['username_ldap'] ?? '') ?>">

                        <!-- Kotak suggestion — muncul di bawah input saat mengetik -->
                        <div id="m_ldap_suggestions" style="
                                display:none;
                                position:absolute;
                                top:100%;
                                left:0;
                                right:0;
                                background:#fff;
                                border:1px solid #e5e7eb;
                                border-radius:10px;
                                box-shadow:0 8px 24px rgba(0,0,0,.10);
                                z-index:1070;
                                max-height:220px;
                                overflow-y:auto;
                                margin-top:2px;
                            "></div>

                        <div class="form-text" style="font-size:.72rem;">Ketik minimal 2 karakter untuk menampilkan
                            saran nama.</div>
                    </div>

                    <!-- Role -->
                    <div class="mb-3">
                        <label for="m_role_id" class="form-label fw-semibold" style="font-size:.82rem;color:#374151;">
                            Role <span class="text-danger">*</span>
                        </label>
                        <select id="m_role_id" name="role_id" class="form-select" required onchange="toggleUnitKerjaModal(this.value)">
                            <option value="">— Pilih Role —</option>
                            <option value="2" <?= ($modalOldInput['role_id'] ?? '2') == 2 ? 'selected' : '' ?>>Pegawai
                            </option>
                            <option value="3" <?= ($modalOldInput['role_id'] ?? '') == 3 ? 'selected' : '' ?>>TU Unit
                            </option>
                            <option value="4" <?= ($modalOldInput['role_id'] ?? '') == 4 ? 'selected' : '' ?>>TU Persuratan
                            </option>
                            <option value="1" <?= ($modalOldInput['role_id'] ?? '') == 1 ? 'selected' : '' ?>>Admin
                            </option>
                        </select>
                    </div>

                    <!-- Unit Kerja (hanya untuk TU Unit) -->
                    <div class="mb-3" id="m_unitKerjaWrap" style="display:none;">
                        <label for="m_unit_kerja_id" class="form-label fw-semibold" style="font-size:.82rem;color:#374151;">
                            Unit Kerja <span class="text-danger">*</span>
                            <span class="text-muted fw-normal" style="font-size:.75rem;">(wajib untuk TU Unit & TU Persuratan)</span>
                        </label>
                        <select id="m_unit_kerja_id" name="unit_kerja_id" class="form-select">
                            <option value="">— Pilih Unit Kerja —</option>
                            <?php foreach ($unitKerjaList as $uk): ?>
                                <option value="<?= $uk['id'] ?>"
                                    <?= ($modalOldInput['unit_kerja_id'] ?? '') == $uk['id'] ? 'selected' : '' ?>>
                                    <?= esc($uk['nama_unit_kerja']) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="form-label fw-semibold d-block" style="font-size:.82rem;color:#374151;">Status
                            Akun</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="m_is_active" name="is_active" value="1"
                                <?= ($modalOldInput['is_active'] ?? '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="m_is_active" style="font-size:.82rem;">Akun
                                Aktif</label>
                        </div>
                        <div class="form-text" style="font-size:.72rem;">User nonaktif tidak dapat login.</div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:1rem 1.5rem;gap:.5rem;">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-success px-4 rounded-pill d-inline-flex align-items-center justify-content-center" style="background-color: #10b981; border-color: #10b981; font-weight: 600; color: white; gap: 6px;"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- Buka modal otomatis jika ada error validasi -->
<?php if ($hasModalErrors): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = new bootstrap.Modal(document.getElementById('modalTambahUser'));
            modal.show();
        });
    </script>
<?php endif ?>

<?= $this->section('css') ?>
<link href="/css/penomoran-custom.css" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function toggleUnitKerjaModal(roleId) {
        var wrap = document.getElementById('m_unitKerjaWrap');
        var sel  = document.getElementById('m_unit_kerja_id');
        // Tampilkan dropdown unit kerja untuk TU Unit (3) dan TU Persuratan (4)
        if (String(roleId) === '3' || String(roleId) === '4') {
            wrap.style.display = 'block';
            sel.required = true;
        } else {
            wrap.style.display = 'none';
            sel.required = false;
            sel.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Inisialisasi awal unit kerja berdasarkan role terpilih
        var initRole = document.getElementById('m_role_id').value;
        toggleUnitKerjaModal(initRole);

        var inputLdap = document.getElementById('m_username_ldap');
        var kotaSuggestion = document.getElementById('m_ldap_suggestions');
        var timerDebounce = null;
        var sedangFetch = false;

        // Sembunyikan suggestion saat klik di luar
        document.addEventListener('click', function (e) {
            if (!inputLdap.contains(e.target) && !kotaSuggestion.contains(e.target)) {
                kotaSuggestion.style.display = 'none';
            }
        });

        // Tampilkan suggestion saat input berubah
        inputLdap.addEventListener('input', function () {
            var query = this.value.trim();
            clearTimeout(timerDebounce);

            // Butuh minimal 2 karakter
            if (query.length < 2) {
                kotaSuggestion.style.display = 'none';
                return;
            }

            // Debounce 300ms agar tidak terlalu sering request
            timerDebounce = setTimeout(function () {
                if (sedangFetch) return;
                sedangFetch = true;

                kotaSuggestion.innerHTML = '<div style="padding:10px 14px;color:#9ca3af;font-size:.82rem;">Mencari...</div>';
                kotaSuggestion.style.display = 'block';

                fetch('<?= base_url('admin/users/searchPegawai') ?>?q=' + encodeURIComponent(query))
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        sedangFetch = false;
                        tampilkanSuggestion(data.results);
                    })
                    .catch(function () {
                        sedangFetch = false;
                        kotaSuggestion.style.display = 'none';
                    });
            }, 300);
        });

        // Render daftar suggestion ke dalam kotak
        function tampilkanSuggestion(hasil) {
            if (!hasil || hasil.length === 0) {
                kotaSuggestion.innerHTML = '<div style="padding:10px 14px;color:#9ca3af;font-size:.82rem;">Tidak ada pegawai ditemukan.</div>';
                kotaSuggestion.style.display = 'block';
                return;
            }

            var html = '';
            hasil.forEach(function (item) {
                html += '<div class="ldap-suggestion-item" data-username="' + item.id + '" style="'
                    + 'padding:9px 14px;cursor:pointer;font-size:.82rem;border-bottom:1px solid #f3f4f6;'
                    + 'transition:background .15s;'
                    + '"'
                    + ' onmouseover="this.style.background=\'#f9fafb\'"'
                    + ' onmouseout="this.style.background=\'#fff\'"'
                    + '>'
                    + '<div style="font-weight:600;color:#111827;">' + item.text.split(' (')[0] + '</div>'
                    + '<div style="color:#6b7280;font-size:.76rem;">' + item.id + '</div>'
                    + '</div>';
            });
            kotaSuggestion.innerHTML = html;
            kotaSuggestion.style.display = 'block';

            // Saat item suggestion diklik → isi input dengan username_ldap
            kotaSuggestion.querySelectorAll('.ldap-suggestion-item').forEach(function (el) {
                el.addEventListener('click', function () {
                    inputLdap.value = this.dataset.username;
                    kotaSuggestion.style.display = 'none';
                });
            });
        }
    });
</script>
<?= $this->endSection() ?>


<!-- Hidden form untuk toggle — diisi oleh JS -->
<form method="POST" id="formToggleStatus" style="display:none;">
    <?= csrf_field() ?>
</form>

<!-- ═══════════════════════════════════════════════
     MODAL — Konfirmasi Toggle Status
════════════════════════════════════════════════ -->
<div class="modal fade" id="modalKonfirmasiToggle" tabindex="-1" aria-labelledby="modalKonfirmasiLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content" style="border:none;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.15);">

            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:1.25rem 1.5rem;">
                <h5 class="modal-title mb-0" id="modalKonfirmasiLabel" style="font-weight:700;color:#111827;">
                    <span id="konfirmasiIcon" class="me-2"></span>
                    <span id="konfirmasiJudul"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body" style="padding:1.25rem 1.5rem;">
                <p class="mb-0" style="color:#374151;font-size:.9rem;" id="konfirmasiPesan"></p>
            </div>

            <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:1rem 1.5rem;gap:.5rem;">
                <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="btn px-4 rounded-pill" id="btnKonfirmasiOk">
                    <i class="fas me-1" id="btnKonfirmasiIcon"></i>
                    <span id="btnKonfirmasiLabel"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ─── Buka modal tambah user otomatis jika ada error validasi ─── -->
<?php if ($hasModalErrors): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('modalTambahUser')).show();
        });
    </script>
<?php endif ?>

<!-- ─── Toggle Status Handler ─── -->
<script>
    function confirmToggle(actionUrl, username, isCurrentlyActive) {
        var modal = new bootstrap.Modal(document.getElementById('modalKonfirmasiToggle'));
        var judul = document.getElementById('konfirmasiJudul');
        var pesan = document.getElementById('konfirmasiPesan');
        var icon = document.getElementById('konfirmasiIcon');
        var btnOk = document.getElementById('btnKonfirmasiOk');
        var btnIcon = document.getElementById('btnKonfirmasiIcon');
        var btnLabel = document.getElementById('btnKonfirmasiLabel');

        if (isCurrentlyActive) {
            judul.textContent = 'Nonaktifkan User';
            icon.innerHTML = '<i class="fas fa-ban" style="color:#dc2626;"></i>';
            pesan.innerHTML = 'Yakin ingin <strong>menonaktifkan</strong> akun <strong>' + username + '</strong>? User tidak akan dapat login.';
            btnOk.className = 'btn btn-danger px-4 rounded-pill';
            btnOk.style.cssText = 'font-weight: 600; color: white;';
            btnIcon.className = 'fas fa-ban me-1';
            btnLabel.textContent = 'Nonaktifkan';
        } else {
            judul.textContent = 'Aktifkan User';
            icon.innerHTML = '<i class="fas fa-check-circle" style="color:#059669;"></i>';
            pesan.innerHTML = 'Yakin ingin <strong>mengaktifkan</strong> kembali akun <strong>' + username + '</strong>?';
            btnOk.className = 'btn btn-success px-4 rounded-pill';
            btnOk.style.cssText = 'background-color: #10b981; border-color: #10b981; font-weight: 600; color: white;';
            btnIcon.className = 'fas fa-check me-1';
            btnLabel.textContent = 'Aktifkan';
        }

        btnOk.onclick = function () {
            var form = document.getElementById('formToggleStatus');
            form.action = actionUrl;
            modal.hide();
            form.submit();
        };

        modal.show();
    }
</script>

<?= $this->endSection() ?>