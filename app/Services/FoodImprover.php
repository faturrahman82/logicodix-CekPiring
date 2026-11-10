<?php

namespace App\Services;

use App\Models\NutritionFact;

class FoodImprover {
    private const MAKS_SARAN = 3;

    private const PENGGANTI = [
        'minyak kelapa sawit' => 'Minyak kedelai',
        'mentega' => 'Margarin',
        'susu kental manis' => 'Susu sapi',
        'ayam' => 'Dada ayam tanpa kulit',
    ];

    public static function buatSemua(array $bahan, float $porsi, array $risiko, array $totalGizi, array $profil): array {
        $kandidat = [];
        foreach ($risiko as $kode => $r) {
            if (empty($r['tersedia']) || empty($r['dinilai']) || !isset(RiskEvaluator::ZAT[$kode])) {
                continue;
            }
            $aktif = !empty($r['kondisi_aktif']);
            if ($r['persen'] > 33 || ($aktif && $r['persen'] >= 20)) {
                $kandidat[] = [
                    'kode' => $kode,
                    'nama' => $r['nama'],
                    'kunci' => RiskEvaluator::ZAT[$kode]['kunci'],
                    'persen' => (int)$r['persen'],
                    'aktif' => $aktif,
                    'satuan_persen' => 'batas harian',
                ];
            }
        }
        usort($kandidat, function ($a, $b) {
            if ($a['aktif'] !== $b['aktif']) {
                return $a['aktif'] ? -1 : 1;
            }
            return $b['persen'] <=> $a['persen'];
        });

        if (empty($kandidat)) {
            $target = (int)($profil['target_kalori'] ?? 2000);
            $persenKalori = $target > 0 ? (int)round(($totalGizi['energi_kkal'] ?? 0) / $target * 100) : 0;
            if ($persenKalori > 40) {
                $kandidat[] = ['kode' => 'energi', 'nama' => 'Kalori', 'kunci' => 'energi_kkal', 'persen' => $persenKalori, 'aktif' => false, 'satuan_persen' => 'target harian'];
            }
        }

        $hasil = [];
        foreach ($kandidat as $t) {
            $saran = self::untukTarget($t, $bahan, $porsi, $totalGizi, $profil);
            if ($saran === null) {
                continue;
            }
            $gabung = false;
            foreach ($hasil as &$ada) {
                if ($ada['kunci_aksi'] === $saran['kunci_aksi']) {
                    $ada['efek'] .= ' · ' . $saran['efek'];
                    $gabung = true;
                    break;
                }
            }
            unset($ada);
            if (!$gabung) {
                $hasil[] = $saran;
            }
            if (count($hasil) >= self::MAKS_SARAN) {
                break;
            }
        }
        return $hasil;
    }

    public static function buat(array $bahan, float $porsi, array $risiko, array $totalGizi, array $profil): ?array {
        return self::buatSemua($bahan, $porsi, $risiko, $totalGizi, $profil)[0] ?? null;
    }

    private static function untukTarget(array $t, array $bahan, float $porsi, array $totalGizi, array $profil): ?array {
        $rincian = $totalGizi['rincian_bahan'] ?? [];
        $opsi = [];

        $indeks = null;
        $terbesar = 0.0;
        foreach ($rincian as $i => $rb) {
            $nilai = (float)($rb['nutrisi'][$t['kunci']] ?? 0);
            if (!empty($rb['ada_data']) && $nilai > $terbesar) {
                $terbesar = $nilai;
                $indeks = $i;
            }
        }
        if ($indeks !== null && isset($bahan[$indeks])) {
            $gramLama = (float)$bahan[$indeks]['berat_g'];
            $gramBaru = max(1, round($gramLama / 2));
            if ($gramBaru < $gramLama) {
                $baru = $bahan;
                $baru[$indeks]['berat_g'] = $gramBaru;
                $opsi[] = [
                    'jenis' => 'kurangi',
                    'indeks' => $indeks,
                    'gram_lama' => (int)$gramLama,
                    'gram_baru' => (int)$gramBaru,
                    'nama_baru' => null,
                    'bahan_baru' => $baru,
                    'aksi' => 'Kurangi ' . mb_strtolower($bahan[$indeks]['nama']) . " {$gramLama} → {$gramBaru} g",
                    'kunci_aksi' => "kurangi:{$indeks}:{$gramBaru}",
                ];
            }
        }

        {
            foreach ($bahan as $i => $b) {
                if (empty($rincian[$i]['ada_data']) || (float)($rincian[$i]['nutrisi'][$t['kunci']] ?? 0) <= 0) {
                    continue;
                }
                $fakta = NutritionFact::findByName((string)$b['nama']);
                $kunciBahan = $fakta['nama_kunci'] ?? '';
                if (!isset(self::PENGGANTI[$kunciBahan])) {
                    continue;
                }
                $namaPengganti = self::PENGGANTI[$kunciBahan];
                $faktaBaru = NutritionFact::findByName($namaPengganti);
                if ($faktaBaru === null || $faktaBaru[$t['kunci']] === null || $faktaBaru['energi_kkal'] === null) {
                    continue;
                }
                $baru = $bahan;
                $baru[$i]['nama'] = $namaPengganti;
                $opsi[] = [
                    'jenis' => 'ganti',
                    'indeks' => $i,
                    'gram_lama' => (int)$b['berat_g'],
                    'gram_baru' => (int)$b['berat_g'],
                    'nama_baru' => $namaPengganti,
                    'bahan_baru' => $baru,
                    'aksi' => 'Ganti ' . mb_strtolower($b['nama']) . ' dengan ' . mb_strtolower($namaPengganti),
                    'kunci_aksi' => "ganti:{$i}:{$namaPengganti}",
                ];
            }
        }

        $terbaik = null;
        foreach ($opsi as $o) {
            $gizi = NutritionCalculator::hitung($o['bahan_baru'], $porsi);
            if (empty($gizi['sukses'])) {
                continue;
            }
            $persenBaru = self::persenTarget($t, $gizi, $profil);
            if ($persenBaru === null || $persenBaru >= $t['persen']) {
                continue;
            }
            $o['persen_baru'] = $persenBaru;
            $o['kkal_hemat'] = (int)round(($totalGizi['energi_kkal'] ?? 0) - ($gizi['energi_kkal'] ?? 0));
            $lebihBaik = $terbaik === null
                || $persenBaru < $terbaik['persen_baru']
                || ($persenBaru === $terbaik['persen_baru'] && $o['jenis'] === 'ganti');
            if ($lebihBaik) {
                $terbaik = $o;
            }
        }
        if ($terbaik === null) {
            return null;
        }

        return [
            'zat' => $t['kode'],
            'nama_zat' => $t['nama'],
            'jenis' => $terbaik['jenis'],
            'indeks' => $terbaik['indeks'],
            'bahan' => $bahan[$terbaik['indeks']]['nama'],
            'nama_baru' => $terbaik['nama_baru'],
            'gram_lama' => $terbaik['gram_lama'],
            'gram_baru' => $terbaik['gram_baru'],
            'persen_lama' => $t['persen'],
            'persen_baru' => $terbaik['persen_baru'],
            'kkal_hemat' => $terbaik['kkal_hemat'],
            'satuan_persen' => $t['satuan_persen'],
            'aksi' => $terbaik['aksi'],
            'efek' => "{$t['nama']} {$t['persen']}% → {$terbaik['persen_baru']}% {$t['satuan_persen']}",
            'kunci_aksi' => $terbaik['kunci_aksi'],
        ];
    }

    private static function persenTarget(array $t, array $gizi, array $profil): ?int {
        if ($t['kode'] === 'energi') {
            $target = (int)($profil['target_kalori'] ?? 2000);
            return $target > 0 ? (int)round(($gizi['energi_kkal'] ?? 0) / $target * 100) : null;
        }
        $risikoBaru = RiskEvaluator::evaluasi($gizi, $profil['kondisi'] ?? [], (int)($profil['target_kalori'] ?? 2000));
        $r = $risikoBaru[$t['kode']] ?? null;
        return ($r && !empty($r['tersedia'])) ? (int)$r['persen'] : null;
    }
}
