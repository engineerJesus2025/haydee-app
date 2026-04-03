<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gastos | Inicio</title>
    <?php
    require_once ROOT_PATH . "/vista/componentes/estilos.php";
    ?>
</head>

<body id="body-pd" class="body-pd">
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
                        <h2>GESTIONAR GASTOS</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <div class="row">
                                    <div class="col-12 col-sm-6 mb-4">
                                    <?php if ($permisosVista['registrar']) : ?>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-tooltip="true" title="Registrar Nuevo Gasto"
                                                data-bs-target="#modal_gastos">Nuevo Gasto</button>
                                    <?php endif; ?>
                                    </div>
                                    <div class="col-12 col-sm-6 mb-4">
                                        <?php require_once ROOT_PATH . "/vista/componentes/buscador_global.php"; ?>
                                    </div>
                                </div>

                                <div id="tabla_gastos"></div>
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
        require_once ROOT_PATH . "/vista/gastos/gastos_modal.php";
    ?>
    
    <!-- Modales -->
    <div class="modal fade" id="modal_vista_previa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                
                <div class="modal-header bg-primary text-white border-bottom-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-cart-check me-2"></i>Detalles del Gasto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                
                <div class="modal-body bg-light p-4">
                    
                    <div class="bg-white p-3 mb-4 rounded border shadow-sm">
                        <div class="row text-center g-3 align-items-center">
                            
                            <div class="col-md-4 border-end">
                                <span class="text-muted text-uppercase fw-bold d-block mb-2" style="font-size: 0.7rem; letter-spacing: 0.5px;">Clasificación</span>
                                <span id="vp_clasificacion" class="badge fs-6 px-3 py-2 shadow-sm">---</span>
                            </div>

                            <div class="col-md-4 border-end">
                                <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Categoría</span>
                                <h6 id="vp_tipo_gasto" class="text-dark mb-0 fw-bold">---</h6>
                            </div>

                            <div class="col-md-4">
                                <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Proveedor Asociado</span>
                                <h6 id="vp_proveedor" class="text-primary mb-0 fw-bold">---</h6>
                            </div>

                        </div>
                    </div>

                    <div class="px-4 py-3 bg-white border rounded shadow-sm mb-4">
                        <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.75rem;"><i class="bi bi-card-text me-1"></i> Descripción General</span>
                        <p id="vp_descripcion" class="mb-0 text-dark fst-italic" style="font-size: 0.9rem;">---</p>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-list-check me-2"></i>Desglose de Pagos / Facturas</h6>
                        </div>
                        <div class="card-body p-0">
                            <div id="tabla_detalles_gastos" class="tabla-sistema-haydee m-0 border-0 rounded-bottom"></div>
                        </div>
                    </div>

                </div>
                
                <div class="modal-footer bg-white border-top justify-content-center">
                    <button class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
                
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal_vista_previa_detalles" tabindex="-1" aria-labelledby="modal_vista_previa_label" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Información del Detalle de Gasto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Fecha:</strong> <span id="vista_fecha_detalles"></span></p>
                    <p><strong>Monto:</strong> <span id="vista_monto_detalles"></span></p>
                    <p><strong>Metodo de Pago:</strong> <span id="vista_metodo_pago_detalles"></span></p>
                    <p><strong>Banco:</strong> <span id="vista_nombre_banco_detalles"></span></p>
                    <p><strong>Referencia:</strong> <span id="vista_referencia_detalles"></span></p>
                    <p><strong>Descripcion:</strong> <span id="vista_descripcion_detalles"></span></p>

                    <div class="text-center mt-3">
                        <img id="vista_imagen_detalles" src="" class="img-fluid border rounded"
                            style="max-height: 300px;" alt="Vista previa de la imagen"
                            onerror="this.style.display='none'; document.getElementById('mensaje_error_imagen_detalles').classList.remove('d-none');">
                        <p id="mensaje_error_imagen_detalles" class="text-danger d-none mt-2">⚠ No se pudo cargar la
                            imagen.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/gastos_validar.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/gastos_ajax.js"></script>
</body>
</html>