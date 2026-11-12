document.addEventListener('DOMContentLoaded', function () {
    const inputUmur = document.getElementById('input-umur');
    const inputTinggi = document.getElementById('input-tinggi');
    const inputBerat = document.getElementById('input-berat');
    const targetAngka = document.getElementById('target-angka');
    const targetDeskripsi = document.getElementById('target-deskripsi');

    if (inputUmur && inputTinggi && inputBerat && targetAngka) {
        const alertDataFisik = document.getElementById('alert-data-fisik');
        const alertIcon = document.getElementById('alert-data-fisik-icon');
        const alertTitle = document.getElementById('alert-data-fisik-title');
        const alertDesc = document.getElementById('alert-data-fisik-desc');

        function setAlert(type, title, desc, iconText) {
            if (!alertDataFisik) return;
            alertDataFisik.className = 'alert-box alert-sm ' + type;
            alertDataFisik.style.display = 'flex';
            if (alertIcon) alertIcon.textContent = iconText || '!';
            if (alertTitle) alertTitle.textContent = title;
            if (alertDesc) alertDesc.textContent = desc;
        }

        function clearAlert() {
            if (!alertDataFisik) return;
            alertDataFisik.style.display = 'none';
        }

        function hitungKaloriClient() {
            const uRaw = inputUmur.value.trim();
            const tRaw = inputTinggi.value.trim();
            const bRaw = inputBerat.value.trim();

            const umur = parseFloat(uRaw);
            const tinggi = parseFloat(tRaw);
            const berat = parseFloat(bRaw);

            const genderEl = document.querySelector('input[name="gender"]:checked');
            const tujuanEl = document.querySelector('input[name="tujuan"]:checked');

            const gender = genderEl ? genderEl.value : 'perempuan';
            const tujuan = tujuanEl ? tujuanEl.value : 'cutting';
            const tujuanLabel = tujuan.charAt(0).toUpperCase() + tujuan.slice(1);

            let tdee = 2000;
            let isCustom = false;

            inputUmur.classList.remove('input-error');
            inputTinggi.classList.remove('input-error');
            inputBerat.classList.remove('input-error');

            const anyFilled = (uRaw !== '' || tRaw !== '' || bRaw !== '');
            const allFilled = (uRaw !== '' && tRaw !== '' && bRaw !== '');

            let hasError = false;
            if (uRaw !== '' && (isNaN(umur) || umur < 1 || umur > 120)) {
                inputUmur.classList.add('input-error');
                hasError = true;
            }
            if (tRaw !== '' && (isNaN(tinggi) || tinggi < 50 || tinggi > 250)) {
                inputTinggi.classList.add('input-error');
                hasError = true;
            }
            if (bRaw !== '' && (isNaN(berat) || berat < 20 || berat > 300)) {
                inputBerat.classList.add('input-error');
                hasError = true;
            }

            if (hasError) {
                setAlert('alert-error', 'Angka tidak wajar', 'Masukkan umur (1–120 thn), tinggi (50–250 cm), dan berat (20–300 kg) yang wajar.', '!');
            } else if (anyFilled && !allFilled) {
                const missing = [];
                if (uRaw === '') { missing.push('umur'); inputUmur.classList.add('input-error'); }
                if (tRaw === '') { missing.push('tinggi'); inputTinggi.classList.add('input-error'); }
                if (bRaw === '') { missing.push('berat'); inputBerat.classList.add('input-error'); }
                setAlert('alert-warning', 'Data fisik belum lengkap', 'Lengkapi juga ' + missing.join(' dan ') + ' agar perkiraan pribadi dari tinggi dan berat bisa dihitung.', '▲');
            } else if (allFilled) {
                isCustom = true;
                let bmr = 0;
                if (gender === 'laki-laki') {
                    bmr = (10 * berat) + (6.25 * tinggi) - (5 * umur) + 5;
                } else {
                    bmr = (10 * berat) + (6.25 * tinggi) - (5 * umur) - 161;
                }
                tdee = bmr * 1.375;
                setAlert('alert-success', 'Data fisik lengkap', 'Target memakai AKG Kemenkes 2019, dengan perkiraan pribadi dari tinggi dan berat sebagai pembanding.', '✓');
            } else {
                clearAlert();
            }

            let tabelAkg = [];
            try {
                tabelAkg = JSON.parse(document.getElementById('data-akg')?.textContent || '[]');
            } catch (e) {
                tabelAkg = [];
            }
            const umurBulat = Math.floor(umur);
            const barisAkg = (!isNaN(umurBulat) && umurBulat >= 10)
                ? tabelAkg.find(r => r.jenis_kelamin === gender && umurBulat >= r.umur_min && umurBulat <= r.umur_maks)
                : null;
            const faktorTujuan = { cutting: 0.85, bulking: 1.15, seimbang: 1.0 }[tujuan] || 1.0;
            const dasar = barisAkg ? barisAkg.energi_kkal : 2000;
            const kaloriFinal = dasar * faktorTujuan;

            targetAngka.textContent = Math.round(kaloriFinal).toLocaleString('id-ID');

            if (targetDeskripsi) {
                const penyesuaian = tujuan === 'cutting' ? ' dikurangi 15% untuk Cutting'
                    : (tujuan === 'bulking' ? ' ditambah 15% untuk Bulking' : '');
                if (barisAkg) {
                    const label = barisAkg.umur_maks >= 200 ? `${barisAkg.umur_min} tahun ke atas` : `${barisAkg.umur_min}-${barisAkg.umur_maks} tahun`;
                    let teks = `AKG Kemenkes 2019 untuk ${gender} ${label}: ${barisAkg.energi_kkal.toLocaleString('id-ID')} kkal${penyesuaian}.`;
                    if (isCustom) {
                        teks += ` Perkiraan pribadi dari tinggi dan berat: sekitar ${Math.round(tdee).toLocaleString('id-ID')} kkal.`;
                    }
                    targetDeskripsi.textContent = teks + ' Estimasi edukatif, bukan resep diet.';
                } else {
                    targetDeskripsi.textContent = `Target umum 2.000 kkal${penyesuaian}. Isi umur untuk memakai AKG Kemenkes 2019.`;
                }
            }
        }

        const elemenInputProfil = document.querySelectorAll(
            '#input-umur, #input-tinggi, #input-berat, input[name="gender"], input[name="tujuan"]'
        );
        elemenInputProfil.forEach(el => {
            el.addEventListener('input', hitungKaloriClient);
            el.addEventListener('change', hitungKaloriClient);
        });

        [inputUmur, inputTinggi, inputBerat].forEach(inp => {
            if (!inp) return;
            inp.addEventListener('keydown', function(e) {
                if (['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.key)) {
                    return;
                }
                if (e.key === '.' && inp === inputBerat && !inp.value.includes('.')) {
                    return;
                }
                if (!/^[0-9]$/.test(e.key)) {
                    e.preventDefault();
                    setAlert('alert-error', 'Input hanya angka', 'Kolom ini hanya boleh diisi angka.', '!');
                }
            });
        });

        const formProfil = document.getElementById('form-profil');
        if (formProfil) {
            formProfil.addEventListener('submit', function (e) {
                const uRaw = inputUmur.value.trim();
                const tRaw = inputTinggi.value.trim();
                const bRaw = inputBerat.value.trim();

                const umur = parseFloat(uRaw);
                const tinggi = parseFloat(tRaw);
                const berat = parseFloat(bRaw);

                if (uRaw === '' || tRaw === '' || bRaw === '') {
                    e.preventDefault();
                    const missing = [];
                    if (uRaw === '') { missing.push('umur'); inputUmur.classList.add('input-error'); }
                    if (tRaw === '') { missing.push('tinggi (cm)'); inputTinggi.classList.add('input-error'); }
                    if (bRaw === '') { missing.push('berat (kg)'); inputBerat.classList.add('input-error'); }

                    setAlert(
                        'alert-error',
                        'Data fisik belum diisi',
                        'Mohon isi ' + missing.join(', ') + ' dengan angka terlebih dahulu sebelum menyimpan profil. Atau klik "Lewati, pakai target umum" jika tidak ingin mengisi.',
                        '!'
                    );

                    if (uRaw === '') inputUmur.focus();
                    else if (tRaw === '') inputTinggi.focus();
                    else if (bRaw === '') inputBerat.focus();
                    return false;
                }

                let hasInvalid = false;
                if (isNaN(umur) || umur < 1 || umur > 120) {
                    inputUmur.classList.add('input-error');
                    hasInvalid = true;
                }
                if (isNaN(tinggi) || tinggi < 50 || tinggi > 250) {
                    inputTinggi.classList.add('input-error');
                    hasInvalid = true;
                }
                if (isNaN(berat) || berat < 20 || berat > 300) {
                    inputBerat.classList.add('input-error');
                    hasInvalid = true;
                }

                if (hasInvalid) {
                    e.preventDefault();
                    setAlert(
                        'alert-error',
                        'Angka tidak valid',
                        'Pastikan umur (1–120 thn), tinggi (50–250 cm), dan berat (20–300 kg) diisi dengan angka yang wajar.',
                        '!'
                    );
                    return false;
                }
            });
        }

        hitungKaloriClient();
    }

    const inputNama = document.getElementById('input-nama-makanan');
    const inputFile = document.getElementById('input-file-foto');
    const btnPilihFoto = document.getElementById('btn-pilih-foto');
    const btnGantiFoto = document.getElementById('btn-ganti-foto');
    const dropzoneEmpty = document.getElementById('dropzone-empty');
    const dropzonePreview = document.getElementById('dropzone-preview');
    const imgPreview = document.getElementById('img-preview');
    const btnSubmitMakanan = document.getElementById('btn-submit-makanan');
    const submitHint = document.getElementById('submit-hint');
    const dropzoneFoto = document.getElementById('dropzone-foto');

    if (inputNama && btnSubmitMakanan) {
        function checkSubmitValid() {
            const namaValid = inputNama.value.trim().length > 0;
            const inputFotoPath = document.getElementById('input-foto-path');
            const hasExistingFoto = inputFotoPath && inputFotoPath.value.trim() !== '';
            const hasFoto = (inputFile && inputFile.files && inputFile.files.length > 0) || 
                            hasExistingFoto ||
                            (dropzonePreview && dropzonePreview.classList.contains('active') && imgPreview && imgPreview.getAttribute('src') && imgPreview.getAttribute('src').trim() !== '');

            if (namaValid && hasFoto) {
                btnSubmitMakanan.removeAttribute('disabled');
                if (submitHint) submitHint.style.display = 'none';
            } else {
                btnSubmitMakanan.setAttribute('disabled', 'disabled');
                if (submitHint) {
                    submitHint.style.display = 'inline';
                    if (!namaValid && !hasFoto) {
                        submitHint.textContent = 'Isi nama makanan dan tambah foto dulu.';
                    } else if (!namaValid) {
                        submitHint.textContent = 'Isi nama makanan dulu.';
                    } else {
                        submitHint.textContent = 'Tambah foto makanan dulu.';
                    }
                }
            }
        }

        const formMakanan = document.getElementById('form-makanan');
        if (formMakanan) {
            formMakanan.addEventListener('submit', function (e) {
                const namaValid = inputNama.value.trim().length > 0;
                const inputFotoPath = document.getElementById('input-foto-path');
                const hasExistingFoto = inputFotoPath && inputFotoPath.value.trim() !== '';
                const hasFoto = (inputFile && inputFile.files && inputFile.files.length > 0) || 
                                hasExistingFoto ||
                                (dropzonePreview && dropzonePreview.classList.contains('active') && imgPreview && imgPreview.getAttribute('src') && imgPreview.getAttribute('src').trim() !== '');

                if (!namaValid || !hasFoto) {
                    e.preventDefault();
                    if (!namaValid) {
                        inputNama.focus();
                    } else if (btnPilihFoto) {
                        btnPilihFoto.focus();
                    }
                    return false;
                }

                // Tampilkan loader saat cek bahan
                const loadingOverlay = document.getElementById('loading-cek-bahan');
                const labelNama = document.getElementById('loading-nama-makanan');

                if (loadingOverlay) {
                    if (labelNama && inputNama.value.trim()) {
                        labelNama.textContent = 'Menganalisis "' + inputNama.value.trim() + '"...';
                    }
                    loadingOverlay.style.display = 'flex';
                    loadingOverlay.classList.add('active');
                    loadingOverlay.setAttribute('aria-hidden', 'false');

                    // Animasi perpindahan status langkah
                    setTimeout(() => {
                        const s1 = document.getElementById('step-upload');
                        const s2 = document.getElementById('step-ai');
                        if (s1) { s1.classList.remove('step-active'); s1.classList.add('step-done'); }
                        if (s2) { s2.classList.add('step-active'); }
                    }, 1000);

                    setTimeout(() => {
                        const s2 = document.getElementById('step-ai');
                        const s3 = document.getElementById('step-db');
                        if (s2) { s2.classList.remove('step-active'); s2.classList.add('step-done'); }
                        if (s3) { s3.classList.add('step-active'); }
                    }, 2400);
                }

                if (btnSubmitMakanan) {
                    btnSubmitMakanan.classList.add('btn-loading');
                    btnSubmitMakanan.innerHTML = '<span class="spinner-sm"></span> Memeriksa bahan...';
                    setTimeout(() => {
                        btnSubmitMakanan.disabled = true;
                    }, 20);
                }
            });
        }

        if (btnPilihFoto && inputFile) {
            btnPilihFoto.addEventListener('click', () => inputFile.click());
        }
        if (btnGantiFoto && inputFile) {
            btnGantiFoto.addEventListener('click', () => inputFile.click());
        }

        if (inputFile) {
            inputFile.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        if (imgPreview) imgPreview.src = e.target.result;
                        if (dropzoneEmpty) dropzoneEmpty.style.display = 'none';
                        if (dropzonePreview) {
                            dropzonePreview.style.display = 'block';
                            dropzonePreview.classList.add('active');
                        }
                        checkSubmitValid();
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }

        if (dropzoneFoto && inputFile) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzoneFoto.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropzoneFoto.style.borderColor = 'var(--primary)';
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzoneFoto.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropzoneFoto.style.borderColor = 'var(--inputborder)';
                }, false);
            });

            dropzoneFoto.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files && files[0]) {
                    inputFile.files = files;
                    const event = new Event('change');
                    inputFile.dispatchEvent(event);
                }
            });
        }

        inputNama.addEventListener('input', checkSubmitValid);
        checkSubmitValid();
    }

    const btnPorsiMinus = document.getElementById('btn-porsi-minus');
    const btnPorsiPlus = document.getElementById('btn-porsi-plus');
    const porsiDisplay = document.getElementById('porsi-display');
    const inputPorsiVal = document.getElementById('input-porsi-val');

    if (btnPorsiMinus && btnPorsiPlus && porsiDisplay && inputPorsiVal) {
        function setPorsiKonfirmasi(nilai) {
            const porsi = Math.min(3.0, Math.max(0.5, Math.round(nilai * 10) / 10));
            inputPorsiVal.value = porsi;
            porsiDisplay.textContent = formatPorsiID(porsi);
            hitungUlangKonfirmasi();
        }
        btnPorsiMinus.addEventListener('click', () => setPorsiKonfirmasi((parseFloat(inputPorsiVal.value) || 1.0) - 0.5));
        btnPorsiPlus.addEventListener('click', () => setPorsiKonfirmasi((parseFloat(inputPorsiVal.value) || 1.0) + 0.5));
    }

    document.querySelectorAll('.chip-ukuran').forEach(chip => {
        chip.addEventListener('click', () => gantiUkuranPiring(chip.dataset.ukuran));
    });

    const inputTambahBahan = document.getElementById('input-tambah-bahan');
    const btnTambahBahan = document.getElementById('btn-tambah-bahan');
    const autocompleteList = document.getElementById('autocomplete-list');
    const listBahanContainer = document.getElementById('list-bahan-container');

    if (inputTambahBahan && autocompleteList && listBahanContainer) {
        let debounceTimer;

        inputTambahBahan.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const query = this.value.trim();
            if (query.length < 2) {
                autocompleteList.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`api/search-ingredient?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(res => {
                        if (res.sukses && res.data && res.data.length > 0) {
                            autocompleteList.innerHTML = '';
                            res.data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'autocomplete-item';
                                div.textContent = `${item.nama} (${item.energi_kkal} kkal / 100g)`;
                                div.addEventListener('click', () => {
                                    tambahBahanItem(item.nama, 50, 'bahan tambahan', item.energi_kkal);
                                    inputTambahBahan.value = '';
                                    autocompleteList.style.display = 'none';
                                });
                                autocompleteList.appendChild(div);
                            });
                            autocompleteList.style.display = 'block';
                        } else {
                            autocompleteList.style.display = 'none';
                        }
                    })
                    .catch(() => {
                        autocompleteList.style.display = 'none';
                    });
            }, 250);
        });

        document.addEventListener('click', (e) => {
            if (!inputTambahBahan.contains(e.target) && !autocompleteList.contains(e.target)) {
                autocompleteList.style.display = 'none';
            }
        });

        if (btnTambahBahan) {
            btnTambahBahan.addEventListener('click', function () {
                const nama = inputTambahBahan.value.trim();
                if (nama.length > 0) {
                    tambahBahanItem(nama, 50, 'bahan tambahan', null);
                    inputTambahBahan.value = '';
                    autocompleteList.style.display = 'none';
                }
            });
        }
    }

    const porsiHasilDisplay = document.getElementById('porsi-hasil-display');
    if (porsiHasilDisplay) {
        let currentPorsi = parseFloat(porsiHasilDisplay.dataset.porsi) || 1.0;
        const geraknyaDikurangi = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let hasilSebelum = null;
        try {
            hasilSebelum = JSON.parse(document.getElementById('data-hasil-awal')?.textContent || 'null');
        } catch (e) {
            hasilSebelum = null;
        }

        function animasiAngka(el, ke, format) {
            if (!el) return;
            const dari = parseFloat(el.dataset.nilai ?? '');
            el.dataset.nilai = ke;
            if (geraknyaDikurangi || !Number.isFinite(dari) || dari === ke) {
                el.textContent = format(ke);
                return;
            }
            const mulai = performance.now();
            const lama = 650;
            function langkah(t) {
                const p = Math.min(1, (t - mulai) / lama);
                const halus = 1 - Math.pow(1 - p, 3);
                el.textContent = format(dari + (ke - dari) * halus);
                if (p < 1) requestAnimationFrame(langkah);
            }
            requestAnimationFrame(langkah);
        }

        function tampilkanRingkasan(lama, baru) {
            const toast = document.getElementById('toast-hasil');
            if (!toast || !lama) return;
            const bagian = [];
            Object.entries(baru.risiko || {}).forEach(([kode, r]) => {
                const l = (lama.risiko || {})[kode];
                if (!l || !l.tersedia || !r.dinilai || l.persen === r.persen) return;
                bagian.push(`${r.nama} ${l.persen}% → ${r.persen}%`);
            });
            const selisih = (lama.energi ?? 0) - (baru.total_gizi?.energi_kkal ?? 0);
            if (selisih >= 1) bagian.push(`${formatAngkaID(selisih)} kkal lebih sedikit`);
            if (selisih <= -1) bagian.push(`${formatAngkaID(-selisih)} kkal lebih banyak`);
            if (!bagian.length) return;
            toast.textContent = bagian.join(' · ');
            toast.hidden = false;
            toast.classList.remove('muncul');
            void toast.offsetWidth;
            toast.classList.add('muncul');
            clearTimeout(tampilkanRingkasan.timer);
            tampilkanRingkasan.timer = setTimeout(() => { toast.hidden = true; }, 4500);
        }
        let sedangMemuat = false;

        function kirimHasil(url, data) {
            if (sedangMemuat) return Promise.resolve(null);
            sedangMemuat = true;
            document.body.classList.add('sedang-hitung');
            const formData = new FormData();
            Object.entries(data || {}).forEach(([k, v]) => formData.append(k, v));
            return fetch(url, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(res => {
                    if (res && res.sukses) renderHasil(res);
                    else if (res) location.reload();
                    return res;
                })
                .catch(err => { console.error('Gagal menghitung ulang:', err); return null; })
                .finally(() => {
                    sedangMemuat = false;
                    document.body.classList.remove('sedang-hitung');
                });
        }

        function setTeks(id, teks) {
            const el = document.getElementById(id);
            if (el) el.textContent = teks;
        }

        function tampil(id, terlihat) {
            const el = document.getElementById(id);
            if (el) el.hidden = !terlihat;
        }

        function renderRisikoItem(r) {
            const tersedia = !!r.tersedia;
            const badge = document.getElementById('badge-' + r.kode);
            if (badge) {
                badge.className = 'badge-level level-' + r.badge;
                badge.textContent = `${r.simbol} ${r.level}`;
            }
            const desimal = r.satuan === 'g' ? 1 : 0;
            const elAngka = document.getElementById('angka-' + r.kode);
            if (tersedia) {
                animasiAngka(elAngka, r.nilai, n => `${formatAngkaID(n, desimal)} ${r.satuan}`);
            } else if (elAngka) {
                elAngka.textContent = '—';
            }
            const lamaR = hasilSebelum && hasilSebelum.risiko ? hasilSebelum.risiko[r.kode] : null;
            const item = document.getElementById('item-risiko-' + r.kode);
            if (item && lamaR && tersedia && r.dinilai && lamaR.tersedia && lamaR.persen !== r.persen) {
                item.classList.remove('berubah-turun', 'berubah-naik');
                void item.offsetWidth;
                item.classList.add(r.persen < lamaR.persen ? 'berubah-turun' : 'berubah-naik');
            }
            const dinilai = !!r.dinilai;
            setTeks('desc-' + r.kode, tersedia
                ? `${dinilai ? r.persen + '% ' : ''}${r.keterangan_profil}${r.sebagian ? ' · data sebagian bahan' : ''}`
                : 'Data bahan belum tersedia');
            const bar = document.getElementById('bar-' + r.kode);
            if (bar) {
                bar.style.width = (tersedia && r.dinilai ? Math.min(100, r.persen) : 0) + '%';
                bar.className = 'progress-fill fill-' + r.badge;
            }
            const peny = document.getElementById('penyumbang-' + r.kode);
            if (peny) {
                peny.textContent = r.penyumbang || '';
                peny.hidden = !(tersedia && r.penyumbang);
            }
        }

        function renderHasil(res) {
            currentPorsi = res.porsi;
            porsiHasilDisplay.dataset.porsi = res.porsi;
            porsiHasilDisplay.textContent = res.porsi_teks;
            setTeks('label-porsi-header', res.porsi_teks);
            setTeks('label-berat-header', formatAngkaID(res.total_gizi.berat_total_g));

            const v = res.verdict;
            const card = document.getElementById('verdict-card');
            if (card) card.className = `card card-verdict verdict-${v.badge_class}`;
            const vb = document.getElementById('verdict-badge');
            if (vb) {
                vb.className = `verdict-badge badge-${v.badge_class}`;
                vb.textContent = v.badge_text;
            }
            setTeks('verdict-title', v.judul);
            setTeks('verdict-desc', v.deskripsi);
            setTeks('verdict-catatan', v.catatan || '');
            tampil('verdict-catatan', !!v.catatan);

            const btnPakai = document.getElementById('btn-pakai-porsi');
            if (btnPakai) {
                btnPakai.dataset.porsi = res.porsi_disarankan;
                setTeks('porsi-saran-teks', res.porsi_disarankan_teks);
                btnPakai.hidden = !(res.porsi_disarankan < res.porsi - 0.001);
            }
            const b = res.perbandingan;
            setTeks('porsi-banding', b
                ? `${b.nama}: 1 piring ${b.persen_penuh}% → ${formatPorsiID(b.porsi_saran)} piring ${b.persen_saran}%`
                : '');
            tampil('porsi-banding', !!b);

            Object.values(res.risiko || {}).forEach(renderRisikoItem);

            animasiAngka(document.getElementById('energi-angka'), res.total_gizi.energi_kkal, n => formatAngkaID(Math.round(n)));
            setTeks('persen-kalori-val', res.persen_kalori + '%');
            const ring = document.getElementById('circle-energy-fill');
            if (ring) {
                const keliling = 2 * Math.PI * 76;
                ring.style.strokeDashoffset = keliling - (keliling * Math.min(1.0, res.persen_kalori / 100));
            }
            setTeks('karbo-val', formatAngkaID(res.total_gizi.karbohidrat_g) + ' g');
            setTeks('protein-val', formatAngkaID(res.total_gizi.protein_g) + ' g');
            setTeks('lemak-val', formatAngkaID(res.total_gizi.lemak_g) + ' g');

            const daftar = Array.isArray(res.saran_list) ? res.saran_list : [];
            const sudah = Array.isArray(res.saran_diterapkan) ? res.saran_diterapkan : [];
            const ul = document.getElementById('saran-list');
            if (ul) {
                ul.innerHTML = daftar.map(sr => `
                    <li class="saran-item">
                        <div class="saran-teks">
                            <p class="saran-aksi">${escapeHtml(String(sr.aksi))}</p>
                            <p class="saran-efek">${escapeHtml(String(sr.efek))}</p>
                        </div>
                        <button type="button" class="btn-terapkan" data-zat="${escapeHtml(String(sr.zat))}">Terapkan</button>
                    </li>`).join('');
                ul.hidden = daftar.length === 0;
            }
            const ulSudah = document.getElementById('saran-diterapkan-list');
            if (ulSudah) {
                ulSudah.innerHTML = sudah.map(sd => `<li>✓ ${escapeHtml(String(sd.aksi))}: ${escapeHtml(String(sd.efek))}</li>`).join('');
            }
            tampil('saran-sudah', sudah.length > 0);
            tampil('card-saran', daftar.length > 0 || sudah.length > 0);

            const tbody = document.getElementById('rincian-body');
            if (tbody && Array.isArray(res.rincian)) {
                tbody.innerHTML = res.rincian.map(rb => `
                    <tr>
                        <td>${escapeHtml(String(rb.nama))}</td>
                        <td>${formatAngkaID(rb.gram)} g</td>
                        <td>${rb.ada_data ? formatAngkaID(rb.kkal) + ' kkal' : 'data belum ada'}</td>
                    </tr>`).join('');
            }

            tampilkanRingkasan(hasilSebelum, res);
            hasilSebelum = {
                energi: res.total_gizi.energi_kkal,
                risiko: Object.fromEntries(Object.entries(res.risiko || {}).map(([k, r]) => [k, { nama: r.nama, persen: r.persen, tersedia: !!r.dinilai }])),
            };

            if (card) {
                card.classList.remove('kedip');
                void card.offsetWidth;
                card.classList.add('kedip');
            }
        }

        function ubahPorsi(nilai) {
            const porsi = Math.round(nilai * 10) / 10;
            if (porsi < 0.3 || porsi > 3.0) return;
            kirimHasil('api/recalculate', { porsi: porsi });
        }

        document.getElementById('btn-hasil-minus')?.addEventListener('click', () => ubahPorsi(currentPorsi - 0.1));
        document.getElementById('btn-hasil-plus')?.addEventListener('click', () => ubahPorsi(currentPorsi + 0.1));
        document.getElementById('btn-pakai-porsi')?.addEventListener('click', function () {
            ubahPorsi(parseFloat(this.dataset.porsi) || 1.0);
        });
        document.getElementById('card-saran')?.addEventListener('click', function (e) {
            const tombol = e.target.closest('.btn-terapkan');
            if (tombol) kirimHasil('api/terapkan-saran', { zat: tombol.dataset.zat });
        });
        document.getElementById('btn-batalkan-saran')?.addEventListener('click', () => kirimHasil('api/batalkan-saran'));
    }
});

function hapusBahan(btn) {
    const item = btn.closest('.item-bahan');
    if (item) {
        item.style.opacity = '0';
        item.style.transform = 'translateX(20px)';
        item.style.transition = 'all 0.25s ease';
        setTimeout(() => {
            item.remove();
            hitungUlangKonfirmasi();
        }, 250);
    }
}

const FAKTOR_UKURAN = { kecil: 0.7, sedang: 1.0, besar: 1.3 };
const LABEL_UKURAN = { kecil: 'Kecil', sedang: 'Sedang', besar: 'Besar' };

function formatAngkaID(n, desimal = 0) {
    return Number(n).toLocaleString('id-ID', { maximumFractionDigits: desimal });
}

function formatPorsiID(n) {
    return formatAngkaID(Math.round(n * 10) / 10, 1);
}

function hitungUlangKonfirmasi() {
    const list = document.getElementById('list-bahan-container');
    if (!list) return;
    const porsi = parseFloat(document.getElementById('input-porsi-val')?.value) || 1.0;
    let totalGram = 0;
    let totalKkal = 0;
    let tanpaData = 0;

    list.querySelectorAll('.item-bahan').forEach(row => {
        const gram = parseFloat(row.querySelector('input[name="bahan_berat[]"]')?.value || '0') || 0;
        const kkal100 = parseFloat(row.dataset.kkal100);
        const gramEl = row.querySelector('.js-gram');
        const kkalEl = row.querySelector('.js-kkal');
        if (gramEl) gramEl.textContent = formatAngkaID(gram) + ' g';
        totalGram += gram;
        if (Number.isFinite(kkal100)) {
            const kkal = kkal100 * gram / 100;
            totalKkal += kkal;
            if (kkalEl) kkalEl.textContent = '±' + formatAngkaID(Math.round(kkal)) + ' kkal';
        } else {
            tanpaData++;
            if (kkalEl) kkalEl.textContent = '';
        }
    });

    const elGram = document.getElementById('total-gram');
    const elKkal = document.getElementById('total-kkal');
    const elCatatan = document.getElementById('total-catatan');
    if (elGram) elGram.textContent = formatAngkaID(Math.round(totalGram * porsi));
    if (elKkal) elKkal.textContent = formatAngkaID(Math.round(totalKkal * porsi));
    if (elCatatan) {
        elCatatan.textContent = tanpaData > 0
            ? `${tanpaData} bahan belum ada datanya dan tidak ikut dihitung.`
            : '';
    }
    const total = document.getElementById('total-porsi');
    if (total) {
        total.classList.remove('kedip');
        void total.offsetWidth;
        total.classList.add('kedip');
    }
}

function gantiUkuranPiring(ukuranBaru) {
    const wrap = document.getElementById('ukuran-porsi');
    if (!wrap || !FAKTOR_UKURAN[ukuranBaru]) return;
    const ukuranLama = wrap.dataset.ukuran || 'sedang';
    if (ukuranLama === ukuranBaru) return;
    const rasio = FAKTOR_UKURAN[ukuranBaru] / FAKTOR_UKURAN[ukuranLama];

    document.querySelectorAll('#list-bahan-container .item-bahan').forEach(row => {
        const input = row.querySelector('input[name="bahan_berat[]"]');
        if (!input) return;
        const gramBaru = Math.max(1, Math.round((parseFloat(input.value) || 0) * rasio));
        input.value = gramBaru;
        const ket = `sekitar ${gramBaru} g untuk piring ${LABEL_UKURAN[ukuranBaru].toLowerCase()}`;
        const ketInput = row.querySelector('input[name="bahan_ket[]"]');
        const ketLabel = row.querySelector('.bahan-ket');
        if (ketInput) ketInput.value = ket;
        if (ketLabel) ketLabel.textContent = ket;
    });

    wrap.dataset.ukuran = ukuranBaru;
    const hidden = document.getElementById('input-ukuran-porsi');
    if (hidden) hidden.value = ukuranBaru;
    wrap.querySelectorAll('.chip-ukuran').forEach(chip => {
        const aktif = chip.dataset.ukuran === ukuranBaru;
        chip.classList.toggle('aktif', aktif);
        chip.setAttribute('aria-checked', aktif ? 'true' : 'false');
    });
    const alasan = document.getElementById('ukuran-alasan');
    if (alasan) alasan.textContent = `Ukuran dipilih sendiri: gram bahan dikali ${formatAngkaID(rasio, 2)}.`;
    hitungUlangKonfirmasi();
}

function cariKkal100(nama) {
    return fetch(`api/search-ingredient?q=${encodeURIComponent(nama)}`)
        .then(res => res.json())
        .then(res => (res.sukses && res.data && res.data.length > 0) ? res.data[0].energi_kkal : null)
        .catch(() => null);
}

function tambahBahanItem(nama, berat, ket, kkal100) {
    const container = document.getElementById('list-bahan-container');
    if (!container) return;

    const div = document.createElement('div');
    div.className = 'item-bahan';
    div.dataset.kkal100 = (kkal100 !== null && kkal100 !== undefined) ? kkal100 : '';
    div.innerHTML = `
        <div class="badge-check">✓</div>
        <div class="bahan-info">
            <div class="bahan-header-line">
                <p class="bahan-nama">${escapeHtml(nama)}</p>
                <span class="bahan-gram js-gram">${berat} g</span>
                <span class="badge-cal-inline js-kkal"></span>
            </div>
            <p class="bahan-ket">${escapeHtml(ket)}</p>
        </div>
        <input type="hidden" name="bahan_nama[]" value="${escapeHtml(nama)}">
        <input type="hidden" name="bahan_berat[]" value="${berat}">
        <input type="hidden" name="bahan_ket[]" value="${escapeHtml(ket)}">
        <div class="bahan-actions">
            <button type="button" class="btn-edit-item" onclick="editBahan(this)">Edit</button>
            <button type="button" class="btn-delete-item" onclick="hapusBahan(this)">Hapus</button>
        </div>
    `;
    container.appendChild(div);
    if (div.dataset.kkal100 === '') {
        cariKkal100(nama).then(k => {
            if (k !== null) div.dataset.kkal100 = k;
            hitungUlangKonfirmasi();
        });
    }
    hitungUlangKonfirmasi();
}

function escapeHtml(str) {
    return str.replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;')
              .replace(/"/g, '&quot;')
              .replace(/'/g, '&#039;');
}

let barisBahanSedangDiedit = null;

function kategoriSatuanBahan(nama) {
    const n = (nama || '').toLowerCase();
    const cair = [
        'minyak', 'kecap', 'saus', 'santan', 'susu', 'air', 'kaldu',
        'kuah', 'cuka', 'madu', 'sirup', 'saus tiram', 'saus tomat',
        'saus sambal', 'mayones', 'margarin cair'
    ];
    return cair.some(k => n.includes(k)) ? 'ml' : 'g';
}

function faktorGramPerMl(nama) {
    const n = (nama || '').toLowerCase();
    if (n.includes('minyak')) return 0.92;
    if (n.includes('kecap')) return 1.15;
    if (n.includes('madu')) return 1.42;
    if (n.includes('santan') || n.includes('susu')) return 1.03;
    if (n.includes('saus') || n.includes('sirup')) return 1.10;
    return 1.00;
}

function konversiKeGram(jumlah, satuan, nama) {
    const faktor = {
        g: 1, butir: 55, sdm: 15, sdt: 5,
        potong: 50, buah: 50, siung: 5, lembar: 5,
        mangkuk: 200, tusuk: 20
    };
    if (satuan === 'ml') return jumlah * faktorGramPerMl(nama);
    return jumlah * (faktor[satuan] || 1);
}

function editBahan(button) {
    const row = button.closest('.item-bahan');
    if (!row) return;
    barisBahanSedangDiedit = row;
    const nama = row.querySelector('input[name="bahan_nama[]"]')?.value || '';
    const berat = parseFloat(row.querySelector('input[name="bahan_berat[]"]')?.value || '100');
    const satuan = kategoriSatuanBahan(nama);
    const jumlah = satuan === 'ml'
        ? berat / faktorGramPerMl(nama)
        : berat;

    document.getElementById('edit-nama-bahan').value = nama;
    document.getElementById('edit-jumlah-bahan').value = Number(jumlah.toFixed(1));
    document.getElementById('edit-satuan-bahan').value = satuan;
    const modal = document.getElementById('modal-edit-bahan');
    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
    document.getElementById('edit-nama-bahan').focus();
}

function tutupModalEdit() {
    const modal = document.getElementById('modal-edit-bahan');
    if (modal) {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
    }
    barisBahanSedangDiedit = null;
}

function simpanEditBahan() {
    if (!barisBahanSedangDiedit) return;
    const nama = document.getElementById('edit-nama-bahan').value.trim();
    const jumlah = parseFloat(document.getElementById('edit-jumlah-bahan').value);
    const satuan = document.getElementById('edit-satuan-bahan').value;
    if (!nama || !Number.isFinite(jumlah) || jumlah <= 0) {
        alert('Nama bahan dan jumlah harus diisi dengan benar.');
        return;
    }

    const gram = Math.max(1, Math.round(konversiKeGram(jumlah, satuan, nama)));
    const labelSatuan = {
        g: 'g', butir: 'butir', sdm: 'sdm', sdt: 'sdt', ml: 'ml',
        potong: 'potong', buah: 'buah', siung: 'siung', lembar: 'lembar',
        mangkuk: 'mangkuk', tusuk: 'tusuk'
    };
    const jumlahTampil = Number.isInteger(jumlah) ? jumlah : Number(jumlah.toFixed(1));
    const keterangan = satuan === 'ml'
        ? `${jumlahTampil} ml (perkiraan ${gram} g)`
        : `${jumlahTampil} ${labelSatuan[satuan]} (perkiraan ${gram} g)`;
    const row = barisBahanSedangDiedit;
    const namaLama = row.querySelector('input[name="bahan_nama[]"]').value;

    row.querySelector('.bahan-nama').textContent = nama;
    row.querySelector('.bahan-ket').textContent = keterangan;
    row.querySelector('input[name="bahan_nama[]"]').value = nama;
    row.querySelector('input[name="bahan_berat[]"]').value = gram;
    row.querySelector('input[name="bahan_ket[]"]').value = keterangan;
    tutupModalEdit();

    if (nama.toLowerCase() !== namaLama.toLowerCase()) {
        cariKkal100(nama).then(k => {
            row.dataset.kkal100 = (k !== null) ? k : '';
            hitungUlangKonfirmasi();
        });
    }
    hitungUlangKonfirmasi();
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#list-bahan-container .item-bahan').forEach(row => {
        const nama = row.querySelector('input[name="bahan_nama[]"]')?.value || '';
        const gramInput = row.querySelector('input[name="bahan_berat[]"]');
        const ketInput = row.querySelector('input[name="bahan_ket[]"]');
        const label = row.querySelector('.bahan-ket');
        const gram = parseFloat(gramInput?.value || '0');
        if (!gram || !ketInput || !label) return;

        let teks = label.textContent.trim();
        if (kategoriSatuanBahan(nama) === 'ml') {
            const ml = gram / faktorGramPerMl(nama);
            const mlTampil = Number(ml.toFixed(1));
            teks = `${mlTampil} ml (sekitar ${Math.round(gram)} g)`;
        } else if (!/\b\d+(\.\d+)?\s*(g|gram|ml)\b/i.test(teks)) {
            teks = `${teks} (sekitar ${Math.round(gram)} g)`;
        }
        ketInput.value = teks;
        label.textContent = teks;
    });

    hitungUlangKonfirmasi();
});

document.addEventListener('click', function(event) {
    const modal = document.getElementById('modal-edit-bahan');
    if (modal && event.target === modal) tutupModalEdit();
});
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') tutupModalEdit();
});
