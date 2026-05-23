<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Dinas — <?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/cetak-surat.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div id="toolbar-cetak">
    <div>
        <div class="toolbar-title">📄 Surat Dinas</div>
        <div class="toolbar-info"><?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?> · <?= esc($penomoran['UNIT_KERJA'] ?? '-') ?></div>
    </div>
    <div class="toolbar-actions">
        <button class="btn-kembali" onclick="window.close()"><i class="fas fa-arrow-left"></i> Kembali</button>
        <button class="btn-cetak" onclick="cetakDokumen()"><i class="fas fa-print"></i> Cetak / Simpan PDF</button>
    </div>
</div>

<div class="halaman-surat">
    <!-- KOP NASKAH DINAS -->
    <div class="kop-naskah-dinas">
        <img src="<?= esc($logoUrl) ?>" alt="Logo Kemenko PMK">
        <div class="kop-teks">
            <div class="instansi-induk">Kementerian Koordinator Bidang</div>
            <div class="instansi-utama">Pembangunan Manusia dan Kebudayaan</div>
            <div class="instansi-sub">Republik Indonesia</div>
            <div class="kop-alamat">Jl. Medan Merdeka Barat No. 3, Jakarta Pusat 10110 | Telp. (021) 345-2165 | www.kemenkopmk.go.id</div>
        </div>
    </div>

    <!-- TEMPAT & TANGGAL (pojok kanan atas) -->
    <div style="text-align:right; margin-top:16px; font-size:11pt;">
        Jakarta, <?= $penomoran['TANGGAL'] ? date('d F Y', strtotime($penomoran['TANGGAL'])) : '-' ?>
    </div>

    <!-- NOMOR, LAMPIRAN, HAL -->
    <table style="width:55%;border-collapse:collapse;margin-top:10px;font-size:11pt;">
        <tr>
            <td style="width:90px;">Nomor</td>
            <td style="width:12px;text-align:center;">:</td>
            <td><strong><?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?></strong></td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td style="text-align:center;">:</td>
            <td><input type="text" class="field-inline" id="lampiran" placeholder="— (Jika tidak ada, biarkan kosong)" style="min-width:200px;"></td>
        </tr>
        <tr>
            <td>Hal</td>
            <td style="text-align:center;">:</td>
            <td><?= esc($penomoran['PERIHAL'] ?? '-') ?></td>
        </tr>
    </table>

    <!-- KEPADA -->
    <div style="margin-top:20px;font-size:11pt;">
        <div>Yth.</div>
        <div style="padding-left:1.2em;">
            <input type="text" class="field-inline" id="yth-nama" placeholder="Nama penerima" style="width:70%;display:block;margin-bottom:4px;">
            <div class="field-hint">Nama penerima surat</div>
            <input type="text" class="field-inline" id="yth-alamat" placeholder="Instansi / Alamat penerima" style="width:80%;display:block;margin-top:4px;">
            <div class="field-hint">Instansi atau alamat penerima</div>
        </div>
    </div>

    <div class="garis-header" style="margin-top:20px;"></div>

    <!-- ISI SURAT -->
    <div class="isi-surat-wrapper">
        <div class="isi-label">💡 Ketik alinea pembuka, isi, dan penutup surat di bawah ini:</div>
        <p class="isi-paragraf">
            <textarea class="field-isi" id="alinea-pembuka" rows="2" placeholder="Alinea pembuka, contoh: Dalam rangka..."></textarea>
            <span class="field-hint">Alinea pembuka</span>
        </p>
        <p class="isi-paragraf">
            <textarea class="field-isi" id="alinea-isi" rows="6" placeholder="Isi utama surat dinas..."></textarea>
            <span class="field-hint">Isi utama</span>
        </p>
        <p class="isi-paragraf">
            <textarea class="field-isi" id="alinea-penutup" rows="2" placeholder="Alinea penutup, contoh: Demikian kami sampaikan, atas perhatian Bapak/Ibu diucapkan terima kasih."></textarea>
            <span class="field-hint">Alinea penutup</span>
        </p>
    </div>

    <!-- KAKI SURAT -->
    <div class="kaki-surat">
        <div class="blok-ttd">
            <div class="tempat-tanggal">a.n. <?= esc($penomoran['UNIT_KERJA'] ?? '-') ?></div>
            <div class="jabatan-ttd">
                <input type="text" class="field-inline" id="jabatan-ttd" placeholder="Jabatan penandatangan" style="text-align:center;width:100%;font-weight:700;">
                <span class="field-hint" style="font-size:7pt;">Jabatan</span>
            </div>
            <div class="nama-ttd">
                <input type="text" class="field-inline" id="nama-ttd" value="<?= esc($penomoran['NAMA'] ?? '') ?>" style="text-align:center;width:100%;font-weight:700;text-decoration:underline;">
            </div>
            <div class="nip-ttd">NIP.&nbsp;<input type="text" class="field-inline" id="nip-ttd" placeholder="NIP" style="min-width:160px;text-align:center;"></div>
        </div>
    </div>

    <!-- TEMBUSAN -->
    <div style="margin-top:24px;font-size:11pt;">
        <div>Tembusan:</div>
        <textarea class="field-isi" id="tembusan" rows="2" placeholder="1. ...\n2. ... (Opsional, kosongkan jika tidak ada tembusan)" style="padding-left:1.2em;"></textarea>
        <div class="field-hint">Opsional — kosongkan jika tidak ada tembusan</div>
    </div>
</div>

<script>
function cetakDokumen() {
    const yth = document.getElementById('yth-nama').value.trim();
    const isi = document.getElementById('alinea-isi').value.trim();
    const jabTtd = document.getElementById('jabatan-ttd').value.trim();
    if (!yth) { alert('Harap isi nama penerima surat (Yth.).'); document.getElementById('yth-nama').focus(); return; }
    if (!isi) { alert('Harap isi konten surat dinas.'); document.getElementById('alinea-isi').focus(); return; }
    if (!jabTtd) { alert('Harap isi jabatan penandatangan.'); document.getElementById('jabatan-ttd').focus(); return; }
    window.print();
}
</script>
</body>
</html>
