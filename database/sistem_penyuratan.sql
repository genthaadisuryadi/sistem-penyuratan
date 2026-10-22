-- ============================================================================
--  Sistem Penyuratan - skema & data awal MySQL / MariaDB
--  Setara dengan hasil `php artisan migrate` + data yang ada di database.sqlite
--  (akun superadmin, 5 jenis surat, log login).
--
--  Cara impor:
--    mysql -u root -p < database/sistem_penyuratan.sql
--  atau lewat phpMyAdmin: tab "Impor" > pilih file ini.
--
--  Setelah impor TIDAK perlu menjalankan `php artisan migrate` lagi
--  (tabel `migrations` sudah terisi).
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET time_zone = '+07:00';

CREATE DATABASE IF NOT EXISTS `sistem_penyuratan`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sistem_penyuratan`;

DROP TABLE IF EXISTS `scan_logs`, `audit_logs`, `surat`, `jenis_surat`,
  `failed_jobs`, `job_batches`, `jobs`, `cache_locks`, `cache`,
  `sessions`, `password_reset_tokens`, `users`, `migrations`;

-- ---------------------------------------------------------------- migrations
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------- users
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `jabatan` varchar(150) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'unit',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------- cache & jobs
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- jenis_surat
CREATE TABLE `jenis_surat` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jenis_surat_nama_unique` (`nama`),
  UNIQUE KEY `jenis_surat_kode_unique` (`kode`),
  KEY `jenis_surat_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------- surat
CREATE TABLE `surat` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jenis_surat_id` bigint unsigned DEFAULT NULL,
  `uuid` char(36) NOT NULL,
  `kode_verifikasi` varchar(12) NOT NULL,
  `nomor_surat` varchar(255) NOT NULL,
  `perihal` varchar(255) NOT NULL,
  `ditujukan_kepada` varchar(255) DEFAULT NULL,
  `tanggal_surat` date NOT NULL,
  `penandatangan_nama` varchar(255) NOT NULL,
  `penandatangan_jabatan` varchar(255) NOT NULL,
  `qr_posisi` varchar(20) NOT NULL,
  `qr_halaman` varchar(10) NOT NULL,
  `file_asli_path` varchar(255) NOT NULL,
  `file_final_path` varchar(255) NOT NULL,
  `file_hash_sha256` char(64) NOT NULL,
  `file_size` int unsigned NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'draf',
  `published_at` timestamp NULL DEFAULT NULL,
  `alasan_pencabutan` text,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `revoked_by` bigint unsigned DEFAULT NULL,
  `scan_count` int unsigned NOT NULL DEFAULT 0,
  `last_scanned_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `penandatangan_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_uuid_unique` (`uuid`),
  UNIQUE KEY `surat_kode_verifikasi_unique` (`kode_verifikasi`),
  UNIQUE KEY `surat_nomor_surat_unique` (`nomor_surat`),
  KEY `surat_tanggal_surat_index` (`tanggal_surat`),
  KEY `surat_status_index` (`status`),
  KEY `surat_published_at_index` (`published_at`),
  KEY `surat_jenis_surat_id_foreign` (`jenis_surat_id`),
  KEY `surat_revoked_by_foreign` (`revoked_by`),
  KEY `surat_created_by_foreign` (`created_by`),
  KEY `surat_penandatangan_id_foreign` (`penandatangan_id`),
  CONSTRAINT `surat_jenis_surat_id_foreign` FOREIGN KEY (`jenis_surat_id`) REFERENCES `jenis_surat` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surat_revoked_by_foreign` FOREIGN KEY (`revoked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surat_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `surat_penandatangan_id_foreign` FOREIGN KEY (`penandatangan_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------- scan_logs
CREATE TABLE `scan_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `surat_id` bigint unsigned NOT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_saat_scan` varchar(10) NOT NULL,
  `ip_hash` char(64) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `is_bot` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `scan_logs_surat_id_scanned_at_index` (`surat_id`,`scanned_at`),
  CONSTRAINT `scan_logs_surat_id_foreign` FOREIGN KEY (`surat_id`) REFERENCES `surat` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------- audit_logs
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `surat_id` bigint unsigned DEFAULT NULL,
  `action` varchar(30) NOT NULL,
  `metadata` json DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_surat_id_foreign` (`surat_id`),
  KEY `audit_logs_action_index` (`action`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `audit_logs_surat_id_foreign` FOREIGN KEY (`surat_id`) REFERENCES `surat` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================================= DATA
INSERT INTO `migrations` (`id`,`migration`,`batch`) VALUES
 (1,'0001_01_01_000000_create_users_table',1),
 (2,'0001_01_01_000001_create_cache_table',1),
 (3,'0001_01_01_000002_create_jobs_table',1),
 (4,'2026_10_19_000001_add_admin_columns_to_users_table',1),
 (5,'2026_10_19_000002_create_surat_table',1),
 (6,'2026_10_19_000003_create_scan_logs_table',1),
 (7,'2026_10_19_000004_create_audit_logs_table',1),
 (8,'2026_10_20_000005_add_master_and_workflow_fields',1);

-- Akun superadmin awal (kata sandi sama dengan yang dipakai di instalasi SQLite).
INSERT INTO `users` (`id`,`name`,`jabatan`,`email`,`username`,`email_verified_at`,`password`,`role`,`is_active`,`last_login_at`,`remember_token`,`created_at`,`updated_at`) VALUES
 (1,'Administrator',NULL,'admin@instansi.test','admin',NULL,'$2y$12$5Qld6N5iWA4RC6s9Y7m/w.6sEAgjWXYXdLjwx9VKmi1mcM3ThQDNe','superadmin',1,'2026-10-21 09:52:11',NULL,'2026-10-20 11:46:33','2026-10-21 09:52:11');

INSERT INTO `jenis_surat` (`id`,`nama`,`kode`,`is_active`,`created_at`,`updated_at`) VALUES
 (1,'Surat Undangan','UND',1,'2026-10-20 11:46:33','2026-10-20 11:46:33'),
 (2,'Surat Pemberitahuan','PEM',1,'2026-10-20 11:46:33','2026-10-20 11:46:33'),
 (3,'Surat Keputusan','KEP',1,'2026-10-20 11:46:33','2026-10-20 11:46:33'),
 (4,'Surat Tugas','TGS',1,'2026-10-20 11:46:33','2026-10-20 11:46:33'),
 (5,'Surat Permohonan','PMH',1,'2026-10-20 11:46:33','2026-10-20 11:46:33');

INSERT INTO `audit_logs` (`id`,`user_id`,`surat_id`,`action`,`metadata`,`ip`,`created_at`) VALUES
 (1,1,NULL,'login',NULL,'127.0.0.1','2026-10-20 11:46:56'),
 (2,1,NULL,'login',NULL,'127.0.0.1','2026-10-21 09:52:11');

SET FOREIGN_KEY_CHECKS = 1;
