<?php

namespace App\Controllers;

use Core\Controller;
use App\Services\NutritionCalculator;
use App\Services\RiskEvaluator;
use App\Services\PortionAdvisor;
use App\Services\VerdictBuilder;
use App\Services\ClinicalAdvisor;

class HasilController extends Controller {
    public function menghitung(): void {
        $profile = session_get('user_profile');
        $analysis = session_get('analysis_data');

        if (empty($profile) || empty($analysis) || empty($analysis['bahan'])) {
            $this->redirect('makanan');
            return;
        }

        $this->render('menghitung', [
            'profile' => $profile,
            'analysis' => $analysis,
            'jumlah_bahan' => count($analysis['bahan'])
        ]);
    }

    public function index(): void {
        $profile = session_get('user_profile');
        $analysis = session_get('analysis_data');

        if (empty($profile) || empty($analysis) || empty($analysis['bahan'])) {
            $this->redirect('makanan');
            return;
        }

        $porsi = (float)($analysis['porsi'] ?? 1.0);
        $totalGizi = NutritionCalculator::hitung($analysis['bahan'], $porsi);

        if (!$totalGizi['sukses']) {
            $this->redirect('galat');
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

        $this->render('hasil', [
            'profile' => $profile,
            'analysis' => $analysis,
            'total_gizi' => $totalGizi,
            'risiko' => $risiko,
            'porsi' => $porsi,
            'porsi_disarankan' => $porsiDisarankan,
            'verdict' => $verdict,
            'clinical_advice' => $clinicalAdvice,
            'target_kalori' => $targetKalori,
            'persen_kalori' => $persenKalori,
            'sisa_kalori' => $sisaKalori,
            'ada_kondisi' => $adaKondisi
        ]);
    }

    public function galat(): void {
        $this->render('galat', [
            'pesan_judul' => 'Estimasi belum bisa dihitung',
            'pesan_deskripsi' => 'Layanan analisis sedang tidak bisa dihubungi, atau belum ada bahan yang punya data gizi. Coba hitung lagi atau kembali ke bahan.',
            'kembali_url' => base_url('konfirmasi')
        ]);
    }
}
