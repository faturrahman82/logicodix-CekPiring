<?php

namespace App\Models;

use CodeIgniter\Model;

class NutritionModel extends Model
{
    protected string $csvPath;

    public function __construct()
    {
        parent::__construct();
        // Path CSV di public atau root proyek
        $this->csvPath = FCPATH . 'nutrition_bersih.csv';
        if (!file_exists($this->csvPath)) {
            $this->csvPath = ROOTPATH . 'nutrition_bersih.csv';
        }
    }

    /**
     * Membaca seluruh data dari nutrition_bersih.csv
     */
    public function getAllNutrition(): array
    {
        if (!file_exists($this->csvPath)) {
            return [];
        }

        $data = [];
        if (($handle = fopen($this->csvPath, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ',');
            if ($header) {
                // Hapus BOM UTF-8 jika ada pada kolom pertama
                $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
                $header = array_map('trim', $header);
            }

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (!empty($row[0])) {
                    $rowTrimmed = array_map('trim', $row);
                    if ($header && count($header) === count($rowTrimmed)) {
                        $item = array_combine($header, $rowTrimmed);
                    } else {
                        $item = [
                            'name'     => $row[0] ?? '',
                            'calories' => (float)($row[1] ?? 0),
                            'proteins' => (float)($row[2] ?? 0),
                            'fat'      => (float)($row[3] ?? 0),
                            'carbohydrate' => (float)($row[4] ?? 0),
                        ];
                    }
                    $data[] = $item;
                }
            }
            fclose($handle);
        }

        return $data;
    }

    /**
     * Mencari bahan terbaik di CSV berdasarkan kata kunci
     */
    public function cariBahanDiCsv(array $allBahan, string $keyword): ?array
    {
        $keyword = strtolower(trim($keyword));
        
        // 1. Pencarian kecocokan persis / awalan
        foreach ($allBahan as $b) {
            $nama = strtolower($b['name'] ?? $b['nama'] ?? $b['nama_bahan'] ?? '');
            if ($nama === $keyword || strpos($nama, $keyword) === 0) {
                return $b;
            }
        }
        
        // 2. Pencarian mengandung kata (contains)
        foreach ($allBahan as $b) {
            $nama = strtolower($b['name'] ?? $b['nama'] ?? $b['nama_bahan'] ?? '');
            if (strpos($nama, $keyword) !== false) {
                return $b;
            }
        }

        return null;
    }

    /**
     * Otomatis memecah menu makanan menjadi bahan-bahan + porsi default (gr)
     */
    public function deteksiBahanDariMenu(string $menu): array
    {
        $allBahan = $this->getAllNutrition();
        $menuLower = strtolower(trim($menu));
        $hasilDeteksi = [];

        // Template menu umum jika user mengisi masakan campuran
        $komposisiMenu = [
            'nasi goreng' => [
                ['keyword' => 'nasi', 'default_gr' => 150],
                ['keyword' => 'telur', 'default_gr' => 50],
                ['keyword' => 'minyak', 'default_gr' => 10],
                ['keyword' => 'bawang merah', 'default_gr' => 10],
            ],
            'mie goreng' => [
                ['keyword' => 'mie', 'default_gr' => 120],
                ['keyword' => 'telur', 'default_gr' => 50],
                ['keyword' => 'sawi', 'default_gr' => 30],
                ['keyword' => 'minyak', 'default_gr' => 10],
            ],
            'soto ayam' => [
                ['keyword' => 'ayam', 'default_gr' => 80],
                ['keyword' => 'bihun', 'default_gr' => 50],
                ['keyword' => 'kol', 'default_gr' => 30],
            ],
            'ayam goreng' => [
                ['keyword' => 'ayam', 'default_gr' => 100],
                ['keyword' => 'minyak', 'default_gr' => 15],
            ],
            'ayam geprek' => [
                ['keyword' => 'ayam', 'default_gr' => 100],
                ['keyword' => 'minyak', 'default_gr' => 15],
                ['keyword' => 'cabai', 'default_gr' => 15],
            ],
            'sayur sop' => [
                ['keyword' => 'wortel', 'default_gr' => 50],
                ['keyword' => 'kentang', 'default_gr' => 50],
                ['keyword' => 'kol', 'default_gr' => 30],
                ['keyword' => 'buncis', 'default_gr' => 20],
            ],
        ];

        // 1. Cek apakah cocok dengan preset komposisi
        $matchedPreset = null;
        foreach ($komposisiMenu as $presetKey => $listPreset) {
            if (strpos($menuLower, $presetKey) !== false) {
                $matchedPreset = $listPreset;
                break;
            }
        }

        if ($matchedPreset) {
            foreach ($matchedPreset as $item) {
                $match = $this->cariBahanDiCsv($allBahan, $item['keyword']);
                $nama = $match['name'] ?? $match['nama'] ?? $match['nama_bahan'] ?? ucfirst($item['keyword']);
                $hasilDeteksi[] = [
                    'nama'     => $nama,
                    'porsi_gr' => $item['default_gr'],
                    'kalori_per_100g'  => (float)($match['calories'] ?? $match['kalori'] ?? 0),
                    'protein_per_100g' => (float)($match['proteins'] ?? $match['protein'] ?? 0),
                    'lemak_per_100g'   => (float)($match['fat'] ?? $match['lemak'] ?? 0),
                    'karbo_per_100g'   => (float)($match['carbohydrate'] ?? $match['karbo'] ?? 0),
                ];
            }
        } else {
            // 2. Jika bukan menu komposit, cari langsung bahan yang paling cocok di CSV
            $match = $this->cariBahanDiCsv($allBahan, $menuLower);
            if ($match) {
                $nama = $match['name'] ?? $match['nama'] ?? $match['nama_bahan'] ?? ucfirst($menu);
                $hasilDeteksi[] = [
                    'nama'     => $nama,
                    'porsi_gr' => 100, // default 100 gram
                    'kalori_per_100g'  => (float)($match['calories'] ?? $match['kalori'] ?? 0),
                    'protein_per_100g' => (float)($match['proteins'] ?? $match['protein'] ?? 0),
                    'lemak_per_100g'   => (float)($match['fat'] ?? $match['lemak'] ?? 0),
                    'karbo_per_100g'   => (float)($match['carbohydrate'] ?? $match['karbo'] ?? 0),
                ];
            } else {
                // Fallback jika tidak ditemukan
                $hasilDeteksi[] = [
                    'nama'     => ucfirst($menu),
                    'porsi_gr' => 100,
                    'kalori_per_100g'  => 0,
                    'protein_per_100g' => 0,
                    'lemak_per_100g'   => 0,
                    'karbo_per_100g'   => 0,
                ];
            }
        }

        return $hasilDeteksi;
    }
}