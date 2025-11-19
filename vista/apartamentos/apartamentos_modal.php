<form action="?pagina=apartamentos_controlador.php&accion=guardar" method="POST" id="form_apartamentos" name="form_apartamentos">
    <div class="row m-3">
        <div class="col-md-6">
            <label for="nro_apartamento">Número del Apartamento</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-3-square-fill"></i></span>
                <input type="text" class="border border-dark form-control nro_apartamento" name="nro_apartamento" id="nro_apartamento" placeholder="Ejem: 2-6" aria-label="nro_apartamento" aria-describedby="basic-addon1" minlength="1" maxlength="3">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="porcentaje_participacion">Porcentaje de Participación</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-percent"></i></span>
                <input type="text" class="border border-dark form-control porcentaje_participacion" name="porcentaje_participacion" id="porcentaje_participacion" placeholder="Ejem: 5.25" aria-label="porcentaje_participacion" aria-describedby="basic-addon1" minlength="1" maxlength="4">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="gas">¿Tiene Gas?</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-fuel-pump-fill"></i></span>
                    <select class="border border-dark form-select gas" aria-label="Default select example" id="gas" for="gas" name="gas">
                        <option selected hidden value="">Seleccione una Opción</option>
                        <option value="1">TIENE</option>
                        <option value="2">NO TIENE</option>
                    </select>
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="agua">¿Tiene Agua?</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-droplet-half"></i></span>
                    <select class="border border-dark form-select agua" aria-label="Default select example" id="agua" for="agua" name="agua">
                        <option selected hidden value="">Seleccione una Opción</option>
                        <option value="1">TIENE</option>
                        <option value="2">NO TIENE</option>
                    </select>
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="alquilado">¿Es Alquilado?</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-buildings"></i></span>
                    <select class="border border-dark form-select agua" aria-label="Default select example" id="alquilado" for="alquilado" name="alquilado">
                        <option selected hidden value="">Seleccione una Opción</option>
                        <option value="1">SI</option>
                        <option value="2">NO</option>
                    </select>
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-12 text-center">
            <button class="btn btn-primary" type="submit" id="boton_formulario">Guardar</button>
        </div>
    </div>
</form>