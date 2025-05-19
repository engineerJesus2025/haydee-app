<form action="?pagina=usuario_controlador.php&accion=guardar" method="POST" id="form_movimientos" name="form_movimientos">
    <div class="row m-3">
        <div class="col-md-4">
            <label for="monto_movimiento">Monto del movimiento</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-coin"></i></span>
                <input type="text" class="form-control" name="monto_movimiento" id="monto_movimiento" placeholder="Ingrese el Monto" aria-label="monto_movimiento" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-4">
            <label for="fecha_movimiento">Fecha del movimiento</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar2-check"></i></span>
                <input type="date" class="form-control" name="fecha_movimiento" id="fecha_movimiento" aria-label="fecha_movimiento" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-4">
            <label for="referencia_movimiento">Referencia del movimiento</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-journal"></i></span>
                <input type="text" class="form-control" name="referencia_movimiento" id="referencia_movimiento" placeholder="Ingrese la Referencia" aria-label="referencia_movimiento" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="tipo_movimiento">Tipo de Movimiento</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-info-lg"></i></span>
                <select class="form-select" aria-label="Default select example" name="tipo_movimiento" id="tipo_movimiento" form="form_movimientos">
                    <option selected hidden value="">Seleccione el tipo de movimiento</option>
                    <option value="Ingreso">Ingreso</option>
                    <option value="Egreso">Egreso</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <label for="banco">Banco</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-bank"></i></span>
                <select class="form-select" aria-label="Default select example" name="banco_movimiento" id="banco_movimiento" form="form_movimientos">
                    <option selected hidden value="">Seleccione el Banco</option>
                    <?php foreach ($registros_bancos as $registro) : ?>
                        <option value="<?php echo $registro["id_banco"] ?>"><?php echo $registro["nombre_banco"] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <h5 class="text-center">Registro del sistema:</h5>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="monto_sistema">Monto según el sistema</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-cash-coin"></i></span>
                <input type="text" class="form-control" name="monto_sistema" id="monto_sistema" placeholder="Monto según el sistema" aria-label="monto_sistema" aria-describedby="basic-addon1" minlength="5" maxlength="50">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="pago_gasto_sistema">Pago realizado por:</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-circle"></i></span>
                <input type="text" class="form-control" name="pago_gasto_sistema" id="pago_gasto_sistema" placeholder="Pago realizado por" aria-label="pago_gasto_sistema" aria-describedby="basic-addon1" minlength="5" maxlength="50">
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="fecha_sistema">Fecha de registro de Pago</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-event"></i></span>
                <input type="text" class="form-control" name="fecha_sistema" id="fecha_sistema" placeholder="Fecha de registro de Pago" aria-label="fecha_sistema" aria-describedby="basic-addon1" minlength="5" maxlength="50">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="referencia_sistema">Referencia Según el Sistema</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-journal-bookmark-fill"></i></span>
                <input type="text" class="form-control" name="referencia_sistema" id="referencia_sistema" placeholder="Referencia Según el Sistema" aria-label="referencia_sistema" aria-describedby="basic-addon1" minlength="5" maxlength="50">
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <h5 class="text-center">Resumen:</h5>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="diferencia_tipo">Diferencia</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-plus-slash-minus"></i></span>
                <input type="text" class="form-control me-1" name="diferencia_tipo" id="diferencia_tipo" placeholder="Nombre diferencia_tipo" aria-label="diferencia_tipo" aria-describedby="basic-addon1" minlength="5" maxlength="50">
                 <input type="text" class="form-control" name="diferencia_monto" id="diferencia_monto" placeholder="diferencia_monto" aria-label="diferencia_monto" aria-describedby="basic-addon1" minlength="5" maxlength="50">
                <span class="w-100"></span>
            </div>
        </div>        
        <div class="col-md-6">
            <label for="estado">Estado</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-brilliance"></i></span>
                <input type="text" class="form-control" name="estado" id="estado" placeholder="Estado" aria-label="estado" aria-describedby="basic-addon1" minlength="5" maxlength="50">
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