-- =============================================================================
-- Skema Basis Data CekPiring
-- LOGICODIX 2026 Hackathon Competition (Subtema: Inclusive Health & Well-being)
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `cekpiring` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cekpiring`;

-- 1. Tabel Master Bahan & Nutrisi (Merujuk TKPI 2017 & USDA SR Legacy)
CREATE TABLE IF NOT EXISTS `nutrition_facts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_asli` INT NULL,
    `nama` VARCHAR(150) NOT NULL,
    `nama_kunci` VARCHAR(150) NOT NULL UNIQUE,
    `energi_kkal` FLOAT NULL,
    `protein_g` FLOAT NULL,
    `lemak_g` FLOAT NULL,
    `karbohidrat_g` FLOAT NULL,
    `serat_g` FLOAT NULL,
    `gula_g` FLOAT NULL,
    `lemak_jenuh_g` FLOAT NULL,
    `kolesterol_mg` FLOAT NULL,
    `natrium_mg` FLOAT NULL,
    `sumber_natrium` VARCHAR(50) NULL,
    `purin_mg` FLOAT NULL,
    `purin_kategori` VARCHAR(50) NULL,
    `purin_dasar` VARCHAR(150) NULL,
    `kode_tkpi` VARCHAR(50) NULL,
    `kode_usda` VARCHAR(50) NULL,
    `dasar_cocok` VARCHAR(50) NULL,
    `catatan_periksa` TEXT NULL,
    `koreksi` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel Aturan Kesehatan & Batas Nutrisi (Health Rules WHO/Kemenkes)
CREATE TABLE IF NOT EXISTS `health_rules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kondisi` VARCHAR(50) NOT NULL,
    `zat_gizi` VARCHAR(50) NOT NULL,
    `batas_harian` FLOAT NOT NULL,
    `satuan` VARCHAR(20) NOT NULL,
    `sumber_acuan` VARCHAR(100) NOT NULL,
    `deskripsi` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel Sesi Analisis (Untuk Riwayat atau Cache Sementara)
CREATE TABLE IF NOT EXISTS `analysis_sessions` (
    `id` VARCHAR(64) PRIMARY KEY,
    `nama_makanan` VARCHAR(150) NOT NULL,
    `foto_path` VARCHAR(255) NULL,
    `tujuan` ENUM('cutting', 'bulking', 'seimbang') DEFAULT 'seimbang',
    `kondisi_diabetes` TINYINT(1) DEFAULT 0,
    `kondisi_hipertensi` TINYINT(1) DEFAULT 0,
    `kondisi_kolesterol` TINYINT(1) DEFAULT 0,
    `kondisi_asam_urat` TINYINT(1) DEFAULT 0,
    `target_kalori` INT NOT NULL,
    `porsi` FLOAT DEFAULT 1.0,
    `hasil_json` LONGTEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
