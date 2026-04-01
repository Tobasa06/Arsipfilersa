CREATE DATABASE IF NOT EXISTS db_earsip_rsa;
USE db_earsip_rsa;

-- Tabel Users
CREATE TABLE `users` (
  `id_user` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','user') NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_user`)
);

-- Tabel Kategori Arisip
CREATE TABLE `kategori` (
  `id_kategori` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_kategori`)
);

-- Tabel File Arsip (Relasi ke Kategori dan User Uploader)
CREATE TABLE `arsip` (
  `id_arsip` INT(11) NOT NULL AUTO_INCREMENT,
  `id_kategori` INT(11) NOT NULL,
  `id_user` INT(11) NOT NULL,
  `judul_arsip` VARCHAR(150) NOT NULL,
  `deskripsi` TEXT,
  `nama_file_enkripsi` VARCHAR(255) NOT NULL,
  `tanggal_upload` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_arsip`),
  FOREIGN KEY (`id_kategori`) REFERENCES `kategori`(`id_kategori`) ON DELETE CASCADE,
  FOREIGN KEY (`id_user`) REFERENCES `users`(`id_user`) ON DELETE CASCADE
);

-- ========= INSERT DATA DEFAULT MOCKUP =========

-- Buat 1 Admin dan 1 User default dengan password standar "password" (di hash bcrypt)
INSERT INTO `users` (`username`, `password`, `role`, `nama_lengkap`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Bapak Administrator'),
('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'Pegawai Loket');

-- 4 Klasifikasi Arsip (Sesuai Permintaan)
INSERT INTO `kategori` (`nama_kategori`) VALUES
('E-Arsip Administrasi Desa'),
('E-Arsip Kependudukan'),
('E-Arsip Pelayanan Masyarakat'),
('E-Arsip Pembangunan & Keuangan');
