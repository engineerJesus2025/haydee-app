<form id="form_usuario" name="form_usuario">
    <div class="row m-3">
        <div class="col-md-6">
            <label for="contra">Nueva Contraseña</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-key"></i></span>
                <input type="password" class="form-control contra-input" name="contra" id="contra" placeholder="Contraseña" aria-label="contra" aria-describedby="basic-addon1" minlength="5" maxlength="50">
                <button class="btn contra-btn" type="button" title="Mostrar Contraseña" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="confir_contra">Confirmar Nueva Contraseña</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-key"></i></span>
                <input type="password" class="form-control contra-input" name="confir_contra" id="confir_contra" placeholder="Confirmar contraseña" aria-label="confir_contra" aria-describedby="basic-addon1" minlength="5" maxlength="50">
                <button class="btn contra-btn" type="button" title="Mostrar Contraseña" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-12 text-center">
            <button class="btn btn-primary" type="submit" id="boton_guardar_contra">Guardar nueva contraseña</button>
        </div>
    </div>
</form>