<?php

namespace App\Services;

class TargetCalculator {
    public const DEFAULT_KALORI = 2000;

    public static function hitung(?string $gender, ?float $umur, ?float $tinggi, ?float $berat, string $tujuan = 'seimbang'): array {
        if (empty($gender) || empty($umur) || empty($tinggi) || empty($berat)) {
            // Target umum default
            $tdee = self::DEFAULT_KALORI;
            if ($tujuan === 'cutting') {
                $kalori = $tdee * 0.85; // 1700
            } elseif ($tujuan === 'bulking') {
                $kalori = $tdee * 1.15; // 2300
            } else {
                $kalori = $tdee;
            }

            return [
                'bmr' => null,
                'tdee' => $tdee,
                'target_kalori' => round($kalori),
                'rumus' => 'Target umum edukatif (data fisik dilewati)',
                'is_custom' => false
            ];
        }

        // BMR Mifflin-St Jeor
        if ($gender === 'laki-laki') {
            $bmr = (10 * $berat) + (6.25 * $tinggi) - (5 * $umur) + 5;
        } else {
            $bmr = (10 * $berat) + (6.25 * $tinggi) - (5 * $umur) - 161;
        }

        $tdee = $bmr * 1.375; // Aktivitas ringan

        if ($tujuan === 'cutting') {
            $target = $tdee - ($tdee * 0.15);
        } elseif ($tujuan === 'bulking') {
            $target = $tdee + ($tdee * 0.15);
        } else {
            $target = $tdee;
        }

        return [
            'bmr' => round($bmr),
            'tdee' => round($tdee),
            'target_kalori' => round($target),
            'rumus' => 'Rumus Mifflin-St Jeor, aktivitas ringan',
            'is_custom' => true
        ];
    }
}
