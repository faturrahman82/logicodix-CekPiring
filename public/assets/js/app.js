document.addEventListener('DOMContentLoaded', function () {
    const inputUmur = document.getElementById('input-umur');
    const inputTinggi = document.getElementById('input-tinggi');
    const inputBerat = document.getElementById('input-berat');
    const targetAngka = document.getElementById('target-angka');
    const targetDeskripsi = document.getElementById('target-deskripsi');

    if (inputUmur && inputTinggi && inputBerat && targetAngka) {
        function hitungKaloriClient() {
            const umur = parseFloat(inputUmur.value);
            const tinggi = parseFloat(inputTinggi.value);
            const berat = parseFloat(inputBerat.value);

            const genderEl = document.querySelector('input[name="gender"]:checked');
            const tujuanEl = document.querySelector('input[name="tujuan"]:checked');

            const gender = genderEl ? genderEl.value : 'perempuan';
            const tujuan = tujuanEl ? tujuanEl.value : 'cutting';
            const tujuanLabel = tujuan.charAt(0).toUpperCase() + tujuan.slice(1);

            let tdee = 2000;
            let isCustom = false;

            if (!isNaN(umur) && !isNaN(tinggi) && !isNaN(berat) && umur > 0 && tinggi > 0 && berat > 0) {
                isCustom = true;
                let bmr = 0;
                if (gender === 'laki-laki') {
                    bmr = (10 * berat) + (6.25 * tinggi) - (5 * umur) + 5;
                } else {
                    bmr = (10 * berat) + (6.25 * tinggi) - (5 * umur) - 161;
                }
                tdee = bmr * 1.375;
            }

            let kaloriFinal = tdee;
            if (tujuan === 'cutting') {
                kaloriFinal = tdee * 0.85;
            } else if (tujuan === 'bulking') {
                kaloriFinal = tdee * 1.15;
            }

            targetAngka.textContent = Math.round(kaloriFinal).toLocaleString('id-ID');

            if (targetDeskripsi) {
                if (isCustom) {
                    targetDeskripsi.textContent = `Rumus Mifflin-St Jeor, aktivitas ringan, disesuaikan untuk ${tujuanLabel}. Estimasi edukatif, bukan resep diet.`;
                } else {
                    targetDeskripsi.textContent = `Target umum edukatif untuk ${tujuanLabel}. Boleh isi data fisik untuk target lebih akurat.`;
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
            if (namaValid) {
                btnSubmitMakanan.removeAttribute('disabled');
                if (submitHint) submitHint.style.display = 'none';
            } else {
                btnSubmitMakanan.setAttribute('disabled', 'disabled');
                if (submitHint) submitHint.style.display = 'inline';
            }
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
                        if (dropzonePreview) dropzonePreview.classList.add('active');
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
        btnPorsiMinus.addEventListener('click', function () {
            let current = parseFloat(inputPorsiVal.value) || 1.0;
            if (current > 0.5) {
                current = Math.max(0.5, Math.round((current - 0.5) * 10) / 10);
                inputPorsiVal.value = current;
                porsiDisplay.textContent = current.toString().replace('.', ',');
            }
        });

        btnPorsiPlus.addEventListener('click', function () {
            let current = parseFloat(inputPorsiVal.value) || 1.0;
            if (current < 3.0) {
                current = Math.min(3.0, Math.round((current + 0.5) * 10) / 10);
                inputPorsiVal.value = current;
                porsiDisplay.textContent = current.toString().replace('.', ',');
            }
        });
    }

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
                                    tambahBahanItem(item.nama, 50, '50 g, bahan tambahan');
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
                    tambahBahanItem(nama, 50, '50 g, bahan tambahan');
                    inputTambahBahan.value = '';
                    autocompleteList.style.display = 'none';
                }
            });
        }
    }

    const btnHasilMinus = document.getElementById('btn-hasil-minus');
    const btnHasilPlus = document.getElementById('btn-hasil-plus');
    const porsiHasilDisplay = document.getElementById('porsi-hasil-display');

    if (btnHasilMinus && btnHasilPlus && porsiHasilDisplay) {
        let currentPorsi = parseFloat(porsiHasilDisplay.textContent.replace(',', '.')) || 1.0;

        function updatePorsiServer(newPorsi) {
            newPorsi = Math.round(newPorsi * 10) / 10;
            if (newPorsi < 0.3 || newPorsi > 3.0) return;

            currentPorsi = newPorsi;
            porsiHasilDisplay.textContent = currentPorsi.toString().replace('.', ',');

            const formData = new FormData();
            formData.append('porsi', currentPorsi);

            fetch('api/recalculate', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.sukses) {
                    const labelPorsiHeader = document.getElementById('label-porsi-header');
                    const labelBeratHeader = document.getElementById('label-berat-header');
                    if (labelPorsiHeader) labelPorsiHeader.textContent = currentPorsi.toString().replace('.', ',');
                    if (labelBeratHeader) labelBeratHeader.textContent = res.total_gizi.berat_total_g;

                    const energiAngka = document.getElementById('energi-angka');
                    const persenKaloriVal = document.getElementById('persen-kalori-val');
                    const sisaKaloriVal = document.getElementById('sisa-kalori-val');
                    const circleEnergyFill = document.getElementById('circle-energy-fill');

                    if (energiAngka) energiAngka.textContent = res.total_gizi.energi_kkal;
                    if (persenKaloriVal) persenKaloriVal.textContent = res.persen_kalori + '%';
                    if (sisaKaloriVal) sisaKaloriVal.textContent = res.sisa_kalori.toLocaleString('id-ID');

                    if (circleEnergyFill) {
                        const radius = 76;
                        const circumference = 2 * Math.PI * radius;
                        const offset = circumference - (circumference * Math.min(1.0, res.persen_kalori / 100));
                        circleEnergyFill.style.strokeDashoffset = offset;
                    }

                    const karboVal = document.getElementById('karbo-val');
                    const barKarbo = document.getElementById('bar-karbo');
                    if (karboVal) karboVal.innerHTML = `${res.total_gizi.karbohidrat_g} g, <span>${res.total_gizi.makro_persen.karbohidrat}%</span> energi`;
                    if (barKarbo) barKarbo.style.width = res.total_gizi.makro_persen.karbohidrat + '%';

                    const proteinVal = document.getElementById('protein-val');
                    const barProtein = document.getElementById('bar-protein');
                    if (proteinVal) proteinVal.innerHTML = `${res.total_gizi.protein_g} g, <span>${res.total_gizi.makro_persen.protein}%</span> energi`;
                    if (barProtein) barProtein.style.width = res.total_gizi.makro_persen.protein + '%';

                    const lemakVal = document.getElementById('lemak-val');
                    const barLemak = document.getElementById('bar-lemak');
                    if (lemakVal) lemakVal.innerHTML = `${res.total_gizi.lemak_g} g, <span>${res.total_gizi.makro_persen.lemak}%</span> energi`;
                    if (barLemak) barLemak.style.width = res.total_gizi.makro_persen.lemak + '%';

                    const angkaNatrium = document.getElementById('angka-natrium');
                    const descNatrium = document.getElementById('desc-natrium');
                    const barNatrium = document.getElementById('bar-natrium');
                    const badgeNatrium = document.getElementById('badge-natrium');

                    if (angkaNatrium) angkaNatrium.textContent = res.total_gizi.natrium_mg + ' mg';
                    if (descNatrium) descNatrium.textContent = `${res.risiko.natrium.persen}% dari batas harian 2.000 mg`;
                    if (barNatrium) {
                        barNatrium.style.width = Math.min(100, res.risiko.natrium.persen) + '%';
                        barNatrium.className = `progress-fill fill-${res.risiko.natrium.badge}`;
                    }
                    if (badgeNatrium) {
                        badgeNatrium.className = `badge-level level-${res.risiko.natrium.badge}`;
                        badgeNatrium.textContent = `${res.risiko.natrium.simbol} ${res.risiko.natrium.level}`;
                    }

                    const angkaGula = document.getElementById('angka-gula');
                    const descGula = document.getElementById('desc-gula');
                    const barGula = document.getElementById('bar-gula');
                    const badgeGula = document.getElementById('badge-gula');

                    if (angkaGula) angkaGula.textContent = res.total_gizi.gula_g + ' g';
                    if (descGula) descGula.textContent = `${res.risiko.gula.persen}% ${res.risiko.gula.keterangan_profil}`;
                    if (barGula) {
                        barGula.style.width = Math.min(100, res.risiko.gula.persen) + '%';
                        barGula.className = `progress-fill fill-${res.risiko.gula.badge}`;
                    }
                    if (badgeGula) {
                        badgeGula.className = `badge-level level-${res.risiko.gula.badge}`;
                        badgeGula.textContent = `${res.risiko.gula.simbol} ${res.risiko.gula.level}`;
                    }

                    const verdictCard = document.getElementById('verdict-card');
                    const verdictBadge = document.getElementById('verdict-badge');
                    const verdictTitle = document.getElementById('verdict-title');
                    const verdictDesc = document.getElementById('verdict-desc');

                    if (verdictCard) verdictCard.className = `card card-verdict verdict-${res.verdict.badge_class}`;
                    if (verdictBadge) {
                        verdictBadge.className = `verdict-badge badge-${res.verdict.badge_class}`;
                        verdictBadge.textContent = res.verdict.badge_text;
                    }
                    if (verdictTitle) verdictTitle.textContent = res.verdict.judul;
                    if (verdictDesc) verdictDesc.textContent = res.verdict.deskripsi;
                }
            })
            .catch(err => console.error('Gagal memperbarui porsi:', err));
        }

        btnHasilMinus.addEventListener('click', () => updatePorsiServer(currentPorsi - 0.1));
        btnHasilPlus.addEventListener('click', () => updatePorsiServer(currentPorsi + 0.1));
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
        }, 250);
    }
}

function tambahBahanItem(nama, berat, ket) {
    const container = document.getElementById('list-bahan-container');
    if (!container) return;

    const div = document.createElement('div');
    div.className = 'item-bahan';
    div.innerHTML = `
        <div class="badge-check">✓</div>
        <div class="bahan-info">
            <p class="bahan-nama">${escapeHtml(nama)}</p>
            <p class="bahan-ket">${escapeHtml(ket)}</p>
        </div>
        <input type="hidden" name="bahan_nama[]" value="${escapeHtml(nama)}">
        <input type="hidden" name="bahan_berat[]" value="${berat}">
        <input type="hidden" name="bahan_ket[]" value="${escapeHtml(ket)}">
        <button type="button" class="btn-delete-item" onclick="hapusBahan(this)">Hapus</button>
    `;
    container.appendChild(div);
}

function escapeHtml(str) {
    return str.replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;')
              .replace(/"/g, '&quot;')
              .replace(/'/g, '&#039;');
}
