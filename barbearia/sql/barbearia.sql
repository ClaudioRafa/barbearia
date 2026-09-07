# SQL-Front 5.1  (Build 4.16)

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE */;
/*!40101 SET SQL_MODE='NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES */;
/*!40103 SET SQL_NOTES='ON' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;


# Host: localhost    Database: barbearia
# ------------------------------------------------------
# Server version 5.5.5-10.4.32-MariaDB

#
# Source for table admin_usuarios
#

DROP TABLE IF EXISTS `admin_usuarios`;
CREATE TABLE `admin_usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL DEFAULT '',
  `criado_em` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Dumping data for table admin_usuarios
#

LOCK TABLES `admin_usuarios` WRITE;
/*!40000 ALTER TABLE `admin_usuarios` DISABLE KEYS */;
INSERT INTO `admin_usuarios` VALUES (1,'rafa','1234','2026-09-06 11:10:59');
INSERT INTO `admin_usuarios` VALUES (2,'admin','$2y$10$tZ3C28L0Qd7YdG/977mX/.K37e6M8h0uXQWb2i9E6fW26PZgT42Y2','2026-09-06 11:50:32');
/*!40000 ALTER TABLE `admin_usuarios` ENABLE KEYS */;
UNLOCK TABLES;

#
# Source for table agendamento_servicos
#

DROP TABLE IF EXISTS `agendamento_servicos`;
CREATE TABLE `agendamento_servicos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agendamento_id` int(11) NOT NULL,
  `servico_id` int(11) NOT NULL,
  `nome_servico` varchar(100) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `agendamento_id` (`agendamento_id`),
  KEY `servico_id` (`servico_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Dumping data for table agendamento_servicos
#

LOCK TABLES `agendamento_servicos` WRITE;
/*!40000 ALTER TABLE `agendamento_servicos` DISABLE KEYS */;
INSERT INTO `agendamento_servicos` VALUES (1,1,1,'Corte com Máquina',20);
INSERT INTO `agendamento_servicos` VALUES (2,2,1,'Corte com Máquina',20);
/*!40000 ALTER TABLE `agendamento_servicos` ENABLE KEYS */;
UNLOCK TABLES;

#
# Source for table agendamentos
#

DROP TABLE IF EXISTS `agendamentos`;
CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_nome` varchar(150) DEFAULT NULL,
  `cliente_telefone` varchar(30) DEFAULT NULL,
  `data` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `valor_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('reservado','concluido','cancelado','bloqueado') NOT NULL DEFAULT 'reservado',
  `observacao` varchar(255) DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_data_hora` (`data`,`hora_inicio`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Dumping data for table agendamentos
#

LOCK TABLES `agendamentos` WRITE;
/*!40000 ALTER TABLE `agendamentos` DISABLE KEYS */;
INSERT INTO `agendamentos` VALUES (1,'SAMARA','21970015947','2026-09-12','09:00:00','09:40:00',20,'reservado',NULL,'2026-09-05 23:26:05','2026-09-05 23:26:05');
INSERT INTO `agendamentos` VALUES (2,'SAMARA','21970015947','2026-09-12','09:40:00','10:20:00',20,'reservado',NULL,'2026-09-06 11:37:01','2026-09-06 11:37:01');
/*!40000 ALTER TABLE `agendamentos` ENABLE KEYS */;
UNLOCK TABLES;

#
# Source for table configuracoes
#

DROP TABLE IF EXISTS `configuracoes`;
CREATE TABLE `configuracoes` (
  `chave` varchar(60) NOT NULL,
  `valor` text DEFAULT NULL,
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Dumping data for table configuracoes
#

LOCK TABLES `configuracoes` WRITE;
/*!40000 ALTER TABLE `configuracoes` DISABLE KEYS */;
INSERT INTO `configuracoes` VALUES ('almoco_fim','15:00');
INSERT INTO `configuracoes` VALUES ('almoco_inicio','13:00');
INSERT INTO `configuracoes` VALUES ('duracao_atendimento','40');
INSERT INTO `configuracoes` VALUES ('endereco','');
INSERT INTO `configuracoes` VALUES ('nome_barbearia','POR NÓS BARBEARIA');
INSERT INTO `configuracoes` VALUES ('texto_hero','Estilo, precisão e tradição em cada corte.');
INSERT INTO `configuracoes` VALUES ('texto_sobre','Na POR NÓS BARBEARIA, cada atendimento é feito com cuidado e atenção aos detalhes. Agende seu horário online e evite filas.');
INSERT INTO `configuracoes` VALUES ('whatsapp','5521987674006');
/*!40000 ALTER TABLE `configuracoes` ENABLE KEYS */;
UNLOCK TABLES;

#
# Source for table dias_funcionamento
#

DROP TABLE IF EXISTS `dias_funcionamento`;
CREATE TABLE `dias_funcionamento` (
  `dia_semana` tinyint(4) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `hora_abertura` time NOT NULL DEFAULT '09:00:00',
  `hora_fechamento` time NOT NULL DEFAULT '20:00:00',
  `ordem_chegada` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`dia_semana`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Dumping data for table dias_funcionamento
#

LOCK TABLES `dias_funcionamento` WRITE;
/*!40000 ALTER TABLE `dias_funcionamento` DISABLE KEYS */;
INSERT INTO `dias_funcionamento` VALUES (0,0,'09:00:00','20:00:00',0);
INSERT INTO `dias_funcionamento` VALUES (1,1,'09:00:00','20:00:00',0);
INSERT INTO `dias_funcionamento` VALUES (2,1,'09:00:00','20:00:00',0);
INSERT INTO `dias_funcionamento` VALUES (3,1,'09:00:00','20:00:00',0);
INSERT INTO `dias_funcionamento` VALUES (4,1,'09:00:00','20:00:00',0);
INSERT INTO `dias_funcionamento` VALUES (5,1,'09:00:00','20:00:00',1);
INSERT INTO `dias_funcionamento` VALUES (6,1,'09:00:00','20:00:00',1);
/*!40000 ALTER TABLE `dias_funcionamento` ENABLE KEYS */;
UNLOCK TABLES;

#
# Source for table servicos
#

DROP TABLE IF EXISTS `servicos`;
CREATE TABLE `servicos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `ordem` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Dumping data for table servicos
#

LOCK TABLES `servicos` WRITE;
/*!40000 ALTER TABLE `servicos` DISABLE KEYS */;
INSERT INTO `servicos` VALUES (1,'Corte com Máquina',20,1,1);
INSERT INTO `servicos` VALUES (2,'Corte com Navalha',25,1,2);
INSERT INTO `servicos` VALUES (3,'Corte Disfarçado',30,1,3);
INSERT INTO `servicos` VALUES (4,'Corte na Tesoura',35,1,4);
INSERT INTO `servicos` VALUES (5,'Pigmentação',15,1,5);
INSERT INTO `servicos` VALUES (6,'Barba',25,1,6);
INSERT INTO `servicos` VALUES (7,'Sobrancelha',7,1,7);
INSERT INTO `servicos` VALUES (8,'Corte Disfarçado + Tesoura',35,1,8);
/*!40000 ALTER TABLE `servicos` ENABLE KEYS */;
UNLOCK TABLES;

#
#  Foreign keys for table agendamento_servicos
#

ALTER TABLE `agendamento_servicos`
ADD CONSTRAINT `agendamento_servicos_ibfk_1` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `agendamento_servicos_ibfk_2` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`);


/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
