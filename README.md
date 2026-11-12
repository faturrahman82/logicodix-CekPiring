# CekPiring

Aplikasi web untuk **LOGICODIX 2026**, subtema **Inclusive Health & Well-being**, kategori **Web**.

CekPiring membantu orang yang harus menjaga makan (diabetes, hipertensi, kolesterol tinggi, asam urat) atau yang punya target kebugaran (cutting, bulking, seimbang) untuk menjawab satu pertanyaan sebelum makan: **"Apakah piring ini aman untukku, dan apa yang bisa kuubah?"**

Pengguna memotret makanan dan mengetik namanya. Aplikasi menebak bahan di piring, pengguna memastikannya, lalu CekPiring menghitung gizi dan risikonya sesuai profil kesehatan pengguna, memberi saran porsi, dan menunjukkan perubahan konkret beserta efeknya pada angka.

> Estimasi edukatif. CekPiring bukan alat diagnosis dan bukan pengganti saran dokter atau ahli gizi.

## Daftar isi

1. [Fitur utama](#fitur-utama)
2. [Alur penggunaan](#alur-penggunaan)
3. [Langkah 1: Profil](#langkah-1-profil)
4. [Langkah 2: Makanan](#langkah-2-makanan)
5. [Langkah 3: Cek bahan](#langkah-3-cek-bahan)
6. [Langkah 4: Hasil](#langkah-4-hasil)
7. [Cara perhitungan](#cara-perhitungan)
8. [Peran AI dan kode](#peran-ai-dan-kode)
9. [Data dan sumber](#data-dan-sumber)
10. [Teknologi dan struktur proyek](#teknologi-dan-struktur-proyek)
11. [Cara menjalankan](#cara-menjalankan)
12. [Keamanan dan privasi](#keamanan-dan-privasi)
13. [Keterbatasan yang diketahui](#keterbatasan-yang-diketahui)
14. [Kontribusi tim](#kontribusi-tim)

## Fitur utama

- **Pengenalan makanan dari foto dan nama** memakai AI (Google Gemini): hidangan, bahan, perkiraan gram, dan ukuran piring.
- **Konfirmasi bahan oleh pengguna**: bahan bisa diedit, dihapus, atau ditambah sebelum dihitung. Bila nama dan foto tidak cocok, pengguna memilih salah satu.
- **Perhitungan gizi yang transparan**: semua angka dihitung kode dari data resmi, bukan ditulis AI.
- **Penilaian sesuai kondisi kesehatan**: batas harian berbeda untuk diabetes, hipertensi, kolesterol tinggi, dan asam urat.
- **Porsi disarankan**: "makan sekitar 0,5 piring" lengkap dengan perbandingan angka.
- **Perbaiki piringmu**: saran mengurangi atau mengganti bahan, dihitung ulang sehingga efeknya terlihat (misalnya "Gula 128% → 48% batas harian").
- **Data yang jujur**: bahan tanpa data ditandai "data belum tersedia" dan tidak dihitung sebagai nol.
- **Loader dengan langkah nyata**: tiga langkah pemeriksaan dicentang hanya setelah server benar-benar menyelesaikannya.

## Alur penggunaan

```
Profil  →  Makanan (foto + nama)  →  Cek bahan  →  Hasil
 (1)              (2)                  (3)          (4)
```

Indikator langkah (1 sampai 4) tampil di bagian atas setiap halaman. Tombol "Analisis makanan lain" di hasil mengulang dari profil.

## Langkah 1: Profil

Halaman pertama menanyakan siapa penggunanya, supaya target kalori dan batas gizi sesuai orangnya. Data hanya disimpan di sesi peramban pengguna (sesi PHP di server), tidak ada akun, kata sandi, atau basis data pengguna.

### Tujuan utama

Pengguna memilih satu dari tiga tujuan. Tujuan hanya mengubah **target kalori harian**:

| Tujuan | Arti | Pengaruh pada target kalori |
|---|---|---|
| **Cutting** | Menurunkan lemak | dikurangi 15% |
| **Seimbang** | Menjaga berat badan | sama dengan kebutuhan harian |
| **Bulking** | Menambah massa otot | ditambah 15% |

Angka 15% adalah aturan tim, belum bersumber pedoman resmi.

### Data fisik

| Kolom | Rentang yang diterima |
|---|---|
| Jenis kelamin | laki-laki atau perempuan |
| Umur | 1 sampai 120 tahun |
| Tinggi | 50 sampai 250 cm |
| Berat | 20 sampai 300 kg |

- Saat menekan **Simpan profil**, ketiga angka (umur, tinggi, berat) wajib diisi dan harus berupa angka wajar. Jika tidak, muncul pesan dan profil tidak tersimpan. Kolom hanya menerima angka.
- Pratinjau target kalori tampil langsung saat pengguna mengetik.

### Kondisi kesehatan

Empat pilihan yang bisa dicentang bersamaan (boleh kosong):

| Kondisi | Zat yang dipantau lebih ketat |
|---|---|
| **Diabetes** | Gula |
| **Hipertensi** | Natrium |
| **Kolesterol tinggi** | Lemak jenuh |
| **Asam urat tinggi** | Purin |

Kondisi yang dicentang menentukan zat mana yang dijadikan fokus di halaman hasil, batas harian yang dipakai, dan isi verdict. Pilihan ini bukan diagnosis.

### Target harian

Target kalori dihitung dari **Angka Kecukupan Gizi (AKG) Kemenkes, Permenkes No. 28 Tahun 2019**, sesuai umur dan jenis kelamin (tabel di `data/akg_2019.csv`), lalu disesuaikan tujuan (±15%).

Contoh: perempuan 45 tahun, tujuan Cutting → AKG 2.150 kkal × 0,85 = 1.828 kkal.

Jika data fisik lengkap, layar profil juga menampilkan **perkiraan pribadi** dari rumus Mifflin-St Jeor (dari tinggi dan berat, aktivitas ringan, ×1,375) sebagai pembanding. Angka utama yang dipakai tetap AKG.

Keterbatasan: tabel AKG yang tercatat mulai umur 10 tahun. Untuk umur di bawah itu, aplikasi memakai target umum 2.000 kkal.

### Tombol "Lewati, pakai target umum"

Pengguna yang tidak ingin mengisi profil bisa menekan tombol ini. Akibatnya:

- target kalori **2.000 kkal per hari**;
- tujuan otomatis **Seimbang** (pilihan tujuan sebelumnya diabaikan);
- jenis kelamin diisi perempuan sebagai nilai bawaan, data fisik kosong;
- **semua kondisi kesehatan dianggap tidak ada**, walaupun sempat dicentang, sehingga yang dipakai batas harian umum.

## Langkah 2: Makanan

Pengguna mengisi:

1. **Nama makanan** (maksimal 100 karakter), misalnya "Sayur sop" atau "Ayam geprek".
2. **Foto makanan**: JPG, PNG, atau WEBP, maksimal 5 MB. Tombol baru aktif setelah nama dan foto terisi.

Menekan **Cek bahan makanan** menjalankan tiga langkah nyata. Setiap langkah dicentang setelah server menyelesaikannya, dengan hasil asli di bawahnya:

| Langkah | Yang dikerjakan | Contoh hasil |
|---|---|---|
| 1. Mengunggah foto makanan | Validasi tipe dan ukuran, lalu simpan | "Foto diterima (13 KB)" |
| 2. Mendeteksi bahan dari foto dengan AI | Panggilan ke Gemini, atau resep contoh bila AI tidak aktif | "Dikenali AI: Sayur sop, 9 bahan" |
| 3. Mencocokkan bahan dengan data gizi | Pencocokan tiap bahan ke data | "7 dari 9 bahan punya data gizi" |

Langkah 2 paling lama (sekitar 10 detik dengan Gemini). Bila ada langkah yang gagal, langkah itu ditandai merah dengan pesan penyebabnya. Tanpa JavaScript, form tetap bekerja lewat pengiriman biasa.

## Langkah 3: Cek bahan

Halaman ini memastikan bahan sebelum dihitung.

- **Foto dan nama hidangan**, dengan label sumber: "Dikenali AI dari foto dan nama" atau "Resep contoh, belum dari foto".
- **Konflik nama dan foto**: jika nama yang diketik berbeda dengan isi foto (misalnya "Sate ayam" tetapi fotonya sayur sop), muncul banner dengan dua pilihan, **Pakai nama** atau **Pakai foto**.
- **Ukuran piring** (Kecil, Sedang, Besar): ditebak AI dari foto, dengan alasan singkat. Bila pengguna menggantinya, gram semua bahan ikut berubah (×0,7, ×1,0, ×1,3) dan total tampil langsung.
- **Daftar bahan**: tiap bahan menampilkan gram, perkiraan kalori, sumber data (misalnya "TKPI 2017"), dan tingkat kelengkapan data (Lengkap/Sebagian). Bahan bisa **diedit** (jumlah dengan satuan g, ml, butir, sdm, sdt, potong, buah, siung, lembar, mangkuk, tusuk), **dihapus**, atau **ditambah** lewat pencarian.
- **Bahan tanpa data** diberi label "Belum ada data" dan tidak ikut dihitung.
- **Berapa piring dimakan**: 0,5 sampai 3 dengan kelipatan 0,5. Total gram dan kalori berubah langsung.

Menekan **Hitung estimasi gizi** menjalankan tiga langkah nyata lagi:

| Langkah | Yang dikerjakan | Contoh hasil |
|---|---|---|
| 1. Mencocokkan bahan dengan data komposisi | Simpan bahan dan porsi, cocokkan ke data | "2 dari 3 bahan punya data gizi" |
| 2. Menghitung energi dan makro per porsi | Hitung gizi | "544 kkal, protein 33 g, lemak 45 g, karbo 0 g" |
| 3. Menyesuaikan profil kesehatan dan batas aman | Nilai risiko dan verdict | "Hati-hati, natrium tertinggi 85%" |

## Langkah 4: Hasil

Halaman hasil dibuat singkat. Urutan isinya:

1. **Judul dan ringkasan**: nama hidangan, ukuran piring dan sumbernya, jumlah piring, total gram, serta kondisi kesehatan yang aktif.
2. **Verdict** (Cocok, Cukup cocok, atau Hati-hati) dengan satu kalimat alasan.
3. **Porsi dimakan**: tombol − dan +, serta tombol **Pakai porsi disarankan (0,5)** dan baris perbandingan (misalnya "Natrium: 1 piring 58% → 0,5 piring 29%"). Mengubah porsi memperbarui semua angka, dan porsi tersimpan.
4. **Zat yang dipantau**: natrium, gula, lemak jenuh, dan purin dengan angka, persen batas harian, level (Rendah, Sedang, Tinggi), dan penyumbang terbesar. Zat dari kondisi yang dicentang tampil di atas, sisanya dilipat sebagai informasi. Zat tanpa data tampil "Data bahan belum tersedia".
5. **Kalori dan makronutrien**: cincin energi, persen dari target harian, sisa kebutuhan, serta karbohidrat, protein, dan lemak.
6. **Perbaiki piringmu**: maksimal tiga saran (misalnya "Ganti daging ayam dengan dada ayam tanpa kulit: Lemak jenuh 102% → 42% batas harian"). Tiap saran punya tombol **Terapkan**, dan tombol **Batalkan semua** mengembalikan bahan awal. Angka, bar, dan verdict bergerak ke nilai baru, dengan ringkasan perubahan.
7. **Satu kalimat saran** dari zat tertinggi, misalnya "Untuk hipertensi dan kolesterol tinggi, lemak jenuh paling tinggi (262% batas harian), terutama dari santan dan daging ayam."
8. **Lihat rincian bahan dan sumber** (dilipat): tabel gram dan kalori per bahan, serta sumber data.

## Cara perhitungan

### Gizi

```
nilai gizi = (nilai per 100 g ÷ 100) × gram bahan × jumlah piring
```

Totalnya dijumlahkan dari semua bahan yang punya data. Bahan yang datanya kosong untuk suatu zat tidak menambah zat itu, dan hasilnya diberi catatan "data sebagian bahan".

### Batas harian

| Zat | Umum (kondisi tidak dicentang) | Kondisi dicentang | Sumber |
|---|---|---|---|
| Natrium | 2.000 mg | 1.500 mg (hipertensi) | Permenkes 30/2013; American Heart Association |
| Gula | 50 g | 5% energi (diabetes) | Permenkes 30/2013; Persagi/PERKENI |
| Lemak jenuh | di bawah 10% energi | di bawah 7% energi (kolesterol tinggi) | WHO; PERKI 2022 |
| Purin | tidak dinilai (hanya informasi) | 400 mg (asam urat) | pedoman gout Jepang (Kaneko 2014) |

Batas gula dan lemak jenuh dihitung dari **target kalori pengguna** (gula: 5% energi ÷ 4 kkal/g; lemak jenuh: 7% atau 10% energi ÷ 9 kkal/g). Sebagian angka dikutip dari sumber sekunder dan perlu dicocokkan ke dokumen pedoman aslinya sebelum dipakai secara formal.

### Level risiko

Level dihitung dari persen terhadap batas harian untuk **satu waktu makan**:

| Persen batas harian | Level |
|---|---|
| di bawah 20% | Rendah |
| 20% sampai 33% | Sedang |
| di atas 33% | Tinggi |

Sepertiga batas harian dipakai sebagai plafon sekali makan.

### Verdict

- **Ada kondisi dicentang**: ditentukan zat terburuk dari kondisi itu. Di atas 33% → **Hati-hati**, 20% sampai 33% → **Cukup cocok**, di bawah 20% → **Cocok**. Bila data zat itu tidak tersedia → **Belum bisa dinilai**, dengan catatan.
- **Tanpa kondisi**: bila ada zat di atas 33% batas umum → **Cukup cocok**. Bila tidak ada, dinilai dari kalori (di atas 40% target harian → Cukup cocok, selain itu Cocok). Verdict tanpa kondisi tidak pernah "Hati-hati".

### Porsi disarankan

```
porsi disarankan = 33% ÷ persen terburuk
```

Dari zat kondisi yang dicentang, dihitung untuk satu piring, dibulatkan **ke bawah** per 0,1 supaya benar-benar di bawah 33%, dengan batas minimal 0,3 dan maksimal 1. Tanpa kondisi, porsi disarankan selalu 1.

### Perbaiki piringmu

Untuk setiap zat yang tinggi (di atas 33%, atau di atas 20% bila zat itu dari kondisi yang dicentang), kode mencoba dua aksi:

1. **Kurangi**: bahan penyumbang terbesar dikurangi setengahnya.
2. **Ganti**: bahan diganti pengganti yang lebih ringan dari daftar tetap (minyak sawit ke minyak kedelai, mentega ke margarin, susu kental manis ke susu sapi, ayam berkulit ke dada ayam tanpa kulit), hanya bila pengganti punya data untuk zat itu.

Setiap aksi dihitung ulang dengan kalkulator dan batas yang sama, lalu dipilih yang paling menurunkan zat itu. Aksi yang sama untuk beberapa zat digabung jadi satu saran. Bila setelah penggantian ada zat lain yang naik (misalnya purin pada dada ayam), angkanya tetap ditampilkan apa adanya.

## Peran AI dan kode

| Tugas | Siapa |
|---|---|
| Mengenali hidangan dari foto dan nama | **AI (Google Gemini)** |
| Menebak bahan dan perkiraan gram | **AI**, hanya memilih nama bahan dari daftar 80-an nama yang sudah dipastikan punya data gizi |
| Menebak ukuran piring | **AI** |
| Mendeteksi nama dan foto yang tidak cocok | **AI** |
| Semua angka gizi, persen batas, level, verdict | **Kode PHP** dari data CSV |
| Porsi disarankan dan saran "Perbaiki piringmu" | **Kode PHP** |
| Kalimat saran dan teks hasil | **Kode PHP** (templat dari angka yang dihitung) |

AI **tidak diminta menyebut angka gizi**, dan angka gizi dari AI bila ada akan diabaikan. Jadi setiap angka di hasil bisa ditelusuri ke sumber data dan hasilnya sama untuk input yang sama.

**Detail penggunaan Gemini**
- Model dibaca dari `GEMINI_MODEL` (bawaan `gemini-3-flash-preview`), dengan model cadangan bila gagal.
- Yang dikirim ke Gemini hanya **foto dan nama makanan**. Profil dan kondisi kesehatan pengguna tidak dikirim.
- Jawaban diminta berupa JSON dan divalidasi: nama bahan wajib ada, berat 1 sampai 1.000 g, maksimal 15 bahan, ukuran piring salah satu dari kecil, sedang, atau besar.
- Kunci API dikirim lewat header (bukan di URL), dan verifikasi SSL aktif.

**Tanpa kunci API atau bila Gemini gagal**: aplikasi memakai resep contoh yang tertanam (sekitar 19 hidangan, di `AiClient`) dan memberi label "Resep contoh, belum dari foto". Hidangan di luar daftar diawali satu bahan dari namanya sendiri, lalu pengguna melengkapinya.

## Data dan sumber

Data gizi ada di folder [`data/`](data/). Rincian kolom, perubahan, dan batasannya ada di [`data/README.md`](data/README.md).

| Data | Sumber | Lisensi / catatan |
|---|---|---|
| Energi, protein, lemak, karbohidrat, serat, natrium (sebagian besar bahan) | **Tabel Komposisi Pangan Indonesia (TKPI) 2017**, Direktorat Gizi Masyarakat, Kementerian Kesehatan RI, Jakarta 2018, ISBN 978-602-416-407-2. [repository.kemkes.go.id/book/777](https://repository.kemkes.go.id/book/777) | Publikasi pemerintah. Izin pakai tidak tertulis eksplisit pada berkas, tim perlu memastikannya. |
| Gula, lemak jenuh, kolesterol, natrium, serat tambahan (puluhan bahan dasar, bumbu, dan saus) | **USDA FoodData Central, SR Legacy (April 2018)**, U.S. Department of Agriculture, Agricultural Research Service. [fdc.nal.usda.gov](https://fdc.nal.usda.gov) | Domain publik. Pemetaan per bahan ada di `data/pemetaan_usda.csv`. |
| Purin (sekitar 100 bahan) | Kaneko K. dkk., "Total purine and purine base content of common foodstuffs for facilitating nutritional therapy for gout and hyperuricemia", *Biological and Pharmaceutical Bulletin* 37(5):709-721, 2014. [jstage.jst.go.jp/article/bpb/37/5/37_b13-00967](https://www.jstage.jst.go.jp/article/bpb/37/5/37_b13-00967/_html) | Pangan Jepang dipetakan ke bahan lokal sebagai padanan, bukan pengukuran langsung. |
| Kecap manis (gula, karbohidrat, natrium, energi) | **Open Food Facts**, median 8 label kemasan. [openfoodfacts.org](https://world.openfoodfacts.org) | Lisensi ODbL: wajib atribusi dan berbagi dengan lisensi sama. Angka adalah perkiraan antar merek. |
| Target kalori dan kebutuhan zat | **Angka Kecukupan Gizi, Permenkes No. 28 Tahun 2019** (energi per umur dan jenis kelamin, `data/akg_2019.csv`) | Peraturan pemerintah. Tabel dibaca dari PDF resmi. |
| Batas harian | Permenkes No. 30 Tahun 2013, American Heart Association, WHO, PERKI 2022, Persagi/PERKENI | Lihat [Batas harian](#batas-harian). |

Berkas data utama:

| Berkas | Isi |
|---|---|
| `data/nutrition_bersih.csv` | Sekitar 1.360 baris pangan, 40 kolom (nilai per 100 g) |
| `data/ingredient_aliases.csv` | Pemetaan nama umum ke nama baku (misalnya "telur" → "telur ayam") |
| `data/akg_2019.csv` | AKG energi dan protein per kelompok umur |
| `data/pemetaan_usda.csv` | Kode USDA tiap bahan yang dilengkapi |

**Aturan data**: kosong berarti tidak tersedia, bukan nol. Aplikasi menampilkan "data belum tersedia" dan tidak menghitung bahan itu untuk zat tersebut. Gula dan lemak jenuh masakan lokal jadi (rawon, soto, rendang) belum ada di sumber terbuka yang diperiksa.

**Pembaruan data**: data TKPI 2017 relatif stabil untuk bahan dasar, sedangkan produk kemasan dan batas anjuran bisa berubah. Karena itu setiap angka mencatat sumbernya.

## Teknologi dan struktur proyek

**Teknologi**: HTML5, CSS3, dan JavaScript (tanpa kerangka kerja) di sisi tampilan; PHP native dengan pola MVC di sisi server; data dari berkas CSV. Tabel MySQL (`database/schema.sql`) disiapkan untuk pengembangan berikutnya, dan bila terisi dipakai lebih dulu dengan CSV sebagai cadangan. Sesi pengguna memakai sesi PHP (`storage/sessions`).

```
app/
  Controllers/   Profil, Makanan, Konfirmasi, Hasil, Api
  Models/        NutritionFact (baca data), HealthRule (batas harian), Ingredient
  Services/      AiClient, PortionSize, NutritionCalculator, TargetCalculator,
                 RiskEvaluator, PortionAdvisor, VerdictBuilder, FoodImprover,
                 ClinicalAdvisor, ResultBuilder
  Views/         profil, makanan, konfirmasi, hasil, galat, partials
config/          routes.php
core/            Router, Controller, Database, Env, helpers
data/            CSV gizi, alias, AKG, pemetaan USDA
database/        schema.sql, seed_dasar.sql
public/          index.php (pintu masuk), assets/css, assets/js, assets/uploads
storage/         sesi dan unggahan
```

**Rute utama**

| Metode | Rute | Fungsi |
|---|---|---|
| GET/POST | `/profil`, `/profil/skip` | Form profil dan lewati |
| GET/POST | `/makanan` | Form nama dan foto |
| GET/POST | `/konfirmasi`, `/konfirmasi/resolve-conflict` | Cek bahan dan pilihan nama/foto |
| GET | `/hasil` | Halaman hasil |
| POST | `/api/cek/1..3` | Tiga langkah cek bahan |
| POST | `/api/hitung/1..3` | Tiga langkah hitung estimasi |
| POST | `/api/recalculate` | Hitung ulang saat porsi diubah |
| POST | `/api/terapkan-saran`, `/api/batalkan-saran` | Terapkan atau batalkan saran perbaikan |
| GET | `/api/search-ingredient` | Pencarian bahan |

## Cara menjalankan

**Kebutuhan**: PHP 8.1 atau lebih baru dengan ekstensi `curl`, `mbstring`, dan `fileinfo`. MySQL tidak wajib untuk saat ini.

1. Salin `.env.example` menjadi `.env`, lalu isi nilainya sendiri.

   | Variabel | Fungsi |
   |---|---|
   | `GEMINI_API_KEY` | Kunci API Gemini milik Anda. Kosongkan untuk memakai resep contoh |
   | `GEMINI_MODEL` | Model Gemini (bawaan `gemini-3-flash-preview`) |
   | `APP_DEBUG` | `true` menampilkan galat di layar, selain itu hanya masuk log |
   | `DB_*` | Dipakai hanya bila MySQL diaktifkan |

   Jangan pernah meng-commit `.env` atau membagikan kunci API. Berkas `.env` sudah masuk `.gitignore`.

2. Jalankan server (dari folder proyek):

   ```
   php -S localhost:8000 -t public
   ```

   Buka `http://localhost:8000`.

3. Atau lewat Laragon/Apache: arahkan akar dokumen ke folder `public`, atau buka `http://localhost/<nama-folder>/public`.

Folder `public/assets/uploads` harus bisa ditulis, karena foto yang diunggah disimpan di sana.

## Keamanan dan privasi

- **Unggah foto**: tipe divalidasi dari isi berkas (bukan nama), ekstensi ditentukan dari tipe, ukuran maksimal 5 MB, dan folder unggahan dilarang menjalankan skrip (`.htaccess`).
- **Foto lama** hanya dipakai bila berasal dari folder unggahan aplikasi, supaya jalur berkas lain tidak bisa dibaca.
- **Pembacaan CSV** memakai pencocokan nama yang ketat, dan keluaran tampilan di-escape.
- **Privasi**: tidak ada akun, login, atau riwayat. Data profil dan hasil hidup di sesi server. Hanya foto dan nama makanan yang dikirim ke Gemini, tanpa profil atau kondisi kesehatan.
- **Rahasia**: kunci API hanya di `.env` dan dikirim lewat header.
- **Foto yang diunggah** tersimpan di server di `public/assets/uploads` (tidak di-commit). Untuk penggunaan nyata, folder ini perlu dibersihkan berkala.

## Keterbatasan yang diketahui

- Data masakan lokal jadi untuk gula dan lemak jenuh belum ada, dan sebagian bahan (misalnya daun jeruk, daun pandan, kencur) tidak punya data resmi.
- Purin hanya tersedia untuk sekitar 100 bahan, dan nilainya berasal dari pangan Jepang sebagai padanan.
- Tabel AKG yang dipakai mulai umur 10 tahun.
- Sebagian batas harian dikutip dari sumber sekunder dan perlu dicocokkan ke dokumen aslinya.
- Tebakan bahan dan ukuran piring oleh AI adalah perkiraan, dan jumlah garam serta minyak di masakan tidak terlihat dari foto, sehingga natrium dan lemak paling tidak pasti.
- Aturan ±15% tujuan, ambang 40% kalori, dan saran "kurangi setengah" adalah aturan tim.
- Belum ada perlindungan CSRF dan pembatasan laju permintaan. Tabel MySQL belum dipakai.
- Kuota gratis Gemini terbatas, dan pada tingkat gratis isi permintaan dapat dipakai penyedia untuk peningkatan layanan.

## Kontribusi tim

Berdasarkan riwayat commit di repositori ini:

| Akun GitHub | Kontribusi |
|---|---|
| `faturrahman82` | Struktur proyek; pembersihan dan pengayaan data gizi (TKPI, USDA, purin, AKG); batas harian per kondisi; ukuran piring dan porsi disarankan; saran perbaikan piring; integrasi dan pengarahan Gemini; perapian keamanan; dokumentasi |
| `B1bub0b` | Kerangka aplikasi PHP MVC; halaman beranda, input makanan, dan hasil; integrasi awal Gemini |
| `putxy1` | Peringatan di profil, pemeriksaan makanan, loader, dan perbaikan tampilan responsif (halaman hasil dan langkah 4) |
| `aripratama1234ari-droid` | Perbaikan galat halaman cek bahan dan hasil |

## Atribusi

Data gizi berasal dari sumber yang tercantum di [Data dan sumber](#data-dan-sumber). Data Open Food Facts berlisensi ODbL, dan USDA FoodData Central berstatus domain publik. Penggunaan AI dalam aplikasi: Google Gemini untuk mengenali makanan dari foto, sebagaimana dijelaskan di [Peran AI dan kode](#peran-ai-dan-kode).
