<form id="form_detalles_gastos" name="form_detalles_gastos"
    enctype="multipart/form-data">
    <div class="container mt-4">

        <!-- Detalles del Gasto -->
        <div id="detalles-container">
            <div class="detalle-gasto card border-0 shadow-sm p-4 mb-4">
                <h5 class="card-title text-primary fw-bold mb-3">
                    <i class="bi bi-receipt-cutoff me-2"></i> Detalles del Gasto
                </h5>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="fecha_detalle" class="form-label fw-semibold">Fecha del detalle del Gasto</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" class="form-control fecha_detalle" name="fecha_detalle[]" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="metodo_pago" class="form-label fw-semibold">Método de Pago</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                                <select class="form-select metodo_pago" name="metodo_pago[]" required>
                                    <option value="" disabled selected>Seleccione un método</option>
                                    <option value="Pago Movil">Pago Movil</option>
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="Efectivo">Efectivo ($)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="monto" class="form-label fw-semibold">Monto</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                <input type="text" inputmode="decimal" pattern="^[0-9]+([.,][0-9]{1,2})?$"
                                    class="form-control monto" name="monto[]" required>
                                <span class="w-100"></span>
                            </div>
                            <div class="invalid-feedback" id="mensaje_monto"></div>
                        </div>
                        <div class="col-md-6 grupo_referencia">
                            <label for="referencia" class="form-label fw-semibold">Referencia/N° Comprobante</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-receipt"></i></span>
                                <input type="text" class="form-control referencia" name="referencia[]">
                                <span class="w-100"></span>
                            </div>
                        </div>
                        <div class="col-md-6 grupo_banco">
                            <label for="banco" class="form-label fw-semibold">Banco</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                <select class="form-select banco" name="banco[]">
                                    <option value="" disabled selected>Seleccione un banco</option>
                                    <?php foreach ($bancos as $banco): ?>
                                        <option value="<?php echo $banco["id_banco"] ?>">
                                            <?php echo $banco["nombre_banco"] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label for="descripcion_detalle" class="form-label fw-semibold">Descripción del
                                Detalle</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <textarea name="descripcion_detalle[]" class="form-control descripcion_detalle"
                                    rows="3" placeholder="Describa el detalle específico del pago..."
                                    required></textarea>
                                <span class="w-100"></span>
                            </div>
                        </div>
                        <div class="col-md-12 grupo_imagen">
                            <label for="imagen" class="form-label fw-semibold">Comprobante (Imagen)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-file-earmark-image"></i></span>
                                <input type="file" class="form-control imagen" name="imagen[]" accept="image/*">
                            </div>
                            <small id="nombre_imagen_cargada" class="text-muted fst-italic d-block mt-1"></small>
                            <button type="button"
                                class="btn btn-sm btn-outline-danger mt-2 d-none boton_eliminar_imagen">
                                <i class="bi bi-trash3"></i> Eliminar imagen cargada
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

                <!-- Botón para agregar detalle -->
        <div class="row mb-4">
            <div class="col-md-3">
                <button type="button" class="btn btn-outline-success w-100" id="agregar_detalle">
                    <i class="bi bi-plus-circle"></i> Agregar Detalle de Gasto
                </button>
            </div>
        </div>

        <!-- Botón -->
        <div class="row mt-4">
            <div class="col-md-12 text-center">
                <button class="btn btn-primary" type="submit" id="boton_formulario_detalles">Registrar</button>
            </div>
        </div>
    </div>
</form>