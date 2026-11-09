<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\NutritionFact;
use App\Services\NutritionCalculator;
use App\Services\RiskEvaluator;
use App\Services\PortionAdvisor;
use App\Services\VerdictBuilder;
use App\Services\ClinicalAdvisor;

class ApiController extends Controller {
    public function recalculate(): void {
        $porsi = (float)($_POST['porsi'] ?? 1.0);
        if ($porsi <= 0) {
            $porsi = 1.0;
        }

        $analysis = session_get('analysis_data');
        $profile = session_get('user_profile');

        if (empty($analysis) || empty($profile)) {
            $this->json(['sukses' => false, 'pesan' => 'Sesi kedaluwarsa'], 400);
            return;
        }

        $totalGizi = NutritionCalculator::hitung($analysis['bahan'], $porsi);
        if (!$totalGizi['sukses']) {
            $this->json(['sukses' => false, 'pesan' => 'Gagal menghitung'], 400);
            return;
        }

        $kondisi = $profile['kondisi'] ?? [];
        $adaKondisi = !empty($kondisi['diabetes']) || !empty($kondisi['hipertensi']) || !empty($kondisi['kolesterol']) || !empty($kondisi['asam_urat']);

        $risiko = RiskEvaluator::evaluasi($totalGizi, $kondisi);
        $porsiDisarankan = PortionAdvisor::hitung($risiko, $adaKondisi);
        $verdict = VerdictBuilder::build($totalGizi, $risiko, $profile, $porsiDisarankan);
        $clinicalAdvice = ClinicalAdvisor::generate($totalGizi, $risiko, $profile, $analysis);

        $targetKalori = (int)($profile['target_kalori'] ?? 2000);
        $persenKalori = ($targetKalori > 0) ? round(($totalGizi['energi_kkal'] / $targetKalori) * 100) : 0;
        $sisaKalori = max(0, $targetKalori - $totalGizi['energi_kkal']);

        $this->json([
            'sukses' => true,
            'porsi' => $porsi,
            'total_gizi' => $totalGizi,
            'risiko' => $risiko,
            'verdict' => $verdict,
            'clinical_advice' => $clinicalAdvice,
            'persen_kalori' => $persenKalori,
            'sisa_kalori' => $sisaKalori,
            'porsi_disarankan' => $porsiDisarankan
        ]);
    }

    public function searchIngredient(): void {
        $q = trim($_GET['q'] ?? '');
        $results = NutritionFact::search($q, 8);
        $this->json([
            'sukses' => true,
            'data' => $results
        ]);
    }
}
