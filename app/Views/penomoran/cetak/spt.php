<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPT — <?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/cetak-surat.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════════
     TOOLBAR — Hanya tampil di layar, hilang saat cetak
════════════════════════════════════════════════════════════════ -->
<div id="toolbar-cetak">
    <div>
        <div class="toolbar-title">📄 Surat Perintah Tugas (SPT)</div>
        <div class="toolbar-info">
            <?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?>
            &nbsp;·&nbsp;
            <?= esc($penomoran['UNIT_KERJA'] ?? '-') ?>
        </div>
    </div>
    <div class="toolbar-actions">
        <button class="btn-kembali" onclick="window.close()">
            <i class="fas fa-arrow-left"></i> Kembali
        </button>
        <button class="btn-cetak" onclick="cetakDokumen()">
            <i class="fas fa-print"></i> Cetak / Simpan PDF
        </button>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     HALAMAN SURAT A4
════════════════════════════════════════════════════════════════ -->
<div class="halaman-surat">

    <!-- KOP NASKAH DINAS (SPT menggunakan Kop Naskah Dinas dengan logo) -->
    <div class="kop-naskah-dinas">
        <img src="<?= esc($logoUrl) ?>" alt="Logo Kemenko PMK">
        <div class="kop-teks">
            <div class="instansi-induk">Kementerian Koordinator Bidang</div>
            <div class="instansi-utama">Pembangunan Manusia dan Kebudayaan</div>
            <div class="instansi-sub">Republik Indonesia</div>
            <div class="kop-alamat">
                Jl. Medan Merdeka Barat No. 3, Jakarta Pusat 10110 &nbsp;|&nbsp;
                Telp. (021) 345-2165 &nbsp;|&nbsp; www.kemenkopmk.go.id
            </div>
        </div>
    </div>

    <!-- JUDUL SURAT -->
    <div class="judul-surat">
        <h2>Surat Perintah Tugas</h2>
        <div class="nomor-surat">
            NOMOR <?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?>
        </div>
    </div>

    <!-- KONSIDERAN -->
    <table class="tabel-konsideran">
        <tr>
            <td class="konsideran-label">Menimbang</td>
            <td class="konsideran-sep">:</td>
            <td>
                <textarea class="field-isi" id="menimbang" rows="3"
                    placeholder="a. bahwa ... ; b. bahwa ..."></textarea>
                <div class="field-hint">Isi pertimbangan / alasan pemberian tugas</div>
            </td>
        </tr>
        <tr>
            <td class="konsideran-label">Dasar</td>
            <td class="konsideran-sep">:</td>
            <td>
                <textarea class="field-isi" id="dasar" rows="3"
                    placeholder="1. Peraturan ... ; 2. Surat ..."></textarea>
                <div class="field-hint">Isi dasar hukum / referensi yang mendasari</div>
            </td>
        </tr>
    </table>

    <!-- BATANG TUBUH -->
    <p style="margin: 16px 0 8px; font-weight: 700; text-align: center; letter-spacing: 1px;">
        MEMERINTAHKAN:
    </p>

    <table class="tabel-konsideran" style="margin-bottom: 8px;">
        <tr>
            <td class="konsideran-label">Kepada</td>
            <td class="konsideran-sep">:</td>
            <td>
                <input type="text" class="field-inline" id="kepada-nama"
                    placeholder="Nama pegawai yang diperintah" style="width:90%;">
                <div class="field-hint">Nama pegawai yang diberi tugas</div>
            </td>
        </tr>
        <tr>
            <td class="konsideran-label">Jabatan</td>
            <td class="konsideran-sep">:</td>
            <td>
                <input type="text" class="field-inline" id="kepada-jabatan"
                    placeholder="Jabatan pegawai" style="width:90%;">
                <div class="field-hint">Jabatan pegawai yang diberi tugas</div>
            </td>
        </tr>
        <tr>
            <td class="konsideran-label">Untuk</td>
            <td class="konsideran-sep">:</td>
            <td>
                <textarea class="field-isi" id="untuk" rows="3"
                    placeholder="Uraian tugas yang harus dilaksanakan, contoh: Melaksanakan perjalanan dinas ke..."></textarea>
                <div class="field-hint">Isi uraian tugas / kegiatan yang dilaksanakan</div>
            </td>
        </tr>
        <tr>
            <td class="konsideran-label">Perihal</td>
            <td class="konsideran-sep">:</td>
            <td><?= esc($penomoran['PERIHAL'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="konsideran-label">Waktu</td>
            <td class="konsideran-sep">:</td>
            <td>
                <input type="text" class="field-inline" id="waktu"
                    placeholder="Tanggal / periode pelaksanaan tugas" style="width:90%;">
                <div class="field-hint">Tanggal atau periode tugas dilaksanakan</div>
            </td>
        </tr>
        <tr>
            <td class="konsideran-label">Tempat</td>
            <td class="konsideran-sep">:</td>
            <td>
                <input type="text" class="field-inline" id="tempat-tugas"
                    placeholder="Lokasi pelaksanaan tugas" style="width:90%;">
                <div class="field-hint">Kota / lokasi pelaksanaan tugas</div>
            </td>
        </tr>
    </table>

    <p style="font-size: 11pt; margin-top: 12px;">
        Setelah selesai melaksanakan tugas, yang bersangkutan diwajibkan melaporkan hasilnya kepada
        <?= esc($penomoran['NAMA'] ?? 'pejabat yang berwenang') ?>.
    </p>

    <!-- KAKI SURAT (TTD) -->
    <div class="kaki-surat">
        <div class="blok-ttd">
            <div class="tempat-tanggal">
                Jakarta, <?= $penomoran['TANGGAL'] ? date('d F Y', strtotime($penomoran['TANGGAL'])) : '-' ?>
            </div>
            <div class="jabatan-ttd">
                <input type="text" class="field-inline" id="jabatan-ttd"
                    placeholder="Jabatan penandatangan"
                    style="text-align:center;width:100%;font-weight:700;">
                <span class="field-hint" style="font-size:7pt;">Jabatan penandatangan</span>
            </div>
            <div class="nama-ttd">
                <input type="text" class="field-inline" id="nama-ttd"
                    value="<?= esc($penomoran['NAMA'] ?? '') ?>"
                    style="text-align:center;width:100%;font-weight:700;text-decoration:underline;">
            </div>
            <div class="nip-ttd">
                NIP.&nbsp;<input type="text" class="field-inline" id="nip-ttd"
                    placeholder="NIP"
                    style="min-width:160px;text-align:center;">
            </div>
        </div>
    </div>

</div><!-- /.halaman-surat -->

<script>
function cetakDokumen() {
    const kepada  = document.getElementById('kepada-nama').value.trim();
    const untuk   = document.getElementById('untuk').value.trim();
    const jabTtd  = document.getElementById('jabatan-ttd').value.trim();

    if (!kepada) {
        alert('Harap isi nama pegawai yang diperintah (kolom "Kepada").');
        document.getElementById('kepada-nama').focus();
        return;
    }
    if (!untuk) {
        alert('Harap isi uraian tugas (kolom "Untuk") sebelum mencetak.');
        document.getElementById('untuk').focus();
        return;
    }
    if (!jabTtd) {
        alert('Harap isi jabatan penandatangan sebelum mencetak.');
        document.getElementById('jabatan-ttd').focus();
        return;
    }

    window.print();
}
</script>
</body>
</html>
