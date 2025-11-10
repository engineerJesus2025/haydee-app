<form action="?pagina=solicitud_gasto_controlador.php&accion=guardar" method="POST" id="form_solicitud_gasto" enctype="multipart/form-data">
    <div class="container mt-4">

        <!-- Fila 0: Selector de Mes y Año -->
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label for="selector_mes">Mes del presupuesto</label>
                <select class="form-select" id="selector_mes" required>
                    <option selected="" hidden="" value="">Seleccione mes</option>
                </select>
                <span class="w-100 invalid-feedback"></span>
            </div>
            <div class="col-md-6 mb-3">
                <label for="selector_anio">Año del presupuesto</label>
                <select class="form-select" id="selector_anio" required>
                    <option selected="" hidden="" value="">Seleccione año</option>
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
                    <label for="fecha">Fecha de la solicitud</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" class="form-control" name="fecha" id="fecha" required>
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nombre">Nombre del Solicitante</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" name="nombre" id="nombre" required>
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
            </div>

            <!-- Monto + Prioridad -->
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label for="monto_estimado">Monto Estimado</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                        <input type="number" step="0.01" min="0" class="form-control" name="monto" id="monto_estimado" required>
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="prioridad">Prioridad</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-exclamation-triangle"></i></span>
                        <select class="form-select" name="prioridad" id="prioridad" required>
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
                    <label for="descripcion">Descripción</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                        <textarea name="descripcion" id="descripcion" class="form-control" rows="3" placeholder="Describa la solicitud..." required></textarea>
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
