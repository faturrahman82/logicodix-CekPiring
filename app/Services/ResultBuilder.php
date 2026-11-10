<?php

namespace App\Services;

class ResultBuilder {
    public static function build(array $analysis, array $profile, ?float $porsi = null): array {
        $bahan = $analysis['bahan'] ?? [];
        $porsi = $porsi ?? (float)($analysis['porsi'] ?? 1.0);
        $porsi = max(0.3, min(3.0, round($porsi, 1)));
        $kondisi = $profile['kondisi'] ?? [];
        $adaKondisi = !empty(array_filter((array)$kondisi));

        $totalGizi = NutritionCalculator::hitung($bahan, $porsi);
        if (empty($totalGizi['sukses'])) {
            return ['sukses' => false, 'pesan' => $totalGizi['pesan'] ?? 'Gagal menghitung'];
        }
        $risiko = RiskEvaluator::evaluasi($totalGizi, $kondisi, (int)($profile['target_kalori'] ?? 2000));

        $gizi1 = abs($porsi - 1.0) < 0.001 ? $totalGizi : NutritionCalculator::hitung($bahan, 1.0);
        $risiko1 = RiskEvaluator::evaluasi($gizi1, $kondisi, (int)($profile['target_kalori'] ?? 2000));
        $porsiDisarankan = PortionAdvisor::hitung($risiko1, $adaKondisi);

        $verdict = VerdictBuilder::build($totalGizi, $risiko, $profile, $porsiDisarankan, $porsi);

        $perbandingan = null;
        $terburuk1 = RiskEvaluator::terburukAktif($risiko1);
        if ($terburuk1 !== null && $porsiDisarankan < 1.0) {
            $perbandingan = [
                'nama' => $terburuk1['nama'],
                'persen_penuh' => (int)$terburuk1['persen'],
                'porsi_saran' => $porsiDisarankan,
                'persen_saran' => (int)round($terburuk1['persen'] * $porsiDisarankan),
            ];
        }

        $saranList = FoodImprover::buatSemua($bahan, $porsi, $risiko, $totalGizi, $profile);

        $targetKalori = (int)($profile['target_kalori'] ?? 2000);
        $persenKalori = ($targetKalori > 0) ? (int)round(($totalGizi['energi_kkal'] / $targetKalori) * 100) : 0;
        $sisaKalori = max(0, $targetKalori - (int)$totalGizi['energi_kkal']);

        $rincian = [];
        foreach ($totalGizi['rincian_bahan'] as $rb) {
            $rincian[] = [
                'nama' => $rb['nama'],
                'gram' => (int)round((float)$rb['berat_g'] * $porsi),
                'kkal' => $rb['ada_data'] ? (int)round((float)($rb['nutrisi']['energi_kkal'] ?? 0)) : null,
                'ada_data' => (bool)$rb['ada_data'],
                'sumber' => $rb['sumber_data'] ?? '',
            ];
        }

        return [
            'sukses' => true,
            'porsi' => $porsi,
            'porsi_teks' => PortionSize::formatPorsi($porsi),
            'ukuran_porsi' => $analysis['ukuran_porsi'] ?? PortionSize::bawaan(),
            'total_gizi' => [
                'energi_kkal' => (int)$totalGizi['energi_kkal'],
                'berat_total_g' => (int)$totalGizi['berat_total_g'],
                'karbohidrat_g' => $totalGizi['karbohidrat_g'],
                'protein_g' => $totalGizi['protein_g'],
                'lemak_g' => $totalGizi['lemak_g'],
                'serat_g' => $totalGizi['serat_g'],
                'natrium_mg' => $totalGizi['natrium_mg'],
                'gula_g' => $totalGizi['gula_g'],
                'lemak_jenuh_g' => $totalGizi['lemak_jenuh_g'],
                'purin_mg' => $totalGizi['purin_mg'],
                'makro_persen' => $totalGizi['makro_persen'],
            ],
            'risiko' => $risiko,
            'ada_kondisi' => $adaKondisi,
            'porsi_disarankan' => $porsiDisarankan,
            'porsi_disarankan_teks' => PortionSize::formatPorsi($porsiDisarankan),
            'perbandingan' => $perbandingan,
            'verdict' => $verdict,
            'saran' => $saranList[0] ?? null,
            'saran_list' => $saranList,
            'target_kalori' => $targetKalori,
            'persen_kalori' => $persenKalori,
            'sisa_kalori' => $sisaKalori,
            'rincian' => $rincian,
        ];
    }
}
