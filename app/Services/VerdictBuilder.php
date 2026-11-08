<?php

namespace App\Services;

class VerdictBuilder {
    public static function build(
        array $totalGizi,
        array $risiko,
        array $profil,
        float $porsiDisarankan
    ): array {
        $tujuan = $profil['tujuan'] ?? 'seimbang';
        $tujuanLabel = ucfirst($tujuan);
        $targetKalori = (int)($profil['target_kalori'] ?? 2000);
        $kondisi = $profil['kondisi'] ?? [];
        
        $adaDiabetes = !empty($kondisi['diabetes']);
        $adaHipertensi = !empty($kondisi['hipertensi']);
        $adaKolesterol = !empty($kondisi['kolesterol']);
        $adaAsamUrat = !empty($kondisi['asam_urat']);
        $adaKondisi = $adaDiabetes || $adaHipertensi || $adaKolesterol || $adaAsamUrat;

        $kalori = (int)$totalGizi['energi_kkal'];
        $persenKalori = ($targetKalori > 0) ? round(($kalori / $targetKalori) * 100) : 0;
        $natriumPersen = $risiko['natrium']['persen'] ?? 0;
        $gulaPersen = $risiko['gula']['persen'] ?? 0;

        // State 5b: Conflict between goal and active condition (e.g. bulking with diabetes)
        if ($tujuan === 'bulking' && $adaDiabetes) {
            return [
                'state' => '5b',
                'level' => 'Hati-hati',
                'badge_class' => 'high',
                'badge_text' => '▲ Hati-hati untuk profilmu',
                'judul' => 'Bulking butuh tambahan kalori, tapi karbohidrat dibatasi diabetes.',
                'deskripsi' => "Porsi penuh membawa {$totalGizi['karbohidrat_g']} g karbohidrat dan gula {$totalGizi['gula_g']} g ({$gulaPersen}% batas harian). Makan {$porsiDisarankan} porsi, lalu tambah kalori dari lauk protein seperti telur atau ayam, bukan dari nasi."
            ];
        }

        // State 5c: Evaluation for profile with no chronic conditions
        if (!$adaKondisi) {
            $level = ($natriumPersen > 33 || $gulaPersen > 33) ? 'Cukup cocok' : 'Cocok';
            $badgeClass = ($level === 'Cocok') ? 'low' : 'med';
            $badgeSymbol = ($level === 'Cocok') ? '●' : '◆';

            return [
                'state' => '5c',
                'level' => $level,
                'badge_class' => $badgeClass,
                'badge_text' => "{$badgeSymbol} {$level} untuk targetmu",
                'judul' => "Cocok untuk target {$tujuanLabel}, perhatikan natriumnya.",
                'deskripsi' => "Porsi penuh memakai {$persenKalori}% kalori harianmu ({$kalori} dari " . number_format($targetKalori, 0, ',', '.') . " kkal). Natrium sudah {$natriumPersen}% batas umum harian, jadi kurangi kecap manis atau kuah asin. Protein baru {$totalGizi['protein_g']} g, tambahkan lauk protein agar kebutuhan hariannya terpenuhi."
            ];
        }

        // State 5: Evaluation against active conditions
        $pelanggaran = [];
        if ($adaHipertensi && $natriumPersen > 33) {
            $pelanggaran[] = 'Natrium tinggi';
        }
        if ($adaDiabetes && $gulaPersen > 20) {
            $pelanggaran[] = ($gulaPersen > 33) ? 'gula tinggi' : 'gula sedang';
        }
        if ($adaKolesterol && ($risiko['lemak_jenuh']['persen'] ?? 0) > 33) {
            $pelanggaran[] = 'lemak jenuh tinggi';
        }
        if ($adaAsamUrat && ($risiko['purin']['persen'] ?? 0) > 33) {
            $pelanggaran[] = 'purin tinggi';
        }

        if (!empty($pelanggaran)) {
            $porsiKoma = str_replace('.', ',', (string)$porsiDisarankan);
            $kaloriPorsi = round($kalori * $porsiDisarankan);
            $persenPorsi = ($targetKalori > 0) ? round(($kaloriPorsi / $targetKalori) * 100) : 0;

            return [
                'state' => '5',
                'level' => 'Hati-hati',
                'badge_class' => 'high',
                'badge_text' => '▲ Hati-hati untuk profilmu',
                'judul' => implode(' dan ', $pelanggaran) . ' di porsi ini.',
                'deskripsi' => "Makan sekitar {$porsiKoma} porsi dan kurangi penyumbang utamanya. Dengan {$porsiKoma} porsi, kalorinya sekitar {$kaloriPorsi} kkal ({$persenPorsi}% target {$tujuanLabel} harianmu). Tambahkan lauk protein untuk melengkapi, bukan nasi."
            ];
        }

        return [
            'state' => '5',
            'level' => 'Cocok',
            'badge_class' => 'low',
            'badge_text' => '● Cocok untuk profilmu',
            'judul' => "Porsi ini aman untuk kondisi kesehatanmu.",
            'deskripsi' => "Kandungan natrium dan gula masih dalam batas aman per waktu makan ({$persenKalori}% dari target harian " . number_format($targetKalori, 0, ',', '.') . " kkal)."
        ];
    }
}
