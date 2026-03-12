<form id="form_usuario" name="form_usuario">
    <div class="row m-3">
        <div class="col-md-6">
            <label for="contra">Nueva Contraseña <spam class="text-danger">*</spam></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-key"></i></span>
                <input type="password" class="form-control contra-input" name="contra" id="contra" placeholder="Contraseña" aria-label="contra" aria-describedby="basic-addon1" minlength="5" maxlength="50">
                <button class="btn contra-btn" type="button" title="Mostrar Contraseña" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
                <div class="progress mt-2 mb-1 w-100" style="height: 6px; border-radius: 10px;">
                    <div id="barra_seguridad" class="progress-bar bg-danger transition-all" role="progressbar" style="width: 0%; transition: width 0.4s ease;"></div>
                </div>
                <small id="texto_seguridad" class="fw-medium text-danger d-block mb-3 w-100 invalid-feedback" style="font-size: 0.75rem;">Nivel de seguridad: Vacío</small>
            </div>
        </div>
        <div class="col-md-6">
            <label for="confir_contra">Confirmar Nueva Contraseña <spam class="text-danger">*</spam></label>
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