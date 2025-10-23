-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-10-2025 a las 06:43:32
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
-- Base de datos: `haydee_pruebas`
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
(24, '2025-01-01', '2026-01-01', 'Abierto', 'Ejecutada prueba de edicion'),
(29, '2024-01-01', '2025-01-01', 'Cerrada', 'Año fiscal 2024'),
(32, '2020-01-01', '2021-01-01', 'Abierto', 'Prueba unitaria'),
(33, '2020-01-01', '2021-01-01', 'Abierto', 'Prueba unitaria');

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
(12, '1-2', 5.25, 2, 1, 1),
(15, '2-1', 4, 1, 2, 1),
(16, '2-2', 5, 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bancos`
--

CREATE TABLE `bancos` (
  `id_banco` int(11) NOT NULL,
  `nombre_banco` varchar(50) NOT NULL,
  `codigo` varchar(7) NOT NULL,
  `numero_cuenta` varchar(50) NOT NULL,
  `telefono_afiliado` varchar(50) NOT NULL,
  `cedula_afiliada` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bancos`
--

INSERT INTO `bancos` (`id_banco`, `nombre_banco`, `codigo`, `numero_cuenta`, `telefono_afiliado`, `cedula_afiliada`) VALUES
(1, 'venezuela', '0102', '0102123412124232323', '04152456842', '23232421'),
(6, 'Banesco', '0117', '1242342342424121211', '04142584985', '30612546');

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
(102, '1212', '', NULL, 118, 1),
(106, '123123', 'lenguaje_comun_1758973341_948.PNG', NULL, 120, 6),
(116, '12312', '1729083773_julio_1759864670_790.png', NULL, 142, 1),
(119, '12123', '1729083773julio1759890237465.png', 159, NULL, 1),
(121, '12312', '1b90b18a7455eccf83f5e9eab37e550c594458bbc0517380e6790cba537d5d931759897809592.jpg', 161, NULL, 1),
(122, '1211', '1728940161julio1759897884486.png', 162, NULL, 1),
(123, '234', '1b90b18a7455eccf83f5e9eab37e550c594458bbc0517380e6790cba537d5d931759897884450.jpg', 163, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_chica`
--

CREATE TABLE `caja_chica` (
  `id_caja_chica` int(11) NOT NULL,
  `fondo_fijo` float NOT NULL,
  `saldo_actual` float NOT NULL,
  `estado` varchar(20) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `fecha_creacion` date NOT NULL DEFAULT current_timestamp(),
  `anio_fiscal_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `caja_chica`
--

INSERT INTO `caja_chica` (`id_caja_chica`, `fondo_fijo`, `saldo_actual`, `estado`, `descripcion`, `fecha_creacion`, `anio_fiscal_id`) VALUES
(21, 115, 90, 'Abierto', 'Caja chica 2025', '2025-01-01', 24),
(22, 100, 100, 'Cerrada', 'Caja chica 2024', '2024-01-01', 29),
(23, 305, 325, 'Cerrada', 'Ejecutada prueba de edicion', '2025-10-19', 24),
(27, 56, 56, 'Cerrada', 'caja para borrar', '2025-10-19', 24);

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
  `descripcion_detalle_gasto` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_gastos`
--

INSERT INTO `detalles_gastos` (`id_detalle_gasto`, `fecha`, `monto`, `monto_dolar`, `metodo_pago`, `gasto_id`, `descripcion_detalle_gasto`) VALUES
(117, '2025-09-02', 2, 0, 'Efectivo', 94, 'se pago el gas en efectivo'),
(118, '2025-09-01', 1, 0, 'Pago Movil', 94, 'asdasdadasds'),
(119, '2025-09-10', 12, 0, 'Efectivo', 101, 'pago en efectivo'),
(120, '2025-09-01', 1, 0, 'Pago Movil', 101, 'asdassadasdas'),
(134, '2025-10-06', 45, 0, 'Efectivo', 109, 'Reposición de Caja Chica'),
(135, '2025-10-06', 25, 0, 'Efectivo', 110, 'Reposición de Caja Chica'),
(136, '2025-10-06', 35, 0, 'Efectivo', 111, 'Reposición de Caja Chica'),
(142, '2025-10-07', 10, 0, 'Transferencia', 112, 'Pago en transferencia'),
(143, '2010-10-10', 10, 0, 'Efectivo', 112, 'Pago en efectivo'),
(146, '2025-10-07', 15, 0, 'Efectivo', 114, 'Reposición de Caja Chica');

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
  `pago_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_pagos`
--

INSERT INTO `detalles_pagos` (`id_detalle_pago`, `fecha`, `monto`, `monto_dolar`, `tipo_pago`, `pago_id`) VALUES
(155, '2025-10-01', 40, 0.21, 'Efectivo', 82),
(158, '2025-10-01', 1, 0.01, 'Efectivo', 84),
(159, '2020-10-10', 1, 0.01, 'Transferencia', 84),
(160, '2025-10-01', 10, 0.05, 'Transferencia', 85),
(161, '2025-10-01', 12, 0.06, 'Pago Movil', 85),
(162, '2025-10-08', 15, 0.08, 'Transferencia', 86),
(163, '2010-01-10', 21, 0.11, 'Efectivo', 86);

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
(745, 1, 'GAS LARA', 57, 1),
(746, 2, 'gato', 57, 1),
(747, 2, 'CORPOELEC', 57, 2),
(748, 2, 'HIDROLARA', 57, 2),
(749, 2, 'pepe', 57, 2),
(750, 3, 'Mantenimiento ascensor', 57, 4),
(751, 4, 'Bolsas de Basura', 57, 9),
(752, 4, 'Productos de Limpieza', 57, 9),
(753, 3, 'Trabajadora Residencial', 57, 3),
(754, 3, 'Bono de alimentacion', 57, 3),
(755, 3, 'Bono de ayuda', 57, 3),
(756, 3, 'Seguridad Social', 57, 3),
(757, 5, 'Comisiones Bancaracias', 57, 10),
(758, 5, 'Exencion cuota del administrador', 57, 10),
(759, 179.43, 'GAS LARA', 67, 1),
(760, 222, 'CORPOELEC', 67, 2),
(761, 333, 'HIDROLARA', 67, 2),
(762, 777, 'Bolsas de Basura', 67, 9),
(763, 123, 'Productos de Limpieza', 67, 9),
(764, 666, 'Mantenimiento ascensor', 67, 4),
(765, 444, 'Trabajadora Residencial', 67, 3),
(766, 555, 'Bono de alimentacion', 67, 3),
(767, 555, 'Bono de ayuda', 67, 3),
(768, 666, 'Seguridad Social', 67, 3),
(769, 321, 'Comisiones Bancaracias', 67, 10),
(770, 234, 'Exencion cuota del administrador', 67, 10),
(771, 23, 'GAS LARA', 68, 1),
(772, 24, 'CORPOELEC', 68, 2),
(773, 24, 'HIDROLARA', 68, 2),
(774, 25, 'Trabajadora Residencial', 68, 3),
(775, 52, 'Bono de alimentacion', 68, 3),
(776, 12, 'Bono de ayuda', 68, 3),
(777, 31, 'Seguridad Social', 68, 3),
(778, 34, 'Bolsas de Basura', 68, 9),
(779, 53, 'Productos de Limpieza', 68, 9),
(780, 42, 'Mantenimiento ascensor', 68, 4),
(781, 546, 'Comisiones Bancaracias', 68, 10),
(782, 34, 'Exencion cuota del administrador', 68, 10),
(783, 100, 'GAS LARA', 73, 1),
(784, 100, 'CORPOELEC', 73, 2),
(785, 100, 'HIDROLARA', 73, 2),
(786, 100, 'Trabajadora Residencial', 73, 3),
(787, 101, 'Bono de alimentacion', 73, 3),
(788, 100, 'Bono de ayuda', 73, 3),
(789, 100, 'Seguridad Social', 73, 3),
(790, 100, 'Bolsas de Basura', 73, 9),
(791, 100, 'Productos de Limpieza', 73, 9),
(792, 100, 'Comisiones Bancaracias', 73, 10),
(793, 100, 'Exencion cuota del administrador', 73, 10),
(794, 100, 'Mantenimiento ascensor', 73, 4),
(795, 10, 'GAS LARA', 74, 1),
(796, 20, 'CORPOELEC', 74, 2),
(797, 10, 'HIDROLARA', 74, 2),
(798, 20, 'Trabajadora Residencial', 74, 3),
(799, 207.89, 'Bono de alimentacion', 74, 3),
(800, 20, 'Bono de ayuda', 74, 3),
(801, 10, 'Seguridad Social', 74, 3),
(802, 20, 'Mantenimiento ascensor', 74, 4),
(803, 10, 'Bolsas de Basura', 74, 9),
(804, 20, 'Productos de Limpieza', 74, 9),
(805, 1, 'Comisiones Bancaracias', 74, 10),
(806, 12, 'Exencion cuota del administrador', 74, 10),
(807, 123, 'detalle prueba', 67, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id_gasto` int(11) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `tipo_gasto_id` int(11) NOT NULL,
  `solicitud_id` int(11) DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `descripcion_gasto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gastos`
--

INSERT INTO `gastos` (`id_gasto`, `tipo`, `tipo_gasto_id`, `solicitud_id`, `proveedor_id`, `descripcion_gasto`) VALUES
(94, 'fijo', 1, 8, 1, 'pago del gas'),
(101, 'fijo', 2, 8, 2, 'pago del cable'),
(109, 'variable', 10, NULL, NULL, 'Reposición de Caja Chica'),
(110, 'variable', 10, NULL, NULL, 'Reposición de Caja Chica'),
(111, 'variable', 10, NULL, NULL, 'Reposición de Caja Chica'),
(112, 'fijo', 1, 8, 1, 'pago del gas'),
(114, 'variable', 10, NULL, NULL, 'Reposición de Caja Chica');

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
(14, 'pepe', 'pipas', '12341212', '10128545122', 'jasda@gasmi.com', '2000-10-10', 'Masculino'),
(15, 'sexo', 'jaaaj', '12131313', '12323122321', 'asdasd@gas.com', '2001-09-03', 'Masculino'),
(17, 'Juan', 'Jimenez', '12312312', '12312312222', 'juan@gmail.com', '1999-10-10', 'Masculino'),
(19, 'Rafael', 'Guiterez', '12345678', '12312322222', 'rafita@gmail.com', '1999-10-10', 'Masculino'),
(20, 'Francisco', 'Mendoza', '1231232', '23122221112', 'fran@gmail.com', '1999-10-10', 'Masculino'),
(21, 'Maria', 'Mendoza', '1111111', '11212111111', 'asdasd@gasd.com', '1900-11-11', 'Femenino'),
(22, 'asdasd', 'asdasd', '1112111', '12312312222', 'asdasda2@gasf.cmo', '1999-11-11', 'Femenino'),
(24, 'Pedro', 'Pedro', '12121212', '21212121212', 'pedro@gmail.com', '1999-10-10', 'Femenino'),
(25, 'Juan', 'Perez', '41223212', '04224223322', 'juab@gami.com', '2000-10-10', 'Masculino');

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
(15, 11, 14, 'Habitante'),
(17, 11, 17, 'Propietario'),
(19, 12, 19, 'Habitante'),
(20, 12, 20, 'Habitante'),
(21, 12, 21, 'Propietario'),
(24, 15, 24, 'Propietario'),
(25, 16, 25, 'Propietario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensualidad`
--

CREATE TABLE `mensualidad` (
  `id_mensualidad` int(11) NOT NULL,
  `monto` float NOT NULL,
  `tasa_dolar` float NOT NULL,
  `mes` varchar(2) NOT NULL,
  `anio` varchar(4) NOT NULL,
  `apartamento_id` int(11) NOT NULL,
  `porcentaje_interes` int(11) NOT NULL DEFAULT 10,
  `limite_mensualidad` int(11) NOT NULL DEFAULT 15
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensualidad`
--

INSERT INTO `mensualidad` (`id_mensualidad`, `monto`, `tasa_dolar`, `mes`, `anio`, `apartamento_id`, `porcentaje_interes`, `limite_mensualidad`) VALUES
(279, 29.14, 29.14, '3', '2025', 11, 10, 15),
(280, 151.52, 151.52, '3', '2025', 12, 10, 15),
(281, 65.38, 65.38, '3', '2025', 15, 10, 15),
(290, 318.94, 187.29, '5', '2025', 11, 10, 15),
(291, 318.94, 187.29, '5', '2025', 12, 10, 15),
(292, 243, 187.29, '5', '2025', 15, 10, 15),
(293, 303.75, 187.29, '5', '2025', 16, 10, 15),
(302, 68.88, 187.29, '6', '2025', 11, 10, 15),
(303, 68.88, 187.29, '6', '2025', 12, 10, 15),
(304, 52.48, 187.29, '6', '2025', 15, 10, 15),
(305, 65.6, 187.29, '6', '2025', 16, 10, 15),
(306, 30.45, 187.29, '4', '2025', 11, 10, 15),
(307, 30.45, 187.29, '4', '2025', 12, 10, 15),
(308, 23.2, 187.29, '4', '2025', 15, 10, 15),
(309, 29, 187.29, '4', '2025', 16, 10, 15),
(315, 19.47, 207.89, '7', '2025', 11, 10, 15),
(316, 19.47, 207.89, '7', '2025', 12, 10, 15),
(317, 14.84, 207.89, '7', '2025', 15, 10, 15),
(319, 20.12, 1, '4', '2025', 11, 10, 15),
(321, 20.12, 1, '4', '2025', 11, 10, 15),
(323, 20.12, 1, '4', '2025', 11, 10, 15),
(324, 20.12, 1, '4', '2025', 11, 10, 15);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_caja`
--

CREATE TABLE `movimientos_caja` (
  `id_movimiento_caja` int(11) NOT NULL,
  `concepto` varchar(100) NOT NULL,
  `monto` float NOT NULL,
  `fecha` date NOT NULL,
  `estado` varchar(50) NOT NULL,
  `caja_chica_id` int(11) NOT NULL,
  `gasto_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos_caja`
--

INSERT INTO `movimientos_caja` (`id_movimiento_caja`, `concepto`, `monto`, `fecha`, `estado`, `caja_chica_id`, `gasto_id`) VALUES
(1, 'Comprar cafe', 10, '2025-10-04', 'Reposado', 21, 109),
(2, 'mas cafe', 15, '2024-10-02', 'Pendiente por reposicion', 22, NULL),
(3, 'arroz', 11, '2025-10-04', 'Reposado', 21, 109),
(7, 'pasta', 4, '2024-10-01', 'Pendiente por reposicion', 22, NULL),
(8, 'soda', 12, '2010-10-10', 'Reposado', 21, 109),
(9, 'gasto menor', 0.55, '2025-10-03', 'Reposado', 21, 109),
(10, 'gasto menor', 54.39, '2025-10-01', 'Reposado', 21, 109),
(11, 'limpieza', 50, '2025-10-01', 'Reposado', 21, 109),
(13, 'gasto menor', 10, '2025-10-07', 'Reposado', 21, 110),
(14, 'gasto menor', 5, '2025-10-06', 'Reposado', 21, 110),
(15, 'sal', 5, '2025-10-01', 'Reposado', 21, 110),
(16, 'mas cafe', 25, '2025-10-06', 'Reposado', 21, 111),
(17, 'mas coffee', 5, '2025-01-10', 'Reposado', 21, 114),
(18, 'se compro cafe', 10, '2010-10-10', 'Reposado', 21, 114),
(20, 'traspore', 25, '2025-10-08', 'Pendiente por reposicion', 21, NULL),
(21, 'trasnpoasd', 25, '2025-10-10', 'Pendiente por reposicion', 21, NULL),
(22, 'prueba de edicion', 15, '2025-10-10', 'Reposado', 23, 109),
(25, 'prueba de registro', 20, '2025-10-10', 'Reposado', 23, 109),
(26, 'prueba de registro', 20, '2025-10-10', 'Reposado', 23, 109),
(27, 'prueba de registro', 20, '2025-10-10', 'Reposado', 23, 109);

--
-- Disparadores `movimientos_caja`
--
DELIMITER $$
CREATE TRIGGER `tr_after_delete_movimiento_caja` AFTER DELETE ON `movimientos_caja` FOR EACH ROW BEGIN
    UPDATE caja_chica
    SET saldo_actual = saldo_actual + OLD.monto
    WHERE id_caja_chica = OLD.caja_chica_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_after_insert_movimiento_caja` AFTER INSERT ON `movimientos_caja` FOR EACH ROW BEGIN
    UPDATE caja_chica
    SET saldo_actual = saldo_actual - NEW.monto
    WHERE id_caja_chica = NEW.caja_chica_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_after_update_movimiento_caja` AFTER UPDATE ON `movimientos_caja` FOR EACH ROW BEGIN
    -- Revertir el monto antiguo
    UPDATE caja_chica
    SET saldo_actual = saldo_actual + OLD.monto
    WHERE id_caja_chica = OLD.caja_chica_id;

    -- Aplicar el nuevo monto
    UPDATE caja_chica
    SET saldo_actual = saldo_actual - NEW.monto
    WHERE id_caja_chica = NEW.caja_chica_id;
END
$$
DELIMITER ;

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
(82, 'Procesado', 'test'),
(84, 'Procesado', 'pago'),
(85, 'Procesado', 'test'),
(86, 'Procesado', 'testa');

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
(136, 160, 280),
(137, 161, 280),
(138, 162, 281),
(139, 163, 281);

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
(56, '2025-01-01', 1, 'Mes de enero'),
(57, '2025-02-01', 1, 'Mes de Febrero'),
(67, '2025-02-01', 150, 'Prueba unitaria editada'),
(68, '2025-04-01', 12, 'Mes de abril'),
(73, '2025-06-01', 111, 'Mes de Junio'),
(74, '2025-07-01', 10, 'uj'),
(75, '2025-04-01', 1000, 'Prueba unitaria'),
(76, '2025-04-01', 1000, 'Prueba unitaria');

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
(1275, 795, 315),
(1276, 796, 315),
(1277, 797, 315),
(1278, 798, 315),
(1279, 799, 315),
(1280, 800, 315),
(1281, 801, 315),
(1282, 802, 315),
(1283, 803, 315),
(1284, 804, 315),
(1285, 805, 315),
(1286, 806, 315),
(1290, 795, 316),
(1291, 796, 316),
(1292, 797, 316),
(1293, 798, 316),
(1294, 799, 316),
(1295, 800, 316),
(1296, 801, 316),
(1297, 802, 316),
(1298, 803, 316),
(1299, 804, 316),
(1300, 805, 316),
(1301, 806, 316),
(1305, 795, 317),
(1306, 796, 317),
(1307, 797, 317),
(1308, 798, 317),
(1309, 799, 317),
(1310, 800, 317),
(1311, 801, 317),
(1312, 802, 317),
(1313, 803, 317),
(1314, 804, 317),
(1315, 805, 317),
(1316, 806, 317);

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
(2, 'Proimca', 'Luz', 'v1231231', 'Quibor'),
(3, 'Jardinero', 'Trabajos en jardineria', 'E13123', 'terminal'),
(4, 'Reparaciones CA', 'Reparar', 'V234234', 'Zona industrial');

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
(8, '2025-09-01', 'Solicitud de consumo de algo', 'Pablo', 10, 'Pendiente', 56, '2'),
(10, '2010-10-10', 'Solicitud de gasto de ejemplo', 'Juan', 15, 'Pendiente', 56, '1');

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
-- Indices de la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  ADD PRIMARY KEY (`id_movimiento_caja`),
  ADD KEY `caja_chica_id` (`caja_chica_id`),
  ADD KEY `gasto_id` (`gasto_id`);

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
  MODIFY `id_anio_fiscal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `apartamentos`
--
ALTER TABLE `apartamentos`
  MODIFY `id_apartamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `bancos`
--
ALTER TABLE `bancos`
  MODIFY `id_banco` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `banco_transacciones`
--
ALTER TABLE `banco_transacciones`
  MODIFY `id_banco_transaccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT de la tabla `caja_chica`
--
ALTER TABLE `caja_chica`
  MODIFY `id_caja_chica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `detalles_gastos`
--
ALTER TABLE `detalles_gastos`
  MODIFY `id_detalle_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT de la tabla `detalles_pagos`
--
ALTER TABLE `detalles_pagos`
  MODIFY `id_detalle_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;

--
-- AUTO_INCREMENT de la tabla `detalles_presupuesto`
--
ALTER TABLE `detalles_presupuesto`
  MODIFY `id_detalle_presupuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=810;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT de la tabla `habitantes`
--
ALTER TABLE `habitantes`
  MODIFY `id_habitante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `habitantes_apartamentos`
--
ALTER TABLE `habitantes_apartamentos`
  MODIFY `id_habitante_apartamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `mensualidad`
--
ALTER TABLE `mensualidad`
  MODIFY `id_mensualidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=325;

--
-- AUTO_INCREMENT de la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  MODIFY `id_movimiento_caja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT de la tabla `pagos_mensualidad`
--
ALTER TABLE `pagos_mensualidad`
  MODIFY `id_pago_mensualidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT de la tabla `presupuesto`
--
ALTER TABLE `presupuesto`
  MODIFY `id_presupuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT de la tabla `presupuesto_mensualidad`
--
ALTER TABLE `presupuesto_mensualidad`
  MODIFY `id_presupuesto_mensualidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1409;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `solicitudes_gasto`
--
ALTER TABLE `solicitudes_gasto`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `tipo_gasto`
--
ALTER TABLE `tipo_gasto`
  MODIFY `id_tipo_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
-- Filtros para la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  ADD CONSTRAINT `movimientos_caja_ibfk_1` FOREIGN KEY (`caja_chica_id`) REFERENCES `caja_chica` (`id_caja_chica`),
  ADD CONSTRAINT `movimientos_caja_ibfk_2` FOREIGN KEY (`gasto_id`) REFERENCES `gastos` (`id_gasto`);

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
