<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'env.php';

$apiKey = GROQ_API_KEY;
$url = 'https://api.groq.com/openai/v1/chat/completions';

// Terima data JSON dari MIT App Inventor
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!is_array($data)) {
    echo json_encode([
        'status' => 'error',
        'hasil' => 'Data permintaan tidak valid.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$soal = trim($data['soal'] ?? '');
$jawaban = trim($data['jawaban'] ?? '');

if ($soal === '' || $jawaban === '') {
    echo json_encode([
        'status' => 'error',
        'hasil' => 'Soal atau jawaban masih kosong.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Instruksi evaluasi
$prompt = "
Kamu adalah evaluator kuis pendidikan.

Berikut soal beserta kunci jawaban yang dihasilkan untuk kuis:

{$soal}

Berikut jawaban yang diberikan siswa:

{$jawaban}

Tugasmu:
1. Periksa jawaban siswa berdasarkan kunci dan konsep yang benar.
2. Berikan skor keseluruhan dari 0 sampai 100.
3. Tampilkan hasil setiap nomor: benar, sebagian benar, atau salah.
4. Jelaskan alasan penilaian dengan bahasa Indonesia yang jelas.
5. Untuk jawaban salah, berikan jawaban yang benar dan penjelasannya.
6. Terima jawaban yang berbeda susunan katanya jika maknanya benar.
7. Jangan memberikan nilai penuh jika jawaban tidak menjawab inti pertanyaan.
8. Jika jawaban suatu nomor tidak diberikan, tandai sebagai belum dijawab.
9. Jangan mengarang soal atau kunci yang tidak tersedia.
10. Gunakan bahasa Indonesia yang ramah, natural, dan suportif. Hindari bahasa laporan formal. Tetap objektif dan jelaskan kesalahan dengan jelas tanpa menghakimi.

Gunakan HTML sederhana.
Jangan gunakan Markdown atau code block.
Awali hasil dengan judul Hasil Evaluasi dan skor keseluruhan.
";

// Payload Groq
$payload = [
    'model' => 'qwen/qwen3.8-27b',
    'messages' => [
        [
            'role' => 'user',
            'content' => $prompt
        ]
    ],
    'temperature' => 0.2,
    'max_completion_tokens' => 1000,
    'stream' => false
];

// Kirim permintaan ke Groq
$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE
    ),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ],
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 120
]);

$response = curl_exec($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

// Periksa kegagalan koneksi
if ($response === false) {
    echo json_encode([
        'status' => 'error',
        'hasil' => 'Koneksi AI gagal: ' . $curlErr
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Baca respons Groq
$result = json_decode($response, true);

if (
    !is_array($result) ||
    $httpCode < 200 ||
    $httpCode >= 300 ||
    isset($result['error'])
) {
    echo json_encode([
        'status' => 'error',
        'hasil' => 'AI gagal mengevaluasi jawaban. Silakan coba lagi.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Ambil hasil evaluasi
$hasilAI = $result['choices'][0]['message']['content'] ?? '';

if (trim($hasilAI) === '') {
    echo json_encode([
        'status' => 'error',
        'hasil' => 'AI tidak memberikan hasil evaluasi.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Kirim hasil ke App Inventor
echo json_encode([
    'status' => 'ok',
    'hasil' => $hasilAI
], JSON_UNESCAPED_UNICODE);
