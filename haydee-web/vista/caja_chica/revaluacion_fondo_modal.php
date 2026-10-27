<div class="modal fade" id="modal_revaluar_fondo" tabindex="-1" aria-labelledby="titulo_modal_revaluar" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content card shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5 fw-bold">
                    <i class="bi bi-arrow-up-circle"></i>
                    <span class="ms-2">Revaluar Fondo de Caja</span>
                </h1>
                <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body rounded-bottom">
                <form class="m-3" id="form_revaluar_fondo">
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-warning d-flex align-items-center small shadow-sm" role="alert">
                                <i class="bi bi-exclamation-triangle-fill fs-5 me-2 flex-shrink-0"></i>
                                <div>
                                    Ingrese el nuevo límite en UCAU y su valor en Bolívares. Esto recalculará el Fondo Fijo de la caja. <b>Asegúrese de ingresar físicamente el nuevo efectivo a la caja.</b>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="limite_ucau">Límite (UCAU): <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                <input type="number" name="limite_ucau" id="limite_ucau" class="form-control" step="0.01" min="0.01" placeholder="Ej: 100">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ucau_valor_bs">Valor de 1 UCAU (Bs.): <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="ucau_valor_bs" id="ucau_valor_bs" class="form-control" step="0.0001" min="0.01" placeholder="Ej: 43.00">
                                <span class="input-group-text">Bs.</span>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="nota_revaluacion">Nota o Referencia (Opcional)</label>
                            <input type="text" name="nota_revaluacion" id="nota_revaluacion" class="form-control" placeholder="Ej: Resolución Conjunta Nro. 001, Gaceta 43.140">
                        </div>
                        <div class="col-12 text-center p-3 bg-light rounded border">
                            <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.8rem;">Nuevo Fondo Fijo Calculado</p>
                            <h2 class="text-success fw-bolder mb-0" id="nuevo_fondo_calculado">0.00 Bs.</h2>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-12">
                            <label class="fw-bold mb-2"><i class="bi bi-clock-history me-1"></i> Historial de Revaluaciones</label>
                            <textarea class="form-control bg-light small" id="revaluaciones_log_text" rows="5" readonly style="resize: none; font-family: monospace;"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="col-md-12 text-end">
                    <button type="button" class="btn btn-soft-secondary me-3 mb-2" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i> Cancelar
                    </button>
                    <button class="btn btn-primary px-3 shadow-sm mb-2" type="button" id="boton_guardar_revaluacion">
                        <i class="bi bi-check2-circle me-2"></i> Aplicar Revaluación
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>