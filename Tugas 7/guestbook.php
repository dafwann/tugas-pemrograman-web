<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/src/GuestBook.php';

// ---------- Konfigurasi koneksi PDO ----------
$dbHost = '127.0.0.1';
$dbName = 'perpustakaan';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Koneksi basis data gagal.');
}

$guestBook = new GuestBook($pdo);

// ---------- Fungsi bantu: sanitasi keluaran ----------
function e(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ---------- Token CSRF ----------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$galat  = [];
$sukses = $_SESSION['flash_sukses'] ?? '';
unset($_SESSION['flash_sukses']);

$nama  = '';
$email = '';
$pesan = '';

// ---------- Proses form ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenKiriman = $_POST['csrf_token'] ?? '';

    if (!is_string($tokenKiriman) || !hash_equals($_SESSION['csrf_token'], $tokenKiriman)) {
        http_response_code(403);
        exit('Token CSRF tidak valid.');
    }

    $nama  = trim((string) ($_POST['nama'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $pesan = trim((string) ($_POST['pesan'] ?? ''));

    $galat = GuestBook::validasi($nama, $email, $pesan);

    if (empty($galat)) {
        try {
            $guestBook->simpan($nama, $email, $pesan);
            $_SESSION['flash_sukses'] = 'Pesan berhasil dikirim. Terima kasih!';
            $_SESSION['csrf_token']   = bin2hex(random_bytes(32));
            header('Location: guestbook.php');
            exit;
        } catch (PDOException $e) {
            $galat['umum'] = 'Terjadi kesalahan saat menyimpan pesan.';
        }
    }
}

// ---------- Ambil data ----------
try {
    $daftarPesan = $guestBook->ambilSemua();
} catch (PDOException $e) {

    $daftarPesan = [];
    $galat['umum'] = 'Terjadi kesalahan saat mengambil data.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tamu Perpustakaan</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 24px; color: #222; }
        .wrap { max-width: 860px; margin: 0 auto; }
        h1, h2 { margin-top: 0; }
        .card { background: #fff; border-radius: 8px; padding: 20px; margin-bottom: 24px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        label { display: block; margin: 12px 0 4px; font-weight: bold; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font: inherit; }
        textarea { min-height: 110px; resize: vertical; }
        button { margin-top: 16px; padding: 10px 20px; background: #1f6feb; color: #fff; border: 0; border-radius: 4px; cursor: pointer; font: inherit; }
        button:hover { background: #1558c0; }
        .galat { color: #b42318; font-size: 14px; margin-top: 4px; }
        .alert-galat { background: #fee4e2; color: #b42318; padding: 10px; border-radius: 4px; margin-bottom: 12px; }
        .alert-sukses { background: #d1fadf; color: #05603a; padding: 10px; border-radius: 4px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        th { background: #f0f2f5; }
        .kosong { text-align: center; color: #666; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>Buku Tamu Perpustakaan</h1>

    <div class="card">
        <h2>Tulis Pesan</h2>

        <?php if ($sukses !== ''): ?>
            <div class="alert-sukses"><?= e($sukses) ?></div>
        <?php endif; ?>

        <?php if (isset($galat['umum'])): ?>
            <div class="alert-galat"><?= e($galat['umum']) ?></div>
        <?php endif; ?>

        <form method="post" action="guestbook.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

            <label for="nama">Nama</label>
            <input type="text" id="nama" name="nama" maxlength="100" value="<?= e($nama) ?>">
            <?php if (isset($galat['nama'])): ?>
                <div class="galat"><?= e($galat['nama']) ?></div>
            <?php endif; ?>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" maxlength="150" value="<?= e($email) ?>">
            <?php if (isset($galat['email'])): ?>
                <div class="galat"><?= e($galat['email']) ?></div>
            <?php endif; ?>

            <label for="pesan">Pesan</label>
            <textarea id="pesan" name="pesan"><?= e($pesan) ?></textarea>
            <?php if (isset($galat['pesan'])): ?>
                <div class="galat"><?= e($galat['pesan']) ?></div>
            <?php endif; ?>

            <button type="submit">Kirim Pesan</button>
        </form>
    </div>

    <div class="card">
        <h2>Daftar Pesan</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Pesan</th>
                    <th>Tanggal Kirim</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($daftarPesan)): ?>
                <tr><td colspan="5" class="kosong">Belum ada pesan.</td></tr>
            <?php else: ?>
                <?php foreach ($daftarPesan as $baris): ?>
                    <tr>
                        <td><?= (int) $baris['id'] ?></td>
                        <td><?= e($baris['nama']) ?></td>
                        <td><?= e($baris['email']) ?></td>
                        <td><?= nl2br(e($baris['pesan'])) ?></td>
                        <td><?= e($baris['tanggal_kirim']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>