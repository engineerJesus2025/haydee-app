<!DOCTYPE html>
<html>

<head>
    <title>Reportes Estadísticos | Inicio</title>
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
                        <h2>Reportes Estadísticos</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>
                    <div class="row g-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="card tarjeta-reporte h-100 shadow-sm contenedor_reportes" title="Reportes de Ingresos y Egresos">
                                <a href="?pagina=reportes&accion=ingreso_egreso" class="text-decoration-none text-reset w-100 h-100 p-4">
                                    <div class="text-center mb-3">
                                        <i class="bi bi-graph-up" style="font-size: 4rem !important;"></i>
                                    </div>
                                    <div class="text-center p-0">
                                        <p class="card-title fw-bold mb-0">Ingresos y Egresos</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card tarjeta-reporte h-100 shadow-sm contenedor_reportes" title="Reportes de Habitantes">
                                <a href="?pagina=reportes&accion=habitantes" class="text-decoration-none text-reset w-100 h-100 p-4">
                                    <div class="text-center mb-3">
                                        <i class="bi bi-people-fill" style="font-size: 4rem !important;"></i>
                                    </div>
                                    <div class="text-center p-0">
                                        <p class="card-title fw-bold mb-0">Reportes de Habitantes</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
    <?php
        require_once ROOT_PATH . "/vista/componentes/script.php";
        require_once ROOT_PATH . "/vista/componentes/footer.php";
    ?>
    <script type="text/javascript">
        document.querySelectorAll(".contenedor_reportes").forEach(div=>{
            new bootstrap.Tooltip(div, {
                placement: 'top',
                trigger: 'hover'
            });
        });
    </script>
</body>
</html>