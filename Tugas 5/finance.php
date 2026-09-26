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
