<?php
/**
 * Partial: Stats Cards
 *
 * Menampilkan 3 kartu statistik: Total Dibuat, Diterbitkan, Dibatalkan.
 * Digunakan di: Views/penomoran/index.php, Views/penomoran/admin.php
 *
 * Variabel yang dibutuhkan dari controller:
 * @var array  $statistics {
 *   int    $total_created    Total semua nomor dibuat
 *   int    $total_published  Total nomor diterbitkan
 *   int    $total_cancelled  Total nomor dibatalkan
 *   int    $monthly_created  Nomor dibuat bulan ini
 *   int    $monthly_published Nomor diterbitkan bulan ini
 *   int    $monthly_cancelled Nomor dibatalkan bulan ini
 *   string $current_month   Nama bulan saat ini
 * }
 * @var string $status  Status filter aktif ('SEMUA'|'DITERBITKAN'|'DIBATALKAN')
 */

$isAllActive       = empty($status) || $status === 'SEMUA';
$isPublishedActive = $status === 'DITERBITKAN';
$isCancelledActive = $status === 'DIBATALKAN';
?>
<div class="stats-container">
    <!-- Kartu 1: Total Nomor Dibuat -->
    <div class="stat-card <?= $isAllActive ? 'active' : '' ?>"
         onclick="filterByStatus('SEMUA')"
         style="cursor: pointer;"
         title="Klik untuk melihat semua surat">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="stat-label mb-1">Total Nomor Dibuat</div>
                <div class="stat-number"><?= $statistics['total_created'] ?></div>
            </div>
            <div class="stat-icon yellow">
                <i class="fas fa-layer-group"></i>
            </div>
        </div>
        <div class="stat-footer">
            <span class="stat-subinfo"><?= $statistics['monthly_created'] ?> Nomor di bulan <?= $statistics['current_month'] ?></span>
        </div>
    </div>

    <!-- Kartu 2: Total Nomor Diterbitkan -->
    <div class="stat-card <?= $isPublishedActive ? 'active' : '' ?>"
         onclick="filterByStatus('DITERBITKAN')"
         style="cursor: pointer;"
         title="Klik untuk melihat surat yang diterbitkan">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="stat-label mb-1">Total Nomor Diterbitkan</div>
                <div class="stat-number"><?= $statistics['total_published'] ?></div>
            </div>
            <div class="stat-icon green">
                <i class="fas fa-check"></i>
            </div>
        </div>
        <div class="stat-footer">
            <span class="stat-subinfo"><?= $statistics['monthly_published'] ?> Nomor di bulan <?= $statistics['current_month'] ?></span>
        </div>
    </div>

    <!-- Kartu 3: Total Nomor Dibatalkan -->
    <div class="stat-card <?= $isCancelledActive ? 'active' : '' ?>"
         onclick="filterByStatus('DIBATALKAN')"
         style="cursor: pointer;"
         title="Klik untuk melihat surat yang dibatalkan">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="stat-label mb-1">Total Nomor Dibatalkan</div>
                <div class="stat-number"><?= $statistics['total_cancelled'] ?></div>
            </div>
            <div class="stat-icon red">
                <i class="fas fa-times"></i>
            </div>
        </div>
        <div class="stat-footer">
            <span class="stat-subinfo"><?= $statistics['monthly_cancelled'] ?> Nomor di bulan <?= $statistics['current_month'] ?></span>
        </div>
    </div>
</div>
