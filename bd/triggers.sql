CREATE TRIGGER `actualizador_caja_eliminar_pago` AFTER DELETE ON `detalles_pagos`
 FOR EACH ROW BEGIN
    IF OLD.caja_id IS NOT NULL THEN
        UPDATE caja_chica 
        SET saldo_actual = saldo_actual - OLD.monto
        WHERE id_caja_chica = OLD.caja_id;
    END IF;
END

CREATE TRIGGER `actualizador_caja_registrar_pago` AFTER INSERT ON `detalles_pagos`
 FOR EACH ROW BEGIN
    IF NEW.caja_id IS NOT NULL THEN
        UPDATE caja_chica 
        SET saldo_actual = saldo_actual + NEW.monto
        WHERE id_caja_chica = NEW.caja_id;
    END IF;
END

CREATE TRIGGER `actualizador_caja_update_pago` AFTER UPDATE ON `detalles_pagos`
 FOR EACH ROW BEGIN
    IF NEW.caja_id IS NOT NULL THEN
        UPDATE caja_chica 
        SET saldo_actual = saldo_actual - OLD.monto + NEW.monto
        WHERE id_caja_chica = NEW.caja_id;
    END IF;
END

CREATE TRIGGER `actualizador_caja_eliminar_gasto` AFTER DELETE ON `detalle_pagos_gastos`
 FOR EACH ROW BEGIN
    IF OLD.caja_id IS NOT NULL THEN
        UPDATE caja_chica 
        SET saldo_actual = saldo_actual + OLD.monto
        WHERE id_caja_chica = OLD.caja_id;
    END IF;
END

CREATE TRIGGER `actualizador_caja_update_gastos` AFTER UPDATE ON `detalle_pagos_gastos`
 FOR EACH ROW BEGIN
    IF NEW.caja_id IS NOT NULL THEN
        UPDATE caja_chica 
        SET saldo_actual = saldo_actual + OLD.monto - NEW.monto
        WHERE id_caja_chica = NEW.caja_id;
    END IF;
END

CREATE TRIGGER `actualizador_caja_registrar_gasto` AFTER INSERT ON `detalle_pagos_gastos`
 FOR EACH ROW BEGIN
    IF NEW.caja_id IS NOT NULL THEN
        UPDATE caja_chica 
        SET saldo_actual = saldo_actual - NEW.monto
        WHERE id_caja_chica = NEW.caja_id;
    END IF;
END
