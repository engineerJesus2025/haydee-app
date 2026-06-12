-- ====================================================================
-- USUARIO PRINCIPAL DE LA APLICACIÓN (Operaciones Diarias)
-- ====================================================================
DROP USER IF EXISTS 'app_condominio'@'localhost';

CREATE USER 'app_condominio'@'localhost' 
IDENTIFIED BY 'haydee.2025'; 

GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE, SHOW VIEW 
ON `haydee_db`.* TO 'app_condominio'@'localhost';

GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE, SHOW VIEW 
ON `seguridad_haydee_db`.* TO 'app_condominio'@'localhost';


-- ====================================================================
-- USUARIO EXCLUSIVO DE RESPALDO (Mínimo Privilegio y BCP)
-- ====================================================================
DROP USER IF EXISTS 'app_respaldos'@'localhost';

CREATE USER 'app_respaldos'@'localhost' 
IDENTIFIED BY 'haydee_backup.2026';

-- Privilegios a nivel de base de datos para lectura y estructura
GRANT SELECT, LOCK TABLES, SHOW VIEW, TRIGGER 
ON `haydee_db`.* TO 'app_respaldos'@'localhost';

GRANT SELECT, LOCK TABLES, SHOW VIEW, TRIGGER 
ON `seguridad_haydee_db`.* TO 'app_respaldos'@'localhost';

-- Privilegio global necesario para rotar logs binarios (FLUSH LOGS / RESET MASTER)
GRANT RELOAD ON *.* TO 'app_respaldos'@'localhost';


-- ====================================================================
-- APLICAR CAMBIOS
-- ====================================================================
-- Aplicar de forma atómica y limpiar memoria RAM de privilegios
FLUSH PRIVILEGES;