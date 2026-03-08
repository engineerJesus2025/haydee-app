<form id="form_pagos" name="form_pagos">
    <div class="row m-3">
        <div class="col-lg-4 mb-3">
            <label class="form-label fw-bold" for="apartamento_id">Apartamento <spam class="text-danger">*</spam></label>
            <div class="input-group has-validation">
                <span class="border border-primary input-group-text"><i class="bi bi-building"></i></span>
                <select class="border border-dark form-select" id="apartamento_id" name="apartamento_id">
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
            <label class="form-label fw-bold" for="mensualidad_id">Mensualidad a Pagar <spam class="text-danger">*</spam></label>
            <div class="input-group has-validation">
                <span class="border border-primary input-group-text"><i class="bi bi-bank2"></i></span>
                <select class="border border-dark form-select" id="mensualidad_id" name="mensualidad_id" disabled>
                    <option selected hidden value="">Escoja primero un Apartamento</option>
                </select>
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-lg-3 mb-3">
            <label class="form-label fw-bold" for="monto_mensualidad">Deuda Total <spam class="text-danger">*</spam></label>
            <div class="input-group">
                <span class="border border-primary input-group-text"><i class="bi bi-cash-coin"></i></span>
                <input type="text" class="border border-dark form-control bg-white" id="monto_mensualidad" placeholder="0.00" readonly>
            </div>
        </div>
    </div>

    <div id="detalles_container" class="mx-3">
        <div class="detalle-pago card shadow-sm border-primary mt-4 mb-4">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-receipt-cutoff me-2"></i> 1) Detalles del Pago
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label">Fecha de Transacción <spam class="text-danger">*</spam></label>
                        <div class="input-group has-validation">
                            <span class="border border-primary input-group-text"><i class="bi bi-calendar-date"></i></span>
                            <input type="date" class="border border-dark form-control fecha_admin">
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label">Método de Pago <spam class="text-danger">*</spam></label>
                        <div class="input-group has-validation">
                            <span class="border border-primary input-group-text"><i class="bi bi-credit-card-fill"></i></span>
                            <select class="border border-dark form-select tipo_pago_admin">
                                <option selected hidden value="">Seleccione método</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Pago Movil">Pago Móvil</option>
                                <option value="Efectivo">Efectivo</option>
                            </select>
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-3 campo-monto d-none">
                        <label class="form-label">Monto (Bs) <spam class="text-danger">*</spam></label>
                        <div class="input-group has-validation">
                            <span class="border border-primary input-group-text">Bs</span>
                            <input type="text" class="border border-dark form-control monto" placeholder="0.00">
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-3">
                        <label class="form-label">Tasa BCV <spam class="text-danger">*</spam></label>
                        <div class="input-group has-validation">
                            <span class="border border-primary input-group-text">Bs/$</span>
                            <input type="text" class="border border-dark form-control tasa_dolar" placeholder="0.00">
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-3">
                        <label class="form-label">Equivalente ($) <spam class="text-danger">*</spam></label>
                        <div class="input-group has-validation">
                            <span class="border border-primary input-group-text">$</span>
                            <input type="text" class="border border-dark form-control monto_dolar bg-light" placeholder="0.00" readonly>
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                </div>
                <div class="row campos-bancarios d-none">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label">Nro. Referencia <spam class="text-danger">*</spam></label>
                        <div class="input-group has-validation">
                            <span class="border border-primary input-group-text"><i class="bi bi-123"></i></span>
                            <input type="text" class="border border-dark form-control referencia" placeholder="Últimos 4-6 dígitos">
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <label class="form-label">Banco Emisor <spam class="text-danger">*</spam></label>
                        <div class="input-group has-validation">
                            <span class="border border-primary input-group-text"><i class="bi bi-bank"></i></span>
                            <select class="border border-dark form-select banco_admin">
                                <option selected hidden value="">Escoja el Banco</option>
                                <?php foreach ($registro_banco as $banco): ?>
                                    <option value="<?php echo $banco["id_banco"] ?>"><?php echo $banco["nombre_banco"] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                    <div class="col-lg-5 mb-3">
                        <label class="form-label">Comprobante (Capture) <spam class="text-danger">*</spam></label>
                        <div class="input-group has-validation">
                            <span class="border border-primary input-group-text"><i class="bi bi-image-fill"></i></span>
                            <input type="file" class="border border-dark form-control imagen" accept=".jpg, .jpeg, .png">
                        </div>
                        <small class="nombre_imagen_cargada text-primary fw-bold d-block mt-1"></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row m-3">
        <div class="col-12 text-center border-bottom pb-4 mb-4">
            <button type="button" class="btn btn-outline-success" id="agregar_detalle">
                <i class="bi bi-plus-circle"></i> Agregar otro pago a esta misma mensualidad
            </button>
        </div>
    </div>

    <div class="row m-3">
        <?php if (!$esPropietario): ?>
            <div class="col-lg-4 mb-3">
                <label class="form-label fw-bold" for="estado">Estado de Verificación <spam class="text-danger">*</spam></label>
                <div class="input-group has-validation">
                    <span class="border border-primary input-group-text"><i class="bi bi-check-circle-fill"></i></span>
                    <select class="border border-dark form-select" id="estado" name="estado">
                        <option value="No verificado">NO VERIFICADO</option>
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
                <span class="border border-primary input-group-text"><i class="bi bi-info-circle-fill"></i></span>
                <input type="text" class="border border-dark form-control" id="observacion" placeholder="Ej: Pago de la mitad de la deuda...">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>

    <div class="row m-3 mt-4">
        <div class="col-md-12 text-center">
            <button class="btn btn-primary px-5 py-2 fs-5" type="submit" id="boton_formulario">Registrar Pago</button>
        </div>
    </div>
</form>

<template id="plantilla-detalle-pago">
    <div class="detalle-pago card shadow-sm border-primary mt-4 mb-4">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="bi bi-receipt-cutoff me-2"></i> Detalles del Pago
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label">Fecha de Transacción <spam class="text-danger">*</spam></label>
                    <div class="input-group has-validation">
                        <span class="border border-primary input-group-text"><i class="bi bi-calendar-date"></i></span>
                        <input type="date" class="border border-dark form-control fecha_admin">
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label">Método de Pago <spam class="text-danger">*</spam></label>
                    <div class="input-group has-validation">
                        <span class="border border-primary input-group-text"><i class="bi bi-credit-card-fill"></i></span>
                        <select class="border border-dark form-select tipo_pago_admin">
                            <option selected hidden value="">Seleccione método</option>
                            <option value="Transferencia">Transferencia</option>
                            <option value="Pago Movil">Pago Móvil</option>
                            <option value="Efectivo">Efectivo</option>
                        </select>
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-3 campo-monto d-none">
                    <label class="form-label">Monto (Bs) <spam class="text-danger">*</spam></label>
                    <div class="input-group has-validation">
                        <span class="border border-primary input-group-text">Bs</span>
                        <input type="text" class="border border-dark form-control monto" placeholder="0.00">
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-3">
                    <label class="form-label">Tasa BCV <spam class="text-danger">*</spam></label>
                    <div class="input-group has-validation">
                        <span class="border border-primary input-group-text">Bs/$</span>
                        <input type="text" class="border border-dark form-control tasa_dolar" placeholder="0.00">
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-3">
                    <label class="form-label">Equivalente ($) <spam class="text-danger">*</spam></label>
                    <div class="input-group has-validation">
                        <span class="border border-primary input-group-text">$</span>
                        <input type="text" class="border border-dark form-control monto_dolar bg-light" placeholder="0.00" readonly>
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
            </div>
            <div class="row campos-bancarios d-none">
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label">Nro. Referencia <spam class="text-danger">*</spam></label>
                    <div class="input-group has-validation">
                        <span class="border border-primary input-group-text"><i class="bi bi-123"></i></span>
                        <input type="text" class="border border-dark form-control referencia" placeholder="Nro de recibo">
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <label class="form-label">Banco <spam class="text-danger">*</spam></label>
                    <div class="input-group has-validation">
                        <span class="border border-primary input-group-text"><i class="bi bi-bank"></i></span>
                        <select class="border border-dark form-select banco_admin">
                            <option selected hidden value="">Escoja el Banco</option>
                            <?php foreach ($registro_banco as $banco): ?>
                                <option value="<?php echo $banco["id_banco"] ?>"><?php echo $banco["nombre_banco"] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
                <div class="col-lg-5 mb-3">
                    <label class="form-label">Comprobante (Capture) <spam class="text-danger">*</spam></label>
                    <div class="input-group has-validation">
                        <span class="border border-primary input-group-text"><i class="bi bi-image-fill"></i></span>
                        <input type="file" class="border border-dark form-control imagen" accept=".jpg, .jpeg, .png">
                    </div>
                    <small class="nombre_imagen_cargada text-primary fw-bold d-block mt-1"></small>
                </div>
            </div>
        </div>
    </div>
</template>