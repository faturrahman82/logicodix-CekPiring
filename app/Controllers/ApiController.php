<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\NutritionFact;
use App\Services\ResultBuilder;

class ApiController extends Controller {
    public function recalculate(): void {
        $analysis = session_get('analysis_data');
        $profile = session_get('user_profile');

        if (empty($analysis) || empty($profile) || empty($analysis['bahan'])) {
            $this->json(['sukses' => false, 'pesan' => 'Sesi kedaluwarsa'], 400);
            return;
        }

        $porsi = (float)($_POST['porsi'] ?? ($analysis['porsi'] ?? 1.0));
        if ($porsi <= 0) {
            $porsi = 1.0;
        }

        $hasil = ResultBuilder::build($analysis, $profile, $porsi);
        if (empty($hasil['sukses'])) {
            $this->json(['sukses' => false, 'pesan' => 'Gagal menghitung'], 400);
            return;
        }

        $analysis['porsi'] = $hasil['porsi'];
        session_set('analysis_data', $analysis);

        $hasil['saran_diterapkan'] = HasilController::daftarDiterapkan($analysis['saran_diterapkan'] ?? []);
        $this->json($hasil);
    }

    public function terapkanSaran(): void {
        $analysis = session_get('analysis_data');
        $profile = session_get('user_profile');

        if (empty($analysis) || empty($profile) || empty($analysis['bahan'])) {
            $this->json(['sukses' => false, 'pesan' => 'Sesi kedaluwarsa'], 400);
            return;
        }

        $sebelum = ResultBuilder::build($analysis, $profile);
        $daftar = $sebelum['saran_list'] ?? [];
        if (empty($sebelum['sukses']) || empty($daftar)) {
            $this->json(['sukses' => false, 'pesan' => 'Tidak ada saran untuk diterapkan'], 400);
            return;
        }

        $zat = (string)($_POST['zat'] ?? '');
        $saran = null;
        foreach ($daftar as $s) {
            if ($zat === '' || $s['zat'] === $zat) {
                $saran = $s;
                break;
            }
        }
        if ($saran === null) {
            $this->json(['sukses' => false, 'pesan' => 'Saran ini sudah tidak berlaku'], 409);
            return;
        }
        if (!isset($analysis['bahan'][$saran['indeks']])) {
            $this->json(['sukses' => false, 'pesan' => 'Bahan tidak ditemukan'], 400);
            return;
        }

        if (empty($analysis['bahan_sebelum_saran'])) {
            $analysis['bahan_sebelum_saran'] = $analysis['bahan'];
        }
        if (($saran['jenis'] ?? 'kurangi') === 'ganti' && !empty($saran['nama_baru'])) {
            $namaLama = $analysis['bahan'][$saran['indeks']]['nama'];
            $analysis['bahan'][$saran['indeks']]['nama'] = $saran['nama_baru'];
            $analysis['bahan'][$saran['indeks']]['keterangan'] = "pengganti {$namaLama} (saran CekPiring)";
        } else {
            $analysis['bahan'][$saran['indeks']]['berat_g'] = $saran['gram_baru'];
            $analysis['bahan'][$saran['indeks']]['keterangan'] = "dikurangi dari {$saran['gram_lama']} g (saran CekPiring)";
        }
        $diterapkan = HasilController::daftarDiterapkan($analysis['saran_diterapkan'] ?? []);
        $diterapkan[] = ['aksi' => $saran['aksi'], 'efek' => $saran['efek']];
        $analysis['saran_diterapkan'] = $diterapkan;
        session_set('analysis_data', $analysis);

        $hasil = ResultBuilder::build($analysis, $profile);
        $hasil['saran_diterapkan'] = $diterapkan;
        $this->json($hasil);
    }

    public function batalkanSaran(): void {
        $analysis = session_get('analysis_data');
        $profile = session_get('user_profile');

        if (empty($analysis) || empty($profile) || empty($analysis['bahan_sebelum_saran'])) {
            $this->json(['sukses' => false, 'pesan' => 'Tidak ada yang bisa dibatalkan'], 400);
            return;
        }

        $analysis['bahan'] = $analysis['bahan_sebelum_saran'];
        $analysis['bahan_sebelum_saran'] = null;
        $analysis['saran_diterapkan'] = [];
        session_set('analysis_data', $analysis);

        $hasil = ResultBuilder::build($analysis, $profile);
        $hasil['saran_diterapkan'] = [];
        $this->json($hasil);
    }

    public function searchIngredient(): void {
        $q = mb_substr(trim($_GET['q'] ?? ''), 0, 60);
        $results = NutritionFact::search($q, 8);
        $this->json([
            'sukses' => true,
            'data' => $results
        ]);
    }
}
