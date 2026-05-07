-- MySQL dump 10.13  Distrib 8.0.44, for macos15 (arm64)
--
-- Host: 127.0.0.1    Database: nutriweb_db2
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `agua`
--

DROP TABLE IF EXISTS `agua`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agua` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quantidade_ml` int NOT NULL,
  `data_registo` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_agua_user` (`user_id`),
  CONSTRAINT `fk_agua_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agua`
--

LOCK TABLES `agua` WRITE;
/*!40000 ALTER TABLE `agua` DISABLE KEYS */;
/*!40000 ALTER TABLE `agua` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `denuncia`
--

DROP TABLE IF EXISTS `denuncia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `denuncia` (
  `id` int NOT NULL AUTO_INCREMENT,
  `target_user_id` int NOT NULL,
  `autor_id` int NOT NULL,
  `motivo` varchar(100) NOT NULL,
  `descricao` text,
  `data_denuncia` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_denuncia_target` (`target_user_id`),
  KEY `fk_denuncia_autor` (`autor_id`),
  CONSTRAINT `fk_denuncia_autor` FOREIGN KEY (`autor_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_denuncia_target` FOREIGN KEY (`target_user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `denuncia`
--

LOCK TABLES `denuncia` WRITE;
/*!40000 ALTER TABLE `denuncia` DISABLE KEYS */;
/*!40000 ALTER TABLE `denuncia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensagem`
--

DROP TABLE IF EXISTS `mensagem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mensagem` (
  `id` int NOT NULL AUTO_INCREMENT,
  `remetente_id` int NOT NULL,
  `destinatario_id` int NOT NULL,
  `conteudo` text NOT NULL,
  `data_envio` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lida` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_msg_remetente` (`remetente_id`),
  KEY `fk_msg_destinatario` (`destinatario_id`),
  CONSTRAINT `fk_msg_destinatario` FOREIGN KEY (`destinatario_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_remetente` FOREIGN KEY (`remetente_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensagem`
--

LOCK TABLES `mensagem` WRITE;
/*!40000 ALTER TABLE `mensagem` DISABLE KEYS */;
/*!40000 ALTER TABLE `mensagem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migration`
--

DROP TABLE IF EXISTS `migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration`
--

LOCK TABLES `migration` WRITE;
/*!40000 ALTER TABLE `migration` DISABLE KEYS */;
INSERT INTO `migration` VALUES ('m000000_000000_base',1774371375),('m150214_044831_init_user',1774371377);
/*!40000 ALTER TABLE `migration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perfil`
--

DROP TABLE IF EXISTS `perfil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perfil` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `Frist_Name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Last_Name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Bio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Foto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Telefone` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_perfil_user` (`user_id`),
  CONSTRAINT `fk_perfil_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perfil`
--

LOCK TABLES `perfil` WRITE;
/*!40000 ALTER TABLE `perfil` DISABLE KEYS */;
INSERT INTO `perfil` VALUES (1,7,'hugo','carne','312312321','img/default.jpeg',0),(2,8,'paulo','escarreta','31232132','0',0),(3,9,'paulo','luis','31232132','img/default.jpeg',0),(4,11,'hugo','lopes',NULL,'img/default.jpeg',123456789),(5,12,'figo','lopes','ola sou o figo ......','uploads/profile/12_1774573625_rodrigo.jpeg',123456789),(6,13,'andre','fidalgo','Sou Comido','uploads/profile/13_1774644455_andre.jpg',123456789);
/*!40000 ALTER TABLE `perfil` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post`
--

DROP TABLE IF EXISTS `post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `conteudo` text NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_post_user` (`user_id`),
  CONSTRAINT `fk_post_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post`
--

LOCK TABLES `post` WRITE;
/*!40000 ALTER TABLE `post` DISABLE KEYS */;
/*!40000 ALTER TABLE `post` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile`
--

DROP TABLE IF EXISTS `profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `profile` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bio` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `foto` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `timezone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_user_id` (`user_id`),
  CONSTRAINT `profile_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile`
--

LOCK TABLES `profile` WRITE;
/*!40000 ALTER TABLE `profile` DISABLE KEYS */;
INSERT INTO `profile` VALUES (1,1,'2026-03-24 16:56:17',NULL,'the one',NULL,NULL,NULL),(2,3,'2026-03-24 18:49:48','2026-03-24 18:49:48','Diogo Nutricionista','Especialista em nutrição desportiva e bem-estar.',NULL,NULL),(3,4,'2026-03-25 17:43:28','2026-03-25 17:43:28',NULL,NULL,NULL,NULL),(4,5,'2026-03-26 09:37:20','2026-03-26 09:37:20',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `can_admin` smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (1,'Admin','2026-03-24 16:56:17',NULL,1),(2,'User','2026-03-24 16:56:17',NULL,0),(3,'Nutricionista','2026-03-24 17:03:35','2026-03-24 17:03:35',0),(4,'Instituicao','2026-03-24 17:03:35','2026-03-24 17:03:35',0);
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seguidor`
--

DROP TABLE IF EXISTS `seguidor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seguidor` (
  `seguidor_id` int NOT NULL,
  `seguido_id` int NOT NULL,
  `data_seguimento` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`seguidor_id`,`seguido_id`),
  KEY `fk_seguido_user` (`seguido_id`),
  CONSTRAINT `fk_seguido_user` FOREIGN KEY (`seguido_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_seguidor_user` FOREIGN KEY (`seguidor_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seguidor`
--

LOCK TABLES `seguidor` WRITE;
/*!40000 ALTER TABLE `seguidor` DISABLE KEYS */;
/*!40000 ALTER TABLE `seguidor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
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
  KEY `user_role_id` (`role_id`),
  CONSTRAINT `user_role_id` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,1,1,'neo@neo.com','neo','$2y$13$dyVw4WkZGkABf2UrGWrhHO4ZmVBv.K4puhOL59Y9jQhIdj63TlV.O','guUjhMnL1dGxJmh2xZefBQ6xmC4Pj8qf','2itruDLviDU7MmBZ8hRXRlEfiHlj2OH0','::1','2026-03-26 18:33:34',NULL,'2026-03-24 16:56:17',NULL,NULL,NULL),(2,3,1,'diogo@nutriweb.pt','diogo_nutri','$2y$13$Eef7AmlL9z5Y.uSjX.S7u.I6zB8A1P6Oq.zX8X8X8X8X8X8X8X8X.','test_key_123',NULL,NULL,NULL,NULL,'2026-03-24 18:48:00','2026-03-24 18:48:00',NULL,NULL),(3,3,1,'diogo_final@nutriweb.pt','diogo_final','$2y$13$Eef7AmlL9z5Y.uSjX.S7u.I6zB8A1P6Oq.zX8X8X8X8X8X8X8X8X.','test_key_123',NULL,NULL,NULL,NULL,'2026-03-24 18:49:48','2026-03-24 18:49:48',NULL,NULL),(4,2,0,'jonecario197@gmail.com','Hirokumata','$2y$13$NwDu1PDFgVIFr5oMCun/SOYcdBGBGRIzWxymKN6sKAOT6HghUyHIO','Q91AYLah8BpZ0kMQVKhVOOegAGeEqZgX','Z3RMFoiZRL8KpZYND4tgmsqarwm7oLM2','::1','2026-03-26 17:19:04','::1','2026-03-25 17:43:28','2026-03-25 17:43:28',NULL,NULL),(5,2,0,'diogocabilhas@lol.com','diogo_cabilhas','$2y$13$3crrUw4tttEuVIgMpGECxu/mQIn.4uilyUBPiXwR5WacwASu0flK6','_bU2fQph-KRzcy-7tQSht4-k1dmTmrjA','S664AVSa6qJxKfsHHd1EdneUgSQwFSwI',NULL,NULL,'::1','2026-03-26 09:37:20','2026-03-26 09:37:20',NULL,NULL),(6,2,0,'julio_luis@hotmail.com','julio_luis','$2y$13$Kcp5f/46iNqVYbjBSKzVuOJC4Eku238w2AdoQ6NzeA7gGhCc1h58a','EXMm-NGlPNt_1mc9nsFT4T8e5_XkjECb','FMpOOSGfWesjaw1KeLcZZqtTZrta6bM0',NULL,NULL,'::1','2026-03-26 13:04:57','2026-03-26 13:04:57',NULL,NULL),(7,2,1,'hugo_carne@gmail.com','hugo_carne','$2y$13$RDTW.zF2qeRB57XPsgtMheIj1g2j8V94qZi7GgckTx2TtZVPlZMnG','3aFigPibEpOJ6ylIiII5BdSrijQrqGvi','MNk-p-_tfywJ9m0NuoTMZpczMsqFxbCP','::1','2026-03-26 20:00:37','::1','2026-03-26 17:56:24','2026-03-26 17:56:24',NULL,NULL),(8,2,1,'paulo@gmail.com','paulo','$2y$13$yjkdkSJRSeGq9zUgJJNYGesCJG.dLaoyVaT/j4dJbIIlyn1Dwk4ua','4hT5fae3n5QnXNW83m7BJxkfr5HgIL8m','oM5Jng6zoNysH_P-JT4FuGoMijlGp7ID',NULL,NULL,'::1','2026-03-26 18:08:22','2026-03-26 18:08:22',NULL,NULL),(9,2,1,'pauloluis@gmail.com','paulo_luis','$2y$13$8QX4LmgJchSj5DJrFiU4UeX7VEAB.BF16vVgNUDYuxF4Zz8pdd7ru','e2cwN9j0Ijn7Q_q3EAhICh0kPDSRF_Wl','S_aCLyZH6tQUIa6O9IydNvGSTVnmbywX',NULL,NULL,'::1','2026-03-26 18:12:20','2026-03-26 18:12:20',NULL,NULL),(10,2,1,'jose_canario@gmail.com','jose_canario','$2y$13$9yJuRJyxUKrQV79H8jrqaOiNpOIwVgIWSST43CAxIHWG9VCuA15LO','zEdyec3Ch8iq8l3zk3lWJLuj1ePxmLTg','6AubDbVnJmS2FeZjInYRn2hR2bWiZMmh','::1','2026-03-27 00:12:02','::1','2026-03-27 00:08:38','2026-03-27 00:08:38',NULL,NULL),(11,2,1,'hugo_lopes@gmail.com','hugo_lopes','$2y$13$22D3Ppy.lnJxMcfMj.kznuubX6TlQWh0MvujyMb4TPMb5ItSHZEk.','adXM-jTLwtbRDthYAjxXWHh7q2XkEk1K','knTce_5-_hcZTEwEbC-C3owoOYOpcsa0','::1','2026-03-27 00:14:40','::1','2026-03-27 00:14:40','2026-03-27 00:14:40',NULL,NULL),(12,2,1,'figo_lopes@gmail.com','figo_lopes','$2y$13$l.oe0fSrqGlB02JGRQpPYu/T2auAAFzAVQFvpMnt.MnWMkYbuBqLi','PGAYQAcfDZlYOe3sZG_tUXMjMml8Ehhj','kwG__vsVSDTZZwnqrh66xzCx2jOvzlPU','::1','2026-03-27 00:29:02','::1','2026-03-27 00:28:53','2026-03-27 00:28:53',NULL,NULL),(13,2,1,'andre_fidalgo@gmail.com','andre_fidalgo67','$2y$13$GYgLkBfK46EEKKocwzLSLeXVahs5Sywna4C09VOFhsBfBpC/0eC8.','6KzTimKV07xRN9ozr8KilaszN_5bDj3B','VYp4vVJYornOIcRyGMZU7G9zo51fHBpm','::1','2026-03-27 20:43:37','::1','2026-03-27 20:43:25','2026-03-27 20:43:25',NULL,NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_auth`
--

DROP TABLE IF EXISTS `user_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_auth` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `provider` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `provider_id` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `provider_attributes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_auth_provider_id` (`provider_id`),
  KEY `user_auth_user_id` (`user_id`),
  CONSTRAINT `user_auth_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_auth`
--

LOCK TABLES `user_auth` WRITE;
/*!40000 ALTER TABLE `user_auth` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_auth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_token`
--

DROP TABLE IF EXISTS `user_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_token` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `type` smallint NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `data` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_token_token` (`token`),
  KEY `user_token_user_id` (`user_id`),
  CONSTRAINT `user_token_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_token`
--

LOCK TABLES `user_token` WRITE;
/*!40000 ALTER TABLE `user_token` DISABLE KEYS */;
INSERT INTO `user_token` VALUES (1,4,1,'Q10B87iymPpzgg9mNQi6hl1fv_MOTyr-',NULL,'2026-03-26 17:04:29',NULL),(2,5,1,'zY-FXpvWWPSuql--XMCI5PtMq_1c3_3t',NULL,'2026-03-26 09:37:20',NULL),(3,6,1,'ZnHzhJ0bwlgzw9y2c8dscArXEjmTHbS5',NULL,'2026-03-26 16:56:07',NULL);
/*!40000 ALTER TABLE `user_token` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-28 19:29:42
