-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-03-2026 a las 05:36:53
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
-- Base de datos: `seguridad_haydee_db`
--

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
(4, 2, 16),
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
(68, 4, 10);

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
  `fecha` date NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `imagen` varchar(100) DEFAULT NULL,
  `prioridad` varchar(10) NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cartelera_virtual`
--

INSERT INTO `cartelera_virtual` (`id_cartelera`, `titulo`, `descripcion`, `fecha`, `tipo`, `imagen`, `prioridad`, `usuario_id`) VALUES
(19, 'Bienvenidos', 'bienvenidos al 2026', '2100-10-10', '', 'WhatsApp_Image_2026-01-08_at_2.35.12_PM_1770308220.jpeg', '1', 1);

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
(10, 'SALDO_BAJO', 'caja_chica', 23, '2026-03-02 17:08:24');

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
  `fecha` date NOT NULL,
  `leido` tinyint(1) NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `titulo`, `descripcion`, `fecha`, `leido`, `usuario_id`) VALUES
(150, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2026-02-13', 1, 1),
(151, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2026-02-13', 0, 39),
(152, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2026-02-14', 1, 1),
(153, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2026-02-14', 0, 39),
(154, 'Saldo bajo en caja chica', 'La caja chica ID 23 tiene saldo de 90,00 Bs.', '2026-02-28', 0, 1),
(155, 'Saldo bajo en caja chica', 'La caja chica ID 23 tiene saldo de 90,00 Bs.', '2026-02-28', 0, 39),
(156, 'Nueva mensualidad disponible', 'Se han generado las mensualidades para el mes 3 del año 2025.', '2026-03-02', 0, 27),
(157, 'Saldo bajo en caja chica', 'La caja chica ID 23 tiene saldo de 80,00 Bs.', '2026-03-02', 1, 1),
(158, 'Saldo bajo en caja chica', 'La caja chica ID 23 tiene saldo de 80,00 Bs.', '2026-03-02', 0, 39);

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
(158, 10);

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
(1, 'registrar', 1),
(2, 'consultar', 1),
(3, 'modificar', 1),
(4, 'eliminar', 1);

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
(2, 'test modificado', 1),
(3, 'Propietario', 1),
(4, 'Contador', 1),
(23, 'Presidente', 1),
(68, 'new', 0);

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
(63, 39, '08b3f587204551170b5adbceb2cfce9afc09eabdd3920e83421ee3b618ba7583', '2026-03-01 06:13:56', 'RECUPERAR_CONTRASENIA'),
(71, 1, '6d91ee0964358d815f4759a90569ba7fde645b9b22e1b2d1554a1d8a63a64d61', '2026-04-04 04:26:52', 'RECORDAR_CONTRASENIA');

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
(1, 'jesus', 'escalona', 'administrador@gmail.com', '$2y$10$0Alzyzq0NExdQ.8mqEWsYObARp6msyLEmNphzvoQZpJit7JO3j84i', 1, 1),
(2, 'francisco', 'mendoza', 'franj@gmail.com', '$2y$10$0KoHFVefo2ZZPv/nh0ocaefcDxfbOKXxcVhnUj844WynuyGhWpaV.', 4, 1),
(27, 'Pepes', 'Campos', 'pepe@gmail.com', '$2y$10$WWp8M1SADJzTWAg910K.mewfZFglQF77ENnqPYLmq1U9AKmmeruY2', 3, 1),
(39, 'Yhsius', 'escalona', 'jesusgescalonae@gmail.com', '$2y$10$uU20sappWrWieWXIfKmiIOAVjmTgChMGlNs7L8wkaOMlypFkWFiJO', 2, 1),
(53, 'perfil editado', 'perfil editado', 'UsuarioperfilEditada@gmail.com', '$2y$10$AzKv19h61AeAkEYPA/FSA.buvyhYKoRfHT/kUFgMDSWE11PKjpBLS', 4, 0),
(54, 'usuario', 'cambiocontra', 'cambiocontrasenia@gmail.com', '$2y$10$soYFxka95IzptEPe5eA.IONdFJI/geOcpt0K/L7aAKNIsTyn.5Nd2', 23, 0);

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
  MODIFY `id_bitacora` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cartelera_virtual`
--
ALTER TABLE `cartelera_virtual`
  MODIFY `id_cartelera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `eventos_sistema`
--
ALTER TABLE `eventos_sistema`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id_modulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT de la tabla `tokens_seguridad`
--
ALTER TABLE `tokens_seguridad`
  MODIFY `id_token` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

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
