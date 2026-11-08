<?php

namespace App\Services;

class PortionAdvisor {
    /**
     * Hitung rekomendasi porsi berdasarkan BR-07
     * Porsi disarankan = 33% dibagi persentase terburuk dari batas yang aktif, maksimal 1 porsi
     */
    public static function hitung(array $risiko, bool $adaKondisiAktif): float {
        if (!$adaKondisiAktif) {
            return 1.0;
        }

        $terburukPersen = 0.0;

        foreach ($risiko as $r) {
            if (!empty($r['kondisi_aktif'])) {
                if ($r['persen'] > $terburukPersen) {
                    $terburukPersen = (float)$r['persen'];
                }
            }
        }

        // Jika tidak ada batas aktif yang melewati plafon 33%
        if ($terburukPersen <= 33.33) {
            return 1.0;
        }

        // Rumus BR-07: 33% / persentase terburuk
        $fraksi = 33.33 / $terburukPersen;
        
        // Dibulatkan ke kelipatan 0.1 terdekat, min 0.3, max 1.0
        $fraksiBulat = round($fraksi, 1);
        if ($fraksiBulat > 1.0) {
            $fraksiBulat = 1.0;
        } elseif ($fraksiBulat < 0.3) {
            $fraksiBulat = 0.3;
        }

        return $fraksiBulat;
    }
}
