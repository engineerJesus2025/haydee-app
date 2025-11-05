<form action="?pagina=pagos_controlador.php&accion=guardar" method="POST" id="form_pagos" name="form_pagos">
    <div class="row m-3">
        <div class="col-lg-4">
            <label for="apartamento">Apartamento</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-building"></i></span>
                <select class="border border-dark form-select apartamento_id" aria-label="Default select example" id="apartamento_id"
                    name="apartamento_id">
                    <option selected hidden value="">Seleccione un Apartamento</option>
                    <?php foreach ($registro_apartamento as $apartamento): ?>
                        <option value="<?php echo $apartamento["id_apartamento"] ?>">
                            <?php echo "Nro: " . $apartamento["nro_apartamento"] ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-lg-4">
            <label for="mensualidad">Mensualidad</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-bank2"></i></span>
                <select class="border border-dark form-select mensualidad_id" aria-label="Default select example" id="mensualidad_id"
                    name="mensualidad_id">
                    <option selected hidden value="">Escoja primero un Apartamento</option>
                </select>
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-lg-3">
            <label for="monto_mensualidad">Monto Mensualidad</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-cash-coin"></i></span>
                <input type="text" class="border border-dark form-control monto_mensualidad" name="monto_mensualidad"
                    id="monto_mensualidad" placeholder="Monto Mensualidad" aria-label="monto_mensualidad"
                    aria-describedby="basic-addon1" minlength="3" maxlength="30" readonly>
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>
    <!-- Detalles del Pago -->
    <div id="detalles_container">
        <div class="detalle-pago card shadow-sm border-primary mt-4 mb-4">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-receipt-cutoff me-2"></i> Detalles del Pago
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-sm-6">
                        <label for="fecha">Fecha</label>
                        <div class="input-group mb-3">
                            <span class="border border-primary input-group-text"><i class="bi bi-calendar-date"></i></span>
                            <input type="date" class="border border-dark form-control fecha_admin" name="fecha[]">
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <label for="tipo_pago">Método de Pago</label>
                        <div class="input-group mb-3">
                            <span class="border border-primary input-group-text"><i class="bi bi-credit-card-fill"></i></span>
                            <select class="border border-dark form-select tipo_pago_admin" name="tipo_pago[]">
                                <option selected hidden value="">Seleccione método</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Pago Movil">Pago Movil</option>
                            </select>
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-6 campo-monto d-none">
                        <label for="monto">Monto</label>
                        <div class="input-group mb-3">
                            <span class="border border-primary input-group-text"><i class="bi bi-cash-coin"></i></span>
                            <input type="text" class="border border-dark form-control monto" name="monto[]" placeholder="Monto">
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-6">
                        <label for="tasa_dolar">Tasa del Dólar</label>
                        <div class="input-group mb-3">
                            <span class="border border-primary input-group-text"><i class="bi bi-coin"></i></span>
                            <input type="text" class="border border-dark form-control tasa_dolar" name="tasa_dolar[]" placeholder="Tasa">
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-6">
                        <label for="monto_dolar">Monto Dólar</label>
                        <div class="input-group mb-3">
                            <span class="border border-primary input-group-text"><i class="bi bi-cash-coin"></i></span>
                            <input type="text" class="border border-dark form-control monto_dolar" name="monto_dolar[]"
                                placeholder="Dólar">
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-sm-6 campos-bancarios d-none">
                        <label for="referencia">Referencia</label>
                        <div class="input-group mb-3">
                            <span class="border border-primary input-group-text"><i class="bi bi-receipt"></i></span>
                            <input type="text" class="border border-dark form-control referencia" name="referencia[]"
                                placeholder="Referencia">
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 campos-bancarios d-none">
                        <label for="banco_id">Banco</label>
                        <div class="input-group mb-3">
                            <span class="border border-primary input-group-text"><i class="bi bi-bank2"></i></span>
                            <select class="border border-dark form-select banco_admin" name="banco_id[]">
                                <option selected hidden value="">Escoga el Banco</option>
                                <?php foreach ($registro_banco as $banco): ?>
                                    <option value="<?php echo $banco["id_banco"] ?>"><?php echo $banco["nombre_banco"] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                    <div class="col-lg-6 campos-bancarios d-none">
                        <label for="imagen">Imagen</label>
                        <div class="input-group mb-3">
                            <span class="border border-primary input-group-text"><i class="bi bi-image-fill"></i></span>
                            <input type="file" class="border border-dark form-control imagen" name="imagen[]">
                        </div>
                        <small class="nombre_imagen_cargada text-muted fst-italic d-block mt-1"></small>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 d-none boton_eliminar_imagen">
                            <i class="bi bi-trash3"></i> Eliminar imagen cargada
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-lg-6 mb-3 d-flex align-items-end mx-auto">
            <button type="button" class="btn btn-outline-success w-100" id="agregar_detalle">
                <i class="bi bi-plus-circle"></i> Agregar Detalle de Pago
            </button>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-lg-4">
            <label for="estado">Estado del Pago</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-check-circle-fill"></i></span>
                <select class="border border-dark form-select estado" aria-label="Default select example" id="estado" for="estado"
                    name="estado">
                    <option selected="" value="No verificado">NO VERIFICADO</option>
                </select>
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-lg-8 mb-3">
            <label for="observacion">Observacion</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-info-circle-fill"></i></span>
                <input type="text" class="border border-dark form-control observacion" name="observacion" id="observacion"
                    placeholder="Puede dejar una breve descripción" aria-label="observacion"
                    aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>

    </div>
    <div class="row m-3">
        <div class="col-md-12 text-center">
            <button class="btn btn-primary" type="submit" id="boton_formulario">Registrar</button>
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
                <div class="col-md-3">
                    <label for="fecha">Fecha</label>
                    <div class="input-group mb-3">
                        <span class="border border-primary input-group-text"><i class="bi bi-calendar-date"></i></span>
                        <input type="date" class="border border-dark form-control fecha_admin" name="fecha[]">
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="tipo_pago">Método de Pago</label>
                    <div class="input-group mb-3">
                        <span class="border border-primary input-group-text"><i class="bi bi-credit-card-fill"></i></span>
                        <select class="border border-dark form-select tipo_pago_admin" name="tipo_pago[]">
                            <option selected hidden value="">Seleccione método</option>
                            <option value="Transferencia">Transferencia</option>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Pago Movil">Pago Movil</option>
                        </select>
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
                <div class="col-md-2 campo-monto d-none">
                    <label for="monto">Monto</label>
                    <div class="input-group mb-3">
                        <span class="border border-primary input-group-text"><i class="bi bi-cash-coin"></i></span>
                        <input type="text" class="border border-dark form-control monto" name="monto[]" placeholder="Monto">
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="tasa_dolar">Tasa del Dólar</label>
                    <div class="input-group mb-3">
                        <span class="border border-primary input-group-text"><i class="bi bi-coin"></i></span>
                        <input type="text" class="border border-dark form-control tasa_dolar" name="tasa_dolar[]" placeholder="Tasa">
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="monto_dolar">Monto Dólar</label>
                    <div class="input-group mb-3">
                        <span class="border border-primary input-group-text"><i class="bi bi-cash-coin"></i></span>
                        <input type="text" class="border border-dark form-control monto_dolar" name="monto_dolar[]" placeholder="Dólar">
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 campos-bancarios d-none">
                    <label for="referencia">Referencia</label>
                    <div class="input-group mb-3">
                        <span class="border border-primary input-group-text"><i class="bi bi-receipt"></i></span>
                        <input type="text" class="border border-dark form-control referencia" name="referencia[]" placeholder="Referencia">
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
                <div class="col-md-3 campos-bancarios d-none">
                    <label for="banco_id">Banco</label>
                    <div class="input-group mb-3">
                        <span class="border border-primary input-group-text"><i class="bi bi-bank2"></i></span>
                        <select class="border border-dark form-select banco_admin" name="banco_id[]">
                            <option selected hidden value="">Escoga el Banco</option>
                            <?php foreach ($registro_banco as $banco): ?>
                                <option value="<?php echo $banco["id_banco"] ?>"><?php echo $banco["nombre_banco"] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
                <div class="col-md-6 campos-bancarios d-none">
                    <label for="imagen">Imagen</label>
                    <div class="input-group mb-3">
                        <span class="border border-primary input-group-text"><i class="bi bi-image-fill"></i></span>
                        <input type="file" class="border border-dark form-control imagen" name="imagen[]">
                        <input type="hidden" class="border border-dark imagen_actual" name="imagen_actual[]">
                    </div>
                    <small class="nombre_imagen_cargada text-muted fst-italic d-block mt-1"></small>
                    <button type="button" class="btn btn-sm btn-outline-danger mt-2 d-none boton_eliminar_imagen">
                        <i class="bi bi-trash3"></i> Eliminar imagen cargada
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>