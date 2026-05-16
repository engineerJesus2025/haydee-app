<div class="modal fade" id="modal_gastos" tabindex="-1" aria-labelledby="titulo-modal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5">
                    <i class="bi bi-cart-check" id="icono_titulo_modal"></i>
                    <span class="ms-2" id="titulo_modal">Registrar Gasto</span>
                </h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body rounded-bottom">
                <form id="form_gastos" name="form_cartelera" enctype="multipart/form-data">
                    <div class="container mt-4">
                        <div class="row mb-3">
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="clasificacion" class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="bi bi-tags"></i></span>
                                    <select class="form-select" name="clasificacion" id="clasificacion" required>
                                        <option value="" disabled selected>Seleccione un tipo</option>
                                        <option value="FIJO">Fijo</option>
                                        <option value="VARIABLE">Variable</option>
                                    </select>
                                    <span class="w-100 invalid-feedback"></span>
                                </div>
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="tipo_gasto_id" class="form-label fw-semibold">Tipo de Gasto <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                                    <select class="form-select" name="tipo_gasto_id" id="tipo_gasto_id" required>
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
                                <label for="descripcion_gasto" class="form-label fw-semibold">Descripción del Gasto <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                    <textarea name="descripcion_gasto" id="descripcion_gasto" class="form-control" rows="3"
                                        placeholder="Describa el detalle del gasto realizado..." minlength="10" required></textarea>
                                    <span class="w-100 invalid-feedback"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Fila 4: Proveedor + Solicitud -->
                        <div class="row mb-3">
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="proveedor_id" class="form-label fw-semibold">Proveedor <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                                    <select class="form-select" name="proveedor_id" id="proveedor_id" required>
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
                                <label for="solicitud" class="form-label fw-semibold">Solicitud <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
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
                                                Gasto <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                                <input type="date" class="form-control fecha_detalle " name="fecha_detalle[]" required>
                                                <span class="w-100 invalid-feedback"></span>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-12 mb-3">
                                            <label for="metodo_pago" class="form-label fw-semibold">Método de Pago <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
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
                                            <label for="monto" class="form-label fw-semibold">Monto <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                                <input type="text" inputmode="decimal" pattern="^[0-9]+([.,][0-9]{1,2})?$"
                                                    class="form-control  monto" name="monto[]" placeholder="Ej: 250.00" required>
                                                <span class="w-100 invalid-feedback"></span>
                                            </div>
                                            <div class="invalid-feedback" id="mensaje_monto"></div>
                                        </div>
                                        <div class="col-lg-6 col-12 mb-3 grupo_bancario d-none">
                                            <label for="referencia" class="form-label fw-semibold">Referencia/N° Comprobante <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text"><i class="bi bi-receipt"></i></span>
                                                <input type="text" class="form-control  referencia" minlength="4" maxlength="20" name="referencia[]" placeholder="Ingrese la referencia">
                                                <span class="w-100 invalid-feedback"></span>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-12 mb-3 grupo_bancario d-none">
                                            <label for="banco" class="form-label fw-semibold">Banco <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
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
                                            <label for="descripcion_detalle_gasto" class="form-label fw-semibold">Descripción del
                                                Detalle <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                                <textarea name="descripcion_detalle_gasto[]" class="form-control  descripcion_detalle_gasto" rows="3"
                                                    placeholder="Describa el detalle específico del pago..." minlength="10" required></textarea>
                                                <span class="w-100 invalid-feedback"></span>
                                            </div>
                                        </div>
                                        <div class="col-12 grupo_imagen d-none">
                                            <label for="imagen" class="form-label fw-semibold">Comprobante (Imagen) <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text"><i class="bi bi-file-earmark-image"></i></span>
                                                <input type="file" class="form-control  imagen" name="imagen[]" accept="image/*">
                                                <span class="w-100 invalid-feedback"></span>
                                            </div>
                                            <small class="text-muted fst-italic d-block mt-1 nombre_imagen_cargada"></small>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Botón para agregar detalle -->
                        <div class="row mb-4">
                            <div class="col-lg-5">
                                <button type="button" class="btn btn-soft-success w-100" id="agregar_detalle">
                                    <i class="bi bi-plus-circle"></i> Agregar Detalle de Gasto
                                </button>
                            </div>
                        </div>

                        <!-- Botón de envío -->
                        <div class="row mt-5 mb-2">
                        <div class="col-md-12 text-end"> 
                            <button type="button" class="btn btn-soft-secondary me-3" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-2"></i> Cancelar
                            </button>
                            <button class="btn btn-primary px-3 shadow-sm" type="submit" id="boton_formulario">
                                <i class="bi bi-check2-circle me-2"></i>
                                <span id="texto_boton_formulario">Guardar Gasto</span>
                            </button>
                        </div>
                    </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<template id="plantilla-detalle-gasto">
    <div class="detalle-gasto card border-0 shadow-sm p-4 mb-4">
        <h5 class="card-title text-primary fw-bold mb-3">
            <i class="bi bi-receipt-cutoff me-2"></i> Detalles del Gasto
        </h5>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-6 col-12 mb-3">
                    <label class="form-label fw-semibold">Fecha del detalle del Gasto <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" class="form-control  fecha_detalle" name="fecha_detalle[]" required>
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>

                <div class="col-lg-6 col-12 mb-3">
                    <label class="form-label fw-semibold">Método de Pago <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
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
                    <label class="form-label fw-semibold">Monto <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                        <input type="text" inputmode="decimal" pattern="^[0-9]+([.,][0-9]{1,2})?$"
                            class="form-control  monto" name="monto[]" required placeholder="Ej: 250.00">
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                    <div class="invalid-feedback" id="mensaje_monto"></div>
                </div>

                <div class="col-lg-6 col-12 mb-3 grupo_bancario d-none">
                    <label class="form-label fw-semibold">Referencia/N° Comprobante <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-receipt"></i></span>
                        <input type="text" class="form-control  referencia" name="referencia[]" placeholder="Ingrese la referencia">
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>

                <div class="col-lg-6 col-12 mb-3 grupo_bancario d-none">
                    <label class="form-label fw-semibold">Banco <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
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
                    <label class="form-label fw-semibold" for="descripcion_detalle_gasto">Descripción del Detalle <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                        <textarea name="descripcion_detalle_gasto[]" class="form-control  descripcion_detalle_gasto" rows="3"
                            placeholder="Describa el detalle específico del pago..." required></textarea>
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>

                <div class="col-12 grupo_imagen d-none">
                    <label class="form-label fw-semibold">Comprobante (Imagen) <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-file-earmark-image"></i></span>
                        <input type="file" class="form-control  imagen" name="imagen[]" accept="image/*">
                    </div>
                    <small class="text-muted fst-italic d-block mt-1 nombre_imagen_cargada"></small>
                    <span class="w-100 invalid-feedback"></span>
                </div>
            </div>
        </div>
    </div>
</template>