<form action="?pagina=presupuesto_controlador.php&accion=guardar" method="POST" id="form_presupuesto" name="form_presupuesto">
    <div class="row m-3">
        <div class="col-md-6">
            <label for="fecha">Fecha del presupuesto</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar"></i></span>
                <select class="form-select" aria-label="Default select example" name="fecha" id="fecha" form="form_presupuesto">
                    <option selected hidden value="">Seleccione la fecha del presupuesto</option>                    
                </select>
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="cuota_reserva">Cuota de Reserva:</label>
            <div class="input-group mb-3">                
                <input type="number" name="cuota_reserva" id="cuota_reserva" placeholder="Ingrese la cuota de reserva" class="form-control" maxlength="15">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>
    <h5 class="text-center mb-0">Asignación de presupuestos:</h5>
    <div class="row my-3 justify-content-center" id="contenedor_presupuestos">
    </div>

    <div class="row m-3">
        <div class="col-md-12">
            <label for="observacion">Observación</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-key"></i></span>
                <input type="text" class="form-control" name="observacion" id="observacion" placeholder="Puede agregar una observación o comentario del presupuesto de este mes" aria-label="observacion" aria-describedby="basic-addon1" minlength="0" maxlength="100">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-12 text-center">
            <button class="btn btn-primary" type="submit" id="boton_formulario">Registrar</button>
        </div>
    </div>
</form>