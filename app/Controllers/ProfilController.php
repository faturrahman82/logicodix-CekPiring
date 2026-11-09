<?php

namespace App\Controllers;

use Core\Controller;
use App\Services\TargetCalculator;

class ProfilController extends Controller {
    public function index(): void {
        $profile = session_get('user_profile', [
            'tujuan' => 'seimbang',
            'gender' => 'perempuan',
            'umur' => '',
            'tinggi' => '',
            'berat' => '',
            'kondisi' => [
                'diabetes' => false,
                'hipertensi' => false,
                'kolesterol' => false,
                'asam_urat' => false,
            ],
            'target_kalori' => 2000
        ]);

        $error = session_get('flash_error');
        session_set('flash_error', null);

        $this->render('profil', [
            'profile' => $profile,
            'error' => $error
        ]);
    }

    /**
     * Reset semua data (Langkah 1 profil & Langkah 2 makanan) saat klik "Analisis makanan lain"
     */
    public function reset(): void {
        session_set('user_profile', null);
        session_set('analysis_data', null);
        session_set('flash_error', null);
        $this->redirect('profil');
    }

    public function save(): void {
        $tujuan = $_POST['tujuan'] ?? 'seimbang';
        $gender = $_POST['gender'] ?? 'perempuan';
        $umurRaw = trim($_POST['umur'] ?? '');
        $tinggiRaw = trim($_POST['tinggi'] ?? '');
        $beratRaw = trim($_POST['berat'] ?? '');

        // Validasi: Data fisik wajib diisi angka saat klik Simpan Profil
        if ($umurRaw === '' || $tinggiRaw === '' || $beratRaw === '') {
            $missing = [];
            if ($umurRaw === '') $missing[] = 'umur';
            if ($tinggiRaw === '') $missing[] = 'tinggi';
            if ($beratRaw === '') $missing[] = 'berat';

            session_set('flash_error', 'Mohon isi data fisik (' . implode(', ', $missing) . ') berupa angka terlebih dahulu sebelum menyimpan profil. Jika tidak ingin mengisi, gunakan opsi "Lewati, pakai target umum".');
            session_set('user_profile', array_merge(session_get('user_profile', []), [
                'tujuan' => $tujuan,
                'gender' => $gender,
                'umur' => $umurRaw,
                'tinggi' => $tinggiRaw,
                'berat' => $beratRaw,
                'kondisi' => $kondisi
            ]));
            $this->redirect('profil');
            return;
        }

        if (!is_numeric($umurRaw) || !is_numeric($tinggiRaw) || !is_numeric($beratRaw)) {
            session_set('flash_error', 'Inputan data fisik hanya boleh berupa angka.');
            session_set('user_profile', array_merge(session_get('user_profile', []), [
                'tujuan' => $tujuan,
                'gender' => $gender,
                'umur' => $umurRaw,
                'tinggi' => $tinggiRaw,
                'berat' => $beratRaw,
                'kondisi' => $kondisi
            ]));
            $this->redirect('profil');
            return;
        }

        $umur = (float)$umurRaw;
        $tinggi = (float)$tinggiRaw;
        $berat = (float)$beratRaw;

        // Validasi batas wajar data fisik
        if ($umur < 1 || $umur > 120 || $tinggi < 50 || $tinggi > 250 || $berat < 20 || $berat > 300) {
            session_set('flash_error', 'Mohon masukkan angka data fisik yang valid dan wajar (Umur: 1–120 thn, Tinggi: 50–250 cm, Berat: 20–300 kg).');
            session_set('user_profile', array_merge(session_get('user_profile', []), [
                'tujuan' => $tujuan,
                'gender' => $gender,
                'umur' => $umurRaw,
                'tinggi' => $tinggiRaw,
                'berat' => $beratRaw,
                'kondisi' => $kondisi
            ]));
            $this->redirect('profil');
            return;
        }

        $target = TargetCalculator::hitung($gender, $umur, $tinggi, $berat, $tujuan);

        $profile = [
            'tujuan' => $tujuan,
            'gender' => $gender,
            'umur' => $umur,
            'tinggi' => $tinggi,
            'berat' => $berat,
            'kondisi' => $kondisi,
            'target_kalori' => $target['target_kalori'],
            'rumus' => $target['rumus'],
            'is_custom' => $target['is_custom']
        ];

        session_set('user_profile', $profile);
        $this->redirect('makanan');
    }

    public function skip(): void {
        $target = TargetCalculator::hitung(null, null, null, null, 'seimbang');

        $profile = [
            'tujuan' => 'seimbang',
            'gender' => 'perempuan',
            'umur' => null,
            'tinggi' => null,
            'berat' => null,
            'kondisi' => [
                'diabetes' => false,
                'hipertensi' => false,
                'kolesterol' => false,
                'asam_urat' => false,
            ],
            'target_kalori' => $target['target_kalori'],
            'rumus' => $target['rumus'],
            'is_custom' => false
        ];

        session_set('user_profile', $profile);
        $this->redirect('makanan');
    }
}
