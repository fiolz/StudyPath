<?php
require 'config.php';

header('Content-Type: application/json');

$user_id = $_POST['user_id'] ?? $_POST['id'] ?? $_GET['user_id'] ?? (is_array($in) ? ($in['user_id'] ?? $in['id'] ?? '') : '') ?? '';
$user_id = intval($user_id);

if ($user_id <= 0) {
    echo json_encode(['status' => 'error', 'pesan' => 'User ID tidak ditemukan.']);
    exit;
}

$today = date('Y-m-d');

// 1. Ambil Nama User
$st_user = $db->prepare("SELECT nama FROM users WHERE id = ?");
$st_user->bind_param('i', $user_id);
$st_user->execute();
$nama = $st_user->get_result()->fetch_assoc()['nama'] ?? 'Pengguna';

// 2. Hitung Total Waktu Belajar
$st1 = $db->prepare("SELECT COALESCE(SUM(durasi_menit), 0) AS total_waktu FROM sesi WHERE user_id = ?");
$st1->bind_param('i', $user_id);
$st1->execute();
$total_waktu = (int)($st1->get_result()->fetch_assoc()['total_waktu'] ?? 0);

// 3. Hitung Fokus Hari Ini
$st2 = $db->prepare("SELECT COALESCE(SUM(durasi_menit), 0) AS fokus_hari_ini FROM sesi WHERE user_id = ? AND tanggal = ?");
$st2->bind_param('is', $user_id, $today);
$st2->execute();
$fokus_hari_ini = (int)($st2->get_result()->fetch_assoc()['fokus_hari_ini'] ?? 0);

// 4. Hitung Total Sesi
$st3 = $db->prepare("SELECT COUNT(id) AS total_sesi FROM sesi WHERE user_id = ?");
$st3->bind_param('i', $user_id);
$st3->execute();
$total_sesi = (int)($st3->get_result()->fetch_assoc()['total_sesi'] ?? 0);

echo json_encode([
    'status' => 'ok',
    'nama' => $nama,
    'total_waktu' => $total_waktu,
    'fokus_hari_ini' => $fokus_hari_ini,
    'total_sesi' => $total_sesi
]);
