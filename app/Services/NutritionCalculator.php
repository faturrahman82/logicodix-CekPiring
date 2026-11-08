<?php

namespace App\Services;

use App\Models\NutritionFact;

class NutritionCalculator {
    public static function hitung(array $ingredients, float $porsi = 1.0): array {
        $total = [
            'energi_kkal' => 0.0,
            'protein_g' => 0.0,
            'lemak_g' => 0.0,
            'karbohidrat_g' => 0.0,
            'serat_g' => 0.0,
            'gula_g' => 0.0,
            'lemak_jenuh_g' => 0.0,
            'kolesterol_mg' => 0.0,
            'natrium_mg' => 0.0,
            'purin_mg' => 0.0,
            'berat_total_g' => 0.0
        ];

        $kontributor = [
            'natrium' => [],
            'gula' => [],
            'lemak_jenuh' => [],
            'purin' => []
        ];

        $adaBahanValid = false;

        foreach ($ingredients as $item) {
            $berat = (float)($item['berat_g'] ?? 0);
            $nama = $item['nama'] ?? '';
            $fact = $item['gizi'] ?? NutritionFact::findByName($nama);

            if ($fact === null || ($fact['energi_kkal'] === null && $fact['protein_g'] === null)) {
                // Bahan tanpa data gizi dilewati sesuai aturan BR-11
                continue;
            }

            $adaBahanValid = true;
            $faktor = ($berat / 100.0) * $porsi;
            $total['berat_total_g'] += $berat * $porsi;

            $total['energi_kkal'] += ($fact['energi_kkal'] ?? 0) * $faktor;
            $total['protein_g'] += ($fact['protein_g'] ?? 0) * $faktor;
            $total['lemak_g'] += ($fact['lemak_g'] ?? 0) * $faktor;
            $total['karbohidrat_g'] += ($fact['karbohidrat_g'] ?? 0) * $faktor;
            $total['serat_g'] += ($fact['serat_g'] ?? 0) * $faktor;
            
            if (isset($fact['gula_g']) && $fact['gula_g'] !== null) {
                $nilaiGula = $fact['gula_g'] * $faktor;
                $total['gula_g'] += $nilaiGula;
                if ($nilaiGula > 0) {
                    $kontributor['gula'][$nama] = ($kontributor['gula'][$nama] ?? 0) + $nilaiGula;
                }
            }

            if (isset($fact['natrium_mg']) && $fact['natrium_mg'] !== null) {
                $nilaiNatrium = $fact['natrium_mg'] * $faktor;
                $total['natrium_mg'] += $nilaiNatrium;
                if ($nilaiNatrium > 0) {
                    $kontributor['natrium'][$nama] = ($kontributor['natrium'][$nama] ?? 0) + $nilaiNatrium;
                }
            }

            if (isset($fact['lemak_jenuh_g']) && $fact['lemak_jenuh_g'] !== null) {
                $nilaiJenuh = $fact['lemak_jenuh_g'] * $faktor;
                $total['lemak_jenuh_g'] += $nilaiJenuh;
                if ($nilaiJenuh > 0) {
                    $kontributor['lemak_jenuh'][$nama] = ($kontributor['lemak_jenuh'][$nama] ?? 0) + $nilaiJenuh;
                }
            }

            if (isset($fact['purin_mg']) && $fact['purin_mg'] !== null) {
                $nilaiPurin = $fact['purin_mg'] * $faktor;
                $total['purin_mg'] += $nilaiPurin;
                if ($nilaiPurin > 0) {
                    $kontributor['purin'][$nama] = ($kontributor['purin'][$nama] ?? 0) + $nilaiPurin;
                }
            }
        }

        if (!$adaBahanValid) {
            return [
                'sukses' => false,
                'pesan' => 'Tidak ada bahan makanan yang memiliki data gizi.'
            ];
        }

        // Konversi energi makronutrien: karbohidrat 4 kkal/g, protein 4 kkal/g, lemak 9 kkal/g
        $kkalKarbo = $total['karbohidrat_g'] * 4;
        $kkalProtein = $total['protein_g'] * 4;
        $kkalLemak = $total['lemak_g'] * 9;
        $kkalTotalMakro = $kkalKarbo + $kkalProtein + $kkalLemak;

        $persenKarbo = ($kkalTotalMakro > 0) ? round(($kkalKarbo / $kkalTotalMakro) * 100) : 0;
        $persenProtein = ($kkalTotalMakro > 0) ? round(($kkalProtein / $kkalTotalMakro) * 100) : 0;
        $persenLemak = ($kkalTotalMakro > 0) ? (100 - $persenKarbo - $persenProtein) : 0;

        $penyumbangNatrium = self::formatPenyumbang($kontributor['natrium']);
        $penyumbangGula = self::formatPenyumbang($kontributor['gula']);
        $penyumbangLemakJenuh = self::formatPenyumbang($kontributor['lemak_jenuh']);
        $penyumbangPurin = self::formatPenyumbang($kontributor['purin']);

        return [
            'sukses' => true,
            'porsi' => $porsi,
            'berat_total_g' => round($total['berat_total_g']),
            'energi_kkal' => round($total['energi_kkal']),
            'protein_g' => round($total['protein_g']),
            'lemak_g' => round($total['lemak_g']),
            'karbohidrat_g' => round($total['karbohidrat_g']),
            'serat_g' => round($total['serat_g'], 1),
            'gula_g' => round($total['gula_g']),
            'natrium_mg' => round($total['natrium_mg']),
            'lemak_jenuh_g' => round($total['lemak_jenuh_g'], 1),
            'purin_mg' => round($total['purin_mg']),
            'makro_persen' => [
                'karbohidrat' => $persenKarbo,
                'protein' => $persenProtein,
                'lemak' => $persenLemak
            ],
            'penyumbang' => [
                'natrium' => $penyumbangNatrium,
                'gula' => $penyumbangGula,
                'lemak_jenuh' => $penyumbangLemakJenuh,
                'purin' => $penyumbangPurin
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
