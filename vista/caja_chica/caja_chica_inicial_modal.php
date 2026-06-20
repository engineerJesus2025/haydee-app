<div class="modal fade" id="modal_inicializar_caja" tabindex="-1" aria-labelledby="titulo_modal_inicial" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content card shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5 fw-bold">
                    <i class="bi bi-safe2-fill"></i>
                    <span class="ms-2" id="titulo_modal_inicial">Inicializar Sistema de Caja Chica</span>
                </h1>
                <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body rounded-bottom">
                <form class="m-3" id="form_inicializar_caja">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="alert alert-warning d-flex align-items-center small shadow-sm" role="alert">
                                <i class="bi bi-exclamation-triangle-fill fs-5 me-2 flex-shrink-0"></i>
                                <div>
                                    <strong>Atención:</strong> Esta acción establecerá el fondo fijo inicial permanente para el año fiscal vigente. Asegúrese de ingresar el monto correcto asignado por la junta de condominio.
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <label for="fondo_fijo_inicial">Monto del Fondo Fijo: <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <input type="number" name="fondo_fijo" id="fondo_fijo_inicial" class="form-control" maxlength="15" minlength="0" title="Monto del fondo fijo" monto="bs" placeholder="Monto" required>
                                <span class="input-group-text icono_moneda">Bs.</span>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>

                        <div class="col-2 d-flex justify-content-center align-items-center">
                            <button class="btn btn-soft-info boton_intercambio_monto" id="boton_intercambio_monto_inicial" title="Presione para cambiar el tipo de moneda">
                                <span><i class="bi bi-arrow-left-right"></i></span>
                            </button>            
                        </div>

                        <div class="col-lg-5 col-10 text-center">
                            <label for="fondo_fijo_inicial_cambio"></label>
                            <div class="input-group mb-3">
                                <input type="text" name="fondo_fijo_cambio" id="fondo_fijo_inicial_cambio" minlength="0" value="0" class="form-control" maxlength="15" disabled title="Monto del fondo en dólares">
                                <span class="input-group-text icono_moneda">$</span>
                            </div>
                        </div>

                        <div class="col-12 mb-2">
                            <label for="descripcion_inicial">Descripción o Nota de Apertura</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <input type="text" class="form-control" name="descripcion" id="descripcion_inicial" placeholder="Ej: Fondo fijo asignado para gastos menores" aria-label="Descripción Inicial" maxlength="255">
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
                    <button class="btn btn-primary px-3 shadow-sm mb-2" type="button" id="boton_inicializar_caja">
                        <i class="bi bi-check2-circle me-2"></i> Inicializar Caja
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>