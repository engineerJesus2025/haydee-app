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
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <?php if (Sesiones::tienePermiso(GESTIONAR_CARTELERA_VIRTUAL, REGISTRAR)) : ?>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_cartelera">Nueva Publicación</button>
                                    <?php else: ?>
                                        <div></div> <?php endif; ?>
                                    
                                    <div class="input-group" style="max-width: 300px;">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" id="busqueda_global" class="form-control" placeholder="Buscar publicación...">
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
    ?>
    
    <!-- Modales -->
    <div class="modal fade" id="modal_vista_previa" tabindex="-1" aria-labelledby="modal_vista_previa_label"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Vista Previa de la Publicación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Título:</strong> <span id="vista_titulo"></span></p>
                    <p><strong>Descripción:</strong></p>
                    <p id="vista_descripcion"></p>
                    <p><strong>Fecha:</strong> <span id="vista_fecha"></span></p>
                    <p><strong>Prioridad:</strong> <span id="vista_prioridad"></span></p>
                    <p><strong>Autor:</strong> <span id="vista_autor"></span></p>
                    <div class="text-center mt-3">
                        <img id="vista_imagen" src="" class="img-fluid border rounded"
                            style="max-height: 300px;" alt="Vista previa de la imagen"
                            onerror="this.style.display='none'; document.getElementById('mensaje_error_imagen').classList.remove('d-none');">
                        <p id="mensaje_error_imagen" class="text-danger d-none mt-2">⚠ No se pudo cargar la
                            imagen.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="modal_cartelera" tabindex="-1"aria-labelledby="titulo-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="titulo_modal">Registrar Publicación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    require_once ROOT_PATH . "/vista/cartelera_virtual/cartelera_virtual_modal.php";
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/cartelera_virtual_validar.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/cartelera_virtual_ajax.js"></script>
</body>

</html>