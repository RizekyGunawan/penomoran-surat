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
      </div>

      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
      <?php endif; ?>

      <!-- Form Login LDAP — selalu ditampilkan di halaman ini -->
      <form method="post" action="/login/lokal" class="d-grid gap-3 pt-2">
          <?= csrf_field() ?>
          <div>
              <label for="username_ldap" class="form-label label text-muted" style="font-size: 0.85rem;">Username</label>
              <input
                type="text"
                class="form-control login-control"
                id="username_ldap"
                name="username_ldap"
                required
                placeholder="Masukkan username..."
                value="<?= esc(old('username_ldap')) ?>"
              >
          </div>
          <button type="submit" class="btn btn-dark w-100 fw-bold py-2 shadow-sm">Masuk</button>
      </form>

      <div class="text-center mt-4" style="font-size: 0.75rem;">
        <!-- Tombol kembali ke halaman login utama (SSO) -->
        <a href="/login" class="text-muted text-decoration-none" style="font-size: 0.8rem;">
          &larr; Kembali ke Login SSO
        </a>
      </div>

      <div class="text-center mt-3 text-muted" style="font-size: 0.75rem;">&copy; <?= date('Y') ?> - Penomoran Surat</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
