<?php
require 'config.php';

$nama = trim($in['nama'] ?? '');
$username = trim($in['username'] ?? '');
$password = $in['password'] ?? '';

if ($nama === '' || $username === '' || $password === '') {
    echo json_encode(['status' => 'error', 'pesan' => 'Semua kolom harus diisi.']);
    exit;
}

$pass = password_hash($password, PASSWORD_DEFAULT);
$st = $db->prepare("INSERT INTO users (nama, username, password) VALUES (?, ?, ?)");
$st->bind_param('sss', $nama, $username, $pass);

try {
    $st->execute();
    echo json_encode(['status' => 'ok']);
} catch (mysqli_sql_exception $e) {
    $pesan = $e->getCode() == 1062 ? 'Username sudah dipakai.' : 'Gagal mendaftar.';
    echo json_encode(['status' => 'error', 'pesan' => $pesan]);
}
