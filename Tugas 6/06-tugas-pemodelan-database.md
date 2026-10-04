# Tugas Mandiri Modul 6: Perancangan ERD E-Library Kampus

## 1. Skenario dan Spesifikasi Kebutuhan

Dirancang basis data relasional untuk sistem peminjaman buku perpustakaan kampus. Sistem mencatat data mahasiswa, buku, penerbit, serta riwayat peminjaman dan pengembalian.

### 1.1 Kebutuhan yang Dipenuhi

| No | Spesifikasi Tugas | Letak Pemenuhan |
| --- | --- | --- |
| 1 | ERD logis untuk Mahasiswa, Buku, Penerbit, Transaksi Peminjaman | Bagian 2 dan 7 |
| 2 | Identifikasi atribut beserta Primary Key dan Foreign Key | Bagian 2 dan 6 |
| 3 | Simulasi normalisasi UNF, 1NF, 2NF, 3NF | Bagian 3, 4, dan 5 |
| 4 | Tabel akhir dalam format Markdown lengkap dengan tipe data | Bagian 6 |
| 5 | Visualisasi relasi kunci (Mermaid dan diagram teks) | Bagian 7 dan 8 |

### 1.2 Aturan Bisnis dan Asumsi

1. Satu mahasiswa dapat melakukan banyak transaksi peminjaman, dan setiap transaksi dilakukan oleh tepat satu mahasiswa.
2. Satu transaksi peminjaman dapat memuat lebih dari satu buku.
3. Satu buku dapat dipinjam pada banyak transaksi yang berbeda (pada waktu yang berbeda).
4. Satu penerbit menerbitkan banyak buku, dan setiap buku diterbitkan oleh tepat satu penerbit.
5. Lama peminjaman adalah 14 hari sejak `tanggal_pinjam`, dicatat sebagai `tanggal_jatuh_tempo`.
6. Pengembalian dicatat per buku. Nilai `tanggal_kembali` yang masih `NULL` berarti buku belum dikembalikan.
7. Denda keterlambatan sebesar Rp1.000 per hari per buku, dicatat saat buku dikembalikan.
8. Data historis peminjaman wajib dipertahankan, sehingga penghapusan mahasiswa, buku, atau penerbit yang masih memiliki data terkait ditolak.

## 2. Identifikasi Entitas, Atribut, dan Relasi

### 2.1 Entitas dan Atribut

| Entitas | Atribut | Primary Key (PK) | Foreign Key (FK) |
| --- | --- | --- | --- |
| Penerbit | penerbit_id, nama_penerbit, kota_penerbit | penerbit_id | - |
| Buku | buku_id, isbn, judul, pengarang, tahun_terbit, stok, penerbit_id | buku_id | penerbit_id |
| Mahasiswa | nim, nama_mhs, program_studi, nomor_hp | nim | - |
| Peminjaman (Transaksi) | peminjaman_id, nim, tanggal_pinjam, tanggal_jatuh_tempo | peminjaman_id | nim |
| Detail Peminjaman | peminjaman_id, buku_id, tanggal_kembali, denda | peminjaman_id + buku_id (komposit) | peminjaman_id, buku_id |

Entitas **Detail Peminjaman** muncul sebagai hasil normalisasi pada Bagian 3 sampai 5. Entitas ini menjadi tabel penghubung relasi many-to-many antara Peminjaman dan Buku.

### 2.2 Relasi dan Kardinalitas

| Relasi | Entitas Induk | Entitas Anak | Kardinalitas | Keterangan |
| --- | --- | --- | --- | --- |
| menerbitkan | Penerbit | Buku | 1 : N | Satu penerbit menerbitkan banyak buku |
| melakukan | Mahasiswa | Peminjaman | 1 : N | Satu mahasiswa melakukan banyak transaksi |
| memuat | Peminjaman | Detail Peminjaman | 1 : N | Satu transaksi memuat satu atau lebih buku |
| dipinjam dalam | Buku | Detail Peminjaman | 1 : N | Satu buku dapat muncul di banyak transaksi |
| (turunan) | Peminjaman dan Buku | - | M : N | Diselesaikan melalui tabel `detail_peminjaman` |

## 3. Simulasi Normalisasi: UNF dan 1NF

### 3.1 Bentuk Tidak Normal (UNF)

Tabel awal mencatat data mahasiswa, transaksi, dan seluruh buku yang dipinjam dalam satu baris besar. Kolom **Buku Dipinjam** memuat kelompok data berulang.

| peminjaman_id | nim | Nama | Prodi | Nomor HP | Tgl Pinjam | Jatuh Tempo | Buku Dipinjam {ID Buku, ISBN, Judul, Pengarang, Tahun, ID Penerbit, Penerbit, Kota Penerbit, Tgl Kembali, Denda} |
| --- | --- | --- | --- | --- | --- | --- | --- |
| P001 | D121241017 | Fikri | Teknik Informatika | 081234567801 | 2026-09-01 | 2026-09-15 | {B001, 9786238100123, Pemrograman Web Dasar, Rizky Maulana, 2022, T001, Cakrawala Ilmu, Jakarta, 2026-09-10, 0}, {B002, 9786238100451, Algoritma dan Struktur Data, Nadia Prameswari, 2021, T002, Pustaka Inovasi, Makassar, 2026-09-18, 3000} |
| P002 | D121241034 | Naufal | Teknik Informatika | 082145678902 | 2026-09-03 | 2026-09-17 | {B001, 9786238100123, Pemrograman Web Dasar, Rizky Maulana, 2022, T001, Cakrawala Ilmu, Jakarta, 2026-09-14, 0} |
| P003 | D121241017 | Fikri | Teknik Informatika | 081234567801 | 2026-09-20 | 2026-10-04 | {B003, 9786238100789, Basis Data Relasional, Yoga Kurniawan, 2023, T001, Cakrawala Ilmu, Jakarta, NULL, 0} |

Permasalahan: kolom **Buku Dipinjam** memuat lebih dari satu kelompok nilai dalam satu sel, sehingga data belum memenuhi prinsip atomisitas pada bentuk normal pertama (1NF).

### 3.2 Konversi ke 1NF

Syarat 1NF: setiap sel hanya berisi satu nilai atomik dan tidak ada kelompok data berulang. Setiap buku dalam satu transaksi dijadikan baris tersendiri. Kunci utama menjadi kunci komposit **(peminjaman_id, buku_id)**.

| peminjaman_id (PK-1) | buku_id (PK-2) | nim | nama_mhs | program_studi | nomor_hp | tanggal_pinjam | tanggal_jatuh_tempo | isbn | judul | pengarang | tahun_terbit | penerbit_id | nama_penerbit | kota_penerbit | tanggal_kembali | denda |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| P001 | B001 | D121241017 | Fikri | Teknik Informatika | 081234567801 | 2026-09-01 | 2026-09-15 | 9786238100123 | Pemrograman Web Dasar | Rizky Maulana | 2022 | T001 | Cakrawala Ilmu | Jakarta | 2026-09-10 | 0 |
| P001 | B002 | D121241017 | Fikri | Teknik Informatika | 081234567801 | 2026-09-01 | 2026-09-15 | 9786238100451 | Algoritma dan Struktur Data | Nadia Prameswari | 2021 | T002 | Pustaka Inovasi | Makassar | 2026-09-18 | 3000 |
| P002 | B001 | D121241034 | Naufal | Teknik Informatika | 082145678902 | 2026-09-03 | 2026-09-17 | 9786238100123 | Pemrograman Web Dasar | Rizky Maulana | 2022 | T001 | Cakrawala Ilmu | Jakarta | 2026-09-14 | 0 |
| P003 | B003 | D121241017 | Fikri | Teknik Informatika | 081234567801 | 2026-09-20 | 2026-10-04 | 9786238100789 | Basis Data Relasional | Yoga Kurniawan | 2023 | T001 | Cakrawala Ilmu | Jakarta | NULL | 0 |

Permasalahan yang masih ditemukan adalah redundansi data. Data Fikri ditulis berulang pada setiap buku yang ia pinjam, dan data buku "Pemrograman Web Dasar" ditulis berulang pada setiap transaksi yang meminjamnya.

## 4. Normalisasi 2NF

Syarat 2NF: memenuhi 1NF dan seluruh atribut bukan kunci bergantung penuh pada seluruh kunci utama. Ketergantungan parsial dipisah ke tabel baru.

### 4.1 Analisis Ketergantungan Fungsional

Kunci komposit: (peminjaman_id, buku_id).

| Ketergantungan | Jenis | Atribut |
| --- | --- | --- |
| peminjaman_id → ... | Parsial (hanya sebagian kunci) | nim, nama_mhs, program_studi, nomor_hp, tanggal_pinjam, tanggal_jatuh_tempo |
| buku_id → ... | Parsial (hanya sebagian kunci) | isbn, judul, pengarang, tahun_terbit, penerbit_id, nama_penerbit, kota_penerbit |
| (peminjaman_id, buku_id) → ... | Penuh | tanggal_kembali, denda |

### 4.2 Hasil 2NF

**Tabel `peminjaman`** (PK: peminjaman_id)

| peminjaman_id | nim | nama_mhs | program_studi | nomor_hp | tanggal_pinjam | tanggal_jatuh_tempo |
| --- | --- | --- | --- | --- | --- | --- |
| P001 | D121241017 | Fikri | Teknik Informatika | 081234567801 | 2026-09-01 | 2026-09-15 |
| P002 | D121241034 | Naufal | Teknik Informatika | 082145678902 | 2026-09-03 | 2026-09-17 |
| P003 | D121241017 | Fikri | Teknik Informatika | 081234567801 | 2026-09-20 | 2026-10-04 |

**Tabel `buku`** (PK: buku_id)

| buku_id | isbn | judul | pengarang | tahun_terbit | penerbit_id | nama_penerbit | kota_penerbit |
| --- | --- | --- | --- | --- | --- | --- | --- |
| B001 | 9786238100123 | Pemrograman Web Dasar | Rizky Maulana | 2022 | T001 | Cakrawala Ilmu | Jakarta |
| B002 | 9786238100451 | Algoritma dan Struktur Data | Nadia Prameswari | 2021 | T002 | Pustaka Inovasi | Makassar |
| B003 | 9786238100789 | Basis Data Relasional | Yoga Kurniawan | 2023 | T001 | Cakrawala Ilmu | Jakarta |

**Tabel `detail_peminjaman`** (PK komposit: peminjaman_id + buku_id)

| peminjaman_id | buku_id | tanggal_kembali | denda |
| --- | --- | --- | --- |
| P001 | B001 | 2026-09-10 | 0 |
| P001 | B002 | 2026-09-18 | 3000 |
| P002 | B001 | 2026-09-14 | 0 |
| P003 | B003 | NULL | 0 |

Permasalahan yang masih ditemukan adalah ketergantungan transitif.

- Pada `peminjaman`: `nim` → `nama_mhs`, `program_studi`, `nomor_hp`. Atribut mahasiswa bergantung pada `nim`, bukan pada `peminjaman_id`.
- Pada `buku`: `penerbit_id` → `nama_penerbit`, `kota_penerbit`. Atribut penerbit bergantung pada `penerbit_id`, bukan pada `buku_id`.
