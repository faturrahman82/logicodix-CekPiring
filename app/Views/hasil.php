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
                <span id="label-porsi-header"><?= str_replace('.', ',', (string)$porsi) ?></span> porsi, sekitar <span id="label-berat-header"><?= $total_gizi['berat_total_g'] ?></span> g. Data demo untuk profil <?= ucfirst($profile['tujuan']) ?><?= !empty($profile['kondisi']['diabetes']) ? ' dan diabetes' : '' ?>.
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
                    </div>
                </div>
            </div>

            <div class="column-right">
                <div class="card card-perhatian">
                    <div class="card-header-clean">
                        <h3 class="card-title">Perhatian nutrisi</h3>
                        <p class="card-desc">Dibandingkan dengan batas harian sesuai profilmu.</p>
                    </div>

                    <div class="risiko-list">
                        <div class="risiko-item">
                            <div class="risiko-row-top">
                                <span class="risiko-label">Natrium</span>
                                <span class="badge-level level-<?= $risiko['natrium']['badge'] ?>" id="badge-natrium">
                                    <?= $risiko['natrium']['simbol'] ?> <?= $risiko['natrium']['level'] ?>
                                </span>
                            </div>
                            <p class="risiko-angka" id="angka-natrium"><?= $total_gizi['natrium_mg'] ?> mg</p>
                            <p class="risiko-pct" id="desc-natrium"><?= $risiko['natrium']['persen'] ?>% dari batas harian 2.000 mg</p>
                            <div class="progress-track">
                                <div class="progress-fill fill-<?= $risiko['natrium']['badge'] ?>" id="bar-natrium" style="width: <?= min(100, $risiko['natrium']['persen']) ?>%;"></div>
                            </div>
                            <?php if (!empty($risiko['natrium']['penyumbang'])): ?>
                                <p class="penyumbang-text" id="penyumbang-natrium"><?= e($risiko['natrium']['penyumbang']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="risiko-item">
                            <div class="risiko-row-top">
                                <span class="risiko-label">Gula</span>
                                <span class="badge-level level-<?= $risiko['gula']['badge'] ?>" id="badge-gula">
                                    <?= $risiko['gula']['simbol'] ?> <?= $risiko['gula']['level'] ?>
                                </span>
                            </div>
                            <p class="risiko-angka" id="angka-gula"><?= $total_gizi['gula_g'] ?> g</p>
                            <p class="risiko-pct" id="desc-gula"><?= $risiko['gula']['persen'] ?>% <?= e($risiko['gula']['keterangan_profil']) ?></p>
                            <div class="progress-track">
                                <div class="progress-fill fill-<?= $risiko['gula']['badge'] ?>" id="bar-gula" style="width: <?= min(100, $risiko['gula']['persen']) ?>%;"></div>
                            </div>
                            <?php if (!empty($risiko['gula']['penyumbang'])): ?>
                                <p class="penyumbang-text" id="penyumbang-gula"><?= e($risiko['gula']['penyumbang']) ?></p>
                            <?php endif; ?>
                        </div>
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
            <a href="<?= base_url('makanan') ?>" class="btn-primary">Analisis makanan lain</a>
        </div>
    </div>

    <script src="<?= asset_url('js/app.js') ?>"></script>
</body>
</html>
