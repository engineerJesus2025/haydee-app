<form action="?pagina=habitantes_controlador.php&accion=guardar" method="POST" id="form_habitantes" name="form_habitantes">
    <div class="row m-3">
        <div class="col-md-6">
            <label for="cedula">Cedula</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-square"></i></i></span>
                <input type="text" class="form-control cedula" name="cedula" id="cedula" placeholder="Cedula" aria-label="cedula" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="nombre_habitante">Nombre</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="text" class="form-control nombre_habitante" name="nombre_habitante" id="nombre_habitante" placeholder="Nombre" aria-label="nombre_habitante" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="apellido">Apellido</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                <input type="text" class="form-control apellido" name="apellido" id="apellido" placeholder="Apellido" aria-label="apellido" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-cake2"></i></span>
                <input type="date" class="form-control fecha_nacimiento" name="fecha_nacimiento" placeholder="Fecha de nacimiento" aria-label="fecha_nacimiento" aria-describedby="basic-addon1">
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="telefono">Telefono</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                <input type="text" class="form-control telefono" name="telefono" id="telefono" placeholder="Telefono" aria-label="telefono" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="apartamento_id">Apartamento</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-gender-male"></i></span>
                <select class="form-select apartamento_id" aria-label="Default select example" for="apartamento_id" name="apartamento_id">
                    <option selected hidden value="">Apartamento</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                </select>
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="sexo">Sexo</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-gender-male"></i></span>
                <select class="form-select sexo" aria-label="Default select example" for="sexo" name="sexo">
                    <option selected hidden value="">Sexo</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
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