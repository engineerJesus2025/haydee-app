<!DOCTYPE html>
<html>
<head>
    <title>Pagos | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once ROOT_PATH . "/vista/componentes/estilos.php"; ?>
</head>
<body id="body-pd" class="body-pd">   
    <div class="container-fluid">
        <div class="row flex-nowrap ">
            <?php require_once ROOT_PATH . "/vista/componentes/navbar.php"; ?>
            <div class="col d-flex flex-column min-vh-100 gris">
                <?php require_once ROOT_PATH . "/vista/componentes/header.php"; ?>

                <main class="col ps-md-2 pt-2 mb-5">
                    <div class="page-header pt-3">
                        <h2 id="titulo_pagina">GESTIONAR PAGOS</h2>
                    </div>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4 shadow-lg">
                                <div class="row">
                                    <div class="col-12 col-sm-6 mb-4">
                                        <?php require ROOT_PATH . "/vista/componentes/boton_nuevo.php"; ?>
                                    </div>
                                    <div class="col-12 col-sm-6 mb-4">
                                        <?php require_once ROOT_PATH . "/vista/componentes/buscador_global.php"; ?>
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
                
                <div class="modal-body rounded-bottom vp-body p-4">
                    
                    <div class="vp-card p-3 mb-4 rounded border shadow-sm card-item">
                        <div class="row text-center g-3 align-items-center">
                            
                            <div class="col-md-3 border-end vp-border-color">
                                <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Apartamento</span>
                                <!-- 'text-primary' está bien para resaltar -->
                                <h4 id="vp_apartamento" class="text-primary mb-0 fw-bold">---</h4>
                            </div>

                            <div class="col-md-3 border-end vp-border-color">
                                <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Fecha de Reporte</span>
                                <h6 id="vp_fecha" class="mb-0 fw-bold">---</h6>
                            </div>

                            <div class="col-md-3 border-end vp-border-color">
                                <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Monto Cubierto</span>
                                <h6 id="vp_monto" class="mb-0 fw-bold">---</h6>
                            </div>

                            <div class="col-md-3">
                                <span class="text-muted text-uppercase fw-bold d-block mb-2" style="font-size: 0.7rem; letter-spacing: 0.5px;">Estado del Trámite</span>
                                <span id="vp_estado" class="badge bg-secondary fs-6 px-3 py-2 shadow-sm">---</span>
                            </div>

                        </div>
                    </div>

                    <div class="px-4 py-3 vp-card border rounded shadow-sm mb-4 card-item">
                        <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.75rem;"><i class="bi bi-chat-text me-1"></i> Observación / Nota</span>
                        <p id="vp_observacion" class="mb-0 fst-italic" style="font-size: 0.9rem;">---</p>
                    </div>

                    <div class="card border-0 shadow-sm vp-card">
                        <div class="vp-card-header border-bottom py-3 vp-border-color">
                            <h6 class="mb-0 fw-bold text-muted"><i class="bi bi-list-check me-2"></i>Desglose de Transferencias / Depósitos</h6>
                        </div>
                        <div class="card-body p-0">
                            <div id="tabla_detalles_pagos" class="tabla-vista-previa m-0 border-0 rounded-bottom"></div>
                        </div>
                    </div>

                </div>
                
                <div class="modal-footer vp-card border-top justify-content-center vp-border-color">
                    <button class="btn btn-soft-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i> Cerrar
                    </button>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/pagos_validar.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/pagos_ajax.js"></script>
</body>
</html>