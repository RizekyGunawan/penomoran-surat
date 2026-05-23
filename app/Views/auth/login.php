<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Penomoran Surat</title>
    <link rel="icon" type="image/png" href="/images/logo-pmk.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/penomoran-custom.css" rel="stylesheet">
  </head>
  <body class="login-bg">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="card card-login shadow-lg p-4 mx-3">
      <div class="mb-3 text-center">
        <div class="title h5 mb-1">Login</div>
        <div class="muted small"></div>
      </div>

      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
      <?php endif; ?>

      <!-- Konten Login SSO — tampilan utama yang dilihat semua pengguna -->
      <div class="text-center py-3">
          <div class="mb-3">
              <img src="/images/logo-smart.png" alt="SSO Logo" width="60" onerror="this.src='https://ui-avatars.com/api/?name=SSO&background=0D8ABC&color=fff&rounded=true'">
          </div>
          <p class="text-muted small mb-4">Gunakan akun terpusat Kementerian Koordinator Bidang Pembangunan Manusia dan Kebudayaan.</p>
          <!-- Tombol ini akan redirect ke Keycloak jika SSO dikonfigurasi di server -->
          <a href="/login" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">Masuk via SSO Kemenko</a>
      </div>

      <div class="text-center mt-4 text-muted" style="font-size: 0.75rem;">&copy; <?= date('Y') ?> - Penomoran Surat PMK</div>
    </div>

    <!-- Tombol tersembunyi di pojok kanan bawah — mengarah ke halaman login lokal -->
    <!-- Sengaja dibuat sangat samar agar tidak mencolok bagi pengguna biasa -->
    <a
      href="/login/lokal"
      id="tautan-login-lokal"
      title="Login Lokal"
      style="
        position: fixed;
        bottom: 16px;
        right: 16px;
        font-size: 0.72rem;
        color: rgba(255, 255, 255, 0.25);
        text-decoration: none;
        padding: 4px 8px;
        transition: color 0.3s ease;
        letter-spacing: 0.03em;
        z-index: 9999;
      "
      onmouseover="this.style.color='rgba(255,255,255,0.7)'"
      onmouseout="this.style.color='rgba(255,255,255,0.25)'"
    >
      Login Lokal
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
