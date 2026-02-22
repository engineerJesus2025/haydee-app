-- Script para crear el usuario app_condominio con los permisos necesarios
-- Ajusta el host según el entorno: 'localhost' para local, '%' para cualquier host o una IP específica
SET @host = 'localhost';  -- Cambiar según entorno: 'localhost', '%', '192.168.1.100', etc.

-- Crear el usuario si no existe, usando el hash de la contraseña actual (obtenido de SHOW GRANTS)
-- El hash es: *B6ED59A321FC70AFB3D2FAB21490C032A08DFEA7
CREATE USER IF NOT EXISTS 'app_condominio'@'localhost' IDENTIFIED BY PASSWORD '*B6ED59A321FC70AFB3D2FAB21490C032A08DFEA7';

-- Otorgar permisos sobre haydee_db
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, LOCK TABLES, 
      EXECUTE, CREATE VIEW, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EVENT, TRIGGER 
ON `haydee_db`.* TO 'app_condominio'@'localhost';

-- Otorgar permisos sobre seguridad_haydee_db
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, LOCK TABLES, 
      EXECUTE, SHOW VIEW, TRIGGER, CREATE ROUTINE, ALTER ROUTINE, EVENT 
ON `seguridad_haydee_db`.* TO 'app_condominio'@'localhost';

-- Permiso para leer la tabla mysql.proc (necesario para ver procedimientos)
GRANT SELECT ON `mysql`.`proc` TO 'app_condominio'@'localhost';

-- Aplicar cambios
FLUSH PRIVILEGES;