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


### Alias bahan makanan (pencocokan nama)

Daftar `data/ingredient_aliases.csv` memetakan nama umum ke `nama_kunci` kanonis pada `data/nutrition_bersih.csv`.
Contoh: `telur` diarahkan ke `telur ayam`, sehingga tidak akan cocok secara parsial dengan `ikan telur asin mentah`.
Jika menambah alias baru, pastikan `nama_kunci` tujuan benar-benar ada di `nutrition_bersih.csv`.
File alias ini dibaca oleh aplikasi PHP dan tidak perlu diimpor ke tabel `nutrition_facts`.
