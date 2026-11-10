USE `cekpiring`;

INSERT INTO `health_rules` (`kondisi`, `zat_gizi`, `batas_harian`, `satuan`, `sumber_acuan`, `deskripsi`) VALUES
('hipertensi', 'natrium_mg', 2000.0, 'mg', 'WHO Sodium reduction (2026)', 'Maksimal 2.000 mg natrium atau setara 1 sendok teh garam per hari.'),
('diabetes', 'gula_g', 25.0, 'g', 'WHO Guideline: Sugars intake (2015)', 'Maksimal 25 g gula bebas (sekitar 5% dari total energi 2.000 kkal).'),
('kolesterol', 'lemak_jenuh_g', 20.0, 'g', 'AHA / WHO', 'Maksimal 10% dari total kebutuhan energi harian.'),
('asam_urat', 'purin_mg', 400.0, 'mg', 'Kaneko dkk. (2014)', 'Membatasi makanan tinggi purin untuk menjaga kadar asam urat.');

