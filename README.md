# Sistem Penomoran Surat Otomatis

> Automated document numbering system with role-based access, SSO integration, and Docker deployment.

## 🎯 Ringkasan Proyek
Sistem Penomoran Surat Otomatis ini dikembangkan untuk mendigitalisasi dan mengotomatisasi tata kelola administrasi persuratan di lingkungan Kemenko PMK. Aplikasi ini memastikan sentralisasi data, mencegah duplikasi nomor surat, dan memberikan hak akses yang aman bagi berbagai peran dalam organisasi.

- **Status:** Active / Completed
- **Tech Stack:** CodeIgniter 4, MySQL, Docker
- **Integrasi:** Single Sign-On (SSO)
- **Dokumentasi Produk & Teknis:** [Baca Product Requirements Document (PRD)](./docs/prd-penomoran-surat.md) 👈

## ✨ Fitur Utama
1. **Otomatisasi Penomoran:** Menghasilkan nomor surat (format unik per unit kerja & kode arsip) secara otomatis yang akan di-reset setiap pergantian tahun.
2. **Role-Based Access Control (RBAC):** Pemisahan hak akses dan *logic* aplikasi secara ketat antara Admin, Unit Kearsipan, dan Pegawai (User).
3. **Single Sign-On (SSO):** Integrasi login yang mulus dan aman menggunakan kredensial internal instansi.
4. **Audit Log & Lock System:** Fitur pembatalan surat dengan batas waktu (H+3) yang akan mengunci nomor agar tidak terjadi duplikasi.
