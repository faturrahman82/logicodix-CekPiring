<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CekPiring - Estimasi Gizi: <?= e($analysis['nama_makanan'] ?? 'Hasil') ?></title>
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
</head>
<body>

    <div class="container">
        <?php view('partials/header', ['step' => 4, 'step_title' => 'Hasil']); ?>

        <div class="page-title">
            <h1 id="hasil-title">Estimasi gizi: <?= e($analysis['nama_makanan']) ?></h1>
            <p id="hasil-subtitle">
                <?php
                    $kondisiAktifList = [];
                    if (!empty($profile['kondisi']['diabetes'])) $kondisiAktifList[] = 'diabetes';
                    if (!empty($profile['kondisi']['kolesterol'])) $kondisiAktifList[] = 'kolesterol tinggi';
                    if (!empty($profile['kondisi']['hipertensi'])) $kondisiAktifList[] = 'hipertensi';
                    if (!empty($profile['kondisi']['asam_urat'])) $kondisiAktifList[] = 'asam urat';
                    $kondisiTeks = !empty($kondisiAktifList) ? ' dengan kondisi ' . implode(', ', $kondisiAktifList) : '';
                ?>
                <span id="label-porsi-header"><?= str_replace('.', ',', (string)$porsi) ?></span> porsi, sekitar <span id="label-berat-header"><?= $total_gizi['berat_total_g'] ?></span> g. Evaluasi gizi untuk profil <?= ucfirst($profile['tujuan']) ?><?= $kondisiTeks ?>.
            </p>
        </div>

        <div class="card card-verdict verdict-<?= $verdict['badge_class'] ?>" id="verdict-card">
            <div class="verdict-content">
                <div class="verdict-badge badge-<?= $verdict['badge_class'] ?>" id="verdict-badge">
                    <span><?= e($verdict['badge_text']) ?></span>
                </div>
                <h2 class="verdict-title" id="verdict-title"><?= e($verdict['judul']) ?></h2>
                <p class="verdict-desc" id="verdict-desc"><?= e($verdict['deskripsi']) ?></p>
            </div>

            <div class="verdict-porsi-box">
                <p class="porsi-box-label">Porsi disarankan</p>
                <div class="pengatur-porsi pengatur-porsi-sm">
                    <button type="button" class="btn-porsi-step" id="btn-hasil-minus">−</button>
                    <span class="porsi-angka" id="porsi-hasil-display"><?= str_replace('.', ',', (string)$porsi) ?></span>
                    <span class="porsi-satuan">porsi</span>
                    <button type="button" class="btn-porsi-step" id="btn-hasil-plus">+</button>
                </div>
                <p class="porsi-box-hint">Coba ubah porsi, angka di bawah ikut berubah.</p>
            </div>
        </div>

        <main class="main-content">
            <div class="column-left">
                <div class="card card-energi">
                    <div class="cincin-energi-wrap">
                        <svg class="cincin-svg" width="180" height="180" viewBox="0 0 180 180">
                            <circle cx="90" cy="90" r="76" class="circle-track" />
                            <?php 
                                $radius = 76;
                                $circumference = 2 * M_PI * $radius;
                                $dashoffset = $circumference - ($circumference * min(1.0, ($persen_kalori / 100)));
                            ?>
                            <circle cx="90" cy="90" r="76" class="circle-fill" id="circle-energy-fill"
                                    style="stroke-dasharray: <?= $circumference ?>; stroke-dashoffset: <?= $dashoffset ?>;" />
                        </svg>
                        <div class="cincin-teks">
                            <span class="cincin-angka" id="energi-angka"><?= $total_gizi['energi_kkal'] ?></span>
                            <span class="cincin-satuan">kkal</span>
                        </div>
                    </div>

                    <div class="energi-info">
                        <p class="energi-label">Energi porsi ini</p>
                        <h3 class="energi-headline" id="energi-headline">
                            <?= ($persen_kalori >= 25 && $persen_kalori <= 40) ? 'Porsi ini sekitar sepertiga kebutuhan harianmu.' : 'Kontribusi terhadap target harianmu.' ?>
                        </h3>
                        <p class="energi-desc" id="energi-desc">
                            <span id="persen-kalori-val"><?= $persen_kalori ?>%</span> dari target harian <?= number_format($target_kalori, 0, ',', '.') ?> kkal (profil <?= ucfirst($profile['tujuan']) ?>). Sisa untuk hari ini sekitar <span id="sisa-kalori-val"><?= number_format($sisa_kalori, 0, ',', '.') ?></span> kkal.
                        </p>
                    </div>
                </div>

                <div class="card card-makro">
                    <div class="card-header-clean">
                        <h3 class="card-title">Komposisi makro</h3>
                        <p class="card-desc">Gram per porsi dan bagian dari energi porsi ini.</p>
                    </div>

                    <div class="makro-list">
                        <div class="makro-item">
                            <div class="makro-row">
                                <span class="makro-name">Karbohidrat</span>
                                <span class="makro-val" id="karbo-val"><?= $total_gizi['karbohidrat_g'] ?> g, <span id="karbo-pct"><?= $total_gizi['makro_persen']['karbohidrat'] ?>%</span> energi</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill bar-carbs" id="bar-karbo" style="width: <?= $total_gizi['makro_persen']['karbohidrat'] ?>%;"></div>
                            </div>
                        </div>

                        <div class="makro-item">
                            <div class="makro-row">
                                <span class="makro-name">Protein</span>
                                <span class="makro-val" id="protein-val"><?= $total_gizi['protein_g'] ?> g, <span id="protein-pct"><?= $total_gizi['makro_persen']['protein'] ?>%</span> energi</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill bar-protein" id="bar-protein" style="width: <?= $total_gizi['makro_persen']['protein'] ?>%;"></div>
                            </div>
                        </div>

                        <div class="makro-item">
                            <div class="makro-row">
                                <span class="makro-name">Lemak</span>
                                <span class="makro-val" id="lemak-val"><?= $total_gizi['lemak_g'] ?> g, <span id="lemak-pct"><?= $total_gizi['makro_persen']['lemak'] ?>%</span> energi</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill bar-fat" id="bar-lemak" style="width: <?= $total_gizi['makro_persen']['lemak'] ?>%;"></div>
                            </div>
                        </div>

                        <div class="makro-item">
                            <div class="makro-row">
                                <span class="makro-name">Serat Pangan</span>
                                <span class="makro-val" id="serat-val"><?= $total_gizi['serat_g'] ?> g <span id="serat-pct">(<?= round(($total_gizi['serat_g'] / 30) * 100) ?>% target harian)</span></span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill bar-fiber" id="bar-serat" style="width: <?= min(100, round(($total_gizi['serat_g'] / 30) * 100)) ?>%;"></div>
                            </div>
                        </div>
                    </div>
                    <p class="makro-fiber-note">
                        Target serat harian: 30 g (AKG Kemenkes). Serat pangan dari sayur, buah, dan kacang-kacangan sangat efektif membantu mengikat kolesterol dan menstabilkan glukosa darah.
                    </p>
                </div>

                <!-- Modul Analisis Klinis & Panduan Piring Sehat (Ahli Gizi POV) -->
                <div class="card card-analisis-klinis" id="card-analisis-klinis">
                    <div class="card-header-clean">
                        <div class="analisis-header-row">
                            <div class="analisis-header-text">
                                <h3 class="card-title">Analisis Klinis & Panduan Piring</h3>
                                <p class="card-desc">Rekomendasi spesifik berbasis profil <strong id="analisis-profil-label"><?= e($clinical_advice['profil_label']) ?></strong></p>
                            </div>
                            <span class="badge-ahli-gizi">🩺 Tinjauan Ahli Gizi</span>
                        </div>
                    </div>

                    <div class="analisis-summary-box" id="analisis-summary-box">
                        <p class="summary-text" id="analisis-summary-text"><?= e($clinical_advice['ringkasan_medis']) ?></p>
                    </div>

                    <div class="analisis-cards-container">
                        <!-- 1. Peringatan / Cautions -->
                        <div class="analisis-subcard subcard-caution" id="subcard-caution">
                            <div class="subcard-header">
                                <span class="subcard-icon">⚠️</span>
                                <h4 class="subcard-title" id="caution-title"><?= e($clinical_advice['cautions']['judul']) ?></h4>
                            </div>
                            <div class="subcard-content" id="caution-body">
                                <p><?= $clinical_advice['cautions']['teks'] ?></p>
                            </div>
                        </div>

                        <!-- 2. Smart Swaps / Trik Makan -->
                        <div class="analisis-subcard subcard-swaps" id="subcard-swaps">
                            <div class="subcard-header">
                                <span class="subcard-icon">💡</span>
                                <h4 class="subcard-title" id="swaps-title"><?= e($clinical_advice['swaps']['judul']) ?></h4>
                            </div>
                            <div class="subcard-content" id="swaps-body">
                                <ul class="subcard-list" id="swaps-list">
                                    <?php foreach ($clinical_advice['swaps']['tips'] as $tip): ?>
                                        <li><?= $tip ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <!-- 3. Isi Piringku & Serat -->
                        <div class="analisis-subcard subcard-plate" id="subcard-plate">
                            <div class="subcard-header">
                                <span class="subcard-icon">🥗</span>
                                <h4 class="subcard-title" id="plate-title"><?= e($clinical_advice['plate']['judul']) ?></h4>
                            </div>
                            <div class="subcard-content" id="plate-body">
                                <p id="plate-text"><?= $clinical_advice['plate']['teks'] ?></p>
                                <?php if (!empty($clinical_advice['plate']['catatan'])): ?>
                                    <div class="subcard-highlight" id="plate-highlight">
                                        <?= $clinical_advice['plate']['catatan'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modul Spektrum Nutrisi Lengkap (Vitamin, Mineral & Mikronutrisi) -->
                <div class="card card-spektrum-nutrisi">
                    <div class="card-header-clean">
                        <div class="analisis-header-row">
                            <div class="analisis-header-text">
                                <h3 class="card-title">Spektrum Nutrisi & Mikronutrisi Lengkap</h3>
                                <p class="card-desc">Berdasarkan data Tabel Komposisi Pangan Indonesia (TKPI 2017) dan USDA SR Legacy.</p>
                            </div>
                            <span class="badge-source-tag">🔬 Data Terverifikasi</span>
                        </div>
                    </div>

                    <!-- 1. Profil Lemak & Kolesterol Tambahan -->
                    <div class="spektrum-subgroup">
                        <h4 class="spektrum-subgroup-title">Profil Lemak & Kolesterol</h4>
                        <div class="spektrum-grid">
                            <div class="spektrum-item">
                                <div class="spektrum-item-header">
                                    <span class="spektrum-name">Lemak Tak Jenuh</span>
                                    <span class="badge-status-nutrisi badge-<?= $total_gizi['status_nutrisi']['lemak_tak_jenuh_g']['status'] ?? 'lengkap' ?>">
                                        <?= ($total_gizi['status_nutrisi']['lemak_tak_jenuh_g']['status'] ?? '') === 'lengkap' ? '✓ Lengkap' : 'ℹ️ Sebagian' ?>
                                    </span>
                                </div>
                                <p class="spektrum-val"><?= $total_gizi['lemak_tak_jenuh_g'] ?? 0 ?> g</p>
                                <p class="spektrum-desc">Lemak baik (omega 3/6/9) untuk kesehatan pembuluh darah</p>
                            </div>
                            <div class="spektrum-item">
                                <div class="spektrum-item-header">
                                    <span class="spektrum-name">Kolesterol</span>
                                    <span class="badge-status-nutrisi badge-<?= $total_gizi['status_nutrisi']['kolesterol_mg']['status'] ?? 'lengkap' ?>">
                                        <?= ($total_gizi['status_nutrisi']['kolesterol_mg']['status'] ?? '') === 'lengkap' ? '✓ Lengkap' : 'ℹ️ Sebagian' ?>
                                    </span>
                                </div>
                                <p class="spektrum-val"><?= $total_gizi['kolesterol_mg'] ?? 0 ?> mg</p>
                                <p class="spektrum-desc">Batas anjuran harian: < 300 mg (Profil Seimbang)</p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Mineral & Elektrolit -->
                    <div class="spektrum-subgroup">
                        <h4 class="spektrum-subgroup-title">Mineral & Elektrolit</h4>
                        <div class="spektrum-grid">
                            <?php 
                            $mineralList = [
                                'Kalsium (Ca)' => ['kalsium_mg', 'mg', 'Kesehatan tulang & gigi'],
                                'Fosfor (P)' => ['fosfor_mg', 'mg', 'Metabolisme energi & struktur sel'],
                                'Zat Besi (Fe)' => ['zat_besi_mg', 'mg', 'Pembentukan sel darah merah'],
                                'Kalium (K)' => ['kalium_mg', 'mg', 'Keseimbangan cairan & tekanan darah'],
                                'Magnesium (Mg)' => ['magnesium_mg', 'mg', 'Fungsi saraf & relaksasi otot'],
                                'Seng (Zn)' => ['seng_mg', 'mg', 'Sistem imun & pemulihan jaringan'],
                            ];
                            foreach ($mineralList as $label => [$key, $unit, $manfaat]): 
                                $status = $total_gizi['status_nutrisi'][$key] ?? null;
                                $ada = !empty($status['tersedia']) && $status['nilai'] !== null;
                            ?>
                                <div class="spektrum-item <?= $ada ? '' : 'item-unavailable' ?>">
                                    <div class="spektrum-item-header">
                                        <span class="spektrum-name"><?= $label ?></span>
                                        <?php if ($ada): ?>
                                            <span class="badge-status-nutrisi badge-<?= $status['status'] ?>">
                                                <?= $status['status'] === 'lengkap' ? '✓ Lengkap' : 'ℹ️ Sebagian' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-status-nutrisi badge-tidak_tersedia">— Tidak tersedia</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="spektrum-val">
                                        <?= $ada ? ($status['nilai'] . ' ' . $unit) : 'Tidak tersedia' ?>
                                    </p>
                                    <p class="spektrum-desc"><?= $manfaat ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 3. Vitamin Esensial -->
                    <div class="spektrum-subgroup">
                        <h4 class="spektrum-subgroup-title">Vitamin Esensial</h4>
                        <div class="spektrum-grid">
                            <?php 
                            $vitaminList = [
                                'Vitamin A' => ['vit_a_mcg', 'mcg', 'Kesehatan mata & imunitas tubuh'],
                                'Vitamin B1 (Tiamin)' => ['vit_b1_mg', 'mg', 'Metabolisme energi karbohidrat'],
                                'Vitamin B2 (Riboflavin)' => ['vit_b2_mg', 'mg', 'Kesehatan kulit & regenerasi sel'],
                                'Vitamin B3 (Niasin)' => ['vit_b3_mg', 'mg', 'Sistem saraf & sirkulasi darah'],
                                'Vitamin B6' => ['vit_b6_mg', 'mg', 'Sintesis asam amino & imunitas'],
                                'Vitamin B12' => ['vit_b12_mcg', 'mcg', 'Produksi sel darah merah & DNA'],
                                'Vitamin C' => ['vit_c_mg', 'mg', 'Antioksidan & daya tahan tubuh'],
                                'Vitamin D' => ['vit_d_mcg', 'mcg', 'Penyerapan kalsium & imunitas'],
                                'Vitamin E' => ['vit_e_mg', 'mg', 'Antioksidan pelindung sel'],
                            ];
                            foreach ($vitaminList as $label => [$key, $unit, $manfaat]): 
                                $status = $total_gizi['status_nutrisi'][$key] ?? null;
                                $ada = !empty($status['tersedia']) && $status['nilai'] !== null;
                            ?>
                                <div class="spektrum-item <?= $ada ? '' : 'item-unavailable' ?>">
                                    <div class="spektrum-item-header">
                                        <span class="spektrum-name"><?= $label ?></span>
                                        <?php if ($ada): ?>
                                            <span class="badge-status-nutrisi badge-<?= $status['status'] ?>">
                                                <?= $status['status'] === 'lengkap' ? '✓ Lengkap' : 'ℹ️ Sebagian' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-status-nutrisi badge-tidak_tersedia">— Tidak tersedia</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="spektrum-val">
                                        <?= $ada ? ($status['nilai'] . ' ' . $unit) : 'Tidak tersedia' ?>
                                    </p>
                                    <p class="spektrum-desc"><?= $manfaat ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 4. Tabel Rincian Kontribusi Tiap Bahan -->
                    <?php if (!empty($total_gizi['rincian_bahan'])): ?>
                        <div class="spektrum-subgroup">
                            <h4 class="spektrum-subgroup-title">Rincian Kontribusi Tiap Bahan (Porsi Ini)</h4>
                            <div class="table-bahan-wrapper">
                                <table class="table-bahan-gizi">
                                    <thead>
                                        <tr>
                                            <th>Bahan</th>
                                            <th>Takaran</th>
                                            <th>Kalori</th>
                                            <th>Protein</th>
                                            <th>Lemak</th>
                                            <th>Karbo</th>
                                            <th>Sumber & Kualitas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($total_gizi['rincian_bahan'] as $rb): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= e($rb['nama']) ?></strong>
                                                    <?php if (!empty($rb['nama_kanonis']) && $rb['nama_kanonis'] !== $rb['nama']): ?>
                                                        <br><span class="sub-kanonis">Ref: <?= e($rb['nama_kanonis']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= e($rb['keterangan']) ?> (<?= $rb['berat_g'] ?>g)</td>
                                                <td><strong><?= $rb['ada_data'] ? ($rb['nutrisi']['energi_kkal'] . ' kkal') : '-' ?></strong></td>
                                                <td><?= $rb['ada_data'] ? ($rb['nutrisi']['protein_g'] . ' g') : '-' ?></td>
                                                <td><?= $rb['ada_data'] ? ($rb['nutrisi']['lemak_g'] . ' g') : '-' ?></td>
                                                <td><?= $rb['ada_data'] ? ($rb['nutrisi']['karbohidrat_g'] . ' g') : '-' ?></td>
                                                <td>
                                                    <span class="badge-source-tiny"><?= e($rb['sumber_data']) ?></span>
                                                    <span class="badge-comp-tiny comp-<?= $rb['tingkat_kelengkapan'] ?>">
                                                        <?= $rb['tingkat_kelengkapan'] === 'data_lengkap' ? 'Lengkap' : ($rb['tingkat_kelengkapan'] === 'data_sebagian' ? 'Sebagian' : 'Perlu Verifikasi') ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="column-right">
                <div class="card card-perhatian">
                    <div class="card-header-clean">
                        <h3 class="card-title">Perhatian nutrisi</h3>
                        <p class="card-desc">Diprioritaskan sesuai kondisi kesehatan dan batas harian profilmu.</p>
                    </div>

                    <?php
                        // Tentukan urutan prioritas nutrisi berdasarkan kondisi aktif profil
                        $nutrisiConfig = [
                            'lemak_jenuh' => [
                                'kunci' => 'lemak_jenuh',
                                'nama' => 'Lemak Jenuh',
                                'satuan' => 'g',
                                'nilai' => $total_gizi['lemak_jenuh_g'],
                                'data' => $risiko['lemak_jenuh'],
                                'prioritas' => !empty($profile['kondisi']['kolesterol']) ? 10 : ($risiko['lemak_jenuh']['persen'] > 33 ? 5 : 2),
                            ],
                            'gula' => [
                                'kunci' => 'gula',
                                'nama' => 'Gula',
                                'satuan' => 'g',
                                'nilai' => $total_gizi['gula_g'],
                                'data' => $risiko['gula'],
                                'prioritas' => !empty($profile['kondisi']['diabetes']) ? 10 : ($risiko['gula']['persen'] > 33 ? 5 : 2),
                            ],
                            'natrium' => [
                                'kunci' => 'natrium',
                                'nama' => 'Natrium',
                                'satuan' => 'mg',
                                'nilai' => $total_gizi['natrium_mg'],
                                'data' => $risiko['natrium'],
                                'prioritas' => !empty($profile['kondisi']['hipertensi']) ? 10 : ($risiko['natrium']['persen'] > 33 ? 5 : 2),
                            ],
                            'purin' => [
                                'kunci' => 'purin',
                                'nama' => 'Purin',
                                'satuan' => 'mg',
                                'nilai' => $total_gizi['purin_mg'],
                                'data' => $risiko['purin'],
                                'prioritas' => !empty($profile['kondisi']['asam_urat']) ? 10 : ($risiko['purin']['persen'] > 33 ? 5 : 1),
                            ],
                        ];

                        uasort($nutrisiConfig, function($a, $b) {
                            if ($a['prioritas'] === $b['prioritas']) {
                                return $b['data']['persen'] <=> $a['data']['persen'];
                            }
                            return $b['prioritas'] <=> $a['prioritas'];
                        });
                    ?>

                    <div class="risiko-list" id="risiko-list-container">
                        <?php foreach ($nutrisiConfig as $item): ?>
                            <?php $d = $item['data']; ?>
                            <div class="risiko-item" id="item-risiko-<?= $item['kunci'] ?>">
                                <div class="risiko-row-top">
                                    <span class="risiko-label">
                                        <?= e($item['nama']) ?>
                                        <?php if (!empty($d['kondisi_aktif'])): ?>
                                            <span class="badge-kondisi-fokus">Fokus Profil</span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="badge-level level-<?= $d['badge'] ?>" id="badge-<?= $item['kunci'] ?>">
                                        <?= $d['simbol'] ?> <?= $d['level'] ?>
                                    </span>
                                </div>
                                <p class="risiko-angka" id="angka-<?= $item['kunci'] ?>"><?= $item['nilai'] ?> <?= $item['satuan'] ?></p>
                                <p class="risiko-pct" id="desc-<?= $item['kunci'] ?>"><?= $d['persen'] ?>% <?= e($d['keterangan_profil']) ?></p>
                                <div class="progress-track">
                                    <div class="progress-fill fill-<?= $d['badge'] ?>" id="bar-<?= $item['kunci'] ?>" style="width: <?= min(100, $d['persen']) ?>%;"></div>
                                </div>
                                <?php if (!empty($d['penyumbang'])): ?>
                                    <p class="penyumbang-text" id="penyumbang-<?= $item['kunci'] ?>"><?= e($d['penyumbang']) ?></p>
                                <?php else: ?>
                                    <p class="penyumbang-text" id="penyumbang-<?= $item['kunci'] ?>" style="display:none;"></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <p class="risiko-panduan">
                        Cara membaca: Rendah di bawah 20%, Sedang 20 sampai 33%, Tinggi di atas 33% dari batas harian. Aturan demo tim, bukan standar medis.
                    </p>
                </div>

                <div class="card card-sumber">
                    <h4 class="sumber-title">Sumber dan asumsi</h4>
                    <p class="sumber-text">
                        Data komposisi: Tabel Komposisi Pangan Indonesia (TKPI 2017 Kemenkes RI) dan USDA FoodData Central. Batas harian natrium 2.000 mg dan gula 25 g (profil diabetes) mengacu anjuran WHO.
                    </p>
                    <p class="sumber-disclaimer">
                        Estimasi edukatif, bukan diagnosis medis atau uji laboratorium.
                    </p>
                </div>
            </div>
        </main>

        <div class="bottom-actions flex-end">
            <a href="<?= base_url('konfirmasi') ?>" class="btn-outline">Koreksi bahan atau porsi</a>
            <a href="<?= base_url('profil/reset') ?>" class="btn-primary">Analisis makanan lain</a>
        </div>
    </div>

    <script src="<?= asset_url('js/app.js') ?>"></script>
</body>
</html>
