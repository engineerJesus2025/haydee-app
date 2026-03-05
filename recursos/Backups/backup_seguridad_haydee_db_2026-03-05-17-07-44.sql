-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: seguridad_haydee_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `asignacion_permisos`
--

DROP TABLE IF EXISTS `asignacion_permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asignacion_permisos` (
  `rol_id` int(11) NOT NULL,
  `permiso_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  PRIMARY KEY (`rol_id`,`permiso_id`,`modulo_id`) USING BTREE,
  KEY `asignacion_permisos_ibfk_1` (`modulo_id`),
  KEY `asignacion_permisos_ibfk_2` (`permiso_id`),
  CONSTRAINT `asignacion_permisos_ibfk_1` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `asignacion_permisos_ibfk_2` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id_permiso`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `asignacion_permisos_ibfk_3` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignacion_permisos`
--

LOCK TABLES `asignacion_permisos` WRITE;
/*!40000 ALTER TABLE `asignacion_permisos` DISABLE KEYS */;
INSERT INTO `asignacion_permisos` VALUES (1,1,1),(1,1,2),(1,1,3),(1,1,4),(1,1,5),(1,1,6),(1,1,7),(1,1,8),(1,1,9),(1,1,10),(1,1,11),(1,1,12),(1,1,13),(1,1,14),(1,1,15),(1,1,16),(1,1,17),(1,1,18),(1,1,19),(1,1,21),(1,1,22),(1,1,23),(1,2,1),(1,2,2),(1,2,3),(1,2,4),(1,2,5),(1,2,6),(1,2,7),(1,2,8),(1,2,9),(1,2,10),(1,2,11),(1,2,12),(1,2,13),(1,2,14),(1,2,15),(1,2,16),(1,2,17),(1,2,18),(1,2,19),(1,2,21),(1,2,22),(1,2,23),(1,3,1),(1,3,2),(1,3,3),(1,3,4),(1,3,5),(1,3,6),(1,3,7),(1,3,8),(1,3,9),(1,3,10),(1,3,11),(1,3,12),(1,3,13),(1,3,14),(1,3,15),(1,3,16),(1,3,17),(1,3,18),(1,3,19),(1,3,21),(1,3,22),(1,3,23),(1,4,1),(1,4,2),(1,4,3),(1,4,4),(1,4,5),(1,4,6),(1,4,7),(1,4,8),(1,4,9),(1,4,10),(1,4,11),(1,4,12),(1,4,13),(1,4,14),(1,4,15),(1,4,16),(1,4,17),(1,4,18),(1,4,19),(1,4,21),(1,4,22),(1,4,23),(3,1,1),(3,2,1),(3,3,1),(3,4,1),(4,1,1),(4,1,2),(4,1,3),(4,1,4),(4,1,15),(4,2,1),(4,2,2),(4,2,3),(4,2,4),(4,2,15),(4,2,16),(4,3,1),(4,3,2),(4,3,3),(4,3,4),(4,3,15),(4,4,1),(4,4,2),(4,4,3),(4,4,4),(4,4,15),(68,1,1),(68,1,2),(68,1,3),(68,1,10),(68,2,1),(68,2,2),(68,2,3),(68,2,10),(68,3,1),(68,3,2),(68,3,3),(68,3,10),(68,4,1),(68,4,2),(68,4,3),(68,4,10);
/*!40000 ALTER TABLE `asignacion_permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bitacora`
--

DROP TABLE IF EXISTS `bitacora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bitacora` (
  `id_bitacora` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_hora` datetime NOT NULL,
  `accion` varchar(100) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  `valores_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{}' CHECK (json_valid(`valores_anteriores`)),
  `valores_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{}' CHECK (json_valid(`valores_nuevos`)),
  PRIMARY KEY (`id_bitacora`),
  KEY `bitacora_ibfk_1` (`usuario_id`),
  KEY `bitacora_ibfk_2` (`modulo_id`),
  CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE,
  CONSTRAINT `bitacora_ibfk_2` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id_modulo`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--

LOCK TABLES `bitacora` WRITE;
/*!40000 ALTER TABLE `bitacora` DISABLE KEYS */;
INSERT INTO `bitacora` VALUES (1,'2026-03-04 23:39:15','cerrar sesion',1,14,'{}','{}'),(2,'2026-03-04 23:39:18','iniciar sesion',1,14,'{}','{}'),(3,'2026-03-05 08:42:04','iniciar sesion',1,14,'{}','{}'),(4,'2026-03-05 08:44:22','consultar',1,2,'{}','{}'),(5,'2026-03-05 08:44:28','consultar',1,1,'{}','{}'),(6,'2026-03-05 08:44:48','consultar',1,3,'{}','{}'),(7,'2026-03-05 08:45:05','consultar',1,1,'{}','{}'),(8,'2026-03-05 08:45:47','registrar',1,1,'{}','{\"apartamento_id\":\"30\",\"mensualidad_id\":\"536\",\"estado\":\"No verificado\",\"cantidad_detalles\":1,\"monto_total\":10}'),(9,'2026-03-05 08:45:47','consultar',1,1,'{}','{}'),(10,'2026-03-05 08:45:55','consultar',1,1,'{}','{}'),(11,'2026-03-05 08:46:09','modificar',1,1,'{\"apartamento_id\":30,\"mensualidad_id\":536,\"estado\":\"No verificado\",\"cantidad_detalles\":1,\"monto_total\":10}','{\"apartamento_id\":\"30\",\"mensualidad_id\":\"536\",\"estado\":\"No verificado\",\"cantidad_detalles\":1,\"monto_total\":800}'),(12,'2026-03-05 08:46:10','consultar',1,1,'{}','{}'),(13,'2026-03-05 08:46:13','consultar',1,1,'{}','{}'),(14,'2026-03-05 08:52:29','consultar',1,1,'{}','{}'),(15,'2026-03-05 08:52:37','consultar',1,2,'{}','{}'),(16,'2026-03-05 08:52:45','consultar',1,4,'{}','{}'),(17,'2026-03-05 08:52:50','consultar',1,3,'{}','{}'),(18,'2026-03-05 08:53:06','consultar',1,5,'{}','{}'),(19,'2026-03-05 08:54:24','consultar',1,6,'{}','{}'),(20,'2026-03-05 08:54:36','consultar',1,7,'{}','{}'),(21,'2026-03-05 08:54:44','consultar',1,8,'{}','{}'),(22,'2026-03-05 08:54:52','consultar',1,9,'{}','{}'),(23,'2026-03-05 08:55:00','consultar',1,15,'{}','{}'),(24,'2026-03-05 08:55:35','consultar',1,15,'{}','{}'),(25,'2026-03-05 08:57:02','consultar',1,15,'{}','{}'),(26,'2026-03-05 08:58:06','consultar',1,15,'{}','{}'),(27,'2026-03-05 08:58:19','consultar',1,15,'{}','{}'),(28,'2026-03-05 08:58:29','consultar',1,11,'{}','{}'),(29,'2026-03-05 08:59:44','consultar',1,12,'{}','{}'),(30,'2026-03-05 08:59:49','consultar',1,13,'{}','{}'),(31,'2026-03-05 08:59:56','consultar',1,14,'{}','{}'),(32,'2026-03-05 09:00:02','consultar',1,17,'{}','{}'),(34,'2026-03-05 09:00:21','consultar',1,23,'{}','{}'),(35,'2026-03-05 09:00:33','consultar',1,19,'{}','{}'),(36,'2026-03-05 09:26:53','consultar',1,5,'{}','{}'),(37,'2026-03-05 09:33:09','consultar',1,9,'{}','{}'),(38,'2026-03-05 09:33:17','registrar',1,9,'{}','{\"fecha_inicio\":\"2026-03-05\",\"estado\":\"Abierto\",\"descripcion\":\"asdasdasd\"}'),(39,'2026-03-05 09:33:17','consultar',1,9,'{}','{}'),(40,'2026-03-05 09:33:22','modificar',1,9,'{\"id_anio_fiscal\":50,\"estado\":\"Abierto\",\"fecha_inicio\":\"2026-03-05\",\"fecha_cierre\":\"2027-03-05\",\"descripcion\":\"asdasdasd\",\"activo\":1}','{\"fecha_inicio\":\"2026-03-05\",\"estado\":\"Abierto\",\"descripcion\":\"aaaaaaaaaa\"}'),(41,'2026-03-05 09:33:22','consultar',1,9,'{}','{}'),(42,'2026-03-05 09:33:24','eliminar',1,9,'{\"id_anio_fiscal\":50,\"estado\":\"Abierto\",\"fecha_inicio\":\"2026-03-05\",\"fecha_cierre\":\"2027-03-05\",\"descripcion\":\"aaaaaaaaaa\",\"activo\":1}','{}'),(43,'2026-03-05 09:33:25','consultar',1,9,'{}','{}'),(44,'2026-03-05 09:33:49','cerrar sesion',1,14,'{}','{}'),(45,'2026-03-05 09:33:52','iniciar sesion',1,14,'{}','{}'),(46,'2026-03-05 09:34:00','cerrar sesion',1,14,'{}','{}'),(47,'2026-03-05 09:34:03','iniciar sesion',1,14,'{}','{}'),(48,'2026-03-05 09:34:15','consultar',1,5,'{}','{}'),(49,'2026-03-05 09:34:33','registrar',1,5,'{}','{\"titulo\":\"aaaaaa\",\"descripcion\":\"asdasdasd\",\"fecha\":\"2026-03-05\",\"prioridad\":\"2\",\"imagen\":\"lenguaje_comun_1772721272_713.PNG\"}'),(50,'2026-03-05 09:34:33','consultar',1,5,'{}','{}'),(51,'2026-03-05 09:34:43','consultar',1,5,'{}','{}'),(52,'2026-03-05 09:34:48','modificar',1,5,'{\"id_cartelera\":23,\"titulo\":\"aaaaaa\",\"descripcion\":\"asdasdasd\",\"fecha\":\"2026-03-05\",\"tipo\":\"\",\"imagen\":\"lenguaje_comun_1772721272_713.PNG\",\"prioridad\":\"2\",\"usuario_id\":1,\"nombre_usuario\":\"jesus\"}','{\"titulo\":\"aaaaaa\",\"descripcion\":\"asdasdasd\",\"fecha\":\"2026-03-05\",\"prioridad\":\"3\",\"imagen\":\"lenguaje_comun_1772721272_713.PNG\"}'),(53,'2026-03-05 09:34:48','consultar',1,5,'{}','{}'),(54,'2026-03-05 10:23:05','iniciar sesion',1,14,'{}','{}'),(55,'2026-03-05 10:24:39','consultar',1,1,'{}','{}'),(56,'2026-03-05 10:24:47','consultar',1,3,'{}','{}'),(57,'2026-03-05 10:25:12','consultar',1,1,'{}','{}'),(58,'2026-03-05 10:25:21','consultar',1,1,'{}','{}'),(59,'2026-03-05 10:25:26','consultar',1,2,'{}','{}'),(60,'2026-03-05 10:25:42','consultar',1,4,'{}','{}'),(61,'2026-03-05 10:25:52','consultar',1,14,'{}','{}'),(62,'2026-03-05 10:25:57','consultar',1,11,'{}','{}'),(63,'2026-03-05 10:27:06','consultar',1,1,'{}','{}'),(64,'2026-03-05 10:28:34','consultar',1,1,'{}','{}'),(65,'2026-03-05 10:28:36','consultar',1,9,'{}','{}'),(66,'2026-03-05 10:29:25','consultar',1,9,'{}','{}'),(67,'2026-03-05 10:29:40','consultar',1,9,'{}','{}'),(68,'2026-03-05 10:30:21','consultar',1,9,'{}','{}'),(69,'2026-03-05 10:30:26','consultar',1,1,'{}','{}'),(70,'2026-03-05 10:30:38','consultar',1,6,'{}','{}'),(71,'2026-03-05 10:30:56','consultar',1,6,'{}','{}'),(72,'2026-03-05 10:32:11','consultar',1,6,'{}','{}'),(73,'2026-03-05 10:33:18','consultar',1,12,'{}','{}'),(74,'2026-03-05 10:34:01','consultar',1,4,'{}','{}'),(75,'2026-03-05 10:34:19','consultar',1,4,'{}','{}'),(76,'2026-03-05 10:34:29','consultar',1,4,'{}','{}'),(77,'2026-03-05 10:34:57','consultar',1,4,'{}','{}'),(78,'2026-03-05 10:35:23','consultar',1,5,'{}','{}'),(79,'2026-03-05 10:36:59','consultar',1,2,'{}','{}'),(80,'2026-03-05 10:39:59','consultar',1,2,'{}','{}'),(81,'2026-03-05 10:41:34','consultar',1,3,'{}','{}'),(82,'2026-03-05 10:41:44','consultar',1,3,'{}','{}'),(83,'2026-03-05 10:43:43','consultar',1,3,'{}','{}'),(84,'2026-03-05 10:43:48','consultar',1,3,'{}','{}'),(85,'2026-03-05 10:45:26','consultar',1,1,'{}','{}'),(86,'2026-03-05 10:48:35','consultar',1,1,'{}','{}'),(87,'2026-03-05 10:50:21','consultar',1,11,'{}','{}'),(88,'2026-03-05 10:51:11','consultar',1,15,'{}','{}'),(89,'2026-03-05 10:53:32','consultar',1,15,'{}','{}'),(90,'2026-03-05 10:53:44','consultar',1,15,'{}','{}'),(91,'2026-03-05 10:54:39','consultar',1,15,'{}','{}'),(92,'2026-03-05 10:55:49','consultar',1,15,'{}','{}'),(93,'2026-03-05 10:56:23','consultar',1,15,'{}','{}'),(94,'2026-03-05 10:58:35','consultar',1,17,'{}','{}'),(95,'2026-03-05 10:58:52','consultar',1,7,'{}','{}'),(96,'2026-03-05 10:59:23','consultar',1,7,'{}','{}'),(97,'2026-03-05 11:03:19','consultar',1,14,'{}','{}'),(98,'2026-03-05 11:07:32','consultar',1,19,'{}','{}'),(99,'2026-03-05 11:07:40','registrar',1,19,'{}','{}');
/*!40000 ALTER TABLE `bitacora` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cartelera_virtual`
--

DROP TABLE IF EXISTS `cartelera_virtual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cartelera_virtual` (
  `id_cartelera` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `fecha` date NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `imagen` varchar(100) DEFAULT NULL,
  `prioridad` varchar(10) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  PRIMARY KEY (`id_cartelera`),
  KEY `cartelera_virtual_ibfk_1` (`usuario_id`),
  CONSTRAINT `cartelera_virtual_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cartelera_virtual`
--

LOCK TABLES `cartelera_virtual` WRITE;
/*!40000 ALTER TABLE `cartelera_virtual` DISABLE KEYS */;
INSERT INTO `cartelera_virtual` VALUES (19,'Bienvenidos','bienvenidos al 2026','2100-10-10','','WhatsApp_Image_2026-01-08_at_2.35.12_PM_1770308220.jpeg','1',1),(23,'aaaaaa','asdasdasd','2026-03-05','','lenguaje_comun_1772721272_713.PNG','3',1);
/*!40000 ALTER TABLE `cartelera_virtual` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eventos_sistema`
--

DROP TABLE IF EXISTS `eventos_sistema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eventos_sistema` (
  `id_evento` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_evento` varchar(50) NOT NULL,
  `tabla_origen` varchar(50) NOT NULL,
  `id_registro_origen` int(11) NOT NULL,
  `fecha_evento` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_evento`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos_sistema`
--

LOCK TABLES `eventos_sistema` WRITE;
/*!40000 ALTER TABLE `eventos_sistema` DISABLE KEYS */;
INSERT INTO `eventos_sistema` VALUES (6,'Nueva Mensualidad','mensualidad',510,'2026-02-13 18:00:47'),(7,'Nueva Mensualidad','mensualidad',513,'2026-02-14 16:45:19'),(8,'SALDO_BAJO','caja_chica',23,'2026-02-28 22:06:47'),(9,'NUEVA_MENSUALIDAD','mensualidad',547,'2026-03-02 16:36:12'),(10,'SALDO_BAJO','caja_chica',23,'2026-03-02 17:08:24');
/*!40000 ALTER TABLE `eventos_sistema` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modulos`
--

DROP TABLE IF EXISTS `modulos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modulos` (
  `id_modulo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_modulo`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modulos`
--

LOCK TABLES `modulos` WRITE;
/*!40000 ALTER TABLE `modulos` DISABLE KEYS */;
INSERT INTO `modulos` VALUES (1,'GESTIONAR_PAGOS',1),(2,'GESTIONAR_GASTOS',1),(3,'GESTIONAR_MENSUALIDAD',1),(4,'GESTIONAR_CAJA_CHICA',1),(5,'GESTIONAR_CARTELERA_VIRTUAL',1),(6,'GESTIONAR_APARTAMENTOS',1),(7,'GESTIONAR_SOLICITUD_GASTO',1),(8,'GESTIONAR_PRESUPUESTO',1),(9,'GESTIONAR_ANIO_FISCAL',1),(10,'GESTIONAR_CONFIGURACION',1),(11,'GESTIONAR_PROVEEDORES',1),(12,'GESTIONAR_BANCOS',1),(13,'GESTIONAR_TIPO_GASTO',1),(14,'GESTIONAR_USUARIOS',1),(15,'GESTIONAR_REPORTES',1),(16,'GESTIONAR_SEGURIDAD',1),(17,'GESTIONAR_ROLES',1),(18,'GESTIONAR_BITACORA',1),(19,'GESTIONAR_MANTENIMIENTO',1),(21,'GESTIONAR_HABITANTES',1),(22,'GESTIONAR_PERMISOS',1),(23,'GESTIONAR_MODULOS',1);
/*!40000 ALTER TABLE `modulos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificacion_evento`
--

DROP TABLE IF EXISTS `notificacion_evento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificacion_evento` (
  `notificacion_id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  PRIMARY KEY (`notificacion_id`,`evento_id`),
  KEY `evento_id` (`evento_id`),
  CONSTRAINT `notificacion_evento_ibfk_1` FOREIGN KEY (`notificacion_id`) REFERENCES `notificaciones` (`id_notificacion`) ON DELETE CASCADE,
  CONSTRAINT `notificacion_evento_ibfk_2` FOREIGN KEY (`evento_id`) REFERENCES `eventos_sistema` (`id_evento`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificacion_evento`
--

LOCK TABLES `notificacion_evento` WRITE;
/*!40000 ALTER TABLE `notificacion_evento` DISABLE KEYS */;
INSERT INTO `notificacion_evento` VALUES (150,6),(151,6),(152,7),(153,7),(154,8),(155,8),(156,9),(157,10),(158,10);
/*!40000 ALTER TABLE `notificacion_evento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha` date NOT NULL,
  `leido` tinyint(1) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  PRIMARY KEY (`id_notificacion`),
  KEY `notificaciones_ibfk_1` (`usuario_id`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
INSERT INTO `notificaciones` VALUES (150,'Mensualidad de Apartamentos','Ya se asginaron las mensualidades de este mes','2026-02-13',1,1),(151,'Mensualidad de Apartamentos','Ya se asginaron las mensualidades de este mes','2026-02-13',0,39),(152,'Mensualidad de Apartamentos','Ya se asginaron las mensualidades de este mes','2026-02-14',1,1),(153,'Mensualidad de Apartamentos','Ya se asginaron las mensualidades de este mes','2026-02-14',0,39),(154,'Saldo bajo en caja chica','La caja chica ID 23 tiene saldo de 90,00 Bs.','2026-02-28',0,1),(155,'Saldo bajo en caja chica','La caja chica ID 23 tiene saldo de 90,00 Bs.','2026-02-28',0,39),(156,'Nueva mensualidad disponible','Se han generado las mensualidades para el mes 3 del año 2025.','2026-03-02',0,27),(157,'Saldo bajo en caja chica','La caja chica ID 23 tiene saldo de 80,00 Bs.','2026-03-02',1,1),(158,'Saldo bajo en caja chica','La caja chica ID 23 tiene saldo de 80,00 Bs.','2026-03-02',0,39);
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permisos`
--

DROP TABLE IF EXISTS `permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permisos` (
  `id_permiso` int(11) NOT NULL AUTO_INCREMENT,
  `accion` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_permiso`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
INSERT INTO `permisos` VALUES (1,'registrar',1),(2,'consultar',1),(3,'modificar',1),(4,'eliminar',1);
/*!40000 ALTER TABLE `permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrador Global',1),(2,'test modificado',1),(3,'Propietario',1),(4,'Contador',1),(23,'Presidente',1),(68,'new',0);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tokens_seguridad`
--

DROP TABLE IF EXISTS `tokens_seguridad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tokens_seguridad` (
  `id_token` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `fecha_expiracion` datetime NOT NULL,
  `tipo` varchar(30) NOT NULL,
  PRIMARY KEY (`id_token`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `tokens_seguridad_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tokens_seguridad`
--

LOCK TABLES `tokens_seguridad` WRITE;
/*!40000 ALTER TABLE `tokens_seguridad` DISABLE KEYS */;
INSERT INTO `tokens_seguridad` VALUES (63,39,'08b3f587204551170b5adbceb2cfce9afc09eabdd3920e83421ee3b618ba7583','2026-03-01 06:13:56','RECUPERAR_CONTRASENIA'),(73,1,'956bdce2e37978681f13932354bda834fa215a343403fc6e237f5f9d93c64229','2026-04-04 17:23:06','RECORDAR_CONTRASENIA');
/*!40000 ALTER TABLE `tokens_seguridad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) NOT NULL,
  `apellido` varchar(20) NOT NULL,
  `correo` varchar(50) NOT NULL,
  `contrasenia` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `correo` (`correo`),
  KEY `usuarios_ibfk_1` (`rol_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'jesus','escalona','administrador@gmail.com','$2y$10$0Alzyzq0NExdQ.8mqEWsYObARp6msyLEmNphzvoQZpJit7JO3j84i',1,1),(2,'francisco','mendoza','franj@gmail.com','$2y$10$0KoHFVefo2ZZPv/nh0ocaefcDxfbOKXxcVhnUj844WynuyGhWpaV.',4,1),(27,'Pepes','Campos','pepe@gmail.com','$2y$10$WWp8M1SADJzTWAg910K.mewfZFglQF77ENnqPYLmq1U9AKmmeruY2',3,1),(39,'Yhsius','escalona','jesusgescalonae@gmail.com','$2y$10$uU20sappWrWieWXIfKmiIOAVjmTgChMGlNs7L8wkaOMlypFkWFiJO',2,1),(53,'perfil editado','perfil editado','UsuarioperfilEditada@gmail.com','$2y$10$AzKv19h61AeAkEYPA/FSA.buvyhYKoRfHT/kUFgMDSWE11PKjpBLS',4,0),(54,'usuario','cambiocontra','cambiocontrasenia@gmail.com','$2y$10$soYFxka95IzptEPe5eA.IONdFJI/geOcpt0K/L7aAKNIsTyn.5Nd2',23,0);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'seguridad_haydee_db'
--

--
-- Dumping routines for database 'seguridad_haydee_db'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_insertar_token` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `sp_insertar_token`(IN `p_usuario_id` INT, IN `p_tipo` VARCHAR(50), IN `p_token` VARCHAR(255), IN `p_fecha_expiracion` DATETIME)
BEGIN
    DELETE FROM tokens_seguridad 
    WHERE usuario_id = p_usuario_id 
      AND tipo = p_tipo;

    INSERT INTO tokens_seguridad (usuario_id, tipo, token, fecha_expiracion)
    VALUES (p_usuario_id, p_tipo, p_token, p_fecha_expiracion);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_notificar_administradores` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `sp_notificar_administradores`(IN `p_titulo` VARCHAR(100), IN `p_descripcion` TEXT, IN `p_tabla_origen` VARCHAR(50), IN `p_id_registro_origen` INT, IN `p_tipo_evento` VARCHAR(50))
BEGIN
    DECLARE v_evento_id INT;
    DECLARE v_usuario_id INT;
    DECLARE v_notificacion_id INT;
    DECLARE done INT DEFAULT FALSE;
    
    DECLARE cur_admins CURSOR FOR 
        SELECT id_usuario FROM usuarios WHERE rol_id IN (1, 2) AND activo = 1;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    INSERT INTO eventos_sistema (tipo_evento, tabla_origen, id_registro_origen, fecha_evento)
    VALUES (p_tipo_evento, p_tabla_origen, p_id_registro_origen, NOW());
    
    SET v_evento_id = LAST_INSERT_ID();

    OPEN cur_admins;

    read_loop: LOOP
        FETCH cur_admins INTO v_usuario_id;
        IF done THEN
            LEAVE read_loop;
        END IF;

        INSERT INTO notificaciones (titulo, descripcion, fecha, leido, usuario_id)
        VALUES (p_titulo, p_descripcion, CURDATE(), 0, v_usuario_id);
        
        SET v_notificacion_id = LAST_INSERT_ID();
        
        INSERT INTO notificacion_evento (notificacion_id, evento_id)
        VALUES (v_notificacion_id, v_evento_id);

    END LOOP;

    CLOSE cur_admins;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-05 11:07:46
