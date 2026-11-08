<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'CekPiring - Analisis Gizi & Risiko Kesehatan') ?></title>
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
</head>
<body>
    <div class="container">
        <?php if (!isset($hide_header) || !$hide_header): ?>
            <?php view('partials/header', ['step' => $step ?? 1, 'step_title' => $step_title ?? 'Profil']); ?>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </div>

    <script src="<?= asset_url('js/app.js') ?>"></script>
</body>
</html>
