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
-- Table structure for table `bitacora`
--

DROP TABLE IF EXISTS `bitacora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bitacora` (
  `id_bitacora` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_hora` datetime NOT NULL,
  `accion` varchar(100) NOT NULL,
  `registro_alterado` varchar(255) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  PRIMARY KEY (`id_bitacora`),
  KEY `bitacora_ibfk_1` (`usuario_id`),
  KEY `bitacora_ibfk_2` (`modulo_id`),
  CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bitacora_ibfk_2` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--

LOCK TABLES `bitacora` WRITE;
/*!40000 ALTER TABLE `bitacora` DISABLE KEYS */;
INSERT INTO `bitacora` VALUES (1,'2025-11-16 11:39:26','iniciar sesion','NINGUNO',1,14),(2,'2025-11-16 11:39:58','consultar','TODOS LOS PROVEEDORES',1,11),(3,'2025-11-16 11:40:07','cerrar sesion','NINGUNO',1,14),(4,'2025-11-16 11:45:00','iniciar sesion','NINGUNO',1,14),(5,'2025-11-16 11:48:24','iniciar sesion','NINGUNO',1,14),(6,'2025-11-16 11:56:55','iniciar sesion','NINGUNO',1,14),(7,'2025-11-16 11:59:33','iniciar sesion','NINGUNO',1,14),(8,'2025-11-17 09:50:26','iniciar sesion','NINGUNO',1,14),(9,'2025-11-17 09:51:30','consultar','TODAS LAS PUBLICACIONES',1,5),(10,'2025-11-17 10:03:05','consultar','TODAS LAS PUBLICACIONES',1,5),(11,'2025-11-17 10:04:07','registrar','ññññÑÑÑÑ - 2025-11-01',1,5),(12,'2025-11-17 10:04:19','modificar','ññññÑÑÑÑe - 2025-11-01',1,5),(13,'2025-11-17 10:04:32','consultar','TODAS LAS PUBLICACIONES',1,5),(14,'2025-11-17 10:05:00','modificar','ññññÑÑÑÑe - 2025-11-01',1,5),(15,'2025-11-17 10:05:06','modificar','ññññÑÑÑÑe - 2025-11-01',1,5),(16,'2025-11-17 10:08:50','modificar','ññññÑÑÑÑe - 2025-11-01',1,5),(17,'2025-11-17 10:09:08','modificar','ññññÑÑÑÑe - 2025-11-01',1,5),(18,'2025-11-17 10:09:26','modificar','ññññÑÑÑÑe - 2025-11-01',1,5),(19,'2025-11-17 10:10:43','modificar','ññññÑÑÑÑe - 2025-11-01',1,5),(20,'2025-11-17 10:15:44','modificar','ññññÑÑÑÑe - 2025-11-01',1,5),(21,'2025-11-17 10:20:54','consultar','TODOS LOS REPORTES PDF',1,15),(22,'2025-11-17 10:21:31','consultar','TODOS LOS REPORTES PDF',1,15),(23,'2025-11-17 10:22:24','consultar','TODOS LOS REPORTES ESTADISTICOS',1,15),(24,'2025-11-17 10:22:45','consultar','TODOS LOS USUARIOS',1,14),(25,'2025-11-17 10:22:50','eliminar','nombre de prueba apellido de prueba',1,14),(26,'2025-11-17 10:22:51','consultar','TODOS LOS USUARIOS',1,14),(27,'2025-11-17 10:22:57','eliminar','nombre de prueba apellido de prueba',1,14),(28,'2025-11-17 10:22:57','consultar','TODOS LOS USUARIOS',1,14),(29,'2025-11-17 10:23:22','registrar','hrsdf sdfsd',1,14),(30,'2025-11-17 10:23:22','consultar','TODOS LOS USUARIOS',1,14),(31,'2025-11-17 10:24:29','consultar','TODOS LOS USUARIOS',1,14),(32,'2025-11-17 10:24:45','modificar','hrsdf sdfsd',1,14),(33,'2025-11-17 10:24:46','consultar','TODOS LOS USUARIOS',1,14),(34,'2025-11-17 10:26:02','consultar','Todos los roles de usuario',1,17),(35,'2025-11-17 10:26:18','consultar','COPIAS DE SEGURIDAD',1,19),(36,'2025-11-17 10:26:37','consultar','COPIAS DE SEGURIDAD',1,19),(37,'2025-11-17 10:37:44','consultar','COPIAS DE SEGURIDAD',1,19),(38,'2025-11-17 10:38:26','consultar','COPIAS DE SEGURIDAD',1,19),(39,'2025-11-17 10:39:06','consultar','COPIAS DE SEGURIDAD',1,19);
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cartelera_virtual`
--

LOCK TABLES `cartelera_virtual` WRITE;
/*!40000 ALTER TABLE `cartelera_virtual` DISABLE KEYS */;
INSERT INTO `cartelera_virtual` VALUES (13,'Titulo de Evento','Descripcion del evento xxxxxxxxx','2025-08-08','','CSS-Logo_1759451531.jpg','1',1),(15,'testa','testa','2000-10-10','',NULL,'1',1),(17,'Evento maratonico','Vamos a terminar los modulos muchachos','2025-10-06','','GUMBAR__A_1759736244_1759793820.jpg','1',1),(18,'ññññÑÑÑÑe','ñññÑÑÑÑ','2025-11-01','','CSS-Logo_1763388643.jpg','1',1);
/*!40000 ALTER TABLE `cartelera_virtual` ENABLE KEYS */;
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
  PRIMARY KEY (`id_modulo`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modulos`
--

LOCK TABLES `modulos` WRITE;
/*!40000 ALTER TABLE `modulos` DISABLE KEYS */;
INSERT INTO `modulos` VALUES (1,'GESTIONAR_PAGOS'),(2,'GESTIONAR_GASTOS'),(3,'GESTIONAR_MENSUALIDAD'),(4,'GESTIONAR_CAJA_CHICA'),(5,'GESTIONAR_CARTELERA_VIRTUAL'),(6,'GESTIONAR_APARTAMENTOS'),(7,'GESTIONAR_SOLICITUD_GASTO'),(8,'GESTIONAR_PRESUPUESTO'),(9,'GESTIONAR_ANIO_FISCAL'),(10,'GESTIONAR_CONFIGURACION'),(11,'GESTIONAR_PROVEEDORES'),(12,'GESTIONAR_BANCOS'),(13,'GESTIONAR_TIPO_GASTO'),(14,'GESTIONAR_USUARIOS'),(15,'GESTIONAR_REPORTES'),(16,'GESTIONAR_SEGURIDAD'),(17,'GESTIONAR_ROLES'),(18,'GESTIONAR_BITACORA'),(19,'GESTIONAR_MANTENIMIENTO');
/*!40000 ALTER TABLE `modulos` ENABLE KEYS */;
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
  `activo` varchar(20) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nombre_modulo` varchar(50) DEFAULT NULL,
  `referencia` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_notificacion`),
  KEY `notificaciones_ibfk_1` (`usuario_id`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
INSERT INTO `notificaciones` VALUES (1,'Cartelera Virtual','Se ha registrado una nueva publicación en la cartelera virtual.','2025-11-17','0',27,NULL,NULL),(2,'Cartelera Virtual','Se ha registrado una nueva publicación en la cartelera virtual.','2025-11-17','0',57,NULL,NULL);
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permisos_usuarios`
--

DROP TABLE IF EXISTS `permisos_usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permisos_usuarios` (
  `id_permiso_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_accion` varchar(50) DEFAULT NULL,
  `modulo_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_permiso_usuario`),
  KEY `permisos_usuarios_ibfk_1` (`modulo_id`),
  CONSTRAINT `permisos_usuarios_ibfk_1` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos_usuarios`
--

LOCK TABLES `permisos_usuarios` WRITE;
/*!40000 ALTER TABLE `permisos_usuarios` DISABLE KEYS */;
INSERT INTO `permisos_usuarios` VALUES (1,'registrar',1),(2,'consultar',1),(3,'modificar',1),(4,'eliminar',1),(5,'registrar',2),(6,'consultar',2),(7,'modificar',2),(8,'eliminar',2),(9,'registrar',3),(10,'consultar',3),(11,'modificar',3),(12,'eliminar',3),(13,'registrar',4),(14,'consultar',4),(15,'modificar',4),(16,'eliminar',4),(17,'registrar',5),(18,'consultar',5),(19,'modificar',5),(20,'eliminar',5),(21,'registrar',6),(22,'consultar',6),(23,'modificar',6),(24,'eliminar',6),(25,'registrar',7),(26,'consultar',7),(27,'modificar',7),(28,'eliminar',7),(30,'consultar',8),(33,'registrar',9),(34,'consultar',9),(35,'modificar',9),(36,'eliminar',9),(38,'consultar',10),(45,'registrar',11),(46,'consultar',11),(47,'modificar',11),(48,'eliminar',11),(50,'consultar',12),(54,'consultar',13),(57,'registrar',14),(58,'consultar',14),(59,'modificar',14),(60,'eliminar',14),(61,'consultar',15),(65,'consultar',16),(66,'registrar',8),(67,'modificar',8),(68,'eliminar',8),(69,'registrar',12),(70,'modificar',12),(71,'eliminar',12),(72,'registrar',13),(73,'modificar',13),(74,'eliminar',13),(75,'registrar',17),(76,'consultar',17),(77,'modificar',17),(78,'eliminar',17),(79,'registrar',18),(80,'consultar',18),(81,'modificar',18),(82,'eliminar',18),(83,'consultar',19);
/*!40000 ALTER TABLE `permisos_usuarios` ENABLE KEYS */;
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
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrador Global'),(2,'Administrador'),(3,'Propietario'),(4,'Contador'),(23,'Presidente');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles_permisos`
--

DROP TABLE IF EXISTS `roles_permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles_permisos` (
  `id_rol_permiso` int(11) NOT NULL AUTO_INCREMENT,
  `rol_id` int(11) DEFAULT NULL,
  `permiso_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_rol_permiso`),
  KEY `roles_permisos_ibfk_1` (`rol_id`),
  KEY `roles_permisos_ibfk_2` (`permiso_usuario_id`),
  CONSTRAINT `roles_permisos_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `roles_permisos_ibfk_2` FOREIGN KEY (`permiso_usuario_id`) REFERENCES `permisos_usuarios` (`id_permiso_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=949 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_permisos`
--

LOCK TABLES `roles_permisos` WRITE;
/*!40000 ALTER TABLE `roles_permisos` DISABLE KEYS */;
INSERT INTO `roles_permisos` VALUES (441,3,1),(442,3,2),(443,3,3),(444,3,4),(445,4,1),(446,4,2),(447,4,3),(448,4,4),(449,4,5),(450,4,6),(451,4,7),(452,4,8),(453,4,9),(454,4,10),(455,4,11),(456,4,12),(457,4,13),(458,4,14),(459,4,15),(460,4,16),(461,4,17),(462,4,18),(463,4,19),(464,4,20),(465,4,50),(687,2,1),(688,2,2),(689,2,3),(690,2,4),(691,2,5),(692,2,6),(693,2,7),(694,2,8),(695,2,9),(696,2,10),(697,2,11),(698,2,12),(699,2,13),(700,2,14),(701,2,15),(702,2,16),(703,2,17),(704,2,18),(705,2,19),(706,2,20),(707,2,21),(708,2,22),(709,2,23),(710,2,24),(711,2,25),(712,2,26),(713,2,27),(714,2,28),(715,2,30),(716,2,33),(717,2,34),(718,2,35),(719,2,36),(721,2,38),(724,2,45),(725,2,46),(726,2,47),(727,2,48),(728,2,50),(729,2,54),(730,2,57),(731,2,58),(732,2,59),(733,2,60),(734,2,61),(738,2,65),(775,1,1),(776,1,2),(777,1,3),(778,1,4),(779,1,5),(780,1,6),(781,1,7),(782,1,8),(783,1,9),(784,1,10),(785,1,11),(786,1,12),(787,1,13),(788,1,14),(789,1,15),(790,1,16),(791,1,17),(792,1,18),(793,1,19),(794,1,20),(795,1,21),(796,1,22),(797,1,23),(798,1,24),(799,1,25),(800,1,26),(801,1,27),(802,1,28),(803,1,30),(804,1,66),(805,1,67),(806,1,68),(807,1,33),(808,1,34),(809,1,35),(810,1,36),(811,1,38),(812,1,45),(813,1,46),(814,1,47),(815,1,48),(816,1,50),(817,1,69),(818,1,70),(819,1,71),(820,1,54),(821,1,72),(822,1,73),(823,1,74),(824,1,57),(825,1,58),(826,1,59),(827,1,60),(828,1,61),(829,1,65),(830,1,75),(831,1,76),(832,1,77),(833,1,78),(834,1,79),(835,1,80),(836,1,81),(837,1,82),(838,1,83),(907,23,2),(908,23,6),(909,23,10),(910,23,14),(911,23,18),(912,23,22),(913,23,26),(914,23,30),(915,23,34),(916,23,38),(917,23,46),(918,23,50);
/*!40000 ALTER TABLE `roles_permisos` ENABLE KEYS */;
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
  `token` varchar(255) DEFAULT NULL,
  `duracion_token` datetime DEFAULT NULL,
  `token_recuerdame` varchar(255) DEFAULT NULL,
  `duracion_token_recuerdame` datetime DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `correo` (`correo`),
  KEY `usuarios_ibfk_1` (`rol_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'jesus','escalona','administrador@gmail.com','$2y$10$KV7wFnyGSxniLWrLgS6M7.OzJvhlvhtxDezLVHftWUFg46qFMVFC2',1,NULL,NULL,NULL,NULL),(2,'francisco','mendoza','fran@gmasil.com','$2y$10$NnxKPDNTGUuo6LKSUyo1FeyHDb/DRhvK3gKAWm2wpwe7ePfhBEbky',2,NULL,NULL,NULL,NULL),(27,'Pepe','Campos','pepe@gmail.com','$2y$10$FPga2GCcdJnZUvs9mY9ZSuFixqONdXDpUrOFPVP5INoplWyM2fabu',3,NULL,NULL,NULL,NULL),(39,'Yhsius','escalona','jesusgescalonae@gmail.com','$2y$10$zacNmkVWpijdz/UqMNJqhevACUdFUoa3Ce.YhGf7Z5Xee.f46g4Hi',2,NULL,NULL,NULL,NULL),(51,'usuario','pruebaT','prueba@gmail.com','123123123',23,'token de prueba','2023-08-24 16:43:41',NULL,NULL),(53,'perfil editado','perfil editado','UsuarioperfilEditada@gmail.com','$2y$10$AzKv19h61AeAkEYPA/FSA.buvyhYKoRfHT/kUFgMDSWE11PKjpBLS',4,NULL,NULL,NULL,NULL),(54,'usuario','cambiocontra','cambiocontrasenia@gmail.com','$2y$10$soYFxka95IzptEPe5eA.IONdFJI/geOcpt0K/L7aAKNIsTyn.5Nd2',23,NULL,NULL,NULL,NULL),(55,'borra','token','tokenborrar@gmail.com','borra el token',4,NULL,NULL,NULL,NULL),(56,'perfil editado','perfil editado','perfilEditada@gmail.com','perfil editar',4,NULL,NULL,NULL,NULL),(57,'token','insertar','agregartoken@gmail.com','agrega un token',3,'token de prueba agregado','2023-08-24 16:43:41','$2y$10$3dBoZQur3FrAhz5MJzGhxuwgpYWiYqWVPF38L/9rMaL7NI8jlrKEe','2025-11-21 23:14:42'),(74,'recuerdame','borrar','correoBorrarRecuerdame@gmail.com','12345',2,NULL,NULL,NULL,NULL),(76,'token','recuerdame','recuerdame@gmaiil.com','mama coco',4,NULL,'0000-00-00 00:00:00','2023-08-24 16:43:42',NULL),(78,'token','recuerdame','recuerdame2@gmaiil.com','mama coco',4,NULL,NULL,'token recuerdame de prueba','2023-08-24 16:43:42'),(84,'hrsdf','sdfsd','sfsdf@gasd.ocm','$2y$10$wo52gYg2acvTejI2EOnue.CHHYcuqFDSs0Q0uelxuHeW0Yu9CZaD.',2,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-17  9:39:17
