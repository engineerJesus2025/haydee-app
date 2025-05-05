-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-05-2025 a las 01:29:10
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id_bitacora` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `accion` varchar(100) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `modulo_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bitacora`
--

INSERT INTO `bitacora` (`id_bitacora`, `fecha_hora`, `accion`, `usuario_id`, `modulo_id`) VALUES
(1, '2025-05-01 18:31:44', 'CERRAR SESION', 1, 12),
(2, '2025-05-01 18:31:48', 'CERRAR SESION', 1, 12),
(3, '2025-05-01 18:37:27', 'CERRAR SESION', 1, 12),
(4, '2025-05-01 18:49:31', 'CERRAR SESION', 1, 12),
(5, '2025-05-01 19:22:37', 'registrar', 1, 12),
(6, '2025-05-01 19:23:35', 'eliminar', 1, 12),
(7, '2025-05-01 19:24:30', 'registrar', 1, 12),
(8, '2025-05-01 19:24:35', 'eliminar', 1, 12),
(9, '2025-05-01 19:26:31', 'CERRAR SESION', 1, 12);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulos`
--

CREATE TABLE `modulos` (
  `id_modulo` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `modulos`
--

INSERT INTO `modulos` (`id_modulo`, `nombre`) VALUES
(1, 'GESTIONAR PAGOS'),
(2, 'GESTIONAR GASTOS'),
(3, 'GESTIONAR EFECTIVO'),
(4, 'GESTIONAR CONCILIACION BANCARIA'),
(5, 'GESTIONAR CARTELERA VIRTUAL'),
(6, 'GESTIONAR HABITANTES'),
(7, 'GESTIONAR PROPIETARIOS'),
(8, 'GESTIONAR CONFIGURACION'),
(9, 'GESTIONAR PROVEEDORES'),
(10, 'GESTIONAR_APARTAMENTOS '),
(11, 'GESTIONAR_MENSUALIDAD'),
(12, 'GESTIONAR_USUARIOS'),
(13, 'GESTIONAR_REPORTES'),
(14, 'GESTIONAR_SEGURIDAD');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos_usuarios`
--

CREATE TABLE `permisos_usuarios` (
  `id_permiso_usuario` int(11) NOT NULL,
  `nombre_accion` varchar(50) DEFAULT NULL,
  `modulo_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos_usuarios`
--

INSERT INTO `permisos_usuarios` (`id_permiso_usuario`, `nombre_accion`, `modulo_id`) VALUES
(1, 'registrar', 1),
(2, 'consultar', 1),
(3, 'modificar', 1),
(4, 'eliminar', 1),
(5, 'registrar', 2),
(6, 'consultar', 2),
(7, 'modificar', 2),
(8, 'eliminar', 2),
(9, 'registrar', 3),
(10, 'consultar', 3),
(11, 'modificar', 3),
(12, 'eliminar', 3),
(13, 'registrar', 4),
(14, 'consultar', 4),
(15, 'modificar', 4),
(16, 'eliminar', 4),
(17, 'registrar', 5),
(18, 'consultar', 5),
(19, 'modificar', 5),
(20, 'eliminar', 5),
(21, 'registrar', 6),
(22, 'consultar', 6),
(23, 'modificar', 6),
(24, 'eliminar', 6),
(25, 'registrar', 7),
(26, 'consultar', 7),
(27, 'modificar', 7),
(28, 'eliminar', 7),
(30, 'consultar', 8),
(33, 'registrar', 9),
(34, 'consultar', 9),
(35, 'modificar', 9),
(36, 'eliminar', 9),
(37, 'registrar', 10),
(38, 'consultar', 10),
(39, 'modificar', 10),
(40, 'eliminar', 10),
(42, 'consultar', 11),
(45, 'registrar', 12),
(46, 'consultar', 12),
(47, 'modificar', 12),
(48, 'eliminar', 12),
(50, 'consultar', 13),
(54, 'consultar', 14);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`) VALUES
(1, 'administrador'),
(2, 'propietario'),
(3, 'contador'),
(4, 'presidente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles_permisos`
--

CREATE TABLE `roles_permisos` (
  `id_rol_permiso` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `permiso_usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles_permisos`
--

INSERT INTO `roles_permisos` (`id_rol_permiso`, `rol_id`, `permiso_usuario_id`) VALUES
(1, 2, 1),
(2, 2, 2),
(3, 2, 3),
(4, 2, 4),
(5, 2, 18),
(6, 3, 1),
(7, 3, 2),
(8, 3, 3),
(9, 3, 4),
(10, 3, 5),
(11, 3, 6),
(12, 3, 7),
(13, 3, 8),
(14, 3, 9),
(15, 3, 10),
(16, 3, 11),
(17, 3, 12),
(18, 3, 14),
(19, 3, 13),
(20, 3, 15),
(21, 3, 16),
(22, 3, 18),
(50, 4, 6),
(51, 4, 26),
(52, 4, 34),
(53, 4, 22),
(54, 4, 18),
(55, 4, 38),
(56, 4, 14),
(57, 4, 42),
(58, 4, 10),
(59, 4, 2),
(60, 4, 50),
(61, 4, 30),
(108, 1, 1),
(109, 1, 2),
(110, 1, 3),
(111, 1, 4),
(112, 1, 5),
(113, 1, 6),
(114, 1, 7),
(115, 1, 8),
(116, 1, 9),
(117, 1, 10),
(118, 1, 11),
(119, 1, 12),
(120, 1, 13),
(121, 1, 14),
(122, 1, 15),
(123, 1, 16),
(124, 1, 17),
(125, 1, 18),
(126, 1, 19),
(127, 1, 20),
(128, 1, 21),
(129, 1, 22),
(130, 1, 23),
(131, 1, 24),
(132, 1, 25),
(133, 1, 26),
(134, 1, 27),
(135, 1, 28),
(136, 1, 30),
(137, 1, 33),
(138, 1, 34),
(139, 1, 35),
(140, 1, 36),
(141, 1, 37),
(142, 1, 38),
(143, 1, 39),
(144, 1, 40),
(145, 1, 42),
(146, 1, 45),
(147, 1, 46),
(148, 1, 47),
(149, 1, 48),
(150, 1, 50),
(151, 1, 54);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `apellido` varchar(20) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `correo` varchar(50) NOT NULL,
  `contrasenia` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `cedula`, `correo`, `contrasenia`, `rol_id`) VALUES
(1, 'jesus', 'escalona', 'V30601403', 'administrador@gmail.com', 'elpapu', 1),
(2, 'randon', 'aleatorio', 'V12458896', 'randon@gmail.com', 'randon', 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id_bitacora`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `modulo_id` (`modulo_id`);

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
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `permisos_usuarios`
--
ALTER TABLE `permisos_usuarios`
  ADD PRIMARY KEY (`id_permiso_usuario`),
  ADD KEY `modulo_id` (`modulo_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `roles_permisos`
--
ALTER TABLE `roles_permisos`
  ADD PRIMARY KEY (`id_rol_permiso`),
  ADD KEY `rol_id` (`rol_id`),
  ADD KEY `permiso_usuario_id` (`permiso_usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD KEY `rol_id` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id_bitacora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id_modulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos_usuarios`
--
ALTER TABLE `permisos_usuarios`
  MODIFY `id_permiso_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `roles_permisos`
--
ALTER TABLE `roles_permisos`
  MODIFY `id_rol_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `bitacora_ibfk_2` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id_modulo`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `permisos_usuarios`
--
ALTER TABLE `permisos_usuarios`
  ADD CONSTRAINT `permisos_usuarios_ibfk_1` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id_modulo`);

--
-- Filtros para la tabla `roles_permisos`
--
ALTER TABLE `roles_permisos`
  ADD CONSTRAINT `roles_permisos_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id_rol`),
  ADD CONSTRAINT `roles_permisos_ibfk_2` FOREIGN KEY (`permiso_usuario_id`) REFERENCES `permisos_usuarios` (`id_permiso_usuario`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
