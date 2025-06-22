-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-06-2025 a las 04:30:04
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
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_sincronizar_gastos_mensualidad` (IN `p_mensualidad_id` INT, IN `p_nuevos_gastos_ids` TEXT)   BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; 
    END;

    CREATE TEMPORARY TABLE IF NOT EXISTS TempNuevosGastos (gasto_id INT PRIMARY KEY);
    TRUNCATE TABLE TempNuevosGastos;

    SET @sql = CONCAT('INSERT INTO TempNuevosGastos (gasto_id) VALUES (', REPLACE(p_nuevos_gastos_ids, ',', '),('), ');');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    
    START TRANSACTION;

        DELETE FROM gastos_mensualidades
        WHERE
            mensualidad_id = p_mensualidad_id
            AND gasto_id NOT IN (SELECT gasto_id FROM TempNuevosGastos);

        INSERT INTO gastos_mensualidades (mensualidad_id, gasto_id)
        SELECT p_mensualidad_id, nuevos.gasto_id
        FROM TempNuevosGastos AS nuevos
        WHERE NOT EXISTS (
            SELECT 1
            FROM gastos_mensualidades AS existentes
            WHERE existentes.mensualidad_id = p_mensualidad_id AND existentes.gasto_id = nuevos.gasto_id
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
(1, '2025-01-01', NULL, 'Abierto', 'Año Fiscal 2025'),
(2, '2024-01-01', '2025-01-01', 'Cerrada', 'Caja chica 2024'),
(5, '2023-01-01', '2024-01-01', 'Abierto', 'Caja 2023');

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
(4, '1-1', 5.25, 1, 1, 1);

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
  `gasto_id` int(11) DEFAULT NULL,
  `banco_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, '2025-05-01', 1000, 850, 'Abierto', 'Caja chica Mayonesa 2025', 1),
(2, '2025-04-01', 500, 1000, 'Cerrada', 'Caja Abril 2025', 1),
(3, '2025-03-01', 200, 500, 'Cerrada', 'Caja Marzo 2025', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_pagos`
--

CREATE TABLE `detalles_pagos` (
  `id_detalle_pago` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `monto` float NOT NULL,
  `tasa_dolar` float NOT NULL,
  `tipo_pago` varchar(20) NOT NULL,
  `pago_id` int(11) NOT NULL,
  `caja_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_pagos`
--

INSERT INTO `detalles_pagos` (`id_detalle_pago`, `fecha`, `monto`, `tasa_dolar`, `tipo_pago`, `pago_id`, `caja_id`) VALUES
(1, '2025-05-09', 100, 100, 'Efectivo', 1, 2);

--
-- Disparadores `detalles_pagos`
--
DELIMITER $$
CREATE TRIGGER `actualizador_caja_pago` BEFORE INSERT ON `detalles_pagos` FOR EACH ROW BEGIN
    IF NEW.caja_id IS NOT NULL THEN
        UPDATE caja_chica 
        SET saldo_actual = saldo_actual - NEW.monto
        WHERE id_caja_chica = NEW.caja_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id_gasto` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `monto` float NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `tipo_gasto_id` int(11) NOT NULL,
  `tasa_dolar` float NOT NULL,
  `metodo_pago` varchar(20) NOT NULL,
  `solicitud_id` int(11) DEFAULT NULL,
  `caja_id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `descripcion_gasto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gastos`
--

INSERT INTO `gastos` (`id_gasto`, `fecha`, `monto`, `tipo`, `tipo_gasto_id`, `tasa_dolar`, `metodo_pago`, `solicitud_id`, `caja_id`, `proveedor_id`, `descripcion_gasto`) VALUES
(15, '2025-05-03', 105, 'Fijo', 3, 100, 'Efectivo', NULL, 1, 2, 'se pago la luz'),
(16, '2025-05-03', 200, 'Variable', 2, 100, 'Efectivo', NULL, 1, 4, 'Se reparo la nevera'),
(17, '2025-05-22', 300, 'Fijo', 1, 100, 'Efectivo', NULL, 1, 1, 'Pago gas'),
(18, '2025-05-24', 200, 'Fijo', 3, 100, 'Efectivo', NULL, 1, 2, 'Pago luz'),
(19, '2025-05-31', 50, 'Variable', 2, 100, 'Efectivo', NULL, 1, 4, 'Se reparo algo'),
(22, '2025-03-18', 300, 'Fijo', 1, 100, 'Efectivo', NULL, 3, 1, 'Pago gas'),
(23, '2025-04-15', 500, 'Fijo', 1, 100, 'Efectivo', NULL, 2, 1, 'Pago gas'),
(25, '2025-05-09', 150, 'Fijo', 2, 100, 'Efectivo', NULL, 1, 4, 'Reparation');

--
-- Disparadores `gastos`
--
DELIMITER $$
CREATE TRIGGER `actualizador_caja_gasto` AFTER INSERT ON `gastos` FOR EACH ROW BEGIN
    IF NEW.caja_id IS NOT NULL THEN
        UPDATE caja_chica 
        SET saldo_actual = saldo_actual - NEW.monto
        WHERE id_caja_chica = NEW.caja_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos_mensualidades`
--

CREATE TABLE `gastos_mensualidades` (
  `id_gasto_mensualidad` int(11) NOT NULL,
  `gasto_id` int(11) NOT NULL,
  `mensualidad_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gastos_mensualidades`
--

INSERT INTO `gastos_mensualidades` (`id_gasto_mensualidad`, `gasto_id`, `mensualidad_id`) VALUES
(126, 19, 91),
(127, 16, 91),
(129, 15, 91),
(130, 18, 91),
(131, 22, 95);

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
  `apartamento_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensualidad`
--

INSERT INTO `mensualidad` (`id_mensualidad`, `monto`, `tasa_dolar`, `mes`, `anio`, `apartamento_id`) VALUES
(91, 29.14, 100.34, '5', '2025', 4),
(94, 26.25, 102.81, '4', '2025', 4),
(95, 15.75, 102.81, '3', '2025', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `monto` float NOT NULL,
  `estado` varchar(20) NOT NULL,
  `observacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `monto`, `estado`, `observacion`) VALUES
(1, 400, 'procesado', 'pago de jose'),
(2, 500, 'procesado', 'pago de pepe'),
(3, 550, 'procesado', 'pago tralaleo tralala'),
(4, 450, 'procesado', 'pago de miguel');

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
(5, 1, 94),
(6, 1, 91);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

CREATE TABLE `personas` (
  `id_persona` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `apellido` varchar(20) NOT NULL,
  `cedula` varchar(9) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `correo` varchar(50) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `sexo` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personas`
--

INSERT INTO `personas` (`id_persona`, `nombre`, `apellido`, `cedula`, `telefono`, `correo`, `fecha_nacimiento`, `sexo`) VALUES
(1, 'pepe', 'pipas', '845321', '244211224', 'asfasd@gasmi.com', '2005-06-01', 'Masculino');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas_apartamentos`
--

CREATE TABLE `personas_apartamentos` (
  `id_persona_apartamento` int(11) NOT NULL,
  `apartamento_id` int(11) NOT NULL,
  `persona_id` int(11) NOT NULL,
  `tipo_vinculo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personas_apartamentos`
--

INSERT INTO `personas_apartamentos` (`id_persona_apartamento`, `apartamento_id`, `persona_id`, `tipo_vinculo`) VALUES
(3, 4, 1, 'Propietario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuestos_mensuales`
--

CREATE TABLE `presupuestos_mensuales` (
  `id_presupuesto` int(11) NOT NULL,
  `mes` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `monto_presupuesto` float NOT NULL,
  `descripcion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'Gas Lara', 'gas', '24425152', 'lara'),
(2, 'Proimca', 'luz', '1251312', 'quibor'),
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
  `presupuesto_mensual_id` int(11) NOT NULL,
  `prioridad` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_gasto`
--

CREATE TABLE `tipo_gasto` (
  `id_tipo_gasto` int(11) NOT NULL,
  `nombre_tipo_gasto` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_gasto`
--

INSERT INTO `tipo_gasto` (`id_tipo_gasto`, `nombre_tipo_gasto`) VALUES
(1, 'Servicio de Gas'),
(2, 'Reparaciones y Mantenimiento'),
(3, 'Servicios Basicos'),
(4, 'Sueldos y Salarios');

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
  ADD KEY `gasto_id` (`gasto_id`);

--
-- Indices de la tabla `caja_chica`
--
ALTER TABLE `caja_chica`
  ADD PRIMARY KEY (`id_caja_chica`),
  ADD KEY `anio_fiscal_id` (`anio_fiscal_id`);

--
-- Indices de la tabla `detalles_pagos`
--
ALTER TABLE `detalles_pagos`
  ADD PRIMARY KEY (`id_detalle_pago`),
  ADD KEY `detalles_pagos_ibfk_1` (`pago_id`),
  ADD KEY `caja_id` (`caja_id`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id_gasto`),
  ADD KEY `proveedor_id` (`proveedor_id`),
  ADD KEY `tipo_gasto_id` (`tipo_gasto_id`),
  ADD KEY `caja_id` (`caja_id`),
  ADD KEY `solicitud_id` (`solicitud_id`);

--
-- Indices de la tabla `gastos_mensualidades`
--
ALTER TABLE `gastos_mensualidades`
  ADD PRIMARY KEY (`id_gasto_mensualidad`),
  ADD KEY `gasto_id` (`gasto_id`),
  ADD KEY `mesualidad_id` (`mensualidad_id`);

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
-- Indices de la tabla `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`id_persona`) USING BTREE,
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `personas_apartamentos`
--
ALTER TABLE `personas_apartamentos`
  ADD PRIMARY KEY (`id_persona_apartamento`),
  ADD KEY `apartamento_id` (`apartamento_id`),
  ADD KEY `persona_id` (`persona_id`);

--
-- Indices de la tabla `presupuestos_mensuales`
--
ALTER TABLE `presupuestos_mensuales`
  ADD PRIMARY KEY (`id_presupuesto`);

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
  ADD KEY `presupuesto_mensual_id` (`presupuesto_mensual_id`);

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
  MODIFY `id_anio_fiscal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `apartamentos`
--
ALTER TABLE `apartamentos`
  MODIFY `id_apartamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `bancos`
--
ALTER TABLE `bancos`
  MODIFY `id_banco` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `banco_transacciones`
--
ALTER TABLE `banco_transacciones`
  MODIFY `id_banco_transaccion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `caja_chica`
--
ALTER TABLE `caja_chica`
  MODIFY `id_caja_chica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `detalles_pagos`
--
ALTER TABLE `detalles_pagos`
  MODIFY `id_detalle_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `gastos_mensualidades`
--
ALTER TABLE `gastos_mensualidades`
  MODIFY `id_gasto_mensualidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT de la tabla `mensualidad`
--
ALTER TABLE `mensualidad`
  MODIFY `id_mensualidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pagos_mensualidad`
--
ALTER TABLE `pagos_mensualidad`
  MODIFY `id_pago_mensualidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `id_persona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `personas_apartamentos`
--
ALTER TABLE `personas_apartamentos`
  MODIFY `id_persona_apartamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `presupuestos_mensuales`
--
ALTER TABLE `presupuestos_mensuales`
  MODIFY `id_presupuesto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `solicitudes_gasto`
--
ALTER TABLE `solicitudes_gasto`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_gasto`
--
ALTER TABLE `tipo_gasto`
  MODIFY `id_tipo_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `banco_transacciones`
--
ALTER TABLE `banco_transacciones`
  ADD CONSTRAINT `banco_transacciones_ibfk_1` FOREIGN KEY (`banco_id`) REFERENCES `bancos` (`id_banco`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `banco_transacciones_ibfk_2` FOREIGN KEY (`detalle_pago_id`) REFERENCES `detalles_pagos` (`id_detalle_pago`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `banco_transacciones_ibfk_3` FOREIGN KEY (`gasto_id`) REFERENCES `gastos` (`id_gasto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `caja_chica`
--
ALTER TABLE `caja_chica`
  ADD CONSTRAINT `caja_chica_ibfk_1` FOREIGN KEY (`anio_fiscal_id`) REFERENCES `anio_fiscal` (`id_anio_fiscal`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalles_pagos`
--
ALTER TABLE `detalles_pagos`
  ADD CONSTRAINT `detalles_pagos_ibfk_1` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id_pago`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalles_pagos_ibfk_2` FOREIGN KEY (`caja_id`) REFERENCES `caja_chica` (`id_caja_chica`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD CONSTRAINT `gastos_ibfk_2` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id_proveedor`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `gastos_ibfk_3` FOREIGN KEY (`tipo_gasto_id`) REFERENCES `tipo_gasto` (`id_tipo_gasto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `gastos_ibfk_4` FOREIGN KEY (`caja_id`) REFERENCES `caja_chica` (`id_caja_chica`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `gastos_ibfk_5` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_gasto` (`id_solicitud`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `gastos_mensualidades`
--
ALTER TABLE `gastos_mensualidades`
  ADD CONSTRAINT `gastos_mensualidades_ibfk_1` FOREIGN KEY (`gasto_id`) REFERENCES `gastos` (`id_gasto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `gastos_mensualidades_ibfk_2` FOREIGN KEY (`mensualidad_id`) REFERENCES `mensualidad` (`id_mensualidad`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Filtros para la tabla `personas_apartamentos`
--
ALTER TABLE `personas_apartamentos`
  ADD CONSTRAINT `personas_apartamentos_ibfk_1` FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos` (`id_apartamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `personas_apartamentos_ibfk_2` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id_persona`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `solicitudes_gasto`
--
ALTER TABLE `solicitudes_gasto`
  ADD CONSTRAINT `solicitudes_gasto_ibfk_1` FOREIGN KEY (`presupuesto_mensual_id`) REFERENCES `presupuestos_mensuales` (`id_presupuesto`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
