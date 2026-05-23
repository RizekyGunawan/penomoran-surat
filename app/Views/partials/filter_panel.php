<?php
/**
 * Partial: Filter Panel
 *
 * Digunakan di: Views/penomoran/index.php, Views/penomoran/admin.php
 *
 * Variabel yang dibutuhkan dari controller:
 * @var string $search          Kata kunci pencarian aktif
 * @var string $startDate       Tanggal mulai filter (format Y-m-d)
 * @var string $endDate         Tanggal akhir filter (format Y-m-d)
 * @var array  $jenisDokumenList Daftar jenis dokumen untuk dropdown
 * @var string $jenisDokumen    Jenis dokumen yang sedang dipilih
 * @var array  $unitKerjaList   Daftar unit kerja untuk dropdown
 * @var string $unitKerja       Unit kerja yang sedang dipilih
 */
?>
<div class="search-container mb-4">
    <div class="search-wrapper">
        <i class="fas fa-search search-icon"></i>
        <input type="text"
               id="searchInput"
               class="search-input rounded-pill"
               placeholder="Cari riwayat nomor surat"
               value="<?= esc($search) ?>">
    </div>
    <div class="position-relative">
        <button id="filterBtn" class="filter-btn" onclick="toggleFilterModal()">
            <i class="fas fa-filter"></i>
            <span>Filters</span>
        </button>
        <!-- Popover Filter -->
        <div id="filterModal" class="filter-modal">
            <div class="filter-content">
                <!-- Header -->
                <h5 class="mb-4 font-weight-bold">Filter Data</h5>

                <!-- Bagian Tanggal -->
                <div class="form-group mb-3">
                    <label class="form-label small text-muted">Tanggal</label>
                    <div class="d-flex flex-column gap-2">
                        <div class="flex-fill position-relative">
                            <input type="date"
                                   id="filterStartDate"
                                   class="form-control"
                                   placeholder="Dari Tanggal"
                                   value="<?= esc($startDate) ?>">
                        </div>
                        <div class="flex-fill position-relative">
                            <input type="date"
                                   id="filterEndDate"
                                   class="form-control"
                                   placeholder="Sampai Tanggal"
                                   value="<?= esc($endDate) ?>">
                        </div>
                    </div>
                </div>

                <!-- Bagian Jenis Surat -->
                <div class="form-group mb-3">
                    <label for="filterJenis" class="form-label small text-muted">Jenis Surat</label>
                    <select id="filterJenis" class="form-control">
                        <option value="">Pilih</option>
                        <?php foreach ($jenisDokumenList as $jenis): ?>
                            <option value="<?= esc($jenis) ?>" <?= ($jenisDokumen === $jenis) ? 'selected' : '' ?>>
                                <?= esc($jenis) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>

                <!-- Bagian Asal Surat -->
                <!-- Catatan: Filter ini disembunyikan untuk Pegawai Biasa (role_id = 2)         -->
                <!-- karena halaman Riwayat Pengajuan hanya menampilkan surat milik user sendiri. -->
                <!-- Rekap lintas unit kerja tersedia di menu Rekap Surat untuk TU Unit & TU Persuratan. -->
                <?php if ((int) session('user.role_id') !== 2): ?>
                <div class="form-group mb-4">
                    <label for="filterUnitKerja" class="form-label small text-muted">Asal Surat</label>
                    <select id="filterUnitKerja" class="form-control">
                        <option value="">Pilih</option>
                        <?php foreach ($unitKerjaList as $unit): ?>
                            <option value="<?= esc($unit['UNIT_KERJA']) ?>" <?= ($unitKerja === $unit['UNIT_KERJA']) ? 'selected' : '' ?>>
                                <?= esc($unit['UNIT_KERJA']) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Tombol Aksi -->
                <div class="d-flex gap-2 justify-content-end">
                    <button onclick="closeFilter()" class="btn btn-outline-secondary px-4">Batalkan</button>
                    <button onclick="applyFilters()" class="btn btn-primary px-4 bg-navy">Terapkan</button>
                </div>
            </div>
        </div>
    </div>
</div>
