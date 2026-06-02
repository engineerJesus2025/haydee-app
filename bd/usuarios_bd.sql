-- Script para crear el usuario app_condominio 
SET @host = 'localhost';

CREATE USER IF NOT EXISTS 'app_condominio'@'localhost' IDENTIFIED BY PASSWORD '*B6ED59A321FC70AFB3D2FAB21490C032A08DFEA7';

-- Permisos sobre la base de datos de negocio (Solo manipulación de datos)
GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE, SHOW VIEW 
ON `haydee_db`.* TO 'app_condominio'@'localhost';

-- Permisos sobre la base de datos de seguridad
GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE, SHOW VIEW 
ON `seguridad_haydee_db`.* TO 'app_condominio'@'localhost';

-- Aplicar cambios
FLUSH PRIVILEGES;