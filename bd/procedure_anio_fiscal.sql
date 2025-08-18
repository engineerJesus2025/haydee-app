DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `gestionar_anio_fiscal`()
BEGIN
    DECLARE existe_anio_actual BOOLEAN;
    DECLARE anio_actual_abierto BOOLEAN;
    
    SELECT COUNT(*) > 0 INTO existe_anio_actual 
    FROM anio_fiscal 
    WHERE YEAR(fecha_inicio) = YEAR(NOW()) AND estado = 'Abierto';
    
    IF NOT existe_anio_actual THEN
        UPDATE anio_fiscal SET estado = 'Cerrada', fecha_cierre = NOW() 
        WHERE estado = 'Abierto';
        
        INSERT INTO anio_fiscal(fecha_inicio, fecha_cierre, estado, descripcion)
        VALUES (NOW(), NULL, 'Abierto', CONCAT('Año fiscal ', YEAR(NOW())));
    END IF;
END$$
DELIMITER ;