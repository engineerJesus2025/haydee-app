INSERT INTO `apartamentos`
(`id_apartamento`, `nro_apartamento`, `porcentaje_participacion`, `gas`, `agua`, `alquilado`, `activo`)
VALUES
(1,  '1-1', 5.25, 1, 1, 0, 1),
(2,  '1-2', 5.25, 0, 1, 0, 1),
(3,  '2-1', 5.25, 0, 1, 0, 1),
(4,  '2-2', 5.25, 0, 1, 0, 1),
(5,  '3-1', 5.25, 1, 1, 0, 1),
(6,  '3-2', 5.25, 1, 1, 0, 1),
(7,  '4-1', 5.25, 1, 1, 0, 1),
(8,  '4-2', 5.25, 1, 1, 0, 1),
(9,  '5-1', 5.25, 1, 1, 0, 1),
(10, '5-2', 5.25, 1, 1, 0, 1),
(11, '6-1', 5.25, 1, 1, 0, 1),
(12, '6-2', 5.25, 1, 1, 0, 1),
(13, '7-1', 5.25, 0, 1, 0, 1),
(14, '7-2', 5.25, 1, 1, 0, 1),
(15, '8-1', 5.25, 0, 1, 0, 1),
(16, '8-2', 5.25, 0, 1, 0, 1),
(17, '9-1', 5.25, 1, 1, 0, 1),
(18, '9-2', 5.25, 0, 1, 0, 1),
(19, 'PH',  5.50, 0, 1, 0, 1);


START TRANSACTION;

INSERT INTO `habitantes`
(`id_habitante`, `nombre`, `apellido`, `cedula`, `telefono`, `correo`, `fecha_nacimiento`, `sexo`, `activo`)
VALUES
(1,  'Maria',     'Lopez',      'V10000001', '04140000001', 'maria.lopez1@example.com',       '1980-05-12', 'F',  1),
(2,  'Jose',      'Garcia',     'V10000002', '04140000002', 'jose.garcia2@example.com',       '1975-08-23', 'M', 1),
(3,  'Ana',       'Martinez',   'V10000003', '04140000003', 'ana.martinez3@example.com',      '1988-11-03', 'F',  1),
(4,  'Carlos',    'Rodriguez',  'V10000004', '04140000004', 'carlos.rodriguez4@example.com',  '1972-02-17', 'M', 1),
(5,  'Elena',     'Perez',      'V10000005', '04140000005', 'elena.perez5@example.com',       '1990-07-09', 'F',  1),
(6,  'Luis',      'Gonzalez',   'V10000006', '04140000006', 'luis.gonzalez6@example.com',     '1983-04-28', 'M', 1),
(7,  'Carmen',    'Hernandez',  'V10000007', '04140000007', 'carmen.hernandez7@example.com',  '1969-12-15', 'F',  1),
(8,  'Rafael',    'Diaz',       'V10000008', '04140000008', 'rafael.diaz8@example.com',       '1978-09-21', 'M', 1),
(9,  'Sofia',     'Torres',     'V10000009', '04140000009', 'sofia.torres9@example.com',      '1992-03-30', 'F',  1),
(10, 'Miguel',    'Ramirez',    'V10000010', '04140000010', 'miguel.ramirez10@example.com',   '1985-06-11', 'M', 1),
(11, 'Daniela',   'Flores',     'V10000011', '04140000011', 'daniela.flores11@example.com',   '1994-10-05', 'F',  1),
(12, 'Andres',    'Rivero',     'V10000012', '04140000012', 'andres.rivero12@example.com',    '1981-01-19', 'M', 1),
(13, 'Paola',     'Mendoza',    'V10000013', '04140000013', 'paola.mendoza13@example.com',    '1987-08-14', 'F',  1),
(14, 'Fernando',  'Castillo',   'V10000014', '04140000014', 'fernando.castillo14@example.com','1976-11-27', 'M', 1),
(15, 'Gabriela',  'Rojas',      'V10000015', '04140000015', 'gabriela.rojas15@example.com',   '1991-05-08', 'F',  1),
(16, 'Ricardo',   'Vargas',     'V10000016', '04140000016', 'ricardo.vargas16@example.com',   '1974-03-02', 'M', 1),
(17, 'Valentina', 'Castro',     'V10000017', '04140000017', 'valentina.castro17@example.com', '1996-12-22', 'F',  1),
(18, 'Alberto',   'Ortiz',      'V10000018', '04140000018', 'alberto.ortiz18@example.com',    '1982-07-07', 'M', 1),
(19, 'Beatriz',   'Silva',      'V10000019', '04140000019', 'beatriz.silva19@example.com',    '1979-09-16', 'F',  1),

-- Familiares adicionales
(20, 'Manuel',    'Lopez',      'V10000020', '04140000020', 'manuel.lopez20@example.com',     '2005-04-18', 'M', 1),
(21, 'Claudia',   'Garcia',     'V10000021', '04140000021', 'claudia.garcia21@example.com',   '1977-06-25', 'F',  1),
(22, 'Diego',     'Martinez',   'V10000022', '04140000022', 'diego.martinez22@example.com',   '2010-01-12', 'M', 1),
(23, 'Patricia',  'Rodriguez',  'V10000023', '04140000023', 'patricia.rodriguez23@example.com','1975-10-31','F',  1),
(24, 'Oscar',     'Perez',      'V10000024', '04140000024', 'oscar.perez24@example.com',      '2015-02-08', 'M', 1),
(25, 'Laura',     'Gonzalez',   'V10000025', '04140000025', 'laura.gonzalez25@example.com',   '1986-09-19', 'F',  1),
(26, 'Javier',    'Hernandez',  'V10000026', '04140000026', 'javier.hernandez26@example.com', '2001-11-11', 'M', 1),
(27, 'Natalia',   'Diaz',       'V10000027', '04140000027', 'natalia.diaz27@example.com',     '2008-07-04', 'F',  1);

INSERT INTO `habitantes_apartamentos`
(`apartamento_id`, `habitante_id`, `tipo_vinculo`)
VALUES
-- Propietarios: uno por cada apartamento
(1,  1,  'PROPIETARIO'),
(2,  2,  'PROPIETARIO'),
(3,  3,  'PROPIETARIO'),
(4,  4,  'PROPIETARIO'),
(5,  5,  'PROPIETARIO'),
(6,  6,  'PROPIETARIO'),
(7,  7,  'PROPIETARIO'),
(8,  8,  'PROPIETARIO'),
(9,  9,  'PROPIETARIO'),
(10, 10, 'PROPIETARIO'),
(11, 11, 'PROPIETARIO'),
(12, 12, 'PROPIETARIO'),
(13, 13, 'PROPIETARIO'),
(14, 14, 'PROPIETARIO'),
(15, 15, 'PROPIETARIO'),
(16, 16, 'PROPIETARIO'),
(17, 17, 'PROPIETARIO'),
(18, 18, 'PROPIETARIO'),
(19, 19, 'PROPIETARIO'),

-- Familiares adicionales
(1,  20, 'FAMILIAR'),
(2,  21, 'FAMILIAR'),
(3,  22, 'FAMILIAR'),
(4,  23, 'FAMILIAR'),
(5,  24, 'FAMILIAR'),
(6,  25, 'FAMILIAR'),
(7,  26, 'FAMILIAR'),
(8,  27, 'FAMILIAR');

COMMIT;




INSERT INTO `bancos`
(`id_banco`, `nombre_banco`, `codigo`, `activo`)
VALUES
(1,  'Bancaribe',                   '0114', 1),
(2,  'Banco del Tesoro',            '0163', 1),
(3,  'Banco de Venezuela',          '0102', 1),
(4,  'Banesco',                     '0134', 1),
(5,  'Mercantil',                   '0105', 1),
(6,  'BBVA Provincial',             '0108', 1),
(7,  'Banco Nacional de Credito',   '0191', 1),
(8,  'Banco Exterior',              '0115', 1),
(9,  'Banco Bicentenario',          '0175', 1),
(10, 'Sofitasa',                    '0137', 1),
(11, 'Banplus',                     '0174', 1),
(12, 'Banco Activo',                '0171', 1),
(13, 'Banco Agricola de Venezuela', '0166', 1),
(14, 'Bancamiga',                   '0172', 1);



INSERT INTO `cuentas_condominio`
(`id_cuenta`, `banco_id`, `numero_cuenta`, `tipo_cuenta`, `telefono_afiliado`, `rif`, `activo`)
VALUES
(1, 1, '01140300063000253595', 'CORRIENTE', '04141234567', 'J305785457', 1);


START TRANSACTION;

INSERT INTO `tipo_gasto` (`id_tipo_gasto`, `nombre_tipo_gasto`, `activo`) VALUES
(1, 'Gastos Fijos',               1),
(2, 'Gastos Variables',           1),
(3, 'Reposición de Caja Chica',   1);

INSERT INTO `conceptos_gasto` (`id_concepto`, `nombre_concepto`, `tipo_gasto_id`, `activo`) VALUES
-- Fijos
(1,  'Sueldo Trabajadora Residencial', 1, 1),
(2,  'Bono de Alimentación',           1, 1),
(3,  'Bono de Ayuda',                  1, 1),
(4,  'Agua HIDROLARA',                 1, 1),
(5,  'Electricidad CORPOELEC',         1, 1),
(6,  'Comisiones Bancarias',           1, 1),
(7,  'Mantenimiento de Ascensor',      1, 1),

-- Variables
(8,  'Bolsas de Basura',               2, 1),
(9,  'Productos de Limpieza',          2, 1),
(10, 'Materiales de Restauración',     2, 1),
(11, 'Mano de Obra',                   2, 1),
(12, 'Servicio de Cerrajería',         2, 1),
(13, 'Bombillos y Luminarias',         2, 1),
(14, 'Ayudante Mecánico',              2, 1),
(15, 'Diferencia en Cambio',           2, 1),
(16, 'Seguridad Social',               2, 1),
(17, 'Gas LARA',                       2, 1),

-- Reposición
(18, 'Reposición de Caja Chica',       3, 1);

COMMIT;


START TRANSACTION;

INSERT INTO `proveedores`
(`id_proveedor`, `nombre_proveedor`, `servicio`, `rif`, `direccion`, `activo`)
VALUES
(1,  'HIDROLARA',
     'Servicio de Agua',
     'J305785357',
     'Av. Vargas con calle 25, Barquisimeto, Estado Lara',
     1),

(2,  'CORPOELEC',
     'Servicio de Electricidad',
     'G200091610',
     'Av. Libertador, Torre CORPOELEC, Caracas, Distrito Capital',
     1),

(3,  'Ascensores Miranda',
     'Mantenimiento de Ascensores',
     'J301234567',
     'Calle 20 entre carreras 19 y 20, Barquisimeto, Estado Lara',
     1),

(4,  'Distribuidora La Limpieza',
     'Productos de Limpieza',
     'J402345678',
     'Av. Venezuela, Local 5, Barquisimeto, Estado Lara',
     1),

(5,  'Inversiones Bolsas del Centro',
     'Bolsas de Basura',
     'J403456789',
     'Carrera 21 con calle 8, Barquisimeto, Estado Lara',
     1),

(6,  'Ferretería El Constructor',
     'Materiales de Construcción',
     'J404567890',
     'Av. Los Leones, Galpón 3, Barquisimeto, Estado Lara',
     1),

(7,  'Cerrajería Central',
     'Servicio de Cerrajería',
     'J405678901',
     'Calle 23 entre carreras 18 y 19, Barquisimeto, Estado Lara',
     1),

(8,  'Iluminación Total',
     'Bombillos y Luminarias',
     'J406789012',
     'Av. 20 con calle 9, Local 2, Barquisimeto, Estado Lara',
     1),

(9,  'Servicios Mecánicos Lara',
     'Mano de Obra',
     'J407890123',
     'Calle 15 con carrera 27, Barquisimeto, Estado Lara',
     1),

(10, 'Gases de Lara',
     'Suministro de Gas',
     'J408901234',
     'Zona Industrial I, Galpón 7, Barquisimeto, Estado Lara',
     1);

COMMIT;





START TRANSACTION;

-- ============================================================
-- 1) AÑOS FISCALES
-- ============================================================
INSERT INTO `anio_fiscal`
(`id_anio_fiscal`, `estado`, `fecha_inicio`, `fecha_cierre`, `descripcion`, `activo`)
VALUES
(1, 'CERRADO', '2023-01-01', '2023-12-31', 'Periodo fiscal 2023', 1),
(2, 'CERRADO', '2024-01-01', '2024-12-31', 'Periodo fiscal 2024', 1),
(3, 'ABIERTO', '2025-01-01', '2025-12-31', 'Periodo fiscal 2025', 1);


-- ============================================================
-- 2) CAJA CHICA (una por año fiscal)
-- ============================================================
INSERT INTO `caja_chica`
(`id_caja_chica`, `fondo_fijo`, `estado`, `descripcion`, `fecha_creacion`, `anio_fiscal_id`, `activo`)
VALUES
(1, 500.00,  'CERRADO', 'Fondo operativo enero 2023', '2023-01-15', 1, 1),
(2, 750.00,  'CERRADO', 'Fondo operativo enero 2024', '2024-01-15', 2, 1),
(3, 1000.00, 'ABIERTO', 'Fondo operativo enero 2025', '2025-01-15', 3, 1);


-- ============================================================
-- 3) MOVIMIENTOS 2023 (todos REPUESTO, año cerrado)
-- ============================================================
INSERT INTO `movimientos_caja`
(`id_movimiento_caja`, `concepto`, `monto`, `tasa_dolar`, `fecha`, `estado`, `caja_chica_id`, `activo`)
VALUES
(1, 'Compra de articulos de limpieza',   45.50, 1.00, '2023-02-10', 'REPUESTO', 1, 1),
(2, 'Papeleria y utiles de oficina',     32.00, 1.00, '2023-03-15', 'REPUESTO', 1, 1),
(3, 'Bombillos para pasillos',           28.75, 1.00, '2023-04-20', 'REPUESTO', 1, 1),
(4, 'Servicio de cafeteria reunion',     55.00, 1.00, '2023-06-05', 'REPUESTO', 1, 1),
(5, 'Materiales de ferreteria',          78.30, 1.00, '2023-08-12', 'REPUESTO', 1, 1),
(6, 'Transporte y fletes',               40.00, 1.00, '2023-09-18', 'REPUESTO', 1, 1),
(7, 'Articulos de aseo para baños',      60.00, 1.00, '2023-11-08', 'REPUESTO', 1, 1);


-- ============================================================
-- 4) MOVIMIENTOS 2024 (todos REPUESTO, año cerrado)
-- ============================================================
INSERT INTO `movimientos_caja`
(`id_movimiento_caja`, `concepto`, `monto`, `tasa_dolar`, `fecha`, `estado`, `caja_chica_id`, `activo`)
VALUES
(8,  'Productos de limpieza',             85.00, 1.00, '2024-02-05', 'REPUESTO', 2, 1),
(9,  'Reparacion menor de puerta',       120.00, 1.00, '2024-03-12', 'REPUESTO', 2, 1),
(10, 'Utiles de oficina',                 45.50, 1.00, '2024-04-22', 'REPUESTO', 2, 1),
(11, 'Pintura para areas comunes',        95.00, 1.00, '2024-05-30', 'REPUESTO', 2, 1),
(12, 'Cafeteria junta directiva',         70.00, 1.00, '2024-07-10', 'REPUESTO', 2, 1),
(13, 'Bombillos exteriores',              55.00, 1.00, '2024-08-15', 'REPUESTO', 2, 1),
(14, 'Materiales de plomeria menor',      88.00, 1.00, '2024-10-05', 'REPUESTO', 2, 1),
(15, 'Servicio de mensajeria',            35.00, 1.00, '2024-11-20', 'REPUESTO', 2, 1),
(16, 'Productos de aseo navidad',        110.00, 1.00, '2024-12-10', 'REPUESTO', 2, 1);


-- ============================================================
-- 5) MOVIMIENTOS 2025 (año en curso)
--    Enero–Agosto: REPUESTO  |  Septiembre: PENDIENTE
-- ============================================================
INSERT INTO `movimientos_caja`
(`id_movimiento_caja`, `concepto`, `monto`, `tasa_dolar`, `fecha`, `estado`, `caja_chica_id`, `activo`)
VALUES
(17, 'Productos de limpieza enero',       75.00, 36.50, '2025-01-15', 'REPUESTO',  3, 1),
(18, 'Papeleria administracion',          40.00, 38.20, '2025-02-10', 'REPUESTO',  3, 1),
(19, 'Reparacion de cerradura',          150.00, 40.00, '2025-03-05', 'REPUESTO',  3, 1),
(20, 'Bombillos LED pasillos',            90.00, 42.75, '2025-04-18', 'REPUESTO',  3, 1),
(21, 'Materiales de pintura',            200.00, 45.10, '2025-05-22', 'REPUESTO',  3, 1),
(22, 'Cafeteria asamblea vecinos',        65.00, 48.30, '2025-06-15', 'REPUESTO',  3, 1),
(23, 'Utiles de limpieza',                80.00, 50.00, '2025-07-20', 'REPUESTO',  3, 1),
(24, 'Reparacion menor de plomeria',     120.00, 52.40, '2025-08-10', 'REPUESTO',  3, 1),
(25, 'Bolsas de basura septiembre',       95.00, 54.00, '2025-09-05', 'PENDIENTE', 3, 1),
(26, 'Servicio de mensajeria',            30.00, 54.00, '2025-09-15', 'PENDIENTE', 3, 1),
(27, 'Insumos varios de oficina',         55.00, 54.00, '2025-09-25', 'PENDIENTE', 3, 1);

COMMIT;


-- BD seguridad


START TRANSACTION;

INSERT INTO `usuarios`
(`id_usuario`, `nombre`, `apellido`, `correo`, `contrasenia`, `rol_id`, `activo`, `ayuda_general_visto`)
VALUES
-- Propietarios de los 19 apartamentos (habitantes 1-19)
(5,  'Maria',     'Lopez',      'maria.lopez1@example.com',        'Haydee2025.', 3, 1, 0),  -- Hab 1  - Apto 1-1
(6,  'Jose',      'Garcia',     'jose.garcia2@example.com',        'Haydee2025.', 3, 1, 0),  -- Hab 2  - Apto 1-2
(7,  'Ana',       'Martinez',   'ana.martinez3@example.com',       'Haydee2025.', 3, 1, 0),  -- Hab 3  - Apto 2-1
(8,  'Carlos',    'Rodriguez',  'carlos.rodriguez4@example.com',   'Haydee2025.', 3, 1, 0),  -- Hab 4  - Apto 2-2
(9,  'Elena',     'Perez',      'elena.perez5@example.com',        'Haydee2025.', 3, 1, 0),  -- Hab 5  - Apto 3-1
(10, 'Luis',      'Gonzalez',   'luis.gonzalez6@example.com',      'Haydee2025.', 3, 1, 0),  -- Hab 6  - Apto 3-2
(11, 'Carmen',    'Hernandez',  'carmen.hernandez7@example.com',   'Haydee2025.', 3, 1, 0),  -- Hab 7  - Apto 4-1
(12, 'Rafael',    'Diaz',       'rafael.diaz8@example.com',        'Haydee2025.', 3, 1, 0),  -- Hab 8  - Apto 4-2
(13, 'Sofia',     'Torres',     'sofia.torres9@example.com',       'Haydee2025.', 3, 1, 0),  -- Hab 9  - Apto 5-1
(14, 'Miguel',    'Ramirez',    'miguel.ramirez10@example.com',    'Haydee2025.', 3, 1, 0),  -- Hab 10 - Apto 5-2
(15, 'Daniela',   'Flores',     'daniela.flores11@example.com',    'Haydee2025.', 3, 1, 0),  -- Hab 11 - Apto 6-1
(16, 'Andres',    'Rivero',     'andres.rivero12@example.com',     'Haydee2025.', 3, 1, 0),  -- Hab 12 - Apto 6-2
(17, 'Paola',     'Mendoza',    'paola.mendoza13@example.com',     'Haydee2025.', 3, 1, 0),  -- Hab 13 - Apto 7-1
(18, 'Fernando',  'Castillo',   'fernando.castillo14@example.com', 'Haydee2025.', 3, 1, 0),  -- Hab 14 - Apto 7-2
(19, 'Gabriela',  'Rojas',      'gabriela.rojas15@example.com',    'Haydee2025.', 3, 1, 0),  -- Hab 15 - Apto 8-1
(20, 'Ricardo',   'Vargas',     'ricardo.vargas16@example.com',    'Haydee2025.', 3, 1, 0),  -- Hab 16 - Apto 8-2
(21, 'Valentina', 'Castro',     'valentina.castro17@example.com',  'Haydee2025.', 3, 1, 0),  -- Hab 17 - Apto 9-1
(22, 'Alberto',   'Ortiz',      'alberto.ortiz18@example.com',     'Haydee2025.', 3, 1, 0),  -- Hab 18 - Apto 9-2
(23, 'Beatriz',   'Silva',      'beatriz.silva19@example.com',     'Haydee2025.', 3, 1, 0);  -- Hab 19 - Apto PH

COMMIT;




START TRANSACTION;

INSERT INTO `cartelera_virtual`
(`id_cartelera`, `titulo`, `descripcion`, `fecha`, `imagen`, `prioridad`, `usuario_id`)
VALUES
-- ============================================================
-- CONTADOR (usuario 3, rol 4) — Avisos financieros y mensualidades
-- ============================================================
(1, 'Generacion de mensualidad septiembre 2025',
    'Se informa a todos los propietarios que la mensualidad correspondiente a septiembre 2025 ya fue generada. Puede consultarla en su estado de cuenta. Fecha limite de pago: 30 de septiembre.',
    '2025-09-01 08:00:00', NULL, '1', 3),

(2, 'Generacion de mensualidad agosto 2025',
    'La mensualidad de agosto 2025 ha sido generada. Le recordamos revisar su estado de cuenta y realizar el pago correspondiente dentro del plazo establecido.',
    '2025-08-01 08:00:00', NULL, '1', 3),

(3, 'Recordatorio de pago del condominio',
    'Estimado propietario, le recordamos que aun tiene pendiente el pago de la mensualidad del mes en curso. Evite recargos y regularice su situacion a la brevedad.',
    '2025-09-15 09:00:00', NULL, '2', 3),

(4, 'Cierre del ejercicio fiscal 2024',
    'Se informa que el ejercicio fiscal 2024 ha sido cerrado satisfactoriamente. Agradecemos a los propietarios por mantener sus cuentas al dia durante el periodo.',
    '2025-01-15 10:30:00', NULL, '2', 3),

-- ============================================================
-- ADMINISTRADOR (usuario 2, rol 2) — Avisos operativos del dia a dia
-- ============================================================
(5, 'Corte de agua programado',
    'Hidrolara ha notificado un corte programado del servicio para el miercoles 25 de septiembre, de 6:00 am a 4:00 pm. Se recomienda almacenar agua con anticipacion.',
    '2025-09-22 14:15:00', NULL, '1', 2),

(6, 'Fumigacion mensual de areas comunes',
    'La fumigacion de areas comunes se realizara el jueves 25 de septiembre a las 9:00 am. Mantenga puertas y ventanas cerradas durante el proceso.',
    '2025-09-20 11:45:00', NULL, '2', 2),

(7, 'Trabajos de pintura en pasillos',
    'Durante la proxima semana se realizaran trabajos de pintura en los pasillos de la planta baja. Evite transitar por las areas en trabajo. Gracias por su comprension.',
    '2025-09-15 16:00:00', NULL, '2', 2),

(8, 'Nueva trabajadora residencial',
    'Damos la bienvenida a la nueva trabajadora residencial del edificio, quien inicia labores a partir del 1 de septiembre. Cualquier reporte de aseo puede dirigirse a la administracion.',
    '2025-08-28 15:30:00', NULL, '3', 2),

(9, 'Campana de reciclaje',
    'Invitamos a todos los residentes a participar en la campana de reciclaje. Los contenedores de plastico, papel y vidrio estan disponibles en el area del estacionamiento.',
    '2025-08-15 10:00:00', NULL, '3', 2),

-- ============================================================
-- ADMINISTRADOR GLOBAL (usuario 1, rol 1) — Avisos institucionales
-- ============================================================
(10, 'Mantenimiento del ascensor',
    'Se informa que el ascensor estara fuera de servicio el sabado 27 de septiembre, de 8:00 am a 12:00 pm, por mantenimiento preventivo programado. Agradecemos su comprension.',
    '2025-09-25 09:30:00', NULL, '1', 1),

(11, 'Asamblea de propietarios septiembre',
    'Se convoca a todos los propietarios a la asamblea ordinaria del mes de septiembre, el viernes 26 a las 7:00 pm en el salon comun. Se trataran temas de presupuesto y cuotas.',
    '2025-09-23 18:00:00', NULL, '1', 1),

(12, 'Jornada de limpieza general',
    'Se realizara una jornada de limpieza general de areas comunes el sabado 10 de mayo de 8:00 am a 12:00 pm. Se solicita la colaboracion de todos los residentes.',
    '2025-05-05 09:00:00', NULL, '2', 1),

(13, 'Felicitaciones a los padres',
    'La junta de condominio felicita a todos los padres del edificio en su dia. Extendemos nuestro reconocimiento a su labor diaria y les deseamos un excelente dia.',
    '2025-06-15 12:00:00', NULL, '3', 1),

(14, 'Actualizacion de datos de contacto',
    'Se solicita a todos los propietarios actualizar sus datos de contacto (telefono y correo) en la administracion para mantener una comunicacion efectiva sobre el condominio.',
    '2025-04-10 11:00:00', NULL, '3', 1);

COMMIT;
