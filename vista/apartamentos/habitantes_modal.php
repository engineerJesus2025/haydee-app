<div class="modal fade" id="modal_habitantes" tabindex="-1" aria-labelledby="titulo_modal_habitantes" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5">
                    <i class="bi bi-person-plus" id="icono_titulo_modal_habitantes"></i>
                    <span class="ms-2" id="titulo_modal_habitantes">Registrar Habitante</span>
                </h1>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-link text-white p-1 me-3 lh-1 d-md-none" id="btn_ayuda_modal" title="Ayuda de este formulario" data-tooltip="true">
                        <i class="bi bi-question-circle-fill fs-4"></i>
                    </button>
                    
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body rounded-bottom ">
                <form id="form_habitantes" name="form_habitantes">
                    <div class="row m-3">
                        <div class="col-md-6">
                            <label for="cedula">Cedula <span class="text-danger">*</span></label>
                            <div class="input-group has-validation mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-vcard-fill"></i></span>
                                <select class="form-select" id="tipo_cedula" aria-label="Default select example" for="tipo_cedula" name="tipo_cedula">
                                    <option selected="" hidden value="">N/A</option>
                                    <option value="V">V</option>
                                    <option value="E">E</option>
                                </select>
                                <input type="text" class=" form-control cedula" name="cedula" id="cedula" placeholder="Ejem: 30475465" aria-label="cedula" aria-describedby="basic-addon1" minlength="7" maxlength="8" style="flex-grow: 7" value="Selección de documento" disabled>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="nombre">Nombre <span class="text-danger">*</span></label>
                            <div class="input-group has-validation mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                                <input type="text" class=" form-control nombre" name="nombre" id="nombre" placeholder="Ejem: Carlos" aria-label="nombre" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row m-3">
                        <div class="col-md-6">
                            <label for="apellido">Apellido <span class="text-danger">*</span></label>
                            <div class="input-group has-validation mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                                <input type="text" class=" form-control apellido" name="apellido" id="apellido" placeholder="Ejem: Rodriguez" aria-label="apellido" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_nacimiento">Fecha de Nacimiento <span class="text-danger">*</span></label>
                            <div class="input-group has-validation mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-cake2"></i></span>
                                <input type="date" class=" form-control fecha_nacimiento" name="fecha_nacimiento" id="fecha_nacimiento" placeholder="Fecha de nacimiento" aria-label="fecha_nacimiento" aria-describedby="basic-addon1">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row m-3">
                        <div class="col-md-6">
                            <label for="telefono">Teléfono <span class="text-danger">*</span></label>
                            <div class="input-group has-validation mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-telephone-fill"></i></span>
                                <input type="text" class=" form-control telefono" name="telefono" id="telefono" placeholder="Ejem: 04167121830" aria-label="telefono" aria-describedby="basic-addon1" maxlength="11">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="sexo">Sexo <span class="text-danger">*</span></label>
                            <div class="input-group has-validation mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-gender-male"></i></span>
                                <select class="form-select sexo" aria-label="Default select example" for="sexo" name="sexo" id="sexo">
                                    <option selected hidden value="">Seleccione un Sexo</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                </select>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        
                    </div> 
                    <div class="row m-3">
                        <div class="col-md-12">
                            <label for="correo">Correo electrónico <span class="text-danger">*</span></label>
                            <div class="input-group has-validation mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                                <input type="text" class=" form-control correo" name="correo" id="correo" placeholder="Ejem: usuario@gmail.com" aria-label="correo" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        
                    </div>
                    <div class="row m-3">
                        <div class="col-md-6">
                            <label for="apartamento_nro_visual">Apartamento <span class="text-danger">*</span></label>
                            <div class="input-group has-validation mb-3">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                
                                <input type="hidden" id="apartamento_id" name="apartamento_id">
                                
                                <input type="text" class=" form-control" id="apartamento_nro_visual" disabled placeholder="Cargando...">
                                
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="tipo_vinculo">Vínculo <span class="text-danger">*</span></label>
                            <div class="input-group has-validation mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-building"></i></span>
                                <select class="form-select tipo_vinculo" aria-label="Default select example" for="tipo_vinculo" name="tipo_vinculo" id="tipo_vinculo">
                                    <option selected hidden value="">Seleccione un Vinculo</option>
                                    <option value="Propietario">Propietario</option>
                                    <option value="Habitante">Habitante</option>
                                </select>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="col-md-12 text-end">
                    <button type="button" class="btn btn-soft-secondary me-3 mb-2" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i> Cancelar
                    </button>
                    <button class="btn btn-primary px-3 shadow-sm" type="submit" id="boton_formulario_habitantes">
                        <i class="bi bi-check2-circle me-2"></i>
                        <span id="texto_boton_formulario_habitantes">Guardar Habitante</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>