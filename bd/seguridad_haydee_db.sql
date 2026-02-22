-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-02-2026 a las 04:46:44
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
(4, 1, 17),
(4, 1, 18),
(4, 2, 16),
(4, 2, 17),
(4, 2, 18),
(4, 3, 17),
(4, 4, 17),
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
  `registro_alterado` varchar(255) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  `valores_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{}' CHECK (json_valid(`valores_anteriores`)),
  `valores_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{}' CHECK (json_valid(`valores_nuevos`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bitacora`
--

INSERT INTO `bitacora` (`id_bitacora`, `fecha_hora`, `accion`, `registro_alterado`, `usuario_id`, `modulo_id`, `valores_anteriores`, `valores_nuevos`) VALUES
(1, '2026-02-13 16:23:12', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(2, '2026-02-13 16:24:37', 'consultar', 'TODOS LAS CAJAS', 1, 4, '{}', '{}'),
(3, '2026-02-13 16:24:43', 'consultar', 'TODOS LOS AÑOS FISCALES', 1, 9, '{}', '{}'),
(4, '2026-02-13 16:24:50', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(5, '2026-02-13 16:29:45', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(6, '2026-02-13 16:32:49', 'modificar', 'Bancaribe (0114)', 1, 12, '{}', '{}'),
(7, '2026-02-13 16:33:11', 'registrar', 'sdas (2342)', 1, 12, '{}', '{}'),
(8, '2026-02-13 16:33:13', 'eliminar', 'sdas (2342)', 1, 12, '{}', '{}'),
(9, '2026-02-13 16:37:17', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(10, '2026-02-13 16:37:29', 'registrar', 'Mensualidad del mes 8 del 2025. De 27.23 Bs.', 1, 3, '{}', '{}'),
(11, '2026-02-13 16:37:29', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(12, '2026-02-13 16:38:40', 'modificar', 'Mensualidad del mes 8 del 2025. De 2.99 Bs.', 1, 3, '{}', '{}'),
(13, '2026-02-13 16:38:40', 'modificar', 'Mensualidad del mes 8 del 2025. De 13.68 Bs.', 1, 3, '{}', '{}'),
(14, '2026-02-13 16:38:41', 'modificar', 'Mensualidad del mes 8 del 2025. De 0.44 Bs.', 1, 3, '{}', '{}'),
(15, '2026-02-13 16:38:41', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(16, '2026-02-13 16:41:51', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(17, '2026-02-13 17:08:17', 'modificar', 'Mensualidad del mes 8 del 2025. De 13.68 Bs.', 1, 3, '{}', '{}'),
(18, '2026-02-13 17:08:17', 'modificar', 'Mensualidad del mes 8 del 2025. De 0.44 Bs.', 1, 3, '{}', '{}'),
(19, '2026-02-13 17:08:18', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(20, '2026-02-13 17:10:20', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(21, '2026-02-13 17:10:34', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(22, '2026-02-13 17:10:44', 'registrar', 'Mensualidad del mes 1 del 2025. De 9.10 Bs.', 1, 3, '{}', '{}'),
(23, '2026-02-13 17:10:44', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(24, '2026-02-13 17:23:14', 'eliminar', 'Eliminadas menusalidades del mes 1 del 2025', 1, 3, '{}', '{}'),
(25, '2026-02-13 17:23:14', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(26, '2026-02-13 17:26:23', 'registrar', 'Mensualidad del mes 1 del 2025. De 5.74 Bs.', 1, 3, '{}', '{}'),
(27, '2026-02-13 17:26:23', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(28, '2026-02-13 17:26:30', 'eliminar', 'Eliminadas menusalidades del mes 1 del 2025', 1, 3, '{}', '{}'),
(29, '2026-02-13 17:26:30', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(30, '2026-02-13 18:11:23', 'registrar', 'Mensualidad del mes 1 del 2025. De 5.74 Bs.', 1, 3, '{}', '{}'),
(31, '2026-02-13 18:11:23', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(32, '2026-02-13 18:12:56', 'eliminar', 'Eliminadas menusalidades del mes 1 del 2025', 1, 3, '{}', '{}'),
(33, '2026-02-13 18:12:57', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(34, '2026-02-13 18:13:10', 'registrar', 'Mensualidad del mes 1 del 2025. De 5.74 Bs.', 1, 3, '{}', '{}'),
(35, '2026-02-13 18:13:10', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(36, '2026-02-13 18:15:21', 'registrar', 'Mensualidad del mes 2 del 2025. De 7.65 Bs.', 1, 3, '{}', '{}'),
(37, '2026-02-13 18:15:21', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(38, '2026-02-13 18:16:02', 'eliminar', 'Eliminadas menusalidades del mes 1 del 2025', 1, 3, '{}', '{}'),
(39, '2026-02-13 18:16:02', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(40, '2026-02-13 18:16:05', 'eliminar', 'Eliminadas menusalidades del mes 2 del 2025', 1, 3, '{}', '{}'),
(41, '2026-02-13 18:16:06', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(42, '2026-02-13 18:16:14', 'registrar', 'Mensualidad del mes 1 del 2025. De 5.74 Bs.', 1, 3, '{}', '{}'),
(43, '2026-02-13 18:16:15', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(44, '2026-02-13 18:16:46', 'eliminar', 'Eliminadas menusalidades del mes 1 del 2025', 1, 3, '{}', '{}'),
(45, '2026-02-13 18:16:46', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(46, '2026-02-13 18:16:54', 'registrar', 'Mensualidad del mes 1 del 2025. De 5.74 Bs.', 1, 3, '{}', '{}'),
(47, '2026-02-13 18:16:55', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(48, '2026-02-13 18:17:58', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(49, '2026-02-13 18:18:02', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(50, '2026-02-13 18:18:07', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(51, '2026-02-13 18:18:15', 'registrar', 'Mensualidad del mes 2 del 2025. De 7.65 Bs.', 1, 3, '{}', '{}'),
(52, '2026-02-13 18:18:15', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(53, '2026-02-13 18:19:31', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(54, '2026-02-13 18:19:46', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(55, '2026-02-13 18:19:52', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(56, '2026-02-13 18:20:13', 'registrar', 'Mensualidad del mes 3 del 2025. De 1469.61 Bs.', 1, 3, '{}', '{}'),
(57, '2026-02-13 18:20:14', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(58, '2026-02-13 18:22:54', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(59, '2026-02-13 18:23:05', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(60, '2026-02-13 18:23:11', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(61, '2026-02-13 18:23:14', 'eliminar', 'Eliminadas menusalidades del mes 1 del 2025', 1, 3, '{}', '{}'),
(62, '2026-02-13 18:23:15', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(63, '2026-02-13 18:23:17', 'eliminar', 'Eliminadas menusalidades del mes 2 del 2025', 1, 3, '{}', '{}'),
(64, '2026-02-13 18:23:18', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(65, '2026-02-13 18:23:32', 'registrar', 'Mensualidad del mes 1 del 2025. De 5.26 Bs.', 1, 3, '{}', '{}'),
(66, '2026-02-13 18:23:33', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(67, '2026-02-13 18:29:44', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(68, '2026-02-13 18:29:49', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(69, '2026-02-13 18:31:18', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(70, '2026-02-13 18:31:23', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(71, '2026-02-13 18:31:27', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(72, '2026-02-13 18:31:34', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(73, '2026-02-13 18:33:16', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(74, '2026-02-13 18:33:19', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(75, '2026-02-13 18:33:21', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(76, '2026-02-13 18:33:49', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(77, '2026-02-13 18:33:52', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(78, '2026-02-13 18:48:01', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(79, '2026-02-13 18:50:16', 'eliminar', 'Eliminadas menusalidades del mes 1 del 2025', 1, 3, '{}', '{}'),
(80, '2026-02-13 18:50:17', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(81, '2026-02-13 18:50:20', 'eliminar', 'Eliminadas menusalidades del mes 3 del 2025', 1, 3, '{}', '{}'),
(82, '2026-02-13 18:50:21', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(83, '2026-02-13 18:51:18', 'registrar', 'Mensualidad del mes 1 del 2025. De 5.74 Bs.', 1, 3, '{}', '{}'),
(84, '2026-02-13 18:51:19', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(85, '2026-02-13 18:52:04', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(86, '2026-02-13 18:52:15', 'eliminar', 'Eliminadas menusalidades del mes 1 del 2025', 1, 3, '{}', '{}'),
(87, '2026-02-13 18:52:15', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(88, '2026-02-13 18:52:23', 'registrar', 'Mensualidad del mes 1 del 2025. De 5.74 Bs.', 1, 3, '{}', '{}'),
(89, '2026-02-13 18:52:24', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(90, '2026-02-13 18:55:39', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(91, '2026-02-13 18:55:46', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(92, '2026-02-13 18:58:51', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(93, '2026-02-13 18:59:10', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(94, '2026-02-13 18:59:28', 'eliminar', 'Eliminadas menusalidades del mes 1 del 2025', 1, 3, '{}', '{}'),
(95, '2026-02-13 18:59:28', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(96, '2026-02-13 18:59:35', 'registrar', 'Mensualidad del mes 1 del 2025. De 5.74 Bs.', 1, 3, '{}', '{}'),
(97, '2026-02-13 18:59:36', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(98, '2026-02-13 18:59:39', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(99, '2026-02-13 18:59:41', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(100, '2026-02-13 19:00:37', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(101, '2026-02-13 19:00:39', 'eliminar', 'Eliminadas menusalidades del mes 1 del 2025', 1, 3, '{}', '{}'),
(102, '2026-02-13 19:00:40', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(103, '2026-02-13 19:00:47', 'registrar', 'Mensualidad del mes 1 del 2025. De 8.62 Bs.', 1, 3, '{}', '{}'),
(104, '2026-02-13 19:00:48', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(105, '2026-02-13 19:02:09', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(106, '2026-02-13 19:02:12', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(107, '2026-02-13 19:04:05', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(108, '2026-02-13 19:04:08', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(109, '2026-02-13 19:04:13', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(110, '2026-02-14 17:00:04', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(111, '2026-02-14 17:03:17', 'consultar', 'TODOS LOS USUARIOS', 1, 14, '{}', '{}'),
(112, '2026-02-14 17:03:21', 'eliminar', 'usuario cambiocontra', 1, 14, '{}', '{}'),
(113, '2026-02-14 17:03:21', 'consultar', 'TODOS LOS USUARIOS', 1, 14, '{}', '{}'),
(114, '2026-02-14 17:03:26', 'consultar', 'TODOS LOS USUARIOS', 1, 14, '{}', '{}'),
(115, '2026-02-14 17:04:19', 'consultar', 'TODOS LOS USUARIOS', 1, 14, '{}', '{}'),
(116, '2026-02-14 17:04:55', 'consultar', 'TODOS LOS USUARIOS', 1, 14, '{}', '{}'),
(117, '2026-02-14 17:05:19', 'eliminar', 'perfil editado perfil editado', 1, 14, '{}', '{}'),
(118, '2026-02-14 17:05:19', 'consultar', 'TODOS LOS USUARIOS', 1, 14, '{}', '{}'),
(119, '2026-02-14 17:05:24', 'consultar', 'TODOS LOS USUARIOS', 1, 14, '{}', '{}'),
(120, '2026-02-14 17:10:00', 'consultar', 'Todos los roles de usuario', 1, 17, '{}', '{}'),
(121, '2026-02-14 17:10:04', 'eliminar', 'Rol \'sasda\' eliminado', 1, 17, '{}', '{}'),
(122, '2026-02-14 17:10:05', 'consultar', 'Todos los roles de usuario', 1, 17, '{}', '{}'),
(123, '2026-02-14 17:10:21', 'registrar', 'Rol \'asdasd\' guardado', 1, 17, '{}', '{}'),
(124, '2026-02-14 17:10:22', 'consultar', 'Todos los roles de usuario', 1, 17, '{}', '{}'),
(125, '2026-02-14 17:10:36', 'eliminar', 'Rol \'asdasd\' eliminado', 1, 17, '{}', '{}'),
(126, '2026-02-14 17:10:36', 'consultar', 'Todos los roles de usuario', 1, 17, '{}', '{}'),
(127, '2026-02-14 17:11:10', 'consultar', 'TODOS LOS TIPOS DE GASTO', 1, 13, '{}', '{}'),
(128, '2026-02-14 17:13:36', 'registrar', 'random', 1, 13, '{}', '{}'),
(129, '2026-02-14 17:13:40', 'eliminar', 'random', 1, 13, '{}', '{}'),
(130, '2026-02-14 17:13:57', 'consultar', 'TODOS LOS TIPOS DE GASTO', 1, 13, '{}', '{}'),
(131, '2026-02-14 17:14:01', 'eliminar', 'random', 1, 13, '{}', '{}'),
(132, '2026-02-14 17:14:08', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(133, '2026-02-14 17:15:54', 'registrar', 'rasdas (1231)', 1, 12, '{}', '{}'),
(134, '2026-02-14 17:15:57', 'eliminar', 'rasdas (1231)', 1, 12, '{}', '{}'),
(135, '2026-02-14 17:16:01', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(136, '2026-02-14 17:16:19', 'consultar', 'TODOS LOS PROVEEDORES', 1, 11, '{}', '{}'),
(137, '2026-02-14 17:17:22', 'registrar', 'rasdasd - E21312312', 1, 11, '{}', '{}'),
(138, '2026-02-14 17:17:25', 'eliminar', 'rasdasd - E21312312', 1, 11, '{}', '{}'),
(139, '2026-02-14 17:17:31', 'consultar', 'TODOS LOS PROVEEDORES', 1, 11, '{}', '{}'),
(140, '2026-02-14 17:17:51', 'consultar', 'TODOS LOS AÑOS FISCALES', 1, 9, '{}', '{}'),
(141, '2026-02-14 17:20:37', 'modificar', '2026-02-02 - Cerrada', 1, 9, '{}', '{}'),
(142, '2026-02-14 17:20:37', 'consultar', 'TODOS LOS AÑOS FISCALES', 1, 9, '{}', '{}'),
(143, '2026-02-14 17:20:45', 'registrar', '2026-02-05 - Abierto', 1, 9, '{}', '{}'),
(144, '2026-02-14 17:20:45', 'consultar', 'TODOS LOS AÑOS FISCALES', 1, 9, '{}', '{}'),
(145, '2026-02-14 17:20:50', 'eliminar', '2026-02-05 - Abierto', 1, 9, '{}', '{}'),
(146, '2026-02-14 17:20:50', 'consultar', 'TODOS LOS AÑOS FISCALES', 1, 9, '{}', '{}'),
(147, '2026-02-14 17:24:06', 'registrar', '2026-02-07 - Abierto', 1, 9, '{}', '{}'),
(148, '2026-02-14 17:24:07', 'consultar', 'TODOS LOS AÑOS FISCALES', 1, 9, '{}', '{}'),
(149, '2026-02-14 17:24:10', 'eliminar', '2026-02-02 - Cerrada', 1, 9, '{}', '{}'),
(150, '2026-02-14 17:24:10', 'consultar', 'TODOS LOS AÑOS FISCALES', 1, 9, '{}', '{}'),
(151, '2026-02-14 17:24:26', 'consultar', 'TODOS LOS PRESUPUESTOS', 1, 8, '{}', '{}'),
(152, '2026-02-14 17:28:02', 'consultar', 'TODOS LOS PRESUPUESTOS', 1, 8, '{}', '{}'),
(153, '2026-02-14 17:28:22', 'eliminar', 'Presupuesto del 2025-09-01', 1, 8, '{}', '{}'),
(154, '2026-02-14 17:28:22', 'consultar', 'TODOS LOS PRESUPUESTOS', 1, 8, '{}', '{}'),
(155, '2026-02-14 17:29:03', 'consultar', 'TODAS LAS SOLICITUDES DE GASTO', 1, 7, '{}', '{}'),
(156, '2026-02-14 17:30:54', 'consultar', 'TODAS LAS SOLICITUDES DE GASTO', 1, 7, '{}', '{}'),
(157, '2026-02-14 17:31:48', 'eliminar', 'Solicitud de  por ', 1, 7, '{}', '{}'),
(158, '2026-02-14 17:31:48', 'eliminar', 'Solicitud Solicitud de gasto de ejemplo de Juan', 1, 7, '{}', '{}'),
(159, '2026-02-14 17:31:56', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(160, '2026-02-14 17:33:28', 'consultar', 'HABITANTES EN APARTAMENTO #32', 1, 21, '{}', '{}'),
(161, '2026-02-14 17:34:01', 'registrar', 'V12312341 (pepe)', 1, 21, '{}', '{}'),
(162, '2026-02-14 17:34:02', 'consultar', 'HABITANTES EN APARTAMENTO #32', 1, 21, '{}', '{}'),
(163, '2026-02-14 17:34:28', 'registrar', 'V21321332 (asdasda)', 1, 21, '{}', '{}'),
(164, '2026-02-14 17:34:29', 'consultar', 'HABITANTES EN APARTAMENTO #32', 1, 21, '{}', '{}'),
(165, '2026-02-14 17:34:39', 'eliminar', ' ()', 1, 21, '{}', '{}'),
(166, '2026-02-14 17:34:47', 'consultar', 'TODAS LAS PUBLICACIONES', 1, 5, '{}', '{}'),
(167, '2026-02-14 17:34:51', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(168, '2026-02-14 17:34:53', 'consultar', 'HABITANTES EN APARTAMENTO #32', 1, 21, '{}', '{}'),
(169, '2026-02-14 17:36:08', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(170, '2026-02-14 17:36:10', 'consultar', 'HABITANTES EN APARTAMENTO #32', 1, 21, '{}', '{}'),
(171, '2026-02-14 17:37:20', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(172, '2026-02-14 17:37:22', 'consultar', 'HABITANTES EN APARTAMENTO #32', 1, 21, '{}', '{}'),
(173, '2026-02-14 17:38:23', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(174, '2026-02-14 17:38:25', 'consultar', 'HABITANTES EN APARTAMENTO #32', 1, 21, '{}', '{}'),
(175, '2026-02-14 17:40:53', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(176, '2026-02-14 17:41:06', 'registrar', '12', 1, 6, '{}', '{}'),
(177, '2026-02-14 17:41:06', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(178, '2026-02-14 17:41:09', 'eliminar', '12', 1, 6, '{}', '{}'),
(179, '2026-02-14 17:41:09', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(180, '2026-02-14 17:41:28', 'consultar', 'TODAS LAS PUBLICACIONES', 1, 5, '{}', '{}'),
(181, '2026-02-14 17:41:54', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(182, '2026-02-14 17:44:42', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(183, '2026-02-14 17:45:03', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(184, '2026-02-14 17:45:19', 'registrar', 'Mensualidad del mes 2 del 2025. De 7.65 Bs.', 1, 3, '{}', '{}'),
(185, '2026-02-14 17:45:20', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(186, '2026-02-14 17:45:23', 'eliminar', 'Eliminadas menusalidades del mes 2 del 2025', 1, 3, '{}', '{}'),
(187, '2026-02-14 17:45:24', 'consultar', 'TODOS LAS MENSUALIDADES', 1, 3, '{}', '{}'),
(188, '2026-02-14 17:45:50', 'consultar', 'TODOS LAS CAJAS', 1, 4, '{}', '{}'),
(189, '2026-02-14 17:47:31', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(190, '2026-02-14 17:47:32', 'consultar', 'TODOS LOS GASTOS', 1, 2, '{}', '{}'),
(191, '2026-02-14 17:49:29', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(192, '2026-02-14 17:49:30', 'consultar', 'TODOS LOS GASTOS', 1, 2, '{}', '{}'),
(193, '2026-02-14 17:49:51', 'registrar', 'asdaasdsadas', 1, 2, '{}', '{}'),
(194, '2026-02-14 17:49:52', 'consultar', 'TODOS LOS GASTOS', 1, 2, '{}', '{}'),
(195, '2026-02-14 17:49:57', 'eliminar', 'asdasdasdasdasd', 1, 2, '{}', '{}'),
(196, '2026-02-14 17:50:00', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(197, '2026-02-14 17:50:02', 'consultar', 'TODOS LOS GASTOS', 1, 2, '{}', '{}'),
(198, '2026-02-14 17:50:09', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(199, '2026-02-14 17:50:10', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(200, '2026-02-14 17:50:12', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(201, '2026-02-14 17:50:12', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(202, '2026-02-14 17:50:12', 'consultar', 'TODOS LOS PAGOS', 1, 1, '{}', '{}'),
(203, '2026-02-14 17:52:25', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(204, '2026-02-14 17:52:25', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(205, '2026-02-14 17:52:27', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(206, '2026-02-14 17:52:27', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(207, '2026-02-14 17:52:27', 'consultar', 'TODOS LOS PAGOS', 1, 1, '{}', '{}'),
(208, '2026-02-14 17:52:34', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(209, '2026-02-14 17:52:34', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(210, '2026-02-14 17:52:35', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(211, '2026-02-14 17:52:35', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(212, '2026-02-14 17:52:35', 'consultar', 'TODOS LOS PAGOS', 1, 1, '{}', '{}'),
(213, '2026-02-14 17:52:38', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(214, '2026-02-14 17:52:38', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(215, '2026-02-14 17:53:59', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(216, '2026-02-14 17:53:59', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(217, '2026-02-14 17:54:21', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(218, '2026-02-14 17:54:21', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(219, '2026-02-14 17:54:21', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(220, '2026-02-14 17:54:21', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(221, '2026-02-14 17:54:30', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(222, '2026-02-14 17:54:30', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(223, '2026-02-14 17:54:31', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(224, '2026-02-14 17:54:31', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(225, '2026-02-14 17:54:31', 'consultar', 'TODOS LOS PAGOS', 1, 1, '{}', '{}'),
(226, '2026-02-14 17:54:36', 'consultar', 'TODOS LOS BANCOS', 1, 12, '{}', '{}'),
(227, '2026-02-14 17:54:36', 'consultar', 'TODOS LOS APARTAMENTOS', 1, 6, '{}', '{}'),
(228, '2026-02-14 17:54:36', 'eliminar', '10.00 (asdasd)', 1, 1, '{}', '{}'),
(229, '2026-02-14 22:01:12', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(230, '2026-02-14 22:01:19', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(231, '2026-02-14 22:01:49', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(232, '2026-02-17 20:46:21', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(233, '2026-02-17 20:46:59', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(234, '2026-02-17 20:50:11', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(235, '2026-02-17 20:50:55', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(236, '2026-02-17 20:56:43', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(237, '2026-02-17 20:58:49', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(238, '2026-02-18 11:33:36', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(239, '2026-02-18 12:57:00', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(240, '2026-02-18 13:07:08', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(241, '2026-02-18 13:07:28', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(242, '2026-02-18 16:29:52', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(243, '2026-02-18 16:30:08', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(244, '2026-02-18 18:18:12', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(245, '2026-02-18 18:43:46', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(246, '2026-02-18 18:51:48', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(247, '2026-02-18 18:52:36', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(248, '2026-02-18 18:53:11', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(249, '2026-02-18 19:10:41', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(250, '2026-02-18 19:10:50', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(251, '2026-02-18 19:11:16', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(252, '2026-02-18 19:19:40', 'registrar', 'Copia de seguridad generada: seguridad', 1, 19, '{}', '{}'),
(253, '2026-02-18 19:21:02', 'registrar', 'Copia de seguridad generada: seguridad', 1, 19, '{}', '{}'),
(254, '2026-02-18 19:22:43', 'registrar', 'Copia de seguridad generada: seguridad', 1, 19, '{}', '{}'),
(255, '2026-02-18 19:22:58', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(256, '2026-02-18 19:23:06', 'registrar', 'Copia de seguridad generada: negocio', 1, 19, '{}', '{}'),
(257, '2026-02-18 19:26:12', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(258, '2026-02-18 19:35:10', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(259, '2026-02-18 19:35:14', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(260, '2026-02-18 19:35:29', 'registrar', 'Copia de seguridad generada: negocio', 1, 19, '{}', '{}'),
(261, '2026-02-18 19:35:36', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(262, '2026-02-18 19:42:29', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(263, '2026-02-18 19:42:36', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(264, '2026-02-18 19:42:51', 'registrar', 'Copia de seguridad generada: negocio', 1, 19, '{}', '{}'),
(265, '2026-02-18 22:59:48', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(266, '2026-02-18 23:03:14', 'consultar', 'Consulta general de usuarios', 1, 14, '{}', '{}'),
(267, '2026-02-18 23:03:43', 'consultar', 'Consulta general de usuarios', 1, 14, '{}', '{}'),
(268, '2026-02-18 23:07:30', 'consultar', 'Consulta general de usuarios', 1, 14, '{}', '{}'),
(269, '2026-02-18 23:08:49', 'consultar', 'Consulta general de usuarios', 1, 14, '{}', '{}'),
(270, '2026-02-18 23:09:15', 'consultar', 'Consulta general de usuarios', 1, 14, '{}', '{}'),
(271, '2026-02-18 23:09:45', 'consultar', 'Consulta general de usuarios', 1, 14, '{}', '{}'),
(272, '2026-02-18 23:12:54', 'consultar', 'Consulta general de usuarios', 1, 14, '{}', '{}'),
(273, '2026-02-18 23:25:34', 'consultar', 'Consulta general de tipos de gasto', 1, 13, '{}', '{}'),
(274, '2026-02-18 23:26:46', 'consultar', 'Consulta general de tipos de gasto', 1, 13, '{}', '{}'),
(275, '2026-02-18 23:28:05', 'consultar', 'Consulta general de tipos de gasto', 1, 13, '{}', '{}'),
(276, '2026-02-18 23:29:30', 'consultar', 'Consulta general de tipos de gasto', 1, 13, '{}', '{}'),
(277, '2026-02-18 23:29:47', 'registrar', 'hola', 1, 13, '{}', '{}'),
(278, '2026-02-18 23:29:48', 'consultar', 'Consulta general de tipos de gasto', 1, 13, '{}', '{}'),
(279, '2026-02-18 23:29:51', 'eliminar', 'hola', 1, 13, '{}', '{}'),
(280, '2026-02-18 23:29:51', 'consultar', 'Consulta general de tipos de gasto', 1, 13, '{}', '{}'),
(281, '2026-02-18 23:30:10', 'consultar', 'Consulta general de usuarios', 1, 14, '{}', '{}'),
(282, '2026-02-18 23:30:17', 'consultar', 'Consulta general de tipos de gasto', 1, 13, '{}', '{}'),
(283, '2026-02-18 23:31:46', 'consultar', 'Consulta general de tipos de gasto', 1, 13, '{}', '{}'),
(284, '2026-02-18 23:34:19', 'consultar', 'Consulta general de usuarios', 1, 14, '{}', '{}'),
(285, '2026-02-18 23:36:19', 'modificar', 'Pepes Campos', 1, 14, '{}', '{}'),
(286, '2026-02-18 23:36:19', 'consultar', 'Consulta general de usuarios', 1, 14, '{}', '{}'),
(287, '2026-02-18 23:38:13', 'consultar', 'Consulta general de usuarios', 1, 14, '{}', '{}'),
(288, '2026-02-18 23:42:43', 'consultar', 'Consulta general de usuarios', 1, 14, '{}', '{}'),
(289, '2026-02-18 23:59:26', 'consultar', 'Consulta general de usuarios', 1, 14, '{}', '{}'),
(290, '2026-02-19 00:26:21', 'modificar', 'Perfil propio actualizado', 1, 14, '{}', '{}'),
(291, '2026-02-19 00:29:25', 'modificar', 'Perfil propio actualizado', 1, 14, '{}', '{}'),
(292, '2026-02-19 08:59:05', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(293, '2026-02-19 09:01:10', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(294, '2026-02-19 09:01:27', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(295, '2026-02-19 09:02:23', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(296, '2026-02-19 09:04:03', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(297, '2026-02-19 09:04:35', 'consultar', 'Consulta general de usuarios', 1, 14, '{}', '{}'),
(298, '2026-02-19 09:04:41', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(299, '2026-02-19 09:13:09', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(300, '2026-02-19 09:15:38', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(301, '2026-02-19 09:18:13', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(302, '2026-02-19 09:18:20', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(303, '2026-02-19 09:20:58', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(304, '2026-02-19 09:22:15', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(305, '2026-02-19 09:25:38', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(306, '2026-02-19 09:38:13', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(307, '2026-02-19 09:38:48', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(308, '2026-02-19 09:39:13', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(309, '2026-02-19 09:39:26', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(310, '2026-02-19 09:46:00', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(311, '2026-02-19 09:47:19', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(312, '2026-02-19 09:48:36', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(313, '2026-02-19 09:48:56', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(314, '2026-02-19 09:52:55', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(315, '2026-02-19 09:55:37', 'registrar', 'asdaasd de pepe', 1, 7, '{}', '{}'),
(316, '2026-02-19 09:55:37', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(317, '2026-02-19 09:59:14', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(318, '2026-02-19 09:59:26', 'modificar', 'seee de pepe', 1, 7, '{}', '{}'),
(319, '2026-02-19 09:59:26', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(320, '2026-02-19 09:59:30', 'eliminar', 'seee de pepe', 1, 7, '{}', '{}'),
(321, '2026-02-19 09:59:30', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(322, '2026-02-19 10:00:06', 'consultar', 'Consulta general de solicitudes', 1, 7, '{}', '{}'),
(323, '2026-02-19 10:13:49', 'consultar', 'Consulta general de roles', 1, 17, '{}', '{}'),
(324, '2026-02-19 10:14:21', 'consultar', 'Consulta general de roles', 1, 17, '{}', '{}'),
(325, '2026-02-19 10:45:26', 'registrar', 'Rol: restess', 1, 17, '{}', '{}'),
(326, '2026-02-19 10:45:26', 'consultar', 'Consulta general de roles', 1, 17, '{}', '{}'),
(327, '2026-02-19 10:46:56', 'consultar', 'Consulta general de roles', 1, 17, '{}', '{}'),
(328, '2026-02-19 10:47:41', 'eliminar', 'Rol: reste', 1, 17, '{}', '{}'),
(329, '2026-02-19 10:47:41', 'consultar', 'Consulta general de roles', 1, 17, '{}', '{}'),
(330, '2026-02-19 10:47:56', 'consultar', 'Consulta general de roles', 1, 17, '{}', '{}'),
(331, '2026-02-19 10:48:09', 'registrar', 'Rol: new', 1, 17, '{}', '{}'),
(332, '2026-02-19 10:48:10', 'consultar', 'Consulta general de roles', 1, 17, '{}', '{}'),
(333, '2026-02-19 10:53:52', 'consultar', 'Consulta general de roles', 1, 17, '{}', '{}'),
(334, '2026-02-19 10:57:37', 'consultar', 'Consulta general de roles', 1, 17, '{}', '{}'),
(335, '2026-02-19 11:03:20', 'consultar', 'Consulta general de roles', 1, 17, '{}', '{}'),
(336, '2026-02-19 11:03:46', 'modificar', 'Rol: new', 1, 17, '{}', '{}'),
(337, '2026-02-19 11:03:46', 'consultar', 'Consulta general de roles', 1, 17, '{}', '{}'),
(338, '2026-02-19 11:03:54', 'eliminar', 'Rol: new', 1, 17, '{}', '{}'),
(339, '2026-02-19 11:03:54', 'consultar', 'Consulta general de roles', 1, 17, '{}', '{}'),
(340, '2026-02-19 11:19:55', 'consultar', 'Consulta general de proveedores', 1, 11, '{}', '{}'),
(341, '2026-02-19 11:20:03', 'consultar', 'Consulta general de proveedores', 1, 11, '{}', '{}'),
(342, '2026-02-19 11:23:08', 'registrar', 'hee - J2423423', 1, 11, '{}', '{}'),
(343, '2026-02-19 11:23:08', 'consultar', 'Consulta general de proveedores', 1, 11, '{}', '{}'),
(344, '2026-02-19 11:23:15', 'modificar', 'hees - J2423423', 1, 11, '{}', '{}'),
(345, '2026-02-19 11:23:15', 'consultar', 'Consulta general de proveedores', 1, 11, '{}', '{}'),
(346, '2026-02-19 11:23:19', 'eliminar', 'hees - J2423423', 1, 11, '{}', '{}'),
(347, '2026-02-19 11:23:19', 'consultar', 'Consulta general de proveedores', 1, 11, '{}', '{}'),
(348, '2026-02-19 11:51:04', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(349, '2026-02-19 11:51:10', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(350, '2026-02-19 11:55:06', 'registrar', '2022-10-10 - Abierto', 1, 9, '{}', '{}'),
(351, '2026-02-19 11:55:06', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(352, '2026-02-19 11:55:16', 'modificar', '2022-10-10 - Abierto', 1, 9, '{}', '{}'),
(353, '2026-02-19 11:55:16', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(354, '2026-02-19 11:55:19', 'eliminar', '2022-10-10 - Abierto', 1, 9, '{}', '{}'),
(355, '2026-02-19 11:55:19', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(356, '2026-02-19 15:23:21', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(357, '2026-02-19 15:24:03', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(358, '2026-02-19 15:24:13', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(359, '2026-02-19 15:38:12', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(360, '2026-02-19 15:39:26', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(361, '2026-02-19 15:40:12', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(362, '2026-02-19 15:40:48', 'registrar', 'Apartamento N° 2-5', 1, 6, '{}', '{}'),
(363, '2026-02-19 15:40:48', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(364, '2026-02-19 15:40:55', 'modificar', 'Apartamento ID: 34', 1, 6, '{}', '{}'),
(365, '2026-02-19 15:40:55', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(366, '2026-02-19 15:40:58', 'eliminar', 'Apartamento N° 2-8', 1, 6, '{}', '{}'),
(367, '2026-02-19 15:40:58', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(368, '2026-02-19 15:57:44', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(369, '2026-02-19 15:58:32', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(370, '2026-02-19 16:21:15', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(371, '2026-02-19 16:28:57', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(372, '2026-02-19 16:32:54', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(373, '2026-02-19 16:37:09', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(374, '2026-02-19 16:38:20', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(375, '2026-02-19 16:38:41', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(376, '2026-02-19 16:41:20', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(377, '2026-02-19 16:43:17', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(378, '2026-02-19 16:48:24', 'registrar', 'pepe pepas', 1, 21, '{}', '{}'),
(379, '2026-02-19 16:50:57', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(380, '2026-02-19 16:59:06', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(381, '2026-02-19 17:03:06', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(382, '2026-02-19 17:06:43', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(383, '2026-02-19 17:07:30', 'registrar', 'sfdfdf sdfsdfs', 1, 21, '{}', '{}'),
(384, '2026-02-19 17:07:41', 'modificar', 'deee sdfsdfs', 1, 21, '{}', '{}'),
(385, '2026-02-19 17:07:47', 'eliminar', 'deee sdfsdfs', 1, 21, '{}', '{}'),
(386, '2026-02-19 17:07:57', 'modificar', 'Apartamento ID: 31', 1, 6, '{}', '{}'),
(387, '2026-02-19 17:07:57', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(388, '2026-02-19 17:08:32', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(389, '2026-02-19 17:08:56', 'consultar', 'Consulta general de apartamentos', 1, 6, '{}', '{}'),
(390, '2026-02-19 17:45:11', 'consultar', 'Consulta general de bancos', 1, 12, '{}', '{}'),
(391, '2026-02-19 17:45:54', 'registrar', 'tesoro (425646456456456456)', 1, 12, '{}', '{}'),
(392, '2026-02-19 17:45:54', 'consultar', 'Consulta general de bancos', 1, 12, '{}', '{}'),
(393, '2026-02-19 17:47:00', 'registrar', 'tesoro (2423423423423234234)', 1, 12, '{}', '{}'),
(394, '2026-02-19 17:47:00', 'consultar', 'Consulta general de bancos', 1, 12, '{}', '{}'),
(395, '2026-02-19 17:47:14', 'eliminar', 'tesoro (425646456456456456)', 1, 12, '{}', '{}'),
(396, '2026-02-19 17:47:14', 'consultar', 'Consulta general de bancos', 1, 12, '{}', '{}'),
(397, '2026-02-19 17:47:21', 'modificar', 'tesoro (2423423423423234234)', 1, 12, '{}', '{}'),
(398, '2026-02-19 17:47:22', 'consultar', 'Consulta general de bancos', 1, 12, '{}', '{}'),
(399, '2026-02-19 17:47:29', 'modificar', 'tesoros (2423423423423234234)', 1, 12, '{}', '{}'),
(400, '2026-02-19 17:47:29', 'consultar', 'Consulta general de bancos', 1, 12, '{}', '{}'),
(401, '2026-02-19 18:08:19', 'consultar', 'Consulta general de cartelera', 1, 5, '{}', '{}'),
(402, '2026-02-19 18:08:23', 'consultar', 'Consulta general de cartelera', 1, 5, '{}', '{}'),
(403, '2026-02-19 18:08:56', 'registrar', 'hola - 2222-10-10', 1, 5, '{}', '{}'),
(404, '2026-02-19 18:08:57', 'consultar', 'Consulta general de cartelera', 1, 5, '{}', '{}'),
(405, '2026-02-19 18:10:44', 'consultar', 'Consulta general de cartelera', 1, 5, '{}', '{}'),
(406, '2026-02-19 18:10:53', 'modificar', 'hola - 2222-10-10', 1, 5, '{}', '{}'),
(407, '2026-02-19 18:10:53', 'consultar', 'Consulta general de cartelera', 1, 5, '{}', '{}'),
(408, '2026-02-19 18:14:59', 'consultar', 'Consulta general de cartelera', 1, 5, '{}', '{}'),
(409, '2026-02-19 18:15:09', 'consultar', 'Consulta general de cartelera', 1, 5, '{}', '{}'),
(410, '2026-02-19 18:15:32', 'consultar', 'Consulta general de cartelera', 1, 5, '{}', '{}'),
(411, '2026-02-19 18:15:39', 'modificar', 'hola - 2222-10-10', 1, 5, '{}', '{}'),
(412, '2026-02-19 18:15:39', 'consultar', 'Consulta general de cartelera', 1, 5, '{}', '{}'),
(413, '2026-02-19 18:16:42', 'consultar', 'Consulta general de cartelera', 1, 5, '{}', '{}'),
(414, '2026-02-19 18:17:00', 'registrar', 'asdasd - 2000-10-10', 1, 5, '{}', '{}'),
(415, '2026-02-19 18:17:00', 'consultar', 'Consulta general de cartelera', 1, 5, '{}', '{}'),
(416, '2026-02-19 18:17:05', 'eliminar', 'asdasd - 2000-10-10', 1, 5, '{}', '{}'),
(417, '2026-02-19 18:17:05', 'consultar', 'Consulta general de cartelera', 1, 5, '{}', '{}'),
(418, '2026-02-19 18:17:28', 'registrar', 'asdasq - 2100-10-10', 1, 5, '{}', '{}'),
(419, '2026-02-19 18:17:29', 'consultar', 'Consulta general de cartelera', 1, 5, '{}', '{}'),
(420, '2026-02-19 18:17:34', 'eliminar', 'asdasq - 2100-10-10', 1, 5, '{}', '{}'),
(421, '2026-02-19 18:17:35', 'consultar', 'Consulta general de cartelera', 1, 5, '{}', '{}'),
(422, '2026-02-19 18:41:13', 'consultar', 'Consulta general de cajas', 1, 4, '{}', '{}'),
(423, '2026-02-19 18:41:19', 'consultar', 'Consulta general de cajas', 1, 4, '{}', '{}'),
(424, '2026-02-19 18:41:32', 'consultar', 'Consulta general de cajas', 1, 4, '{}', '{}'),
(425, '2026-02-19 18:46:50', 'consultar', 'Consulta general de cajas', 1, 4, '{}', '{}'),
(426, '2026-02-19 18:47:30', 'modificar', 'Actualizó descripción caja ID: 23', 1, 4, '{}', '{}'),
(427, '2026-02-19 18:47:30', 'consultar', 'Consulta general de cajas', 1, 4, '{}', '{}'),
(428, '2026-02-19 18:47:53', 'consultar', 'Consulta general de cajas', 1, 4, '{}', '{}'),
(429, '2026-02-19 19:00:45', 'registrar', 'Nuevo Gasto: 1231asd (1)', 1, 4, '{}', '{}'),
(430, '2026-02-19 19:00:52', 'eliminar', 'Anulación Gasto ID: 35', 1, 4, '{}', '{}'),
(431, '2026-02-19 19:00:54', 'consultar', 'Consulta general de cajas', 1, 4, '{}', '{}'),
(432, '2026-02-19 19:03:42', 'consultar', 'Consulta general de cajas', 1, 4, '{}', '{}'),
(433, '2026-02-19 19:04:01', 'registrar', 'Nuevo Gasto: cafeee (398.75)', 1, 4, '{}', '{}'),
(434, '2026-02-19 19:05:10', 'consultar', 'Consulta general de cajas', 1, 4, '{}', '{}'),
(435, '2026-02-19 19:05:55', 'modificar', 'Editó movimiento ID: 36', 1, 4, '{}', '{}'),
(436, '2026-02-19 19:06:02', 'modificar', 'Editó movimiento ID: 36', 1, 4, '{}', '{}'),
(437, '2026-02-19 19:14:39', 'registrar', 'Reposición de caja ID: 23 por 398.75', 1, 4, '{}', '{}'),
(438, '2026-02-19 19:14:39', 'consultar', 'Consulta general de cajas', 1, 4, '{}', '{}'),
(439, '2026-02-19 19:14:45', 'consultar', 'Consulta general de cajas', 1, 4, '{}', '{}'),
(440, '2026-02-19 19:16:35', 'registrar', 'Reposición de caja ID: 23 por 398.75', 1, 4, '{}', '{}'),
(441, '2026-02-19 19:16:36', 'consultar', 'Consulta general de cajas', 1, 4, '{}', '{}'),
(442, '2026-02-19 19:18:41', 'consultar', 'Consulta general de cajas', 1, 4, '{}', '{}'),
(443, '2026-02-19 19:18:50', 'registrar', 'Reposición de caja ID: 23 por 398.75', 1, 4, '{}', '{}'),
(444, '2026-02-19 19:18:50', 'consultar', 'Consulta general de cajas', 1, 4, '{}', '{}'),
(445, '2026-02-19 21:42:04', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(446, '2026-02-19 22:53:06', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(447, '2026-02-19 22:53:09', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(448, '2026-02-19 22:53:17', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(449, '2026-02-19 22:54:18', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(450, '2026-02-19 22:57:07', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(451, '2026-02-19 22:57:34', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(452, '2026-02-19 23:02:35', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(453, '2026-02-19 23:10:34', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(454, '2026-02-19 23:18:22', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(455, '2026-02-19 23:37:48', 'registrar', 'asdasdasdasd', 1, 2, '{}', '{}'),
(456, '2026-02-19 23:37:48', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(457, '2026-02-19 23:38:04', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(458, '2026-02-19 23:45:31', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(459, '2026-02-19 23:47:20', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(460, '2026-02-19 23:47:25', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(461, '2026-02-19 23:48:51', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(462, '2026-02-19 23:59:42', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(463, '2026-02-20 00:02:40', 'registrar', 'asdasdasasd', 1, 2, '{}', '{}'),
(464, '2026-02-20 00:02:48', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(465, '2026-02-20 00:03:46', 'registrar', '3333333333333333', 1, 2, '{}', '{}'),
(466, '2026-02-20 00:03:47', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(467, '2026-02-20 00:07:25', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(468, '2026-02-20 00:07:56', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(469, '2026-02-20 00:08:02', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(470, '2026-02-20 00:18:55', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(471, '2026-02-20 00:20:24', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(472, '2026-02-20 00:20:46', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(473, '2026-02-20 00:26:53', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(474, '2026-02-20 00:28:26', 'modificar', 'holassssssssssssss', 1, 2, '{}', '{}'),
(475, '2026-02-20 00:28:26', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(476, '2026-02-20 00:28:56', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(477, '2026-02-20 00:30:08', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(478, '2026-02-20 00:30:37', 'modificar', 'holassssssssssssss', 1, 2, '{}', '{}'),
(479, '2026-02-20 00:30:37', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(480, '2026-02-20 00:30:50', 'modificar', 'holasssssssssssssssaaa', 1, 2, '{}', '{}'),
(481, '2026-02-20 00:30:50', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(482, '2026-02-20 00:31:01', 'eliminar', '', 1, 2, '{}', '{}'),
(483, '2026-02-20 00:31:01', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(484, '2026-02-20 00:31:18', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(485, '2026-02-20 10:57:43', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(486, '2026-02-20 11:20:18', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(487, '2026-02-20 11:20:29', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(488, '2026-02-20 11:20:49', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(489, '2026-02-20 11:25:24', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(490, '2026-02-20 11:31:47', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(491, '2026-02-20 11:31:56', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(492, '2026-02-20 11:33:10', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(493, '2026-02-20 11:35:53', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(494, '2026-02-20 11:36:27', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(495, '2026-02-20 11:36:53', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(496, '2026-02-20 11:38:48', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(497, '2026-02-20 11:40:25', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(498, '2026-02-20 11:41:11', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(499, '2026-02-20 11:41:37', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(500, '2026-02-20 11:42:14', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(501, '2026-02-20 11:43:15', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(502, '2026-02-20 11:46:30', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(503, '2026-02-20 11:46:47', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(504, '2026-02-20 11:56:13', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(505, '2026-02-20 11:58:02', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(506, '2026-02-20 11:59:27', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(507, '2026-02-20 11:59:54', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(508, '2026-02-20 12:00:48', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(509, '2026-02-20 12:01:48', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(510, '2026-02-20 12:03:51', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(511, '2026-02-20 12:04:48', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(512, '2026-02-20 12:05:55', 'registrar', 'Registro masivo de mensualidades para periodo 2/2025', 1, 3, '{}', '{}'),
(513, '2026-02-20 12:05:55', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(514, '2026-02-20 12:24:48', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(515, '2026-02-20 12:25:27', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(516, '2026-02-20 12:40:15', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(517, '2026-02-20 12:41:40', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(518, '2026-02-20 12:42:18', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(519, '2026-02-20 12:45:03', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(520, '2026-02-20 12:45:30', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(521, '2026-02-20 12:46:23', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(522, '2026-02-20 12:46:58', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(523, '2026-02-20 12:48:16', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(524, '2026-02-20 12:48:56', 'modificar', 'Edición masiva de mensualidades para periodo NaN/NaN', 1, 3, '{}', '{}'),
(525, '2026-02-20 12:48:57', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(526, '2026-02-20 12:49:22', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(527, '2026-02-20 12:49:46', 'registrar', 'Registro masivo de mensualidades para periodo 3/2025', 1, 3, '{}', '{}'),
(528, '2026-02-20 12:49:46', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(529, '2026-02-20 12:51:51', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(530, '2026-02-20 12:52:03', 'modificar', 'Edición masiva de mensualidades para periodo 2/2025', 1, 3, '{}', '{}'),
(531, '2026-02-20 12:52:04', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(532, '2026-02-20 12:52:32', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(533, '2026-02-20 12:52:41', 'registrar', 'Registro masivo de mensualidades para periodo 1/2025', 1, 3, '{}', '{}'),
(534, '2026-02-20 12:52:42', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(535, '2026-02-20 12:53:21', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(536, '2026-02-20 12:54:49', 'eliminar', 'Mensualidades del mes 01 del 2025 eliminadas', 1, 3, '{}', '{}'),
(537, '2026-02-20 12:54:49', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(538, '2026-02-20 12:55:09', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(539, '2026-02-20 12:55:17', 'registrar', 'Registro masivo de mensualidades para periodo 2/2025', 1, 3, '{}', '{}'),
(540, '2026-02-20 12:55:17', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(541, '2026-02-20 13:01:51', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(542, '2026-02-20 13:02:36', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(543, '2026-02-20 13:02:55', 'modificar', 'Edición masiva de mensualidades para periodo 2/2025', 1, 3, '{}', '{}'),
(544, '2026-02-20 13:02:55', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(545, '2026-02-20 13:03:04', 'modificar', 'Edición masiva de mensualidades para periodo 2/2025', 1, 3, '{}', '{}'),
(546, '2026-02-20 13:03:04', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(547, '2026-02-20 13:03:46', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(548, '2026-02-20 13:04:34', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(549, '2026-02-20 13:05:34', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(550, '2026-02-20 13:05:51', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(551, '2026-02-20 13:12:04', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(552, '2026-02-20 13:17:28', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(553, '2026-02-20 13:17:56', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(554, '2026-02-20 13:27:19', 'registrar', 'Registro masivo de mensualidades para periodo 3/2025', 1, 3, '{}', '{}'),
(555, '2026-02-20 13:27:19', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(556, '2026-02-20 13:27:46', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(557, '2026-02-20 13:27:52', 'registrar', 'Registro masivo de mensualidades para periodo 4/2025', 1, 3, '{}', '{}'),
(558, '2026-02-20 13:27:53', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(559, '2026-02-20 13:29:11', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}');
INSERT INTO `bitacora` (`id_bitacora`, `fecha_hora`, `accion`, `registro_alterado`, `usuario_id`, `modulo_id`, `valores_anteriores`, `valores_nuevos`) VALUES
(560, '2026-02-20 13:29:48', 'modificar', 'Edición masiva de mensualidades para periodo 2/2025', 1, 3, '{}', '{}'),
(561, '2026-02-20 13:29:48', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(562, '2026-02-20 13:35:28', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(563, '2026-02-20 13:35:38', 'registrar', 'Registro masivo de mensualidades para periodo 1/2025', 1, 3, '{}', '{}'),
(564, '2026-02-20 13:35:38', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(565, '2026-02-20 13:35:43', 'modificar', 'Edición masiva de mensualidades para periodo 1/2025', 1, 3, '{}', '{}'),
(566, '2026-02-20 13:35:43', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(567, '2026-02-20 16:54:26', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(568, '2026-02-20 16:54:39', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(569, '2026-02-20 16:57:43', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(570, '2026-02-20 16:57:54', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(571, '2026-02-20 17:00:11', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(572, '2026-02-20 17:00:45', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(573, '2026-02-20 17:00:51', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(574, '2026-02-20 17:01:47', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(575, '2026-02-20 17:02:24', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(576, '2026-02-20 17:10:59', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(577, '2026-02-20 17:11:10', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(578, '2026-02-20 17:11:34', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(579, '2026-02-20 17:12:57', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(580, '2026-02-20 17:13:43', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(581, '2026-02-20 17:19:47', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(582, '2026-02-20 17:21:31', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(583, '2026-02-20 17:21:39', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(584, '2026-02-20 17:22:15', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(585, '2026-02-20 17:25:29', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(586, '2026-02-20 17:26:26', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(587, '2026-02-20 17:28:43', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(588, '2026-02-20 17:29:24', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(589, '2026-02-20 17:31:20', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(590, '2026-02-20 17:31:55', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(591, '2026-02-20 17:35:00', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(592, '2026-02-20 17:35:46', 'registrar', 'Presupuesto del 2025-10-01', 1, 8, '{}', '{}'),
(593, '2026-02-20 17:35:48', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(594, '2026-02-20 17:36:08', 'modificar', 'Edición de Presupuesto ID: 101', 1, 8, '{}', '{}'),
(595, '2026-02-20 17:36:08', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(596, '2026-02-20 17:37:17', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(597, '2026-02-20 17:37:39', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(598, '2026-02-20 17:42:09', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(599, '2026-02-20 17:42:24', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(600, '2026-02-20 17:42:54', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(601, '2026-02-20 17:43:33', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(602, '2026-02-20 17:50:58', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(603, '2026-02-20 17:51:19', 'modificar', 'Edición de Presupuesto ID: 101', 1, 8, '{}', '{}'),
(604, '2026-02-20 17:51:19', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(605, '2026-02-20 17:51:28', 'eliminar', 'Presupuesto del 2025-01-01', 1, 8, '{}', '{}'),
(606, '2026-02-20 17:51:28', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(607, '2026-02-20 17:51:36', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(608, '2026-02-20 17:56:56', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(609, '2026-02-20 19:09:37', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(610, '2026-02-20 19:22:48', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(611, '2026-02-20 19:23:24', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(612, '2026-02-20 19:23:35', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(613, '2026-02-20 19:24:29', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(614, '2026-02-20 19:25:12', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(615, '2026-02-20 19:26:54', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(616, '2026-02-20 19:28:58', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(617, '2026-02-20 19:31:00', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(618, '2026-02-20 19:38:45', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(619, '2026-02-20 19:39:03', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(620, '2026-02-20 19:39:24', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(621, '2026-02-20 19:39:47', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(622, '2026-02-20 19:42:01', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(623, '2026-02-20 19:42:22', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(624, '2026-02-20 19:48:56', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(625, '2026-02-20 22:10:26', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(626, '2026-02-20 22:10:41', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(627, '2026-02-20 22:36:14', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(628, '2026-02-20 22:40:40', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(629, '2026-02-20 22:40:51', 'modificar', '3333333333333333', 1, 2, '{}', '{}'),
(630, '2026-02-20 22:41:28', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(631, '2026-02-20 22:41:35', 'modificar', '22222222222222222', 1, 2, '{}', '{}'),
(632, '2026-02-20 22:41:55', 'modificar', '22222222222222222', 1, 2, '{}', '{}'),
(633, '2026-02-20 22:41:56', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(634, '2026-02-20 22:52:10', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(635, '2026-02-20 22:52:14', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(636, '2026-02-20 22:54:26', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(637, '2026-02-20 22:54:45', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(638, '2026-02-20 22:57:47', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(639, '2026-02-20 22:58:15', 'registrar', 'Procesamiento atómico de Pago ID: 117', 1, 1, '{}', '{}'),
(640, '2026-02-20 22:58:15', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(641, '2026-02-20 22:59:30', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(642, '2026-02-20 23:01:56', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(643, '2026-02-20 23:05:49', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(644, '2026-02-20 23:06:10', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(645, '2026-02-20 23:06:48', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(646, '2026-02-20 23:07:44', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(647, '2026-02-20 23:08:24', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(648, '2026-02-20 23:20:56', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(649, '2026-02-20 23:27:08', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(650, '2026-02-21 00:22:02', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(651, '2026-02-21 00:22:50', 'registrar', 'Procesamiento atómico de Pago ID: 118', 1, 1, '{}', '{}'),
(652, '2026-02-21 00:22:50', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(653, '2026-02-21 00:23:51', 'modificar', 'Procesamiento atómico de Pago ID: 118', 1, 1, '{}', '{}'),
(654, '2026-02-21 00:23:52', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(655, '2026-02-21 00:30:52', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(656, '2026-02-21 00:31:02', 'registrar', 'Copia de seguridad generada: negocio', 1, 19, '{}', '{}'),
(657, '2026-02-21 00:31:16', 'registrar', 'Copia de seguridad generada: seguridad', 1, 19, '{}', '{}'),
(658, '2026-02-21 00:31:31', 'consultar', 'Consulta general de cartelera', 1, 5, '{}', '{}'),
(659, '2026-02-21 00:31:38', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(660, '2026-02-21 00:31:50', 'registrar', '2026-02-03 - Abierto', 1, 9, '{}', '{}'),
(661, '2026-02-21 00:31:51', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(662, '2026-02-21 00:31:53', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(663, '2026-02-21 00:42:59', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(664, '2026-02-21 00:43:10', 'registrar', 'Copia de seguridad generada: negocio', 1, 19, '{}', '{}'),
(665, '2026-02-21 00:45:48', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(666, '2026-02-21 00:46:03', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(667, '2026-02-21 00:50:24', 'registrar', 'Copia de seguridad generada: negocio', 1, 19, '{}', '{}'),
(668, '2026-02-21 00:50:46', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(669, '2026-02-21 00:51:42', 'modificar', '2026-02-07 - Abierto', 1, 9, '{}', '{}'),
(670, '2026-02-21 00:51:42', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(671, '2026-02-21 00:51:48', 'modificar', '2026-02-07 - Abierto', 1, 9, '{}', '{}'),
(672, '2026-02-21 00:51:49', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(673, '2026-02-21 00:51:51', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(674, '2026-02-21 00:52:04', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(675, '2026-02-21 00:55:40', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(676, '2026-02-21 00:56:49', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(677, '2026-02-21 01:04:12', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(678, '2026-02-21 01:04:22', 'registrar', 'Copia de seguridad generada: negocio', 1, 19, '{}', '{}'),
(679, '2026-02-21 01:04:49', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(680, '2026-02-21 01:04:55', 'modificar', '2026-02-07 - Abierto', 1, 9, '{}', '{}'),
(681, '2026-02-21 01:04:56', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(682, '2026-02-21 01:04:58', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(683, '2026-02-21 01:06:12', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(684, '2026-02-21 01:06:18', 'modificar', '2026-02-07 - Abierto', 1, 9, '{}', '{}'),
(685, '2026-02-21 01:06:18', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(686, '2026-02-21 01:06:20', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(687, '2026-02-21 01:09:35', 'registrar', 'Copia importada: backup_haydee_db_2026-02-21-07-04-19.sql en negocio', 1, 19, '{}', '{}'),
(688, '2026-02-21 01:09:44', 'consultar', 'Consulta general de años fiscales', 1, 9, '{}', '{}'),
(689, '2026-02-21 01:23:24', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(690, '2026-02-21 01:23:34', 'registrar', 'Copia de seguridad generada: negocio', 1, 19, '{}', '{}'),
(691, '2026-02-21 01:23:54', 'consultar', 'Acceso a módulo de mantenimiento', 1, 19, '{}', '{}'),
(692, '2026-02-21 01:24:25', 'registrar', 'Copia importada: backup_haydee_db_2026-02-21-07-23-31.sql en negocio', 1, 19, '{}', '{}'),
(693, '2026-02-21 01:28:15', 'registrar', 'Copia importada: backup_seguridad_haydee_db_2026-02-21-07-27-35.sql en seguridad', 1, 19, '{}', '{}'),
(694, '2026-02-21 01:32:04', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(695, '2026-02-21 08:53:15', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(696, '2026-02-21 08:53:42', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(697, '2026-02-21 09:01:10', 'consultar', 'Acceso a reportes estadísticos', 1, 15, '{}', '{}'),
(698, '2026-02-21 09:19:55', 'consultar', 'Acceso a reportes estadísticos', 1, 15, '{}', '{}'),
(699, '2026-02-21 09:21:58', 'consultar', 'Acceso a reportes estadísticos', 1, 15, '{}', '{}'),
(700, '2026-02-21 10:20:30', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(701, '2026-02-21 10:21:29', 'consultar', 'Acceso a reportes estadísticos', 1, 15, '{}', '{}'),
(702, '2026-02-21 11:48:48', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(703, '2026-02-21 11:48:56', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(704, '2026-02-21 11:49:43', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(705, '2026-02-21 11:50:16', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(706, '2026-02-21 11:51:16', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(707, '2026-02-21 11:51:30', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(708, '2026-02-21 11:52:19', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(709, '2026-02-21 12:01:51', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(710, '2026-02-21 12:05:08', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(711, '2026-02-21 12:05:24', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(712, '2026-02-21 12:07:27', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(713, '2026-02-21 12:08:13', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(714, '2026-02-21 12:08:52', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(715, '2026-02-21 12:09:07', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(716, '2026-02-21 12:11:33', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(717, '2026-02-21 12:12:10', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(718, '2026-02-21 12:12:34', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(719, '2026-02-21 12:12:39', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(720, '2026-02-21 12:12:58', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(721, '2026-02-21 12:13:21', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(722, '2026-02-21 12:30:09', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(723, '2026-02-21 14:56:57', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(724, '2026-02-21 14:57:04', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(725, '2026-02-21 14:57:19', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(726, '2026-02-21 15:23:17', 'consultar', 'Acceso a reportes PDF', 1, 15, '{}', '{}'),
(727, '2026-02-21 15:25:29', 'consultar', 'Acceso a reportes estadísticos', 1, 15, '{}', '{}'),
(728, '2026-02-21 15:27:59', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(729, '2026-02-21 15:34:19', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(730, '2026-02-21 15:35:25', 'modificar', '22222222222222222', 1, 2, '{}', '{}'),
(731, '2026-02-21 15:35:25', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(732, '2026-02-21 15:35:40', 'modificar', 'holasssssssssssssssaaa', 1, 2, '{}', '{}'),
(733, '2026-02-21 15:35:41', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(734, '2026-02-21 15:35:57', 'modificar', '22222222222222222', 1, 2, '{}', '{}'),
(735, '2026-02-21 15:35:57', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(736, '2026-02-21 15:36:27', 'modificar', '22222222222222222', 1, 2, '{}', '{}'),
(737, '2026-02-21 15:36:27', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(738, '2026-02-21 15:36:37', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(739, '2026-02-21 15:36:50', 'modificar', 'Procesamiento atómico de Pago ID: 118', 1, 1, '{}', '{}'),
(740, '2026-02-21 15:36:50', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(741, '2026-02-21 15:52:47', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(742, '2026-02-21 15:53:05', 'modificar', '22222222222222222', 1, 2, '{}', '{}'),
(743, '2026-02-21 15:53:05', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(744, '2026-02-21 15:53:15', 'modificar', '222222222222222223', 1, 2, '{}', '{}'),
(745, '2026-02-21 15:53:15', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(746, '2026-02-21 15:53:42', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(747, '2026-02-21 15:53:57', 'modificar', '222222222222222223', 1, 2, '{}', '{}'),
(748, '2026-02-21 15:53:58', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(749, '2026-02-21 15:54:08', 'modificar', '222222222222224', 1, 2, '{}', '{}'),
(750, '2026-02-21 15:54:08', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(751, '2026-02-21 15:56:19', 'modificar', '222222222222224', 1, 2, '{}', '{}'),
(752, '2026-02-21 15:56:33', 'modificar', '222222222222224', 1, 2, '{}', '{}'),
(753, '2026-02-21 15:56:33', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(754, '2026-02-21 15:57:47', 'modificar', '222222222222224', 1, 2, '{}', '{}'),
(755, '2026-02-21 15:57:47', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(756, '2026-02-21 15:58:06', 'modificar', '222222222222224', 1, 2, '{}', '{}'),
(757, '2026-02-21 15:58:06', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(758, '2026-02-21 16:01:06', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(759, '2026-02-21 16:01:17', 'modificar', 'Procesamiento atómico de Pago ID: 118', 1, 1, '{}', '{}'),
(760, '2026-02-21 16:01:17', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(761, '2026-02-21 16:03:22', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(762, '2026-02-21 16:03:26', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(763, '2026-02-21 16:04:07', 'modificar', 'Procesamiento atómico de Pago ID: 117', 1, 1, '{}', '{}'),
(764, '2026-02-21 16:04:07', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(765, '2026-02-21 16:04:37', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(766, '2026-02-21 16:05:23', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(767, '2026-02-21 16:10:58', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(768, '2026-02-21 16:14:05', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(769, '2026-02-21 16:14:10', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(770, '2026-02-21 16:20:13', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(771, '2026-02-21 16:20:30', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(772, '2026-02-21 16:21:21', 'modificar', 'Procesamiento atómico de Pago ID: 117', 1, 1, '{}', '{}'),
(773, '2026-02-21 16:21:22', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(774, '2026-02-21 16:22:12', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(775, '2026-02-21 16:24:13', 'modificar', '222222222222224', 1, 2, '{}', '{}'),
(776, '2026-02-21 16:24:13', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(777, '2026-02-21 16:24:30', 'modificar', '222222222222224', 1, 2, '{}', '{}'),
(778, '2026-02-21 16:24:31', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(779, '2026-02-21 16:27:39', 'modificar', '222222222222224', 1, 2, '{}', '{}'),
(780, '2026-02-21 16:27:39', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(781, '2026-02-21 16:27:49', 'modificar', '222222222222224', 1, 2, '{}', '{}'),
(782, '2026-02-21 16:27:49', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(783, '2026-02-21 16:28:01', 'modificar', '222222222222224', 1, 2, '{}', '{}'),
(784, '2026-02-21 16:28:01', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(785, '2026-02-21 16:29:19', 'modificar', '222222222222224', 1, 2, '{}', '{}'),
(786, '2026-02-21 16:29:19', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(787, '2026-02-21 16:45:41', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(788, '2026-02-21 16:45:56', 'modificar', '222222222222224', 1, 2, '{}', '{}'),
(789, '2026-02-21 16:45:56', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(790, '2026-02-21 16:46:09', 'modificar', '222222222222224', 1, 2, '{}', '{}'),
(791, '2026-02-21 16:46:09', 'consultar', 'Consulta general de gastos', 1, 2, '{}', '{}'),
(792, '2026-02-21 16:52:33', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(793, '2026-02-21 16:52:57', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(794, '2026-02-21 16:55:09', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(795, '2026-02-21 16:56:13', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(796, '2026-02-21 16:56:25', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(797, '2026-02-21 17:09:53', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(798, '2026-02-21 17:09:56', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(799, '2026-02-21 17:09:59', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(800, '2026-02-21 17:10:03', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(801, '2026-02-21 17:10:20', 'consultar', 'Consulta de pagos', 1, 1, '{}', '{}'),
(802, '2026-02-21 17:10:22', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(803, '2026-02-21 17:18:15', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(804, '2026-02-21 17:18:29', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(805, '2026-02-21 17:18:34', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(806, '2026-02-21 17:18:37', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(807, '2026-02-21 17:18:46', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(808, '2026-02-21 17:18:56', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(809, '2026-02-21 17:20:18', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(810, '2026-02-21 17:20:31', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(811, '2026-02-21 17:25:12', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(812, '2026-02-21 17:25:52', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(813, '2026-02-21 17:31:33', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(814, '2026-02-21 17:32:06', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(815, '2026-02-21 17:35:27', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(816, '2026-02-21 17:35:35', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(817, '2026-02-21 17:39:31', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(818, '2026-02-21 17:41:42', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(819, '2026-02-21 17:41:46', 'consultar', 'Consulta general de cajas', 1, 4, '{}', '{}'),
(820, '2026-02-21 17:43:47', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(821, '2026-02-21 17:44:33', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(822, '2026-02-21 17:44:54', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(823, '2026-02-21 17:45:00', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(824, '2026-02-21 17:46:00', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(825, '2026-02-21 17:46:04', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(826, '2026-02-21 17:49:18', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(827, '2026-02-21 17:51:32', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(828, '2026-02-21 17:51:47', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(829, '2026-02-21 17:52:46', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(830, '2026-02-21 17:53:50', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(831, '2026-02-21 17:54:04', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(832, '2026-02-21 17:54:18', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(833, '2026-02-21 17:55:33', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(834, '2026-02-21 18:06:31', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(835, '2026-02-21 18:06:38', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(836, '2026-02-21 18:07:36', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(837, '2026-02-21 18:07:41', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(838, '2026-02-21 18:10:35', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(839, '2026-02-21 18:10:39', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(840, '2026-02-21 18:10:42', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(841, '2026-02-21 18:10:47', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(842, '2026-02-21 18:24:31', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(843, '2026-02-21 18:24:36', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(844, '2026-02-21 18:24:42', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(845, '2026-02-21 18:26:05', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(846, '2026-02-21 18:27:46', 'consultar', 'Consulta de mensualidades por mes', 1, 3, '{}', '{}'),
(847, '2026-02-21 18:29:24', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(848, '2026-02-21 19:32:46', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(849, '2026-02-21 19:42:50', 'cerrar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(850, '2026-02-21 19:43:25', 'iniciar sesion', 'NINGUNO', 1, 14, '{}', '{}'),
(851, '2026-02-21 19:49:46', 'consultar', 'Consulta general de cajas', 1, 4, '{}', '{}'),
(852, '2026-02-21 19:51:12', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(853, '2026-02-21 19:51:36', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(854, '2026-02-21 19:51:48', 'modificar', 'Edición de Presupuesto ID: 84', 1, 8, '{}', '{}'),
(855, '2026-02-21 19:51:48', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(856, '2026-02-21 19:56:50', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(857, '2026-02-21 19:57:37', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(858, '2026-02-21 19:57:43', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(859, '2026-02-21 19:59:11', 'consultar', 'Consulta general de tipos de gasto', 1, 13, '{}', '{}'),
(860, '2026-02-21 19:59:28', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}'),
(861, '2026-02-21 20:04:10', 'consultar', 'Consulta general de presupuestos', 1, 8, '{}', '{}');

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
(19, 'bienvenidos', 'bienvenidos al 2026', '2100-10-10', '', 'WhatsApp_Image_2026-01-08_at_2.35.12_PM_1770308220.jpeg', '1', 1);

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
(7, 'Nueva Mensualidad', 'mensualidad', 513, '2026-02-14 16:45:19');

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
(21, 'GESTIONAR_HABITANTES', 1);

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
(152, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2026-02-14', 0, 1),
(153, 'Mensualidad de Apartamentos', 'Ya se asginaron las mensualidades de este mes', '2026-02-14', 0, 39);

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
(153, 7);

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
(43, 39, '9128e24e97876e33e71460e5df6dde1c2101298503ec74283914f10f57eb9916', '2026-02-22 01:45:53', 'RECUPERAR_CONTRASENIA'),
(45, 1, '9e8f15142af4f64e8ab94aeebfb7a497fe1aee858501778194f1438884faa365', '2026-03-24 01:43:25', 'RECORDAR_CONTRASENIA');

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
(1, 'jesus', 'escalona', 'administrador@gmail.com', '$2y$10$rHLqXErv1pL7G7hCFFVeruWhNn.lUj3HnBMfkP5jk/ptvAGFcOF..', 1, 1),
(2, 'francisco', 'mendoza', 'fran@gmasil.com', '$2y$10$qDrsHu1jAtLWmyyFj00L0uT4iOUadnmnKNWWVAckxXhnEzEMSsYRC', 4, 1),
(27, 'Pepes', 'Campos', 'pepe@gmail.com', '$2y$10$WWp8M1SADJzTWAg910K.mewfZFglQF77ENnqPYLmq1U9AKmmeruY2', 3, 1),
(39, 'Yhsius', 'escalona', 'jesusgescalonae@gmail.com', '$2y$10$raGt0fnRiaew7TzRlABsjuUqHE1STc4j13jd./gVPlJKyiZoIUfXe', 2, 1),
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
  MODIFY `id_bitacora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=862;

--
-- AUTO_INCREMENT de la tabla `cartelera_virtual`
--
ALTER TABLE `cartelera_virtual`
  MODIFY `id_cartelera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `eventos_sistema`
--
ALTER TABLE `eventos_sistema`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id_modulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=154;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT de la tabla `tokens_seguridad`
--
ALTER TABLE `tokens_seguridad`
  MODIFY `id_token` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

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
