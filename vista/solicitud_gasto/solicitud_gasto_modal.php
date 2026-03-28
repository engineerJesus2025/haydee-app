<div class="modal fade" id="modal_solicitud_gasto" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="titulo_modal">Registrar
                    Solicitud de
                    Gasto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form_solicitud_gasto" enctype="multipart/form-data">
                    <div class="container mt-4">

                        <!-- Fila 0: Selector de Mes y Año -->
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label for="selector_mes">Mes del presupuesto <spam class="text-danger">*</spam></label>
                                <select class="border border-dark form-select" id="selector_mes" required>
                                    <option selected="" hidden value="">Seleccione mes</option>
                                </select>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="selector_anio">Año del presupuesto <spam class="text-danger">*</spam></label>
                                <select class="border border-dark form-select" id="selector_anio" required>
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
                                    <label for="fecha_reporte">Fecha de la solicitud <spam class="text-danger">*</spam></label>
                                    <div class="input-group">
                                        <span class="border border-primary input-group-text"><i class="bi bi-calendar-event"></i></span>
                                        <input type="date" class="border border-dark rounded-end form-control" name="fecha_reporte" id="fecha_reporte" required>
                                        <span class="w-100 invalid-feedback"></span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nombre_solicitante">Nombre del Solicitante <spam class="text-danger">*</spam></label>
                                    <div class="input-group">
                                        <span class="border border-primary input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="border border-dark rounded-end form-control" name="nombre_solicitante" id="nombre_solicitante" placeholder="Ejem: Raul" minlength="3" maxlength="40" required>
                                        <span class="w-100 invalid-feedback"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Monto + Prioridad -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="monto_estimado">Monto Estimado <spam class="text-danger">*</spam></label>
                                    <div class="input-group">
                                        <span class="border border-primary input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" step="0.01" min="0" class="border border-dark rounded-end form-control" name="monto_estimado" id="monto_estimado" maxlength="12" placeholder="0" required>
                                        <span class="w-100 invalid-feedback"></span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="prioridad">Prioridad <spam class="text-danger">*</spam></label>
                                    <div class="input-group">
                                        <span class="border border-primary input-group-text"><i class="bi bi-exclamation-triangle"></i></span>
                                        <select class="border border-dark rounded-end form-select" name="prioridad" id="prioridad" required>
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
                                    <label for="descripcion_necesidad">Descripción <spam class="text-danger">*</spam></label>
                                    <div class="input-group">
                                        <span class="border border-primary input-group-text"><i class="bi bi-card-text"></i></span>
                                        <textarea name="descripcion_necesidad" id="descripcion_necesidad" class="border border-dark rounded-end form-control" rows="3" placeholder="Describa la solicitud..." minlength="3" maxlength="60" required></textarea>
                                        <span class="w-100 invalid-feedback"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón -->
                            <div class="row mt-4">
                                <div class="col-md-12 text-center">
                                    <button class="btn btn-primary" type="submit" id="boton_formulario">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>