<div class="modal fade" id="modal_reponer_caja" tabindex="-1" aria-labelledby="titulo_modal_reponer_caja" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content card shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5">
                    <i class="bi bi-arrow-repeat" id="icono_titulo_modal_repocicion"></i>
                    <span class="ms-2" id="titulo_modal_reponer_caja">Reponer Caja</span>
                </h1>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body rounded-bottom">
                <div class="m-3">
                    <div class="alert alert-info d-flex align-items-center small shadow-sm mb-4" role="alert">
                        <i class="bi bi-info-circle-fill fs-5 me-2 flex-shrink-0"></i>
                        <div>
                            El sistema calculará automáticamente el total a reponer basándose en los gastos pendientes y generará el registro contable correspondiente.
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 text-center mb-3">
                           <p class="text-muted mb-1">Total de Fondos Gastados (Pendientes):</p>
                           <h3 class="text-danger fw-bold" id="fondos_gastados_reponer">0.00 Bs.</h3>
                           <p class="text-muted small" id="fondos_gastados_reponer_usd">0.00 $</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="col-md-12 text-end">
                   <button type="button" class="btn btn-soft-secondary me-3 mb-2" data-bs-dismiss="modal">
                      <i class="bi bi-x-circle me-2"></i> Cancelar
                   </button>
                   <button class="btn btn-primary px-3 shadow-sm mb-2" type="button" id="boton_guardar_reposicion">
                       <i class="bi bi-check2-circle me-2"></i>
                       <span>Procesar Reposición</span>
                   </button>
                </div>
            </div>
        </div>
    </div>
</div>