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
                                    Esta acción establecerá el fondo fijo inicial permanente para el año fiscal vigente.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="limite_ucau_inicial">Límite Autorizado (UCAU): <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="limite_ucau" id="limite_ucau_inicial" class="form-control" step="0.01" min="0.01" value="100" required>
                                <span class="input-group-text">UCAU</span>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="ucau_valor_inicial">Valor de 1 UCAU (Bs.): <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="ucau_valor_bs" id="ucau_valor_inicial" class="form-control" step="0.0001" min="0.01" placeholder="Ej: 43.00" required>
                                <span class="input-group-text">Bs.</span>
                            </div>
                        </div>

                        <div class="col-12 mb-3 text-center bg-light p-3 rounded border">
                            <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.8rem;">Fondo Fijo Calculado</p>
                            <h3 class="text-primary fw-bold mb-0" id="fondo_inicial_calculado">0.00 Bs.</h3>
                            <input type="hidden" name="fondo_fijo" id="fondo_fijo_inicial_hidden" value="0">
                        </div>

                        <div class="col-12 mb-2">
                            <label for="descripcion_inicial">Descripción o Nota de Apertura</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <input type="text" class="form-control" name="descripcion" id="descripcion_inicial" placeholder="Ej: Fondo fijo asignado para gastos menores" maxlength="255">
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