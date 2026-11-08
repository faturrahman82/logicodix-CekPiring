<?php

namespace App\Models;

use Core\Database;

class NutritionFact {
    private static ?array $csvCache = null;

    /**
     * Cari bahan berdasarkan nama atau kata kunci
     */
    public static function findByName(string $name): ?array {
        $cleanName = mb_strtolower(trim($name));

        $pdo = Database::getConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM nutrition_facts WHERE nama_kunci = ? OR nama = ? LIMIT 1");
                $stmt->execute([$cleanName, $name]);
                $result = $stmt->fetch();
                if ($result) {
                    return self::formatFact($result);
                }
            } catch (\Exception $e) {
                // Fallback to CSV if database query fails.
            }
        }

        $items = self::getAllFromCsv();
        
        foreach ($items as $item) {
            if ($item['nama_kunci'] === $cleanName || mb_strtolower($item['nama']) === $cleanName) {
                return self::formatFact($item);
            }
        }

        foreach ($items as $item) {
            if (strpos($item['nama_kunci'], $cleanName) !== false || strpos($cleanName, $item['nama_kunci']) !== false) {
                return self::formatFact($item);
            }
        }

        return null;
    }

    /**
     * Pencarian mirip / autocomplete untuk form tambah bahan
     */
    public static function search(string $keyword, int $limit = 10): array {
        $keyword = mb_strtolower(trim($keyword));
        if (empty($keyword)) {
            return [];
        }

        $items = self::getAllFromCsv();
        $results = [];

        foreach ($items as $item) {
            if (strpos($item['nama_kunci'], $keyword) !== false || strpos(mb_strtolower($item['nama']), $keyword) !== false) {
                $results[] = [
                    'id' => $item['id'],
                    'nama' => $item['nama'],
                    'energi_kkal' => (float)($item['energi_kkal'] ?? 0),
                    'protein_g' => (float)($item['protein_g'] ?? 0),
                    'karbohidrat_g' => (float)($item['karbohidrat_g'] ?? 0),
                    'lemak_g' => (float)($item['lemak_g'] ?? 0),
                ];
                if (count($results) >= $limit) {
                    break;
                }
            }
        }

        return $results;
    }

    private static function getAllFromCsv(): array {
        if (self::$csvCache !== null) {
            return self::$csvCache;
        }

        $csvPath = __DIR__ . '/../../data/nutrition_bersih.csv';
        if (!file_exists($csvPath)) {
            return [];
        }

        $rows = [];
        if (($handle = fopen($csvPath, 'r')) !== false) {
            $headers = fgetcsv($handle, 1000, ',');
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($headers) === count($data)) {
                    $rows[] = array_combine($headers, $data);
                }
            }
            fclose($handle);
        }

        self::$csvCache = $rows;
        return self::$csvCache;
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
