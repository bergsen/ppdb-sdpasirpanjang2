-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.27-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.4.0.6659
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for ppdb
CREATE DATABASE IF NOT EXISTS `ppdb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `ppdb`;

-- Dumping structure for table ppdb.berkas
CREATE TABLE IF NOT EXISTS `berkas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `calon_siswa_id` int(11) NOT NULL,
  `jenis_berkas` varchar(50) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `status` enum('valid','tidak_valid','belum_diperiksa') DEFAULT 'belum_diperiksa',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `calon_siswa_id` (`calon_siswa_id`),
  CONSTRAINT `berkas_ibfk_1` FOREIGN KEY (`calon_siswa_id`) REFERENCES `calon_siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ppdb.berkas: ~5 rows (approximately)
DELETE FROM `berkas`;
INSERT INTO `berkas` (`id`, `calon_siswa_id`, `jenis_berkas`, `nama_file`, `file_path`, `status`, `catatan`, `created_at`) VALUES
	(73, 20, 'Foto Siswa', 'REG2026103312_foto_uciha_shibito_6ad7fd7823e99.jpg', 'foto', 'valid', '', '2026-10-19 09:50:58'),
	(74, 20, 'Akta Kelahiran', 'REG2026103312_akta_uciha_shibito_6ad7fd7824df8.png', 'akta', 'tidak_valid', '', '2026-10-19 09:50:58'),
	(75, 20, 'Kartu Keluarga', 'REG2026103312_kk_uciha_shibito_6ad7fd78256cb.png', 'kk', 'belum_diperiksa', '', '2026-10-19 09:50:58'),
	(76, 20, 'KTP Orang Tua', 'REG2026103312_ktp_ortu_uciha_shibito_6ad7fd7825d8c.pdf', 'ktp_ortu', 'belum_diperiksa', '', '2026-10-19 09:50:58'),
	(77, 20, 'Ijazah TK', 'REG2026103312_ijazah_tk_uciha_shibito_6ad7fd7826263.pdf', 'ijazah_tk', 'belum_diperiksa', '', '2026-10-19 09:50:58');

-- Dumping structure for table ppdb.calon_siswa
CREATE TABLE IF NOT EXISTS `calon_siswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL,
  `nomor_pendaftaran` varchar(20) NOT NULL,
  `nama_siswa` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `tempat_lahir` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `agama` enum('Islam','Kristen Protestan','Katolik','Hindu','Buddha','Khonghucu') DEFAULT NULL,
  `kewarganegaraan` enum('WNI','WNA') DEFAULT 'WNI',
  `anak_ke` int(11) DEFAULT NULL,
  `jumlah_saudara` int(11) DEFAULT NULL,
  `golongan_darah` varchar(3) DEFAULT NULL,
  `riwayat_penyakit` text DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `nama_ayah` varchar(100) DEFAULT NULL,
  `pekerjaan_ayah` varchar(50) DEFAULT NULL,
  `penghasilan_ayah` varchar(50) DEFAULT NULL,
  `hp_ayah` varchar(20) DEFAULT NULL,
  `nama_ibu` varchar(100) DEFAULT NULL,
  `pekerjaan_ibu` varchar(50) DEFAULT NULL,
  `penghasilan_ibu` varchar(50) DEFAULT NULL,
  `hp_ibu` varchar(20) DEFAULT NULL,
  `nama_wali` varchar(100) DEFAULT NULL,
  `hubungan_wali` varchar(50) DEFAULT NULL,
  `hp_wali` varchar(20) DEFAULT NULL,
  `nama_tk_asal` varchar(100) DEFAULT NULL,
  `pindahan_dari` varchar(100) DEFAULT NULL,
  `kelas_pindahan` varchar(50) DEFAULT NULL,
  `status_terima` enum('pending','diterima','tidak_diterima') DEFAULT 'pending',
  `catatan_panitia` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_pendaftaran` (`nomor_pendaftaran`),
  KEY `user_id` (`user_id`),
  KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  CONSTRAINT `calon_siswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `calon_siswa_ibfk_2` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ppdb.calon_siswa: ~0 rows (approximately)
DELETE FROM `calon_siswa`;
INSERT INTO `calon_siswa` (`id`, `user_id`, `tahun_ajaran_id`, `nomor_pendaftaran`, `nama_siswa`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `agama`, `kewarganegaraan`, `anak_ke`, `jumlah_saudara`, `golongan_darah`, `riwayat_penyakit`, `alamat`, `nama_ayah`, `pekerjaan_ayah`, `penghasilan_ayah`, `hp_ayah`, `nama_ibu`, `pekerjaan_ibu`, `penghasilan_ibu`, `hp_ibu`, `nama_wali`, `hubungan_wali`, `hp_wali`, `nama_tk_asal`, `pindahan_dari`, `kelas_pindahan`, `status_terima`, `catatan_panitia`, `created_at`) VALUES
	(20, 15, 2, 'REG/202610/3312', 'uciha shibito', 'L', 'linggisraya', '2020-01-19', 'Islam', 'WNI', 1, 0, 'O', '-', 'pasirpanjang rt 11 rw 3', 'alfan bekti', 'buruh', '150000', '081227615406', 'sumingah', 'ibu rt', '10000', '081227614092', NULL, NULL, NULL, 'tk semut pasir panjang', NULL, NULL, 'pending', '                                                                                                                                    ', '2026-10-19 09:50:58');

-- Dumping structure for table ppdb.informasi
CREATE TABLE IF NOT EXISTS `informasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(100) NOT NULL,
  `konten` text NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `jenis` enum('informasi','jadwal','persyaratan','lokasi','alur','profil') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ppdb.informasi: ~2 rows (approximately)
DELETE FROM `informasi`;
INSERT INTO `informasi` (`id`, `judul`, `konten`, `file`, `jenis`, `created_at`) VALUES
	(2, 'Persyaratan', '1. FC Ijazah TK\n2. FC Akta Kelahiran\n3. FC KK\n4. FC KTP Orang Tua', NULL, 'persyaratan', '2024-01-01 00:00:00'),
	(11, 'Demo Akreditasi', 'sdn pasirpanjang sudah berakreditasi baik sekali dan berjalan sesuai visi misai yang ada untuk tumbuh kembang generasi bangsa', 'INFORMASI_1778998566_6a095d26624ed.png', 'informasi', '2026-05-17 06:16:06');

-- Dumping structure for table ppdb.pengumuman
CREATE TABLE IF NOT EXISTS `pengumuman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(100) NOT NULL,
  `isi` text NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `tampil_index` enum('ya','tidak') DEFAULT 'ya',
  `allow_download` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ppdb.pengumuman: ~1 rows (approximately)
DELETE FROM `pengumuman`;
INSERT INTO `pengumuman` (`id`, `judul`, `isi`, `file`, `tampil_index`, `allow_download`, `created_at`) VALUES
	(6, 'penerimaan siswa baru', 'penerimaan siswa baru 2025/2026', 'PENGUMUMAN_1778998608_6a095d5025e28.pdf', 'ya', 1, '2026-05-17 06:16:48');

-- Dumping structure for table ppdb.sekolah_berita
CREATE TABLE IF NOT EXISTS `sekolah_berita` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(200) NOT NULL,
  `kategori` varchar(100) NOT NULL DEFAULT 'Umum',
  `tanggal_berita` date NOT NULL,
  `ringkasan` text DEFAULT NULL,
  `isi` mediumtext NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('draft','dipublikasikan') NOT NULL DEFAULT 'draft',
  `tampil_beranda` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_berita_status` (`status`),
  KEY `idx_berita_kategori` (`kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ppdb.sekolah_berita: ~1 rows (approximately)
DELETE FROM `sekolah_berita`;
INSERT INTO `sekolah_berita` (`id`, `judul`, `kategori`, `tanggal_berita`, `ringkasan`, `isi`, `gambar`, `status`, `tampil_beranda`, `created_at`, `updated_at`) VALUES
	(1, 'tes', 'renov sekolah', '2026-10-21', 'renovasi sekolah pada tanggal sekoan dan sekian', 'rekjbdfyvbjk;ms lntuirhnmx sdkflrtmdsjmmnadbfihefg berjalan lancar renovasinya', 'berita_20261021_74c387c18317.jpg', 'dipublikasikan', 1, '2026-10-21 04:33:45', '2026-10-21 04:46:01');

-- Dumping structure for table ppdb.sekolah_ekskul
CREATE TABLE IF NOT EXISTS `sekolah_ekskul` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ppdb.sekolah_ekskul: ~0 rows (approximately)
DELETE FROM `sekolah_ekskul`;
INSERT INTO `sekolah_ekskul` (`id`, `nama`, `deskripsi`, `foto`, `urutan`, `created_at`, `updated_at`) VALUES
	(1, 'sepak bola', '1234ttyuu8', 'ekskul_20261021_716086359c33.png', 0, '2026-10-21 04:43:14', '2026-10-21 04:43:14');

-- Dumping structure for table ppdb.sekolah_fasilitas
CREATE TABLE IF NOT EXISTS `sekolah_fasilitas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ppdb.sekolah_fasilitas: ~0 rows (approximately)
DELETE FROM `sekolah_fasilitas`;
INSERT INTO `sekolah_fasilitas` (`id`, `nama`, `deskripsi`, `foto`, `urutan`, `created_at`, `updated_at`) VALUES
	(1, 'perpustakaan', 'perpus yang mantap', 'fasilitas_20261021_184020c6d173.png', 1, '2026-10-21 04:44:11', '2026-10-21 04:44:11');

-- Dumping structure for table ppdb.sekolah_guru
CREATE TABLE IF NOT EXISTS `sekolah_guru` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(150) NOT NULL,
  `jabatan` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ppdb.sekolah_guru: ~0 rows (approximately)
DELETE FROM `sekolah_guru`;
INSERT INTO `sekolah_guru` (`id`, `nama`, `jabatan`, `deskripsi`, `foto`, `urutan`, `created_at`, `updated_at`) VALUES
	(1, 'rudi pambudi', 'kepala sekolah', 'mmmmmmmm', 'guru_20261021_1b7770aedc16.png', 1, '2026-10-21 04:39:27', '2026-10-21 04:39:27');

-- Dumping structure for table ppdb.sekolah_kegiatan
CREATE TABLE IF NOT EXISTS `sekolah_kegiatan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(200) NOT NULL,
  `kategori` varchar(100) NOT NULL DEFAULT 'Umum',
  `tanggal_kegiatan` date DEFAULT NULL,
  `waktu_kegiatan` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `isi` mediumtext DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('draft','dipublikasikan') NOT NULL DEFAULT 'draft',
  `tampil_beranda` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_kegiatan_status` (`status`),
  KEY `idx_kegiatan_kategori` (`kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ppdb.sekolah_kegiatan: ~0 rows (approximately)
DELETE FROM `sekolah_kegiatan`;
INSERT INTO `sekolah_kegiatan` (`id`, `judul`, `kategori`, `tanggal_kegiatan`, `waktu_kegiatan`, `deskripsi`, `isi`, `foto`, `status`, `tampil_beranda`, `created_at`, `updated_at`) VALUES
	(1, 'demo lomba', 'lomba pramuka', '2026-10-05', '', 'irtioweguefk', 'md ejouhfeoiheriognklfn', 'kegiatan_20261021_9f08f6950494.jpg', 'dipublikasikan', 0, '2026-10-21 04:41:20', '2026-10-21 04:41:20');

-- Dumping structure for table ppdb.sekolah_visi_misi
CREATE TABLE IF NOT EXISTS `sekolah_visi_misi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visi` text DEFAULT NULL,
  `misi` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ppdb.sekolah_visi_misi: ~0 rows (approximately)
DELETE FROM `sekolah_visi_misi`;
INSERT INTO `sekolah_visi_misi` (`id`, `visi`, `misi`, `updated_at`) VALUES
	(1, 'foya lehvsnoifsnvke vkda klx V', 'mendorong siswa menjadi pintar', '2026-10-21 04:44:42');

-- Dumping structure for table ppdb.tahun_ajaran
CREATE TABLE IF NOT EXISTS `tahun_ajaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tahun` varchar(20) NOT NULL,
  `kuota` int(11) DEFAULT 0,
  `status` enum('aktif','nonaktif') DEFAULT 'nonaktif',
  `tanggal_buka` date DEFAULT NULL,
  `tanggal_tutup` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tahun` (`tahun`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ppdb.tahun_ajaran: ~2 rows (approximately)
DELETE FROM `tahun_ajaran`;
INSERT INTO `tahun_ajaran` (`id`, `tahun`, `kuota`, `status`, `tanggal_buka`, `tanggal_tutup`) VALUES
	(1, '2024/2025', 48, 'nonaktif', '2024-01-01', '2024-12-31'),
	(2, '2025/2026', 31, 'aktif', '2025-01-01', '2026-12-30');

-- Dumping structure for table ppdb.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','panitia','pendaftar') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expired` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ppdb.users: ~8 rows (approximately)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `nama`, `username`, `no_hp`, `email`, `password`, `role`, `created_at`, `reset_token`, `reset_expired`) VALUES
	(4, 'zen inari', 'zen', '085327776405', 'zenina@gmail.com', '$2y$10$MYAPPLpy53yW0OnI0uqX2uFD2bqq91veB3b/WWju5boJsOJ0qx2oO', 'admin', '2026-01-27 10:02:56', NULL, NULL),
	(5, 'amar hamzey', 'amar', '081343553656', 'amarhzy@gmail.com', '$2y$10$SPhmy4VzrN.rtSA6fH4C8ud6mnMsJ39ODLUZKKDgCndWy5pHWrNSy', 'panitia', '2026-01-27 10:04:59', NULL, NULL),
	(8, 'shibto mikazuki', 'mikazuki', '081223943375', 'shibitomikadzuki@gmail.com', '$2y$10$eQMNyeD0K9wUUp14d3pKIeKQwMwXLdFp4kc1w/JF7xenaTgMAe.O6', 'pendaftar', '2026-01-31 11:33:44', '8c76c091892b32d355c6ad9a4b8f24d35f1ce41679301bc14aa44161ad651be3', '2026-01-31 13:12:11'),
	(12, 'Haikal Admin', 'haikaladmin', '085210449458', 'haikaldewa@gmail.com', '$2y$10$et4vRLbI/DM1w4SYDoFKxOdjAL58YckHiHQhhgoWESBY9kN/MXoem', 'admin', '2026-05-17 06:58:48', NULL, NULL),
	(13, 'Haikal Panitia', 'haikalpanitia', '085210449458', 'haikaldewa11@gmail.com', '$2y$10$5kCfsbKdtDP6qw7vDZm8huTndECVMIypJs2.zG6S9lPURi/F9dsXi', 'panitia', '2026-05-17 07:49:17', NULL, NULL),
	(14, 'Haikal Dewanata', 'haikal', '085210449458', 'haikaldewa98@gmail.com', '$2y$10$NFsrKNukINAHS4H8vht3juAMGndJp4fvtqmpfBW7.O7gNDOSIqVfS', 'pendaftar', '2026-05-17 07:50:37', NULL, NULL),
	(15, 'alfan bekti', 'alfanbekti', '081227615406', 'alfanbekti@gmail.com', '$2y$10$QCdL7ZUFLGIAq0CoBUlPUe5zXLpZoD1uFZKORTaNy59mtJQYefdVi', 'pendaftar', '2026-10-19 09:45:33', NULL, NULL),
	(16, 'uciha shibito', 'ucihashibito', '081227615406', 'ucihashibito@gmail.com', '$2y$10$fAORg1ZLYWg7jjqPJ.yDQ.slxiZfxYXWM2g.mEGQsmahMdvRlGxUC', 'admin', '2026-10-19 10:19:48', '5f5fbfd60510eed60f778de40fc2bd3742bb99a2ebb819578d9ade06b6909c79', '2026-10-21 07:24:29');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
