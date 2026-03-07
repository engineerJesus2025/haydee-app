<!DOCTYPE html>
<html>

<head>
    <title>Reportes Estadísticos | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once ROOT_PATH . "/vista/componentes/estilos.php";
    ?>
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
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="card mb-3 shadow" title="Reportes Estadisticos">
                                <a href="?pagina=reportes&accion=ingreso_egreso" class="text-decoration-none text-black">
                                    <div class="card-header text-center bg-white border-bottom-0 p-0">
                                        <i class="bi-graph-up" style="font-size: 5rem !important;"></i>
                                    </div>
                                    <div class="card-body text-center p-0 mb-3">
                                        <p class="card-title fw-bold">Reportes De Ingresos y Egresos</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card mb-3 shadow" title="Reportes Estadisticos">
                                <a href="?pagina=reportes&accion=habitantes" class="text-decoration-none text-black">
                                    <div class="card-header text-center bg-white border-bottom-0 p-0">
                                        <i class="bi-people-fill" style="font-size: 5rem !important;"></i>
                                    </div>
                                    <div class="card-body text-center p-0 mb-3">
                                        <p class="card-title fw-bold">Reportes De Habitantes</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </main>
                <?php                
                require_once ROOT_PATH . "/vista/componentes/script.php";
                ?>
            </div>
        </div>
    </div>
    <?php require_once ROOT_PATH . "/vista/componentes/footer.php"; ?>
</body>
</html>