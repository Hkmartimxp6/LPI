-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 12-Jun-2025 às 14:19
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `felixbus`
--
CREATE DATABASE IF NOT EXISTS `felixbus` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `felixbus`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `alerta`
--

CREATE TABLE `alerta` (
  `id_alerta` int(100) NOT NULL,
  `descricao` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `alerta_utilizador`
--

CREATE TABLE `alerta_utilizador` (
  `id_utilizador` int(100) NOT NULL,
  `id_alerta` int(100) NOT NULL,
  `data` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `autocarro`
--

CREATE TABLE `autocarro` (
  `id_autocarro` int(100) NOT NULL,
  `lugares` int(100) NOT NULL,
  `nome_motorista` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `autocarro`
--

INSERT INTO `autocarro` (`id_autocarro`, `lugares`, `nome_motorista`) VALUES
(1, 65, 'Manel Manobras'),
(2, 65, 'Ernesto Pirilampo'),
(3, 65, 'Pedro Pladour');

-- --------------------------------------------------------

--
-- Estrutura da tabela `bilhete`
--

CREATE TABLE `bilhete` (
  `id_bilhete` int(100) NOT NULL,
  `id_viagem` int(100) NOT NULL,
  `id_utilizador` int(100) NOT NULL,
  `data_compra` datetime(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `carteira`
--

CREATE TABLE `carteira` (
  `id_carteira` int(100) NOT NULL,
  `saldo` decimal(65,3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `carteira`
--

INSERT INTO `carteira` (`id_carteira`, `saldo`) VALUES
(1, 0.000),
(2, 50.000),
(3, 0.000),
(4, 0.000),
(5, 0.000);

-- --------------------------------------------------------

--
-- Estrutura da tabela `carteira_log`
--

CREATE TABLE `carteira_log` (
  `id_carteira_log` int(100) NOT NULL,
  `id_carteira` int(100) NOT NULL,
  `id_operacao` int(100) NOT NULL,
  `data` datetime(6) NOT NULL,
  `montante` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `carteira_log`
--

INSERT INTO `carteira_log` (`id_carteira_log`, `id_carteira`, `id_operacao`, `data`, `montante`) VALUES
(1, 2, 1, '2025-06-10 19:07:01.000000', 10),
(2, 2, 1, '2025-06-10 19:07:14.000000', 10),
(3, 2, 2, '2025-06-10 19:08:04.000000', 20),
(4, 2, 1, '2025-06-10 20:03:36.000000', 40);

-- --------------------------------------------------------

--
-- Estrutura da tabela `localidade`
--

CREATE TABLE `localidade` (
  `id_localidade` int(100) NOT NULL,
  `localidade` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `localidade`
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
-- Estrutura da tabela `operacao`
--

CREATE TABLE `operacao` (
  `id_operacao` int(100) NOT NULL,
  `descricao` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `operacao`
--

INSERT INTO `operacao` (`id_operacao`, `descricao`) VALUES
(1, 'Adicionar Saldo'),
(2, 'Retirar Saldo'),
(3, 'Comprar Bilhete'),
(4, 'Vender Bilhete');

-- --------------------------------------------------------

--
-- Estrutura da tabela `rota`
--

CREATE TABLE `rota` (
  `id_rota` int(100) NOT NULL,
  `id_origem` int(100) NOT NULL,
  `id_destino` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `rota`
--

INSERT INTO `rota` (`id_rota`, `id_origem`, `id_destino`) VALUES
(1, 15, 9),
(2, 10, 13),
(3, 3, 18),
(4, 9, 15),
(5, 13, 10),
(6, 18, 3);

-- --------------------------------------------------------

--
-- Estrutura da tabela `tipo_utilizador`
--

CREATE TABLE `tipo_utilizador` (
  `id_tipo_utilizador` int(100) NOT NULL,
  `descricao` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `tipo_utilizador`
--

INSERT INTO `tipo_utilizador` (`id_tipo_utilizador`, `descricao`) VALUES
(1, 'admin'),
(2, 'funcionario'),
(3, 'cliente'),
(4, 'cliente_nao_valido'),
(5, 'cliente_apagado');

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizador`
--

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
-- Extraindo dados da tabela `utilizador`
--

INSERT INTO `utilizador` (`id_utilizador`, `password`, `nome_utilizador`, `nome`, `morada`, `telemovel`, `tipo_utilizador`, `id_carteira`, `email`) VALUES
(1, '21232f297a57a5a743894a0e4a801fc3', 'admin', NULL, NULL, NULL, 1, 1, ''),
(15, 'cc7a84634199040d54376793842fe035', 'funcionario', NULL, NULL, NULL, 2, 1, ''),
(16, '4983a0ab83ed86e0e7213c8783940193', 'cliente', NULL, NULL, NULL, 3, 2, ''),
(17, '34b7da764b21d298ef307d04d8152dc5', 'tom', NULL, NULL, NULL, 3, 3, ''),
(18, 'd39c73f590d2ad95763720f728258cdd', 'martim', NULL, NULL, NULL, 3, 4, 'martim@ipcb.pt'),
(19, '202cb962ac59075b964b07152d234b70', 'hkalexandrexp6', 'Ernesto', 'rua do alexandre', '9394707733', 3, 5, 'alexandre123@gmail.com');

-- --------------------------------------------------------

--
-- Estrutura da tabela `viagem`
--

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
-- Extraindo dados da tabela `viagem`
--

INSERT INTO `viagem` (`id_viagem`, `id_rota`, `id_autocarro`, `data`, `hora_chegada`, `hora`, `preco`) VALUES
(1, 1, 1, '2025-06-15', '16:45:00', '15:00:00', 15),
(2, 2, 2, '2025-06-16', '17:30:00', '15:00:00', 15),
(3, 3, 3, '2025-06-18', '22:00:00', '19:00:00', 16),
(4, 4, 1, '2025-06-19', '12:00:00', '09:00:00', 15),
(5, 5, 2, '2025-06-18', '22:45:00', '21:00:00', 17),
(6, 6, 3, '2025-06-27', '00:30:00', '22:00:00', 20);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `alerta`
--
ALTER TABLE `alerta`
  ADD PRIMARY KEY (`id_alerta`);

--
-- Índices para tabela `alerta_utilizador`
--
ALTER TABLE `alerta_utilizador`
  ADD KEY `id_utilizador` (`id_utilizador`,`id_alerta`),
  ADD KEY `id_alerta_fk` (`id_alerta`);

--
-- Índices para tabela `autocarro`
--
ALTER TABLE `autocarro`
  ADD PRIMARY KEY (`id_autocarro`);

--
-- Índices para tabela `bilhete`
--
ALTER TABLE `bilhete`
  ADD PRIMARY KEY (`id_bilhete`),
  ADD KEY `id_viagem` (`id_viagem`),
  ADD KEY `id_utilizador` (`id_utilizador`);

--
-- Índices para tabela `carteira`
--
ALTER TABLE `carteira`
  ADD PRIMARY KEY (`id_carteira`);

--
-- Índices para tabela `carteira_log`
--
ALTER TABLE `carteira_log`
  ADD PRIMARY KEY (`id_carteira_log`),
  ADD KEY `id_carteira` (`id_carteira`,`id_operacao`),
  ADD KEY `id_operacao_fk` (`id_operacao`);

--
-- Índices para tabela `localidade`
--
ALTER TABLE `localidade`
  ADD PRIMARY KEY (`id_localidade`);

--
-- Índices para tabela `operacao`
--
ALTER TABLE `operacao`
  ADD PRIMARY KEY (`id_operacao`);

--
-- Índices para tabela `rota`
--
ALTER TABLE `rota`
  ADD PRIMARY KEY (`id_rota`),
  ADD KEY `id_origem` (`id_origem`),
  ADD KEY `id_destino` (`id_destino`);

--
-- Índices para tabela `tipo_utilizador`
--
ALTER TABLE `tipo_utilizador`
  ADD PRIMARY KEY (`id_tipo_utilizador`);

--
-- Índices para tabela `utilizador`
--
ALTER TABLE `utilizador`
  ADD PRIMARY KEY (`id_utilizador`),
  ADD UNIQUE KEY `nome_utilizador` (`nome_utilizador`),
  ADD KEY `tipo_utilizador` (`tipo_utilizador`),
  ADD KEY `id_carteira` (`id_carteira`),
  ADD KEY `id_carteira_utilizador` (`id_carteira`);

--
-- Índices para tabela `viagem`
--
ALTER TABLE `viagem`
  ADD PRIMARY KEY (`id_viagem`),
  ADD KEY `id_rota` (`id_rota`),
  ADD KEY `id_autocarro` (`id_autocarro`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alerta`
--
ALTER TABLE `alerta`
  MODIFY `id_alerta` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `autocarro`
--
ALTER TABLE `autocarro`
  MODIFY `id_autocarro` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `bilhete`
--
ALTER TABLE `bilhete`
  MODIFY `id_bilhete` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `carteira`
--
ALTER TABLE `carteira`
  MODIFY `id_carteira` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2025000004;

--
-- AUTO_INCREMENT de tabela `carteira_log`
--
ALTER TABLE `carteira_log`
  MODIFY `id_carteira_log` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `localidade`
--
ALTER TABLE `localidade`
  MODIFY `id_localidade` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `operacao`
--
ALTER TABLE `operacao`
  MODIFY `id_operacao` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `rota`
--
ALTER TABLE `rota`
  MODIFY `id_rota` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `tipo_utilizador`
--
ALTER TABLE `tipo_utilizador`
  MODIFY `id_tipo_utilizador` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `utilizador`
--
ALTER TABLE `utilizador`
  MODIFY `id_utilizador` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `viagem`
--
ALTER TABLE `viagem`
  MODIFY `id_viagem` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `alerta_utilizador`
--
ALTER TABLE `alerta_utilizador`
  ADD CONSTRAINT `id_alerta_fk` FOREIGN KEY (`id_alerta`) REFERENCES `alerta` (`id_alerta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_utilizador_fk` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizador` (`id_utilizador`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `bilhete`
--
ALTER TABLE `bilhete`
  ADD CONSTRAINT `id_utilizador` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizador` (`id_utilizador`),
  ADD CONSTRAINT `id_viagem_fk` FOREIGN KEY (`id_viagem`) REFERENCES `viagem` (`id_viagem`);

--
-- Limitadores para a tabela `carteira_log`
--
ALTER TABLE `carteira_log`
  ADD CONSTRAINT `carteira_log_ibfk_1` FOREIGN KEY (`id_carteira`) REFERENCES `carteira` (`id_carteira`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_operacao_fk` FOREIGN KEY (`id_operacao`) REFERENCES `operacao` (`id_operacao`);

--
-- Limitadores para a tabela `rota`
--
ALTER TABLE `rota`
  ADD CONSTRAINT `id_destino_fk` FOREIGN KEY (`id_destino`) REFERENCES `localidade` (`id_localidade`),
  ADD CONSTRAINT `id_origem_fk` FOREIGN KEY (`id_origem`) REFERENCES `localidade` (`id_localidade`);

--
-- Limitadores para a tabela `utilizador`
--
ALTER TABLE `utilizador`
  ADD CONSTRAINT `id_carteira_fk` FOREIGN KEY (`id_carteira`) REFERENCES `carteira` (`id_carteira`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tipo_utilizador_fk` FOREIGN KEY (`tipo_utilizador`) REFERENCES `tipo_utilizador` (`id_tipo_utilizador`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `viagem`
--
ALTER TABLE `viagem`
  ADD CONSTRAINT `id_autocaro_fk` FOREIGN KEY (`id_autocarro`) REFERENCES `autocarro` (`id_autocarro`),
  ADD CONSTRAINT `id_rota_fk` FOREIGN KEY (`id_rota`) REFERENCES `rota` (`id_rota`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
