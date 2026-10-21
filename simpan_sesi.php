<?php
require 'config.php';

$user_id = $in['user_id'] ?? '';
$nama_mapel = trim($in['nama_mapel'] ?? 'Umum');
$durasi = $in['durasi_menit'] ?? 0;
$catatan = trim($in['catatan'] ?? ''); // Tangkap catatan dari App Inventor
$tanggal = date('Y-m-d');

if (empty($user_id) || $durasi <= 0) {
    echo json_encode(['status' => 'error', 'pesan' => 'Data tidak valid.']);
    exit;
}

// 1. Cek apakah mapel sudah ada, kalau belum buat baru
$st_mapel = $db->prepare("SELECT id FROM mapel WHERE user_id = ? AND nama_mapel = ?");
$st_mapel->bind_param('is', $user_id, $nama_mapel);
$st_mapel->execute();
$res_mapel = $st_mapel->get_result()->fetch_assoc();

if ($res_mapel) {
    $mapel_id = $res_mapel['id'];
} else {
    $st_ins = $db->prepare("INSERT INTO mapel (user_id, nama_mapel, tanggal_ujian) VALUES (?, ?, ?)");
    $st_ins->bind_param('iss', $user_id, $nama_mapel, $tanggal);
    $st_ins->execute();
    $mapel_id = $st_ins->insert_id;
}

// 2. Simpan ke tabel sesi beserta catatannya
$st = $db->prepare("INSERT INTO sesi (user_id, mapel_id, tanggal, durasi_menit, catatan) VALUES (?, ?, ?, ?, ?)");
$st->bind_param('iisis', $user_id, $mapel_id, $tanggal, $durasi, $catatan);

if ($st->execute()) {
    echo json_encode(['status' => 'ok', 'pesan' => 'Sesi belajar berhasil disimpan!']);
} else {
    echo json_encode(['status' => 'error', 'pesan' => 'Gagal menyimpan sesi.']);
}
