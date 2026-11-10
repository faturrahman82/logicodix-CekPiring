# Data gizi CekPiring

## Isi folder

| File | Keterangan |
|---|---|
| `nutrition_bersih.csv` | Versi yang dipakai tim. 1.346 baris (pangan Indonesia, garam dapur, kecap manis). |
| `periksa_ke_sumber.csv` | 28 baris yang tidak ditemukan di TKPI 2017 dan angkanya masih meragukan. |
| `akg_2019.csv` | Angka Kecukupan Gizi Permenkes 28/2019 (energi dan protein per umur dan jenis kelamin), dipakai untuk target kalori. |
| `pemetaan_usda.csv` | Daftar 27 bahan kita yang dipetakan ke baris USDA (`kode_usda`), untuk penelusuran. |
| `raw/nutrition_asli.csv` | File asli (1.346 baris), tidak boleh diubah. Dipakai sebagai pembanding. |

## Sumber data

**Tabel Komposisi Pangan Indonesia (TKPI) 2017**, Direktorat Gizi Masyarakat, Kementerian Kesehatan RI, Jakarta 2018, ISBN 978-602-416-407-2, 135 halaman. Tersedia di [Repository Kemenkes](https://repository.kemkes.go.id/book/777).

Dasar penetapan: 72,2% baris (970 dari 1.344) di file ini sama persis, pada keempat nilai gizinya, dengan satu baris TKPI 2017. Nilai gizi diperlakukan per 100 gram, sesuai judul kolom TKPI ("komposisi zat gizi makanan per 100 gram"). Catat sumber ini di README utama sebagai atribusi. Izin pakai tidak tertulis di file asli. TKPI adalah publikasi pemerintah, tetapi tim perlu memastikannya sendiri.

## Kolom `nutrition_bersih.csv`

| Kolom | Arti |
|---|---|
| `id` | Nomor urut baru (urut abjad nama). |
| `id_asli` | Nomor pada file asli, untuk penelusuran. |
| `nama` | Nama pangan. Spasi berlebih sudah dirapikan. |
| `nama_kunci` | Nama huruf kecil untuk pencocokan nama bahan. Unik. |
| `energi_kkal`, `protein_g`, `lemak_g`, `karbohidrat_g` | Nilai gizi per 100 gram. |
| `serat_g`, `natrium_mg` | Serat (g) dan natrium (mg) per 100 gram, diambil dari TKPI 2017. **Kosong berarti datanya tidak tersedia, bukan nol.** Lihat bagian "Natrium dan serat". |
| `gula_g`, `lemak_jenuh_g`, `kolesterol_mg` | Gula total (g), lemak jenuh (g), kolesterol (mg) per 100 gram, dari USDA. Hanya terisi untuk 27 bahan yang dipetakan (lihat `pemetaan_usda.csv`) dan garam. Kosong berarti tidak tersedia. |
| `sumber_natrium` | Asal angka natrium: `tkpi`, `usda`, atau `label_off` (kecap manis). |
| `purin_mg`, `purin_kategori`, `purin_dasar` | Purin total (mg per 100 g), kategorinya, dan dasar pemetaannya. Dari Kaneko 2014, 83 baris. Lihat bagian "Purin". |
| `kode_tkpi` | Kode baris TKPI 2017 yang dipakai untuk mengisi serat dan natrium (mis. `NP005`). Kosong bila tidak ada padanan. |
| `kode_usda` | Nomor `fdc_id` baris USDA SR Legacy yang dipakai. Kosong bila tidak dipetakan. |
| `dasar_cocok` | Dasar pencocokan ke TKPI: `nilai_sama`, `koreksi_tkpi`, atau `nama_sama`. |
| `catatan_periksa` | Hasil pencocokan ke TKPI untuk baris yang awalnya meragukan. Kosong berarti tidak ada masalah yang terdeteksi. |
| `koreksi` | Penjelasan bila nama atau nilai baris itu diubah dari file asli, lengkap dengan kode dan halaman TKPI. |

## Yang diubah dari file asli

1. Kolom `image` dibuang. Nama kolom diganti ke bahasa Indonesia dengan satuan. Spasi berlebih pada 83 nama dihapus. Diurutkan menurut abjad.
2. **Dua baris duplikat dibuang** (nama sama, nilai hampir sama). Dipertahankan baris yang energinya paling cocok dengan makronya.

   | Dibuang (id_asli) | Dipertahankan (id_asli) |
   |---|---|
   | Beras Parboiled: 364 kkal, protein 6,8, lemak 0,6, karbohidrat 80,1 (129) | Beras parboiled (130) |
   | Terasi Merah: 174 kkal, protein 30, lemak 3,5, karbohidrat 3,5 (1275) | Terasi merah (1276) |

3. **"Bika ambon" dibedakan** menjadi `(versi A)` dan `(versi B)`. Nilainya jauh berbeda dan belum diketahui mana yang benar.
4. **17 nilai dikoreksi** (2 berdasarkan bukti internal, 15 berdasarkan TKPI 2017):
   - Pilus: karbohidrat 647 menjadi 64,7 (647 g per 100 g mustahil, cocok dengan energi).
   - Santan (kelapa saja): karbohidrat 55,6 menjadi 5,6 (sama dengan entri "Santan murni").
   - 15 baris disamakan dengan TKPI 2017: Bawang Merah, Boros Kunci, Coklat Susu batang, Daun Labu Siam, Daun Semanggi, Dendeng Daging Sapi, Ikan Gabus segar, Jeruk Nipis, Kalakai, Kecap, Kerang, Kue Koya, Rebon kering, Srikaya, dan Tebu Terubuk. Nilai lama dan baru tertulis di kolom `koreksi`.

Nilai gizi lainnya tidak diubah.

## Arti `catatan_periksa`

Awalnya ada 66 baris yang meragukan (energi tidak cocok dengan hitungan protein x 4 + karbohidrat x 4 + lemak x 9, atau jumlah makro lebih dari 100 g). Setelah dicocokkan dengan TKPI 2017:

- 15 baris: ternyata salah ketik di file ini, sudah dikoreksi (lihat `koreksi`).
- 23 baris `sesuai_tkpi_2017`: nilainya sama persis dengan TKPI, jadi **selisihnya ada di sumber resminya**, bukan kesalahan kita. Tidak diubah. Contoh: Mi kering tertulis karbohidrat 6,3 g di TKPI, dan tiga entri dendeng mujair punya jumlah makro lebih dari 100 g di TKPI.
- 28 baris `tidak_ditemukan_di_tkpi_2017`: tidak ada padanannya di TKPI 2017 (mis. Nasi Goreng, Pisang Goreng, Ubi Jalar Goreng/Rebus, Kerupuk Udang, Teh). Nilainya belum bisa diverifikasi, tidak diubah, dan tercatat di `periksa_ke_sumber.csv`.

Sekitar 28% baris di file ini (374 baris) tidak sama persis dengan TKPI 2017. Itu belum diperiksa satu per satu, karena tidak semuanya ditandai meragukan.

## Natrium dan serat

Kolom `natrium_mg` dan `serat_g` diambil dari tabel TKPI 2017 (kolom "NATRIUM" dan "SERAT"). Cakupan: **natrium terisi untuk 822 dari 1.344 baris (61%)** dan serat untuk 894 baris (67%). Sisanya kosong karena tidak ada padanan di TKPI atau TKPI sendiri tidak memuat angkanya (ditulis "-").

Dasar pencocokan, dari yang paling kuat:

| `dasar_cocok` | Jumlah | Arti |
|---|---|---|
| `nilai_sama` | 958 | Keempat nilai gizi (energi, protein, lemak, karbohidrat) sama persis dengan satu baris TKPI. |
| `koreksi_tkpi` | 15 | Baris ini dikoreksi ke nilai TKPI (lihat kolom `koreksi`). |
| `nama_sama` | 96 | Nilai gizinya berbeda dari TKPI, tetapi nama pangannya sama. Dasarnya lebih lemah: bentuk pangannya bisa sedikit berbeda. Dipakai hanya bila energinya dalam selisih 25% dari TKPI. |

Cara aman memakainya:
- Anggap **kosong sebagai "data tidak tersedia"**, jangan dihitung sebagai 0. Aplikasi sebaiknya menampilkan "data natrium belum tersedia" untuk bahan itu.
- 12 pasangan yang angkanya kebetulan sama tetapi jelas pangan berbeda (mis. Telur ayam kampung dengan Telur bebek asin) sudah dikeluarkan.
- Bahan yang sering dipakai tetapi masih kosong: Telur Ayam, Tempe Goreng, Kluwek, dan Rawon masakan (baris TKPI-nya tidak memuat natrium). Tidak ada "garam" di data ini.

## Gula, lemak jenuh, dan kolesterol (USDA)

Sumber: **USDA FoodData Central, dataset SR Legacy (April 2018)**, U.S. Department of Agriculture, Agricultural Research Service. Data milik pemerintah AS dan berstatus domain publik. Nilainya per 100 gram.

Bahan kita dipetakan satu per satu ke padanan USDA secara manual (`pemetaan_usda.csv`), karena nama Indonesia tidak bisa dicocokkan otomatis. Pasangan hanya diterima bila energi, protein, dan lemaknya cukup dekat (supaya bentuk pangan tidak berbeda). Hasilnya **27 bahan**: telur ayam (utuh, kuning, putih), tahu, tempe kedelai murni mentah, santan (dua entri), enam jenis minyak, dua jenis gula, bawang merah dan bombay, cabai merah, mentega, susu kental manis, pisang ambon, kentang, nanas, jeruk manis, kacang tanah, susu kedelai, dan wortel.

Aturan yang dipakai:
- Angka TKPI yang sudah ada **tidak ditimpa**. USDA hanya mengisi kolom gula, lemak jenuh, dan kolesterol, serta natrium yang masih kosong (6 bahan, ditandai `sumber_natrium = usda`).
- Satu baris ditambahkan: **Garam dapur** (id 1345, USDA "Salt, table", natrium 38.758 mg per 100 g). Garam tidak ada di TKPI. Baris ini diletakkan di akhir agar nomor `id` lainnya tetap.
- Bahan yang profil gizinya tidak cocok dengan padanan USDA dilewati (mis. Bawang Putih, Santan dengan air). Nasi, Jahe, Bayam, dan Tomat juga dilewati karena kadar airnya berbeda.

## Sumber lain yang sudah diperiksa dan tidak dipakai

| Sumber | Alasan |
|---|---|
| TKPI 2020 (revisi cetakan 1, Kemenkes) | PDF berupa gambar beresolusi rendah dengan watermark besar dan sel tersorot yang menutupi angka. Tidak bisa diekstrak dengan andal tanpa OCR. Isi utamanya sama dengan TKPI 2017. PDF disimpan di `sumber_data/TKPI_2020`. |
| Panganku.org | Situs lama (2018) dengan pencarian lewat antarmuka web, tanpa unduhan massal atau lisensi data yang jelas. |
| Open Food Facts | Data produk kemasan berlisensi ODbL (wajib atribusi dan berbagi dengan lisensi sama bila data turunan dipublikasikan). Cocok untuk satu dua produk tertentu, tidak untuk dimasukkan massal ke repo publik. |

## Purin (asam urat)

Sumber: Kaneko K. dkk., "Total purine and purine base content of common foodstuffs for facilitating nutritional therapy for gout and hyperuricemia", *Biological and Pharmaceutical Bulletin* 37(5):709-721, 2014 (270 pangan, HPLC, mg per 100 g). Kolom `purin_mg` = purin total, `purin_kategori` dan `purin_dasar` menjelaskan asalnya.

- Pangan di jurnal itu pangan Jepang. Hanya **83 baris** yang dipetakan. `purin_dasar` berisi `tepat` (jenis sama, mis. hati sapi) atau `padanan` (jenis terdekat atau rata-rata beberapa potongan, mis. Daging Sapi = rata-rata 9 potongan), lalu nama item di jurnal.
- Kategori (pilihan tim, mengikuti pengelompokan jurnal): `rendah` kurang dari 50, `sedang` 50 sampai 199, `tinggi` 200 ke atas (mg per 100 g). Hasil: 26 rendah, 37 sedang, 20 tinggi.
- Tidak ada di jurnal, jadi **kosong (data belum tersedia)**: tempe, oncom, melinjo dan emping, kangkung, daun singkong, ikan segar lokal selain yang tercantum, dan semua masakan jadi.
- Cara menampilkan: kosong berarti "data purin belum tersedia", jangan dianggap 0.
- Tabel lengkap hasil ekstrak ada di `D:\FolderSelainKuliah\Lomba\sumber_data\Kaneko_purin` (di luar repo).

## Kecap manis

Baris baru id 1346 "Kecap manis (rata-rata label kemasan)": median dari 8 label kecap manis di Open Food Facts (lisensi ODbL, wajib atribusi): energi 317 kkal, karbohidrat 76,5 g, **gula 62,9 g**, natrium 2.240 mg per 100 g. Kisaran gula antar merek 45 sampai 79 g, jadi angka ini perkiraan, bukan satu merek. Protein dan lemak tidak ada di label. Baris "Kecap" lama (TKPI) tetap ada dan merupakan kecap umum, bukan kecap manis.

## Air dan kol (USDA)

Dua baris ditambahkan karena sering muncul dari pengenalan foto tetapi tidak ada di TKPI 2017:

| id | Nama | Sumber | Catatan |
|---|---|---|---|
| 1351 | Air | USDA SR Legacy 173647 (*Beverages, water, tap, drinking*) | 0 kkal, natrium 4 mg per 100 g. Dipakai untuk kuah bening. |
| 1352 | Kol (kubis) segar | USDA SR Legacy 169975 (*Cabbage, raw*) | TKPI 2017 hanya memuat daun kubis (DR044). Purin 3,2 mg dari Kaneko 2014 (*Cabbage*). |

Alias `kol` di `ingredient_aliases.csv` sebelumnya menunjuk ke `kol krop segar` yang tidak ada di data (kode DR113 di TKPI adalah kembang kol). Sekarang menunjuk ke baris 1352.

## Penambahan USDA tahap 2 (10 November 2026)

Untuk bahan yang paling sering dikenali dari foto, kolom yang masih kosong (gula, lemak jenuh, kolesterol, natrium, serat) diisi dari USDA FoodData Central SR Legacy. **Angka TKPI yang sudah ada tidak ditimpa.**

- 32 bahan: bihun, tepung terigu, makaroni, daging sapi, daging kambing, ayam, hati sapi, udang, cumi-cumi, bandeng, sawi, bayam, kangkung, buncis, kacang panjang, labu siam, tomat, ketimun, selada, taoge, jamur tiram, daun bawang, seledri, jeruk nipis, margarin, keju, susu sapi, gula aren, kacang tanah goreng, kacang merah, dan sebagian lagi.
- `pemetaan_usda.csv` mencatat kode USDA tiap bahan. Kolom `dasar`: `tepat` (jenis sama) atau `padanan` (jenis terdekat atau potongan dengan profil lemak paling mirip, mis. ayam dipetakan ke leher dengan kulit karena kadar lemaknya paling dekat dengan baris Ayam TKPI).
- Natrium USDA tidak dipakai untuk kacang tanah goreng karena biasanya diberi garam.
- Purin ditambah dari Kaneko 2014 untuk bawang merah, tomat, jagung muda, taoge, jahe, dan kacang tanah goreng.
- Ditolak karena profil gizinya terlalu beda dengan baris kita (kadar air atau bentuk olahan): nasi masak, mi basah, mi kering, ikan tongkol, ikan mas, lele goreng, tempe goreng, bakso, jagung muda, nangka muda, bawang putih, jahe, santan, daun singkong.

Cakupan 71 bahan yang diusulkan ke Gemini setelah tahap ini (kosong): gula 25, lemak jenuh 20, kolesterol 19, natrium 9, purin 35. Bahan yang kosong tetap ditandai "data belum tersedia" di aplikasi.

## Bumbu dan saus (USDA, 10 November 2026)

Dari foto masakan seperti tinoransak, Gemini sering menyebut bumbu yang tidak ada di TKPI 2017. Enam baris ditambahkan dari USDA SR Legacy (id 1353-1358):

| id | Nama | Sumber USDA | Catatan |
|---|---|---|---|
| 1353 | Serai | 168573 Lemon grass, raw | |
| 1354 | Asam jawa | 167763 Tamarinds, raw | |
| 1355 | Saus tiram | 174529 | Purin 134,4 mg (Kaneko 2014, Oyster sauce) |
| 1356 | Kecap ikan | 174531 | Purin 93,1 mg (Kaneko 2014, Nampla). Natrium 7.851 mg per 100 g |
| 1357 | Kaldu bubuk ayam | 171562 | Natrium 23.875 mg per 100 g, pakai takaran 1-3 g |
| 1358 | Jintan | 170923 | |

Alias ditambah untuk serai/sereh, lengkuas/laos (menuju `boros laja (lengkuas)`), ketumbar, asam jawa, saus tiram, kecap ikan, kaldu/penyedap, dan jintan.

**Tanpa data resmi, sengaja dikosongkan:** daun jeruk, daun pandan, kencur. Ketiganya tidak ada di TKPI 2017 maupun USDA SR Legacy. Gemini diminta tidak menuliskannya sebagai bahan (takarannya hanya 1-3 g dan tidak dimakan). Bila tetap muncul, aplikasi menandainya "data belum tersedia".

## AKG 2019 (`akg_2019.csv`)

Tabel 1 Angka Kecukupan Gizi, Permenkes No. 28 Tahun 2019 (lampiran halaman 7-8), untuk umur 10 tahun ke atas: berat dan tinggi acuan, energi, protein, lemak, karbohidrat, dan serat per orang per hari. Dibaca manual dari PDF resmi (peraturan.bpk.go.id, halaman tabelnya berupa gambar). Kelompok "80+ tahun" ditulis `umur_min=81`, `umur_maks=200`. Aplikasi memakai energinya sebagai target kalori utama (`TargetCalculator`).

## Yang belum ada

- **Gula dan lemak jenuh untuk masakan lokal** (rawon, soto, rendang, nasi goreng) tidak tersedia dari sumber terbuka yang sudah diperiksa (TKPI, USDA, Panganku, Kaggle, Open Food Facts). Basis data Singapura (SG FoodID) punya hidangan serupa tetapi perlu unduh manual dan memeriksa syarat pakainya, belum dilakukan. Tampilkan "data belum tersedia" untuk bahan itu.
- Natrium masih kosong untuk sekitar 500 bahan. Pemetaan USDA bisa diperluas bila perlu.

## Data yang dikeluarkan dari proyek

Pada 8 November 2026, data berikut dinilai tidak dipakai fitur MVP dan dipindahkan ke `D:\FolderSelainKuliah\Lomba\data_tidak_dipakai` (di luar repo): dataset pangan Barat `FOOD-DATA` beserta zip dan gambar analisisnya (nilai tidak per 100 gram), data pengukuran balita (sensitif), konsumsi pangan per provinsi, tabel konsumsi BPS, dan prevalensi stunting provinsi.

PDF TKPI 2017 dan hasil ekstrak tabelnya disimpan di `D:\FolderSelainKuliah\Lomba\sumber_data\TKPI_2017` (di luar repo).
