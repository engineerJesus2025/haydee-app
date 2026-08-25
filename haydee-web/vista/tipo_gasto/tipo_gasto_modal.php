<div class="modal fade" id="modal_tipo_gasto" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content card shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5">
                    <i class="bi bi-clipboard-plus" id="icono_titulo_modal"></i>
                    <span class="ms-2" id="titulo_modal">Registrar Tipo de Gasto</span>
                </h1>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-link text-white p-1 me-3 lh-1 d-md-none" id="btn_ayuda_modal" title="Ayuda de este formulario" data-tooltip="true">
                        <i class="bi bi-question-circle-fill fs-4"></i>
                    </button>
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body rounded-bottom p-4">
                <form id="form_tipo_gasto" name="form_tipo_gasto">
                    <div class="row mb-4">
                        <div class="col-12">
                            <label for="nombre_tipo_gasto" class="form-label fw-bold">Partida Principal (Tipo de Gasto) <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <span class="input-group-text"><i class="bi bi-tags"></i></span>
                                <input type="text" class="form-control nombre_tipo_gasto" name="nombre_tipo_gasto" id="nombre_tipo_gasto" placeholder="Ejem: Servicios Públicos" minlength="3" maxlength="50" required autofocus>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-list-nested me-2"></i>Conceptos Asociados</h6>
                            <button type="button" class="btn btn-sm btn-soft-success" id="btn_agregar_concepto">
                                <i class="bi bi-plus-lg me-1"></i> Añadir Concepto
                            </button>
                        </div>
                        <div class="card-body p-3" id="contenedor_conceptos">
                            <div class="row align-items-start mb-2 fila-concepto">
                                <input type="hidden" name="id_concepto[]" value="">
                                <div class="col-sm-10 col-9">
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="bi bi-arrow-return-right text-muted"></i></span>
                                        <input type="text" class="form-control" name="nombre_concepto[]" placeholder="Ejem: Servicio de Agua" required minlength="3" maxlength="100">
                                        <span class="w-100 invalid-feedback"></span>
                                    </div>
                                </div>
                                <div class="col-sm-2 col-3 text-end mt-1">
                                    <button type="button" class="btn btn-soft-danger btn-sm btn_eliminar_concepto" title="Quitar Concepto">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="col-md-12 text-end">
                    <button type="button" class="btn btn-soft-secondary me-3" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i> Cancelar
                    </button>
                    <button class="btn btn-primary px-4 shadow-sm" type="submit" id="boton_formulario">
                        <i class="bi bi-check2-circle me-2"></i>
                        <span id="texto_boton_formulario">Guardar Catálogo</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="plantilla_concepto">
    <div class="row align-items-start mb-2 fila-concepto">
        <input type="hidden" name="id_concepto[]" value="">
        <div class="col-sm-10 col-9">
            <div class="input-group has-validation">
                <span class="input-group-text"><i class="bi bi-arrow-return-right text-muted"></i></span>
                <input type="text" class="form-control" name="nombre_concepto[]" placeholder="Ejem: Servicio de Agua" required minlength="3" maxlength="100">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-sm-2 col-3 text-end mt-1">
            <button type="button" class="btn btn-soft-danger btn-sm btn_eliminar_concepto" title="Quitar Concepto">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
</template>