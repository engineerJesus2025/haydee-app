<form id="form_usuario" name="form_usuario">
    <div class="row m-3">
        <div class="col-md-6">
            <label for="nombre">Nombre del usuario</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Ejem: Robert" aria-label="nombre" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="apellido">Apellido del usuario</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="text" class="form-control apellido" name="apellido" id="apellido" placeholder="Ejem: Salazar" aria-label="apellido" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="correo">Correo electrónico</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                <input type="text" class="form-control" name="correo" id="correo" placeholder="Ejem: usuario@gmail.com" aria-label="correo" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="rol">Rol</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-gear"></i></span>
                <select class="form-select rol" aria-label="Default select example" name="rol" id="rol" form="form_usuario">
                    <option selected hidden value="">Seleccione un Rol</option>
                    <?php foreach ($roles['datos'] as $rol) : ?>
                        <option value="<?php echo $rol["id_rol"] ?>"><?php echo $rol["nombre"] ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="contra">Contraseña</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-key"></i></span>
                <input type="password" class="form-control" name="contra" id="contra" placeholder="Contraseña" aria-label="contra" aria-describedby="basic-addon1" minlength="5" maxlength="50">
                <button class="btn contra" type="button" title="Mostrar Contraseña" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="confir_contra">Confirmar contraseña</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-key"></i></span>
                <input type="password" class="form-control confir_contra" name="confir_contra" id="confir_contra" placeholder="Confirmar contraseña" aria-label="confir_contra" aria-describedby="basic-addon1" minlength="5" maxlength="50">
                <button class="btn contra" type="button" title="Mostrar Contraseña" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
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