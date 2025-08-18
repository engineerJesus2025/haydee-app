<form action="?pagina=pagos_controlador.php&accion=guardar" method="POST" id="form_detalles_pagos" name="form_detalles_pagos">
    <div class="row m-3">
        <div class="col-md-6">
            <label for="apartamento">Apartamento</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-building"></i></span>
                <select class="form-select apartamento_id" aria-label="Default select example" id="apartamento_id_detalles" name="apartamento_id">
                    <option selected hidden value="">Seleccione su Apartamento</option>
                    <?php foreach($registro_apartamento as $apartamento): ?>
                        <option value="<?php echo $apartamento["id_apartamento"]?>"><?php echo "Nro: ".$apartamento["nro_apartamento"] ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="mensualidad">Mensualidad</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-bank2"></i></span>
                    <select class="form-select mensualidad_id" aria-label="Default select example" id="mensualidad_id_detalles" name="mensualidad_id">
                        <option selected hidden value="">Escoja primero un Apartamento</option> 
                    </select>
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-lg-3 col-sm-6">
            <label for="fecha">Fecha</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-date"></i></span>
                <input type="date" class="form-control fecha" name="fecha" id="fecha_detalles" placeholder="Fecha" aria-label="fecha" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <label for="tipo_pago">Metodo de Pago</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-credit-card-fill"></i></span>
                    <select class="form-select tipo_pago" aria-label="Default select example" id="tipo_pago_detalles" for="tipo_pago" name="tipo_pago">
                        <option selected hidden value="">Metodo de Pago</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Pago Movil">Pago Movil</option>
                    </select>
                <span class="w-100"></span>
            </div>
        </div>
        <!--<div class="col-md-2">
            <label for="tasa_dolar">USD</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-coin"></i></span>
                <input type="text" class="form-control tasa_dolar" name="tasa_dolar" id="monto_dolares" placeholder="USD" aria-label="tasa_dolar" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100"></span>
            </div>
        </div>-->
        <div class="col-lg-3 col-sm-6">
            <label for="monto_dolar">Tasa del Dolar</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-coin"></i></span>
                <input type="text" class="form-control monto_dolar" name="monto_dolar" id="monto_dolar_detalles" placeholder="Tasa del Dolar" aria-label="tasa_dolar" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100"></span>
            </div>
        </div>
        <!--<div class="col-md-2">
            <label for="tasa_dolar">Bolivares</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-coin"></i></span>
                <input type="text" class="form-control tasa_dolar" name="tasa_dolar" id="monto_bolivares" placeholder="Bolivares" aria-label="tasa_dolar" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100"></span>
            </div>
        </div>-->
        <div class="col-lg-3 col-sm-6">
            <label for="monto_mensualidad">Monto Mensualidad</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-cash-coin"></i></span>
                <input type="text" class="form-control monto" name="monto_mensualidad" id="monto_mensualidad_detalles" placeholder="Monto Mensualidad" aria-label="monto_mensualidad" aria-describedby="basic-addon1" minlength="3" maxlength="30" readonly>
                <span class="w-100"></span>
            </div>
        </div>
    </div> 
    <div class="row m-3">
        <div class="col-lg-3 col-sm-6 campo-monto d-none">
            <label for="monto">Monto</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-cash-coin"></i></span>
                <input type="text" class="form-control monto" name="monto" id="monto_detalles" placeholder="Monto" aria-label="monto" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 campos-bancarios d-none">
            <label for="referencia">Referencia</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-receipt"></i></span>
                    <input type="text" class="form-control referencia" name="referencia" id="referencia_detalles" placeholder="Referencia" aria-label="referencia" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6 campos-bancarios d-none">
            <label for="banco_id">Banco</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-bank2"></i></span>
                    <select class="form-select banco" aria-label="Default select example" id="banco_id_detalles" name="banco_id">
                        <option selected hidden value="">Escoga el Banco</option>
                        <?php foreach($registro_banco as $banco): ?>
                            <option value="<?php echo $banco["id_banco"]?>"><?php echo $banco["nombre_banco"] ?></option>
                        <?php endforeach; ?>
                    </select>
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-lg-6 campos-bancarios d-none">
            <label for="imagen">Imagen</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-image-fill"></i></span>
                    <input type="file" class="form-control imagen" name="imagen" id="imagen_detalles" placeholder="Imagen" aria-label="imagen" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                    <small id="nombre_imagen_cargada_detalles" class="text-muted fst-italic d-block mt-1"></small>
                    <button type="button" id="boton_eliminar_imagen_detalles" class="btn btn-sm btn-outline-danger mt-2 d-none">
                        <i class="bi bi-trash3"></i> Eliminar imagen cargada
                    </button>
                <span class="w-100"></span>
            </div>
        </div> 
    </div>
    <div class="row m-3">
        <div class="col-md-12 text-center">
            <button class="btn btn-primary" type="submit" id="boton_formulario_detalles">Registrar</button>
        </div>
    </div>
</form>