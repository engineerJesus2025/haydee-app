-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-05-2026 a las 00:52:52
-- Versión del servidor: 10.4.32-MariaDB
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

    INSERT INTO detalles_gastos (fecha, monto, tasa_dolar, metodo_pago, gasto_id, descripcion_detalle_gasto)
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
('616865', 'a8b5604f-a7cb-4da2-9847-505df11bc3ed_1778972668_569.jpeg', 6, 2033),
('543524', 'images__2__1773805029_187.png', 9, 2024),
('5435245', 'images__2__1774719607_329.png', 9, 2026),
('45215231', 'virustotla_1778943667_768.PNG', 13, 2031);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitantes_apartamentos`
--

CREATE TABLE `habitantes_apartamentos` (
  `apartamento_id` int(11) NOT NULL,
  `habitante_id` int(11) NOT NULL,
  `tipo_vinculo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
('1234578', 'images_1778942882_622.png', 1, 1031),
('2312', 'virustotla_1771651369_672.PNG', 6, 370),
('4213123', 'lenguaje_comun_1771707846_116.PNG', 6, 384),
('4213', 'CSS-Logo_1772718346_856.jpg', 6, 1007),
('678678', 'default.png', 6, 1016),
('532423', 'default.png', 6, 1018),
('532434345', 'colores_inicio_1_1773856062_104.PNG', 6, 1020),
('4676768', '37f5d4c3-fea6-46c7-bef0-dc8b313c4662_1778973567_561.jpeg', 6, 1034),
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
(610, 1, 203.94, 30, 10, 15, 1),
(611, 1, 213.21, 31, 10, 15, 1),
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
(50, 'pago', 900.00, '2026-04-07', 'Pendiente por reposicion', 26, 1),
(51, 'compramos cafe', 11.00, '2026-05-16', 'Pendiente por reposicion', 27, 1);

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
(138, 'PENDIENTE', 'Pago registrado desde la App', 1),
(139, 'PROCESADO', 'HOLA', 1),
(140, 'PROCESADO', 'pago de febrero', 1),
(141, 'PROCESADO', 'Pago registrado desde la App', 1),
(142, 'PROCESADO', 'Pago registrado desde la App', 1);

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
(1029, 642),
(1030, 649),
(1031, 649),
(1032, 642),
(1033, 615),
(1034, 650);

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
(1, '1', '2026', 515.18, 1),
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
(15, '2026-03-18', 'asdasdasdas', 'aasasd', 13, 'Pendiente', 106, '2', 1),
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
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_estado_cuentas_mensualidad`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_estado_cuentas_mensualidad` (
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_saldo_caja_chica`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_saldo_caja_chica` (
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
-- AUTO_INCREMENT de la tabla `mensualidad`
--
ALTER TABLE `mensualidad`
  MODIFY `id_mensualidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=656;

--
-- AUTO_INCREMENT de la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  MODIFY `id_movimiento_caja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
