<?php

namespace App\Services;

use App\Models\HealthRule;

class RiskEvaluator {
    public static function evaluasi(array $totalGizi, array $kondisiKesehatan): array {
        $hasil = [];

        $natriumEvaluasi = HealthRule::evaluasiLevel('natrium_mg', (float)$totalGizi['natrium_mg']);
        $natriumEvaluasi['kondisi_aktif'] = !empty($kondisiKesehatan['hipertensi']);
        $natriumEvaluasi['keterangan_profil'] = $natriumEvaluasi['kondisi_aktif'] ? 'untuk profil hipertensi' : 'dari batas harian umum';
        $natriumEvaluasi['penyumbang'] = $totalGizi['penyumbang']['natrium'] ?? '';
        $hasil['natrium'] = $natriumEvaluasi;

        $gulaEvaluasi = HealthRule::evaluasiLevel('gula_g', (float)$totalGizi['gula_g']);
        $gulaEvaluasi['kondisi_aktif'] = !empty($kondisiKesehatan['diabetes']);
        $gulaEvaluasi['keterangan_profil'] = $gulaEvaluasi['kondisi_aktif'] ? 'untuk profil diabetes' : 'dari batas harian umum';
        $gulaEvaluasi['penyumbang'] = $totalGizi['penyumbang']['gula'] ?? '';
        $hasil['gula'] = $gulaEvaluasi;

        $jenuhEvaluasi = HealthRule::evaluasiLevel('lemak_jenuh_g', (float)$totalGizi['lemak_jenuh_g']);
        $jenuhEvaluasi['kondisi_aktif'] = !empty($kondisiKesehatan['kolesterol']);
        $jenuhEvaluasi['keterangan_profil'] = $jenuhEvaluasi['kondisi_aktif'] ? 'untuk profil kolesterol tinggi' : 'dari anjuran harian';
        $jenuhEvaluasi['penyumbang'] = $totalGizi['penyumbang']['lemak_jenuh'] ?? '';
        $hasil['lemak_jenuh'] = $jenuhEvaluasi;

        $purinEvaluasi = HealthRule::evaluasiLevel('purin_mg', (float)$totalGizi['purin_mg']);
        $purinEvaluasi['kondisi_aktif'] = !empty($kondisiKesehatan['asam_urat']);
        $purinEvaluasi['keterangan_profil'] = $purinEvaluasi['kondisi_aktif'] ? 'untuk profil asam urat' : 'dari batas harian';
        $purinEvaluasi['penyumbang'] = $totalGizi['penyumbang']['purin'] ?? '';
        $hasil['purin'] = $purinEvaluasi;

        return $hasil;
    }
}
