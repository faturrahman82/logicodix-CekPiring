<?php

namespace App\Models;

use Core\Database;

class NutritionFact {
    private static ?array $csvCache = null;

    /**
     * Normalisasi teks agar input " Telur ", "TELUR" dan "telur" setara.
     */
    private static function normalize(string $name): string {
        $name = mb_strtolower(trim($name), 'UTF-8');
        $name = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $name);
        $name = preg_replace('/\s+/u', ' ', $name);
        return trim($name);
    }

    /**
     * Cari alias hanya dengan kecocokan utuh. Tidak ada lagi fallback substring
     * yang bisa membuat "telur" salah mengambil "ikan telur asin mentah".
     */
    private static function resolveAlias(string $name, array $items): string {
        $needle = self::normalize($name);
        if ($needle === '') return '';

        // Alias generik didahulukan untuk istilah yang memang ambigu di dataset,
        // misalnya "kecap" -> kecap manis dan "nasi" -> nasi matang.
        foreach (self::getAliases() as $aliasRow) {
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

        return $needle;
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

    /**
     * Pencarian data gizi berdasarkan nama/alias yang cocok persis.
     */
    public static function findByName(string $name): ?array {
        $items = self::getAllFromCsv();
        $canonicalKey = self::resolveAlias($name, $items);
        if ($canonicalKey === '') return null;

        // Database tetap menjadi sumber utama, tetapi hanya setelah alias
        // dipetakan ke nama_kunci kanonis.
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
                // Gunakan CSV jika tabel belum diimpor atau query gagal.
            }
        }

        foreach ($items as $item) {
            if (self::normalize((string)($item['nama_kunci'] ?? '')) === $canonicalKey) {
                return self::formatFact($item);
            }
        }

        return null;
    }

    /**
     * Autocomplete: exact/prefix match lebih dulu, lalu kecocokan kata.
     * Hasil tidak mengambil item acak hanya karena mengandung substring.
     */
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
            $results[] = [
                'id' => $item['id'] ?? null,
                'nama' => $item['nama'] ?? '',
                'energi_kkal' => (float)($item['energi_kkal'] ?? 0),
                'protein_g' => (float)($item['protein_g'] ?? 0),
                'karbohidrat_g' => (float)($item['karbohidrat_g'] ?? 0),
                'lemak_g' => (float)($item['lemak_g'] ?? 0),
            ];
        }
        return $results;
    }

    private static function getAllFromCsv(): array {
        if (self::$csvCache !== null) return self::$csvCache;

        $csvPath = __DIR__ . '/../../data/nutrition_bersih.csv';
        if (!file_exists($csvPath)) return [];

        $rows = [];
        if (($handle = fopen($csvPath, 'r')) !== false) {
            $headers = fgetcsv($handle, 0, ',');
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                if (count($headers) === count($data)) {
                    $rows[] = array_combine($headers, $data);
                }
            }
            fclose($handle);
        }

        self::$csvCache = $rows;
        return self::$csvCache;
    }

    private static ?array $aliasCache = null;

    private static function getAliases(): array {
        if (self::$aliasCache !== null) return self::$aliasCache;
        $path = __DIR__ . '/../../data/ingredient_aliases.csv';
        if (!file_exists($path)) return self::$aliasCache = [];
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle, 0, ',');
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                if (count($headers) === count($data)) {
                    $rows[] = array_combine($headers, $data);
                }
            }
            fclose($handle);
        }
        self::$aliasCache = $rows;
        return self::$aliasCache;
    }

    private static function formatFact(array $row): array {
        return [
            'id' => $row['id'] ?? null,
            'nama' => $row['nama'] ?? '',
            'nama_kunci' => $row['nama_kunci'] ?? '',
            'energi_kkal' => is_numeric($row['energi_kkal'] ?? null) ? (float)$row['energi_kkal'] : null,
            'protein_g' => is_numeric($row['protein_g'] ?? null) ? (float)$row['protein_g'] : null,
            'lemak_g' => is_numeric($row['lemak_g'] ?? null) ? (float)$row['lemak_g'] : null,
            'karbohidrat_g' => is_numeric($row['karbohidrat_g'] ?? null) ? (float)$row['karbohidrat_g'] : null,
            'serat_g' => is_numeric($row['serat_g'] ?? null) ? (float)$row['serat_g'] : null,
            'gula_g' => is_numeric($row['gula_g'] ?? null) ? (float)$row['gula_g'] : null,
            'lemak_jenuh_g' => is_numeric($row['lemak_jenuh_g'] ?? null) ? (float)$row['lemak_jenuh_g'] : null,
            'kolesterol_mg' => is_numeric($row['kolesterol_mg'] ?? null) ? (float)$row['kolesterol_mg'] : null,
            'natrium_mg' => is_numeric($row['natrium_mg'] ?? null) ? (float)$row['natrium_mg'] : null,
            'purin_mg' => is_numeric($row['purin_mg'] ?? null) ? (float)$row['purin_mg'] : null,
            'purin_kategori' => $row['purin_kategori'] ?? null,
            'sumber_natrium' => $row['sumber_natrium'] ?? '',
        ];
    }
}
