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
        VALUES (NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), 'Abierto', CONCAT('Año fiscal ', YEAR(NOW())));
    END IF;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_gestion_caja_chica_mensual`()
sp_block: BEGIN 
    DECLARE v_mes_actual VARCHAR(7);
    DECLARE v_existe_caja_abierta INT;
    DECLARE v_saldo_final_mes_anterior DECIMAL(15,2); -- Ajustado a (15,2) como tu tabla
    DECLARE v_fecha_actual DATE;
    DECLARE v_id_anio_fiscal INT;
    DECLARE v_nombre_mes_espanol VARCHAR(20);
    -- Variable para definir el tope del fondo fijo (Configurable)
    DECLARE v_monto_fondo_fijo DECIMAL(15,2) DEFAULT 1000.00; 
    
    SET v_fecha_actual = CURDATE();
    SET v_mes_actual = DATE_FORMAT(v_fecha_actual, '%Y-%m');
    
    -- 1. Validar Año Fiscal
    SELECT id_anio_fiscal INTO v_id_anio_fiscal
    FROM anio_fiscal
    WHERE v_fecha_actual BETWEEN fecha_inicio AND fecha_cierre
    AND estado = 'Abierto'
    LIMIT 1;
    
    -- Si no hay año fiscal, salimos
    IF v_id_anio_fiscal IS NULL THEN
        -- Opcional: Podrías registrar este error en una tabla de bitácora de sistema
        LEAVE sp_block; 
    END IF;
    
    -- 2. Verificar si ya existe caja para este mes
    SELECT COUNT(*) INTO v_existe_caja_abierta 
    FROM caja_chica 
    WHERE DATE_FORMAT(fecha_creacion, '%Y-%m') = v_mes_actual 
    AND anio_fiscal_id = v_id_anio_fiscal; 
    -- Nota: Quité "AND estado = 'Abierta'" porque si ya se creó una este mes 
    -- (aunque esté cerrada por error), no deberíamos duplicarla automáticamente.
    
    IF v_existe_caja_abierta = 0 THEN
    
        -- A. Cerrar cajas de meses anteriores que hayan quedado abiertas
        UPDATE caja_chica 
        SET estado = 'Cerrada'
        WHERE estado = 'Abierta'
        AND anio_fiscal_id = v_id_anio_fiscal;
        
        -- B. Obtener el saldo remanente de la última caja cerrada
        SET v_saldo_final_mes_anterior = NULL;
        
        SELECT saldo_actual INTO v_saldo_final_mes_anterior
        FROM caja_chica
        WHERE anio_fiscal_id = v_id_anio_fiscal
        ORDER BY fecha_creacion DESC
        LIMIT 1;
        
        -- Si es la primera caja del año o del sistema, inicia con el monto base
        IF v_saldo_final_mes_anterior IS NULL THEN
            SET v_saldo_final_mes_anterior = v_monto_fondo_fijo;
        END IF;
        
        -- C. Calcular nombre del mes
        SET v_nombre_mes_espanol = 
            CASE MONTH(v_fecha_actual)
                WHEN 1 THEN 'Enero' WHEN 2 THEN 'Febrero' WHEN 3 THEN 'Marzo'
                WHEN 4 THEN 'Abril' WHEN 5 THEN 'Mayo' WHEN 6 THEN 'Junio'
                WHEN 7 THEN 'Julio' WHEN 8 THEN 'Agosto' WHEN 9 THEN 'Septiembre'
                WHEN 10 THEN 'Octubre' WHEN 11 THEN 'Noviembre' WHEN 12 THEN 'Diciembre'
                ELSE 'Desconocido'
            END;
        
        -- D. Insertar la nueva caja
        -- NOTA: En tu tabla hay 'fondo_fijo' y 'saldo_actual'. 
        -- Asumo que 'fondo_fijo' es el tope teórico y 'saldo_actual' es el dinero real que pasa.
        INSERT INTO caja_chica 
            (fecha_creacion, fondo_fijo, saldo_actual, estado, 
            descripcion, anio_fiscal_id) 
        VALUES 
            (v_fecha_actual, v_monto_fondo_fijo, v_saldo_final_mes_anterior, 
            'Abierta', CONCAT('Caja chica del mes ', v_nombre_mes_espanol, ' - ', YEAR(v_fecha_actual)), v_id_anio_fiscal);
       
    END IF;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_reposicion_caja`(IN `p_monto_reposicion` DECIMAL(15,2), IN `p_caja_id` INT)
sp_block: BEGIN
    -- 1. Declaración de Variables
    DECLARE v_mensaje VARCHAR(500);
    DECLARE v_codigo_error INT DEFAULT 0;
    
    DECLARE v_proveedor_id INT;
    DECLARE v_tipo_gasto_id INT;
    DECLARE v_solicitud_gasto_id INT;
    DECLARE v_gasto_id INT;
    DECLARE v_saldo_acumulado DECIMAL(15,2) DEFAULT 0.00;
    
    -- Variables para el cursor (Recorrer movimientos)
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_mov_id INT;
    DECLARE v_mov_monto DECIMAL(15,2);
    
    -- Cursor: Trae los movimientos pendientes de esa caja, ordenados por fecha (FIFO)
    DECLARE cur_movimientos CURSOR FOR 
        SELECT id_movimiento_caja, monto 
        FROM movimientos_caja 
        WHERE caja_chica_id = p_caja_id 
        AND estado = 'Pendiente por reposicion'
        ORDER BY fecha ASC; -- Primero los más viejos
        
    -- Handler para cuando se acaben los movimientos
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    -- Manejo de errores SQL (Rollback si algo falla)
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        GET DIAGNOSTICS CONDITION 1
            v_mensaje = MESSAGE_TEXT,
            v_codigo_error = MYSQL_ERRNO;
        
        SELECT CONCAT('Error ', v_codigo_error, ': ', v_mensaje) AS mensaje;
    END;

    -- INICIO DE LA TRANSACCIÓN
    START TRANSACTION;

    -- 2. Obtener IDs "Semilla" (Buscando por nombre como pediste)
    -- Buscamos el Proveedor Interno
    SELECT id_proveedor INTO v_proveedor_id 
    FROM proveedores 
    WHERE proveedores.nombre_proveedor LIKE '%Administración (Caja Chica)%' 
    LIMIT 1;

    -- Buscamos el Tipo de Gasto (Reposición)
    SELECT id_tipo_gasto INTO v_tipo_gasto_id 
    FROM tipo_gasto 
    WHERE tipo_gasto.nombre_tipo_gasto LIKE '%Reposición%' 
    LIMIT 1;

    -- Buscamos la solicitud de gasto (Reposición)
    SELECT id_solicitud INTO v_solicitud_gasto_id 
    FROM solicitudes_gasto 
    WHERE solicitudes_gasto.nombre_solicitante LIKE '%Administracion%' 
    LIMIT 1;

    -- Validamos que existan
    IF v_proveedor_id IS NULL THEN
        SELECT 'Error: No se encontró el Proveedor "Administración (Caja Chica)".' AS mensaje;
        ROLLBACK;
        LEAVE sp_block;
    END IF;

    IF v_tipo_gasto_id IS NULL THEN
        SELECT 'Error: No se encontró el Tipo de Gasto "Reposición".' AS mensaje;
        ROLLBACK;
        LEAVE sp_block;
    END IF;

    IF v_solicitud_gasto_id IS NULL THEN
        SELECT 'Error: No se encontró la solicitud de Gasto "Reposición".' AS mensaje;
        ROLLBACK;
        LEAVE sp_block;
    END IF;

    -- 3. Registrar el Gasto Global (Cabecera)
    -- En la tabla 'gastos' registramos la clasificación y relaciones 
    INSERT INTO gastos (descripcion_gasto, proveedor_id, tipo_gasto_id, solicitud_id, clasificacion)
    VALUES (
        CONCAT('Reposición de Caja Chica - ', DATE_FORMAT(NOW(), '%d/%m/%Y')), 
        v_proveedor_id, 
        v_tipo_gasto_id,
        v_solicitud_gasto_id,
        'Reposición'
    );
    
    SET v_gasto_id = LAST_INSERT_ID(); 

    -- 4. Registrar el Detalle del Gasto (Financiero)
    -- Insertamos en 'detalles_gastos' el monto real de la transacción 
    INSERT INTO detalles_gastos (fecha, monto, monto_dolar, metodo_pago, gasto_id, descripcion_detalle_gasto)
    VALUES (
        CURDATE(),
        p_monto_reposicion,
        0.00, -- Ajustar si manejas tasa de cambio
        'Efectivo', 
        v_gasto_id,
        CONCAT('Detalle de reposición por monto de: ', p_monto_reposicion)
    );

    -- 5. Procesar Movimientos Individuales
    OPEN cur_movimientos;

    read_loop: LOOP
        FETCH cur_movimientos INTO v_mov_id, v_mov_monto;
        
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Verificamos si al sumar este movimiento nos pasamos del monto del cheque
        IF (v_saldo_acumulado + v_mov_monto) <= p_monto_reposicion THEN
            
            -- A. Insertar en la tabla intermedia (Plan B)
            INSERT INTO reposiciones (gasto_id, movimiento_caja_id)
            VALUES (v_gasto_id, v_mov_id);

            -- B. Actualizar el estado del movimiento
            UPDATE movimientos_caja 
            SET estado = 'Repuesto' 
            WHERE id_movimiento_caja = v_mov_id;

            -- C. Sumar al acumulado
            SET v_saldo_acumulado = v_saldo_acumulado + v_mov_monto;
            
        ELSE
            -- Si el siguiente ticket es muy grande y se pasa del monto, 
            -- decidimos parar aquí (o podrías seguir buscando tickets más pequeños, 
            -- pero por orden contable es mejor parar).
            LEAVE read_loop;
        END IF;
        
    END LOOP;

    CLOSE cur_movimientos;

    -- 5. Actualizar el Saldo de la Caja
    -- El dinero entra a la caja, recuperando lo gastado
    UPDATE caja_chica 
    SET saldo_actual = saldo_actual + v_saldo_acumulado
    WHERE id_caja_chica = p_caja_id;

    -- Confirmar cambios
    COMMIT;
    
    SELECT CONCAT('Reposición exitosa. Gasto ID: ', v_gasto_id, '. Total Repuesto: ', v_saldo_acumulado) AS mensaje;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_sincronizar_presupuestos_mensualidad`(IN `p_mensualidad_id` INT, IN `p_nuevos_presupuestos_ids` TEXT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; 
    END;

    CREATE TEMPORARY TABLE IF NOT EXISTS TempNuevosGastos (detalle_presupuesto_id INT PRIMARY KEY);
    TRUNCATE TABLE TempNuevosGastos;

    SET @sql = CONCAT('INSERT INTO TempNuevosGastos (detalle_presupuesto_id) VALUES (', REPLACE(p_nuevos_presupuestos_ids, ',', '),('), ');');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    
    START TRANSACTION;

        DELETE FROM presupuesto_mensualidad
        WHERE
            mensualidad_id = p_mensualidad_id
            AND detalle_presupuesto_id NOT IN (SELECT detalle_presupuesto_id FROM TempNuevosGastos);

        INSERT INTO presupuesto_mensualidad (mensualidad_id, detalle_presupuesto_id)
        SELECT p_mensualidad_id, nuevos.detalle_presupuesto_id
        FROM TempNuevosGastos AS nuevos
        WHERE NOT EXISTS (
            SELECT 1
            FROM presupuesto_mensualidad AS existentes
            WHERE existentes.mensualidad_id = p_mensualidad_id AND existentes.detalle_presupuesto_id = nuevos.detalle_presupuesto_id
        );

    COMMIT;

    DROP TEMPORARY TABLE TempNuevosGastos;

END$$
DELIMITER ;
