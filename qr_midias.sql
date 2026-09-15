-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 15/09/2026 às 09:58
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `qr_midias`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `midiaqr`
--

CREATE TABLE `midiaqr` (
  `id` int(11) NOT NULL,
  `codigo_qr` varchar(100) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `midiaqr`
--

INSERT INTO `midiaqr` (`id`, `codigo_qr`, `ativo`, `criado_em`) VALUES
(5, 'testeqr', 1, '2026-09-06 12:52:15'),
(6, 'SALMOS', 1, '2026-09-06 16:11:19'),
(7, 'MarcosFF', 1, '2026-09-15 00:34:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `midias`
--

CREATE TABLE `midias` (
  `id` int(11) NOT NULL,
  `midiaQR_id` int(11) NOT NULL,
  `tipo` enum('audio','video','imagem') NOT NULL,
  `arquivo` varchar(255) NOT NULL,
  `nome_original` varchar(255) NOT NULL,
  `letra_audio` text NOT NULL,
  `ordem` int(11) DEFAULT 0,
  `visualizacoes` int(11) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `midias`
--

INSERT INTO `midias` (`id`, `midiaQR_id`, `tipo`, `arquivo`, `nome_original`, `letra_audio`, `ordem`, `visualizacoes`, `criado_em`) VALUES
(16, 6, 'audio', '6a9d968b7b6c2.mp3', 'Salmo 01.mp3', '', 0, 0, '2026-09-06 16:36:27'),
(17, 6, 'audio', '6a9d9698787c8.mp3', 'Salmo 02.mp3', '', 0, 0, '2026-09-06 16:36:40'),
(18, 6, 'audio', '6a9d969dcf1ad.mp3', 'Salmo 03.mp3', '', 0, 0, '2026-09-06 16:36:45'),
(19, 6, 'audio', '6a9d96a5d6145.mp3', 'Salmo 04.mp3', '', 0, 0, '2026-09-06 16:36:53'),
(20, 6, 'audio', '6a9d96ab50d61.mp3', 'Salmo 05.mp3', '', 0, 0, '2026-09-06 16:36:59'),
(22, 6, 'audio', '6a9da5dfef19b.mp3', 'Salmo 06.mp3', '¹ Ao mestre de canto. Com instrumentos de corda. Em oitava. Salmo de Davi.\r\n² Senhor, em vossa cólera não me repreendais, em vosso furor não me castigueis.\r\n³ Tende piedade de mim, Senhor, porque desfaleço; sarai-me, pois sinto abalados os meus ossos.\r\n⁴ Minha alma está muito perturbada; vós, porém, Senhor, até quando?...\r\n⁵ Voltai, Senhor, livrai minha alma; salvai-me, pela vossa bondade.\r\n⁶ Porque no seio da morte não há quem de vós se lembre; quem vos glorificará na habitação dos mortos?\r\n⁷ Eu me esgoto gemendo; todas as noites banho de pranto minha cama, com lágrimas inundo o meu leito.\r\n⁸ De amargura meus olhos se turvam, esmorecem por causa dos que me oprimem.\r\n⁹ Apartai-vos de mim, vós todos que praticais o mal, porque o Senhor atendeu às minhas lágrimas.\r\n¹⁰ O Senhor escutou a minha oração, o Senhor acolheu a minha súplica.\r\n¹¹ Que todos os meus inimigos sejam envergonhados e aterrados; recuem imediatamente, cobertos de confusão!\r\n\r\nSalmos 6:1-11', 0, 0, '2026-09-06 17:41:51'),
(23, 5, 'audio', '6a9db58ad71d9.mp3', 'Salmo 23.mp3', 'O Senhor é o meu pastor, nada me faltará.\r\nDeitar-me faz em verdes pastos, guia-me mansamente a águas tranquilas.\r\nRefrigera a minha alma; guia-me pelas veredas da justiça, por amor do seu nome.\r\nAinda que eu andasse pelo vale da sombra da morte, não temeria mal algum, porque tu estás comigo; a tua vara e o teu cajado me consolam.\r\nPreparas uma mesa perante mim na presença dos meus inimigos, unges a minha cabeça com óleo, o meu cálice transborda.\r\nCertamente que a bondade e a misericórdia me seguirão todos os dias da minha vida; e habitarei na casa do Senhor por longos dias.', 0, 0, '2026-09-06 18:48:42'),
(24, 5, 'audio', '6a9dbc86072d8.mp3', 'GUSTAVO LIMA 60 Segundos.mp3', 'Me dê um minuto pra falar\r\nQue eu preciso de você, meu amor\r\n59 segundos é o que resta\r\nPra entender essa paixão no meu peito\r\n\r\nCada segundo que se passa\r\nMais aumenta essa vontade\r\nEsse desejo, essa paixão\r\nQue me domina, me fascina\r\nCom seu jeito de menina tão linda\r\n\r\nVocê enlouquece o meu coração\r\nVocê sabe que eu tô em suas mãos\r\n\r\nO tempo passa depressa quando estou com você\r\nAs horas viram minutos, não consigo entender\r\nE o que eu mais quero é viver ao seu lado\r\nCada segundo, amor\r\n\r\nO tempo passa depressa quando estou com você\r\nAs horas viram minutos, não consigo entender\r\nE o que eu mais quero é viver ao seu lado\r\nCada segundo, amor\r\n\r\nTe fazer feliz por toda a vida\r\n\r\nCada segundo que se passa\r\nMais aumenta essa vontade\r\nEsse desejo, essa paixão\r\nQue me ilumina, me fascina\r\nCom seu jeito de menina tão linda\r\n\r\nVocê enlouquece o meu coração\r\nVocê sabe que eu tô em suas mãos\r\n\r\nO tempo passa depressa quando estou com você\r\nAs horas viram minutos, não consigo entender\r\nE o que eu mais quero é viver ao seu lado\r\nCada segundo, amor\r\n\r\nO tempo passa depressa quando estou com você\r\nAs horas viram minutos, não consigo entender\r\nE o que eu mais quero é viver ao seu lado\r\nCada segundo, amor\r\n\r\nTe fazer feliz por toda a vida', 0, 0, '2026-09-06 19:18:30'),
(25, 7, 'video', '6aa893346ff07.mp4', 'WhatsApp Video 2026-09-14 at 16.20.07.mp4', '', 0, 0, '2026-09-15 00:37:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `perfis`
--

CREATE TABLE `perfis` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descricao` varchar(150) DEFAULT NULL,
  `pode_cadastrar` tinyint(1) DEFAULT 0,
  `pode_editar` tinyint(1) DEFAULT 0,
  `pode_excluir` tinyint(1) DEFAULT 0,
  `pode_visualizar` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `perfis`
--

INSERT INTO `perfis` (`id`, `nome`, `descricao`, `pode_cadastrar`, `pode_editar`, `pode_excluir`, `pode_visualizar`) VALUES
(1, 'Administrador', 'Acesso total ao sistema', 1, 1, 1, 1),
(2, 'Gerente', 'Pode editar e visualizar dados', 1, 1, 0, 1),
(3, 'Operador', 'Pode cadastrar e visualizar dados', 1, 0, 0, 1),
(4, 'Visualizador', 'Somente leitura', 0, 0, 0, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `permissoes`
--

CREATE TABLE `permissoes` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `chave` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `permissoes`
--

INSERT INTO `permissoes` (`id`, `nome`, `chave`) VALUES
(1, 'Gerenciar cadastro de filmes/series', 'catalogo'),
(36, 'Gerenciar cadastro mídias', 'cad_midias');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expira` datetime DEFAULT NULL,
  `perfil_id` int(11) DEFAULT 4
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `ativo`, `criado_em`, `reset_token`, `reset_expira`, `perfil_id`) VALUES
(1, 'Adelmo Santos', 'dexter.craus@gmail.com', '$2y$10$NWGo.nstV.yRqgaCiExlWONXddYl730ZcqMwEbMAPF2SbA5KRwi5W', 1, '2025-10-30 20:58:26', NULL, NULL, 1),
(2, 'DAVY LUCAS', 'davy@gmail.com', '$2y$10$lk6aMAryybzA3clYX5vAuuNLg29MBreVT.hlm9cUAcwJNZXuBUncG', 1, '2025-10-31 03:21:35', NULL, NULL, 3),
(4, 'TESTE45', 'sad.zonasul@gmail.com', '$2y$10$/R75DsctLKmRxmfimrPY8uxy3Z4bLTUPLCL29IeAdVx2JAgr9UfIO', 1, '2026-01-18 15:46:29', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_tokens`
--

CREATE TABLE `usuarios_tokens` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expira_em` datetime NOT NULL DEFAULT current_timestamp(),
  `usado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios_tokens`
--

INSERT INTO `usuarios_tokens` (`id`, `usuario_id`, `token`, `expira_em`, `usado`) VALUES
(1, 1, '0bd3fe25f6a95833c113c04264574f6181e4cda952142fc2bf7a8f9b2f9c54c9', '2025-10-31 02:15:06', 0),
(2, 1, 'e300137717c5aa95f4ec9d699a802ec743bdfb4f21a8f14985746b7668bd2a55', '2025-10-31 02:25:04', 0),
(3, 1, 'f7eb1ff25687657e7014fe3c105cef74dcf67fc3627a61e391e80d385224f32c', '2025-10-31 02:26:41', 0),
(4, 1, '8ff6920bd135ce24cdb0609a87766d54b666fd247c3f23db8266700a526b64c6', '2025-10-31 02:35:15', 1),
(5, 1, '4fd2920dd89a02dec8762fe80138f3b8eb2cc6938b305fdbdb3292d76d876bd3', '2025-10-31 08:26:49', 0),
(6, 1, 'c8ec09309ff82f6df69659147a038d52cab4ed45fa05311819417fec04e44700', '2025-10-31 08:26:56', 0),
(7, 1, 'e6844a446a1e3b44b9e672d261ea30965a4aa7c744a437b639f77b96944b0c9d', '2025-11-03 02:27:47', 0),
(8, 1, 'f3e357015764fbfc32c13497c28d2a5919b33202296c1d8cd59f174cae5f8bde', '2025-11-06 01:45:01', 1),
(9, 1, '39bbaef85393b4e4f9a90bb467fecbd68a52f3cc652f557b830e50ebf8efb9a0', '2025-11-06 02:06:38', 0),
(10, 1, 'ce3dfcefb49428ba2d57ae4bbe9974fe6c9a9f31c2088473b010c25c8ee9be81', '2025-11-06 02:11:42', 0),
(11, 1, 'bbd4dcc03ec7e00ef8ef96551c7c427bd00580ebe03c9e476785024d333d4bf2', '2025-11-06 02:24:20', 0),
(12, 1, '779d61105aac4e070cd852b5adb25d7726bb1dd37fad56b494acd54c08f5d522', '2025-11-06 21:32:16', 0),
(13, 1, '2eedef0db864ad2dffa08c1973e0f5d7ca854283de92fb950842f709445b8b67', '2025-11-06 21:35:36', 0),
(26, 4, '950be45835cf799a898b95c4fb782b33d79ad4f0c316ef4ca86d0aadcc6adc8d', '2026-01-18 18:10:55', 0),
(27, 4, '91e89a70704fe1a586f2375724b838a037875872c965de071bfac2b7f671c02e', '2026-01-18 18:11:43', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario_permissoes`
--

CREATE TABLE `usuario_permissoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `permissao_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario_permissoes`
--

INSERT INTO `usuario_permissoes` (`id`, `usuario_id`, `permissao_id`) VALUES
(72, 2, 7),
(73, 2, 6),
(74, 2, 34),
(75, 2, 35),
(76, 2, 30);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `midiaqr`
--
ALTER TABLE `midiaqr`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_qr` (`codigo_qr`),
  ADD KEY `idx_codigo` (`codigo_qr`);

--
-- Índices de tabela `midias`
--
ALTER TABLE `midias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subgrupo` (`midiaQR_id`);

--
-- Índices de tabela `perfis`
--
ALTER TABLE `perfis`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `permissoes`
--
ALTER TABLE `permissoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave` (`chave`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `perfil_id` (`perfil_id`);

--
-- Índices de tabela `usuarios_tokens`
--
ALTER TABLE `usuarios_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `usuario_permissoes`
--
ALTER TABLE `usuario_permissoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `permissao_id` (`permissao_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `midiaqr`
--
ALTER TABLE `midiaqr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `midias`
--
ALTER TABLE `midias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de tabela `perfis`
--
ALTER TABLE `perfis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `permissoes`
--
ALTER TABLE `permissoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `usuarios_tokens`
--
ALTER TABLE `usuarios_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `usuario_permissoes`
--
ALTER TABLE `usuario_permissoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `midias`
--
ALTER TABLE `midias`
  ADD CONSTRAINT `fk_midias_subgrupo` FOREIGN KEY (`midiaQR_id`) REFERENCES `midiaqr` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
