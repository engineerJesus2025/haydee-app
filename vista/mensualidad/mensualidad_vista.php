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
                                <div class="row justify-content-end align-items-center">
                                    <div class="col-12 col-sm-6 mb-4" hidden>
                                        <?php if ($permisosVista['registrar']) : ?>
                                        <button class="btn btn-primary my-2" id="boton_registrar" type="button" data-bs-toggle="modal" data-bs-target="#modal_mensualidad" data-tooltip="true" title="Registrar Nueva mensualidad">Nueva Mensualidad</button>
                                        <p class="text-danger"></p>
                                        <?php endif; ?>
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
                        <div class="row text-center g-3">
                            <div class="col-md-2 border-end">
                                <span class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Período</span>
                                <h6 id="vp_periodo" class="text-primary mt-1 mb-0 fw-bold">---</h6>
                            </div>
                            <div class="col-md-3 border-end">
                                <span class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Monto Base</span>
                                <h6 id="vp_monto_base_bs" class="text-dark mt-1 mb-0 fw-bold">---</h6>
                                <span id="vp_monto_base_usd" class="text-muted" style="font-size: 0.75rem;">---</span>
                            </div>
                            <div class="col-md-2 border-end">
                                <span class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Tasa BCV Aplicada</span>
                                <h6 id="vp_tasa" class="text-dark mt-1 mb-0 fw-bold">---</h6>
                            </div>
                            <div class="col-md-3 border-end">
                                <span class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Recargo por Mora</span>
                                <h6 id="vp_recargo" class="text-danger mt-1 mb-0 fw-bold">---</h6>
                            </div>
                            <div class="col-md-2">
                                <span class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Día Límite</span>
                                <h6 id="vp_limite" class="text-dark mt-1 mb-0 fw-bold">---</h6>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div id="mensualidades_apartamentos" class="tabla-sistema-haydee m-0 border-0 rounded-bottom"></div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-white border-top justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/mensualidades_ajax.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/mensualidad_validar.js"></script>
</body>
</html>