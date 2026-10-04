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

## 5. Normalisasi 3NF

Syarat 3NF: memenuhi 2NF dan tidak ada atribut bukan kunci yang bergantung pada atribut bukan kunci lainnya. Atribut yang bergantung transitif dipisahkan ke tabel tersendiri.

### 5.1 Analisis Ketergantungan Transitif

| Tabel 2NF | Ketergantungan Transitif | Solusi |
| --- | --- | --- |
| peminjaman | peminjaman_id → nim → (nama_mhs, program_studi, nomor_hp) | Pisahkan menjadi tabel `mahasiswa` dengan PK `nim` |
| buku | buku_id → penerbit_id → (nama_penerbit, kota_penerbit) | Pisahkan menjadi tabel `penerbit` dengan PK `penerbit_id` |

### 5.2 Hasil 3NF

| Tabel | Kunci Utama | Foreign Key | Kolom Data |
| --- | --- | --- | --- |
| penerbit | penerbit_id | - | nama_penerbit, kota_penerbit |
| buku | buku_id | penerbit_id | isbn, judul, pengarang, tahun_terbit, stok |
| mahasiswa | nim | - | nama_mhs, program_studi, nomor_hp |
| peminjaman | peminjaman_id | nim | tanggal_pinjam, tanggal_jatuh_tempo |
| detail_peminjaman | peminjaman_id + buku_id | peminjaman_id, buku_id | tanggal_kembali, denda |

### 5.2.1 Contoh Data Setelah Normalisasi 3NF

#### Tabel `penerbit`

| penerbit_id | nama_penerbit | kota_penerbit |
| --- | --- | --- |
| T001 | Cakrawala Ilmu | Jakarta |
| T002 | Pustaka Inovasi | Makassar |

#### Tabel `mahasiswa`

| nim | nama_mhs | program_studi | nomor_hp |
| --- | --- | --- | --- |
| D121241017 | Fikri | Teknik Informatika | 081234567801 |
| D121241034 | Naufal | Teknik Informatika | 082145678902 |

#### Tabel `buku`

| buku_id | isbn | judul | pengarang | tahun_terbit | stok | penerbit_id |
| --- | --- | --- | --- | --- | --- | --- |
| B001 | 9786238100123 | Pemrograman Web Dasar | Rizky Maulana | 2022 | 5 | T001 |
| B002 | 9786238100451 | Algoritma dan Struktur Data | Nadia Prameswari | 2021 | 3 | T002 |
| B003 | 9786238100789 | Basis Data Relasional | Yoga Kurniawan | 2023 | 4 | T001 |

#### Tabel `peminjaman`

| peminjaman_id | nim | tanggal_pinjam | tanggal_jatuh_tempo |
| --- | --- | --- | --- |
| P001 | D121241017 | 2026-09-01 | 2026-09-15 |
| P002 | D121241034 | 2026-09-03 | 2026-09-17 |
| P003 | D121241017 | 2026-09-20 | 2026-10-04 |

`detail_peminjaman` identik dengan hasil 2NF pada Bagian 4.2.

### 5.3 Pemeriksaan Anomali

| Anomali | Sebelum Normalisasi | Setelah 3NF |
| --- | --- | --- |
| Sisip | Penerbit atau buku baru tidak dapat dicatat sebelum ada transaksi peminjaman | Penerbit, buku, dan mahasiswa dapat dimasukkan tanpa transaksi |
| Hapus | Menghapus satu-satunya transaksi sebuah buku ikut menghilangkan data buku dan penerbitnya | Menghapus transaksi tidak memengaruhi tabel `buku` maupun `penerbit` |
| Pembaruan | Mengubah nomor HP Fikri harus dilakukan pada banyak baris dan berisiko tidak konsisten | Nomor HP Fikri cukup diubah pada satu baris di tabel `mahasiswa` |

## 6. Rancangan Tabel Akhir

Konvensi penamaan menggunakan `snake_case`, nama tabel berupa kata benda tunggal, dan nama Foreign Key dibuat identik dengan Primary Key pada tabel yang dirujuk. Tipe data dituliskan secara eksplisit beserta panjang atau presisinya apabila relevan.

### 6.1 Tabel `penerbit`

Struktur tabel berikut menunjukkan nama kolom, tipe data, status kunci, constraint, dan fungsi masing-masing atribut.

| Kolom | Tipe Data | Kunci | Constraint | Keterangan |
| --- | --- | --- | --- | --- |
| penerbit_id | VARCHAR(10) | PK | NOT NULL | Identitas unik penerbit |
| nama_penerbit | VARCHAR(100) | - | NOT NULL, UNIQUE | Nama penerbit |
| kota_penerbit | VARCHAR(50) | - | NOT NULL | Kota kedudukan penerbit |

### 6.2 Tabel `buku`

| Kolom | Tipe Data | Kunci | Constraint | Keterangan |
| --- | --- | --- | --- | --- |
| buku_id | VARCHAR(10) | PK | NOT NULL | Identitas unik buku |
| isbn | CHAR(13) | - | NOT NULL, UNIQUE | ISBN-13 tanpa tanda hubung |
| judul | VARCHAR(200) | - | NOT NULL | Judul buku |
| pengarang | VARCHAR(100) | - | NOT NULL | Nama pengarang |
| tahun_terbit | YEAR | - | NOT NULL | Tahun terbit |
| stok | SMALLINT UNSIGNED | - | NOT NULL, DEFAULT 0 | Jumlah eksemplar tersedia |
| penerbit_id | VARCHAR(10) | FK | NOT NULL | Merujuk `penerbit.penerbit_id` |

### 6.3 Tabel `mahasiswa`

| Kolom | Tipe Data | Kunci | Constraint | Keterangan |
| --- | --- | --- | --- | --- |
| nim | CHAR(10) | PK | NOT NULL | Nomor induk mahasiswa |
| nama_mhs | VARCHAR(100) | - | NOT NULL | Nama lengkap mahasiswa |
| program_studi | VARCHAR(50) | - | NOT NULL | Program studi |
| nomor_hp | VARCHAR(15) | - | NOT NULL | Nomor telepon seluler mahasiswa |

### 6.4 Tabel `peminjaman`

| Kolom | Tipe Data | Kunci | Constraint | Keterangan |
| --- | --- | --- | --- | --- |
| peminjaman_id | VARCHAR(10) | PK | NOT NULL | Identitas unik transaksi |
| nim | CHAR(10) | FK | NOT NULL | Merujuk `mahasiswa.nim` |
| tanggal_pinjam | DATE | - | NOT NULL | Tanggal transaksi dilakukan |
| tanggal_jatuh_tempo | DATE | - | NOT NULL, CHECK (tanggal_jatuh_tempo >= tanggal_pinjam) | Batas pengembalian |

### 6.5 Tabel `detail_peminjaman`

| Kolom | Tipe Data | Kunci | Constraint | Keterangan |
| --- | --- | --- | --- | --- |
| peminjaman_id | VARCHAR(10) | PK, FK | NOT NULL | Merujuk `peminjaman.peminjaman_id` |
| buku_id | VARCHAR(10) | PK, FK | NOT NULL | Merujuk `buku.buku_id` |
| tanggal_kembali | DATE | - | NULL | `NULL` berarti belum dikembalikan |
| denda | DECIMAL(10,2) | - | NOT NULL, DEFAULT 0, CHECK (denda >= 0) | Denda keterlambatan dalam rupiah |

Primary Key komposit `(peminjaman_id, buku_id)` mencegah buku yang sama tercatat dua kali dalam satu transaksi.

### 6.6 Aturan Integritas Referensial

| Foreign Key | Merujuk | ON DELETE | ON UPDATE | Alasan |
| --- | --- | --- | --- | --- |
| buku.penerbit_id | penerbit.penerbit_id | RESTRICT | CASCADE | Buku tidak boleh kehilangan penerbit |
| peminjaman.nim | mahasiswa.nim | RESTRICT | CASCADE | Riwayat peminjaman wajib dipertahankan |
| detail_peminjaman.peminjaman_id | peminjaman.peminjaman_id | CASCADE | CASCADE | Detail tidak bermakna tanpa transaksi induknya |
| detail_peminjaman.buku_id | buku.buku_id | RESTRICT | CASCADE | Buku yang pernah dipinjam tidak boleh terhapus |

`CASCADE` pada penghapusan mahasiswa sengaja dihindari karena akan menghapus seluruh riwayat peminjaman secara berantai dan tidak dapat dipulihkan.

## 7. Diagram ERD (Mermaid)

```mermaid
erDiagram
    PENERBIT ||--o{ BUKU : menerbitkan
    MAHASISWA ||--o{ PEMINJAMAN : melakukan
    PEMINJAMAN ||--|{ DETAIL_PEMINJAMAN : memuat
    BUKU ||--o{ DETAIL_PEMINJAMAN : dipinjam_dalam

    PENERBIT {
        varchar(10) penerbit_id PK
        varchar(100) nama_penerbit
        varchar(50) kota_penerbit
    }

    BUKU {
        varchar(10) buku_id PK
        char(13) isbn
        varchar(200) judul
        varchar(100) pengarang
        year tahun_terbit
        smallint stok
        varchar(10) penerbit_id FK
    }

    MAHASISWA {
        char(10) nim PK
        varchar(100) nama_mhs
        varchar(50) program_studi
        varchar(15) nomor_hp
    }

    PEMINJAMAN {
        varchar(10) peminjaman_id PK
        char(10) nim FK
        date tanggal_pinjam
        date tanggal_jatuh_tempo
    }

    DETAIL_PEMINJAMAN {
        varchar(10) peminjaman_id PK, FK
        varchar(10) buku_id PK, FK
        date tanggal_kembali
        decimal(10,2) denda
    }
```

Pembacaan notasi: `||` berarti tepat satu, `o{` berarti nol atau banyak, dan `|{` berarti satu atau banyak.

