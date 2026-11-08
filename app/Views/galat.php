<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CekPiring - Galat</title>
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
</head>
<body>

    <div class="container container-center">
        <?php view('partials/header', ['step' => 4, 'step_title' => 'Hasil']); ?>

        <div class="card card-galat">
            <div class="galat-icon-circle">
                <span>!</span>
            </div>

            <h1 class="galat-title"><?= e($pesan_judul ?? 'Estimasi belum bisa dihitung') ?></h1>
            <p class="galat-desc">
                <?= e($pesan_deskripsi ?? 'Layanan analisis sedang tidak bisa dihubungi, atau belum ada bahan yang punya data gizi. Coba hitung lagi atau kembali ke bahan.') ?>
            </p>

            <div class="galat-actions">
                <a href="<?= base_url('menghitung') ?>" class="btn-primary">Coba hitung lagi</a>
                <a href="<?= e($kembali_url ?? base_url('konfirmasi')) ?>" class="btn-outline">Kembali ke bahan</a>
            </div>
        </div>
    </div>

</body>
</html>
