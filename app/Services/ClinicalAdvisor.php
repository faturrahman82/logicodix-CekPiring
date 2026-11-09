<?php

namespace App\Services;

class ClinicalAdvisor {
    /**
     * Menghasilkan analisis klinis terpersonalisasi berdasarkan profil kesehatan,
     * tujuan diet, total kandungan gizi porsi makanan, dan bahan-bahan yang terdeteksi.
     */
    public static function generate(
        array $totalGizi,
        array $risiko,
        array $profile,
        array $analysis
    ): array {
        $kondisi = $profile['kondisi'] ?? [];
        $tujuan = $profile['tujuan'] ?? 'seimbang';
        $tujuanLabel = ucfirst($tujuan);
        $targetKalori = (int)($profile['target_kalori'] ?? 2000);

        $isKolesterol = !empty($kondisi['kolesterol']);
        $isDiabetes = !empty($kondisi['diabetes']);
        $isHipertensi = !empty($kondisi['hipertensi']);
        $isAsamUrat = !empty($kondisi['asam_urat']);
        $adaKondisi = $isKolesterol || $isDiabetes || $isHipertensi || $isAsamUrat;

        $namaMakanan = $analysis['nama_makanan'] ?? 'Makanan ini';
        $bahanList = $analysis['bahan'] ?? [];

        // Deteksi karakteristik bahan makanan (kuliner Indonesia)
        $adaGorengan = false;
        $adaKulitAtauTepung = false;
        $adaKecapManis = false;
        $adaNasiPutih = false;
        $adaSantauAtauJeroan = false;

        foreach ($bahanList as $b) {
            $namaB = strtolower($b['nama'] ?? '');
            if (str_contains($namaB, 'minyak') || str_contains($namaB, 'goreng')) {
                $adaGorengan = true;
            }
            if (str_contains($namaB, 'tepung') || str_contains($namaB, 'kulit')) {
                $adaKulitAtauTepung = true;
            }
            if (str_contains($namaB, 'kecap')) {
                $adaKecapManis = true;
            }
            if (str_contains($namaB, 'nasi') || str_contains($namaB, 'beras')) {
                $adaNasiPutih = true;
            }
            if (str_contains($namaB, 'santan') || str_contains($namaB, 'jeroan') || str_contains($namaB, 'hati') || str_contains($namaB, 'paru')) {
                $adaSantauAtauJeroan = true;
            }
        }

        // Susun Label Profil
        $kondisiLabels = [];
        if ($isKolesterol) $kondisiLabels[] = 'Kolesterol Tinggi';
        if ($isDiabetes) $kondisiLabels[] = 'Diabetes';
        if ($isHipertensi) $kondisiLabels[] = 'Hipertensi';
        if ($isAsamUrat) $kondisiLabels[] = 'Asam Urat';

        if (!empty($kondisiLabels)) {
            $profilLabel = implode(' & ', $kondisiLabels) . " (Target {$tujuanLabel})";
        } else {
            $profilLabel = "Kebugaran Umum (Target {$tujuanLabel})";
        }

        // ==========================================
        // 1. RINGKASAN MEDIS (Summary)
        // ==========================================
        $ringkasanMedis = '';
        if ($isKolesterol && ($risiko['lemak_jenuh']['persen'] ?? 0) > 30) {
            $ringkasanMedis = "Porsi ini memiliki beban lemak jenuh sebesar {$totalGizi['lemak_jenuh_g']} g (" . ($risiko['lemak_jenuh']['persen'] ?? 0) . "% batas harian). Untuk profil kolesterol tinggi, fokus utama adalah meminimalkan lemak jenuh dan meningkatkan asupan serat larut pengikat kolesterol.";
        } elseif ($isDiabetes && ($risiko['gula']['persen'] ?? 0) > 25) {
            $ringkasanMedis = "Porsi ini membawa karbohidrat cepat serap dan gula yang cukup tinggi bagi profil diabetes. Diperlukan strategi urutan makan (*food sequencing*) untuk meredam lonjakan glukosa darah postprandial.";
        } elseif ($isHipertensi && ($risiko['natrium']['persen'] ?? 0) > 33) {
            $ringkasanMedis = "Kandungan natrium porsi ini mencapai {$totalGizi['natrium_mg']} mg. Bagi profil hipertensi, asupan natrium tinggi memicu retensi cairan yang secara langsung meningkatkan tekanan darah vaskular.";
        } elseif ($isAsamUrat && ($risiko['purin']['persen'] ?? 0) > 33) {
            $ringkasanMedis = "Porsi ini tergolong tinggi purin ({$totalGizi['purin_mg']} mg). Katabolisme purin menghasilkan asam urat yang berisiko memicu serangan nyeri sendi akut pada penderita hiperurisemia/gout.";
        } else {
            $persenKalori = ($targetKalori > 0) ? round(($totalGizi['energi_kkal'] / $targetKalori) * 100) : 0;
            $ringkasanMedis = "Porsi ini menyumbang {$totalGizi['energi_kkal']} kkal ({$persenKalori}% dari target harian {$targetKalori} kkal). Komposisi makro didominasi oleh " . ($totalGizi['makro_persen']['karbohidrat'] > 45 ? 'karbohidrat' : 'lemak') . ", dengan kadar serat yang perlu diimbangi.";
        }

        // ==========================================
        // 2. PERINGATAN & BATASAN SPESIFIK (Cautions)
        // ==========================================
        $cautionsJudul = 'Hal yang Perlu Dibatasi & Diwaspadai';
        $cautionsTeks = '';

        if ($isKolesterol) {
            $penyumbangJenuh = preg_replace('/^Terbesar dari\s+/i', '', $risiko['lemak_jenuh']['penyumbang'] ?? 'minyak atau lemak hewani');
            $cautionsJudul = 'Peringatan Klinis: Waspada Lemak Jenuh (Kolesterol)';
            $cautionsTeks = "Lemak jenuh pada hidangan ini terutama disumbang oleh <strong>" . htmlspecialchars(rtrim($penyumbangJenuh, '.')) . "</strong>. Asam lemak jenuh (SFA) terbukti menurunkan sensitivitas reseptor LDL di hepatosit hati, sehingga kolesterol LDL beredar lebih lama di darah dan berisiko memicu aterosklerosis.";
        } elseif ($isDiabetes) {
            $penyumbangGula = preg_replace('/^Terbesar dari\s+/i', '', $risiko['gula']['penyumbang'] ?? 'bumbu manis atau karbohidrat olahan');
            $cautionsJudul = 'Peringatan Klinis: Indeks Glikemik & Gula (Diabetes)';
            $cautionsTeks = "Porsi ini menyumbang <strong>{$totalGizi['karbohidrat_g']} g karbohidrat</strong> dan gula dari <em>" . htmlspecialchars(rtrim($penyumbangGula, '.')) . "</em>. Tanpa kehadiran serat yang memadai, karbohidrat ini akan dihidrolisis cepat menjadi glukosa darah, membebani kinerja insulin pankreas.";
        } elseif ($isHipertensi) {
            $penyumbangNatrium = preg_replace('/^Terbesar dari\s+/i', '', $risiko['natrium']['penyumbang'] ?? 'saus, kecap, atau garam bumbu');
            $cautionsJudul = 'Peringatan Klinis: Muatan Natrium (Hipertensi)';
            $cautionsTeks = "Natrium tercatat <strong>{$totalGizi['natrium_mg']} mg</strong>, disumbang oleh <em>" . htmlspecialchars(rtrim($penyumbangNatrium, '.')) . "</em>. Menurut pedoman DASH (*Dietary Approaches to Stop Hypertension*), asupan natrium per waktu makan idealnya tidak melebihi 500–600 mg untuk mencegah vasokonstriksi arteriol.";
        } elseif ($isAsamUrat) {
            $penyumbangPurin = preg_replace('/^Terbesar dari\s+/i', '', $risiko['purin']['penyumbang'] ?? 'protein hewani atau ekstrak kaldu');
            $cautionsJudul = 'Peringatan Klinis: Purin Tinggi (Asam Urat)';
            $cautionsTeks = "Kandungan purin sebesar <strong>{$totalGizi['purin_mg']} mg</strong> terutama berasal dari <em>" . htmlspecialchars(rtrim($penyumbangPurin, '.')) . "</em>. Penderita asam urat dianjurkan membatasi purin maksimal 100–150 mg per waktu makan agar kadar asam urat serum tetap terkontrol di bawah 6 mg/dL.";
        } else {
            if ($tujuan === 'cutting') {
                $cautionsJudul = 'Evaluasi Target: Defisit Kalori (Cutting)';
                $cautionsTeks = "Hidangan ini membawa <strong>{$totalGizi['energi_kkal']} kkal</strong> dengan lemak <strong>{$totalGizi['lemak_g']} g</strong>. Waspadai densitas kalori tersembunyi dari minyak penggorengan agar defisit kalori harian Anda tidak terlampaui.";
            } elseif ($tujuan === 'bulking') {
                $cautionsJudul = 'Evaluasi Target: Kualitas Protein (Bulking)';
                $cautionsTeks = "Protein tercatat <strong>{$totalGizi['protein_g']} g</strong>. Untuk memaksimalkan hipertrofi otot (*Muscle Protein Synthesis*), pastikan asupan protein bersih bebas dari kelebihan lemak jenuh berlebih.";
            } else {
                $cautionsJudul = 'Evaluasi Keseimbangan Gizi';
                $cautionsTeks = "Energi dari lemak tercatat <strong>{$totalGizi['makro_persen']['lemak']}%</strong> (anjuran seimbang: 20–35%). Pengurangan sedikit porsi gorengan akan membuat rasio energi harian lebih optimal.";
            }
        }

        // ==========================================
        // 3. SMART SWAPS / TRIK SEHAT PRAKTIS (Swaps)
        // ==========================================
        $swapsJudul = 'Trik Sehat di Meja Makan (Aksi Nyata)';
        $tips = [];

        if ($adaGorengan || $adaKulitAtauTepung) {
            $tips[] = "<strong>Kurangi Lapisan Tepung / Kulit:</strong> Menyisihkan separuh kulit tepung krispi dapat memangkas 8–15 g lemak jenuh dan menghemat hingga 100–140 kkal tanpa mengurangi kenikmatan daging ayamnya.";
            $tips[] = "<strong>Tiriskan Minyak Sambal:</strong> Ambil cabai/sambal dengan sendok berongga atau tiriskan genangan minyaknya sebelum dilumurkan ke nasi.";
        }

        if ($adaNasiPutih && ($isDiabetes || $tujuan === 'cutting')) {
            $tips[] = "<strong>Kontrol Takaran Nasi:</strong> Cukup santap 1/2 hingga 3/4 porsi nasi (sekitar 100–140 g) untuk menjaga indeks glikemik dan memberikan ruang kalori bagi serat sayuran.";
        } elseif ($adaNasiPutih && $isKolesterol) {
            $tips[] = "<strong>Kombinasi Karbo Sehat:</strong> Untuk waktu makan berikutnya, pertimbangkan mencampur nasi putih dengan beras merah atau porang/shirataki guna mendongkrak serat larut.";
        }

        if ($adaKecapManis && ($isDiabetes || $isHipertensi)) {
            $tips[] = "<strong>Alternatif Cita Rasa Gurih:</strong> Gunakan perasan jeruk nipis, irisan bawang merah, dan cabai rawit segar untuk rasa gurih segar tanpa tambahan natrium atau gula rafinasi.";
        }

        // Tip default jika list tips masih sedikit
        if (count($tips) < 2) {
            $tips[] = "<strong>Teknik Masak Mendatang:</strong> Prioritaskan olahan ayam/ikan panggang, kukus, sup kuah bening, atau pepes sebagai variasi menu rendah minyak jenuh.";
            $tips[] = "<strong>Cukupkan Hidrasi:</strong> Minum 1 gelas air putih 15–20 menit sebelum makan untuk membantu regulasi rasa kenyang dan laju metabolisme alami.";
        }

        // ==========================================
        // 4. PANDUAN "ISI PIRINGKU" KEMENKES (Plate)
        // ==========================================
        $plateJudul = 'Panduan Isi Piringku & Penyeimbang Nutrisi';
        $seratPorsi = (float)$totalGizi['serat_g'];
        $persenSerat = round(($seratPorsi / 30.0) * 100);

        if ($seratPorsi < 3.0) {
            $plateTeks = "Porsi ini mengalami <strong>defisit serat pangan signifikan</strong> (baru {$seratPorsi} g atau {$persenSerat}% dari target 30 g AKG Kemenkes). " .
                ($isKolesterol 
                    ? "Bagi penderita kolesterol, <em>soluble fiber</em> (serat larut air) adalah penangkal alami yang mengikat asam empedu di usus halus agar kolesterol dibuang lewat ekskresi alami." 
                    : "Serat sangat krusial untuk memperlambat laju penyerapan karbohidrat dan menjaga kesehatan mikrobiota usus besar.");
            $plateCatatan = "<strong>Rekomendasi Tambahan:</strong> Tambahkan <strong>1 mangkuk sayuran hijau</strong> (bayam, brokoli, labu siam) atau <strong>lalapan segar</strong> (mentimun, selada, tomat sekitar 100–150 g).";
        } elseif ($seratPorsi < 8.0) {
            $plateTeks = "Kandungan serat tercatat {$seratPorsi} g ({$persenSerat}% target harian). Asupan ini cukup baik, namun masih dapat dioptimalkan dengan menambah porsi sayur berdaun hijau atau buah potong (apel/pepaya).";
            $plateCatatan = "<strong>Aturan 1/2 Piring Sayur:</strong> Terapkan konsep Kemenkes RI: separuh piring diisi sayur dan buah, seperempat piring karbohidrat, dan seperempat piring lauk berprotein.";
        } else {
            $plateTeks = "Porsi ini kaya akan serat pangan ({$seratPorsi} g, {$persenSerat}% target harian). Pertahankan pola makan kaya serat ini untuk kestabilan profil lipid dan kontrol glikemik jangka panjang.";
            $plateCatatan = "<strong>Keseimbangan Optimal:</strong> Pastikan asupan cairan minimal 2 liter per hari agar serat pangan dapat bekerja optimal di saluran pencernaan.";
        }

        return [
            'profil_label' => $profilLabel,
            'ringkasan_medis' => $ringkasanMedis,
            'cautions' => [
                'judul' => $cautionsJudul,
                'teks' => $cautionsTeks,
            ],
            'swaps' => [
                'judul' => $swapsJudul,
                'tips' => $tips,
            ],
            'plate' => [
                'judul' => $plateJudul,
                'teks' => $plateTeks,
                'catatan' => $plateCatatan,
            ],
        ];
    }
}
