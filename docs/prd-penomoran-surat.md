# Product Requirements Document (PRD) - Modul Penomoran Surat
**Sistem Penomoran Surat Otomatis — SATU KEMENKO PMK**
*Domain Produksi: penomoran.kemenkopmk.go.id*

---

## 📌 Informasi Dokumen

| Peran | Nama / Entitas |
| :--- | :--- |
| **Tanggal Dokumen** | Januari 2026 |
| **Product Manager** | Luqyana Andin |
| **Project Manager** | Biro Umum dan Keuangan |
| **Business Owner** | Sekretaris Kemenko PMK |
| **Tech Leads** | Kepala Biro Digitalisasi dan Pengelolaan Informasi |
| **Target Live** | Februari 2026 |

### Riwayat Versi (Version History)
| Versi | Tanggal | Perubahan |
| :---: | :--- | :--- |
| 1.0 | Januari 2026 | Initial Document & Technical Specs Integration |

---

## 1. 📖 Latar Belakang
Dalam pelaksanaan tata kelola administrasi persuratan, proses pencatatan dan pengambilan nomor surat masih dilakukan secara manual menggunakan media *spreadsheet*. Proses manual tersebut menimbulkan berbagai kendala, seperti tingginya risiko kesalahan input, duplikasi nomor surat, keterlambatan distribusi nomor, serta tidak adanya kendali terpusat terhadap riwayat dan penggunaan nomor surat di setiap unit kerja. 

Melalui pengembangan aplikasi penomoran surat, proses pemberian nomor dapat dilakukan secara otomatis, terdokumentasi dengan baik, serta memiliki jejak audit yang jelas. Sistem ini diharapkan mampu meminimalkan potensi *human error*, mempercepat penerbitan nomor surat, serta memperkuat akuntabilitas pengelolaan dokumen resmi.

## 2. 🎯 Target Sasaran
Pengguna dari Modul Penomoran Surat ini meliputi:
*   **Seluruh Pegawai Kemenko PMK:** Bertindak sebagai peminta layanan penomoran surat.
*   **Unit Kearsipan Biro Umum dan Keuangan:** Bertindak sebagai pelaksana layanan, pengawas, dan verifikator.
*   **Pegawai Tata Usaha di Unit Kerja:** Bertindak sebagai pemroses dan pemantau pengambilan nomor dengan prosedur yang terstandarisasi.

## 3. ⚠️ Kendala dan Strategi Mitigasi

**Potensi Kendala:**
*   Format penomoran surat yang berbeda-beda antar unit kerja.
*   Perubahan *workflow* yang dinamis mengikuti kebijakan internal.
*   Tantangan adopsi sistem baru oleh *user*.

**Strategi Mitigasi:**
*   Menetapkan format data dan struktur referensi (Kode Arsip) yang seragam dan dikunci sejak awal.
*   Melakukan *requirement gathering* komprehensif pada fase inisiasi.
*   Menerapkan metode SDLC *Agile Development* agar adaptif terhadap perubahan.

---

## 4. 📝 Deskripsi Sistem & Spesifikasi

### a. Spesifikasi Teknis & Aturan Penomoran
Sistem akan mengotomatisasi *generate* nomor surat dengan batasan sistem dan aturan ketat sebagai berikut:

> **Format Penomoran Otomatis:**
> `Nomor Urut / Unit Kerja / Kode Arsip / Bulan / Tahun`
> *Contoh output:* `001/DIGI/PR.01/06/2026`

**Aturan Penomoran (System Logic):**
1. Nomor surat **dibuat secara otomatis** oleh sistem saat pengajuan dilakukan.
2. Nomor surat bersifat **unik dan tidak boleh duplikat** di dalam database.
3. Nomor urut berlaku secara **global dalam satu tahun** berjalan.
4. Nomor urut akan **kembali direset (dimulai dari angka 1)** secara otomatis pada pergantian tahun berikutnya.

### b. Deskripsi Fitur (Feature Breakdown)

**1. Pengajuan Nomor Surat**
*   **Kebutuhan:** Pegawai mengajukan permohonan pengambilan nomor surat melalui form yang disediakan.
*   **Data Input:** Jenis surat, Tanggal Surat, Asal Surat (Unit Kerja), dan Perihal.
*   **Otomatisasi:** Terintegrasi dengan *Single Sign-On (SSO)* untuk merekam identitas pengaju secara otomatis.
*   **Acceptance Criteria:**
    * Sistem menampilkan *pop-up* informasi detail surat beserta nomor surat yang berhasil di-*generate*.
    * Pengguna wajib mengisi *field* Nomor Surat Lengkap sebelum menyelesaikan proses pengajuan.

**2. Pembatalan Nomor Surat**
*   **Kebutuhan:** Pegawai dapat membatalkan nomor surat yang telah terbit sebelumnya.
*   **Data Input:** Nomor Surat, Jenis Surat, Tanggal Surat, dan Alasan Pembatalan.
*   **Batasan Waktu:** Maksimal **H+3** dari waktu pengambilan nomor surat.
*   **Acceptance Criteria:**
    * Nomor surat yang telah dibatalkan akan dikunci (*locked*) dan tidak dapat diambil atau digunakan oleh pengguna lain.
    * Informasi pembatalan terekam di halaman Detail Nomor Surat.

**3. Pencatatan & Monitoring (Admin View)**
*   **Kebutuhan:** Admin dan Bagian Kearsipan dapat memantau seluruh pencatatan nomor surat.
*   **Fitur Pendukung:** 
    * Filter data berdasarkan: Tanggal Surat, Jenis Surat, dan Asal Surat.
    * Pencarian bebas menggunakan kata kunci.
*   **Acceptance Criteria:**
    * Tabel menampilkan: Nomor, Jenis, Tanggal, Perihal, Asal Surat, Pengaju, dan Status (Terbit/Batal).
    * Klik 'Detail' akan menampilkan *pop-up* informasi komprehensif mengenai riwayat pengambilan surat tersebut.

**4. Riwayat Pengambilan Pribadi (User View)**
*   **Kebutuhan:** Pegawai dapat melihat histori pengambilan nomor surat yang diajukan oleh akunnya sendiri.
*   **Acceptance Criteria:**
    * Tersedia tabel riwayat personal berisi: Nomor Surat, Jenis, Perihal, dan Status.
    * Tersedia fitur klik 'Detail' untuk melihat informasi lengkap pengajuan.

### c. Role-Based Access Control (RBAC)

| Fitur / Modul | Admin | Kearsipan | Pegawai |
| :--- | :---: | :---: | :---: |
| **Pengajuan Nomor Surat** | Create, Read | Create, Read | Create, Read |
| **Pembatalan Nomor Surat** | Update, Read | Update, Read | Update, Read |
| **Pencatatan Seluruh Surat**| Read | Read | - |
| **Riwayat Surat Pribadi** | Read | Read | Read |
| **Hapus Surat (Hard Delete)**| ❌ | ❌ | ❌ |

> **Catatan Security:** Penghapusan permanen (*hard delete*) **tidak diizinkan** untuk *role* manapun guna menjaga integritas *Log* institusi.

### d. Non-functional Requirement
*   **Availability:** Sistem dapat diakses menggunakan jaringan internet setiap hari (Senin-Minggu) selama 24 jam.
*   **Security:**
    * Menerapkan *zero-tolerance* terhadap akses yang tidak sah.
    * Menggunakan enkripsi untuk penyimpanan data kredensial (seperti *password*) dan komunikasi *client-server*.
    * Menerapkan deteksi ancaman keamanan secara *real-time*.
*   **Usability:** Tampilan antarmuka sistem (SMART PMK) harus *user-friendly*, responsif, dan mudah diakses oleh seluruh rentang usia pengguna.

### e. Roadmap & Scope (Iterasi 1)
Rilis pertama (Iterasi 1) mencakup seluruh fitur inti persuratan: Pengajuan otomatis, pelacakan riwayat, pembatalan dengan batas waktu, dan *dashboard monitoring* sentral. 

*(Di luar lingkup: Generate format fisik dokumen PDF secara keseluruhan dan integrasi API ke sistem Persuratan eksternal lintas kementerian atau SRIKANDI).*

---
*Dokumen ini disusun sebagai bagian dari portofolio Product Management & System Analysis.*
