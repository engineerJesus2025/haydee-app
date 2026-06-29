<div class="modal fade" id="modal_pagos" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content card shadow-lg">
            <div class="modal-header card-header bg-primary text-white fw-bold d-flex align-items-center justify-content-between">
                <h1 class="modal-title fs-5 mb-0 d-flex align-items-center">
                    <i class="bi bi-cash-stack" id="icono_titulo_modal"></i>
                    <span class="ms-2" id="titulo_modal"><?php echo $esPropietario ? 'Reportar Pago' : 'Registrar Pago'; ?></span>
                </h1>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-link text-white p-1 me-3 lh-1 d-md-none" id="btn_ayuda_modal" title="Ayuda de este formulario" data-tooltip="true">
                        <i class="bi bi-question-circle-fill fs-4"></i>
                    </button>
                    
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body rounded-bottom">
                <form id="form_pagos" name="form_pagos">
                    <div class="row m-3">
                        <div class="col-lg-4 mb-3">
                            <label class="form-label fw-bold" for="apartamento_id">Apartamento <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <span class="  input-group-text"><i class="bi bi-building"></i></span>
                                <select class=" form-select" id="apartamento_id" name="apartamento_id">
                                    <option selected hidden value="">Seleccione un Apartamento</option>
                                    <?php foreach ($registro_apartamento as $apartamento): ?>
                                        <option value="<?php echo $apartamento["id_apartamento"] ?>">
                                            Nro: <?php echo $apartamento["nro_apartamento"] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-lg-5 mb-3">
                            <label class="form-label fw-bold" for="mensualidad_id">Mensualidad a Pagar <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <span class="  input-group-text"><i class="bi bi-bank2"></i></span>
                                <select class="  form-select" id="mensualidad_id" name="mensualidad_id" disabled>
                                    <option selected hidden value="">Escoja primero un Apartamento</option>
                                </select>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label class="form-label fw-bold" for="monto_mensualidad">Deuda Total <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="  input-group-text"><i class="bi bi-cash-coin"></i></span>
                                <input type="text" class="  form-control " id="monto_mensualidad" placeholder="0.00" readonly>
                            </div>
                        </div>
                    </div>

                    <div id="detalles_container" class="mx-3">
                        <div class="detalle-pago card shadow-sm  mt-4 mb-4">
                            <div class="card-header bg-primary text-white fw-bold">
                                <i class="bi bi-receipt-cutoff me-2"></i> 1) Detalles del Pago
                            </div>
                            <div class="card-body p-4">
    
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Fecha de Transacción <span class="text-danger">*</span></label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                                            <input type="date" class="form-control fecha_pago" name="fecha_pago[]">
                                            <span class="invalid-feedback"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Método de Pago <span class="text-danger">*</span></label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-credit-card-fill"></i></span>
                                            <select class="form-select tipo_pago" name="tipo_pago[]">
                                                <option selected hidden value="">Seleccione método</option>
                                                <option value="TRANSFERENCIA">Transferencia</option>
                                                <option value="PAGO MOVIL">Pago Móvil</option>
                                                <option value="EFECTIVO">Efectivo</option>
                                            </select>
                                            <span class="invalid-feedback"></span>
                                        </div>
                                    </div>

                                    <!-- El capture ahora tiene un tercio completo del modal en PC, espacio de sobra -->
                                    <div class="col-12 mb-3 campos-bancarios d-none">
                                        <label class="form-label text-primary fw-bold"><i class="bi bi-magic me-1"></i> Subir Comprobante <span class="text-danger">*</span></label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text border-primary text-primary"><i class="bi bi-image-fill"></i></span>
                                            <input type="file" class="form-control imagen border-primary" accept=".jpg, .jpeg, .png">
                                            <span class="invalid-feedback"></span>
                                        </div>
                                        <small class="nombre_imagen_cargada text-muted d-block mt-1"></small>
                                    </div>
                                </div>

                                <div class="row campos-bancarios d-none mt-2">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Banco Emisor <span class="text-danger">*</span></label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                            <select class="form-select banco_id" name="banco_id[]">
                                                <option selected hidden value="">Escoja el Banco</option>
                                                <?php foreach ($registro_banco as $banco): ?>
                                                    <option value="<?php echo $banco["id_banco"] ?>" data-codigo="<?php echo $banco["codigo"] ?>">
                                                        <?php echo $banco["nombre_banco"] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span class="invalid-feedback"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Nro. Referencia <span class="text-danger">*</span></label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-123"></i></span>
                                            <input type="text" class="form-control referencia" name="referencia[]" placeholder="Introduzca el número de operación completo">
                                            <span class="invalid-feedback"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row campo-monto d-none mt-2">
                                    <!-- Cada campo se lleva un col-md-4 limpio, pero sobre el 100% del modal. Espacio gigante garantizado -->
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Monto (Bs) <span class="text-danger">*</span></label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text fw-bold">Bs</span>
                                            <input type="text" class="form-control monto" placeholder="0.00">
                                            <span class="invalid-feedback"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Tasa BCV <span class="text-danger">*</span></label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text text-muted">Bs/$</span>
                                            <input type="text" class="form-control tasa_dolar" placeholder="0.00">
                                            <span class="invalid-feedback"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Equivalente ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text text-success fw-bold">$</span>
                                            <input type="text" class="form-control monto_usd_visual" placeholder="0.00" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row m-3">
                        <div class="col-12 text-center -bottom pb-4 mb-4">
                            <button type="button" class="btn btn-soft-success" id="agregar_detalle">
                                <i class="bi bi-plus-circle me-2"></i> Agregar otro pago a esta misma mensualidad
                            </button>
                        </div>
                    </div>

                    <div class="row m-3">
                        <?php if (!$esPropietario): ?>
                            <div class="col-lg-4 mb-3">
                                <label class="form-label fw-bold" for="estado">Estado del Pago <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="  input-group-text"><i class="bi bi-check-circle-fill"></i></span>
                                    <select class="  form-select" id="estado" name="estado">
                                        <option value="PENDIENTE">Pendiente</option>
                                        <option value="PROCESADO">PROCESADO (Aprobado)</option>
                                        <option value="RECHAZADO">RECHAZADO</option>
                                    </select>
                                    <span class="w-100 invalid-feedback"></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="<?php echo $esPropietario ? 'col-lg-12' : 'col-lg-8'; ?> mb-3">
                            <label class="form-label fw-bold" for="observacion">Nota / Observación</label>
                            <div class="input-group has-validation">
                                <span class="  input-group-text"><i class="bi bi-info-circle-fill"></i></span>
                                <input type="text" class="  form-control" id="observacion" placeholder="Ej: Pago de la mitad de la deuda...">
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
                    <button class="btn btn-primary px-3 shadow-sm mb-2" type="submit" id="boton_formulario">
                        <i class="bi bi-check2-circle me-2"></i>
                        <span id="texto_boton_formulario">Guardar Pago</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<template id="template_detalle_pago">
    <div class="detalle-pago card shadow-sm  mt-4 mb-4">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="bi bi-receipt-cutoff me-2"></i> Detalles del Pago
        </div>
        <div class="card-body">
    <!-- Datos Iniciales y Capture -->
    <div class="row align-items-end">
        <div class="col-lg-4 col-md-6 mb-3">
            <label class="form-label">Fecha de Transacción <span class="text-danger">*</span></label>
            <div class="input-group has-validation">
                <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                <input type="date" class="form-control fecha_pago" name="fecha_pago[]">
                <span class="invalid-feedback"></span>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
            <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
            <div class="input-group has-validation">
                <span class="input-group-text"><i class="bi bi-credit-card-fill"></i></span>
                <select class="form-select tipo_pago" name="tipo_pago[]">
                    <option selected hidden value="">Seleccione método</option>
                    <option value="TRANSFERENCIA">Transferencia</option>
                    <option value="PAGO MOVIL">Pago Móvil</option>
                    <option value="EFECTIVO">Efectivo</option>
                </select>
                <span class="invalid-feedback"></span>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 mb-3 campos-bancarios d-none">
            <label class="form-label text-primary fw-bold"><i class="bi bi-magic me-1"></i> Subir Capture <span class="text-danger">*</span></label>
            <div class="input-group has-validation">
                <input type="file" class="form-control imagen border-primary" accept=".jpg, .jpeg, .png">
                <span class="invalid-feedback"></span>
            </div>
            <small class="nombre_imagen_cargada text-muted d-block mt-1"></small>
        </div>
    </div>

    <!-- Datos Bancarios -->
    <div class="row campos-bancarios d-none">
        <div class="col-lg-6 col-md-6 mb-3">
            <label class="form-label">Banco Emisor <span class="text-danger">*</span></label>
            <div class="input-group has-validation">
                <span class="input-group-text"><i class="bi bi-bank"></i></span>
                <select class="form-select banco_id" name="banco_id[]">
                    <option selected hidden value="">Escoja el Banco</option>
                    <?php foreach ($registro_banco as $banco): ?>
                        <!-- Inyectamos el atributo data-codigo -->
                        <option value="<?php echo $banco["id_banco"] ?>" data-codigo="<?php echo $banco["codigo"] ?>">
                            <?php echo $banco["nombre_banco"] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="invalid-feedback"></span>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 mb-3">
            <label class="form-label">Nro. Referencia <span class="text-danger">*</span></label>
            <div class="input-group has-validation">
                <span class="input-group-text"><i class="bi bi-123"></i></span>
                <input type="text" class="form-control referencia" name="referencia[]" placeholder="Últimos 4-6 dígitos">
                <span class="invalid-feedback"></span>
            </div>
        </div>
    </div>

    <!-- Desglose Monetario -->
    <div class="row campo-monto d-none">
        <div class="col-lg-4 col-md-4 mb-3">
            <label class="form-label">Monto (Bs) <span class="text-danger">*</span></label>
            <div class="input-group has-validation">
                <span class="input-group-text">Bs</span>
                <input type="text" class="form-control monto" placeholder="0.00">
                <span class="invalid-feedback"></span>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 mb-3">
            <label class="form-label">Tasa BCV <span class="text-danger">*</span></label>
            <div class="input-group has-validation">
                <span class="input-group-text">Bs/$</span>
                <input type="text" class="form-control tasa_dolar" placeholder="0.00">
                <span class="invalid-feedback"></span>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 mb-3">
            <label class="form-label">Equivalente ($) <span class="text-danger">*</span></label>
            <div class="input-group has-validation">
                <span class="input-group-text">$</span>
                <input type="text" class="form-control monto_usd_visual" placeholder="0.00" readonly>
                <span class="invalid-feedback"></span>
            </div>
        </div>
    </div>
</div>
    </div>
</template>