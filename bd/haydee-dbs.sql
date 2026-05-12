-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-05-2026 a las 04:10:56
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET FOREIGN_KEY_CHECKS=0;
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
CREATE DEFINER=`app_condominio`@`localhost` PROCEDURE `gestionar_anio_fiscal` ()   BEGIN
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
END$$

CREATE DEFINER=`app_condominio`@`localhost` PROCEDURE `sp_gestion_caja_chica_mensual` ()   sp_block: BEGIN 
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
END$$

CREATE DEFINER=`app_condominio`@`localhost` PROCEDURE `sp_registrar_reposicion_caja` (IN `p_monto_reposicion` DECIMAL(15,2), IN `p_caja_id` INT)   sp_block: BEGIN
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
END$$

CREATE DEFINER=`app_condominio`@`localhost` PROCEDURE `sp_sincronizar_presupuestos_mensualidad` (IN `p_mensualidad_id` INT, IN `p_nuevos_presupuestos_ids` TEXT)   BEGIN
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
  `descripcion` varchar(255) NOT NULL DEFAULT 'Sin descripción',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `anio_fiscal`
--

INSERT INTO `anio_fiscal` (`id_anio_fiscal`, `estado`, `fecha_inicio`, `fecha_cierre`, `descripcion`, `activo`) VALUES
(47, 'Abierto', '2026-02-07', '2027-02-07', 'Año fiscal 2026', 1),
(52, 'Cerrada', '2026-03-12', '2027-03-12', 'asdas', 0),
(53, 'Cerrada', '2026-03-05', '2027-03-05', 'AAA', 0),
(54, 'Cerrada', '2026-03-14', '2027-03-14', 'nueva des', 0),
(55, 'Cerrada', '2026-03-13', '2027-03-13', 'asda', 0),
(56, 'Cerrada', '2026-03-16', '2027-03-16', '', 0),
(57, 'Cerrada', '2026-03-02', '2027-03-02', '', 0),
(58, 'Cerrada', '2026-03-10', '2027-03-10', 'asd', 0),
(59, 'Cerrada', '2026-04-07', '2027-04-07', 'pepe', 0),
(60, 'Cerrada', '2026-04-25', '2027-04-25', 'ss', 0),
(61, 'Cerrada', '2025-04-25', '2026-04-25', '', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `apartamentos`
--

CREATE TABLE `apartamentos` (
  `id_apartamento` int(11) NOT NULL,
  `nro_apartamento` varchar(3) NOT NULL,
  `porcentaje_participacion` float NOT NULL,
  `gas` tinyint(1) NOT NULL,
  `agua` tinyint(1) NOT NULL,
  `alquilado` tinyint(1) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `apartamentos`
--

INSERT INTO `apartamentos` (`id_apartamento`, `nro_apartamento`, `porcentaje_participacion`, `gas`, `agua`, `alquilado`, `activo`) VALUES
(30, '1-2', 22, 2, 1, 2, 1),
(31, '2-3', 23, 1, 1, 1, 1),
(32, '2-1', 1, 2, 1, 1, 1),
(33, '12', 23, 1, 1, 1, 0),
(34, '2-8', 2, 1, 2, 1, 0),
(35, '3-1', 5, 2, 1, 1, 1),
(36, '2-5', 52, 1, 1, 1, 0),
(37, '4-1', 5, 1, 1, 2, 1),
(38, '4-2', 5, 1, 1, 2, 0),
(39, '3-2', 1, 2, 1, 1, 1),
(40, '', 0, 0, 0, 0, 0),
(41, '5-1', 5, 1, 1, 1, 1);

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
  `telefono_afiliado` varchar(50) NOT NULL,
  `rif` varchar(20) NOT NULL,
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
  `descripcion` varchar(255) NOT NULL DEFAULT 'Sin descripción',
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
(27, 1000.00, 'Abierto', 'Caja chicas del mes Mayo - 2026', '2026-05-01', 47, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_gastos`
--

CREATE TABLE `detalles_gastos` (
  `id_detalle_gasto` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `monto_dolar` decimal(15,2) NOT NULL,
  `metodo_pago` varchar(20) NOT NULL,
  `gasto_id` int(11) NOT NULL,
  `descripcion_detalle_gasto` varchar(255) NOT NULL DEFAULT 'Sin descripción'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_gastos`
--

INSERT INTO `detalles_gastos` (`id_detalle_gasto`, `fecha`, `monto`, `monto_dolar`, `metodo_pago`, `gasto_id`, `descripcion_detalle_gasto`) VALUES
(164, '2026-02-03', 12.00, 0.00, 'Pago Movil', 133, 'asdasdasdasdasd'),
(217, '2026-02-21', 12.00, 0.00, 'Efectivo', 136, 'adasdas'),
(218, '2026-02-21', 900.00, 0.00, 'Efectivo', 135, 'Detalle de reposición por monto de: 900.00'),
(2001, '2025-12-20', 150.00, 4.00, 'Transferencia', 201, 'Mes diciembre'),
(2002, '2026-01-15', 700.00, 20.00, 'Transferencia', 202, 'Quincena 1'),
(2003, '2026-01-25', 1500.00, 40.00, 'Divisa', 203, 'Repuestos bomba'),
(2004, '2026-02-15', 720.00, 20.00, 'Pago Movil', 204, 'Quincena 1 feb'),
(2005, '2026-03-02', 300.00, 8.00, 'Efectivo', 205, 'Limpieza pasillos'),
(2006, '2026-03-05', 43.00, 0.00, 'Efectivo', 206, 'xxxxxxxxxxxxxxxxxxxx'),
(2011, '2026-03-08', 12312.00, 0.00, 'Efectivo', 207, 'prueba detalle 1'),
(2012, '2026-03-07', 21.00, 0.00, 'Pago Movil', 207, 'prueba detalle 2'),
(2017, '2026-03-16', 910.00, 0.00, 'Efectivo', 208, 'Detalle de reposición por monto de: 910.00'),
(2018, '2026-02-21', 32.00, 0.00, 'Efectivo', 134, 'detalle 1 s'),
(2019, '2026-02-06', 12.00, 0.00, 'Pago Movil', 134, 'detalle 2s'),
(2020, '2026-03-17', 0.00, 0.00, 'Efectivo', 209, 'Detalle de reposición por monto de: 0.00'),
(2021, '2026-03-17', 0.00, 0.00, 'Efectivo', 210, 'Detalle de reposición por monto de: 0.00'),
(2022, '2026-02-05', 12.00, 0.00, 'Pago Movil', 132, 'adiossssssssssssssss'),
(2023, '2026-03-17', 15.00, 0.00, 'Efectivo', 211, 'asdasd'),
(2024, '2026-03-17', 10.00, 0.00, 'Transferencia', 211, 'asddasdas'),
(2026, '2026-03-11', 123.00, 0.00, 'Pago Movil', 212, 'assas'),
(2027, '2026-04-28', 28.00, 0.00, 'Efectivo', 213, 'Lhlfld'),
(2028, '2026-04-30', 22.00, 0.00, 'Efectivo', 214, 'Nzdnd'),
(2029, '2026-05-01', 1.00, 0.00, 'Efectivo', 215, 'Sks');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_pagos`
--

CREATE TABLE `detalles_pagos` (
  `id_detalle_pago` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `monto_dolar` decimal(15,2) NOT NULL,
  `tipo_pago` varchar(20) NOT NULL,
  `pago_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_pagos`
--

INSERT INTO `detalles_pagos` (`id_detalle_pago`, `fecha`, `monto`, `monto_dolar`, `tipo_pago`, `pago_id`) VALUES
(362, '2026-02-01', 10.00, 0.03, 'Pago Movil', 116),
(370, '2026-02-14', 10.00, 0.02, 'Pago Movil', 118),
(371, '2026-02-21', 100.00, 0.25, 'Efectivo', 118),
(382, '2026-02-22', 12.00, 0.03, 'Pago Movil', 119),
(383, '2026-02-01', 12.00, 1.00, 'Efectivo', 119),
(384, '2026-02-05', 12.00, 0.03, 'Pago Movil', 117),
(385, '2026-02-04', 100.00, 0.25, 'Transferencia', 117),
(386, '2026-02-20', 100.00, 0.25, 'Efectivo', 117),
(1001, '2025-12-15', 350.00, 10.00, 'Pago Movil', 101),
(1002, '2026-01-10', 360.00, 10.00, 'Transferencia', 102),
(1003, '2026-02-05', 180.00, 5.00, 'Efectivo', 103),
(1004, '2026-02-28', 360.00, 10.00, 'Divisa', 104),
(1005, '2026-03-01', 365.00, 10.00, 'Pago Movil', 105),
(1007, '2026-03-05', 800.00, 1.87, 'Transferencia', 121),
(1008, '2026-03-05', 4.00, 0.01, 'Efectivo', 122),
(1010, '2026-03-07', 10.00, 0.02, 'Efectivo', 123),
(1012, '2026-02-25', 1.40, 0.00, 'Efectivo', 120),
(1014, '2026-03-04', 10.00, 0.02, 'Efectivo', 124),
(1016, '2026-03-04', 10.00, 0.00, 'Pago Movil', 127),
(1017, '2026-03-05', 10.00, 0.00, 'Transferencia', 128),
(1018, '2026-03-18', 23.00, 0.00, 'Pago Movil', 129),
(1020, '2026-03-18', 23.00, 0.00, 'Pago Movil', 131),
(1022, '2026-03-04', 50.00, 0.00, 'Efectivo', 132),
(1023, '2026-03-25', 50.00, 0.00, 'Transferencia', 132),
(1024, '2026-04-04', 224.33, 0.00, 'Pago Movil', 133),
(1025, '2026-04-04', 100.00, 0.00, 'Efectivo', 134),
(1028, '2026-04-28', 777.00, 0.00, 'Pago Movil', 137),
(1029, '2026-04-29', 10.00, 0.00, 'Transferencia', 138);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_presupuesto`
--

CREATE TABLE `detalles_presupuesto` (
  `id_detalle_presupuesto` int(11) NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `monto_dolar` decimal(15,2) NOT NULL,
  `nombre_detalle` varchar(50) NOT NULL,
  `presupuesto_id` int(11) NOT NULL,
  `tipo_gasto_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_presupuesto`
--

INSERT INTO `detalles_presupuesto` (`id_detalle_presupuesto`, `monto`, `monto_dolar`, `nombre_detalle`, `presupuesto_id`, `tipo_gasto_id`) VALUES
(1327, 15.00, 0.00, 'CORPOELEC', 106, 2),
(1328, 12.00, 0.00, 'HIDROLARA', 106, 2),
(1329, 100.00, 0.00, 'Trabajadora Residencial', 106, 3),
(1330, 100.00, 0.00, 'Bono de alimentacion', 106, 3),
(1331, 100.00, 0.00, 'Bono de ayuda', 106, 3),
(1332, 100.00, 0.00, 'Seguridad Social', 106, 3),
(1333, 100.00, 0.00, 'Mantenimiento ascensor', 106, 4),
(1334, 100.00, 0.00, 'GAS LARA', 106, 5),
(1335, 100.00, 0.00, 'Bolsas de Basura', 106, 9),
(1336, 100.00, 0.00, 'Productos de Limpieza', 106, 9),
(1337, 100.00, 0.00, 'Comisiones Bancarias', 106, 10),
(1338, 100.00, 0.00, 'Exencion cuota del administrador', 106, 10),
(1351, 23.00, 0.00, 'CORPOELEC', 107, 2),
(1352, 15.00, 0.00, 'HIDROLARA', 107, 2),
(1353, 233.00, 0.00, 'Trabajadora Residencial', 107, 3),
(1354, 12.00, 0.00, 'Bono de alimentacion', 107, 3),
(1355, 12.00, 0.00, 'Bono de ayuda', 107, 3),
(1356, 100.00, 0.00, 'Seguridad Social', 107, 3),
(1357, 12.00, 0.00, 'Mantenimiento ascensor', 107, 4),
(1358, 200.00, 0.00, 'GAS LARA', 107, 5),
(1359, 20.00, 0.00, 'Bolsas de Basura', 107, 9),
(1360, 200.00, 0.00, 'Productos de Limpieza', 107, 9),
(1361, 200.00, 0.00, 'Comisiones Bancarias', 107, 10),
(1362, 200.00, 0.00, 'Exencion cuota del administrador', 107, 10),
(1387, 12.00, 0.00, 'Exencion cuota del administrador', 110, 10),
(1388, 20.00, 0.00, 'Bombillos pasillo', 106, 1);

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
('123123123', 'CSS-Logo_1771706140_757.jpg', 1, 2022),
('1235412', 'CSS-Logo_1773003460_864.jpg', 6, 2012),
('412123', 'mensualidad_1771710356_291.PNG', 6, 2019),
('543524', 'images__2__1773805029_187.png', 9, 2024),
('5435245', 'images__2__1774719607_329.png', 9, 2026);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id_gasto` int(11) NOT NULL,
  `clasificacion` varchar(20) NOT NULL,
  `tipo_gasto_id` int(11) NOT NULL,
  `solicitud_id` int(11) DEFAULT NULL,
  `proveedor_id` int(11) NOT NULL,
  `descripcion_gasto` text NOT NULL DEFAULT 'Sin descripción',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gastos`
--

INSERT INTO `gastos` (`id_gasto`, `clasificacion`, `tipo_gasto_id`, `solicitud_id`, `proveedor_id`, `descripcion_gasto`, `activo`) VALUES
(132, 'fijo', 2, NULL, 2, 'holasssssssssssssssaaa', 1),
(133, 'fijo', 2, NULL, 3, 'asdasdasasd', 0),
(134, 'fijo', 2, NULL, 3, '22222a2222222224', 1),
(135, 'reposicion', 1, NULL, 1, 'Reposición de Caja Chica - 22/02/2026', 1),
(136, 'fijo', 2, NULL, 3, 'wwwwwwwwwwwww', 0),
(201, 'Fijo', 1, NULL, 1, 'Pago de servicio de agua', 0),
(202, 'Fijo', 2, NULL, 1, 'Honorarios de vigilancia Enero', 0),
(203, 'Variable', 3, NULL, 1, 'Reparación de bomba de agua', 1),
(204, 'Fijo', 2, NULL, 1, 'Honorarios de vigilancia Febrero', 1),
(205, 'Variable', 4, NULL, 1, 'Compra de artículos de limpieza', 1),
(206, 'fijo', 1, NULL, 2, 'zzzzzzzzzzzzzzzzz', 0),
(207, 'variable', 2, NULL, 3, 'prueba de gasto 1', 1),
(208, 'reposicion', 1, NULL, 1, 'Reposición de Caja Chica - 16/03/2026', 1),
(209, 'reposicion', 1, NULL, 1, 'Reposición de Caja Chica - 17/03/2026', 1),
(210, 'reposicion', 1, NULL, 1, 'Reposición de Caja Chica - 17/03/2026', 1),
(211, 'variable', 2, NULL, 3, 'asdasdasdasd', 1),
(212, 'fijo', 1, NULL, 3, 'asdasdasdasd', 1),
(213, 'Variable', 1, NULL, 1, 'Lhlfld', 1),
(214, 'Variable', 1, NULL, 1, 'Nzdnd', 1),
(215, 'Variable', 1, NULL, 1, 'Sks', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitantes`
--

CREATE TABLE `habitantes` (
  `id_habitante` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `apellido` varchar(20) NOT NULL,
  `cedula` varchar(9) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `correo` varchar(50) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `sexo` varchar(10) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habitantes`
--

INSERT INTO `habitantes` (`id_habitante`, `nombre`, `apellido`, `cedula`, `telefono`, `correo`, `fecha_nacimiento`, `sexo`, `activo`) VALUES
(43, 'jesus', 'asdasda', 'E1105510', '12312312123', 'aaa@gasd.com', '2000-12-12', 'Masculino', 1),
(44, 'jesa', 'asdasd', 'V15321212', '21312312121', 'asda@asasd.ocm', '2000-10-10', 'Masculino', 1),
(45, 'asdasd', 'asdasdas', 'V12012120', '23423423434', 'ASDASD@sfas.com', '1980-10-10', 'Femenino', 1),
(46, 'papap', 'lalala', 'V23424234', '21312312312', 'lala@gasmic.com', '1950-10-10', 'Masculino', 1),
(47, 'ssdfsdf', 'asda', 'V23432423', '23423423423', 'asdasda@asfas.com', '1945-10-10', 'Femenino', 1),
(48, 'boor', 'borra', 'V23423234', '23654564321', 'asd@asd.com', '1999-01-01', 'Masculino', 0),
(49, 'asa', 'asdasd', 'V2343121', '12313455648', 'asda@adsd.com', '1999-10-10', 'Masculino', 0),
(50, 'asa', 'asdasd', 'V23432121', '12313455648', 'asda@adaassdsd.com', '1999-10-10', 'Masculino', 0),
(51, 'fhfgh', 'asdasd', 'V2342342', '42342342342', 'asdasd@asd.com', '1999-10-10', 'Masculino', 1),
(52, 'dasdas', 'asdasd', 'E12312313', '22342323232', 'ada@asd.com', '2000-10-10', 'Femenino', 0);

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
('45342', 'cog_1770410107_961.PNG', 1, 362),
('12312', 'fiabil_1771818751_885.PNG', 1, 382),
('REF-123456', '', 1, 1001),
('REF-987654', '', 1, 1002),
('REF-555666', '', 1, 1005),
('18685', '3a2ad720-ccf0-49ff-90c2-81b0c1f4de08_1777419456_975.jpeg', 1, 1028),
('5355655', 'a1e73341-5842-4406-8f25-2d659eaef214_1777429536_273.jpeg', 1, 1029),
('2312', 'virustotla_1771651369_672.PNG', 6, 370),
('4213123', 'lenguaje_comun_1771707846_116.PNG', 6, 384),
('4213', 'CSS-Logo_1772718346_856.jpg', 6, 1007),
('678678', 'default.png', 6, 1016),
('532423', 'default.png', 6, 1018),
('532434345', 'colores_inicio_1_1773856062_104.PNG', 6, 1020),
('234sad2', 'colores_inicio_2_1771708881_962.PNG', 9, 385),
('45654', 'default.png', 9, 1017),
('23423412', 'lenguaje_comun_1774402497_922.PNG', 9, 1023),
('5231234', 'meme_1775350129_999.PNG', 13, 1024);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensualidad`
--

CREATE TABLE `mensualidad` (
  `id_mensualidad` int(11) NOT NULL,
  `periodo_id` int(11) NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `apartamento_id` int(11) NOT NULL,
  `porcentaje_interes` int(11) NOT NULL DEFAULT 10,
  `limite_mensualidad` int(11) NOT NULL DEFAULT 15,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensualidad`
--

INSERT INTO `mensualidad` (`id_mensualidad`, `periodo_id`, `monto`, `apartamento_id`, `porcentaje_interes`, `limite_mensualidad`, `activo`) VALUES
(610, 1, 155.00, 30, 10, 15, 1),
(611, 1, 223.21, 31, 10, 15, 1),
(612, 1, 46.35, 35, 10, 15, 1),
(613, 1, 9.27, 39, 10, 15, 1),
(614, 1, 46.35, 37, 10, 15, 1),
(615, 1, 9.27, 32, 10, 15, 1),
(616, 2, 2.64, 30, 10, 15, 0),
(617, 2, 48.76, 31, 10, 15, 0),
(618, 2, 0.12, 32, 10, 15, 0),
(619, 2, 0.60, 35, 10, 15, 0),
(620, 2, 10.60, 37, 10, 15, 0),
(621, 2, 0.12, 39, 10, 15, 0),
(622, 3, 5035.58, 30, 10, 15, 0),
(623, 3, 5264.47, 31, 10, 15, 0),
(624, 3, 228.89, 32, 10, 15, 0),
(625, 3, 1144.45, 35, 10, 15, 0),
(626, 3, 1144.45, 37, 10, 15, 0),
(627, 3, 228.89, 39, 10, 15, 0),
(628, 3, 29.04, 30, 10, 15, 0),
(629, 3, 30.36, 31, 10, 15, 0),
(630, 3, 1.32, 32, 10, 15, 0),
(631, 3, 6.60, 35, 10, 15, 0),
(632, 3, 6.60, 37, 10, 15, 0),
(633, 3, 1.32, 39, 10, 15, 0),
(634, 3, 2.64, 30, 10, 15, 0),
(635, 3, 2.76, 31, 10, 15, 0),
(636, 3, 0.12, 32, 10, 15, 0),
(637, 3, 0.60, 35, 10, 15, 0),
(638, 3, 0.60, 37, 10, 15, 0),
(639, 3, 0.12, 39, 10, 15, 0),
(640, 3, 0.60, 41, 10, 15, 0),
(641, 1, 25.00, 41, 10, 15, 1),
(642, 2, 169.18, 30, 10, 15, 1),
(643, 2, 7.69, 32, 10, 15, 1),
(644, 2, 222.87, 31, 10, 15, 1),
(645, 2, 38.45, 35, 10, 15, 1),
(646, 2, 9.69, 39, 10, 15, 1),
(647, 2, 48.45, 37, 10, 15, 1),
(648, 2, 48.45, 41, 10, 15, 1),
(649, 2, 90.64, 30, 10, 15, 1),
(650, 2, 4.12, 32, 10, 15, 1),
(651, 2, 140.76, 31, 10, 15, 1),
(652, 2, 20.60, 35, 10, 15, 1),
(653, 2, 4.12, 39, 10, 15, 1),
(654, 2, 30.60, 37, 10, 15, 1),
(655, 2, 30.60, 41, 10, 15, 1);

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
  `fecha` date NOT NULL,
  `estado` varchar(50) NOT NULL,
  `caja_chica_id` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos_caja`
--

INSERT INTO `movimientos_caja` (`id_movimiento_caja`, `concepto`, `monto`, `fecha`, `estado`, `caja_chica_id`, `activo`) VALUES
(42, 'cafe', 10.00, '2026-03-09', 'Repuesto', 25, 1),
(43, 'se pagaron 3 bombillos nuevos', 900.00, '2026-03-15', 'Repuesto', 25, 1),
(44, 'aaaaa', 10.00, '2026-03-16', 'Pendiente por reposicion', 25, 0),
(45, 'pago de transporte', 900.00, '2026-04-07', 'Pendiente por reposicion', 26, 0),
(46, 'pago de trasnporte', 900.00, '2026-04-07', 'Pendiente por reposicion', 26, 0),
(47, 'pago', 900.00, '2026-04-07', 'Pendiente por reposicion', 26, 0),
(48, 'pago', 900.00, '2026-04-07', 'Pendiente por reposicion', 26, 0),
(49, 'pago', 900.00, '2026-04-07', 'Pendiente por reposicion', 26, 0),
(50, 'pago', 900.00, '2026-04-07', 'Pendiente por reposicion', 26, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `observacion` text NOT NULL DEFAULT 'Sin observación',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `estado`, `observacion`, `activo`) VALUES
(101, 'ANULADO', 'Pago de mensualidad Diciembre', 0),
(102, 'ANULADO', 'Pago de mensualidad Enero', 0),
(103, 'ANULADO', 'Abono a deuda', 0),
(104, 'ANULADO', 'Pago en revisión', 0),
(105, 'ANULADO', 'Pago mensualidad Marzo', 0),
(116, 'Procesado', 'asdasd', 0),
(117, 'RECHAZADO', 'sesss', 1),
(118, 'ANULADO', 'si mi panax', 0),
(119, 'PROCESADO', 'pago', 1),
(120, 'ANULADO', 'mitasd de pagok', 0),
(121, 'No verificado', 'Broder', 1),
(122, 'PROCESADO', 'ssssssssssssssss', 1),
(123, 'No verificado', '', 1),
(124, 'ANULADO', 'xxxxxxxxxxxxx', 0),
(127, 'PROCESADO', '', 1),
(128, 'RECHAZADO', '', 1),
(129, 'PROCESADO', '', 1),
(131, 'PROCESADO', '', 1),
(132, 'PROCESADO', 'pago de ayer', 1),
(133, 'PROCESADO', 'Pago completo enero', 1),
(134, 'PROCESADO', 'pago completo febrero', 1),
(137, 'PENDIENTE', 'Pago registrado desde la App', 1),
(138, 'PENDIENTE', 'Pago registrado desde la App', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_mensualidad`
--

CREATE TABLE `pagos_mensualidad` (
  `detalle_pago_id` int(11) NOT NULL,
  `mensualidad_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos_mensualidad`
--

INSERT INTO `pagos_mensualidad` (`detalle_pago_id`, `mensualidad_id`) VALUES
(1022, 611),
(1023, 611),
(1024, 610),
(1025, 642),
(1028, 610),
(1029, 642);

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
(1, '1', '2026', 455.25, 1),
(2, '2', '2026', 476.43, 0),
(3, '3', '2026', 455.25, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto`
--

CREATE TABLE `presupuesto` (
  `id_presupuesto` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `cuota_reserva` float NOT NULL,
  `observacion` varchar(50) NOT NULL DEFAULT 'Sin observación',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto`
--

INSERT INTO `presupuesto` (`id_presupuesto`, `fecha`, `cuota_reserva`, `observacion`, `activo`) VALUES
(106, '2026-01-01', 10, 'enero 2026', 1),
(107, '2026-02-01', 230, 'febrero 2026 editado', 1),
(110, '2026-03-01', 234, 'Sin observación', 1);

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
(1327, 610),
(1327, 611),
(1327, 612),
(1327, 613),
(1327, 614),
(1327, 615),
(1328, 610),
(1328, 611),
(1328, 612),
(1328, 613),
(1328, 614),
(1328, 615),
(1329, 610),
(1329, 611),
(1329, 612),
(1329, 613),
(1329, 614),
(1329, 615),
(1329, 641),
(1330, 610),
(1330, 611),
(1330, 612),
(1330, 613),
(1330, 614),
(1330, 615),
(1330, 641),
(1331, 610),
(1331, 611),
(1331, 612),
(1331, 613),
(1331, 614),
(1331, 615),
(1331, 641),
(1332, 610),
(1332, 611),
(1332, 612),
(1332, 613),
(1332, 614),
(1332, 615),
(1332, 641),
(1334, 610),
(1334, 611),
(1334, 612),
(1334, 613),
(1334, 614),
(1334, 615),
(1334, 641),
(1335, 610),
(1335, 611),
(1335, 612),
(1335, 613),
(1335, 614),
(1335, 615),
(1336, 610),
(1336, 611),
(1336, 612),
(1336, 613),
(1336, 614),
(1336, 615),
(1337, 610),
(1337, 611),
(1337, 612),
(1337, 613),
(1337, 614),
(1337, 615),
(1338, 610),
(1338, 611),
(1338, 612),
(1338, 613),
(1338, 614),
(1338, 615),
(1353, 642),
(1353, 643),
(1353, 644),
(1353, 645),
(1353, 646),
(1353, 647),
(1353, 648),
(1354, 642),
(1354, 643),
(1354, 644),
(1354, 645),
(1354, 646),
(1354, 647),
(1354, 648),
(1355, 642),
(1355, 643),
(1355, 644),
(1355, 645),
(1355, 646),
(1355, 647),
(1355, 648),
(1356, 642),
(1356, 643),
(1356, 644),
(1356, 645),
(1356, 646),
(1356, 647),
(1356, 648),
(1357, 616),
(1357, 617),
(1357, 618),
(1357, 619),
(1357, 620),
(1357, 621),
(1357, 642),
(1357, 643),
(1357, 644),
(1357, 645),
(1357, 646),
(1357, 647),
(1357, 648),
(1357, 649),
(1357, 650),
(1357, 651),
(1357, 652),
(1357, 653),
(1357, 654),
(1357, 655),
(1358, 617),
(1358, 620),
(1358, 644),
(1358, 646),
(1358, 647),
(1358, 648),
(1358, 651),
(1358, 654),
(1358, 655),
(1361, 642),
(1361, 643),
(1361, 644),
(1361, 645),
(1361, 646),
(1361, 647),
(1361, 648),
(1361, 649),
(1361, 650),
(1361, 651),
(1361, 652),
(1361, 653),
(1361, 654),
(1361, 655),
(1362, 642),
(1362, 643),
(1362, 644),
(1362, 645),
(1362, 646),
(1362, 647),
(1362, 648),
(1362, 649),
(1362, 650),
(1362, 651),
(1362, 652),
(1362, 653),
(1362, 654),
(1362, 655),
(1387, 634),
(1387, 635),
(1387, 636),
(1387, 637),
(1387, 638),
(1387, 639),
(1387, 640);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `nombre_proveedor` varchar(100) NOT NULL,
  `servicio` varchar(100) NOT NULL,
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
  `descripcion_necesidad` varchar(100) NOT NULL,
  `nombre_solicitante` varchar(20) NOT NULL,
  `monto_estimado` float NOT NULL,
  `estado` varchar(20) NOT NULL,
  `presupuesto_id` int(11) NOT NULL,
  `prioridad` varchar(20) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitudes_gasto`
--

INSERT INTO `solicitudes_gasto` (`id_solicitud`, `fecha_reporte`, `descripcion_necesidad`, `nombre_solicitante`, `monto_estimado`, `estado`, `presupuesto_id`, `prioridad`, `activo`) VALUES
(15, '2026-03-18', 'asdasdasdas', 'aasasd', 12, 'Pendiente', 106, '2', 1),
(16, '2026-04-12', 'ssssss', 'aaaaa', 12, 'Pendiente', 106, '1', 1),
(17, '2026-04-11', 'asassss', 'miguel', 10, 'Pendiente', 110, '3', 1);

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
,`descripcion_presupuesto` varchar(50)
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
,`nro_apartamento` varchar(3)
,`mes` varchar(2)
,`anio` varchar(4)
,`monto_cuota` decimal(15,2)
,`total_abonado` decimal(37,2)
,`deuda_pendiente` decimal(38,2)
,`estado_pago` varchar(9)
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

CREATE ALGORITHM=UNDEFINED DEFINER=`app_condominio`@`localhost` SQL SECURITY DEFINER VIEW `vw_ejecucion_presupuesto`  AS SELECT `p`.`id_presupuesto` AS `id_presupuesto`, year(`p`.`fecha`) AS `anio_presupuesto`, `p`.`observacion` AS `descripcion_presupuesto`, `tg`.`nombre_tipo_gasto` AS `partida`, `dp`.`monto` AS `monto_presupuestado`, ifnull((select sum(`dg`.`monto`) from (`gastos` `g` join `detalles_gastos` `dg` on(`g`.`id_gasto` = `dg`.`gasto_id`)) where `g`.`tipo_gasto_id` = `dp`.`tipo_gasto_id` and year(`dg`.`fecha`) = year(`p`.`fecha`) and `g`.`activo` = 1),0) AS `monto_ejecutado`, `dp`.`monto`- ifnull((select sum(`dg`.`monto`) from (`gastos` `g` join `detalles_gastos` `dg` on(`g`.`id_gasto` = `dg`.`gasto_id`)) where `g`.`tipo_gasto_id` = `dp`.`tipo_gasto_id` and year(`dg`.`fecha`) = year(`p`.`fecha`) and `g`.`activo` = 1),0) AS `disponible` FROM ((`presupuesto` `p` join `detalles_presupuesto` `dp` on(`p`.`id_presupuesto` = `dp`.`presupuesto_id`)) join `tipo_gasto` `tg` on(`dp`.`tipo_gasto_id` = `tg`.`id_tipo_gasto`)) WHERE `p`.`activo` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_estado_cuentas_mensualidad`
--
DROP TABLE IF EXISTS `vw_estado_cuentas_mensualidad`;

CREATE ALGORITHM=UNDEFINED DEFINER=`app_condominio`@`localhost` SQL SECURITY DEFINER VIEW `vw_estado_cuentas_mensualidad`  AS SELECT `m`.`id_mensualidad` AS `id_mensualidad`, `a`.`nro_apartamento` AS `nro_apartamento`, `p`.`mes` AS `mes`, `p`.`anio` AS `anio`, `m`.`monto` AS `monto_cuota`, ifnull((select sum(`dp`.`monto`) from ((`pagos_mensualidad` `pm` join `detalles_pagos` `dp` on(`pm`.`detalle_pago_id` = `dp`.`id_detalle_pago`)) join `pagos` `pg` on(`dp`.`pago_id` = `pg`.`id_pago`)) where `pm`.`mensualidad_id` = `m`.`id_mensualidad` and `pg`.`activo` = 1),0) AS `total_abonado`, `m`.`monto`- ifnull((select sum(`dp`.`monto`) from ((`pagos_mensualidad` `pm` join `detalles_pagos` `dp` on(`pm`.`detalle_pago_id` = `dp`.`id_detalle_pago`)) join `pagos` `pg` on(`dp`.`pago_id` = `pg`.`id_pago`)) where `pm`.`mensualidad_id` = `m`.`id_mensualidad` and `pg`.`activo` = 1),0) AS `deuda_pendiente`, CASE WHEN `m`.`monto` - ifnull((select sum(`dp`.`monto`) from ((`pagos_mensualidad` `pm` join `detalles_pagos` `dp` on(`pm`.`detalle_pago_id` = `dp`.`id_detalle_pago`)) join `pagos` `pg` on(`dp`.`pago_id` = `pg`.`id_pago`)) where `pm`.`mensualidad_id` = `m`.`id_mensualidad` AND `pg`.`activo` = 1),0) <= 0 THEN 'Solvente' ELSE 'Pendiente' END AS `estado_pago` FROM ((`mensualidad` `m` join `apartamentos` `a` on(`m`.`apartamento_id` = `a`.`id_apartamento`)) join `periodos_mensualidad` `p` on(`m`.`periodo_id` = `p`.`id_periodo`)) WHERE `m`.`activo` = 1 AND `a`.`activo` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_saldo_caja_chica`
--
DROP TABLE IF EXISTS `vw_saldo_caja_chica`;

CREATE ALGORITHM=UNDEFINED DEFINER=`app_condominio`@`localhost` SQL SECURITY DEFINER VIEW `vw_saldo_caja_chica`  AS SELECT `cc`.`id_caja_chica` AS `id_caja_chica`, `cc`.`estado` AS `estado`, `cc`.`fondo_fijo` AS `monto_base`, ifnull((select sum(`mc`.`monto`) from `movimientos_caja` `mc` where `mc`.`caja_chica_id` = `cc`.`id_caja_chica` and `mc`.`activo` = 1 and `mc`.`estado` <> 'Repuesto'),0) AS `total_gastado_pendiente`, `cc`.`fondo_fijo`- ifnull((select sum(`mc`.`monto`) from `movimientos_caja` `mc` where `mc`.`caja_chica_id` = `cc`.`id_caja_chica` and `mc`.`activo` = 1 and `mc`.`estado` <> 'Repuesto'),0) AS `saldo_disponible` FROM `caja_chica` AS `cc` WHERE `cc`.`activo` = 1 ;

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
  ADD KEY `detalles_pagos_ibfk_1` (`pago_id`);

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
  ADD PRIMARY KEY (`detalle_pago_id`,`mensualidad_id`),
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
  MODIFY `id_anio_fiscal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

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
  MODIFY `id_caja_chica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `detalles_gastos`
--
ALTER TABLE `detalles_gastos`
  MODIFY `id_detalle_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2030;

--
-- AUTO_INCREMENT de la tabla `detalles_pagos`
--
ALTER TABLE `detalles_pagos`
  MODIFY `id_detalle_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1030;

--
-- AUTO_INCREMENT de la tabla `detalles_presupuesto`
--
ALTER TABLE `detalles_presupuesto`
  MODIFY `id_detalle_presupuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1390;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=216;

--
-- AUTO_INCREMENT de la tabla `habitantes`
--
ALTER TABLE `habitantes`
  MODIFY `id_habitante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `mensualidad`
--
ALTER TABLE `mensualidad`
  MODIFY `id_mensualidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=656;

--
-- AUTO_INCREMENT de la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  MODIFY `id_movimiento_caja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT de la tabla `periodos_mensualidad`
--
ALTER TABLE `periodos_mensualidad`
  MODIFY `id_periodo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `presupuesto`
--
ALTER TABLE `presupuesto`
  MODIFY `id_presupuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

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
  ADD CONSTRAINT `pagos_mensualidad_ibfk_3` FOREIGN KEY (`detalle_pago_id`) REFERENCES `detalles_pagos` (`id_detalle_pago`) ON DELETE CASCADE ON UPDATE CASCADE;

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
CREATE DEFINER=`app_condominio`@`localhost` PROCEDURE `sp_insertar_token` (IN `p_usuario_id` INT, IN `p_tipo` VARCHAR(50), IN `p_token` VARCHAR(255), IN `p_fecha_expiracion` DATETIME)   BEGIN
    DELETE FROM tokens_seguridad 
    WHERE usuario_id = p_usuario_id 
      AND tipo = p_tipo;

    INSERT INTO tokens_seguridad (usuario_id, tipo, token, fecha_expiracion)
    VALUES (p_usuario_id, p_tipo, p_token, p_fecha_expiracion);
END$$

CREATE DEFINER=`app_condominio`@`localhost` PROCEDURE `sp_notificar_administradores` (IN `p_titulo` VARCHAR(100), IN `p_descripcion` TEXT, IN `p_tabla_origen` VARCHAR(50), IN `p_id_registro_origen` INT, IN `p_tipo_evento` VARCHAR(50))   BEGIN
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
(3, 3, 1),
(3, 4, 1),
(4, 1, 1),
(4, 1, 2),
(4, 1, 3),
(4, 1, 4),
(4, 1, 15),
(4, 2, 1),
(4, 2, 2),
(4, 2, 3),
(4, 2, 4),
(4, 2, 15),
(4, 3, 1),
(4, 3, 2),
(4, 3, 3),
(4, 3, 4),
(4, 3, 15),
(4, 4, 1),
(4, 4, 2),
(4, 4, 3),
(4, 4, 4),
(4, 4, 15),
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
  `accion` varchar(100) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  `valores_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{}' CHECK (json_valid(`valores_anteriores`)),
  `valores_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{}' CHECK (json_valid(`valores_nuevos`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cartelera_virtual`
--

CREATE TABLE `cartelera_virtual` (
  `id_cartelera` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
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
(27, 'aaaaaaaaaaa', 'fbbbbbbbbbbbbbbbbbbbb', '2010-10-10 00:00:00', 'ref_tarjeta_1774719930_739.PNG', '2', 1),
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
(39, 'Ya casi', 'Ya casi', '2026-04-30 11:50:54', 'f44e1ac5-7768-4f6c-944f-1989807ebe7b_1777564254_220.png', '2', 1);

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
(6, 'Nueva Mensualidad', 'mensualidad', 510, '2026-02-13 18:00:47'),
(7, 'Nueva Mensualidad', 'mensualidad', 513, '2026-02-14 16:45:19'),
(8, 'SALDO_BAJO', 'caja_chica', 23, '2026-02-28 22:06:47'),
(9, 'NUEVA_MENSUALIDAD', 'mensualidad', 547, '2026-03-02 16:36:12'),
(10, 'SALDO_BAJO', 'caja_chica', 23, '2026-03-02 17:08:24'),
(11, 'NUEVA_MENSUALIDAD', 'mensualidad', 551, '2026-03-10 16:42:57'),
(12, 'NUEVA_MENSUALIDAD', 'mensualidad', 555, '2026-03-12 11:42:58'),
(13, 'NUEVA_MENSUALIDAD', 'mensualidad', 559, '2026-03-15 15:24:18'),
(14, 'NUEVA_MENSUALIDAD', 'mensualidad', 563, '2026-03-15 15:28:09'),
(15, 'NUEVA_MENSUALIDAD', 'mensualidad', 571, '2026-03-15 17:24:16'),
(16, 'NUEVA_MENSUALIDAD', 'mensualidad', 575, '2026-03-15 17:38:41'),
(17, 'NUEVA_MENSUALIDAD', 'mensualidad', 579, '2026-03-15 18:25:23'),
(18, 'SALDO_BAJO', 'caja_chica', 25, '2026-03-15 18:26:00'),
(19, 'SALDO_BAJO', 'caja_chica', 25, '2026-03-16 21:21:07'),
(20, 'NUEVA_MENSUALIDAD', 'mensualidad', 584, '2026-03-16 21:48:32'),
(21, 'NUEVA_MENSUALIDAD', 'mensualidad', 589, '2026-03-16 21:50:51'),
(22, 'NUEVA_MENSUALIDAD', 'mensualidad', 648, '2026-03-23 19:37:22'),
(23, 'CREACION_AVISO', 'cartelera_virtual', 30, '2026-04-07 19:16:57'),
(24, 'CREACION_AVISO', 'cartelera_virtual', 31, '2026-04-07 19:22:55'),
(25, 'CREACION_AVISO', 'cartelera_virtual', 32, '2026-04-07 19:25:16'),
(26, 'CREACION_AVISO', 'cartelera_virtual', 33, '2026-04-07 19:25:54'),
(27, 'CREACION_AVISO', 'cartelera_virtual', 34, '2026-04-07 19:26:08'),
(28, 'SALDO_BAJO', 'caja_chica', 26, '2026-04-07 19:42:45'),
(29, 'SALDO_BAJO', 'caja_chica', 26, '2026-04-07 19:45:02'),
(30, 'NUEVA_MENSUALIDAD', 'mensualidad', 655, '2026-04-11 14:53:40'),
(31, 'CREACION_AVISO', 'cartelera_virtual', 35, '2026-04-12 16:16:00');

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
(150, 6),
(151, 6),
(152, 7),
(153, 7),
(154, 8),
(155, 8),
(156, 9),
(157, 10),
(158, 10),
(159, 11),
(160, 12),
(161, 12),
(162, 12),
(163, 12),
(164, 13),
(165, 13),
(166, 13),
(167, 13),
(168, 14),
(169, 14),
(170, 14),
(171, 14),
(172, 15),
(173, 15),
(174, 15),
(175, 15),
(176, 16),
(177, 16),
(178, 16),
(179, 16),
(180, 17),
(181, 17),
(182, 17),
(183, 17),
(184, 18),
(185, 18),
(186, 19),
(187, 19),
(188, 20),
(189, 20),
(190, 20),
(191, 20),
(192, 21),
(193, 21),
(194, 21),
(195, 21),
(196, 22),
(197, 22),
(198, 22),
(199, 22),
(200, 23),
(201, 23),
(202, 23),
(203, 23),
(204, 23),
(205, 24),
(206, 24),
(207, 24),
(208, 24),
(209, 24),
(210, 25),
(211, 25),
(212, 25),
(213, 25),
(214, 25),
(215, 26),
(216, 26),
(217, 26),
(218, 26),
(219, 26),
(220, 27),
(221, 27),
(222, 27),
(223, 27),
(224, 27),
(225, 28),
(226, 28),
(227, 29),
(228, 29),
(229, 30),
(230, 30),
(231, 30),
(232, 30),
(233, 30),
(234, 31),
(235, 31),
(236, 31),
(237, 31),
(238, 31);

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
(4, 'ELIMINAR', 1);

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
-- Estructura de tabla para la tabla `tokens_seguridad`
--

CREATE TABLE `tokens_seguridad` (
  `id_token` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `fecha_expiracion` datetime NOT NULL,
  `tipo` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tokens_seguridad`
--

INSERT INTO `tokens_seguridad` (`id_token`, `usuario_id`, `token`, `fecha_expiracion`, `tipo`) VALUES
(103, 39, 'e15f6aff21b73ab42fc6482539e29d6fc05b7cd61044999e70760f1c44372acc', '2026-04-15 20:07:09', 'RECUPERAR_CONTRASENIA'),
(138, 39, '28bebc8f28ac57691fb3f9f88f3555778b7125dc1933632c1ed0b1abcf3efed9', '2026-05-31 19:18:37', 'RECORDAR_CONTRASENIA'),
(140, 1, '7730f4e63cb55117c368f51c7f6db1caa92e01a6b2519aa1df1656ff3959d69d', '2026-06-07 01:02:08', 'RECORDAR_CONTRASENIA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `apellido` varchar(20) NOT NULL,
  `correo` varchar(50) NOT NULL,
  `contrasenia` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasenia`, `rol_id`, `activo`) VALUES
(1, 'Jesus', 'Escalona', 'administrador@gmail.com', '$2y$10$DUaMi5Xk1iURAgqwcoHTc.OqMk43DTTz4DZp/gi.inaeB7XCkqkEm', 1, 1),
(2, 'francisco', 'mendoza', 'franj@gmail.com', '$2y$10$0KoHFVefo2ZZPv/nh0ocaefcDxfbOKXxcVhnUj844WynuyGhWpaV.', 4, 1),
(27, 'Pepes', 'Campos', 'pepe@gmail.com', '$2y$10$WWp8M1SADJzTWAg910K.mewfZFglQF77ENnqPYLmq1U9AKmmeruY2', 3, 1),
(39, 'Yhsius', 'asdasd', 'jesusgescalonae@gmail.com', '$2y$10$NRSnjmozFiGc7GP7Hc61..Z9OazXjr8B3KvNhEmslcCk1zDeVmjRy', 2, 1),
(53, 'perfil editado', 'perfil editado', 'UsuarioperfilEditada@gmail.com', '$2y$10$AzKv19h61AeAkEYPA/FSA.buvyhYKoRfHT/kUFgMDSWE11PKjpBLS', 4, 0),
(54, 'usuario', 'cambiocontra', 'cambiocontrasenia@gmail.com', '$2y$10$soYFxka95IzptEPe5eA.IONdFJI/geOcpt0K/L7aAKNIsTyn.5Nd2', 23, 0),
(89, 'pepe', 'puias', 'pepa@gmail.com', '$2y$10$GtV9.reiR/8A/NindSEEUOtCPjAs.lLxS67Qp9ZNtG6Ug3wzd5nXi', 1, 0),
(90, 'asdasd', 'asdasd', 'asdas@ad.com', '$2y$10$tTc2q0y3G8sZ8glF3vyDiO.b3DqLtm/TJ/HTJl8QZM2pforDC0TdO', 1, 0),
(91, 'test', 'test', 'test@gmail.com', '$2y$10$M/b/1lwlXVk7g0bHNfBiIOZ1XM0OQIB6SU5GPG2SL0JnhTQvD4Dqu', 69, 1),
(92, 'presi', 'presi', 'presi@gmail.com', '$2y$10$mE8PllA/a3G4ecc.UIZi4u9771m5QBZ3vk/7jWRutkQooM5rD1ryG', 23, 1);

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
-- Indices de la tabla `tokens_seguridad`
--
ALTER TABLE `tokens_seguridad`
  ADD PRIMARY KEY (`id_token`),
  ADD KEY `usuario_id` (`usuario_id`);

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
  MODIFY `id_bitacora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4045;

--
-- AUTO_INCREMENT de la tabla `cartelera_virtual`
--
ALTER TABLE `cartelera_virtual`
  MODIFY `id_cartelera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `eventos_sistema`
--
ALTER TABLE `eventos_sistema`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id_modulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=239;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

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
-- AUTO_INCREMENT de la tabla `tokens_seguridad`
--
ALTER TABLE `tokens_seguridad`
  MODIFY `id_token` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

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
