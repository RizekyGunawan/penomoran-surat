<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0" style="font-weight:700;color:#111827;">Kelola Jenis Naskah</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">
            <i class="fas fa-list-alt me-1"></i>Manajemen Jenis Surat dan Dokumen
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah" style="border-radius: 6px;">
            <i class="fas fa-plus me-1"></i> Tambah Jenis Naskah
        </button>
    </div>
</div>

<!-- Table Card -->
<div class="card shadow-sm mb-4" style="border-radius:16px; border: 1px solid #e5e7eb;">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="custom-table" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th>Nama Jenis Naskah</th>
                        <th width="15%" class="text-center">Status</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($jenis_naskah as $row): ?>
                        <tr>
                            <td class="text-center" style="font-size:.85rem;"><?= $no++ ?></td>
                            <td style="font-weight:600; font-size:.85rem; color:#111827;"><?= esc($row['nama_jenis']) ?></td>
                            <td class="text-center">
                                <?php if ($row['is_active']): ?>
                                    <span class="badge bg-success" style="padding:5px 0; width:75px; display:inline-block; text-align:center; border-radius:99px; font-size:0.7rem; font-weight:600;">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary" style="padding:5px 0; width:75px; display:inline-block; text-align:center; border-radius:99px; font-size:0.7rem; font-weight:600;">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary" style="padding: 2px 6px; font-size: 0.8rem;" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id'] ?>" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="<?= base_url('admin/jenis-naskah/toggle/' . $row['id']) ?>" method="post" class="d-inline m-0 p-0">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm <?= $row['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" 
                                                style="padding: 2px 6px; font-size: 0.8rem;"
                                                onclick="return confirm('Yakin ingin <?= $row['is_active'] ? 'menonaktifkan' : 'mengaktifkan' ?> jenis naskah ini?')"
                                                title="<?= $row['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                            <i class="fas <?= $row['is_active'] ? 'fa-ban' : 'fa-check' ?>"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('admin/jenis-naskah/store') ?>" method="post" class="w-100">
            <?= csrf_field() ?>
            <div class="modal-content" style="border-radius:16px; border:none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                <div class="modal-header px-4 pt-4 pb-3" style="border-bottom: 1px solid #f3f4f6;">
                    <h5 class="modal-title fw-bold" id="modalTambahLabel" style="color:#111827;">Tambah Jenis Naskah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:0.85rem; color:#374151;">Nama Jenis Naskah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_jenis" placeholder="Contoh: Surat Edaran" style="border-radius:8px; border:1px solid #e5e7eb; padding:10px 15px; font-size:0.9rem;" required>
                    </div>
                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active_new" name="is_active" value="1" checked>
                        <label class="form-check-label ms-2 fw-medium" style="font-size:0.9rem; color:#374151;" for="is_active_new">Aktifkan Jenis Naskah</label>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 pt-0 d-flex justify-content-end flex-nowrap gap-2">
                    <button type="button" class="btn btn-outline-secondary m-0" style="border-radius:8px; font-size:0.9rem; padding:8px 16px;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary m-0 px-4 text-nowrap" style="border-radius:8px; font-size:0.9rem; padding:8px 16px;">Simpan Naskah</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php foreach ($jenis_naskah as $row): ?>
<!-- Modal Edit -->
<div class="modal fade" id="modalEdit<?= $row['id'] ?>" tabindex="-1" aria-labelledby="modalEditLabel<?= $row['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('admin/jenis-naskah/update/' . $row['id']) ?>" method="post" class="w-100">
            <?= csrf_field() ?>
            <div class="modal-content" style="border-radius:16px; border:none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                <div class="modal-header px-4 pt-4 pb-3" style="border-bottom: 1px solid #f3f4f6;">
                    <h5 class="modal-title fw-bold" id="modalEditLabel<?= $row['id'] ?>" style="color:#111827;">Edit Jenis Naskah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:0.85rem; color:#374151;">Nama Jenis Naskah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_jenis" value="<?= esc($row['nama_jenis']) ?>" style="border-radius:8px; border:1px solid #e5e7eb; padding:10px 15px; font-size:0.9rem;" required>
                    </div>
                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active_<?= $row['id'] ?>" name="is_active" value="1" <?= $row['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label ms-2 fw-medium" style="font-size:0.9rem; color:#374151;" for="is_active_<?= $row['id'] ?>">Aktifkan Jenis Naskah</label>
                    </div>
                    <small class="text-muted d-block ms-1" style="font-size:0.8rem;">Naskah yang tidak aktif tidak akan muncul di pilihan dropdown form penomoran surat.</small>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 pt-0 d-flex justify-content-end flex-nowrap gap-2">
                    <button type="button" class="btn btn-outline-secondary m-0" style="border-radius:8px; font-size:0.9rem; padding:8px 16px;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary m-0 px-4 text-nowrap" style="border-radius:8px; font-size:0.9rem; padding:8px 16px;">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
    /* Styling khusus untuk menyamai desain layout DataTables dari gambar */
    .dt-bottom-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .dt-bottom-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    div.dataTables_wrapper div.dataTables_length,
    div.dataTables_wrapper div.dataTables_info,
    div.dataTables_wrapper div.dataTables_paginate {
        margin: 0 !important;
        padding: 0 !important;
    }
    div.dataTables_wrapper div.dataTables_length select {
        width: 70px !important;
        padding: 4px 24px 4px 12px !important;
        display: inline-block;
        font-size: 0.85rem;
        border-radius: 8px;
    }
    div.dataTables_wrapper div.dataTables_info {
        font-size: 0.85rem;
        color: #6b7280;
    }
    div.dataTables_wrapper div.dataTables_paginate ul.pagination {
        gap: 6px;
        margin: 0;
    }
    div.dataTables_wrapper div.dataTables_paginate .page-item .page-link {
        width: 32px;
        height: 32px;
        border-radius: 50% !important;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
        font-size: 0.85rem !important;
        color: #4b5563;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }
    div.dataTables_wrapper div.dataTables_paginate .page-item.active .page-link {
        background-color: #1e3a8a;
        border-color: #1e3a8a;
        color: #fff;
    }
    /* Membuat kolom pencarian (search) lebih compact */
    div.dataTables_wrapper div.dataTables_filter label {
        font-size: 0.85rem;
        color: #4b5563;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }
    div.dataTables_wrapper div.dataTables_filter input {
        margin-left: 0 !important;
        padding: 4px 12px !important;
        font-size: 0.85rem;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        height: 32px; /* Dibuat compact seperti select entries */
        width: 180px;
    }
    div.dataTables_wrapper div.dataTables_filter input:focus {
        border-color: #1e3a8a;
        box-shadow: 0 0 0 2px rgba(30, 58, 138, 0.1);
        outline: none;
    }
</style>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "dom": "<'row mb-3'<'col-sm-12 col-md-6'><'col-sm-12 col-md-6 d-flex justify-content-end'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'dt-bottom-wrapper'<'dt-bottom-left'li>p>",
            "language": {
                "lengthMenu": "_MENU_",
                "search": "Cari:",
                "info": "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 data",
                "infoFiltered": "(difilter dari _MAX_ data)",
                "zeroRecords": "Tidak ada data yang ditemukan",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "<i class='fas fa-chevron-right' style='font-size:0.7rem;'></i>",
                    "previous": "<i class='fas fa-chevron-left' style='font-size:0.7rem;'></i>"
                }
            },
            "pageLength": 10,
            "ordering": false // Disable ordering so that it stays alphabetical based on controller
        });
    });
</script>
<?= $this->endSection() ?>
