<form action="?pagina=apartamentos_controlador.php&accion=guardar" method="POST" id="form_apartamentos" name="form_apartamentos">
    <div class="row m-3">
        <div class="col-md-6">
            <label for="nro_apartamento">Número del Apartamento</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="text" class="form-control nro_apartamento" name="nro_apartamento" id="nro_apartamento" placeholder="Número del Apartamento" aria-label="nro_apartamento" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="porcentaje_participacion">Porcentaje de Participación</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="text" class="form-control porcentaje_participacion" name="porcentaje_participacion" id="porcentaje_participacion" placeholder="Porcentaje de Participación" aria-label="porcentaje_participacion" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="gas">¿Tiene Gas?</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                    <select class="form-select gas" aria-label="Default select example" id="gas" for="gas" name="gas">
                        <option selected hidden value="">Seleccione una Opción</option>
                        <option value="1">TIENE</option>
                        <option value="0">NO TIENE</option>
                    </select>
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="agua">¿Tiene Agua?</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                    <select class="form-select agua" aria-label="Default select example" id="agua" for="agua" name="agua">
                        <option selected hidden value="">Seleccione una Opción</option>
                        <option value="1">TIENE</option>
                        <option value="0">NO TIENE</option>
                    </select>
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="alquilado">¿Es Alquilado?</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                    <select class="form-select agua" aria-label="Default select example" id="alquilado" for="alquilado" name="alquilado">
                        <option selected hidden value="">Seleccione una Opción</option>
                        <option value="1">SI</option>
                        <option value="0">NO</option>
                    </select>
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="propietario_id">Propietario</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                    <select class="form-select propietario_id" aria-label="Default select example" id="propietario_id" name="propietario_id">
                        <option selected hidden value="">Seleccione un Propietario</option>
                        <?php foreach($registro_propietario as $propietario): ?>
                            <option value="<?php echo $propietario["id_propietario"]?>"><?php echo $propietario["nombre"]." ".$propietario["apellido"] ?></option>
                        <?php endforeach; ?>
                    </select>
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