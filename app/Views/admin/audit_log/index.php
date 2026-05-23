<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row mb-2">
    <div class="col-md-12">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-history text-muted me-2"></i> Riwayat Audit Sistem</h1>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Beranda</a></li>
                <li class="breadcrumb-item">Admin</li>
                <li class="breadcrumb-item active" aria-current="page">Audit Log</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card shadow" style="border-radius: 12px; border-color: #e5e7eb;">
    <div class="card-body p-4">
        <?php if (empty($logs)): ?>
            <div class="alert alert-info border-0 rounded-4 shadow-sm" role="alert">
                <i class="fas fa-info-circle me-2"></i> Belum ada riwayat aktivitas yang terekam.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle custom-table">
                    <thead>
                        <tr>
                            <th width="15%">Tanggal & Waktu</th>
                            <th width="15%">Jenis Aktivitas</th>
                            <th width="15%">Modul Terdampak</th>
                            <th width="20%">Identitas Pengguna</th>
                            <th width="35%">Rincian Perubahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $l): ?>
                            <?php
                            $rawAction = strtoupper($l['action']);
                            $displayAction = $rawAction;
                            $badgeColor = 'bg-secondary';

                            if (in_array($rawAction, ['BUAT', 'INSERT', 'CREATE'])) {
                                $displayAction = 'PENAMBAHAN';
                                $badgeColor = 'bg-success';
                            } elseif (in_array($rawAction, ['UBAH', 'UPDATE', 'EDIT'])) {
                                $displayAction = 'PEMBARUAN';
                                $badgeColor = 'bg-primary';
                            } elseif (in_array($rawAction, ['HAPUS', 'DELETE', 'REMOVE'])) {
                                $displayAction = 'PENGHAPUSAN';
                                $badgeColor = 'bg-danger';
                            } elseif (in_array($rawAction, ['BATAL', 'CANCEL'])) {
                                $displayAction = 'PEMBATALAN';
                                $badgeColor = 'bg-danger';
                            }

                            $oldVal = json_decode($l['old_values'] ?? '{}', true);
                            $newVal = json_decode($l['new_values'] ?? '{}', true);
                            ?>
                            <tr>
                                <td class="text-muted" style="font-size: 0.85rem;">
                                    <i class="far fa-clock me-1"></i> <?= date('d M Y, H:i', strtotime($l['created_at'])) ?>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeColor ?> px-3 py-2 rounded-pill shadow-sm"
                                        style="letter-spacing: 0.5px; font-weight: 600;"><?= esc($displayAction) ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?= esc($l['entity_table']) ?>
                                    </div>
                                    <div class="text-muted small">ID: <?= esc($l['entity_id'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <div class="text-dark" style="font-size: 0.9rem; font-weight: 500;">
                                        <i class="fas fa-user-circle text-muted me-1"></i>
                                        <?php if (empty($l['user_id'])): ?>
                                            Sistem / Sistem Bawah Layar
                                        <?php else: ?>
                                            <?= esc($l['username_ldap'] ?? 'Unknown User') ?> <span
                                                class="badge bg-light text-secondary border border-secondary"
                                                style="font-weight: 500; font-size:0.7rem; margin-left:2px; letter-spacing: 0;">ID:
                                                <?= esc($l['user_id']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small mt-1"><i class="fas fa-network-wired text-muted me-1"></i> IP
                                        Address : <?= esc($l['ip_address']) ?></div>
                                </td>
                                <td>
                                    <div class="p-2 bg-light rounded text-dark"
                                        style="font-size: 0.85rem; border-left: 3px solid #dee2e6;">
                                        <?php if ($displayAction === 'PENAMBAHAN'): ?>
                                            <i>Membuat data baru (Cipta record).</i>
                                        <?php elseif ($displayAction === 'PEMBARUAN'): ?>
                                            <i>Terjadi perubahan struktur data record terkait.</i>
                                        <?php elseif ($displayAction === 'PENGHAPUSAN'): ?>
                                            <i class="text-danger">Menghapus entitas/record dari sistem.</i>
                                        <?php elseif ($displayAction === 'PEMBATALAN'): ?>
                                            <i>Pembatalan terekam. Memo terlampir:
                                                "<?= esc($newVal['ALASAN_PEMBATALAN'] ?? 'Tanpa Alasan/Tidak Ditemukan') ?>"</i>
                                        <?php else: ?>
                                            <i>Aktivitas sistem: <?= esc($displayAction) ?></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 pt-3 border-top">
                <?= view('partials/pagination') ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- JS Pagination bridge for partial compatibility -->
<script>
    function changePerPage(perPage) {
        const url = new URL(window.location.href);
        url.searchParams.set("per_page", perPage);
        url.searchParams.set("page", 1);
        window.location.href = url.toString();
    }

    function goToPage(page) {
        const url = new URL(window.location.href);
        url.searchParams.set("page", page);
        window.location.href = url.toString();
    }
</script>

<?= $this->endSection() ?>