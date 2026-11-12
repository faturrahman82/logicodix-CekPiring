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
                                <span><?= ($analysis['sumber_analisis'] ?? '') === 'ai' ? 'Dikenali AI dari foto dan nama' : 'Resep contoh, belum dari foto' ?></span>
                            </div>
                        </div>

                        <?php
                            $ukuranInfo = $ukuran_porsi ?? \App\Services\PortionSize::bawaan();
                            $ukuranAktif = \App\Services\PortionSize::valid($ukuranInfo['ukuran'] ?? 'sedang');
                        ?>
                        <div class="ukuran-porsi" id="ukuran-porsi" data-ukuran="<?= e($ukuranAktif) ?>">
                            <p class="ukuran-judul">Ukuran piring</p>
                            <div class="chip-group" role="radiogroup" aria-label="Ukuran piring">
                                <?php foreach (\App\Services\PortionSize::FAKTOR as $kode => $faktor): ?>
                                    <button type="button"
                                        class="chip-ukuran<?= $kode === $ukuranAktif ? ' aktif' : '' ?>"
                                        role="radio"
                                        aria-checked="<?= $kode === $ukuranAktif ? 'true' : 'false' ?>"
                                        data-ukuran="<?= e($kode) ?>"
                                        data-faktor="<?= e((string)$faktor) ?>">
                                        <?= e(\App\Services\PortionSize::label($kode)) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <p class="ukuran-alasan" id="ukuran-alasan">
                                <?php if (($ukuranInfo['sumber'] ?? '') === 'foto'): ?>
                                    Ditebak AI dari foto<?= !empty($ukuranInfo['alasan']) ? ': ' . e(rtrim($ukuranInfo['alasan'], '. ')) : '' ?>.
                                <?php elseif (($ukuranInfo['sumber'] ?? '') === 'pengguna'): ?>
                                    Ukuran dipilih sendiri.
                                <?php else: ?>
                                    Ukuran standar, belum ditebak dari foto.
                                <?php endif; ?>
                            </p>
                            <input type="hidden" name="ukuran_porsi" id="input-ukuran-porsi" value="<?= e($ukuranAktif) ?>">
                        </div>

                        <p class="disclaimer-foto">Ganti ukuran piring, gram semua bahan ikut berubah.</p>
                    </div>
                </div>

                <div class="col-bahan">
                    <div class="card card-daftar-bahan">
                        <h2 class="card-title">Bahan terdeteksi</h2>

                        <div class="list-bahan" id="list-bahan-container">
                            <?php foreach ($bahan as $index => $item): ?>
                                <?php if ($item['ada_data_gizi']): ?>
                                    <div class="item-bahan" data-id="<?= $index ?>" data-kkal100="<?= e((string)($item['kkal100'] ?? '')) ?>">
                                        <div class="badge-check">✓</div>
                                        <div class="bahan-info">
                                            <div class="bahan-header-line">
                                                <p class="bahan-nama"><?= e($item['nama']) ?></p>
                                                <span class="bahan-gram js-gram"><?= round($item['berat_g']) ?> g</span>
                                                <span class="badge-cal-inline js-kkal"><?= $item['kalori_estimasi'] !== null ? '±' . $item['kalori_estimasi'] . ' kkal' : '' ?></span>
                                            </div>
                                            <p class="bahan-ket"><?= e($item['keterangan']) ?></p>
                                            <div class="bahan-meta-tags">
                                                <span class="badge-completeness completeness-<?= $item['kelengkapan'] ?? 'data_lengkap' ?>">
                                                    <?= e($item['kelengkapan_label'] ?? 'Data Lengkap') ?>
                                                </span>
                                                <span class="badge-source-tag">
                                                    <?= e($item['sumber_data'] ?? 'TKPI 2017') ?>
                                                </span>
                                            </div>
                                        </div>
                                        <input type="hidden" name="bahan_nama[]" value="<?= e($item['nama']) ?>">
                                        <input type="hidden" name="bahan_berat[]" value="<?= e($item['berat_g']) ?>">
                                        <input type="hidden" name="bahan_ket[]" value="<?= e($item['keterangan']) ?>">
                                        <div class="bahan-actions">
                                            <button type="button" class="btn-edit-item" onclick="editBahan(this)">Edit</button>
                                            <button type="button" class="btn-delete-item" onclick="hapusBahan(this)">Hapus</button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="item-bahan item-tanpa-data" data-id="<?= $index ?>" data-kkal100="">
                                        <div class="badge-warn">!</div>
                                        <div class="bahan-info">
                                            <div class="bahan-header-line">
                                                <p class="bahan-nama"><?= e($item['nama']) ?></p>
                                                <span class="bahan-gram js-gram"><?= round($item['berat_g']) ?> g</span>
                                                <span class="badge-cal-inline js-kkal"></span>
                                            </div>
                                            <p class="bahan-ket"><?= e($item['keterangan']) ?></p>
                                            <div class="bahan-meta-tags">
                                                <span class="badge-completeness badge-nodata">Belum ada data</span>
                                                <span class="bahan-warn-label"><?= ((float)$item['berat_g'] <= 3) ? 'Takaran kecil, tidak memengaruhi hasil.' : 'Tidak ikut dihitung. Hapus atau ganti dengan bahan yang mirip.' ?></span>
                                            </div>
                                        </div>
                                        <input type="hidden" name="bahan_nama[]" value="<?= e($item['nama']) ?>">
                                        <input type="hidden" name="bahan_berat[]" value="<?= e($item['berat_g']) ?>">
                                        <input type="hidden" name="bahan_ket[]" value="<?= e($item['keterangan']) ?>">
                                        <div class="bahan-actions">
                                            <button type="button" class="btn-edit-item" onclick="editBahan(this)">Edit</button>
                                            <button type="button" class="btn-delete-item" onclick="hapusBahan(this)">Hapus</button>
                                        </div>
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
                            <button type="button" class="btn-outline" id="btn-tambah-bahan">Tambah bahan</button>
                        </div>
                        <p class="helper-text">Hapus dan tambah bahan hanya mengubah perhitungan ini.</p>
                    </div>

                    <div class="card card-porsi">
                        <h2 class="card-title">Berapa piring kamu makan?</h2>

                        <div class="pengatur-porsi">
                            <button type="button" class="btn-porsi-step" id="btn-porsi-minus" aria-label="Kurangi porsi">−</button>
                            <span class="porsi-angka" id="porsi-display"><?= e(\App\Services\PortionSize::formatPorsi((float)$porsi)) ?></span>
                            <span class="porsi-satuan">piring</span>
                            <button type="button" class="btn-porsi-step" id="btn-porsi-plus" aria-label="Tambah porsi">+</button>
                            <input type="hidden" name="porsi" id="input-porsi-val" value="<?= e((string)$porsi) ?>">
                        </div>
                        <p class="total-porsi" id="total-porsi" aria-live="polite">
                            <?php
                                $totalGramAwal = array_sum(array_map(fn($b) => (float)$b['berat_g'], $bahan)) * (float)$porsi;
                                $totalKkalAwal = array_sum(array_map(fn($b) => (float)($b['kalori_estimasi'] ?? 0), $bahan)) * (float)$porsi;
                            ?>
                            Total <strong id="total-gram"><?= number_format($totalGramAwal, 0, ',', '.') ?></strong> g · <strong id="total-kkal"><?= number_format($totalKkalAwal, 0, ',', '.') ?></strong> kkal
                        </p>
                        <p class="helper-text" id="total-catatan"></p>
                    </div>
                </div>
            </div>

            <div class="bottom-actions">
                <a href="<?= base_url('makanan') ?>" class="btn-secondary">Ubah makanan</a>
                <button type="submit" class="btn-primary" id="btn-submit-konfirmasi">Hitung estimasi gizi</button>
            </div>
        </form>
    </div>

    <div class="modal-overlay" id="loading-hitung-gizi" aria-hidden="true" style="display: none;">
        <div class="card card-loading-cek">
            <div class="loading-spinner-circle">
                <svg width="64" height="64" viewBox="0 0 72 72" fill="none" class="animate-spin">
                    <circle cx="36" cy="36" r="30" stroke="#e2e8f0" stroke-width="6"/>
                    <path d="M36 6C52.5685 6 66 19.4315 66 36" stroke="#047857" stroke-width="6" stroke-linecap="round"/>
                </svg>
            </div>

            <h2 class="loading-title">Menghitung estimasi gizi</h2>
            <p class="loading-subtitle"><?= !empty($analysis['nama_makanan']) ? 'Menganalisis "' . e($analysis['nama_makanan']) . '"...' : 'Menyiapkan rincian kalori &amp; evaluasi gizi...' ?></p>

<ol class="loading-steps-mini" id="langkah-hitung">
                <li class="mini-step-item" data-langkah="1">
                    <span class="mini-step-dot"></span>
                    <span class="mini-step-isi"><span class="mini-step-nama">Mencocokkan bahan dengan data komposisi</span><span class="mini-step-detail"></span></span>
                </li>
                <li class="mini-step-item" data-langkah="2">
                    <span class="mini-step-dot"></span>
                    <span class="mini-step-isi"><span class="mini-step-nama">Menghitung energi dan makro per porsi</span><span class="mini-step-detail"></span></span>
                </li>
                <li class="mini-step-item" data-langkah="3">
                    <span class="mini-step-dot"></span>
                    <span class="mini-step-isi"><span class="mini-step-nama">Menyesuaikan profil kesehatan dan batas aman</span><span class="mini-step-detail"></span></span>
                </li>
            </ol>
            <p class="loading-galat" id="loading-galat" role="alert" hidden></p>
            <button type="button" class="btn-outline" id="btn-tutup-loading" hidden>Kembali ke bahan</button>
        </div>
    </div>


    <div class="modal-overlay" id="modal-edit-bahan" aria-hidden="true">
      <div class="modal-edit-card" role="dialog" aria-modal="true" aria-labelledby="modal-edit-title">
        <div class="modal-edit-header"><div><h2 id="modal-edit-title">Edit bahan makanan</h2><p>Sesuaikan bahan dan jumlah sesuai yang kamu masak.</p></div>
          <button type="button" class="modal-close" onclick="tutupModalEdit()" aria-label="Tutup">&times;</button>
        </div>
        <div class="modal-edit-body">
          <label class="form-label" for="edit-nama-bahan">Nama bahan</label>
          <input class="form-input" type="text" id="edit-nama-bahan" autocomplete="off">
          <div class="edit-quantity-grid">
            <div><label class="form-label" for="edit-jumlah-bahan">Jumlah</label><input class="form-input" type="number" id="edit-jumlah-bahan" min="0.1" step="0.1" value="100"></div>
            <div><label class="form-label" for="edit-satuan-bahan">Satuan</label>
              <select class="form-input" id="edit-satuan-bahan">
                <option value="g">gram (g)</option><option value="butir">butir</option><option value="sdm">sendok makan</option><option value="sdt">sendok teh</option><option value="ml">mililiter (ml)</option><option value="potong">potong</option><option value="buah">buah</option><option value="siung">siung</option><option value="lembar">lembar</option><option value="mangkuk">mangkuk</option><option value="tusuk">tusuk</option>
              </select>
            </div>
          </div>
          <p class="modal-edit-note">Bahan cair seperti minyak dan kecap menggunakan ml; bahan padat menggunakan gram. Sistem mengonversi perkiraan berat ke gram untuk perhitungan gizi.</p>
        </div>
        <div class="modal-edit-footer"><button type="button" class="btn-secondary" onclick="tutupModalEdit()">Batal</button><button type="button" class="btn-primary" onclick="simpanEditBahan()">Simpan perubahan</button></div>
      </div>
    </div>

    <script src="<?= asset_url('js/app.js') ?>"></script>
</body>
</html>
