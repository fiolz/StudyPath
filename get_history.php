<?php
require 'config.php';

header('Content-Type: application/json');

$user_id = $_POST['user_id'] ?? $_POST['id'] ?? $_GET['user_id'] ?? (is_array($in) ? ($in['user_id'] ?? $in['id'] ?? '') : '') ?? '';
$user_id = intval($user_id);

if ($user_id <= 0) {
    echo json_encode(['status' => 'error', 'pesan' => 'User ID tidak ditemukan.']);
    exit;
}

$query = "SELECT s.id, COALESCE(m.nama_mapel, 'Mata Pelajaran') AS nama_mapel, s.tanggal, s.durasi_menit, COALESCE(s.catatan, '') AS catatan 
          FROM sesi s
          LEFT JOIN mapel m ON s.mapel_id = m.id
          WHERE s.user_id = ?
          ORDER BY s.tanggal DESC, s.id DESC";

$st = $db->prepare($query);
$st->bind_param('i', $user_id);
$st->execute();
$result = $st->get_result();

$data_riwayat = [];
while ($row = $result->fetch_assoc()) {
    $data_riwayat[] = [
        'id' => (int)$row['id'],
        'nama_mapel' => $row['nama_mapel'],
        'tanggal' => $row['tanggal'],
        'durasi_menit' => (int)$row['durasi_menit'],
        'catatan' => !empty($row['catatan']) ? $row['catatan'] : 'Tanpa catatan'
    ];
}

echo json_encode([
    'status' => 'ok',
    'total' => count($data_riwayat),
    'data' => $data_riwayat
]);
