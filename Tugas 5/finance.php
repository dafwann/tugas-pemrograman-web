<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/Transaction.php';

if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 0.0;
}

if (!isset($_SESSION['history'])) {
    $_SESSION['history'] = [];
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors  = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], (string) $token)) {
        $errors[] = 'Token CSRF tidak valid. Silakan muat ulang halaman.';
    } else {
        $type       = $_POST['type'] ?? '';
        $amountRaw  = $_POST['amount'] ?? '';
        $validTypes = ['deposit', 'withdraw'];

        if (!in_array($type, $validTypes, true)) {
            $errors[] = 'Jenis transaksi tidak valid.';
        }

        if (!is_numeric($amountRaw) || (float) $amountRaw <= 0) {
            $errors[] = 'Jumlah transaksi harus berupa angka desimal positif.';
        }

        if (empty($errors)) {
            $amount = (float) $amountRaw;
            $id     = uniqid('trx_', true);

            $transaction = new Transaction($id, $type, $amount);
            $balance     = (float) $_SESSION['balance'];

            $processed = $transaction->process($balance);

            if (!$processed) {
                $errors[] = 'Saldo tidak mencukupi untuk melakukan penarikan.';
            } else {
                $_SESSION['balance']  = $balance;
                $_SESSION['history'][] = $transaction->toArray();
                $success = 'Transaksi berhasil diproses.';
            }
        }
    }

    // Regenerasi token setelah setiap percobaan submit (mencegah replay).
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];
$balance   = (float) $_SESSION['balance'];
$history   = $_SESSION['history'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sistem Manajemen Keuangan Sederhana</title>
<style>
    body { font-family: Arial, sans-serif; max-width: 640px; margin: 40px auto; padding: 0 16px; color: #1f2937; }
    h1 { font-size: 1.4rem; }
    form { border: 1px solid #d1d5db; border-radius: 8px; padding: 16px; margin-bottom: 24px; }
    label { display: block; margin-bottom: 4px; font-weight: bold; }
    select, input[type="number"] { width: 100%; padding: 8px; margin-bottom: 12px; box-sizing: border-box; }
    button { background: #2563eb; color: #fff; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; }
    button:hover { background: #1d4ed8; }
    .balance { font-size: 1.2rem; font-weight: bold; margin-bottom: 16px; }
    .error { color: #b91c1c; background: #fee2e2; padding: 8px 12px; border-radius: 6px; margin-bottom: 8px; }
    .success { color: #065f46; background: #d1fae5; padding: 8px 12px; border-radius: 6px; margin-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb; }
    th { background: #f3f4f6; }
</style>
</head>
<body>

<h1>Sistem Manajemen Keuangan Sederhana</h1>

<?php foreach ($errors as $error): ?>
    <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endforeach; ?>

<?php if ($success !== null): ?>
    <div class="success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="balance">
    Saldo saat ini: Rp<?= htmlspecialchars(number_format($balance, 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?>
</div>

<form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

    <label for="type">Jenis Transaksi</label>
    <select name="type" id="type" required>
        <option value="deposit">Deposit</option>
        <option value="withdraw">Penarikan</option>
    </select>

    <label for="amount">Jumlah</label>
    <input type="number" name="amount" id="amount" step="0.01" min="0.01" required>

    <button type="submit">Proses Transaksi</button>
</form>

<h2>Riwayat Transaksi</h2>

<?php if (empty($history)): ?>
    <p>Belum ada transaksi.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Jenis</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach (array_reverse($history) as $trx): ?>
            <tr>
                <td><?= htmlspecialchars((string) $trx['id'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($trx['type'] === 'deposit' ? 'Deposit' : 'Penarikan', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars(number_format((float) $trx['amount'], 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>