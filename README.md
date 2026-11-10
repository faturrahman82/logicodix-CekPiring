# CekPiring

Aplikasi web untuk LOGICODIX 2026 (subtema Inclusive Health & Well-being, kategori Web). Pengguna memasukkan foto dan nama makanan, memastikan bahan-bahannya, lalu melihat estimasi gizi dan risikonya sesuai kondisi kesehatan.

> Bagian lain README (cara menjalankan, penggunaan AI, kontribusi tim) diisi tim saat pengembangan.

## Sumber data

Data gizi ada di folder [`data/`](data/). Rincian kolom, perubahan, dan batasannya ada di [`data/README.md`](data/README.md). Ringkasan sumbernya:

| Data | Sumber | Lisensi / catatan |
|---|---|---|
| Energi, protein, lemak, karbohidrat, serat, natrium (sebagian besar bahan) | **Tabel Komposisi Pangan Indonesia (TKPI) 2017**, Direktorat Gizi Masyarakat, Kementerian Kesehatan RI, Jakarta 2018, ISBN 978-602-416-407-2. [repository.kemkes.go.id/book/777](https://repository.kemkes.go.id/book/777) | Publikasi pemerintah. Izin pakai tidak tertulis eksplisit pada berkas, tim perlu memastikannya. |
| Gula, lemak jenuh, kolesterol, dan sebagian natrium (27 bahan dasar dan garam dapur) | **USDA FoodData Central, SR Legacy (April 2018)**, U.S. Department of Agriculture, Agricultural Research Service. [fdc.nal.usda.gov](https://fdc.nal.usda.gov) | Domain publik. |
| Purin (83 bahan) | Kaneko K. dkk., "Total purine and purine base content of common foodstuffs for facilitating nutritional therapy for gout and hyperuricemia", *Biological and Pharmaceutical Bulletin* 37(5):709-721, 2014. [jstage.jst.go.jp/article/bpb/37/5/37_b13-00967](https://www.jstage.jst.go.jp/article/bpb/37/5/37_b13-00967/_html) | Angka diambil dari tabel jurnal. Pangan Jepang dipetakan ke bahan lokal sebagai padanan, bukan pengukuran langsung. |
| Kecap manis (gula, karbohidrat, natrium, energi) | **Open Food Facts**, median 8 label kemasan. [openfoodfacts.org](https://world.openfoodfacts.org) | Lisensi ODbL: wajib atribusi dan berbagi dengan lisensi sama. Angka adalah perkiraan antar merek. |

Data kosong berarti tidak tersedia, bukan nol. Aplikasi menampilkan "data belum tersedia" untuk bahan itu dan tidak memasukkannya ke perhitungan.

Gula dan lemak jenuh masakan lokal (rawon, soto, rendang, nasi goreng) belum ada di sumber terbuka yang sudah diperiksa.

### Target kalori dan batas harian

| Hal | Umum (kondisi tidak dicentang) | Kondisi dicentang | Sumber |
|---|---|---|---|
| Target kalori | AKG per umur dan jenis kelamin; cutting -15%, bulking +15% (aturan tim) | sama | Permenkes 28/2019 (`data/akg_2019.csv`); rumus Mifflin-St Jeor hanya pembanding |
| Natrium | 2.000 mg | 1.500 mg (hipertensi) | Permenkes 30/2013; AHA |
| Gula | 50 g | 5% energi (diabetes) | Permenkes 30/2013; Persagi/PERKENI |
| Lemak jenuh | di bawah 10% energi | di bawah 7% energi (kolesterol tinggi) | WHO; PERKI 2022 |
| Purin | tidak dinilai (informasi) | 400 mg (asam urat) | pedoman gout Jepang (Kaneko 2014) |

Level per waktu makan: Rendah di bawah 20%, Sedang 20-33%, Tinggi di atas 33% batas harian (sepertiga batas untuk sekali makan). Sebagian angka dikutip dari sumber sekunder dan perlu dicocokkan ke dokumen pedoman aslinya.

### Cara aplikasi membaca data

Versi MVP membaca data gizi langsung dari `data/nutrition_bersih.csv` (lewat `App\Models\NutritionFact`). Tabel MySQL `nutrition_facts` di `database/schema.sql` disiapkan untuk pengembangan berikutnya. Bila tabel itu sudah diisi, aplikasi memakainya lebih dulu dan CSV menjadi cadangan.

### Peran AI dan kode

- **AI (Google Gemini):** mengenali hidangan dari foto dan nama, menebak ukuran piring (kecil/sedang/besar), dan mengusulkan daftar bahan beserta gramnya. AI tidak diminta menyebut angka gizi.
- **Kode PHP (deterministik):** semua angka gizi, persen batas harian, verdict, porsi disarankan, dan saran perbaikan dihitung dari data CSV. Rumusnya: nilai per 100 g × gram ÷ 100 × jumlah piring.
- **Perbaiki piringmu (kode, bukan AI):** untuk setiap zat yang tinggi, aplikasi mencoba dua aksi, yaitu mengurangi setengah bahan penyumbang terbesar atau mengganti bahan dengan pengganti yang lebih ringan dari daftar tetap (minyak sawit ke minyak kedelai, mentega ke margarin, susu kental manis ke susu sapi, ayam berkulit ke dada ayam tanpa kulit). Setiap aksi dihitung ulang dengan rumus yang sama, lalu dipilih yang paling menurunkan zat itu. Pengganti hanya dipakai bila datanya tersedia.
- Tanpa kunci API, aplikasi memakai resep contoh dan memberi label "Resep contoh, belum dari foto".


### Alias bahan makanan (pencocokan nama)

Daftar `data/ingredient_aliases.csv` memetakan nama umum ke `nama_kunci` kanonis pada `data/nutrition_bersih.csv`.
Contoh: `telur` diarahkan ke `telur ayam`, sehingga tidak akan cocok secara parsial dengan `ikan telur asin mentah`.
Jika menambah alias baru, pastikan `nama_kunci` tujuan benar-benar ada di `nutrition_bersih.csv`.
File alias ini dibaca oleh aplikasi PHP dan tidak perlu diimpor ke tabel `nutrition_facts`.
