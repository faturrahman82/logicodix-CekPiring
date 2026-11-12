<?php
$step = $step ?? 1;
$step_title = $step_title ?? 'Profil';
?>
<div class="stepper">
    <p class="teks-langkah">Langkah <?= $step ?> dari 4: <?= e($step_title) ?></p>
    <div class="step-bars">
        <div class="bar <?= ($step >= 1) ? 'active' : '' ?>"></div>
        <div class="bar <?= ($step >= 2) ? 'active' : '' ?>"></div>
        <div class="bar <?= ($step >= 3) ? 'active' : '' ?>"></div>
        <div class="bar <?= ($step >= 4) ? 'active' : '' ?>"></div>
    </div>
</div>
