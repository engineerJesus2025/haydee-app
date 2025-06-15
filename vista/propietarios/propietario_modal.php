<form action="?pagina=propietario_controlador.php&accion=guardar" method="POST" id="form_propietario"
    name="form_propietario">
    <div class="row m-3">
        <div class="col-md-6">
            <label for="nombre">Nombre del propietario</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="text" class="form-control nombre" name="nombre" id="nombre" placeholder="Nombre"
                    aria-label="nombre" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="apellido">Apellido del propietario</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="text" class="form-control apellido" name="apellido" id="apellido" placeholder="Apellido"
                    aria-label="apellido" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="cedula">Cédula del propietario</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-badge"></i></span>
                <input type="text" class="form-control cedula" name="cedula" id="cedula"
                    placeholder="Cédula del propietario" aria-label="cedula" aria-describedby="basic-addon1"
                    minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="telefono">Teléfono del propietario</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-phone"></i></span>
                <input type="text" class="form-control telefono" name="telefono" id="telefono"
                    placeholder="Teléfono del propietario" aria-label="telefono" aria-describedby="basic-addon1"
                    minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="correo">Correo electrónico</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                <input type="text" class="form-control correo" name="correo" id="correo"
                    placeholder="Correo electrónico" aria-label="correo" aria-describedby="basic-addon1" minlength="3"
                    maxlength="60">
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