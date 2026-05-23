<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Kuasa — <?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/cetak-surat.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div id="toolbar-cetak">
    <div>
        <div class="toolbar-title">📄 Surat Kuasa</div>
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
        <h2>Surat Kuasa</h2>
        <div class="nomor-surat">NOMOR <?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?></div>
    </div>

    <div class="garis-header"></div>

    <p style="font-size:11pt;margin-bottom:16px;">Yang bertanda tangan di bawah ini:</p>

    <!-- PEMBERI KUASA -->
    <table class="tabel-konsideran" style="margin-bottom:16px;">
        <tr><td colspan="3" style="font-weight:700;padding-bottom:4px;">Pemberi Kuasa</td></tr>
        <tr>
            <td class="konsideran-label">Nama</td>
            <td class="konsideran-sep">:</td>
            <td><input type="text" class="field-inline" id="pemberi-nama" value="<?= esc($penomoran['NAMA'] ?? '') ?>" style="width:85%;"></td>
        </tr>
        <tr>
            <td class="konsideran-label">NIP</td>
            <td class="konsideran-sep">:</td>
            <td><input type="text" class="field-inline" id="pemberi-nip" placeholder="NIP" style="width:85%;"></td>
        </tr>
        <tr>
            <td class="konsideran-label">Jabatan</td>
            <td class="konsideran-sep">:</td>
            <td><input type="text" class="field-inline" id="pemberi-jabatan" placeholder="Jabatan pemberi kuasa" style="width:85%;"></td>
        </tr>
        <tr>
            <td class="konsideran-label">Unit Kerja</td>
            <td class="konsideran-sep">:</td>
            <td><?= esc($penomoran['UNIT_KERJA'] ?? '-') ?></td>
        </tr>
    </table>

    <p style="font-size:11pt;margin-bottom:12px;">memberikan kuasa kepada:</p>

    <!-- PENERIMA KUASA -->
    <table class="tabel-konsideran" style="margin-bottom:20px;">
        <tr><td colspan="3" style="font-weight:700;padding-bottom:4px;">Penerima Kuasa</td></tr>
        <tr>
            <td class="konsideran-label">Nama</td>
            <td class="konsideran-sep">:</td>
            <td><input type="text" class="field-inline" id="penerima-nama" placeholder="Nama penerima kuasa" style="width:85%;"></td>
        </tr>
        <tr>
            <td class="konsideran-label">NIP</td>
            <td class="konsideran-sep">:</td>
            <td><input type="text" class="field-inline" id="penerima-nip" placeholder="NIP" style="width:85%;"></td>
        </tr>
        <tr>
            <td class="konsideran-label">Jabatan</td>
            <td class="konsideran-sep">:</td>
            <td><input type="text" class="field-inline" id="penerima-jabatan" placeholder="Jabatan penerima kuasa" style="width:85%;"></td>
        </tr>
    </table>

    <p style="font-size:11pt;">Untuk:</p>
    <textarea class="field-isi" id="untuk" rows="4" style="margin:8px 0 16px;"
        placeholder="Uraian hal/pekerjaan yang dikuasakan, contoh: Mewakili... dalam rangka..."></textarea>

    <p style="font-size:11pt;">
        Jakarta, <?= $penomoran['TANGGAL'] ? date('d F Y', strtotime($penomoran['TANGGAL'])) : '-' ?>
    </p>

    <!-- TANDA TANGAN DUA PIHAK -->
    <div style="display:flex;justify-content:space-between;margin-top:20px;">
        <div class="blok-ttd" style="text-align:center;min-width:200px;">
            <div style="font-size:11pt;margin-bottom:4px;">Penerima Kuasa,</div>
            <div style="height:70px;"></div>
            <div style="font-weight:700;text-decoration:underline;" id="penerima-nama-ttd">
                <input type="text" class="field-inline" placeholder="Nama penerima kuasa" style="text-align:center;width:180px;font-weight:700;text-decoration:underline;">
            </div>
            <div style="font-size:11pt;">NIP.&nbsp;<input type="text" class="field-inline" placeholder="NIP" style="min-width:130px;text-align:center;"></div>
        </div>
        <div class="blok-ttd" style="text-align:center;min-width:200px;">
            <div style="font-size:11pt;margin-bottom:4px;">Pemberi Kuasa,</div>
            <div style="height:70px;"></div>
            <div style="font-weight:700;text-decoration:underline;">
                <input type="text" class="field-inline" id="pemberi-nama-ttd" value="<?= esc($penomoran['NAMA'] ?? '') ?>" style="text-align:center;width:180px;font-weight:700;text-decoration:underline;">
            </div>
            <div style="font-size:11pt;">NIP.&nbsp;<input type="text" class="field-inline" id="pemberi-nip-ttd" placeholder="NIP" style="min-width:130px;text-align:center;"></div>
        </div>
    </div>
</div>

<script>
function cetakDokumen() {
    const penerimaNama = document.getElementById('penerima-nama').value.trim();
    const untuk = document.getElementById('untuk').value.trim();
    if (!penerimaNama) { alert('Harap isi nama penerima kuasa.'); document.getElementById('penerima-nama').focus(); return; }
    if (!untuk) { alert('Harap isi uraian hal yang dikuasakan.'); document.getElementById('untuk').focus(); return; }
    window.print();
}
</script>
</body>
</html>
