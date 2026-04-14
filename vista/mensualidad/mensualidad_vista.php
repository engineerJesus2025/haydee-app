<!DOCTYPE html>
<html>
<head>
    <title>Mensualidad | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                        <h2>GESTIONAR MENSUALIDAD</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row my-4 justify-content-center">
                        <div class="col-11">
                            <div class="card p-4 row">
                                <div class="row justify-content-end align-items-start">
                                    <div class="col-12 col-sm-6 mb-4">
                                        <?php require ROOT_PATH . "/vista/componentes/boton_nuevo.php"; ?>
                                        <p class="text-danger"></p>
                                    </div>
                                    <div class="col-12 col-sm-6 mb-4">
                                        <?php require_once ROOT_PATH . "/vista/componentes/buscador_global.php"; ?>
                                    </div>
                                </div>
                                <div id="tabla_mensualidad" class="tabla-sistema-haydee"></div>
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
        require_once ROOT_PATH . "/vista/mensualidad/mensualidad_modal.php";
    ?>
    
    <!-- Modales -->
    <div class="modal fade" id="modal_mensualidades_apartamentos" tabindex="-1" aria-labelledby="titulo_modal_mensualidad" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-bottom-0">
                    <h5 class="modal-title fw-bold" id="titulo_modal_mensualidad">
                        <i class="bi bi-buildings me-2"></i>Estado de Mensualidades por Apartamento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light p-4">
                    
                    <div class="bg-white p-3 mb-4 rounded border shadow-sm">
                        <div class="row text-center g-3 align-items-start">
                            <div class="col-md-2 border-end">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <i class="bi bi-calendar2-month text-primary me-1 fs-6"></i> Período
                                </div>
                                <h6 id="vp_periodo" class="text-dark mb-0 fw-bold">---</h6>
                            </div>
                            <div class="col-md-3 border-end">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <i class="bi bi-cash-stack text-success me-1 fs-6"></i> Monto Base
                                </div>
                                <h6 id="vp_monto_base_bs" class="text-dark mb-0 fw-bold">---</h6>
                                <span id="vp_monto_base_usd" class="text-muted" style="font-size: 0.75rem;">---</span>
                            </div>
                            <div class="col-md-2 border-end">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <i class="bi bi-currency-exchange text-info me-1 fs-6"></i> Tasa BCV
                                </div>
                                <h6 id="vp_tasa" class="text-dark mb-0 fw-bold">---</h6>
                            </div>
                            <div class="col-md-2 border-end">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <i class="bi bi-graph-up-arrow text-danger me-1 fs-6"></i> Recargo
                                </div>
                                <span id="vp_recargo" class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1 shadow-sm">---</span>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <i class="bi bi-calendar-x text-warning me-1 fs-6"></i> Día Límite
                                </div>
                                <h6 id="vp_limite" class="text-dark mb-0 fw-bold">---</h6>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div id="mensualidades_apartamentos" class="tabla-sistema-haydee m-0 border-0 rounded-bottom"></div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-white border-top d-flex justify-content-between">
                    <button type="button" id="btn_generar_reporte_modal" class="btn btn-info text-white px-4 shadow-sm" style="background-color:#3939a9;" title="Descargar Reporte de Pagos" data-tooltip="true">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Cuadro de Pagos
                    </button>
                    <button type="button" class="btn btn-secondary px-4 shadow-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/mensualidades_ajax.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/mensualidad_validar.js"></script>
</body>
</html>