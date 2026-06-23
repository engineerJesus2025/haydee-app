<!DOCTYPE html>
<html>
<head>
    <title>Reportes PDF | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once ROOT_PATH . "/vista/componentes/estilos.php";
    ?>
    <link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>recursos/css/src/reportes.css">
</head>

<body id="body-pd" class="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap ">
            <?php
            require_once ROOT_PATH . "/vista/componentes/navbar.php";
            ?>

            <div class="col d-flex flex-column  min-vh-100 gris">

                <?php
                require_once ROOT_PATH . "/vista/componentes/header.php";
                ?>

                <main class="col ps-md-2 pt-2">

                    <div class="page-header pt-3">
                        <h2>Reportes PDF</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>
                    <div class="row g-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="card tarjeta-reporte h-100 shadow-sm" title="Click para ver opciones para constancias de residencia">
                                <button data-bs-toggle="modal" data-bs-target="#modal_reporte_persona" type="button"
                                    class="btn text-decoration-none text-reset w-100 h-100 p-4" id="boton_residencia" disabled>
                                    <div class="text-center mb-3">
                                        <div class="spinner-grow text-primary" role="status" style="width: 4rem; height: 4rem;">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    <div class="text-center p-0">
                                        <p class="card-title fw-bold mb-0">Constancias de Residencia</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card tarjeta-reporte h-100 shadow-sm" title="Click para ver opciones para solicitudes de solvencia">
                                <button data-bs-toggle="modal" data-bs-target="#modal_reporte_persona" type="button"
                                    class="btn text-decoration-none text-reset w-100 h-100 p-4" id="boton_solvencia" disabled>
                                    <div class="text-center mb-3">
                                        <div class="spinner-grow text-primary" role="status" style="width: 4rem; height: 4rem;">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    <div class="text-center p-0">
                                        <p class="card-title fw-bold mb-0">Solicitud de Solvencia</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card tarjeta-reporte h-100 shadow-sm" title="Click para ver opciones para el cuadro de pagos">
                                <button data-bs-toggle="modal" data-bs-target="#modal_reporte_persona" type="button" 
                                    class="btn text-decoration-none text-reset w-100 h-100 p-4" id="boton_cuadro_pagos" disabled>
                                    <div class="text-center mb-3">
                                        <div class="spinner-grow text-primary" role="status" style="width: 4rem; height: 4rem;">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>                                        
                                    </div>
                                    <div class="text-center p-0">
                                        <p class="card-title fw-bold mb-0">Cuadro de Pagos</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card tarjeta-reporte h-100 shadow-sm" title="Click para ver opciones para el cuadro de gastos">
                                <button type="button" class="btn text-decoration-none text-reset w-100 h-100 p-4" data-bs-toggle="modal"
                                    data-bs-target="#modalGastosMensual" id="boton_cuadro_gastos" disabled>
                                    <div class="text-center mb-3">
                                        <div class="spinner-grow text-primary" role="status" style="width: 4rem; height: 4rem;">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>  
                                    </div>
                                    <div class="text-center p-0">
                                        <p class="card-title fw-bold mb-0">Relación de Gastos</p>
                                    </div>
                                </button>
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
        // Modales
        require_once ROOT_PATH . "/vista/reportes/reportes_pdf/reporte_gastos_mensual_modal.php";
        require_once ROOT_PATH . "/vista/reportes/reportes_pdf/reporte_persona_modal.php";
    ?>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/reportes/reporte_constancias.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/reportes/cuadro_pagos.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/reportes/reporte_gastos.js"></script>    
</body>

</html>