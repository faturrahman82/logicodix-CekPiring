<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\NutritionFact;
use App\Services\AiClient;
use App\Services\PortionSize;

class KonfirmasiController extends Controller {
    public function index(): void {
        $analysis = session_get('analysis_data');
        if (empty($analysis) || empty($analysis['bahan'])) {
            $this->redirect('makanan');
            return;
        }

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
                'kkal100' => ($adaGizi && $fact['energi_kkal'] !== null) ? (float)$fact['energi_kkal'] : null,
                'kalori_estimasi' => ($adaGizi && $fact['energi_kkal'] !== null) ? round(($fact['energi_kkal'] / 100) * $berat) : null,
                'gizi' => $fact
            ];
        }

        $this->render('konfirmasi', [
            'analysis' => $analysis,
            'bahan' => $bahanDivalidasi,
            'ada_bahan_tanpa_data' => $adaBahanTanpaData,
            'konflik' => $analysis['konflik'] ?? false,
            'konflik_teks' => $analysis['konflik_teks'] ?? '',
            'porsi' => (float)($analysis['porsi'] ?? 1.0),
            'ukuran_porsi' => $analysis['ukuran_porsi'] ?? PortionSize::bawaan()
        ]);
    }

    public static function simpanDariPost(): array {
        $analysis = session_get('analysis_data', []);
        $namaBahan = $_POST['bahan_nama'] ?? [];
        $beratBahan = $_POST['bahan_berat'] ?? [];
        $ketBahan = $_POST['bahan_ket'] ?? [];
        $porsi = (float)($_POST['porsi'] ?? 1.0);
        $porsi = ($porsi > 0) ? max(0.3, min(3.0, round($porsi, 1))) : 1.0;

        $ukuranLama = $analysis['ukuran_porsi'] ?? PortionSize::bawaan();
        $ukuranBaru = PortionSize::valid($_POST['ukuran_porsi'] ?? ($ukuranLama['ukuran'] ?? 'sedang'));
        if ($ukuranBaru !== PortionSize::valid($ukuranLama['ukuran'] ?? 'sedang')) {
            $ukuranLama = ['ukuran' => $ukuranBaru, 'sumber' => 'pengguna', 'alasan' => ''];
        }

        $bahanBaru = [];
        for ($i = 0; $i < count($namaBahan); $i++) {
            $nama = trim($namaBahan[$i]);
            if (!empty($nama)) {
                $berat = isset($beratBahan[$i]) ? (float)$beratBahan[$i] : 50;
                $berat = max(1, min(2000, $berat));
                $nama = mb_substr($nama, 0, 80);
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

        $analysis['bahan'] = array_slice($bahanBaru, 0, 20);
        $analysis['porsi'] = $porsi;
        $analysis['ukuran_porsi'] = $ukuranLama;
        $analysis['saran_diterapkan'] = [];
        $analysis['bahan_sebelum_saran'] = null;
        session_set('analysis_data', $analysis);

        return $analysis;
    }

    public function save(): void {
        self::simpanDariPost();
        $this->redirect('hasil');
    }

    public function resolveConflict(): void {
        $pilihan = $_POST['pilihan'] ?? 'nama';
        $analysis = session_get('analysis_data', []);

        if ($pilihan === 'foto' && !empty($analysis['nama_foto'])) {
            $analysis['nama_makanan'] = $analysis['nama_foto'];
        } else {
            $hasilAi = AiClient::identify($analysis['nama_input'] ?? ($analysis['nama_makanan'] ?? ''), null);
            if (!empty($hasilAi['sukses'])) {
                $analysis['nama_makanan'] = $hasilAi['nama'];
                $analysis['bahan'] = $hasilAi['bahan'];
                $analysis['ukuran_porsi'] = $hasilAi['ukuran_porsi'] ?? PortionSize::bawaan();
                $analysis['sumber_analisis'] = $hasilAi['sumber_analisis'] ?? 'contoh';
            }
        }
        $analysis['konflik'] = false;
        $analysis['konflik_teks'] = '';

        session_set('analysis_data', $analysis);
        $this->redirect('konfirmasi');
    }
}
