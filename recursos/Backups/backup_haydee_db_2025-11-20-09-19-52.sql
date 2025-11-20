-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: haydee_db
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
-- Table structure for table `anio_fiscal`
--

DROP TABLE IF EXISTS `anio_fiscal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anio_fiscal` (
  `id_anio_fiscal` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_inicio` date NOT NULL,
  `fecha_cierre` date DEFAULT NULL,
  `estado` varchar(20) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  PRIMARY KEY (`id_anio_fiscal`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anio_fiscal`
--

LOCK TABLES `anio_fiscal` WRITE;
/*!40000 ALTER TABLE `anio_fiscal` DISABLE KEYS */;
INSERT INTO `anio_fiscal` VALUES (24,'2025-01-01','2026-01-01','Abierto','anio Modificado 9477');
/*!40000 ALTER TABLE `anio_fiscal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apartamentos`
--

DROP TABLE IF EXISTS `apartamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apartamentos` (
  `id_apartamento` int(11) NOT NULL AUTO_INCREMENT,
  `nro_apartamento` varchar(3) NOT NULL,
  `porcentaje_participacion` float NOT NULL,
  `gas` tinyint(1) NOT NULL,
  `agua` tinyint(1) NOT NULL,
  `alquilado` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_apartamento`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apartamentos`
--

LOCK TABLES `apartamentos` WRITE;
/*!40000 ALTER TABLE `apartamentos` DISABLE KEYS */;
INSERT INTO `apartamentos` VALUES (30,'1-2',23,1,1,1),(31,'2-3',24,1,1,1),(32,'2-1',1,2,1,1);
/*!40000 ALTER TABLE `apartamentos` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER after_delete_apartamento
AFTER DELETE ON apartamentos
FOR EACH ROW
BEGIN
    DELETE habitantes FROM habitantes
    LEFT JOIN habitantes_apartamentos ON habitantes.id_habitante = habitantes_apartamentos.habitante_id
    WHERE habitantes_apartamentos.habitante_id IS NULL;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `banco_transacciones`
--

DROP TABLE IF EXISTS `banco_transacciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banco_transacciones` (
  `id_banco_transaccion` int(11) NOT NULL AUTO_INCREMENT,
  `referencia` varchar(20) NOT NULL,
  `imagen` varchar(100) NOT NULL,
  `detalle_pago_id` int(11) DEFAULT NULL,
  `detalle_gasto_id` int(11) DEFAULT NULL,
  `banco_id` int(11) NOT NULL,
  PRIMARY KEY (`id_banco_transaccion`),
  UNIQUE KEY `referencia` (`referencia`),
  KEY `banco_id` (`banco_id`),
  KEY `detalle_pago_id` (`detalle_pago_id`),
  KEY `gasto_id` (`detalle_gasto_id`),
  CONSTRAINT `banco_transacciones_ibfk_1` FOREIGN KEY (`banco_id`) REFERENCES `bancos` (`id_banco`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `banco_transacciones_ibfk_2` FOREIGN KEY (`detalle_pago_id`) REFERENCES `detalles_pagos` (`id_detalle_pago`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `banco_transacciones_ibfk_3` FOREIGN KEY (`detalle_gasto_id`) REFERENCES `detalles_gastos` (`id_detalle_gasto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=190 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banco_transacciones`
--

LOCK TABLES `banco_transacciones` WRITE;
/*!40000 ALTER TABLE `banco_transacciones` DISABLE KEYS */;
INSERT INTO `banco_transacciones` VALUES (102,'1212','',NULL,118,1),(106,'123123','lenguaje_comun_1758973341_948.PNG',NULL,120,6),(116,'12312','1729083773_julio_1759864670_790.png',NULL,142,1),(188,'7546345','fiabil_1763601294_142.PNG',355,NULL,1),(189,'34524','lenguaje_comun_1763601294_138.PNG',356,NULL,6);
/*!40000 ALTER TABLE `banco_transacciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bancos`
--

DROP TABLE IF EXISTS `bancos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bancos` (
  `id_banco` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_banco` varchar(50) NOT NULL,
  `codigo` varchar(7) NOT NULL,
  `numero_cuenta` varchar(50) NOT NULL,
  `telefono_afiliado` varchar(50) NOT NULL,
  `cedula_afiliada` varchar(20) NOT NULL,
  PRIMARY KEY (`id_banco`),
  UNIQUE KEY `numero_cuenta` (`numero_cuenta`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bancos`
--

LOCK TABLES `bancos` WRITE;
/*!40000 ALTER TABLE `bancos` DISABLE KEYS */;
INSERT INTO `bancos` VALUES (1,'venezuela','0102','0102123412124232323','04152456842','23232421'),(6,'Banesco','0117','1242342342424121211','04142584985','V5464565');
/*!40000 ALTER TABLE `bancos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caja_chica`
--

DROP TABLE IF EXISTS `caja_chica`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caja_chica` (
  `id_caja_chica` int(11) NOT NULL AUTO_INCREMENT,
  `fondo_fijo` float NOT NULL,
  `saldo_actual` float NOT NULL,
  `estado` varchar(20) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `fecha_creacion` date NOT NULL DEFAULT current_timestamp(),
  `anio_fiscal_id` int(11) NOT NULL,
  PRIMARY KEY (`id_caja_chica`),
  KEY `anio_fiscal_id` (`anio_fiscal_id`),
  CONSTRAINT `caja_chica_ibfk_1` FOREIGN KEY (`anio_fiscal_id`) REFERENCES `anio_fiscal` (`id_anio_fiscal`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caja_chica`
--

LOCK TABLES `caja_chica` WRITE;
/*!40000 ALTER TABLE `caja_chica` DISABLE KEYS */;
INSERT INTO `caja_chica` VALUES (21,115,55,'Abierto','Caja chica 2025','2025-01-01',24);
/*!40000 ALTER TABLE `caja_chica` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_gastos`
--

DROP TABLE IF EXISTS `detalles_gastos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_gastos` (
  `id_detalle_gasto` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `monto` float NOT NULL,
  `monto_dolar` float NOT NULL,
  `metodo_pago` varchar(20) NOT NULL,
  `gasto_id` int(11) NOT NULL,
  `descripcion_detalle_gasto` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id_detalle_gasto`),
  KEY `gasto_id` (`gasto_id`),
  CONSTRAINT `detalles_gastos_ibfk_1` FOREIGN KEY (`gasto_id`) REFERENCES `gastos` (`id_gasto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_gastos`
--

LOCK TABLES `detalles_gastos` WRITE;
/*!40000 ALTER TABLE `detalles_gastos` DISABLE KEYS */;
INSERT INTO `detalles_gastos` VALUES (117,'2025-09-02',2,0,'Efectivo',94,'se pago el gas en efectivo'),(118,'2025-09-01',1,0,'Pago Movil',94,'asdasdadasds'),(119,'2025-09-10',12,0,'Efectivo',101,'pago en efectivo'),(120,'2025-09-01',1,0,'Pago Movil',101,'asdassadasdas'),(134,'2025-10-06',45,0,'Efectivo',109,'Reposición de Caja Chica'),(135,'2025-10-06',25,0,'Efectivo',110,'Reposición de Caja Chica'),(136,'2025-10-06',35,0,'Efectivo',111,'Reposición de Caja Chica'),(142,'2025-10-07',10,0,'Transferencia',112,'Pago en transferencia'),(143,'2010-10-10',10,0,'Efectivo',112,'Pago en efectivo'),(146,'2025-10-07',15,0,'Efectivo',114,'Reposición de Caja Chica'),(148,'2025-10-13',15,0,'Efectivo',116,'Reposición de Caja Chica'),(149,'2010-10-10',11,0,'Pago Movil',117,'texto de ejemploasd'),(150,'2010-10-10',11,0,'Pago Movil',118,'texto de ejemploasd');
/*!40000 ALTER TABLE `detalles_gastos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_pagos`
--

DROP TABLE IF EXISTS `detalles_pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_pagos` (
  `id_detalle_pago` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `monto` float NOT NULL,
  `monto_dolar` float NOT NULL,
  `tipo_pago` varchar(20) NOT NULL,
  `pago_id` int(11) NOT NULL,
  PRIMARY KEY (`id_detalle_pago`),
  KEY `detalles_pagos_ibfk_1` (`pago_id`),
  CONSTRAINT `detalles_pagos_ibfk_1` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id_pago`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=357 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_pagos`
--

LOCK TABLES `detalles_pagos` WRITE;
/*!40000 ALTER TABLE `detalles_pagos` DISABLE KEYS */;
INSERT INTO `detalles_pagos` VALUES (353,'2025-11-12',15,0.06,'Efectivo',109),(354,'2025-11-19',10,0.04,'Efectivo',109),(355,'2025-11-05',41,0.17,'Transferencia',109),(356,'2025-10-28',13,0.05,'Pago Movil',109);
/*!40000 ALTER TABLE `detalles_pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_presupuesto`
--

DROP TABLE IF EXISTS `detalles_presupuesto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_presupuesto` (
  `id_detalle_presupuesto` int(11) NOT NULL AUTO_INCREMENT,
  `monto_detalle` float NOT NULL,
  `nombre_detalle` varchar(50) NOT NULL,
  `presupuesto_id` int(11) NOT NULL,
  `tipo_gasto_id` int(11) NOT NULL,
  PRIMARY KEY (`id_detalle_presupuesto`),
  KEY `presupuesto_id` (`presupuesto_id`),
  KEY `tipo_gasto_id` (`tipo_gasto_id`),
  CONSTRAINT `detalles_presupuesto_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuesto` (`id_presupuesto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detalles_presupuesto_ibfk_2` FOREIGN KEY (`tipo_gasto_id`) REFERENCES `tipo_gasto` (`id_tipo_gasto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1215 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_presupuesto`
--

LOCK TABLES `detalles_presupuesto` WRITE;
/*!40000 ALTER TABLE `detalles_presupuesto` DISABLE KEYS */;
INSERT INTO `detalles_presupuesto` VALUES (660,42,'GAS LARA',70,1),(661,15,'CORPOELEC',70,2),(662,34,'HIDROLARA',70,2),(663,23,'Trabajadora Residencial',70,3),(664,5623,'Bono de alimentacion',70,3),(665,2,'Bono de ayuda',70,3),(666,123,'Seguridad Social',70,3),(667,21,'Mantenimiento ascensor',70,4),(668,23,'Bolsas de Basura',70,9),(669,21,'Productos de Limpieza',70,9),(670,124,'Comisiones Bancaracias',70,10),(671,12,'Exencion cuota del administrador',70,10),(745,1,'GAS LARA',57,1),(746,2,'gato',57,1),(747,2,'CORPOELEC',57,2),(748,2,'HIDROLARA',57,2),(749,2,'pepe',57,2),(750,3,'Mantenimiento ascensor',57,4),(751,4,'Bolsas de Basura',57,9),(752,4,'Productos de Limpieza',57,9),(753,3,'Trabajadora Residencial',57,3),(754,3,'Bono de alimentacion',57,3),(755,3,'Bono de ayuda',57,3),(756,3,'Seguridad Social',57,3),(757,5,'Comisiones Bancaracias',57,10),(758,5,'Exencion cuota del administrador',57,10),(759,179.43,'GAS LARA',67,1),(760,222,'CORPOELEC',67,2),(761,333,'HIDROLARA',67,2),(762,777,'Bolsas de Basura',67,9),(763,123,'Productos de Limpieza',67,9),(764,666,'Mantenimiento ascensor',67,4),(765,444,'Trabajadora Residencial',67,3),(766,555,'Bono de alimentacion',67,3),(767,555,'Bono de ayuda',67,3),(768,666,'Seguridad Social',67,3),(769,321,'Comisiones Bancaracias',67,10),(770,234,'Exencion cuota del administrador',67,10),(771,23,'GAS LARA',68,1),(772,24,'CORPOELEC',68,2),(773,24,'HIDROLARA',68,2),(774,25,'Trabajadora Residencial',68,3),(775,52,'Bono de alimentacion',68,3),(776,12,'Bono de ayuda',68,3),(777,31,'Seguridad Social',68,3),(778,34,'Bolsas de Basura',68,9),(779,53,'Productos de Limpieza',68,9),(780,42,'Mantenimiento ascensor',68,4),(781,546,'Comisiones Bancaracias',68,10),(782,34,'Exencion cuota del administrador',68,10),(819,20,'GAS LARA',76,1),(820,12,'Trabajadora Residencial',76,3),(821,10,'Bono de alimentacion',76,3),(822,15,'Bono de ayuda',76,3),(823,10,'Seguridad Social',76,3),(824,15,'Mantenimiento ascensor',76,4),(825,41,'Bolsas de Basura',76,9),(826,41,'Productos de Limpieza',76,9),(827,54,'Comisiones Bancaracias',76,10),(828,42,'Exencion cuota del administrador',76,10),(829,15,'CORPOELEC',76,2),(830,10,'HIDROLARA',76,2),(831,13,'GAS LARA',77,1),(832,14,'CORPOELEC',77,2),(833,2,'HIDROLARA',77,2),(834,12,'Mantenimiento ascensor',77,4),(835,24,'Bolsas de Basura',77,9),(836,12,'Productos de Limpieza',77,9),(837,12,'Comisiones Bancaracias',77,10),(838,124,'Exencion cuota del administrador',77,10),(839,23,'Trabajadora Residencial',77,3),(840,23,'Bono de alimentacion',77,3),(841,42,'Bono de ayuda',77,3),(842,42,'Seguridad Social',77,3),(903,13,'GAS LARA',84,1),(904,14,'CORPOELEC',84,2),(905,2,'HIDROLARA',84,2),(906,52,'Trabajadora Residencial',84,3),(907,32,'Bono de alimentacion',84,3),(908,24,'Bono de ayuda',84,3),(909,12,'Seguridad Social',84,3),(910,41,'Mantenimiento ascensor',84,4),(911,23,'Bolsas de Basura',84,9),(912,24,'Productos de Limpieza',84,9),(913,23,'Comisiones Bancaracias',84,10),(914,21,'Exencion cuota del administrador',84,10),(1095,1000,'GAS LARA',98,1),(1096,0,'CORPOELEC',98,2),(1097,0,'HIDROLARA',98,2),(1098,0,'Trabajadora Residencial',98,3),(1099,0,'Bono de alimentacion',98,3),(1100,0,'Bono de ayuda',98,3),(1101,0,'Seguridad Social',98,3),(1102,0,'Mantenimiento ascensor',98,4),(1103,0,'Bolsas de Basura',98,9),(1104,0,'Productos de Limpieza',98,9),(1105,0,'Comisiones Bancaracias',98,10),(1106,0,'Exencion cuota del administrador',98,10),(1179,3,'CORPOELEC',56,2),(1180,4,'HIDROLARA',56,2),(1181,2,'Bolsas de Basura',56,9),(1182,3,'Productos de Limpieza',56,9),(1183,2,'GAS LARA',56,1),(1184,1,'Mantenimiento ascensor',56,4),(1185,5,'Trabajadora Residencial',56,3),(1186,6,'Bono de alimentacion',56,3),(1187,7,'Bono de ayuda',56,3),(1188,8,'Seguridad Social',56,3),(1189,4,'Comisiones Bancaracias',56,10),(1190,5,'Exencion cuota del administrador',56,10);
/*!40000 ALTER TABLE `detalles_presupuesto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gastos`
--

DROP TABLE IF EXISTS `gastos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gastos` (
  `id_gasto` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(20) NOT NULL,
  `tipo_gasto_id` int(11) NOT NULL,
  `solicitud_id` int(11) DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `descripcion_gasto` text DEFAULT NULL,
  PRIMARY KEY (`id_gasto`),
  KEY `proveedor_id` (`proveedor_id`),
  KEY `tipo_gasto_id` (`tipo_gasto_id`),
  KEY `solicitud_id` (`solicitud_id`),
  CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id_proveedor`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `gastos_ibfk_2` FOREIGN KEY (`tipo_gasto_id`) REFERENCES `tipo_gasto` (`id_tipo_gasto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `gastos_ibfk_3` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_gasto` (`id_solicitud`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gastos`
--

LOCK TABLES `gastos` WRITE;
/*!40000 ALTER TABLE `gastos` DISABLE KEYS */;
INSERT INTO `gastos` VALUES (94,'fijo',1,8,1,'pago del gas'),(101,'fijo',2,8,2,'pago del cable'),(109,'variable',10,NULL,NULL,'Reposición de Caja Chica'),(110,'variable',10,NULL,NULL,'Reposición de Caja Chica'),(111,'variable',10,NULL,NULL,'Reposición de Caja Chica'),(112,'fijo',1,8,1,'pago del gas'),(114,'variable',10,NULL,NULL,'Reposición de Caja Chica'),(116,'variable',10,NULL,NULL,'Reposición de Caja Chica'),(117,'fijo',1,8,2,'Testso eje esaasda'),(118,'fijo',1,8,2,'Testso eje esaasda');
/*!40000 ALTER TABLE `gastos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `habitantes`
--

DROP TABLE IF EXISTS `habitantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `habitantes` (
  `id_habitante` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) NOT NULL,
  `apellido` varchar(20) NOT NULL,
  `cedula` varchar(9) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `correo` varchar(50) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `sexo` varchar(10) NOT NULL,
  PRIMARY KEY (`id_habitante`) USING BTREE,
  UNIQUE KEY `cedula` (`cedula`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `habitantes`
--

LOCK TABLES `habitantes` WRITE;
/*!40000 ALTER TABLE `habitantes` DISABLE KEYS */;
INSERT INTO `habitantes` VALUES (32,'carlos','rodriega','V30601403','01241223423','carlos@gmail.com','2001-10-10','Masculino');
/*!40000 ALTER TABLE `habitantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `habitantes_apartamentos`
--

DROP TABLE IF EXISTS `habitantes_apartamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `habitantes_apartamentos` (
  `id_habitante_apartamento` int(11) NOT NULL AUTO_INCREMENT,
  `apartamento_id` int(11) NOT NULL,
  `habitante_id` int(11) NOT NULL,
  `tipo_vinculo` varchar(20) NOT NULL,
  PRIMARY KEY (`id_habitante_apartamento`),
  KEY `apartamento_id` (`apartamento_id`),
  KEY `persona_id` (`habitante_id`),
  CONSTRAINT `habitantes_apartamentos_ibfk_1` FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos` (`id_apartamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `habitantes_apartamentos_ibfk_2` FOREIGN KEY (`habitante_id`) REFERENCES `habitantes` (`id_habitante`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `habitantes_apartamentos`
--

LOCK TABLES `habitantes_apartamentos` WRITE;
/*!40000 ALTER TABLE `habitantes_apartamentos` DISABLE KEYS */;
INSERT INTO `habitantes_apartamentos` VALUES (32,30,32,'Propietario');
/*!40000 ALTER TABLE `habitantes_apartamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensualidad`
--

DROP TABLE IF EXISTS `mensualidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mensualidad` (
  `id_mensualidad` int(11) NOT NULL AUTO_INCREMENT,
  `monto` float NOT NULL,
  `tasa_dolar` float NOT NULL,
  `mes` varchar(2) NOT NULL,
  `anio` varchar(4) NOT NULL,
  `apartamento_id` int(11) NOT NULL,
  `porcentaje_interes` int(11) NOT NULL DEFAULT 10,
  `limite_mensualidad` int(11) NOT NULL DEFAULT 15,
  PRIMARY KEY (`id_mensualidad`),
  KEY `apartamento_id` (`apartamento_id`),
  CONSTRAINT `mensualidad_ibfk_1` FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos` (`id_apartamento`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=464 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensualidad`
--

LOCK TABLES `mensualidad` WRITE;
/*!40000 ALTER TABLE `mensualidad` DISABLE KEYS */;
INSERT INTO `mensualidad` VALUES (436,8.74,237.75,'1','2025',30,65,15),(438,127.65,1,'3','2025',30,7910,15),(439,133.4,1,'4','2025',30,6110,15),(440,40.94,237.75,'5','2025',30,10010,15),(441,22.08,1,'6','2025',30,1210,15),(442,31.28,236.84,'7','2025',30,10,15),(454,2.64,237.75,'1','2025',31,65,15),(455,0.09,237.75,'1','2025',32,65,15),(462,10.08,237.75,'5','2025',31,10010,15),(463,0.42,237.75,'5','2025',32,10010,15);
/*!40000 ALTER TABLE `mensualidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_caja`
--

DROP TABLE IF EXISTS `movimientos_caja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movimientos_caja` (
  `id_movimiento_caja` int(11) NOT NULL AUTO_INCREMENT,
  `concepto` varchar(100) NOT NULL,
  `monto` float NOT NULL,
  `fecha` date NOT NULL,
  `estado` varchar(50) NOT NULL,
  `caja_chica_id` int(11) NOT NULL,
  `gasto_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_movimiento_caja`),
  KEY `caja_chica_id` (`caja_chica_id`),
  KEY `gasto_id` (`gasto_id`),
  CONSTRAINT `movimientos_caja_ibfk_1` FOREIGN KEY (`caja_chica_id`) REFERENCES `caja_chica` (`id_caja_chica`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `movimientos_caja_ibfk_2` FOREIGN KEY (`gasto_id`) REFERENCES `gastos` (`id_gasto`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_caja`
--

LOCK TABLES `movimientos_caja` WRITE;
/*!40000 ALTER TABLE `movimientos_caja` DISABLE KEYS */;
INSERT INTO `movimientos_caja` VALUES (1,'Comprar cafe',10,'2025-10-04','Reposado',21,109),(3,'arroz',11,'2025-10-04','Reposado',21,109),(8,'soda',12,'2010-10-10','Reposado',21,109),(9,'gasto menor',0.55,'2025-10-03','Reposado',21,109),(10,'gasto menor',54.39,'2025-10-01','Reposado',21,109),(11,'limpieza',50,'2025-10-01','Reposado',21,109),(13,'gasto menor',10,'2025-10-07','Reposado',21,110),(14,'gasto menor',5,'2025-10-06','Reposado',21,110),(15,'sal',5,'2025-10-01','Reposado',21,110),(16,'mas cafe',25,'2025-10-06','Reposado',21,111),(17,'mas coffee',5,'2025-01-10','Reposado',21,114),(18,'se compro cafe',10,'2010-10-10','Reposado',21,114),(20,'traspore',25,'2025-10-08','Reposado',21,116),(23,'test',15,'2025-10-10','Reposado',21,116),(24,'gato',10,'2025-10-01','Pendiente por reposicion',21,NULL),(25,'cocepto de gasto Varios Selenium ',10,'2025-11-18','Pendiente por reposicion',21,NULL),(26,'cocepto de gasto Varios Selenium ',10,'2025-11-18','Pendiente por reposicion',21,NULL),(27,'cocepto de gasto Varios Selenium ',10,'2025-11-18','Pendiente por reposicion',21,NULL),(28,'cocepto ',10,'2025-11-18','Pendiente por reposicion',21,NULL),(29,'gasto Modificado 8743',10,'2025-11-18','Pendiente por reposicion',21,NULL);
/*!40000 ALTER TABLE `movimientos_caja` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `tr_after_insert_movimiento_caja` AFTER INSERT ON `movimientos_caja` FOR EACH ROW BEGIN
    UPDATE caja_chica
    SET saldo_actual = saldo_actual - NEW.monto
    WHERE id_caja_chica = NEW.caja_chica_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `tr_after_update_movimiento_caja` AFTER UPDATE ON `movimientos_caja` FOR EACH ROW BEGIN
    -- Revertir el monto antiguo
    UPDATE caja_chica
    SET saldo_actual = saldo_actual + OLD.monto
    WHERE id_caja_chica = OLD.caja_chica_id;

    -- Aplicar el nuevo monto
    UPDATE caja_chica
    SET saldo_actual = saldo_actual - NEW.monto
    WHERE id_caja_chica = NEW.caja_chica_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `tr_after_delete_movimiento_caja` AFTER DELETE ON `movimientos_caja` FOR EACH ROW BEGIN
    UPDATE caja_chica
    SET saldo_actual = saldo_actual + OLD.monto
    WHERE id_caja_chica = OLD.caja_chica_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `pagos`
--

DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL AUTO_INCREMENT,
  `estado` varchar(20) NOT NULL,
  `observacion` text DEFAULT NULL,
  PRIMARY KEY (`id_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos`
--

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
INSERT INTO `pagos` VALUES (109,'Procesado','sadasd');
/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos_mensualidad`
--

DROP TABLE IF EXISTS `pagos_mensualidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagos_mensualidad` (
  `id_pago_mensualidad` int(11) NOT NULL AUTO_INCREMENT,
  `detalle_pago_id` int(11) DEFAULT NULL,
  `mensualidad_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_pago_mensualidad`),
  KEY `mensualidad_id` (`mensualidad_id`),
  KEY `pagos_mensualidad_ibfk_1` (`detalle_pago_id`),
  CONSTRAINT `pagos_mensualidad_ibfk_2` FOREIGN KEY (`mensualidad_id`) REFERENCES `mensualidad` (`id_mensualidad`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pagos_mensualidad_ibfk_3` FOREIGN KEY (`detalle_pago_id`) REFERENCES `detalles_pagos` (`id_detalle_pago`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=332 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos_mensualidad`
--

LOCK TABLES `pagos_mensualidad` WRITE;
/*!40000 ALTER TABLE `pagos_mensualidad` DISABLE KEYS */;
INSERT INTO `pagos_mensualidad` VALUES (328,353,438),(329,354,438),(330,355,438),(331,356,438);
/*!40000 ALTER TABLE `pagos_mensualidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `presupuesto`
--

DROP TABLE IF EXISTS `presupuesto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `presupuesto` (
  `id_presupuesto` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `cuota_reserva` decimal(15,0) NOT NULL,
  `observacion` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_presupuesto`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `presupuesto`
--

LOCK TABLES `presupuesto` WRITE;
/*!40000 ALTER TABLE `presupuesto` DISABLE KEYS */;
INSERT INTO `presupuesto` VALUES (56,'2025-01-01',1,'presupuesto modificado 5864'),(57,'2025-02-01',1,'Mes de Febrero'),(67,'2025-03-01',122,'Mes de Marzo'),(68,'2025-04-01',12,'Mes de abril'),(70,'2025-05-01',12,'mayo'),(76,'2025-06-01',10,'asdas'),(77,'2025-07-01',12,'asdas'),(84,'2025-08-01',12,'agosto'),(98,'2025-09-01',1000,'presupuesto Varios Selenium 2024');
/*!40000 ALTER TABLE `presupuesto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `presupuesto_mensualidad`
--

DROP TABLE IF EXISTS `presupuesto_mensualidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `presupuesto_mensualidad` (
  `id_presupuesto_mensualidad` int(11) NOT NULL AUTO_INCREMENT,
  `presupuesto_id` int(11) NOT NULL,
  `mensualidad_id` int(11) NOT NULL,
  PRIMARY KEY (`id_presupuesto_mensualidad`),
  KEY `gasto_id` (`presupuesto_id`),
  KEY `mesualidad_id` (`mensualidad_id`),
  CONSTRAINT `presupuesto_mensualidad_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `detalles_presupuesto` (`id_detalle_presupuesto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `presupuesto_mensualidad_ibfk_2` FOREIGN KEY (`mensualidad_id`) REFERENCES `mensualidad` (`id_mensualidad`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2358 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `presupuesto_mensualidad`
--

LOCK TABLES `presupuesto_mensualidad` WRITE;
/*!40000 ALTER TABLE `presupuesto_mensualidad` DISABLE KEYS */;
INSERT INTO `presupuesto_mensualidad` VALUES (2260,1189,436),(2261,1190,436),(2262,1185,436),(2263,1186,436),(2264,1187,436),(2265,1188,436),(2271,769,438),(2272,770,438),(2273,781,439),(2274,782,439),(2275,670,440),(2276,671,440),(2277,827,441),(2278,828,441),(2279,837,442),(2280,838,442),(2287,1183,436),(2288,1184,436),(2326,1183,454),(2327,1189,454),(2328,1190,454),(2329,1189,455),(2330,1190,455),(2355,660,440),(2356,660,462),(2357,660,463);
/*!40000 ALTER TABLE `presupuesto_mensualidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_proveedor` varchar(100) DEFAULT NULL,
  `servicio` varchar(100) DEFAULT NULL,
  `rif` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'Gas Lara','Gas','V2352345','Lara'),(2,'Proimca','Luz','V3434523','Quibor'),(3,'Jardinero','Trabajos en jardineria','E13123343','terminal'),(4,'Reparaciones CA','Reparara','V2342344','Zona industrial');
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitudes_gasto`
--

DROP TABLE IF EXISTS `solicitudes_gasto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `solicitudes_gasto` (
  `id_solicitud` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_reporte` date NOT NULL,
  `descripcion_necesidad` varchar(100) NOT NULL,
  `nombre_solicitante` varchar(20) NOT NULL,
  `monto_estimado` float NOT NULL,
  `estado` varchar(20) NOT NULL,
  `presupuesto_id` int(11) NOT NULL,
  `prioridad` varchar(20) NOT NULL,
  PRIMARY KEY (`id_solicitud`),
  KEY `presupuesto_mensual_id` (`presupuesto_id`),
  CONSTRAINT `solicitudes_gasto_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuesto` (`id_presupuesto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitudes_gasto`
--

LOCK TABLES `solicitudes_gasto` WRITE;
/*!40000 ALTER TABLE `solicitudes_gasto` DISABLE KEYS */;
INSERT INTO `solicitudes_gasto` VALUES (8,'2025-09-01','Solicitud de consumo de algo','Pablo',10,'Pendiente',56,'2'),(10,'2010-10-10','Solicitud de gasto de ejemplo','Juan',15,'Pendiente',56,'1');
/*!40000 ALTER TABLE `solicitudes_gasto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_gasto`
--

DROP TABLE IF EXISTS `tipo_gasto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipo_gasto` (
  `id_tipo_gasto` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_tipo_gasto` varchar(50) NOT NULL,
  PRIMARY KEY (`id_tipo_gasto`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_gasto`
--

LOCK TABLES `tipo_gasto` WRITE;
/*!40000 ALTER TABLE `tipo_gasto` DISABLE KEYS */;
INSERT INTO `tipo_gasto` VALUES (1,'Servicio de Gas'),(2,'Servicios Públicos'),(3,'Personal y Obligaciones Laborales'),(4,'Mantenimientos y Reparaciones'),(9,'Suministros de Limpieza y Operacion'),(10,'Gastos Administrativos y Financieros'),(13,'seses');
/*!40000 ALTER TABLE `tipo_gasto` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-20  3:19:55
