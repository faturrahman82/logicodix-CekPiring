<?php

/** @var \Core\Router $router */

$router->get('/', [\App\Controllers\ProfilController::class, 'index']);
$router->get('/profil', [\App\Controllers\ProfilController::class, 'index']);
$router->post('/profil', [\App\Controllers\ProfilController::class, 'save']);
$router->get('/profil/skip', [\App\Controllers\ProfilController::class, 'skip']);

$router->get('/makanan', [\App\Controllers\MakananController::class, 'index']);
$router->post('/makanan', [\App\Controllers\MakananController::class, 'process']);

$router->get('/konfirmasi', [\App\Controllers\KonfirmasiController::class, 'index']);
$router->post('/konfirmasi', [\App\Controllers\KonfirmasiController::class, 'save']);
$router->post('/konfirmasi/resolve-conflict', [\App\Controllers\KonfirmasiController::class, 'resolveConflict']);

$router->get('/menghitung', [\App\Controllers\HasilController::class, 'menghitung']);

$router->get('/hasil', [\App\Controllers\HasilController::class, 'index']);
$router->get('/galat', [\App\Controllers\HasilController::class, 'galat']);

$router->post('/api/recalculate', [\App\Controllers\ApiController::class, 'recalculate']);
$router->get('/api/search-ingredient', [\App\Controllers\ApiController::class, 'searchIngredient']);
