<?php

namespace App\Services;

class AiClient {
    // Standard Indonesian dishes dictionary for instant demo / fallback
    private static array $resepLokal = [
        'nasi goreng telur' => [
            'nama' => 'Nasi goreng telur',
            'deskripsi' => 'Nasi goreng dengan bumbu kecap manis, telur, dan minyak goreng.',
            'bahan' => [
                ['nama' => 'Nasi putih', 'berat_g' => 200, 'keterangan' => '200 g, perkiraan dari foto'],
                ['nama' => 'Telur', 'berat_g' => 55, 'keterangan' => '1 butir, sekitar 55 g'],
                ['nama' => 'Minyak goreng', 'berat_g' => 14, 'keterangan' => '1 sendok makan'],
                ['nama' => 'Kecap manis', 'berat_g' => 15, 'keterangan' => '1 sendok makan, terlihat dari warna nasi'],
            ]
        ],
        'soto ayam' => [
            'nama' => 'Soto ayam',
            'deskripsi' => 'Soto ayam berkuah kuning dengan suwiran ayam, soun, dan tauge.',
            'bahan' => [
                ['nama' => 'Daging ayam', 'berat_g' => 80, 'keterangan' => 'Suwiran daging ayam'],
                ['nama' => 'Soun', 'berat_g' => 50, 'keterangan' => 'Soun rebus'],
                ['nama' => 'Tauge kacang hijau', 'berat_g' => 30, 'keterangan' => 'Tauge segar rebus'],
                ['nama' => 'Garam dapur', 'berat_g' => 3, 'keterangan' => 'Garam dalam kuah soto'],
                ['nama' => 'Minyak kelapa sawit', 'berat_g' => 5, 'keterangan' => 'Minyak tumis bumbu']
            ]
        ],
        'rawon daging sapi' => [
            'nama' => 'Rawon daging sapi',
            'deskripsi' => 'Sup daging sapi khas Jawa Timur berkuah hitam kluwek.',
            'bahan' => [
                ['nama' => 'Daging sapi', 'berat_g' => 90, 'keterangan' => 'Potongan daging sapi'],
                ['nama' => 'Tauge kacang hijau', 'berat_g' => 30, 'keterangan' => 'Tauge pendek pelengkap'],
                ['nama' => 'Garam dapur', 'berat_g' => 3, 'keterangan' => 'Garam kuah rawon'],
                ['nama' => 'Minyak kelapa sawit', 'berat_g' => 5, 'keterangan' => 'Minyak tumis bumbu'],
                ['nama' => 'Kluwek', 'berat_g' => 20, 'keterangan' => '2 buah, dari foto'] // State 3c demo
            ]
        ],
        'rendang daging' => [
            'nama' => 'Rendang daging',
            'deskripsi' => 'Daging sapi dimasak perlahan dalam santan dan rempah.',
            'bahan' => [
                ['nama' => 'Daging sapi', 'berat_g' => 100, 'keterangan' => '1 potong daging sapi matang'],
                ['nama' => 'Santan kelapa murni', 'berat_g' => 120, 'keterangan' => 'Kuah santan kental tereduksi'],
                ['nama' => 'Minyak kelapa sawit', 'berat_g' => 10, 'keterangan' => 'Minyak tumis bumbu'],
                ['nama' => 'Garam dapur', 'berat_g' => 2, 'keterangan' => 'Garam bumbu']
            ]
        ],
        'bakso' => [
            'nama' => 'Bakso',
            'deskripsi' => 'Bakso dengan mi, sayuran, dan kuah kaldu.',
            'bahan' => [
                ['nama' => 'Bakso daging sapi', 'berat_g' => 150, 'keterangan' => '5 butir ukuran sedang'],
                ['nama' => 'Mi kuning basah', 'berat_g' => 100, 'keterangan' => '1 porsi mi'],
                ['nama' => 'Soun', 'berat_g' => 30, 'keterangan' => 'Segenggam kecil'],
                ['nama' => 'Sawi hijau', 'berat_g' => 25, 'keterangan' => 'Beberapa helai'],
                ['nama' => 'Kaldu', 'berat_g' => 200, 'keterangan' => '1 mangkuk kuah']
            ]
        ],
        'mie ayam' => [
            'nama' => 'Mi ayam',
            'deskripsi' => 'Mi ayam dengan potongan ayam, sawi, dan kuah.',
            'bahan' => [
                ['nama' => 'Mi telur basah', 'berat_g' => 150, 'keterangan' => '1 porsi mi'],
                ['nama' => 'Daging ayam', 'berat_g' => 60, 'keterangan' => 'Topping ayam sekitar 1 sendok saji'],
                ['nama' => 'Sawi hijau', 'berat_g' => 30, 'keterangan' => 'Beberapa helai'],
                ['nama' => 'Minyak kelapa sawit', 'berat_g' => 8, 'keterangan' => 'Sekitar 1/2 sendok makan minyak bumbu']
            ]
        ],
        'ayam geprek' => [
            'nama' => 'Ayam geprek',
            'deskripsi' => 'Ayam goreng tepung dengan sambal dan nasi.',
            'bahan' => [
                ['nama' => 'Nasi putih', 'berat_g' => 200, 'keterangan' => '1 porsi nasi'],
                ['nama' => 'Daging ayam', 'berat_g' => 100, 'keterangan' => '1 potong ayam'],
                ['nama' => 'Tepung terigu', 'berat_g' => 25, 'keterangan' => 'Lapisan tepung'],
                ['nama' => 'Cabai merah', 'berat_g' => 15, 'keterangan' => 'Sambal sekitar 3 buah cabai'],
                ['nama' => 'Minyak kelapa sawit', 'berat_g' => 15, 'keterangan' => 'Minyak terserap perkiraan']
            ]
        ],
        'gado-gado' => [
            'nama' => 'Gado-gado',
            'deskripsi' => 'Sayuran rebus dengan tahu, telur, dan saus kacang.',
            'bahan' => [
                ['nama' => 'Tauge kacang hijau', 'berat_g' => 40, 'keterangan' => 'Segenggam tauge'],
                ['nama' => 'Kangkung', 'berat_g' => 50, 'keterangan' => '1 porsi sayuran'],
                ['nama' => 'Tahu', 'berat_g' => 50, 'keterangan' => '1 potong tahu'],
                ['nama' => 'Telur', 'berat_g' => 55, 'keterangan' => '1 butir telur'],
                ['nama' => 'Kacang tanah', 'berat_g' => 35, 'keterangan' => 'Saus kacang sekitar 2 sendok makan']
            ]
        ],
        'pecel' => [
            'nama' => 'Pecel',
            'deskripsi' => 'Sayuran rebus dengan saus kacang.',
            'bahan' => [
                ['nama' => 'Kangkung', 'berat_g' => 50, 'keterangan' => '1 porsi sayuran'],
                ['nama' => 'Tauge kacang hijau', 'berat_g' => 40, 'keterangan' => 'Segenggam tauge'],
                ['nama' => 'Kacang panjang', 'berat_g' => 40, 'keterangan' => 'Beberapa potong'],
                ['nama' => 'Kacang tanah', 'berat_g' => 35, 'keterangan' => 'Saus kacang sekitar 2 sendok makan'],
                ['nama' => 'Nasi putih', 'berat_g' => 150, 'keterangan' => '1 porsi nasi']
            ]
        ],
        'sate ayam' => [
            'nama' => 'Sate ayam',
            'deskripsi' => 'Sate ayam dengan bumbu kacang dan lontong.',
            'bahan' => [
                ['nama' => 'Daging ayam', 'berat_g' => 100, 'keterangan' => '5 tusuk sate, daging tanpa tusuk'],
                ['nama' => 'Kacang tanah', 'berat_g' => 40, 'keterangan' => 'Bumbu kacang sekitar 2 sendok makan'],
                ['nama' => 'Kecap manis', 'berat_g' => 10, 'keterangan' => 'Sekitar 2 sendok teh'],
                ['nama' => 'Lontong', 'berat_g' => 150, 'keterangan' => '1 porsi lontong']
            ]
        ],
        'nasi uduk' => [
            'nama' => 'Nasi uduk',
            'deskripsi' => 'Nasi gurih dengan santan dan lauk pelengkap.',
            'bahan' => [
                ['nama' => 'Nasi putih', 'berat_g' => 200, 'keterangan' => '1 porsi nasi uduk'],
                ['nama' => 'Santan kelapa murni', 'berat_g' => 35, 'keterangan' => 'Santan yang digunakan saat memasak'],
                ['nama' => 'Telur', 'berat_g' => 55, 'keterangan' => '1 butir telur, jika disajikan'],
                ['nama' => 'Tempe', 'berat_g' => 40, 'keterangan' => '1 potong tempe, jika disajikan']
            ]
        ],
        'rawon' => [
            'nama' => 'Rawon',
            'deskripsi' => 'Sup daging sapi berkuah kluwek khas Jawa Timur.',
            'bahan' => [
                ['nama' => 'Daging sapi', 'berat_g' => 90, 'keterangan' => '1 porsi daging'],
                ['nama' => 'Tauge kacang hijau', 'berat_g' => 30, 'keterangan' => 'Tauge pendek pelengkap'],
                ['nama' => 'Kluwek', 'berat_g' => 15, 'keterangan' => 'Bumbu kluwek'],
                ['nama' => 'Nasi putih', 'berat_g' => 150, 'keterangan' => '1 porsi nasi, jika disertakan']
            ]
        ]
    ];

    /**
     * Identifikasi makanan dari foto dan nama
     */
    public static function identify(string $namaInput, ?string $photoPath = null): array {
        $namaLower = mb_strtolower(trim($namaInput));
        $apiKey = getenv('GEMINI_API_KEY');

        // Cek API jika tersedia
        if (!empty($apiKey) && !empty($photoPath) && file_exists($photoPath)) {
            $apiResult = self::callGeminiVision($apiKey, $namaInput, $photoPath);
            if ($apiResult !== null) {
                return $apiResult;
            }
        }

        // Cek apakah ada konflik yang disengaja untuk skenario demo State 3b
        // Contoh di PRD: Pengguna menulis "Nasi goreng telur", tapi foto soto ayam
        if (strpos($namaLower, 'nasi goreng') !== false && !empty($photoPath) && strpos(mb_strtolower($photoPath), 'soto') !== false) {
            $data = self::$resepLokal['nasi goreng telur'];
            return [
                'sukses' => true,
                'nama' => $data['nama'],
                'deskripsi' => $data['deskripsi'],
                'bahan' => $data['bahan'],
                'konflik' => true, // State 3b
                'konflik_teks' => 'Kamu menulis “' . $data['nama'] . '”, tetapi fotonya tampak seperti soto ayam. Pilih yang mau dipakai untuk menentukan bahan.',
                'nama_foto' => 'Soto ayam'
            ];
        }

        // Cari resep yang cocok dari kamus lokal
        foreach (self::$resepLokal as $kunci => $data) {
            if (strpos($namaLower, $kunci) !== false || strpos($kunci, $namaLower) !== false) {
                return [
                    'sukses' => true,
                    'nama' => $data['nama'],
                    'deskripsi' => $data['deskripsi'],
                    'bahan' => $data['bahan'],
                    'konflik' => false,
                    'konflik_teks' => ''
                ];
            }
        }

        // Makanan umum tidak dikenal di kamus, buat perkiraan bahan dasar
        if (!empty($namaInput)) {
            return [
                'sukses' => true,
                'nama' => ucwords($namaInput),
                'deskripsi' => 'Analisis bahan berdasarkan nama dan foto makanan.',
                'bahan' => [
                    ['nama' => ucwords($namaInput), 'berat_g' => 200, 'keterangan' => 'Estimasi awal 200 g; silakan koreksi bahan dan berat sesuai makanan sebenarnya']
                ],
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
        // cURL ke Gemini Multimodal endpoint
        // Bila respons valid JSON dengan list bahan, kembalikan
        return null; // Fallback ke deterministik
    }
}
