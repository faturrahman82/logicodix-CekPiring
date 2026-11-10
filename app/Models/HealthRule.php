<?php

namespace App\Models;

class HealthRule {
    public const PLAFON_PER_MAKAN = 0.3333;

    public const BATAS_HARIAN = [
        'natrium_mg' => 2000.0,
        'gula_g' => 50.0,
        'lemak_jenuh_g' => 22.0,
        'purin_mg' => 400.0,
    ];

    public static function batas(string $zat, bool $kondisiAktif, int $targetKalori = 2000): array {
        $kkal = max(1000, $targetKalori);
        switch ($zat) {
            case 'natrium_mg':
                return $kondisiAktif
                    ? ['batas' => 1500.0, 'sumber' => 'AHA, hipertensi', 'dinilai' => true]
                    : ['batas' => 2000.0, 'sumber' => 'Permenkes 30/2013', 'dinilai' => true];
            case 'gula_g':
                return $kondisiAktif
                    ? ['batas' => round($kkal * 0.05 / 4, 1), 'sumber' => '5% energi, diabetes', 'dinilai' => true]
                    : ['batas' => 50.0, 'sumber' => 'Permenkes 30/2013', 'dinilai' => true];
            case 'lemak_jenuh_g':
                return $kondisiAktif
                    ? ['batas' => round($kkal * 0.07 / 9, 1), 'sumber' => 'di bawah 7% energi, PERKI 2022', 'dinilai' => true]
                    : ['batas' => round($kkal * 0.10 / 9, 1), 'sumber' => 'di bawah 10% energi, WHO', 'dinilai' => true];
            case 'purin_mg':
                return $kondisiAktif
                    ? ['batas' => 400.0, 'sumber' => 'pedoman gout, Kaneko 2014', 'dinilai' => true]
                    : ['batas' => null, 'sumber' => 'tidak ada batas umum resmi', 'dinilai' => false];
        }
        return ['batas' => null, 'sumber' => '', 'dinilai' => false];
    }

    public static function evaluasiLevel(string $zat, float $nilai, ?float $batas = null): array {
        $batas = $batas ?? (self::BATAS_HARIAN[$zat] ?? 100.0);
        $persen = ($batas > 0) ? ($nilai / $batas) * 100 : 0;

        if ($persen < 20.0) {
            $level = 'Rendah';
            $badge = 'low';
            $simbol = '●';
        } elseif ($persen <= 33.33) {
            $level = 'Sedang';
            $badge = 'med';
            $simbol = '◆';
        } else {
            $level = 'Tinggi';
            $badge = 'high';
            $simbol = '▲';
        }

        return [
            'nilai' => $nilai,
            'batas' => $batas,
            'persen' => round($persen),
            'level' => $level,
            'badge' => $badge,
            'simbol' => $simbol,
            'melewati_plafon' => $persen > 33.33
        ];
    }
}
