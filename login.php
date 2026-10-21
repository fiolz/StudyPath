<?php
require 'config.php';

$user = $in['username'] ?? "";
$st = $db->prepare("SELECT id, nama, password FROM users WHERE username=?");
$st->bind_param('s', $user);
$st->execute();
$row = $st->get_result()->fetch_assoc();

if ($row && password_verify($in['password'] ?? '', $row['password'])) {
    echo json_encode(['status' => 'ok', 'id' => $row['id'], 'nama' => $row['nama']]);
} else {
    echo json_encode(['status' => 'error', 'pesan' => 'Username atau password salah.']);
}
