-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 07, 2020 at 01:49 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `har`
--

-- --------------------------------------------------------

--
-- Table structure for table `data_acc`
--

CREATE TABLE `data_acc` (
  `id_detailproject` int(11) DEFAULT NULL,
  `id_project` int(10) NOT NULL,
  `id_users` int(10) NOT NULL,
  `x` varchar(20) NOT NULL,
  `y` varchar(20) NOT NULL,
  `z` varchar(20) NOT NULL,
  `hrv` varchar(5) NOT NULL,
  `createdate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `data_gyro`
--

CREATE TABLE `data_gyro` (
  `id_detailproject` int(11) DEFAULT NULL,
  `id_project` int(10) NOT NULL,
  `id_users` int(10) NOT NULL,
  `x` varchar(20) NOT NULL,
  `y` varchar(20) NOT NULL,
  `z` varchar(20) NOT NULL,
  `hrv` varchar(5) NOT NULL,
  `createdate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `data_hrv`
--

CREATE TABLE `data_hrv` (
  `id_detailproject` int(11) DEFAULT NULL,
  `id_project` int(10) NOT NULL,
  `id_users` int(10) NOT NULL,
  `x` varchar(20) NOT NULL,
  `y` varchar(20) NOT NULL,
  `z` varchar(20) NOT NULL,
  `hrv` varchar(5) NOT NULL,
  `createdate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `detailproject`
--

CREATE TABLE `detailproject` (
  `id_detailproject` int(11) DEFAULT NULL,
  `id_project` int(10) NOT NULL,
  `id_users` int(11) NOT NULL,
  `gyro_x` varchar(30) NOT NULL,
  `gyro_y` varchar(30) NOT NULL,
  `gyro_z` varchar(30) NOT NULL,
  `acc_x` varchar(30) NOT NULL,
  `acc_y` varchar(30) NOT NULL,
  `acc_z` varchar(30) NOT NULL,
  `hrv` varchar(4) NOT NULL,
  `createdate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `masterproject`
--

CREATE TABLE `masterproject` (
  `id_project` int(11) NOT NULL,
  `nama_project` varchar(40) NOT NULL,
  `tipe` varchar(10) NOT NULL,
  `createdate` datetime NOT NULL,
  `create_by` int(10) NOT NULL,
  `insert_interval` int(10) DEFAULT NULL,
  `sync_interval` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `masterproject`
--

INSERT INTO `masterproject` (`id_project`, `nama_project`, `tipe`, `createdate`, `create_by`, `insert_interval`, `sync_interval`) VALUES
(1, 'Test HAR', 'HAR', '2020-08-07 10:58:39', 1, 10000, 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `createdate` datetime NOT NULL,
  `jenis_kelamin` varchar(10) NOT NULL,
  `usia` int(2) NOT NULL,
  `tgl_lahir` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `email`, `password`, `createdate`, `jenis_kelamin`, `usia`, `tgl_lahir`) VALUES
(1, 'tomi', 'tomi@gmail.com', '1234', '2020-08-06 22:37:26', 'Pria', 26, '2020-03-23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `masterproject`
--
ALTER TABLE `masterproject`
  ADD PRIMARY KEY (`id_project`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `masterproject`
--
ALTER TABLE `masterproject`
  MODIFY `id_project` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
