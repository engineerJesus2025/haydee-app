-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-08-2025 a las 20:15:50
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
CREATE DEFINER=`root`@`localhost` PROCEDURE `gestionar_anio_fiscal` ()   BEGIN
    DECLARE existe_anio_actual BOOLEAN;
    DECLARE anio_actual_abierto BOOLEAN;
    
    SELECT COUNT(*) > 0 INTO existe_anio_actual 
    FROM anio_fiscal 
    WHERE YEAR(fecha_inicio) = YEAR(NOW()) AND estado = 'Abierto';
    
    IF NOT existe_anio_actual THEN
        UPDATE anio_fiscal SET estado = 'Cerrada', fecha_cierre = NOW() 
        WHERE estado = 'Abierto';
        
        INSERT INTO anio_fiscal(fecha_inicio, fecha_cierre, estado, descripcion)
        VALUES (NOW(), NULL, 'Abierto', CONCAT('Año fiscal ', YEAR(NOW())));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_gestion_caja_chica_mensual` ()   sp_block: BEGIN 
    DECLARE v_mes_actual VARCHAR(7);
    DECLARE v_existe_caja_abierta INT;
    DECLARE v_saldo_final_mes_anterior DECIMAL(10,2);
    DECLARE v_fecha_apertura DATE;
    DECLARE v_id_anio_fiscal INT;
    DECLARE v_anio_fiscal_actual VARCHAR(20);
    DECLARE v_nombre_mes_espanol VARCHAR(20);
    
    SET v_fecha_apertura = CURDATE();
    SET v_mes_actual = DATE_FORMAT(v_fecha_apertura, '%Y-%m');
    
    SELECT id_anio_fiscal, descripcion INTO v_id_anio_fiscal, v_anio_fiscal_actual
    FROM anio_fiscal
    WHERE v_fecha_apertura BETWEEN fecha_inicio AND fecha_cierre
    AND estado = 'Abierto'
    LIMIT 1;
    
    IF v_id_anio_fiscal IS NULL THEN
        SELECT 'Error: No existe un año fiscal abierto para la fecha indicada' AS mensaje;
        LEAVE sp_block; 
    END IF;
    
    SELECT COUNT(*) INTO v_existe_caja_abierta 
    FROM caja_chica 
    WHERE DATE_FORMAT(fecha_apertura, '%Y-%m') = v_mes_actual 
    AND anio_fiscal_id = v_id_anio_fiscal
    AND estado = 'Abierta';
    
    IF v_existe_caja_abierta = 0 THEN
    UPDATE caja_chica 
        SET estado = 'Cerrada'
        WHERE estado = 'Abierta'
        AND anio_fiscal_id = v_id_anio_fiscal;
        
    SET v_saldo_final_mes_anterior = NULL;
        SELECT saldo_actual INTO v_saldo_final_mes_anterior
        FROM caja_chica
        WHERE estado = 'Cerrada'
        AND anio_fiscal_id = v_id_anio_fiscal
        ORDER BY fecha_apertura DESC
        LIMIT 1;
        
        IF v_saldo_final_mes_anterior IS NULL THEN
            SET v_saldo_final_mes_anterior = 1000.00;
        END IF;
        
         SET v_nombre_mes_espanol = 
            CASE MONTH(v_fecha_apertura)
                WHEN 1 THEN 'Enero'
                WHEN 2 THEN 'Febrero'
                WHEN 3 THEN 'Marzo'
                WHEN 4 THEN 'Abril'
                WHEN 5 THEN 'Mayo'
                WHEN 6 THEN 'Junio'
                WHEN 7 THEN 'Julio'
                WHEN 8 THEN 'Agosto'
                WHEN 9 THEN 'Septiembre'
                WHEN 10 THEN 'Octubre'
                WHEN 11 THEN 'Noviembre'
                WHEN 12 THEN 'Diciembre'
                ELSE 'Desconocido'
            END;
        
        INSERT INTO caja_chica 
            (fecha_apertura, monto_inicial, saldo_actual, estado, 
            observaciones, anio_fiscal_id) 
        VALUES 
            (v_fecha_apertura, v_saldo_final_mes_anterior, v_saldo_final_mes_anterior, 
            'Abierta', CONCAT('Caja chica del mes ', v_nombre_mes_espanol, ' del ', YEAR(v_fecha_apertura)), v_id_anio_fiscal);
       
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_sincronizar_presupuestos_mensualidad` (IN `p_mensualidad_id` INT, IN `p_nuevos_presupuestos_ids` TEXT)   BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; 
    END;

    CREATE TEMPORARY TABLE IF NOT EXISTS TempNuevosGastos (presupuesto_id INT PRIMARY KEY);
    TRUNCATE TABLE TempNuevosGastos;

    SET @sql = CONCAT('INSERT INTO TempNuevosGastos (presupuesto_id) VALUES (', REPLACE(p_nuevos_presupuestos_ids, ',', '),('), ');');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    
    START TRANSACTION;

        DELETE FROM presupuesto_mensualidad
        WHERE
            mensualidad_id = p_mensualidad_id
            AND presupuesto_id NOT IN (SELECT presupuesto_id FROM TempNuevosGastos);

        INSERT INTO presupuesto_mensualidad (mensualidad_id, presupuesto_id)
        SELECT p_mensualidad_id, nuevos.presupuesto_id
        FROM TempNuevosGastos AS nuevos
        WHERE NOT EXISTS (
            SELECT 1
            FROM presupuesto_mensualidad AS existentes
            WHERE existentes.mensualidad_id = p_mensualidad_id AND existentes.presupuesto_id = nuevos.presupuesto_id
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
  `fecha_inicio` date NOT NULL,
  `fecha_cierre` date DEFAULT NULL,
  `estado` varchar(20) NOT NULL,
  `descripcion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `anio_fiscal`
--

INSERT INTO `anio_fiscal` (`id_anio_fiscal`, `fecha_inicio`, `fecha_cierre`, `estado`, `descripcion`) VALUES
(24, '2025-01-01', '2026-01-01', 'Abierto', 'año fiscal 2025');

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
  `alquilado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `apartamentos`
--

INSERT INTO `apartamentos` (`id_apartamento`, `nro_apartamento`, `porcentaje_participacion`, `gas`, `agua`, `alquilado`) VALUES
(11, '1-1', 5.25, 1, 1, 1),
(12, '1-2', 5.25, 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bancos`
--

CREATE TABLE `bancos` (
  `id_banco` int(11) NOT NULL,
  `nombre_banco` varchar(50) NOT NULL,
  `codigo` int(7) NOT NULL,
  `numero_cuenta` varchar(50) NOT NULL,
  `telefono_afiliado` varchar(50) NOT NULL,
  `cedula_afiliada` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bancos`
--

INSERT INTO `bancos` (`id_banco`, `nombre_banco`, `codigo`, `numero_cuenta`, `telefono_afiliado`, `cedula_afiliada`) VALUES
(1, 'venezuela', 102, '0102-123412124', '041524568', '152456542');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `banco_transacciones`
--

CREATE TABLE `banco_transacciones` (
  `id_banco_transaccion` int(11) NOT NULL,
  `referencia` varchar(20) NOT NULL,
  `imagen` varchar(100) NOT NULL,
  `detalle_pago_id` int(11) DEFAULT NULL,
  `detalle_gasto_id` int(11) DEFAULT NULL,
  `banco_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `banco_transacciones`
--

INSERT INTO `banco_transacciones` (`id_banco_transaccion`, `referencia`, `imagen`, `detalle_pago_id`, `detalle_gasto_id`, `banco_id`) VALUES
(39, '10145445', '', 76, NULL, 1),
(40, '10101', 'images1754700772795.png', 78, NULL, 1),
(41, '123123', 'CSS-Logo_1754700864_816.jpg', NULL, 65, 1),
(42, '13123', 'fiabil_1754700909_200.PNG', NULL, 66, 1),
(43, '12312312', 'javascript-logo-javascript-icon-transparent-free-png_1754700909_867.png', NULL, 67, 1),
(44, '123123', 'xamp2_1754700970_650.PNG', NULL, 68, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_chica`
--

CREATE TABLE `caja_chica` (
  `id_caja_chica` int(11) NOT NULL,
  `fecha_apertura` date NOT NULL,
  `monto_inicial` float NOT NULL,
  `saldo_actual` float NOT NULL,
  `estado` varchar(20) NOT NULL,
  `observaciones` varchar(100) NOT NULL,
  `anio_fiscal_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `caja_chica`
--

INSERT INTO `caja_chica` (`id_caja_chica`, `fecha_apertura`, `monto_inicial`, `saldo_actual`, `estado`, `observaciones`, `anio_fiscal_id`) VALUES
(15, '2025-07-01', 1500, 2000, 'Cerrada', 'Caja chica mes Julio', 24),
(18, '2025-08-05', 2000, -396019, 'Abierta', 'Caja chica del mes Agosto del 2025', 24);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_gastos`
--

CREATE TABLE `detalles_gastos` (
  `id_detalle_gasto` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `monto` float NOT NULL,
  `monto_dolar` float NOT NULL,
  `metodo_pago` varchar(20) NOT NULL,
  `gasto_id` int(11) NOT NULL,
  `caja_id` int(11) NOT NULL,
  `descripcion_detalle_gasto` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_gastos`
--

INSERT INTO `detalles_gastos` (`id_detalle_gasto`, `fecha`, `monto`, `monto_dolar`, `metodo_pago`, `gasto_id`, `caja_id`, `descripcion_detalle_gasto`) VALUES
(65, '2025-08-02', 123123, 0, 'Transferencia', 86, 18, 'asdasdasdas'),
(66, '2025-08-02', 123123, 0, 'Transferencia', 87, 18, 'asdasdasda'),
(67, '2025-08-02', 22, 0, 'Transferencia', 87, 18, 'asdasdasdas'),
(68, '2025-08-02', 2313, 0, 'Transferencia', 88, 18, 'asdasdasasdas'),
(69, '2025-08-02', 13, 0, 'Efectivo', 88, 18, 'asdasdasdasd'),
(70, '2025-08-02', 111, 0, 'Transferencia', 88, 18, 'asdasdasdsas');

--
-- Disparadores `detalles_gastos`
--
DELIMITER $$
CREATE TRIGGER `actualizador_caja_eliminar_gasto` AFTER DELETE ON `detalles_gastos` FOR EACH ROW BEGIN
    IF OLD.caja_id IS NOT NULL THEN
        UPDATE caja_chica 
        SET saldo_actual = saldo_actual + OLD.monto
        WHERE id_caja_chica = OLD.caja_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `actualizador_caja_registrar_gasto` AFTER INSERT ON `detalles_gastos` FOR EACH ROW BEGIN
    IF NEW.caja_id IS NOT NULL THEN
        UPDATE caja_chica 
        SET saldo_actual = saldo_actual - NEW.monto
        WHERE id_caja_chica = NEW.caja_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `actualizador_caja_update_gastos` AFTER UPDATE ON `detalles_gastos` FOR EACH ROW BEGIN
    IF NEW.caja_id IS NOT NULL THEN
        UPDATE caja_chica 
        SET saldo_actual = saldo_actual + OLD.monto - NEW.monto
        WHERE id_caja_chica = NEW.caja_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_pagos`
--

CREATE TABLE `detalles_pagos` (
  `id_detalle_pago` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `monto` float NOT NULL,
  `monto_dolar` float NOT NULL,
  `tipo_pago` varchar(20) NOT NULL,
  `pago_id` int(11) NOT NULL,
  `caja_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_pagos`
--

INSERT INTO `detalles_pagos` (`id_detalle_pago`, `fecha`, `monto`, `monto_dolar`, `tipo_pago`, `pago_id`, `caja_id`) VALUES
(76, '2025-08-01', 101, 0.77, 'Transferencia', 49, 18),
(77, '2025-08-01', 10, 0.08, 'Efectivo', 50, 18),
(78, '2025-08-02', 10, 0.08, 'Transferencia', 50, 18),
(80, '2025-08-01', 20, 0.15, 'Efectivo', 51, 18);

--
-- Disparadores `detalles_pagos`
--
DELIMITER $$
CREATE TRIGGER `actualizador_caja_eliminar_pago` AFTER DELETE ON `detalles_pagos` FOR EACH ROW BEGIN
    IF OLD.caja_id IS NOT NULL THEN
        UPDATE caja_chica 
        SET saldo_actual = saldo_actual - OLD.monto
        WHERE id_caja_chica = OLD.caja_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `actualizador_caja_registrar_pago` AFTER INSERT ON `detalles_pagos` FOR EACH ROW BEGIN
    IF NEW.caja_id IS NOT NULL THEN
        UPDATE caja_chica 
        SET saldo_actual = saldo_actual + NEW.monto
        WHERE id_caja_chica = NEW.caja_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `actualizador_caja_update_pago` AFTER UPDATE ON `detalles_pagos` FOR EACH ROW BEGIN
    IF NEW.caja_id IS NOT NULL THEN
        UPDATE caja_chica 
        SET saldo_actual = saldo_actual - OLD.monto + NEW.monto
        WHERE id_caja_chica = NEW.caja_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_presupuesto`
--

CREATE TABLE `detalles_presupuesto` (
  `id_detalle_presupuesto` int(11) NOT NULL,
  `monto_detalle` float NOT NULL,
  `nombre_detalle` varchar(50) NOT NULL,
  `presupuesto_id` int(11) NOT NULL,
  `tipo_gasto_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_presupuesto`
--

INSERT INTO `detalles_presupuesto` (`id_detalle_presupuesto`, `monto_detalle`, `nombre_detalle`, `presupuesto_id`, `tipo_gasto_id`) VALUES
(270, 10, 'GAS LARA', 39, 1),
(271, 30, 'Trabajadora Residencial', 39, 3),
(272, 30, 'Bono de alimentacion', 39, 3),
(273, 30, 'Bono de ayuda', 39, 3),
(274, 30, 'Seguridad Social', 39, 3),
(275, 40, 'Mantenimiento ascensor', 39, 4),
(276, 50, 'Bolsas de Basura', 39, 9),
(277, 50, 'Productos de Limpieza', 39, 9),
(278, 60, 'Comisiones Bancaracias', 39, 10),
(279, 60, 'Exencion cuota del administrador', 39, 10),
(280, 20, 'CORPOELEC', 39, 2),
(281, 20, 'HIDROLARA', 39, 2),
(306, 12, 'GAS LARA', 40, 1),
(307, 13, 'CORPOELEC', 40, 2),
(308, 14, 'HIDROLARA', 40, 2),
(309, 15, 'Trabajadora Residencial', 40, 3),
(310, 16, 'Bono de alimentacion', 40, 3),
(311, 174, 'Bono de ayuda', 40, 3),
(312, 18, 'Seguridad Social', 40, 3),
(313, 20, 'Bolsas de Basura', 40, 9),
(314, 21, 'Productos de Limpieza', 40, 9),
(315, 19, 'Mantenimiento ascensor', 40, 4),
(316, 13, 'Comisiones Bancaracias', 40, 10),
(317, 14, 'Exencion cuota del administrador', 40, 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id_gasto` int(11) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `tipo_gasto_id` int(11) NOT NULL,
  `solicitud_id` int(11) DEFAULT NULL,
  `proveedor_id` int(11) NOT NULL,
  `descripcion_gasto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gastos`
--

INSERT INTO `gastos` (`id_gasto`, `tipo`, `tipo_gasto_id`, `solicitud_id`, `proveedor_id`, `descripcion_gasto`) VALUES
(86, 'fijo', 1, 7, 2, 'asdasdasasd'),
(87, 'variable', 2, 7, 1, 'asdasdasdasd'),
(88, 'fijo', 2, 7, 2, 'sesesessesss');

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
  `sexo` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habitantes`
--

INSERT INTO `habitantes` (`id_habitante`, `nombre`, `apellido`, `cedula`, `telefono`, `correo`, `fecha_nacimiento`, `sexo`) VALUES
(1, 'pepes', 'pipas', '8453213', '24421122412', 'pepe@gmail.com', '2005-06-01', 'Masculino'),
(2, 'Juan', 'Jimenez', '12341222', '45612456445', 'asda@gmail.com', '2025-07-01', 'Masculino'),
(6, 'jimena', 'mendez', '5124123', '45631212123', 'sdada@gami.com', '2019-10-10', 'Masculino'),
(11, 'Yomismo', 'Soy', '21212212', '12312321112', 'asda@gami.com', '2021-10-10', 'Masculino'),
(13, 'asdasd', 'asdasda', '13231232', '12313212322', 'asdas@fasm.com', '2010-10-10', 'Masculino'),
(14, 'pepe', 'pipas', '12341212', '10128545122', 'jasda@gasmi.com', '2000-10-10', 'Masculino');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitantes_apartamentos`
--

CREATE TABLE `habitantes_apartamentos` (
  `id_habitante_apartamento` int(11) NOT NULL,
  `apartamento_id` int(11) NOT NULL,
  `habitante_id` int(11) NOT NULL,
  `tipo_vinculo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habitantes_apartamentos`
--

INSERT INTO `habitantes_apartamentos` (`id_habitante_apartamento`, `apartamento_id`, `habitante_id`, `tipo_vinculo`) VALUES
(12, 11, 11, 'Propietario'),
(14, 12, 13, 'Propietario'),
(15, 11, 14, 'Habitante');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensualidad`
--

CREATE TABLE `mensualidad` (
  `id_mensualidad` int(11) NOT NULL,
  `monto` float NOT NULL,
  `monto_dolar` float NOT NULL,
  `mes` varchar(2) NOT NULL,
  `anio` varchar(4) NOT NULL,
  `apartamento_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensualidad`
--

INSERT INTO `mensualidad` (`id_mensualidad`, `monto`, `monto_dolar`, `mes`, `anio`, `apartamento_id`) VALUES
(231, 19.95, 0.147081, '2', '2025', 11),
(232, 4.73, 0.0348717, '2', '2025', 12),
(237, 3.05, 0.0237835, '3', '2025', 11),
(238, 15.28, 0.119152, '3', '2025', 12);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `observacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `estado`, `observacion`) VALUES
(49, 'Procesado', 'dfsfsdfsd'),
(50, 'Procesado', 'sdfdf'),
(51, 'Procesado', 'sasasasas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_mensualidad`
--

CREATE TABLE `pagos_mensualidad` (
  `id_pago_mensualidad` int(11) NOT NULL,
  `detalle_pago_id` int(11) DEFAULT NULL,
  `mensualidad_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos_mensualidad`
--

INSERT INTO `pagos_mensualidad` (`id_pago_mensualidad`, `detalle_pago_id`, `mensualidad_id`) VALUES
(81, 76, 231),
(82, 77, 237),
(83, 78, 237),
(85, 80, 231);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto`
--

CREATE TABLE `presupuesto` (
  `id_presupuesto` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `cuota_reserva` decimal(15,0) NOT NULL,
  `observacion` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto`
--

INSERT INTO `presupuesto` (`id_presupuesto`, `fecha`, `cuota_reserva`, `observacion`) VALUES
(39, '2025-02-01', 10, 'Perico'),
(40, '2025-03-01', 1000, 'Loro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_mensualidad`
--

CREATE TABLE `presupuesto_mensualidad` (
  `id_presupuesto_mensualidad` int(11) NOT NULL,
  `presupuesto_id` int(11) NOT NULL,
  `mensualidad_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto_mensualidad`
--

INSERT INTO `presupuesto_mensualidad` (`id_presupuesto_mensualidad`, `presupuesto_id`, `mensualidad_id`) VALUES
(578, 279, 231),
(579, 278, 231),
(580, 274, 231),
(581, 273, 231),
(582, 272, 231),
(583, 271, 231),
(584, 281, 231),
(585, 280, 231),
(586, 277, 231),
(587, 276, 231),
(588, 275, 232),
(589, 270, 232),
(590, 281, 232),
(591, 280, 232),
(632, 317, 237),
(633, 316, 237),
(634, 315, 237),
(635, 306, 237),
(636, 312, 238),
(637, 311, 238),
(638, 310, 238),
(639, 309, 238),
(640, 307, 238),
(641, 308, 238),
(642, 314, 238),
(643, 313, 238);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `nombre_proveedor` varchar(100) DEFAULT NULL,
  `servicio` varchar(100) DEFAULT NULL,
  `rif` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `nombre_proveedor`, `servicio`, `rif`, `direccion`) VALUES
(1, 'Gas Lara', 'Gas', 'v123124', 'Lara'),
(2, 'Proimca', 'luz', 'v1231231', 'quibor'),
(3, 'Jardinero', 'Trabajos en jardinería', '124512423', 'Sukasa'),
(4, 'Reparaciones CA', 'Reparar XD', '121424', 'Por alla');

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
  `prioridad` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitudes_gasto`
--

INSERT INTO `solicitudes_gasto` (`id_solicitud`, `fecha_reporte`, `descripcion_necesidad`, `nombre_solicitante`, `monto_estimado`, `estado`, `presupuesto_id`, `prioridad`) VALUES
(7, '2025-07-03', 'se necesita', 'pepe', 100, 'Pendiente', 40, '2');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_gasto`
--

CREATE TABLE `tipo_gasto` (
  `id_tipo_gasto` int(11) NOT NULL,
  `nombre_tipo_gasto` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_gasto`
--

INSERT INTO `tipo_gasto` (`id_tipo_gasto`, `nombre_tipo_gasto`) VALUES
(1, 'Servicio de Gas'),
(2, 'Servicios Públicos'),
(3, 'Personal y Obligaciones Laborales'),
(4, 'Mantenimientos y Reparaciones'),
(9, 'Suministros de Limpieza y Operacion'),
(10, 'Gastos Administrativos y Financieros');

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
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `banco_transacciones`
--
ALTER TABLE `banco_transacciones`
  ADD PRIMARY KEY (`id_banco_transaccion`),
  ADD KEY `banco_id` (`banco_id`),
  ADD KEY `detalle_pago_id` (`detalle_pago_id`),
  ADD KEY `gasto_id` (`detalle_gasto_id`);

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
  ADD KEY `gasto_id` (`gasto_id`),
  ADD KEY `caja_id` (`caja_id`);

--
-- Indices de la tabla `detalles_pagos`
--
ALTER TABLE `detalles_pagos`
  ADD PRIMARY KEY (`id_detalle_pago`),
  ADD KEY `detalles_pagos_ibfk_1` (`pago_id`),
  ADD KEY `caja_id` (`caja_id`);

--
-- Indices de la tabla `detalles_presupuesto`
--
ALTER TABLE `detalles_presupuesto`
  ADD PRIMARY KEY (`id_detalle_presupuesto`),
  ADD KEY `presupuesto_id` (`presupuesto_id`),
  ADD KEY `tipo_gasto_id` (`tipo_gasto_id`);

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
  ADD PRIMARY KEY (`id_habitante_apartamento`),
  ADD KEY `apartamento_id` (`apartamento_id`),
  ADD KEY `persona_id` (`habitante_id`);

--
-- Indices de la tabla `mensualidad`
--
ALTER TABLE `mensualidad`
  ADD PRIMARY KEY (`id_mensualidad`),
  ADD KEY `apartamento_id` (`apartamento_id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`);

--
-- Indices de la tabla `pagos_mensualidad`
--
ALTER TABLE `pagos_mensualidad`
  ADD PRIMARY KEY (`id_pago_mensualidad`),
  ADD KEY `mensualidad_id` (`mensualidad_id`),
  ADD KEY `pagos_mensualidad_ibfk_1` (`detalle_pago_id`);

--
-- Indices de la tabla `presupuesto`
--
ALTER TABLE `presupuesto`
  ADD PRIMARY KEY (`id_presupuesto`);

--
-- Indices de la tabla `presupuesto_mensualidad`
--
ALTER TABLE `presupuesto_mensualidad`
  ADD PRIMARY KEY (`id_presupuesto_mensualidad`),
  ADD KEY `gasto_id` (`presupuesto_id`),
  ADD KEY `mesualidad_id` (`mensualidad_id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`);

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
  MODIFY `id_anio_fiscal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `apartamentos`
--
ALTER TABLE `apartamentos`
  MODIFY `id_apartamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `bancos`
--
ALTER TABLE `bancos`
  MODIFY `id_banco` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `banco_transacciones`
--
ALTER TABLE `banco_transacciones`
  MODIFY `id_banco_transaccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `caja_chica`
--
ALTER TABLE `caja_chica`
  MODIFY `id_caja_chica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `detalles_gastos`
--
ALTER TABLE `detalles_gastos`
  MODIFY `id_detalle_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT de la tabla `detalles_pagos`
--
ALTER TABLE `detalles_pagos`
  MODIFY `id_detalle_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT de la tabla `detalles_presupuesto`
--
ALTER TABLE `detalles_presupuesto`
  MODIFY `id_detalle_presupuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=334;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT de la tabla `habitantes`
--
ALTER TABLE `habitantes`
  MODIFY `id_habitante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `habitantes_apartamentos`
--
ALTER TABLE `habitantes_apartamentos`
  MODIFY `id_habitante_apartamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `mensualidad`
--
ALTER TABLE `mensualidad`
  MODIFY `id_mensualidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=239;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de la tabla `pagos_mensualidad`
--
ALTER TABLE `pagos_mensualidad`
  MODIFY `id_pago_mensualidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT de la tabla `presupuesto`
--
ALTER TABLE `presupuesto`
  MODIFY `id_presupuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `presupuesto_mensualidad`
--
ALTER TABLE `presupuesto_mensualidad`
  MODIFY `id_presupuesto_mensualidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=644;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `solicitudes_gasto`
--
ALTER TABLE `solicitudes_gasto`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tipo_gasto`
--
ALTER TABLE `tipo_gasto`
  MODIFY `id_tipo_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `banco_transacciones`
--
ALTER TABLE `banco_transacciones`
  ADD CONSTRAINT `banco_transacciones_ibfk_1` FOREIGN KEY (`banco_id`) REFERENCES `bancos` (`id_banco`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `banco_transacciones_ibfk_2` FOREIGN KEY (`detalle_pago_id`) REFERENCES `detalles_pagos` (`id_detalle_pago`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `banco_transacciones_ibfk_3` FOREIGN KEY (`detalle_gasto_id`) REFERENCES `detalles_gastos` (`id_detalle_gasto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `caja_chica`
--
ALTER TABLE `caja_chica`
  ADD CONSTRAINT `caja_chica_ibfk_1` FOREIGN KEY (`anio_fiscal_id`) REFERENCES `anio_fiscal` (`id_anio_fiscal`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalles_gastos`
--
ALTER TABLE `detalles_gastos`
  ADD CONSTRAINT `detalles_gastos_ibfk_1` FOREIGN KEY (`gasto_id`) REFERENCES `gastos` (`id_gasto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalles_gastos_ibfk_2` FOREIGN KEY (`caja_id`) REFERENCES `caja_chica` (`id_caja_chica`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalles_pagos`
--
ALTER TABLE `detalles_pagos`
  ADD CONSTRAINT `detalles_pagos_ibfk_1` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id_pago`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalles_pagos_ibfk_2` FOREIGN KEY (`caja_id`) REFERENCES `caja_chica` (`id_caja_chica`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalles_presupuesto`
--
ALTER TABLE `detalles_presupuesto`
  ADD CONSTRAINT `detalles_presupuesto_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuesto` (`id_presupuesto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalles_presupuesto_ibfk_2` FOREIGN KEY (`tipo_gasto_id`) REFERENCES `tipo_gasto` (`id_tipo_gasto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id_proveedor`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `gastos_ibfk_2` FOREIGN KEY (`tipo_gasto_id`) REFERENCES `tipo_gasto` (`id_tipo_gasto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `gastos_ibfk_3` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_gasto` (`id_solicitud`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `habitantes_apartamentos`
--
ALTER TABLE `habitantes_apartamentos`
  ADD CONSTRAINT `habitantes_apartamentos_ibfk_1` FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos` (`id_apartamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `habitantes_apartamentos_ibfk_2` FOREIGN KEY (`habitante_id`) REFERENCES `habitantes` (`id_habitante`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `mensualidad`
--
ALTER TABLE `mensualidad`
  ADD CONSTRAINT `mensualidad_ibfk_1` FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos` (`id_apartamento`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `presupuesto_mensualidad_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `detalles_presupuesto` (`id_detalle_presupuesto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presupuesto_mensualidad_ibfk_2` FOREIGN KEY (`mensualidad_id`) REFERENCES `mensualidad` (`id_mensualidad`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `solicitudes_gasto`
--
ALTER TABLE `solicitudes_gasto`
  ADD CONSTRAINT `solicitudes_gasto_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuesto` (`id_presupuesto`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
