-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-11-2026 a las 01:21:01
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET FOREIGN_KEY_CHECKS=0;
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
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_token` (IN `p_usuario_id` INT(11), IN `p_tipo` VARCHAR(30), IN `p_token` TEXT, IN `p_fecha_expiracion` DATETIME)   BEGIN
    
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; 
    END;

    
    START TRANSACTION;

    
    
    DELETE FROM tokens_seguridad 
    WHERE usuario_id = p_usuario_id AND fecha_expiracion < NOW();

    
    
    
    DELETE FROM tokens_seguridad 
    WHERE usuario_id = p_usuario_id AND tipo = p_tipo;

    
    INSERT INTO tokens_seguridad (
        usuario_id, 
        tipo, 
        token, 
        fecha_expiracion
    ) VALUES (
        p_usuario_id, 
        p_tipo, 
        p_token, 
        p_fecha_expiracion
    );

    
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_notificar_administradores` (IN `p_tit` VARCHAR(100), IN `p_desc` TEXT, IN `p_tabla` VARCHAR(50), IN `p_id_reg` INT, IN `p_tipo` VARCHAR(50))   BEGIN
    DECLARE v_evento_id INT;
    DECLARE v_admin_id INT;
    DECLARE v_notif_id INT;
    DECLARE v_done INT DEFAULT FALSE;
    
    
    DECLARE cur_admins CURSOR FOR 
        SELECT id_usuario FROM usuarios WHERE rol_id IN (1, 2) AND activo = 1;
        
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = TRUE;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;

    
    INSERT INTO eventos_sistema (tipo_evento, tabla_origen, id_registro_origen, fecha_evento)
    VALUES (p_tipo, p_tabla, p_id_reg, NOW());
    
    SET v_evento_id = LAST_INSERT_ID();

    
    OPEN cur_admins;
    
    read_loop: LOOP
        FETCH cur_admins INTO v_admin_id;
        IF v_done THEN
            LEAVE read_loop;
        END IF;

        
        INSERT INTO notificaciones (titulo, descripcion, fecha, leido, usuario_id)
        VALUES (p_tit, p_desc, NOW(), 0, v_admin_id);
        
        SET v_notif_id = LAST_INSERT_ID();

        
        INSERT INTO notificacion_evento (notificacion_id, evento_id)
        VALUES (v_notif_id, v_evento_id);
        
    END LOOP;
    
    CLOSE cur_admins;

    COMMIT;
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
(1, 1, 26),
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
(1, 2, 26),
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
(1, 3, 26),
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
(1, 4, 26),
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
(2, 1, 26),
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
(2, 2, 26),
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
(2, 3, 26),
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
(2, 4, 26),
(3, 1, 1),
(3, 2, 1),
(3, 2, 5),
(3, 3, 1),
(3, 4, 1),
(4, 1, 1),
(4, 1, 2),
(4, 1, 3),
(4, 1, 4),
(4, 1, 5),
(4, 1, 15),
(4, 1, 26),
(4, 2, 1),
(4, 2, 2),
(4, 2, 3),
(4, 2, 4),
(4, 2, 5),
(4, 2, 15),
(4, 2, 26),
(4, 3, 1),
(4, 3, 2),
(4, 3, 3),
(4, 3, 4),
(4, 3, 5),
(4, 3, 15),
(4, 3, 26),
(4, 4, 1),
(4, 4, 2),
(4, 4, 3),
(4, 4, 4),
(4, 4, 5),
(4, 4, 15),
(4, 4, 26),
(5, 2, 1),
(5, 2, 2),
(5, 2, 3),
(5, 2, 4),
(5, 2, 5),
(5, 2, 6),
(5, 2, 7),
(5, 2, 8),
(5, 2, 9),
(5, 2, 10),
(5, 2, 11),
(5, 2, 12),
(5, 2, 13),
(5, 2, 14),
(5, 2, 15),
(5, 2, 16),
(5, 2, 17),
(5, 2, 18),
(5, 2, 19),
(5, 2, 21),
(5, 2, 22),
(5, 2, 23),
(5, 2, 26);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id_bitacora` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `accion` varchar(20) NOT NULL,
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
  `descripcion` text NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `imagen` varchar(100) DEFAULT NULL,
  `prioridad` varchar(10) NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cartelera_virtual`
--

INSERT INTO `cartelera_virtual` (`id_cartelera`, `titulo`, `descripcion`, `fecha`, `imagen`, `prioridad`, `usuario_id`) VALUES
(1, 'Generación de mensualidad septiembre 2025', 'Se informa a todos los propietarios que la mensualidad correspondiente a septiembre 2025 ya fue generada. Puede consultarla en su estado de cuenta. Fecha limite de pago: 30 de septiembre.', '2025-09-01 08:00:00', 'cuadro-gastos_1792937602_689.jpg', '1', 3),
(2, 'Generación de mensualidad agosto 2025', 'La mensualidad de agosto 2025 ha sido generada. Le recordamos revisar su estado de cuenta y realizar el pago correspondiente dentro del plazo establecido.', '2025-08-01 08:00:00', 'cuadro_pagos1_1792937958_460.PNG', '1', 3),
(3, 'Recordatorio de pago del condominio', 'Estimado propietario, le recordamos que aun tiene pendiente el pago de la mensualidad del mes en curso. Evite recargos y regularice su situación a la brevedad.', '2025-09-15 09:00:00', 'recordatorio_pago1_1792936072_192.webp', '2', 3),
(4, 'Cierre del ejercicio fiscal 2024', 'Se informa que el ejercicio fiscal 2024 ha sido cerrado satisfactoriamente. Agradecemos a los propietarios por mantener sus cuentas al día durante el periodo.', '2025-01-15 10:30:00', 'cierre_fiscal1_1792936240_322.webp', '2', 3),
(5, 'Corte de agua programado', 'Hidrolara ha notificado un corte programado del servicio para el miércoles 25 de septiembre, de 6:00 am a 4:00 pm. Se recomienda almacenar agua con anticipación.', '2025-09-22 14:15:00', 'corte_agua1_1792937417_180.jpg', '1', 2),
(6, 'Fumigación mensual de áreas comunes', 'La fumigación de áreas comunes se realizara el jueves 25 de septiembre a las 9:00 am. Mantenga puertas y ventanas cerradas durante el proceso.', '2025-09-20 11:45:00', 'fumigacion1_1792937209_638.jpg', '2', 2),
(7, 'Trabajos de pintura en pasillos', 'Durante la próxima semana se realizaran trabajos de pintura en los pasillos de la planta baja. Evite transitar por las áreas en trabajo. Gracias por su comprensión.', '2025-09-15 16:00:00', 'pintar_apartamento1_1792937015_280.jpg', '2', 2),
(8, 'Nueva trabajadora residencial', 'Damos la bienvenida a la nueva trabajadora residencial del edificio, quien inicia labores a partir del 1 de septiembre. Cualquier reporte de aseo puede dirigirse a la administración.', '2025-08-28 15:30:00', 'nueva_empleada1_1792936883_877.jpg', '3', 2),
(9, 'Campaña de reciclaje', 'Invitamos a todos los residentes a participar en la campaña de reciclaje. Los contenedores de plástico, papel y vidrio están disponibles en el área del estacionamiento.', '2025-08-15 10:00:00', 'reciclaje1_1792936810_783.jpg', '3', 2),
(10, 'Mantenimiento del ascensor', 'Se informa que el ascensor estará fuera de servicio el sábado 27 de septiembre, de 8:00 am a 12:00 pm, por mantenimiento preventivo programado. Agradecemos su comprensión.', '2025-09-25 09:30:00', 'mantenimiento_ascensor1_1792937557_521.png', '1', 1),
(11, 'Asamblea de propietarios septiembre', 'Se convoca a todos los propietarios a la asamblea ordinaria del mes de septiembre, el viernes 26 a las 7:00 pm en mi apartamento. Se trataran temas de presupuesto y cuotas.', '2025-09-23 18:00:00', 'asamblea1_1792937488_393.jpg', '1', 1),
(12, 'Jornada de limpieza general', 'Se realizara una jornada de limpieza general de áreas comunes el sábado 10 de mayo de 8:00 am a 12:00 pm. Se solicita la colaboración de todos los residentes.', '2025-05-05 09:00:00', 'limpieza_areas_verdes1_1792936635_402.jpg', '2', 1),
(13, 'Felicitaciones a los padres', 'La junta de condominio felicita a todos los padres del edificio en su día. Extendemos nuestro reconocimiento a su labor diaria y les deseamos un excelente día.', '2025-06-15 12:00:00', 'dia_padre1_1792936697_348.jpg', '3', 1),
(14, 'Actualización de datos de contacto', 'Se solicita a todos los propietarios actualizar sus datos de contacto (teléfono y correo) en la administración para mantener una comunicación efectiva sobre el condominio.', '2025-04-10 11:00:00', 'cambair_dato_contacto1_1792936374_489.png', '3', 1);

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
  `motivo` text DEFAULT NULL,
  `fecha_agregado` datetime NOT NULL DEFAULT current_timestamp(),
  `reincidencias` int(11) NOT NULL DEFAULT 1,
  `fecha_expiracion` datetime DEFAULT NULL
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
(23, 'GESTIONAR_MODULOS', 1),
(26, 'GESTIONAR_CUENTAS', 1);

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
  `ultimo_intento` datetime NOT NULL DEFAULT current_timestamp(),
  `infracciones` int(11) NOT NULL DEFAULT 0
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
(5, 'Presidente', 1);

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `suscripciones_push_movil`
--

CREATE TABLE `suscripciones_push_movil` (
  `id_suscripcion_movil` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `expo_token` varchar(255) NOT NULL,
  `plataforma` enum('ANDROID','IOS') NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tokens_seguridad`
--

CREATE TABLE `tokens_seguridad` (
  `id_token` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` text NOT NULL,
  `fecha_expiracion` datetime NOT NULL,
  `tipo` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trafico_usuarios`
--

CREATE TABLE `trafico_usuarios` (
  `usuario_id` int(11) NOT NULL,
  `peticiones` int(11) NOT NULL DEFAULT 1,
  `ultimo_intento` datetime NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasenia` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `ayuda_general_visto` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasenia`, `rol_id`, `activo`, `ayuda_general_visto`) VALUES
(1, 'Jesus', 'Escalona', 'administrador@gmail.com', '$2y$10$Lb.ms5xRTopsTVniVYEPr.gNybtKcXfWl2rs.e9poNhIyMVMWQsLe', 1, 1, 1),
(2, 'Francisco', 'Mendoza', 'franj@gmail.com', '$2y$10$sZUkN8amsuzoxQs2uhp.A.7ycF0kB.No0U6sCwjjctyfRXSQ6XuyK', 2, 1, 0),
(3, 'Rafael', 'Gutiérrez', 'rafita@gmail.com', '$2y$10$jElEajmaBzqLIlqxOQyPAeSBv6n0YVCoKY/TfJ2XZK5QBjEQM/bSC', 4, 1, 1),
(4, 'Edecio', 'Freitez', 'edecio@gmail.com', '$2y$10$lTRyQb7GQcJC48thzyt3a.qdHKR3d6VbX6GYNj4RV8vBtg6RAQIce', 5, 1, 0),
(5, 'Maria', 'Lopez', 'maria.lopez1@example.com', '$2y$10$Sq4JzaMkRcsu2P7yp7inIe826hywjHrwaIvGULmJKGTiHHBSmIKqy', 3, 1, 1),
(6, 'Jose', 'Garcia', 'jose.garcia2@example.com', '$2y$10$qUrVvlX7ZZ3s.udMw.8zdePTy04kPgQcWJVZzJG3B64D.Eo.2Fg2S', 3, 1, 0),
(7, 'Ana', 'Martinez', 'ana.martinez3@example.com', '$2y$10$87mi.ta/IcJ/8HtZuFq6fOnBACEl.TI5.9vKaAiyS09EOsO7piKq6', 3, 1, 0),
(8, 'Carlos', 'Rodriguez', 'carlos.rodriguez4@example.com', '$2y$10$0FQ/mLGFef/stiILmDbsduOHODIyKRlZd/BeF/Vo3VNyCUpLLynKi', 3, 1, 0),
(9, 'Elena', 'Perez', 'elena.perez5@example.com', '$2y$10$RDeAnsLwrnXrmWiM5oVULed.8Ck5qNhv1lLdD/XdaDuRxaqCFDAPC', 3, 1, 0),
(10, 'Luis', 'Gonzalez', 'luis.gonzalez6@example.com', '$2y$10$1P6ViOk4o8RM4udB4Sd4tOOMGYmzYwSYT0Mj6qN98QZnjRuLShKs2', 3, 1, 0),
(11, 'Carmen', 'Hernandez', 'carmen.hernandez7@example.com', '$2y$10$yBTTI1AY16XQD0b8TI6P4.1/dA9o2NlNQzoFFKEkcdJ1J4qM9yez2', 3, 1, 0),
(12, 'Rafael', 'Diaz', 'rafael.diaz8@example.com', '$2y$10$LSov5uVKM.xrJ7xuv7T1Ye1dxo49nkLyb5fB3YmaPHFCkEpl4/klq', 3, 1, 0),
(13, 'Sofia', 'Torres', 'sofia.torres9@example.com', '$2y$10$IuvMNMQ9ecIqWIiDZX6IrOFkBqTWbBJSR2CVH5kqKPLTs4x2.kKxm', 3, 1, 0),
(14, 'Miguel', 'Ramirez', 'miguel.ramirez10@example.com', '$2y$10$s2kMbG8GNlJ7w68LU.QpQ.6tx2zXkphgYKiwNVqxv7OTQvGi1TLHm', 3, 1, 0),
(15, 'Daniela', 'Flores', 'daniela.flores11@example.com', '$2y$10$vhqpVu.I7s9ihGgaUnmKteBHJXP0VkdLN56XWyfe1Cm7RZjHkPrkm', 3, 1, 0),
(16, 'Andres', 'Rivero', 'andres.rivero12@example.com', '$2y$10$dgSSjOmgW8tqmm.jueTY8OgmXQ4yftH8A5U714AyuFvrZwl52gKqe', 3, 1, 0),
(17, 'Paola', 'Mendoza', 'paola.mendoza13@example.com', '$2y$10$2p7.FEogjN0hy76A9yecQ.fnmHs3SPOAIikimBq2Bnp0gE4/tjEti', 3, 1, 0),
(18, 'Fernando', 'Castillo', 'fernando.castillo14@example.com', '$2y$10$MQIXdhK7l90Gh2h6Az/uc.g64HnHPIS9S7NIcEPSIDxil0B2toB7y', 3, 1, 0),
(19, 'Gabriela', 'Rojas', 'gabriela.rojas15@example.com', '$2y$10$d3cBCNrOcA2ERkWxlfKAg.ZiSs02.04D5BUfdMgtWjBqWGJj7c9GO', 3, 1, 0),
(20, 'Ricardo', 'Vargas', 'ricardo.vargas16@example.com', '$2y$10$QySCwq/DCrT1HRnJAbCr3OXRah8dQOkTXTLj.4F2S88GqVWToPWXa', 3, 1, 0),
(21, 'Valentina', 'Castro', 'valentina.castro17@example.com', '$2y$10$pcbHo0k3wt8ZUicoDOn8b.b/3OEqnhNdvLo6RzqlVSKrqdr4mp6JC', 3, 1, 0),
(22, 'Alberto', 'Ortiz', 'alberto.ortiz18@example.com', '$2y$10$Z3oSNqh6QVBjtw25eZY.bOKhqkne4hDQsqAlqA1chgbNRGXpPyaVq', 3, 1, 0),
(23, 'Beatriz', 'Silva', 'beatriz.silva19@example.com', '$2y$10$dqu1kXykp1MR4Ugid2zuwOKW5MN09ckkpxV4iZQ7aJ3zm62v0mRAa', 3, 1, 0);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_perfiles_usuarios`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_perfiles_usuarios` (
`id_usuario` int(11)
,`nombre` varchar(50)
,`apellido` varchar(50)
,`correo` varchar(100)
,`contrasenia` varchar(255)
,`activo` tinyint(1)
,`rol_id` int(11)
,`nombre_rol` varchar(50)
,`ayuda` tinyint(1)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_perfiles_usuarios`
--
DROP TABLE IF EXISTS `vw_perfiles_usuarios`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_perfiles_usuarios`  AS SELECT `u`.`id_usuario` AS `id_usuario`, `u`.`nombre` AS `nombre`, `u`.`apellido` AS `apellido`, `u`.`correo` AS `correo`, `u`.`contrasenia` AS `contrasenia`, `u`.`activo` AS `activo`, `u`.`rol_id` AS `rol_id`, `r`.`nombre` AS `nombre_rol`, `u`.`ayuda_general_visto` AS `ayuda` FROM (`usuarios` `u` join `roles` `r` on(`u`.`rol_id` = `r`.`id_rol`)) ;

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
-- Indices de la tabla `suscripciones_push_movil`
--
ALTER TABLE `suscripciones_push_movil`
  ADD PRIMARY KEY (`id_suscripcion_movil`),
  ADD UNIQUE KEY `expo_token_unico` (`expo_token`),
  ADD KEY `fk_suscripcion_movil_usuario` (`usuario_id`);

--
-- Indices de la tabla `tokens_seguridad`
--
ALTER TABLE `tokens_seguridad`
  ADD PRIMARY KEY (`id_token`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `trafico_usuarios`
--
ALTER TABLE `trafico_usuarios`
  ADD PRIMARY KEY (`usuario_id`);

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
  MODIFY `id_cartelera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `eventos_sistema`
--
ALTER TABLE `eventos_sistema`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `listas_acceso_ip`
--
ALTER TABLE `listas_acceso_ip`
  MODIFY `id_lista` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id_modulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de la tabla `suscripciones_push`
--
ALTER TABLE `suscripciones_push`
  MODIFY `id_suscripcion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `suscripciones_push_movil`
--
ALTER TABLE `suscripciones_push_movil`
  MODIFY `id_suscripcion_movil` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tokens_seguridad`
--
ALTER TABLE `tokens_seguridad`
  MODIFY `id_token` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

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
-- Filtros para la tabla `suscripciones_push_movil`
--
ALTER TABLE `suscripciones_push_movil`
  ADD CONSTRAINT `fk_suscripcion_movil_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

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
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
