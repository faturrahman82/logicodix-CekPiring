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

                    const seratVal = document.getElementById('serat-val');
                    const barSerat = document.getElementById('bar-serat');
                    if (seratVal) {
                        const seratPct = Math.round((res.total_gizi.serat_g / 30) * 100);
                        seratVal.innerHTML = `${res.total_gizi.serat_g} g <span id="serat-pct">(${seratPct}% target harian)</span>`;
                    }
                    if (barSerat) {
                        barSerat.style.width = Math.min(100, Math.round((res.total_gizi.serat_g / 30) * 100)) + '%';
                    }

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

                    const angkaJenuh = document.getElementById('angka-lemak_jenuh');
                    const descJenuh = document.getElementById('desc-lemak_jenuh');
                    const barJenuh = document.getElementById('bar-lemak_jenuh');
                    const badgeJenuh = document.getElementById('badge-lemak_jenuh');
                    const penyumbangJenuh = document.getElementById('penyumbang-lemak_jenuh');

                    if (res.risiko && res.risiko.lemak_jenuh) {
                        if (angkaJenuh) angkaJenuh.textContent = res.total_gizi.lemak_jenuh_g + ' g';
                        if (descJenuh) descJenuh.textContent = `${res.risiko.lemak_jenuh.persen}% ${res.risiko.lemak_jenuh.keterangan_profil}`;
                        if (barJenuh) {
                            barJenuh.style.width = Math.min(100, res.risiko.lemak_jenuh.persen) + '%';
                            barJenuh.className = `progress-fill fill-${res.risiko.lemak_jenuh.badge}`;
                        }
                        if (badgeJenuh) {
                            badgeJenuh.className = `badge-level level-${res.risiko.lemak_jenuh.badge}`;
                            badgeJenuh.textContent = `${res.risiko.lemak_jenuh.simbol} ${res.risiko.lemak_jenuh.level}`;
                        }
                        if (penyumbangJenuh && res.risiko.lemak_jenuh.penyumbang) {
                            penyumbangJenuh.textContent = res.risiko.lemak_jenuh.penyumbang;
                            penyumbangJenuh.style.display = 'block';
                        }
                    }

                    const angkaPurin = document.getElementById('angka-purin');
                    const descPurin = document.getElementById('desc-purin');
                    const barPurin = document.getElementById('bar-purin');
                    const badgePurin = document.getElementById('badge-purin');
                    const penyumbangPurin = document.getElementById('penyumbang-purin');

                    if (res.risiko && res.risiko.purin) {
                        if (angkaPurin) angkaPurin.textContent = res.total_gizi.purin_mg + ' mg';
                        if (descPurin) descPurin.textContent = `${res.risiko.purin.persen}% ${res.risiko.purin.keterangan_profil}`;
                        if (barPurin) {
                            barPurin.style.width = Math.min(100, res.risiko.purin.persen) + '%';
                            barPurin.className = `progress-fill fill-${res.risiko.purin.badge}`;
                        }
                        if (badgePurin) {
                            badgePurin.className = `badge-level level-${res.risiko.purin.badge}`;
                            badgePurin.textContent = `${res.risiko.purin.simbol} ${res.risiko.purin.level}`;
                        }
                        if (penyumbangPurin && res.risiko.purin.penyumbang) {
                            penyumbangPurin.textContent = res.risiko.purin.penyumbang;
                            penyumbangPurin.style.display = 'block';
                        }
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

                    // Update modul analisis klinis dinamis
                    if (res.clinical_advice) {
                        const ca = res.clinical_advice;
                        const sumText = document.getElementById('analisis-summary-text');
                        if (sumText) sumText.textContent = ca.ringkasan_medis;

                        const cautionTitle = document.getElementById('caution-title');
                        const cautionBody = document.getElementById('caution-body');
                        if (cautionTitle) cautionTitle.textContent = ca.cautions.judul;
                        if (cautionBody) cautionBody.innerHTML = `<p>${ca.cautions.teks}</p>`;

                        const swapsTitle = document.getElementById('swaps-title');
                        const swapsList = document.getElementById('swaps-list');
                        if (swapsTitle) swapsTitle.textContent = ca.swaps.judul;
                        if (swapsList && Array.isArray(ca.swaps.tips)) {
                            swapsList.innerHTML = ca.swaps.tips.map(t => `<li>${t}</li>`).join('');
                        }

                        const plateTitle = document.getElementById('plate-title');
                        const plateText = document.getElementById('plate-text');
                        const plateHighlight = document.getElementById('plate-highlight');
                        if (plateTitle) plateTitle.textContent = ca.plate.judul;
                        if (plateText) plateText.innerHTML = ca.plate.teks;
                        if (plateHighlight) {
                            if (ca.plate.catatan) {
                                plateHighlight.innerHTML = ca.plate.catatan;
                                plateHighlight.style.display = 'block';
                            } else {
                                plateHighlight.style.display = 'none';
                            }
                        }
                    }
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
        <div class="bahan-actions">
            <button type="button" class="btn-edit-item" onclick="editBahan(this)">Edit</button>
            <button type="button" class="btn-delete-item" onclick="hapusBahan(this)">Hapus</button>
        </div>
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


// Modal koreksi bahan. Berat gram tetap disimpan untuk kompatibilitas kalkulator gizi.
// Bahan cair ditampilkan dalam ml; bahan padat tetap menggunakan gram/butir/dll.
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
    return 1.00; // air, kuah, kaldu, cuka, dan cairan lain: estimasi mendekati air
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

    row.querySelector('.bahan-nama').textContent = nama;
    row.querySelector('.bahan-ket').textContent = keterangan;
    row.querySelector('input[name="bahan_nama[]"]').value = nama;
    row.querySelector('input[name="bahan_berat[]"]').value = gram;
    row.querySelector('input[name="bahan_ket[]"]').value = keterangan;
    tutupModalEdit();
}

// Pastikan setiap bahan menampilkan takaran ramah beserta takaran gram/ml
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
});

document.addEventListener('click', function(event) {
    const modal = document.getElementById('modal-edit-bahan');
    if (modal && event.target === modal) tutupModalEdit();
});
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') tutupModalEdit();
});
