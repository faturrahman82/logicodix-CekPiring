<?php

namespace App\Services;

class TargetCalculator {
    public const DEFAULT_KALORI = 2000;
    public const FAKTOR_TUJUAN = ['cutting' => 0.85, 'bulking' => 1.15, 'seimbang' => 1.0];

    private static ?array $akgCache = null;

    public static function hitung(?string $gender, ?float $umur, ?float $tinggi, ?float $berat, string $tujuan = 'seimbang'): array {
        $faktor = self::FAKTOR_TUJUAN[$tujuan] ?? 1.0;
        $akg = (!empty($gender) && !empty($umur)) ? self::akg($gender, (int)$umur) : null;

        $kaloriRumus = null;
        if (!empty($gender) && !empty($umur) && !empty($tinggi) && !empty($berat)) {
            $bmr = (10 * $berat) + (6.25 * $tinggi) - (5 * $umur) + ($gender === 'laki-laki' ? 5 : -161);
            $kaloriRumus = (int)round($bmr * 1.375);
        }

        if ($akg === null) {
            return [
                'bmr' => null,
                'tdee' => self::DEFAULT_KALORI,
                'target_kalori' => (int)round(self::DEFAULT_KALORI * $faktor),
                'kalori_dasar' => self::DEFAULT_KALORI,
                'kalori_rumus' => $kaloriRumus,
                'protein_harian' => null,
                'akg_kelompok' => null,
                'rumus' => 'Target umum edukatif (data fisik dilewati)',
                'is_custom' => false,
            ];
        }

        $kelompok = ($gender === 'laki-laki' ? 'Laki-laki' : 'Perempuan') . ' ' . self::labelUmur($akg);
        $rumus = 'AKG Kemenkes 2019 untuk ' . mb_strtolower($kelompok) . ': ' . number_format($akg['energi_kkal'], 0, ',', '.') . ' kkal';
        if ($kaloriRumus !== null) {
            $rumus .= '. Perkiraan pribadi dari tinggi dan berat: sekitar ' . number_format($kaloriRumus, 0, ',', '.') . ' kkal';
        }

        return [
            'bmr' => null,
            'tdee' => $akg['energi_kkal'],
            'target_kalori' => (int)round($akg['energi_kkal'] * $faktor),
            'kalori_dasar' => $akg['energi_kkal'],
            'kalori_rumus' => $kaloriRumus,
            'protein_harian' => $akg['protein_g'],
            'akg_kelompok' => $kelompok,
            'rumus' => $rumus,
            'is_custom' => true,
        ];
    }

    public static function akg(string $gender, int $umur): ?array {
        if ($umur < 10) {
            return null;
        }
        $jk = ($gender === 'laki-laki') ? 'laki-laki' : 'perempuan';
        foreach (self::tabelAkg() as $r) {
            if ($r['jenis_kelamin'] === $jk && $umur >= $r['umur_min'] && $umur <= $r['umur_maks']) {
                return $r;
            }
        }
        return null;
    }

    public static function tabelAkg(): array {
        if (self::$akgCache !== null) {
            return self::$akgCache;
        }
        $path = __DIR__ . '/../../data/akg_2019.csv';
        $rows = [];
        if (is_file($path) && ($h = fopen($path, 'r')) !== false) {
            $head = fgetcsv($h, 0, ',', '"', '');
            while (($d = fgetcsv($h, 0, ',', '"', '')) !== false) {
                if ($head && count($d) === count($head)) {
                    $r = array_combine($head, $d);
                    foreach ($r as $k => $v) {
                        if ($k !== 'jenis_kelamin') {
                            $r[$k] = (int)$v;
                        }
                    }
                    $rows[] = $r;
                }
            }
            fclose($h);
        }
        return self::$akgCache = $rows;
    }

    private static function labelUmur(array $r): string {
        return $r['umur_maks'] >= 200 ? "{$r['umur_min']} tahun ke atas" : "{$r['umur_min']}-{$r['umur_maks']} tahun";
    }
}
