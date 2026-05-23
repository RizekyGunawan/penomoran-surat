# Panduan Sistem Penomoran Dokumen

## Deskripsi

Sistem ini dirancang untuk mengelola penomoran surat dinas secara otomatis, terstruktur, dan akuntabel sesuai regulasi **TND Permenko No. 1 Tahun 2024**.

Fitur utama:
- **Penomoran Otomatis**: Nomor urut naik otomatis per jenis dokumen, per tahun
- **Multi-Jenis Dokumen**: 9 jenis dokumen aktif didukung
- **Kontrol Akses Berbasis Peran**: 4 peran pengguna (Admin, Pegawai, TU Unit, Persuratan)
- **Audit Log Otomatis**: Semua aksi tercatat (BUAT, UBAH, HAPUS, CANCEL)
- **Validasi Akses**: Hanya pegawai yang didaftarkan Admin yang dapat masuk
- **Integrasi SSO**: Login melalui SSO Kemenko PMK (di lingkungan production)
- **Cetak Surat**: Template cetak untuk beberapa jenis dokumen

---

## Struktur Database

Aplikasi menggunakan **2 database terpisah**:

### Database 1: `penomoran_db` (Database Utama Aplikasi)

#### Tabel `penomoran`

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| **NO** | INT (PK) | Nomor urut per jenis dokumen per tahun |
| **JENIS_DOKUMEN** | VARCHAR(100) (PK) | Jenis dokumen surat |
| **TAHUN** | INT(4) (PK) | Tahun surat diterbitkan |
| NOMOR_SURAT_LENGKAP | VARCHAR(255) | Format nomor surat lengkap |
| TANGGAL | DATE | Tanggal surat |
| UNIT_KERJA | VARCHAR(150) | Nama unit kerja asal surat |
| unit_kerja_id | INT | ID relasi ke tabel unit_kerja di kemenkopmk_db |
| PERIHAL | TEXT | Perihal surat |
| NAMA | VARCHAR(150) | Nama pengaju |
| USER_ID | INT | ID user yang membuat (relasi ke tabel users) |
| STATUS | VARCHAR(20) | Status surat: `AKTIF` atau `DIBATALKAN` |
| ALASAN_PEMBATALAN | TEXT | Diisi jika STATUS = DIBATALKAN |
| TANGGAL_PEMBATALAN | DATETIME | Waktu pembatalan |
| DIBATALKAN_OLEH | INT | USER_ID yang membatalkan |
| KODE_KLASIFIKASI | VARCHAR(50) | Kode arsip (TND Permenko 2024) |
| SIFAT_SURAT | ENUM | SR / R / T / B |
| KODE_JABATAN | VARCHAR(50) | Kode jabatan penandatangan |
| CREATED_AT | DATETIME | Waktu pembuatan record |
| UPDATED_AT | DATETIME | Waktu pembaruan record |

**Primary Key**: Composite Key (`NO`, `JENIS_DOKUMEN`, `TAHUN`)

#### Tabel `users`

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | INT (PK) | ID unik lokal |
| username_ldap | VARCHAR(100) | Username login (unik) |
| pegawai_id | VARCHAR(100) | Referensi ke pegawai di kemenkopmk_db |
| unit_kerja_id | INT | ID unit kerja (wajib untuk role TU Unit) |
| role_id | INT | Peran: 1=Admin, 2=Pegawai, 3=TU Unit, 4=Persuratan |
| is_active | TINYINT | 1=aktif, 0=nonaktif |
| deleted_at | DATETIME | Soft delete |

#### Tabel `klasifikasi_arsip`

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | INT (PK) | ID unik |
| kode | VARCHAR(20) | Kode klasifikasi (contoh: PR.01) |
| kode_induk | VARCHAR(20) | Kode induk klasifikasi |
| uraian | TEXT | Deskripsi klasifikasi |
| fungsi | ENUM | `fasilitatif` atau `substantif` |
| is_active | TINYINT | 1=aktif, 0=nonaktif |

#### Tabel `nomor_awal_config`

Menyimpan konfigurasi nomor urut awal per jenis dokumen per tahun. Berguna jika penomoran tidak dimulai dari 1 (misal karena migrasi dari sistem lama).

#### Tabel `audit_logs`

Mencatat semua aksi penting: `BUAT`, `UBAH`, `HAPUS`, `CANCEL`. Terisi otomatis via Model Callbacks.

---

### Database 2: `kemenkopmk_db` (Database Referensi / Master)

Hanya **dibaca**, tidak ditulis oleh aplikasi penomoran (kecuali `unit_kerja` via `UnitKerjaModel`).

| Tabel | Dipakai Untuk |
|-------|--------------|
| `users` | Validasi login, pencarian pegawai di admin panel |
| `pegawai` | Data lengkap pegawai (nama, NIP, jabatan, foto) |
| `unit_kerja` | Hierarki organisasi (Kemenko → Biro/Deputi → Bagian) |
| `jabatan` | Master jabatan (Struktural/Fungsional/Pelaksana/PPPK) |
| `eselon` | Level eselon (I.a, I.b, II.a, III.a, IV.a) |

---

## Struktur Peran Pengguna

| role_id | Peran | Halaman Awal | Yang Bisa Dilakukan |
|---------|-------|-------------|---------------------|
| 1 | Admin | `/admin/users` | Kelola user, unit kerja, nomor awal, lihat audit log, bypass semua batasan |
| 2 | Pegawai | `/penomoran` | Buat/edit/batalkan surat **milik sendiri** |
| 3 | TU Unit | `/tu-unit/dashboard` | Lihat rekap surat unit kerja sendiri |
| 4 | Persuratan | `/tu-pusat/dashboard` | Lihat rekap surat semua unit |

### Aturan Akses Penting
- **Kepemilikan**: Pegawai hanya bisa edit/hapus/batalkan surat milik sendiri (`USER_ID`)
- **Backdate**: Hanya Admin yang boleh membuat surat dengan tanggal mundur
- **Batas Pembatalan**: Surat hanya bisa dibatalkan dalam **H+3** sejak pembuatan (Admin bebas)
- **Pendaftaran**: Pegawai **harus didaftarkan Admin** terlebih dahulu — tidak ada auto-registrasi

---

## Jenis Dokumen yang Didukung

| Jenis Dokumen | Status | Template Cetak |
|---------------|--------|---------------|
| Surat Dinas | ✅ Aktif | ✅ Ada |
| Undangan Eksternal | ✅ Aktif | ✅ Ada |
| SPT | ✅ Aktif | ✅ Ada |
| Surat Kuasa | ✅ Aktif | ✅ Ada |
| Surat Keterangan | ✅ Aktif | ❌ Belum ada |
| Berita Acara | ✅ Aktif | ✅ Ada |
| Sertifikat | ✅ Aktif | ❌ Belum ada |
| Pengumuman | ✅ Aktif | ✅ Ada |
| Surat Edaran | ✅ Aktif | ✅ Ada |
| Nota Dinas | ⚠️ Dihapus dari sistem | — |

> **Catatan Nota Dinas**: Dihapus dari dropdown, tapi data lama di database tetap ada dan masih bisa dilihat.

---

## Instalasi & Setup

### 1. Clone Repository
```bash
git clone https://gitlab.kemenkopmk.go.id/muh.rizeky/penomoran-surat.git
cd penomoran-surat
```

### 2. Install Dependensi
```bash
composer install
```

### 3. Konfigurasi Environment
Salin `.env.example` menjadi `.env` lalu sesuaikan:
```bash
cp .env.example .env
```

Isi konfigurasi minimal:
```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'

# Database penomoran
database.default.hostname = 192.168.10.145
database.default.database = penomoran_db
database.default.username = sipd
database.default.password = "..."

# Database master Kemenko
database.kemenkopmk.hostname = 192.168.10.145
database.kemenkopmk.database = kemenkopmk_db
database.kemenkopmk.username = sipd
database.kemenkopmk.password = "..."

# SSO (kosongkan untuk development lokal — akan fallback ke form login)
# SSO_URL=https://sso.kemenkopmk.go.id
# SSO_CLIENT_KEY=...
# SSO_CLIENT_SECRET=...
# SERVER_HOST=https://penomoran.kemenkopmk.go.id
```

### 4. Jalankan Migration
```bash
php spark migrate
```

### 5. Jalankan Seeder (Opsional — untuk data awal)
```bash
php spark db:seed UserSeeder
php spark db:seed KlasifikasiArsipSeeder
```

### 6. Jalankan Server Lokal
```bash
php spark serve
```
Akses di: `http://localhost:8080`

---

## Alur Login

```
Akses aplikasi
    │
    ├─ SSO dikonfigurasi di .env → Redirect ke SSO Kemenko
    │       └─ Callback ke /sso/callback → validasi token → set session
    │
    └─ SSO tidak dikonfigurasi (development) → Tampilkan form login manual
            └─ POST /login → validasi username di kemenkopmk_db
                    ├─ Tidak ada di kemenkopmk_db → error
                    ├─ Ada, tapi belum terdaftar di lokal → /tidak-terdaftar
                    ├─ Terdaftar tapi nonaktif → error
                    └─ Terdaftar & aktif → session dibuat → redirect sesuai role
```

---

## Route (URL) Lengkap

### Publik (Tanpa Login)
| Method | URL | Keterangan |
|--------|-----|------------|
| GET | `/login` | Halaman login / redirect SSO |
| POST | `/login` | Proses login manual |
| GET | `/logout` | Logout (hancurkan session) |
| GET | `/sso/callback` | Callback dari server SSO |
| GET | `/tidak-terdaftar` | Halaman info pegawai belum terdaftar |

### Pegawai (Login)
| Method | URL | Keterangan |
|--------|-----|------------|
| GET | `/penomoran` | Riwayat surat milik sendiri |
| GET | `/penomoran/create` | Form tambah surat baru |
| POST | `/penomoran/store` | Simpan surat baru |
| GET | `/penomoran/edit/{no}/{jenis}/{tahun}` | Form edit surat |
| POST | `/penomoran/update/{no}/{jenis}/{tahun}` | Simpan perubahan |
| POST | `/penomoran/delete/{no}/{jenis}/{tahun}` | Hapus surat |
| GET | `/penomoran/detail/{no}/{jenis}/{tahun}` | Detail surat |
| GET | `/penomoran/cancel/{no}/{jenis}/{tahun}` | Form konfirmasi batalkan |
| POST | `/penomoran/cancel-submit` | Proses pembatalan |
| GET | `/penomoran/cetak/{no}/{jenis}/{tahun}` | Template cetak surat |

### Admin
| Method | URL | Keterangan |
|--------|-----|------------|
| GET | `/admin/users` | Daftar user |
| GET/POST | `/admin/users/create`, `/store` | Tambah user |
| GET/POST | `/admin/users/edit/{id}`, `/update/{id}` | Edit user |
| POST | `/admin/users/toggle/{id}` | Aktifkan/nonaktifkan user |
| GET/POST | `/admin/nomor-awal` | Konfigurasi nomor awal |
| GET/POST | `/admin/unit-kerja` | Kelola unit kerja |
| GET | `/admin/audit-logs` | Lihat log aktivitas |

### TU Unit / Persuratan
| Method | URL | Keterangan |
|--------|-----|------------|
| GET | `/tu-unit/dashboard` | Dashboard rekap TU Unit |
| GET | `/tu-unit/rekap` | Rekap lengkap TU Unit |
| GET | `/tu-pusat/dashboard` | Dashboard rekap Persuratan |
| GET | `/tu-pusat/rekap` | Rekap lengkap Persuratan |

---

## Troubleshooting

### Login Redirect Loop (`ERR_TOO_MANY_REDIRECTS`)
**Penyebab**: Variabel `SSO_URL` / `SSO_IP_LOCAL` di `.env` belum diisi tapi `SSOService` tetap dipanggil.
**Solusi**: Di mode development, pastikan `SSO_URL` **dikosongkan** di `.env` agar sistem fallback ke form login manual.

### Tabel Tidak Ditemukan
**Solusi**: Jalankan migration
```bash
php spark migrate
```

### Pegawai Tidak Bisa Login (Halaman "Tidak Terdaftar")
**Penyebab**: Username pegawai belum didaftarkan di sistem lokal.
**Solusi**: Admin harus mendaftarkan pegawai via menu `/admin/users`.

### NO Tidak Bertambah Otomatis
**Penyebab**: Jenis Dokumen belum dipilih saat membuka form.
**Solusi**: Pilih Jenis Dokumen lebih dulu — nomor akan otomatis tampil via AJAX.

---

## Support

Untuk pertanyaan atau masalah, silakan hubungi developer sistem atau buka issue di GitLab:
`https://gitlab.kemenkopmk.go.id/muh.rizeky/penomoran-surat`
