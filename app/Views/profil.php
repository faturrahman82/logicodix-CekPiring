<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CekPiring - Atur Profil</title>
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>?v=<?= time() ?>">
</head>
<body>

    <div class="container">
        <?php view('partials/header', ['step' => 1, 'step_title' => 'Profil']); ?>

        <div class="page-title">
            <h1>Atur profilmu dulu</h1>
            <p>Target kalori dan peringatan nutrisi dihitung dari sini. Datamu hanya disimpan di perangkat ini.</p>
        </div>

        <form action="<?= base_url('profil') ?>" method="POST" id="form-profil">
            <main class="main-content">
                <div class="column-left">
                    <div class="card">
                        <h2 class="card-title">Tujuan utama</h2>
                        
                        <div class="radio-group">
                            <label class="radio-card">
                                <input type="radio" name="tujuan" value="cutting" <?= (($profile['tujuan'] ?? '') === 'cutting') ? 'checked' : '' ?>>
                                <div class="custom-radio"></div>
                                <div class="radio-content">
                                    <span class="radio-title">Cutting</span>
                                    <span class="radio-desc">Menurunkan lemak. Target kalori di bawah kebutuhan harian.</span>
                                </div>
                            </label>

                            <label class="radio-card">
                                <input type="radio" name="tujuan" value="bulking" <?= (($profile['tujuan'] ?? '') === 'bulking') ? 'checked' : '' ?>>
                                <div class="custom-radio"></div>
                                <div class="radio-content">
                                    <span class="radio-title">Bulking</span>
                                    <span class="radio-desc">Menambah massa otot. Target kalori di atas kebutuhan harian.</span>
                                </div>
                            </label>

                            <label class="radio-card">
                                <input type="radio" name="tujuan" value="seimbang" <?= (($profile['tujuan'] ?? '') === 'seimbang') ? 'checked' : '' ?>>
                                <div class="custom-radio"></div>
                                <div class="radio-content">
                                    <span class="radio-title">Seimbang</span>
                                    <span class="radio-desc">Menjaga berat badan. Target kalori sama dengan kebutuhan harian.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="card card-data-fisik">
                        <h2 class="card-title">Data fisik</h2>
                        <p class="card-desc">Wajib diisi angka untuk menyimpan profil. Atau klik "Lewati" jika ingin memakai target umum.</p>

                        <div class="form-group">
                            <label class="form-label">Jenis kelamin</label>
                            <div class="segmented-control">
                                <label class="segment">
                                    <input type="radio" name="gender" value="perempuan" <?= (($profile['gender'] ?? 'perempuan') === 'perempuan') ? 'checked' : '' ?>>
                                    <div class="segment-btn">Perempuan</div>
                                </label>
                                <label class="segment">
                                    <input type="radio" name="gender" value="laki-laki" <?= (($profile['gender'] ?? '') === 'laki-laki') ? 'checked' : '' ?>>
                                    <div class="segment-btn">Laki-laki</div>
                                </label>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="input-umur">Umur (tahun)</label>
                                <input type="number" id="input-umur" name="umur" class="form-input" placeholder="Contoh: 21" min="1" max="120" value="<?= e($profile['umur'] ?? '') ?>" inputmode="numeric">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="input-tinggi">Tinggi (cm)</label>
                                <input type="number" id="input-tinggi" name="tinggi" class="form-input" placeholder="Contoh: 165" min="50" max="250" value="<?= e($profile['tinggi'] ?? '') ?>" inputmode="numeric">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="input-berat">Berat (kg)</label>
                                <input type="number" id="input-berat" name="berat" class="form-input" placeholder="Contoh: 60" min="20" max="300" step="0.1" value="<?= e($profile['berat'] ?? '') ?>" inputmode="decimal">
                            </div>
                        </div>

                        <div id="alert-data-fisik" class="alert-box alert-sm <?= !empty($error) ? 'alert-error' : '' ?>" style="<?= !empty($error) ? 'display: flex;' : 'display: none;' ?>">
                            <div class="alert-icon" id="alert-data-fisik-icon"><?= !empty($error) ? '!' : '' ?></div>
                            <div class="alert-content">
                                <p class="alert-title" id="alert-data-fisik-title"><?= !empty($error) ? 'Periksa Data Fisik' : '' ?></p>
                                <p class="alert-desc" id="alert-data-fisik-desc"><?= !empty($error) ? e($error) : '' ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="column-right">
                    <div class="card">
                        <h2 class="card-title">Kondisi kesehatan</h2>
                        <p class="card-desc">Nyalakan yang sesuai. Batas gula, natrium, lemak jenuh, dan purin ikut menyesuaikan.</p>

                        <div class="toggle-list">
                            <div class="toggle-item">
                                <div class="toggle-text">
                                    <span class="toggle-title">Diabetes</span>
                                    <span class="toggle-desc">Gula diberi batas lebih ketat.</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="diabetes" value="1" <?= !empty($profile['kondisi']['diabetes']) ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="toggle-item">
                                <div class="toggle-text">
                                    <span class="toggle-title">Hipertensi</span>
                                    <span class="toggle-desc">Natrium diberi batas lebih ketat.</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="hipertensi" value="1" <?= !empty($profile['kondisi']['hipertensi']) ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="toggle-item">
                                <div class="toggle-text">
                                    <span class="toggle-title">Kolesterol tinggi</span>
                                    <span class="toggle-desc">Lemak jenuh diberi batas lebih ketat.</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="kolesterol" value="1" <?= !empty($profile['kondisi']['kolesterol']) ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="toggle-item">
                                <div class="toggle-text">
                                    <span class="toggle-title">Asam urat</span>
                                    <span class="toggle-desc">Purin diberi batas lebih ketat.</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="asam_urat" value="1" <?= !empty($profile['kondisi']['asam_urat']) ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <p class="disclaimer-text">Ini bukan diagnosis. Kosongkan jika tidak ada.</p>
                    </div>

                    <div class="card card-target">
                        <h3 class="target-title">Target harianmu (perkiraan)</h3>
                        <div class="target-value">
                            <span class="number" id="target-angka"><?= number_format($profile['target_kalori'] ?? 1600, 0, ',', '.') ?></span>
                            <span class="unit">kkal per hari</span>
                        </div>
                        <p class="target-desc" id="target-deskripsi">
                            <?= e($profile['rumus'] ?? 'Berdasarkan Angka Kecukupan Gizi (AKG) Kemenkes 2019 sesuai umur dan jenis kelamin.') ?> Estimasi edukatif, bukan resep diet.
                        </p>
                        <script type="application/json" id="data-akg"><?= json_encode(\App\Services\TargetCalculator::tabelAkg(), JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
                    </div>
                </div>
            </main>

            <div class="bottom-actions">
                <a href="<?= base_url('profil/skip') ?>" class="btn-skip">Lewati, pakai target umum</a>
                <button type="submit" class="btn-primary">Simpan profil dan pilih makanan</button>
            </div>
        </form>
    </div>

    <script src="<?= asset_url('js/app.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
