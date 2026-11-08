<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CekPiring - Konfirmasi Bahan</title>
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
</head>
<body>

    <div class="container">
        <?php view('partials/header', ['step' => 3, 'step_title' => 'Bahan']); ?>

        <div class="page-title">
            <h1>Cek bahan dan porsinya</h1>
            <p>Hapus bahan yang tidak ada di piringmu, tambah yang kurang.</p>
        </div>

        <?php if (!empty($konflik)): ?>
            <div class="banner-konflik">
                <div class="banner-text">
                    <p class="banner-title">Nama dan foto tidak cocok</p>
                    <p class="banner-desc"><?= e($konflik_teks) ?></p>
                </div>
                <div class="banner-actions">
                    <form action="<?= base_url('konfirmasi/resolve-conflict') ?>" method="POST" style="display:inline;">
                        <input type="hidden" name="pilihan" value="nama">
                        <button type="submit" class="btn-conflict-outline">Pakai nama</button>
                    </form>
                    <form action="<?= base_url('konfirmasi/resolve-conflict') ?>" method="POST" style="display:inline;">
                        <input type="hidden" name="pilihan" value="foto">
                        <button type="submit" class="btn-conflict-solid">Pakai foto</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('konfirmasi') ?>" method="POST" id="form-konfirmasi">
            <div class="layout-konfirmasi">
                <div class="col-makanan">
                    <div class="card card-makanan-info">
                        <div class="foto-wrap">
                            <?php if (!empty($analysis['foto'])): ?>
                                <img src="<?= base_url($analysis['foto']) ?>" alt="<?= e($analysis['nama_makanan']) ?>" class="foto-makanan-img">
                            <?php else: ?>
                                <div class="foto-demo-placeholder">
                                    <span>FOTO DEMO</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="makanan-meta">
                            <h2 class="nama-makanan-title"><?= e($analysis['nama_makanan'] ?? 'Makanan') ?></h2>
                            <div class="badge-source">
                                <span>Dikenali dari nama dan foto</span>
                            </div>
                        </div>

                        <p class="disclaimer-foto">Foto hanya membantu mengenali makanan, bukan mengukur gizi.</p>
                    </div>
                </div>

                <div class="col-bahan">
                    <div class="card card-daftar-bahan">
                        <h2 class="card-title">Bahan terdeteksi</h2>

                        <div class="list-bahan" id="list-bahan-container">
                            <?php foreach ($bahan as $index => $item): ?>
                                <?php if ($item['ada_data_gizi']): ?>
                                    <div class="item-bahan" data-id="<?= $index ?>">
                                        <div class="badge-check">✓</div>
                                        <div class="bahan-info">
                                            <p class="bahan-nama"><?= e($item['nama']) ?></p>
                                            <p class="bahan-ket"><?= e($item['keterangan']) ?></p>
                                        </div>
                                        <input type="hidden" name="bahan_nama[]" value="<?= e($item['nama']) ?>">
                                        <input type="hidden" name="bahan_berat[]" value="<?= e($item['berat_g']) ?>">
                                        <input type="hidden" name="bahan_ket[]" value="<?= e($item['keterangan']) ?>">
                                        <button type="button" class="btn-delete-item" onclick="hapusBahan(this)">Hapus</button>
                                    </div>
                                <?php else: ?>
                                    <div class="item-bahan item-tanpa-data" data-id="<?= $index ?>">
                                        <div class="badge-warn">!</div>
                                        <div class="bahan-info">
                                            <p class="bahan-nama"><?= e($item['nama']) ?></p>
                                            <p class="bahan-ket"><?= e($item['keterangan']) ?></p>
                                            <p class="bahan-warn-label">Data gizi belum tersedia, tidak ikut dihitung.</p>
                                        </div>
                                        <input type="hidden" name="bahan_nama[]" value="<?= e($item['nama']) ?>">
                                        <input type="hidden" name="bahan_berat[]" value="<?= e($item['berat_g']) ?>">
                                        <input type="hidden" name="bahan_ket[]" value="<?= e($item['keterangan']) ?>">
                                        <button type="button" class="btn-delete-item" onclick="hapusBahan(this)">Hapus</button>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <div class="tambah-bahan-row">
                            <div class="form-group flex-1">
                                <label class="form-label">Tambah bahan lain</label>
                                <div class="autocomplete-wrap">
                                    <input type="text" id="input-tambah-bahan" class="form-input" placeholder="Ketik nama bahan">
                                    <div id="autocomplete-list" class="autocomplete-dropdown"></div>
                                </div>
                            </div>
                            <button type="button" class="btn-outline" id="btn-tambah-bahan" style="height: 52px; align-self: flex-end;">Tambah bahan</button>
                        </div>
                        <p class="helper-text">Hapus dan tambah bahan hanya mengubah perhitungan ini.</p>
                    </div>

                    <div class="card card-porsi">
                        <h2 class="card-title">Porsi yang dimakan</h2>
                        
                        <div class="pengatur-porsi">
                            <button type="button" class="btn-porsi-step" id="btn-porsi-minus">−</button>
                            <span class="porsi-angka" id="porsi-display"><?= str_replace('.', ',', (string)$porsi) ?></span>
                            <span class="porsi-satuan">porsi</span>
                            <button type="button" class="btn-porsi-step" id="btn-porsi-plus">+</button>
                            <input type="hidden" name="porsi" id="input-porsi-val" value="<?= $porsi ?>">
                        </div>
                        <p class="helper-text">Satu porsi sedang sekitar 350 g.</p>
                    </div>
                </div>
            </div>

            <div class="bottom-actions">
                <a href="<?= base_url('makanan') ?>" class="btn-secondary">Ubah makanan</a>
                <button type="submit" class="btn-primary">Hitung estimasi gizi</button>
            </div>
        </form>
    </div>

    <script src="<?= asset_url('js/app.js') ?>"></script>
</body>
</html>
