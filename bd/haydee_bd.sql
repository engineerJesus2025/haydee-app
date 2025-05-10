
-- =========================
-- CREACIÓN DE BASE DE DATOS DE SEGURIDAD
-- =========================
CREATE DATABASE IF NOT EXISTS seguridad_haydee_db;
USE seguridad_haydee_db;

CREATE TABLE roles (
    id_rol INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL
);

INSERT INTO `roles` (`id_rol`, `nombre`) VALUES
(1, 'administrador'),
(2, 'propietario'),
(3, 'contador'),
(4, 'presidente');

CREATE TABLE modulos (
    id_modulo INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL
);

INSERT INTO `modulos` (`id_modulo`, `nombre`) VALUES
(1, 'GESTIONAR_PAGOS'),
(2, 'GESTIONAR_GASTOS'),
(3, 'GESTIONAR_EFECTIVO'),
(4, 'GESTIONAR_CONCILIACION_BANCARIA'),
(5, 'GESTIONAR_CARTELERA_VIRTUAL'),
(6, 'GESTIONAR_HABITANTES'),
(7, 'GESTIONAR_PROPIETARIOS'),
(8, 'GESTIONAR_CONFIGURACION'),
(9, 'GESTIONAR_PROVEEDORES'),
(10, 'GESTIONAR_APARTAMENTOS '),
(11, 'GESTIONAR_MENSUALIDAD'),
(12, 'GESTIONAR_USUARIOS'),
(13, 'GESTIONAR_REPORTES'),
(14, 'GESTIONAR_SEGURIDAD'),
(15, 'GESTIONAR_ROLES'),
(16, 'GESTIONAR_BITACORA'),
(17, 'GESTIONAR_BANCOS');

CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(20) NOT NULL,
    apellido VARCHAR(20) NOT NULL,
    correo VARCHAR(50) NOT NULL UNIQUE,
    contrasenia VARCHAR(255) NOT NULL,
    rol_id INT,
    FOREIGN KEY (rol_id) REFERENCES roles(id_rol)
);

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasenia`, `rol_id`) VALUES
(1, 'jesus', 'escalona', 'administrador@gmail.com', '$2y$10$jZHeKafL95Er5S.w6DFk1u5nObHXn2qJrmLJJnWmbFcheqvAhUS8S', 1),
(2, 'randonsa', 'aleatorio', 'randon@gmasil.com', '$2y$10$j7vZ1Gk9RcdoxJg3e3hiOOvbam.T3Emdv22t25hPTS99aL3zjAvA6', 2),
(27, 'Esz', 'Rogger', 'roger@gmail.com', '$2y$10$3y5Pt91X.Niw7KD2JSpcO.WGubTQ9Nu/d1YMnlPy7revzkBGnd0IC', 2);

CREATE TABLE permisos_usuarios (
    id_permiso_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre_accion VARCHAR(50),
    modulo_id INT,
    FOREIGN KEY (modulo_id) REFERENCES modulos(id_modulo)
);

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
(54, 'consultar', 14),
(57, 'registrar', 15),
(58, 'consultar', 15),
(59, 'modificar', 15),
(60, 'eliminar', 15),
(61, 'consultar', 16),
(62, 'registrar', 17),
(63, 'consultar', 17),
(64, 'modificar', 17),
(65, 'eliminar', 17);


CREATE TABLE roles_permisos (
    id_rol_permiso INT PRIMARY KEY AUTO_INCREMENT,
    rol_id INT,
    permiso_usuario_id INT,
    FOREIGN KEY (rol_id) REFERENCES roles(id_rol),
    FOREIGN KEY (permiso_usuario_id) REFERENCES permisos_usuarios(id_permiso_usuario)
);

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
(151, 1, 54),
(152, 1, 57),
(153, 1, 58),
(154, 1, 59),
(155, 1, 60),
(156, 1, 61),
(157, 1, 62),
(158, 1, 63),
(159, 1, 64),
(160, 1, 65),
(161, 4, 63);


CREATE TABLE bitacora (
    id_bitacora INT PRIMARY KEY AUTO_INCREMENT,
    fecha_hora DATETIME NOT NULL,
    accion VARCHAR(100),
    usuario_id INT,
    modulo_id INT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (modulo_id) REFERENCES modulos(id_modulo)
);

CREATE TABLE notificaciones (
    id_notificacion INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(100),
    descripcion TEXT,
    fecha DATE,
    usuario_id INT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario)
);

-- =========================
-- CREACIÓN DE BASE DE DATOS DE CONDOMINIO HAYDEE
-- =========================
CREATE DATABASE IF NOT EXISTS haydee_db;
USE haydee_db;

CREATE TABLE propietarios (
    id_propietario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(20) NOT NULL,
    apellido VARCHAR(20) NOT NULL,
    cedula VARCHAR(20) NOT NULL,
    telefono VARCHAR(15),
    correo VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE bancos (
  id_banco INT PRIMARY KEY AUTO_INCREMENT,
  nombre_banco VARCHAR(50) NOT NULL,
  codigo INT(7) NOT NULL UNIQUE,
  numero_cuenta VARCHAR(50) NOT NULL,
  telefono_afiliado VARCHAR(50) DEFAULT NULL,
  cedula_afiliada VARCHAR(20) DEFAULT NULL
);

CREATE TABLE apartamentos (
    id_apartamento INT PRIMARY KEY AUTO_INCREMENT,
    nro_apartamento VARCHAR(3) NOT NULL,
    porcentaje_participacion FLOAT NOT NULL,
    gas BOOLEAN,
    agua BOOLEAN,
    alquilado BOOLEAN,
    propietario_id INT,
    FOREIGN KEY (propietario_id) REFERENCES propietarios(id_propietario)
);

CREATE TABLE habitantes (
    id_habitante INT PRIMARY KEY AUTO_INCREMENT,
    cedula VARCHAR(15) NOT NULL UNIQUE,
    nombre_habitante VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    fecha_nacimiento DATE,
    sexo VARCHAR(10),
    telefono VARCHAR(15),
    apartamento_id INT,
    FOREIGN KEY (apartamento_id) REFERENCES apartamentos(id_apartamento)
);

CREATE TABLE mensualidad (
    id_mensualidad INT PRIMARY KEY AUTO_INCREMENT,
    monto FLOAT NOT NULL,
    tasa_dolar FLOAT,
    mes VARCHAR(2) NOT NULL,
    anio VARCHAR(4) NOT NULL,
    apartamento_id INT,
    FOREIGN KEY (apartamento_id) REFERENCES apartamentos(id_apartamento)
);

CREATE TABLE caja_chica (
    id_caja INT PRIMARY KEY AUTO_INCREMENT,
    fecha_inicio DATE,
    fecha_fin DATE,
    saldo_inicial FLOAT,
    saldo_final FLOAT,
    saldo_sistema FLOAT,
    diferencia FLOAT,
    tasa_dolar FLOAT,
    estado VARCHAR(20),
    observaciones VARCHAR(255)
);

CREATE TABLE cuentas_cobrar (
    id_cuenta_cobrar INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE,
    monto FLOAT
);

CREATE TABLE proveedores (
    id_proveedor INT PRIMARY KEY AUTO_INCREMENT,
    nombre_proveedor VARCHAR(100),
    servicio VARCHAR(100),
    rif VARCHAR(20),
    direccion TEXT
);

CREATE TABLE pagos (
    id_pago INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE NOT NULL,
    monto FLOAT NOT NULL,
    tasa_dolar FLOAT,
    estado VARCHAR(20),
    metodo_pago VARCHAR(20),
    banco_id INT,
    referencia VARCHAR(50),
    imagen VARCHAR(255),
    caja_id INT,
    cuenta_cobrar_id INT,
    observacion TEXT,
    FOREIGN KEY (caja_id) REFERENCES caja_chica(id_caja),
    FOREIGN KEY (cuenta_cobrar_id) REFERENCES cuentas_cobrar(id_cuenta_cobrar),
    FOREIGN KEY (banco_id) REFERENCES bancos(id_banco)
);

CREATE TABLE pagos_mensualidad (
    id_pago_mensualidad INT PRIMARY KEY AUTO_INCREMENT,
    pago_id INT,
    mensualidad_id INT,
    FOREIGN KEY (pago_id) REFERENCES pagos(id_pago),
    FOREIGN KEY (mensualidad_id) REFERENCES mensualidad(id_mensualidad)
);

CREATE TABLE conciliacion_bancaria (
    id_conciliacion INT PRIMARY KEY AUTO_INCREMENT,
    fecha_inicio DATE,
    fecha_fin DATE,
    estado VARCHAR(20),
    banco VARCHAR(50),
    saldo_inicio FLOAT,
    saldo_fin FLOAT,
    saldo_sistema FLOAT,
    diferencia FLOAT,
    tasa_dolar FLOAT,
    ingreso_banco_no_correspondido FLOAT,
    ingreso_sistema_no_correspondido FLOAT,
    egreso_banco_no_correspondido FLOAT,
    egreso_sistema_no_correspondido FLOAT,
    observaciones VARCHAR(255)
);

CREATE TABLE gastos_mes (
    id_gasto_mes INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE,
    monto FLOAT,
    mensualidad_id INT,
    FOREIGN KEY (mensualidad_id) REFERENCES mensualidad(id_mensualidad)
);

CREATE TABLE gastos (
    id_gasto INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE,
    monto FLOAT,
    tipo_gasto VARCHAR(50),
    tasa_dolar FLOAT,
    gastos_mes_id INT,
    metodo_pago VARCHAR(20),
    banco_id INT,
    referencia VARCHAR(50),
    imagen VARCHAR(255),
    caja_id INT,
    proveedor_id INT,
    descripcion_gasto TEXT,
    FOREIGN KEY (caja_id) REFERENCES caja_chica(id_caja),
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id_proveedor),
    FOREIGN KEY (gastos_mes_id) REFERENCES gastos_mes(id_gasto_mes),
    FOREIGN KEY (banco_id) REFERENCES bancos(id_banco)
);

CREATE TABLE movimientos_bancarios (
    id_movimiento INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE,
    monto FLOAT,
    referencia VARCHAR(50),
    tipo_movimiento VARCHAR(20),
    estado VARCHAR(20),
    monto_diferencia FLOAT,
    tipo_diferencia VARCHAR(50),
    conciliacion_id INT,
    pago_id INT,
    gasto_id INT,
    FOREIGN KEY (conciliacion_id) REFERENCES conciliacion_bancaria(id_conciliacion),
    FOREIGN KEY (pago_id) REFERENCES pagos(id_pago),
    FOREIGN KEY (gasto_id) REFERENCES gastos(id_gasto)
);


CREATE TABLE cartelera_virtual (
    id_cartelera INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(100),
    descripcion TEXT,
    fecha DATE,
    tipo VARCHAR(50),
    imagen VARCHAR(255)
);

CREATE TABLE apartamento_cartelera (
    id_apartamento_cartelera INT PRIMARY KEY AUTO_INCREMENT,
    apartamento_id INT,
    cartelera_id INT,
    FOREIGN KEY (apartamento_id) REFERENCES apartamentos(id_apartamento),
    FOREIGN KEY (cartelera_id) REFERENCES cartelera_virtual(id_cartelera)
);


