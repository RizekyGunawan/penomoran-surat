<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
    $pageTitle  = $isEdit ? 'Edit User' : 'Tambah User';
    $actionUrl  = $isEdit
        ? base_url('admin/users/update/' . $user['id'])
        : base_url('admin/users/store');
    $errors     = session('errors') ?? [];
?>

<div class="row mb-2">
    <div class="col-md-12">
        <h1 class="h3 mb-0 text-gray-800">Admin — <?= $pageTitle ?></h1>
    </div>
</div>

<!-- Breadcrumb -->
<div class="row mb-3">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Beranda</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('admin/users') ?>">Manage User</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $pageTitle ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Pesan Error -->
<?php if (! empty($errors)): ?>
    <div class="alert alert-danger mb-3">
        <i class="fas fa-exclamation-circle me-1"></i>
        <strong>Terdapat kesalahan:</strong>
        <ul class="mb-0 mt-1">
            <?php foreach ($errors as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>

<!-- Card Form -->
<div class="card shadow" style="max-width: 600px;">
    <div class="card-body p-4">
        <h2 class="section-header mb-4 section-header-custom"><?= $pageTitle ?></h2>

        <form method="POST" action="<?= $actionUrl ?>">
            <?= csrf_field() ?>

            <!-- Username -->
            <div class="mb-3">
                <label for="username_ldap" class="form-label fw-semibold">
                    Username <span class="text-danger">*</span>
                </label>
                <select
                    id="username_ldap"
                    name="username_ldap"
                    class="form-select <?= isset($errors['username_ldap']) ? 'is-invalid' : '' ?>"
                    required
                >
                    <?php if (old('username_ldap', $user['username_ldap'] ?? '')): ?>
                        <option value="<?= esc(old('username_ldap', $user['username_ldap'] ?? '')) ?>" selected>
                            <?= esc(old('username_ldap', $user['username_ldap'] ?? '')) ?>
                        </option>
                    <?php endif; ?>
                </select>
                <?php if (isset($errors['username_ldap'])): ?>
                    <div class="invalid-feedback"><?= esc($errors['username_ldap']) ?></div>
                <?php endif ?>
                <div class="form-text">Username yang digunakan untuk login ke sistem.</div>
            </div>

            <!-- Role -->
            <div class="mb-3">
                <label for="role_id" class="form-label fw-semibold">
                    Role <span class="text-danger">*</span>
                </label>
                <select
                    id="role_id"
                    name="role_id"
                    class="form-select <?= isset($errors['role_id']) ? 'is-invalid' : '' ?>"
                    required
                    onchange="toggleUnitKerja(this.value)"
                >
                    <option value="">— Pilih Role —</option>
                    <option value="1" <?= (old('role_id', $user['role_id'] ?? '') == 1) ? 'selected' : '' ?>>
                        Admin
                    </option>
                    <option value="2" <?= (old('role_id', $user['role_id'] ?? '') == 2) ? 'selected' : '' ?>>
                        Pegawai
                    </option>
                    <option value="3" <?= (old('role_id', $user['role_id'] ?? '') == 3) ? 'selected' : '' ?>>
                        TU Unit
                    </option>
                    <option value="4" <?= (old('role_id', $user['role_id'] ?? '') == 4) ? 'selected' : '' ?>>
                        TU Persuratan
                    </option>
                </select>
                <?php if (isset($errors['role_id'])): ?>
                    <div class="invalid-feedback"><?= esc($errors['role_id']) ?></div>
                <?php endif ?>
            </div>

            <!-- Unit Kerja (hanya untuk TU Unit) -->
            <div class="mb-3" id="unitKerjaWrap" style="display:none;">
                <label for="unit_kerja_id" class="form-label fw-semibold">
                    Unit Kerja <span class="text-danger">*</span>
                    <span class="text-muted fw-normal" style="font-size:.75rem;">(wajib untuk TU Unit & TU Persuratan)</span>
                </label>
                <select id="unit_kerja_id" name="unit_kerja_id" class="form-select">
                    <option value="">— Pilih Unit Kerja —</option>
                    <?php foreach ($unitKerjaList as $uk): ?>
                        <option value="<?= $uk['id'] ?>"
                            <?= (old('unit_kerja_id', $user['unit_kerja_id'] ?? '') == $uk['id']) ? 'selected' : '' ?>>
                            <?= esc($uk['nama_unit_kerja']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>

            <?= $this->section('scripts') ?>
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            <script>
            function toggleUnitKerja(roleId) {
                var wrap = document.getElementById('unitKerjaWrap');
                var sel  = document.getElementById('unit_kerja_id');
                // Tampilkan dropdown unit kerja untuk TU Unit (3) dan TU Persuratan (4)
                if (String(roleId) === '3' || String(roleId) === '4') {
                    wrap.style.display = '';
                    sel.required = true;
                } else {
                    wrap.style.display = 'none';
                    sel.required = false;
                    sel.value = '';
                }
            }
            // Run on page load (for edit mode)
            document.addEventListener('DOMContentLoaded', () => {
                toggleUnitKerja(document.getElementById('role_id').value);
                
                // Initialize Select2
                $('#username_ldap').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Cari Nama atau Username...',
                    minimumInputLength: 2,
                    ajax: {
                        url: '<?= base_url('admin/users/searchPegawai') ?>',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.results
                            };
                        },
                        cache: true
                    }
                });
            });
            </script>
            <?= $this->endSection() ?>

            <!-- Status Aktif -->
            <div class="mb-4">
                <label class="form-label fw-semibold d-block">Status Akun</label>
                <div class="form-check form-switch">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="is_active"
                        name="is_active"
                        value="1"
                        <?= (old('is_active', $user['is_active'] ?? 1) == 1) ? 'checked' : '' ?>
                    >
                    <label class="form-check-label" for="is_active">Akun Aktif</label>
                </div>
                <div class="form-text">User dengan akun nonaktif tidak dapat login.</div>
            </div>

            <!-- Custom CSS (consolidated) -->
            <?= $this->section('css') ?>
            <link href="/css/penomoran-custom.css" rel="stylesheet">
            
            <!-- Select2 CSS -->
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
            <?= $this->endSection() ?>

            <!-- Tombol -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success px-4 rounded-pill d-inline-flex align-items-center justify-content-center" style="background-color: #10b981; border-color: #10b981; font-weight: 600; color: white; gap: 6px;"><i class="fas fa-save"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah User' ?></button>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary px-4 rounded-pill">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
