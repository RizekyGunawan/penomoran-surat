<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Akses Tidak Diizinkan - Penomoran Surat</title>
  <link rel="icon" type="image/png" href="/images/logo-pmk.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/css/penomoran-custom.css" rel="stylesheet">
  <style>
    /* ─────────────────────────────────────────────────────────────
     * Gaya khusus halaman "Tidak Terdaftar"
     * Menggunakan variabel dan pola yang sama dengan halaman login
     * ───────────────────────────────────────────────────────────── */

    .card-tidak-terdaftar {
      width: 100%;
      max-width: 480px;
      border-radius: 1rem;
      border: none;
      background: var(--card-bg, #fff);
      padding: 2.5rem 2rem;
    }

    /* Ikon lingkaran merah sebagai penanda peringatan */
    .ikon-peringatan {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background: rgba(220, 53, 69, 0.12);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.25rem;
    }

    .ikon-peringatan svg {
      width: 36px;
      height: 36px;
      color: #dc3545;
    }

    /* Nama pegawai yang ditampilkan */
    .nama-pegawai {
      font-weight: 700;
      color: var(--heading-color, #1a1a2e);
      word-break: break-word;
    }

    /* Kotak informasi langkah-langkah */
    .kotak-langkah {
      background: rgba(13, 110, 253, 0.06);
      border-left: 4px solid #0d6efd;
      border-radius: 0.5rem;
      padding: 1rem 1.25rem;
      text-align: left;
    }

    .kotak-langkah ol {
      margin-bottom: 0;
      padding-left: 1.25rem;
      font-size: 0.875rem;
    }

    .kotak-langkah ol li {
      margin-bottom: 0.35rem;
      color: var(--muted-color, #6c757d);
      line-height: 1.5;
    }

    .kotak-langkah ol li:last-child {
      margin-bottom: 0;
    }

    .judul-langkah {
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #0d6efd;
      margin-bottom: 0.5rem;
    }
  </style>
</head>
<body class="login-bg">

  <!-- Dekorasi latar belakang (sama seperti halaman login) -->
  <div class="shape shape-1"></div>
  <div class="shape shape-2"></div>

  <div class="card card-tidak-terdaftar shadow-lg mx-3">

    <!-- Ikon peringatan -->
    <div class="ikon-peringatan">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
      </svg>
    </div>

    <!-- Judul -->
    <div class="text-center mb-2">
      <h5 class="fw-bold text-danger mb-1" style="font-size: 1.25rem;">Akses Tidak Diizinkan</h5>
    </div>

    <!-- Pesan utama -->
    <?php if (!empty($namaPegawai)): ?>
      <p class="text-center mb-3" style="font-size: 0.92rem; color: var(--muted-color, #6c757d);">
        Halo, <span class="nama-pegawai"><?= $namaPegawai ?></span>.<br>
        Aplikasi Penomoran Surat ini khusus ditujukan untuk <strong>Pengelola Tata Usaha</strong>.
      </p>
    <?php else: ?>
      <p class="text-center mb-3" style="font-size: 0.92rem; color: var(--muted-color, #6c757d);">
        Aplikasi Penomoran Surat ini khusus ditujukan untuk <strong>Pengelola Tata Usaha</strong>.
      </p>
    <?php endif; ?>

    <!-- Tombol kembali ke halaman login (memicu logout SSO & lokal) -->
    <a href="/logout" class="btn btn-primary w-100">
      ← Kembali ke Halaman Masuk
    </a>

    <div class="text-center mt-3 muted small">&copy; <?= date('Y') ?> - Penomoran Surat.</div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
