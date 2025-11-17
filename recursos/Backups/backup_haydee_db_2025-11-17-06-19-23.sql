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
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anio_fiscal`
--

LOCK TABLES `anio_fiscal` WRITE;
/*!40000 ALTER TABLE `anio_fiscal` DISABLE KEYS */;
INSERT INTO `anio_fiscal` VALUES (24,'2025-01-01','2026-01-01','Abierto','Ejecutada prueba de edicion'),(29,'2024-01-01','2025-01-01','Cerrada','Año fiscal 2024');
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apartamentos`
--

LOCK TABLES `apartamentos` WRITE;
/*!40000 ALTER TABLE `apartamentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `apartamentos` ENABLE KEYS */;
UNLOCK TABLES;

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
  KEY `banco_id` (`banco_id`),
  KEY `detalle_pago_id` (`detalle_pago_id`),
  KEY `gasto_id` (`detalle_gasto_id`),
  CONSTRAINT `banco_transacciones_ibfk_1` FOREIGN KEY (`banco_id`) REFERENCES `bancos` (`id_banco`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `banco_transacciones_ibfk_2` FOREIGN KEY (`detalle_pago_id`) REFERENCES `detalles_pagos` (`id_detalle_pago`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `banco_transacciones_ibfk_3` FOREIGN KEY (`detalle_gasto_id`) REFERENCES `detalles_gastos` (`id_detalle_gasto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banco_transacciones`
--

LOCK TABLES `banco_transacciones` WRITE;
/*!40000 ALTER TABLE `banco_transacciones` DISABLE KEYS */;
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
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bancos`
--

LOCK TABLES `bancos` WRITE;
/*!40000 ALTER TABLE `bancos` DISABLE KEYS */;
INSERT INTO `bancos` VALUES (6,'Banesco','0117','1242342342424121211','04142584985','30612546'),(18,'Venezuela','0102','327458972346382393','04127721822','30353397');
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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caja_chica`
--

LOCK TABLES `caja_chica` WRITE;
/*!40000 ALTER TABLE `caja_chica` DISABLE KEYS */;
INSERT INTO `caja_chica` VALUES (21,115,337.94,'Abierto','Caja chica 2025','2025-01-01',24),(22,100,100,'Cerrada','Caja chica 2024','2024-01-01',29),(23,305,325,'Cerrada','Ejecutada prueba de edicion','2025-10-19',24),(27,56,56,'Cerrada','caja para borrar','2025-10-19',24);
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
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_gastos`
--

LOCK TABLES `detalles_gastos` WRITE;
/*!40000 ALTER TABLE `detalles_gastos` DISABLE KEYS */;
INSERT INTO `detalles_gastos` VALUES (134,'2025-10-06',45,0,'Efectivo',109,'Reposición de Caja Chica'),(135,'2025-10-06',25,0,'Efectivo',110,'Reposición de Caja Chica'),(136,'2025-10-06',35,0,'Efectivo',111,'Reposición de Caja Chica'),(146,'2025-10-07',15,0,'Efectivo',114,'Reposición de Caja Chica');
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
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_pagos`
--

LOCK TABLES `detalles_pagos` WRITE;
/*!40000 ALTER TABLE `detalles_pagos` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=858 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_presupuesto`
--

LOCK TABLES `detalles_presupuesto` WRITE;
/*!40000 ALTER TABLE `detalles_presupuesto` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gastos`
--

LOCK TABLES `gastos` WRITE;
/*!40000 ALTER TABLE `gastos` DISABLE KEYS */;
INSERT INTO `gastos` VALUES (109,'variable',10,NULL,NULL,'Reposición de Caja Chica'),(110,'variable',10,NULL,NULL,'Reposición de Caja Chica'),(111,'variable',10,NULL,NULL,'Reposición de Caja Chica'),(114,'variable',10,NULL,NULL,'Reposición de Caja Chica');
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `habitantes`
--

LOCK TABLES `habitantes` WRITE;
/*!40000 ALTER TABLE `habitantes` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `habitantes_apartamentos`
--

LOCK TABLES `habitantes_apartamentos` WRITE;
/*!40000 ALTER TABLE `habitantes_apartamentos` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=343 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensualidad`
--

LOCK TABLES `mensualidad` WRITE;
/*!40000 ALTER TABLE `mensualidad` DISABLE KEYS */;
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
  CONSTRAINT `movimientos_caja_ibfk_1` FOREIGN KEY (`caja_chica_id`) REFERENCES `caja_chica` (`id_caja_chica`),
  CONSTRAINT `movimientos_caja_ibfk_2` FOREIGN KEY (`gasto_id`) REFERENCES `gastos` (`id_gasto`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_caja`
--

LOCK TABLES `movimientos_caja` WRITE;
/*!40000 ALTER TABLE `movimientos_caja` DISABLE KEYS */;
INSERT INTO `movimientos_caja` VALUES (2,'mas cafe',15,'2024-10-02','Pendiente por reposicion',22,NULL),(7,'pasta',4,'2024-10-01','Pendiente por reposicion',22,NULL),(22,'prueba de edicion',15,'2025-10-10','Reposado',23,109),(25,'prueba de registro',20,'2025-10-10','Reposado',23,109),(26,'prueba de registro',20,'2025-10-10','Reposado',23,109),(27,'prueba de registro',20,'2025-10-10','Reposado',23,109);
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
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos`
--

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=157 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos_mensualidad`
--

LOCK TABLES `pagos_mensualidad` WRITE;
/*!40000 ALTER TABLE `pagos_mensualidad` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `presupuesto`
--

LOCK TABLES `presupuesto` WRITE;
/*!40000 ALTER TABLE `presupuesto` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=1676 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `presupuesto_mensualidad`
--

LOCK TABLES `presupuesto_mensualidad` WRITE;
/*!40000 ALTER TABLE `presupuesto_mensualidad` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
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
INSERT INTO `tipo_gasto` VALUES (1,'Servicio de Gas'),(2,'Servicios Públicos'),(3,'Personal y Obligaciones Laborales'),(4,'Mantenimientos y Reparaciones'),(9,'Suministros de Limpieza y Operacion'),(10,'Gastos Administrativos y Financieros');
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

-- Dump completed on 2025-11-17  1:19:23
