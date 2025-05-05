
-- =========================
-- CREACIÓN DE BASE DE DATOS DE SEGURIDAD
-- =========================
CREATE DATABASE IF NOT EXISTS seguridad_haydee_db;
USE seguridad_haydee_db;

CREATE TABLE roles (
    id_rol INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL
);

CREATE TABLE modulos (
    id_modulo INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL
);

CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(20) NOT NULL,
    apellido VARCHAR(20) NOT NULL,
    correo VARCHAR(50) NOT NULL UNIQUE,
    contrasenia VARCHAR(255) NOT NULL,
    rol_id INT,
    FOREIGN KEY (rol_id) REFERENCES roles(id_rol)
);

CREATE TABLE permisos_usuarios (
    id_permiso_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre_accion VARCHAR(50),
    modulo_id INT,
    FOREIGN KEY (modulo_id) REFERENCES modulos(id_modulo)
);

CREATE TABLE roles_permisos (
    id_rol_permiso INT PRIMARY KEY AUTO_INCREMENT,
    rol_id INT,
    permiso_usuario_id INT,
    FOREIGN KEY (rol_id) REFERENCES roles(id_rol),
    FOREIGN KEY (permiso_usuario_id) REFERENCES permisos_usuarios(id_permiso_usuario)
);

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
    banco VARCHAR(50),
    referencia VARCHAR(50),
    imagen VARCHAR(255),
    caja_id INT,
    cuenta_cobrar_id INT,
    observacion TEXT,
    FOREIGN KEY (caja_id) REFERENCES caja_chica(id_caja),
    FOREIGN KEY (cuenta_cobrar_id) REFERENCES cuentas_cobrar(id_cuenta_cobrar)
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
    banco VARCHAR(50),
    referencia VARCHAR(50),
    imagen VARCHAR(255),
    caja_id INT,
    proveedor_id INT,
    descripcion_gasto TEXT,
    FOREIGN KEY (caja_id) REFERENCES caja_chica(id_caja),
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id_proveedor),
    FOREIGN KEY (gastos_mes_id) REFERENCES gastos_mes(id_gasto_mes)
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


