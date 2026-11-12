<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CekPiring - Menghitung Estimasi Gizi</title>
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
</head>
<body>

    <div class="container container-center">
        <?php view('partials/header', ['step' => 4, 'step_title' => 'Hasil']); ?>

        <div class="card card-loading-cek card-loading-transparan">
            <div class="loading-spinner-circle">
                <svg width="64" height="64" viewBox="0 0 72 72" fill="none" class="animate-spin">
                    <circle cx="36" cy="36" r="30" stroke="#e2e8f0" stroke-width="6"/>
                    <path d="M36 6C52.5685 6 66 19.4315 66 36" stroke="#047857" stroke-width="6" stroke-linecap="round"/>
                </svg>
            </div>

            <h2 class="loading-title">Menghitung estimasi gizi</h2>
            <p class="loading-subtitle">Menyiapkan rincian kalori &amp; evaluasi piring...</p>

            <div class="loading-steps-mini">
                <div class="mini-step-item step-active" id="step-cocok-bahan">
                    <span class="mini-step-dot"></span>
                    <span>Mencocokkan <?= (int)$jumlah_bahan ?> bahan dengan data komposisi</span>
                </div>

                <div class="mini-step-item" id="step-hitung-makro">
                    <span class="mini-step-dot"></span>
                    <span>Menghitung energi dan makro per porsi</span>
                </div>

                <div class="mini-step-item" id="step-sesuaikan-profil">
                    <span class="mini-step-dot"></span>
                    <span>
                        Menyesuaikan profil <?= ucfirst($profile['tujuan'] ?? 'Seimbang') ?><?= !empty($profile['kondisi']['diabetes']) ? ' &amp; diabetes' : '' ?><?= !empty($profile['kondisi']['hipertensi']) ? ' &amp; hipertensi' : '' ?>
                    </span>
                </div>
            </div>

            <p class="loading-desc">Biasanya butuh 1–2 detik. Mohon tunggu...</p>

            <a href="<?= base_url('konfirmasi') ?>" class="btn-cancel-loading">Batal, kembali ke bahan</a>
        </div>
    </div>

    <script>
        // Animasi pergantian langkah persis seperti pada loader cek bahan
        setTimeout(function() {
            var s1 = document.getElementById('step-cocok-bahan');
            var s2 = document.getElementById('step-hitung-makro');
            if (s1) { s1.classList.remove('step-active'); s1.classList.add('step-done'); }
            if (s2) { s2.classList.add('step-active'); }
        }, 650);

        setTimeout(function() {
            var s2 = document.getElementById('step-hitung-makro');
            var s3 = document.getElementById('step-sesuaikan-profil');
            if (s2) { s2.classList.remove('step-active'); s2.classList.add('step-done'); }
            if (s3) { s3.classList.add('step-active'); }
        }, 1350);

        setTimeout(function() {
            var s3 = document.getElementById('step-sesuaikan-profil');
            if (s3) { s3.classList.remove('step-active'); s3.classList.add('step-done'); }
            window.location.href = "<?= base_url('hasil') ?>";
        }, 2000);
    </script>
</body>
</html>
