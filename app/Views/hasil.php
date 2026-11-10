<?php
    use App\Services\PortionSize;

    $ukuranInfo = $ukuran_porsi ?? PortionSize::bawaan();
    $ukuranLabel = PortionSize::label($ukuranInfo['ukuran'] ?? 'sedang');
    $sumberUkuran = ($ukuranInfo['sumber'] ?? '') === 'foto' ? 'ditebak AI dari foto' : (($ukuranInfo['sumber'] ?? '') === 'pengguna' ? 'dipilih sendiri' : 'ukuran standar');

    $labelKondisi = [
        'diabetes' => 'Diabetes',
        'hipertensi' => 'Hipertensi',
        'kolesterol' => 'Kolesterol tinggi',
        'asam_urat' => 'Asam urat tinggi',
    ];
    $kondisiAktif = [];
    foreach ($labelKondisi as $k => $label) {
        if (!empty($profile['kondisi'][$k])) $kondisiAktif[] = $label;
    }

    $risikoFokus = array_filter($risiko, fn($r) => !empty($r['kondisi_aktif']));
    $risikoLain = array_filter($risiko, fn($r) => empty($r['kondisi_aktif']));

    $fmt = fn($n, $d = 0) => number_format((float)$n, $d, ',', '.');
    $radius = 76;
    $circumference = 2 * M_PI * $radius;
    $dashoffset = $circumference - ($circumference * min(1.0, ($persen_kalori / 100)));

    $renderRisiko = function (array $r) use ($fmt) {
        $tersedia = !empty($r['tersedia']);
        $dinilai = !empty($r['dinilai']);
        ?>
        <div class="risiko-item<?= $tersedia ? '' : ' risiko-kosong' ?>" id="item-risiko-<?= e($r['kode']) ?>">
            <div class="risiko-row-top">
                <span class="risiko-label"><?= e($r['nama']) ?></span>
                <span class="badge-level level-<?= e($r['badge']) ?>" id="badge-<?= e($r['kode']) ?>"><?= e($r['simbol'] . ' ' . $r['level']) ?></span>
            </div>
            <p class="risiko-angka" id="angka-<?= e($r['kode']) ?>"<?= $tersedia ? ' data-nilai="' . e((string)$r['nilai']) . '"' : '' ?>><?= $tersedia ? e($fmt($r['nilai'], $r['satuan'] === 'g' ? 1 : 0) . ' ' . $r['satuan']) : '—' ?></p>
            <p class="risiko-pct" id="desc-<?= e($r['kode']) ?>"><?= !$tersedia ? 'Data bahan belum tersedia' : e(($dinilai ? $r['persen'] . '% ' : '') . $r['keterangan_profil'] . (!empty($r['sebagian']) ? ' · data sebagian bahan' : '')) ?></p>
            <div class="progress-track">
                <div class="progress-fill fill-<?= e($r['badge']) ?>" id="bar-<?= e($r['kode']) ?>" style="width: <?= $dinilai ? min(100, (int)$r['persen']) : 0 ?>%;"></div>
            </div>
            <p class="penyumbang-text" id="penyumbang-<?= e($r['kode']) ?>"<?= ($tersedia && !empty($r['penyumbang'])) ? '' : ' hidden' ?>><?= e($r['penyumbang']) ?></p>
        </div>
        <?php
    };
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CekPiring - Hasil: <?= e($analysis['nama_makanan'] ?? 'Hasil') ?></title>
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
</head>
<body>

    <div class="container">
        <?php view('partials/header', ['step' => 4, 'step_title' => 'Hasil']); ?>

        <div class="page-title">
            <h1 id="hasil-title"><?= e($analysis['nama_makanan']) ?></h1>
            <p id="hasil-subtitle">
                Piring <?= e(mb_strtolower($ukuranLabel)) ?> (<?= e($sumberUkuran) ?>) ·
                <span id="label-porsi-header"><?= e($porsi_teks) ?></span> piring ·
                ≈<span id="label-berat-header"><?= e($fmt($total_gizi['berat_total_g'])) ?></span> g
            </p>
            <?php if (!empty($kondisiAktif)): ?>
                <p class="kondisi-chips">
                    <?php foreach ($kondisiAktif as $label): ?><span class="chip-kondisi"><?= e($label) ?></span><?php endforeach; ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="card card-verdict verdict-<?= e($verdict['badge_class']) ?>" id="verdict-card" aria-live="polite">
            <div class="verdict-content">
                <div class="verdict-badge badge-<?= e($verdict['badge_class']) ?>" id="verdict-badge">
                    <span><?= e($verdict['badge_text']) ?></span>
                </div>
                <h2 class="verdict-title" id="verdict-title"><?= e($verdict['judul']) ?></h2>
                <p class="verdict-desc" id="verdict-desc"><?= e($verdict['deskripsi']) ?></p>
                <p class="verdict-catatan" id="verdict-catatan"<?= empty($verdict['catatan']) ? ' hidden' : '' ?>><?= e($verdict['catatan']) ?></p>
            </div>

            <div class="verdict-porsi-box">
                <p class="porsi-box-label">Porsi dimakan</p>
                <div class="pengatur-porsi pengatur-porsi-sm">
                    <button type="button" class="btn-porsi-step" id="btn-hasil-minus" aria-label="Kurangi porsi">−</button>
                    <span class="porsi-angka" id="porsi-hasil-display" data-porsi="<?= e((string)$porsi) ?>"><?= e($porsi_teks) ?></span>
                    <span class="porsi-satuan">piring</span>
                    <button type="button" class="btn-porsi-step" id="btn-hasil-plus" aria-label="Tambah porsi">+</button>
                </div>
                <button type="button" class="btn-pakai-saran" id="btn-pakai-porsi"
                    data-porsi="<?= e((string)$porsi_disarankan) ?>"
                    <?= ($porsi_disarankan < $porsi - 0.001) ? '' : 'hidden' ?>>
                    Pakai porsi disarankan (<span id="porsi-saran-teks"><?= e($porsi_disarankan_teks) ?></span>)
                </button>
                <p class="porsi-banding" id="porsi-banding"<?= empty($perbandingan) ? ' hidden' : '' ?>>
                    <?php if (!empty($perbandingan)): ?>
                        <?= e($perbandingan['nama']) ?>: 1 piring <?= (int)$perbandingan['persen_penuh'] ?>% → <?= e(PortionSize::formatPorsi($perbandingan['porsi_saran'])) ?> piring <?= (int)$perbandingan['persen_saran'] ?>%
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <main class="main-content">
            <div class="column-left">
                <div class="card card-perhatian">
                    <h3 class="card-title"><?= !empty($risikoFokus) ? 'Untuk kondisimu' : 'Zat yang dipantau' ?></h3>
                    <div class="risiko-list">
                        <?php foreach (!empty($risikoFokus) ? $risikoFokus : $risiko as $r) { $renderRisiko($r); } ?>
                    </div>
                    <?php if (!empty($risikoFokus) && !empty($risikoLain)): ?>
                        <details class="lipatan">
                            <summary>Zat lain (informasi)</summary>
                            <div class="risiko-list">
                                <?php foreach ($risikoLain as $r) { $renderRisiko($r); } ?>
                            </div>
                        </details>
                    <?php endif; ?>
                    <p class="risiko-panduan">Rendah &lt;20%, Sedang 20–33%, Tinggi &gt;33% batas harian.</p>
                </div>

                <div class="card card-energi">
                    <div class="cincin-energi-wrap">
                        <svg class="cincin-svg" width="160" height="160" viewBox="0 0 180 180" aria-hidden="true">
                            <circle cx="90" cy="90" r="76" class="circle-track" />
                            <circle cx="90" cy="90" r="76" class="circle-fill" id="circle-energy-fill"
                                    style="stroke-dasharray: <?= $circumference ?>; stroke-dashoffset: <?= $dashoffset ?>;" />
                        </svg>
                        <div class="cincin-teks">
                            <span class="cincin-angka" id="energi-angka" data-nilai="<?= (int)$total_gizi['energi_kkal'] ?>"><?= e($fmt($total_gizi['energi_kkal'])) ?></span>
                            <span class="cincin-satuan">kkal</span>
                        </div>
                    </div>
                    <div class="energi-info">
                        <p class="energi-desc">
                            <strong id="persen-kalori-val"><?= (int)$persen_kalori ?>%</strong> target harian <?= e($fmt($target_kalori)) ?> kkal
                        </p>
                        <div class="makro-ringkas">
                            <div><span class="makro-ringkas-angka" id="karbo-val"><?= e($fmt($total_gizi['karbohidrat_g'])) ?> g</span><span class="makro-ringkas-label">Karbo</span></div>
                            <div><span class="makro-ringkas-angka" id="protein-val"><?= e($fmt($total_gizi['protein_g'])) ?> g</span><span class="makro-ringkas-label">Protein</span></div>
                            <div><span class="makro-ringkas-angka" id="lemak-val"><?= e($fmt($total_gizi['lemak_g'])) ?> g</span><span class="makro-ringkas-label">Lemak</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column-right">
                <div class="card card-saran" id="card-saran"<?= (empty($saran_list) && empty($saran_diterapkan)) ? ' hidden' : '' ?>>
                    <h3 class="card-title">Perbaiki piringmu</h3>
                    <ul class="saran-list" id="saran-list"<?= empty($saran_list) ? ' hidden' : '' ?>>
                        <?php foreach (($saran_list ?? []) as $sr): ?>
                            <li class="saran-item">
                                <div class="saran-teks">
                                    <p class="saran-aksi"><?= e($sr['aksi']) ?></p>
                                    <p class="saran-efek"><?= e($sr['efek']) ?></p>
                                </div>
                                <button type="button" class="btn-terapkan" data-zat="<?= e($sr['zat']) ?>">Terapkan</button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div id="saran-sudah"<?= empty($saran_diterapkan) ? ' hidden' : '' ?>>
                        <ul class="saran-diterapkan-list" id="saran-diterapkan-list">
                            <?php foreach (($saran_diterapkan ?? []) as $sd): ?>
                                <li>✓ <?= e(($sd['aksi'] ?? '') . ': ' . ($sd['efek'] ?? '')) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-link" id="btn-batalkan-saran">Batalkan semua</button>
                    </div>
                </div>

                <details class="card lipatan-rincian">
                    <summary>Lihat rincian bahan dan sumber</summary>
                    <table class="table-bahan-gizi">
                        <thead>
                            <tr><th>Bahan</th><th>Gram</th><th>Kalori</th></tr>
                        </thead>
                        <tbody id="rincian-body">
                            <?php foreach ($rincian as $rb): ?>
                                <tr>
                                    <td><?= e($rb['nama']) ?></td>
                                    <td><?= e($fmt($rb['gram'])) ?> g</td>
                                    <td><?= $rb['ada_data'] ? e($fmt($rb['kkal'])) . ' kkal' : 'data belum ada' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="sumber-text">Data gizi: TKPI 2017 Kemenkes RI, USDA FoodData Central, Kaneko dkk. 2014 (purin), Open Food Facts (kecap manis). Target kalori: AKG Permenkes 28/2019. Batas harian umum: natrium 2.000 mg dan gula 50 g (Permenkes 30/2013), lemak jenuh di bawah 10% energi (WHO). Batas untuk kondisi: natrium 1.500 mg (AHA, hipertensi), gula 5% energi (diabetes), lemak jenuh di bawah 7% energi (PERKI 2022), purin 400 mg (pedoman gout).</p>
                </details>

                <p class="sumber-disclaimer">Estimasi edukatif, bukan pengganti saran dokter atau ahli gizi.</p>
            </div>
        </main>

        <div class="toast-hasil" id="toast-hasil" role="status" aria-live="polite" hidden></div>
        <script type="application/json" id="data-hasil-awal"><?= json_encode([
            'energi' => (int)$total_gizi['energi_kkal'],
            'risiko' => array_map(fn($r) => ['nama' => $r['nama'], 'persen' => $r['persen'], 'tersedia' => !empty($r['dinilai'])], $risiko),
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>

        <div class="bottom-actions flex-end">
            <a href="<?= base_url('konfirmasi') ?>" class="btn-outline">Koreksi bahan</a>
            <a href="<?= base_url('profil/reset') ?>" class="btn-primary">Analisis makanan lain</a>
        </div>
    </div>

    <script src="<?= asset_url('js/app.js') ?>"></script>
</body>
</html>
