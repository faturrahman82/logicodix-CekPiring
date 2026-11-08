<?php

namespace App\Controllers;

use Core\Controller;
use App\Services\TargetCalculator;

class ProfilController extends Controller {
    public function index(): void {
        $profile = session_get('user_profile', [
            'tujuan' => 'cutting',
            'gender' => 'perempuan',
            'umur' => '',
            'tinggi' => '',
            'berat' => '',
            'kondisi' => [
                'diabetes' => true,
                'hipertensi' => false,
                'kolesterol' => false,
                'asam_urat' => false,
            ],
            'target_kalori' => 1600
        ]);

        $this->render('profil', [
            'profile' => $profile
        ]);
    }

    public function save(): void {
        $tujuan = $_POST['tujuan'] ?? 'seimbang';
        $gender = $_POST['gender'] ?? 'perempuan';
        $umur = !empty($_POST['umur']) ? (float)$_POST['umur'] : null;
        $tinggi = !empty($_POST['tinggi']) ? (float)$_POST['tinggi'] : null;
        $berat = !empty($_POST['berat']) ? (float)$_POST['berat'] : null;

        $kondisi = [
            'diabetes' => !empty($_POST['diabetes']),
            'hipertensi' => !empty($_POST['hipertensi']),
            'kolesterol' => !empty($_POST['kolesterol']),
            'asam_urat' => !empty($_POST['asam_urat']),
        ];

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
