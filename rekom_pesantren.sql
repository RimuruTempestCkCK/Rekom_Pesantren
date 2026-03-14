-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 14 Mar 2026 pada 10.06
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rekom_pesantren`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `kriteria`
--

CREATE TABLE `kriteria` (
  `id` int(11) NOT NULL,
  `kode_kriteria` varchar(10) NOT NULL,
  `nama_kriteria` varchar(100) NOT NULL,
  `jenis` enum('benefit','cost') NOT NULL,
  `bobot` decimal(4,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kriteria`
--

INSERT INTO `kriteria` (`id`, `kode_kriteria`, `nama_kriteria`, `jenis`, `bobot`) VALUES
(1, 'C1', 'Biaya Pendidikan', 'cost', 0.25),
(2, 'C2', 'Jarak / Lokasi', 'cost', 0.20),
(3, 'C3', 'Fasilitas', 'benefit', 0.20),
(4, 'C4', 'Program Pendidikan', 'benefit', 0.20),
(5, 'C5', 'Jumlah Santri', 'benefit', 0.15);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pondok`
--

CREATE TABLE `pondok` (
  `id` int(11) NOT NULL,
  `nama_pondok` varchar(150) NOT NULL,
  `lokasi` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `nilai_biaya` tinyint(1) NOT NULL COMMENT 'C1: 1=Sangat Murah s/d 5=Sangat Mahal (Cost)',
  `nilai_jarak` tinyint(1) NOT NULL COMMENT 'C2: 1=Sangat Dekat s/d 5=Sangat Jauh (Cost)',
  `nilai_fasilitas` tinyint(1) NOT NULL COMMENT 'C3: 1=Sangat Kurang s/d 5=Sangat Lengkap (Benefit)',
  `nilai_program` tinyint(1) NOT NULL COMMENT 'C4: 1=Salaf, 2=Modern, 3=Tahfidz (Benefit)',
  `nilai_santri` tinyint(1) NOT NULL COMMENT 'C5: 1=<100, 2=100-300, 3=300-500, 4=500-700, 5=>700 (Benefit)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pondok`
--

INSERT INTO `pondok` (`id`, `nama_pondok`, `lokasi`, `deskripsi`, `foto`, `nilai_biaya`, `nilai_jarak`, `nilai_fasilitas`, `nilai_program`, `nilai_santri`) VALUES
(1, 'Pesantren Al-Hikmah', 'Kab. Kampar, Riau', 'Pesantren Tahfidz dengan program hafalan 30 juz dan kajian kitab kuning.', 'pondok_1773431199_577.png', 2, 3, 4, 3, 4),
(2, 'Pesantren Darul Ulum', 'Kota Pekanbaru, Riau', 'Pesantren Salaf fokus pendalaman ilmu fiqih, ushul fiqih, dan ilmu hadits.', 'pondok_1773431207_416.png', 3, 2, 4, 1, 4),
(3, 'Pesantren Nurul Falah', 'Kab. Siak, Riau', 'Pesantren Modern kurikulum terpadu ilmu agama dan umum setara SMP & SMA.', 'pondok_1773431214_144.png', 3, 3, 3, 2, 3),
(4, 'Pesantren Al-Ikhlas', 'Kab. Pelalawan, Riau', 'Pesantren Tahfidz dengan pembinaan akhlak dan karakter santri.', 'pondok_1773431224_475.png', 2, 4, 3, 3, 3),
(5, 'Pesantren Miftahul Huda', 'Kab. Indragiri Hulu, Riau', 'Pesantren Salaf tradisional metode kitab kuning dan sorogan.', 'pondok_1773431231_114.png', 1, 4, 2, 1, 3),
(6, 'Pesantren Raudlatul Ulum', 'Kota Dumai, Riau', 'Pesantren Modern fasilitas lengkap, asrama nyaman, pendidikan terpadu.', 'pondok_1773431238_808.png', 3, 3, 5, 2, 3),
(7, 'Pesantren Baitul Quran', 'Kab. Rokan Hulu, Riau', 'Pesantren Tahfidz putri dengan setoran hafalan harian dan bimbingan intensif.', 'pondok_1773431246_545.png', 2, 5, 3, 3, 2),
(8, 'Pesantren Darul Falah', 'Kab. Bengkalis, Riau', 'Pesantren Salaf dengan program keterampilan hidup dan wirausaha berbasis syariah.', 'pondok_1773431253_730.png', 1, 4, 2, 1, 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('admin','santri') NOT NULL DEFAULT 'santri',
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'Administrator', 'admin', 'admin', 'admin', 'aktif', '2026-03-14 01:56:58'),
(2, 'Ahmad Fauzi', 'santri1', 'santri1', 'santri', 'aktif', '2026-03-14 01:56:58'),
(3, 'Siti Aisyah', 'santri2', 'santri2', 'santri', 'aktif', '2026-03-14 01:56:58');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `kriteria`
--
ALTER TABLE `kriteria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_kriteria` (`kode_kriteria`);

--
-- Indeks untuk tabel `pondok`
--
ALTER TABLE `pondok`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `kriteria`
--
ALTER TABLE `kriteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `pondok`
--
ALTER TABLE `pondok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
