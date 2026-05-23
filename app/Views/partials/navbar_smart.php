<nav class="smart-navbar navbar navbar-expand-lg bg-white shadow-sm" style="padding-top: 0.5rem; padding-bottom: 0.5rem;">
    <div class="container-fluid px-lg-5">
        
        <!-- Left: Logo -->
        <a href="/" class="navbar-brand d-flex align-items-center text-decoration-none">
            <img src="/images/logo-pmk.png" alt="Logo PMK" width="40" height="40" class="img-fluid me-2">
            <div class="logo-text d-flex flex-column justify-content-center">
                <span class="fw-bold" style="color: #1e3a5f; font-size: 1.1rem; line-height: 1.2;">Penomoran Surat</span>
                <span class="text-muted" style="font-size: 0.75rem; letter-spacing: 0.05em; line-height: 1.2; font-weight: 600;">KEMENKO PMK</span>
            </div>
        </a>

        <!-- Hamburger Toggler untuk Mobile -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSmartCollapse" aria-controls="navbarSmartCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars" style="color: #1e3a5f;"></i>
        </button>

        <!-- Navbar Content (Collapse) -->
        <div class="collapse navbar-collapse" id="navbarSmartCollapse">
            
            <!-- Center: Navigation Links -->
            <div class="navbar-nav me-auto mb-2 mb-lg-0 d-flex flex-column flex-lg-row align-items-lg-center">
                
                <a href="/penomoran" class="nav-link-smart px-lg-3 <?= uri_string() == 'penomoran' ? 'active' : '' ?>">Penomoran Surat</a>

                <!-- Dropdown Admin — hanya tampil jika role_id = 1 -->
                <?php if ((int) session('user.role_id') === 1): ?>
                <div class="nav-item-smart dropdown">
                    <a href="#" class="nav-link-smart dropdown-toggle px-lg-3" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Admin
                    </a>
                    <ul class="dropdown-menu border-0 shadow-sm mt-2" aria-labelledby="adminDropdown">
                        <li><h6 class="dropdown-header text-uppercase" style="font-size:.65rem;letter-spacing:.06em;">Penomoran</h6></li>
                        <li><a class="dropdown-item" href="/tu-persuratan/dashboard"><i class="fas fa-chart-pie me-2 text-muted"></i>Dashboard Surat</a></li>
                        <li><a class="dropdown-item" href="/tu-persuratan/rekap"><i class="fas fa-list-alt me-2 text-muted"></i>Rekap Semua Surat</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header text-uppercase" style="font-size:.65rem;letter-spacing:.06em;">Manajemen</h6></li>
                        <li><a class="dropdown-item" href="/admin/users"><i class="fas fa-users me-2 text-muted"></i>Manage User</a></li>
                        <li><a class="dropdown-item" href="/admin/approval-akses"><i class="fas fa-user-check me-2 text-muted"></i>Approval TU Unit</a></li>
                        <li><a class="dropdown-item" href="/admin/nomor-awal"><i class="fas fa-cog me-2 text-muted"></i>Set Nomor Awal</a></li>
                        <li><a class="dropdown-item" href="/admin/jenis-naskah"><i class="fas fa-file-signature me-2 text-muted"></i>Kelola Jenis Naskah</a></li>
                        <li><a class="dropdown-item" href="/admin/klasifikasi-arsip"><i class="fas fa-archive me-2 text-muted"></i>Kelola Kode Klasifikasi Arsip</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/admin/audit-logs"><i class="fas fa-history me-2 text-muted"></i>Riwayat Audit</a></li>
                    </ul>
                </div>
                <?php endif ?>

                <!-- Menu TU Unit — hanya tampil jika role_id = 3 -->
                <?php if ((int) session('user.role_id') === 3): ?>
                <div class="nav-item-smart dropdown">
                    <a href="#" class="nav-link-smart dropdown-toggle px-lg-3" id="tuUnitDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        TU Unit
                    </a>
                    <ul class="dropdown-menu border-0 shadow-sm mt-2" aria-labelledby="tuUnitDropdown">
                        <li><a class="dropdown-item" href="/tu-unit/dashboard"><i class="fas fa-chart-bar me-2 text-muted"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="/tu-unit/rekap"><i class="fas fa-list me-2 text-muted"></i>Rekap Surat Unit</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header text-uppercase" style="font-size:.65rem;letter-spacing:.06em;">Manajemen Tim</h6></li>
                        <li><a class="dropdown-item" href="/tu-unit/pengajuan-akses"><i class="fas fa-user-plus me-2 text-muted"></i>Kelola Akses Tim</a></li>
                    </ul>
                </div>
                <?php endif ?>

                <!-- Menu Persuratan — hanya tampil jika role_id = 4 -->
                <?php if ((int) session('user.role_id') === 4): ?>
                <div class="nav-item-smart dropdown">
                    <a href="<?= base_url('tu-persuratan/dashboard') ?>" class="nav-link-smart dropdown-toggle px-lg-3 <?= uri_string() == 'tu-persuratan/dashboard' ? 'active' : '' ?>" id="tuPersuratanDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        TU Persuratan
                    </a>
                    <ul class="dropdown-menu border-0 shadow-sm mt-2" aria-labelledby="tuPersuratanDropdown">
                        <li><a class="dropdown-item" href="/tu-persuratan/dashboard"><i class="fas fa-chart-pie me-2 text-muted"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="/tu-persuratan/rekap"><i class="fas fa-table me-2 text-muted"></i>Rekap Semua Unit</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header text-uppercase" style="font-size:.65rem;letter-spacing:.06em;">Persetujuan</h6></li>
                        <li><a class="dropdown-item" href="/tu-persuratan/approval-akses"><i class="fas fa-user-check me-2 text-muted"></i>Approval TU Unit</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header text-uppercase" style="font-size:.65rem;letter-spacing:.06em;">Manajemen</h6></li>
                        <li><a class="dropdown-item" href="/admin/nomor-awal"><i class="fas fa-cog me-2 text-muted"></i>Set Nomor Awal</a></li>
                        <li><a class="dropdown-item" href="/admin/jenis-naskah"><i class="fas fa-file-signature me-2 text-muted"></i>Kelola Jenis Naskah</a></li>
                        <li><a class="dropdown-item" href="/admin/klasifikasi-arsip"><i class="fas fa-archive me-2 text-muted"></i>Kelola Kode Klasifikasi Arsip</a></li>
                    </ul>
                </div>
                <?php endif ?>
            </div>

            <!-- Right: User Actions -->
            <div class="navbar-actions d-flex align-items-center mt-3 mt-lg-0 pb-3 pb-lg-0">
                <!-- User Profile -->
                <div class="user-profile dropdown w-100">
                    <a href="#" class="d-flex align-items-center text-decoration-none" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php
                            // Ambil foto profil user dari session, gunakan avatar default jika kosong
                            $fotoProfil = session('user.foto') ? base_url('uploads/foto/' . session('user.foto')) : base_url('img/avatar-default.png');
                        ?>
                        <img src="<?= $fotoProfil ?>"
                             alt="Foto Profil <?= esc(session('user.name') ?? 'User') ?>"
                             class="avatar-img"
                             onerror="this.onerror=null; this.src='<?= base_url('img/avatar-default.png') ?>'">
                        <span class="d-lg-none ms-3 text-dark fw-medium"><?= esc(session('user.name') ?? 'User') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2" aria-labelledby="userDropdown">
                        <li><h6 class="dropdown-header">Halo, <?= esc(session('user.name') ?? 'User') ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/logout">Keluar</a></li>
                    </ul>
                </div>
            </div>
            
        </div>
    </div>
</nav>
