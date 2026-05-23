<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0" style="font-weight:700;color:#111827;">Dashboard Persuratan</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">
            <i class="fas fa-landmark me-1"></i>Rekap surat seluruh unit kerja instansi
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <form method="GET" class="d-flex gap-2">
            <select name="tahun" class="form-select form-select-sm" style="width:100px;" onchange="this.form.submit()">
                <?php foreach ($tahunList as $t): ?>
                    <option value="<?= $t ?>" <?= $t == $tahun ? 'selected' : '' ?>><?= $t ?></option>
                <?php endforeach ?>
            </select>
        </form>
        <a href="<?= base_url('tu-persuratan/rekap') ?>" class="btn btn-primary btn-sm px-3">
            <i class="fas fa-list me-1"></i> Rekap Detail
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-container">
    <!-- Kartu 1: Total Nomor Dibuat -->
    <div class="stat-card" style="cursor: default;">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="stat-label mb-1">Total Surat</div>
                <div class="stat-number"><?= number_format($statistics['total_created'] ?? 0) ?></div>
            </div>
            <div class="stat-icon purple">
                <i class="fas fa-file-alt"></i>
            </div>
        </div>
        <div class="stat-footer">
            <span class="stat-subinfo">Semua unit — <?= $tahun ?></span>
        </div>
    </div>

    <!-- Kartu 2: Total Nomor Diterbitkan -->
    <div class="stat-card" style="cursor: default;">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="stat-label mb-1">Diterbitkan</div>
                <div class="stat-number"><?= number_format($statistics['total_published'] ?? 0) ?></div>
            </div>
            <div class="stat-icon green">
                <i class="fas fa-check"></i>
            </div>
        </div>
        <div class="stat-footer">
            <span class="stat-subinfo">Aktif / Berlaku</span>
        </div>
    </div>

    <!-- Kartu 3: Total Nomor Dibatalkan -->
    <div class="stat-card" style="cursor: default;">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="stat-label mb-1">Dibatalkan</div>
                <div class="stat-number"><?= number_format($statistics['total_cancelled'] ?? 0) ?></div>
            </div>
            <div class="stat-icon red">
                <i class="fas fa-times"></i>
            </div>
        </div>
        <div class="stat-footer">
            <span class="stat-subinfo">Tidak berlaku</span>
        </div>
    </div>

    <!-- Kartu 4: Bulan Ini -->
    <div class="stat-card" style="cursor: default;">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="stat-label mb-1">Bulan Ini</div>
                <div class="stat-number"><?= number_format($statistics['monthly_created'] ?? 0) ?></div>
            </div>
            <div class="stat-icon blue">
                <i class="fas fa-calendar-day"></i>
            </div>
        </div>
        <div class="stat-footer">
            <span class="stat-subinfo"><?= $months[date('n') - 1] ?> <?= $tahun ?></span>
        </div>
    </div>
</div>

<!-- Rekap Per Unit Kerja -->
<div class="card shadow-sm mb-4" style="border-radius:16px; border: 1px solid #e5e7eb;">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="mb-0" style="font-weight:700;color:#111827;">
                <i class="fas fa-sitemap me-2" style="color:#6d28d9;"></i>Rekap Per Unit Kerja — <?= $tahun ?>
            </h6>
            <a href="<?= base_url('tu-persuratan/rekap?tahun=' . $tahun) ?>" class="btn btn-outline-secondary btn-sm px-3"
                style="font-size:.75rem;">
                Lihat semua →
            </a>
        </div>

        <?php if (empty($rekapPerUnit)): ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-inbox" style="font-size:2rem;opacity:.3;"></i>
                <p class="mt-2 mb-0" style="font-size:.85rem;">Belum ada data unit kerja.</p>
            </div>
        <?php else: ?>
            <?php
            $totalSemua = array_sum(array_column($rekapPerUnit, 'total'));
            ?>
            <div class="table-responsive">
                <table class="custom-table" style="font-size:.82rem;">
                    <thead>
                        <tr>
                            <th>Unit Kerja</th>
                            <th class="text-center" style="width:80px;">Total</th>
                            <th class="text-center" style="width:80px;">Aktif</th>
                            <th class="text-center" style="width:90px;">Dibatalkan</th>
                            <th class="text-center" style="width:90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rekapPerUnit as $row): ?>
                            <?php
                            $ids = isset($row['child_ids']) && is_array($row['child_ids'])
                                ? implode(',', $row['child_ids'])
                                : $row['unit_kerja_id'];
                            ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600;font-size:.85rem;color:#111827;">
                                        <?= esc($row['nama_unit']) ?>
                                    </div>
                                </td>
                                <td class="text-center" style="font-weight:700;"><?= number_format($row['total']) ?></td>
                                <td class="text-center" style="color:#059669;font-weight:600;"><?= number_format($row['aktif']) ?></td>
                                <td class="text-center" style="color:#dc2626;"><?= number_format($row['dibatalkan']) ?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('tu-persuratan/rekap?tahun=' . $tahun . '&unit_kerja_id=' . $ids) ?>"
                                        class="action-btn text-primary" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif ?>
    </div>
</div>

<?= $this->endSection() ?>