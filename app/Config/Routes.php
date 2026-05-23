<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::login');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attempt');
$routes->get('logout', 'Auth::logout');
$routes->get('sso/callback', 'Auth::ssoCallback');
$routes->get('tidak-terdaftar', 'Auth::tidakTerdaftar'); // Halaman info jika pegawai belum didaftarkan

// Route khusus login lokal — bypass SSO, untuk keperluan admin/developer
// Sengaja dibuat terpisah agar tidak mengganggu alur login SSO utama
$routes->get('login/lokal', 'Auth::loginLokal');
$routes->post('login/lokal', 'Auth::attempt');

$routes->get('dashboard', 'Dashboard::index');
$routes->post('dashboard/filterPenomoran', 'Dashboard::filterPenomoran');
$routes->get('sapa-data', 'SapaData::index');

// Routes untuk Penomoran (Pegawai)
$routes->get('penomoran', 'Penomoran::index');
$routes->get('penomoran/create', 'Penomoran::create');
$routes->post('penomoran/store', 'Penomoran::store');
$routes->get('penomoran/edit/(:num)/(:any)', 'Penomoran::edit/$1/$2');
$routes->post('penomoran/update/(:num)/(:any)', 'Penomoran::update/$1/$2');
$routes->post('penomoran/delete/(:num)/(:any)/(:num)', 'Penomoran::delete/$1/$2/$3');
$routes->get('penomoran/detail/(:num)/(:any)/(:num)', 'Penomoran::detail/$1/$2/$3');
$routes->get('penomoran/cancel/(:num)/(:any)/(:num)', 'Penomoran::cancel/$1/$2/$3');
$routes->post('penomoran/cancel-submit', 'Penomoran::cancelSubmit');
$routes->get('penomoran/cetak/(:num)/(:any)/(:num)', 'Penomoran::cetak/$1/$2/$3');



// Routes AJAX (PenomoranAjax)
$routes->get('penomoran/getNextNoAjax', 'PenomoranAjax::getNextNoAjax');
$routes->post('penomoran/preview', 'PenomoranAjax::preview');
$routes->post('penomoran/finalize', 'PenomoranAjax::finalize');
$routes->get('penomoran/getKlasifikasiAjax', 'PenomoranAjax::getKlasifikasiAjax');

// ─────────────────────────────────────────────────────────
// Routes Admin — dilindungi AdminFilter via Filters.php
// ─────────────────────────────────────────────────────────
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {

    // Manage User
    $routes->get('users', 'UserController::index');
    $routes->get('users/create', 'UserController::create');
    $routes->get('users/searchPegawai', 'UserController::searchPegawai');
    $routes->post('users/store', 'UserController::store');
    $routes->get('users/edit/(:num)', 'UserController::edit/$1');
    $routes->post('users/update/(:num)', 'UserController::update/$1');
    $routes->post('users/toggle/(:num)', 'UserController::toggleStatus/$1');

    // Kelola Jenis Naskah
    $routes->get('jenis-naskah', 'JenisNaskahController::index');
    $routes->post('jenis-naskah/store', 'JenisNaskahController::store');
    $routes->post('jenis-naskah/update/(:num)', 'JenisNaskahController::update/$1');
    $routes->post('jenis-naskah/toggle/(:num)', 'JenisNaskahController::toggle/$1');

    // Kelola Klasifikasi Arsip
    $routes->get('klasifikasi-arsip', 'KlasifikasiArsipController::index');
    $routes->post('klasifikasi-arsip/store', 'KlasifikasiArsipController::store');
    $routes->post('klasifikasi-arsip/update/(:num)', 'KlasifikasiArsipController::update/$1');
    $routes->post('klasifikasi-arsip/toggle/(:num)', 'KlasifikasiArsipController::toggle/$1');

    // Konfigurasi Nomor Awal
    $routes->get('nomor-awal', 'NomorAwalController::index');
    $routes->post('nomor-awal/store', 'NomorAwalController::store');
    $routes->post('nomor-awal/delete/(:num)', 'NomorAwalController::delete/$1');

    // Kelola Unit Kerja
    $routes->get('unit-kerja', 'UnitKerjaController::index');
    $routes->post('unit-kerja/store', 'UnitKerjaController::store');
    $routes->post('unit-kerja/update/(:num)', 'UnitKerjaController::update/$1');
    $routes->post('unit-kerja/toggle/(:num)', 'UnitKerjaController::toggle/$1');

    // Persetujuan Akses Bypass (menggunakan Controller TU Persuratan)
    $routes->get('approval-akses', '\App\Controllers\TuPersuratan\ApprovalAksesController::index');
    $routes->post('approval-akses/approve/(:num)', '\App\Controllers\TuPersuratan\ApprovalAksesController::approve/$1');
    $routes->post('approval-akses/reject/(:num)', '\App\Controllers\TuPersuratan\ApprovalAksesController::reject/$1');
    $routes->post('approval-akses/revoke/(:num)', '\App\Controllers\TuPersuratan\ApprovalAksesController::revoke/$1');

    // Audit Log
    $routes->get('audit-logs', 'AuditLogController::index');

});

// ─────────────────────────────────────────────────────────
// Routes TU Unit — dilindungi TuUnitFilter via Filters.php
// ─────────────────────────────────────────────────────────
$routes->group('tu-unit', ['namespace' => 'App\Controllers\TuUnit'], function ($routes) {
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('rekap', 'RekapController::index');
    $routes->get('pengajuan-akses', 'PengajuanController::index');
    $routes->post('pengajuan-akses/store', 'PengajuanController::store');
    $routes->post('pengajuan-akses/revoke/(:num)', 'PengajuanController::revoke/$1');
    // Route untuk melihat file Nota Dinas (akses aman via controller)
    $routes->get('pengajuan-akses/nota-dinas/(:num)', 'PengajuanController::viewNotaDinas/$1');
});

// ─────────────────────────────────────────────────────────
// Routes TU Persuratan — dilindungi TuPersuratanFilter via Filters.php
// ─────────────────────────────────────────────────────────
$routes->group('tu-persuratan', ['namespace' => 'App\Controllers\TuPersuratan'], function ($routes) {
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('rekap', 'RekapController::index');
    $routes->get('approval-akses', 'ApprovalAksesController::index');
    $routes->post('approval-akses/approve/(:num)', 'ApprovalAksesController::approve/$1');
    $routes->post('approval-akses/reject/(:num)', 'ApprovalAksesController::reject/$1');
    $routes->post('approval-akses/revoke/(:num)', 'ApprovalAksesController::revoke/$1');
    // Route untuk melihat Nota Dinas saat proses approval
    $routes->get('approval-akses/nota-dinas/(:num)', 'ApprovalAksesController::viewNotaDinas/$1');
});
