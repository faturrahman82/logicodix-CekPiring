<?php
$step = $step ?? 1;
$step_title = $step_title ?? 'Profil';
?>
<header class="header">
    <a href="<?= base_url() ?>" class="logo-link">
        <div class="wordmark">
            <div class="cincin-wordmark">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="#047857" stroke-width="3" stroke-dasharray="4 2"/>
                    <circle cx="12" cy="12" r="5" fill="#047857"/>
                </svg>
            </div>
            <span class="nama-produk">CekPiring</span>
        </div>
    </a>
    
    <div class="stepper">
        <p class="teks-langkah">Langkah <?= $step ?> dari 4: <?= e($step_title) ?></p>
        <div class="step-bars">
            <div class="bar <?= ($step >= 1) ? 'active' : '' ?>"></div>
            <div class="bar <?= ($step >= 2) ? 'active' : '' ?>"></div>
            <div class="bar <?= ($step >= 3) ? 'active' : '' ?>"></div>
            <div class="bar <?= ($step >= 4) ? 'active' : '' ?>"></div>
        </div>
    </div>
</header>
