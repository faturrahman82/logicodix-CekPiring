<?php

namespace App\Services;

class ClinicalAdvisor {
    private const LABEL_KONDISI = [
        'diabetes' => 'diabetes',
        'hipertensi' => 'hipertensi',
        'kolesterol' => 'kolesterol tinggi',
        'asam_urat' => 'asam urat tinggi',
    ];

    public static function generate(array $totalGizi, array $risiko, array $profile, array $analysis): array {
        $kondisi = $profile['kondisi'] ?? [];
        $aktif = [];
        foreach (self::LABEL_KONDISI as $kunci => $label) {
            if (!empty($kondisi[$kunci])) {
                $aktif[] = $label;
            }
        }

        $terburuk = RiskEvaluator::terburukAktif($risiko);
        if ($terburuk === null) {
            foreach ($risiko as $r) {
                if (!empty($r['dinilai']) && ($terburuk === null || $r['persen'] > $terburuk['persen'])) {
                    $terburuk = $r;
                }
            }
        }

        $target = (int)($profile['target_kalori'] ?? 2000);
        $persenKalori = $target > 0 ? (int)round(($totalGizi['energi_kkal'] ?? 0) / $target * 100) : 0;

        if ($terburuk !== null && $terburuk['persen'] >= 20) {
            $penyumbang = preg_replace('/^Terbesar dari\s+/i', '', (string)($terburuk['penyumbang'] ?? ''));
            $penyumbang = rtrim($penyumbang, '.');
            $untuk = !empty($aktif) && !empty($terburuk['kondisi_aktif']) ? 'Untuk ' . implode(' dan ', $aktif) . ', ' : '';
            $kalimat = $untuk . mb_strtolower($terburuk['nama']) . " paling tinggi ({$terburuk['persen']}% batas harian)";
            $kalimat .= $penyumbang !== '' ? ", terutama dari {$penyumbang}." : '.';
            $kalimat = mb_strtoupper(mb_substr($kalimat, 0, 1)) . mb_substr($kalimat, 1);
        } else {
            $kalimat = "Porsi ini {$persenKalori}% dari target kalori harianmu dan tidak ada zat yang melewati batas.";
        }

        return [
            'profil_label' => !empty($aktif) ? implode(' & ', $aktif) : 'tanpa kondisi',
            'kalimat' => $kalimat,
        ];
    }
}
