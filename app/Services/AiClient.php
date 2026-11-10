<?php

namespace App\Services;

class AiClient {
    private const DAFTAR_BAHAN = [
        'Air', 'Nasi putih', 'Ketupat ketan', 'Mi basah', 'Mi kering', 'Bihun',
        'Makaroni', 'Tepung terigu', 'Kentang', 'Telur ayam', 'Daging ayam', 'Daging sapi',
        'Daging kambing', 'Hati sapi', 'Bakso', 'Udang segar', 'Cumi-cumi segar', 'Ikan kembung',
        'Ikan tongkol segar', 'Ikan bandeng', 'Ikan mas', 'Ikan lele goreng', 'Tahu', 'Tempe',
        'Oncom', 'Wortel', 'Kol', 'Sawi', 'Bayam', 'Kangkung',
        'Buncis', 'Kacang panjang', 'Labu siam', 'Terong', 'Tomat', 'Mentimun',
        'Selada', 'Tauge', 'Jagung muda', 'Daun singkong', 'Nangka muda', 'Melinjo',
        'Petai', 'Jamur tiram segar', 'Daun bawang', 'Seledri', 'Bawang merah', 'Bawang putih',
        'Bawang bombay', 'Cabai merah', 'Kemiri', 'Jahe', 'Kunyit', 'Jeruk nipis',
        'Kluwek', 'Santan', 'Minyak goreng', 'Mentega', 'Margarin', 'Keju',
        'Susu sapi', 'Susu kental manis', 'Kecap manis', 'Sambal terasi', 'Garam dapur', 'Gula pasir',
        'Gula aren', 'Kacang tanah', 'Kacang hijau', 'Kacang merah', 'Kerupuk udang goreng',
        'Dada ayam tanpa kulit', 'Serai', 'Lengkuas', 'Ketumbar', 'Jintan', 'Asam jawa', 'Saus tiram', 'Kecap ikan', 'Kaldu bubuk ayam',
    ];

    private static array $resepLokal = [
        'sayur sop' => [
            'nama' => 'Sayur sop',
            'bahan' => [
                ['nama' => 'Wortel', 'berat_g' => 40, 'keterangan' => 'potongan wortel'],
                ['nama' => 'Kentang', 'berat_g' => 50, 'keterangan' => 'potongan kentang'],
                ['nama' => 'Kol', 'berat_g' => 40, 'keterangan' => 'potongan kol'],
                ['nama' => 'Buncis', 'berat_g' => 20, 'keterangan' => 'beberapa batang'],
                ['nama' => 'Makaroni', 'berat_g' => 15, 'keterangan' => 'makaroni kering'],
                ['nama' => 'Daun bawang', 'berat_g' => 5, 'keterangan' => 'irisan'],
                ['nama' => 'Seledri', 'berat_g' => 3, 'keterangan' => 'irisan'],
                ['nama' => 'Bawang putih', 'berat_g' => 5, 'keterangan' => 'bumbu'],
                ['nama' => 'Garam dapur', 'berat_g' => 2, 'keterangan' => 'garam kuah'],
            ]
        ],
        'sayur asem' => [
            'nama' => 'Sayur asem',
            'bahan' => [
                ['nama' => 'Labu siam', 'berat_g' => 40, 'keterangan' => 'potongan labu siam'],
                ['nama' => 'Kacang panjang', 'berat_g' => 25, 'keterangan' => 'potongan'],
                ['nama' => 'Jagung muda', 'berat_g' => 30, 'keterangan' => 'potongan jagung'],
                ['nama' => 'Melinjo', 'berat_g' => 15, 'keterangan' => 'buah dan daun melinjo'],
                ['nama' => 'Kacang tanah', 'berat_g' => 10, 'keterangan' => 'kacang tanah'],
                ['nama' => 'Gula aren', 'berat_g' => 5, 'keterangan' => 'gula merah'],
                ['nama' => 'Garam dapur', 'berat_g' => 2, 'keterangan' => 'garam kuah'],
            ]
        ],
        'nasi goreng' => [
            'nama' => 'Nasi goreng telur',
            'bahan' => [
                ['nama' => 'Nasi putih', 'berat_g' => 200, 'keterangan' => '1 piring nasi'],
                ['nama' => 'Telur ayam', 'berat_g' => 55, 'keterangan' => '1 butir'],
                ['nama' => 'Minyak goreng', 'berat_g' => 14, 'keterangan' => '1 sendok makan'],
                ['nama' => 'Kecap manis', 'berat_g' => 15, 'keterangan' => '1 sendok makan'],
                ['nama' => 'Bawang merah', 'berat_g' => 10, 'keterangan' => 'bumbu'],
                ['nama' => 'Garam dapur', 'berat_g' => 1, 'keterangan' => 'sejumput'],
            ]
        ],
        'mie goreng' => [
            'nama' => 'Mi goreng',
            'bahan' => [
                ['nama' => 'Mi basah', 'berat_g' => 150, 'keterangan' => '1 porsi mi'],
                ['nama' => 'Telur ayam', 'berat_g' => 55, 'keterangan' => '1 butir'],
                ['nama' => 'Sawi', 'berat_g' => 30, 'keterangan' => 'beberapa helai'],
                ['nama' => 'Kecap manis', 'berat_g' => 15, 'keterangan' => '1 sendok makan'],
                ['nama' => 'Minyak goreng', 'berat_g' => 10, 'keterangan' => 'minyak tumis'],
                ['nama' => 'Bawang putih', 'berat_g' => 5, 'keterangan' => 'bumbu'],
            ]
        ],
        'soto ayam' => [
            'nama' => 'Soto ayam',
            'bahan' => [
                ['nama' => 'Daging ayam', 'berat_g' => 80, 'keterangan' => 'suwiran ayam'],
                ['nama' => 'Bihun', 'berat_g' => 30, 'keterangan' => 'bihun/soun'],
                ['nama' => 'Tauge', 'berat_g' => 30, 'keterangan' => 'tauge'],
                ['nama' => 'Telur ayam', 'berat_g' => 25, 'keterangan' => 'setengah butir'],
                ['nama' => 'Kunyit', 'berat_g' => 3, 'keterangan' => 'bumbu kuah kuning'],
                ['nama' => 'Minyak goreng', 'berat_g' => 5, 'keterangan' => 'minyak tumis bumbu'],
                ['nama' => 'Seledri', 'berat_g' => 3, 'keterangan' => 'irisan'],
                ['nama' => 'Garam dapur', 'berat_g' => 3, 'keterangan' => 'garam kuah'],
            ]
        ],
        'rawon' => [
            'nama' => 'Rawon',
            'bahan' => [
                ['nama' => 'Daging sapi', 'berat_g' => 90, 'keterangan' => 'potongan daging'],
                ['nama' => 'Kluwek', 'berat_g' => 15, 'keterangan' => 'bumbu kluwek'],
                ['nama' => 'Tauge', 'berat_g' => 30, 'keterangan' => 'tauge pendek'],
                ['nama' => 'Nasi putih', 'berat_g' => 150, 'keterangan' => '1 porsi nasi'],
                ['nama' => 'Minyak goreng', 'berat_g' => 5, 'keterangan' => 'minyak tumis bumbu'],
                ['nama' => 'Garam dapur', 'berat_g' => 3, 'keterangan' => 'garam kuah'],
            ]
        ],
        'rendang' => [
            'nama' => 'Rendang daging',
            'bahan' => [
                ['nama' => 'Daging sapi', 'berat_g' => 100, 'keterangan' => '1 potong daging'],
                ['nama' => 'Santan', 'berat_g' => 120, 'keterangan' => 'kuah santan kental'],
                ['nama' => 'Minyak goreng', 'berat_g' => 10, 'keterangan' => 'minyak tumis bumbu'],
                ['nama' => 'Garam dapur', 'berat_g' => 2, 'keterangan' => 'garam bumbu'],
            ]
        ],
        'bakso' => [
            'nama' => 'Bakso',
            'bahan' => [
                ['nama' => 'Bakso', 'berat_g' => 150, 'keterangan' => '5 butir sedang'],
                ['nama' => 'Mi basah', 'berat_g' => 75, 'keterangan' => 'mi kuning'],
                ['nama' => 'Bihun', 'berat_g' => 25, 'keterangan' => 'bihun/soun'],
                ['nama' => 'Sawi', 'berat_g' => 25, 'keterangan' => 'beberapa helai'],
                ['nama' => 'Seledri', 'berat_g' => 2, 'keterangan' => 'irisan'],
                ['nama' => 'Garam dapur', 'berat_g' => 2, 'keterangan' => 'garam kuah'],
            ]
        ],
        'mie ayam' => [
            'nama' => 'Mi ayam',
            'bahan' => [
                ['nama' => 'Mi basah', 'berat_g' => 150, 'keterangan' => '1 porsi mi'],
                ['nama' => 'Daging ayam', 'berat_g' => 60, 'keterangan' => 'topping ayam'],
                ['nama' => 'Sawi', 'berat_g' => 30, 'keterangan' => 'beberapa helai'],
                ['nama' => 'Kecap manis', 'berat_g' => 10, 'keterangan' => 'bumbu ayam'],
                ['nama' => 'Minyak goreng', 'berat_g' => 8, 'keterangan' => 'minyak bumbu'],
            ]
        ],
        'ayam geprek' => [
            'nama' => 'Ayam geprek',
            'bahan' => [
                ['nama' => 'Nasi putih', 'berat_g' => 200, 'keterangan' => '1 porsi nasi'],
                ['nama' => 'Daging ayam', 'berat_g' => 100, 'keterangan' => '1 potong ayam'],
                ['nama' => 'Tepung terigu', 'berat_g' => 25, 'keterangan' => 'lapisan tepung'],
                ['nama' => 'Cabai merah', 'berat_g' => 15, 'keterangan' => 'sambal'],
                ['nama' => 'Minyak goreng', 'berat_g' => 15, 'keterangan' => 'minyak terserap'],
            ]
        ],
        'gado-gado' => [
            'nama' => 'Gado-gado',
            'bahan' => [
                ['nama' => 'Tauge', 'berat_g' => 40, 'keterangan' => 'segenggam'],
                ['nama' => 'Kangkung', 'berat_g' => 50, 'keterangan' => 'sayur rebus'],
                ['nama' => 'Kentang', 'berat_g' => 40, 'keterangan' => 'kentang rebus'],
                ['nama' => 'Tahu', 'berat_g' => 50, 'keterangan' => '1 potong'],
                ['nama' => 'Telur ayam', 'berat_g' => 55, 'keterangan' => '1 butir'],
                ['nama' => 'Kacang tanah', 'berat_g' => 35, 'keterangan' => 'saus kacang'],
                ['nama' => 'Kerupuk', 'berat_g' => 10, 'keterangan' => 'kerupuk'],
            ]
        ],
        'pecel lele' => [
            'nama' => 'Pecel lele',
            'bahan' => [
                ['nama' => 'Ikan lele goreng', 'berat_g' => 100, 'keterangan' => '1 ekor'],
                ['nama' => 'Nasi putih', 'berat_g' => 150, 'keterangan' => '1 porsi nasi'],
                ['nama' => 'Sambal terasi', 'berat_g' => 20, 'keterangan' => 'sambal'],
                ['nama' => 'Mentimun', 'berat_g' => 30, 'keterangan' => 'lalapan'],
            ]
        ],
        'pecel' => [
            'nama' => 'Pecel',
            'bahan' => [
                ['nama' => 'Kangkung', 'berat_g' => 50, 'keterangan' => 'sayur rebus'],
                ['nama' => 'Tauge', 'berat_g' => 40, 'keterangan' => 'segenggam'],
                ['nama' => 'Kacang panjang', 'berat_g' => 40, 'keterangan' => 'beberapa potong'],
                ['nama' => 'Kacang tanah', 'berat_g' => 35, 'keterangan' => 'bumbu pecel'],
                ['nama' => 'Nasi putih', 'berat_g' => 150, 'keterangan' => '1 porsi nasi'],
            ]
        ],
        'sate ayam' => [
            'nama' => 'Sate ayam',
            'bahan' => [
                ['nama' => 'Daging ayam', 'berat_g' => 100, 'keterangan' => '5 tusuk'],
                ['nama' => 'Kacang tanah', 'berat_g' => 40, 'keterangan' => 'bumbu kacang'],
                ['nama' => 'Kecap manis', 'berat_g' => 10, 'keterangan' => '2 sendok teh'],
                ['nama' => 'Nasi putih', 'berat_g' => 150, 'keterangan' => 'lontong, dihitung seperti nasi'],
            ]
        ],
        'nasi uduk' => [
            'nama' => 'Nasi uduk',
            'bahan' => [
                ['nama' => 'Nasi putih', 'berat_g' => 200, 'keterangan' => '1 porsi nasi'],
                ['nama' => 'Santan', 'berat_g' => 35, 'keterangan' => 'santan untuk menanak'],
                ['nama' => 'Telur ayam', 'berat_g' => 55, 'keterangan' => '1 butir'],
                ['nama' => 'Tempe', 'berat_g' => 40, 'keterangan' => '1 potong tempe goreng'],
                ['nama' => 'Sambal terasi', 'berat_g' => 15, 'keterangan' => 'sambal'],
            ]
        ],
        'opor ayam' => [
            'nama' => 'Opor ayam',
            'bahan' => [
                ['nama' => 'Daging ayam', 'berat_g' => 100, 'keterangan' => '1 potong ayam'],
                ['nama' => 'Santan', 'berat_g' => 100, 'keterangan' => 'kuah santan'],
                ['nama' => 'Bawang merah', 'berat_g' => 10, 'keterangan' => 'bumbu'],
                ['nama' => 'Bawang putih', 'berat_g' => 5, 'keterangan' => 'bumbu'],
                ['nama' => 'Kemiri', 'berat_g' => 5, 'keterangan' => 'bumbu'],
                ['nama' => 'Garam dapur', 'berat_g' => 2, 'keterangan' => 'garam kuah'],
            ]
        ],
        'capcay' => [
            'nama' => 'Capcay',
            'bahan' => [
                ['nama' => 'Wortel', 'berat_g' => 30, 'keterangan' => 'irisan'],
                ['nama' => 'Kol', 'berat_g' => 40, 'keterangan' => 'potongan kol'],
                ['nama' => 'Sawi', 'berat_g' => 40, 'keterangan' => 'sawi'],
                ['nama' => 'Udang segar', 'berat_g' => 30, 'keterangan' => 'beberapa ekor'],
                ['nama' => 'Bawang putih', 'berat_g' => 5, 'keterangan' => 'bumbu'],
                ['nama' => 'Minyak goreng', 'berat_g' => 7, 'keterangan' => 'minyak tumis'],
                ['nama' => 'Garam dapur', 'berat_g' => 2, 'keterangan' => 'garam'],
            ]
        ],
        'tumis kangkung' => [
            'nama' => 'Tumis kangkung',
            'bahan' => [
                ['nama' => 'Kangkung', 'berat_g' => 100, 'keterangan' => '1 ikat kecil'],
                ['nama' => 'Bawang putih', 'berat_g' => 5, 'keterangan' => 'bumbu'],
                ['nama' => 'Cabai merah', 'berat_g' => 5, 'keterangan' => 'irisan'],
                ['nama' => 'Minyak goreng', 'berat_g' => 7, 'keterangan' => 'minyak tumis'],
                ['nama' => 'Garam dapur', 'berat_g' => 1, 'keterangan' => 'sejumput'],
            ]
        ],
        'ketoprak' => [
            'nama' => 'Ketoprak',
            'bahan' => [
                ['nama' => 'Tahu', 'berat_g' => 60, 'keterangan' => 'tahu goreng'],
                ['nama' => 'Bihun', 'berat_g' => 40, 'keterangan' => 'bihun'],
                ['nama' => 'Tauge', 'berat_g' => 30, 'keterangan' => 'tauge'],
                ['nama' => 'Kacang tanah', 'berat_g' => 35, 'keterangan' => 'bumbu kacang'],
                ['nama' => 'Kecap manis', 'berat_g' => 10, 'keterangan' => 'kecap'],
                ['nama' => 'Nasi putih', 'berat_g' => 100, 'keterangan' => 'lontong, dihitung seperti nasi'],
            ]
        ],
    ];

    public static function identify(string $namaInput, ?string $photoPath = null): array {
        $namaLower = mb_strtolower(trim($namaInput));
        $apiKey = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? ($_SERVER['GEMINI_API_KEY'] ?? ''));

        $fullPhotoPath = null;
        if (!empty($photoPath)) {
            $uploadDir = realpath(__DIR__ . '/../../public/assets/uploads');
            $kandidat = realpath(__DIR__ . '/../../public/' . ltrim($photoPath, '/'));
            if ($uploadDir && $kandidat && strpos($kandidat, $uploadDir . DIRECTORY_SEPARATOR) === 0 && is_file($kandidat)) {
                $fullPhotoPath = $kandidat;
            }
        }

        if (!empty($apiKey) && !empty($fullPhotoPath)) {
            $apiResult = self::callGeminiVision($apiKey, $namaInput, $fullPhotoPath);
            if ($apiResult !== null && !empty($apiResult['bahan'])) {
                return $apiResult;
            }
        }

        $resep = self::$resepLokal;
        uksort($resep, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));
        $namaCari = preg_replace('/(^|\s)mi(\s|$)/u', '$1mie$2', $namaLower);
        if (isset($resep[$namaCari])) {
            $resep = [$namaCari => $resep[$namaCari]] + $resep;
        }
        foreach ($resep as $kunci => $data) {
            if ($namaLower !== '' && (strpos($namaCari, $kunci) !== false || (mb_strlen($namaLower) >= 4 && strpos($kunci, $namaLower) !== false))) {
                return [
                    'sukses' => true,
                    'nama' => $data['nama'],
                    'deskripsi' => 'Bahan dari resep contoh, bukan dari foto. Periksa dan sesuaikan.',
                    'bahan' => $data['bahan'],
                    'ukuran_porsi' => PortionSize::bawaan(),
                    'sumber_analisis' => 'contoh',
                    'konflik' => false,
                    'konflik_teks' => ''
                ];
            }
        }

        if (!empty($namaInput)) {
            return [
                'sukses' => true,
                'nama' => ucwords($namaInput),
                'deskripsi' => 'Bahan belum bisa ditebak otomatis. Tambah atau koreksi bahan di bawah.',
                'bahan' => [
                    ['nama' => ucwords($namaInput), 'berat_g' => 200, 'keterangan' => 'Perkiraan awal 200 g']
                ],
                'ukuran_porsi' => PortionSize::bawaan(),
                'sumber_analisis' => 'contoh',
                'konflik' => false,
                'konflik_teks' => ''
            ];
        }

        return [
            'sukses' => false,
            'pesan' => 'Makanan tidak dikenali'
        ];
    }

    private static function callGeminiVision(string $apiKey, string $nama, string $photoPath): ?array {
        $fileData = @file_get_contents($photoPath);
        if ($fileData === false || strlen($fileData) === 0) {
            return null;
        }

        $mimeType = mime_content_type($photoPath);
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        $daftarBahan = implode(', ', self::DAFTAR_BAHAN);
        $namaAman = str_replace(['"', "\n", "\r"], ["'", ' ', ' '], mb_substr($nama, 0, 100));

        $prompt = <<<PROMPT
Kamu membantu aplikasi CekPiring mengenali makanan Indonesia dari foto.
Nama makanan yang ditulis pengguna: "{$namaAman}".

Tugas:
1. Kenali hidangan di foto. Isi "nama" dengan nama hidangan yang TERLIHAT di foto. Bila nama dari pengguna jelas berbeda dengan isi foto (misalnya menulis "Sate ayam" padahal fotonya sayur sop), isi "konflik": true, "nama_foto" dengan nama hidangan di foto, dan "konflik_teks" satu kalimat ramah yang menyebut keduanya. Bila sesuai atau mirip, "konflik": false.
2. Tebak ukuran porsi di foto: "kecil", "sedang", atau "besar" (porsi sedang = satu piring makan biasa, sekitar 350 g total). Isi "alasan_porsi" maksimal 12 kata, berdasarkan yang terlihat (misalnya "nasi menutupi setengah piring").
3. Bahan SELALU berdasarkan isi foto, bukan nama ketikan. Sebutkan bahan utama dan bumbu yang berpengaruh (minyak, santan, kecap, garam, gula), maksimal 12 bahan. Perkirakan gram setiap bahan sesuai ukuran porsi yang kamu tebak. Cairan: 1 ml dianggap 1 g.
4. Untuk nama bahan, PILIH dari daftar ini bila cocok (data gizinya tersedia): {$daftarBahan}. Tulis persis seperti di daftar. Bila bahan tidak ada di daftar, pakai nama umum Indonesia yang singkat. Kuah bening dari air ditulis "Air"; kaldu instan ditulis "Kaldu bubuk ayam" (takaran kecil, 1-3 g). Daun aromatik yang tidak dimakan atau tidak punya data (daun jeruk, daun pandan, daun salam, kencur, taburan di bawah 3 g) TIDAK perlu ditulis sebagai bahan.
5. JANGAN menyebut kalori atau angka gizi apa pun. Angka gizi dihitung aplikasi dari tabel resmi.

Jawab HANYA dengan JSON valid:
{
  "nama": "Nama hidangan",
  "deskripsi": "Satu kalimat singkat",
  "ukuran_porsi": "sedang",
  "alasan_porsi": "nasi sekitar satu centong, lauk satu potong",
  "konflik": false,
  "konflik_teks": "",
  "nama_foto": "",
  "bahan": [
    {"nama": "Nasi putih", "berat_g": 150, "keterangan": "1 centong"}
  ]
}
PROMPT;

        $requestBody = [
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                    ['inline_data' => ['mime_type' => $mimeType, 'data' => base64_encode($fileData)]],
                ],
            ]],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'temperature' => 0.2,
            ],
        ];

        $primaryModel = getenv('GEMINI_MODEL') ?: ($_ENV['GEMINI_MODEL'] ?? '');
        $modelsToTry = array_values(array_unique(array_filter([
            $primaryModel,
            'gemini-3-flash-preview',
            'gemini-3.1-flash-lite',
        ])));

        $response = null;
        $httpCode = 0;

        foreach ($modelsToTry as $currentModel) {
            if (!preg_match('/^[a-z0-9.\-]+$/i', $currentModel)) {
                continue;
            }
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$currentModel}:generateContent";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $apiKey,
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($ch, CURLOPT_TIMEOUT, 25);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            if (defined('CURLSSLOPT_NATIVE_CA')) {
                curl_setopt($ch, CURLOPT_SSL_OPTIONS, CURLSSLOPT_NATIVE_CA);
            }
            $caBundle = getenv('CURL_CA_BUNDLE') ?: '';
            if ($caBundle !== '' && is_file($caBundle)) {
                curl_setopt($ch, CURLOPT_CAINFO, $caBundle);
            }

            $response = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200 && !empty($response)) {
                break;
            }
            error_log("CekPiring - Gemini [{$currentModel}] HTTP {$httpCode}: " . ($curlErr ?: substr((string)$response, 0, 200)));
        }

        if ($httpCode !== 200 || empty($response)) {
            return null;
        }

        $responseData = json_decode($response, true);
        $rawText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (empty($rawText)) {
            return null;
        }

        $cleanJson = trim($rawText);
        $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', $cleanJson);
        $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);

        $parsed = json_decode($cleanJson, true);
        if (!is_array($parsed) || empty($parsed['bahan']) || !is_array($parsed['bahan'])) {
            return null;
        }

        $bahan = [];
        foreach (array_slice($parsed['bahan'], 0, 15) as $b) {
            if (!is_array($b)) {
                continue;
            }
            $namaBahan = trim(strip_tags((string)($b['nama'] ?? '')));
            if ($namaBahan === '') {
                continue;
            }
            $berat = (float)($b['berat_g'] ?? 0);
            $berat = max(1, min(1000, $berat > 0 ? $berat : 50));
            $bahan[] = [
                'nama' => mb_substr($namaBahan, 0, 80),
                'berat_g' => round($berat),
                'keterangan' => mb_substr(trim(strip_tags((string)($b['keterangan'] ?? ''))), 0, 80),
            ];
        }
        if (empty($bahan)) {
            return null;
        }

        return [
            'sukses' => true,
            'nama' => mb_substr(trim(strip_tags((string)($parsed['nama'] ?? ''))), 0, 80) ?: ucwords($nama),
            'deskripsi' => mb_substr(trim(strip_tags((string)($parsed['deskripsi'] ?? ''))), 0, 160),
            'bahan' => $bahan,
            'ukuran_porsi' => PortionSize::dariAi($parsed['ukuran_porsi'] ?? null, $parsed['alasan_porsi'] ?? ''),
            'sumber_analisis' => 'ai',
            'konflik' => !empty($parsed['konflik']),
            'konflik_teks' => mb_substr(trim(strip_tags((string)($parsed['konflik_teks'] ?? ''))), 0, 200),
            'nama_foto' => mb_substr(trim(strip_tags((string)($parsed['nama_foto'] ?? ''))), 0, 80),
        ];
    }
}
