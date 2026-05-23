# Analisa Data Pegawai Bermasalah — `kemenkopmk_db`

**Dibuat**: 2026-05-04
**Tujuan**: Mendokumentasikan temuan ketidaksesuaian data pegawai di database master agar dapat ditindaklanjuti oleh operator kepegawaian.

---

## 1. Rony Bintoro M.Gurning, S.Kom, M.T.I

### Identitas

| Kolom | Nilai di Database |
|---|---|
| `pegawai.id` | 82 |
| NIP | 197506232008011006 |
| Nama tersimpan | **`RONY123 Rony Bintoro M.Gurning, S.Kom, M.T.I`** ⚠️ |
| Pangkat / Golongan | Pembina / IV/a |
| Unit Kerja | Biro Digitalisasi dan Pengelolaan Informasi |

### Jabatan yang Tercatat

| Jenis | Jabatan | Eselon |
|---|---|---|
| Aktif (`jabatan_id = 58`) | Pranata Komputer Ahli Muda | *(tidak ada)* |
| PLT (`jabatan_plt_id`) | *(kosong / 0)* | — |
| PLH (`jabatan_plh_id`) | *(kosong / 0)* | — |

### Temuan & Masalah

**A. Pangkat tidak sesuai jabatan**

Pangkat **Pembina / IV/a** adalah pangkat yang lazim disandang oleh:
- Pejabat Eselon III.a (Kepala Bagian), **atau**
- Pejabat Fungsional Ahli Madya

Namun jabatan yang tercatat adalah "Pranata Komputer **Ahli Muda**" yang normalnya berpangkat III/c–III/d. Ada selisih 2 tingkat golongan — tidak wajar.

**B. Tidak ada jabatan Kabag Biro Digitalisasi di tabel master**

Dari seluruh isi tabel `jabatan`, tidak ada satu pun jabatan *Kepala Bagian* yang berafiliasi dengan Biro Digitalisasi dan Pengelolaan Informasi. Artinya jabatan Kabag Rony belum pernah didaftarkan ke database.

**C. Nama pegawai memiliki prefix tidak wajar**

Nama tersimpan sebagai `RONY123 Rony Bintoro M.Gurning` — prefix `RONY123` adalah artefak dari proses input data yang tidak bersih (kemungkinan saat migrasi data atau uji coba import).

**D. Riwayat jabatan kosong**

Tabel `riwayat_jabatan` tidak memiliki catatan apapun untuk pegawai ini, sehingga tidak bisa ditelusuri perjalanan jabatan sebelumnya.

### Kesimpulan

> Data jabatan Rony Bintoro di `kemenkopmk_db` **belum diperbarui** setelah yang bersangkutan diangkat sebagai Kepala Bagian. Sistem kepegawaian masih mencatat jabatan fungsional lama.

### Rekomendasi Tindak Lanjut

| No | Aksi | Tabel yang Diubah |
|---|---|---|
| 1 | Perbaiki nama: hapus prefix `RONY123` | `pegawai` |
| 2 | Tambahkan jabatan baru sesuai SK pengangkatan (contoh: "Kepala Bagian Sistem Informasi") dengan `eselon = 'III.a'` | `jabatan` |
| 3 | Update `jabatan_id` pegawai ke jabatan baru tersebut | `pegawai` |
| 4 | Sesuaikan `unit_kerja_id` jika Bagian baru memiliki unit kerja tersendiri | `pegawai` |

---

## 2. Theophanie Oktrianti A.L. Solin, S.Tr.Kom

### Identitas

| Kolom | Nilai di Database |
|---|---|
| NIP | 199510232020122019 |
| Pangkat / Golongan | Penata Muda / III/b |
| Unit Kerja | **Bagian Tata Usaha** ⚠️ |
| Jabatan Tercatat | Kepala Biro Digitalisasi dan Pengelolaan Informasi |
| Eselon Jabatan | II.a |

### Temuan & Masalah

Pegawai dengan pangkat **Penata Muda / III/b** tidak mungkin secara organisatoris menjabat sebagai **Kepala Biro (Eselon II.a)**. Jabatan Eselon II.a mensyaratkan pangkat minimal Pembina (IV/a) ke atas.

Ditambah, unit kerja yang tercatat adalah "Bagian Tata Usaha", bukan "Biro Digitalisasi" — semakin memperkuat bahwa ini adalah **kesalahan input `jabatan_id`**.

### Kesimpulan

> `jabatan_id` untuk pegawai ini salah tunjuk. Kemungkinan `jabatan_id` seharusnya mengarah ke jabatan Pelaksana/Fungsional, bukan Kepala Biro.

### Rekomendasi Tindak Lanjut

| No | Aksi | Tabel yang Diubah |
|---|---|---|
| 1 | Koreksi `jabatan_id` ke jabatan yang sesuai pangkat III/b | `pegawai` |
| 2 | Sesuaikan `unit_kerja_id` ke unit yang benar | `pegawai` |

---

## Daftar Masalah Prioritas

| Prioritas | Pegawai | Masalah |
|---|---|---|
| 🔴 Tinggi | Theophanie Oktrianti | Jabatan Eselon II.a tidak sesuai pangkat III/b |
| 🟡 Sedang | Rony Bintoro | Jabatan belum diperbarui setelah naik ke Kabag |
| 🟡 Sedang | Rony Bintoro | Nama pegawai memiliki prefix `RONY123` |

---

## Catatan Teknis

Untuk melakukan perbaikan, operator kepegawaian perlu menjalankan query berikut di `kemenkopmk_db`:

```sql
-- Perbaiki nama Rony Bintoro
UPDATE pegawai
SET nama = 'Rony Bintoro M.Gurning'
WHERE id = 82;

-- Tambahkan jabatan baru Kabag (sesuaikan nama dengan SK)
-- INSERT INTO jabatan (nama_jabatan, tipe_jabatan, eselon, level) VALUES ('Kepala Bagian ...', 'Struktural', 'III.a', 3);

-- Setelah jabatan baru ditambah, update jabatan_id Rony
-- UPDATE pegawai SET jabatan_id = [id_jabatan_baru] WHERE id = 82;

-- Perbaiki jabatan Theophanie (ganti [jabatan_sesuai] dengan jabatan yang benar)
-- UPDATE pegawai SET jabatan_id = [jabatan_sesuai] WHERE nip = '199510232020122019';
```

> **Perhatian**: Seluruh perubahan di `kemenkopmk_db` harus dikoordinasikan dengan tim pengelola data kepegawaian Kemenko PMK, karena database ini bersifat master dan digunakan oleh lebih dari satu sistem.
