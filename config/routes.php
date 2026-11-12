<?php

$router->get('/', [\App\Controllers\ProfilController::class, 'index']);
$router->get('/profil', [\App\Controllers\ProfilController::class, 'index']);
$router->get('/profil/reset', [\App\Controllers\ProfilController::class, 'reset']);
$router->post('/profil', [\App\Controllers\ProfilController::class, 'save']);
$router->get('/profil/skip', [\App\Controllers\ProfilController::class, 'skip']);

$router->get('/makanan', [\App\Controllers\MakananController::class, 'index']);
$router->get('/makanan/reset', [\App\Controllers\MakananController::class, 'reset']);
$router->post('/makanan', [\App\Controllers\MakananController::class, 'process']);

$router->get('/konfirmasi', [\App\Controllers\KonfirmasiController::class, 'index']);
$router->post('/konfirmasi', [\App\Controllers\KonfirmasiController::class, 'save']);
$router->post('/konfirmasi/resolve-conflict', [\App\Controllers\KonfirmasiController::class, 'resolveConflict']);

$router->get('/menghitung', [\App\Controllers\HasilController::class, 'menghitung']);

$router->get('/hasil', [\App\Controllers\HasilController::class, 'index']);
$router->get('/galat', [\App\Controllers\HasilController::class, 'galat']);

$router->post('/api/cek/1', [\App\Controllers\ApiController::class, 'cekLangkah1']);
$router->post('/api/cek/2', [\App\Controllers\ApiController::class, 'cekLangkah2']);
$router->post('/api/cek/3', [\App\Controllers\ApiController::class, 'cekLangkah3']);
$router->post('/api/hitung/1', [\App\Controllers\ApiController::class, 'hitungLangkah1']);
$router->post('/api/hitung/2', [\App\Controllers\ApiController::class, 'hitungLangkah2']);
$router->post('/api/hitung/3', [\App\Controllers\ApiController::class, 'hitungLangkah3']);
$router->post('/api/recalculate', [\App\Controllers\ApiController::class, 'recalculate']);
$router->post('/api/terapkan-saran', [\App\Controllers\ApiController::class, 'terapkanSaran']);
$router->post('/api/batalkan-saran', [\App\Controllers\ApiController::class, 'batalkanSaran']);
$router->get('/api/search-ingredient', [\App\Controllers\ApiController::class, 'searchIngredient']);
