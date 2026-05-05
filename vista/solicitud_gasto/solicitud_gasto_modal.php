<div class="modal fade" id="modal_solicitud_gasto" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5">
                    <i class="bi bi-send-plus" id="icono_titulo_modal"></i>
                    <span class="ms-2" id="titulo_modal">Registrar Solicitud de
                    Gasto</span>
                </h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body rounded-bottom">
                <form id="form_solicitud_gasto" enctype="multipart/form-data">
                    <div class="container mt-4">

                        <!-- Fila 0: Selector de Mes y Año -->
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label for="selector_mes">Mes del presupuesto <span class="text-danger">*</span></label>
                                <select class="form-select" id="selector_mes" required>
                                    <option selected="" hidden value="">Seleccione mes</option>
                                </select>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="selector_anio">Año del presupuesto <span class="text-danger">*</span></label>
                                <select class="form-select" id="selector_anio" required>
                                    <option selected="" hidden value="">Seleccione año</option>
                                </select>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>

                        <!-- Fila 1: Info presupuesto -->
                        <div class="row mb-3" id="info_presupuesto" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <strong>Presupuesto total:</strong> $ <span id="presupuesto_total">-</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Disponible:</strong> $ <span id="presupuesto_disponible">-</span>
                            </div>
                        </div>

                        <!-- INPUT OCULTO que se llena dinámicamente con el ID del presupuesto -->
                        <input type="hidden" name="presupuesto_id" id="presupuesto_id" required>
                        <input type="hidden" name="estado" value="pendiente">

                        <!-- Fila 2+: Resto del formulario (oculto hasta que haya presupuesto) -->
                        <div id="campos_formulario_completo" style="display: none;">

                            <!-- Fecha + Nombre -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_reporte">Fecha de la solicitud <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                        <input type="date" class="rounded-end form-control" name="fecha_reporte" id="fecha_reporte" required>
                                        <span class="w-100 invalid-feedback"></span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nombre_solicitante">Nombre del Solicitante <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="rounded-end form-control" name="nombre_solicitante" id="nombre_solicitante" placeholder="Ejem: Raul" minlength="3" maxlength="40" required>
                                        <span class="w-100 invalid-feedback"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Monto + Prioridad -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="monto_estimado">Monto Estimado <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" step="0.01" min="0" class="rounded-end form-control" name="monto_estimado" id="monto_estimado" maxlength="12" placeholder="0" required>
                                        <span class="w-100 invalid-feedback"></span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="prioridad">Prioridad <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-exclamation-triangle"></i></span>
                                        <select class="rounded-end form-select" name="prioridad" id="prioridad" required>
                                            <option value="" disabled selected>Seleccione prioridad</option>
                                            <option value="1">Alta</option>
                                            <option value="2">Media</option>
                                            <option value="3">Baja</option>
                                        </select>
                                        <span class="w-100 invalid-feedback"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="row mb-3">
                                <div class="col-md-12 mb-3">
                                    <label for="descripcion_necesidad">Descripción <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                        <textarea name="descripcion_necesidad" id="descripcion_necesidad" class="rounded-end form-control" rows="3" placeholder="Describa la solicitud..." minlength="3" maxlength="60" required></textarea>
                                        <span class="w-100 invalid-feedback"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón -->
                            <div class="row mt-5 mb-2">
                                <div class="col-md-12 text-end"> 
                                    <button type="button" class="btn btn-soft-secondary me-3" data-bs-dismiss="modal">
                                        <i class="bi bi-x-circle me-2"></i> Cancelar
                                    </button>
                                    <button class="btn btn-primary px-3 shadow-sm" type="submit" id="boton_formulario">
                                        <i class="bi bi-check2-circle me-2"></i>
                                        <span id="texto_boton_formulario">Guardar Solicitud</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>