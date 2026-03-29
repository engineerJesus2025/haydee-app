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
  `estado` varchar(20) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_cierre` date NOT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT 'Sin descripción',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_anio_fiscal`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anio_fiscal`
--

LOCK TABLES `anio_fiscal` WRITE;
/*!40000 ALTER TABLE `anio_fiscal` DISABLE KEYS */;
INSERT INTO `anio_fiscal` VALUES (47,'Abierto','2026-02-07','2027-02-07','Año fiscal 2026',1),(52,'Cerrada','2026-03-12','2027-03-12','asdas',0),(53,'Cerrada','2026-03-05','2027-03-05','AAA',0),(54,'Cerrada','2026-03-14','2027-03-14','nueva des',0),(55,'Cerrada','2026-03-13','2027-03-13','asda',0),(56,'Cerrada','2026-03-16','2027-03-16','',0),(57,'Cerrada','2026-03-02','2027-03-02','',0),(58,'Cerrada','2026-03-10','2027-03-10','asd',0);
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
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_apartamento`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apartamentos`
--

LOCK TABLES `apartamentos` WRITE;
/*!40000 ALTER TABLE `apartamentos` DISABLE KEYS */;
INSERT INTO `apartamentos` VALUES (30,'1-2',22,2,1,2,1),(31,'2-3',23,1,1,1,1),(32,'2-1',1,2,1,1,1),(33,'12',23,1,1,1,0),(34,'2-8',2,1,2,1,0),(35,'3-1',5,2,1,1,1),(36,'2-5',52,1,1,1,0),(37,'4-1',5,1,1,2,1),(38,'4-2',5,1,1,2,0),(39,'3-2',1,2,1,1,1),(40,'',0,0,0,0,0),(41,'5-1',5,1,1,1,1);
/*!40000 ALTER TABLE `apartamentos` ENABLE KEYS */;
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
  `tipo_cuenta` varchar(30) NOT NULL,
  `telefono_afiliado` varchar(50) NOT NULL,
  `rif` varchar(20) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_banco`),
  UNIQUE KEY `numero_cuenta` (`numero_cuenta`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bancos`
--

LOCK TABLES `bancos` WRITE;
/*!40000 ALTER TABLE `bancos` DISABLE KEYS */;
INSERT INTO `bancos` VALUES (1,'venezuela','0102','0102123412124232323','Ahorro','04152456842','J3232421',1),(6,'Banesco','0117','1242342342424121211','Corriente','04142584985','V5464565',1),(9,'Bancaribe','0114','01140300063000253595','Corriente','04114124142','J305785457',1),(11,'rasdas','1231','2342342342342342323','','21321253213','V2123132',0),(12,'tesoro','1231','425646456456456456','','24243245564','V12345678',0),(13,'Tesoros','0102','2423423423423234234','Corriente','23423423232','V123412321',1),(14,'sdfsdfs','2342','2315646545645656564','','23123153545','V24653215',0),(15,'aASDASD','1234','1231312312312312123','Corriente','12312312323','V231231231',1);
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
  `fondo_fijo` decimal(15,2) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT 'Sin descripción',
  `fecha_creacion` date NOT NULL DEFAULT current_timestamp(),
  `anio_fiscal_id` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_caja_chica`),
  KEY `anio_fiscal_id` (`anio_fiscal_id`),
  CONSTRAINT `caja_chica_ibfk_1` FOREIGN KEY (`anio_fiscal_id`) REFERENCES `anio_fiscal` (`id_anio_fiscal`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caja_chica`
--

LOCK TABLES `caja_chica` WRITE;
/*!40000 ALTER TABLE `caja_chica` DISABLE KEYS */;
INSERT INTO `caja_chica` VALUES (24,0.00,'Cerrada','Caja chicas del mes enero - 2026','2026-01-01',47,1),(25,1000.00,'Abierto','Caja chicas del mes Marzo - 2026','2026-03-09',47,1);
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
  `monto` decimal(15,2) NOT NULL,
  `monto_dolar` decimal(15,2) NOT NULL,
  `metodo_pago` varchar(20) NOT NULL,
  `gasto_id` int(11) NOT NULL,
  `descripcion_detalle_gasto` varchar(255) NOT NULL DEFAULT 'Sin descripción',
  PRIMARY KEY (`id_detalle_gasto`),
  KEY `gasto_id` (`gasto_id`),
  CONSTRAINT `detalles_gastos_ibfk_1` FOREIGN KEY (`gasto_id`) REFERENCES `gastos` (`id_gasto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2026 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_gastos`
--

LOCK TABLES `detalles_gastos` WRITE;
/*!40000 ALTER TABLE `detalles_gastos` DISABLE KEYS */;
INSERT INTO `detalles_gastos` VALUES (164,'2026-02-03',12.00,0.00,'Pago Movil',133,'asdasdasdasdasd'),(217,'2026-02-21',12.00,0.00,'Efectivo',136,'adasdas'),(218,'2026-02-21',900.00,0.00,'Efectivo',135,'Detalle de reposición por monto de: 900.00'),(2001,'2025-12-20',150.00,4.00,'Transferencia',201,'Mes diciembre'),(2002,'2026-01-15',700.00,20.00,'Transferencia',202,'Quincena 1'),(2003,'2026-01-25',1500.00,40.00,'Divisa',203,'Repuestos bomba'),(2004,'2026-02-15',720.00,20.00,'Pago Movil',204,'Quincena 1 feb'),(2005,'2026-03-02',300.00,8.00,'Efectivo',205,'Limpieza pasillos'),(2006,'2026-03-05',43.00,0.00,'Efectivo',206,'xxxxxxxxxxxxxxxxxxxx'),(2011,'2026-03-08',12312.00,0.00,'Efectivo',207,'prueba detalle 1'),(2012,'2026-03-07',21.00,0.00,'Pago Movil',207,'prueba detalle 2'),(2017,'2026-03-16',910.00,0.00,'Efectivo',208,'Detalle de reposición por monto de: 910.00'),(2018,'2026-02-21',32.00,0.00,'Efectivo',134,'detalle 1 s'),(2019,'2026-02-06',12.00,0.00,'Pago Movil',134,'detalle 2s'),(2020,'2026-03-17',0.00,0.00,'Efectivo',209,'Detalle de reposición por monto de: 0.00'),(2021,'2026-03-17',0.00,0.00,'Efectivo',210,'Detalle de reposición por monto de: 0.00'),(2022,'2026-02-05',12.00,0.00,'Pago Movil',132,'adiossssssssssssssss'),(2023,'2026-03-17',15.00,0.00,'Efectivo',211,'asdasd'),(2024,'2026-03-17',10.00,0.00,'Transferencia',211,'asddasdas'),(2025,'2026-03-11',123.00,0.00,'Pago Movil',212,'assas');
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
  `monto` decimal(15,2) NOT NULL,
  `monto_dolar` decimal(15,2) NOT NULL,
  `tipo_pago` varchar(20) NOT NULL,
  `pago_id` int(11) NOT NULL,
  PRIMARY KEY (`id_detalle_pago`),
  KEY `detalles_pagos_ibfk_1` (`pago_id`),
  CONSTRAINT `detalles_pagos_ibfk_1` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id_pago`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1024 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_pagos`
--

LOCK TABLES `detalles_pagos` WRITE;
/*!40000 ALTER TABLE `detalles_pagos` DISABLE KEYS */;
INSERT INTO `detalles_pagos` VALUES (362,'2026-02-01',10.00,0.03,'Pago Movil',116),(370,'2026-02-14',10.00,0.02,'Pago Movil',118),(371,'2026-02-21',100.00,0.25,'Efectivo',118),(382,'2026-02-22',12.00,0.03,'Pago Movil',119),(383,'2026-02-01',12.00,1.00,'Efectivo',119),(384,'2026-02-05',12.00,0.03,'Pago Movil',117),(385,'2026-02-04',100.00,0.25,'Transferencia',117),(386,'2026-02-20',100.00,0.25,'Efectivo',117),(1001,'2025-12-15',350.00,10.00,'Pago Movil',101),(1002,'2026-01-10',360.00,10.00,'Transferencia',102),(1003,'2026-02-05',180.00,5.00,'Efectivo',103),(1004,'2026-02-28',360.00,10.00,'Divisa',104),(1005,'2026-03-01',365.00,10.00,'Pago Movil',105),(1007,'2026-03-05',800.00,1.87,'Transferencia',121),(1008,'2026-03-05',4.00,0.01,'Efectivo',122),(1010,'2026-03-07',10.00,0.02,'Efectivo',123),(1012,'2026-02-25',1.40,0.00,'Efectivo',120),(1014,'2026-03-04',10.00,0.02,'Efectivo',124),(1016,'2026-03-04',10.00,0.00,'Pago Movil',127),(1017,'2026-03-05',10.00,0.00,'Transferencia',128),(1018,'2026-03-18',23.00,0.00,'Pago Movil',129),(1020,'2026-03-18',23.00,0.00,'Pago Movil',131),(1022,'2026-03-04',50.00,0.00,'Efectivo',132),(1023,'2026-03-25',50.00,0.00,'Transferencia',132);
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
  `monto` decimal(15,2) NOT NULL,
  `monto_dolar` decimal(15,2) NOT NULL,
  `nombre_detalle` varchar(50) NOT NULL,
  `presupuesto_id` int(11) NOT NULL,
  `tipo_gasto_id` int(11) NOT NULL,
  PRIMARY KEY (`id_detalle_presupuesto`),
  KEY `presupuesto_id` (`presupuesto_id`),
  KEY `tipo_gasto_id` (`tipo_gasto_id`),
  CONSTRAINT `detalles_presupuesto_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuesto` (`id_presupuesto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detalles_presupuesto_ibfk_2` FOREIGN KEY (`tipo_gasto_id`) REFERENCES `tipo_gasto` (`id_tipo_gasto`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1388 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_presupuesto`
--

LOCK TABLES `detalles_presupuesto` WRITE;
/*!40000 ALTER TABLE `detalles_presupuesto` DISABLE KEYS */;
INSERT INTO `detalles_presupuesto` VALUES (1327,15.00,0.00,'CORPOELEC',106,2),(1328,12.00,0.00,'HIDROLARA',106,2),(1329,100.00,0.00,'Trabajadora Residencial',106,3),(1330,100.00,0.00,'Bono de alimentacion',106,3),(1331,100.00,0.00,'Bono de ayuda',106,3),(1332,100.00,0.00,'Seguridad Social',106,3),(1333,100.00,0.00,'Mantenimiento ascensor',106,4),(1334,100.00,0.00,'GAS LARA',106,5),(1335,100.00,0.00,'Bolsas de Basura',106,9),(1336,100.00,0.00,'Productos de Limpieza',106,9),(1337,100.00,0.00,'Comisiones Bancarias',106,10),(1338,100.00,0.00,'Exencion cuota del administrador',106,10),(1351,23.00,0.00,'CORPOELEC',107,2),(1352,15.00,0.00,'HIDROLARA',107,2),(1353,233.00,0.00,'Trabajadora Residencial',107,3),(1354,12.00,0.00,'Bono de alimentacion',107,3),(1355,12.00,0.00,'Bono de ayuda',107,3),(1356,100.00,0.00,'Seguridad Social',107,3),(1357,12.00,0.00,'Mantenimiento ascensor',107,4),(1358,200.00,0.00,'GAS LARA',107,5),(1359,20.00,0.00,'Bolsas de Basura',107,9),(1360,200.00,0.00,'Productos de Limpieza',107,9),(1361,200.00,0.00,'Comisiones Bancarias',107,10),(1362,200.00,0.00,'Exencion cuota del administrador',107,10),(1387,12.00,0.00,'Exencion cuota del administrador',110,10);
/*!40000 ALTER TABLE `detalles_presupuesto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `egresos_bancarios`
--

DROP TABLE IF EXISTS `egresos_bancarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `egresos_bancarios` (
  `referencia` varchar(20) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `banco_id` int(11) NOT NULL,
  `detalle_gasto_id` int(11) NOT NULL,
  PRIMARY KEY (`banco_id`,`detalle_gasto_id`),
  UNIQUE KEY `referencia` (`referencia`),
  KEY `egresos_bancarios_ibfk_1` (`detalle_gasto_id`),
  CONSTRAINT `egresos_bancarios_ibfk_1` FOREIGN KEY (`detalle_gasto_id`) REFERENCES `detalles_gastos` (`id_detalle_gasto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `egresos_bancarios_ibfk_2` FOREIGN KEY (`banco_id`) REFERENCES `bancos` (`id_banco`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `egresos_bancarios`
--

LOCK TABLES `egresos_bancarios` WRITE;
/*!40000 ALTER TABLE `egresos_bancarios` DISABLE KEYS */;
INSERT INTO `egresos_bancarios` VALUES ('31231','javascript-logo-javascript-icon-transparent-free-png_1771563760_144.png',1,164),('GASTO-001','',1,2001),('GASTO-002','',1,2002),('GASTO-003','',1,2004),('123123123','CSS-Logo_1771706140_757.jpg',1,2022),('1235412','CSS-Logo_1773003460_864.jpg',6,2012),('412123','mensualidad_1771710356_291.PNG',6,2019),('543524','images__2__1773805029_187.png',9,2024),('5435245','default.png',9,2025);
/*!40000 ALTER TABLE `egresos_bancarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gastos`
--

DROP TABLE IF EXISTS `gastos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gastos` (
  `id_gasto` int(11) NOT NULL AUTO_INCREMENT,
  `clasificacion` varchar(20) NOT NULL,
  `tipo_gasto_id` int(11) NOT NULL,
  `solicitud_id` int(11) DEFAULT NULL,
  `proveedor_id` int(11) NOT NULL,
  `descripcion_gasto` text NOT NULL DEFAULT 'Sin descripción',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_gasto`),
  KEY `proveedor_id` (`proveedor_id`),
  KEY `tipo_gasto_id` (`tipo_gasto_id`),
  KEY `solicitud_id` (`solicitud_id`),
  CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id_proveedor`) ON UPDATE CASCADE,
  CONSTRAINT `gastos_ibfk_2` FOREIGN KEY (`tipo_gasto_id`) REFERENCES `tipo_gasto` (`id_tipo_gasto`) ON UPDATE CASCADE,
  CONSTRAINT `gastos_ibfk_3` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_gasto` (`id_solicitud`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=213 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gastos`
--

LOCK TABLES `gastos` WRITE;
/*!40000 ALTER TABLE `gastos` DISABLE KEYS */;
INSERT INTO `gastos` VALUES (132,'fijo',2,NULL,2,'holasssssssssssssssaaa',1),(133,'fijo',2,NULL,3,'asdasdasasd',0),(134,'fijo',2,NULL,3,'22222a2222222224',1),(135,'reposicion',1,NULL,1,'Reposición de Caja Chica - 22/02/2026',1),(136,'fijo',2,NULL,3,'wwwwwwwwwwwww',0),(201,'Fijo',1,NULL,1,'Pago de servicio de agua',0),(202,'Fijo',2,NULL,1,'Honorarios de vigilancia Enero',0),(203,'Variable',3,NULL,1,'Reparación de bomba de agua',1),(204,'Fijo',2,NULL,1,'Honorarios de vigilancia Febrero',1),(205,'Variable',4,NULL,1,'Compra de artículos de limpieza',1),(206,'fijo',1,NULL,2,'zzzzzzzzzzzzzzzzz',0),(207,'variable',2,NULL,3,'prueba de gasto 1',1),(208,'reposicion',1,NULL,1,'Reposición de Caja Chica - 16/03/2026',1),(209,'reposicion',1,NULL,1,'Reposición de Caja Chica - 17/03/2026',1),(210,'reposicion',1,NULL,1,'Reposición de Caja Chica - 17/03/2026',1),(211,'variable',2,NULL,3,'asdasdasdasd',1),(212,'fijo',1,NULL,3,'asdasdasdasd',1);
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
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_habitante`) USING BTREE,
  UNIQUE KEY `cedula` (`cedula`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `habitantes`
--

LOCK TABLES `habitantes` WRITE;
/*!40000 ALTER TABLE `habitantes` DISABLE KEYS */;
INSERT INTO `habitantes` VALUES (43,'jesus','asdasda','E1105510','12312312123','aaa@gasd.com','2000-12-12','Masculino',1),(44,'jesa','asdasd','V15321212','21312312121','asda@asasd.ocm','2000-10-10','Masculino',1),(45,'asdasd','asdasdas','V12012120','23423423434','ASDASD@sfas.com','1980-10-10','Femenino',1),(46,'papap','lalala','V23424234','21312312312','lala@gasmic.com','1950-10-10','Masculino',1),(47,'ssdfsdf','asda','V23432423','23423423423','asdasda@asfas.com','1945-10-10','Femenino',1),(48,'boor','borra','V23423234','23654564321','asd@asd.com','1999-01-01','Masculino',0),(49,'asa','asdasd','V2343121','12313455648','asda@adsd.com','1999-10-10','Masculino',0),(50,'asa','asdasd','V23432121','12313455648','asda@adaassdsd.com','1999-10-10','Masculino',0),(51,'fhfgh','asdasd','V2342342','42342342342','asdasd@asd.com','1999-10-10','Masculino',1),(52,'dasdas','asdasd','E12312313','22342323232','ada@asd.com','2000-10-10','Femenino',0);
/*!40000 ALTER TABLE `habitantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `habitantes_apartamentos`
--

DROP TABLE IF EXISTS `habitantes_apartamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `habitantes_apartamentos` (
  `apartamento_id` int(11) NOT NULL,
  `habitante_id` int(11) NOT NULL,
  `tipo_vinculo` varchar(20) NOT NULL,
  PRIMARY KEY (`apartamento_id`,`habitante_id`),
  KEY `habitantes_apartamentos_ibfk_2` (`habitante_id`),
  CONSTRAINT `habitantes_apartamentos_ibfk_1` FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos` (`id_apartamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `habitantes_apartamentos_ibfk_2` FOREIGN KEY (`habitante_id`) REFERENCES `habitantes` (`id_habitante`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `habitantes_apartamentos`
--

LOCK TABLES `habitantes_apartamentos` WRITE;
/*!40000 ALTER TABLE `habitantes_apartamentos` DISABLE KEYS */;
INSERT INTO `habitantes_apartamentos` VALUES (30,43,'Propietario'),(30,44,'Habitante'),(30,48,'Habitante'),(30,49,'Habitante'),(30,50,'Habitante'),(30,51,'Habitante'),(30,52,'Habitante'),(31,46,'Propietario'),(32,45,'Propietario'),(35,47,'Propietario');
/*!40000 ALTER TABLE `habitantes_apartamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingresos_bancarios`
--

DROP TABLE IF EXISTS `ingresos_bancarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ingresos_bancarios` (
  `referencia` varchar(20) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `banco_id` int(11) NOT NULL,
  `detalle_pago_id` int(11) NOT NULL,
  PRIMARY KEY (`banco_id`,`detalle_pago_id`),
  UNIQUE KEY `referencia` (`referencia`),
  KEY `ingresos_bancarios_ibfk_1` (`detalle_pago_id`),
  CONSTRAINT `ingresos_bancarios_ibfk_1` FOREIGN KEY (`detalle_pago_id`) REFERENCES `detalles_pagos` (`id_detalle_pago`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ingresos_bancarios_ibfk_2` FOREIGN KEY (`banco_id`) REFERENCES `bancos` (`id_banco`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingresos_bancarios`
--

LOCK TABLES `ingresos_bancarios` WRITE;
/*!40000 ALTER TABLE `ingresos_bancarios` DISABLE KEYS */;
INSERT INTO `ingresos_bancarios` VALUES ('45342','cog_1770410107_961.PNG',1,362),('12312','fiabil_1771818751_885.PNG',1,382),('REF-123456','',1,1001),('REF-987654','',1,1002),('REF-555666','',1,1005),('2312','virustotla_1771651369_672.PNG',6,370),('4213123','lenguaje_comun_1771707846_116.PNG',6,384),('4213','CSS-Logo_1772718346_856.jpg',6,1007),('678678','default.png',6,1016),('532423','default.png',6,1018),('532434345','colores_inicio_1_1773856062_104.PNG',6,1020),('234sad2','colores_inicio_2_1771708881_962.PNG',9,385),('45654','default.png',9,1017),('23423412','lenguaje_comun_1774402497_922.PNG',9,1023);
/*!40000 ALTER TABLE `ingresos_bancarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensualidad`
--

DROP TABLE IF EXISTS `mensualidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mensualidad` (
  `id_mensualidad` int(11) NOT NULL AUTO_INCREMENT,
  `periodo_id` int(11) NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `apartamento_id` int(11) NOT NULL,
  `porcentaje_interes` int(11) NOT NULL DEFAULT 10,
  `limite_mensualidad` int(11) NOT NULL DEFAULT 15,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_mensualidad`),
  KEY `apartamento_id` (`apartamento_id`),
  KEY `mensualidad_ibfk_periodo` (`periodo_id`),
  CONSTRAINT `mensualidad_ibfk_1` FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos` (`id_apartamento`) ON UPDATE CASCADE,
  CONSTRAINT `mensualidad_ibfk_periodo` FOREIGN KEY (`periodo_id`) REFERENCES `periodos_mensualidad` (`id_periodo`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=649 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensualidad`
--

LOCK TABLES `mensualidad` WRITE;
/*!40000 ALTER TABLE `mensualidad` DISABLE KEYS */;
INSERT INTO `mensualidad` VALUES (610,1,203.94,30,10,15,1),(611,1,213.21,31,10,15,1),(612,1,46.35,35,10,15,1),(613,1,9.27,39,10,15,1),(614,1,46.35,37,10,15,1),(615,1,9.27,32,10,15,1),(616,2,2.64,30,10,15,0),(617,2,48.76,31,10,15,0),(618,2,0.12,32,10,15,0),(619,2,0.60,35,10,15,0),(620,2,10.60,37,10,15,0),(621,2,0.12,39,10,15,0),(622,3,5035.58,30,10,15,0),(623,3,5264.47,31,10,15,0),(624,3,228.89,32,10,15,0),(625,3,1144.45,35,10,15,0),(626,3,1144.45,37,10,15,0),(627,3,228.89,39,10,15,0),(628,3,29.04,30,10,15,0),(629,3,30.36,31,10,15,0),(630,3,1.32,32,10,15,0),(631,3,6.60,35,10,15,0),(632,3,6.60,37,10,15,0),(633,3,1.32,39,10,15,0),(634,3,2.64,30,10,15,0),(635,3,2.76,31,10,15,0),(636,3,0.12,32,10,15,0),(637,3,0.60,35,10,15,0),(638,3,0.60,37,10,15,0),(639,3,0.12,39,10,15,0),(640,3,0.60,41,10,15,0),(641,1,25.00,41,10,15,1),(642,2,90.64,30,10,15,1),(643,2,4.12,32,10,15,1),(644,2,140.76,31,10,15,1),(645,2,20.60,35,10,15,1),(646,2,6.12,39,10,15,1),(647,2,30.60,37,10,15,1),(648,2,30.60,41,10,15,1);
/*!40000 ALTER TABLE `mensualidad` ENABLE KEYS */;
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
/*!50003 CREATE*/ /*!50017 /*!50003 TRIGGER `tr_validar_montos_mensualidad_registrar` BEFORE INSERT ON `mensualidad` FOR EACH ROW BEGIN
    IF NEW.monto < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error Crítico de BD: No se permiten montos negativos en Mensualidades.';
    END IF;
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
/*!50003 CREATE*/ /*!50017 /*!50003 TRIGGER `tr_validar_montos_mensualidad_editar` BEFORE UPDATE ON `mensualidad` FOR EACH ROW BEGIN
    IF NEW.monto < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error Crítico de BD: No se permiten montos negativos en Mensualidades.';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `movimientos_caja`
--

DROP TABLE IF EXISTS `movimientos_caja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movimientos_caja` (
  `id_movimiento_caja` int(11) NOT NULL AUTO_INCREMENT,
  `concepto` varchar(100) NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `fecha` date NOT NULL,
  `estado` varchar(50) NOT NULL,
  `caja_chica_id` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_movimiento_caja`),
  KEY `caja_chica_id` (`caja_chica_id`),
  CONSTRAINT `movimientos_caja_ibfk_1` FOREIGN KEY (`caja_chica_id`) REFERENCES `caja_chica` (`id_caja_chica`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_caja`
--

LOCK TABLES `movimientos_caja` WRITE;
/*!40000 ALTER TABLE `movimientos_caja` DISABLE KEYS */;
INSERT INTO `movimientos_caja` VALUES (42,'cafe',10.00,'2026-03-09','Repuesto',25,1),(43,'se pagaron 3 bombillos nuevos',900.00,'2026-03-15','Repuesto',25,1),(44,'aaaaa',10.00,'2026-03-16','Pendiente por reposicion',25,0);
/*!40000 ALTER TABLE `movimientos_caja` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos`
--

DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL AUTO_INCREMENT,
  `estado` varchar(20) NOT NULL,
  `observacion` text NOT NULL DEFAULT 'Sin observación',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos`
--

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
INSERT INTO `pagos` VALUES (101,'ANULADO','Pago de mensualidad Diciembre',0),(102,'ANULADO','Pago de mensualidad Enero',0),(103,'ANULADO','Abono a deuda',0),(104,'ANULADO','Pago en revisión',0),(105,'ANULADO','Pago mensualidad Marzo',0),(116,'Procesado','asdasd',0),(117,'RECHAZADO','sesss',1),(118,'ANULADO','si mi panax',0),(119,'PROCESADO','pago',1),(120,'ANULADO','mitasd de pagok',0),(121,'No verificado','Broder',1),(122,'PROCESADO','ssssssssssssssss',1),(123,'No verificado','',1),(124,'ANULADO','xxxxxxxxxxxxx',0),(127,'PROCESADO','',1),(128,'RECHAZADO','',1),(129,'PROCESADO','',1),(131,'PROCESADO','',1),(132,'PROCESADO','pago de ayer',1);
/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos_mensualidad`
--

DROP TABLE IF EXISTS `pagos_mensualidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagos_mensualidad` (
  `detalle_pago_id` int(11) NOT NULL,
  `mensualidad_id` int(11) NOT NULL,
  PRIMARY KEY (`detalle_pago_id`,`mensualidad_id`),
  KEY `pagos_mensualidad_ibfk_2` (`mensualidad_id`),
  CONSTRAINT `pagos_mensualidad_ibfk_2` FOREIGN KEY (`mensualidad_id`) REFERENCES `mensualidad` (`id_mensualidad`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pagos_mensualidad_ibfk_3` FOREIGN KEY (`detalle_pago_id`) REFERENCES `detalles_pagos` (`id_detalle_pago`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos_mensualidad`
--

LOCK TABLES `pagos_mensualidad` WRITE;
/*!40000 ALTER TABLE `pagos_mensualidad` DISABLE KEYS */;
INSERT INTO `pagos_mensualidad` VALUES (1022,611),(1023,611);
/*!40000 ALTER TABLE `pagos_mensualidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periodos_mensualidad`
--

DROP TABLE IF EXISTS `periodos_mensualidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `periodos_mensualidad` (
  `id_periodo` int(11) NOT NULL AUTO_INCREMENT,
  `mes` varchar(2) NOT NULL,
  `anio` varchar(4) NOT NULL,
  `tasa_dolar` decimal(15,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_periodo`),
  UNIQUE KEY `periodo_unico` (`mes`,`anio`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periodos_mensualidad`
--

LOCK TABLES `periodos_mensualidad` WRITE;
/*!40000 ALTER TABLE `periodos_mensualidad` DISABLE KEYS */;
INSERT INTO `periodos_mensualidad` VALUES (1,'1','2026',455.25,1),(2,'2','2026',455.25,1),(3,'3','2026',455.25,1);
/*!40000 ALTER TABLE `periodos_mensualidad` ENABLE KEYS */;
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
  `cuota_reserva` float NOT NULL,
  `observacion` varchar(50) NOT NULL DEFAULT 'Sin observación',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_presupuesto`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `presupuesto`
--

LOCK TABLES `presupuesto` WRITE;
/*!40000 ALTER TABLE `presupuesto` DISABLE KEYS */;
INSERT INTO `presupuesto` VALUES (106,'2026-01-01',10,'enero 2026',1),(107,'2026-02-01',230,'febrero 2026 editado',1),(110,'2026-03-01',234,'Sin observación',1);
/*!40000 ALTER TABLE `presupuesto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `presupuesto_mensualidad`
--

DROP TABLE IF EXISTS `presupuesto_mensualidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `presupuesto_mensualidad` (
  `detalle_presupuesto_id` int(11) NOT NULL,
  `mensualidad_id` int(11) NOT NULL,
  PRIMARY KEY (`detalle_presupuesto_id`,`mensualidad_id`),
  KEY `presupuesto_mensualidad_ibfk_2` (`mensualidad_id`),
  CONSTRAINT `presupuesto_mensualidad_ibfk_1` FOREIGN KEY (`detalle_presupuesto_id`) REFERENCES `detalles_presupuesto` (`id_detalle_presupuesto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `presupuesto_mensualidad_ibfk_2` FOREIGN KEY (`mensualidad_id`) REFERENCES `mensualidad` (`id_mensualidad`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `presupuesto_mensualidad`
--

LOCK TABLES `presupuesto_mensualidad` WRITE;
/*!40000 ALTER TABLE `presupuesto_mensualidad` DISABLE KEYS */;
INSERT INTO `presupuesto_mensualidad` VALUES (1327,610),(1327,611),(1327,612),(1327,613),(1327,614),(1327,615),(1328,610),(1328,611),(1328,612),(1328,613),(1328,614),(1328,615),(1329,610),(1329,611),(1329,612),(1329,613),(1329,614),(1329,615),(1329,641),(1330,610),(1330,611),(1330,612),(1330,613),(1330,614),(1330,615),(1330,641),(1331,610),(1331,611),(1331,612),(1331,613),(1331,614),(1331,615),(1331,641),(1332,610),(1332,611),(1332,612),(1332,613),(1332,614),(1332,615),(1332,641),(1334,610),(1334,611),(1334,612),(1334,613),(1334,614),(1334,615),(1334,641),(1335,610),(1335,611),(1335,612),(1335,613),(1335,614),(1335,615),(1336,610),(1336,611),(1336,612),(1336,613),(1336,614),(1336,615),(1337,610),(1337,611),(1337,612),(1337,613),(1337,614),(1337,615),(1338,610),(1338,611),(1338,612),(1338,613),(1338,614),(1338,615),(1357,616),(1357,617),(1357,618),(1357,619),(1357,620),(1357,621),(1357,642),(1357,643),(1357,644),(1357,645),(1357,646),(1357,647),(1357,648),(1358,617),(1358,620),(1358,644),(1358,646),(1358,647),(1358,648),(1361,642),(1361,643),(1361,644),(1361,645),(1361,646),(1361,647),(1361,648),(1362,642),(1362,643),(1362,644),(1362,645),(1362,646),(1362,647),(1362,648),(1387,634),(1387,635),(1387,636),(1387,637),(1387,638),(1387,639),(1387,640);
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
  `nombre_proveedor` varchar(100) NOT NULL,
  `servicio` varchar(100) NOT NULL,
  `rif` varchar(20) NOT NULL,
  `direccion` text NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'Administración (Caja Chica)','Reposición de Caja','J0000000','Oficina Administrativa',1),(2,'Proimca','Luz','V3434523','Quibor',1),(3,'Jardinero','Trabajos en jardineria','E13123343','terminal',1),(4,'Reparaciones CA','Reparara','V2342344','Zona industrial',1),(5,'Gas Lara','Gas','V2352345','Lara',1),(14,'pepe','pepes','E1231245','Pepelandia',1);
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reposiciones`
--

DROP TABLE IF EXISTS `reposiciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reposiciones` (
  `gasto_id` int(11) NOT NULL,
  `movimiento_caja_id` int(11) NOT NULL,
  PRIMARY KEY (`gasto_id`,`movimiento_caja_id`),
  KEY `reposiciones_ibfk_2` (`movimiento_caja_id`),
  CONSTRAINT `reposiciones_ibfk_1` FOREIGN KEY (`gasto_id`) REFERENCES `gastos` (`id_gasto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reposiciones_ibfk_2` FOREIGN KEY (`movimiento_caja_id`) REFERENCES `movimientos_caja` (`id_movimiento_caja`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reposiciones`
--

LOCK TABLES `reposiciones` WRITE;
/*!40000 ALTER TABLE `reposiciones` DISABLE KEYS */;
INSERT INTO `reposiciones` VALUES (208,42),(208,43);
/*!40000 ALTER TABLE `reposiciones` ENABLE KEYS */;
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
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_solicitud`),
  KEY `presupuesto_mensual_id` (`presupuesto_id`),
  CONSTRAINT `solicitudes_gasto_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuesto` (`id_presupuesto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitudes_gasto`
--

LOCK TABLES `solicitudes_gasto` WRITE;
/*!40000 ALTER TABLE `solicitudes_gasto` DISABLE KEYS */;
INSERT INTO `solicitudes_gasto` VALUES (15,'2026-03-18','asdasdasdas','aasasd',12,'Pendiente',106,'2',1);
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
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_tipo_gasto`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_gasto`
--

LOCK TABLES `tipo_gasto` WRITE;
/*!40000 ALTER TABLE `tipo_gasto` DISABLE KEYS */;
INSERT INTO `tipo_gasto` VALUES (1,'Reposición de Caja Chica',1),(2,'Servicios Públicos',1),(3,'Personal y Obligaciones Laborales',1),(4,'Mantenimientos y Reparaciones',1),(5,'Servicio de Gas',1),(9,'Suministros de Limpieza y Operacion',1),(10,'Gastos Administrativos y Financieros',1),(14,'random',0),(15,'hola',0),(16,'aaaa',0);
/*!40000 ALTER TABLE `tipo_gasto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `vw_ejecucion_presupuesto`
--

DROP TABLE IF EXISTS `vw_ejecucion_presupuesto`;
/*!50001 DROP VIEW IF EXISTS `vw_ejecucion_presupuesto`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_ejecucion_presupuesto` AS SELECT
 1 AS `id_presupuesto`,
  1 AS `anio_presupuesto`,
  1 AS `descripcion_presupuesto`,
  1 AS `partida`,
  1 AS `monto_presupuestado`,
  1 AS `monto_ejecutado`,
  1 AS `disponible` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `vw_estado_cuentas_mensualidad`
--

DROP TABLE IF EXISTS `vw_estado_cuentas_mensualidad`;
/*!50001 DROP VIEW IF EXISTS `vw_estado_cuentas_mensualidad`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_estado_cuentas_mensualidad` AS SELECT
 1 AS `id_mensualidad`,
  1 AS `nro_apartamento`,
  1 AS `mes`,
  1 AS `anio`,
  1 AS `monto_cuota`,
  1 AS `total_abonado`,
  1 AS `deuda_pendiente`,
  1 AS `estado_pago` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `vw_saldo_caja_chica`
--

DROP TABLE IF EXISTS `vw_saldo_caja_chica`;
/*!50001 DROP VIEW IF EXISTS `vw_saldo_caja_chica`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_saldo_caja_chica` AS SELECT
 1 AS `id_caja_chica`,
  1 AS `estado`,
  1 AS `monto_base`,
  1 AS `total_gastado_pendiente`,
  1 AS `saldo_disponible` */;
SET character_set_client = @saved_cs_client;

--
-- Dumping events for database 'haydee_db'
--

--
-- Dumping routines for database 'haydee_db'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `gestionar_anio_fiscal` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `gestionar_anio_fiscal`()
BEGIN
    DECLARE existe_anio_actual BOOLEAN;
    
    SELECT COUNT(*) > 0 INTO existe_anio_actual 
    FROM anio_fiscal 
    WHERE YEAR(fecha_inicio) = YEAR(NOW()) 
    AND estado = 'Abierto'
    AND activo = 1; 
    
    IF NOT existe_anio_actual THEN
        UPDATE anio_fiscal SET estado = 'Cerrada', fecha_cierre = NOW() 
        WHERE estado = 'Abierto' AND activo = 1;
        
        INSERT INTO anio_fiscal(fecha_inicio, fecha_cierre, estado, descripcion)
        VALUES (NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), 'Abierto', CONCAT('Año fiscal ', YEAR(NOW())));
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_gestion_caja_chica_mensual` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `sp_gestion_caja_chica_mensual`()
sp_block: BEGIN 
    DECLARE v_mes_actual VARCHAR(7);
    DECLARE v_existe_caja_abierta INT;
    DECLARE v_fecha_actual DATE;
    DECLARE v_id_anio_fiscal INT;
    DECLARE v_nombre_mes_espanol VARCHAR(20);
    DECLARE v_id_caja_anterior INT;
    
    DECLARE v_monto_fondo_fijo DECIMAL(15,2) DEFAULT 1000.00; 
    
    SET v_fecha_actual = CURDATE();
    SET v_mes_actual = DATE_FORMAT(v_fecha_actual, '%Y-%m');    
    
    SELECT id_anio_fiscal INTO v_id_anio_fiscal
    FROM anio_fiscal
    WHERE v_fecha_actual BETWEEN fecha_inicio AND fecha_cierre
    AND estado = 'Abierto'
    LIMIT 1;
    
    IF v_id_anio_fiscal IS NULL THEN
        LEAVE sp_block; 
    END IF;
    
    SELECT COUNT(*) INTO v_existe_caja_abierta 
    FROM caja_chica 
    WHERE DATE_FORMAT(fecha_creacion, '%Y-%m') = v_mes_actual 
    AND anio_fiscal_id = v_id_anio_fiscal; 
    
    IF v_existe_caja_abierta = 0 THEN
    
        SELECT id_caja_chica INTO v_id_caja_anterior
        FROM caja_chica
        WHERE estado = 'Abierto'
        AND anio_fiscal_id = v_id_anio_fiscal
        ORDER BY fecha_creacion DESC
        LIMIT 1;

        UPDATE caja_chica 
        SET estado = 'Cerrada'
        WHERE estado = 'Abierto'
        AND anio_fiscal_id = v_id_anio_fiscal;
        
        SET v_nombre_mes_espanol = 
            CASE MONTH(v_fecha_actual)
                WHEN 1 THEN 'Enero' WHEN 2 THEN 'Febrero' WHEN 3 THEN 'Marzo'
                WHEN 4 THEN 'Abril' WHEN 5 THEN 'Mayo' WHEN 6 THEN 'Junio'
                WHEN 7 THEN 'Julio' WHEN 8 THEN 'Agosto' WHEN 9 THEN 'Septiembre'
                WHEN 10 THEN 'Octubre' WHEN 11 THEN 'Noviembre' WHEN 12 THEN 'Diciembre'
                ELSE 'Desconocido'
            END;
        
        INSERT INTO caja_chica 
            (fecha_creacion, fondo_fijo, estado, descripcion, anio_fiscal_id) 
        VALUES 
            (v_fecha_actual, v_monto_fondo_fijo, 'Abierto', 
             CONCAT('Caja chicas del mes ', v_nombre_mes_espanol, ' - ', YEAR(v_fecha_actual)), 
             v_id_anio_fiscal);
       
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_registrar_reposicion_caja` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `sp_registrar_reposicion_caja`(IN `p_monto_reposicion` DECIMAL(15,2), IN `p_caja_id` INT)
sp_block: BEGIN
    DECLARE v_mensaje VARCHAR(500);
    DECLARE v_codigo_error INT DEFAULT 0;
    DECLARE v_proveedor_id INT;
    DECLARE v_tipo_gasto_id INT;
    DECLARE v_solicitud_gasto_id INT;
    DECLARE v_gasto_id INT;
    DECLARE v_saldo_acumulado DECIMAL(15,2) DEFAULT 0.00;
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_mov_id INT;
    DECLARE v_mov_monto DECIMAL(15,2);
    
    DECLARE cur_movimientos CURSOR FOR 
        SELECT id_movimiento_caja, monto 
        FROM movimientos_caja 
        WHERE caja_chica_id = p_caja_id 
        AND estado = 'Pendiente por reposicion'
        AND activo = 1 
        ORDER BY fecha ASC;
        
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        GET DIAGNOSTICS CONDITION 1 v_mensaje = MESSAGE_TEXT, v_codigo_error = MYSQL_ERRNO;
        SELECT CONCAT('Error ', v_codigo_error, ': ', v_mensaje) AS mensaje;
    END;

    START TRANSACTION;

    SELECT id_proveedor INTO v_proveedor_id FROM proveedores WHERE nombre_proveedor LIKE '%Administración (Caja Chica)%' LIMIT 1;
    SELECT id_tipo_gasto INTO v_tipo_gasto_id FROM tipo_gasto WHERE nombre_tipo_gasto LIKE '%Reposición%' LIMIT 1;
    SELECT id_solicitud INTO v_solicitud_gasto_id FROM solicitudes_gasto WHERE nombre_solicitante LIKE '%Administracion%' LIMIT 1;

    IF v_proveedor_id IS NULL OR v_tipo_gasto_id IS NULL OR v_solicitud_gasto_id IS NULL THEN
        SELECT 'Error: Faltan datos semilla (Proveedor, Tipo Gasto o Solicitud).' AS mensaje;
        ROLLBACK;
        LEAVE sp_block;
    END IF;

    INSERT INTO gastos (descripcion_gasto, proveedor_id, tipo_gasto_id, solicitud_id, clasificacion, activo)
    VALUES (
        CONCAT('Reposición de Caja Chica - ', DATE_FORMAT(NOW(), '%d/%m/%Y')), 
        v_proveedor_id, v_tipo_gasto_id, v_solicitud_gasto_id, 'reposicion', 1
    );
    SET v_gasto_id = LAST_INSERT_ID(); 

    INSERT INTO detalles_gastos (fecha, monto, monto_dolar, metodo_pago, gasto_id, descripcion_detalle_gasto)
    VALUES (CURDATE(), p_monto_reposicion, 0.00, 'Efectivo', v_gasto_id, CONCAT('Detalle de reposición por monto de: ', p_monto_reposicion));

    OPEN cur_movimientos;
    read_loop: LOOP
        FETCH cur_movimientos INTO v_mov_id, v_mov_monto;
        IF done THEN LEAVE read_loop; END IF;

        IF (v_saldo_acumulado + v_mov_monto) <= p_monto_reposicion THEN
            INSERT INTO reposiciones (gasto_id, movimiento_caja_id) VALUES (v_gasto_id, v_mov_id);
            
            UPDATE movimientos_caja SET estado = 'Repuesto' WHERE id_movimiento_caja = v_mov_id;
            
            SET v_saldo_acumulado = v_saldo_acumulado + v_mov_monto;
        ELSE
            LEAVE read_loop;
        END IF;
    END LOOP;
    CLOSE cur_movimientos;

    COMMIT;
    SELECT CONCAT('Reposición exitosa. Total Repuesto: ', v_saldo_acumulado) AS mensaje;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_sincronizar_presupuestos_mensualidad` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `sp_sincronizar_presupuestos_mensualidad`(IN `p_mensualidad_id` INT, IN `p_nuevos_presupuestos_ids` TEXT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; 
    END;

    CREATE TEMPORARY TABLE IF NOT EXISTS TempNuevosGastos (detalle_presupuesto_id INT PRIMARY KEY);
    TRUNCATE TABLE TempNuevosGastos;

    SET @sql = CONCAT('INSERT INTO TempNuevosGastos (detalle_presupuesto_id) VALUES (', REPLACE(p_nuevos_presupuestos_ids, ',', '),('), ');');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    
    START TRANSACTION;

        DELETE FROM presupuesto_mensualidad
        WHERE
            mensualidad_id = p_mensualidad_id
            AND detalle_presupuesto_id NOT IN (SELECT detalle_presupuesto_id FROM TempNuevosGastos);

        INSERT INTO presupuesto_mensualidad (mensualidad_id, detalle_presupuesto_id)
        SELECT p_mensualidad_id, nuevos.detalle_presupuesto_id
        FROM TempNuevosGastos AS nuevos
        WHERE NOT EXISTS (
            SELECT 1
            FROM presupuesto_mensualidad AS existentes
            WHERE existentes.mensualidad_id = p_mensualidad_id AND existentes.detalle_presupuesto_id = nuevos.detalle_presupuesto_id
        );

    COMMIT;

    DROP TEMPORARY TABLE TempNuevosGastos;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `vw_ejecucion_presupuesto`
--

/*!50001 DROP VIEW IF EXISTS `vw_ejecucion_presupuesto`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY DEFINER */
/*!50001 VIEW `vw_ejecucion_presupuesto` AS select `p`.`id_presupuesto` AS `id_presupuesto`,year(`p`.`fecha`) AS `anio_presupuesto`,`p`.`observacion` AS `descripcion_presupuesto`,`tg`.`nombre_tipo_gasto` AS `partida`,`dp`.`monto` AS `monto_presupuestado`,ifnull((select sum(`dg`.`monto`) from (`gastos` `g` join `detalles_gastos` `dg` on(`g`.`id_gasto` = `dg`.`gasto_id`)) where `g`.`tipo_gasto_id` = `dp`.`tipo_gasto_id` and year(`dg`.`fecha`) = year(`p`.`fecha`) and `g`.`activo` = 1),0) AS `monto_ejecutado`,`dp`.`monto` - ifnull((select sum(`dg`.`monto`) from (`gastos` `g` join `detalles_gastos` `dg` on(`g`.`id_gasto` = `dg`.`gasto_id`)) where `g`.`tipo_gasto_id` = `dp`.`tipo_gasto_id` and year(`dg`.`fecha`) = year(`p`.`fecha`) and `g`.`activo` = 1),0) AS `disponible` from ((`presupuesto` `p` join `detalles_presupuesto` `dp` on(`p`.`id_presupuesto` = `dp`.`presupuesto_id`)) join `tipo_gasto` `tg` on(`dp`.`tipo_gasto_id` = `tg`.`id_tipo_gasto`)) where `p`.`activo` = 1 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_estado_cuentas_mensualidad`
--

/*!50001 DROP VIEW IF EXISTS `vw_estado_cuentas_mensualidad`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY DEFINER */
/*!50001 VIEW `vw_estado_cuentas_mensualidad` AS select `m`.`id_mensualidad` AS `id_mensualidad`,`a`.`nro_apartamento` AS `nro_apartamento`,`p`.`mes` AS `mes`,`p`.`anio` AS `anio`,`m`.`monto` AS `monto_cuota`,ifnull((select sum(`dp`.`monto`) from ((`pagos_mensualidad` `pm` join `detalles_pagos` `dp` on(`pm`.`detalle_pago_id` = `dp`.`id_detalle_pago`)) join `pagos` `pg` on(`dp`.`pago_id` = `pg`.`id_pago`)) where `pm`.`mensualidad_id` = `m`.`id_mensualidad` and `pg`.`activo` = 1),0) AS `total_abonado`,`m`.`monto` - ifnull((select sum(`dp`.`monto`) from ((`pagos_mensualidad` `pm` join `detalles_pagos` `dp` on(`pm`.`detalle_pago_id` = `dp`.`id_detalle_pago`)) join `pagos` `pg` on(`dp`.`pago_id` = `pg`.`id_pago`)) where `pm`.`mensualidad_id` = `m`.`id_mensualidad` and `pg`.`activo` = 1),0) AS `deuda_pendiente`,case when `m`.`monto` - ifnull((select sum(`dp`.`monto`) from ((`pagos_mensualidad` `pm` join `detalles_pagos` `dp` on(`pm`.`detalle_pago_id` = `dp`.`id_detalle_pago`)) join `pagos` `pg` on(`dp`.`pago_id` = `pg`.`id_pago`)) where `pm`.`mensualidad_id` = `m`.`id_mensualidad` and `pg`.`activo` = 1),0) <= 0 then 'Solvente' else 'Pendiente' end AS `estado_pago` from ((`mensualidad` `m` join `apartamentos` `a` on(`m`.`apartamento_id` = `a`.`id_apartamento`)) join `periodos_mensualidad` `p` on(`m`.`periodo_id` = `p`.`id_periodo`)) where `m`.`activo` = 1 and `a`.`activo` = 1 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_saldo_caja_chica`
--

/*!50001 DROP VIEW IF EXISTS `vw_saldo_caja_chica`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY DEFINER */
/*!50001 VIEW `vw_saldo_caja_chica` AS select `cc`.`id_caja_chica` AS `id_caja_chica`,`cc`.`estado` AS `estado`,`cc`.`fondo_fijo` AS `monto_base`,ifnull((select sum(`mc`.`monto`) from `movimientos_caja` `mc` where `mc`.`caja_chica_id` = `cc`.`id_caja_chica` and `mc`.`activo` = 1 and `mc`.`estado` <> 'Repuesto'),0) AS `total_gastado_pendiente`,`cc`.`fondo_fijo` - ifnull((select sum(`mc`.`monto`) from `movimientos_caja` `mc` where `mc`.`caja_chica_id` = `cc`.`id_caja_chica` and `mc`.`activo` = 1 and `mc`.`estado` <> 'Repuesto'),0) AS `saldo_disponible` from `caja_chica` `cc` where `cc`.`activo` = 1 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-28 13:05:05
