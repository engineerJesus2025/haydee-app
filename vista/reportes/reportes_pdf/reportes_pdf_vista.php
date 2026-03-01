<!DOCTYPE html>
<html>
<head>
    <title>Reportes PDF | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body id="body-pd" class="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap ">
            <?php
            require_once "vista/componentes/navbar.php";
            ?>

            <div class="col d-flex flex-column  min-vh-100 gris">

                <?php
                require_once "vista/componentes/header.php";
                ?>

                <main class="col ps-md-2 pt-2">

                    <div class="page-header pt-3">
                        <h2>Reportes PDF</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="card mb-3 shadow" title="Click para ver opciones para constacias de residencias">
                                <button data-bs-toggle="modal" data-bs-target="#modal_reporte_persona" type="button"
                                    class="btn text-decoration-none text-black" id="boton_residencia" disabled="">
                                    <div class="card-header text-center bg-white border-bottom-0 ">
                                        <div class="spinner-grow text-dark" role="status" style="width: 5rem; height: 5rem; z-index: 1000">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    <div class="card-body text-center p-0 mb-3">
                                        <p class="card-title fw-bold">Constancias de Residencia</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card mb-3 shadow" title="Click para ver opciones para solicitudes de solvencia">
                                <button data-bs-toggle="modal" data-bs-target="#modal_reporte_persona" type="button"
                                    class="btn text-decoration-none text-black" id="boton_solvencia" disabled="">
                                    <div class="card-header text-center bg-white border-bottom-0">
                                        <div class="spinner-grow text-dark" role="status" style="width: 5rem; height: 5rem; z-index: 1000">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    <div class="card-body text-center p-0 mb-3">
                                        <p class="card-title fw-bold">Solicitud de Solvencia</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card mb-3 shadow" title="Click para ver opciones para el cuadro de pagos">
                                <button data-bs-toggle="modal" data-bs-target="#modal_reporte_persona" type="button" class="btn text-decoration-none text-black" id="boton_cuadro_pagos" disabled="">
                                    <div class="card-header text-center bg-white border-bottom-0">
                                        <div class="spinner-grow text-dark" role="status" style="width: 5rem; height: 5rem; z-index: 1000">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>                                        
                                    </div>
                                    <div class="card-body text-center p-0 mb-3">
                                        <p class="card-title fw-bold">Cuadro de Pagos</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card mb-3 shadow" title="Click para ver opciones para el cuadro de gastos">
                                <button type="button" class="btn text-decoration-none text-black" data-bs-toggle="modal"
                                    data-bs-target="#modal_gastos_mensual" id="boton_cuadro_gastos" disabled>
                                    <div class="card-header text-center bg-white border-bottom-0">
                                        <div class="spinner-grow text-dark" role="status" style="width: 5rem; height: 5rem; z-index: 1000">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>  
                                    </div>
                                    <div class="card-body text-center p-0 mb-3">
                                        <p class="card-title fw-bold">Relación de Gastos</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php require_once "vista/reportes/reportes_pdf/reporte_gastos_mensual_modal.php"; ?>
                </main>
            </div>
        </div>
    </div>
    <!-- Componentes -->
    <?php
        require_once "vista/componentes/footer.php";
        require_once "vista/componentes/script.php";
        require_once 'vista/componentes/modal_carga.php';
    ?>
    
    <!-- Modales -->
    <div class="modal fade" id="modal_reporte_persona" tabindex="-1" aria-labelledby="titulo_modal_persona" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="titulo_modal_persona">Generar Cuadro de pagos</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <?php
                    require_once "vista/reportes/reportes_pdf/reporte_persona_modal.php";
                    ?>

                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="recursos/js/reportes/reporte_constancias.js"></script>
    <script type="text/javascript" src="recursos/js/reportes/cuadro_pagos.js"></script>
    <script type="text/javascript" src="recursos/js/reportes/reporte_gastos.js"></script>    
</body>

</html>