<?php

namespace App\Services;

class VerdictBuilder {
    public static function build(
        array $totalGizi,
        array $risiko,
        array $profil,
        float $porsiDisarankan,
        float $porsi = 1.0
    ): array {
        $tujuan = $profil['tujuan'] ?? 'seimbang';
        $targetKalori = (int)($profil['target_kalori'] ?? 2000);
        $kondisi = $profil['kondisi'] ?? [];
        $adaKondisi = !empty(array_filter((array)$kondisi));

        $kalori = (int)($totalGizi['energi_kkal'] ?? 0);
        $persenKalori = ($targetKalori > 0) ? (int)round(($kalori / $targetKalori) * 100) : 0;

        $belumDinilai = [];
        foreach ($risiko as $r) {
            if (!empty($r['kondisi_aktif']) && empty($r['tersedia'])) {
                $belumDinilai[] = mb_strtolower($r['nama']);
            }
        }
        $catatan = !empty($belumDinilai)
            ? ucfirst(implode(' dan ', $belumDinilai)) . ' belum bisa dinilai: data bahannya belum tersedia.'
            : '';

        if (!$adaKondisi) {
            $tertinggi = null;
            foreach ($risiko as $r) {
                if (!empty($r['tersedia']) && !empty($r['dinilai']) && ($tertinggi === null || $r['persen'] > $tertinggi['persen'])) {
                    $tertinggi = $r;
                }
            }
            if ($tertinggi !== null && $tertinggi['persen'] > 33) {
                return self::hasil(
                    'Cukup cocok',
                    'med',
                    "{$tertinggi['nama']} {$tertinggi['persen']}% batas harian umum di porsi ini.",
                    'Cukup tinggi dibanding batas umum. Lihat saran perbaikan di bawah.',
                    $catatan
                );
            }
            $besar = $persenKalori > 40;
            return self::hasil(
                $besar ? 'Cukup cocok' : 'Cocok',
                $besar ? 'med' : 'low',
                "Porsi ini {$persenKalori}% dari target kalori harianmu.",
                $besar ? 'Cukup besar untuk sekali makan, imbangi di waktu makan lain.' : 'Masih wajar untuk sekali makan.',
                $catatan
            );
        }

        $terburuk = RiskEvaluator::terburukAktif($risiko);

        if ($terburuk === null) {
            return self::hasil(
                'Belum bisa dinilai',
                'med',
                'Data zat untuk kondisimu belum tersedia di bahan ini.',
                'Angka kalori dan makro tetap bisa dipakai sebagai gambaran.',
                $catatan
            );
        }

        $nama = $terburuk['nama'];
        $persen = (int)$terburuk['persen'];
        $porsiTeks = PortionSize::formatPorsi($porsiDisarankan);

        if ($persen > 33) {
            $saran = $porsiDisarankan < $porsi
                ? "Makan sekitar {$porsiTeks} piring agar tidak lebih dari 33%."
                : 'Kurangi penyumbang terbesarnya agar tidak lebih dari 33%.';
            if ($tujuan === 'bulking' && !empty($kondisi['diabetes'])) {
                $saran .= ' Tambah kalori dari lauk protein, bukan nasi.';
            }
            return self::hasil(
                'Hati-hati',
                'high',
                "{$nama} {$persen}% batas harian di porsi ini.",
                $saran,
                $catatan
            );
        }

        if ($persen >= 20) {
            return self::hasil(
                'Cukup cocok',
                'med',
                "{$nama} {$persen}% batas harian, masih di bawah batas sekali makan.",
                'Jaga waktu makan berikutnya tetap rendah ' . mb_strtolower($nama) . '.',
                $catatan
            );
        }

        return self::hasil(
            'Cocok',
            'low',
            'Aman untuk kondisimu di porsi ini.',
            "{$nama} hanya {$persen}% batas harian.",
            $catatan
        );
    }

    private static function hasil(string $level, string $badge, string $judul, string $deskripsi, string $catatan): array {
        $simbol = ['low' => '●', 'med' => '◆', 'high' => '▲'][$badge] ?? '◆';
        return [
            'level' => $level,
            'badge_class' => $badge,
            'badge_text' => "{$simbol} {$level}",
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'catatan' => $catatan,
        ];
    }
}
