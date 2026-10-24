-- ============================================================
-- FILE DUMMY DATABASE: dbn_perpustakaan
-- PROYEK: Perpustakaan Digital Sekolah (Tema Flora)
-- TUGAS UKK REKAYASA PERANGKAT LUNAK (PAKET 4)
-- CARA PAKAI:
-- 1. Buka http://localhost/phpmyadmin/
-- 2. Klik tab "SQL", lalu salin (paste) seluruh isi file ini, lalu klik "Kirim" / "Go"
--    ATAU Import file ini langsung melalui menu "Import"
-- ============================================================

-- 1. Buat dan Pilih Database
CREATE DATABASE IF NOT EXISTS `dbn_perpustakaan` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `dbn_perpustakaan`;

-- Matikan foreign key check sementara agar proses pembuatan tabel berjalan mulus
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 2. TABEL KATEGORI BUKU
-- ============================================================
DROP TABLE IF EXISTS `kategori`;
CREATE TABLE `kategori` (
  `id_kategori` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` VARCHAR(50) NOT NULL,
  `deskripsi` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Dummy Kategori
INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Teknologi & RPL', 'Buku panduan coding, web development, sistem basis data, dan rekayasa perangkat lunak'),
(2, 'Flora & Sains Alam', 'Buku botani, tanaman hias, obat herbal nusantara, biologi dan kelestarian alam'),
(3, 'Bahasa & Sastra', 'Kumpulan karya sastra, novel inspiratif, puisi, dan tata bahasa'),
(4, 'Sejarah & Sosial', 'Catatan sejarah bangsa, peradaban dunia, serta nilai sosial kemasyarakatan'),
(5, 'Pengembangan Diri', 'Buku motivasi belajar, manajemen waktu, kepemimpinan, dan produktivitas');

-- ============================================================
-- 3. TABEL USERS (ADMIN, PETUGAS & SISWA)
-- ============================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id_user` INT(11) NOT NULL AUTO_INCREMENT,
  `nis_nip` VARCHAR(30) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `level` ENUM('admin','siswa') NOT NULL DEFAULT 'siswa',
  `kelas` VARCHAR(20) DEFAULT NULL,
  `jurusan` VARCHAR(50) DEFAULT NULL,
  `telepon` VARCHAR(20) DEFAULT NULL,
  `alamat` TEXT DEFAULT NULL,
  `status_aktif` ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Dummy Pengguna
-- Password dienkripsi dengan MD5:
-- admin: admin123  |  siswa, budi, siti, rizky: siswa123
INSERT INTO `users` (`id_user`, `nis_nip`, `nama_lengkap`, `username`, `password`, `level`, `kelas`, `jurusan`, `telepon`, `alamat`, `status_aktif`) VALUES
(1, '198701012010011001', 'Admin Perpustakaan Flora', 'admin', MD5('admin123'), 'admin', '-', 'Petugas Perpustakaan', '081234567890', 'Ruang Perpustakaan Gedung A', 'aktif'),
(2, '2024101001', 'Nesya Aurelia', 'siswa', MD5('siswa123'), 'siswa', 'XII RPL 1', 'Rekayasa Perangkat Lunak', '085712345678', 'Jl. Flamboyan No. 12', 'aktif'),
(3, '2024101002', 'Budi Pratama', 'budi', MD5('siswa123'), 'siswa', 'XII RPL 1', 'Rekayasa Perangkat Lunak', '085811223344', 'Jl. Melati Indah No. 05', 'aktif'),
(4, '2024101003', 'Siti Nurhaliza', 'siti', MD5('siswa123'), 'siswa', 'XII RPL 2', 'Rekayasa Perangkat Lunak', '085933445566', 'Jl. Kenanga Asri No. 18', 'aktif'),
(5, '2024101004', 'Rizky Ramadhan', 'rizky', MD5('siswa123'), 'siswa', 'XI RPL 1', 'Rekayasa Perangkat Lunak', '081377889900', 'Jl. Cempaka Putih No. 03', 'aktif');

-- ============================================================
-- 4. TABEL BUKU
-- ============================================================
DROP TABLE IF EXISTS `buku`;
CREATE TABLE `buku` (
  `id_buku` INT(11) NOT NULL AUTO_INCREMENT,
  `kode_buku` VARCHAR(20) NOT NULL UNIQUE,
  `judul` VARCHAR(150) NOT NULL,
  `id_kategori` INT(11) NOT NULL,
  `pengarang` VARCHAR(100) NOT NULL,
  `penerbit` VARCHAR(100) NOT NULL,
  `tahun_terbit` INT(4) NOT NULL,
  `isbn` VARCHAR(30) DEFAULT NULL,
  `jumlah_buku` INT(5) NOT NULL DEFAULT 1,
  `sisa_buku` INT(5) NOT NULL DEFAULT 1,
  `lokasi_rak` VARCHAR(30) NOT NULL DEFAULT 'Rak A1',
  `deskripsi` TEXT DEFAULT NULL,
  `gambar` VARCHAR(255) DEFAULT 'default-book.png',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_buku`),
  KEY `id_kategori` (`id_kategori`),
  CONSTRAINT `fk_buku_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Dummy Buku
INSERT INTO `buku` (`id_buku`, `kode_buku`, `judul`, `id_kategori`, `pengarang`, `penerbit`, `tahun_terbit`, `isbn`, `jumlah_buku`, `sisa_buku`, `lokasi_rak`, `deskripsi`, `gambar`) VALUES
(1, 'BK-RPL-001', 'Pemrograman Web Dasar & PHP MySQL', 1, 'Budi Raharjo', 'Informatika Bandung', 2022, '978-602-1234-01-1', 10, 8, 'Rak RPL-01', 'Panduan komprehensif membangun website responsif dengan PHP native, HTML5, CSS3, dan basis data MySQL.', 'default-book.png'),
(2, 'BK-FLR-002', 'Pesona Flora Nusantara & Tanaman Herbal', 2, 'Dr. Ratna Kartika', 'Pustaka Hijau Alam', 2021, '978-602-5678-02-2', 8, 7, 'Rak BIO-03', 'Eksplorasi tanaman hijau, flora endemik Indonesia, serta pemanfaatan tanaman herbal untuk kesehatan alami.', 'default-book.png'),
(3, 'BK-RPL-003', 'Algoritma & Struktur Data untuk Siswa RPL', 1, 'Ir. Hendra Kusuma', 'Erlangga Teknika', 2023, '978-602-7890-03-3', 12, 12, 'Rak RPL-02', 'Dasar logika berpikir komputasional, flowchart sistem, pseudocode, array, dan implementasi program.', 'default-book.png'),
(4, 'BK-STR-004', 'Laskar Pelangi', 3, 'Andrea Hirata', 'Bentang Pustaka', 2020, '978-979-1227-04-4', 6, 6, 'Rak SAS-01', 'Kisah inspiratif perjuangan sepuluh anak di Belitung dalam menggapai mimpi melalui dunia pendidikan.', 'default-book.png'),
(5, 'BK-DIR-005', 'Atomic Habits (Edisi Bahasa Indonesia)', 5, 'James Clear', 'Gramedia Pustaka Utama', 2021, '978-602-0633-05-5', 7, 7, 'Rak PSI-02', 'Perubahan-perubahan kecil yang memberikan hasil luar biasa dalam membangun kebiasaan produktif.', 'default-book.png'),
(6, 'BK-FLR-006', 'Seni Merawat Bonsai dan Sukulen Rumah', 2, 'Dewi Sartika', 'Agromedia Pustaka', 2023, '978-602-4455-06-6', 5, 5, 'Rak BIO-04', 'Teknik penyiraman, pemupukan, pencahayaan, serta perawatan tanaman hias indoor minimalis.', 'default-book.png'),
(7, 'BK-RPL-007', 'Desain Basis Data Relasional dengan MySQL', 1, 'Wahana Komputer', 'Andi Offset', 2022, '978-979-2900-07-7', 9, 9, 'Rak RPL-03', 'Normalisasi tabel basis data (1NF, 2NF, 3NF), relasi tabel foreign key, query join, dan indexing.', 'default-book.png'),
(8, 'BK-SEJ-008', 'Sejarah Peradaban Dunia Kuno', 4, 'Prof. Wardiman', 'Balai Pustaka', 2019, '978-602-3344-08-8', 4, 4, 'Rak SEJ-01', 'Eksplorasi peradaban Mesopotamia, Mesir Kuno, Yunani, dan jalur sutra perdagangan dunia.', 'default-book.png');

-- ============================================================
-- 5. TABEL TRANSAKSI (PEMINJAMAN & PENGEMBALIAN)
-- ============================================================
DROP TABLE IF EXISTS `transaksi`;
CREATE TABLE `transaksi` (
  `id_transaksi` INT(11) NOT NULL AUTO_INCREMENT,
  `kode_transaksi` VARCHAR(30) NOT NULL UNIQUE,
  `id_user` INT(11) NOT NULL,
  `id_buku` INT(11) NOT NULL,
  `tgl_pinjam` DATE NOT NULL,
  `tgl_jatuh_tempo` DATE NOT NULL,
  `tgl_kembali` DATE DEFAULT NULL,
  `status` ENUM('menunggu','dipinjam','dikembalikan','ditolak') NOT NULL DEFAULT 'dipinjam',
  `denda` INT(11) NOT NULL DEFAULT 0,
  `catatan` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_transaksi`),
  KEY `id_user` (`id_user`),
  KEY `id_buku` (`id_buku`),
  CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_buku` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Dummy Transaksi untuk Demonstrasi Pengujian
INSERT INTO `transaksi` (`id_transaksi`, `kode_transaksi`, `id_user`, `id_buku`, `tgl_pinjam`, `tgl_jatuh_tempo`, `tgl_kembali`, `status`, `denda`, `catatan`) VALUES
-- 1. Transaksi Aktif Masih Dalam Batas Waktu
(1, 'TRX-20261020-001', 2, 1, '2026-10-20', '2026-10-27', NULL, 'dipinjam', 0, 'Peminjaman buku modul praktikum UKK RPL'),
-- 2. Transaksi Aktif Terlambat (Untuk Demo Denda di depan Penguji)
(2, 'TRX-20261010-002', 3, 2, '2026-10-10', '2026-10-17', NULL, 'dipinjam', 0, 'Referensi tugas botani lingkungan'),
-- 3. Transaksi yang Sudah Selesai Dikembalikan (Tepat Waktu)
(3, 'TRX-20261001-003', 4, 3, '2026-10-01', '2026-10-08', '2026-10-07', 'dikembalikan', 0, 'Buku dikembalikan dalam kondisi sangat baik'),
-- 4. Transaksi yang Sudah Selesai Dikembalikan (Terlambat 2 hari, denda Rp 2.000)
(4, 'TRX-20261005-004', 5, 1, '2026-10-05', '2026-10-12', '2026-10-14', 'dikembalikan', 2000, 'Terlambat 2 hari, denda sudah dibayar lunas');

-- Nyalakan kembali foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
