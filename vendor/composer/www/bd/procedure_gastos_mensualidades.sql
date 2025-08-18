DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_sincronizar_gastos_mensualidad`(IN `p_mensualidad_id` INT, IN `p_nuevos_gastos_ids` TEXT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; 
    END;

    CREATE TEMPORARY TABLE IF NOT EXISTS TempNuevosGastos (gasto_id INT PRIMARY KEY);
    TRUNCATE TABLE TempNuevosGastos;

    SET @sql = CONCAT('INSERT INTO TempNuevosGastos (gasto_id) VALUES (', REPLACE(p_nuevos_gastos_ids, ',', '),('), ');');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    
    START TRANSACTION;

        DELETE FROM gastos_mensualidades
        WHERE
            mensualidad_id = p_mensualidad_id
            AND gasto_id NOT IN (SELECT gasto_id FROM TempNuevosGastos);

        INSERT INTO gastos_mensualidades (mensualidad_id, gasto_id)
        SELECT p_mensualidad_id, nuevos.gasto_id
        FROM TempNuevosGastos AS nuevos
        WHERE NOT EXISTS (
            SELECT 1
            FROM gastos_mensualidades AS existentes
            WHERE existentes.mensualidad_id = p_mensualidad_id AND existentes.gasto_id = nuevos.gasto_id
        );

    COMMIT;

    DROP TEMPORARY TABLE TempNuevosGastos;

END$$
DELIMITER ;