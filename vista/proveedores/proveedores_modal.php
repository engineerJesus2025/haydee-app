<div class="modal fade" id="modal_proveedores" tabindex="-1" aria-labelledby="titulo_modal" aria_hidden="true">
     <div class="modal-dialog modal-lg">
         <div class="modal-content border-0 shadow-lg">
             <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5">
                    <i class="bi bi-bag-plus" id="icono_titulo_modal"></i>
                    <span class="ms-2" id="titulo_modal">Registrar Proveedor</span>
                </h1>
                 <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"aria-label="Close"></button>
             </div>
             <div class="modal-body rounded-bottom">
                <form id="form_proveedores" name="form_proveedores">
                    <div class="row m-3">
                        <div class="col-md-6">
                            <label for="nombre_proveedor">Nombre del Proveedor <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                                <input type="text" class="rounded-end form-control nombre_proveedor" name="nombre_proveedor" id="nombre_proveedor" placeholder="Ejem: Hidrolara"
                                       aria-label="nombre_proveedor" aria-describedby="basic-addon1" minlength="3" maxlength="50" autofocus>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="servicio">Servicio <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                                <input type="text" class="rounded-end form-control servicio" name="servicio" id="servicio" placeholder="Ejem: Agua"
                                       aria-label="servicio" aria-describedby="basic-addon1" minlength="3" maxlength="20">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row m-3">
                        <div class="col-md-6">
                            <label for="rif">RIF <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-badge"></i></span>
                                <select class="form-select" id="tipo_documento" aria-label="Default select example" for="tipo_documento" name="tipo_documento">
                                    <option selected="" hidden value="">N/A</option>
                                    <option value="V">V</option>
                                    <option value="E">E</option>
                                    <option value="J">J</option>
                                    <option value="G">G</option>
                                </select>
                                <input type="text" class="rounded-end form-control rif" name="rif" id="rif"
                                       placeholder="Ejem: 7236483" aria-label="rif" aria-describedby="basic-addon1"
                                       minlength="7" maxlength="9" disabled style="flex-grow: 7">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="direccion">Dirección <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-map"></i></span>
                                <input type="text" class="rounded-end form-control direccion" name="direccion" id="direccion" placeholder="Ejem: Calle 123, Urbanización XYZ..."
                                       aria-label="direccion" aria-describedby="basic-addon1" minlength="3" maxlength="100">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-5 mb-2">
                        <div class="col-md-12 text-end"> 
                            <button type="button" class="btn btn-soft-secondary me-3" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-2"></i> Cancelar
                            </button>
                            <button class="btn btn-primary px-3 shadow-sm" type="submit" id="boton_formulario">
                                <i class="bi bi-check2-circle me-2"></i>
                                <span id="texto_boton_formulario">Guardar Proveedor</span>
                            </button>
                        </div>
                    </div>
                </form>
             </div>
         </div>
     </div>
</div>