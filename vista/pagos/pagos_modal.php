<form action="?pagina=pagos_controlador.php&accion=guardar" method="POST" id="form_pagos" name="form_pagos">
    <div class="row m-3">
        <div class="col-md-6">
            <label for="fecha">Fecha</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-date"></i></span>
                <input type="date" class="form-control fecha" name="fecha" id="fecha" placeholder="Fecha" aria-label="fecha" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="monto">Monto</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-cash-coin"></i></span>
                <input type="text" class="form-control monto" name="monto" id="monto" placeholder="Monto" aria-label="monto" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="tasa_dolar">Tasa del Dolar</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-coin"></i></span>
                <input type="text" class="form-control tasa_dolar" name="tasa_dolar" id="tasa_dolar" placeholder="Tasa del Dolar" aria-label="tasa_dolar" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="metodo_pago">Metodo de Pago</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-credit-card-fill"></i></span>
                    <select class="form-select metodo_pago" aria-label="Default select example" id="metodo_pago" for="metodo_pago" name="metodo_pago">
                        <option selected hidden value="">Metodo de Pago</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Pago Movil">Pago Movil</option>
                    </select>
                <span class="w-100"></span>
            </div>
        </div>
    </div> 
    <div class="row m-3">
        <div class="col-md-6">
            <label for="banco_id">Banco</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-bank2"></i></span>
                    <select class="form-select banco" aria-label="Default select example" id="banco_id" name="banco_id">
                        <option selected hidden value="">Banco</option>
                        <?php foreach($registro_banco as $banco): ?>
                            <option value="<?php echo $banco["id_banco"]?>"><?php echo $banco["nombre_banco"] ?></option>
                        <?php endforeach; ?>
                    </select>
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="referencia">Referencia</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-receipt"></i></span>
                    <input type="text" class="form-control referencia" name="referencia" id="referencia" placeholder="Referencia" aria-label="referencia" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="imagen">Imagen</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-image-fill"></i></span>
                    <input type="file" class="form-control imagen" name="imagen" id="imagen" placeholder="Imagen" aria-label="imagen" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100"></span>
            </div>
        </div> 
        <div class="col-md-6">
            <label for="estado">Estado del Pago</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-check-circle-fill"></i></span>
                    <select class="form-select estado" aria-label="Default select example" id="estado" for="estado" name="estado">
                        <option selected hidden value="">Estado del Pago</option>
                        <option value="COMPROBADO">COMPROBADO</option>
                        <option value="PENDIENTE">PENDIENTE</option>
                    </select>
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-12">
            <label for="observacion">Observacion</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-info-circle-fill"></i></span>
                    <input type="text" class="form-control observacion" name="observacion" id="observacion" placeholder="Observacion" aria-label="observacion" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-12 text-center">
            <button class="btn btn-primary" type="submit" id="boton_formulario">Registrar</button>
        </div>
    </div>
</form>