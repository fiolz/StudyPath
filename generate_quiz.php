<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'env.php';

// Konfigurasi
$mode = $_GET['mode'] ?? 'teks';
$jumlah = isset($_GET['jumlah']) ? (int) $_GET['jumlah'] : 5;

// Validasi jumlah soal
$jumlah = max(1, min($jumlah, 20));

// API Key Groq dari env.php
$apiKey = GROQ_API_KEY;

// Endpoint Groq
$url = 'https://api.groq.com/openai/v1/chat/completions';

// Ambil materi berdasarkan mode input

if ($mode === 'foto') {
    // Web.PostFile mengirim data gambar mentah
    $rawInput = file_get_contents('php://input');

    if ($rawInput === false || $rawInput === '') {
        echo json_encode([
            'status' => 'error',
            'hasil' => 'Data foto kosong atau gagal dibaca.'
        ]);
        exit;
    }
} else {
    // Materi teks dikirim menggunakan Web.PostText
    $rawInput = file_get_contents('php://input');

    if ($rawInput === false || trim($rawInput) === '') {
        echo json_encode([
            'status' => 'error',
            'hasil' => 'Materi teks kosong.'
        ]);
        exit;
    }
}

// Instruksi format soal
// Ambil pilihan tingkat kesulitan dan format soal
$difficulty = $_GET['difficulty'] ?? 'Foundation (Mudah)';
$format = $_GET['format'] ?? 'Pilihan Ganda';

// Instruksi tingkat kesulitan
$difficultyInstructions = [
    'Foundation (Mudah)' =>
    'Buat soal tingkat dasar untuk menguji pemahaman konsep dan fakta penting. Gunakan pertanyaan yang jelas dan langsung.',

    'Analytical Thinking (Medium)' =>
    'Buat soal yang menguji penerapan konsep, analisis informasi, perbandingan, dan hubungan sebab-akibat. Siswa perlu berpikir sebelum menjawab.',

    'Critical Thinking (Sulit)' =>
    'Buat soal yang menuntut penalaran mendalam, analisis beberapa informasi, evaluasi, atau penyelesaian masalah bertahap. Hindari soal yang hanya menguji hafalan.'
];

// Instruksi format soal
$formatInstructions = [
    'Pilihan Ganda' =>
    'Setiap soal harus memiliki pilihan A, B, C, dan D. Pastikan hanya ada satu jawaban yang benar.',

    'Isian Singkat' =>
    'Setiap soal harus dijawab dengan kata, istilah, angka, rumus, atau kalimat singkat. Jangan memberikan pilihan A, B, C, dan D.',

    'Uraian' =>
    'Setiap soal harus meminta siswa menjelaskan alasan, proses, atau langkah penyelesaian. Jangan memberikan pilihan A, B, C, dan D.'
];

// Gunakan nilai default jika parameter tidak dikenal
if (!isset($difficultyInstructions[$difficulty])) {
    $difficulty = 'Foundation (Mudah)';
}

if (!isset($formatInstructions[$format])) {
    $format = 'Pilihan Ganda';
}

// Instruksi umum untuk AI
$htmlPrompt = "
Gunakan bahasa Indonesia yang jelas.
Format seluruh jawaban menggunakan HTML.
Gunakan <h1>, <h2>, <b>, <i>, dan tag HTML lain yang sesuai.
Jangan gunakan tag <ul> atau <li>.
Gunakan format Soal 1, Soal 2, dan seterusnya.
Letakkan teks soal pada baris setelah judul soal.
Jangan gunakan Markdown atau code block.
TINGKAT KESULITAN:
{$difficulty}
INSTRUKSI TINGKAT KESULITAN:
{$difficultyInstructions[$difficulty]}
FORMAT SOAL:
{$format}
INSTRUKSI FORMAT:
{$formatInstructions[$format]}
Buat tepat {$jumlah} soal.
Cantumkan kunci jawaban atau pedoman jawaban
di bagian paling akhir, terpisah dari daftar soal.
Pastikan jawaban sesuai dengan materi yang diberikan.
1. Keluaran harus berupa HTML yang dapat langsung ditampilkan di aplikasi.
2. Tulis seluruh soal terlebih dahulu, sesuai jumlah soal yang diminta.
3. Setelah semua soal selesai, tulis kunci jawaban tepat satu kali di bagian paling akhir.
4. Gunakan penanda judul kunci jawaban berikut secara PERSIS, tanpa perubahan karakter, spasi, atau tanda baca:
   <h2>Kunci Jawaban </h2>
5. Jangan menggunakan variasi judul seperti:
   <h2>Kunci Jawaban</h2>
   <h2>Kunci Jawaban:</h2>
   <h3>Kunci Jawaban </h3>
6. Jangan menulis kunci jawaban sebelum penanda tersebut.
8. Pastikan setiap soal memiliki nomor dan seluruh kunci jawaban sesuai dengan soal yang dibuat.
Gunakan bahasa Indonesia yang ramah, natural, dan suportif. Hindari bahasa laporan formal. Tetap objektif dan jelaskan kesalahan dengan jelas tanpa menghakimi.
";

// Membuat prompt berdasarkan mode input
if ($mode === 'foto') {

    // Ubah data gambar menjadi Base64
    $imageData = base64_encode($rawInput);

    $prompt = "
Analisis gambar catatan atau materi pelajaran yang diberikan.

Buat soal berdasarkan informasi yang benar-benar terbaca
pada gambar.

Jika ada bagian gambar yang tidak terbaca,
jangan mengarang informasi dari bagian tersebut.

Jika materinya matematika, buat juga soal berdasarkan
rumus yang terdapat pada materi.

{$htmlPrompt}
";

    $messageContent = [
        [
            'type' => 'text',
            'text' => $prompt
        ],
        [
            'type' => 'image_url',
            'image_url' => [
                'url' => 'data:image/jpeg;base64,' . $imageData
            ]
        ]
    ];
} else {

    $prompt = "
Berdasarkan materi berikut:

{$rawInput}

Buat soal berdasarkan materi tersebut.

Jangan membuat soal yang tidak berkaitan dengan materi.

Jika materinya matematika, buat juga soal berdasarkan
rumus yang terdapat pada materi.

{$htmlPrompt}
";

    $messageContent = $prompt;
}



// Payload untuk Groq Chat Completions API
$payload = [
    'model' => 'qwen/qwen3.8-27b',
    'messages' => [
        [
            'role' => 'user',
            'content' => $messageContent
        ]
    ],
    'temperature' => 0.7,
    'max_completion_tokens' => 2048,
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

// Jika koneksi cURL gagal
if ($response === false) {
    echo json_encode([
        'status' => 'ok',
        'hasil' => '<b>cURL Error:</b> '
            . htmlspecialchars($curlErr, ENT_QUOTES, 'UTF-8')
    ]);
    exit;
}

// Baca respons dari Groq
$result = json_decode($response, true);

// Jika respons bukan JSON valid
if (!is_array($result)) {
    echo json_encode([
        'status' => 'ok',
        'hasil' => '<b>Error:</b> Respons dari Groq tidak valid.'
    ]);
    exit;
}

// Jika Groq mengembalikan error
if ($httpCode < 200 || $httpCode >= 300 || isset($result['error'])) {

    $errorMessage = $result['error']['message']
        ?? 'Permintaan AI gagal. Periksa konfigurasi API dan model.';

    echo json_encode([
        'status' => 'ok',
        'hasil' => '<b>Groq Error:</b> '
            . htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8')
            . '<br><small>HTTP Status: '
            . (int) $httpCode
            . '</small>'
    ]);

    exit;
}

// Ambil teks hasil AI
$hasil_ai = $result['choices'][0]['message']['content'] ?? null;

// Kirim hasil ke MIT App Inventor
if (is_string($hasil_ai) && trim($hasil_ai) !== '') {

    echo json_encode([
        'status' => 'ok',
        'hasil' => $hasil_ai
    ], JSON_UNESCAPED_UNICODE);
} else {

    echo json_encode([
        'status' => 'ok',
        'hasil' => '<b>Error:</b> Groq tidak memberikan hasil soal.'
    ], JSON_UNESCAPED_UNICODE);
}
