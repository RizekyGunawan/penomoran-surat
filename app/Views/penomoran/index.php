<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <!-- Sertakan CSS Kustom -->

    <!-- Judul Halaman -->
    <div class="row mb-2">
        <div class="col-md-12">
            <h1 class="h3 mb-0 text-gray-800">Penomoran Surat</h1>
        </div>
    </div>

    <!-- Navigasi Breadcrumb -->
    <div class="row mb-3">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Beranda</a></li>
                    <li class="breadcrumb-item">Semua Menu</li>
                    <li class="breadcrumb-item active" aria-current="page">Penomoran Surat</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Pesan Notifikasi (Dihandle oleh Layout dengan Toast) -->

    <!-- Navigasi Tab -->
    <div class="tab-navigation">
        <a href="<?= base_url('penomoran/create') ?>" class="tab-btn">Form Penomoran</a>
        <a href="<?= base_url('penomoran') ?>" class="tab-btn active">Riwayat Pengajuan</a>
    </div>

    <?= view('partials/stats_cards') ?>

    <!-- Kartu Tabel dengan Header dan Pencarian di Dalamnya -->
    <div class="card shadow" style="border-color: #e5e7eb !important; border-radius: 12px !important; overflow: visible !important;">
        <div class="card-body p-4">
            <!-- Header Bagian di Dalam Kartu -->
            <h2 class="section-header mb-4" style="font-weight: 600; color: #111827; font-size: 1.25rem;">Riwayat Nomor Surat</h2>

            <?= view('partials/filter_panel') ?>

            <!-- Active Filters Container -->
            <div id="activeFilters" class="d-flex flex-wrap gap-2 mb-4 align-items-center" style="min-height: 0;"></div>

            <!-- Konten Tabel -->
            <?php if (empty($penomoran)): ?>
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i> 
                    <?php if ($hasFilter): ?>
                        Data penomoran dengan filter yang dipilih belum ada. <a href="<?= base_url('penomoran/create') ?>">Tambah data baru</a>
                    <?php else: ?>
                        Belum ada data penomoran. <a href="<?= base_url('penomoran/create') ?>">Tambah data baru</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php
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
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Nomor Surat Lengkap</th>
                                <th class="text-center">Jenis</th>
                                <th>Perihal</th>
                                <th>Dibuat</th>
                                <th>Pengajuan Oleh</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($penomoran as $row): ?>
                                <?php 
                                $isCancelled = isset($row['STATUS']) && $row['STATUS'] === 'DIBATALKAN';
                                $isOwner = isset($row['USER_ID']) && $row['USER_ID'] == $currentUserId;
                                ?>
                                <tr>
                                    <td class="fw-bold text-dark">
                                        <span style="font-family:monospace;font-weight:600;color:#065f46;font-size:.85rem;background:#f0fdf4;padding:2px 8px;border-radius:4px;white-space:nowrap;">
                                            <?= esc($row['NOMOR_SURAT_LENGKAP'] ?? $row['NO']) ?>
                                        </span>
                                    </td>
                                    <?php
                                    $jenis_dok = $row['JENIS_DOKUMEN'] ?? '';
                                    $colors = $jenisBadgeColor[$jenis_dok] ?? ['bg' => '#f1f5f9', 'text' => '#475569'];
                                    ?>
                                    <td class="text-center">
                                        <span style="display:inline-block;width:135px;text-align:center;background:<?= $colors['bg'] ?>;color:<?= $colors['text'] ?>;font-size:.72rem;font-weight:600;padding:2px 8px;border-radius:99px;white-space:nowrap;">
                                            <?= esc($jenis_dok) ?>
                                        </span>
                                    </td>
                                    <td><?= esc(substr($row['PERIHAL'] ?? '', 0, 50)) ?><?= strlen($row['PERIHAL'] ?? '') > 50 ? '...' : '' ?></td>
                                    <td><?= format_date_with_time($row['CREATED_AT'] ?? $row['TANGGAL']) ?></td>
                                    <td><?= esc($row['NAMA'] ?? '-') ?></td>
                                    <td class="text-center">
                                        <?php if ($isCancelled): ?>
                                            <span class="status-badge cancelled">Dibatalkan</span>
                                        <?php else: ?>
                                            <span class="status-badge published">Diterbitkan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <!-- Tombol Detail -->
                                            <a href="<?= base_url('penomoran/detail/' . $row['NO'] . '/' . urlencode($row['JENIS_DOKUMEN']) . '/' . $row['TAHUN']) ?>" 
                                               class="action-btn text-primary" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <?php if ($isOwner): ?>
                                                <?php if (!$isCancelled): ?>
                                                    <!-- Tombol Batal -->
                                                    <button type="button" 
                                                            class="action-btn text-danger" 
                                                            title="Batalkan Surat"
                                                            onclick="openCancelModal('<?= esc($row['NO']) ?>', '<?= esc($row['JENIS_DOKUMEN']) ?>', '<?= esc($row['TANGGAL']) ?>', '<?= esc($row['TAHUN']) ?>', '<?= esc($row['NOMOR_SURAT_LENGKAP'] ?? $row['NO']) ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="action-btn text-muted" disabled title="Sudah dibatalkan">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <button class="action-btn text-muted" disabled title="Bukan milik Anda">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>

                <?= view('partials/pagination') ?>
            <?php endif ?>
        </div>
    </div>



    <!-- Modal Filter dihapus/dipindahkan -->

    
<!-- Modal Pembatalan -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-body text-center pt-5 pb-4 px-4">
                <!-- Ikon -->
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #fee2e2; border-radius: 50%;">
                        <i class="fas fa-trash" style="font-size: 1.5rem; color: #dc2626;"></i>
                    </div>
                </div>
                
                <!-- Judul -->
                <h5 class="mb-3" style="font-weight: 600;">Konfirmasi Pembatalan Nomor Surat</h5>
                
                <!-- Deskripsi -->
                <p class="text-muted mb-4" style="font-size: 0.875rem;">
                    Apakah anda yakin akan membatalkan pada nomor surat berikut?
                </p>
                
                <!-- Formulir -->
                <form action="<?= base_url('penomoran/cancel-submit') ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <!-- Kolom Tersembunyi -->
                    <input type="hidden" name="NO" id="cancelInputNo">
                    <input type="hidden" name="JENIS_DOKUMEN" id="cancelInputJenis">
                    <input type="hidden" name="TAHUN" id="cancelInputTahun">
                    
                    <!-- Kolom Informasi -->
                    <div class="text-start mb-3">
                        <!-- Kotak Nomor Surat -->
                        <div class="mb-3 p-3 border rounded" style="background-color: white;">
                            <label class="d-block text-muted small mb-1">Nomor Surat</label>
                            <div class="fw-bold fs-5 text-dark" id="cancelShowNoText"></div>
                        </div>
                        
                        <!-- Jenis Surat & Tanggal Surat (2 kolom) -->
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="p-3 border rounded h-100" style="background-color: white;">
                                    <label class="d-block text-muted small mb-1">Jenis Surat</label>
                                    <div class="fw-medium text-dark" id="cancelShowJenisText"></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 border rounded h-100" style="background-color: white;">
                                    <label class="d-block text-muted small mb-1">Tanggal Surat</label>
                                    <div class="fw-medium text-dark" id="cancelShowTanggalText"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Alasan Pembatalan -->
                        <div class="mb-3">
                            <label for="ALASAN_PEMBATALAN" class="form-label small fw-bold">
                                Alasan Pembatalan<span class="text-danger">*</span>
                            </label>
                            <textarea 
                                class="form-control" 
                                id="ALASAN_PEMBATALAN" 
                                name="ALASAN_PEMBATALAN" 
                                rows="4" 
                                required
                                placeholder="Jelaskan alasan pembatalan nomor surat ini"
                                style="border-radius: 8px; resize: none;"></textarea>
                        </div>
                    </div>
                    
                    <!-- Tombol -->
                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <button type="submit" 
                                class="btn btn-danger px-4 rounded-pill" 
                                style="min-width: 120px;">
                            Batalkan
                        </button>
                        <button type="button" 
                                data-bs-dismiss="modal"
                                class="btn btn-outline-secondary px-4 rounded-pill" 
                                style="min-width: 120px;">
                            Kembali
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Sertakan JavaScript Kustom -->
<script src="<?= base_url('js/penomoran-custom.js') ?>"></script>


<?= $this->endSection() ?>
