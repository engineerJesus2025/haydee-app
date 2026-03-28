<?php use haydee\ayuda\Sesiones; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Pagos | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once ROOT_PATH . "/vista/componentes/estilos.php"; ?>
</head>

<body id="body-pd" class="body-pd">
    <input type="text" hidden id="permiso_eliminar" value="<?php echo Sesiones::tienePermiso(GESTIONAR_PAGOS, ELIMINAR) ? 1 : 0; ?>">
    <input type="text" hidden id="permiso_modificar" value="<?php echo Sesiones::tienePermiso(GESTIONAR_PAGOS, MODIFICAR) ? 1 : 0; ?>">
    
    <div class="container-fluid">
        <div class="row flex-nowrap ">
            <?php require_once ROOT_PATH . "/vista/componentes/navbar.php"; ?>
            <div class="col d-flex flex-column min-vh-100 gris">
                <?php require_once ROOT_PATH . "/vista/componentes/header.php"; ?>

                <main class="col ps-md-2 pt-2 mb-5">
                    <div class="page-header pt-3">
                        <h2>GESTIONAR PAGOS</h2>
                    </div>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <div class="row">
                                    <div class="col-12 col-sm-6 mb-4">
                                    <?php if (Sesiones::tienePermiso(GESTIONAR_PAGOS, REGISTRAR)) : ?>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-tooltip="true" title="Registrar Nuevo Pago" data-bs-target="#modal_pagos">Nuevo Pago</button>
                                    <?php endif; ?>
                                    </div>
                                    <div class="col-12 col-sm-6 mb-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" id="busqueda_global" data-tooltip="true" title="Buscar Registro" class="form-control" placeholder="Buscar pago...">
                                        </div>
                                    </div>
                                </div>
                                <div id="tabla_pagos" class="tabla-sistema-haydee"></div>
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
        require_once ROOT_PATH . "/vista/pagos/pagos_modal.php";
    ?>

    <!-- Modales -->
    <div class="modal fade" id="modal_vista_previa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                
                <div class="modal-header bg-primary text-white border-bottom-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-receipt me-2"></i>Detalles del Recibo de Pago
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                
                <div class="modal-body bg-light p-4">
                    
                    <div class="bg-white p-3 mb-4 rounded border shadow-sm">
                        <div class="row text-center g-3 align-items-center">
                            
                            <div class="col-md-3 border-end">
                                <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Apartamento</span>
                                <h4 id="vp_apartamento" class="text-primary mb-0 fw-bold">---</h4>
                            </div>

                            <div class="col-md-3 border-end">
                                <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Fecha de Reporte</span>
                                <h6 id="vp_fecha" class="text-dark mb-0 fw-bold">---</h6>
                            </div>

                            <div class="col-md-3 border-end">
                                <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Monto Cubierto</span>
                                <h6 id="vp_monto" class="text-dark mb-0 fw-bold">---</h6>
                            </div>

                            <div class="col-md-3">
                                <span class="text-muted text-uppercase fw-bold d-block mb-2" style="font-size: 0.7rem; letter-spacing: 0.5px;">Estado del Trámite</span>
                                <span id="vp_estado" class="badge bg-secondary fs-6 px-3 py-2 shadow-sm">---</span>
                            </div>

                        </div>
                    </div>

                    <div class="px-4 py-3 bg-white border rounded shadow-sm mb-4">
                        <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.75rem;"><i class="bi bi-chat-text me-1"></i> Observación / Nota</span>
                        <p id="vp_observacion" class="mb-0 text-dark fst-italic" style="font-size: 0.9rem;">---</p>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-list-check me-2"></i>Desglose de Transferencias / Depósitos</h6>
                        </div>
                        <div class="card-body p-0">
                            <div id="tabla_detalles_pagos" class="tabla-sistema-haydee m-0 border-0 rounded-bottom"></div>
                        </div>
                    </div>

                </div>
                
                <div class="modal-footer bg-white border-top justify-content-center">
                    <button class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/pagos_validar.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/pagos_ajax.js"></script>
</body>
</html>