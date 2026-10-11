<?php

declare(strict_types=1);

/**
 * Kelas GuestBook
 *
 * Mengelola penyimpanan dan pengambilan data buku tamu
 * melalui koneksi PDO dengan prepared statements.
 */
class GuestBook
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * Menyimpan satu pesan ke tabel buku_tamu.
     */
    public function simpan(string $nama, string $email, string $pesan): bool
    {
        $sql = 'INSERT INTO buku_tamu (nama, email, pesan) VALUES (:nama, :email, :pesan)';
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':nama'  => $nama,
            ':email' => $email,
            ':pesan' => $pesan,
        ]);
    }

    /**
     * Mengambil seluruh pesan, terbaru lebih dulu.
     *
     * @return array<int, array<string, mixed>>
     */
    public function ambilSemua(): array
    {
        $sql = 'SELECT id, nama, email, pesan, tanggal_kirim
                FROM buku_tamu
                ORDER BY tanggal_kirim DESC, id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validasi masukan. Mengembalikan array pesan galat per kolom.
     *
     * @return array<string, string>
     */
    public static function validasi(string $nama, string $email, string $pesan): array
    {
        $galat = [];

        if ($nama === '') {
            $galat['nama'] = 'Nama tidak boleh kosong.';
        } elseif (mb_strlen($nama) > 100) {
            $galat['nama'] = 'Nama maksimal 100 karakter.';
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $galat['email'] = 'Format email tidak valid.';
        } elseif (mb_strlen($email) > 150) {
            $galat['email'] = 'Email maksimal 150 karakter.';
        }

        if (mb_strlen($pesan) < 5) {
            $galat['pesan'] = 'Pesan minimal 5 karakter.';
        }

        return $galat;
    }
}