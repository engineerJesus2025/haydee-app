-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-05-2026 a las 00:53:14
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
(2, 1, 1),
(2, 1, 2),
(2, 1, 3),
(2, 1, 4),
(2, 1, 5),
(2, 1, 6),
(2, 1, 7),
(2, 1, 8),
(2, 1, 9),
(2, 1, 10),
(2, 1, 11),
(2, 1, 12),
(2, 1, 13),
(2, 1, 14),
(2, 1, 15),
(2, 1, 16),
(2, 1, 17),
(2, 1, 18),
(2, 1, 19),
(2, 1, 21),
(2, 1, 22),
(2, 1, 23),
(2, 2, 1),
(2, 2, 2),
(2, 2, 3),
(2, 2, 4),
(2, 2, 5),
(2, 2, 6),
(2, 2, 7),
(2, 2, 8),
(2, 2, 9),
(2, 2, 10),
(2, 2, 11),
(2, 2, 12),
(2, 2, 13),
(2, 2, 14),
(2, 2, 15),
(2, 2, 16),
(2, 2, 17),
(2, 2, 18),
(2, 2, 19),
(2, 2, 21),
(2, 2, 22),
(2, 2, 23),
(2, 3, 1),
(2, 3, 2),
(2, 3, 3),
(2, 3, 4),
(2, 3, 5),
(2, 3, 6),
(2, 3, 7),
(2, 3, 8),
(2, 3, 9),
(2, 3, 10),
(2, 3, 11),
(2, 3, 12),
(2, 3, 13),
(2, 3, 14),
(2, 3, 15),
(2, 3, 16),
(2, 3, 17),
(2, 3, 18),
(2, 3, 19),
(2, 3, 21),
(2, 3, 22),
(2, 3, 23),
(2, 4, 1),
(2, 4, 2),
(2, 4, 3),
(2, 4, 4),
(2, 4, 5),
(2, 4, 6),
(2, 4, 7),
(2, 4, 8),
(2, 4, 9),
(2, 4, 10),
(2, 4, 11),
(2, 4, 12),
(2, 4, 13),
(2, 4, 14),
(2, 4, 15),
(2, 4, 16),
(2, 4, 17),
(2, 4, 18),
(2, 4, 19),
(2, 4, 21),
(2, 4, 22),
(2, 4, 23),
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
(68, 4, 10),
(69, 2, 1),
(69, 2, 2),
(69, 2, 3),
(69, 2, 4),
(69, 2, 5),
(69, 2, 6),
(69, 2, 7),
(69, 2, 8),
(69, 2, 9),
(69, 2, 10),
(69, 2, 11),
(69, 2, 12),
(69, 2, 13),
(69, 2, 14),
(69, 2, 15),
(69, 2, 16),
(69, 2, 17),
(69, 2, 18),
(69, 2, 19),
(69, 2, 21),
(69, 2, 22),
(69, 2, 23),
(70, 1, 1),
(70, 1, 2),
(70, 2, 1),
(70, 2, 2),
(70, 3, 1),
(70, 3, 2),
(70, 4, 1),
(70, 4, 2),
(71, 1, 19),
(71, 2, 19),
(71, 3, 19),
(71, 4, 19),
(72, 1, 21),
(72, 2, 21),
(72, 3, 21),
(72, 4, 21);

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

--
-- Volcado de datos para la tabla `bitacora`
--

INSERT INTO `bitacora` (`id_bitacora`, `fecha_hora`, `accion`, `usuario_id`, `modulo_id`, `valores_anteriores`, `valores_nuevos`) VALUES
(4045, '2026-05-07 22:34:00', 'CONSULTAR', 1, 1, '{}', '{}'),
(4046, '2026-05-07 22:34:10', 'CONSULTAR', 1, 1, '{}', '{}'),
(4047, '2026-05-07 22:38:05', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4048, '2026-05-09 22:24:54', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4049, '2026-05-09 22:33:15', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4050, '2026-05-09 22:34:18', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4051, '2026-05-09 22:35:27', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4052, '2026-05-09 22:40:11', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4053, '2026-05-09 22:41:28', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4054, '2026-05-09 22:42:07', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4055, '2026-05-09 22:47:08', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4056, '2026-05-09 22:52:40', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4057, '2026-05-09 22:53:44', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4058, '2026-05-09 23:01:12', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4059, '2026-05-09 23:01:58', 'CONSULTAR', 1, 5, '{}', '{}'),
(4060, '2026-05-09 23:03:25', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4061, '2026-05-09 23:14:22', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4062, '2026-05-09 23:16:13', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4063, '2026-05-09 23:18:30', 'CONSULTAR', 1, 5, '{}', '{}'),
(4064, '2026-05-09 23:20:59', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4065, '2026-05-09 23:26:16', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4066, '2026-05-09 23:34:21', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4067, '2026-05-09 23:40:26', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4068, '2026-05-09 23:45:23', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4069, '2026-05-09 23:58:23', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4070, '2026-05-10 00:10:10', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4071, '2026-05-10 00:11:31', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4072, '2026-05-10 00:19:47', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4073, '2026-05-10 00:24:13', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4074, '2026-05-10 00:25:16', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4075, '2026-05-10 00:28:06', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4076, '2026-05-10 00:28:34', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4077, '2026-05-10 00:33:36', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4078, '2026-05-10 00:34:30', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4079, '2026-05-10 00:36:50', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4080, '2026-05-10 00:39:08', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4081, '2026-05-10 00:44:26', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4082, '2026-05-10 01:00:14', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4083, '2026-05-10 01:03:06', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4084, '2026-05-10 01:05:34', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4085, '2026-05-10 01:06:30', 'CONSULTAR', 1, 5, '{}', '{}'),
(4086, '2026-05-10 01:07:21', 'CONSULTAR', 1, 5, '{}', '{}'),
(4087, '2026-05-10 01:07:36', 'CONSULTAR', 1, 5, '{}', '{}'),
(4088, '2026-05-10 01:08:29', 'CONSULTAR', 1, 5, '{}', '{}'),
(4089, '2026-05-10 01:12:56', 'CONSULTAR', 1, 5, '{}', '{}'),
(4090, '2026-05-10 01:13:57', 'CONSULTAR', 1, 5, '{}', '{}'),
(4091, '2026-05-11 22:20:04', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4092, '2026-05-11 22:20:44', 'CONSULTAR', 1, 1, '{}', '{}'),
(4093, '2026-05-11 22:29:02', 'CONSULTAR', 1, 1, '{}', '{}'),
(4094, '2026-05-11 22:29:21', 'CONSULTAR', 1, 1, '{}', '{}'),
(4095, '2026-05-11 22:29:37', 'CONSULTAR', 1, 19, '{}', '{}'),
(4096, '2026-05-11 23:03:37', 'CONSULTAR', 1, 1, '{}', '{}'),
(4097, '2026-05-11 23:03:41', 'CONSULTAR', 1, 15, '{}', '{}'),
(4098, '2026-05-11 23:04:35', 'CONSULTAR', 1, 7, '{}', '{}'),
(4099, '2026-05-12 10:56:24', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4100, '2026-05-12 10:58:03', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4101, '2026-05-12 10:58:24', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4102, '2026-05-12 11:03:23', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4103, '2026-05-12 11:05:16', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4104, '2026-05-12 11:12:09', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4105, '2026-05-12 11:22:16', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4106, '2026-05-12 11:46:09', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4107, '2026-05-12 13:01:00', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4108, '2026-05-12 13:01:07', 'CONSULTAR', 1, 9, '{}', '{}'),
(4109, '2026-05-12 13:01:34', 'CONSULTAR', 1, 9, '{}', '{}'),
(4110, '2026-05-12 13:48:34', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4111, '2026-05-12 13:48:39', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4112, '2026-05-12 18:05:27', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4113, '2026-05-12 18:05:37', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4114, '2026-05-12 18:05:46', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4115, '2026-05-12 18:05:52', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4116, '2026-05-12 18:45:54', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4117, '2026-05-12 20:09:19', 'CONSULTAR', 1, 9, '{}', '{}'),
(4118, '2026-05-12 20:13:31', 'CONSULTAR', 1, 9, '{}', '{}'),
(4119, '2026-05-12 20:17:06', 'CONSULTAR', 1, 9, '{}', '{}'),
(4120, '2026-05-12 20:25:24', 'CONSULTAR', 1, 6, '{}', '{}'),
(4121, '2026-05-12 20:25:28', 'CONSULTAR', 1, 15, '{}', '{}'),
(4122, '2026-05-13 00:58:11', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4123, '2026-05-13 00:58:21', 'CONSULTAR', 1, 9, '{}', '{}'),
(4124, '2026-05-13 00:59:27', 'REGISTRAR', 1, 9, '{}', '{}'),
(4125, '2026-05-13 00:59:27', 'CONSULTAR', 1, 9, '{}', '{}'),
(4126, '2026-05-13 01:04:34', 'ELIMINAR', 1, 9, '{\"estado\":\"ABIERTO\",\"fecha_inicio\":\"2026-05-13\",\"fecha_cierre\":\"2027-05-13\",\"descripcion\":\"hola\"}', '{}'),
(4127, '2026-05-13 01:04:34', 'CONSULTAR', 1, 9, '{}', '{}'),
(4128, '2026-05-13 01:09:27', 'CONSULTAR', 1, 8, '{}', '{}'),
(4129, '2026-05-13 01:09:52', 'CONSULTAR', 1, 8, '{}', '{}'),
(4130, '2026-05-13 01:25:03', 'CONSULTAR', 1, 19, '{}', '{}'),
(4131, '2026-05-13 01:27:54', 'CONSULTAR', 1, 19, '{}', '{}'),
(4132, '2026-05-13 01:30:53', 'CONSULTAR', 1, 19, '{}', '{}'),
(4133, '2026-05-13 01:31:30', 'CONSULTAR', 1, 14, '{}', '{}'),
(4134, '2026-05-13 01:31:34', 'CONSULTAR', 1, 6, '{}', '{}'),
(4135, '2026-05-13 01:31:41', 'CONSULTAR', 1, 6, '{}', '{}'),
(4136, '2026-05-13 01:32:20', 'CONSULTAR', 1, 6, '{}', '{}'),
(4137, '2026-05-13 01:32:29', 'CONSULTAR', 1, 1, '{}', '{}'),
(4138, '2026-05-13 01:32:34', 'CONSULTAR', 1, 2, '{}', '{}'),
(4139, '2026-05-13 01:32:39', 'CONSULTAR', 1, 4, '{}', '{}'),
(4140, '2026-05-13 01:32:49', 'CONSULTAR', 1, 3, '{}', '{}'),
(4141, '2026-05-13 01:32:56', 'CONSULTAR', 1, 5, '{}', '{}'),
(4142, '2026-05-13 01:33:18', 'CONSULTAR', 1, 5, '{}', '{}'),
(4143, '2026-05-13 01:33:28', 'CONSULTAR', 1, 3, '{}', '{}'),
(4144, '2026-05-13 01:33:41', 'CONSULTAR', 1, 7, '{}', '{}'),
(4145, '2026-05-13 11:17:05', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4146, '2026-05-13 11:18:46', 'CONSULTAR', 1, 2, '{}', '{}'),
(4147, '2026-05-13 18:44:31', 'CONSULTAR', 1, 4, '{}', '{}'),
(4148, '2026-05-13 18:44:37', 'CONSULTAR', 1, 3, '{}', '{}'),
(4149, '2026-05-13 18:45:45', 'CONSULTAR', 1, 14, '{}', '{}'),
(4150, '2026-05-13 18:46:38', 'CONSULTAR', 1, 2, '{}', '{}'),
(4151, '2026-05-13 18:48:45', 'CONSULTAR', 1, 2, '{}', '{}'),
(4152, '2026-05-13 18:51:14', 'CONSULTAR', 1, 9, '{}', '{}'),
(4153, '2026-05-13 18:51:17', 'CONSULTAR', 1, 3, '{}', '{}'),
(4154, '2026-05-13 18:59:03', 'CONSULTAR', 1, 4, '{}', '{}'),
(4155, '2026-05-13 18:59:06', 'CERRAR SESION', 1, 14, '{}', '{}'),
(4156, '2026-05-13 18:59:11', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4157, '2026-05-14 11:54:43', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4158, '2026-05-14 12:41:58', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4159, '2026-05-14 12:45:46', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4160, '2026-05-14 12:51:00', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4161, '2026-05-14 13:00:35', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4162, '2026-05-14 13:02:59', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4163, '2026-05-15 16:26:13', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4164, '2026-05-15 16:34:27', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4165, '2026-05-15 17:23:21', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4166, '2026-05-16 10:44:31', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4167, '2026-05-16 10:45:07', 'CONSULTAR', 1, 1, '{}', '{}'),
(4168, '2026-05-16 10:48:02', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PROCESADO\",\"observacion\":\"HOLA\"}'),
(4169, '2026-05-16 10:57:32', 'REGISTRAR', 1, 1, '{}', '{\"estado\":\"PROCESADO\",\"observacion\":\"pago de febrero\"}'),
(4170, '2026-05-16 10:58:28', 'CONSULTAR', 1, 2, '{}', '{}'),
(4171, '2026-05-16 11:01:07', 'REGISTRAR', 1, 2, '{}', '{\"clasificacion\":\"Fijo\",\"descripcion_gasto\":\"pago de descrio\"}'),
(4172, '2026-05-16 11:06:00', 'CONSULTAR', 1, 4, '{}', '{}'),
(4173, '2026-05-16 11:06:41', 'CONSULTAR', 1, 4, '{}', '{}'),
(4174, '2026-05-16 11:11:39', 'CONSULTAR', 1, 4, '{}', '{}'),
(4175, '2026-05-16 11:11:56', 'CONSULTAR', 1, 4, '{}', '{}'),
(4176, '2026-05-16 11:12:30', 'CONSULTAR', 1, 4, '{}', '{}'),
(4177, '2026-05-16 11:13:18', 'CONSULTAR', 1, 4, '{}', '{}'),
(4178, '2026-05-16 11:14:23', 'REGISTRAR', 1, 4, '{}', '{\"concepto\":\"compramos cafe\",\"monto_movimiento\":\"10\",\"fecha_movimiento\":\"2026-05-16\"}'),
(4179, '2026-05-16 11:16:34', 'CONSULTAR', 1, 4, '{}', '{}'),
(4180, '2026-05-16 11:16:40', 'MODIFICAR', 1, 4, '{}', '{\"monto_movimiento\":\"11\",\"fecha_movimiento\":\"2026-05-16\"}'),
(4181, '2026-05-16 11:20:35', 'MODIFICAR', 1, 4, '{}', '{\"monto_movimiento\":\"11.00\",\"fecha_movimiento\":\"2026-05-16\"}'),
(4182, '2026-05-16 12:05:13', 'CONSULTAR', 1, 3, '{}', '{}'),
(4183, '2026-05-16 12:05:57', 'MODIFICAR', 1, 3, '{\"tasa_dolar\":\"455.25\"}', '{\"tasa_dolar\":\"515.18\"}'),
(4184, '2026-05-16 12:06:17', 'CONSULTAR', 1, 4, '{}', '{}'),
(4185, '2026-05-16 12:06:34', 'CONSULTAR', 1, 5, '{}', '{}'),
(4186, '2026-05-16 12:10:46', 'CONSULTAR', 1, 7, '{}', '{}'),
(4187, '2026-05-16 12:12:00', 'MODIFICAR', 1, 7, '{\"monto_estimado\":12}', '{\"monto_estimado\":\"13\"}'),
(4188, '2026-05-16 12:12:03', 'CONSULTAR', 1, 8, '{}', '{}'),
(4189, '2026-05-16 12:12:13', 'MODIFICAR', 1, 8, '{}', '{\"tasa_dolar\":\"515.18\"}'),
(4190, '2026-05-16 12:12:19', 'CONSULTAR', 1, 9, '{}', '{}'),
(4191, '2026-05-16 12:12:31', 'REGISTRAR', 1, 9, '{}', '{\"fecha_inicio\":\"2026-05-16\",\"fecha_cierre\":\"2027-05-16\",\"estado\":\"ABIERTO\",\"descripcion\":\"ooo\"}'),
(4192, '2026-05-16 12:12:41', 'ELIMINAR', 1, 9, '{\"estado\":\"ABIERTO\",\"fecha_inicio\":\"2026-05-16\",\"fecha_cierre\":\"2027-05-16\",\"descripcion\":\"ooo\"}', '{}'),
(4193, '2026-05-16 12:12:46', 'CONSULTAR', 1, 15, '{}', '{}'),
(4194, '2026-05-16 12:12:56', 'CONSULTAR', 1, 15, '{}', '{}'),
(4195, '2026-05-16 12:20:48', 'CONSULTAR', 1, 15, '{}', '{}'),
(4196, '2026-05-16 12:24:33', 'CONSULTAR', 1, 15, '{}', '{}'),
(4197, '2026-05-16 12:25:32', 'CONSULTAR', 1, 15, '{}', '{}'),
(4198, '2026-05-16 12:25:50', 'CONSULTAR', 1, 15, '{}', '{}'),
(4199, '2026-05-16 12:29:02', 'CONSULTAR', 1, 15, '{}', '{}'),
(4200, '2026-05-16 12:29:12', 'DESCARGAR', 1, 15, '{}', '{\"tipo_reporte\":\"Constancia de Residencia\",\"habitante\":\"jesus asdasda\"}'),
(4201, '2026-05-16 12:29:27', 'CONSULTAR', 1, 15, '{}', '{}'),
(4202, '2026-05-16 12:31:08', 'CONSULTAR', 1, 15, '{}', '{}'),
(4203, '2026-05-16 12:34:11', 'CONSULTAR', 1, 15, '{}', '{}'),
(4204, '2026-05-16 12:58:30', 'CONSULTAR', 1, 15, '{}', '{}'),
(4205, '2026-05-16 13:01:42', 'CONSULTAR', 1, 17, '{}', '{}'),
(4206, '2026-05-16 13:01:50', 'CONSULTAR', 1, 19, '{}', '{}'),
(4207, '2026-05-16 13:02:00', 'RESPALDAR', 1, 19, '{}', '{\"accion\":\"Generó copia de seguridad\",\"base_datos\":\"NEGOCIO\"}'),
(4208, '2026-05-16 14:21:57', 'CONSULTAR', 1, 14, '{}', '{}'),
(4209, '2026-05-16 14:22:52', 'REGISTRAR', 1, 14, '{}', '{\"apellido\":\"test\",\"nombre\":\"test\",\"correo\":\"tests@gmail.com\",\"contra\":\"12345\"}'),
(4210, '2026-05-16 14:23:58', 'CONSULTAR', 1, 14, '{}', '{}'),
(4211, '2026-05-16 14:24:02', 'ELIMINAR', 1, 14, '{\"nombre\":\"test\",\"apellido\":\"test\",\"correo\":\"tests@gmail.com\",\"contrasenia\":\"$2y$10$apVDsr\\/j3IR3lOovWTAgje3Czb4e1mmPXBAedAzQnkvGD1PVkF9ia\",\"nombre_rol\":\"Contador\"}', '{}'),
(4212, '2026-05-16 14:27:23', 'CONSULTAR', 1, 17, '{}', '{}'),
(4213, '2026-05-16 14:28:03', 'CONSULTAR', 1, 17, '{}', '{}'),
(4214, '2026-05-16 14:28:32', 'CONSULTAR', 1, 23, '{}', '{}'),
(4215, '2026-05-16 14:28:36', 'CONSULTAR', 1, 22, '{}', '{}'),
(4216, '2026-05-16 14:28:43', 'CONSULTAR', 1, 12, '{}', '{}'),
(4217, '2026-05-16 14:28:49', 'CONSULTAR', 1, 11, '{}', '{}'),
(4218, '2026-05-16 14:28:52', 'CONSULTAR', 1, 13, '{}', '{}'),
(4219, '2026-05-16 18:51:39', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4220, '2026-05-16 18:53:33', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4221, '2026-05-16 19:02:37', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4222, '2026-05-16 19:21:37', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4223, '2026-05-16 19:21:45', 'CONSULTAR', 1, 1, '{}', '{}'),
(4224, '2026-05-16 19:43:58', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4225, '2026-05-16 22:26:00', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4226, '2026-05-16 22:34:19', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4227, '2026-05-16 22:35:38', 'INICIAR SESION', 1, 14, '{}', '{}'),
(4228, '2026-05-16 22:36:26', 'CONSULTAR', 1, 5, '{}', '{}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cartelera_virtual`
--

CREATE TABLE `cartelera_virtual` (
  `id_cartelera` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `imagen` varchar(100) DEFAULT NULL,
  `prioridad` varchar(10) NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cartelera_virtual`
--

INSERT INTO `cartelera_virtual` (`id_cartelera`, `titulo`, `descripcion`, `fecha`, `imagen`, `prioridad`, `usuario_id`) VALUES
(19, 'Bienvenidos', 'bienvenidos al 2026', '2100-10-10 00:00:00', 'err_1774720041_174.PNG', '3', 1),
(23, 'aaaaaa', 'asdasdasd', '2026-03-05 00:00:00', 'Captura__2__1774720054_938.PNG', '3', 1),
(27, 'aaaaaaaaaaa', 'fbbbbbbbbbbbbbbbbbbbb', '2010-10-10 00:00:00', 'ref_tarjeta_1774719930_739.PNG', '2', 1),
(28, 'pepe tapo el escusado', 'pepe tapo el escusado mis pana', '2026-03-28 12:48:36', 'meme_1774720065_811.PNG', '1', 1),
(29, 'adasdasdas', 'adasdasdasd', '2026-03-28 13:42:59', 'fake_new_1774719779_771.PNG', '2', 1),
(30, 'hola pana', 'nueva publicacione', '2026-04-07 19:16:57', 'Captura__2__1775603817_147.PNG', '1', 1),
(31, 'hola', 'publicacion 2', '2026-04-07 19:22:54', 'ejemplo2_1775604174_147.PNG', '2', 1),
(32, 'sfasdasd', 'sadasdas', '2026-04-07 19:25:16', 'ref_tarjeta_1775604316_210.PNG', '1', 1),
(33, 'asdasd', 'asdasd', '2026-04-07 19:25:54', '', '2', 1),
(34, 'adasdas', 'asdasd', '2026-04-07 19:26:08', '', '3', 1),
(35, 'hola pepe ', 'hola pepe como estas', '2026-04-12 16:16:00', 'fake_new_1776024960_274.PNG', '2', 1),
(36, 'Publicacion react', 'Esta es una publicación desde react native', '2026-04-25 15:38:02', '9a8d16b0-577f-4f27-ab0d-6284568289de_1777145882_484.jpeg', '1', 1),
(37, 'Hola', 'Habló desde react', '2026-04-25 15:50:27', 'ae247589-02a4-4b81-bafc-7909594ad77b_1777146627_726.jpeg', '3', 1),
(38, 'Se dañó un botón ', 'Mano el botón de la luna se dañó hay que arreglarlo ', '2026-04-30 10:48:52', '6fd97767-f745-4e47-b660-27993ddf5cd8_1777560532_605.png', '1', 1),
(39, 'Ya casi', 'Ya casi', '2026-04-30 11:50:54', 'f44e1ac5-7768-4f6c-944f-1989807ebe7b_1777564254_220.png', '2', 1),
(40, 'Hola', 'Hola buenas ', '2026-05-10 01:07:08', '', '1', 1),
(41, 'Hola buenas ', 'Hola buenas ', '2026-05-10 01:07:28', '', '1', 1),
(42, 'Hola mi amor ', 'Gkdksksslsmsmd', '2026-05-10 01:08:24', '', '2', 1),
(43, 'Hola mi vida ', 'Jdjdkdkddd', '2026-05-10 01:13:49', '40823269-8e2c-44d1-b355-97052ec4ea17_1778390029_619.png', '2', 1),
(44, 'Hola', 'Soy un mensaje encriptado ', '2026-05-12 11:01:12', '459de706-6428-4814-91cc-ad224bb37a03_1778598072_120.jpeg', '2', 1),
(45, 'Publicacion', 'Publicacion genérica ', '2026-05-12 11:30:42', '9ba0bd5b-db27-4a64-84ca-0ed4b0c8db60_1778599842_730.png', '3', 1),
(46, 'Hola ', 'Hola ora vez ', '2026-05-14 12:53:10', '88b272cc-4918-466a-ac05-a16893278e10_1778777590_510.jpeg', '3', 1),
(47, 'Hola ', 'Hola chamo', '2026-05-14 13:03:54', 'aadd8944-bbab-4b0a-bccb-1c29e86ac835_1778778234_743.jpeg', '2', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `claves_sesion`
--

CREATE TABLE `claves_sesion` (
  `dispositivo_id` varchar(255) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `clave_aes` text NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `ultima_actividad` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `claves_sesion`
--

INSERT INTO `claves_sesion` (`dispositivo_id`, `usuario_id`, `clave_aes`, `fecha_creacion`, `ultima_actividad`) VALUES
('3bb214f0-fa54-4ddb-8fdb-556b5ebfb7c1', 1, 'Yp2nGSuokh42AICVxcxIazlLx2/740lM3BO3JC+w2SA=', '2026-05-09 22:33:15', '2026-05-16 22:34:19');

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intentos_login`
--

CREATE TABLE `intentos_login` (
  `usuario_id` int(11) NOT NULL,
  `intentos` int(11) NOT NULL DEFAULT 1,
  `ultimo_intento` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `listas_acceso_ip`
--

CREATE TABLE `listas_acceso_ip` (
  `id_lista` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `tipo_lista` enum('BLANCA','NEGRA') NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `fecha_agregado` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion_evento`
--

CREATE TABLE `notificacion_evento` (
  `notificacion_id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'REGISTRAR', 1),
(2, 'CONSULTAR', 1),
(3, 'MODIFICAR', 1),
(4, 'ELIMINAR', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_ips`
--

CREATE TABLE `registro_ips` (
  `ip` varchar(45) NOT NULL,
  `intentos` int(11) NOT NULL DEFAULT 1,
  `ultimo_intento` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(2, 'Administrador', 1),
(3, 'Propietario', 1),
(4, 'Contador', 1),
(23, 'Presidente', 1),
(68, 'new', 0),
(69, 'rebelde', 1),
(70, 'ssssa', 0),
(71, 'gasasdasdas', 0),
(72, 'asasdasd', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `suscripciones_push`
--

CREATE TABLE `suscripciones_push` (
  `id_suscripcion` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `endpoint` text NOT NULL,
  `p256dh` varchar(255) NOT NULL,
  `auth` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `suscripciones_push`
--

INSERT INTO `suscripciones_push` (`id_suscripcion`, `usuario_id`, `endpoint`, `p256dh`, `auth`, `fecha_registro`) VALUES
(1, 1, 'https://updates.push.services.mozilla.com/wpush/v2/gAAAAABp1ZseTDQjnU5lI9AUO3eTuNHUP_qj8r1xvlJZs3h9TYmjAETOu3DuVzspQCdWsiH1pXGvZE-ofcigSRPYAYj1JhNSe2Dzmp4MSjmvDKvZQeYV00zfaXuyZ-WNy0madX2qSkqrBRjsB_UrzgsBcc-h7MAdL41G3cQrc5fY6i7HsVaJD7k', 'BPSoziQjJfxaG05/wBlvkgZ+XmfRZI/7Wnjw48WyE3qDOKayqeoGLQYihJM7KtCUDASM4Rv43HTvx7CSINbphkE=', 'dVIKY/GHisXcOM5xCM90ww==', '2026-04-07 22:45:44');

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
(103, 39, 'e15f6aff21b73ab42fc6482539e29d6fc05b7cd61044999e70760f1c44372acc', '2026-04-15 20:07:09', 'RECUPERAR_CONTRASENIA'),
(138, 39, '28bebc8f28ac57691fb3f9f88f3555778b7125dc1933632c1ed0b1abcf3efed9', '2026-05-31 19:18:37', 'RECORDAR_CONTRASENIA');

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
(1, 'Jesus', 'Escalona', 'administrador@gmail.com', '$2y$10$PSQuQ6JSX.UcGDlV8w4oauDJkL9o7d06f7AFZc4QCH9xAd2VSHA/G', 1, 1),
(2, 'francisco', 'mendoza', 'franj@gmail.com', '$2y$10$0KoHFVefo2ZZPv/nh0ocaefcDxfbOKXxcVhnUj844WynuyGhWpaV.', 4, 1),
(27, 'Pepes', 'Campos', 'pepe@gmail.com', '$2y$10$WWp8M1SADJzTWAg910K.mewfZFglQF77ENnqPYLmq1U9AKmmeruY2', 3, 1),
(39, 'Yhsius', 'asdasd', 'jesusgescalonae@gmail.com', '$2y$10$NRSnjmozFiGc7GP7Hc61..Z9OazXjr8B3KvNhEmslcCk1zDeVmjRy', 2, 1),
(53, 'perfil editado', 'perfil editado', 'UsuarioperfilEditada@gmail.com', '$2y$10$AzKv19h61AeAkEYPA/FSA.buvyhYKoRfHT/kUFgMDSWE11PKjpBLS', 4, 0),
(54, 'usuario', 'cambiocontra', 'cambiocontrasenia@gmail.com', '$2y$10$soYFxka95IzptEPe5eA.IONdFJI/geOcpt0K/L7aAKNIsTyn.5Nd2', 23, 0),
(89, 'pepe', 'puias', 'pepa@gmail.com', '$2y$10$GtV9.reiR/8A/NindSEEUOtCPjAs.lLxS67Qp9ZNtG6Ug3wzd5nXi', 1, 0),
(90, 'asdasd', 'asdasd', 'asdas@ad.com', '$2y$10$tTc2q0y3G8sZ8glF3vyDiO.b3DqLtm/TJ/HTJl8QZM2pforDC0TdO', 1, 0),
(91, 'test', 'test', 'test@gmail.com', '$2y$10$M/b/1lwlXVk7g0bHNfBiIOZ1XM0OQIB6SU5GPG2SL0JnhTQvD4Dqu', 69, 1),
(92, 'presi', 'presi', 'presi@gmail.com', '$2y$10$mE8PllA/a3G4ecc.UIZi4u9771m5QBZ3vk/7jWRutkQooM5rD1ryG', 23, 1),
(93, 'test', 'test', 'tests@gmail.com', '$2y$10$apVDsr/j3IR3lOovWTAgje3Czb4e1mmPXBAedAzQnkvGD1PVkF9ia', 4, 0);

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
-- Indices de la tabla `claves_sesion`
--
ALTER TABLE `claves_sesion`
  ADD PRIMARY KEY (`dispositivo_id`),
  ADD KEY `idx_usuario_sesion` (`usuario_id`);

--
-- Indices de la tabla `eventos_sistema`
--
ALTER TABLE `eventos_sistema`
  ADD PRIMARY KEY (`id_evento`);

--
-- Indices de la tabla `intentos_login`
--
ALTER TABLE `intentos_login`
  ADD PRIMARY KEY (`usuario_id`);

--
-- Indices de la tabla `listas_acceso_ip`
--
ALTER TABLE `listas_acceso_ip`
  ADD PRIMARY KEY (`id_lista`),
  ADD UNIQUE KEY `idx_ip_unica` (`ip`);

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
-- Indices de la tabla `registro_ips`
--
ALTER TABLE `registro_ips`
  ADD PRIMARY KEY (`ip`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `suscripciones_push`
--
ALTER TABLE `suscripciones_push`
  ADD PRIMARY KEY (`id_suscripcion`),
  ADD KEY `suscripciones_push_ibfk_1` (`usuario_id`);

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
  MODIFY `id_bitacora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4229;

--
-- AUTO_INCREMENT de la tabla `cartelera_virtual`
--
ALTER TABLE `cartelera_virtual`
  MODIFY `id_cartelera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `eventos_sistema`
--
ALTER TABLE `eventos_sistema`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `listas_acceso_ip`
--
ALTER TABLE `listas_acceso_ip`
  MODIFY `id_lista` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id_modulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=239;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de la tabla `suscripciones_push`
--
ALTER TABLE `suscripciones_push`
  MODIFY `id_suscripcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tokens_seguridad`
--
ALTER TABLE `tokens_seguridad`
  MODIFY `id_token` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

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
-- Filtros para la tabla `claves_sesion`
--
ALTER TABLE `claves_sesion`
  ADD CONSTRAINT `fk_claves_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `intentos_login`
--
ALTER TABLE `intentos_login`
  ADD CONSTRAINT `intentos_login_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Filtros para la tabla `suscripciones_push`
--
ALTER TABLE `suscripciones_push`
  ADD CONSTRAINT `suscripciones_push_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

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
