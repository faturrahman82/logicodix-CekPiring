<?php

namespace App\Models;

use Core\Database;

class NutritionFact {
    private static ?array $csvCache = null;
    private static ?array $aliasCache = null;

    public static function normalize(string $name): string {
        $name = mb_strtolower(trim($name), 'UTF-8');
        $name = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $name);
        $name = preg_replace('/\s+/u', ' ', $name);
        return trim($name);
    }

    public static function resolveAlias(string $name, ?array $items = null): string {
        $needle = self::normalize($name);
        if ($needle === '') return '';

        if ($items === null) {
            $items = self::getAllFromCsv();
        }
        $aliases = self::getAliases();

        foreach ($aliases as $aliasRow) {
            foreach (explode('|', (string)($aliasRow['alias'] ?? '')) as $alias) {
                if ($needle === self::normalize($alias)) {
                    return self::normalize((string)$aliasRow['nama_kunci']);
                }
            }
        }

        foreach ($items as $item) {
            $canonical = self::normalize((string)($item['nama_kunci'] ?? $item['nama'] ?? ''));
            $nama = self::normalize((string)($item['nama'] ?? ''));
            if ($needle === $canonical || $needle === $nama) {
                return $canonical;
            }
        }

        $modifiers = [
            'goreng', 'bakar', 'panggang', 'rebus', 'kukus', 'tumis', 'mentah',
            'segar', 'matang', 'suwir', 'lalap', 'lalapan', 'tepung', 'kuah',
            'iris', 'cincang', 'ulek', 'pedas', 'kering', 'renyah', 'kremes', 'geprek'
        ];
        $words = explode(' ', $needle);
        $strippedWords = array_diff($words, $modifiers);
        $stripped = implode(' ', $strippedWords);

        if (!empty($stripped) && $stripped !== $needle) {
            foreach ($aliases as $aliasRow) {
                foreach (explode('|', (string)($aliasRow['alias'] ?? '')) as $alias) {
                    if ($stripped === self::normalize($alias)) {
                        return self::normalize((string)$aliasRow['nama_kunci']);
                    }
                }
            }
            foreach ($items as $item) {
                $canonical = self::normalize((string)($item['nama_kunci'] ?? $item['nama'] ?? ''));
                $nama = self::normalize((string)($item['nama'] ?? ''));
                if ($stripped === $canonical || $stripped === $nama) {
                    return $canonical;
                }
            }
        }

        foreach ($aliases as $aliasRow) {
            foreach (explode('|', (string)($aliasRow['alias'] ?? '')) as $alias) {
                $normAlias = self::normalize($alias);
                if (mb_strlen($normAlias) >= 4 && (self::memuatKata($needle, $normAlias) || self::memuatKata($normAlias, $needle))) {
                    return self::normalize((string)$aliasRow['nama_kunci']);
                }
            }
        }

        return $needle;
    }

    private static function memuatKata(string $teks, string $kata): bool {
        return $kata !== '' && preg_match('/(?:^|\s)' . preg_quote($kata, '/') . '(?:\s|$)/u', $teks) === 1;
    }

    public static function canonicalName(string $name): string {
        $items = self::getAllFromCsv();
        $key = self::resolveAlias($name, $items);
        foreach ($items as $item) {
            if (self::normalize((string)($item['nama_kunci'] ?? '')) === $key) {
                return (string)$item['nama'];
            }
        }
        return trim($name);
    }

    public static function findByName(string $name): ?array {
        $items = self::getAllFromCsv();
        $canonicalKey = self::resolveAlias($name, $items);
        if ($canonicalKey === '') return null;

        $pdo = Database::getConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare(
                    "SELECT * FROM nutrition_facts WHERE LOWER(nama_kunci) = ? LIMIT 1"
                );
                $stmt->execute([$canonicalKey]);
                $result = $stmt->fetch();
                if ($result) return self::formatFact($result);
            } catch (\Throwable $e) {
            }
        }

        foreach ($items as $item) {
            if (self::normalize((string)($item['nama_kunci'] ?? '')) === $canonicalKey) {
                return self::formatFact($item);
            }
        }

        return null;
    }

    public static function search(string $keyword, int $limit = 10): array {
        $needle = self::normalize($keyword);
        if ($needle === '') return [];

        $items = self::getAllFromCsv();
        $ranked = [];

        foreach ($items as $item) {
            $key = self::normalize((string)($item['nama_kunci'] ?? ''));
            $nama = self::normalize((string)($item['nama'] ?? ''));
            $aliases = [];
            foreach (self::getAliases() as $aliasRow) {
                if (self::normalize((string)($aliasRow['nama_kunci'] ?? '')) === $key) {
                    $aliases = array_filter(array_map(
                        [self::class, 'normalize'],
                        explode('|', (string)($aliasRow['alias'] ?? ''))
                    ));
                    break;
                }
            }

            $rank = 99;
            if (in_array($needle, $aliases, true)) {
                $rank = -1;
            } elseif ($key === $needle || $nama === $needle) {
                $rank = 0;
            } elseif (strpos($key, $needle) === 0 || strpos($nama, $needle) === 0) {
                $rank = 1;
            } elseif (preg_match('/(?:^|\s)' . preg_quote($needle, '/') . '(?:\s|$)/u', $nama)) {
                $rank = 2;
            } elseif (strpos($key, $needle) !== false || strpos($nama, $needle) !== false) {
                $rank = 3;
            }

            if ($rank < 99) {
                $ranked[] = ['rank' => $rank, 'item' => $item];
            }
        }

        usort($ranked, function ($a, $b) {
            return $a['rank'] <=> $b['rank'];
        });

        $results = [];
        foreach (array_slice($ranked, 0, max(1, $limit)) as $entry) {
            $item = $entry['item'];
            $fact = self::formatFact($item);
            $results[] = [
                'id' => $fact['id'],
                'nama' => $fact['nama'],
                'energi_kkal' => $fact['energi_kkal'] ?? 0,
                'protein_g' => $fact['protein_g'] ?? 0,
                'karbohidrat_g' => $fact['karbohidrat_g'] ?? 0,
                'lemak_g' => $fact['lemak_g'] ?? 0,
                'sumber_data' => $fact['sumber_data'],
                'tingkat_kelengkapan' => $fact['tingkat_kelengkapan']
            ];
        }
        return $results;
    }

    public static function getAllFromCsv(): array {
        if (self::$csvCache !== null) return self::$csvCache;

        $csvPath = __DIR__ . '/../../data/nutrition_bersih.csv';
        if (!file_exists($csvPath)) return [];

        $rows = [];
        if (($handle = fopen($csvPath, 'r')) !== false) {
            $headers = fgetcsv($handle, 0, ',', '"', '');
            while (($data = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                if (count($headers) === count($data)) {
                    $rows[] = array_combine($headers, $data);
                }
            }
            fclose($handle);
        }

        self::$csvCache = $rows;
        return self::$csvCache;
    }

    public static function getAliases(): array {
        if (self::$aliasCache !== null) return self::$aliasCache;
        $path = __DIR__ . '/../../data/ingredient_aliases.csv';
        if (!file_exists($path)) return self::$aliasCache = [];
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle, 0, ',', '"', '');
            while (($data = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                if (count($headers) === count($data)) {
                    $rows[] = array_combine($headers, $data);
                }
            }
            fclose($handle);
        }
        self::$aliasCache = $rows;
        return self::$aliasCache;
    }

    public static function formatFact(array $row): array {
        $num = function($val) {
            return (isset($val) && is_numeric(trim((string)$val))) ? (float)$val : null;
        };

        $sumber = trim($row['sumber_data'] ?? '');
        if (empty($sumber)) {
            if (!empty($row['kode_tkpi'])) {
                $sumber = 'TKPI 2017 (' . $row['kode_tkpi'] . ')';
            } elseif (!empty($row['kode_usda'])) {
                $sumber = 'USDA FoodData Central';
            } else {
                $sumber = 'TKPI 2017 Kemenkes RI';
            }
        }

        $kelengkapan = trim($row['tingkat_kelengkapan'] ?? '');
        if (empty($kelengkapan)) {
            $hasMacro = $num($row['energi_kkal'] ?? null) !== null && $num($row['protein_g'] ?? null) !== null;
            $hasMicros = $num($row['natrium_mg'] ?? null) !== null && $num($row['serat_g'] ?? null) !== null;
            if ($hasMacro && $hasMicros) {
                $kelengkapan = 'data_lengkap';
            } elseif ($hasMacro) {
                $kelengkapan = 'data_sebagian';
            } else {
                $kelengkapan = 'perlu_verifikasi';
            }
        }

        return [
            'id' => $row['id'] ?? null,
            'nama' => $row['nama'] ?? '',
            'nama_kunci' => $row['nama_kunci'] ?? '',
            'energi_kkal' => $num($row['energi_kkal'] ?? null),
            'protein_g' => $num($row['protein_g'] ?? null),
            'lemak_g' => $num($row['lemak_g'] ?? null),
            'karbohidrat_g' => $num($row['karbohidrat_g'] ?? null),
            'serat_g' => $num($row['serat_g'] ?? null),
            'gula_g' => $num($row['gula_g'] ?? null),
            'lemak_jenuh_g' => $num($row['lemak_jenuh_g'] ?? null),
            'lemak_tak_jenuh_g' => $num($row['lemak_tak_jenuh_g'] ?? null),
            'kolesterol_mg' => $num($row['kolesterol_mg'] ?? null),
            'natrium_mg' => $num($row['natrium_mg'] ?? null),
            'kalium_mg' => $num($row['kalium_mg'] ?? null),
            'kalsium_mg' => $num($row['kalsium_mg'] ?? null),
            'fosfor_mg' => $num($row['fosfor_mg'] ?? null),
            'zat_besi_mg' => $num($row['zat_besi_mg'] ?? null),
            'magnesium_mg' => $num($row['magnesium_mg'] ?? null),
            'seng_mg' => $num($row['seng_mg'] ?? null),
            'vit_a_mcg' => $num($row['vit_a_mcg'] ?? null),
            'vit_b1_mg' => $num($row['vit_b1_mg'] ?? null),
            'vit_b2_mg' => $num($row['vit_b2_mg'] ?? null),
            'vit_b3_mg' => $num($row['vit_b3_mg'] ?? null),
            'vit_b6_mg' => $num($row['vit_b6_mg'] ?? null),
            'vit_b12_mcg' => $num($row['vit_b12_mcg'] ?? null),
            'vit_c_mg' => $num($row['vit_c_mg'] ?? null),
            'vit_d_mcg' => $num($row['vit_d_mcg'] ?? null),
            'vit_e_mg' => $num($row['vit_e_mg'] ?? null),
            'purin_mg' => $num($row['purin_mg'] ?? null),
            'purin_kategori' => $row['purin_kategori'] ?? null,
            'purin_dasar' => $row['purin_dasar'] ?? null,
            'kode_tkpi' => $row['kode_tkpi'] ?? null,
            'kode_usda' => $row['kode_usda'] ?? null,
            'sumber_natrium' => $row['sumber_natrium'] ?? '',
            'sumber_data' => $sumber,
            'tingkat_kelengkapan' => $kelengkapan
        ];
    }
}
