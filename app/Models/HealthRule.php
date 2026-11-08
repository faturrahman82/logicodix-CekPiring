<?php

namespace App\Models;

class HealthRule {
    public const BATAS_HARIAN = [
        'natrium_mg' => 2000.0,    // Standar WHO
        'gula_g' => 25.0,           // Standar WHO / Profil Diabetes
        'lemak_jenuh_g' => 20.0,    // Profil Kolesterol tinggi
        'purin_mg' => 400.0,        // Profil Asam urat
    ];

    public const PLAFON_PER_MAKAN = 0.3333; // Sepertiga batas harian

    public static function evaluasiLevel(string $zat, float $nilai): array {
        $batas = self::BATAS_HARIAN[$zat] ?? 100.0;
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
