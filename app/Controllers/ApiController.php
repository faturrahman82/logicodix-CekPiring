<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\NutritionFact;
use App\Services\AiClient;
use App\Services\NutritionCalculator;
use App\Services\ResultBuilder;

class ApiController extends Controller {
    public function cekLangkah1(): void {
        $foto = MakananController::terimaFoto($_POST['nama_makanan'] ?? '');
        if (isset($foto['error'])) {
            $this->json(['sukses' => false, 'pesan' => $foto['error']], 422);
            return;
        }
        session_set('cek_tertunda', ['nama' => $foto['nama'], 'foto' => $foto['foto']]);
        $this->json(['sukses' => true, 'detail' => 'Foto diterima (' . $foto['ukuran_kb'] . ' KB)']);
    }

    public function cekLangkah2(): void {
        $tertunda = session_get('cek_tertunda');
        if (empty($tertunda['nama']) || empty($tertunda['foto'])) {
            $this->json(['sukses' => false, 'pesan' => 'Foto belum diunggah, ulangi dari awal.'], 400);
            return;
        }
        $hasilAi = AiClient::identify($tertunda['nama'], $tertunda['foto']);
        if (empty($hasilAi['sukses'])) {
            $this->json(['sukses' => false, 'pesan' => 'Makanan tidak dikenali. Coba ketik nama makanan lain.'], 422);
            return;
        }
        MakananController::simpanAnalisis($tertunda['nama'], $tertunda['foto'], $hasilAi);

        $jumlah = count($hasilAi['bahan']);
        $sumber = ($hasilAi['sumber_analisis'] ?? '') === 'ai' ? 'Dikenali AI' : 'Resep contoh (AI tidak aktif)';
        $detail = "{$sumber}: {$hasilAi['nama']}, {$jumlah} bahan";
        if (!empty($hasilAi['konflik'])) {
            $detail .= '. Nama dan foto tampak berbeda';
        }
        $this->json(['sukses' => true, 'detail' => $detail]);
    }

    public function cekLangkah3(): void {
        $analysis = session_get('analysis_data');
        if (empty($analysis['bahan'])) {
            $this->json(['sukses' => false, 'pesan' => 'Belum ada bahan terdeteksi, ulangi dari awal.'], 400);
            return;
        }
        $cocok = 0;
        foreach ($analysis['bahan'] as $b) {
            $fakta = NutritionFact::findByName((string)$b['nama']);
            if ($fakta !== null && ($fakta['energi_kkal'] !== null || $fakta['protein_g'] !== null)) {
                $cocok++;
            }
        }
        session_set('cek_tertunda', null);
        $this->json([
            'sukses' => true,
            'detail' => "{$cocok} dari " . count($analysis['bahan']) . ' bahan punya data gizi',
            'redirect' => base_url('konfirmasi'),
        ]);
    }

    public function hitungLangkah1(): void {
        $analysis = KonfirmasiController::simpanDariPost();
        $bahan = $analysis['bahan'] ?? [];
        if (empty($bahan)) {
            $this->json(['sukses' => false, 'pesan' => 'Belum ada bahan untuk dihitung.'], 400);
            return;
        }

        $cocok = 0;
        $tanpaData = [];
        foreach ($bahan as $b) {
            $fakta = NutritionFact::findByName((string)$b['nama']);
            if ($fakta !== null && ($fakta['energi_kkal'] !== null || $fakta['protein_g'] !== null)) {
                $cocok++;
            } else {
                $tanpaData[] = $b['nama'];
            }
        }
        $this->json([
            'sukses' => true,
            'cocok' => $cocok,
            'total' => count($bahan),
            'tanpa_data' => $tanpaData,
            'detail' => "{$cocok} dari " . count($bahan) . ' bahan punya data gizi',
        ]);
    }

    public function hitungLangkah2(): void {
        $analysis = session_get('analysis_data');
        if (empty($analysis['bahan'])) {
            $this->json(['sukses' => false, 'pesan' => 'Sesi kedaluwarsa, ulangi dari cek bahan.'], 400);
            return;
        }
        $gizi = NutritionCalculator::hitung($analysis['bahan'], (float)($analysis['porsi'] ?? 1.0));
        if (empty($gizi['sukses'])) {
            $this->json(['sukses' => false, 'pesan' => $gizi['pesan'] ?? 'Tidak ada bahan yang punya data gizi.'], 422);
            return;
        }
        $this->json([
            'sukses' => true,
            'detail' => number_format((int)$gizi['energi_kkal'], 0, ',', '.') . ' kkal, protein ' . $gizi['protein_g'] . ' g, lemak ' . $gizi['lemak_g'] . ' g, karbo ' . $gizi['karbohidrat_g'] . ' g',
        ]);
    }

    public function hitungLangkah3(): void {
        $analysis = session_get('analysis_data');
        $profile = session_get('user_profile');
        if (empty($analysis['bahan']) || empty($profile)) {
            $this->json(['sukses' => false, 'pesan' => 'Sesi kedaluwarsa, ulangi dari awal.'], 400);
            return;
        }
        $hasil = ResultBuilder::build($analysis, $profile);
        if (empty($hasil['sukses'])) {
            $this->json(['sukses' => false, 'pesan' => $hasil['pesan'] ?? 'Gagal menilai profil.'], 422);
            return;
        }
        $tertinggi = null;
        foreach ($hasil['risiko'] as $r) {
            if (!empty($r['dinilai']) && ($tertinggi === null || $r['persen'] > $tertinggi['persen'])) {
                $tertinggi = $r;
            }
        }
        $detail = $hasil['verdict']['level'];
        if ($tertinggi !== null) {
            $detail .= ', ' . mb_strtolower($tertinggi['nama']) . ' tertinggi ' . $tertinggi['persen'] . '%';
        }
        $this->json(['sukses' => true, 'detail' => $detail, 'redirect' => base_url('hasil')]);
    }

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
