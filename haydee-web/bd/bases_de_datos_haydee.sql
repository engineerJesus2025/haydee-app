-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-06-2026 a las 00:13:07
-- Versión del servidor: 10.4.32-MariaDB-log
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `haydee_db`
--
DROP DATABASE IF EXISTS `haydee_db`;
CREATE DATABASE IF NOT EXISTS `haydee_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `haydee_db`;

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_gestionar_periodos_automaticos` ()   BEGIN
    DECLARE v_abiertos INT;
    DECLARE v_ultimo_fondo DECIMAL(15,2) DEFAULT 0;
    DECLARE v_id_anio_activo INT DEFAULT NULL;
    DECLARE v_cajas_abiertas INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error crítico al ejecutar los cierres automáticos.' AS mensaje;
    END;

    START TRANSACTION;

    UPDATE anio_fiscal 
    SET estado = 'Cerrada', activo = 0 
    WHERE estado = 'Abierto' AND fecha_cierre < CURDATE() AND activo = 1;

    SELECT COUNT(*) INTO v_abiertos 
    FROM anio_fiscal 
    WHERE estado = 'Abierto' AND activo = 1;

    IF v_abiertos = 0 THEN
        INSERT INTO anio_fiscal (estado, fecha_inicio, fecha_cierre, descripcion, activo)
        VALUES (
            'Abierto', 
            CURDATE(), 
            DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 
            CONCAT('Año fiscal automático ', YEAR(CURDATE())), 
            1
        );
    END IF;

    UPDATE caja_chica
    SET estado = 'Cerrada'
    WHERE estado = 'Abierto'
      AND (
          YEAR(fecha_creacion) < YEAR(CURDATE()) 
          OR 
          (YEAR(fecha_creacion) = YEAR(CURDATE()) AND MONTH(fecha_creacion) < MONTH(CURDATE()))
      );

    SELECT COUNT(*) INTO v_cajas_abiertas 
    FROM caja_chica 
    WHERE estado = 'Abierto' AND activo = 1;

    IF v_cajas_abiertas = 0 THEN
        
        SELECT id_anio_fiscal INTO v_id_anio_activo 
        FROM anio_fiscal 
        WHERE estado = 'Abierto' AND activo = 1 
        LIMIT 1;

        IF v_id_anio_activo IS NOT NULL THEN
            SELECT fondo_fijo INTO v_ultimo_fondo 
            FROM caja_chica 
            ORDER BY id_caja_chica DESC 
            LIMIT 1;

            INSERT INTO caja_chica (fondo_fijo, estado, descripcion, fecha_creacion, anio_fiscal_id, activo)
            VALUES (
                v_ultimo_fondo, 
                'Abierto', 
                CONCAT('Caja chica automática - Mes ', MONTH(CURDATE()), '/', YEAR(CURDATE())), 
                CURDATE(), 
                v_id_anio_activo, 
                1
            );
        END IF;
    END IF;

    COMMIT;
    SELECT 'Periodos (Año Fiscal y Caja Chica) verificados y gestionados correctamente.' AS mensaje;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_reposicion_caja` (IN `p_monto` DECIMAL(15,2), IN `p_id_caja` INT, IN `p_tasa_dolar` DECIMAL(15,2))   BEGIN
    DECLARE v_total_pendiente DECIMAL(15,2);
    DECLARE v_gasto_id INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error crítico al procesar la reposición en la base de datos.' AS mensaje;
    END;

    START TRANSACTION;

    SELECT IFNULL(SUM(monto), 0) INTO v_total_pendiente
    FROM movimientos_caja
    WHERE caja_chica_id = p_id_caja AND estado = 'Pendiente por reposicion' AND activo = 1
    FOR UPDATE;

    IF v_total_pendiente = 0 THEN
        ROLLBACK;
        SELECT 'No hay movimientos pendientes por reponer en esta caja.' AS mensaje;
    ELSE
        INSERT INTO gastos (
            clasificacion, 
            tasa_dolar, 
            tipo_gasto_id, 
            proveedor_id, 
            descripcion_gasto, 
            activo
        ) VALUES (
            'reposicion', 
            p_tasa_dolar, 
            1,            
            1,            
            CONCAT('Reposición de Caja Chica - ', DATE_FORMAT(CURDATE(), '%d/%m/%Y')), 
            1
        );

        SET v_gasto_id = LAST_INSERT_ID();

        INSERT INTO reposiciones (gasto_id, movimiento_caja_id)
        SELECT v_gasto_id, id_movimiento_caja
        FROM movimientos_caja
        WHERE caja_chica_id = p_id_caja AND estado = 'Pendiente por reposicion' AND activo = 1;

        UPDATE movimientos_caja
        SET estado = 'Repuesto'
        WHERE caja_chica_id = p_id_caja AND estado = 'Pendiente por reposicion' AND activo = 1;

        COMMIT;
        SELECT CONCAT('Reposición procesada exitosamente por ', v_total_pendiente, ' Bs. Gasto registrado con Nro: ', v_gasto_id) AS mensaje;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anio_fiscal`
--

CREATE TABLE `anio_fiscal` (
  `id_anio_fiscal` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_cierre` date NOT NULL,
  `descripcion` text NOT NULL DEFAULT 'Sin descripción',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `anio_fiscal`
--

INSERT INTO `anio_fiscal` (`id_anio_fiscal`, `estado`, `fecha_inicio`, `fecha_cierre`, `descripcion`, `activo`) VALUES
(47, 'Abierto', '2026-02-07', '2027-02-07', 'Año fiscal 2026', 1),
(59, 'Cerrada', '2026-04-07', '2027-04-07', 'pepe', 1),
(63, 'ABIERTO', '2026-05-28', '2027-05-28', 'registro 2', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `apartamentos`
--

CREATE TABLE `apartamentos` (
  `id_apartamento` int(11) NOT NULL,
  `nro_apartamento` varchar(5) NOT NULL,
  `porcentaje_participacion` decimal(5,2) NOT NULL,
  `gas` tinyint(1) NOT NULL,
  `agua` tinyint(1) NOT NULL,
  `alquilado` tinyint(1) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `apartamentos`
--

INSERT INTO `apartamentos` (`id_apartamento`, `nro_apartamento`, `porcentaje_participacion`, `gas`, `agua`, `alquilado`, `activo`) VALUES
(30, '1-2', 22.00, 2, 1, 2, 1),
(31, '2-3', 23.00, 1, 1, 1, 1),
(32, '2-1', 1.00, 2, 1, 1, 1),
(33, '12', 23.00, 1, 1, 1, 0),
(34, '2-8', 2.00, 1, 2, 1, 0),
(35, '3-1', 5.00, 2, 1, 1, 1),
(36, '2-5', 52.00, 1, 1, 1, 0),
(37, '4-1', 5.00, 1, 1, 2, 1),
(38, '4-2', 5.00, 1, 1, 2, 0),
(39, '3-2', 1.00, 2, 1, 1, 1),
(41, '5-1', 5.00, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bancos`
--

CREATE TABLE `bancos` (
  `id_banco` int(11) NOT NULL,
  `nombre_banco` varchar(50) NOT NULL,
  `codigo` varchar(7) NOT NULL,
  `numero_cuenta` varchar(50) NOT NULL,
  `tipo_cuenta` varchar(30) NOT NULL,
  `telefono_afiliado` varchar(20) NOT NULL,
  `rif` varchar(30) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bancos`
--

INSERT INTO `bancos` (`id_banco`, `nombre_banco`, `codigo`, `numero_cuenta`, `tipo_cuenta`, `telefono_afiliado`, `rif`, `activo`) VALUES
(1, 'venezuela', '0102', '0102123412124232323', 'Ahorro', '04152456842', 'J3232421', 1),
(6, 'Banesco', '0117', '1242342342424121211', 'Corriente', '04142584985', 'V5464565', 1),
(9, 'Bancaribe', '0114', '01140300063000253595', 'Corriente', '04114124142', 'J305785457', 1),
(11, 'rasdas', '1231', '2342342342342342323', '', '21321253213', 'V2123132', 0),
(12, 'tesoro', '1231', '425646456456456456', '', '24243245564', 'V12345678', 0),
(13, 'Tesoros', '0102', '2423423423423234234', 'Corriente', '23423423232', 'V123412321', 1),
(14, 'sdfsdfs', '2342', '2315646545645656564', '', '23123153545', 'V24653215', 0),
(15, 'aASDASD', '1234', '1231312312312312123', 'Corriente', '12312312323', 'V231231231', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_chica`
--

CREATE TABLE `caja_chica` (
  `id_caja_chica` int(11) NOT NULL,
  `fondo_fijo` decimal(15,2) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `descripcion` text NOT NULL DEFAULT 'Sin descripción',
  `fecha_creacion` date NOT NULL DEFAULT current_timestamp(),
  `anio_fiscal_id` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `caja_chica`
--

INSERT INTO `caja_chica` (`id_caja_chica`, `fondo_fijo`, `estado`, `descripcion`, `fecha_creacion`, `anio_fiscal_id`, `activo`) VALUES
(24, 0.00, 'Cerrada', 'Caja chicas del mes enero - 2026', '2026-01-01', 47, 1),
(25, 1000.00, 'Cerrada', 'Caja chicas del mes Marzo - 2026', '2026-03-09', 47, 1),
(26, 1000.00, 'Cerrada', 'Caja chica - 2026', '2026-04-02', 47, 1),
(27, 1000.00, 'Cerrada', 'Caja chicas del mes Mayo - 2026', '2026-05-17', 47, 1),
(28, 1000.00, 'Abierto', 'Caja chica automática - Mes 6/2026', '2026-06-01', 47, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_gastos`
--

CREATE TABLE `detalles_gastos` (
  `id_detalle_gasto` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `metodo_pago` varchar(20) NOT NULL,
  `gasto_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_gastos`
--

INSERT INTO `detalles_gastos` (`id_detalle_gasto`, `fecha`, `monto`, `metodo_pago`, `gasto_id`) VALUES
(164, '2026-02-03', 12.00, 'Pago Movil', 133),
(217, '2026-02-21', 12.00, 'Efectivo', 136),
(218, '2026-02-21', 900.00, 'Efectivo', 135),
(2001, '2025-12-20', 150.00, 'Transferencia', 201),
(2002, '2026-01-15', 700.00, 'Transferencia', 202),
(2003, '2026-01-25', 1500.00, 'Divisa', 203),
(2004, '2026-02-15', 720.00, 'Pago Movil', 204),
(2005, '2026-03-02', 300.00, 'Efectivo', 205),
(2006, '2026-03-05', 43.00, 'Efectivo', 206),
(2011, '2026-03-08', 12312.00, 'Efectivo', 207),
(2012, '2026-03-07', 21.00, 'Pago Movil', 207),
(2017, '2026-03-16', 910.00, 'Efectivo', 208),
(2018, '2026-02-21', 32.00, 'Efectivo', 134),
(2019, '2026-02-06', 12.00, 'Pago Movil', 134),
(2020, '2026-03-17', 0.00, 'Efectivo', 209),
(2021, '2026-03-17', 0.00, 'Efectivo', 210),
(2023, '2026-03-17', 15.00, 'Efectivo', 211),
(2024, '2026-03-17', 10.00, 'Transferencia', 211),
(2027, '2026-05-21', 10.00, 'Efectivo', 213),
(2028, '2026-05-21', 10.00, 'Transferencia', 213),
(2047, '2026-05-23', 10.00, 'Transferencia', 212),
(2048, '2026-05-26', 55.00, 'Efectivo', 214),
(2049, '2026-05-27', 30.00, 'Transferencia', 215),
(2050, '2026-05-27', 60.00, 'Efectivo', 216),
(2051, '2026-05-28', 50.00, 'Pago Movil', 217),
(2052, '2026-06-03', 50.00, 'Efectivo', 218),
(2053, '2026-06-03', 20.00, 'Transferencia', 219),
(2054, '2026-06-03', 20.00, 'Efectivo', 220),
(2055, '2026-06-03', 20.00, 'Pago Movil', 221),
(2056, '2026-06-03', 25.00, 'Efectivo', 222);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_pagos`
--

CREATE TABLE `detalles_pagos` (
  `id_detalle_pago` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `tipo_pago` varchar(20) NOT NULL,
  `pago_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_pagos`
--

INSERT INTO `detalles_pagos` (`id_detalle_pago`, `fecha`, `monto`, `tipo_pago`, `pago_id`) VALUES
(1026, '2026-05-18', 50.00, 'Efectivo', 135),
(1030, '2026-05-18', 16.60, 'Pago Movil', 138),
(1031, '2026-05-19', 50.00, 'Transferencia', 139),
(1032, '2026-05-20', 10.00, 'Efectivo', 140),
(1033, '2026-05-20', 10.00, 'Efectivo', 141),
(1034, '2026-05-20', 10.00, 'Efectivo', 142),
(1035, '2026-05-20', 25.00, 'Pago Movil', 143),
(1036, '2026-05-01', 20.00, 'Divisa', 143),
(1041, '2026-05-23', 12.00, 'Divisa', 143),
(1055, '2026-05-23', 8.00, 'Transferencia', 143),
(1056, '2026-05-26', 505.00, 'Transferencia', 147),
(1057, '2026-05-27', 20.00, 'Transferencia', 148),
(1058, '2026-06-03', 10.00, 'Transferencia', 149),
(1059, '2026-06-03', 20.00, 'Transferencia', 150),
(1060, '2026-06-03', 12.00, 'Transferencia', 151),
(1063, '2026-06-03', 10.00, 'Transferencia', 154),
(1064, '2026-06-03', 20.00, 'Transferencia', 155),
(1065, '2026-06-03', 100.00, 'Efectivo', 156),
(1066, '2026-06-03', 100.00, 'Efectivo', 157),
(1068, '2026-06-03', 20.00, 'Transferencia', 153),
(1069, '2026-06-03', 10.00, 'Transferencia', 152),
(1071, '2026-06-04', 10.00, 'Pago Movil', 158),
(1073, '2026-06-08', 5.00, 'Efectivo', 160),
(1075, '2026-06-08', 5.00, 'Efectivo', 161),
(1077, '2026-06-08', 5.00, 'Efectivo', 162),
(1078, '2026-06-08', 5.00, 'Efectivo', 163),
(1079, '2026-06-08', 5.00, 'Efectivo', 164),
(1080, '2026-06-08', 10.00, 'Efectivo', 165),
(1081, '2026-06-08', 5.00, 'Efectivo', 166),
(1083, '2026-06-08', 10.00, 'Efectivo', 159);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_presupuesto`
--

CREATE TABLE `detalles_presupuesto` (
  `id_detalle_presupuesto` int(11) NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `nombre_detalle` varchar(50) NOT NULL,
  `presupuesto_id` int(11) NOT NULL,
  `tipo_gasto_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_presupuesto`
--

INSERT INTO `detalles_presupuesto` (`id_detalle_presupuesto`, `monto`, `nombre_detalle`, `presupuesto_id`, `tipo_gasto_id`) VALUES
(1327, 15.00, 'CORPOELEC', 106, 2),
(1328, 12.00, 'HIDROLARA', 106, 2),
(1329, 100.00, 'Trabajadora Residencial', 106, 3),
(1330, 100.00, 'Bono de alimentacion', 106, 3),
(1331, 100.00, 'Bono de ayuda', 106, 3),
(1332, 100.00, 'Seguridad Social', 106, 3),
(1333, 100.00, 'Mantenimiento ascensor', 106, 4),
(1334, 100.00, 'GAS LARA', 106, 5),
(1335, 100.00, 'Bolsas de Basura', 106, 9),
(1336, 100.00, 'Productos de Limpieza', 106, 9),
(1337, 100.00, 'Comisiones Bancarias', 106, 10),
(1338, 100.00, 'Exencion cuota del administrador', 106, 10),
(1351, 23.00, 'CORPOELEC', 107, 2),
(1352, 15.00, 'HIDROLARA', 107, 2),
(1353, 233.00, 'Trabajadora Residencial', 107, 3),
(1354, 12.00, 'Bono de alimentacion', 107, 3),
(1355, 12.00, 'Bono de ayuda', 107, 3),
(1356, 100.00, 'Seguridad Social', 107, 3),
(1357, 12.00, 'Mantenimiento ascensor', 107, 4),
(1358, 200.00, 'GAS LARA', 107, 5),
(1359, 20.00, 'Bolsas de Basura', 107, 9),
(1360, 200.00, 'Productos de Limpieza', 107, 9),
(1361, 200.00, 'Comisiones Bancarias', 107, 10),
(1362, 200.00, 'Exencion cuota del administrador', 107, 10),
(1387, 12.00, 'Exencion cuota del administrador', 110, 10),
(1388, 10.00, 'CORPOELEC', 111, 2),
(1389, 5.00, 'HIDROLARA', 111, 2),
(1390, 123.00, 'Trabajadora Residencial', 111, 3),
(1391, 15.00, 'Bono de alimentacion', 111, 3),
(1392, 123.00, 'Bono de ayuda', 111, 3),
(1393, 15.00, 'Seguridad Social', 111, 3),
(1394, 12.00, 'Mantenimiento ascensor', 111, 4),
(1395, 23.00, 'GAS LARA', 111, 5),
(1396, 51.00, 'Bolsas de Basura', 111, 9),
(1397, 12.00, 'Productos de Limpieza', 111, 9),
(1398, 10.00, 'Comisiones Bancarias', 111, 10),
(1399, 21.00, 'Exencion cuota del administrador', 111, 10),
(1412, 10.00, 'CORPOELEC', 112, 2),
(1413, 20.00, 'HIDROLARA', 112, 2),
(1414, 50.00, 'Trabajadora Residencial', 112, 3),
(1415, 10.00, 'Bono de alimentacion', 112, 3),
(1416, 60.00, 'Bono de ayuda', 112, 3),
(1417, 50.00, 'Seguridad Social', 112, 3),
(1418, 85.00, 'Mantenimiento ascensor', 112, 4),
(1419, 78.00, 'GAS LARA', 112, 5),
(1420, 100.00, 'Bolsas de Basura', 112, 9),
(1421, 100.00, 'Productos de Limpieza', 112, 9),
(1422, 100.00, 'Comisiones Bancarias', 112, 10),
(1423, 100.00, 'Exencion cuota del administrador', 112, 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `egresos_bancarios`
--

CREATE TABLE `egresos_bancarios` (
  `referencia` varchar(20) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `banco_id` int(11) NOT NULL,
  `detalle_gasto_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `egresos_bancarios`
--

INSERT INTO `egresos_bancarios` (`referencia`, `imagen`, `banco_id`, `detalle_gasto_id`) VALUES
('31231', 'javascript-logo-javascript-icon-transparent-free-png_1771563760_144.png', 1, 164),
('GASTO-001', '', 1, 2001),
('GASTO-002', '', 1, 2002),
('GASTO-003', '', 1, 2004),
('42321', 'mensualidad_1779395597_500.PNG', 1, 2028),
('1235412', 'CSS-Logo_1773003460_864.jpg', 6, 2012),
('412123', 'mensualidad_1771710356_291.PNG', 6, 2019),
('5858822', '955ad5d5-ef40-44a7-8b1a-e3213090e54d_1779922826_345.jpeg', 6, 2049),
('508538466', '74df44ed-237d-4196-84eb-ab585842a1ac_1779972265_875.jpeg', 6, 2051),
('266564464', 'aeaa2ba2-b172-400d-bd3d-1cf7dffe2cae_1780491076_932.png', 6, 2053),
('208569', 'a65cc271-3c1f-438c-ad40-a44b8dc349f4_1780496404_663.jpeg', 6, 2055),
('543524', 'images__2__1773805029_187.png', 9, 2024);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id_gasto` int(11) NOT NULL,
  `clasificacion` varchar(20) NOT NULL,
  `tasa_dolar` decimal(15,2) NOT NULL DEFAULT 1.00,
  `tipo_gasto_id` int(11) NOT NULL,
  `solicitud_id` int(11) DEFAULT NULL,
  `proveedor_id` int(11) NOT NULL,
  `descripcion_gasto` text NOT NULL DEFAULT 'Sin descripción',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gastos`
--

INSERT INTO `gastos` (`id_gasto`, `clasificacion`, `tasa_dolar`, `tipo_gasto_id`, `solicitud_id`, `proveedor_id`, `descripcion_gasto`, `activo`) VALUES
(133, 'fijo', 1.00, 2, NULL, 3, 'asdasdasasd', 0),
(134, 'fijo', 1.00, 2, NULL, 3, '22222a2222222224', 1),
(135, 'reposicion', 1.00, 1, NULL, 1, 'Reposición de Caja Chica - 22/02/2026', 1),
(136, 'fijo', 1.00, 2, NULL, 3, 'wwwwwwwwwwwww', 0),
(201, 'Fijo', 1.00, 1, NULL, 1, 'Pago de servicio de agua', 0),
(202, 'Fijo', 1.00, 2, NULL, 1, 'Honorarios de vigilancia Enero', 0),
(203, 'Variable', 1.00, 3, NULL, 1, 'Reparación de bomba de agua', 1),
(204, 'Fijo', 1.00, 2, NULL, 1, 'Honorarios de vigilancia Febrero', 1),
(205, 'Variable', 1.00, 4, NULL, 1, 'Compra de artículos de limpieza', 1),
(206, 'fijo', 1.00, 1, NULL, 2, 'zzzzzzzzzzzzzzzzz', 0),
(207, 'variable', 1.00, 2, NULL, 3, 'prueba de gasto 1', 1),
(208, 'reposicion', 1.00, 1, NULL, 1, 'Reposición de Caja Chica - 16/03/2026', 1),
(209, 'reposicion', 1.00, 1, NULL, 1, 'Reposición de Caja Chica - 17/03/2026', 1),
(210, 'reposicion', 1.00, 1, NULL, 1, 'Reposición de Caja Chica - 17/03/2026', 1),
(211, 'variable', 1.00, 2, NULL, 3, 'asdasdasdasd', 1),
(212, 'fijo', 1.00, 1, NULL, 3, 'asdasdasdasd', 1),
(213, 'FIJO', 523.67, 2, 15, 2, 'sssssssssss', 1),
(214, 'FIJO', 1.00, 2, NULL, 4, 'Zbsvss', 1),
(215, 'VARIABLE', 1.00, 2, NULL, 2, 'Tvybyb', 1),
(216, 'VARIABLE', 1.00, 2, NULL, 2, 'Hola', 1),
(217, 'FIJO', 1.00, 4, NULL, 4, 'Hola ', 1),
(218, 'VARIABLE', 1.00, 3, NULL, 2, 'Hola ', 1),
(219, 'VARIABLE', 1.00, 2, NULL, 2, 'Hola buenas ', 1),
(220, 'VARIABLE', 1.00, 2, NULL, 2, 'Hola ', 1),
(221, 'VARIABLE', 1.00, 2, NULL, 2, 'Hola ', 1),
(222, 'VARIABLE', 1.00, 2, NULL, 2, 'Ffff', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitantes`
--

CREATE TABLE `habitantes` (
  `id_habitante` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `cedula` varchar(15) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `sexo` varchar(10) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habitantes`
--

INSERT INTO `habitantes` (`id_habitante`, `nombre`, `apellido`, `cedula`, `telefono`, `correo`, `fecha_nacimiento`, `sexo`, `activo`) VALUES
(43, 'pepe', 'asdasda', 'E1105510', '12312312123', 'pepe@gmail.com', '2000-12-12', 'Masculino', 1),
(44, 'jesa', 'asdasd', 'V15321212', '21312312121', 'asda@asasd.ocm', '2000-10-10', 'Masculino', 1),
(45, 'asdasd', 'asdasdas', 'V12012120', '23423423434', 'ASDASD@sfas.com', '1980-10-10', 'Femenino', 1),
(46, 'papap', 'lalala', 'V23424234', '21312312312', 'lala@gasmic.com', '1950-10-10', 'Masculino', 1),
(47, 'ssdfsdf', 'asda', 'V23432423', '23423423423', 'asdasda@asfas.com', '1945-10-10', 'Femenino', 1),
(48, 'boor', 'borra', 'V23423234', '23654564321', 'asd@asd.com', '1999-01-01', 'Masculino', 0),
(49, 'asa', 'asdasd', 'V2343121', '12313455648', 'asda@adsd.com', '1999-10-10', 'Masculino', 0),
(50, 'asa', 'asdasd', 'V23432121', '12313455648', 'asda@adaassdsd.com', '1999-10-10', 'Masculino', 0),
(51, 'fhfgh', 'asdasd', 'V2342342', '42342342342', 'asdasd@asd.com', '1999-10-10', 'Masculino', 1),
(52, 'dasdas', 'asdasd', 'E12312313', '22342323232', 'ada@asd.com', '2000-10-10', 'Femenino', 0),
(54, 'Maria', 'Gomez', 'V29123456', '04141234567', 'maria@mail.com', '1992-02-02', 'Femenino', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitantes_apartamentos`
--

CREATE TABLE `habitantes_apartamentos` (
  `apartamento_id` int(11) NOT NULL,
  `habitante_id` int(11) NOT NULL,
  `tipo_vinculo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habitantes_apartamentos`
--

INSERT INTO `habitantes_apartamentos` (`apartamento_id`, `habitante_id`, `tipo_vinculo`) VALUES
(30, 43, 'Propietario'),
(30, 44, 'Habitante'),
(30, 48, 'Habitante'),
(30, 49, 'Habitante'),
(30, 50, 'Habitante'),
(30, 51, 'Habitante'),
(30, 52, 'Habitante'),
(31, 46, 'Propietario'),
(32, 45, 'Propietario'),
(34, 54, 'Habitante'),
(35, 47, 'Propietario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingresos_bancarios`
--

CREATE TABLE `ingresos_bancarios` (
  `referencia` varchar(20) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `banco_id` int(11) NOT NULL,
  `detalle_pago_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ingresos_bancarios`
--

INSERT INTO `ingresos_bancarios` (`referencia`, `imagen`, `banco_id`, `detalle_pago_id`) VALUES
('53246', 'fiabil_1779159711_524.PNG', 1, 1030),
('5865659', '43c0a065-a53e-4115-bd8c-004838072d63_1779235146_170.jpeg', 1, 1031),
('4686286', '10367e49-f030-4e26-8ac1-c8342ae380b1_1779920771_991.jpeg', 1, 1057),
('3235654', 'default.png', 1, 1058),
('283833468', 'default.png', 1, 1059),
('5683758', 'default.png', 1, 1060),
('20464648', 'd2a87bcc-ec8a-4ad8-9944-e6c86c314a78_1780494949_475.jpeg', 1, 1063),
('289676646', '95e8b8e8-115a-4a02-a081-461511fc0a5e_1780490946_260.jpeg', 1, 1068),
('268656556', '22c76522-3b75-45c1-85a4-e273383b6fef_1780460215_709.jpeg', 1, 1069),
('1345312', 'cog_1779299571_171.PNG', 6, 1035),
('4686858', 'ff3aa0e1-7886-43ac-b0bc-18e73d4fc263_1779820535_428.jpeg', 6, 1056),
('---------', 'd8321413-2506-40fa-9d46-c728a2376ae4_1780495132_607.jpeg', 6, 1064);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensualidad`
--

CREATE TABLE `mensualidad` (
  `id_mensualidad` int(11) NOT NULL,
  `periodo_id` int(11) NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `apartamento_id` int(11) NOT NULL,
  `porcentaje_interes` decimal(5,2) NOT NULL DEFAULT 10.00,
  `limite_mensualidad` tinyint(2) NOT NULL DEFAULT 15,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensualidad`
--

INSERT INTO `mensualidad` (`id_mensualidad`, `periodo_id`, `monto`, `apartamento_id`, `porcentaje_interes`, `limite_mensualidad`, `activo`) VALUES
(663, 5, 66.00, 30, 10.00, 15, 1),
(664, 5, 3.00, 32, 10.00, 15, 1),
(665, 5, 92.00, 31, 10.00, 15, 1),
(666, 5, 15.00, 35, 10.00, 15, 1),
(667, 5, 3.00, 39, 10.00, 15, 1),
(668, 5, 20.00, 37, 10.00, 15, 1),
(669, 5, 20.00, 41, 10.00, 15, 1),
(684, 7, 190.00, 30, 10.00, 15, 1),
(685, 7, 177.79, 31, 10.00, 15, 1),
(686, 7, 7.73, 32, 10.00, 15, 1),
(687, 7, 38.65, 35, 10.00, 15, 1),
(688, 7, 38.65, 37, 10.00, 15, 1),
(689, 7, 7.73, 39, 10.00, 15, 1),
(690, 7, 38.65, 41, 10.00, 15, 1);

--
-- Disparadores `mensualidad`
--
DELIMITER $$
CREATE TRIGGER `tr_validar_montos_mensualidad_editar` BEFORE UPDATE ON `mensualidad` FOR EACH ROW BEGIN


    IF NEW.monto < 0 THEN


        SIGNAL SQLSTATE '45000'


        SET MESSAGE_TEXT = 'Error Crítico de BD: No se permiten montos negativos en Mensualidades.';


    END IF;


END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_validar_montos_mensualidad_registrar` BEFORE INSERT ON `mensualidad` FOR EACH ROW BEGIN


    IF NEW.monto < 0 THEN


        SIGNAL SQLSTATE '45000'


        SET MESSAGE_TEXT = 'Error Crítico de BD: No se permiten montos negativos en Mensualidades.';


    END IF;


END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_caja`
--

CREATE TABLE `movimientos_caja` (
  `id_movimiento_caja` int(11) NOT NULL,
  `concepto` varchar(100) NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `tasa_dolar` decimal(15,2) NOT NULL DEFAULT 1.00,
  `fecha` date NOT NULL,
  `estado` varchar(30) NOT NULL,
  `caja_chica_id` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos_caja`
--

INSERT INTO `movimientos_caja` (`id_movimiento_caja`, `concepto`, `monto`, `tasa_dolar`, `fecha`, `estado`, `caja_chica_id`, `activo`) VALUES
(42, 'cafe', 10.00, 1.00, '2026-03-09', 'Repuesto', 25, 1),
(43, 'se pagaron 3 bombillos nuevos', 900.00, 1.00, '2026-03-15', 'Repuesto', 25, 1),
(44, 'aaaaa', 10.00, 1.00, '2026-03-16', 'Pendiente por reposicion', 25, 0),
(57, 'Compra de bombillos seguros', 50.00, 1.00, '2026-06-02', 'Pendiente por reposicion', 26, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `tasa_dolar` decimal(15,2) NOT NULL DEFAULT 1.00,
  `observacion` text NOT NULL DEFAULT 'Sin observación',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `estado`, `tasa_dolar`, `observacion`, `activo`) VALUES
(135, 'PROCESADO', 530.00, 'pago', 1),
(138, 'PROCESADO', 1.00, 'pago', 1),
(139, 'PROCESADO', 1.00, 'Pago registrado desde la App', 1),
(140, 'PROCESADO', 0.00, 'Estado actualizado desde la App Móvil', 1),
(141, 'PROCESADO', 0.00, 'Estado actualizado desde la App Móvil', 1),
(142, 'RECHAZADO', 520.91, 'pago', 1),
(143, 'PROCESADO', 535.00, 'Doble revisi?n ejecutada por T2', 1),
(144, 'ANULADO', 535.00, 'Abono registrado por Transacci?n 2', 0),
(146, 'ANULADO', 535.00, 'Abono registrado por Transacci?n 2', 0),
(147, 'PROCESADO', 1.00, 'Estado actualizado desde la App Móvil', 1),
(148, 'PROCESADO', 1.00, 'Estado actualizado desde la App Móvil', 1),
(149, 'PENDIENTE', 557.97, 'Pago registrado desde la App', 1),
(150, 'RECHAZADO', 557.97, 'Estado actualizado desde la App Móvil', 1),
(151, 'PENDIENTE', 557.97, 'Pago registrado desde la App', 1),
(152, 'PENDIENTE', 557.97, 'Pago registrado desde la Apps', 1),
(153, 'PENDIENTE', 558.64, 'Pago registrado desde la App', 1),
(154, 'PENDIENTE', 558.64, 'Pago registrado desde la App', 1),
(155, 'PROCESADO', 558.64, 'Estado actualizado desde la App Móvil', 1),
(156, 'PENDIENTE', 558.64, 'Pago registrado desde la App', 1),
(157, 'RECHAZADO', 558.64, 'Estado actualizado desde la App Móvil', 1),
(158, 'PENDIENTE', 560.38, 'pago de 2-3', 1),
(159, 'PROCESADO', 563.29, 'pago', 1),
(160, 'PROCESADO', 563.29, 'pago', 1),
(161, 'PROCESADO', 563.29, 'pago', 1),
(162, 'PROCESADO', 563.29, 'pago procesado', 1),
(163, 'PROCESADO', 563.29, 'pago', 1),
(164, 'PROCESADO', 563.29, 'pago', 1),
(165, 'PENDIENTE', 563.29, 'pago pendiente de pepe', 1),
(166, 'PROCESADO', 563.29, 'pago confirmado de pepe', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_mensualidad`
--

CREATE TABLE `pagos_mensualidad` (
  `pago_id` int(11) NOT NULL,
  `mensualidad_id` int(11) NOT NULL,
  `monto_abonado` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos_mensualidad`
--

INSERT INTO `pagos_mensualidad` (`pago_id`, `mensualidad_id`, `monto_abonado`) VALUES
(135, 663, 50.00),
(138, 663, 16.60),
(139, 684, 50.00),
(139, 687, 15.00),
(140, 684, 10.00),
(141, 684, 10.00),
(142, 684, 10.00),
(143, 686, 8.50),
(144, 688, 18.65),
(146, 688, 18.65),
(147, 690, 505.00),
(148, 684, 20.00),
(149, 686, 10.00),
(150, 684, 20.00),
(151, 684, 12.00),
(152, 684, 10.00),
(153, 684, 20.00),
(154, 684, 10.00),
(155, 684, 20.00),
(156, 684, 100.00),
(157, 684, 100.00),
(158, 685, 10.00),
(159, 687, 10.00),
(160, 689, 5.00),
(161, 669, 5.00),
(162, 688, 5.00),
(163, 688, 5.00),
(164, 688, 5.00),
(165, 684, 10.00),
(166, 684, 5.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodos_mensualidad`
--

CREATE TABLE `periodos_mensualidad` (
  `id_periodo` int(11) NOT NULL,
  `mes` varchar(2) NOT NULL,
  `anio` varchar(4) NOT NULL,
  `tasa_dolar` decimal(15,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `periodos_mensualidad`
--

INSERT INTO `periodos_mensualidad` (`id_periodo`, `mes`, `anio`, `tasa_dolar`, `activo`) VALUES
(5, '1', '2026', 560.38, 1),
(7, '05', '2026', 515.18, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto`
--

CREATE TABLE `presupuesto` (
  `id_presupuesto` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `cuota_reserva` decimal(15,2) NOT NULL,
  `tasa_dolar` decimal(15,2) NOT NULL DEFAULT 1.00,
  `observacion` text NOT NULL DEFAULT 'Sin observación',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto`
--

INSERT INTO `presupuesto` (`id_presupuesto`, `fecha`, `cuota_reserva`, `tasa_dolar`, `observacion`, `activo`) VALUES
(106, '2026-01-01', 10.00, 1.00, 'enero 2026', 1),
(107, '2026-02-01', 230.00, 1.00, 'febrero 2026 editado', 0),
(110, '2026-03-01', 234.00, 1.00, 'Sin observación', 1),
(111, '2026-04-01', 10.00, 1.00, 'presupuesto abril', 1),
(112, '2026-05-01', 10.00, 1.00, 'presupuesto mayo', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_mensualidad`
--

CREATE TABLE `presupuesto_mensualidad` (
  `detalle_presupuesto_id` int(11) NOT NULL,
  `mensualidad_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto_mensualidad`
--

INSERT INTO `presupuesto_mensualidad` (`detalle_presupuesto_id`, `mensualidad_id`) VALUES
(1328, 684),
(1333, 663),
(1333, 664),
(1333, 665),
(1333, 666),
(1333, 667),
(1333, 668),
(1333, 669),
(1334, 665),
(1334, 668),
(1334, 669),
(1337, 663),
(1337, 664),
(1337, 665),
(1337, 666),
(1337, 667),
(1337, 668),
(1337, 669),
(1338, 663),
(1338, 664),
(1338, 665),
(1338, 666),
(1338, 667),
(1338, 668),
(1338, 669),
(1412, 684),
(1412, 685),
(1412, 686),
(1412, 688),
(1412, 689),
(1412, 690),
(1413, 684),
(1413, 685),
(1413, 686),
(1413, 688),
(1413, 689),
(1413, 690),
(1414, 684),
(1414, 685),
(1414, 686),
(1414, 688),
(1414, 689),
(1414, 690),
(1415, 684),
(1415, 685),
(1415, 686),
(1415, 688),
(1415, 689),
(1415, 690),
(1416, 684),
(1416, 685),
(1416, 686),
(1416, 688),
(1416, 689),
(1416, 690),
(1417, 684),
(1417, 685),
(1417, 686),
(1417, 688),
(1417, 689),
(1417, 690),
(1418, 684),
(1418, 685),
(1418, 686),
(1418, 688),
(1418, 689),
(1418, 690),
(1419, 684),
(1419, 685),
(1419, 686),
(1419, 688),
(1419, 689),
(1419, 690),
(1420, 684),
(1420, 685),
(1420, 686),
(1420, 688),
(1420, 689),
(1420, 690),
(1421, 684),
(1421, 685),
(1421, 686),
(1421, 688),
(1421, 689),
(1421, 690),
(1422, 684),
(1422, 685),
(1422, 686),
(1422, 688),
(1422, 689),
(1422, 690),
(1423, 684),
(1423, 685),
(1423, 686),
(1423, 688),
(1423, 689),
(1423, 690);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `nombre_proveedor` varchar(50) NOT NULL,
  `servicio` varchar(50) NOT NULL,
  `rif` varchar(20) NOT NULL,
  `direccion` text NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `nombre_proveedor`, `servicio`, `rif`, `direccion`, `activo`) VALUES
(1, 'Administración (Caja Chica)', 'Reposición de Caja', 'J0000000', 'Oficina Administrativa', 1),
(2, 'Proimca', 'Luz', 'V3434523', 'Quibor', 1),
(3, 'Jardinero', 'Trabajos en jardineria', 'E13123343', 'terminal', 1),
(4, 'Reparaciones CA', 'Reparara', 'V2342344', 'Zona industrial', 1),
(5, 'Gas Lara', 'Gas', 'V2352345', 'Lara', 1),
(14, 'pepe', 'pepes', 'E1231245', 'Pepelandia', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reposiciones`
--

CREATE TABLE `reposiciones` (
  `gasto_id` int(11) NOT NULL,
  `movimiento_caja_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reposiciones`
--

INSERT INTO `reposiciones` (`gasto_id`, `movimiento_caja_id`) VALUES
(208, 42),
(208, 43);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_gasto`
--

CREATE TABLE `solicitudes_gasto` (
  `id_solicitud` int(11) NOT NULL,
  `fecha_reporte` date NOT NULL,
  `descripcion_necesidad` text NOT NULL,
  `nombre_solicitante` varchar(50) NOT NULL,
  `monto_estimado` decimal(15,2) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `presupuesto_id` int(11) NOT NULL,
  `prioridad` varchar(20) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitudes_gasto`
--

INSERT INTO `solicitudes_gasto` (`id_solicitud`, `fecha_reporte`, `descripcion_necesidad`, `nombre_solicitante`, `monto_estimado`, `estado`, `presupuesto_id`, `prioridad`, `activo`) VALUES
(15, '2026-03-18', 'asdasdasdas', 'aasasd', 12.00, 'Pendiente', 106, '2', 1),
(16, '2026-04-12', 'ssssss', 'aaaaa', 12.00, 'Pendiente', 106, '1', 1),
(17, '2026-04-11', 'asassss', 'miguel', 10.00, 'Pendiente', 110, '3', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_gasto`
--

CREATE TABLE `tipo_gasto` (
  `id_tipo_gasto` int(11) NOT NULL,
  `nombre_tipo_gasto` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_gasto`
--

INSERT INTO `tipo_gasto` (`id_tipo_gasto`, `nombre_tipo_gasto`, `activo`) VALUES
(1, 'Reposición de Caja Chica', 1),
(2, 'Servicios Públicos', 1),
(3, 'Personal y Obligaciones Laborales', 1),
(4, 'Mantenimientos y Reparaciones', 1),
(5, 'Servicio de Gas', 1),
(9, 'Suministros de Limpieza y Operacion', 1),
(10, 'Gastos Administrativos y Financieros', 1),
(14, 'random', 0),
(15, 'hola', 0),
(16, 'aaaa', 0);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_ejecucion_presupuesto`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_ejecucion_presupuesto` (
`id_presupuesto` int(11)
,`anio_presupuesto` int(4)
,`descripcion_presupuesto` text
,`partida` varchar(50)
,`monto_presupuestado` decimal(15,2)
,`monto_ejecutado` decimal(37,2)
,`disponible` decimal(38,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_estado_cuentas_mensualidad`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_estado_cuentas_mensualidad` (
`id_mensualidad` int(11)
,`apartamento_id` int(11)
,`nro_apartamento` varchar(5)
,`mes` varchar(2)
,`anio` varchar(4)
,`monto_cuota` decimal(15,2)
,`total_abonado` decimal(37,2)
,`deuda_pendiente` decimal(38,2)
,`estado_pago` varchar(9)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_historial_pagos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_historial_pagos` (
`id_pago` int(11)
,`estado` varchar(20)
,`activo` tinyint(1)
,`ultima_fecha` date
,`monto_total` decimal(37,2)
,`tipo_pago_predominante` mediumtext
,`apartamento` varchar(6)
,`periodos` mediumtext
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_saldo_caja_chica`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_saldo_caja_chica` (
`id_caja_chica` int(11)
,`estado` varchar(20)
,`monto_base` decimal(15,2)
,`total_gastado_pendiente` decimal(37,2)
,`saldo_disponible` decimal(38,2)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_ejecucion_presupuesto`
--
DROP TABLE IF EXISTS `vw_ejecucion_presupuesto`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_ejecucion_presupuesto`  AS SELECT `p`.`id_presupuesto` AS `id_presupuesto`, year(`p`.`fecha`) AS `anio_presupuesto`, `p`.`observacion` AS `descripcion_presupuesto`, `tg`.`nombre_tipo_gasto` AS `partida`, `dp`.`monto` AS `monto_presupuestado`, ifnull((select sum(`dg`.`monto`) from (`gastos` `g` join `detalles_gastos` `dg` on(`g`.`id_gasto` = `dg`.`gasto_id`)) where `g`.`tipo_gasto_id` = `dp`.`tipo_gasto_id` and year(`dg`.`fecha`) = year(`p`.`fecha`) and `g`.`activo` = 1),0) AS `monto_ejecutado`, `dp`.`monto`- ifnull((select sum(`dg`.`monto`) from (`gastos` `g` join `detalles_gastos` `dg` on(`g`.`id_gasto` = `dg`.`gasto_id`)) where `g`.`tipo_gasto_id` = `dp`.`tipo_gasto_id` and year(`dg`.`fecha`) = year(`p`.`fecha`) and `g`.`activo` = 1),0) AS `disponible` FROM ((`presupuesto` `p` join `detalles_presupuesto` `dp` on(`p`.`id_presupuesto` = `dp`.`presupuesto_id`)) join `tipo_gasto` `tg` on(`dp`.`tipo_gasto_id` = `tg`.`id_tipo_gasto`)) WHERE `p`.`activo` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_estado_cuentas_mensualidad`
--
DROP TABLE IF EXISTS `vw_estado_cuentas_mensualidad`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_estado_cuentas_mensualidad`  AS SELECT `m`.`id_mensualidad` AS `id_mensualidad`, `m`.`apartamento_id` AS `apartamento_id`, `a`.`nro_apartamento` AS `nro_apartamento`, `p`.`mes` AS `mes`, `p`.`anio` AS `anio`, `m`.`monto` AS `monto_cuota`, coalesce(`abonos`.`total_abonado`,0) AS `total_abonado`, `m`.`monto`- coalesce(`abonos`.`total_abonado`,0) AS `deuda_pendiente`, CASE WHEN `m`.`monto` - coalesce(`abonos`.`total_abonado`,0) <= 0 THEN 'SOLVENTE' ELSE 'PENDIENTE' END AS `estado_pago` FROM (((`mensualidad` `m` join `apartamentos` `a` on(`m`.`apartamento_id` = `a`.`id_apartamento`)) join `periodos_mensualidad` `p` on(`m`.`periodo_id` = `p`.`id_periodo`)) left join (select `pm`.`mensualidad_id` AS `mensualidad_id`,sum(`pm`.`monto_abonado`) AS `total_abonado` from (`pagos_mensualidad` `pm` join `pagos` `pg` on(`pm`.`pago_id` = `pg`.`id_pago`)) where `pg`.`activo` = 1 and ucase(`pg`.`estado`) = 'PROCESADO' group by `pm`.`mensualidad_id`) `abonos` on(`m`.`id_mensualidad` = `abonos`.`mensualidad_id`)) WHERE `m`.`activo` = 1 AND `a`.`activo` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_historial_pagos`
--
DROP TABLE IF EXISTS `vw_historial_pagos`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_historial_pagos`  AS SELECT `p`.`id_pago` AS `id_pago`, ucase(`p`.`estado`) AS `estado`, `p`.`activo` AS `activo`, coalesce(max(`dp`.`fecha`),curdate()) AS `ultima_fecha`, sum(coalesce(`dp`.`monto`,0)) AS `monto_total`, substring_index(group_concat(coalesce(`dp`.`tipo_pago`,'No asignado') order by `dp`.`fecha` DESC separator ','),',',1) AS `tipo_pago_predominante`, CASE WHEN count(distinct `a`.`nro_apartamento`) = 1 THEN max(`a`.`nro_apartamento`) ELSE 'Varios' END AS `apartamento`, group_concat(distinct concat(`pm_per`.`mes`,'/',`pm_per`.`anio`) separator ', ') AS `periodos` FROM (((((`pagos` `p` left join `detalles_pagos` `dp` on(`p`.`id_pago` = `dp`.`pago_id`)) left join `pagos_mensualidad` `pm` on(`p`.`id_pago` = `pm`.`pago_id`)) left join `mensualidad` `m` on(`pm`.`mensualidad_id` = `m`.`id_mensualidad`)) left join `periodos_mensualidad` `pm_per` on(`m`.`periodo_id` = `pm_per`.`id_periodo`)) left join `apartamentos` `a` on(`m`.`apartamento_id` = `a`.`id_apartamento`)) WHERE `p`.`activo` = 1 GROUP BY `p`.`id_pago` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_saldo_caja_chica`
--
DROP TABLE IF EXISTS `vw_saldo_caja_chica`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_saldo_caja_chica`  AS SELECT `cc`.`id_caja_chica` AS `id_caja_chica`, `cc`.`estado` AS `estado`, `cc`.`fondo_fijo` AS `monto_base`, ifnull((select sum(`mc`.`monto`) from `movimientos_caja` `mc` where `mc`.`caja_chica_id` = `cc`.`id_caja_chica` and `mc`.`activo` = 1 and `mc`.`estado` <> 'Repuesto'),0) AS `total_gastado_pendiente`, `cc`.`fondo_fijo`- ifnull((select sum(`mc`.`monto`) from `movimientos_caja` `mc` where `mc`.`caja_chica_id` = `cc`.`id_caja_chica` and `mc`.`activo` = 1 and `mc`.`estado` <> 'Repuesto'),0) AS `saldo_disponible` FROM `caja_chica` AS `cc` WHERE `cc`.`activo` = 1 ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `anio_fiscal`
--
ALTER TABLE `anio_fiscal`
  ADD PRIMARY KEY (`id_anio_fiscal`);

--
-- Indices de la tabla `apartamentos`
--
ALTER TABLE `apartamentos`
  ADD PRIMARY KEY (`id_apartamento`);

--
-- Indices de la tabla `bancos`
--
ALTER TABLE `bancos`
  ADD PRIMARY KEY (`id_banco`),
  ADD UNIQUE KEY `numero_cuenta` (`numero_cuenta`);

--
-- Indices de la tabla `caja_chica`
--
ALTER TABLE `caja_chica`
  ADD PRIMARY KEY (`id_caja_chica`),
  ADD KEY `anio_fiscal_id` (`anio_fiscal_id`);

--
-- Indices de la tabla `detalles_gastos`
--
ALTER TABLE `detalles_gastos`
  ADD PRIMARY KEY (`id_detalle_gasto`),
  ADD KEY `gasto_id` (`gasto_id`);

--
-- Indices de la tabla `detalles_pagos`
--
ALTER TABLE `detalles_pagos`
  ADD PRIMARY KEY (`id_detalle_pago`),
  ADD KEY `detalles_pagos_ibfk_1` (`pago_id`),
  ADD KEY `idx_pago_fecha` (`pago_id`,`fecha`);

--
-- Indices de la tabla `detalles_presupuesto`
--
ALTER TABLE `detalles_presupuesto`
  ADD PRIMARY KEY (`id_detalle_presupuesto`),
  ADD KEY `presupuesto_id` (`presupuesto_id`),
  ADD KEY `tipo_gasto_id` (`tipo_gasto_id`);

--
-- Indices de la tabla `egresos_bancarios`
--
ALTER TABLE `egresos_bancarios`
  ADD PRIMARY KEY (`banco_id`,`detalle_gasto_id`),
  ADD UNIQUE KEY `referencia` (`referencia`),
  ADD KEY `egresos_bancarios_ibfk_1` (`detalle_gasto_id`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id_gasto`),
  ADD KEY `proveedor_id` (`proveedor_id`),
  ADD KEY `tipo_gasto_id` (`tipo_gasto_id`),
  ADD KEY `solicitud_id` (`solicitud_id`);

--
-- Indices de la tabla `habitantes`
--
ALTER TABLE `habitantes`
  ADD PRIMARY KEY (`id_habitante`) USING BTREE,
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `habitantes_apartamentos`
--
ALTER TABLE `habitantes_apartamentos`
  ADD PRIMARY KEY (`apartamento_id`,`habitante_id`),
  ADD KEY `habitantes_apartamentos_ibfk_2` (`habitante_id`);

--
-- Indices de la tabla `ingresos_bancarios`
--
ALTER TABLE `ingresos_bancarios`
  ADD PRIMARY KEY (`banco_id`,`detalle_pago_id`),
  ADD UNIQUE KEY `referencia` (`referencia`),
  ADD KEY `ingresos_bancarios_ibfk_1` (`detalle_pago_id`);

--
-- Indices de la tabla `mensualidad`
--
ALTER TABLE `mensualidad`
  ADD PRIMARY KEY (`id_mensualidad`),
  ADD UNIQUE KEY `periodo_apartamento_unico` (`periodo_id`,`apartamento_id`),
  ADD KEY `apartamento_id` (`apartamento_id`),
  ADD KEY `mensualidad_ibfk_periodo` (`periodo_id`);

--
-- Indices de la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  ADD PRIMARY KEY (`id_movimiento_caja`),
  ADD KEY `caja_chica_id` (`caja_chica_id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`);

--
-- Indices de la tabla `pagos_mensualidad`
--
ALTER TABLE `pagos_mensualidad`
  ADD PRIMARY KEY (`pago_id`,`mensualidad_id`),
  ADD KEY `pagos_mensualidad_ibfk_2` (`mensualidad_id`);

--
-- Indices de la tabla `periodos_mensualidad`
--
ALTER TABLE `periodos_mensualidad`
  ADD PRIMARY KEY (`id_periodo`),
  ADD UNIQUE KEY `periodo_unico` (`mes`,`anio`);

--
-- Indices de la tabla `presupuesto`
--
ALTER TABLE `presupuesto`
  ADD PRIMARY KEY (`id_presupuesto`);

--
-- Indices de la tabla `presupuesto_mensualidad`
--
ALTER TABLE `presupuesto_mensualidad`
  ADD PRIMARY KEY (`detalle_presupuesto_id`,`mensualidad_id`),
  ADD KEY `presupuesto_mensualidad_ibfk_2` (`mensualidad_id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`);

--
-- Indices de la tabla `reposiciones`
--
ALTER TABLE `reposiciones`
  ADD PRIMARY KEY (`gasto_id`,`movimiento_caja_id`),
  ADD KEY `reposiciones_ibfk_2` (`movimiento_caja_id`);

--
-- Indices de la tabla `solicitudes_gasto`
--
ALTER TABLE `solicitudes_gasto`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `presupuesto_mensual_id` (`presupuesto_id`);

--
-- Indices de la tabla `tipo_gasto`
--
ALTER TABLE `tipo_gasto`
  ADD PRIMARY KEY (`id_tipo_gasto`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `anio_fiscal`
--
ALTER TABLE `anio_fiscal`
  MODIFY `id_anio_fiscal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT de la tabla `apartamentos`
--
ALTER TABLE `apartamentos`
  MODIFY `id_apartamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `bancos`
--
ALTER TABLE `bancos`
  MODIFY `id_banco` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `caja_chica`
--
ALTER TABLE `caja_chica`
  MODIFY `id_caja_chica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `detalles_gastos`
--
ALTER TABLE `detalles_gastos`
  MODIFY `id_detalle_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2057;

--
-- AUTO_INCREMENT de la tabla `detalles_pagos`
--
ALTER TABLE `detalles_pagos`
  MODIFY `id_detalle_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1084;

--
-- AUTO_INCREMENT de la tabla `detalles_presupuesto`
--
ALTER TABLE `detalles_presupuesto`
  MODIFY `id_detalle_presupuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1424;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=223;

--
-- AUTO_INCREMENT de la tabla `habitantes`
--
ALTER TABLE `habitantes`
  MODIFY `id_habitante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de la tabla `mensualidad`
--
ALTER TABLE `mensualidad`
  MODIFY `id_mensualidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=710;

--
-- AUTO_INCREMENT de la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  MODIFY `id_movimiento_caja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

--
-- AUTO_INCREMENT de la tabla `periodos_mensualidad`
--
ALTER TABLE `periodos_mensualidad`
  MODIFY `id_periodo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `presupuesto`
--
ALTER TABLE `presupuesto`
  MODIFY `id_presupuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `solicitudes_gasto`
--
ALTER TABLE `solicitudes_gasto`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `tipo_gasto`
--
ALTER TABLE `tipo_gasto`
  MODIFY `id_tipo_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `caja_chica`
--
ALTER TABLE `caja_chica`
  ADD CONSTRAINT `caja_chica_ibfk_1` FOREIGN KEY (`anio_fiscal_id`) REFERENCES `anio_fiscal` (`id_anio_fiscal`);

--
-- Filtros para la tabla `detalles_gastos`
--
ALTER TABLE `detalles_gastos`
  ADD CONSTRAINT `detalles_gastos_ibfk_1` FOREIGN KEY (`gasto_id`) REFERENCES `gastos` (`id_gasto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalles_pagos`
--
ALTER TABLE `detalles_pagos`
  ADD CONSTRAINT `detalles_pagos_ibfk_1` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id_pago`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalles_presupuesto`
--
ALTER TABLE `detalles_presupuesto`
  ADD CONSTRAINT `detalles_presupuesto_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuesto` (`id_presupuesto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalles_presupuesto_ibfk_2` FOREIGN KEY (`tipo_gasto_id`) REFERENCES `tipo_gasto` (`id_tipo_gasto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `egresos_bancarios`
--
ALTER TABLE `egresos_bancarios`
  ADD CONSTRAINT `egresos_bancarios_ibfk_1` FOREIGN KEY (`detalle_gasto_id`) REFERENCES `detalles_gastos` (`id_detalle_gasto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `egresos_bancarios_ibfk_2` FOREIGN KEY (`banco_id`) REFERENCES `bancos` (`id_banco`);

--
-- Filtros para la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id_proveedor`) ON UPDATE CASCADE,
  ADD CONSTRAINT `gastos_ibfk_2` FOREIGN KEY (`tipo_gasto_id`) REFERENCES `tipo_gasto` (`id_tipo_gasto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `gastos_ibfk_3` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_gasto` (`id_solicitud`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `habitantes_apartamentos`
--
ALTER TABLE `habitantes_apartamentos`
  ADD CONSTRAINT `habitantes_apartamentos_ibfk_1` FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos` (`id_apartamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `habitantes_apartamentos_ibfk_2` FOREIGN KEY (`habitante_id`) REFERENCES `habitantes` (`id_habitante`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ingresos_bancarios`
--
ALTER TABLE `ingresos_bancarios`
  ADD CONSTRAINT `ingresos_bancarios_ibfk_1` FOREIGN KEY (`detalle_pago_id`) REFERENCES `detalles_pagos` (`id_detalle_pago`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ingresos_bancarios_ibfk_2` FOREIGN KEY (`banco_id`) REFERENCES `bancos` (`id_banco`);

--
-- Filtros para la tabla `mensualidad`
--
ALTER TABLE `mensualidad`
  ADD CONSTRAINT `mensualidad_ibfk_1` FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos` (`id_apartamento`) ON UPDATE CASCADE,
  ADD CONSTRAINT `mensualidad_ibfk_periodo` FOREIGN KEY (`periodo_id`) REFERENCES `periodos_mensualidad` (`id_periodo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  ADD CONSTRAINT `movimientos_caja_ibfk_1` FOREIGN KEY (`caja_chica_id`) REFERENCES `caja_chica` (`id_caja_chica`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pagos_mensualidad`
--
ALTER TABLE `pagos_mensualidad`
  ADD CONSTRAINT `pagos_mensualidad_ibfk_2` FOREIGN KEY (`mensualidad_id`) REFERENCES `mensualidad` (`id_mensualidad`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pagos_mensualidad_ibfk_pago_maestro` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id_pago`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `presupuesto_mensualidad`
--
ALTER TABLE `presupuesto_mensualidad`
  ADD CONSTRAINT `presupuesto_mensualidad_ibfk_1` FOREIGN KEY (`detalle_presupuesto_id`) REFERENCES `detalles_presupuesto` (`id_detalle_presupuesto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presupuesto_mensualidad_ibfk_2` FOREIGN KEY (`mensualidad_id`) REFERENCES `mensualidad` (`id_mensualidad`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `reposiciones`
--
ALTER TABLE `reposiciones`
  ADD CONSTRAINT `reposiciones_ibfk_1` FOREIGN KEY (`gasto_id`) REFERENCES `gastos` (`id_gasto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reposiciones_ibfk_2` FOREIGN KEY (`movimiento_caja_id`) REFERENCES `movimientos_caja` (`id_movimiento_caja`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `solicitudes_gasto`
--
ALTER TABLE `solicitudes_gasto`
  ADD CONSTRAINT `solicitudes_gasto_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuesto` (`id_presupuesto`) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Base de datos: `seguridad_haydee_db`
--
DROP DATABASE IF EXISTS `seguridad_haydee_db`;
CREATE DATABASE IF NOT EXISTS `seguridad_haydee_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `seguridad_haydee_db`;

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_token` (IN `p_usuario_id` INT(11), IN `p_tipo` VARCHAR(30), IN `p_token` TEXT, IN `p_fecha_expiracion` DATETIME)   BEGIN
    
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; 
    END;

    
    START TRANSACTION;

    
    
    DELETE FROM tokens_seguridad 
    WHERE usuario_id = p_usuario_id AND fecha_expiracion < NOW();

    
    
    
    DELETE FROM tokens_seguridad 
    WHERE usuario_id = p_usuario_id AND tipo = p_tipo;

    
    INSERT INTO tokens_seguridad (
        usuario_id, 
        tipo, 
        token, 
        fecha_expiracion
    ) VALUES (
        p_usuario_id, 
        p_tipo, 
        p_token, 
        p_fecha_expiracion
    );

    
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_notificar_administradores` (IN `p_tit` VARCHAR(100), IN `p_desc` TEXT, IN `p_tabla` VARCHAR(50), IN `p_id_reg` INT, IN `p_tipo` VARCHAR(50))   BEGIN
    DECLARE v_evento_id INT;
    DECLARE v_admin_id INT;
    DECLARE v_notif_id INT;
    DECLARE v_done INT DEFAULT FALSE;
    
    
    DECLARE cur_admins CURSOR FOR 
        SELECT id_usuario FROM usuarios WHERE rol_id IN (1, 2) AND activo = 1;
        
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = TRUE;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;

    
    INSERT INTO eventos_sistema (tipo_evento, tabla_origen, id_registro_origen, fecha_evento)
    VALUES (p_tipo, p_tabla, p_id_reg, NOW());
    
    SET v_evento_id = LAST_INSERT_ID();

    
    OPEN cur_admins;
    
    read_loop: LOOP
        FETCH cur_admins INTO v_admin_id;
        IF v_done THEN
            LEAVE read_loop;
        END IF;

        
        INSERT INTO notificaciones (titulo, descripcion, fecha, leido, usuario_id)
        VALUES (p_tit, p_desc, NOW(), 0, v_admin_id);
        
        SET v_notif_id = LAST_INSERT_ID();

        
        INSERT INTO notificacion_evento (notificacion_id, evento_id)
        VALUES (v_notif_id, v_evento_id);
        
    END LOOP;
    
    CLOSE cur_admins;

    COMMIT;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacion_permisos`
--

CREATE TABLE `asignacion_permisos` (
  `rol_id` int(11) NOT NULL,
  `permiso_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asignacion_permisos`
--

INSERT INTO `asignacion_permisos` (`rol_id`, `permiso_id`, `modulo_id`) VALUES
(1, 1, 1),
(1, 1, 2),
(1, 1, 3),
(1, 1, 4),
(1, 1, 5),
(1, 1, 6),
(1, 1, 7),
(1, 1, 8),
(1, 1, 9),
(1, 1, 10),
(1, 1, 11),
(1, 1, 12),
(1, 1, 13),
(1, 1, 14),
(1, 1, 15),
(1, 1, 16),
(1, 1, 17),
(1, 1, 18),
(1, 1, 19),
(1, 1, 21),
(1, 1, 22),
(1, 1, 23),
(1, 2, 1),
(1, 2, 2),
(1, 2, 3),
(1, 2, 4),
(1, 2, 5),
(1, 2, 6),
(1, 2, 7),
(1, 2, 8),
(1, 2, 9),
(1, 2, 10),
(1, 2, 11),
(1, 2, 12),
(1, 2, 13),
(1, 2, 14),
(1, 2, 15),
(1, 2, 16),
(1, 2, 17),
(1, 2, 18),
(1, 2, 19),
(1, 2, 21),
(1, 2, 22),
(1, 2, 23),
(1, 3, 1),
(1, 3, 2),
(1, 3, 3),
(1, 3, 4),
(1, 3, 5),
(1, 3, 6),
(1, 3, 7),
(1, 3, 8),
(1, 3, 9),
(1, 3, 10),
(1, 3, 11),
(1, 3, 12),
(1, 3, 13),
(1, 3, 14),
(1, 3, 15),
(1, 3, 16),
(1, 3, 17),
(1, 3, 18),
(1, 3, 19),
(1, 3, 21),
(1, 3, 22),
(1, 3, 23),
(1, 4, 1),
(1, 4, 2),
(1, 4, 3),
(1, 4, 4),
(1, 4, 5),
(1, 4, 6),
(1, 4, 7),
(1, 4, 8),
(1, 4, 9),
(1, 4, 10),
(1, 4, 11),
(1, 4, 12),
(1, 4, 13),
(1, 4, 14),
(1, 4, 15),
(1, 4, 16),
(1, 4, 17),
(1, 4, 18),
(1, 4, 19),
(1, 4, 21),
(1, 4, 22),
(1, 4, 23),
(2, 1, 1),
(2, 1, 2),
(2, 1, 3),
(2, 1, 4),
(2, 1, 5),
(2, 1, 6),
(2, 1, 7),
(2, 1, 8),
(2, 1, 9),
(2, 1, 10),
(2, 1, 11),
(2, 1, 12),
(2, 1, 13),
(2, 1, 14),
(2, 1, 15),
(2, 1, 16),
(2, 1, 17),
(2, 1, 18),
(2, 1, 19),
(2, 1, 21),
(2, 1, 22),
(2, 1, 23),
(2, 2, 1),
(2, 2, 2),
(2, 2, 3),
(2, 2, 4),
(2, 2, 5),
(2, 2, 6),
(2, 2, 7),
(2, 2, 8),
(2, 2, 9),
(2, 2, 10),
(2, 2, 11),
(2, 2, 12),
(2, 2, 13),
(2, 2, 14),
(2, 2, 15),
(2, 2, 16),
(2, 2, 17),
(2, 2, 18),
(2, 2, 19),
(2, 2, 21),
(2, 2, 22),
(2, 2, 23),
(2, 3, 1),
(2, 3, 2),
(2, 3, 3),
(2, 3, 4),
(2, 3, 5),
(2, 3, 6),
(2, 3, 7),
(2, 3, 8),
(2, 3, 9),
(2, 3, 10),
(2, 3, 11),
(2, 3, 12),
(2, 3, 13),
(2, 3, 14),
(2, 3, 15),
(2, 3, 16),
(2, 3, 17),
(2, 3, 18),
(2, 3, 19),
(2, 3, 21),
(2, 3, 22),
(2, 3, 23),
(2, 4, 1),
(2, 4, 2),
(2, 4, 3),
(2, 4, 4),
(2, 4, 5),
(2, 4, 6),
(2, 4, 7),
(2, 4, 8),
(2, 4, 9),
(2, 4, 10),
(2, 4, 11),
(2, 4, 12),
(2, 4, 13),
(2, 4, 14),
(2, 4, 15),
(2, 4, 16),
(2, 4, 17),
(2, 4, 18),
(2, 4, 19),
(2, 4, 21),
(2, 4, 22),
(2, 4, 23),
(3, 1, 1),
(3, 2, 1),
(3, 2, 5),
(3, 3, 1),
(3, 4, 1),
(4, 1, 1),
(4, 1, 2),
(4, 1, 3),
(4, 1, 4),
(4, 1, 5),
(4, 1, 15),
(4, 2, 1),
(4, 2, 2),
(4, 2, 3),
(4, 2, 4),
(4, 2, 5),
(4, 2, 15),
(4, 3, 1),
(4, 3, 2),
(4, 3, 3),
(4, 3, 4),
(4, 3, 5),
(4, 3, 15),
(4, 4, 1),
(4, 4, 2),
(4, 4, 3),
(4, 4, 4),
(4, 4, 5),
(4, 4, 15),
(4, 90, 5),
(68, 1, 1),
(68, 1, 2),
(68, 1, 3),
(68, 1, 10),
(68, 2, 1),
(68, 2, 2),
(68, 2, 3),
(68, 2, 10),
(68, 3, 1),
(68, 3, 2),
(68, 3, 3),
(68, 3, 10),
(68, 4, 1),
(68, 4, 2),
(68, 4, 3),
(68, 4, 10),
(69, 2, 1),
(69, 2, 2),
(69, 2, 3),
(69, 2, 4),
(69, 2, 5),
(69, 2, 6),
(69, 2, 7),
(69, 2, 8),
(69, 2, 9),
(69, 2, 10),
(69, 2, 11),
(69, 2, 12),
(69, 2, 13),
(69, 2, 14),
(69, 2, 15),
(69, 2, 16),
(69, 2, 17),
(69, 2, 18),
(69, 2, 19),
(69, 2, 21),
(69, 2, 22),
(69, 2, 23),
(70, 1, 1),
(70, 1, 2),
(70, 2, 1),
(70, 2, 2),
(70, 3, 1),
(70, 3, 2),
(70, 4, 1),
(70, 4, 2),
(71, 1, 19),
(71, 2, 19),
(71, 3, 19),
(71, 4, 19),
(72, 1, 21),
(72, 2, 21),
(72, 3, 21),
(72, 4, 21);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id_bitacora` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `accion` varchar(20) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  `valores_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{}' CHECK (json_valid(`valores_anteriores`)),
  `valores_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{}' CHECK (json_valid(`valores_nuevos`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bitacora`
--

INSERT INTO `bitacora` (`id_bitacora`, `fecha_hora`, `accion`, `usuario_id`, `modulo_id`, `valores_anteriores`, `valores_nuevos`) VALUES
(4045, '2026-05-07 22:34:00', 'CONSULTAR', 1, 1, '{}', '{}'),
(4046, '2026-05-07 22:34:10', 'CONSULTAR', 1, 1, '{}', '{}'),
(4047, '2026-05-07 22:38:05', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4048, '2026-05-09 22:24:54', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4049, '2026-05-09 22:33:15', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4050, '2026-05-09 22:34:18', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4051, '2026-05-09 22:35:27', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4052, '2026-05-09 22:40:11', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4053, '2026-05-09 22:41:28', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4054, '2026-05-09 22:42:07', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4055, '2026-05-09 22:47:08', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4056, '2026-05-09 22:52:40', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4057, '2026-05-09 22:53:44', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4058, '2026-05-09 23:01:12', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4059, '2026-05-09 23:01:58', 'CONSULTAR', 1, 5, '{}', '{}'),
(4060, '2026-05-09 23:03:25', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4061, '2026-05-09 23:14:22', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4062, '2026-05-09 23:16:13', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4063, '2026-05-09 23:18:30', 'CONSULTAR', 1, 5, '{}', '{}'),
(4064, '2026-05-09 23:20:59', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4065, '2026-05-09 23:26:16', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4066, '2026-05-09 23:34:21', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4067, '2026-05-09 23:40:26', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4068, '2026-05-09 23:45:23', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4069, '2026-05-09 23:58:23', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4070, '2026-05-10 00:10:10', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4071, '2026-05-10 00:11:31', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4072, '2026-05-10 00:19:47', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4073, '2026-05-10 00:24:13', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4074, '2026-05-10 00:25:16', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4075, '2026-05-10 00:28:06', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4076, '2026-05-10 00:28:34', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4077, '2026-05-10 00:33:36', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4078, '2026-05-10 00:34:30', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4079, '2026-05-10 00:36:50', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4080, '2026-05-10 00:39:08', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4081, '2026-05-10 00:44:26', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4082, '2026-05-10 01:00:14', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4083, '2026-05-10 01:03:06', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4084, '2026-05-10 01:05:34', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4085, '2026-05-10 01:06:30', 'CONSULTAR', 1, 5, '{}', '{}'),
(4086, '2026-05-10 01:07:21', 'CONSULTAR', 1, 5, '{}', '{}'),
(4087, '2026-05-10 01:07:36', 'CONSULTAR', 1, 5, '{}', '{}'),
(4088, '2026-05-10 01:08:29', 'CONSULTAR', 1, 5, '{}', '{}'),
(4089, '2026-05-10 01:12:56', 'CONSULTAR', 1, 5, '{}', '{}'),
(4090, '2026-05-10 01:13:57', 'CONSULTAR', 1, 5, '{}', '{}'),
(4091, '2026-05-11 22:20:04', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4092, '2026-05-11 22:20:44', 'CONSULTAR', 1, 1, '{}', '{}'),
(4093, '2026-05-11 22:29:02', 'CONSULTAR', 1, 1, '{}', '{}'),
(4094, '2026-05-11 22:29:21', 'CONSULTAR', 1, 1, '{}', '{}'),
(4095, '2026-05-11 22:29:37', 'CONSULTAR', 1, 19, '{}', '{}'),
(4096, '2026-05-11 23:03:37', 'CONSULTAR', 1, 1, '{}', '{}'),
(4097, '2026-05-11 23:03:41', 'CONSULTAR', 1, 15, '{}', '{}'),
(4098, '2026-05-11 23:04:35', 'CONSULTAR', 1, 7, '{}', '{}'),
(4099, '2026-05-12 10:56:24', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4100, '2026-05-12 10:58:03', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4101, '2026-05-12 10:58:24', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4102, '2026-05-12 11:03:23', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4103, '2026-05-12 11:05:16', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4104, '2026-05-12 11:12:09', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4105, '2026-05-12 11:22:16', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4106, '2026-05-12 11:46:09', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4107, '2026-05-12 13:01:00', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4108, '2026-05-12 13:01:07', 'CONSULTAR', 1, 9, '{}', '{}'),
(4109, '2026-05-12 13:01:34', 'CONSULTAR', 1, 9, '{}', '{}'),
(4110, '2026-05-12 13:48:34', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4111, '2026-05-12 13:48:39', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4112, '2026-05-12 18:05:27', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4113, '2026-05-12 18:05:37', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4114, '2026-05-12 18:05:46', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4115, '2026-05-12 18:05:52', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4116, '2026-05-12 18:45:54', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4117, '2026-05-12 20:09:19', 'CONSULTAR', 1, 9, '{}', '{}'),
(4118, '2026-05-12 20:13:31', 'CONSULTAR', 1, 9, '{}', '{}'),
(4119, '2026-05-12 20:17:06', 'CONSULTAR', 1, 9, '{}', '{}'),
(4120, '2026-05-12 20:25:24', 'CONSULTAR', 1, 6, '{}', '{}'),
(4121, '2026-05-12 20:25:28', 'CONSULTAR', 1, 15, '{}', '{}'),
(4122, '2026-05-13 00:58:11', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4123, '2026-05-13 00:58:21', 'CONSULTAR', 1, 9, '{}', '{}'),
(4124, '2026-05-13 00:59:27', 'REGISTRAR', 1, 9, '{}', '{}'),
(4125, '2026-05-13 00:59:27', 'CONSULTAR', 1, 9, '{}', '{}'),
(4126, '2026-05-13 01:04:34', 'ELIMINAR', 1, 9, '{\"estado\":\"ABIERTO\",\"fecha_inicio\":\"2026-05-13\",\"fecha_cierre\":\"2027-05-13\",\"descripcion\":\"hola\"}', '{}'),
(4127, '2026-05-13 01:04:34', 'CONSULTAR', 1, 9, '{}', '{}'),
(4128, '2026-05-13 01:09:27', 'CONSULTAR', 1, 8, '{}', '{}'),
(4129, '2026-05-13 01:09:52', 'CONSULTAR', 1, 8, '{}', '{}'),
(4130, '2026-05-13 01:25:03', 'CONSULTAR', 1, 19, '{}', '{}'),
(4131, '2026-05-13 01:27:54', 'CONSULTAR', 1, 19, '{}', '{}'),
(4132, '2026-05-13 01:30:53', 'CONSULTAR', 1, 19, '{}', '{}'),
(4133, '2026-05-13 01:31:30', 'CONSULTAR', 1, 14, '{}', '{}'),
(4134, '2026-05-13 01:31:34', 'CONSULTAR', 1, 6, '{}', '{}'),
(4135, '2026-05-13 01:31:41', 'CONSULTAR', 1, 6, '{}', '{}'),
(4136, '2026-05-13 01:32:20', 'CONSULTAR', 1, 6, '{}', '{}'),
(4137, '2026-05-13 01:32:29', 'CONSULTAR', 1, 1, '{}', '{}'),
(4138, '2026-05-13 01:32:34', 'CONSULTAR', 1, 2, '{}', '{}'),
(4139, '2026-05-13 01:32:39', 'CONSULTAR', 1, 4, '{}', '{}'),
(4140, '2026-05-13 01:32:49', 'CONSULTAR', 1, 3, '{}', '{}'),
(4141, '2026-05-13 01:32:56', 'CONSULTAR', 1, 5, '{}', '{}'),
(4142, '2026-05-13 01:33:18', 'CONSULTAR', 1, 5, '{}', '{}'),
(4143, '2026-05-13 01:33:28', 'CONSULTAR', 1, 3, '{}', '{}'),
(4144, '2026-05-13 01:33:41', 'CONSULTAR', 1, 7, '{}', '{}'),
(4145, '2026-05-13 11:17:05', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4146, '2026-05-13 11:18:46', 'CONSULTAR', 1, 2, '{}', '{}'),
(4147, '2026-05-13 18:44:31', 'CONSULTAR', 1, 4, '{}', '{}'),
(4148, '2026-05-13 18:44:37', 'CONSULTAR', 1, 3, '{}', '{}'),
(4149, '2026-05-13 18:45:45', 'CONSULTAR', 1, 14, '{}', '{}'),
(4150, '2026-05-13 18:46:38', 'CONSULTAR', 1, 2, '{}', '{}'),
(4151, '2026-05-13 18:48:45', 'CONSULTAR', 1, 2, '{}', '{}'),
(4152, '2026-05-13 18:51:14', 'CONSULTAR', 1, 9, '{}', '{}'),
(4153, '2026-05-13 18:51:17', 'CONSULTAR', 1, 3, '{}', '{}'),
(4154, '2026-05-13 18:59:03', 'CONSULTAR', 1, 4, '{}', '{}'),
(4155, '2026-05-13 18:59:06', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4156, '2026-05-13 18:59:11', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4157, '2026-05-14 11:54:43', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4158, '2026-05-14 12:41:58', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4159, '2026-05-14 12:45:46', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4160, '2026-05-14 12:51:00', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4161, '2026-05-14 13:00:35', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4162, '2026-05-14 13:02:59', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4163, '2026-05-15 16:26:13', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4164, '2026-05-15 16:34:27', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4165, '2026-05-15 17:23:21', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4166, '2026-05-16 10:44:31', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4167, '2026-05-16 10:45:07', 'CONSULTAR', 1, 1, '{}', '{}'),
(4168, '2026-05-16 10:48:02', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PROCESADO\",\"observacion\":\"HOLA\"}'),
(4169, '2026-05-16 10:57:32', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PROCESADO\",\"observacion\":\"pago de febrero\"}'),
(4170, '2026-05-16 10:58:28', 'CONSULTAR', 1, 2, '{}', '{}'),
(4171, '2026-05-16 11:01:07', 'REGISTRAR', 1, 2, '{}', '{\"clasificacion\":\"Fijo\",\"descripcion_gasto\":\"pago de descrio\"}'),
(4172, '2026-05-16 11:06:00', 'CONSULTAR', 1, 4, '{}', '{}'),
(4173, '2026-05-16 11:06:41', 'CONSULTAR', 1, 4, '{}', '{}'),
(4174, '2026-05-16 11:11:39', 'CONSULTAR', 1, 4, '{}', '{}'),
(4175, '2026-05-16 11:11:56', 'CONSULTAR', 1, 4, '{}', '{}'),
(4176, '2026-05-16 11:12:30', 'CONSULTAR', 1, 4, '{}', '{}'),
(4177, '2026-05-16 11:13:18', 'CONSULTAR', 1, 4, '{}', '{}'),
(4178, '2026-05-16 11:14:23', 'REGISTRAR', 1, 4, '{}', '{\"concepto\":\"compramos cafe\",\"monto_movimiento\":\"10\",\"fecha_movimiento\":\"2026-05-16\"}'),
(4179, '2026-05-16 11:16:34', 'CONSULTAR', 1, 4, '{}', '{}'),
(4180, '2026-05-16 11:16:40', 'MODIFICAR', 1, 4, '{}', '{\"monto_movimiento\":\"11\",\"fecha_movimiento\":\"2026-05-16\"}'),
(4181, '2026-05-16 11:20:35', 'MODIFICAR', 1, 4, '{}', '{\"monto_movimiento\":\"11.00\",\"fecha_movimiento\":\"2026-05-16\"}'),
(4182, '2026-05-16 12:05:13', 'CONSULTAR', 1, 3, '{}', '{}'),
(4183, '2026-05-16 12:05:57', 'MODIFICAR', 1, 3, '{\"tasa_dolar\":\"455.25\"}', '{\"tasa_dolar\":\"515.18\"}'),
(4184, '2026-05-16 12:06:17', 'CONSULTAR', 1, 4, '{}', '{}'),
(4185, '2026-05-16 12:06:34', 'CONSULTAR', 1, 5, '{}', '{}'),
(4186, '2026-05-16 12:10:46', 'CONSULTAR', 1, 7, '{}', '{}'),
(4187, '2026-05-16 12:12:00', 'MODIFICAR', 1, 7, '{\"monto_estimado\":12}', '{\"monto_estimado\":\"13\"}'),
(4188, '2026-05-16 12:12:03', 'CONSULTAR', 1, 8, '{}', '{}'),
(4189, '2026-05-16 12:12:13', 'MODIFICAR', 1, 8, '{}', '{\"tasa_dolar\":\"515.18\"}'),
(4190, '2026-05-16 12:12:19', 'CONSULTAR', 1, 9, '{}', '{}'),
(4191, '2026-05-16 12:12:31', 'REGISTRAR', 1, 9, '{}', '{\"fecha_inicio\":\"2026-05-16\",\"fecha_cierre\":\"2027-05-16\",\"estado\":\"ABIERTO\",\"descripcion\":\"ooo\"}'),
(4192, '2026-05-16 12:12:41', 'ELIMINAR', 1, 9, '{\"estado\":\"ABIERTO\",\"fecha_inicio\":\"2026-05-16\",\"fecha_cierre\":\"2027-05-16\",\"descripcion\":\"ooo\"}', '{}'),
(4193, '2026-05-16 12:12:46', 'CONSULTAR', 1, 15, '{}', '{}'),
(4194, '2026-05-16 12:12:56', 'CONSULTAR', 1, 15, '{}', '{}'),
(4195, '2026-05-16 12:20:48', 'CONSULTAR', 1, 15, '{}', '{}'),
(4196, '2026-05-16 12:24:33', 'CONSULTAR', 1, 15, '{}', '{}'),
(4197, '2026-05-16 12:25:32', 'CONSULTAR', 1, 15, '{}', '{}'),
(4198, '2026-05-16 12:25:50', 'CONSULTAR', 1, 15, '{}', '{}'),
(4199, '2026-05-16 12:29:02', 'CONSULTAR', 1, 15, '{}', '{}'),
(4200, '2026-05-16 12:29:12', 'DESCARGAR', 1, 15, '{}', '{\"tipo_reporte\":\"Constancia de Residencia\",\"habitante\":\"jesus asdasda\"}'),
(4201, '2026-05-16 12:29:27', 'CONSULTAR', 1, 15, '{}', '{}'),
(4202, '2026-05-16 12:31:08', 'CONSULTAR', 1, 15, '{}', '{}'),
(4203, '2026-05-16 12:34:11', 'CONSULTAR', 1, 15, '{}', '{}'),
(4204, '2026-05-16 12:58:30', 'CONSULTAR', 1, 15, '{}', '{}'),
(4205, '2026-05-16 13:01:42', 'CONSULTAR', 1, 17, '{}', '{}'),
(4206, '2026-05-16 13:01:50', 'CONSULTAR', 1, 19, '{}', '{}'),
(4207, '2026-05-16 13:02:00', 'RESPALDAR', 1, 19, '{}', '{\"accion\":\"Generó copia de seguridad\",\"base_datos\":\"NEGOCIO\"}'),
(4208, '2026-05-16 14:21:57', 'CONSULTAR', 1, 14, '{}', '{}'),
(4209, '2026-05-16 14:22:52', 'REGISTRAR', 1, 14, '{}', '{\"apellido\":\"test\",\"nombre\":\"test\",\"correo\":\"tests@gmail.com\",\"contra\":\"12345\"}'),
(4210, '2026-05-16 14:23:58', 'CONSULTAR', 1, 14, '{}', '{}'),
(4211, '2026-05-16 14:24:02', 'ELIMINAR', 1, 14, '{\"nombre\":\"test\",\"apellido\":\"test\",\"correo\":\"tests@gmail.com\",\"contrasenia\":\"$2y$10$apVDsr\\/j3IR3lOovWTAgje3Czb4e1mmPXBAedAzQnkvGD1PVkF9ia\",\"nombre_rol\":\"Contador\"}', '{}'),
(4212, '2026-05-16 14:27:23', 'CONSULTAR', 1, 17, '{}', '{}'),
(4213, '2026-05-16 14:28:03', 'CONSULTAR', 1, 17, '{}', '{}'),
(4214, '2026-05-16 14:28:32', 'CONSULTAR', 1, 23, '{}', '{}'),
(4215, '2026-05-16 14:28:36', 'CONSULTAR', 1, 22, '{}', '{}'),
(4216, '2026-05-16 14:28:43', 'CONSULTAR', 1, 12, '{}', '{}'),
(4217, '2026-05-16 14:28:49', 'CONSULTAR', 1, 11, '{}', '{}'),
(4218, '2026-05-16 14:28:52', 'CONSULTAR', 1, 13, '{}', '{}'),
(4219, '2026-05-16 18:51:39', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4220, '2026-05-16 18:53:33', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4221, '2026-05-16 19:02:37', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4222, '2026-05-16 19:21:37', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4223, '2026-05-16 19:21:45', 'CONSULTAR', 1, 1, '{}', '{}'),
(4224, '2026-05-16 19:43:58', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4225, '2026-05-16 22:26:00', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4226, '2026-05-16 22:34:19', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4227, '2026-05-16 22:35:38', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4228, '2026-05-16 22:36:26', 'CONSULTAR', 1, 5, '{}', '{}'),
(4229, '2026-05-17 19:12:14', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4230, '2026-05-17 19:12:22', 'CONSULTAR', 1, 1, '{}', '{}'),
(4231, '2026-05-17 19:12:40', 'CONSULTAR', 1, 5, '{}', '{}'),
(4232, '2026-05-17 19:14:38', 'CONSULTAR', 1, 8, '{}', '{}'),
(4233, '2026-05-17 19:15:09', 'REGISTRAR', 1, 8, '{}', '{\"fecha\":\"2026-04-01\",\"cuota_reserva\":\"10\",\"observacion\":\"presupuesto abril\",\"tasa_dolar\":\"515.18\"}'),
(4234, '2026-05-17 19:15:15', 'CONSULTAR', 1, 3, '{}', '{}'),
(4235, '2026-05-18 17:18:53', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4236, '2026-05-18 17:25:59', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4237, '2026-05-18 17:59:53', 'CONSULTAR', 1, 1, '{}', '{}'),
(4238, '2026-05-18 18:00:53', 'CONSULTAR', 1, 4, '{}', '{}'),
(4239, '2026-05-18 18:00:59', 'CONSULTAR', 1, 3, '{}', '{}'),
(4240, '2026-05-18 18:01:10', 'CONSULTAR', 1, 5, '{}', '{}'),
(4241, '2026-05-18 18:01:30', 'CONSULTAR', 1, 12, '{}', '{}'),
(4242, '2026-05-18 19:48:17', 'CONSULTAR', 1, 11, '{}', '{}'),
(4243, '2026-05-18 19:50:22', 'CONSULTAR', 1, 19, '{}', '{}'),
(4244, '2026-05-18 19:50:50', 'CONSULTAR', 1, 14, '{}', '{}'),
(4245, '2026-05-18 19:51:01', 'CONSULTAR', 1, 17, '{}', '{}'),
(4246, '2026-05-18 19:54:30', 'CONSULTAR', 1, 17, '{}', '{}'),
(4247, '2026-05-18 19:54:54', 'CONSULTAR', 1, 17, '{}', '{}'),
(4248, '2026-05-18 20:22:35', 'CONSULTAR', 1, 22, '{}', '{}'),
(4249, '2026-05-18 20:53:41', 'CONSULTAR', 1, 22, '{}', '{}'),
(4250, '2026-05-18 20:53:47', 'CONSULTAR', 1, 22, '{}', '{}'),
(4251, '2026-05-18 20:54:20', 'CONSULTAR', 1, 22, '{}', '{}'),
(4252, '2026-05-18 20:54:36', 'CONSULTAR', 1, 14, '{}', '{}'),
(4253, '2026-05-18 21:54:44', 'CONSULTAR', 1, 12, '{}', '{}'),
(4254, '2026-05-18 22:20:07', 'CONSULTAR', 1, 3, '{}', '{}'),
(4255, '2026-05-18 22:20:20', 'REGISTRAR', 1, 3, '{}', '{\"tasa_dolar\":\"515.18\",\"mes\":\"1\",\"anio\":\"2026\",\"porcentaje_interes\":\"10\",\"limite_mensualidad\":\"15\"}'),
(4256, '2026-05-18 22:21:02', 'ELIMINAR', 1, 3, '{\"tasa_dolar\":\"515.18\",\"mes\":\"1\",\"anio\":\"2026\",\"porcentaje_interes\":10,\"limite_mensualidad\":15}', '{}'),
(4257, '2026-05-18 22:21:23', 'REGISTRAR', 1, 3, '{}', '{\"tasa_dolar\":\"515.18\",\"mes\":\"1\",\"anio\":\"2026\",\"porcentaje_interes\":\"10\",\"limite_mensualidad\":\"15\"}'),
(4258, '2026-05-18 22:44:01', 'CONSULTAR', 1, 1, '{}', '{}'),
(4259, '2026-05-18 22:45:59', 'CONSULTAR', 1, 1, '{}', '{}'),
(4260, '2026-05-18 22:47:54', 'CONSULTAR', 1, 1, '{}', '{}'),
(4261, '2026-05-18 22:52:25', 'CONSULTAR', 1, 1, '{}', '{}'),
(4262, '2026-05-18 22:52:45', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PROCESADO\",\"observacion\":\"pago\"}'),
(4263, '2026-05-18 22:54:32', 'CONSULTAR', 1, 1, '{}', '{}'),
(4264, '2026-05-18 22:54:54', 'CONSULTAR', 1, 3, '{}', '{}'),
(4265, '2026-05-18 22:55:40', 'CONSULTAR', 1, 1, '{}', '{}'),
(4266, '2026-05-18 23:01:52', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PENDIENTE\",\"observacion\":\"pago\"}'),
(4267, '2026-05-18 23:02:07', 'MODIFICAR', 1, 1, '{\"estado\":\"PENDIENTE\"}', '{\"estado\":\"PROCESADO\"}'),
(4268, '2026-05-18 23:02:16', 'CONSULTAR', 1, 3, '{}', '{}'),
(4269, '2026-05-18 23:02:40', 'CONSULTAR', 1, 2, '{}', '{}'),
(4270, '2026-05-18 23:02:43', 'CONSULTAR', 1, 1, '{}', '{}'),
(4271, '2026-05-18 23:09:38', 'CONSULTAR', 1, 3, '{}', '{}'),
(4272, '2026-05-18 23:09:43', 'CONSULTAR', 1, 8, '{}', '{}'),
(4273, '2026-05-18 23:10:19', 'REGISTRAR', 1, 8, '{}', '{\"fecha\":\"2026-05-01\",\"cuota_reserva\":\"10\",\"observacion\":\"presupuesto mayo\",\"tasa_dolar\":\"515.18\"}'),
(4274, '2026-05-18 23:10:25', 'CONSULTAR', 1, 3, '{}', '{}'),
(4275, '2026-05-18 23:10:52', 'CONSULTAR', 1, 8, '{}', '{}'),
(4276, '2026-05-18 23:11:18', 'MODIFICAR', 1, 8, '{}', '{\"tasa_dolar\":\"515.18\"}'),
(4277, '2026-05-18 23:11:24', 'CONSULTAR', 1, 3, '{}', '{}'),
(4278, '2026-05-18 23:12:02', 'CONSULTAR', 1, 8, '{}', '{}'),
(4279, '2026-05-19 00:04:27', 'CONSULTAR', 1, 1, '{}', '{}'),
(4280, '2026-05-19 12:39:47', 'CONSULTAR', 1, 2, '{}', '{}'),
(4281, '2026-05-19 12:40:11', 'CONSULTAR', 1, 14, '{}', '{}'),
(4282, '2026-05-19 12:40:15', 'CONSULTAR', 1, 14, '{}', '{}'),
(4283, '2026-05-19 12:40:36', 'CONSULTAR', 1, 2, '{}', '{}'),
(4284, '2026-05-19 12:40:38', 'CONSULTAR', 1, 4, '{}', '{}'),
(4285, '2026-05-19 12:41:06', 'CONSULTAR', 1, 1, '{}', '{}'),
(4286, '2026-05-19 12:41:11', 'CONSULTAR', 1, 1, '{}', '{}'),
(4287, '2026-05-19 12:41:19', 'CONSULTAR', 1, 1, '{}', '{}'),
(4288, '2026-05-19 12:46:29', 'CONSULTAR', 1, 1, '{}', '{}'),
(4289, '2026-05-19 12:48:36', 'CONSULTAR', 1, 1, '{}', '{}'),
(4290, '2026-05-19 12:49:01', 'CONSULTAR', 1, 1, '{}', '{}'),
(4291, '2026-05-19 12:51:28', 'CONSULTAR', 1, 1, '{}', '{}'),
(4292, '2026-05-19 12:51:53', 'CONSULTAR', 1, 1, '{}', '{}'),
(4293, '2026-05-19 12:53:09', 'CONSULTAR', 1, 1, '{}', '{}'),
(4294, '2026-05-19 12:54:28', 'CONSULTAR', 1, 1, '{}', '{}'),
(4295, '2026-05-19 12:54:40', 'CONSULTAR', 1, 1, '{}', '{}'),
(4296, '2026-05-19 12:54:55', 'CONSULTAR', 1, 1, '{}', '{}'),
(4297, '2026-05-19 12:55:31', 'CONSULTAR', 1, 1, '{}', '{}'),
(4298, '2026-05-19 12:55:37', 'CONSULTAR', 1, 1, '{}', '{}'),
(4299, '2026-05-19 12:55:46', 'CONSULTAR', 1, 1, '{}', '{}'),
(4300, '2026-05-19 12:55:48', 'CONSULTAR', 1, 1, '{}', '{}'),
(4301, '2026-05-19 12:55:56', 'CONSULTAR', 1, 1, '{}', '{}'),
(4302, '2026-05-19 12:57:42', 'CONSULTAR', 1, 1, '{}', '{}'),
(4303, '2026-05-19 12:57:47', 'CONSULTAR', 1, 2, '{}', '{}'),
(4304, '2026-05-19 12:58:02', 'CONSULTAR', 1, 2, '{}', '{}'),
(4305, '2026-05-19 12:58:11', 'CONSULTAR', 1, 2, '{}', '{}'),
(4306, '2026-05-19 13:00:42', 'CONSULTAR', 1, 2, '{}', '{}'),
(4307, '2026-05-19 13:00:51', 'CONSULTAR', 1, 2, '{}', '{}'),
(4308, '2026-05-19 13:01:05', 'CONSULTAR', 1, 2, '{}', '{}'),
(4309, '2026-05-19 13:01:19', 'CONSULTAR', 1, 2, '{}', '{}'),
(4310, '2026-05-19 13:05:22', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4311, '2026-05-19 13:06:49', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4312, '2026-05-19 13:07:37', 'CONSULTAR', 1, 15, '{}', '{}'),
(4313, '2026-05-19 13:07:40', 'CONSULTAR', 1, 15, '{}', '{}'),
(4314, '2026-05-19 13:07:47', 'CONSULTAR', 1, 15, '{}', '{}'),
(4315, '2026-05-19 13:08:01', 'CONSULTAR', 1, 15, '{}', '{}'),
(4316, '2026-05-19 13:08:06', 'CONSULTAR', 1, 15, '{}', '{}'),
(4317, '2026-05-19 13:08:32', 'CONSULTAR', 1, 15, '{}', '{}'),
(4318, '2026-05-19 13:08:53', 'CONSULTAR', 1, 14, '{}', '{}'),
(4319, '2026-05-19 13:08:58', 'CONSULTAR', 1, 14, '{}', '{}'),
(4320, '2026-05-19 13:09:12', 'CONSULTAR', 1, 14, '{}', '{}'),
(4321, '2026-05-19 13:10:00', 'CONSULTAR', 1, 1, '{}', '{}'),
(4322, '2026-05-19 13:10:25', 'CONSULTAR', 1, 1, '{}', '{}'),
(4323, '2026-05-19 13:10:30', 'CONSULTAR', 1, 1, '{}', '{}'),
(4324, '2026-05-19 13:31:19', 'CONSULTAR', 1, 4, '{}', '{}'),
(4325, '2026-05-19 13:32:19', 'CONSULTAR', 1, 4, '{}', '{}'),
(4326, '2026-05-19 13:43:14', 'CONSULTAR', 1, 4, '{}', '{}'),
(4327, '2026-05-19 13:43:44', 'CONSULTAR', 1, 2, '{}', '{}'),
(4328, '2026-05-19 13:48:04', 'CONSULTAR', 1, 2, '{}', '{}'),
(4329, '2026-05-19 13:49:40', 'CONSULTAR', 1, 2, '{}', '{}'),
(4330, '2026-05-19 13:49:58', 'CONSULTAR', 1, 2, '{}', '{}'),
(4331, '2026-05-19 13:53:01', 'CONSULTAR', 1, 3, '{}', '{}'),
(4332, '2026-05-19 13:53:15', 'CONSULTAR', 1, 5, '{}', '{}'),
(4333, '2026-05-19 13:53:18', 'CONSULTAR', 1, 3, '{}', '{}'),
(4334, '2026-05-19 13:53:24', 'CONSULTAR', 1, 2, '{}', '{}'),
(4335, '2026-05-19 13:53:30', 'CONSULTAR', 1, 1, '{}', '{}'),
(4336, '2026-05-19 13:53:35', 'CONSULTAR', 1, 2, '{}', '{}'),
(4337, '2026-05-19 17:12:27', 'CONSULTAR', 1, 1, '{}', '{}'),
(4338, '2026-05-19 17:14:23', 'CONSULTAR', 1, 19, '{}', '{}'),
(4339, '2026-05-19 17:20:51', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4340, '2026-05-19 17:23:53', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4341, '2026-05-19 17:35:23', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4342, '2026-05-19 17:37:23', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4343, '2026-05-19 17:57:31', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4344, '2026-05-19 18:05:28', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4345, '2026-05-19 18:24:43', 'INICIAR SESION', 39, 14, '{}', '{}'),
(4346, '2026-05-19 19:06:49', 'CONSULTAR', 1, 19, '{}', '{}'),
(4347, '2026-05-19 19:23:23', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4348, '2026-05-19 19:31:06', 'INICIAR SESION', 91, 14, '{}', '{}'),
(4349, '2026-05-19 19:53:09', 'INICIAR SESION', 39, 14, '{}', '{}'),
(4350, '2026-05-19 19:57:36', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4351, '2026-05-19 20:10:51', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4352, '2026-05-19 20:22:41', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4353, '2026-05-19 20:25:34', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4354, '2026-05-19 23:45:01', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4355, '2026-05-19 23:52:41', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4356, '2026-05-20 00:04:43', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4357, '2026-05-20 00:05:07', 'CONSULTAR', 1, 4, '{}', '{}'),
(4358, '2026-05-20 00:13:49', 'CONSULTAR', 1, 4, '{}', '{}'),
(4359, '2026-05-20 00:14:04', 'CONSULTAR', 1, 4, '{}', '{}'),
(4360, '2026-05-20 00:14:08', 'CONSULTAR', 1, 4, '{}', '{}'),
(4361, '2026-05-20 00:14:28', 'CONSULTAR', 1, 4, '{}', '{}'),
(4362, '2026-05-20 00:14:33', 'CONSULTAR', 1, 4, '{}', '{}'),
(4363, '2026-05-20 00:16:55', 'CONSULTAR', 1, 4, '{}', '{}'),
(4364, '2026-05-20 00:17:05', 'CONSULTAR', 1, 4, '{}', '{}'),
(4365, '2026-05-20 00:21:45', 'CONSULTAR', 1, 4, '{}', '{}'),
(4366, '2026-05-20 00:22:20', 'CONSULTAR', 1, 4, '{}', '{}'),
(4367, '2026-05-20 00:22:24', 'CONSULTAR', 1, 4, '{}', '{}'),
(4368, '2026-05-20 00:22:42', 'CONSULTAR', 1, 4, '{}', '{}'),
(4369, '2026-05-20 00:23:05', 'CONSULTAR', 1, 4, '{}', '{}'),
(4370, '2026-05-20 13:18:12', 'CONSULTAR', 1, 1, '{}', '{}'),
(4371, '2026-05-20 13:29:06', 'CONSULTAR', 1, 1, '{}', '{}'),
(4372, '2026-05-20 13:30:31', 'CONSULTAR', 1, 1, '{}', '{}'),
(4373, '2026-05-20 13:39:49', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PENDIENTE\",\"observacion\":\"pago\",\"tasa_dolar\":[\"520.91\"]}'),
(4374, '2026-05-20 13:39:59', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PENDIENTE\",\"observacion\":\"pago\",\"tasa_dolar\":[\"520.91\"]}'),
(4375, '2026-05-20 13:40:42', 'CONSULTAR', 1, 1, '{}', '{}'),
(4376, '2026-05-20 13:41:41', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"RECHAZADO\",\"observacion\":\"pago\",\"tasa_dolar\":\"520.91\"}'),
(4377, '2026-05-20 13:48:00', 'CONSULTAR', 1, 1, '{}', '{}'),
(4378, '2026-05-20 13:52:52', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PROCESADO\",\"observacion\":\"pago de mayo\",\"tasa_dolar\":\"520.90\"}'),
(4379, '2026-05-20 14:09:08', 'CONSULTAR', 1, 2, '{}', '{}'),
(4380, '2026-05-20 14:09:10', 'CONSULTAR', 1, 2, '{}', '{}'),
(4381, '2026-05-20 14:12:27', 'CONSULTAR', 1, 2, '{}', '{}'),
(4382, '2026-05-20 14:13:36', 'CONSULTAR', 1, 2, '{}', '{}'),
(4383, '2026-05-21 00:01:29', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4384, '2026-05-21 00:01:36', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4385, '2026-05-21 00:02:02', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4386, '2026-05-21 01:18:36', 'CONSULTAR', 1, 4, '{}', '{}'),
(4387, '2026-05-21 01:18:39', 'CONSULTAR', 1, 2, '{}', '{}'),
(4388, '2026-05-21 01:18:51', 'CONSULTAR', 1, 1, '{}', '{}'),
(4389, '2026-05-21 01:19:09', 'CONSULTAR', 1, 5, '{}', '{}'),
(4390, '2026-05-21 01:19:21', 'CONSULTAR', 1, 15, '{}', '{}'),
(4391, '2026-05-21 08:46:59', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4392, '2026-05-21 08:57:39', 'CONSULTAR', 1, 1, '{}', '{}'),
(4393, '2026-05-21 09:05:36', 'CONSULTAR', 1, 2, '{}', '{}'),
(4394, '2026-05-21 09:05:57', 'CONSULTAR', 1, 19, '{}', '{}'),
(4395, '2026-05-21 09:06:16', 'CONSULTAR', 1, 19, '{}', '{}'),
(4396, '2026-05-21 09:06:40', 'CONSULTAR', 1, 2, '{}', '{}'),
(4397, '2026-05-21 15:19:17', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4398, '2026-05-21 15:19:58', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4399, '2026-05-21 15:20:10', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4400, '2026-05-21 15:22:58', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4401, '2026-05-21 15:23:11', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4402, '2026-05-21 16:20:13', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4403, '2026-05-21 16:27:13', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4404, '2026-05-21 16:32:19', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4405, '2026-05-21 16:32:33', 'CONSULTAR', 1, 1, '{}', '{}'),
(4406, '2026-05-21 16:32:38', 'CONSULTAR', 1, 2, '{}', '{}'),
(4407, '2026-05-21 16:33:17', 'REGISTRAR', 1, 2, '{}', '{\"clasificacion\":\"FIJO\",\"descripcion_gasto\":\"sssssssssss\",\"tasa_dolar\":\"523.67\"}'),
(4408, '2026-05-21 16:34:26', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4409, '2026-05-22 19:41:41', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4410, '2026-05-22 20:06:28', 'INICIAR SESION', 39, 14, '{}', '{}'),
(4411, '2026-05-22 20:11:21', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4412, '2026-05-22 20:15:16', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4413, '2026-05-22 20:18:14', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4414, '2026-05-22 20:23:47', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4415, '2026-05-22 20:34:43', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4416, '2026-05-22 22:16:27', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4417, '2026-05-22 22:24:00', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4418, '2026-05-22 22:24:10', 'CONSULTAR', 1, 2, '{}', '{}'),
(4419, '2026-05-22 22:32:36', 'CONSULTAR', 1, 2, '{}', '{}'),
(4420, '2026-05-22 22:40:21', 'CONSULTAR', 1, 2, '{}', '{}'),
(4421, '2026-05-22 22:41:53', 'CONSULTAR', 1, 2, '{}', '{}'),
(4422, '2026-05-22 22:50:07', 'CONSULTAR', 1, 2, '{}', '{}'),
(4423, '2026-05-22 22:50:11', 'CONSULTAR', 1, 2, '{}', '{}'),
(4424, '2026-05-22 22:50:28', 'CONSULTAR', 1, 3, '{}', '{}'),
(4425, '2026-05-22 23:12:20', 'CONSULTAR', 1, 3, '{}', '{}'),
(4426, '2026-05-22 23:23:40', 'CONSULTAR', 1, 19, '{}', '{}'),
(4427, '2026-05-22 23:24:36', 'CONSULTAR', 1, 1, '{}', '{}'),
(4428, '2026-05-22 23:24:45', 'CONSULTAR', 1, 2, '{}', '{}'),
(4429, '2026-05-22 23:25:25', 'CONSULTAR', 1, 8, '{}', '{}'),
(4430, '2026-05-22 23:25:35', 'CONSULTAR', 1, 13, '{}', '{}'),
(4431, '2026-05-22 23:25:42', 'CONSULTAR', 1, 8, '{}', '{}'),
(4432, '2026-05-22 23:25:45', 'CONSULTAR', 1, 8, '{}', '{}'),
(4433, '2026-05-22 23:25:57', 'CONSULTAR', 1, 8, '{}', '{}'),
(4434, '2026-05-22 23:26:50', 'CONSULTAR', 1, 8, '{}', '{}'),
(4435, '2026-05-22 23:28:51', 'CONSULTAR', 1, 8, '{}', '{}'),
(4436, '2026-05-22 23:30:06', 'CONSULTAR', 1, 8, '{}', '{}'),
(4437, '2026-05-22 23:32:05', 'CONSULTAR', 1, 8, '{}', '{}'),
(4438, '2026-05-22 23:32:42', 'ELIMINAR', 1, 8, '{\"fecha\":\"2026-02-01\",\"cuota_reserva\":230,\"observacion\":\"febrero 2026 editado\"}', '{}'),
(4439, '2026-05-22 23:32:47', 'CONSULTAR', 1, 8, '{}', '{}'),
(4440, '2026-05-24 16:49:19', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4441, '2026-05-24 23:31:26', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4442, '2026-05-24 23:31:32', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4443, '2026-05-25 00:00:24', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4444, '2026-05-25 18:31:41', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4445, '2026-05-25 18:33:12', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4446, '2026-05-25 18:39:34', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4447, '2026-05-25 18:45:05', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4448, '2026-05-25 18:51:33', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4449, '2026-05-25 20:52:42', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4450, '2026-05-25 21:39:17', 'CONSULTAR', 1, 14, '{}', '{}'),
(4451, '2026-05-25 21:44:51', 'CONSULTAR', 1, 7, '{}', '{}'),
(4452, '2026-05-25 21:45:27', 'CONSULTAR', 1, 5, '{}', '{}'),
(4453, '2026-05-25 21:46:44', 'CONSULTAR', 1, 1, '{}', '{}'),
(4454, '2026-05-25 21:47:17', 'CONSULTAR', 1, 2, '{}', '{}'),
(4455, '2026-05-25 21:47:24', 'CONSULTAR', 1, 4, '{}', '{}'),
(4456, '2026-05-25 21:47:40', 'CONSULTAR', 1, 3, '{}', '{}'),
(4457, '2026-05-25 21:47:53', 'CONSULTAR', 1, 5, '{}', '{}'),
(4458, '2026-05-25 21:48:07', 'CONSULTAR', 1, 7, '{}', '{}'),
(4459, '2026-05-25 21:48:13', 'CONSULTAR', 1, 8, '{}', '{}'),
(4460, '2026-05-25 21:48:24', 'CONSULTAR', 1, 9, '{}', '{}'),
(4461, '2026-05-25 21:48:58', 'CONSULTAR', 1, 2, '{}', '{}'),
(4462, '2026-05-25 21:54:11', 'CONSULTAR', 1, 7, '{}', '{}'),
(4463, '2026-05-25 21:54:51', 'CONSULTAR', 1, 8, '{}', '{}'),
(4464, '2026-05-25 21:54:59', 'CONSULTAR', 1, 9, '{}', '{}'),
(4465, '2026-05-25 21:55:11', 'CONSULTAR', 1, 14, '{}', '{}'),
(4466, '2026-05-25 21:55:19', 'CONSULTAR', 1, 17, '{}', '{}'),
(4467, '2026-05-25 21:56:54', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4468, '2026-05-25 21:58:01', 'CONSULTAR', 1, 4, '{}', '{}'),
(4469, '2026-05-25 21:58:37', 'CONSULTAR', 1, 14, '{}', '{}'),
(4470, '2026-05-25 21:58:44', 'CONSULTAR', 1, 19, '{}', '{}'),
(4471, '2026-05-25 22:00:16', 'CONSULTAR', 1, 1, '{}', '{}'),
(4472, '2026-05-25 22:00:39', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4473, '2026-05-25 22:40:09', 'CONSULTAR', 1, 1, '{}', '{}'),
(4474, '2026-05-26 10:07:42', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4475, '2026-05-26 14:27:19', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4476, '2026-05-26 14:34:36', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4477, '2026-05-26 14:54:39', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4478, '2026-05-27 18:19:28', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4479, '2026-05-27 18:47:01', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4480, '2026-05-27 19:07:48', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4481, '2026-05-27 19:09:30', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4482, '2026-05-27 19:24:42', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4483, '2026-05-27 19:35:01', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4484, '2026-05-27 19:40:44', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4485, '2026-05-27 19:41:14', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4486, '2026-05-27 19:41:48', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4487, '2026-05-27 19:44:15', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4488, '2026-05-27 19:49:30', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4489, '2026-05-27 20:05:35', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4490, '2026-05-27 20:08:05', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4491, '2026-05-27 20:08:42', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4492, '2026-05-27 20:10:29', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4493, '2026-05-27 23:08:05', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4494, '2026-05-27 23:22:16', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4495, '2026-05-27 23:29:04', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4496, '2026-05-27 23:34:30', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4497, '2026-05-28 00:15:45', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4498, '2026-05-28 00:22:47', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4499, '2026-05-28 01:36:35', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4500, '2026-05-28 01:43:16', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4501, '2026-05-28 01:43:27', 'CONSULTAR', 1, 1, '{}', '{}'),
(4502, '2026-05-28 01:50:07', 'CONSULTAR', 1, 8, '{}', '{}'),
(4503, '2026-05-28 08:40:50', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4504, '2026-05-28 09:06:41', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4505, '2026-05-28 09:09:37', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4506, '2026-05-28 09:21:05', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4507, '2026-05-28 09:27:55', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4508, '2026-05-28 09:28:08', 'CONSULTAR', 1, 14, '{}', '{}'),
(4509, '2026-05-28 09:34:08', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4510, '2026-05-28 09:44:30', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4511, '2026-05-28 10:26:29', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4512, '2026-05-28 10:42:42', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4513, '2026-05-28 10:49:10', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4514, '2026-05-28 11:02:02', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4515, '2026-05-28 11:06:35', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4516, '2026-05-28 11:11:46', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4517, '2026-05-28 11:24:51', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4518, '2026-05-28 11:27:44', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4519, '2026-05-28 11:57:05', 'CONSULTAR', 1, 1, '{}', '{}'),
(4520, '2026-05-28 17:27:51', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4521, '2026-05-28 17:28:31', 'CONSULTAR', 1, 1, '{}', '{}'),
(4522, '2026-05-28 17:29:17', 'CONSULTAR', 1, 2, '{}', '{}'),
(4523, '2026-05-28 17:29:32', 'CONSULTAR', 1, 1, '{}', '{}'),
(4524, '2026-05-28 17:29:47', 'CONSULTAR', 1, 4, '{}', '{}'),
(4525, '2026-05-28 17:31:28', 'CONSULTAR', 1, 1, '{}', '{}'),
(4526, '2026-05-28 17:38:13', 'CONSULTAR', 1, 1, '{}', '{}'),
(4527, '2026-05-28 17:41:16', 'CONSULTAR', 1, 1, '{}', '{}'),
(4528, '2026-05-28 17:42:35', 'CONSULTAR', 1, 1, '{}', '{}'),
(4529, '2026-05-28 17:44:44', 'CONSULTAR', 1, 1, '{}', '{}'),
(4530, '2026-05-28 17:45:18', 'CONSULTAR', 1, 1, '{}', '{}'),
(4531, '2026-05-28 17:46:44', 'CONSULTAR', 1, 1, '{}', '{}'),
(4532, '2026-05-28 17:56:40', 'CONSULTAR', 1, 1, '{}', '{}'),
(4533, '2026-05-28 17:57:30', 'CONSULTAR', 1, 1, '{}', '{}'),
(4534, '2026-05-28 17:57:55', 'CONSULTAR', 1, 1, '{}', '{}'),
(4535, '2026-05-28 17:59:09', 'CONSULTAR', 1, 1, '{}', '{}'),
(4536, '2026-05-28 17:59:24', 'CONSULTAR', 1, 1, '{}', '{}'),
(4537, '2026-05-28 17:59:43', 'CONSULTAR', 1, 1, '{}', '{}'),
(4538, '2026-05-28 18:00:17', 'CONSULTAR', 1, 1, '{}', '{}'),
(4539, '2026-05-28 18:01:58', 'CONSULTAR', 1, 1, '{}', '{}'),
(4540, '2026-05-28 18:02:06', 'CONSULTAR', 1, 2, '{}', '{}'),
(4541, '2026-05-28 18:03:01', 'CONSULTAR', 1, 14, '{}', '{}'),
(4542, '2026-05-28 18:03:25', 'CONSULTAR', 1, 14, '{}', '{}'),
(4543, '2026-05-28 18:06:33', 'CONSULTAR', 1, 5, '{}', '{}'),
(4544, '2026-05-28 18:08:17', 'CONSULTAR', 1, 1, '{}', '{}'),
(4545, '2026-05-28 18:21:29', 'CONSULTAR', 1, 14, '{}', '{}'),
(4546, '2026-05-28 18:22:55', 'CONSULTAR', 1, 14, '{}', '{}'),
(4547, '2026-05-28 18:24:47', 'CONSULTAR', 1, 14, '{}', '{}'),
(4548, '2026-05-28 18:25:30', 'CONSULTAR', 1, 14, '{}', '{}'),
(4549, '2026-05-28 18:25:46', 'CONSULTAR', 1, 9, '{}', '{}'),
(4550, '2026-05-28 18:33:00', 'CONSULTAR', 1, 9, '{}', '{}'),
(4551, '2026-05-28 18:35:16', 'CONSULTAR', 1, 14, '{}', '{}'),
(4552, '2026-05-28 18:35:41', 'CONSULTAR', 1, 14, '{}', '{}'),
(4553, '2026-05-28 18:36:08', 'CONSULTAR', 1, 14, '{}', '{}'),
(4554, '2026-05-28 18:45:01', 'CONSULTAR', 1, 14, '{}', '{}'),
(4555, '2026-05-28 18:45:36', 'CONSULTAR', 1, 14, '{}', '{}'),
(4556, '2026-05-28 18:47:22', 'CONSULTAR', 1, 14, '{}', '{}'),
(4557, '2026-05-28 18:50:19', 'CONSULTAR', 1, 14, '{}', '{}'),
(4558, '2026-05-28 18:50:33', 'CONSULTAR', 1, 17, '{}', '{}'),
(4559, '2026-05-28 18:51:10', 'CONSULTAR', 1, 4, '{}', '{}'),
(4560, '2026-05-28 18:51:31', 'CONSULTAR', 1, 4, '{}', '{}'),
(4561, '2026-05-28 18:57:12', 'CONSULTAR', 1, 4, '{}', '{}'),
(4562, '2026-05-28 18:59:25', 'CONSULTAR', 1, 4, '{}', '{}'),
(4563, '2026-05-28 19:02:41', 'CONSULTAR', 1, 4, '{}', '{}'),
(4564, '2026-05-28 19:03:10', 'CONSULTAR', 1, 4, '{}', '{}'),
(4565, '2026-05-28 19:03:40', 'CONSULTAR', 1, 5, '{}', '{}'),
(4566, '2026-05-28 19:05:44', 'CONSULTAR', 1, 5, '{}', '{}'),
(4567, '2026-05-28 19:06:26', 'CONSULTAR', 1, 5, '{}', '{}'),
(4568, '2026-05-28 19:07:10', 'CONSULTAR', 1, 19, '{}', '{}'),
(4569, '2026-05-28 19:10:00', 'CONSULTAR', 1, 15, '{}', '{}'),
(4570, '2026-05-28 19:10:15', 'CONSULTAR', 1, 15, '{}', '{}'),
(4571, '2026-05-28 19:10:38', 'CONSULTAR', 1, 19, '{}', '{}'),
(4572, '2026-05-28 19:13:44', 'CONSULTAR', 1, 19, '{}', '{}'),
(4573, '2026-05-28 19:14:12', 'CONSULTAR', 1, 15, '{}', '{}'),
(4574, '2026-05-28 19:15:25', 'CONSULTAR', 1, 15, '{}', '{}'),
(4575, '2026-05-28 19:16:16', 'CONSULTAR', 1, 15, '{}', '{}'),
(4576, '2026-05-28 19:17:04', 'CONSULTAR', 1, 15, '{}', '{}'),
(4577, '2026-05-28 19:19:32', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4578, '2026-05-28 19:20:00', 'CONSULTAR', 1, 1, '{}', '{}'),
(4579, '2026-05-28 19:20:44', 'CONSULTAR', 1, 2, '{}', '{}'),
(4580, '2026-05-28 19:20:54', 'CONSULTAR', 1, 15, '{}', '{}'),
(4581, '2026-05-28 19:21:08', 'CONSULTAR', 1, 17, '{}', '{}'),
(4582, '2026-05-28 19:22:03', 'CONSULTAR', 1, 19, '{}', '{}'),
(4583, '2026-05-28 19:22:26', 'CONSULTAR', 1, 4, '{}', '{}'),
(4584, '2026-05-28 19:22:33', 'CONSULTAR', 1, 4, '{}', '{}'),
(4585, '2026-05-28 19:23:10', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4586, '2026-05-29 16:42:37', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4587, '2026-05-30 09:42:25', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4588, '2026-05-30 09:43:19', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4589, '2026-05-30 09:44:51', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4590, '2026-05-30 09:47:39', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4591, '2026-05-30 09:48:04', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4592, '2026-05-30 09:53:19', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4593, '2026-05-30 09:57:20', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4594, '2026-05-30 09:59:18', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4595, '2026-05-30 10:00:21', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4596, '2026-05-30 10:03:54', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4597, '2026-05-30 10:04:32', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4598, '2026-05-30 10:05:32', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4599, '2026-05-30 10:08:41', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4600, '2026-05-30 10:10:52', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4601, '2026-05-30 10:10:59', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4602, '2026-05-30 10:12:02', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4603, '2026-05-30 10:13:21', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4604, '2026-05-30 10:14:04', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4605, '2026-05-30 10:15:05', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4606, '2026-05-30 10:16:48', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4607, '2026-05-30 10:18:10', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4608, '2026-05-30 10:20:27', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4609, '2026-05-30 10:29:14', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4610, '2026-05-30 10:31:11', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4611, '2026-05-30 10:31:50', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4612, '2026-05-30 10:35:50', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4613, '2026-05-30 10:37:54', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4614, '2026-05-30 10:38:45', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4615, '2026-05-30 10:49:20', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4616, '2026-05-30 10:50:10', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4617, '2026-05-30 11:08:53', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4618, '2026-05-30 11:11:10', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4619, '2026-05-30 11:12:11', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4620, '2026-05-30 11:18:47', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4621, '2026-05-30 11:20:48', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4622, '2026-05-30 12:15:15', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4623, '2026-05-30 15:17:06', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4624, '2026-05-31 17:09:15', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4625, '2026-05-31 17:09:47', 'CONSULTAR', 1, 1, '{}', '{}'),
(4626, '2026-05-31 17:42:19', 'CONSULTAR', 1, 1, '{}', '{}'),
(4627, '2026-05-31 17:54:15', 'CONSULTAR', 1, 1, '{}', '{}'),
(4628, '2026-05-31 18:02:54', 'CONSULTAR', 1, 1, '{}', '{}'),
(4629, '2026-05-31 18:03:01', 'CONSULTAR', 1, 1, '{}', '{}'),
(4630, '2026-05-31 18:05:59', 'CONSULTAR', 1, 1, '{}', '{}'),
(4631, '2026-05-31 18:06:20', 'CONSULTAR', 1, 1, '{}', '{}'),
(4632, '2026-05-31 18:07:05', 'CONSULTAR', 1, 1, '{}', '{}'),
(4633, '2026-06-01 22:36:54', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4634, '2026-06-01 22:37:02', 'CONSULTAR', 1, 19, '{}', '{}'),
(4635, '2026-06-01 22:37:05', 'CONSULTAR', 1, 19, '{}', '{}'),
(4636, '2026-06-01 22:37:37', 'CONSULTAR', 1, 19, '{}', '{}'),
(4637, '2026-06-01 22:39:28', 'RESPALDAR', 1, 19, '{}', '{\"accion\":\"Generó copia de seguridad\",\"base_datos\":\"SEGURIDAD\"}'),
(4638, '2026-06-01 23:00:07', 'CONSULTAR', 1, 19, '{}', '{}'),
(4639, '2026-06-01 23:00:32', 'CONSULTAR', 1, 19, '{}', '{}'),
(4640, '2026-06-01 23:01:18', 'RESPALDAR', 1, 19, '{}', '{\"accion\":\"Generó copia de seguridad\",\"base_datos\":\"NEGOCIO\"}'),
(4641, '2026-06-01 23:01:26', 'RESPALDAR', 1, 19, '{}', '{\"accion\":\"Generó copia de seguridad\",\"base_datos\":\"SEGURIDAD\"}'),
(4642, '2026-06-01 23:06:43', 'CONSULTAR', 1, 19, '{}', '{}'),
(4643, '2026-06-01 23:09:03', 'CONSULTAR', 1, 19, '{}', '{}'),
(4644, '2026-06-01 23:10:48', 'CONSULTAR', 1, 2, '{}', '{}'),
(4645, '2026-06-01 23:11:48', 'ELIMINAR', 1, 2, '{\"clasificacion\":\"FIJO\",\"descripcion_gasto\":\"Hola \"}', '{}'),
(4646, '2026-06-01 23:12:00', 'MODIFICAR', 1, 2, '{\"descripcion_gasto\":\"Tvybyb\"}', '{\"descripcion_gasto\":\"AAAAAAAAAAAAAAAA\",\"tasa_dolar\":\"1\"}'),
(4647, '2026-06-01 23:12:14', 'CONSULTAR', 1, 19, '{}', '{}'),
(4648, '2026-06-01 23:29:28', 'CONSULTAR', 1, 19, '{}', '{}'),
(4649, '2026-06-01 23:30:09', 'RESTAURAR', 1, 19, '{}', '{\"accion\":\"Restauró desde servidor\",\"base_datos\":\"NEGOCIO\",\"archivo\":\"backup_haydee_db_2026-06-02_05-08-54_AUTOMATICO.sql.gz\"}'),
(4650, '2026-06-01 23:30:17', 'CONSULTAR', 1, 2, '{}', '{}'),
(4651, '2026-06-01 23:31:27', 'ELIMINAR', 1, 2, '{\"clasificacion\":\"VARIABLE\",\"descripcion_gasto\":\"Tvybyb\"}', '{}'),
(4652, '2026-06-01 23:31:30', 'ELIMINAR', 1, 2, '{\"clasificacion\":\"VARIABLE\",\"descripcion_gasto\":\"Hola\"}', '{}'),
(4653, '2026-06-01 23:31:33', 'CONSULTAR', 1, 19, '{}', '{}'),
(4654, '2026-06-01 23:32:06', 'RESTAURAR', 1, 19, '{}', '{\"accion\":\"Restauró desde PC\",\"base_datos\":\"NEGOCIO\"}'),
(4655, '2026-06-01 23:33:01', 'CONSULTAR', 1, 2, '{}', '{}'),
(4656, '2026-06-01 23:51:24', 'CONSULTAR', 1, 19, '{}', '{}'),
(4657, '2026-06-02 00:07:08', 'CONSULTAR', 1, 5, '{}', '{}'),
(4658, '2026-06-02 00:07:10', 'CONSULTAR', 1, 2, '{}', '{}'),
(4659, '2026-06-02 00:07:31', 'MODIFICAR', 1, 2, '{\"descripcion_gasto\":\"Hola \"}', '{\"descripcion_gasto\":\"Holassssssss\",\"tasa_dolar\":\"1\"}'),
(4660, '2026-06-02 00:10:22', 'CONSULTAR', 1, 19, '{}', '{}'),
(4661, '2026-06-02 00:10:57', 'RESTAURAR', 1, 19, '{}', '{\"accion\":\"Restauró desde servidor\",\"base_datos\":\"NEGOCIO\",\"archivo\":\"backup_haydee_db_2026-06-02_05-08-54_AUTOMATICO.sql.gz\"}'),
(4662, '2026-06-02 00:11:00', 'CONSULTAR', 1, 2, '{}', '{}'),
(4663, '2026-06-02 00:14:11', 'CONSULTAR', 1, 2, '{}', '{}'),
(4664, '2026-06-02 00:24:22', 'CONSULTAR', 1, 9, '{}', '{}'),
(4665, '2026-06-02 00:24:37', 'REGISTRAR', 1, 9, '{}', '{\"fecha_inicio\":\"2026-06-02\",\"fecha_cierre\":\"2027-06-02\",\"estado\":\"ABIERTO\",\"descripcion\":\"año fiscal de preuba\"}'),
(4666, '2026-06-02 00:31:49', 'CONSULTAR', 1, 9, '{}', '{}'),
(4667, '2026-06-02 00:33:00', 'CONSULTAR', 1, 3, '{}', '{}'),
(4668, '2026-06-02 22:57:33', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4669, '2026-06-02 23:09:46', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4670, '2026-06-02 23:16:32', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4671, '2026-06-03 00:17:08', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4672, '2026-06-03 08:48:02', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4673, '2026-06-03 09:05:06', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4674, '2026-06-03 09:51:47', 'INICIAR SESION', 39, 14, '{}', '{}'),
(4675, '2026-06-03 09:51:58', 'CERRAR SESION', 39, 14, '{}', '{}'),
(4676, '2026-06-03 09:52:08', 'INICIAR SESION', 39, 14, '{}', '{}'),
(4677, '2026-06-03 10:11:12', 'CERRAR SESION', 39, 14, '{}', '{}'),
(4678, '2026-06-03 10:12:21', 'INICIAR SESION', 39, 14, '{}', '{}'),
(4679, '2026-06-03 10:20:11', 'INICIAR SESION', 39, 14, '{}', '{}'),
(4680, '2026-06-03 10:31:50', 'CERRAR SESION', 39, 14, '{}', '{}'),
(4681, '2026-06-03 10:46:44', 'INICIAR SESION', 39, 14, '{}', '{}'),
(4682, '2026-06-03 10:48:03', 'CERRAR SESION', 39, 14, '{}', '{}'),
(4683, '2026-06-03 10:48:15', 'INICIAR SESION', 27, 14, '{}', '{}'),
(4684, '2026-06-03 10:49:12', 'CONSULTAR', 39, 14, '{}', '{}'),
(4685, '2026-06-03 10:49:20', 'CONSULTAR', 39, 17, '{}', '{}'),
(4686, '2026-06-03 10:49:37', 'MODIFICAR', 39, 17, '{}', '{\"nombre\":\"Propietario\",\"permisos_asignados\":[{\"modulo_id\":1,\"permiso_id\":1},{\"modulo_id\":1,\"permiso_id\":2},{\"modulo_id\":1,\"permiso_id\":3},{\"modulo_id\":1,\"permiso_id\":4},{\"modulo_id\":5,\"permiso_id\":2}]}'),
(4687, '2026-06-03 10:49:42', 'CERRAR SESION', 27, 14, '{}', '{}'),
(4688, '2026-06-03 10:50:01', 'INICIAR SESION', 27, 14, '{}', '{}'),
(4689, '2026-06-03 10:59:38', 'CERRAR SESION', 39, 14, '{}', '{}'),
(4690, '2026-06-03 10:59:43', 'INICIAR SESION', 27, 14, '{}', '{}'),
(4691, '2026-06-03 10:59:47', 'CONSULTAR', 27, 1, '{}', '{}'),
(4692, '2026-06-03 11:00:06', 'CERRAR SESION', 27, 14, '{}', '{}'),
(4693, '2026-06-03 11:00:10', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4694, '2026-06-03 11:02:15', 'CERRAR SESION', 27, 14, '{}', '{}'),
(4695, '2026-06-03 11:02:30', 'INICIAR SESION', 27, 14, '{}', '{}'),
(4696, '2026-06-03 11:02:52', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4697, '2026-06-03 11:02:56', 'INICIAR SESION', 27, 14, '{}', '{}'),
(4698, '2026-06-03 11:03:00', 'CONSULTAR', 27, 1, '{}', '{}'),
(4699, '2026-06-03 11:03:31', 'CONSULTAR', 27, 1, '{}', '{}'),
(4700, '2026-06-03 11:25:38', 'CERRAR SESION', 27, 14, '{}', '{}'),
(4701, '2026-06-03 11:26:11', 'INICIAR SESION', 27, 14, '{}', '{}'),
(4702, '2026-06-03 11:26:16', 'INICIAR SESION', 27, 14, '{}', '{}'),
(4703, '2026-06-03 16:22:33', 'CERRAR SESION', 27, 14, '{}', '{}'),
(4704, '2026-06-03 16:22:44', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4705, '2026-06-03 16:23:19', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4706, '2026-06-03 16:24:25', 'INICIAR SESION', 27, 14, '{}', '{}'),
(4707, '2026-06-03 19:12:06', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4708, '2026-06-03 19:12:15', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4709, '2026-06-03 19:12:35', 'INICIAR SESION', 27, 14, '{}', '{}'),
(4710, '2026-06-03 20:23:28', 'CERRAR SESION', 27, 14, '{}', '{}'),
(4711, '2026-06-03 20:23:59', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4712, '2026-06-03 20:24:43', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4713, '2026-06-03 20:24:55', 'INICIAR SESION', 27, 14, '{}', '{}'),
(4714, '2026-06-03 20:32:44', 'CERRAR SESION', 27, 14, '{}', '{}'),
(4715, '2026-06-03 20:32:53', 'INICIAR SESION', 27, 14, '{}', '{}'),
(4716, '2026-06-03 20:42:39', 'CERRAR SESION', 27, 14, '{}', '{}'),
(4717, '2026-06-03 20:42:46', 'INICIAR SESION', 27, 14, '{}', '{}'),
(4718, '2026-06-04 08:32:42', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4719, '2026-06-04 08:32:50', 'CONSULTAR', 1, 19, '{}', '{}'),
(4720, '2026-06-04 08:33:18', 'CONSULTAR', 1, 19, '{}', '{}'),
(4721, '2026-06-04 08:33:35', 'CONSULTAR', 1, 19, '{}', '{}'),
(4722, '2026-06-04 08:34:20', 'CONSULTAR', 1, 19, '{}', '{}'),
(4723, '2026-06-04 08:35:32', 'RESPALDAR', 1, 19, '{}', '{\"accion\":\"Generó copia de seguridad\",\"base_datos\":\"NEGOCIO\"}'),
(4724, '2026-06-04 08:35:48', 'CONSULTAR', 1, 19, '{}', '{}'),
(4725, '2026-06-04 08:45:49', 'RESTAURAR', 1, 19, '{}', '{\"accion\":\"Restauró desde PC\",\"base_datos\":\"SEGURIDAD\"}'),
(4726, '2026-06-04 08:45:54', 'CONSULTAR', 1, 17, '{}', '{}'),
(4727, '2026-06-04 08:46:03', 'CONSULTAR', 1, 19, '{}', '{}'),
(4728, '2026-06-04 09:19:16', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4729, '2026-06-04 09:19:27', 'CONSULTAR', 1, 19, '{}', '{}'),
(4730, '2026-06-04 09:20:40', 'CONSULTAR', 1, 1, '{}', '{}'),
(4731, '2026-06-04 09:22:43', 'ELIMINAR', 1, 1, '{\"estado\":\"PENDIENTE\",\"observacion\":\"Abono registrado por Transacci?n 2\"}', '{}'),
(4732, '2026-06-04 09:22:47', 'ELIMINAR', 1, 1, '{\"estado\":\"PENDIENTE\",\"observacion\":\"Abono registrado por Transacci?n 2\"}', '{}'),
(4733, '2026-06-04 09:31:38', 'CONSULTAR', 1, 1, '{}', '{}'),
(4734, '2026-06-04 09:34:15', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PENDIENTE\",\"observacion\":\"pago de 2-3\",\"tasa_dolar\":\"560.38\"}'),
(4735, '2026-06-04 09:41:43', 'MODIFICAR', 1, 1, '{}', '{\"tasa_dolar\":\"558.64\"}'),
(4736, '2026-06-04 09:42:16', 'MODIFICAR', 1, 1, '{\"observacion\":\"Pago registrado desde la App\"}', '{\"observacion\":\"Pago registrado desde la Apps\",\"tasa_dolar\":\"557.97\"}'),
(4737, '2026-06-04 09:42:57', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4738, '2026-06-04 09:43:01', 'INICIAR SESION', 27, 14, '{}', '{}'),
(4739, '2026-06-04 09:43:06', 'CONSULTAR', 27, 5, '{}', '{}'),
(4740, '2026-06-04 09:45:03', 'CONSULTAR', 27, 5, '{}', '{}'),
(4741, '2026-06-04 09:47:49', 'CONSULTAR', 27, 5, '{}', '{}'),
(4742, '2026-06-04 09:50:47', 'CONSULTAR', 27, 5, '{}', '{}'),
(4743, '2026-06-04 09:57:26', 'CONSULTAR', 27, 1, '{}', '{}'),
(4744, '2026-06-04 09:58:12', 'CERRAR SESION', 27, 14, '{}', '{}'),
(4745, '2026-06-04 10:07:28', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4746, '2026-06-04 10:07:51', 'CONSULTAR', 1, 19, '{}', '{}'),
(4747, '2026-06-04 10:18:56', 'CONSULTAR', 1, 22, '{}', '{}'),
(4748, '2026-06-04 10:19:02', 'REGISTRAR', 1, 22, '{}', '{\"accion\":\"errr\"}'),
(4749, '2026-06-04 10:19:41', 'CONSULTAR', 1, 22, '{}', '{}'),
(4750, '2026-06-04 10:19:54', 'CONSULTAR', 1, 22, '{}', '{}'),
(4751, '2026-06-04 10:48:31', 'CONSULTAR', 1, 3, '{}', '{}'),
(4752, '2026-06-04 10:48:43', 'MODIFICAR', 1, 3, '{\"tasa_dolar\":\"515.18\"}', '{\"tasa_dolar\":\"560.38\"}'),
(4753, '2026-06-04 11:02:14', 'CONSULTAR', 1, 1, '{}', '{}'),
(4754, '2026-06-04 11:03:36', 'MODIFICAR', 1, 1, '{}', '{\"tasa_dolar\":\"560.38\"}'),
(4755, '2026-06-04 11:42:25', 'CONSULTAR', 1, 4, '{}', '{}'),
(4756, '2026-06-04 11:42:39', 'CONSULTAR', 1, 2, '{}', '{}'),
(4757, '2026-06-04 11:54:11', 'CONSULTAR', 1, 14, '{}', '{}'),
(4758, '2026-06-04 17:02:22', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4759, '2026-06-04 17:03:26', 'CONSULTAR', 1, 19, '{}', '{}'),
(4760, '2026-06-04 17:03:37', 'RESPALDAR', 1, 19, '{}', '{\"accion\":\"Generó copia de seguridad\",\"base_datos\":\"NEGOCIO\"}'),
(4761, '2026-06-04 17:03:43', 'RESPALDAR', 1, 19, '{}', '{\"accion\":\"Generó copia de seguridad\",\"base_datos\":\"SEGURIDAD\"}'),
(4762, '2026-06-04 17:04:02', 'CONSULTAR', 1, 3, '{}', '{}'),
(4763, '2026-06-04 17:04:12', 'CONSULTAR', 1, 2, '{}', '{}'),
(4764, '2026-06-04 17:04:16', 'CONSULTAR', 1, 9, '{}', '{}'),
(4765, '2026-06-04 17:05:11', 'CONSULTAR', 1, 9, '{}', '{}'),
(4766, '2026-06-04 17:05:18', 'REGISTRAR', 1, 9, '{}', '{\"fecha_inicio\":\"2026-06-04\",\"fecha_cierre\":\"2027-06-04\",\"estado\":\"ABIERTO\",\"descripcion\":\"prueba\"}'),
(4767, '2026-06-04 17:07:10', 'CONSULTAR', 1, 9, '{}', '{}'),
(4768, '2026-06-04 17:08:31', 'CONSULTAR', 1, 9, '{}', '{}'),
(4769, '2026-06-04 17:09:06', 'CONSULTAR', 1, 9, '{}', '{}');
INSERT INTO `bitacora` (`id_bitacora`, `fecha_hora`, `accion`, `usuario_id`, `modulo_id`, `valores_anteriores`, `valores_nuevos`) VALUES
(4770, '2026-06-04 17:09:15', 'MODIFICAR', 1, 9, '{\"descripcion\":\"año fiscal de preuba\"}', '{\"descripcion\":\"año fiscal de \"}'),
(4771, '2026-06-04 17:09:21', 'CONSULTAR', 1, 8, '{}', '{}'),
(4772, '2026-06-04 17:09:27', 'CONSULTAR', 1, 11, '{}', '{}'),
(4773, '2026-06-04 17:10:18', 'CONSULTAR', 1, 11, '{}', '{}'),
(4774, '2026-06-04 17:10:25', 'CONSULTAR', 1, 9, '{}', '{}'),
(4775, '2026-06-04 17:21:14', 'CONSULTAR', 1, 9, '{}', '{}'),
(4776, '2026-06-04 17:30:07', 'CONSULTAR', 1, 19, '{}', '{}'),
(4778, '2026-06-04 17:32:44', 'CONSULTAR', 1, 9, '{}', '{}'),
(4779, '2026-06-04 17:34:33', 'CONSULTAR', 1, 9, '{}', '{}'),
(4780, '2026-06-04 17:35:14', 'CONSULTAR', 1, 9, '{}', '{}'),
(4781, '2026-06-04 17:35:34', 'CONSULTAR', 1, 9, '{}', '{}'),
(4782, '2026-06-04 17:36:22', 'CONSULTAR', 1, 9, '{}', '{}'),
(4783, '2026-06-04 17:37:03', 'MODIFICAR', 1, 9, '{\"descripcion\":\"año fiscal de preuba\"}', '{\"descripcion\":\"año fiscal de\"}'),
(4784, '2026-06-04 17:40:42', 'CONSULTAR', 1, 9, '{}', '{}'),
(4785, '2026-06-04 17:40:46', 'CONSULTAR', 1, 19, '{}', '{}'),
(4786, '2026-06-04 17:41:18', 'RESTAURAR', 1, 19, '{}', '{\"accion\":\"Restauró desde servidor\",\"base_datos\":\"NEGOCIO\",\"archivo\":\"backup_haydee_db_2026-06-04_23-03-33_MANUAL.sql.gz\"}'),
(4787, '2026-06-04 17:41:24', 'CONSULTAR', 1, 9, '{}', '{}'),
(4788, '2026-06-04 17:41:34', 'CONSULTAR', 1, 9, '{}', '{}'),
(4789, '2026-06-04 17:50:43', 'CONSULTAR', 1, 9, '{}', '{}'),
(4790, '2026-06-04 17:59:21', 'CONSULTAR', 1, 9, '{}', '{}'),
(4791, '2026-06-04 18:03:31', 'CONSULTAR', 1, 9, '{}', '{}'),
(4792, '2026-06-04 18:04:51', 'CONSULTAR', 1, 19, '{}', '{}'),
(4793, '2026-06-04 18:05:01', 'RESPALDAR', 1, 19, '{}', '{\"accion\":\"Generó copia de seguridad\",\"base_datos\":\"NEGOCIO\"}'),
(4794, '2026-06-04 18:06:08', 'CONSULTAR', 1, 9, '{}', '{}'),
(4795, '2026-06-04 18:06:54', 'CONSULTAR', 1, 9, '{}', '{}'),
(4796, '2026-06-04 18:07:00', 'REGISTRAR', 1, 9, '{}', '{\"fecha_inicio\":\"2026-06-04\",\"fecha_cierre\":\"2027-06-04\",\"estado\":\"ABIERTO\",\"descripcion\":\"saaa\"}'),
(4797, '2026-06-04 18:07:58', 'CONSULTAR', 1, 2, '{}', '{}'),
(4798, '2026-06-04 18:08:15', 'CONSULTAR', 1, 19, '{}', '{}'),
(4799, '2026-06-04 18:08:44', 'RESTAURAR', 1, 19, '{}', '{\"accion\":\"Restauró desde servidor\",\"base_datos\":\"NEGOCIO\",\"archivo\":\"backup_haydee_db_2026-06-05_00-04-57_MANUAL.sql.gz\"}'),
(4800, '2026-06-04 18:08:48', 'CONSULTAR', 1, 2, '{}', '{}'),
(4801, '2026-06-04 18:08:51', 'CONSULTAR', 1, 9, '{}', '{}'),
(4802, '2026-06-04 18:12:21', 'CONSULTAR', 1, 9, '{}', '{}'),
(4803, '2026-06-04 18:14:47', 'ELIMINAR', 1, 9, '{\"estado\":\"ABIERTO\",\"fecha_inicio\":\"2026-06-02\",\"fecha_cierre\":\"2027-06-02\",\"descripcion\":\"año fiscal de preuba\"}', '{}'),
(4804, '2026-06-04 18:15:00', 'CONSULTAR', 1, 19, '{}', '{}'),
(4805, '2026-06-04 18:15:08', 'RESPALDAR', 1, 19, '{}', '{\"accion\":\"Generó copia de seguridad\",\"base_datos\":\"NEGOCIO\"}'),
(4806, '2026-06-04 18:16:23', 'CONSULTAR', 1, 9, '{}', '{}'),
(4807, '2026-06-04 18:16:32', 'REGISTRAR', 1, 9, '{}', '{\"fecha_inicio\":\"2026-06-04\",\"fecha_cierre\":\"2027-06-04\",\"estado\":\"ABIERTO\",\"descripcion\":\"prueba\"}'),
(4808, '2026-06-04 18:17:15', 'CONSULTAR', 1, 19, '{}', '{}'),
(4809, '2026-06-04 18:17:56', 'RESTAURAR', 1, 19, '{}', '{\"accion\":\"Restauró desde servidor\",\"base_datos\":\"NEGOCIO\",\"archivo\":\"backup_haydee_db_2026-06-05_00-15-06_MANUAL.sql.gz\"}'),
(4810, '2026-06-04 18:18:13', 'CONSULTAR', 1, 9, '{}', '{}'),
(4811, '2026-06-04 18:19:35', 'CONSULTAR', 1, 9, '{}', '{}'),
(4812, '2026-06-04 18:21:32', 'CONSULTAR', 1, 9, '{}', '{}'),
(4813, '2026-06-04 18:23:29', 'CONSULTAR', 1, 9, '{}', '{}'),
(4814, '2026-06-04 18:23:37', 'REGISTRAR', 1, 9, '{}', '{\"fecha_inicio\":\"2026-06-04\",\"fecha_cierre\":\"2027-06-04\",\"estado\":\"ABIERTO\",\"descripcion\":\"prueba\"}'),
(4815, '2026-06-04 18:24:32', 'CONSULTAR', 1, 19, '{}', '{}'),
(4816, '2026-06-04 18:25:02', 'RESTAURAR', 1, 19, '{}', '{\"accion\":\"Restauró desde servidor\",\"base_datos\":\"NEGOCIO\",\"archivo\":\"backup_haydee_db_2026-06-05_00-15-06_MANUAL.sql.gz\"}'),
(4817, '2026-06-04 18:25:06', 'CONSULTAR', 1, 9, '{}', '{}'),
(4818, '2026-06-04 18:25:56', 'CONSULTAR', 1, 9, '{}', '{}'),
(4819, '2026-06-04 18:27:01', 'CONSULTAR', 1, 9, '{}', '{}'),
(4820, '2026-06-04 18:31:23', 'CONSULTAR', 1, 9, '{}', '{}'),
(4821, '2026-06-04 18:31:35', 'CONSULTAR', 1, 9, '{}', '{}'),
(4822, '2026-06-04 18:34:28', 'CONSULTAR', 1, 9, '{}', '{}'),
(4823, '2026-06-04 18:34:38', 'REGISTRAR', 1, 9, '{}', '{\"fecha_inicio\":\"2026-06-04\",\"fecha_cierre\":\"2027-06-04\",\"estado\":\"ABIERTO\",\"descripcion\":\"registro\"}'),
(4824, '2026-06-04 18:35:54', 'ELIMINAR', 1, 9, '{\"estado\":\"ABIERTO\",\"fecha_inicio\":\"2026-06-04\",\"fecha_cierre\":\"2027-06-04\",\"descripcion\":\"registro\"}', '{}'),
(4825, '2026-06-04 18:35:58', 'CONSULTAR', 1, 19, '{}', '{}'),
(4826, '2026-06-04 18:36:11', 'RESPALDAR', 1, 19, '{}', '{\"accion\":\"Generó copia de seguridad\",\"base_datos\":\"NEGOCIO\"}'),
(4827, '2026-06-04 18:36:24', 'CONSULTAR', 1, 9, '{}', '{}'),
(4828, '2026-06-04 18:36:31', 'REGISTRAR', 1, 9, '{}', '{\"fecha_inicio\":\"2026-06-04\",\"fecha_cierre\":\"2027-06-04\",\"estado\":\"ABIERTO\",\"descripcion\":\"registro\"}'),
(4829, '2026-06-04 18:37:12', 'CONSULTAR', 1, 9, '{}', '{}'),
(4830, '2026-06-04 18:37:46', 'REGISTRAR', 1, 9, '{}', '{\"fecha_inicio\":\"2026-05-28\",\"fecha_cierre\":\"2027-05-28\",\"estado\":\"ABIERTO\",\"descripcion\":\"registro 2\"}'),
(4831, '2026-06-04 18:39:07', 'CONSULTAR', 1, 19, '{}', '{}'),
(4832, '2026-06-04 18:39:32', 'RESTAURAR', 1, 19, '{}', '{\"accion\":\"Restauró desde servidor\",\"base_datos\":\"NEGOCIO\",\"archivo\":\"backup_haydee_db_2026-06-05_00-36-09_MANUAL.sql.gz\"}'),
(4833, '2026-06-04 18:39:36', 'CONSULTAR', 1, 9, '{}', '{}'),
(4834, '2026-06-04 18:40:49', 'CONSULTAR', 1, 9, '{}', '{}'),
(4835, '2026-06-04 18:40:52', 'CONSULTAR', 1, 9, '{}', '{}'),
(4836, '2026-06-04 18:40:53', 'CONSULTAR', 1, 9, '{}', '{}'),
(4837, '2026-06-04 18:41:10', 'CONSULTAR', 1, 9, '{}', '{}'),
(4838, '2026-06-04 18:41:40', 'CONSULTAR', 1, 9, '{}', '{}'),
(4839, '2026-06-04 18:42:51', 'CONSULTAR', 1, 9, '{}', '{}'),
(4840, '2026-06-04 18:42:59', 'CONSULTAR', 1, 9, '{}', '{}'),
(4841, '2026-06-04 18:49:07', 'CONSULTAR', 1, 3, '{}', '{}'),
(4842, '2026-06-04 18:49:09', 'CONSULTAR', 1, 2, '{}', '{}'),
(4843, '2026-06-04 18:49:11', 'CONSULTAR', 1, 1, '{}', '{}'),
(4844, '2026-06-04 18:49:21', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4845, '2026-06-04 18:49:34', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4846, '2026-06-04 18:49:38', 'CONSULTAR', 1, 19, '{}', '{}'),
(4847, '2026-06-04 18:49:48', 'RESPALDAR', 1, 19, '{}', '{\"accion\":\"Generó copia de seguridad\",\"base_datos\":\"NEGOCIO\"}'),
(4848, '2026-06-04 18:49:54', 'RESPALDAR', 1, 19, '{}', '{\"accion\":\"Generó copia de seguridad\",\"base_datos\":\"SEGURIDAD\"}'),
(4849, '2026-06-04 18:50:00', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4850, '2026-06-06 17:23:12', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4851, '2026-06-06 17:39:01', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4852, '2026-06-06 17:39:21', 'CONSULTAR', 1, 5, '{}', '{}'),
(4853, '2026-06-06 17:40:17', 'REGISTRAR', 1, 5, '{}', '{\"titulo\":\"hola\",\"descripcion\":\"muy bienas, soy una publicacion desde la web\",\"imagen\":\"xam1_1780782017_175.PNG\",\"prioridad\":\"2\"}'),
(4854, '2026-06-06 17:40:47', 'CONSULTAR', 1, 14, '{}', '{}'),
(4855, '2026-06-06 17:40:58', 'CONSULTAR', 1, 17, '{}', '{}'),
(4856, '2026-06-06 17:41:18', 'MODIFICAR', 1, 17, '{}', '{\"nombre\":\"Contador\",\"permisos_asignados\":[{\"modulo_id\":1,\"permiso_id\":1},{\"modulo_id\":1,\"permiso_id\":2},{\"modulo_id\":1,\"permiso_id\":3},{\"modulo_id\":1,\"permiso_id\":4},{\"modulo_id\":2,\"permiso_id\":1},{\"modulo_id\":2,\"permiso_id\":2},{\"modulo_id\":2,\"permiso_id\":3},{\"modulo_id\":2,\"permiso_id\":4},{\"modulo_id\":3,\"permiso_id\":1},{\"modulo_id\":3,\"permiso_id\":2},{\"modulo_id\":3,\"permiso_id\":3},{\"modulo_id\":3,\"permiso_id\":4},{\"modulo_id\":4,\"permiso_id\":1},{\"modulo_id\":4,\"permiso_id\":2},{\"modulo_id\":4,\"permiso_id\":3},{\"modulo_id\":4,\"permiso_id\":4},{\"modulo_id\":5,\"permiso_id\":1},{\"modulo_id\":5,\"permiso_id\":2},{\"modulo_id\":5,\"permiso_id\":3},{\"modulo_id\":5,\"permiso_id\":4},{\"modulo_id\":5,\"permiso_id\":90},{\"modulo_id\":15,\"permiso_id\":1},{\"modulo_id\":15,\"permiso_id\":2},{\"modulo_id\":15,\"permiso_id\":3},{\"modulo_id\":15,\"permiso_id\":4}]}'),
(4857, '2026-06-06 17:41:24', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4858, '2026-06-06 17:41:54', 'INICIAR SESION', 2, 14, '{}', '{}'),
(4859, '2026-06-06 17:42:01', 'CONSULTAR', 2, 5, '{}', '{}'),
(4860, '2026-06-06 17:42:37', 'REGISTRAR', 2, 5, '{}', '{\"titulo\":\"hola\",\"descripcion\":\"soy una publicacion hecha por el contador francisco\",\"imagen\":\"fiabil_1780782157_115.PNG\",\"prioridad\":\"1\"}'),
(4861, '2026-06-06 17:43:52', 'REGISTRAR', 2, 5, '{}', '{\"titulo\":\"hola\",\"descripcion\":\"soy otra publicacion de francisco\",\"imagen\":\"virustotla_1780782232_303.PNG\",\"prioridad\":\"2\"}'),
(4862, '2026-06-06 17:44:12', 'CERRAR SESION', 2, 14, '{}', '{}'),
(4863, '2026-06-06 17:44:26', 'INICIAR SESION', 27, 14, '{}', '{}'),
(4864, '2026-06-06 17:44:32', 'CONSULTAR', 27, 1, '{}', '{}'),
(4865, '2026-06-06 17:44:58', 'CONSULTAR', 27, 1, '{}', '{}'),
(4866, '2026-06-06 17:45:26', 'CERRAR SESION', 27, 14, '{}', '{}'),
(4867, '2026-06-06 17:45:29', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4868, '2026-06-06 17:45:58', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4869, '2026-06-06 17:46:13', 'INICIAR SESION', 27, 14, '{}', '{}'),
(4870, '2026-06-06 17:46:18', 'CONSULTAR', 27, 1, '{}', '{}'),
(4871, '2026-06-06 17:51:00', 'CONSULTAR', 27, 1, '{}', '{}'),
(4872, '2026-06-06 17:59:14', 'CONSULTAR', 27, 1, '{}', '{}'),
(4873, '2026-06-06 17:59:28', 'REGISTRAR', 27, 1, '{}', '{\"estado\":\"PENDIENTE\",\"observacion\":\"pago de pepe\",\"tasa_dolar\":\"563.29\",\"correo\":\"pepe@gmail.com\"}'),
(4874, '2026-06-06 18:00:11', 'CERRAR SESION', 27, 14, '{}', '{}'),
(4875, '2026-06-06 18:00:25', 'INICIAR SESION', 2, 14, '{}', '{}'),
(4876, '2026-06-06 18:00:34', 'CONSULTAR', 2, 5, '{}', '{}'),
(4877, '2026-06-06 18:01:12', 'REGISTRAR', 2, 5, '{}', '{\"titulo\":\"publicacion\",\"descripcion\":\"tercera publicacion de fran\",\"imagen\":\"virustotla_1780783272_666.PNG\",\"prioridad\":\"3\"}'),
(4878, '2026-06-06 18:01:31', 'ELIMINAR', 2, 5, '{\"titulo\":\"aaaaaaaaaaa\",\"descripcion\":\"fbbbbbbbbbbbbbbbbbbbb\",\"fecha\":\"2010-10-10 00:00:00\",\"imagen\":\"ref_tarjeta_1774719930_739.PNG\",\"prioridad\":\"2\",\"nombre_usuario\":\"Jesus\"}', '{}'),
(4879, '2026-06-06 18:08:23', 'REGISTRAR', 2, 5, '{}', '{\"titulo\":\"cuarta\",\"descripcion\":\"cuarta publicacion del contador francisco\",\"imagen\":\"mensualidad_1780783703_401.PNG\",\"prioridad\":\"2\"}'),
(4880, '2026-06-06 18:33:31', 'REGISTRAR', 2, 5, '{}', '{\"titulo\":\"quinta\",\"descripcion\":\"quinta publicacion de francisco\",\"imagen\":\"vlcsnap-2024-05-12-05h01m32s964_1780785211_752.png\",\"prioridad\":\"1\"}'),
(4881, '2026-06-06 18:37:37', 'REGISTRAR', 2, 5, '{}', '{\"titulo\":\"sexta\",\"descripcion\":\"sexta publicacion de francisco\",\"imagen\":\"cog_1780785457_408.PNG\",\"prioridad\":\"2\"}'),
(4882, '2026-06-06 18:50:42', 'REGISTRAR', 2, 5, '{}', '{\"titulo\":\"septima \",\"descripcion\":\"septima notificacion de francisco\",\"imagen\":\"CSS-Logo_1780786242_515.jpg\",\"prioridad\":\"3\"}'),
(4883, '2026-06-06 21:01:37', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4884, '2026-06-06 21:01:55', 'CONSULTAR', 1, 9, '{}', '{}'),
(4885, '2026-06-06 21:02:10', 'ELIMINAR', 1, 9, '{\"estado\":\"Cerrada\",\"fecha_inicio\":\"2026-06-05\",\"fecha_cierre\":\"2026-06-05\",\"descripcion\":\"soy nuevo\"}', '{}'),
(4886, '2026-06-06 21:02:25', 'MODIFICAR', 1, 9, '{\"descripcion\":\"registro 2\"}', '{\"descripcion\":\"registro eliminable\"}'),
(4887, '2026-06-06 21:22:20', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4888, '2026-06-06 21:22:42', 'CONSULTAR', 1, 9, '{}', '{}'),
(4889, '2026-06-06 21:32:24', 'CONSULTAR', 1, 19, '{}', '{}'),
(4890, '2026-06-06 21:32:33', 'CONSULTAR', 1, 9, '{}', '{}'),
(4891, '2026-06-06 21:33:36', 'CONSULTAR', 1, 9, '{}', '{}'),
(4892, '2026-06-06 21:33:44', 'REGISTRAR', 1, 9, '{}', '{\"fecha_inicio\":\"2026-06-06\",\"fecha_cierre\":\"2027-06-06\",\"estado\":\"ABIERTO\",\"descripcion\":\"asdasdasdas\"}'),
(4893, '2026-06-06 21:33:52', 'ELIMINAR', 1, 9, '{\"estado\":\"Cerrada\",\"fecha_inicio\":\"2026-04-07\",\"fecha_cierre\":\"2027-04-07\",\"descripcion\":\"pepes\"}', '{}'),
(4894, '2026-06-06 21:36:52', 'CONSULTAR', 1, 9, '{}', '{}'),
(4895, '2026-06-06 21:43:10', 'CONSULTAR', 1, 9, '{}', '{}'),
(4896, '2026-06-06 21:44:05', 'CONSULTAR', 1, 9, '{}', '{}'),
(4897, '2026-06-06 21:45:27', 'CONSULTAR', 1, 9, '{}', '{}'),
(4898, '2026-06-06 21:45:54', 'CONSULTAR', 1, 9, '{}', '{}'),
(4899, '2026-06-07 17:11:17', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4900, '2026-06-07 17:12:20', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4901, '2026-06-07 17:12:32', 'CONSULTAR', 1, 5, '{}', '{}'),
(4902, '2026-06-07 17:13:02', 'REGISTRAR', 1, 5, '{}', '{\"titulo\":\"publicacion\",\"descripcion\":\"publicacion del administrador\",\"imagen\":\"virustotla_1780866782_871.PNG\",\"prioridad\":\"2\"}'),
(4903, '2026-06-07 17:18:43', 'REGISTRAR', 1, 5, '{}', '{\"titulo\":\"publicacion\",\"descripcion\":\"publicacion del administrador\",\"imagen\":\"virustotla_1780867123_909.PNG\",\"prioridad\":\"2\"}'),
(4904, '2026-06-07 17:24:16', 'CONSULTAR', 1, 5, '{}', '{}'),
(4905, '2026-06-07 17:40:02', 'CONSULTAR', 1, 1, '{}', '{}'),
(4906, '2026-06-07 17:40:27', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PENDIENTE\",\"observacion\":\"pago del apartamento 2-3\",\"tasa_dolar\":\"563.29\"}'),
(4907, '2026-06-07 18:23:25', 'CONSULTAR', 1, 1, '{}', '{}'),
(4908, '2026-06-07 18:29:20', 'INICIAR SESION', 2, 14, '{}', '{}'),
(4909, '2026-06-07 18:29:26', 'CONSULTAR', 2, 5, '{}', '{}'),
(4910, '2026-06-07 18:29:38', 'CONSULTAR', 1, 1, '{}', '{}'),
(4911, '2026-06-07 18:29:45', 'CONSULTAR', 1, 1, '{}', '{}'),
(4912, '2026-06-07 18:30:19', 'REGISTRAR', 2, 5, '{}', '{\"titulo\":\"octava\",\"descripcion\":\"octava publicacion de francico\",\"imagen\":\"images__2__1780871419_795.png\",\"prioridad\":\"1\"}'),
(4913, '2026-06-07 18:30:54', 'REGISTRAR', 2, 5, '{}', '{\"titulo\":\"octava\",\"descripcion\":\"octava publicacion de francico\",\"imagen\":\"images__2__1780871454_853.png\",\"prioridad\":\"1\"}'),
(4914, '2026-06-07 18:32:06', 'REGISTRAR', 2, 5, '{}', '{\"titulo\":\"octava\",\"descripcion\":\"octava publicacion de francico\",\"imagen\":\"images__2__1780871525_523.png\",\"prioridad\":\"1\"}'),
(4915, '2026-06-07 18:32:38', 'CERRAR SESION', 2, 14, '{}', '{}'),
(4916, '2026-06-07 18:32:48', 'CONSULTAR', 1, 5, '{}', '{}'),
(4917, '2026-06-08 11:31:53', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4918, '2026-06-08 12:28:40', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4919, '2026-06-08 12:35:36', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4920, '2026-06-08 16:56:37', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4921, '2026-06-08 16:59:00', 'CONSULTAR', 1, 19, '{}', '{}'),
(4922, '2026-06-08 16:59:28', 'RESTAURAR', 1, 19, '{}', '{\"accion\":\"Restauró desde servidor\",\"base_datos\":\"NEGOCIO\",\"archivo\":\"backup_haydee_db_2026-06-05_00-49-46_MANUAL.sql.gz\"}'),
(4923, '2026-06-08 17:27:35', 'CONSULTAR', 1, 4, '{}', '{}'),
(4924, '2026-06-08 17:41:25', 'CONSULTAR', 1, 5, '{}', '{}'),
(4925, '2026-06-08 17:41:59', 'CONSULTAR', 1, 1, '{}', '{}'),
(4926, '2026-06-08 17:42:09', 'CONSULTAR', 1, 1, '{}', '{}'),
(4927, '2026-06-08 17:48:51', 'CONSULTAR', 1, 5, '{}', '{}'),
(4928, '2026-06-08 17:48:57', 'CONSULTAR', 1, 5, '{}', '{}'),
(4929, '2026-06-08 17:49:05', 'CONSULTAR', 1, 5, '{}', '{}'),
(4930, '2026-06-08 17:51:17', 'CONSULTAR', 1, 5, '{}', '{}'),
(4931, '2026-06-08 17:51:26', 'CONSULTAR', 1, 5, '{}', '{}'),
(4932, '2026-06-08 17:51:34', 'CONSULTAR', 1, 5, '{}', '{}'),
(4933, '2026-06-08 17:52:33', 'CONSULTAR', 1, 1, '{}', '{}'),
(4934, '2026-06-08 17:52:35', 'CONSULTAR', 1, 1, '{}', '{}'),
(4935, '2026-06-08 17:52:37', 'CONSULTAR', 1, 19, '{}', '{}'),
(4936, '2026-06-08 17:52:52', 'CONSULTAR', 1, 1, '{}', '{}'),
(4937, '2026-06-08 17:53:09', 'CONSULTAR', 1, 1, '{}', '{}'),
(4938, '2026-06-08 17:53:19', 'CONSULTAR', 1, 1, '{}', '{}'),
(4939, '2026-06-08 17:53:50', 'CONSULTAR', 1, 1, '{}', '{}'),
(4940, '2026-06-08 17:54:09', 'CONSULTAR', 1, 1, '{}', '{}'),
(4941, '2026-06-08 17:58:08', 'CONSULTAR', 1, 5, '{}', '{}'),
(4942, '2026-06-08 18:01:49', 'CONSULTAR', 1, 5, '{}', '{}'),
(4943, '2026-06-08 18:01:59', 'CONSULTAR', 1, 5, '{}', '{}'),
(4944, '2026-06-08 18:02:48', 'CONSULTAR', 1, 5, '{}', '{}'),
(4945, '2026-06-08 18:07:58', 'CONSULTAR', 1, 5, '{}', '{}'),
(4946, '2026-06-08 18:08:10', 'CONSULTAR', 1, 5, '{}', '{}'),
(4947, '2026-06-08 18:08:19', 'CONSULTAR', 1, 5, '{}', '{}'),
(4948, '2026-06-08 18:08:31', 'CONSULTAR', 1, 5, '{}', '{}'),
(4949, '2026-06-08 18:08:36', 'CONSULTAR', 1, 5, '{}', '{}'),
(4950, '2026-06-08 18:08:51', 'CONSULTAR', 1, 5, '{}', '{}'),
(4951, '2026-06-08 18:11:46', 'CONSULTAR', 1, 5, '{}', '{}'),
(4952, '2026-06-08 18:11:53', 'CONSULTAR', 1, 5, '{}', '{}'),
(4953, '2026-06-08 18:12:04', 'CONSULTAR', 1, 5, '{}', '{}'),
(4954, '2026-06-08 18:20:47', 'CONSULTAR', 1, 1, '{}', '{}'),
(4955, '2026-06-08 18:20:51', 'CONSULTAR', 1, 1, '{}', '{}'),
(4956, '2026-06-08 18:21:22', 'CONSULTAR', 1, 1, '{}', '{}'),
(4957, '2026-06-08 18:23:49', 'CONSULTAR', 1, 1, '{}', '{}'),
(4958, '2026-06-08 18:24:22', 'CONSULTAR', 1, 1, '{}', '{}'),
(4959, '2026-06-08 18:24:28', 'CONSULTAR', 1, 5, '{}', '{}'),
(4960, '2026-06-08 18:24:37', 'CONSULTAR', 1, 1, '{}', '{}'),
(4961, '2026-06-08 18:26:29', 'CONSULTAR', 1, 1, '{}', '{}'),
(4962, '2026-06-08 18:34:31', 'CONSULTAR', 1, 1, '{}', '{}'),
(4963, '2026-06-08 18:35:23', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PROCESADO\",\"observacion\":\"pago\",\"tasa_dolar\":\"563.29\"}'),
(4964, '2026-06-08 18:39:42', 'CONSULTAR', 1, 1, '{}', '{}'),
(4965, '2026-06-08 18:40:15', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PROCESADO\",\"observacion\":\"pago\",\"tasa_dolar\":\"563.29\"}'),
(4966, '2026-06-08 18:40:30', 'CONSULTAR', 1, 1, '{}', '{}'),
(4967, '2026-06-08 18:40:37', 'MODIFICAR', 1, 1, '{}', '{\"tasa_dolar\":\"563.29\"}'),
(4968, '2026-06-08 18:41:36', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PROCESADO\",\"observacion\":\"pago\",\"tasa_dolar\":\"563.29\"}'),
(4969, '2026-06-08 18:50:04', 'CONSULTAR', 1, 1, '{}', '{}'),
(4970, '2026-06-08 18:50:19', 'MODIFICAR', 1, 1, '{}', '{\"tasa_dolar\":\"563.29\"}'),
(4971, '2026-06-08 18:50:42', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PROCESADO\",\"observacion\":\"pago procesado\",\"tasa_dolar\":\"563.29\"}'),
(4972, '2026-06-08 18:50:56', 'CONSULTAR', 1, 1, '{}', '{}'),
(4973, '2026-06-08 18:51:15', 'CONSULTAR', 1, 1, '{}', '{}'),
(4974, '2026-06-08 18:51:32', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PROCESADO\",\"observacion\":\"pago\",\"tasa_dolar\":\"563.29\"}'),
(4975, '2026-06-08 18:55:21', 'CONSULTAR', 1, 1, '{}', '{}'),
(4976, '2026-06-08 18:56:35', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PROCESADO\",\"observacion\":\"pago\",\"tasa_dolar\":\"563.29\"}'),
(4977, '2026-06-08 18:57:00', 'CONSULTAR', 1, 1, '{}', '{}'),
(4978, '2026-06-08 19:00:05', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PENDIENTE\",\"observacion\":\"pago pendiente de pepe\",\"tasa_dolar\":\"563.29\"}'),
(4979, '2026-06-08 19:00:33', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PROCESADO\",\"observacion\":\"pago confirmado de pepe\",\"tasa_dolar\":\"563.29\"}'),
(4980, '2026-06-08 19:08:01', 'CONSULTAR', 1, 1, '{}', '{}'),
(4981, '2026-06-08 19:12:29', 'CONSULTAR', 1, 1, '{}', '{}'),
(4982, '2026-06-08 19:35:38', 'CONSULTAR', 1, 1, '{}', '{}'),
(4983, '2026-06-08 19:35:58', 'CONSULTAR', 1, 1, '{}', '{}'),
(4984, '2026-06-09 09:41:56', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4985, '2026-06-09 10:07:22', 'CONSULTAR', 1, 1, '{}', '{}'),
(4986, '2026-06-09 10:07:40', 'MODIFICAR', 1, 1, '{}', '{\"tasa_dolar\":\"563.29\"}'),
(4987, '2026-06-09 10:28:57', 'CONSULTAR', 1, 1, '{}', '{}'),
(4988, '2026-06-09 10:31:16', 'CONSULTAR', 1, 1, '{}', '{}'),
(4989, '2026-06-09 10:31:23', 'MODIFICAR', 1, 1, '{}', '{\"tasa_dolar\":\"563.29\"}'),
(4990, '2026-06-09 20:14:00', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4991, '2026-06-09 20:14:16', 'CONSULTAR', 1, 2, '{}', '{}'),
(4992, '2026-06-09 20:14:19', 'CONSULTAR', 1, 2, '{}', '{}'),
(4993, '2026-06-09 20:15:19', 'CONSULTAR', 1, 2, '{}', '{}'),
(4994, '2026-06-09 20:17:15', 'CONSULTAR', 1, 2, '{}', '{}'),
(4995, '2026-06-09 20:21:08', 'CONSULTAR', 1, 1, '{}', '{}'),
(4996, '2026-06-09 20:21:18', 'CONSULTAR', 1, 2, '{}', '{}'),
(4997, '2026-06-09 20:21:28', 'CONSULTAR', 1, 1, '{}', '{}'),
(4998, '2026-06-09 20:22:06', 'CONSULTAR', 1, 5, '{}', '{}'),
(4999, '2026-06-09 20:22:09', 'CONSULTAR', 1, 5, '{}', '{}'),
(5000, '2026-06-09 20:22:12', 'CONSULTAR', 1, 3, '{}', '{}'),
(5001, '2026-06-09 20:22:43', 'CONSULTAR', 1, 1, '{}', '{}'),
(5002, '2026-06-09 20:22:57', 'CONSULTAR', 1, 5, '{}', '{}'),
(5003, '2026-06-09 20:23:49', 'CONSULTAR', 1, 1, '{}', '{}'),
(5004, '2026-06-09 20:24:11', 'CONSULTAR', 1, 2, '{}', '{}'),
(5005, '2026-06-09 20:24:48', 'CONSULTAR', 1, 2, '{}', '{}'),
(5006, '2026-06-09 20:25:01', 'CONSULTAR', 1, 3, '{}', '{}'),
(5007, '2026-06-09 20:27:52', 'CONSULTAR', 1, 2, '{}', '{}'),
(5008, '2026-06-09 20:29:05', 'CONSULTAR', 1, 1, '{}', '{}'),
(5009, '2026-06-09 20:30:34', 'CONSULTAR', 1, 1, '{}', '{}'),
(5010, '2026-06-09 20:30:58', 'CONSULTAR', 1, 1, '{}', '{}'),
(5011, '2026-06-09 20:31:10', 'CONSULTAR', 1, 2, '{}', '{}'),
(5012, '2026-06-09 20:33:24', 'CONSULTAR', 1, 8, '{}', '{}'),
(5013, '2026-06-09 20:33:32', 'CONSULTAR', 1, 8, '{}', '{}'),
(5014, '2026-06-09 20:35:06', 'CONSULTAR', 1, 8, '{}', '{}'),
(5015, '2026-06-09 20:36:41', 'CONSULTAR', 1, 19, '{}', '{}'),
(5016, '2026-06-09 20:37:05', 'CONSULTAR', 1, 19, '{}', '{}'),
(5017, '2026-06-09 20:42:28', 'CONSULTAR', 1, 19, '{}', '{}'),
(5018, '2026-06-09 20:42:37', 'CONSULTAR', 1, 19, '{}', '{}'),
(5019, '2026-06-09 20:43:24', 'CONSULTAR', 1, 19, '{}', '{}'),
(5020, '2026-06-09 20:44:10', 'CONSULTAR', 1, 19, '{}', '{}'),
(5021, '2026-06-09 20:44:31', 'RESPALDAR', 1, 19, '{}', '{\"accion\":\"Generó copia de seguridad\",\"base_datos\":\"NEGOCIO\"}'),
(5022, '2026-06-09 20:45:27', 'CONSULTAR', 1, 19, '{}', '{}'),
(5023, '2026-06-09 20:45:37', 'CONSULTAR', 1, 19, '{}', '{}'),
(5024, '2026-06-09 20:45:58', 'CONSULTAR', 1, 19, '{}', '{}'),
(5025, '2026-06-09 20:46:43', 'CONSULTAR', 1, 19, '{}', '{}'),
(5026, '2026-06-09 20:47:16', 'CONSULTAR', 1, 19, '{}', '{}'),
(5027, '2026-06-09 20:56:08', 'CONSULTAR', 1, 19, '{}', '{}'),
(5028, '2026-06-09 20:57:54', 'CONSULTAR', 1, 19, '{}', '{}'),
(5029, '2026-06-09 20:59:29', 'RESTAURAR', 1, 19, '{}', '{\"accion\":\"Restauró desde servidor\",\"base_datos\":\"NEGOCIO\",\"archivo\":\"backup_haydee_db_2026-06-10_02-44-28_MANUAL.sql.gz\"}'),
(5030, '2026-06-09 21:00:28', 'RESTAURAR', 1, 19, '{}', '{\"accion\":\"Restauró desde servidor\",\"base_datos\":\"NEGOCIO\",\"archivo\":\"backup_haydee_db_2026-06-10_02-44-28_MANUAL.sql.gz\"}'),
(5031, '2026-06-09 21:00:40', 'CONSULTAR', 1, 19, '{}', '{}'),
(5032, '2026-06-09 21:01:18', 'RESTAURAR', 1, 19, '{}', '{\"accion\":\"Restauró desde servidor\",\"base_datos\":\"NEGOCIO\",\"archivo\":\"backup_haydee_db_2026-06-10_02-44-28_MANUAL.sql.gz\"}'),
(5033, '2026-06-09 21:01:22', 'CONSULTAR', 1, 19, '{}', '{}'),
(5034, '2026-06-09 21:05:27', 'RESTAURAR', 1, 19, '{}', '{\"accion\":\"Restauró desde servidor\",\"base_datos\":\"SEGURIDAD\",\"archivo\":\"backup_seguridad_haydee_db_2026-06-10_03-01-41_MANUAL.sql.gz\"}'),
(5035, '2026-06-09 21:05:30', 'CONSULTAR', 1, 19, '{}', '{}'),
(5036, '2026-06-09 21:05:35', 'CONSULTAR', 1, 5, '{}', '{}'),
(5037, '2026-06-09 21:09:59', 'CONSULTAR', 1, 5, '{}', '{}'),
(5038, '2026-06-09 21:10:05', 'CONSULTAR', 1, 19, '{}', '{}'),
(5039, '2026-06-09 21:10:40', 'RESTAURAR', 1, 19, '{}', '{\"accion\":\"Restauró desde servidor\",\"base_datos\":\"NEGOCIO\",\"archivo\":\"backup_haydee_db_2026-06-10_02-44-28_MANUAL.sql.gz\"}'),
(5040, '2026-06-09 21:10:43', 'CONSULTAR', 1, 19, '{}', '{}'),
(5041, '2026-06-09 21:10:53', 'CONSULTAR', 1, 19, '{}', '{}'),
(5042, '2026-06-09 23:38:53', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5043, '2026-06-10 17:04:22', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5044, '2026-06-10 17:04:33', 'CONSULTAR', 1, 1, '{}', '{}'),
(5045, '2026-06-10 17:06:08', 'CONSULTAR', 1, 1, '{}', '{}'),
(5046, '2026-06-10 17:06:15', 'CONSULTAR', 1, 1, '{}', '{}'),
(5047, '2026-06-10 17:06:40', 'CONSULTAR', 1, 1, '{}', '{}'),
(5048, '2026-06-10 17:06:55', 'CONSULTAR', 1, 9, '{}', '{}'),
(5049, '2026-06-10 17:08:10', 'CONSULTAR', 1, 9, '{}', '{}'),
(5050, '2026-06-10 17:08:17', 'CONSULTAR', 1, 9, '{}', '{}'),
(5051, '2026-06-10 17:08:36', 'CONSULTAR', 1, 9, '{}', '{}'),
(5052, '2026-06-10 17:09:31', 'CONSULTAR', 1, 9, '{}', '{}'),
(5053, '2026-06-10 17:09:35', 'CONSULTAR', 1, 9, '{}', '{}'),
(5054, '2026-06-10 17:09:41', 'CONSULTAR', 1, 9, '{}', '{}'),
(5055, '2026-06-10 17:10:25', 'CONSULTAR', 1, 9, '{}', '{}'),
(5056, '2026-06-10 17:10:47', 'CONSULTAR', 1, 9, '{}', '{}'),
(5057, '2026-06-10 17:11:00', 'CONSULTAR', 1, 1, '{}', '{}'),
(5058, '2026-06-12 17:21:56', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5059, '2026-06-12 17:24:14', 'CERRAR SESION', 1, 14, '{}', '{}'),
(5060, '2026-06-12 17:25:06', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5061, '2026-06-12 18:45:42', 'CERRAR SESION', 1, 14, '{}', '{}'),
(5062, '2026-06-12 18:50:05', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5063, '2026-06-12 18:53:12', 'CERRAR SESION', 1, 14, '{}', '{}'),
(5064, '2026-06-12 18:53:32', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5065, '2026-06-12 18:57:56', 'CERRAR SESION', 1, 14, '{}', '{}'),
(5066, '2026-06-12 18:58:05', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5067, '2026-06-12 18:58:30', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5068, '2026-06-13 17:52:13', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5069, '2026-06-13 17:54:07', 'CERRAR SESION', 1, 14, '{}', '{}'),
(5070, '2026-06-13 18:04:56', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5071, '2026-06-13 18:05:45', 'CERRAR SESION', 1, 14, '{}', '{}'),
(5072, '2026-06-13 18:10:53', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5073, '2026-06-13 19:27:33', 'CERRAR SESION', 1, 14, '{}', '{}'),
(5074, '2026-06-13 19:27:48', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5075, '2026-06-13 19:30:29', 'CERRAR SESION', 1, 14, '{}', '{}'),
(5076, '2026-06-13 19:30:48', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5077, '2026-06-13 19:31:16', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5078, '2026-06-13 19:31:21', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5079, '2026-06-13 19:31:49', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5080, '2026-06-13 19:48:05', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5081, '2026-06-13 19:50:02', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5082, '2026-06-13 19:50:28', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5083, '2026-06-13 19:51:14', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5084, '2026-06-13 19:51:25', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5085, '2026-06-13 19:59:17', 'CONSULTAR', 1, 1, '{}', '{}'),
(5086, '2026-06-13 20:14:07', 'CERRAR SESION', 1, 14, '{}', '{}'),
(5087, '2026-06-13 21:19:42', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5088, '2026-06-13 21:21:13', 'CONSULTAR', 1, 9, '{}', '{}'),
(5089, '2026-06-13 21:35:39', 'CONSULTAR', 1, 2, '{}', '{}'),
(5090, '2026-06-13 21:35:42', 'CONSULTAR', 1, 4, '{}', '{}'),
(5091, '2026-06-14 11:45:43', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5092, '2026-06-14 11:46:47', 'CONSULTAR', 1, 2, '{}', '{}'),
(5093, '2026-06-14 11:46:54', 'CONSULTAR', 1, 4, '{}', '{}'),
(5094, '2026-06-14 11:47:48', 'CONSULTAR', 1, 5, '{}', '{}'),
(5095, '2026-06-14 11:48:47', 'CONSULTAR', 1, 5, '{}', '{}'),
(5096, '2026-06-14 11:56:38', 'CONSULTAR', 1, 5, '{}', '{}'),
(5097, '2026-06-14 12:04:24', 'CONSULTAR', 1, 5, '{}', '{}'),
(5098, '2026-06-14 12:06:39', 'CONSULTAR', 1, 5, '{}', '{}'),
(5099, '2026-06-14 12:10:53', 'CONSULTAR', 1, 5, '{}', '{}'),
(5100, '2026-06-14 12:11:55', 'CONSULTAR', 1, 5, '{}', '{}'),
(5101, '2026-06-14 12:14:36', 'CONSULTAR', 1, 5, '{}', '{}'),
(5102, '2026-06-14 12:15:38', 'CONSULTAR', 1, 5, '{}', '{}'),
(5103, '2026-06-14 12:19:46', 'CONSULTAR', 1, 5, '{}', '{}'),
(5104, '2026-06-14 12:24:43', 'CONSULTAR', 1, 5, '{}', '{}'),
(5105, '2026-06-14 12:25:09', 'CONSULTAR', 1, 5, '{}', '{}'),
(5106, '2026-06-14 12:28:21', 'CONSULTAR', 1, 5, '{}', '{}'),
(5107, '2026-06-14 12:29:23', 'CONSULTAR', 1, 5, '{}', '{}'),
(5108, '2026-06-14 12:36:43', 'CONSULTAR', 1, 9, '{}', '{}'),
(5109, '2026-06-14 12:46:12', 'CONSULTAR', 1, 9, '{}', '{}'),
(5110, '2026-06-14 12:47:15', 'CONSULTAR', 1, 9, '{}', '{}'),
(5111, '2026-06-14 12:50:12', 'CONSULTAR', 1, 9, '{}', '{}'),
(5112, '2026-06-14 12:55:45', 'CONSULTAR', 1, 9, '{}', '{}'),
(5113, '2026-06-14 12:56:02', 'CONSULTAR', 1, 9, '{}', '{}'),
(5114, '2026-06-14 13:01:00', 'CONSULTAR', 1, 9, '{}', '{}'),
(5115, '2026-06-14 13:01:57', 'CONSULTAR', 1, 9, '{}', '{}'),
(5116, '2026-06-14 14:03:52', 'CONSULTAR', 1, 9, '{}', '{}'),
(5117, '2026-06-14 14:15:24', 'CONSULTAR', 1, 9, '{}', '{}'),
(5118, '2026-06-14 15:20:23', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5119, '2026-06-14 15:36:11', 'CERRAR SESION', 1, 14, '{}', '{}'),
(5120, '2026-06-14 15:36:19', 'INICIAR SESION', 1, 14, '{}', '{}'),
(5121, '2026-06-14 17:32:37', 'CONSULTAR', 1, 9, '{}', '{}'),
(5122, '2026-06-14 17:37:08', 'CERRAR SESION', 1, 14, '{}', '{}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cartelera_virtual`
--

CREATE TABLE `cartelera_virtual` (
  `id_cartelera` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `imagen` varchar(100) DEFAULT NULL,
  `prioridad` varchar(10) NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cartelera_virtual`
--

INSERT INTO `cartelera_virtual` (`id_cartelera`, `titulo`, `descripcion`, `fecha`, `imagen`, `prioridad`, `usuario_id`) VALUES
(19, 'Bienvenidos', 'bienvenidos al 2026', '2100-10-10 00:00:00', 'err_1774720041_174.PNG', '3', 1),
(23, 'aaaaaa', 'asdasdasd', '2026-03-05 00:00:00', 'Captura__2__1774720054_938.PNG', '3', 1),
(28, 'pepe tapo el escusado', 'pepe tapo el escusado mis pana', '2026-03-28 12:48:36', 'meme_1774720065_811.PNG', '1', 1),
(29, 'adasdasdas', 'adasdasdasd', '2026-03-28 13:42:59', 'fake_new_1774719779_771.PNG', '2', 1),
(30, 'hola pana', 'nueva publicacione', '2026-04-07 19:16:57', 'Captura__2__1775603817_147.PNG', '1', 1),
(31, 'hola', 'publicacion 2', '2026-04-07 19:22:54', 'ejemplo2_1775604174_147.PNG', '2', 1),
(32, 'sfasdasd', 'sadasdas', '2026-04-07 19:25:16', 'ref_tarjeta_1775604316_210.PNG', '1', 1),
(33, 'asdasd', 'asdasd', '2026-04-07 19:25:54', '', '2', 1),
(34, 'adasdas', 'asdasd', '2026-04-07 19:26:08', '', '3', 1),
(35, 'hola pepe ', 'hola pepe como estas', '2026-04-12 16:16:00', 'fake_new_1776024960_274.PNG', '2', 1),
(36, 'Publicacion react', 'Esta es una publicación desde react native', '2026-04-25 15:38:02', '9a8d16b0-577f-4f27-ab0d-6284568289de_1777145882_484.jpeg', '1', 1),
(37, 'Hola', 'Habló desde react', '2026-04-25 15:50:27', 'ae247589-02a4-4b81-bafc-7909594ad77b_1777146627_726.jpeg', '3', 1),
(38, 'Se dañó un botón ', 'Mano el botón de la luna se dañó hay que arreglarlo ', '2026-04-30 10:48:52', '6fd97767-f745-4e47-b660-27993ddf5cd8_1777560532_605.png', '1', 1),
(39, 'Ya casi', 'Ya casi', '2026-04-30 11:50:54', 'f44e1ac5-7768-4f6c-944f-1989807ebe7b_1777564254_220.png', '2', 1),
(40, 'Hola', 'Hola buenas ', '2026-05-10 01:07:08', '', '1', 1),
(41, 'Hola buenas ', 'Hola buenas ', '2026-05-10 01:07:28', '', '1', 1),
(42, 'Hola mi amor ', 'Gkdksksslsmsmd', '2026-05-10 01:08:24', '', '2', 1),
(43, 'Hola mi vida ', 'Jdjdkdkddd', '2026-05-10 01:13:49', '40823269-8e2c-44d1-b355-97052ec4ea17_1778390029_619.png', '2', 1),
(44, 'Hola', 'Soy un mensaje encriptado ', '2026-05-12 11:01:12', '459de706-6428-4814-91cc-ad224bb37a03_1778598072_120.jpeg', '2', 1),
(45, 'Publicacion', 'Publicacion genérica ', '2026-05-12 11:30:42', '9ba0bd5b-db27-4a64-84ca-0ed4b0c8db60_1778599842_730.png', '3', 1),
(46, 'Hola ', 'Hola ora vez ', '2026-05-14 12:53:10', '88b272cc-4918-466a-ac05-a16893278e10_1778777590_510.jpeg', '3', 1),
(47, 'Hola ', 'Hola chamo', '2026-05-14 13:03:54', 'aadd8944-bbab-4b0a-bccb-1c29e86ac835_1778778234_743.jpeg', '2', 1),
(48, 'Nuevo aviso', 'Hay un nuevo avuso', '2026-05-26 10:12:38', 'e3af546b-803d-4d3d-8492-55828c7a6cd3_1779804758_629.jpeg', '2', 1),
(49, 'Publicacion 2', 'Publicacion número dos', '2026-05-26 14:41:28', '988a6b55-8142-4741-bca9-ba0d6cff8123_1779820888_675.jpeg', '3', 1),
(50, 'Hola', 'Hola mano como estas', '2026-05-27 18:30:58', '959833da-8fb5-4cb7-89da-9e3c8b16d323_1779921058_442.jpeg', '2', 1),
(51, 'Hola ', 'Hola', '2026-06-02 23:02:33', '', '2', 1),
(52, 'hola', 'muy bienas, soy una publicacion desde la web', '2026-06-06 17:40:17', 'xam1_1780782017_175.PNG', '2', 1),
(53, 'hola', 'soy una publicacion hecha por el contador francisco', '2026-06-06 17:42:37', 'fiabil_1780782157_115.PNG', '1', 2),
(54, 'hola', 'soy otra publicacion de francisco', '2026-06-06 17:43:52', 'virustotla_1780782232_303.PNG', '2', 2),
(55, 'publicacion', 'tercera publicacion de fran', '2026-06-06 18:01:12', 'virustotla_1780783272_666.PNG', '3', 2),
(56, 'cuarta', 'cuarta publicacion del contador francisco', '2026-06-06 18:08:23', 'mensualidad_1780783703_401.PNG', '2', 2),
(57, 'quinta', 'quinta publicacion de francisco', '2026-06-06 18:33:31', 'vlcsnap-2024-05-12-05h01m32s964_1780785211_752.png', '1', 2),
(58, 'sexta', 'sexta publicacion de francisco', '2026-06-06 18:37:37', 'cog_1780785457_408.PNG', '2', 2),
(59, 'septima ', 'septima notificacion de francisco', '2026-06-06 18:50:42', 'CSS-Logo_1780786242_515.jpg', '3', 2),
(60, 'publicacion', 'publicacion del administrador', '2026-06-07 17:13:02', 'virustotla_1780866782_871.PNG', '2', 1),
(61, 'publicacion', 'publicacion del administrador', '2026-06-07 17:18:43', 'virustotla_1780867123_909.PNG', '2', 1),
(62, 'octava', 'octava publicacion de francico', '2026-06-07 18:30:19', 'images__2__1780871419_795.png', '1', 2),
(63, 'octava', 'octava publicacion de francico', '2026-06-07 18:30:54', 'images__2__1780871454_853.png', '1', 2),
(64, 'octava', 'octava publicacion de francico', '2026-06-07 18:32:05', 'images__2__1780871525_523.png', '1', 2),
(65, 'Atencion', 'Necesitamos mejorar el diseño de los detalles ', '2026-06-08 12:27:52', '3a92702a-fd90-4f39-b0a5-9426132044fc_1780936072_495.jpeg', '3', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `claves_sesion`
--

CREATE TABLE `claves_sesion` (
  `dispositivo_id` varchar(255) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `clave_aes` text NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `ultima_actividad` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `claves_sesion`
--

INSERT INTO `claves_sesion` (`dispositivo_id`, `usuario_id`, `clave_aes`, `fecha_creacion`, `ultima_actividad`) VALUES
('3bb214f0-fa54-4ddb-8fdb-556b5ebfb7c1', 27, 'MppkRgG/dA3zE95NiauhwLhkOM3364f8/u2Oq30yg7g=', '2026-06-03 20:42:47', '2026-06-03 20:59:38'),
('695a0696-feea-4ae0-b902-76ea585c2dff', 1, 'pIGaBV2l57ep+5sg4NtJE6JzslYU1L06bpKLhNDzhws=', '2026-06-14 15:36:20', '2026-06-14 15:45:25'),
('c6b2c137-59e7-4bd5-b390-48e26acb46c5', 1, 'ruHyIJ7gmRZeMBNS3V/lHH06XWUQPOtqFz6VnRVt39I=', '2026-06-09 23:38:54', '2026-06-09 23:49:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos_sistema`
--

CREATE TABLE `eventos_sistema` (
  `id_evento` int(11) NOT NULL,
  `tipo_evento` varchar(50) NOT NULL,
  `tabla_origen` varchar(50) NOT NULL,
  `id_registro_origen` int(11) NOT NULL,
  `fecha_evento` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `eventos_sistema`
--

INSERT INTO `eventos_sistema` (`id_evento`, `tipo_evento`, `tabla_origen`, `id_registro_origen`, `fecha_evento`) VALUES
(32, 'NUEVA_MENSUALIDAD', 'mensualidad', 669, '2026-05-18 22:20:20'),
(33, 'NUEVA_MENSUALIDAD', 'mensualidad', 669, '2026-05-18 22:21:23'),
(34, 'CREACION_AVISO', 'cartelera_virtual', 52, '2026-06-06 17:40:18'),
(35, 'CREACION_AVISO', 'cartelera_virtual', 53, '2026-06-06 17:42:37'),
(36, 'CREACION_AVISO', 'cartelera_virtual', 54, '2026-06-06 17:43:52'),
(37, 'CREACION_AVISO', 'cartelera_virtual', 55, '2026-06-06 18:01:13'),
(38, 'CREACION_AVISO', 'cartelera_virtual', 56, '2026-06-06 18:08:23'),
(39, 'CREACION_AVISO', 'cartelera_virtual', 57, '2026-06-06 18:33:31'),
(40, 'CREACION_AVISO', 'cartelera_virtual', 58, '2026-06-06 18:37:37'),
(41, 'CREACION_AVISO', 'cartelera_virtual', 59, '2026-06-06 18:50:42'),
(42, 'CREACION_AVISO', 'cartelera_virtual', 60, '2026-06-07 17:13:02'),
(43, 'nueva_publicacion', 'cartelera_virtual', 61, '2026-06-07 17:18:43'),
(44, 'pago_recibido', 'pagos', 160, '2026-06-07 17:40:27'),
(45, 'nueva_publicacion', 'cartelera_virtual', 62, '2026-06-07 18:30:19'),
(46, 'nueva_publicacion', 'cartelera_virtual', 63, '2026-06-07 18:30:54'),
(47, 'nueva_publicacion', 'cartelera_virtual', 64, '2026-06-07 18:32:06'),
(48, 'pago_recibido', 'pagos', 161, '2026-06-08 11:48:18'),
(49, 'nueva_publicacion', 'cartelera_virtual', 65, '2026-06-08 12:27:52'),
(50, 'pago_recibido', 'pagos', 159, '2026-06-08 18:35:23'),
(51, 'pago_recibido', 'pagos', 160, '2026-06-08 18:40:15'),
(52, 'pago_recibido', 'pagos', 161, '2026-06-08 18:41:36'),
(53, 'pago_recibido', 'pagos', 162, '2026-06-08 18:50:43'),
(54, 'pago_recibido', 'pagos', 163, '2026-06-08 18:51:33'),
(55, 'pago_recibido', 'pagos', 164, '2026-06-08 18:56:35'),
(56, 'pago_recibido', 'pagos', 165, '2026-06-08 19:00:06'),
(57, 'pago_recibido', 'pagos', 166, '2026-06-08 19:00:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intentos_login`
--

CREATE TABLE `intentos_login` (
  `usuario_id` int(11) NOT NULL,
  `intentos` int(11) NOT NULL DEFAULT 1,
  `ultimo_intento` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `listas_acceso_ip`
--

CREATE TABLE `listas_acceso_ip` (
  `id_lista` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `tipo_lista` enum('BLANCA','NEGRA') NOT NULL,
  `motivo` text DEFAULT NULL,
  `fecha_agregado` datetime NOT NULL DEFAULT current_timestamp(),
  `reincidencias` int(11) NOT NULL DEFAULT 1,
  `fecha_expiracion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulos`
--

CREATE TABLE `modulos` (
  `id_modulo` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `modulos`
--

INSERT INTO `modulos` (`id_modulo`, `nombre`, `activo`) VALUES
(1, 'GESTIONAR_PAGOS', 1),
(2, 'GESTIONAR_GASTOS', 1),
(3, 'GESTIONAR_MENSUALIDAD', 1),
(4, 'GESTIONAR_CAJA_CHICA', 1),
(5, 'GESTIONAR_CARTELERA_VIRTUAL', 1),
(6, 'GESTIONAR_APARTAMENTOS', 1),
(7, 'GESTIONAR_SOLICITUD_GASTO', 1),
(8, 'GESTIONAR_PRESUPUESTO', 1),
(9, 'GESTIONAR_ANIO_FISCAL', 1),
(10, 'GESTIONAR_CONFIGURACION', 1),
(11, 'GESTIONAR_PROVEEDORES', 1),
(12, 'GESTIONAR_BANCOS', 1),
(13, 'GESTIONAR_TIPO_GASTO', 1),
(14, 'GESTIONAR_USUARIOS', 1),
(15, 'GESTIONAR_REPORTES', 1),
(16, 'GESTIONAR_SEGURIDAD', 1),
(17, 'GESTIONAR_ROLES', 1),
(18, 'GESTIONAR_BITACORA', 1),
(19, 'GESTIONAR_MANTENIMIENTO', 1),
(21, 'GESTIONAR_HABITANTES', 1),
(22, 'GESTIONAR_PERMISOS', 1),
(23, 'GESTIONAR_MODULOS', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `titulo`, `descripcion`, `fecha`, `leido`, `usuario_id`) VALUES
(239, 'Nueva mensualidad disponible', 'Se han generado las mensualidades para el mes 1 del año 2026.', '2026-05-18 22:20:20', 0, 1),
(240, 'Nueva mensualidad disponible', 'Se han generado las mensualidades para el mes 1 del año 2026.', '2026-05-18 22:20:20', 0, 2),
(241, 'Nueva mensualidad disponible', 'Se han generado las mensualidades para el mes 1 del año 2026.', '2026-05-18 22:20:20', 0, 27),
(242, 'Nueva mensualidad disponible', 'Se han generado las mensualidades para el mes 1 del año 2026.', '2026-05-18 22:20:20', 0, 39),
(243, 'Nueva mensualidad disponible', 'Se han generado las mensualidades para el mes 1 del año 2026.', '2026-05-18 22:20:20', 0, 91),
(244, 'Nueva mensualidad disponible', 'Se han generado las mensualidades para el mes 1 del año 2026.', '2026-05-18 22:20:20', 0, 92),
(245, 'Nueva mensualidad disponible', 'Se han generado las mensualidades para el mes 1 del año 2026.', '2026-05-18 22:21:23', 1, 1),
(246, 'Nueva mensualidad disponible', 'Se han generado las mensualidades para el mes 1 del año 2026.', '2026-05-18 22:21:23', 0, 2),
(247, 'Nueva mensualidad disponible', 'Se han generado las mensualidades para el mes 1 del año 2026.', '2026-05-18 22:21:23', 0, 27),
(248, 'Nueva mensualidad disponible', 'Se han generado las mensualidades para el mes 1 del año 2026.', '2026-05-18 22:21:23', 0, 39),
(249, 'Nueva mensualidad disponible', 'Se han generado las mensualidades para el mes 1 del año 2026.', '2026-05-18 22:21:23', 0, 91),
(250, 'Nueva mensualidad disponible', 'Se han generado las mensualidades para el mes 1 del año 2026.', '2026-05-18 22:21:23', 0, 92),
(251, 'Nuevo aviso: hola', 'muy bienas, soy una publicacion desde la web', '2026-06-06 17:40:18', 1, 1),
(252, 'Nuevo aviso: hola', 'muy bienas, soy una publicacion desde la web', '2026-06-06 17:40:18', 0, 2),
(253, 'Nuevo aviso: hola', 'muy bienas, soy una publicacion desde la web', '2026-06-06 17:40:18', 0, 27),
(254, 'Nuevo aviso: hola', 'muy bienas, soy una publicacion desde la web', '2026-06-06 17:40:18', 0, 39),
(255, 'Nuevo aviso: hola', 'muy bienas, soy una publicacion desde la web', '2026-06-06 17:40:18', 0, 91),
(256, 'Nuevo aviso: hola', 'muy bienas, soy una publicacion desde la web', '2026-06-06 17:40:18', 0, 92),
(257, 'Nuevo aviso: hola', 'soy una publicacion hecha por el contador francisco', '2026-06-06 17:42:37', 1, 1),
(258, 'Nuevo aviso: hola', 'soy una publicacion hecha por el contador francisco', '2026-06-06 17:42:37', 0, 2),
(259, 'Nuevo aviso: hola', 'soy una publicacion hecha por el contador francisco', '2026-06-06 17:42:37', 0, 27),
(260, 'Nuevo aviso: hola', 'soy una publicacion hecha por el contador francisco', '2026-06-06 17:42:37', 0, 39),
(261, 'Nuevo aviso: hola', 'soy una publicacion hecha por el contador francisco', '2026-06-06 17:42:37', 0, 91),
(262, 'Nuevo aviso: hola', 'soy una publicacion hecha por el contador francisco', '2026-06-06 17:42:37', 0, 92),
(263, 'Nuevo aviso: hola', 'soy otra publicacion de francisco', '2026-06-06 17:43:52', 1, 1),
(264, 'Nuevo aviso: hola', 'soy otra publicacion de francisco', '2026-06-06 17:43:52', 0, 2),
(265, 'Nuevo aviso: hola', 'soy otra publicacion de francisco', '2026-06-06 17:43:52', 0, 27),
(266, 'Nuevo aviso: hola', 'soy otra publicacion de francisco', '2026-06-06 17:43:52', 0, 39),
(267, 'Nuevo aviso: hola', 'soy otra publicacion de francisco', '2026-06-06 17:43:52', 0, 91),
(268, 'Nuevo aviso: hola', 'soy otra publicacion de francisco', '2026-06-06 17:43:52', 0, 92),
(269, 'Nuevo aviso: publicacion', 'tercera publicacion de fran', '2026-06-06 18:01:14', 0, 1),
(270, 'Nuevo aviso: publicacion', 'tercera publicacion de fran', '2026-06-06 18:01:14', 0, 2),
(271, 'Nuevo aviso: publicacion', 'tercera publicacion de fran', '2026-06-06 18:01:14', 0, 27),
(272, 'Nuevo aviso: publicacion', 'tercera publicacion de fran', '2026-06-06 18:01:14', 0, 39),
(273, 'Nuevo aviso: publicacion', 'tercera publicacion de fran', '2026-06-06 18:01:15', 0, 91),
(274, 'Nuevo aviso: publicacion', 'tercera publicacion de fran', '2026-06-06 18:01:15', 0, 92),
(275, 'Nuevo aviso: cuarta', 'cuarta publicacion del contador francisco', '2026-06-06 18:08:23', 0, 1),
(276, 'Nuevo aviso: cuarta', 'cuarta publicacion del contador francisco', '2026-06-06 18:08:23', 0, 2),
(277, 'Nuevo aviso: cuarta', 'cuarta publicacion del contador francisco', '2026-06-06 18:08:23', 0, 27),
(278, 'Nuevo aviso: cuarta', 'cuarta publicacion del contador francisco', '2026-06-06 18:08:23', 0, 39),
(279, 'Nuevo aviso: cuarta', 'cuarta publicacion del contador francisco', '2026-06-06 18:08:23', 0, 91),
(280, 'Nuevo aviso: cuarta', 'cuarta publicacion del contador francisco', '2026-06-06 18:08:23', 0, 92),
(281, 'Nuevo aviso: quinta', 'quinta publicacion de francisco', '2026-06-06 18:33:31', 1, 1),
(282, 'Nuevo aviso: quinta', 'quinta publicacion de francisco', '2026-06-06 18:33:32', 0, 2),
(283, 'Nuevo aviso: quinta', 'quinta publicacion de francisco', '2026-06-06 18:33:32', 0, 27),
(284, 'Nuevo aviso: quinta', 'quinta publicacion de francisco', '2026-06-06 18:33:32', 0, 39),
(285, 'Nuevo aviso: quinta', 'quinta publicacion de francisco', '2026-06-06 18:33:32', 0, 91),
(286, 'Nuevo aviso: quinta', 'quinta publicacion de francisco', '2026-06-06 18:33:32', 0, 92),
(287, 'Nuevo aviso: sexta', 'sexta publicacion de francisco', '2026-06-06 18:37:37', 1, 1),
(288, 'Nuevo aviso: sexta', 'sexta publicacion de francisco', '2026-06-06 18:37:37', 0, 2),
(289, 'Nuevo aviso: sexta', 'sexta publicacion de francisco', '2026-06-06 18:37:37', 0, 27),
(290, 'Nuevo aviso: sexta', 'sexta publicacion de francisco', '2026-06-06 18:37:37', 0, 39),
(291, 'Nuevo aviso: sexta', 'sexta publicacion de francisco', '2026-06-06 18:37:37', 0, 91),
(292, 'Nuevo aviso: sexta', 'sexta publicacion de francisco', '2026-06-06 18:37:37', 0, 92),
(293, 'Nuevo aviso: septima ', 'septima notificacion de francisco', '2026-06-06 18:50:42', 0, 1),
(294, 'Nuevo aviso: septima ', 'septima notificacion de francisco', '2026-06-06 18:50:42', 0, 2),
(295, 'Nuevo aviso: septima ', 'septima notificacion de francisco', '2026-06-06 18:50:42', 0, 27),
(296, 'Nuevo aviso: septima ', 'septima notificacion de francisco', '2026-06-06 18:50:42', 0, 39),
(297, 'Nuevo aviso: septima ', 'septima notificacion de francisco', '2026-06-06 18:50:42', 0, 91),
(298, 'Nuevo aviso: septima ', 'septima notificacion de francisco', '2026-06-06 18:50:42', 0, 92),
(299, 'Nuevo aviso: publicacion', 'publicacion del administrador', '2026-06-07 17:13:02', 0, 1),
(300, 'Nuevo aviso: publicacion', 'publicacion del administrador', '2026-06-07 17:13:02', 0, 2),
(301, 'Nuevo aviso: publicacion', 'publicacion del administrador', '2026-06-07 17:13:02', 0, 27),
(302, 'Nuevo aviso: publicacion', 'publicacion del administrador', '2026-06-07 17:13:02', 0, 39),
(303, 'Nuevo aviso: publicacion', 'publicacion del administrador', '2026-06-07 17:13:02', 0, 91),
(304, 'Nuevo aviso: publicacion', 'publicacion del administrador', '2026-06-07 17:13:02', 0, 92),
(305, 'Nuevo aviso: publicacion', 'publicacion del administrador', '2026-06-07 17:18:43', 0, 1),
(306, 'Nuevo aviso: publicacion', 'publicacion del administrador', '2026-06-07 17:18:43', 0, 2),
(307, 'Nuevo aviso: publicacion', 'publicacion del administrador', '2026-06-07 17:18:43', 0, 27),
(308, 'Nuevo aviso: publicacion', 'publicacion del administrador', '2026-06-07 17:18:43', 0, 39),
(309, 'Nuevo aviso: publicacion', 'publicacion del administrador', '2026-06-07 17:18:43', 0, 91),
(310, 'Nuevo aviso: publicacion', 'publicacion del administrador', '2026-06-07 17:18:43', 0, 92),
(311, 'Nuevo Pago Registrado', 'Se ha registrado un pago a través del portal web. Requiere revisión y aprobación.', '2026-06-07 17:40:28', 1, 1),
(312, 'Nuevo Pago Registrado', 'Se ha registrado un pago a través del portal web. Requiere revisión y aprobación.', '2026-06-07 17:40:28', 0, 39),
(313, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:30:19', 0, 1),
(314, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:30:19', 0, 2),
(315, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:30:19', 0, 27),
(316, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:30:19', 0, 39),
(317, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:30:19', 0, 91),
(318, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:30:19', 0, 92),
(319, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:30:54', 0, 1),
(320, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:30:54', 0, 2),
(321, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:30:54', 0, 27),
(322, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:30:54', 0, 39),
(323, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:30:54', 0, 91),
(324, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:30:54', 0, 92),
(325, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:32:06', 0, 1),
(326, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:32:06', 0, 2),
(327, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:32:06', 0, 27),
(328, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:32:06', 0, 39),
(329, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:32:06', 0, 91),
(330, 'Nuevo aviso: octava', 'octava publicacion de francico', '2026-06-07 18:32:06', 0, 92),
(331, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 11:48:18', 1, 1),
(332, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 11:48:18', 0, 39),
(333, 'Nuevo aviso: Atencion', 'Necesitamos mejorar el diseño de los detalles ', '2026-06-08 12:27:52', 1, 1),
(334, 'Nuevo aviso: Atencion', 'Necesitamos mejorar el diseño de los detalles ', '2026-06-08 12:27:52', 0, 2),
(335, 'Nuevo aviso: Atencion', 'Necesitamos mejorar el diseño de los detalles ', '2026-06-08 12:27:52', 0, 27),
(336, 'Nuevo aviso: Atencion', 'Necesitamos mejorar el diseño de los detalles ', '2026-06-08 12:27:52', 0, 39),
(337, 'Nuevo aviso: Atencion', 'Necesitamos mejorar el diseño de los detalles ', '2026-06-08 12:27:52', 0, 91),
(338, 'Nuevo aviso: Atencion', 'Necesitamos mejorar el diseño de los detalles ', '2026-06-08 12:27:52', 0, 92),
(339, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 18:35:23', 0, 1),
(340, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 18:35:23', 0, 39),
(341, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 18:40:15', 0, 1),
(342, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 18:40:15', 0, 39),
(343, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 18:41:36', 0, 1),
(344, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 18:41:36', 0, 39),
(345, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 18:50:44', 0, 1),
(346, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 18:50:44', 0, 39),
(347, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 18:51:33', 0, 1),
(348, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 18:51:33', 0, 39),
(349, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 18:56:35', 0, 1),
(350, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 18:56:35', 0, 39),
(351, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 19:00:06', 0, 1),
(352, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 19:00:06', 0, 39),
(353, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 19:00:33', 0, 1),
(354, 'Nuevo Pago Registrado', 'Requiere revisión y aprobación.', '2026-06-08 19:00:33', 0, 39);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion_evento`
--

CREATE TABLE `notificacion_evento` (
  `notificacion_id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificacion_evento`
--

INSERT INTO `notificacion_evento` (`notificacion_id`, `evento_id`) VALUES
(239, 32),
(240, 32),
(241, 32),
(242, 32),
(243, 32),
(244, 32),
(245, 33),
(246, 33),
(247, 33),
(248, 33),
(249, 33),
(250, 33),
(251, 34),
(252, 34),
(253, 34),
(254, 34),
(255, 34),
(256, 34),
(257, 35),
(258, 35),
(259, 35),
(260, 35),
(261, 35),
(262, 35),
(263, 36),
(264, 36),
(265, 36),
(266, 36),
(267, 36),
(268, 36),
(269, 37),
(270, 37),
(271, 37),
(272, 37),
(273, 37),
(274, 37),
(275, 38),
(276, 38),
(277, 38),
(278, 38),
(279, 38),
(280, 38),
(281, 39),
(282, 39),
(283, 39),
(284, 39),
(285, 39),
(286, 39),
(287, 40),
(288, 40),
(289, 40),
(290, 40),
(291, 40),
(292, 40),
(293, 41),
(294, 41),
(295, 41),
(296, 41),
(297, 41),
(298, 41),
(299, 42),
(300, 42),
(301, 42),
(302, 42),
(303, 42),
(304, 42),
(305, 43),
(306, 43),
(307, 43),
(308, 43),
(309, 43),
(310, 43),
(311, 44),
(312, 44),
(313, 45),
(314, 45),
(315, 45),
(316, 45),
(317, 45),
(318, 45),
(319, 46),
(320, 46),
(321, 46),
(322, 46),
(323, 46),
(324, 46),
(325, 47),
(326, 47),
(327, 47),
(328, 47),
(329, 47),
(330, 47),
(331, 48),
(332, 48),
(333, 49),
(334, 49),
(335, 49),
(336, 49),
(337, 49),
(338, 49),
(339, 50),
(340, 50),
(341, 51),
(342, 51),
(343, 52),
(344, 52),
(345, 53),
(346, 53),
(347, 54),
(348, 54),
(349, 55),
(350, 55),
(351, 56),
(352, 56),
(353, 57),
(354, 57);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id_permiso` int(11) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id_permiso`, `accion`, `activo`) VALUES
(1, 'REGISTRAR', 1),
(2, 'CONSULTAR', 1),
(3, 'MODIFICAR', 1),
(4, 'ELIMINAR', 1),
(90, 'errr', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_ips`
--

CREATE TABLE `registro_ips` (
  `ip` varchar(45) NOT NULL,
  `intentos` int(11) NOT NULL DEFAULT 1,
  `ultimo_intento` datetime NOT NULL DEFAULT current_timestamp(),
  `infracciones` int(11) NOT NULL DEFAULT 0
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registro_ips`
--

INSERT INTO `registro_ips` (`ip`, `intentos`, `ultimo_intento`, `infracciones`) VALUES
('127.0.0.1', 3, '2026-06-14 17:37:08', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `activo`) VALUES
(1, 'Administrador Global', 1),
(2, 'Administrador', 1),
(3, 'Propietario', 1),
(4, 'Contador', 1),
(23, 'Presidente', 1),
(68, 'new', 0),
(69, 'rebelde', 1),
(70, 'ssssa', 0),
(71, 'gasasdasdas', 0),
(72, 'asasdasd', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `suscripciones_push`
--

CREATE TABLE `suscripciones_push` (
  `id_suscripcion` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `endpoint` text NOT NULL,
  `p256dh` varchar(255) NOT NULL,
  `auth` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `suscripciones_push`
--

INSERT INTO `suscripciones_push` (`id_suscripcion`, `usuario_id`, `endpoint`, `p256dh`, `auth`, `fecha_registro`) VALUES
(1, 1, 'https://updates.push.services.mozilla.com/wpush/v2/gAAAAABp1ZseTDQjnU5lI9AUO3eTuNHUP_qj8r1xvlJZs3h9TYmjAETOu3DuVzspQCdWsiH1pXGvZE-ofcigSRPYAYj1JhNSe2Dzmp4MSjmvDKvZQeYV00zfaXuyZ-WNy0madX2qSkqrBRjsB_UrzgsBcc-h7MAdL41G3cQrc5fY6i7HsVaJD7k', 'BPSoziQjJfxaG05/wBlvkgZ+XmfRZI/7Wnjw48WyE3qDOKayqeoGLQYihJM7KtCUDASM4Rv43HTvx7CSINbphkE=', 'dVIKY/GHisXcOM5xCM90ww==', '2026-04-07 22:45:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `suscripciones_push_movil`
--

CREATE TABLE `suscripciones_push_movil` (
  `id_suscripcion_movil` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `expo_token` varchar(255) NOT NULL,
  `plataforma` enum('ANDROID','IOS') NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `suscripciones_push_movil`
--

INSERT INTO `suscripciones_push_movil` (`id_suscripcion_movil`, `usuario_id`, `expo_token`, `plataforma`, `fecha_registro`) VALUES
(1, 1, 'ExponentPushToken[5u-wmJMB4mrfEUwdS58JX_]', 'ANDROID', '2026-06-06 21:37:20'),
(2, 1, 'ExponentPushToken[hdEzyaMoeVaN5ruVlBcXqm]', 'ANDROID', '2026-06-12 21:22:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tokens_seguridad`
--

CREATE TABLE `tokens_seguridad` (
  `id_token` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` text NOT NULL,
  `fecha_expiracion` datetime NOT NULL,
  `tipo` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tokens_seguridad`
--

INSERT INTO `tokens_seguridad` (`id_token`, `usuario_id`, `token`, `fecha_expiracion`, `tipo`) VALUES
(292, 27, '746720dd632b9db52c35c86e4fbaf2ccae892e5c104fde2d47e2c43808a1f749', '2026-07-04 02:42:47', 'REFRESH_TOKEN_MOVIL'),
(306, 1, 'e036cee2293f4967487823569b098110f985bee6b9a32b61593757182bb0ddcf', '2026-07-14 15:36:20', 'REFRESH_TOKEN_MOVIL');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trafico_usuarios`
--

CREATE TABLE `trafico_usuarios` (
  `usuario_id` int(11) NOT NULL,
  `peticiones` int(11) NOT NULL DEFAULT 1,
  `ultimo_intento` datetime NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `trafico_usuarios`
--

INSERT INTO `trafico_usuarios` (`usuario_id`, `peticiones`, `ultimo_intento`) VALUES
(1, 2, '2026-06-14 17:32:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasenia` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasenia`, `rol_id`, `activo`) VALUES
(1, 'Jesus', 'Escalona', 'administrador@gmail.com', '$2y$10$PSQuQ6JSX.UcGDlV8w4oauDJkL9o7d06f7AFZc4QCH9xAd2VSHA/G', 1, 1),
(2, 'francisco', 'mendoza', 'franj@gmail.com', '$2y$10$0KoHFVefo2ZZPv/nh0ocaefcDxfbOKXxcVhnUj844WynuyGhWpaV.', 4, 1),
(27, 'Pepes', 'Campos', 'pepe@gmail.com', '$2y$10$WWp8M1SADJzTWAg910K.mewfZFglQF77ENnqPYLmq1U9AKmmeruY2', 3, 1),
(39, 'Yhsius', 'asdasd', 'jesusgescalonae@gmail.com', '$2y$10$o5jicwKwqPcPyZmciN/pvOokS9MSOUghVCZMB5aHh3YQ3qZ5oSs8G', 2, 1),
(53, 'perfil editado', 'perfil editado', 'UsuarioperfilEditada@gmail.com', '$2y$10$AzKv19h61AeAkEYPA/FSA.buvyhYKoRfHT/kUFgMDSWE11PKjpBLS', 4, 0),
(54, 'usuario', 'cambiocontra', 'cambiocontrasenia@gmail.com', '$2y$10$soYFxka95IzptEPe5eA.IONdFJI/geOcpt0K/L7aAKNIsTyn.5Nd2', 23, 0),
(89, 'pepe', 'puias', 'pepa@gmail.com', '$2y$10$GtV9.reiR/8A/NindSEEUOtCPjAs.lLxS67Qp9ZNtG6Ug3wzd5nXi', 1, 0),
(90, 'asdasd', 'asdasd', 'asdas@ad.com', '$2y$10$tTc2q0y3G8sZ8glF3vyDiO.b3DqLtm/TJ/HTJl8QZM2pforDC0TdO', 1, 0),
(91, 'test', 'test', 'test@gmail.com', '$2y$10$M/b/1lwlXVk7g0bHNfBiIOZ1XM0OQIB6SU5GPG2SL0JnhTQvD4Dqu', 69, 1),
(92, 'presi', 'presi', 'presi@gmail.com', '$2y$10$mE8PllA/a3G4ecc.UIZi4u9771m5QBZ3vk/7jWRutkQooM5rD1ryG', 23, 1),
(93, 'test', 'test', 'tests@gmail.com', '$2y$10$apVDsr/j3IR3lOovWTAgje3Czb4e1mmPXBAedAzQnkvGD1PVkF9ia', 4, 0);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_perfiles_usuarios`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_perfiles_usuarios` (
`id_usuario` int(11)
,`nombre` varchar(50)
,`apellido` varchar(50)
,`correo` varchar(100)
,`contrasenia` varchar(255)
,`activo` tinyint(1)
,`rol_id` int(11)
,`nombre_rol` varchar(50)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_perfiles_usuarios`
--
DROP TABLE IF EXISTS `vw_perfiles_usuarios`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_perfiles_usuarios`  AS SELECT `u`.`id_usuario` AS `id_usuario`, `u`.`nombre` AS `nombre`, `u`.`apellido` AS `apellido`, `u`.`correo` AS `correo`, `u`.`contrasenia` AS `contrasenia`, `u`.`activo` AS `activo`, `u`.`rol_id` AS `rol_id`, `r`.`nombre` AS `nombre_rol` FROM (`usuarios` `u` join `roles` `r` on(`u`.`rol_id` = `r`.`id_rol`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignacion_permisos`
--
ALTER TABLE `asignacion_permisos`
  ADD PRIMARY KEY (`rol_id`,`permiso_id`,`modulo_id`) USING BTREE,
  ADD KEY `asignacion_permisos_ibfk_1` (`modulo_id`),
  ADD KEY `asignacion_permisos_ibfk_2` (`permiso_id`);

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id_bitacora`),
  ADD KEY `bitacora_ibfk_1` (`usuario_id`),
  ADD KEY `bitacora_ibfk_2` (`modulo_id`);

--
-- Indices de la tabla `cartelera_virtual`
--
ALTER TABLE `cartelera_virtual`
  ADD PRIMARY KEY (`id_cartelera`),
  ADD KEY `cartelera_virtual_ibfk_1` (`usuario_id`);

--
-- Indices de la tabla `claves_sesion`
--
ALTER TABLE `claves_sesion`
  ADD PRIMARY KEY (`dispositivo_id`),
  ADD KEY `idx_usuario_sesion` (`usuario_id`);

--
-- Indices de la tabla `eventos_sistema`
--
ALTER TABLE `eventos_sistema`
  ADD PRIMARY KEY (`id_evento`);

--
-- Indices de la tabla `intentos_login`
--
ALTER TABLE `intentos_login`
  ADD PRIMARY KEY (`usuario_id`);

--
-- Indices de la tabla `listas_acceso_ip`
--
ALTER TABLE `listas_acceso_ip`
  ADD PRIMARY KEY (`id_lista`),
  ADD UNIQUE KEY `idx_ip_unica` (`ip`);

--
-- Indices de la tabla `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`id_modulo`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `notificaciones_ibfk_1` (`usuario_id`);

--
-- Indices de la tabla `notificacion_evento`
--
ALTER TABLE `notificacion_evento`
  ADD PRIMARY KEY (`notificacion_id`,`evento_id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id_permiso`);

--
-- Indices de la tabla `registro_ips`
--
ALTER TABLE `registro_ips`
  ADD PRIMARY KEY (`ip`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `suscripciones_push`
--
ALTER TABLE `suscripciones_push`
  ADD PRIMARY KEY (`id_suscripcion`),
  ADD KEY `suscripciones_push_ibfk_1` (`usuario_id`);

--
-- Indices de la tabla `suscripciones_push_movil`
--
ALTER TABLE `suscripciones_push_movil`
  ADD PRIMARY KEY (`id_suscripcion_movil`),
  ADD UNIQUE KEY `expo_token_unico` (`expo_token`),
  ADD KEY `fk_suscripcion_movil_usuario` (`usuario_id`);

--
-- Indices de la tabla `tokens_seguridad`
--
ALTER TABLE `tokens_seguridad`
  ADD PRIMARY KEY (`id_token`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `trafico_usuarios`
--
ALTER TABLE `trafico_usuarios`
  ADD PRIMARY KEY (`usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `usuarios_ibfk_1` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id_bitacora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5123;

--
-- AUTO_INCREMENT de la tabla `cartelera_virtual`
--
ALTER TABLE `cartelera_virtual`
  MODIFY `id_cartelera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT de la tabla `eventos_sistema`
--
ALTER TABLE `eventos_sistema`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de la tabla `listas_acceso_ip`
--
ALTER TABLE `listas_acceso_ip`
  MODIFY `id_lista` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id_modulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=355;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de la tabla `suscripciones_push`
--
ALTER TABLE `suscripciones_push`
  MODIFY `id_suscripcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `suscripciones_push_movil`
--
ALTER TABLE `suscripciones_push_movil`
  MODIFY `id_suscripcion_movil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tokens_seguridad`
--
ALTER TABLE `tokens_seguridad`
  MODIFY `id_token` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=307;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignacion_permisos`
--
ALTER TABLE `asignacion_permisos`
  ADD CONSTRAINT `asignacion_permisos_ibfk_1` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asignacion_permisos_ibfk_2` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id_permiso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asignacion_permisos_ibfk_3` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `bitacora_ibfk_2` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id_modulo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `cartelera_virtual`
--
ALTER TABLE `cartelera_virtual`
  ADD CONSTRAINT `cartelera_virtual_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `claves_sesion`
--
ALTER TABLE `claves_sesion`
  ADD CONSTRAINT `fk_claves_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `intentos_login`
--
ALTER TABLE `intentos_login`
  ADD CONSTRAINT `intentos_login_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificacion_evento`
--
ALTER TABLE `notificacion_evento`
  ADD CONSTRAINT `notificacion_evento_ibfk_1` FOREIGN KEY (`notificacion_id`) REFERENCES `notificaciones` (`id_notificacion`) ON DELETE CASCADE,
  ADD CONSTRAINT `notificacion_evento_ibfk_2` FOREIGN KEY (`evento_id`) REFERENCES `eventos_sistema` (`id_evento`) ON DELETE CASCADE;

--
-- Filtros para la tabla `suscripciones_push`
--
ALTER TABLE `suscripciones_push`
  ADD CONSTRAINT `suscripciones_push_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `suscripciones_push_movil`
--
ALTER TABLE `suscripciones_push_movil`
  ADD CONSTRAINT `fk_suscripcion_movil_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tokens_seguridad`
--
ALTER TABLE `tokens_seguridad`
  ADD CONSTRAINT `tokens_seguridad_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
