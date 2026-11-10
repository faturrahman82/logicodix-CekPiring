<?php

namespace App\Services;

class PortionSize {
    public const FAKTOR = [
        'kecil' => 0.7,
        'sedang' => 1.0,
        'besar' => 1.3,
    ];

    public const LABEL = [
        'kecil' => 'Kecil',
        'sedang' => 'Sedang',
        'besar' => 'Besar',
    ];

    public static function valid(?string $ukuran): string {
        $ukuran = mb_strtolower(trim((string)$ukuran));
        return array_key_exists($ukuran, self::FAKTOR) ? $ukuran : 'sedang';
    }

    public static function bawaan(): array {
        return [
            'ukuran' => 'sedang',
            'sumber' => 'standar',
            'alasan' => '',
        ];
    }

    public static function dariAi(?string $ukuran, ?string $alasan): array {
        $alasan = trim(strip_tags((string)$alasan));
        if (mb_strlen($alasan) > 90) {
            $alasan = mb_substr($alasan, 0, 87) . '...';
        }
        return [
            'ukuran' => self::valid($ukuran),
            'sumber' => 'foto',
            'alasan' => $alasan,
        ];
    }

    public static function skala(array $bahan, string $dari, string $ke): array {
        $rasio = self::FAKTOR[self::valid($ke)] / self::FAKTOR[self::valid($dari)];
        if (abs($rasio - 1.0) < 0.0001) {
            return $bahan;
        }
        foreach ($bahan as &$b) {
            $b['berat_g'] = max(1, round((float)($b['berat_g'] ?? 0) * $rasio));
        }
        unset($b);
        return $bahan;
    }

    public static function label(string $ukuran): string {
        return self::LABEL[self::valid($ukuran)];
    }

    public static function formatPorsi(float $porsi): string {
        $teks = rtrim(rtrim(number_format($porsi, 1, ',', ''), '0'), ',');
        return $teks === '' ? '0' : $teks;
    }
}
