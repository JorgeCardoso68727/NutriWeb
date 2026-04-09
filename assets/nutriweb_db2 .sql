-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 07-Abr-2026 às 16:28
-- Versão do servidor: 8.4.7
-- versão do PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de dados: `nutriweb_db2`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `agua`
--

DROP TABLE IF EXISTS `agua`;
CREATE TABLE IF NOT EXISTS `agua` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quantidade_ml` int NOT NULL,
  `data_registo` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_agua_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `badge_pedido`
--

DROP TABLE IF EXISTS `badge_pedido`;
CREATE TABLE IF NOT EXISTS `badge_pedido` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `diploma_pdf` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `admin_user_id` int DEFAULT NULL,
  `observacao` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_badge_pedido_user_id` (`user_id`),
  KEY `idx_badge_pedido_estado` (`estado`),
  KEY `fk_badge_pedido_admin_user` (`admin_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `denuncia`
--

DROP TABLE IF EXISTS `denuncia`;
CREATE TABLE IF NOT EXISTS `denuncia` (
  `id` int NOT NULL AUTO_INCREMENT,
  `target_user_id` int NOT NULL,
  `autor_id` int NOT NULL,
  `motivo` varchar(100) NOT NULL,
  `descricao` text,
  `data_denuncia` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_denuncia_target` (`target_user_id`),
  KEY `fk_denuncia_autor` (`autor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `likes`
--

DROP TABLE IF EXISTS `likes`;
CREATE TABLE IF NOT EXISTS `likes` (
  `id_like` int NOT NULL AUTO_INCREMENT,
  `id_post` int NOT NULL,
  `id_user` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_like`),
  UNIQUE KEY `ux_likes_id_post_id_user` (`id_post`,`id_user`),
  KEY `idx_likes_id_post` (`id_post`),
  KEY `idx_likes_id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `mensagem`
--

DROP TABLE IF EXISTS `mensagem`;
CREATE TABLE IF NOT EXISTS `mensagem` (
  `id` int NOT NULL AUTO_INCREMENT,
  `remetente_id` int NOT NULL,
  `destinatario_id` int NOT NULL,
  `conteudo` text NOT NULL,
  `data_envio` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lida` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_msg_remetente` (`remetente_id`),
  KEY `fk_msg_destinatario` (`destinatario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `migration`
--

DROP TABLE IF EXISTS `migration`;
CREATE TABLE IF NOT EXISTS `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `migration`
--

INSERT INTO `migration` (`version`, `apply_time`) VALUES
('m000000_000000_base', 1774371375),
('m150214_044831_init_user', 1774371377),
('m260402_120001_create_pedido_badge_table', 1775576381),
('m260407_000001_create_likes_table', 1775577817),
('m260407_000002_create_likes_table', 1775578224);

-- --------------------------------------------------------

--
-- Estrutura da tabela `perfil`
--

DROP TABLE IF EXISTS `perfil`;
CREATE TABLE IF NOT EXISTS `perfil` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `Frist_Name` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Last_Name` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Bio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Foto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Telefone` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_perfil_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `perfil`
--

INSERT INTO `perfil` (`id`, `user_id`, `Frist_Name`, `Last_Name`, `Bio`, `Foto`, `Telefone`) VALUES
(1, 7, 'Eloa', 'Cardoso', '', 'img/default.jpeg', 936767676),
(2, 8, 'Marcelo', 'Bandok', 'Ola sou um nutricionista gordo', 'uploads/profile/8_1775579170_WhatsAppImage2026-04-07at172529.jpeg', 932926676);

-- --------------------------------------------------------

--
-- Estrutura da tabela `post`
--

DROP TABLE IF EXISTS `post`;
CREATE TABLE IF NOT EXISTS `post` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `conteudo` text NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `CorPost` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_post_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `post`
--

INSERT INTO `post` (`id`, `titulo`, `conteudo`, `imagem`, `data_criacao`, `user_id`, `CorPost`) VALUES
(8, 'Massa Gourmet', 'Massa com molho', 'uploads/posts/post_69d5256750d627.09696263.jpg', '2026-04-07 14:40:23', 8, '#FFF9C4'),
(9, 'Mulheres', 'sim', 'uploads/posts/post_69d52d1fe6bf20.38282512.png', '2026-04-07 15:13:19', 8, '#FFC9D4'),
(10, 'Pretas', 'Nao', 'uploads/posts/post_69d52d35eac340.70343597.png', '2026-04-07 15:13:41', 8, '#D79AFF'),
(11, 'Demonio Deficiente', 'Sim', 'uploads/posts/post_69d52d6f2dcdc0.49056705.png', '2026-04-07 15:14:39', 8, '#990000');

-- --------------------------------------------------------

--
-- Estrutura da tabela `profile`
--

DROP TABLE IF EXISTS `profile`;
CREATE TABLE IF NOT EXISTS `profile` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bio` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `foto` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `timezone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Extraindo dados da tabela `profile`
--

INSERT INTO `profile` (`id`, `user_id`, `created_at`, `updated_at`, `full_name`, `bio`, `foto`, `timezone`) VALUES
(1, 1, '2026-03-24 16:56:17', NULL, 'the one', NULL, NULL, NULL),
(3, 4, '2026-03-25 17:43:28', '2026-03-25 17:43:28', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `role`
--

DROP TABLE IF EXISTS `role`;
CREATE TABLE IF NOT EXISTS `role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `can_admin` smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Extraindo dados da tabela `role`
--

INSERT INTO `role` (`id`, `name`, `created_at`, `updated_at`, `can_admin`) VALUES
(1, 'Admin', '2026-03-24 16:56:17', NULL, 1),
(2, 'User', '2026-03-24 16:56:17', NULL, 0),
(3, 'Nutricionista', '2026-03-24 17:03:35', '2026-03-24 17:03:35', 0),
(4, 'Instituicao', '2026-03-24 17:03:35', '2026-03-24 17:03:35', 0);

-- --------------------------------------------------------

--
-- Estrutura da tabela `seguidor`
--

DROP TABLE IF EXISTS `seguidor`;
CREATE TABLE IF NOT EXISTS `seguidor` (
  `seguidor_id` int NOT NULL,
  `seguido_id` int NOT NULL,
  `data_seguimento` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`seguidor_id`,`seguido_id`),
  KEY `fk_seguido_user` (`seguido_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `seguidor`
--

INSERT INTO `seguidor` (`seguidor_id`, `seguido_id`, `data_seguimento`) VALUES
(7, 8, '2026-04-07 15:17:50'),
(8, 7, '2026-04-07 14:36:46');

-- --------------------------------------------------------

--
-- Estrutura da tabela `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `status` smallint NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `auth_key` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `access_token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `logged_in_ip` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `logged_in_at` timestamp NULL DEFAULT NULL,
  `created_ip` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `banned_at` timestamp NULL DEFAULT NULL,
  `banned_reason` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_email` (`email`),
  UNIQUE KEY `user_username` (`username`),
  KEY `user_role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Extraindo dados da tabela `user`
--

INSERT INTO `user` (`id`, `role_id`, `status`, `email`, `username`, `password`, `auth_key`, `access_token`, `logged_in_ip`, `logged_in_at`, `created_ip`, `created_at`, `updated_at`, `banned_at`, `banned_reason`) VALUES
(1, 1, 1, 'neo@neo.com', 'neo', '$2y$13$dyVw4WkZGkABf2UrGWrhHO4ZmVBv.K4puhOL59Y9jQhIdj63TlV.O', 'guUjhMnL1dGxJmh2xZefBQ6xmC4Pj8qf', '2itruDLviDU7MmBZ8hRXRlEfiHlj2OH0', '::1', '2026-03-25 16:41:46', NULL, '2026-03-24 16:56:17', NULL, NULL, NULL),
(4, 2, 0, 'jonecario197@gmail.com', 'Hirokumata', '$2y$13$NwDu1PDFgVIFr5oMCun/SOYcdBGBGRIzWxymKN6sKAOT6HghUyHIO', 'Q91AYLah8BpZ0kMQVKhVOOegAGeEqZgX', 'Z3RMFoiZRL8KpZYND4tgmsqarwm7oLM2', '::1', '2026-03-26 17:30:36', '::1', '2026-03-25 17:43:28', '2026-03-25 17:43:28', NULL, NULL),
(7, 2, 1, 'EloadoVale@gmail.com', 'Eloavale', '$2y$13$hTnEAabR3sb5hTaF79UE4eZAcPg6HsLZc8X3R4u6cgm4NjJFwajxe', 'cWQzi7GprIVcfSmkTV6VoNjYuL9KzAxN', 'ngdvURdYMJOL3LzCWLas7itY07S41VIy', '::1', '2026-04-07 15:17:28', '::1', '2026-03-26 18:17:58', '2026-03-26 18:17:58', NULL, NULL),
(8, 3, 1, 'marcelo7674@gmail.com', 'Marcelo7674', '$2y$13$3pY4IKDBNzWts1XJLu78.u1M.LLK4IERqXjoeC96v8l6MulEBbGiq', 'UrDz5tsSFcMBrotibn4L3kMvK1kWVepf', '_joaxRMPogExLZmRR8kdt7wERvUNE7U4', '::1', '2026-04-07 15:25:59', '::1', '2026-04-07 14:26:15', '2026-04-07 14:26:15', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `user_auth`
--

DROP TABLE IF EXISTS `user_auth`;
CREATE TABLE IF NOT EXISTS `user_auth` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `provider` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `provider_id` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `provider_attributes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_auth_provider_id` (`provider_id`),
  KEY `user_auth_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `user_token`
--

DROP TABLE IF EXISTS `user_token`;
CREATE TABLE IF NOT EXISTS `user_token` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `type` smallint NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `data` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_token_token` (`token`),
  KEY `user_token_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Extraindo dados da tabela `user_token`
--

INSERT INTO `user_token` (`id`, `user_id`, `type`, `token`, `data`, `created_at`, `expired_at`) VALUES
(1, 4, 1, 'EABV3QTWOo5ghsSPAWb5iUKNR6umBHzB', NULL, '2026-03-25 17:43:28', NULL);

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `agua`
--
ALTER TABLE `agua`
  ADD CONSTRAINT `fk_agua_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `denuncia`
--
ALTER TABLE `denuncia`
  ADD CONSTRAINT `fk_denuncia_autor` FOREIGN KEY (`autor_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_denuncia_target` FOREIGN KEY (`target_user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `fk_likes_post` FOREIGN KEY (`id_post`) REFERENCES `post` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_likes_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `mensagem`
--
ALTER TABLE `mensagem`
  ADD CONSTRAINT `fk_msg_destinatario` FOREIGN KEY (`destinatario_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_msg_remetente` FOREIGN KEY (`remetente_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `perfil`
--
ALTER TABLE `perfil`
  ADD CONSTRAINT `fk_perfil_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `fk_post_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `profile`
--
ALTER TABLE `profile`
  ADD CONSTRAINT `profile_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Limitadores para a tabela `seguidor`
--
ALTER TABLE `seguidor`
  ADD CONSTRAINT `fk_seguido_user` FOREIGN KEY (`seguido_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_seguidor_user` FOREIGN KEY (`seguidor_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_role_id` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);

--
-- Limitadores para a tabela `user_auth`
--
ALTER TABLE `user_auth`
  ADD CONSTRAINT `user_auth_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Limitadores para a tabela `user_token`
--
ALTER TABLE `user_token`
  ADD CONSTRAINT `user_token_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
