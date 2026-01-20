-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 20, 2026 at 01:09 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_tagihan_listrik`
--

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `pesan` text NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('dibaca','belum_dibaca') DEFAULT 'belum_dibaca'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `pesan`, `tanggal`, `status`) VALUES
(1, 2, 'Sistemnya sangat membantu, terima kasih!', '2024-06-01', 'dibaca'),
(2, 3, 'Minta tolong perbaiki tampilan mobile', '2024-06-05', 'belum_dibaca'),
(3, 4, 'Pelayanan cepat dan ramah', '2024-06-10', 'dibaca');

-- --------------------------------------------------------

--
-- Table structure for table `konsumen`
--

CREATE TABLE `konsumen` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nomor_kwh` varchar(20) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `daya` int(11) NOT NULL,
  `tarif_per_kwh` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `konsumen`
--

INSERT INTO `konsumen` (`id`, `user_id`, `nomor_kwh`, `nama_pelanggan`, `alamat`, `daya`, `tarif_per_kwh`) VALUES
(1, 2, 'KWH0012345', 'Budi Santoso', 'Jl. Merdeka No. 123, Jakarta', 1300, 1444.70),
(2, 3, 'KWH0012346', 'Sari Dewi', 'Jl. Sudirman No. 45, Bandung', 2200, 1444.70),
(3, 4, 'KWH0012347', 'Rudi Hartono', 'Jl. Thamrin No. 67, Surabaya', 900, 605.00);

-- --------------------------------------------------------

--
-- Table structure for table `pemakaian`
--

CREATE TABLE `pemakaian` (
  `id` int(11) NOT NULL,
  `konsumen_id` int(11) NOT NULL,
  `bulan` varchar(20) NOT NULL,
  `tahun` year(4) NOT NULL,
  `meter_awal` int(11) NOT NULL,
  `meter_akhir` int(11) NOT NULL,
  `tanggal_catat` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemakaian`
--

INSERT INTO `pemakaian` (`id`, `konsumen_id`, `bulan`, `tahun`, `meter_awal`, `meter_akhir`, `tanggal_catat`) VALUES
(1, 1, 'Januari', '2024', 500, 650, '2024-01-25'),
(2, 1, 'Februari', '2024', 650, 800, '2024-02-25'),
(3, 1, 'Maret', '2024', 800, 950, '2024-03-25'),
(4, 2, 'Januari', '2024', 1000, 1200, '2024-01-25'),
(5, 2, 'Februari', '2024', 1200, 1400, '2024-02-25'),
(6, 2, 'Maret', '2024', 1400, 1600, '2024-03-25'),
(7, 3, 'Januari', '2024', 800, 900, '2024-01-25'),
(8, 3, 'Februari', '2024', 900, 1000, '2024-02-25'),
(9, 3, 'Maret', '2024', 1000, 1100, '2024-03-25'),
(10, 3, 'Maret', '2026', 150, 450, '2026-01-19');

-- --------------------------------------------------------

--
-- Table structure for table `tagihan`
--

CREATE TABLE `tagihan` (
  `id` int(11) NOT NULL,
  `pemakaian_id` int(11) DEFAULT NULL,
  `total_pemakaian` int(11) NOT NULL,
  `total_bayar` decimal(15,2) NOT NULL,
  `status` enum('belum_bayar','lunas') DEFAULT 'belum_bayar',
  `tanggal_bayar` date DEFAULT NULL,
  `tanggal_jatuh_tempo` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tagihan`
--

INSERT INTO `tagihan` (`id`, `pemakaian_id`, `total_pemakaian`, `total_bayar`, `status`, `tanggal_bayar`, `tanggal_jatuh_tempo`) VALUES
(1, 1, 150, 216705.00, 'lunas', '2024-02-05', '2024-02-10'),
(2, 2, 150, 216705.00, 'lunas', '2024-03-08', '2024-03-10'),
(3, 3, 150, 216705.00, 'belum_bayar', NULL, '2024-04-10'),
(4, 4, 200, 288940.00, 'lunas', '2024-02-06', '2024-02-10'),
(5, 5, 200, 288940.00, 'belum_bayar', NULL, '2024-03-10'),
(6, 6, 200, 288940.00, 'lunas', '2026-01-20', '2024-04-10'),
(7, 7, 100, 60500.00, 'lunas', '2024-02-04', '2024-02-10'),
(8, 8, 100, 60500.00, 'belum_bayar', NULL, '2024-03-10'),
(9, 9, 100, 60500.00, 'belum_bayar', NULL, '2024-04-10'),
(10, 10, 300, 181500.00, 'belum_bayar', NULL, '2026-02-08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','konsumen') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Sistem', 'admin', '2026-01-19 02:18:07'),
(2, 'user123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', 'konsumen', '2026-01-19 02:18:07'),
(3, 'user456', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sari Dewi', 'konsumen', '2026-01-19 02:18:07'),
(4, 'user789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rudi Hartono', 'konsumen', '2026-01-19 02:18:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_feedback_status` (`status`);

--
-- Indexes for table `konsumen`
--
ALTER TABLE `konsumen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_kwh` (`nomor_kwh`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_konsumen_user_id` (`user_id`);

--
-- Indexes for table `pemakaian`
--
ALTER TABLE `pemakaian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pemakaian_konsumen` (`konsumen_id`);

--
-- Indexes for table `tagihan`
--
ALTER TABLE `tagihan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pemakaian_id` (`pemakaian_id`),
  ADD KEY `idx_tagihan_status` (`status`),
  ADD KEY `idx_tagihan_tanggal` (`tanggal_jatuh_tempo`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_users_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `konsumen`
--
ALTER TABLE `konsumen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pemakaian`
--
ALTER TABLE `pemakaian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tagihan`
--
ALTER TABLE `tagihan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `konsumen`
--
ALTER TABLE `konsumen`
  ADD CONSTRAINT `konsumen_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pemakaian`
--
ALTER TABLE `pemakaian`
  ADD CONSTRAINT `pemakaian_ibfk_1` FOREIGN KEY (`konsumen_id`) REFERENCES `konsumen` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tagihan`
--
ALTER TABLE `tagihan`
  ADD CONSTRAINT `tagihan_ibfk_1` FOREIGN KEY (`pemakaian_id`) REFERENCES `pemakaian` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
