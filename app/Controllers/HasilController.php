<?php

namespace App\Controllers;

use Core\Controller;
use App\Services\ResultBuilder;

class HasilController extends Controller {
    public function menghitung(): void {
        $this->redirect('hasil');
        return;
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

        $hasil = ResultBuilder::build($analysis, $profile);
        if (empty($hasil['sukses'])) {
            $this->redirect('galat');
            return;
        }

        $this->render('hasil', array_merge($hasil, [
            'profile' => $profile,
            'analysis' => $analysis,
            'saran_diterapkan' => self::daftarDiterapkan($analysis['saran_diterapkan'] ?? []),
        ]));
    }

    public function galat(): void {
        $this->render('galat', [
            'pesan_judul' => 'Estimasi belum bisa dihitung',
            'pesan_deskripsi' => 'Layanan analisis sedang tidak bisa dihubungi, atau belum ada bahan yang punya data gizi. Coba hitung lagi atau kembali ke bahan.',
            'kembali_url' => base_url('konfirmasi')
        ]);
    }

    public static function daftarDiterapkan($data): array {
        if (empty($data) || !is_array($data)) {
            return [];
        }
        return isset($data['aksi']) ? [$data] : array_values($data);
    }
}
