<form action="?pagina=gastos_controlador.php&accion=guardar" method="POST" id="form_gastos" name="form_cartelera"
    enctype="multipart/form-data">
    <div class="container mt-4">

        <!-- Fila 2: Tipo + Tipo de Gasto -->
        <div class="row mb-3">
            <div class="col-lg-6 col-12 mb-3">
                <label for="clasificacion" class="form-label fw-semibold">Tipo</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-tags"></i></span>
                    <select class="form-select" name="clasificacion" id="clasificacion" required>
                        <option value="" disabled selected>Seleccione un tipo</option>
                        <option value="fijo">Fijo</option>
                        <option value="variable">Variable</option>
                    </select>
                    <span class="w-100 invalid-feedback"></span>
                </div>
            </div>
            <div class="col-lg-6 col-12 mb-3">
                <label for="tipo_gasto" class="form-label fw-semibold">Tipo de Gasto</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                    <select class="form-select" name="tipo_gasto" id="tipo_gasto" required>
                        <option value="" disabled selected>Seleccione un tipo</option>
                        <?php foreach ($tipos_gasto['datos'] as $tipo): ?>
                            <option value="<?php echo $tipo["id_tipo_gasto"] ?>"><?php echo $tipo["nombre_tipo_gasto"] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="w-100 invalid-feedback"></span>
                </div>
            </div>
        </div>

        <!-- Fila 3: Descripción general del gasto -->
        <div class="row mb-3">
            <div class="col-12">
                <label for="descripcion_gasto" class="form-label fw-semibold">Descripción del Gasto</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                    <textarea name="descripcion_gasto" id="descripcion_gasto" class="form-control" rows="3"
                        placeholder="Describa el detalle del gasto realizado..." minlength="10" required></textarea>
                    <span class="w-100 invalid-feedback"></span>
                    <small class="form-text text-danger mensaje-validacion" data-for="descripcion_gasto"></small>
                </div>
            </div>
        </div>

        <!-- Fila 4: Proveedor + Solicitud -->
        <div class="row mb-3">
            <div class="col-lg-6 col-12 mb-3">
                <label for="proveedor" class="form-label fw-semibold">Proveedor</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                    <select class="form-select" name="proveedor" id="proveedor" required>
                        <option value="" disabled selected>Seleccione un proveedor</option>
                        <?php foreach ($proveedores['datos'] as $proveedor): ?>
                            <option value="<?php echo $proveedor["id_proveedor"] ?>">
                                <?php echo $proveedor["nombre_proveedor"] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="w-100 invalid-feedback"></span>
                </div>
            </div>
            <div class="col-lg-6 col-12 mb-3">
                <label for="solicitud" class="form-label fw-semibold">Solicitud</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                    <select class="form-select" name="solicitud" id="solicitud" required>
                        <option value="" disabled selected>Seleccione una solicitud</option>
                        <?php foreach ($solicitudes_gasto['datos'] as $solicitud): ?>
                            <option value="<?php echo $solicitud["id_solicitud"] ?>">
                                <?php echo $solicitud["descripcion_necesidad"] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="w-100 invalid-feedback"></span>
                </div>
            </div>
        </div>

        <!-- Detalles del Gasto -->
        <div id="detalles-container">
            <div class="detalle-gasto card border-0 shadow-sm p-4 mb-4">
                <h5 class="card-title text-primary fw-bold mb-3">
                    <i class="bi bi-receipt-cutoff me-2"></i> Detalles del Gasto
                </h5>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-6 col-12 mb-3">
                            <label for="fecha_detalle" class="form-label fw-semibold">Fecha del detalle del
                                Gasto</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" class="form-control fecha_detalle" name="fecha_detalle[]" required>
                                <span class="w-100 invalid-feedback"></span>
                                <small class="form-text text-danger mensaje-validacion"
                                    data-for="fecha_detalle"></small>
                            </div>
                        </div>
                        <div class="col-lg-6 col-12 mb-3">
                            <label for="metodo_pago" class="form-label fw-semibold">Método de Pago</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                                <select class="form-select metodo_pago" name="metodo_pago[]" required>
                                    <option value="" disabled selected>Seleccione un método</option>
                                    <option value="Pago Movil">Pago Movil</option>
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="Efectivo">Efectivo ($)</option>
                                </select>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-lg-6 col-12 mb-3">
                            <label for="monto" class="form-label fw-semibold">Monto</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                <input type="text" inputmode="decimal" pattern="^[0-9]+([.,][0-9]{1,2})?$"
                                    class="form-control monto" name="monto[]" required>
                                <small class="w-100 form-text text-danger mensaje-validacion" data-for="monto"></small>

                                <span class="w-100"></span>
                            </div>
                            <div class="invalid-feedback" id="mensaje_monto"></div>
                        </div>
                        <div class="col-lg-6 col-12 mb-3 grupo_referencia">
                            <label for="referencia" class="form-label fw-semibold">Referencia/N° Comprobante</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-receipt"></i></span>
                                <input type="text" class="form-control referencia" minlength="4" maxlength="20" name="referencia[]">
                                <small class="w-100 form-text text-danger mensaje-validacion" data-for="referencia"></small>
                                <span class="w-100"></span>
                            </div>
                        </div>
                        <div class="col-lg-6 col-12 mb-3 grupo_banco">
                            <label for="banco" class="form-label fw-semibold">Banco</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                <select class="form-select banco" name="banco_id[]">
                                    <option value="" disabled selected>Seleccione un banco</option>
                                    <?php foreach ($bancos['datos'] as $banco): ?>
                                        <option value="<?php echo $banco["id_banco"] ?>">
                                            <?php echo $banco["nombre_banco"] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="descripcion_detalle" class="form-label fw-semibold">Descripción del
                                Detalle</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <textarea name="descripcion_detalle[]" class="form-control descripcion_detalle" rows="3"
                                    placeholder="Describa el detalle específico del pago..." minlength="10" required></textarea>
                                <span class="w-100"></span>
                                <small class="form-text text-danger mensaje-validacion" data-for="descripcion_detalle"></small>
                            </div>
                        </div>
                        <div class="col-12 grupo_imagen">
                            <label for="imagen" class="form-label fw-semibold">Comprobante (Imagen)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-file-earmark-image"></i></span>
                                <input type="file" class="form-control imagen" name="imagen[]" accept="image/*">
                            </div>
                            <small id="nombre_imagen_cargada" class="text-muted fst-italic d-block mt-1"></small>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Botón para agregar detalle -->
        <div class="row mb-4">
            <div class="col-lg-3">
                <button type="button" class="btn btn-outline-success w-100" id="agregar_detalle">
                    <i class="bi bi-plus-circle"></i> Agregar Detalle de Gasto
                </button>
            </div>
        </div>

        <!-- Botón de envío -->
        <div class="row">
            <div class="col-md-12 text-center">
                <button class="btn btn-primary px-4" type="submit" id="boton_formulario">
                    <i class="bi bi-save me-1"></i> Guardar
                </button>
            </div>
        </div>

    </div>
</form>

<template id="plantilla-detalle-gasto">
    <div class="detalle-gasto card border-0 shadow-sm p-4 mb-4">
        <h5 class="card-title text-primary fw-bold mb-3">
            <i class="bi bi-receipt-cutoff me-2"></i> Detalles del Gasto
        </h5>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-6 col-12 mb-3">
                    <label class="form-label fw-semibold">Fecha del detalle del Gasto</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" class="form-control fecha_detalle" name="fecha_detalle[]" required>
                        <span class="w-100 invalid-feedback"></span>
                        <small class="form-text text-danger mensaje-validacion" data-for="fecha_detalle"></small>
                    </div>
                </div>

                <div class="col-lg-6 col-12 mb-3">
                    <label class="form-label fw-semibold">Método de Pago</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                        <select class="form-select metodo_pago" name="metodo_pago[]" required>
                            <option value="" disabled selected>Seleccione un método</option>
                            <option value="Pago Movil">Pago Movil</option>
                            <option value="Transferencia">Transferencia</option>
                            <option value="Efectivo">Efectivo ($)</option>
                        </select>
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>

                <div class="col-lg-6 col-12 mb-3">
                    <label class="form-label fw-semibold">Monto</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                        <input type="text" inputmode="decimal" pattern="^[0-9]+([.,][0-9]{1,2})?$"
                            class="form-control monto" name="monto[]" required>
                        <span class="w-100 invalid-feedback"></span>
                        <small class="w-100 form-text text-danger mensaje-validacion" data-for="monto"></small>
                    </div>
                    <div class="invalid-feedback" id="mensaje_monto"></div>
                </div>

                <div class="col-lg-6 col-12 mb-3 grupo_referencia d-none">
                    <label class="form-label fw-semibold">Referencia/N° Comprobante</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-receipt"></i></span>
                        <input type="text" class="form-control referencia" name="referencia[]">
                        <span class="w-100 invalid-feedback"></span>
                        <small class="w-100 form-text text-danger mensaje-validacion" data-for="referencia"></small>
                    </div>
                </div>

                <div class="col-lg-6 col-12 mb-3 grupo_banco d-none">
                    <label class="form-label fw-semibold">Banco</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-bank"></i></span>
                        <select class="form-select banco" name="banco_id[]">
                            <option value="" disabled selected>Seleccione un banco</option>
                            <?php foreach ($bancos['datos'] as $banco): ?>
                                <option value="<?= $banco["id_banco"] ?>"><?= $banco["nombre_banco"] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Descripción del Detalle</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                        <textarea name="descripcion_detalle[]" class="form-control descripcion_detalle" rows="3"
                            placeholder="Describa el detalle específico del pago..." required></textarea>
                        <span class="w-100 invalid-feedback"></span>
                        <small class="form-text text-danger mensaje-validacion" data-for="descripcion_detalle"></small>

                    </div>
                </div>

                <div class="col-12 grupo_imagen d-none">
                    <label class="form-label fw-semibold">Comprobante (Imagen)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-file-earmark-image"></i></span>
                        <input type="file" class="form-control imagen" name="imagen[]" accept="image/*">
                    </div>
                    <small class="text-muted fst-italic d-block mt-1 nombre_imagen_cargada"></small>
                    <span class="w-100 invalid-feedback"></span>
                </div>
            </div>
        </div>
    </div>
</template>