<div class="modal fade" id="modal_cartelera" tabindex="-1"aria-labelledby="titulo-modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content card shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5">
                    <i class="bi bi-megaphone" id="icono_titulo_modal"></i>
                    <span class="ms-2" id="titulo_modal">Registrar Publicación</span>
                </h1>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-link text-white p-1 me-3 lh-1 d-md-none" id="btn_ayuda_modal" title="Ayuda de este formulario" data-tooltip="true">
                        <i class="bi bi-question-circle-fill fs-4"></i>
                    </button>
                    
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body rounded-bottom">
                <form id="form_cartelera" enctype="multipart/form-data">
                    <div class="container mt-4">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="titulo">Título de la publicación <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-type"></i></span>
                                    <input type="text" class="rounded-end form-control" name="titulo" id="titulo" placeholder="Ingrese un título..."
                                        aria-label="titulo" minlength="3" maxlength="100" required autofocus>
                                        <span class="w-100 invalid-feedback"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="descripcion">Descripción de la publicación <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                    <textarea name="descripcion" id="descripcion" class="rounded-end form-control" rows="4"
                                        placeholder="Describe el contenido..." minlength="3" maxlength="200" required></textarea>
                                        <span class="w-100 invalid-feedback"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-lg-6 mb-4">
                                <label for="prioridad">Prioridad de la publicación <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-exclamation-triangle"></i></span>
                                    <select class="rounded-end form-select" name="prioridad" id="prioridad" required>
                                        <option value="" disabled selected>Seleccione una prioridad</option>
                                        <option value="1">Alta</option>
                                        <option value="2">Media</option>
                                        <option value="3">Baja</option>
                                    </select>
                                    <span class="w-100 invalid-feedback"></span>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-4">
                                <label for="imagen">Imagen de la publicación</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-image"></i></span>
                                    <input type="file" class="rounded-end form-control" name="imagen" id="imagen" accept="image/*">
                                    <span class="w-100 invalid-feedback"></span>
                                </div>
                                <small id="nombre_imagen_cargada" class="text-muted fst-italic d-block mt-1"></small>
                                <button type="button" id="boton_eliminar_imagen" class="btn btn-sm btn-outline-danger mt-2 d-none">
                                    <i class="bi bi-trash3"></i> Eliminar imagen cargada
                                </button>
                            </div>
                            <input type="hidden" id="nombre_usuario" value="<?php echo $_SESSION['nombre_completo']; ?>">

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
                        <span id="texto_boton_formulario">Guardar Publicación</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>