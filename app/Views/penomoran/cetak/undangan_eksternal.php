<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Undangan — <?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/cetak-surat.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div id="toolbar-cetak">
    <div>
        <div class="toolbar-title">📄 Undangan Eksternal</div>
        <div class="toolbar-info"><?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?> · <?= esc($penomoran['UNIT_KERJA'] ?? '-') ?></div>
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

    <div style="text-align:right;margin-top:16px;font-size:11pt;">
        Jakarta, <?= $penomoran['TANGGAL'] ? date('d F Y', strtotime($penomoran['TANGGAL'])) : '-' ?>
    </div>

    <table style="width:55%;border-collapse:collapse;margin-top:10px;font-size:11pt;">
        <tr>
            <td style="width:90px;">Nomor</td>
            <td style="width:12px;text-align:center;">:</td>
            <td><strong><?= esc($penomoran['NOMOR_SURAT_LENGKAP'] ?? $penomoran['NO']) ?></strong></td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td style="text-align:center;">:</td>
            <td><input type="text" class="field-inline" id="lampiran" placeholder="— (opsional)" style="min-width:180px;"></td>
        </tr>
        <tr>
            <td>Hal</td>
            <td style="text-align:center;">:</td>
            <td><strong><?= esc($penomoran['PERIHAL'] ?? '-') ?></strong></td>
        </tr>
    </table>

    <div style="margin-top:20px;font-size:11pt;">
        <div>Yth.</div>
        <div style="padding-left:1.2em;">
            <input type="text" class="field-inline" id="yth-nama" placeholder="Nama / Jabatan undangan" style="width:70%;display:block;margin-bottom:4px;">
            <div class="field-hint">Nama atau jabatan penerima undangan</div>
            <input type="text" class="field-inline" id="yth-tempat" placeholder="Instansi / Kota" style="width:70%;display:block;margin-top:4px;">
        </div>
    </div>

    <div class="garis-header" style="margin-top:20px;"></div>

    <div class="isi-surat-wrapper">
        <p class="isi-paragraf">
            <textarea class="field-isi" id="alinea-pembuka" rows="2"
                placeholder="Dengan hormat, dalam rangka..."></textarea>
        </p>
        <p class="isi-paragraf">
            <textarea class="field-isi" id="alinea-isi" rows="3"
                placeholder="Isi undangan — maksud dan tujuan acara..."></textarea>
        </p>
    </div>

    <table class="tabel-konsideran" style="margin:12px 0 20px;">
        <tr>
            <td class="konsideran-label">Hari / Tanggal</td>
            <td class="konsideran-sep">:</td>
            <td><input type="text" class="field-inline" id="hari-tanggal" placeholder="Senin, 28 April 2026" style="width:85%;"></td>
        </tr>
        <tr>
            <td class="konsideran-label">Pukul</td>
            <td class="konsideran-sep">:</td>
            <td><input type="text" class="field-inline" id="pukul" placeholder="09.00 WIB s.d. selesai" style="width:85%;"></td>
        </tr>
        <tr>
            <td class="konsideran-label">Tempat</td>
            <td class="konsideran-sep">:</td>
            <td><input type="text" class="field-inline" id="tempat-acara" placeholder="Nama ruang / gedung / alamat" style="width:85%;"></td>
        </tr>
        <tr>
            <td class="konsideran-label">Agenda</td>
            <td class="konsideran-sep">:</td>
            <td><input type="text" class="field-inline" id="agenda" placeholder="Agenda/topik rapat" style="width:85%;"></td>
        </tr>
    </table>

    <p class="isi-paragraf">
        <textarea class="field-isi" id="alinea-penutup" rows="2"
            placeholder="Demikian undangan ini disampaikan, atas kehadiran Bapak/Ibu diucapkan terima kasih."></textarea>
    </p>

    <div class="kaki-surat">
        <div class="blok-ttd">
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
</div>

<script>
function cetakDokumen() {
    const yth = document.getElementById('yth-nama').value.trim();
    const hari = document.getElementById('hari-tanggal').value.trim();
    const jabTtd = document.getElementById('jabatan-ttd').value.trim();
    if (!yth) { alert('Harap isi nama/jabatan penerima undangan.'); document.getElementById('yth-nama').focus(); return; }
    if (!hari) { alert('Harap isi hari/tanggal acara.'); document.getElementById('hari-tanggal').focus(); return; }
    if (!jabTtd) { alert('Harap isi jabatan penandatangan.'); document.getElementById('jabatan-ttd').focus(); return; }
    window.print();
}
</script>
</body>
</html>
