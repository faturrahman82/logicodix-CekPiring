<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CekPiring - Makanan</title>
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>?v=<?= time() ?>">
</head>
<body>

    <div class="container">
        <?php view('partials/header', ['step' => 2, 'step_title' => 'Makanan']); ?>

        <div class="page-title">
            <h1>Makanan apa yang kamu makan?</h1>
            <p>Isi nama makanan dan tambah foto. Keduanya dibutuhkan supaya hasilnya lebih akurat.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-box alert-error">
                <div class="alert-icon">!</div>
                <div class="alert-content">
                    <p class="alert-title">Perhatian</p>
                    <p class="alert-desc"><?= e($error) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('makanan') ?>" method="POST" enctype="multipart/form-data" id="form-makanan">
            <div class="card card-input-makanan">
                <div class="input-nama-col">
                    <div class="form-group">
                        <label class="form-label" for="input-nama-makanan">Nama makanan</label>
                        <input type="text" id="input-nama-makanan" name="nama_makanan" class="form-input form-input-lg" 
                               placeholder="Contoh: nasi goreng telur" 
                               value="<?= e($analysis['nama_makanan'] ?? '') ?>" autocomplete="off" required>
                    </div>
                    <p class="helper-text">Contoh lain: soto ayam, rawon daging sapi, rendang daging.</p>
                </div>

                <div class="input-foto-col">
                    <div class="upload-dropzone" id="dropzone-foto">
                        <input type="file" id="input-file-foto" name="foto" accept="image/jpeg,image/png,image/webp" class="file-hidden">
                        <input type="hidden" name="foto_path" id="input-foto-path" value="<?= e($analysis['foto'] ?? '') ?>">
                        
                        <div class="dropzone-empty" id="dropzone-empty" style="<?= !empty($analysis['foto']) ? 'display: none;' : '' ?>">
                            <p class="upload-title">Tambah foto makanan</p>
                            <p class="upload-desc">JPG atau PNG, satu foto yang jelas</p>
                            <button type="button" class="btn-outline" id="btn-pilih-foto">Pilih foto</button>
                        </div>

                        <div class="dropzone-preview <?= !empty($analysis['foto']) ? 'active' : '' ?>" id="dropzone-preview" style="<?= empty($analysis['foto']) ? 'display: none;' : '' ?>">
                            <img src="<?= !empty($analysis['foto']) ? base_url($analysis['foto']) : '' ?>" alt="Pratinjau Makanan" id="img-preview">
                            <button type="button" class="btn-change-photo" id="btn-ganti-foto">Ganti foto</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bottom-actions">
                <a href="<?= base_url('profil') ?>" class="btn-secondary">Ubah profil</a>
                
                <div class="submit-wrap">
                    <span class="submit-hint" id="submit-hint">Isi nama makanan dan tambah foto dulu.</span>
                    <button type="submit" class="btn-primary" id="btn-submit-makanan" disabled>Cek bahan makanan</button>
                </div>
            </div>
        </form>
    </div>

    <div class="modal-overlay" id="loading-cek-bahan" aria-hidden="true" style="display: none;">
        <div class="card card-loading-cek">
            <div class="loading-spinner-circle">
                <svg width="64" height="64" viewBox="0 0 72 72" fill="none" class="animate-spin">
                    <circle cx="36" cy="36" r="30" stroke="#e2e8f0" stroke-width="6"/>
                    <path d="M36 6C52.5685 6 66 19.4315 66 36" stroke="#047857" stroke-width="6" stroke-linecap="round"/>
                </svg>
            </div>
            <h2 class="loading-title">Memeriksa bahan makanan</h2>
            <p class="loading-subtitle" id="loading-nama-makanan">Menganalisis foto dan nama makanan...</p>

            <div class="loading-steps-mini">
                <div class="mini-step-item step-active" id="step-upload">
                    <span class="mini-step-dot"></span>
                    <span>Mengunggah foto makanan</span>
                </div>
                <div class="mini-step-item" id="step-ai">
                    <span class="mini-step-dot"></span>
                    <span>Mendeteksi bahan dari foto dengan AI</span>
                </div>
                <div class="mini-step-item" id="step-db">
                    <span class="mini-step-dot"></span>
                    <span>Mencocokkan takaran &amp; data gizi</span>
                </div>
            </div>

            <p class="loading-desc">Biasanya butuh 2–5 detik. Mohon tunggu...</p>
        </div>
    </div>

    <script src="<?= asset_url('js/app.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
