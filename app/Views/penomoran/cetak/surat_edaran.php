<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Edaran — <?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/cetak-surat.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div id="toolbar-cetak">
    <div>
        <div class="toolbar-title">📄 Surat Edaran</div>
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

    <!-- JUDUL -->
    <div class="judul-surat">
        <h2>Surat Edaran</h2>
        <div class="nomor-surat">NOMOR <?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?></div>
    </div>

    <!-- TENTANG -->
    <div style="text-align:center;font-size:11pt;margin-bottom:20px;">
        <strong>TENTANG</strong><br>
        <input type="text" class="field-inline" id="tentang"
            value="<?= esc($penomoran['PERIHAL'] ?? '') ?>"
            placeholder="Judul/Perihal Surat Edaran"
            style="text-align:center;width:90%;font-weight:700;font-size:12pt;">
    </div>

    <div class="garis-header"></div>

    <!-- KEPADA -->
    <table style="width:100%;border-collapse:collapse;font-size:11pt;margin-bottom:16px;">
        <tr>
            <td style="width:60px;">Yth.</td>
            <td style="width:12px;text-align:center;">:</td>
            <td>
                <textarea class="field-isi" id="yth" rows="3"
                    placeholder="1. Seluruh Pejabat Eselon I...\n2. Kepala Biro...\n(tulis satu per baris)"></textarea>
                <div class="field-hint">Daftar penerima surat edaran</div>
            </td>
        </tr>
    </table>

    <!-- ISI -->
    <div class="isi-surat-wrapper">
        <div class="isi-label">💡 Isi pemberitahuan / ketentuan dalam surat edaran:</div>
        <p class="isi-paragraf">
            <textarea class="field-isi" id="alinea-pembuka" rows="2" placeholder="Alinea pembuka, contoh: Dalam rangka..."></textarea>
            <span class="field-hint">Alinea pembuka</span>
        </p>
        <p class="isi-paragraf">
            <textarea class="field-isi" id="alinea-isi" rows="8" placeholder="Isi/ketentuan surat edaran..."></textarea>
            <span class="field-hint">Isi pemberitahuan / ketentuan</span>
        </p>
        <p class="isi-paragraf">
            <textarea class="field-isi" id="alinea-penutup" rows="2" placeholder="Alinea penutup..."></textarea>
            <span class="field-hint">Alinea penutup</span>
        </p>
    </div>

    <!-- TEMPAT DAN TANGGAL + TTD -->
    <div class="kaki-surat">
        <div class="blok-ttd">
            <div class="tempat-tanggal">
                Jakarta, <?= $penomoran['TANGGAL'] ? date('d F Y', strtotime($penomoran['TANGGAL'])) : '-' ?>
            </div>
            <div class="jabatan-ttd">
                <input type="text" class="field-inline" id="jabatan-ttd" placeholder="Jabatan (misal: Menteri Koordinator)" style="text-align:center;width:100%;font-weight:700;">
                <span class="field-hint" style="font-size:7pt;">Jabatan</span>
            </div>
            <div class="nama-ttd">
                <input type="text" class="field-inline" id="nama-ttd" value="<?= esc($penomoran['NAMA'] ?? '') ?>" style="text-align:center;width:100%;font-weight:700;text-decoration:underline;">
            </div>
            <div class="nip-ttd">NIP.&nbsp;<input type="text" class="field-inline" id="nip-ttd" placeholder="NIP" style="min-width:160px;text-align:center;"></div>
        </div>
    </div>
</div>

<script>
function cetakDokumen() {
    const tentang = document.getElementById('tentang').value.trim();
    const yth = document.getElementById('yth').value.trim();
    const isi = document.getElementById('alinea-isi').value.trim();
    const jabTtd = document.getElementById('jabatan-ttd').value.trim();
    if (!tentang) { alert('Harap isi perihal/judul surat edaran.'); document.getElementById('tentang').focus(); return; }
    if (!yth) { alert('Harap isi daftar penerima surat edaran (Yth.).'); document.getElementById('yth').focus(); return; }
    if (!isi) { alert('Harap isi konten surat edaran.'); document.getElementById('alinea-isi').focus(); return; }
    if (!jabTtd) { alert('Harap isi jabatan penandatangan.'); document.getElementById('jabatan-ttd').focus(); return; }
    window.print();
}
</script>
</body>
</html>
