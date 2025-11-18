-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-11-2025 a las 04:51:35
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
  `registro_alterado` varchar(255) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bitacora`
--

INSERT INTO `bitacora` (`id_bitacora`, `fecha_hora`, `accion`, `registro_alterado`, `usuario_id`, `modulo_id`) VALUES
(1, '2025-11-17 10:59:28', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3),
(2, '2025-11-17 10:59:31', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(3, '2025-11-17 10:59:33', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 19),
(4, '2025-11-17 10:59:37', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3),
(5, '2025-11-17 10:59:41', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(6, '2025-11-17 10:59:42', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 19),
(7, '2025-11-17 10:59:48', 'consultar', 'Todos los roles de usuario', 1, 17),
(8, '2025-11-17 11:00:20', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3),
(9, '2025-11-17 11:02:44', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3),
(10, '2025-11-17 11:03:00', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(11, '2025-11-17 11:03:02', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 19),
(12, '2025-11-17 11:03:23', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 19),
(13, '2025-11-17 11:04:09', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(14, '2025-11-17 11:04:15', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 19),
(15, '2025-11-17 11:04:18', 'consultar', 'HABITANTES EN APARTAMENTO #15', 1, 19),
(16, '2025-11-17 11:05:04', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(17, '2025-11-17 11:05:07', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 19),
(18, '2025-11-17 11:06:08', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(19, '2025-11-17 11:06:13', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 19),
(20, '2025-11-17 11:06:18', 'consultar', 'HABITANTES EN APARTAMENTO #15', 1, 19),
(21, '2025-11-17 11:07:01', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(22, '2025-11-17 11:07:03', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 19),
(23, '2025-11-17 11:07:14', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(24, '2025-11-17 11:07:16', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 19),
(25, '2025-11-17 11:07:20', 'consultar', 'HABITANTES EN APARTAMENTO #12', 1, 19),
(26, '2025-11-17 11:07:22', 'consultar', 'HABITANTES EN APARTAMENTO #15', 1, 19),
(27, '2025-11-17 11:07:24', 'consultar', 'HABITANTES EN APARTAMENTO #16', 1, 19),
(28, '2025-11-17 11:08:54', 'consultar', 'TODOS LOS AÑOS FISCALES', 1, 9),
(29, '2025-11-17 11:11:42', 'eliminar', '2024-01-01 - Cerrada', 1, 9),
(30, '2025-11-17 11:11:43', 'consultar', 'TODOS LOS AÑOS FISCALES', 1, 9),
(31, '2025-11-17 11:28:23', 'iniciar sesion', 'NINGUNO', 1, 14),
(32, '2025-11-17 13:46:10', 'iniciar sesion', 'NINGUNO', 1, 14),
(33, '2025-11-17 13:46:15', 'consultar', 'TODOS LOS USUARIOS', 1, 14),
(34, '2025-11-17 13:46:36', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(35, '2025-11-17 13:46:39', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 19),
(36, '2025-11-17 13:52:28', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 19),
(37, '2025-11-17 13:52:53', 'consultar', 'Todos los roles de usuario', 1, 17),
(38, '2025-11-17 13:54:45', 'consultar', 'Todos los roles de usuario', 1, 17),
(39, '2025-11-17 13:55:06', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(40, '2025-11-17 13:55:07', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 19),
(41, '2025-11-17 13:55:54', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(42, '2025-11-17 13:55:57', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(43, '2025-11-17 13:57:11', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(44, '2025-11-17 13:57:41', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(45, '2025-11-17 13:57:45', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(46, '2025-11-17 14:00:33', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(47, '2025-11-17 14:00:35', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(48, '2025-11-17 14:01:05', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(49, '2025-11-17 14:01:06', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(50, '2025-11-17 14:09:03', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(51, '2025-11-17 14:09:06', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(52, '2025-11-17 14:10:10', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(53, '2025-11-17 14:10:12', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(54, '2025-11-17 14:14:11', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(55, '2025-11-17 14:14:15', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(56, '2025-11-17 14:15:27', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(57, '2025-11-17 14:15:30', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(58, '2025-11-17 14:16:44', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(59, '2025-11-17 14:16:50', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(60, '2025-11-17 14:28:20', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(61, '2025-11-17 14:28:22', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(62, '2025-11-17 14:29:52', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(63, '2025-11-17 14:29:55', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(64, '2025-11-17 14:32:24', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(65, '2025-11-17 14:32:26', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(66, '2025-11-17 14:32:34', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(67, '2025-11-17 14:32:36', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(68, '2025-11-17 14:32:58', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(69, '2025-11-17 14:33:00', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(70, '2025-11-17 14:34:17', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(71, '2025-11-17 14:34:20', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(72, '2025-11-17 14:38:48', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(73, '2025-11-17 14:38:50', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(74, '2025-11-17 14:39:11', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(75, '2025-11-17 14:39:14', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(76, '2025-11-17 14:39:43', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(77, '2025-11-17 14:39:45', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(78, '2025-11-17 14:47:42', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(79, '2025-11-17 14:47:45', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(80, '2025-11-17 14:48:10', 'registrar', 'V30601202 (sfsdfd)', 1, 21),
(81, '2025-11-17 14:48:11', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(82, '2025-11-17 14:53:37', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(83, '2025-11-17 14:53:43', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(84, '2025-11-17 14:54:34', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(85, '2025-11-17 14:54:35', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(86, '2025-11-17 18:33:22', 'iniciar sesion', 'NINGUNO', 1, 14),
(87, '2025-11-17 18:33:45', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(88, '2025-11-17 18:33:48', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(89, '2025-11-17 18:33:51', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(90, '2025-11-17 18:33:53', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(91, '2025-11-17 18:36:10', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(92, '2025-11-17 18:36:13', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(93, '2025-11-17 18:36:16', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(94, '2025-11-17 18:37:06', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(95, '2025-11-17 18:37:07', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(96, '2025-11-17 18:42:16', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(97, '2025-11-17 18:45:37', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(98, '2025-11-17 18:45:40', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(99, '2025-11-17 18:46:38', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(100, '2025-11-17 18:46:42', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(101, '2025-11-17 18:48:03', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(102, '2025-11-17 18:48:22', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(103, '2025-11-17 18:51:29', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(104, '2025-11-17 18:51:31', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(105, '2025-11-17 18:52:26', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(106, '2025-11-17 18:52:29', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(107, '2025-11-17 18:55:12', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(108, '2025-11-17 18:55:14', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(109, '2025-11-17 18:56:19', 'registrar', 'V2342423 (sdfsd)', 1, 21),
(110, '2025-11-17 18:56:20', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(111, '2025-11-17 18:56:28', 'modificar', 'V2342423 (hola)', 1, 21),
(112, '2025-11-17 18:56:29', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(113, '2025-11-17 19:05:56', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(114, '2025-11-17 19:05:58', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(115, '2025-11-17 19:07:00', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(116, '2025-11-17 19:07:04', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(117, '2025-11-17 19:07:50', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(118, '2025-11-17 19:07:52', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(119, '2025-11-17 19:12:56', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(120, '2025-11-17 19:12:59', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(121, '2025-11-17 19:15:43', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(122, '2025-11-17 19:15:46', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(123, '2025-11-17 19:17:02', 'registrar', 'V1012001 (fgfgj)', 1, 21),
(124, '2025-11-17 19:17:03', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(125, '2025-11-17 19:17:14', 'modificar', 'V2342423 (hola)', 1, 21),
(126, '2025-11-17 19:17:14', 'consultar', 'HABITANTES EN APARTAMENTO #11', 1, 21),
(127, '2025-11-17 19:17:23', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(128, '2025-11-17 19:42:34', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(129, '2025-11-17 19:42:55', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(130, '2025-11-17 19:49:40', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(131, '2025-11-17 19:50:30', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(132, '2025-11-17 19:56:29', 'registrar', 'asdas (2421)', 1, 12),
(133, '2025-11-17 19:57:11', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(134, '2025-11-17 19:57:46', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(135, '2025-11-17 19:59:55', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(136, '2025-11-17 20:00:12', 'modificar', 'asasas (2421)', 1, 12),
(137, '2025-11-17 20:00:20', 'eliminar', 'asasas (2421)', 1, 12),
(138, '2025-11-17 20:02:20', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(139, '2025-11-17 20:02:59', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(140, '2025-11-17 20:05:24', 'consultar', 'TODOS LOS PROVEEDORES', 1, 11),
(141, '2025-11-17 20:11:12', 'consultar', 'TODOS LAS CAJAS', 1, 4),
(142, '2025-11-17 20:11:26', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3),
(143, '2025-11-17 20:11:37', 'consultar', 'TODOS LOS PRESUPUESTOS', 1, 8),
(144, '2025-11-17 20:11:51', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(145, '2025-11-17 20:11:54', 'consultar', 'TODOS LOS GASTOS', 1, 2),
(146, '2025-11-17 20:12:07', 'consultar', 'TODAS LAS SOLICITUDES DE GASTO', 1, 7),
(147, '2025-11-17 20:12:41', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(148, '2025-11-17 20:12:44', 'consultar', 'HABITANTES EN APARTAMENTO #26', 1, 21),
(149, '2025-11-17 20:14:33', 'consultar', 'HABITANTES EN APARTAMENTO #26', 1, 21),
(150, '2025-11-17 20:14:36', 'eliminar', '4-2', 1, 6),
(151, '2025-11-17 20:14:37', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(152, '2025-11-17 20:15:26', 'eliminar', '4-1', 1, 6),
(153, '2025-11-17 20:15:26', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(154, '2025-11-17 20:15:28', 'eliminar', '2-3', 1, 6),
(155, '2025-11-17 20:15:29', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(156, '2025-11-17 20:15:31', 'eliminar', '2-2', 1, 6),
(157, '2025-11-17 20:15:31', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(158, '2025-11-17 20:15:33', 'eliminar', '2-1', 1, 6),
(159, '2025-11-17 20:15:34', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(160, '2025-11-17 20:15:36', 'eliminar', '1-2', 1, 6),
(161, '2025-11-17 20:15:36', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(162, '2025-11-17 20:15:38', 'eliminar', '1-1', 1, 6),
(163, '2025-11-17 20:15:38', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(164, '2025-11-17 20:41:19', 'registrar', '1-1', 1, 6),
(165, '2025-11-17 20:41:21', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(166, '2025-11-17 20:41:23', 'consultar', 'HABITANTES EN APARTAMENTO #29', 1, 21),
(167, '2025-11-17 20:41:51', 'registrar', 'V1023423 (jeje)', 1, 21),
(168, '2025-11-17 20:41:52', 'consultar', 'HABITANTES EN APARTAMENTO #29', 1, 21),
(169, '2025-11-17 20:42:03', 'eliminar', '1-1', 1, 6),
(170, '2025-11-17 20:42:03', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(171, '2025-11-17 20:42:17', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(172, '2025-11-17 20:42:17', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(173, '2025-11-17 20:42:32', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(174, '2025-11-17 20:42:35', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(175, '2025-11-17 20:42:36', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(176, '2025-11-17 20:42:50', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(177, '2025-11-17 20:42:50', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(178, '2025-11-17 20:42:50', 'eliminar', '25 (pago)', 1, 1),
(179, '2025-11-17 20:42:53', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(180, '2025-11-17 20:42:53', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(181, '2025-11-17 20:42:53', 'eliminar', '21 (si pagas)', 1, 1),
(182, '2025-11-17 20:42:56', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(183, '2025-11-17 20:42:56', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(184, '2025-11-17 20:42:56', 'eliminar', '23 (pago de pepe)', 1, 1),
(185, '2025-11-17 20:42:59', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(186, '2025-11-17 20:42:59', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(187, '2025-11-17 20:43:00', 'eliminar', '2 (2)', 1, 1),
(188, '2025-11-17 20:43:02', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(189, '2025-11-17 20:43:03', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(190, '2025-11-17 20:43:03', 'eliminar', '10 (1)', 1, 1),
(191, '2025-11-17 20:43:06', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(192, '2025-11-17 20:43:06', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(193, '2025-11-17 20:43:06', 'eliminar', '41 (test)', 1, 1),
(194, '2025-11-17 20:43:10', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(195, '2025-11-17 20:43:10', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(196, '2025-11-17 20:43:10', 'eliminar', '10 (2)', 1, 1),
(197, '2025-11-17 20:45:02', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(198, '2025-11-17 20:45:02', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(199, '2025-11-17 20:45:10', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(200, '2025-11-17 20:45:18', 'registrar', '1-2', 1, 6),
(201, '2025-11-17 20:45:18', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(202, '2025-11-17 20:45:28', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(203, '2025-11-17 20:45:28', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(204, '2025-11-17 20:45:32', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3),
(205, '2025-11-17 20:45:42', 'registrar', 'Mensualidad del mes 1 del 2025. De 11.50 Bs.', 1, 3),
(206, '2025-11-17 20:45:42', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3),
(207, '2025-11-17 20:45:46', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(208, '2025-11-17 20:45:47', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(209, '2025-11-17 20:45:49', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(210, '2025-11-17 20:45:49', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(211, '2025-11-17 20:45:50', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(212, '2025-11-17 20:45:54', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(213, '2025-11-17 20:45:54', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(214, '2025-11-17 20:45:55', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(215, '2025-11-17 20:45:55', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(216, '2025-11-17 20:45:58', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(217, '2025-11-17 20:45:58', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(218, '2025-11-17 20:46:13', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(219, '2025-11-17 20:46:14', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(220, '2025-11-17 20:46:14', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(221, '2025-11-17 20:46:14', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(222, '2025-11-17 20:46:15', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(223, '2025-11-17 20:46:15', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(224, '2025-11-17 20:46:16', 'registrar', ' (sis)', 1, 1),
(225, '2025-11-17 20:46:16', 'registrar', '2025-10-10 (Detalle pago anexado: 10)', 1, 1),
(226, '2025-11-17 20:46:17', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(227, '2025-11-17 20:46:17', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(228, '2025-11-17 20:46:17', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(229, '2025-11-17 20:46:20', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(230, '2025-11-17 20:46:20', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(231, '2025-11-17 20:46:20', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(232, '2025-11-17 20:46:20', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(233, '2025-11-17 20:46:21', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(234, '2025-11-17 20:46:21', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(235, '2025-11-17 20:46:21', 'consultar', 'DETALLES DE PAGO ID 101', 1, 1),
(236, '2025-11-17 20:46:21', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(237, '2025-11-17 20:46:21', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(238, '2025-11-17 20:46:21', 'consultar', 'DETALLES DE PAGO ID 101', 1, 1),
(239, '2025-11-17 20:46:23', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(240, '2025-11-17 20:46:23', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(241, '2025-11-17 20:46:37', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(242, '2025-11-17 20:46:37', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(243, '2025-11-17 20:46:37', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(244, '2025-11-17 20:46:37', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(245, '2025-11-17 20:46:38', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(246, '2025-11-17 20:46:38', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(247, '2025-11-17 20:46:38', 'consultar', 'DETALLES DE PAGO ID 101', 1, 1),
(248, '2025-11-17 20:46:38', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(249, '2025-11-17 20:46:39', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(250, '2025-11-17 20:46:39', 'consultar', 'DETALLES DE PAGO ID 101', 1, 1),
(251, '2025-11-17 20:46:47', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(252, '2025-11-17 20:46:48', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(253, '2025-11-17 20:46:48', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(254, '2025-11-17 20:46:48', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(255, '2025-11-17 20:46:50', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(256, '2025-11-17 20:46:50', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(257, '2025-11-17 20:47:07', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(258, '2025-11-17 20:47:07', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(259, '2025-11-17 20:47:07', 'eliminar', '10 (sis)', 1, 1),
(260, '2025-11-17 20:47:11', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(261, '2025-11-17 20:47:11', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(262, '2025-11-17 20:47:11', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(263, '2025-11-17 20:47:11', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(264, '2025-11-17 20:47:13', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(265, '2025-11-17 20:47:13', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(266, '2025-11-17 20:47:23', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(267, '2025-11-17 20:47:23', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(268, '2025-11-17 20:47:24', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(269, '2025-11-17 20:47:24', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(270, '2025-11-17 20:47:24', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(271, '2025-11-17 20:47:24', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(272, '2025-11-17 20:47:25', 'registrar', ' (sisi)', 1, 1),
(273, '2025-11-17 20:47:25', 'registrar', '2025-10-10 (Detalle pago anexado: 11.5)', 1, 1),
(274, '2025-11-17 20:47:25', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(275, '2025-11-17 20:47:25', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(276, '2025-11-17 20:47:26', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(277, '2025-11-17 20:47:30', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(278, '2025-11-17 20:47:30', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(279, '2025-11-17 20:47:30', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(280, '2025-11-17 20:47:30', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(281, '2025-11-17 20:47:40', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(282, '2025-11-17 20:47:40', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(283, '2025-11-17 20:47:40', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(284, '2025-11-17 20:47:41', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(285, '2025-11-17 20:47:45', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(286, '2025-11-17 20:47:46', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(287, '2025-11-17 20:47:46', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(288, '2025-11-17 20:47:46', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(289, '2025-11-17 20:47:53', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(290, '2025-11-17 20:47:53', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(291, '2025-11-17 20:47:53', 'eliminar', '11.5 (sisi)', 1, 1),
(292, '2025-11-17 20:47:58', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(293, '2025-11-17 20:47:58', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(294, '2025-11-17 20:47:58', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(295, '2025-11-17 20:47:58', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(296, '2025-11-17 20:48:00', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(297, '2025-11-17 20:48:00', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(298, '2025-11-17 20:48:00', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(299, '2025-11-17 20:48:00', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(300, '2025-11-17 20:48:11', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(301, '2025-11-17 20:48:11', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(302, '2025-11-17 20:48:11', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(303, '2025-11-17 20:48:11', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(304, '2025-11-17 20:48:12', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(305, '2025-11-17 20:48:12', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(306, '2025-11-17 20:48:13', 'registrar', ' (seses)', 1, 1),
(307, '2025-11-17 20:48:13', 'registrar', '2025-11-03 (Detalle pago anexado: 11)', 1, 1),
(308, '2025-11-17 20:48:14', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(309, '2025-11-17 20:48:14', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(310, '2025-11-17 20:48:14', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(311, '2025-11-17 20:48:17', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(312, '2025-11-17 20:48:17', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(313, '2025-11-17 20:48:18', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(314, '2025-11-17 20:48:18', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(315, '2025-11-17 20:48:22', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(316, '2025-11-17 20:48:22', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(317, '2025-11-17 20:48:22', 'eliminar', '11 (seses)', 1, 1),
(318, '2025-11-17 20:51:17', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(319, '2025-11-17 20:51:17', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(320, '2025-11-17 20:51:17', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(321, '2025-11-17 20:51:17', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(322, '2025-11-17 20:51:26', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(323, '2025-11-17 20:51:26', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(324, '2025-11-17 20:51:27', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(325, '2025-11-17 20:51:27', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(326, '2025-11-17 20:51:28', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(327, '2025-11-17 20:51:29', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(328, '2025-11-17 20:51:29', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(329, '2025-11-17 20:51:29', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(330, '2025-11-17 20:51:44', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(331, '2025-11-17 20:51:44', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(332, '2025-11-17 20:51:44', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(333, '2025-11-17 20:51:44', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(334, '2025-11-17 20:51:45', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(335, '2025-11-17 20:51:45', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(336, '2025-11-17 20:51:46', 'registrar', ' (sdfd)', 1, 1),
(337, '2025-11-17 20:51:46', 'registrar', '2025-11-03 (Detalle pago anexado: 10)', 1, 1),
(338, '2025-11-17 20:51:47', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(339, '2025-11-17 20:51:47', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(340, '2025-11-17 20:51:47', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(341, '2025-11-17 20:52:11', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(342, '2025-11-17 20:52:11', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(343, '2025-11-17 20:52:12', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(344, '2025-11-17 20:52:12', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(345, '2025-11-17 20:52:41', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(346, '2025-11-17 20:52:41', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(347, '2025-11-17 20:52:41', 'eliminar', '10 (sdfd)', 1, 1),
(348, '2025-11-17 20:52:45', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(349, '2025-11-17 20:52:45', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(350, '2025-11-17 20:52:45', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(351, '2025-11-17 20:52:46', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(352, '2025-11-17 20:52:47', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(353, '2025-11-17 20:52:48', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(354, '2025-11-17 20:52:48', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(355, '2025-11-17 20:52:48', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(356, '2025-11-17 20:52:56', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(357, '2025-11-17 20:52:56', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(358, '2025-11-17 20:52:56', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(359, '2025-11-17 20:52:56', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(360, '2025-11-17 20:52:59', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(361, '2025-11-17 20:52:59', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(362, '2025-11-17 20:53:00', 'registrar', ' (asdasd)', 1, 1),
(363, '2025-11-17 20:53:00', 'registrar', '2025-11-04 (Detalle pago anexado: 10)', 1, 1),
(364, '2025-11-17 20:53:00', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(365, '2025-11-17 20:53:01', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(366, '2025-11-17 20:53:01', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(367, '2025-11-17 20:53:07', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(368, '2025-11-17 20:53:07', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(369, '2025-11-17 20:53:07', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(370, '2025-11-17 20:53:07', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(371, '2025-11-17 21:01:50', 'consultar', 'Todos los roles de usuario', 1, 17),
(372, '2025-11-17 21:02:27', 'registrar', 'Rol \'hola\' guardado', 1, 17),
(373, '2025-11-17 21:02:28', 'consultar', 'Todos los roles de usuario', 1, 17),
(374, '2025-11-17 21:04:29', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(375, '2025-11-17 21:04:29', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(376, '2025-11-17 21:04:30', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(377, '2025-11-17 21:04:31', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(378, '2025-11-17 21:04:31', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(379, '2025-11-17 21:04:36', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(380, '2025-11-17 21:04:36', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(381, '2025-11-17 21:04:39', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(382, '2025-11-17 21:04:39', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(383, '2025-11-17 21:04:39', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(384, '2025-11-17 21:04:44', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(385, '2025-11-17 21:04:45', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(386, '2025-11-17 21:04:45', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(387, '2025-11-17 21:04:45', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(388, '2025-11-17 21:06:51', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(389, '2025-11-17 21:06:51', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(390, '2025-11-17 21:06:52', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(391, '2025-11-17 21:06:52', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(392, '2025-11-17 21:06:52', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(393, '2025-11-17 21:07:51', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(394, '2025-11-17 21:07:51', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(395, '2025-11-17 21:07:51', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(396, '2025-11-17 21:07:51', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(397, '2025-11-17 21:09:37', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(398, '2025-11-17 21:09:37', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(399, '2025-11-17 21:09:39', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(400, '2025-11-17 21:09:39', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(401, '2025-11-17 21:09:40', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(402, '2025-11-17 21:09:44', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(403, '2025-11-17 21:09:44', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(404, '2025-11-17 21:09:44', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(405, '2025-11-17 21:09:44', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(406, '2025-11-17 21:10:51', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(407, '2025-11-17 21:10:51', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(408, '2025-11-17 21:10:53', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(409, '2025-11-17 21:10:53', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(410, '2025-11-17 21:10:54', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(411, '2025-11-17 21:10:55', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(412, '2025-11-17 21:10:55', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(413, '2025-11-17 21:10:55', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(414, '2025-11-17 21:10:56', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(415, '2025-11-17 21:37:33', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(416, '2025-11-17 21:37:33', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(417, '2025-11-17 21:37:37', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(418, '2025-11-17 21:37:38', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(419, '2025-11-17 21:37:43', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(420, '2025-11-17 21:37:43', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(421, '2025-11-17 21:37:44', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(422, '2025-11-17 21:38:16', 'consultar', 'TODOS LOS PROVEEDORES', 1, 11),
(423, '2025-11-17 21:38:30', 'consultar', 'TODOS LOS PROVEEDORES', 1, 11),
(424, '2025-11-17 21:46:11', 'consultar', 'TODOS LOS PROVEEDORES', 1, 11),
(425, '2025-11-17 21:49:56', 'consultar', 'TODOS LOS PROVEEDORES', 1, 11),
(426, '2025-11-17 21:51:07', 'consultar', 'TODOS LOS PROVEEDORES', 1, 11),
(427, '2025-11-17 21:52:56', 'consultar', 'TODOS LOS PROVEEDORES', 1, 11),
(428, '2025-11-17 21:53:36', 'consultar', 'TODOS LOS PROVEEDORES', 1, 11),
(429, '2025-11-17 21:53:47', 'consultar', 'TODOS LOS PROVEEDORES', 1, 11),
(430, '2025-11-17 21:54:22', 'consultar', 'TODOS LOS PROVEEDORES', 1, 11),
(431, '2025-11-17 22:02:14', 'consultar', 'TODOS LOS PROVEEDORES', 1, 11),
(432, '2025-11-17 22:02:36', 'modificar', 'Reparaciones CA - V2342344', 1, 11),
(433, '2025-11-17 22:02:46', 'modificar', 'Jardinero - E13123343', 1, 11),
(434, '2025-11-17 22:02:58', 'modificar', 'Proimca - V3434523', 1, 11),
(435, '2025-11-17 22:03:05', 'modificar', 'Gas Lara - V2352345', 1, 11),
(436, '2025-11-17 22:03:46', 'registrar', 'sdsd - V3534453', 1, 11),
(437, '2025-11-17 22:03:50', 'eliminar', 'sdsd - V3534453', 1, 11),
(438, '2025-11-17 22:03:53', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(439, '2025-11-17 22:03:53', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(440, '2025-11-17 22:03:54', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(441, '2025-11-17 22:03:54', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(442, '2025-11-17 22:03:54', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(443, '2025-11-17 22:04:38', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(444, '2025-11-17 22:04:38', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(445, '2025-11-17 22:04:38', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(446, '2025-11-17 22:04:38', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(447, '2025-11-17 22:05:05', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(448, '2025-11-17 22:05:05', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(449, '2025-11-17 22:05:07', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(450, '2025-11-17 22:05:07', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(451, '2025-11-17 22:05:08', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(452, '2025-11-17 22:05:28', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(453, '2025-11-17 22:05:28', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(454, '2025-11-17 22:05:30', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(455, '2025-11-17 22:05:30', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(456, '2025-11-17 22:05:30', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(457, '2025-11-17 22:05:38', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(458, '2025-11-17 22:05:42', 'consultar', 'TODOS LOS GASTOS', 1, 2),
(459, '2025-11-17 22:06:20', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(460, '2025-11-17 22:06:21', 'consultar', 'TODOS LOS GASTOS', 1, 2),
(461, '2025-11-17 22:06:38', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(462, '2025-11-17 22:06:38', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(463, '2025-11-17 22:06:40', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(464, '2025-11-17 22:06:40', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(465, '2025-11-17 22:06:40', 'consultar', 'TODOS LOS PAGOS', 1, 1),
(466, '2025-11-17 22:08:13', 'consultar', 'TODOS LAS CAJAS', 1, 4),
(467, '2025-11-17 22:08:49', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3),
(468, '2025-11-17 22:08:51', 'consultar', 'TODAS LAS PUBLICACIONES', 1, 5),
(469, '2025-11-17 22:09:05', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3),
(470, '2025-11-17 22:09:15', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6),
(471, '2025-11-17 22:09:20', 'consultar', 'HABITANTES EN APARTAMENTO #30', 1, 21),
(472, '2025-11-17 22:09:38', 'consultar', 'TODAS LAS SOLICITUDES DE GASTO', 1, 7),
(473, '2025-11-17 22:09:51', 'consultar', 'TODOS LOS PRESUPUESTOS', 1, 8),
(474, '2025-11-17 22:10:01', 'consultar', 'TODOS LOS AÑOS FISCALES', 1, 9),
(475, '2025-11-17 22:10:15', 'consultar', 'TODOS LOS REPORTES PDF', 1, 15),
(476, '2025-11-17 22:10:28', 'consultar', 'TODOS LOS PROVEEDORES', 1, 11),
(477, '2025-11-17 22:10:35', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(478, '2025-11-17 22:11:01', 'consultar', 'TODOS LOS PROVEEDORES', 1, 11),
(479, '2025-11-17 22:11:40', 'consultar', 'TODOS LOS TIPOS DE GASTO', 1, 13),
(480, '2025-11-17 22:11:47', 'consultar', 'TODOS LOS USUARIOS', 1, 14),
(481, '2025-11-17 22:12:12', 'consultar', 'Todos los roles de usuario', 1, 17),
(482, '2025-11-17 22:12:15', 'consultar', 'COPIAS DE SEGURIDAD', 1, 19),
(483, '2025-11-17 22:12:29', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3),
(484, '2025-11-17 22:13:12', 'consultar', 'Todos los roles de usuario', 1, 17),
(485, '2025-11-17 22:14:27', 'consultar', 'Todos los roles de usuario', 1, 17),
(486, '2025-11-17 22:25:45', 'consultar', 'Todos los roles de usuario', 1, 17),
(487, '2025-11-17 22:55:21', 'consultar', 'Todos los roles de usuario', 1, 17),
(488, '2025-11-17 22:56:09', 'consultar', 'Todos los roles de usuario', 1, 17),
(489, '2025-11-17 22:57:53', 'consultar', 'Todos los roles de usuario', 1, 17),
(490, '2025-11-17 23:07:11', 'consultar', 'Todos los roles de usuario', 1, 17),
(491, '2025-11-17 23:13:40', 'consultar', 'Todos los roles de usuario', 1, 17),
(492, '2025-11-17 23:14:13', 'consultar', 'Todos los roles de usuario', 1, 17),
(493, '2025-11-17 23:15:59', 'consultar', 'Todos los roles de usuario', 1, 17),
(494, '2025-11-17 23:25:51', 'consultar', 'TODAS LAS PUBLICACIONES', 1, 5),
(495, '2025-11-17 23:46:07', 'consultar', 'TODOS LAS CAJAS', 1, 4),
(496, '2025-11-17 23:46:09', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(497, '2025-11-17 23:46:11', 'consultar', 'TODOS LOS GASTOS', 1, 2),
(498, '2025-11-17 23:47:07', 'registrar', 'Testso eje esaasda', 1, 2),
(499, '2025-11-17 23:47:21', 'registrar', 'Testso eje esaasda', 1, 2),
(500, '2025-11-17 23:50:05', 'consultar', 'TODOS LOS BANCOS', 1, 12),
(501, '2025-11-17 23:50:10', 'consultar', 'TODOS LOS GASTOS', 1, 2),
(502, '2025-11-17 23:50:12', 'consultar', 'TODAS LAS PUBLICACIONES', 1, 5);

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
(13, 'Titulo de Evento', 'Descripcion del evento xxxxxxxxx', '2025-08-08', '', 'CSS-Logo_1759451531.jpg', '1', 1),
(15, 'testa', 'testa', '2000-10-10', '', NULL, '1', 1),
(17, 'Evento maratonico', 'Vamos a terminar los modulos muchachos', '2025-10-06', '', 'GUMBAR__A_1759736244_1759793820.jpg', '1', 1);

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
(21, 'GESTIONAR_HABITANTES');

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
  `usuario_id` int(11) NOT NULL,
  `nombre_modulo` varchar(50) DEFAULT NULL,
  `referencia` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `titulo`, `descripcion`, `fecha`, `activo`, `usuario_id`, `nombre_modulo`, `referencia`) VALUES
(1, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-11-17', '0', 1, 'mensualidad', '1/2025'),
(2, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-11-17', '0', 80, 'mensualidad', '1/2025'),
(3, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-11-17', '0', 83, 'mensualidad', '1/2025'),
(4, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-11-17', '0', 2, 'mensualidad', '1/2025'),
(5, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-11-17', '0', 39, 'mensualidad', '1/2025'),
(6, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2025-11-17', '0', 74, 'mensualidad', '1/2025');

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
(52, 'hola');

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
(842, 1, 87),
(907, 23, 2),
(908, 23, 6),
(909, 23, 10),
(910, 23, 14),
(911, 23, 18),
(912, 23, 22),
(913, 23, 26),
(914, 23, 30),
(915, 23, 34),
(916, 23, 38),
(917, 23, 46),
(918, 23, 50),
(919, 23, 84),
(920, 23, 85),
(921, 23, 86),
(922, 23, 87),
(949, 52, 1),
(950, 52, 2),
(951, 52, 3),
(952, 52, 4),
(953, 52, 5),
(954, 52, 6),
(955, 52, 7),
(956, 52, 8),
(957, 52, 9),
(958, 52, 10),
(959, 52, 11),
(960, 52, 12),
(961, 52, 13),
(962, 52, 14),
(963, 52, 15),
(964, 52, 16),
(965, 52, 17),
(966, 52, 18),
(967, 52, 19),
(968, 52, 20),
(969, 52, 21),
(970, 52, 22),
(971, 52, 23),
(972, 52, 24),
(973, 52, 25),
(974, 52, 26),
(975, 52, 27),
(976, 52, 28),
(977, 52, 30),
(978, 52, 66),
(979, 52, 67),
(980, 52, 68),
(981, 52, 33),
(982, 52, 34),
(983, 52, 35),
(984, 52, 36),
(985, 52, 38),
(986, 52, 45),
(987, 52, 46),
(988, 52, 47),
(989, 52, 48),
(990, 52, 54),
(991, 52, 72),
(992, 52, 73),
(993, 52, 74),
(994, 52, 83);

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
  `duracion_token` datetime DEFAULT NULL,
  `token_recuerdame` varchar(255) DEFAULT NULL,
  `duracion_token_recuerdame` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasenia`, `rol_id`, `token`, `duracion_token`, `token_recuerdame`, `duracion_token_recuerdame`) VALUES
(1, 'jesus', 'escalona', 'administrador@gmail.com', '$2y$10$KV7wFnyGSxniLWrLgS6M7.OzJvhlvhtxDezLVHftWUFg46qFMVFC2', 1, NULL, NULL, NULL, NULL),
(2, 'francisco', 'mendoza', 'fran@gmasil.com', '$2y$10$NnxKPDNTGUuo6LKSUyo1FeyHDb/DRhvK3gKAWm2wpwe7ePfhBEbky', 2, NULL, NULL, NULL, NULL),
(27, 'Pepe', 'Campos', 'pepe@gmail.com', '$2y$10$FPga2GCcdJnZUvs9mY9ZSuFixqONdXDpUrOFPVP5INoplWyM2fabu', 3, NULL, NULL, NULL, NULL),
(39, 'Yhsius', 'escalona', 'jesusgescalonae@gmail.com', '$2y$10$zacNmkVWpijdz/UqMNJqhevACUdFUoa3Ce.YhGf7Z5Xee.f46g4Hi', 2, NULL, NULL, NULL, NULL),
(51, 'usuario', 'pruebaT', 'prueba@gmail.com', '123123123', 23, 'token de prueba', '2023-08-24 16:43:41', NULL, NULL),
(53, 'perfil editado', 'perfil editado', 'UsuarioperfilEditada@gmail.com', '$2y$10$AzKv19h61AeAkEYPA/FSA.buvyhYKoRfHT/kUFgMDSWE11PKjpBLS', 4, NULL, NULL, NULL, NULL),
(54, 'usuario', 'cambiocontra', 'cambiocontrasenia@gmail.com', '$2y$10$soYFxka95IzptEPe5eA.IONdFJI/geOcpt0K/L7aAKNIsTyn.5Nd2', 23, NULL, NULL, NULL, NULL),
(55, 'borra', 'token', 'tokenborrar@gmail.com', 'borra el token', 4, NULL, NULL, NULL, NULL),
(56, 'perfil editado', 'perfil editado', 'perfilEditada@gmail.com', 'perfil editar', 4, NULL, NULL, NULL, NULL),
(57, 'token', 'insertar', 'agregartoken@gmail.com', 'agrega un token', 3, 'token de prueba agregado', '2023-08-24 16:43:41', '$2y$10$3dBoZQur3FrAhz5MJzGhxuwgpYWiYqWVPF38L/9rMaL7NI8jlrKEe', '2025-11-21 23:14:42'),
(74, 'recuerdame', 'borrar', 'correoBorrarRecuerdame@gmail.com', '12345', 2, NULL, NULL, NULL, NULL),
(76, 'token', 'recuerdame', 'recuerdame@gmaiil.com', 'mama coco', 4, NULL, '0000-00-00 00:00:00', '2023-08-24 16:43:42', NULL),
(78, 'token', 'recuerdame', 'recuerdame2@gmaiil.com', 'mama coco', 4, NULL, NULL, 'token recuerdame de prueba', '2023-08-24 16:43:42'),
(80, 'nombre de prueba', 'apellido de prueba', 'ranmdon@gmail.com', '$2y$10$dce5YPesGJtjQp3XLuUKtuQ/zMya6iFEnVW0Mn3tpQBLEjxQ.Wyaq', 1, NULL, NULL, NULL, NULL),
(83, 'nombre de prueba', 'apellido de prueba', 'pruebaregistrada@gmail.com', '$2y$10$G3B8tbA3kvBFltnM9/bDVuJay7mrOV5oPG9I.uBH6z3xzmyyqiB1a', 1, NULL, NULL, NULL, NULL);

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
  MODIFY `id_bitacora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=503;

--
-- AUTO_INCREMENT de la tabla `cartelera_virtual`
--
ALTER TABLE `cartelera_virtual`
  MODIFY `id_cartelera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id_modulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `permisos_usuarios`
--
ALTER TABLE `permisos_usuarios`
  MODIFY `id_permiso_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `roles_permisos`
--
ALTER TABLE `roles_permisos`
  MODIFY `id_rol_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=995;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

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
