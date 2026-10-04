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