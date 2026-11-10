<?php

namespace App\Services;

use App\Models\HealthRule;

class RiskEvaluator {
    public const ZAT = [
        'natrium' => ['kunci' => 'natrium_mg', 'kondisi' => 'hipertensi', 'nama' => 'Natrium', 'satuan' => 'mg', 'label_kondisi' => 'hipertensi'],
        'gula' => ['kunci' => 'gula_g', 'kondisi' => 'diabetes', 'nama' => 'Gula', 'satuan' => 'g', 'label_kondisi' => 'diabetes'],
        'lemak_jenuh' => ['kunci' => 'lemak_jenuh_g', 'kondisi' => 'kolesterol', 'nama' => 'Lemak jenuh', 'satuan' => 'g', 'label_kondisi' => 'kolesterol tinggi'],
        'purin' => ['kunci' => 'purin_mg', 'kondisi' => 'asam_urat', 'nama' => 'Purin', 'satuan' => 'mg', 'label_kondisi' => 'asam urat tinggi'],
    ];

    public static function evaluasi(array $totalGizi, ?array $kondisiKesehatan, int $targetKalori = 2000): array {
        $kondisiKesehatan = $kondisiKesehatan ?? [];
        $statusNutrisi = $totalGizi['status_nutrisi'] ?? [];
        $hasil = [];

        foreach (self::ZAT as $kode => $cfg) {
            $status = $statusNutrisi[$cfg['kunci']] ?? null;
            $tersedia = $status === null ? true : !empty($status['tersedia']);
            $nilai = (float)($totalGizi[$cfg['kunci']] ?? 0);
            $aktif = !empty($kondisiKesehatan[$cfg['kondisi']]);

            $aturan = HealthRule::batas($cfg['kunci'], $aktif, $targetKalori);
            if ($tersedia && !$aturan['dinilai']) {
                $eval = [
                    'nilai' => $nilai,
                    'batas' => null,
                    'persen' => null,
                    'level' => 'Info',
                    'badge' => 'none',
                    'simbol' => 'ℹ',
                    'melewati_plafon' => false,
                ];
            } elseif ($tersedia) {
                $eval = HealthRule::evaluasiLevel($cfg['kunci'], $nilai, $aturan['batas']);
            } else {
                $eval = [
                    'nilai' => null,
                    'batas' => HealthRule::BATAS_HARIAN[$cfg['kunci']] ?? null,
                    'persen' => null,
                    'level' => 'Belum bisa dinilai',
                    'badge' => 'none',
                    'simbol' => '–',
                    'melewati_plafon' => false,
                ];
            }

            $eval['kode'] = $kode;
            $eval['nama'] = $cfg['nama'];
            $eval['satuan'] = $cfg['satuan'];
            $eval['tersedia'] = $tersedia;
            $eval['dinilai'] = $tersedia && $aturan['dinilai'];
            $eval['sumber_batas'] = $aturan['sumber'];
            $eval['sebagian'] = $tersedia && (($status['status'] ?? '') === 'sebagian');
            $eval['kondisi_aktif'] = $aktif;
            $teksBatas = $aturan['batas'] !== null
                ? ' (' . number_format($aturan['batas'], $cfg['satuan'] === 'g' ? 0 : 0, ',', '.') . ' ' . $cfg['satuan'] . ')'
                : '';
            $eval['keterangan_profil'] = $aturan['dinilai']
                ? ($aktif ? 'batas harian untuk ' . $cfg['label_kondisi'] : 'batas harian umum') . $teksBatas
                : 'tidak ada batas umum, dinilai bila asam urat dicentang';
            $eval['penyumbang'] = $totalGizi['penyumbang'][$kode] ?? '';
            $hasil[$kode] = $eval;
        }

        return $hasil;
    }

    public static function terburukAktif(array $risiko): ?array {
        $terburuk = null;
        foreach ($risiko as $r) {
            if (empty($r['kondisi_aktif']) || empty($r['tersedia']) || empty($r['dinilai'])) {
                continue;
            }
            if ($terburuk === null || $r['persen'] > $terburuk['persen']) {
                $terburuk = $r;
            }
        }
        return $terburuk;
    }
}
