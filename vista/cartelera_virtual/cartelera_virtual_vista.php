<?php use haydee\ayuda\Sesiones; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cartelera Virtual | Inicio</title>
    <?php
    require_once ROOT_PATH . "/vista/componentes/estilos.php";
    ?>
</head>

<body id="body-pd" class="body-pd">
    <input type="text" hidden id="permiso_eliminar"
        value="<?php echo Sesiones::tienePermiso(GESTIONAR_CARTELERA_VIRTUAL, ELIMINAR) ?>">
    <input type="text" hidden id="permiso_modificar"
        value="<?php echo Sesiones::tienePermiso(GESTIONAR_CARTELERA_VIRTUAL, MODIFICAR) ?>">
    <div class="container-fluid">
        <div class="row flex-nowrap">

            <?php
            require_once ROOT_PATH . "/vista/componentes/navbar.php";
            ?>

            <div class="col d-flex flex-column  min-vh-100 gris">

                <?php
                require_once ROOT_PATH . "/vista/componentes/header.php";
                ?>

                <main class="col ps-md-2 pt-2 mb-5">

                    <div class="page-header pt-3">
                        <h2>GESTIONAR CARTELERA VIRTUAL</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <div class="row">
                                    <div class="col-12 col-sm-6 mb-4">
                                    <?php if (Sesiones::tienePermiso(GESTIONAR_CARTELERA_VIRTUAL, REGISTRAR)) : ?>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-tooltip="true" title="Registrar Nueva Publicación" data-bs-target="#modal_cartelera">Nueva Publicación</button>
                                    <?php endif; ?>
                                    </div>
                                    <div class="col-12 col-sm-6 mb-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" id="busqueda_global" data-tooltip="true" title="Buscar Registro" class="form-control" placeholder="Buscar publicación...">
                                        </div>
                                    </div>
                                </div>
                                <div id="tabla_cartelera_virtual" class="tabla-sistema-haydee"></div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
    <!-- Componentes -->
    <?php
        require_once ROOT_PATH . "/vista/componentes/footer.php";
        require_once ROOT_PATH . "/vista/componentes/script.php";
        require_once ROOT_PATH . "/vista/componentes/modal_carga.php";
        require_once ROOT_PATH . "/vista/componentes/boton_ayuda.php";
        // Modales
        require_once ROOT_PATH . "/vista/cartelera_virtual/cartelera_virtual_modal.php";
    ?>
    
    <!-- Modales -->
    <div class="modal fade" id="modal_vista_previa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                
                <div class="modal-header bg-primary text-white border-bottom-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-megaphone me-2"></i>Detalles de la Publicación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-0 bg-light">
                    
                    <div class="bg-white p-4 border-bottom shadow-sm">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">Título</span>
                                <h4 id="vista_titulo" class="text-primary mb-0 fw-bold text-wrap" style="word-break: break-word;">---</h4>
                            </div>
                            <span id="vista_prioridad" class="badge fs-6 px-3 py-2 shadow-sm text-nowrap">---</span>
                        </div>
                    </div>

                    <div class="px-4 py-3 bg-white border-bottom">
                        <div class="row text-center text-md-start">
                            <div class="col-md-6 mb-2 mb-md-0 border-md-end">
                                <div class="d-flex align-items-center justify-content-center justify-content-md-start text-muted">
                                    <i class="bi bi-person-fill me-2 text-primary"></i>
                                    <span class="fw-semibold me-1">Autor:</span>
                                    <span id="vista_autor" class="fw-bold text-dark">---</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-center justify-content-md-start text-muted ms-md-3">
                                    <i class="bi bi-calendar-event me-2 text-primary"></i>
                                    <span class="fw-semibold me-1">Fecha:</span>
                                    <span id="vista_fecha" class="fw-bold text-dark">---</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="contenedor_imagen" class="text-center bg-white border-bottom p-4" style="display: none;">
                        <img id="vista_imagen" src="" class="img-fluid border rounded shadow-sm" style="max-height: 400px; object-fit: contain;" alt="Vista previa de la imagen" onerror="this.style.display='none'; document.getElementById('mensaje_error_imagen').classList.remove('d-none');">
                        <p id="mensaje_error_imagen" class="text-danger d-none mt-2 mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> ⚠ No se pudo cargar la imagen.
                        </p>
                    </div>

                    <div class="p-4 bg-light">
                        <h6 class="fw-bold text-secondary mb-3">
                            <i class="bi bi-justify-left me-2"></i>Descripción
                        </h6>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4 bg-white rounded">
                                <div id="vista_descripcion" class="text-dark card-content-text text-wrap" style="font-size: 1rem; line-height: 1.7; word-break: break-word; white-space: pre-wrap;">---</div>
                            </div>
                        </div>
                    </div>

                </div>
                
                <div class="modal-footer bg-white border-top-0 justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/cartelera_virtual_validar.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/cartelera_virtual_ajax.js"></script>
</body>

</html>