<?php

namespace App\Services;

class PortionAdvisor {
    public static function hitung(array $risiko, bool $adaKondisiAktif): float {
        if (!$adaKondisiAktif) {
            return 1.0;
        }

        $terburuk = RiskEvaluator::terburukAktif($risiko);
        if ($terburuk === null || (float)$terburuk['persen'] <= 33.33) {
            return 1.0;
        }

        $fraksi = floor((33.33 / (float)$terburuk['persen']) * 10) / 10;
        return max(0.3, min(1.0, $fraksi));
    }
}
