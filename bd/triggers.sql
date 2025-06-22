CREATE TRIGGER `actualizador_caja_gasto` AFTER INSERT ON `gastos`
 FOR EACH ROW BEGIN
    IF NEW.caja_id IS NOT NULL THEN
        UPDATE caja_chica 
        SET saldo_actual = saldo_actual - NEW.monto
        WHERE id_caja_chica = NEW.caja_id;
    END IF;
END

CREATE TRIGGER `actualizador_caja_pago` BEFORE INSERT ON `detalles_pagos`
 FOR EACH ROW BEGIN
    IF NEW.caja_id IS NOT NULL THEN
        UPDATE caja_chica 
        SET saldo_actual = saldo_actual - NEW.monto
        WHERE id_caja_chica = NEW.caja_id;
    END IF;
END
