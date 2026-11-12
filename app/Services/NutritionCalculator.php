<?php

namespace App\Services;

use App\Models\NutritionFact;

class NutritionCalculator {
    public static function hitung(array $ingredients, float $porsi = 1.0): array {
        $nutrientKeys = [
            'energi_kkal', 'protein_g', 'lemak_g', 'karbohidrat_g', 'serat_g',
            'gula_g', 'lemak_jenuh_g', 'lemak_tak_jenuh_g', 'kolesterol_mg', 'natrium_mg',
            'kalium_mg', 'kalsium_mg', 'fosfor_mg', 'zat_besi_mg', 'magnesium_mg', 'seng_mg',
            'vit_a_mcg', 'vit_b1_mg', 'vit_b2_mg', 'vit_b3_mg', 'vit_b6_mg',
            'vit_b12_mcg', 'vit_c_mg', 'vit_d_mcg', 'vit_e_mg',
            'purin_mg'
        ];

        $totals = [];
        $counts = [];
        foreach ($nutrientKeys as $k) {
            $totals[$k] = 0.0;
            $counts[$k] = 0;
        }

        $beratTotal = 0.0;
        $rincianBahan = [];
        $adaBahanValid = false;

        $kontributor = [
            'natrium' => [],
            'gula' => [],
            'lemak_jenuh' => [],
            'purin' => [],
            'kolesterol' => []
        ];

        $totalBahanCount = count($ingredients);

        foreach ($ingredients as $item) {
            $berat = (float)($item['berat_g'] ?? 0);
            $nama = $item['nama'] ?? '';
            $fact = $item['gizi'] ?? NutritionFact::findByName($nama);

            if ($fact === null || ($fact['energi_kkal'] === null && $fact['protein_g'] === null)) {
                $rincianBahan[] = [
                    'nama' => $nama,
                    'berat_g' => $berat,
                    'keterangan' => $item['keterangan'] ?? "{$berat} g",
                    'ada_data' => false,
                    'tingkat_kelengkapan' => 'perlu_verifikasi',
                    'sumber_data' => 'Data belum tersedia',
                    'nutrisi' => []
                ];
                continue;
            }

            $adaBahanValid = true;
            $faktor = ($berat / 100.0) * $porsi;
            $beratTotal += $berat * $porsi;

            $nutrisiBahan = [];

            foreach ($nutrientKeys as $k) {
                if (isset($fact[$k]) && $fact[$k] !== null && is_numeric($fact[$k])) {
                    $val = (float)$fact[$k] * $faktor;
                    $totals[$k] += $val;
                    $counts[$k]++;
                    $nutrisiBahan[$k] = round($val, 2);
                } else {
                    $nutrisiBahan[$k] = null;
                }
            }

            if (!empty($nutrisiBahan['natrium_mg']) && $nutrisiBahan['natrium_mg'] > 0) {
                $kontributor['natrium'][$nama] = ($kontributor['natrium'][$nama] ?? 0) + $nutrisiBahan['natrium_mg'];
            }
            if (!empty($nutrisiBahan['gula_g']) && $nutrisiBahan['gula_g'] > 0) {
                $kontributor['gula'][$nama] = ($kontributor['gula'][$nama] ?? 0) + $nutrisiBahan['gula_g'];
            }
            if (!empty($nutrisiBahan['lemak_jenuh_g']) && $nutrisiBahan['lemak_jenuh_g'] > 0) {
                $kontributor['lemak_jenuh'][$nama] = ($kontributor['lemak_jenuh'][$nama] ?? 0) + $nutrisiBahan['lemak_jenuh_g'];
            }
            if (!empty($nutrisiBahan['purin_mg']) && $nutrisiBahan['purin_mg'] > 0) {
                $kontributor['purin'][$nama] = ($kontributor['purin'][$nama] ?? 0) + $nutrisiBahan['purin_mg'];
            }
            if (!empty($nutrisiBahan['kolesterol_mg']) && $nutrisiBahan['kolesterol_mg'] > 0) {
                $kontributor['kolesterol'][$nama] = ($kontributor['kolesterol'][$nama] ?? 0) + $nutrisiBahan['kolesterol_mg'];
            }

            $rincianBahan[] = [
                'nama' => $nama,
                'nama_kanonis' => $fact['nama'] ?? $nama,
                'berat_g' => $berat,
                'keterangan' => $item['keterangan'] ?? "{$berat} g",
                'ada_data' => true,
                'tingkat_kelengkapan' => $fact['tingkat_kelengkapan'] ?? 'data_lengkap',
                'sumber_data' => $fact['sumber_data'] ?? 'TKPI 2017 Kemenkes RI',
                'kode_tkpi' => $fact['kode_tkpi'] ?? null,
                'kode_usda' => $fact['kode_usda'] ?? null,
                'nutrisi' => $nutrisiBahan
            ];
        }

        if (!$adaBahanValid) {
            return [
                'sukses' => false,
                'pesan' => 'Tidak ada bahan makanan yang memiliki data gizi.'
            ];
        }

        $statusNutrisi = [];
        $validIngredientsCount = count(array_filter($rincianBahan, fn($b) => $b['ada_data']));

        foreach ($nutrientKeys as $k) {
            $count = $counts[$k];
            if ($count === 0) {
                $statusNutrisi[$k] = [
                    'tersedia' => false,
                    'status' => 'tidak_tersedia',
                    'label' => 'Tidak tersedia',
                    'kontribusi_bahan' => 0,
                    'total_bahan' => $validIngredientsCount,
                    'nilai' => null
                ];
            } elseif ($count === $validIngredientsCount) {
                $statusNutrisi[$k] = [
                    'tersedia' => true,
                    'status' => 'lengkap',
                    'label' => 'Lengkap (' . $count . '/' . $validIngredientsCount . ' bahan)',
                    'kontribusi_bahan' => $count,
                    'total_bahan' => $validIngredientsCount,
                    'nilai' => round($totals[$k], 1)
                ];
            } else {
                $statusNutrisi[$k] = [
                    'tersedia' => true,
                    'status' => 'sebagian',
                    'label' => 'Sebagian (' . $count . '/' . $validIngredientsCount . ' bahan)',
                    'kontribusi_bahan' => $count,
                    'total_bahan' => $validIngredientsCount,
                    'nilai' => round($totals[$k], 1)
                ];
            }
        }

        $kkalKarbo = $totals['karbohidrat_g'] * 4;
        $kkalProtein = $totals['protein_g'] * 4;
        $kkalLemak = $totals['lemak_g'] * 9;
        $kkalTotalMakro = $kkalKarbo + $kkalProtein + $kkalLemak;

        $persenKarbo = ($kkalTotalMakro > 0) ? round(($kkalKarbo / $kkalTotalMakro) * 100) : 0;
        $persenProtein = ($kkalTotalMakro > 0) ? round(($kkalProtein / $kkalTotalMakro) * 100) : 0;
        $persenLemak = ($kkalTotalMakro > 0) ? (100 - $persenKarbo - $persenProtein) : 0;

        $penyumbangNatrium = self::formatPenyumbang($kontributor['natrium']);
        $penyumbangGula = self::formatPenyumbang($kontributor['gula']);
        $penyumbangLemakJenuh = self::formatPenyumbang($kontributor['lemak_jenuh']);
        $penyumbangPurin = self::formatPenyumbang($kontributor['purin']);
        $penyumbangKolesterol = self::formatPenyumbang($kontributor['kolesterol']);

        $purinVal = $totals['purin_mg'];
        $purinKategori = 'rendah';
        if ($purinVal >= 200) {
            $purinKategori = 'tinggi';
        } elseif ($purinVal >= 50) {
            $purinKategori = 'sedang';
        }

        return [
            'sukses' => true,
            'porsi' => $porsi,
            'berat_total_g' => round($beratTotal),
            'energi_kkal' => round($totals['energi_kkal']),
            'protein_g' => round($totals['protein_g']),
            'lemak_g' => round($totals['lemak_g']),
            'karbohidrat_g' => round($totals['karbohidrat_g']),
            'serat_g' => round($totals['serat_g'], 1),
            'gula_g' => round($totals['gula_g']),
            'natrium_mg' => round($totals['natrium_mg']),
            'lemak_jenuh_g' => round($totals['lemak_jenuh_g'], 1),
            'lemak_tak_jenuh_g' => round($totals['lemak_tak_jenuh_g'], 1),
            'kolesterol_mg' => round($totals['kolesterol_mg']),
            'kalium_mg' => round($totals['kalium_mg']),
            'kalsium_mg' => round($totals['kalsium_mg']),
            'fosfor_mg' => round($totals['fosfor_mg']),
            'zat_besi_mg' => round($totals['zat_besi_mg'], 1),
            'magnesium_mg' => round($totals['magnesium_mg']),
            'seng_mg' => round($totals['seng_mg'], 1),
            'vit_a_mcg' => round($totals['vit_a_mcg']),
            'vit_b1_mg' => round($totals['vit_b1_mg'], 2),
            'vit_b2_mg' => round($totals['vit_b2_mg'], 2),
            'vit_b3_mg' => round($totals['vit_b3_mg'], 2),
            'vit_b6_mg' => round($totals['vit_b6_mg'], 2),
            'vit_b12_mcg' => round($totals['vit_b12_mcg'], 2),
            'vit_c_mg' => round($totals['vit_c_mg'], 1),
            'vit_d_mcg' => round($totals['vit_d_mcg'], 1),
            'vit_e_mg' => round($totals['vit_e_mg'], 1),
            'purin_mg' => round($totals['purin_mg']),
            'purin_kategori' => $purinKategori,
            'status_nutrisi' => $statusNutrisi,
            'rincian_bahan' => $rincianBahan,
            'makro_persen' => [
                'karbohidrat' => $persenKarbo,
                'protein' => $persenProtein,
                'lemak' => $persenLemak
            ],
            'penyumbang' => [
                'natrium' => $penyumbangNatrium,
                'gula' => $penyumbangGula,
                'lemak_jenuh' => $penyumbangLemakJenuh,
                'purin' => $penyumbangPurin,
                'kolesterol' => $penyumbangKolesterol
            ]
        ];
    }

    private static function formatPenyumbang(array $items): string {
        if (empty($items)) {
            return '';
        }
        arsort($items);
        $top = array_slice(array_keys($items), 0, 2);
        if (count($top) === 1) {
            return "Terbesar dari " . mb_strtolower($top[0]) . ".";
        }
        return "Terbesar dari " . mb_strtolower($top[0]) . " dan " . mb_strtolower($top[1]) . ".";
    }
}
