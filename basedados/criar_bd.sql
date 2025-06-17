-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 17, 2025 at 07:44 PM
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
-- Table structure for table `alerta`
--

DROP TABLE IF EXISTS `alerta`;
CREATE TABLE `alerta` (
  `id_alerta` int(100) NOT NULL,
  `descricao` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alerta_utilizador`
--

DROP TABLE IF EXISTS `alerta_utilizador`;
CREATE TABLE `alerta_utilizador` (
  `id_utilizador` int(100) NOT NULL,
  `id_alerta` int(100) NOT NULL,
  `data` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `autocarro`
--

DROP TABLE IF EXISTS `autocarro`;
CREATE TABLE `autocarro` (
  `id_autocarro` int(100) NOT NULL,
  `lugares` int(100) NOT NULL,
  `nome_motorista` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `autocarro`
--

INSERT INTO `autocarro` (`id_autocarro`, `lugares`, `nome_motorista`) VALUES
(1, 65, 'Manel Manobras'),
(2, 65, 'Ernesto Pirilampo'),
(3, 65, 'Pedro Pladour');

-- --------------------------------------------------------

--
-- Table structure for table `bilhete`
--

DROP TABLE IF EXISTS `bilhete`;
CREATE TABLE `bilhete` (
  `id_bilhete` int(100) NOT NULL,
  `id_viagem` int(100) NOT NULL,
  `id_utilizador` int(100) NOT NULL,
  `data_compra` datetime(6) NOT NULL,
  `identificador` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bilhete`
--

INSERT INTO `bilhete` (`id_bilhete`, `id_viagem`, `id_utilizador`, `data_compra`, `identificador`) VALUES
(1, 1, 1, '2025-06-13 13:36:30.000000', 'BILHETE_684c1b4e40ada'),
(2, 1, 1, '2025-06-13 13:36:39.000000', 'BILHETE_684c1b575fddb'),
(3, 1, 1, '2025-06-13 14:05:17.000000', 'BILHETE_684c220d775d5'),
(4, 1, 1, '2025-06-13 14:33:02.000000', 'BILHETE_684c288e27a0d'),
(9, 1, 16, '2025-06-17 17:28:24.000000', 'BILHETE_685197a8d28ac'),
(10, 1, 16, '2025-06-17 17:39:18.000000', 'BILHETE_68519a368baff');

-- --------------------------------------------------------

--
-- Table structure for table `carteira`
--

DROP TABLE IF EXISTS `carteira`;
CREATE TABLE `carteira` (
  `id_carteira` int(100) NOT NULL,
  `saldo` decimal(65,3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carteira`
--

INSERT INTO `carteira` (`id_carteira`, `saldo`) VALUES
(1, 9999980.000),
(2, 230.000),
(3, 0.000),
(4, 0.000),
(5, 0.000),
(6, 0.000),
(7, 0.000);

-- --------------------------------------------------------

--
-- Table structure for table `carteira_log`
--

DROP TABLE IF EXISTS `carteira_log`;
CREATE TABLE `carteira_log` (
  `id_carteira_log` int(100) NOT NULL,
  `id_carteira` int(100) NOT NULL,
  `id_operacao` int(100) NOT NULL,
  `data` datetime(6) NOT NULL,
  `montante` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carteira_log`
--

INSERT INTO `carteira_log` (`id_carteira_log`, `id_carteira`, `id_operacao`, `data`, `montante`) VALUES
(1, 2, 1, '2025-06-10 19:07:01.000000', 10),
(2, 2, 1, '2025-06-10 19:07:14.000000', 10),
(3, 2, 2, '2025-06-10 19:08:04.000000', 20),
(4, 2, 1, '2025-06-10 20:03:36.000000', 40),
(5, 2, 1, '2025-06-12 16:31:03.000000', 50),
(6, 2, 2, '2025-06-12 16:31:27.000000', 20),
(7, 2, 1, '2025-06-12 17:07:31.000000', 10),
(8, 1, 1, '2025-06-12 18:46:25.000000', 10),
(9, 1, 2, '2025-06-12 18:46:47.000000', 9),
(10, 2, 1, '2025-06-12 18:50:13.000000', 11),
(11, 2, 2, '2025-06-12 18:51:21.000000', 1),
(12, 1, 2, '2025-06-13 13:36:30.000000', 15),
(13, 1, 2, '2025-06-13 13:36:39.000000', 15),
(14, 1, 2, '2025-06-13 14:05:17.000000', 15),
(15, 1, 3, '2025-06-13 14:33:02.000000', 15),
(16, 1, 1, '2025-06-13 14:37:12.000000', 10),
(17, 1, 1, '2025-06-13 14:40:21.000000', 10),
(18, 1, 2, '2025-06-13 14:40:25.000000', 10),
(19, 1, 1, '2025-06-13 14:43:48.000000', 10),
(20, 1, 2, '2025-06-13 14:43:51.000000', 10),
(21, 1, 2, '2025-06-13 14:43:55.000000', 1000),
(22, 1, 1, '2025-06-13 14:43:58.000000', 1000),
(23, 2, 1, '2025-06-14 23:28:07.000000', 100),
(24, 2, 2, '2025-06-14 23:28:12.000000', 50),
(25, 2, 1, '2025-06-14 23:30:06.000000', 100),
(26, 2, 4, '2025-06-14 23:30:21.000000', 15),
(27, 1, 3, '2025-06-14 23:30:21.000000', 15),
(28, 2, 4, '2025-06-17 15:51:21.000000', 15),
(29, 1, 3, '2025-06-17 15:51:21.000000', 15),
(30, 2, 3, '2025-06-17 15:53:03.000000', 15),
(31, 1, 4, '2025-06-17 15:53:03.000000', 15),
(32, 2, 3, '2025-06-17 16:11:14.000000', 17),
(33, 1, 4, '2025-06-17 16:11:14.000000', 17),
(34, 2, 3, '2025-06-17 16:58:31.000000', 15),
(35, 1, 4, '2025-06-17 16:58:31.000000', 15),
(36, 2, 1, '2025-06-17 16:58:41.000000', 10),
(37, 2, 2, '2025-06-17 16:58:49.000000', 10),
(38, 2, 4, '2025-06-17 17:00:36.000000', 15),
(39, 1, 3, '2025-06-17 17:00:36.000000', 15),
(40, 2, 4, '2025-06-17 17:00:40.000000', 17),
(41, 1, 3, '2025-06-17 17:00:40.000000', 17),
(42, 2, 4, '2025-06-17 17:00:41.000000', 15),
(43, 1, 3, '2025-06-17 17:00:41.000000', 15),
(44, 2, 3, '2025-06-17 17:28:24.000000', 15),
(45, 1, 4, '2025-06-17 17:28:24.000000', 15),
(46, 2, 1, '2025-06-17 17:39:04.000000', 10),
(47, 2, 3, '2025-06-17 17:39:18.000000', 15),
(48, 1, 4, '2025-06-17 17:39:18.000000', 15);

-- --------------------------------------------------------

--
-- Table structure for table `localidade`
--

DROP TABLE IF EXISTS `localidade`;
CREATE TABLE `localidade` (
  `id_localidade` int(100) NOT NULL,
  `localidade` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `localidade`
--

INSERT INTO `localidade` (`id_localidade`, `localidade`) VALUES
(1, 'Aveiro'),
(2, 'Beja'),
(3, 'Braga'),
(4, 'Bragança'),
(5, 'Castelo Branco'),
(6, 'Coimbra'),
(7, 'Évora'),
(8, 'Faro'),
(9, 'Guarda'),
(10, 'Leiria'),
(11, 'Lisboa'),
(12, 'Portalegre'),
(13, 'Porto'),
(14, 'Santarém'),
(15, 'Setúbal'),
(16, 'Viana do Castelo'),
(17, 'Vila Real'),
(18, 'Viseu');

-- --------------------------------------------------------

--
-- Table structure for table `operacao`
--

DROP TABLE IF EXISTS `operacao`;
CREATE TABLE `operacao` (
  `id_operacao` int(100) NOT NULL,
  `descricao` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `operacao`
--

INSERT INTO `operacao` (`id_operacao`, `descricao`) VALUES
(1, 'Adicionar Saldo'),
(2, 'Retirar Saldo'),
(3, 'Comprar Bilhete'),
(4, 'Vender Bilhete');

-- --------------------------------------------------------

--
-- Table structure for table `rota`
--

DROP TABLE IF EXISTS `rota`;
CREATE TABLE `rota` (
  `id_rota` int(100) NOT NULL,
  `id_origem` int(100) NOT NULL,
  `id_destino` int(100) NOT NULL,
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rota`
--

INSERT INTO `rota` (`id_rota`, `id_origem`, `id_destino`, `estado`) VALUES
(1, 7, 10, 1),
(2, 2, 1, 1),
(3, 3, 18, 1),
(4, 9, 15, 1),
(5, 13, 10, 1),
(6, 18, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tipo_utilizador`
--

DROP TABLE IF EXISTS `tipo_utilizador`;
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

DROP TABLE IF EXISTS `utilizador`;
CREATE TABLE `utilizador` (
  `id_utilizador` int(100) NOT NULL,
  `password` varchar(500) NOT NULL,
  `nome_utilizador` varchar(20) NOT NULL,
  `nome` varchar(20) DEFAULT NULL,
  `morada` varchar(40) DEFAULT NULL,
  `telemovel` varchar(20) DEFAULT NULL,
  `tipo_utilizador` int(100) NOT NULL,
  `id_carteira` int(100) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `utilizador`
--

INSERT INTO `utilizador` (`id_utilizador`, `password`, `nome_utilizador`, `nome`, `morada`, `telemovel`, `tipo_utilizador`, `id_carteira`, `email`) VALUES
(1, '21232f297a57a5a743894a0e4a801fc3', 'admin', 'admin', 'Rua Boss Lande ', '100100100', 1, 1, 'admin@mail.com'),
(15, 'cc7a84634199040d54376793842fe035', 'funcionario', NULL, NULL, NULL, 2, 1, ''),
(16, '4983a0ab83ed86e0e7213c8783940193', 'cliente', 'cliente', 'Rua Landan Paulo', '912345678', 3, 2, 'cliente@mail.com'),
(17, '34b7da764b21d298ef307d04d8152dc5', 'tom', NULL, NULL, NULL, 5, 3, ''),
(18, 'd39c73f590d2ad95763720f728258cdd', 'martim', NULL, NULL, NULL, 5, 4, 'martim@ipcb.pt'),
(19, '202cb962ac59075b964b07152d234b70', 'hkalexandrexp6', 'Ernesto', 'rua do alexandre', '9394707733', 5, 5, 'alexandre123@gmail.com'),
(20, '0f759dd1ea6c4c76cedc299039ca4f23', 'leo', 'leo', 'leo', '912345678', 5, 6, 'leo@mail.com'),
(21, '500711d41246f7b9b5002f9893f66214', 'oda', NULL, NULL, NULL, 5, 7, '');

-- --------------------------------------------------------

--
-- Table structure for table `viagem`
--

DROP TABLE IF EXISTS `viagem`;
CREATE TABLE `viagem` (
  `id_viagem` int(100) NOT NULL,
  `id_rota` int(100) NOT NULL,
  `id_autocarro` int(100) NOT NULL,
  `data` date NOT NULL,
  `hora_chegada` time NOT NULL,
  `hora` time NOT NULL,
  `preco` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `viagem`
--

INSERT INTO `viagem` (`id_viagem`, `id_rota`, `id_autocarro`, `data`, `hora_chegada`, `hora`, `preco`) VALUES
(1, 1, 1, '2025-06-15', '16:45:00', '15:00:00', 15),
(2, 2, 2, '2025-06-16', '17:30:00', '15:00:00', 15),
(3, 3, 3, '2025-06-18', '22:00:00', '19:00:00', 16),
(4, 4, 1, '2025-06-19', '12:00:00', '09:00:00', 15),
(5, 5, 2, '2025-06-18', '22:45:00', '21:00:00', 17),
(6, 6, 3, '2025-06-27', '00:30:00', '22:00:00', 20);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alerta`
--
ALTER TABLE `alerta`
  ADD PRIMARY KEY (`id_alerta`);

--
-- Indexes for table `alerta_utilizador`
--
ALTER TABLE `alerta_utilizador`
  ADD KEY `id_utilizador` (`id_utilizador`,`id_alerta`),
  ADD KEY `id_alerta_fk` (`id_alerta`);

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
-- Indexes for table `carteira`
--
ALTER TABLE `carteira`
  ADD PRIMARY KEY (`id_carteira`);

--
-- Indexes for table `carteira_log`
--
ALTER TABLE `carteira_log`
  ADD PRIMARY KEY (`id_carteira_log`),
  ADD KEY `id_carteira` (`id_carteira`,`id_operacao`),
  ADD KEY `id_operacao_fk` (`id_operacao`);

--
-- Indexes for table `localidade`
--
ALTER TABLE `localidade`
  ADD PRIMARY KEY (`id_localidade`);

--
-- Indexes for table `operacao`
--
ALTER TABLE `operacao`
  ADD PRIMARY KEY (`id_operacao`);

--
-- Indexes for table `rota`
--
ALTER TABLE `rota`
  ADD PRIMARY KEY (`id_rota`),
  ADD KEY `id_origem` (`id_origem`),
  ADD KEY `id_destino` (`id_destino`);

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
  ADD KEY `tipo_utilizador` (`tipo_utilizador`),
  ADD KEY `id_carteira` (`id_carteira`),
  ADD KEY `id_carteira_utilizador` (`id_carteira`);

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
-- AUTO_INCREMENT for table `alerta`
--
ALTER TABLE `alerta`
  MODIFY `id_alerta` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `autocarro`
--
ALTER TABLE `autocarro`
  MODIFY `id_autocarro` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bilhete`
--
ALTER TABLE `bilhete`
  MODIFY `id_bilhete` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `carteira`
--
ALTER TABLE `carteira`
  MODIFY `id_carteira` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2025000004;

--
-- AUTO_INCREMENT for table `carteira_log`
--
ALTER TABLE `carteira_log`
  MODIFY `id_carteira_log` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `localidade`
--
ALTER TABLE `localidade`
  MODIFY `id_localidade` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `operacao`
--
ALTER TABLE `operacao`
  MODIFY `id_operacao` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rota`
--
ALTER TABLE `rota`
  MODIFY `id_rota` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tipo_utilizador`
--
ALTER TABLE `tipo_utilizador`
  MODIFY `id_tipo_utilizador` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `utilizador`
--
ALTER TABLE `utilizador`
  MODIFY `id_utilizador` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `viagem`
--
ALTER TABLE `viagem`
  MODIFY `id_viagem` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alerta_utilizador`
--
ALTER TABLE `alerta_utilizador`
  ADD CONSTRAINT `id_alerta_fk` FOREIGN KEY (`id_alerta`) REFERENCES `alerta` (`id_alerta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_utilizador_fk` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizador` (`id_utilizador`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `bilhete`
--
ALTER TABLE `bilhete`
  ADD CONSTRAINT `id_utilizador` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizador` (`id_utilizador`),
  ADD CONSTRAINT `id_viagem_fk` FOREIGN KEY (`id_viagem`) REFERENCES `viagem` (`id_viagem`);

--
-- Constraints for table `carteira_log`
--
ALTER TABLE `carteira_log`
  ADD CONSTRAINT `carteira_log_ibfk_1` FOREIGN KEY (`id_carteira`) REFERENCES `carteira` (`id_carteira`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_operacao_fk` FOREIGN KEY (`id_operacao`) REFERENCES `operacao` (`id_operacao`);

--
-- Constraints for table `rota`
--
ALTER TABLE `rota`
  ADD CONSTRAINT `id_destino_fk` FOREIGN KEY (`id_destino`) REFERENCES `localidade` (`id_localidade`),
  ADD CONSTRAINT `id_origem_fk` FOREIGN KEY (`id_origem`) REFERENCES `localidade` (`id_localidade`);

--
-- Constraints for table `utilizador`
--
ALTER TABLE `utilizador`
  ADD CONSTRAINT `id_carteira_fk` FOREIGN KEY (`id_carteira`) REFERENCES `carteira` (`id_carteira`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tipo_utilizador_fk` FOREIGN KEY (`tipo_utilizador`) REFERENCES `tipo_utilizador` (`id_tipo_utilizador`) ON DELETE CASCADE ON UPDATE CASCADE;

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
