-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-07-2025 a las 09:07:20
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
  `accion` varchar(100) NOT NULL,
  `registro_alterado` varchar(30) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL
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
(7, 'Evento deportivo', 'Tendremos un evento deportivo el dia jueves. No te lo pierdas', '2025-07-02', '', 'images__2__1751514087.png', '2', 1);

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
(1, 'GESTIONAR_PAGOS'),
(2, 'GESTIONAR_GASTOS'),
(3, 'GESTIONAR_MENSUALIDAD'),
(4, 'GESTIONAR_CAJA_CHICA'),
(5, 'GESTIONAR_CARTELERA_VIRTUAL'),
(6, 'GESTIONAR_APARTAMENTOS'),
(7, 'GESTIONAR_SOLICITUD_GASTO'),
(8, 'GESTIONAR_PRESUPUESTO'),
(9, 'GESTIONAR_ANIO_FISCAL'),
(10, 'GESTIONAR_CONFIGURACION'),
(11, 'GESTIONAR_PROVEEDORES'),
(12, 'GESTIONAR_BANCOS'),
(13, 'GESTIONAR_TIPO_GASTO'),
(14, 'GESTIONAR_USUARIOS'),
(15, 'GESTIONAR_REPORTES'),
(16, 'GESTIONAR_SEGURIDAD'),
(17, 'GESTIONAR_ROLES'),
(18, 'GESTIONAR_BITACORA'),
(19, 'GESTIONAR_MANTENIMIENTO'),
(21, 'GESTIONAR_PERSONAS');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha` date NOT NULL,
  `activo` varchar(20) NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `titulo`, `descripcion`, `fecha`, `activo`, `usuario_id`) VALUES
(4, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-18', '1', 1),
(5, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-18', '0', 2),
(6, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-18', '1', 27),
(7, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '1', 1),
(8, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 2),
(9, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 27),
(10, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '1', 1),
(11, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 2),
(12, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 27),
(13, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '1', 1),
(14, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 2),
(15, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 27),
(16, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '1', 1),
(17, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 2),
(18, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 27),
(19, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '1', 1),
(20, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 2),
(21, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 27),
(22, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '1', 1),
(23, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 2),
(24, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 27),
(25, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '1', 1),
(26, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 2),
(27, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 27),
(28, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '1', 1),
(29, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 2),
(30, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 27),
(31, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '1', 1),
(32, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 2),
(33, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 27),
(34, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '1', 1),
(35, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 2),
(36, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 27),
(37, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '1', 1),
(38, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 2),
(39, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 27),
(40, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '1', 1),
(41, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 2),
(42, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 27),
(43, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '1', 1),
(44, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 2),
(45, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 27),
(46, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '1', 1),
(47, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 2),
(48, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-06-27', '0', 27),
(49, 'Cartelera Virtual', 'Se ha registrado una nueva publicación en la cartelera virtual.', '2025-07-02', '0', 27),
(50, 'Cartelera Virtual', 'Se ha registrado una nueva publicación en la cartelera virtual.', '2025-07-03', '0', 27),
(51, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-07-03', '1', 1),
(52, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-07-03', '0', 2),
(53, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-07-03', '0', 27),
(54, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-07-03', '0', 1),
(55, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-07-03', '0', 2),
(56, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-07-03', '0', 27),
(57, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-07-03', '0', 1),
(58, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-07-03', '0', 2),
(59, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-07-03', '0', 27),
(60, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-07-03', '0', 1),
(61, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-07-03', '0', 2),
(62, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-07-03', '0', 27);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos_usuarios`
--

CREATE TABLE `permisos_usuarios` (
  `id_permiso_usuario` int(11) NOT NULL,
  `nombre_accion` varchar(50) DEFAULT NULL,
  `modulo_id` int(11) DEFAULT NULL
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
(38, 'consultar', 10),
(45, 'registrar', 11),
(46, 'consultar', 11),
(47, 'modificar', 11),
(48, 'eliminar', 11),
(50, 'consultar', 12),
(54, 'consultar', 13),
(57, 'registrar', 14),
(58, 'consultar', 14),
(59, 'modificar', 14),
(60, 'eliminar', 14),
(61, 'consultar', 15),
(65, 'consultar', 16),
(66, 'registrar', 8),
(67, 'modificar', 8),
(68, 'eliminar', 8),
(69, 'registrar', 12),
(70, 'modificar', 12),
(71, 'eliminar', 12),
(72, 'registrar', 13),
(73, 'modificar', 13),
(74, 'eliminar', 13),
(75, 'registrar', 17),
(76, 'consultar', 17),
(77, 'modificar', 17),
(78, 'eliminar', 17),
(79, 'registrar', 18),
(80, 'consultar', 18),
(81, 'modificar', 18),
(82, 'eliminar', 18),
(83, 'consultar', 19),
(84, 'registrar', 21),
(85, 'consultar', 21),
(86, 'modificar', 21),
(87, 'eliminar', 21);

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
(1, 'Administrador Global'),
(2, 'Administrador'),
(3, 'Propietario'),
(4, 'Contador'),
(23, 'Presidente'),
(27, 'rarar'),
(30, 'ultimoas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles_permisos`
--

CREATE TABLE `roles_permisos` (
  `id_rol_permiso` int(11) NOT NULL,
  `rol_id` int(11) DEFAULT NULL,
  `permiso_usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles_permisos`
--

INSERT INTO `roles_permisos` (`id_rol_permiso`, `rol_id`, `permiso_usuario_id`) VALUES
(441, 3, 1),
(442, 3, 2),
(443, 3, 3),
(444, 3, 4),
(445, 4, 1),
(446, 4, 2),
(447, 4, 3),
(448, 4, 4),
(449, 4, 5),
(450, 4, 6),
(451, 4, 7),
(452, 4, 8),
(453, 4, 9),
(454, 4, 10),
(455, 4, 11),
(456, 4, 12),
(457, 4, 13),
(458, 4, 14),
(459, 4, 15),
(460, 4, 16),
(461, 4, 17),
(462, 4, 18),
(463, 4, 19),
(464, 4, 20),
(465, 4, 50),
(466, 23, 2),
(467, 23, 6),
(468, 23, 10),
(469, 23, 14),
(470, 23, 18),
(471, 23, 22),
(472, 23, 26),
(473, 23, 30),
(474, 23, 34),
(475, 23, 38),
(476, 23, 46),
(477, 23, 50),
(687, 2, 1),
(688, 2, 2),
(689, 2, 3),
(690, 2, 4),
(691, 2, 5),
(692, 2, 6),
(693, 2, 7),
(694, 2, 8),
(695, 2, 9),
(696, 2, 10),
(697, 2, 11),
(698, 2, 12),
(699, 2, 13),
(700, 2, 14),
(701, 2, 15),
(702, 2, 16),
(703, 2, 17),
(704, 2, 18),
(705, 2, 19),
(706, 2, 20),
(707, 2, 21),
(708, 2, 22),
(709, 2, 23),
(710, 2, 24),
(711, 2, 25),
(712, 2, 26),
(713, 2, 27),
(714, 2, 28),
(715, 2, 30),
(716, 2, 33),
(717, 2, 34),
(718, 2, 35),
(719, 2, 36),
(721, 2, 38),
(724, 2, 45),
(725, 2, 46),
(726, 2, 47),
(727, 2, 48),
(728, 2, 50),
(729, 2, 54),
(730, 2, 57),
(731, 2, 58),
(732, 2, 59),
(733, 2, 60),
(734, 2, 61),
(738, 2, 65),
(762, 27, 13),
(763, 27, 14),
(764, 27, 30),
(765, 27, 54),
(766, 27, 57),
(767, 27, 58),
(770, 27, 65),
(775, 1, 1),
(776, 1, 2),
(777, 1, 3),
(778, 1, 4),
(779, 1, 5),
(780, 1, 6),
(781, 1, 7),
(782, 1, 8),
(783, 1, 9),
(784, 1, 10),
(785, 1, 11),
(786, 1, 12),
(787, 1, 13),
(788, 1, 14),
(789, 1, 15),
(790, 1, 16),
(791, 1, 17),
(792, 1, 18),
(793, 1, 19),
(794, 1, 20),
(795, 1, 21),
(796, 1, 22),
(797, 1, 23),
(798, 1, 24),
(799, 1, 25),
(800, 1, 26),
(801, 1, 27),
(802, 1, 28),
(803, 1, 30),
(804, 1, 66),
(805, 1, 67),
(806, 1, 68),
(807, 1, 33),
(808, 1, 34),
(809, 1, 35),
(810, 1, 36),
(811, 1, 38),
(812, 1, 45),
(813, 1, 46),
(814, 1, 47),
(815, 1, 48),
(816, 1, 50),
(817, 1, 69),
(818, 1, 70),
(819, 1, 71),
(820, 1, 54),
(821, 1, 72),
(822, 1, 73),
(823, 1, 74),
(824, 1, 57),
(825, 1, 58),
(826, 1, 59),
(827, 1, 60),
(828, 1, 61),
(829, 1, 65),
(830, 1, 75),
(831, 1, 76),
(832, 1, 77),
(833, 1, 78),
(834, 1, 79),
(835, 1, 80),
(836, 1, 81),
(837, 1, 82),
(838, 1, 83),
(839, 1, 84),
(840, 1, 85),
(841, 1, 86),
(842, 1, 87);

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
  `token` varchar(255) DEFAULT NULL,
  `duracion_token` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasenia`, `rol_id`, `token`, `duracion_token`) VALUES
(1, 'jesus', 'escalona', 'administrador@gmail.com', '$2y$10$haF/p9hybaBi7XujsRQ4rOqPnIWRJT7Qi6GKrBELUD0i3ZiTs4uLO', 1, NULL, NULL),
(2, 'francisco', 'mendoza', 'fran@gmasil.com', '$2y$10$NnxKPDNTGUuo6LKSUyo1FeyHDb/DRhvK3gKAWm2wpwe7ePfhBEbky', 2, NULL, NULL),
(27, 'Pepe', 'Campos', 'pepe@gmail.com', '$2y$10$FPga2GCcdJnZUvs9mY9ZSuFixqONdXDpUrOFPVP5INoplWyM2fabu', 3, NULL, NULL);

--
-- Índices para tablas volcadas
--

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
-- Indices de la tabla `permisos_usuarios`
--
ALTER TABLE `permisos_usuarios`
  ADD PRIMARY KEY (`id_permiso_usuario`),
  ADD KEY `permisos_usuarios_ibfk_1` (`modulo_id`);

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
  ADD KEY `roles_permisos_ibfk_1` (`rol_id`),
  ADD KEY `roles_permisos_ibfk_2` (`permiso_usuario_id`);

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
  MODIFY `id_cartelera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id_modulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `permisos_usuarios`
--
ALTER TABLE `permisos_usuarios`
  MODIFY `id_permiso_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `roles_permisos`
--
ALTER TABLE `roles_permisos`
  MODIFY `id_rol_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=849;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bitacora_ibfk_2` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Filtros para la tabla `permisos_usuarios`
--
ALTER TABLE `permisos_usuarios`
  ADD CONSTRAINT `permisos_usuarios_ibfk_1` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `roles_permisos`
--
ALTER TABLE `roles_permisos`
  ADD CONSTRAINT `roles_permisos_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `roles_permisos_ibfk_2` FOREIGN KEY (`permiso_usuario_id`) REFERENCES `permisos_usuarios` (`id_permiso_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
