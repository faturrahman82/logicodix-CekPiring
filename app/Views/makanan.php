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
                            <div class="plus-circle">
                                <span>+</span>
                            </div>
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

    <script src="<?= asset_url('js/app.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
