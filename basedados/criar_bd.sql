-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2025 at 08:29 PM
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
-- Database: `felixbus`
--

CREATE DATABASE IF NOT EXISTS `felixbus` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `felixbus`;

-- --------------------------------------------------------

--
-- Table structure for table `autocarro`
--

CREATE TABLE `autocarro` (
  `id_autocarro` int(100) NOT NULL,
  `lugares` int(100) NOT NULL,
  `nome_motorista` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bilhete`
--

CREATE TABLE `bilhete` (
  `id_bilhete` int(100) NOT NULL,
  `id_viagem` int(100) NOT NULL,
  `id_utilizador` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rota`
--

CREATE TABLE `rota` (
  `id_rota` int(100) NOT NULL,
  `origem` varchar(100) NOT NULL,
  `destino` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipo_utilizador`
--

CREATE TABLE `tipo_utilizador` (
  `id_tipo_utilizador` int(100) NOT NULL,
  `descricao` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tipo_utilizador`
--

INSERT INTO `tipo_utilizador` (`id_tipo_utilizador`, `descricao`) VALUES
(1, 'admin'),
(2, 'funcionario'),
(3, 'cliente'),
(4, 'cliente_nao_valido'),
(5, 'cliente_apagado');

-- --------------------------------------------------------

--
-- Table structure for table `utilizador`
--

CREATE TABLE `utilizador` (
  `id_utilizador` int(100) NOT NULL,
  `password` varchar(20) NOT NULL,
  `nome_utilizador` varchar(20) NOT NULL,
  ` nome` varchar(20) DEFAULT NULL,
  `morada` varchar(40) DEFAULT NULL,
  `telemovel` varchar(20) DEFAULT NULL,
  `tipo_utilizador` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `utilizador`
--

INSERT INTO `utilizador` (`id_utilizador`, `password`, `nome_utilizador`, ` nome`, `morada`, `telemovel`, `tipo_utilizador`) VALUES
(1, 'admin', 'admin', NULL, NULL, NULL, 1),
(2, 'funcionario', 'funcionario', NULL, NULL, NULL, 2),
(3, 'cliente', 'cliente', NULL, NULL, NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `viagem`
--

CREATE TABLE `viagem` (
  `id_viagem` int(11) NOT NULL,
  `id_rota` int(11) NOT NULL,
  `id_autocarro` int(11) NOT NULL,
  `data` date NOT NULL,
  `hora` time NOT NULL,
  `preco` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `autocarro`
--
ALTER TABLE `autocarro`
  ADD PRIMARY KEY (`id_autocarro`);

--
-- Indexes for table `bilhete`
--
ALTER TABLE `bilhete`
  ADD PRIMARY KEY (`id_bilhete`),
  ADD KEY `id_viagem` (`id_viagem`),
  ADD KEY `id_utilizador` (`id_utilizador`);

--
-- Indexes for table `rota`
--
ALTER TABLE `rota`
  ADD PRIMARY KEY (`id_rota`);

--
-- Indexes for table `tipo_utilizador`
--
ALTER TABLE `tipo_utilizador`
  ADD PRIMARY KEY (`id_tipo_utilizador`);

--
-- Indexes for table `utilizador`
--
ALTER TABLE `utilizador`
  ADD PRIMARY KEY (`id_utilizador`),
  ADD UNIQUE KEY `nome_utilizador` (`nome_utilizador`),
  ADD KEY `tipo_utilizador` (`tipo_utilizador`);

--
-- Indexes for table `viagem`
--
ALTER TABLE `viagem`
  ADD PRIMARY KEY (`id_viagem`),
  ADD KEY `id_rota` (`id_rota`),
  ADD KEY `id_autocarro` (`id_autocarro`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `autocarro`
--
ALTER TABLE `autocarro`
  MODIFY `id_autocarro` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bilhete`
--
ALTER TABLE `bilhete`
  MODIFY `id_bilhete` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rota`
--
ALTER TABLE `rota`
  MODIFY `id_rota` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tipo_utilizador`
--
ALTER TABLE `tipo_utilizador`
  MODIFY `id_tipo_utilizador` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `utilizador`
--
ALTER TABLE `utilizador`
  MODIFY `id_utilizador` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `viagem`
--
ALTER TABLE `viagem`
  MODIFY `id_viagem` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bilhete`
--
ALTER TABLE `bilhete`
  ADD CONSTRAINT `id_utilizador` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizador` (`id_utilizador`),
  ADD CONSTRAINT `id_viagem_fk` FOREIGN KEY (`id_viagem`) REFERENCES `viagem` (`id_viagem`);

--
-- Constraints for table `utilizador`
--
ALTER TABLE `utilizador`
  ADD CONSTRAINT `tipo_utilizador_fk` FOREIGN KEY (`tipo_utilizador`) REFERENCES `tipo_utilizador` (`id_tipo_utilizador`);

--
-- Constraints for table `viagem`
--
ALTER TABLE `viagem`
  ADD CONSTRAINT `id_autocaro_fk` FOREIGN KEY (`id_autocarro`) REFERENCES `autocarro` (`id_autocarro`),
  ADD CONSTRAINT `id_rota_fk` FOREIGN KEY (`id_rota`) REFERENCES `rota` (`id_rota`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
