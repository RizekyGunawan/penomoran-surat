<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Page Title -->
<div class="row mb-2">
    <div class="col-md-12">
        <h1 class="h3 mb-0 text-gray-800">Penomoran Surat</h1>
    </div>
</div>

<!-- Breadcrumb -->
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

<!-- Tab Navigation -->
<div class="tab-navigation">
    <a href="<?= base_url('penomoran/create') ?>" class="tab-btn active">Form Penomoran</a>
    <a href="<?= base_url('penomoran') ?>" class="tab-btn">Riwayat Pengajuan</a>
</div>

<!-- Form Card -->
<div class="card border rounded card-custom">
    <div class="card-body p-4">
        <!-- Form Title -->
        <h4 class="mb-4 form-section-title">Form Pengajuan Nomor Surat</h4>

        <!-- JS Validation Alert (Hidden by default) -->
        <div id="jsValidationAlert" class="d-none mb-4 alert-validation">
            <div class="d-flex align-items-center">
                <i class="far fa-times-circle" style="color: #dc2626; font-size: 1.25rem; margin-right: 12px;"></i>
                <span style="color: #ef4444; font-size: 0.875rem; font-weight: 500;">
                    Form belum lengkap, pastikan semua field sudah terisi dengan benar
                </span>
            </div>
        </div>

        <?php if (session()->has('errors')): ?>
            <div class="alert alert-dismissible fade show mb-4" role="alert"
                style="background-color: #fee2e2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px 16px;">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-circle"
                        style="color: #dc2626; font-size: 1.25rem; margin-right: 12px; margin-top: 2px;"></i>
                    <div style="flex: 1;">
                        <span style="color: #991b1b; font-size: 0.875rem; font-weight: 500;">
                            Form belum lengkap, pastikan semua field sudah terisi dengan benar
                        </span>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <form id="penomoranForm" method="POST"
            action="<?= isset($penomoran) ? base_url('penomoran/update/' . $penomoran['NO'] . '/' . urlencode($penomoran['JENIS_DOKUMEN'])) : base_url('penomoran/store') ?>"
            enctype="multipart/form-data" novalidate>
            <?= csrf_field() ?>

            <?php
            // Ambil konteks unit kerja dari controller
            // Jika tidak ada (kompatibilitas), buat konteks default
            $ctx = $unitContext ?? [
                'nama' => $unitKerja ?? '',
                'kode' => '',
                'type' => 'fixed',
                'options' => [],
                'selected_id' => null,
            ];
            ?>

            <!-- Row 1: Asal Surat & Pengajuan Oleh -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="unit_kerja" class="form-label text-muted small">Asal Surat</label>

                    <?php if ($ctx['type'] === 'dropdown' && !empty($ctx['options'])): ?>
                        <!-- MODE DROPDOWN: Untuk pegawai Deputi (bisa pilih Asdep) -->
                        <select class="form-control" id="unit_kerja" name="UNIT_KERJA" onchange="onAsalSuratChange(this)"
                            required>
                            <?php foreach ($ctx['options'] as $opt): ?>
                                <option value="<?= esc($opt['nama_unit_kerja']) ?>"
                                    data-kode="<?= esc(\App\Services\UnitKerjaService::UNIT_KODE_MAP[$ctx['parent_id']] ?? '') ?>"
                                    <?= ((int) $opt['id'] === (int) $ctx['selected_id']) ? 'selected' : '' ?>>
                                    <?= esc($opt['nama_unit_kerja']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted" style="font-size:0.75rem;">
                            <i class="fas fa-info-circle"></i>
                            Pilih unit kerja pengaju surat
                        </small>

                    <?php else: ?>
                        <!-- MODE FIXED: Untuk Biro, Inspektorat, Staf Ahli, dll -->
                        <input type="text" class="form-control bg-light" id="unit_kerja" name="UNIT_KERJA"
                            value="<?= esc($ctx['nama']) ?>" <?= empty($ctx['nama']) && session('user.role_id') == 1 ? '' : 'readonly' ?> required>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="nama" class="form-label text-muted small">Pengajuan Oleh</label>
                    <input type="text" class="form-control bg-light" id="nama" name="NAMA"
                        value="<?= isset($penomoran) ? esc($penomoran['NAMA']) : (isset($userName) ? esc($userName) : '') ?>"
                        placeholder="Nama Pengaju" readonly>
                </div>
            </div>

            <!-- Row 2: Jenis Surat & Tanggal Surat -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="jenis_dokumen" class="form-label text-muted small">
                        Jenis Surat<span class="text-danger">*</span>
                    </label>
                    <select class="form-control" id="jenis_dokumen" name="JENIS_DOKUMEN" <?= isset($penomoran) ? 'disabled' : '' ?> required>
                        <option value="">Pilih jenis surat</option>
                        <?php
                        $selectedJenis = isset($penomoran) ? $penomoran['JENIS_DOKUMEN'] : ($jenisDokumen ?? '');
                        foreach ($jenisDokumenList as $jenis):
                            ?>
                            <option value="<?= esc($jenis) ?>" <?= ($selectedJenis === $jenis) ? 'selected' : '' ?>>
                                <?= esc($jenis) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($penomoran)): ?>
                        <input type="hidden" name="JENIS_DOKUMEN" value="<?= esc($penomoran['JENIS_DOKUMEN']) ?>">
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="tanggal" class="form-label text-muted small">Tanggal Surat<span
                            class="text-danger">*</span></label>
                    <?php $isAdmin = session('user.role_id') == 1; ?>
                    <input type="date" class="form-control" id="tanggal" name="TANGGAL"
                        value="<?= isset($penomoran) && $penomoran['TANGGAL'] ? date('Y-m-d', strtotime($penomoran['TANGGAL'])) : date('Y-m-d') ?>"
                        readonly style="background-color: #f1f5f9 !important; pointer-events: none;" required>
                </div>
            </div>

            <!-- Row 3: Perihal Surat -->
            <div class="mb-3">
                <label for="perihal" class="form-label text-muted small">Perihal Surat<span
                        class="text-danger">*</span></label>
                <textarea class="form-control" id="perihal" name="PERIHAL" rows="3"
                    placeholder="Contoh: Undangan Rapat Koordinasi"
                    required><?= isset($penomoran) ? esc($penomoran['PERIHAL']) : '' ?></textarea>
            </div>

            <!-- Row 4: Sifat Surat & Kode Jabatan (TND) -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <!-- Wrapper Sifat Surat -->
                    <div id="wrapper-sifat-surat">
                        <label for="sifat_surat" class="form-label text-muted small">
                            Sifat Surat <span class="text-danger" id="sifat-asterisk">*</span>
                            <span class="text-muted" style="font-weight:400;"
                                title="Sesuai Permenko No. 1/2024">&#9432;</span>
                        </label>
                        <select class="form-control" id="sifat_surat" name="SIFAT_SURAT">
                            <?php
                            $selectedSifat = isset($penomoran) ? ($penomoran['SIFAT_SURAT'] ?? 'B') : 'B';
                            $sifatOptions = [
                                'B' => 'B — Biasa',
                                'T' => 'T — Terbatas',
                                'R' => 'R — Rahasia',
                                'SR' => 'SR — Sangat Rahasia',
                            ];
                            foreach ($sifatOptions as $val => $label):
                                ?>
                                <option value="<?= $val ?>" <?= ($selectedSifat === $val) ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Wrapper Kode Jabatan -->
                    <div id="wrapper-kode-jabatan" style="display:none;">
                        <label for="kode_jabatan" class="form-label text-muted small">
                            Kode Jabatan/Unit
                            <span class="text-muted" style="font-weight:400;"
                                title="Digunakan pada SPT">&#9432;</span>
                        </label>
                        <?php
                        // Kode jabatan: jika ada di record (mode edit), pakai itu
                        // jika mode create, pakai dari konteks unit kerja (otomatis)
                        $kodeJabatanValue = isset($penomoran)
                            ? esc($penomoran['KODE_JABATAN'] ?? $ctx['kode'] ?? '')
                            : esc($ctx['kode'] ?? '');
                        ?>
                        <input type="text" class="form-control bg-light" id="kode_jabatan" name="KODE_JABATAN"
                            value="<?= $kodeJabatanValue ?>" placeholder="Terisi otomatis" maxlength="50"
                            style="text-transform:uppercase;" readonly>
                        <small class="text-muted" style="font-size:0.75rem;">
                            <i class="fas fa-lock"></i> Otomatis berdasarkan unit kerja
                        </small>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="kode_klasifikasi_display" class="form-label text-muted small">
                        Kode Klasifikasi Arsip
                        <span class="text-muted" style="font-weight:400;" title="Contoh: KA.01, TU.00.01">&#9432;</span>
                    </label>
                    <!-- Autocomplete wrapper -->
                    <div style="position:relative;">
                        <input type="text" class="form-control" id="kode_klasifikasi_display" autocomplete="off"
                            value="<?= isset($penomoran) ? esc($penomoran['KODE_KLASIFIKASI'] ?? '') : '' ?>"
                            placeholder="Ketik kode atau nama..." maxlength="50">
                        <!-- Dropdown list -->
                        <div id="klasifikasi-dropdown" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1000;
                                   background:#fff; border:1px solid #dee2e6; border-radius:0 0 6px 6px;
                                   max-height:220px; overflow-y:auto; box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                        </div>
                    </div>
                    <!-- Field tersembunyi yang benar-benar dikirim ke server -->
                    <input type="hidden" id="kode_klasifikasi" name="KODE_KLASIFIKASI"
                        value="<?= isset($penomoran) ? esc($penomoran['KODE_KLASIFIKASI'] ?? '') : '' ?>">
                    <!-- Uraian kode yang dipilih -->
                    <small id="kode-klasifikasi-uraian" class="text-success" style="font-size:0.75rem;"></small>
                </div>
            </div>

            <!-- Preview Nomor Surat (TND) -->
            <div class="mb-4" id="preview-nomor-tnd" style="display:none;">
                <label class="form-label text-muted small">Preview Nomor Surat (TND)</label>
                <div class="d-flex align-items-center gap-2 px-3 py-2 rounded"
                    style="background:#f0fdf4; border:1px solid #bbf7d0;">
                    <i class="fas fa-file-alt" style="color:#10b981;"></i>
                    <span id="tnd-nomor-preview" class="fw-semibold"
                        style="color:#065f46; font-size:1rem; letter-spacing:0.5px;"></span>
                    <span class="badge ms-2" style="background:#dcfce7; color:#166534; font-size:0.7rem;">PREVIEW</span>
                </div>
                <small class="text-muted">Preview otomatis — akan diperbarui saat field TND diubah</small>
            </div>

            <!-- Hidden Fields -->
            <input type="hidden" name="NO"
                value="<?= isset($penomoran) ? esc($penomoran['NO']) : (isset($nextNo) ? $nextNo : '') ?>">
            <input type="hidden" name="TAHUN"
                value="<?= isset($penomoran) ? esc($penomoran['TAHUN']) : (isset($tahun) && !empty($tahun) ? $tahun : date('Y')) ?>">
            <input type="hidden" name="JUMLAH_LAMPIRAN" value="0">
            <input type="hidden" name="TUJUAN_SURAT" value="">
            <input type="hidden" name="TEMBUSAN_SURAT" value="">

            <!-- Buttons -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success px-4 rounded-pill d-inline-flex align-items-center justify-content-center" style="background-color: #10b981; border-color: #10b981; font-weight: 600; color: white; gap: 6px;"><?= isset($penomoran) ? 'Perbarui' : 'Generate Nomor' ?></button>
                <button type="reset" class="btn btn-outline-secondary px-4 rounded-pill d-inline-flex align-items-center justify-content-center" style="font-weight: 600; gap: 6px;">Reset</button>
            </div>
        </form>
    </div>
</div>


<!-- Modal Konfirmasi Nomor Surat -->
<div class="modal fade" id="modalKonfirmasi" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <!-- Header with Icon -->
            <div class="modal-body text-center p-4">
                <!-- Success Icon -->
                <div class="mb-2">
                    <i class="fas fa-check-circle" style="font-size: 3rem; color: #10b981;"></i>
                </div>

                <!-- Title -->
                <h5 class="mb-4" style="font-weight: 600;">Nomor surat berhasil dibuat</h5>

                <!-- Information Fields -->
                <div class="text-start mb-3">
                    <!-- Nomor Surat (Disembunyikan / Hidden untuk Backend Saja) -->
                    <div class="d-none">
                        <span id="preview-no"></span>
                    </div>

                    <!-- Nomor Surat Lengkap (Pindah ke Atas & Menonjol) -->
                    <div class="mb-4">
                        <label for="nomor_surat_lengkap" class="form-label text-muted small mb-1 fw-bold">
                            Nomor Surat Lengkap<span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-lg shadow-sm" style="border-radius: 8px; overflow: hidden;">
                            <input type="text" class="form-control fw-bold" id="nomor_surat_lengkap"
                                name="NOMOR_SURAT_LENGKAP"
                                style="color: #0f766e; background-color: #f0fdfa; border-color: #5eead4; border-right: none;"
                                placeholder="Format nomor surat..." required>
                            <button class="btn" type="button" onclick="copyInputValue('nomor_surat_lengkap')"
                                style="background-color: #f0fdfa; border: 1px solid #5eead4; border-left: none; color: #0d9488;"
                                title="Copy ke Clipboard">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                        <small class="text-danger d-none" id="error-nomor-lengkap">
                            Nomor Surat Lengkap wajib diisi
                        </small>
                    </div>

                    <!-- Details Group -->
                    <div class="border rounded p-4 mb-4">
                        <!-- Jenis Surat & Tanggal Surat (2 columns) -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="mb-1">
                                    <label class="form-label text-muted small mb-1">Jenis Surat</label>
                                    <div>
                                        <span id="preview-jenis" class="fw-medium text-dark"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-1">
                                    <label class="form-label text-muted small mb-1">Tanggal Surat</label>
                                    <div>
                                        <span id="preview-tanggal" class="fw-medium text-dark"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pengajuan Oleh -->
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Pengajuan Oleh</label>
                            <div>
                                <span id="preview-nama" class="fw-medium text-dark"></span>
                            </div>
                        </div>

                        <!-- Unit Kerja Yang Mengajukan -->
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Unit Kerja Yang Mengajukan</label>
                            <div>
                                <span id="preview-unit" class="fw-medium text-dark"></span>
                            </div>
                        </div>

                        <!-- Perihal Surat -->
                        <div class="mb-0">
                            <label class="form-label text-muted small mb-1">Perihal Surat</label>
                            <div>
                                <span id="preview-perihal" class="fw-medium text-dark"></span>
                            </div>
                        </div>
                    </div>

                    <!-- (Nomor Surat Lengkap sudah dipindah ke atas) -->
                </div>

                <!-- Buttons -->
                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-danger px-4 rounded-pill" onclick="batalKonfirmasi()"
                        style="min-width: 120px;">
                        Batalkan
                    </button>
                    <div class="d-flex gap-2">
                        <!-- Tombol Cetak Dokumen — membuka template di tab baru -->
                        <button type="button" class="btn px-4 rounded-pill d-none" id="btnCetakDokumen"
                            onclick="bukaCetakDokumen()"
                            style="background-color:#1e3a5f;border-color:#1e3a5f;color:white;min-width:150px;"
                            title="Buka template dokumen untuk diisi dan dicetak">
                            <i class="fas fa-print me-1"></i> Cetak Dokumen
                        </button>
                        <button type="button" class="btn px-4 rounded-pill" id="btnSelesai"
                            onclick="selesaiKonfirmasi()"
                            style="background-color: #6b7280; border-color: #6b7280; color: white; min-width: 120px;">
                            Selesai
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Copy to Clipboard Function -->
<script>
    function copyToClipboard(elementId) {
        const text = document.getElementById(elementId).textContent;
        navigator.clipboard.writeText(text).then(() => {
            alert('Teks berhasil disalin!');
        });
    }

    function copyInputValue(elementId) {
        const input = document.getElementById(elementId);
        input.select();
        input.setSelectionRange(0, 99999); /* For mobile devices */
        navigator.clipboard.writeText(input.value).then(() => {
            // SweetAlert toast notification (more elegant than alert)
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Nomor disalin ke clipboard!',
                showConfirmButton: false,
                timer: 2000
            });
        });
    }
</script>

<!-- SweetAlert2 CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Check if this is edit mode
    const isEditMode = <?= isset($penomoran) ? 'true' : 'false' ?>;

    /**
     * Dipanggil saat dropdown "Asal Surat" berubah (khusus mode Deputi).
     * Fungsi ini memperbarui field Kode Jabatan secara otomatis
     * mengikuti pilihan unit kerja, lalu memperbarui preview nomor surat.
     */
    function onAsalSuratChange(selectEl) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        // Ambil kode jabatan dari data-kode atribut option yang dipilih
        const kode = selectedOption.getAttribute('data-kode') || '';
        const kodeJabatanEl = document.getElementById('kode_jabatan');
        if (kodeJabatanEl) {
            kodeJabatanEl.value = kode.toUpperCase();
            updateTndPreview(); // Perbarui preview nomor surat
        }
    }

    // Tempat simpan CSRF token segar setelah Step 1 (preview) selesai.
    // Token ini menggantikan token lama yang di-bake saat halaman pertama kali dibuka.
    let _freshCsrf = { token: '<?= csrf_token() ?>', hash: '<?= csrf_hash() ?>' };

    /**
     * Perbarui _freshCsrf dan hidden input form dengan token terbaru dari response server.
     * Dipanggil setiap kali menerima response dari finalize() agar token tidak kadaluarsa
     * saat user ingin Generate Nomor ulang (akibat regenerate = true di Security config).
     */
    function refreshCsrfFromResponse(data) {
        if (data && data.csrf_token && data.csrf_hash) {
            _freshCsrf = { token: data.csrf_token, hash: data.csrf_hash };
            // Perbarui hidden input CSRF di form agar submit berikutnya pakai token segar
            const csrfInput = document.querySelector(`input[name="${_freshCsrf.token}"]`);
            if (csrfInput) {
                csrfInput.value = _freshCsrf.hash;
            }
        }
    }

    // Form submission handler
    document.getElementById('penomoranForm').addEventListener('submit', function (e) {
        e.preventDefault();

        // Reset validation alert
        document.getElementById('jsValidationAlert').classList.add('d-none');

        // Manual validation check
        if (!this.checkValidity()) {
            e.stopPropagation();
            this.classList.add('was-validated');

            // Show inline alert
            const alertBox = document.getElementById('jsValidationAlert');
            alertBox.classList.remove('d-none');

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });

            return;
        }

        // If edit mode, use traditional form submission
        if (isEditMode) {
            this.submit();
            return;
        }

        // Create mode: use AJAX for 2-step process
        const formData = new FormData(this);

        // Show loading
        Swal.fire({
            title: 'Memproses...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // AJAX request untuk preview (Step 1)
        fetch('<?= base_url('penomoran/preview') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(async response => {
                const isJson = response.headers.get('content-type')?.includes('application/json');
                const data = isJson ? await response.json() : null;
                if (!response.ok) {
                    throw new Error((data && data.message) || response.statusText || 'Terjadi kesalahan pada server');
                }
                if (!isJson) {
                    throw new Error('Respon server bukan JSON. Silakan cek log CodeIgniter.');
                }
                return data;
            })
            .then(data => {
                Swal.close();

                // Perbarui CSRF token dengan yang baru dari server.
                // Token lama sudah tidak valid karena di-rotate oleh Security::$regenerate = true.
                if (data.csrf_token && data.csrf_hash) {
                    _freshCsrf = { token: data.csrf_token, hash: data.csrf_hash };
                    // Update value di DOM form input agar jika disubmit ulang tidak pakai token lama
                    const csrfInput = document.querySelector(`input[name="${_freshCsrf.token}"]`);
                    if (csrfInput) {
                        csrfInput.value = _freshCsrf.hash;
                    }
                }

                if (data.success) {
                    // Populate modal dengan data
                    document.getElementById('preview-no').textContent = data.data.NO;
                    document.getElementById('preview-jenis').textContent = data.data.JENIS_DOKUMEN;
                    document.getElementById('preview-tanggal').textContent = data.data.TANGGAL_DISPLAY || data.data.TANGGAL;
                    document.getElementById('preview-perihal').textContent = data.data.PERIHAL || '-';
                    document.getElementById('preview-unit').textContent = data.data.UNIT_KERJA || '-';
                    document.getElementById('preview-nama').textContent = data.data.NAMA || '-';

                    // Auto-fill nomor surat lengkap dari server
                    document.getElementById('nomor_surat_lengkap').value = data.nomorSuratLengkap || '';

                    // Simpan data cetak untuk tombol Cetak Dokumen
                    window._cetakData = {
                        no: data.data.NO,
                        jenis: data.data.JENIS_DOKUMEN,
                        tahun: data.data.TAHUN,
                    };

                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('modalKonfirmasi'));
                    modal.show();
                } else {
                    // Tampilkan alert validasi dengan detail error dari server
                    const alertBox = document.getElementById('jsValidationAlert');
                    let errorMsg = 'Form belum lengkap, pastikan semua field sudah terisi dengan benar';

                    if (data.errors) {
                        const fieldLabels = {
                            'JENIS_DOKUMEN': 'Jenis Surat',
                            'TANGGAL': 'Tanggal Surat',
                            'UNIT_KERJA': 'Asal Surat',
                            'PERIHAL': 'Perihal Surat',
                            'NAMA': 'Pengajuan Oleh',
                            'TAHUN': 'Tahun',
                        };
                        const failedFields = Object.keys(data.errors)
                            .map(k => fieldLabels[k] || k)
                            .join(', ');
                        errorMsg += ` (Field kosong: ${failedFields})`;
                    }

                    alertBox.querySelector('span').textContent = errorMsg;
                    alertBox.classList.remove('d-none');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            })
            .catch(error => {
                setTimeout(() => {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: 'Error: ' + error.message,
                        confirmButtonText: 'OK'
                    });
                }, 200);
            });
    });

    // Batal konfirmasi
    function batalKonfirmasi() {
        Swal.fire({
            title: 'Yakin ingin membatalkan?',
            text: 'Data tidak akan tersimpan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalKonfirmasi'));
                modal.hide();
                document.getElementById('nomor_surat_lengkap').value = '';
            }
        });
    }

    // Buka template cetak di tab baru
    function bukaCetakDokumen() {
        if (!window._cetakData) {
            alert('Data surat belum tersedia. Silakan generate nomor surat terlebih dahulu.');
            return;
        }

        const nomorLengkap = document.getElementById('nomor_surat_lengkap').value.trim();

        // Disable kedua tombol untuk mencegah double submission
        document.getElementById('btnSelesai').disabled = true;
        document.getElementById('btnCetakDokumen').disabled = true;

        // Tampilkan loading
        Swal.fire({
            title: 'Menyimpan & Menyiapkan Dokumen...',
            text: 'Nomor surat sedang disimpan, halaman cetak akan terbuka otomatis.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        // Pakai token CSRF segar (sama seperti selesaiKonfirmasi)
        const finalizeData = new FormData();
        finalizeData.append('NOMOR_SURAT_LENGKAP', nomorLengkap);
        finalizeData.append(_freshCsrf.token, _freshCsrf.hash);

        fetch('<?= base_url('penomoran/finalize') ?>', {
            method: 'POST',
            body: finalizeData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(async response => {
                const isJson = response.headers.get('content-type')?.includes('application/json');
                const data = isJson ? await response.json() : null;
                if (!response.ok) {
                    throw new Error((data && data.message) || response.statusText || 'Terjadi kesalahan pada server');
                }
                if (!isJson) {
                    throw new Error('Respon server bukan JSON. Silakan cek log CodeIgniter.');
                }
                return data;
            })
            .then(data => {
                Swal.close();

                // Selalu perbarui CSRF token dari response, sukses maupun gagal
                refreshCsrfFromResponse(data);

                if (data.success) {
                    // Buka halaman cetak di tab baru SETELAH record tersimpan
                    const { no, jenis, tahun } = window._cetakData;
                    const cetakUrl = `<?= base_url('penomoran/cetak') ?>/${no}/${encodeURIComponent(jenis)}/${tahun}`;
                    window.open(cetakUrl, '_blank');

                    // Redirect halaman utama ke riwayat (sama seperti selesaiKonfirmasi)
                    window.location.href = data.redirect;

                } else {
                    // Kembalikan tombol jika gagal
                    document.getElementById('btnSelesai').disabled = false;
                    document.getElementById('btnCetakDokumen').disabled = false;

                    let errorMsg = data.message || 'Terjadi kesalahan saat menyimpan data';
                    if (data.isDuplicate) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Nomor Surat Sudah Digunakan',
                            text: errorMsg,
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: '#6b7280',
                            allowOutsideClick: false
                        }).then(() => {
                            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalKonfirmasi'));
                            if (modalInstance) modalInstance.hide();
                            document.getElementById('nomor_surat_lengkap').value = '';
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: errorMsg });
                    }
                }
            })
            .catch(error => {
                setTimeout(() => {
                    Swal.close();
                    document.getElementById('btnSelesai').disabled = false;
                    document.getElementById('btnCetakDokumen').disabled = false;
                    Swal.fire({ icon: 'error', title: 'Error Koneksi', text: error.message });
                }, 200);
            });
    }


    // Selesai konfirmasi (Step 2)
    function selesaiKonfirmasi() {
        const nomorLengkap = document.getElementById('nomor_surat_lengkap').value.trim();

        // Nomor lengkap sekarang opsional, tidak perlu validasi
        // Hide error if any
        document.getElementById('error-nomor-lengkap').classList.add('d-none');
        document.getElementById('nomor_surat_lengkap').classList.remove('is-invalid');

        // Disable button to prevent double submission
        document.getElementById('btnSelesai').disabled = true;

        // Show loading
        Swal.fire({
            title: 'Menyimpan...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });



        // Prepare FormData untuk finalize — gunakan token segar, bukan token lama
        // yang di-bake saat halaman dibuka (sudah basi setelah Step 1 preview).
        const finalizeData = new FormData();
        finalizeData.append('NOMOR_SURAT_LENGKAP', nomorLengkap);
        finalizeData.append(_freshCsrf.token, _freshCsrf.hash);

        // AJAX request untuk finalize (Step 2)
        fetch('<?= base_url('penomoran/finalize') ?>', {
            method: 'POST',
            body: finalizeData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(async response => {
                const isJson = response.headers.get('content-type')?.includes('application/json');
                const data = isJson ? await response.json() : null;
                if (!response.ok) {
                    throw new Error((data && data.message) || response.statusText || 'Terjadi kesalahan pada server');
                }
                if (!isJson) {
                    throw new Error('Respon server bukan JSON. Silakan cek log CodeIgniter.');
                }
                return data;
            })
            .then(data => {
                Swal.close();

                // Selalu perbarui CSRF token dari response, sukses maupun gagal
                refreshCsrfFromResponse(data);

                if (data.success) {
                    // Langsung redirect, pesan sukses akan muncul via Toast global
                    window.location.href = data.redirect;
                } else {
                    // Re-enable button on error
                    document.getElementById('btnSelesai').disabled = false;

                    // Build error message
                    let errorMsg = data.message || 'Terjadi kesalahan saat menyimpan data';
                    if (data.errors) {
                        errorMsg += '\n\nDetail error:\n';
                        for (let field in data.errors) {
                            errorMsg += '• ' + data.errors[field] + '\n';
                        }
                    }
                    if (data.isDuplicate) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Nomor Surat Sudah Digunakan',
                            text: errorMsg,
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: '#6b7280',
                            allowOutsideClick: false
                        }).then(() => {
                            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalKonfirmasi'));
                            if (modalInstance) modalInstance.hide();
                            document.getElementById('nomor_surat_lengkap').value = '';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: errorMsg,
                            confirmButtonText: 'OK'
                        });
                    }
                } // end: if (data.success)
            })
            .catch(error => {
                setTimeout(() => {
                    Swal.close();
                    document.getElementById('btnSelesai').disabled = false;

                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: 'Error: ' + error.message,
                        confirmButtonText: 'OK'
                    });
                }, 200);
            });
    }

    // Auto-update NO when JENIS_DOKUMEN changes (existing functionality)
    document.getElementById('jenis_dokumen').addEventListener('change', function () {
        if (this.value) {
            const tahunEl = document.querySelector('input[name="TAHUN"]');
            const tahun = tahunEl ? tahunEl.value : new Date().getFullYear();

            // GET request — tidak terkena CSRF filter
            fetch(`<?= base_url('penomoran/getNextNoAjax') ?>?jenis_dokumen=${encodeURIComponent(this.value)}&tahun=${tahun}`, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.json())
                .then(data => {
                    const noEl = document.querySelector('input[name="NO"]');
                    if (noEl) noEl.value = data.next_no;
                    updateTndPreview();
                })
                .catch(() => {
                    // Silent — auto-update NO tidak kritis
                });

            // Tampilkan/sembunyikan field Kode Jabatan (hanya untuk SPT)
            toggleKodeJabatanField(this.value);
        }
    });

    // =============================================
    // LIVE PREVIEW NOMOR SURAT (TND)
    // =============================================
    // Nota Dinas dihapus dari sistem — hanya SPT yang menggunakan format kode jabatan
    const jenisTndInternal = ['SPT'];

    /**
     * Tampilkan/sembunyikan field Kode Jabatan sesuai jenis dokumen.
     * Hanya relevan untuk SPT.
     */
    function toggleKodeJabatanField(jenisDokumen) {
        const wrapperSifatSurat = document.getElementById('wrapper-sifat-surat');
        const wrapperKodeJabatan = document.getElementById('wrapper-kode-jabatan');

        if (jenisTndInternal.includes(jenisDokumen)) {
            // Jika SPT: Sifat Surat Hilang, digantikan Kode Jabatan
            if (wrapperSifatSurat) wrapperSifatSurat.style.display = 'none';
            if (wrapperKodeJabatan) {
                wrapperKodeJabatan.style.display = '';
                document.getElementById('kode_jabatan').placeholder = 'Wajib untuk ' + jenisDokumen + '. Contoh: ROUM';
            }
        } else {
            // Jika BUKAN SPT: Sifat Surat Tampil, Kode Jabatan Hilang
            if (wrapperSifatSurat) wrapperSifatSurat.style.display = '';
            if (wrapperKodeJabatan) wrapperKodeJabatan.style.display = 'none';
        }
    }

    /**
     * Hitung dan tampilkan preview nomor surat sesuai format TND.
     */
    function updateTndPreview() {
        const jenis = document.getElementById('jenis_dokumen').value;
        const tanggal = document.getElementById('tanggal').value;
        const noEl = document.getElementById('no');
        const no = noEl ? parseInt(noEl.value) || 1 : <?= $nextNo ?? 1 ?>;
        const tahun = document.getElementById('tahun') ? document.getElementById('tahun').value : new Date().getFullYear();
        const klasif = document.getElementById('kode_klasifikasi').value.trim() || '...';
        const sifat = document.getElementById('sifat_surat').value || 'B';
        const jabatan = (document.getElementById('kode_jabatan').value.trim() || '...').toUpperCase();

        if (!jenis) {
            document.getElementById('preview-nomor-tnd').style.display = 'none';
            return;
        }

        // Ambil bulan dari tanggal surat
        let bulan = String(new Date().getMonth() + 1).padStart(2, '0');
        if (tanggal) {
            const d = new Date(tanggal);
            if (!isNaN(d)) bulan = String(d.getMonth() + 1).padStart(2, '0');
        }

        const noPadded = String(no).padStart(3, '0');
        let preview = '';

        if (jenisTndInternal.includes(jenis)) {
            // Format internal: {No}/{KodeJabatan}/{KodeKlasifikasi}/{Bulan}/{Tahun}
            preview = `${noPadded}/${jabatan}/${klasif}/${bulan}/${tahun}`;
        } else {
            // Format eksternal: {Sifat}-{No}/{KodeKlasifikasi}/{Bulan}/{Tahun}
            preview = `${sifat.toUpperCase()}-${noPadded}/${klasif}/${bulan}/${tahun}`;
        }

        document.getElementById('tnd-nomor-preview').textContent = preview;
        document.getElementById('preview-nomor-tnd').style.display = '';
    }

    // Pasang event listener ke semua field yang mempengaruhi preview
    ['kode_klasifikasi', 'sifat_surat', 'kode_jabatan', 'tanggal'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', updateTndPreview);
        if (el) el.addEventListener('change', updateTndPreview);
    });
    document.getElementById('jenis_dokumen').addEventListener('change', updateTndPreview);

    // =============================================
    // AUTOCOMPLETE KODE KLASIFIKASI ARSIP
    // =============================================
    const inputDisplay = document.getElementById('kode_klasifikasi_display');
    const inputHidden = document.getElementById('kode_klasifikasi');
    const dropdownEl = document.getElementById('klasifikasi-dropdown');
    const uraianEl = document.getElementById('kode-klasifikasi-uraian');
    let acTimeout = null;

    function renderDropdown(items) {
        dropdownEl.innerHTML = '';
        if (!items.length) {
            dropdownEl.innerHTML = '<div style="padding:8px 12px; color:#6b7280; font-size:0.85rem;">Tidak ada hasil</div>';
        } else {
            items.forEach(item => {
                const div = document.createElement('div');
                div.style.cssText = 'padding:8px 12px; cursor:pointer; font-size:0.85rem; border-bottom:1px solid #f3f4f6;';
                div.innerHTML = `<span style="font-weight:600;color:#065f46;">${item.kode}</span>
                    <span style="color:#6b7280; margin-left:6px;">${item.uraian}</span>
                    <span class="badge ms-1" style="background:${item.fungsi === 'fasilitatif' ? '#dbeafe' : '#fef3c7'};
                        color:${item.fungsi === 'fasilitatif' ? '#1e40af' : '#92400e'}; font-size:0.65rem;">
                        ${item.fungsi}</span>`;
                div.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    inputDisplay.value = item.kode;
                    inputHidden.value = item.kode;
                    uraianEl.textContent = item.uraian;
                    dropdownEl.style.display = 'none';
                    updateTndPreview();
                });
                div.addEventListener('mouseover', () => div.style.background = '#f0fdf4');
                div.addEventListener('mouseout', () => div.style.background = '');
                dropdownEl.appendChild(div);
            });
        }
        dropdownEl.style.display = 'block';
    }

    inputDisplay.addEventListener('input', function () {
        const q = this.value.trim();
        inputHidden.value = q; // sync hidden juga
        clearTimeout(acTimeout);
        uraianEl.textContent = '';
        if (q.length < 1) { dropdownEl.style.display = 'none'; return; }
        acTimeout = setTimeout(() => {
            fetch(`<?= base_url('penomoran/getKlasifikasiAjax') ?>?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) renderDropdown(data.data);
                })
                .catch(() => { });
        }, 250);
    });

    inputDisplay.addEventListener('blur', () => {
        setTimeout(() => { dropdownEl.style.display = 'none'; }, 200);
    });

    inputDisplay.addEventListener('focus', function () {
        if (this.value.trim().length >= 1) {
            this.dispatchEvent(new Event('input'));
        }
    });

    // Sinkronisasi hidden field saat user ketik langsung (tanpa pilih dari dropdown)
    inputDisplay.addEventListener('change', function () {
        inputHidden.value = this.value.trim();
        updateTndPreview();
    });

    // Validasi Dynamic untuk Tombol Selesai
    const inputNomorLengkap = document.getElementById('nomor_surat_lengkap');
    const btnSelesai = document.getElementById('btnSelesai');

    // Function to update button state
    function updateBtnSelesaiState() {
        if (inputNomorLengkap.value.trim() !== '') {
            // State: Filled -> Green & Enabled
            btnSelesai.disabled = false;
            btnSelesai.style.backgroundColor = '#10b981'; // Green (Emerald-500 equivalent)
            btnSelesai.style.borderColor = '#10b981';
            btnSelesai.style.color = 'white';
            btnSelesai.style.cursor = 'pointer';
        } else {
            // State: Empty -> Gray & Disabled
            btnSelesai.disabled = true;
            btnSelesai.style.backgroundColor = '#6b7280'; // Gray (Gray-500)
            btnSelesai.style.borderColor = '#6b7280';
            btnSelesai.style.color = 'white';
            btnSelesai.style.cursor = 'not-allowed';
        }
    }

    // Listen to input changes
    inputNomorLengkap.addEventListener('input', updateBtnSelesaiState);
    inputNomorLengkap.addEventListener('change', updateBtnSelesaiState); // Extra caution

    // Trigger check when modal is shown (because of auto-fill)
    const modalKonfirmasiEl = document.getElementById('modalKonfirmasi');
    modalKonfirmasiEl.addEventListener('shown.bs.modal', function () {
        updateBtnSelesaiState();
    });

    // Initial check (in case)
    updateBtnSelesaiState();

    // Trigger change on page load if editing
    window.addEventListener('load', function () {
        const jenisDokumenSelect = document.getElementById('jenis_dokumen');
        if (jenisDokumenSelect.value && !document.getElementById('no').value) {
            jenisDokumenSelect.dispatchEvent(new Event('change'));
        }
        // Jalankan inisialisasi TND
        toggleKodeJabatanField(jenisDokumenSelect.value);
        updateTndPreview();
    });
</script>
<?= $this->endSection() ?>