<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

/* Warna badge per jenis dokumen */
$jenisBadgeColor = [
    'SPT' => ['bg' => '#dbeafe', 'text' => '#1e40af'],
    'Surat Dinas' => ['bg' => '#d1fae5', 'text' => '#065f46'],
    'Undangan Eksternal' => ['bg' => '#fef9c3', 'text' => '#92400e'],
    'Surat Edaran' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
    'Pengumuman' => ['bg' => '#ffedd5', 'text' => '#9a3412'],
    'Berita Acara' => ['bg' => '#e0f2fe', 'text' => '#075985'],
    'Surat Kuasa' => ['bg' => '#f1f5f9', 'text' => '#475569'],
];
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0" style="font-weight:700;color:#111827;">Dashboard TU Unit</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">
            <i class="fas fa-building me-1"></i>
            <?= esc($unitKerja['nama_unit_kerja'] ?? 'Unit Kerja Tidak Diketahui') ?>
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
        <a href="<?= base_url('tu-unit/rekap') ?>" class="btn btn-primary btn-sm px-3">
            <i class="fas fa-list me-1"></i> Lihat Rekap
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-container">
    <!-- Kartu 1: Total Surat -->
    <div class="stat-card" style="cursor: default;">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="stat-label mb-1">Total Surat</div>
                <div class="stat-number"><?= number_format($statistics['total'] ?? 0) ?></div>
            </div>
            <div class="stat-icon purple">
                <i class="fas fa-file-alt"></i>
            </div>
        </div>
        <div class="stat-footer">
            <span class="stat-subinfo">Tahun <?= $tahun ?></span>
        </div>
    </div>

    <!-- Kartu 2: Aktif -->
    <div class="stat-card" style="cursor: default;">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="stat-label mb-1">Diterbitkan</div>
                <div class="stat-number"><?= number_format($statistics['aktif'] ?? 0) ?></div>
            </div>
            <div class="stat-icon green">
                <i class="fas fa-check"></i>
            </div>
        </div>
        <div class="stat-footer">
            <span class="stat-subinfo">Aktif / Berlaku</span>
        </div>
    </div>

    <!-- Kartu 3: Dibatalkan -->
    <div class="stat-card" style="cursor: default;">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="stat-label mb-1">Dibatalkan</div>
                <div class="stat-number"><?= number_format($statistics['dibatalkan'] ?? 0) ?></div>
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
                <div class="stat-number"><?= number_format($statistics['bulan_ini'] ?? 0) ?></div>
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

<div class="row g-3">
    <!-- Rekap Per Jenis -->
    <div class="col-md-4">
        <div class="card shadow-sm h-100" style="border-radius:16px; border: 1px solid #e5e7eb;">
            <div class="card-body p-4">
                <h6 class="mb-3" style="font-weight:700;color:#111827;">
                    <i class="fas fa-list-ul me-2" style="color:#6d28d9;"></i>Rekap Per Jenis Dokumen
                </h6>
                <?php if (empty($rekapJenis)): ?>
                    <p class="text-muted" style="font-size:.85rem;">Belum ada data untuk tahun <?= $tahun ?>.</p>
                <?php else: ?>
                    <table style="width:100%;border-collapse:collapse;">
                        <?php foreach ($rekapJenis as $i => $item): ?>
                            <tr style="<?= $i < count($rekapJenis) - 1 ? 'border-bottom:1px solid #f3f4f6;' : '' ?>">
                                <td style="padding:7px 0;font-size:.82rem;color:#374151;">
                                    <?= esc($item['JENIS_DOKUMEN']) ?>
                                </td>
                                <td style="padding:7px 0;text-align:right;">
                                    <span style="background:#ede9fe;color:#6d28d9;font-size:.78rem;font-weight:700;
                                                 padding:2px 10px;border-radius:99px;">
                                        <?= number_format($item['total']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </table>
                <?php endif ?>
            </div>
        </div>
    </div>

    <!-- 5 Surat Terbaru -->
    <div class="col-md-8">
        <div class="card shadow-sm h-100" style="border-radius:16px; border: 1px solid #e5e7eb;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0" style="font-weight:700;color:#111827;">
                        <i class="fas fa-clock me-2" style="color:#6d28d9;"></i>Surat Terbaru
                    </h6>
                    <a href="<?= base_url('tu-unit/rekap?tahun=' . $tahun) ?>"
                        class="btn btn-outline-secondary btn-sm px-3" style="font-size:.75rem;">
                        Lihat semua →
                    </a>
                </div>
                <?php if (empty($recentLetters)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox" style="font-size:2rem;opacity:.3;"></i>
                        <p class="mt-2 mb-0" style="font-size:.85rem;">Belum ada surat.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="custom-table" style="font-size:.82rem;">
                            <thead>
                                <tr>
                                    <th style="width:35%;">Nomor Surat</th>
                                    <th class="text-center" style="width:22%;">Jenis</th>
                                    <th>Perihal</th>
                                    <th style="width:12%;white-space:nowrap;">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLetters as $s): ?>
                                    <?php
                                    $jenis = $s['JENIS_DOKUMEN'] ?? '';
                                    $colors = $jenisBadgeColor[$jenis] ?? ['bg' => '#f1f5f9', 'text' => '#475569'];
                                    ?>
                                    <tr>
                                        <!-- Nomor surat: font monospace agar rapi -->
                                        <td>
                                            <span
                                                style="font-family:monospace;font-weight:600;color:#065f46;font-size:.8rem;background:#f0fdf4;padding:2px 6px;border-radius:4px;white-space:nowrap;">
                                                <?= esc($s['NOMOR_SURAT_LENGKAP'] ?? $s['NO']) ?>
                                            </span>
                                        </td>
                                        <!-- Badge jenis dengan warna -->
                                        <td class="text-center">
                                            <span
                                                style="display:inline-block;width:135px;text-align:center;background:<?= $colors['bg'] ?>;color:<?= $colors['text'] ?>;font-size:.72rem;font-weight:600;padding:2px 8px;border-radius:99px;white-space:nowrap;">
                                                <?= esc($jenis) ?>
                                            </span>
                                        </td>
                                        <!-- Perihal dengan ellipsis -->
                                        <td
                                            style="max-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#374151;">
                                            <?= esc($s['PERIHAL'] ?? '-') ?>
                                        </td>
                                        <td style="color:#6b7280;white-space:nowrap;">
                                            <?= $s['TANGGAL'] ? date('d/m/Y', strtotime($s['TANGGAL'])) : '-' ?>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>