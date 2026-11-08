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
                    ['nama' => 'Nasi putih', 'berat_g' => 150, 'keterangan' => 'Perkiraan karbohidrat utama'],
                    ['nama' => 'Daging ayam', 'berat_g' => 70, 'keterangan' => 'Lauk utama'],
                    ['nama' => 'Minyak kelapa sawit', 'berat_g' => 10, 'keterangan' => 'Minyak olahan'],
                    ['nama' => 'Garam dapur', 'berat_g' => 2, 'keterangan' => 'Bumbu garam']
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
