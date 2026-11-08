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

        <div class="card card-loading">
            <div class="loading-spinner-circle">
                <svg width="72" height="72" viewBox="0 0 72 72" fill="none" class="animate-spin">
                    <circle cx="36" cy="36" r="30" stroke="#e2e8f0" stroke-width="6"/>
                    <path d="M36 6C52.5685 6 66 19.4315 66 36" stroke="#047857" stroke-width="6" stroke-linecap="round"/>
                </svg>
            </div>

            <h1 class="loading-title">Menghitung estimasi gizi</h1>

            <div class="loading-steps">
                <div class="loading-step-item">
                    <div class="step-icon step-done">✓</div>
                    <span class="step-label">Mencocokkan <?= (int)$jumlah_bahan ?> bahan dengan data komposisi</span>
                </div>

                <div class="loading-step-item">
                    <div class="step-icon step-done">✓</div>
                    <span class="step-label">Menghitung energi dan makro per porsi</span>
                </div>

                <div class="loading-step-item">
                    <div class="step-icon step-processing">…</div>
                    <span class="step-label font-bold">
                        Menyesuaikan dengan profil <?= ucfirst($profile['tujuan'] ?? 'Seimbang') ?><?= !empty($profile['kondisi']['diabetes']) ? ' dan diabetes' : '' ?><?= !empty($profile['kondisi']['hipertensi']) ? ' dan hipertensi' : '' ?>
                    </span>
                </div>
            </div>

            <p class="loading-desc">Biasanya beberapa detik.</p>

            <a href="<?= base_url('konfirmasi') ?>" class="btn-cancel-loading">Batal, kembali ke bahan</a>
        </div>
    </div>

    <script>
        setTimeout(function() {
            window.location.href = "<?= base_url('hasil') ?>";
        }, 1500);
    </script>
</body>
</html>
