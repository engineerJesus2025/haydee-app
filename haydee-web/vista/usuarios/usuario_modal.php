<div class="modal fade" id="modal_usuario" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content card shadow-lg"> 
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-plus me-2" id="icono_titulo_modal"></i>
                    <span id="titulo_modal">Registrar Usuario</span>
                </h5>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            
            <div class="modal-body rounded-bottom vp-body p-4">
                <form id="form_usuario" name="form_usuario">
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="nombre" class="form-label fw-semibold">Nombre del usuario <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Ejem: Robert" aria-label="nombre" aria-describedby="basic-addon1" minlength="3" maxlength="30" autocomplete="new-password" autofocus>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="apellido" class="form-label fw-semibold">Apellido del usuario <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <span class="input-group-text" id="basic-addon2"><i class="bi bi-person-badge"></i></span>
                                <input type="text" class="form-control apellido" name="apellido" id="apellido" placeholder="Ejem: Salazar" aria-label="apellido" aria-describedby="basic-addon2" minlength="3" maxlength="30">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row my-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="correo" class="form-label fw-semibold">Correo electrónico <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <span class="input-group-text" id="basic-addon3"><i class="bi bi-envelope-at"></i></span>
                                <input type="text" class="form-control" name="correo" id="correo" placeholder="Ejem: usuario@gmail.com" aria-label="correo" aria-describedby="basic-addon3" minlength="3" maxlength="60" autocomplete="new-password">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="rol_id" class="form-label fw-semibold">Rol <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <span class="input-group-text" id="basic-addon4"><i class="bi bi-person-gear"></i></span>
                                <select class="form-select rol" aria-label="Default select example" name="rol_id" id="rol_id" form="form_usuario">
                                    <option selected hidden value="">Seleccione un Rol</option>
                                    <?php foreach ($roles['datos'] as $rol) : ?>
                                        <option value="<?php echo $rol["id_rol"] ?>"><?php echo $rol["nombre"] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row my-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="contra" class="form-label fw-semibold">Contraseña <span class="text-danger span-contra">*</span></label>
                            <div class="input-group has-validation">
                                <span class="input-group-text" id="basic-addon5"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control contra-input" name="contra" id="contra" placeholder="Contraseña" aria-label="contra" aria-describedby="basic-addon5" minlength="5" maxlength="50" autocomplete="new-password">
                                <button class="btn contra-btn" type="button" title="Mostrar Contraseña" tabindex="-1">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <div class="progress mt-2 mb-1 w-100" style="height: 6px; border-radius: 10px;">
                                    <div id="barra_seguridad" class="progress-bar bg-danger transition-all" role="progressbar" style="width: 0%; transition: width 0.4s ease;"></div>
                                </div>
                                <small id="texto_seguridad" class="fw-medium text-danger d-block w-100 invalid-feedback" style="font-size: 0.75rem;">Nivel de seguridad: Vacío</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="confir_contra" class="form-label fw-semibold">Confirmar contraseña <span class="text-danger span-contra">*</span></label>
                            <div class="input-group has-validation">
                                <span class="input-group-text" id="basic-addon6"><i class="bi bi-key-fill"></i></span>
                                <input type="password" class="form-control contra-input" name="confir_contra" id="confir_contra" placeholder="Confirmar contraseña" aria-label="confir_contra" aria-describedby="basic-addon6" minlength="5" maxlength="50">
                                <button class="btn contra-btn" type="button" title="Mostrar Contraseña" tabindex="-1">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="col-md-12 text-end"> 
                    <button type="button" class="btn btn-soft-secondary px-4 me-2 mb-2" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i> Cancelar
                    </button>
                    <button class="btn btn-primary px-4 shadow-sm mb-2" type="submit" id="boton_formulario">
                        <i class="bi bi-check2-circle me-2"></i>
                        <span id="texto_boton_formulario">Guardar Usuario</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>