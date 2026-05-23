<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara — <?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/cetak-surat.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div id="toolbar-cetak">
    <div>
        <div class="toolbar-title">📄 Berita Acara</div>
        <div class="toolbar-info"><?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?></div>
    </div>
    <div class="toolbar-actions">
        <button class="btn-kembali" onclick="window.close()"><i class="fas fa-arrow-left"></i> Kembali</button>
        <button class="btn-cetak" onclick="cetakDokumen()"><i class="fas fa-print"></i> Cetak / Simpan PDF</button>
    </div>
</div>

<div class="halaman-surat">
    <div class="kop-naskah-dinas">
        <img src="<?= esc($logoUrl) ?>" alt="Logo Kemenko PMK">
        <div class="kop-teks">
            <div class="instansi-induk">Kementerian Koordinator Bidang</div>
            <div class="instansi-utama">Pembangunan Manusia dan Kebudayaan</div>
            <div class="instansi-sub">Republik Indonesia</div>
            <div class="kop-alamat">Jl. Medan Merdeka Barat No. 3, Jakarta Pusat 10110 | Telp. (021) 345-2165 | www.kemenkopmk.go.id</div>
        </div>
    </div>

    <div class="judul-surat">
        <h2>Berita Acara</h2>
        <div class="nomor-surat">NOMOR <?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?></div>
    </div>

    <div style="text-align:center;font-size:11pt;margin-bottom:16px;">
        <strong>TENTANG</strong><br>
        <input type="text" class="field-inline" id="tentang" value="<?= esc($penomoran['PERIHAL'] ?? '') ?>"
            placeholder="Perihal Berita Acara" style="text-align:center;width:90%;font-weight:700;font-size:12pt;">
    </div>

    <div class="garis-header"></div>

    <!-- PADA HARI INI -->
    <p style="font-size:11pt;margin-bottom:16px;text-align:justify;">
        Pada hari ini,&nbsp;
        <input type="text" class="field-inline" id="hari" placeholder="Senin" style="min-width:80px;">,
        tanggal&nbsp;
        <input type="text" class="field-inline" id="tgl-ba"
            value="<?= $penomoran['TANGGAL'] ? date('d F Y', strtotime($penomoran['TANGGAL'])) : '' ?>"
            placeholder="28 April 2026" style="min-width:130px;">,
        yang bertanda tangan di bawah ini:
    </p>

    <!-- PIHAK-PIHAK -->
    <div id="pihak-container">
        <div class="pihak-block" style="border:1px dashed #ccc;border-radius:8px;padding:12px;margin-bottom:12px;">
            <p style="font-size:11pt;font-weight:700;margin-bottom:8px;">Pihak I</p>
            <table class="tabel-konsideran">
                <tr><td class="konsideran-label">Nama</td><td class="konsideran-sep">:</td><td><input type="text" class="field-inline" placeholder="Nama" style="width:85%;"></td></tr>
                <tr><td class="konsideran-label">NIP</td><td class="konsideran-sep">:</td><td><input type="text" class="field-inline" placeholder="NIP" style="width:85%;"></td></tr>
                <tr><td class="konsideran-label">Jabatan</td><td class="konsideran-sep">:</td><td><input type="text" class="field-inline" placeholder="Jabatan" style="width:85%;"></td></tr>
            </table>
        </div>
        <div class="pihak-block" style="border:1px dashed #ccc;border-radius:8px;padding:12px;margin-bottom:12px;">
            <p style="font-size:11pt;font-weight:700;margin-bottom:8px;">Pihak II</p>
            <table class="tabel-konsideran">
                <tr><td class="konsideran-label">Nama</td><td class="konsideran-sep">:</td><td><input type="text" class="field-inline" placeholder="Nama" style="width:85%;"></td></tr>
                <tr><td class="konsideran-label">NIP</td><td class="konsideran-sep">:</td><td><input type="text" class="field-inline" placeholder="NIP" style="width:85%;"></td></tr>
                <tr><td class="konsideran-label">Jabatan</td><td class="konsideran-sep">:</td><td><input type="text" class="field-inline" placeholder="Jabatan" style="width:85%;"></td></tr>
            </table>
        </div>
    </div>

    <p style="font-size:11pt;margin:8px 0;">Telah melaksanakan/menyepakati hal-hal sebagai berikut:</p>
    <textarea class="field-isi" id="isi-ba" rows="8"
        placeholder="Uraian isi berita acara / hal-hal yang disepakati..."></textarea>

    <p style="font-size:11pt;margin:16px 0 8px;">
        Demikian berita acara ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.
    </p>

    <!-- TANDA TANGAN MULTI-PIHAK -->
    <div style="display:flex;justify-content:space-around;margin-top:20px;gap:20px;">
        <div style="text-align:center;flex:1;">
            <div style="font-size:11pt;margin-bottom:4px;font-weight:700;">Pihak I,</div>
            <div style="height:70px;"></div>
            <div><input type="text" class="field-inline" placeholder="Nama Pihak I" style="text-align:center;width:90%;font-weight:700;text-decoration:underline;"></div>
            <div style="font-size:11pt;">NIP.&nbsp;<input type="text" class="field-inline" placeholder="NIP" style="min-width:120px;text-align:center;"></div>
        </div>
        <div style="text-align:center;flex:1;">
            <div style="font-size:11pt;margin-bottom:4px;font-weight:700;">Pihak II,</div>
            <div style="height:70px;"></div>
            <div><input type="text" class="field-inline" placeholder="Nama Pihak II" style="text-align:center;width:90%;font-weight:700;text-decoration:underline;"></div>
            <div style="font-size:11pt;">NIP.&nbsp;<input type="text" class="field-inline" placeholder="NIP" style="min-width:120px;text-align:center;"></div>
        </div>
    </div>

    <!-- MENGETAHUI -->
    <div style="margin-top:24px;text-align:center;">
        <div style="font-size:11pt;font-weight:700;margin-bottom:4px;">Mengetahui,</div>
        <div style="font-size:11pt;">
            <input type="text" class="field-inline" id="mengetahui-jabatan" placeholder="Jabatan pejabat mengetahui" style="text-align:center;width:300px;font-weight:700;">
        </div>
        <div style="height:70px;"></div>
        <div><input type="text" class="field-inline" id="mengetahui-nama" value="<?= esc($penomoran['NAMA'] ?? '') ?>" style="text-align:center;width:300px;font-weight:700;text-decoration:underline;"></div>
        <div style="font-size:11pt;">NIP.&nbsp;<input type="text" class="field-inline" placeholder="NIP" style="min-width:160px;text-align:center;"></div>
    </div>
</div>

<script>
function cetakDokumen() {
    const tentang = document.getElementById('tentang').value.trim();
    const isi = document.getElementById('isi-ba').value.trim();
    if (!tentang) { alert('Harap isi perihal berita acara.'); document.getElementById('tentang').focus(); return; }
    if (!isi) { alert('Harap isi uraian berita acara.'); document.getElementById('isi-ba').focus(); return; }
    window.print();
}
</script>
</body>
</html>
