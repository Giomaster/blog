-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 25/01/2021 às 21:59
-- Versão do servidor: 10.4.13-MariaDB
-- Versão do PHP: 7.4.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `blog`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `Posts`
--

CREATE TABLE `Posts` (
  `id` bigint(20) NOT NULL,
  `title` text NOT NULL,
  `content` mediumtext NOT NULL,
  `userId` bigint(20) NOT NULL,
  `published` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Despejando dados para a tabela `Posts`
--

INSERT INTO `Posts` (`id`, `title`, `content`, `userId`, `published`, `updated`) VALUES
(561938672075000635, 'Shelter', 'Yeah, that is really pretty good', 333778300967882622, '2021-01-25 19:35:59', '2021-01-25 19:56:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Users`
--

CREATE TABLE `Users` (
  `id` bigint(20) NOT NULL,
  `displayName` tinytext NOT NULL,
  `email` tinytext NOT NULL,
  `password` varchar(6) NOT NULL,
  `image` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Despejando dados para a tabela `Users`
--

INSERT INTO `Users` (`id`, `displayName`, `email`, `password`, `image`) VALUES
(2366216580, 'Brett Wiltshire', 'brett@asdasd.com', '123456', 'http://4.bp.blogspot.com/_YA50adQ-7vQ/S1gfR_6ufpI/AAAAAAAAAAk/1ErJGgRWZDg/S45/brett.png'),
(333778300967882622, 'Sorogaya Kun', 'brett@email.com', '123456', '');

--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `Posts`
--
ALTER TABLE `Posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userId` (`userId`);

--
-- Índices de tabela `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`) USING HASH;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
