-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-02-2026 a las 04:45:36
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
    DECLARE v_saldo_final_mes_anterior DECIMAL(15,2); 
    DECLARE v_fecha_actual DATE;
    DECLARE v_id_anio_fiscal INT;
    DECLARE v_nombre_mes_espanol VARCHAR(20);
    
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
    
        
        UPDATE caja_chica 
        SET estado = 'Cerrada'
        WHERE estado = 'Abierta'
        AND anio_fiscal_id = v_id_anio_fiscal;
        
        
        SET v_saldo_final_mes_anterior = NULL;
        
        SELECT saldo_actual INTO v_saldo_final_mes_anterior
        FROM caja_chica
        WHERE anio_fiscal_id = v_id_anio_fiscal
        ORDER BY fecha_creacion DESC
        LIMIT 1;
        
        
        IF v_saldo_final_mes_anterior IS NULL THEN
            SET v_saldo_final_mes_anterior = v_monto_fondo_fijo;
        END IF;
        
        
        SET v_nombre_mes_espanol = 
            CASE MONTH(v_fecha_actual)
                WHEN 1 THEN 'Enero' WHEN 2 THEN 'Febrero' WHEN 3 THEN 'Marzo'
                WHEN 4 THEN 'Abril' WHEN 5 THEN 'Mayo' WHEN 6 THEN 'Junio'
                WHEN 7 THEN 'Julio' WHEN 8 THEN 'Agosto' WHEN 9 THEN 'Septiembre'
                WHEN 10 THEN 'Octubre' WHEN 11 THEN 'Noviembre' WHEN 12 THEN 'Diciembre'
                ELSE 'Desconocido'
            END;
        
        
        
        
        INSERT INTO caja_chica 
            (fecha_creacion, fondo_fijo, saldo_actual, estado, 
            descripcion, anio_fiscal_id) 
        VALUES 
            (v_fecha_actual, v_monto_fondo_fijo, v_saldo_final_mes_anterior, 
            'Abierta', CONCAT('Caja chica del mes ', v_nombre_mes_espanol, ' - ', YEAR(v_fecha_actual)), v_id_anio_fiscal);
       
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
        v_proveedor_id, v_tipo_gasto_id, v_solicitud_gasto_id, 'Reposición', 1
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
    SELECT CONCAT('Reposición exitosa. Gasto ID: ', v_gasto_id, '. Total Repuesto: ', v_saldo_acumulado) AS mensaje;
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
(45, 'Cerrada', '2026-02-02', '2027-02-02', 'Año fiscal 2026', 0),
(47, 'Abierto', '2026-02-07', '2027-02-07', 'asda', 1);

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
(30, '1-2', 23, 1, 1, 1, 1),
(31, '2-3', 23, 1, 1, 1, 1),
(32, '2-1', 1, 2, 1, 1, 1),
(33, '12', 23, 1, 1, 1, 0),
(34, '2-8', 2, 1, 2, 1, 0);

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
(1, 'venezuela', '0102', '0102123412124232323', 'Ahorro', '04152456842', '23232421', 1),
(6, 'Banesco', '0117', '1242342342424121211', 'Corriente', '04142584985', 'V5464565', 1),
(9, 'Bancaribe', '0114', '01140300063000253595', 'Corriente', '04114124142', 'J305785457', 1),
(11, 'rasdas', '1231', '2342342342342342323', '', '21321253213', 'V2123132', 0),
(12, 'tesoro', '1231', '425646456456456456', '', '24243245564', 'V12345678', 0),
(13, 'tesoros', '0102', '2423423423423234234', '', '23423423232', 'V123412321', 1);

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
(23, 1000.00, 'Abierto', 'Caja chicas del mes Febreros - 2026', '2026-02-06', 45, 1);

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
(178, '2026-02-05', 12.00, 0.00, 'Pago Movil', 132, 'adiossssssssssssssss'),
(213, '2026-02-21', 32.00, 0.00, 'Efectivo', 134, 'detalle 1 s'),
(214, '2026-02-06', 12.00, 0.00, 'Pago Movil', 134, 'detalle 2s');

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
(374, '2026-02-05', 12.00, 0.03, 'Pago Movil', 117),
(375, '2026-02-20', 100.00, 0.25, 'Efectivo', 117),
(376, '2026-02-04', 100.00, 0.25, 'Transferencia', 117);

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
(660, 42.00, 0.00, 'GAS LARA', 70, 5),
(661, 15.00, 0.00, 'CORPOELEC', 70, 2),
(662, 34.00, 0.00, 'HIDROLARA', 70, 2),
(663, 23.00, 0.00, 'Trabajadora Residencial', 70, 3),
(664, 5623.00, 0.00, 'Bono de alimentacion', 70, 3),
(665, 2.00, 0.00, 'Bono de ayuda', 70, 3),
(666, 123.00, 0.00, 'Seguridad Social', 70, 3),
(667, 21.00, 0.00, 'Mantenimiento ascensor', 70, 4),
(668, 23.00, 0.00, 'Bolsas de Basura', 70, 9),
(669, 21.00, 0.00, 'Productos de Limpieza', 70, 9),
(670, 124.00, 0.00, 'Comisiones Bancarias', 70, 10),
(671, 12.00, 0.00, 'Exencion cuota del administrador', 70, 10),
(745, 1.00, 0.00, 'GAS LARA', 57, 5),
(746, 2.00, 0.00, 'gato', 57, 5),
(747, 2.00, 0.00, 'CORPOELEC', 57, 2),
(748, 2.00, 0.00, 'HIDROLARA', 57, 2),
(749, 2.00, 0.00, 'pepe', 57, 2),
(750, 3.00, 0.00, 'Mantenimiento ascensor', 57, 4),
(751, 4.00, 0.00, 'Bolsas de Basura', 57, 9),
(752, 4.00, 0.00, 'Productos de Limpieza', 57, 9),
(753, 3.00, 0.00, 'Trabajadora Residencial', 57, 3),
(754, 3.00, 0.00, 'Bono de alimentacion', 57, 3),
(755, 3.00, 0.00, 'Bono de ayuda', 57, 3),
(756, 3.00, 0.00, 'Seguridad Social', 57, 3),
(757, 5.00, 0.00, 'Comisiones Bancarias', 57, 10),
(758, 5.00, 0.00, 'Exencion cuota del administrador', 57, 10),
(759, 179.43, 0.00, 'GAS LARA', 67, 5),
(760, 222.00, 0.00, 'CORPOELEC', 67, 2),
(761, 333.00, 0.00, 'HIDROLARA', 67, 2),
(762, 777.00, 0.00, 'Bolsas de Basura', 67, 9),
(763, 123.00, 0.00, 'Productos de Limpieza', 67, 9),
(764, 666.00, 0.00, 'Mantenimiento ascensor', 67, 4),
(765, 444.00, 0.00, 'Trabajadora Residencial', 67, 3),
(766, 555.00, 0.00, 'Bono de alimentacion', 67, 3),
(767, 555.00, 0.00, 'Bono de ayuda', 67, 3),
(768, 666.00, 0.00, 'Seguridad Social', 67, 3),
(769, 321.00, 0.00, 'Comisiones Bancarias', 67, 10),
(770, 234.00, 0.00, 'Exencion cuota del administrador', 67, 10),
(771, 23.00, 0.00, 'GAS LARA', 68, 5),
(772, 24.00, 0.00, 'CORPOELEC', 68, 2),
(773, 24.00, 0.00, 'HIDROLARA', 68, 2),
(774, 25.00, 0.00, 'Trabajadora Residencial', 68, 3),
(775, 52.00, 0.00, 'Bono de alimentacion', 68, 3),
(776, 12.00, 0.00, 'Bono de ayuda', 68, 3),
(777, 31.00, 0.00, 'Seguridad Social', 68, 3),
(778, 34.00, 0.00, 'Bolsas de Basura', 68, 9),
(779, 53.00, 0.00, 'Productos de Limpieza', 68, 9),
(780, 42.00, 0.00, 'Mantenimiento ascensor', 68, 4),
(781, 546.00, 0.00, 'Comisiones Bancarias', 68, 10),
(782, 34.00, 0.00, 'Exencion cuota del administrador', 68, 10),
(819, 20.00, 0.00, 'GAS LARA', 76, 5),
(820, 12.00, 0.00, 'Trabajadora Residencial', 76, 3),
(821, 10.00, 0.00, 'Bono de alimentacion', 76, 3),
(822, 15.00, 0.00, 'Bono de ayuda', 76, 3),
(823, 10.00, 0.00, 'Seguridad Social', 76, 3),
(824, 15.00, 0.00, 'Mantenimiento ascensor', 76, 4),
(825, 41.00, 0.00, 'Bolsas de Basura', 76, 9),
(826, 41.00, 0.00, 'Productos de Limpieza', 76, 9),
(827, 54.00, 0.00, 'Comisiones Bancarias', 76, 10),
(828, 42.00, 0.00, 'Exencion cuota del administrador', 76, 10),
(829, 15.00, 0.00, 'CORPOELEC', 76, 2),
(830, 10.00, 0.00, 'HIDROLARA', 76, 2),
(831, 13.00, 0.00, 'GAS LARA', 77, 5),
(832, 14.00, 0.00, 'CORPOELEC', 77, 2),
(833, 2.00, 0.00, 'HIDROLARA', 77, 2),
(834, 12.00, 0.00, 'Mantenimiento ascensor', 77, 4),
(835, 24.00, 0.00, 'Bolsas de Basura', 77, 9),
(836, 12.00, 0.00, 'Productos de Limpieza', 77, 9),
(837, 12.00, 0.00, 'Comisiones Bancarias', 77, 10),
(838, 124.00, 0.00, 'Exencion cuota del administrador', 77, 10),
(839, 23.00, 0.00, 'Trabajadora Residencial', 77, 3),
(840, 23.00, 0.00, 'Bono de alimentacion', 77, 3),
(841, 42.00, 0.00, 'Bono de ayuda', 77, 3),
(842, 42.00, 0.00, 'Seguridad Social', 77, 3),
(1095, 1000.00, 0.00, 'GAS LARA', 98, 5),
(1096, 0.00, 0.00, 'CORPOELEC', 98, 2),
(1097, 0.00, 0.00, 'HIDROLARA', 98, 2),
(1098, 0.00, 0.00, 'Trabajadora Residencial', 98, 3),
(1099, 0.00, 0.00, 'Bono de alimentacion', 98, 3),
(1100, 0.00, 0.00, 'Bono de ayuda', 98, 3),
(1101, 0.00, 0.00, 'Seguridad Social', 98, 3),
(1102, 0.00, 0.00, 'Mantenimiento ascensor', 98, 4),
(1103, 0.00, 0.00, 'Bolsas de Basura', 98, 9),
(1104, 0.00, 0.00, 'Productos de Limpieza', 98, 9),
(1105, 0.00, 0.00, 'Comisiones Bancarias', 98, 10),
(1106, 0.00, 0.00, 'Exencion cuota del administrador', 98, 10),
(1179, 3.00, 0.00, 'CORPOELEC', 56, 2),
(1180, 4.00, 0.00, 'HIDROLARA', 56, 2),
(1181, 2.00, 0.00, 'Bolsas de Basura', 56, 9),
(1182, 3.00, 0.00, 'Productos de Limpieza', 56, 9),
(1183, 2.00, 0.00, 'GAS LARA', 56, 5),
(1184, 1.00, 0.00, 'Mantenimiento ascensor', 56, 4),
(1185, 5.00, 0.00, 'Trabajadora Residencial', 56, 3),
(1186, 6.00, 0.00, 'Bono de alimentacion', 56, 3),
(1187, 7.00, 0.00, 'Bono de ayuda', 56, 3),
(1188, 8.00, 0.00, 'Seguridad Social', 56, 3),
(1189, 4.00, 0.00, 'Comisiones Bancarias', 56, 10),
(1190, 5.00, 0.00, 'Exencion cuota del administrador', 56, 10),
(1239, 1.50, 0.00, 'CORPOELEC', 101, 2),
(1240, 23.00, 0.00, 'HIDROLARA', 101, 2),
(1241, 231.00, 0.00, 'Trabajadora Residencial', 101, 3),
(1242, 232.00, 0.00, 'Bono de alimentacion', 101, 3),
(1243, 54.00, 0.00, 'Bono de ayuda', 101, 3),
(1244, 89.00, 0.00, 'Seguridad Social', 101, 3),
(1245, 245.00, 0.00, 'Mantenimiento ascensor', 101, 4),
(1246, 546.00, 0.00, 'GAS LARA', 101, 5),
(1247, 545.00, 0.00, 'Bolsas de Basura', 101, 9),
(1248, 564.00, 0.00, 'Productos de Limpieza', 101, 9),
(1249, 564.00, 0.00, 'Comisiones Bancarias', 101, 10),
(1250, 654.00, 0.00, 'Exencion cuota del administrador', 101, 10),
(1251, 14.00, 0.00, 'CORPOELEC', 84, 2),
(1252, 2.00, 0.00, 'HIDROLARA', 84, 2),
(1253, 52.00, 0.00, 'Trabajadora Residencial', 84, 3),
(1254, 32.00, 0.00, 'Bono de alimentacion', 84, 3),
(1255, 24.00, 0.00, 'Bono de ayuda', 84, 3),
(1256, 12.00, 0.00, 'Seguridad Social', 84, 3),
(1257, 41.00, 0.00, 'Mantenimiento ascensor', 84, 4),
(1258, 13.00, 0.00, 'GAS LARA', 84, 5),
(1259, 23.00, 0.00, 'Bolsas de Basura', 84, 9),
(1260, 24.00, 0.00, 'Productos de Limpieza', 84, 9),
(1261, 10.00, 0.00, 'Comisiones Bancarias', 84, 10),
(1262, 21.00, 0.00, 'Exencion cuota del administrador', 84, 10);

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
('123123123', 'CSS-Logo_1771706140_757.jpg', 1, 178),
('412123', 'mensualidad_1771710356_291.PNG', 6, 214);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id_gasto` int(11) NOT NULL,
  `clasificacion` varchar(20) NOT NULL,
  `tipo_gasto_id` int(11) NOT NULL,
  `solicitud_id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `descripcion_gasto` text NOT NULL DEFAULT 'Sin descripción',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gastos`
--

INSERT INTO `gastos` (`id_gasto`, `clasificacion`, `tipo_gasto_id`, `solicitud_id`, `proveedor_id`, `descripcion_gasto`, `activo`) VALUES
(132, 'fijo', 2, 12, 2, 'holasssssssssssssssaaa', 1),
(133, 'fijo', 2, 8, 3, 'asdasdasasd', 0),
(134, 'fijo', 2, 12, 3, '222222222222224', 1);

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
(32, 'carlos', 'rodriega', 'V30601403', '01241223423', 'carlos@gmail.com', '2001-10-10', 'Masculino', 1),
(35, 'pepe', 'perez', 'V12312341', '12351513255', 'perez@gmail.com', '1999-10-10', 'Masculino', 1),
(36, 'asdasda', 'sdasdas', 'V21321332', '12313233212', 'asdasd@fas.cp', '1999-10-10', 'Masculino', 0),
(37, 'pepe', 'pepas', 'E2112332', '04121235221', 'epep@gmail.com', '1999-10-10', 'Masculino', 1),
(38, 'asdasd', 'asdasd', 'V3060143', '12312312312', 'asd@gasd.com', '1999-02-04', 'Masculino', 1),
(39, 'deee', 'sdfsdfs', 'V4242142', '23423423332', 'esad2f@agasd.com', '1999-10-10', 'Masculino', 0);

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
(30, 32, 'Propietario'),
(30, 39, 'Habitante'),
(31, 37, 'Propietario'),
(32, 35, 'Propietario'),
(32, 36, 'Habitante');

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
('2312', 'virustotla_1771651369_672.PNG', 6, 370),
('4213123', 'lenguaje_comun_1771707846_116.PNG', 6, 374),
('234sad2', 'colores_inicio_2_1771708881_962.PNG', 9, 376);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensualidad`
--

CREATE TABLE `mensualidad` (
  `id_mensualidad` int(11) NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `tasa_dolar` decimal(15,2) NOT NULL,
  `mes` varchar(2) NOT NULL,
  `anio` varchar(4) NOT NULL,
  `apartamento_id` int(11) NOT NULL,
  `porcentaje_interes` int(11) NOT NULL DEFAULT 10,
  `limite_mensualidad` int(11) NOT NULL DEFAULT 15,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensualidad`
--

INSERT INTO `mensualidad` (`id_mensualidad`, `monto`, `tasa_dolar`, `mes`, `anio`, `apartamento_id`, `porcentaje_interes`, `limite_mensualidad`, `activo`) VALUES
(533, 2.53, 402.33, '1', '2025', 30, 10, 15, 1),
(534, 0.11, 402.33, '1', '2025', 32, 10, 15, 1),
(535, 2.53, 402.33, '1', '2025', 31, 10, 15, 1),
(536, 890.33, 402.33, '10', '2025', 30, 10, 15, 1),
(537, 890.33, 402.33, '10', '2025', 31, 10, 15, 1),
(538, 38.71, 402.33, '10', '2025', 32, 10, 15, 1);

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
(32, 'CAFE', 50.00, '2026-02-06', 'Repuesto', 23, 1),
(33, 'pan', 75.00, '2026-02-06', 'Repuesto', 23, 1),
(34, 'cafe', 25.00, '2026-02-06', 'Repuesto', 23, 1),
(35, '1231asd', 1.00, '2026-02-05', 'Pendiente por reposicion', 23, 0),
(36, 'cafs', 398.75, '2026-02-19', 'Repuesto', 23, 1);

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
(116, 'Procesado', 'asdasd', 0),
(117, 'No verificado', 'sesss', 1),
(118, 'No verificado', 'si mi panax', 1);

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
(370, 536),
(371, 536),
(374, 533),
(375, 533),
(376, 533);

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
(56, '2025-01-01', 1, 'presupuesto modificado 5864', 0),
(57, '2025-02-01', 1, 'Mes de Febrero', 1),
(67, '2025-03-01', 122, 'Mes de Marzo', 1),
(68, '2025-04-01', 12, 'Mes de abril', 1),
(70, '2025-05-01', 12, 'mayo', 1),
(76, '2025-06-01', 10, 'asdas', 1),
(77, '2025-07-01', 12, 'asdas', 1),
(84, '2025-08-01', 12, 'agosto', 1),
(98, '2025-09-01', 1000, 'presupuesto Varios Selenium 2024', 0),
(101, '2025-10-01', 125, 'si mi pana', 1);

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
(1183, 533),
(1183, 534),
(1183, 535),
(1189, 533),
(1189, 534),
(1189, 535),
(1190, 533),
(1190, 534),
(1190, 535);

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
(5, 'Gas Lara', 'Gas', 'V2352345', 'Lara', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reposiciones`
--

CREATE TABLE `reposiciones` (
  `gasto_id` int(11) NOT NULL,
  `movimiento_caja_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(8, '2025-09-01', 'Solicitud de consumo de algo', 'Pablo', 10, 'Pendiente', 56, '2', 1),
(10, '2010-10-10', 'Solicitud de gasto de ejemplo', 'Juan', 15, 'Pendiente', 56, '1', 0),
(12, '2026-02-06', 'Sin Solicitud', 'Administracion', 1, 'Pendiente', 56, '3', 1),
(13, '2026-02-12', 'seee', 'pepe', 12, 'Pendiente', 56, '2', 0);

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
(15, 'hola', 0);

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

CREATE ALGORITHM=UNDEFINED DEFINER=`app_condominio`@`localhost` SQL SECURITY DEFINER VIEW `vw_estado_cuentas_mensualidad`  AS SELECT `m`.`id_mensualidad` AS `id_mensualidad`, `a`.`nro_apartamento` AS `nro_apartamento`, `m`.`mes` AS `mes`, `m`.`anio` AS `anio`, `m`.`monto` AS `monto_cuota`, ifnull((select sum(`dp`.`monto`) from ((`pagos_mensualidad` `pm` join `detalles_pagos` `dp` on(`pm`.`detalle_pago_id` = `dp`.`id_detalle_pago`)) join `pagos` `p` on(`dp`.`pago_id` = `p`.`id_pago`)) where `pm`.`mensualidad_id` = `m`.`id_mensualidad` and `p`.`activo` = 1),0) AS `total_abonado`, `m`.`monto`- ifnull((select sum(`dp`.`monto`) from ((`pagos_mensualidad` `pm` join `detalles_pagos` `dp` on(`pm`.`detalle_pago_id` = `dp`.`id_detalle_pago`)) join `pagos` `p` on(`dp`.`pago_id` = `p`.`id_pago`)) where `pm`.`mensualidad_id` = `m`.`id_mensualidad` and `p`.`activo` = 1),0) AS `deuda_pendiente`, CASE WHEN `m`.`monto` - ifnull((select sum(`dp`.`monto`) from ((`pagos_mensualidad` `pm` join `detalles_pagos` `dp` on(`pm`.`detalle_pago_id` = `dp`.`id_detalle_pago`)) join `pagos` `p` on(`dp`.`pago_id` = `p`.`id_pago`)) where `pm`.`mensualidad_id` = `m`.`id_mensualidad` AND `p`.`activo` = 1),0) <= 0 THEN 'Solvente' ELSE 'Pendiente' END AS `estado_pago` FROM (`mensualidad` `m` join `apartamentos` `a` on(`m`.`apartamento_id` = `a`.`id_apartamento`)) WHERE `m`.`activo` = 1 AND `a`.`activo` = 1 ;

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
  ADD KEY `apartamento_id` (`apartamento_id`);

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
  MODIFY `id_anio_fiscal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `apartamentos`
--
ALTER TABLE `apartamentos`
  MODIFY `id_apartamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `bancos`
--
ALTER TABLE `bancos`
  MODIFY `id_banco` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `caja_chica`
--
ALTER TABLE `caja_chica`
  MODIFY `id_caja_chica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `detalles_gastos`
--
ALTER TABLE `detalles_gastos`
  MODIFY `id_detalle_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=215;

--
-- AUTO_INCREMENT de la tabla `detalles_pagos`
--
ALTER TABLE `detalles_pagos`
  MODIFY `id_detalle_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=377;

--
-- AUTO_INCREMENT de la tabla `detalles_presupuesto`
--
ALTER TABLE `detalles_presupuesto`
  MODIFY `id_detalle_presupuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1263;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT de la tabla `habitantes`
--
ALTER TABLE `habitantes`
  MODIFY `id_habitante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `mensualidad`
--
ALTER TABLE `mensualidad`
  MODIFY `id_mensualidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=539;

--
-- AUTO_INCREMENT de la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  MODIFY `id_movimiento_caja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT de la tabla `presupuesto`
--
ALTER TABLE `presupuesto`
  MODIFY `id_presupuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `solicitudes_gasto`
--
ALTER TABLE `solicitudes_gasto`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `tipo_gasto`
--
ALTER TABLE `tipo_gasto`
  MODIFY `id_tipo_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  ADD CONSTRAINT `gastos_ibfk_3` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_gasto` (`id_solicitud`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `mensualidad_ibfk_1` FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos` (`id_apartamento`) ON UPDATE CASCADE;

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
  ADD CONSTRAINT `solicitudes_gasto_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuesto` (`id_presupuesto`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
