<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\NutritionFact;
use App\Services\AiClient;

class KonfirmasiController extends Controller {
    public function index(): void {
        $analysis = session_get('analysis_data');
        if (empty($analysis) || empty($analysis['bahan'])) {
            $this->redirect('makanan');
            return;
        }

        // Periksa status data gizi setiap bahan (State 3c jika ada bahan tanpa gizi)
        $bahanDivalidasi = [];
        $adaBahanTanpaData = false;

        foreach ($analysis['bahan'] as $b) {
            $fact = NutritionFact::findByName($b['nama']);
            $adaGizi = ($fact !== null && ($fact['energi_kkal'] !== null || $fact['protein_g'] !== null));
            if (!$adaGizi) {
                $adaBahanTanpaData = true;
            }

            $berat = (float)($b['berat_g'] ?? 50);
            $ket = trim($b['keterangan'] ?? '');
            if (empty($ket)) {
                $ket = round($berat) . ' g';
            } elseif (!preg_match('/\b\d+(\.\d+)?\s*(g|gram|ml)\b/i', $ket)) {
                $ket = $ket . ' (sekitar ' . round($berat) . ' g)';
            }

            // Gunakan nama kuliner ramah pengguna, bukan istilah mentah tabel database
            $namaTampil = !empty($b['nama']) ? $b['nama'] : ($fact['nama'] ?? 'Bahan');

            $kelengkapan = $fact['tingkat_kelengkapan'] ?? ($adaGizi ? 'data_lengkap' : 'perlu_verifikasi');
            $kelengkapanLabel = ($kelengkapan === 'data_lengkap') ? 'Data Lengkap' : (($kelengkapan === 'data_sebagian') ? 'Data Sebagian' : 'Perlu Verifikasi');

            $bahanDivalidasi[] = [
                'nama' => $namaTampil,
                'berat_g' => $berat,
                'keterangan' => $ket,
                'ada_data_gizi' => $adaGizi,
                'sumber_data' => $fact['sumber_data'] ?? ($adaGizi ? 'TKPI 2017' : 'Data belum tersedia'),
                'kelengkapan' => $kelengkapan,
                'kelengkapan_label' => $kelengkapanLabel,
                'kalori_estimasi' => $adaGizi ? round(($fact['energi_kkal'] / 100) * $berat) : null,
                'gizi' => $fact
            ];
        }

        $this->render('konfirmasi', [
            'analysis' => $analysis,
            'bahan' => $bahanDivalidasi,
            'ada_bahan_tanpa_data' => $adaBahanTanpaData,
            'konflik' => $analysis['konflik'] ?? false,
            'konflik_teks' => $analysis['konflik_teks'] ?? '',
            'porsi' => (float)($analysis['porsi'] ?? 1.0)
        ]);
    }

    public function save(): void {
        $analysis = session_get('analysis_data', []);
        $namaBahan = $_POST['bahan_nama'] ?? [];
        $beratBahan = $_POST['bahan_berat'] ?? [];
        $ketBahan = $_POST['bahan_ket'] ?? [];
        $porsi = (float)($_POST['porsi'] ?? 1.0);

        if ($porsi <= 0) {
            $porsi = 1.0;
        }

        $bahanBaru = [];
        for ($i = 0; $i < count($namaBahan); $i++) {
            $nama = trim($namaBahan[$i]);
            if (!empty($nama)) {
                $berat = isset($beratBahan[$i]) ? (float)$beratBahan[$i] : 50;
                $ket = trim($ketBahan[$i] ?? '');
                if (empty($ket)) {
                    $ket = round($berat) . ' g';
                } elseif (!preg_match('/\b\d+(\.\d+)?\s*(g|gram|ml)\b/i', $ket)) {
                    $ket = $ket . ' (sekitar ' . round($berat) . ' g)';
                }
                $bahanBaru[] = [
                    'nama' => $nama,
                    'berat_g' => $berat,
                    'keterangan' => $ket
                ];
            }
        }

        $analysis['bahan'] = $bahanBaru;
        $analysis['porsi'] = $porsi;
        session_set('analysis_data', $analysis);

        $this->redirect('menghitung');
    }

    public function resolveConflict(): void {
        $pilihan = $_POST['pilihan'] ?? 'nama'; // 'nama' atau 'foto'
        $analysis = session_get('analysis_data', []);

        if ($pilihan === 'foto' && !empty($analysis['nama_foto'])) {
            $target = $analysis['nama_foto'];
        } else {
            $target = $analysis['nama_makanan'];
        }

        $hasilAi = AiClient::identify($target, null);
        $analysis['nama_makanan'] = $hasilAi['nama'];
        $analysis['bahan'] = $hasilAi['bahan'];
        $analysis['konflik'] = false;
        $analysis['konflik_teks'] = '';

        session_set('analysis_data', $analysis);
        $this->redirect('konfirmasi');
    }
}
