-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20260503.e79a6a78f4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 21, 2026 at 12:14 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `studypath_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `durasi_target` int NOT NULL,
  `selesai` tinyint DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mapel`
--

CREATE TABLE `mapel` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `nama_mapel` varchar(50) NOT NULL,
  `tanggal_ujian` date NOT NULL,
  `tingkat_kesulitan` tinyint DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mapel`
--

INSERT INTO `mapel` (`id`, `user_id`, `nama_mapel`, `tanggal_ujian`, `tingkat_kesulitan`) VALUES
(1, 1, 'Bahasa Inggris', '2026-10-04', 2),
(2, 4, 'Software Engineering', '2026-10-04', 2),
(3, 1, 'Software Engineering', '2026-10-05', 2),
(4, 1, 'Matematika', '2026-10-05', 2);

-- --------------------------------------------------------

--
-- Table structure for table `sesi`
--

CREATE TABLE `sesi` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `durasi_menit` int NOT NULL,
  `catatan` text,
  `foto_bukti` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sesi`
--

INSERT INTO `sesi` (`id`, `user_id`, `mapel_id`, `tanggal`, `durasi_menit`, `catatan`, `foto_bukti`) VALUES
(1, 1, 1, '2026-10-04', 1, NULL, NULL),
(2, 4, 2, '2026-10-04', 2, NULL, NULL),
(3, 1, 3, '2026-10-05', 1, 'belajar bikin aplikasi di MIT App Inventor', NULL),
(4, 1, 1, '2026-10-05', 3, 'gatau', NULL),
(5, 1, 4, '2026-10-05', 3, '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(50) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profil_selesai` tinyint DEFAULT '0',
  `jam_fokus` varchar(20) DEFAULT NULL,
  `durasi_sesi` int DEFAULT NULL,
  `jenis_belajar` varchar(30) DEFAULT NULL,
  `penyebab_tunda` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `profil_selesai`, `jam_fokus`, `durasi_sesi`, `jenis_belajar`, `penyebab_tunda`) VALUES
(1, 'Fiola', 'fiolz', '$2y$10$SQnIT74PXDdqu43sUs5t2.0OWi9YqoVoHhbzobSntw/T8Ic824Asm', 0, NULL, NULL, NULL, NULL),
(2, 'Ola Keren Sedunia', 'olawell', '$2y$10$h3wgVagz5K74yVXzHTEmPOLCXEC493O1qIsWI/obrz5L0e7TrujRS', 0, NULL, NULL, NULL, NULL),
(3, 'bebek', 'bebek', '$2y$10$sOrQ8jVXyo78VRuBt9vdd.E9Kz07tUVtd.1l7un1dgrmjTl6Aotte', 0, NULL, NULL, NULL, NULL),
(4, 'ola', 'ola', '$2y$10$Ej0dNMJxZhYdbNGVIdfc7O6k8ETeIl44s3LNbi6TfcMv9eh4BwmMC', 0, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `mapel_id` (`mapel_id`);

--
-- Indexes for table `mapel`
--
ALTER TABLE `mapel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sesi`
--
ALTER TABLE `sesi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `mapel_id` (`mapel_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mapel`
--
ALTER TABLE `mapel`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sesi`
--
ALTER TABLE `sesi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mapel` (`id`);

--
-- Constraints for table `mapel`
--
ALTER TABLE `mapel`
  ADD CONSTRAINT `mapel_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `sesi`
--
ALTER TABLE `sesi`
  ADD CONSTRAINT `sesi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `sesi_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mapel` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
