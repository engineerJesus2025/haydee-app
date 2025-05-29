<!DOCTYPE html>
<html>

<head>
    <title>Reportes PDF | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body class="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap ">
            <?php
            require_once "vista/componentes/sesion.php";
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
                        <div class="col-md-3 col-12">
                            <div class="card mb-3 shadow" title="Reportes PDF">
                                <a href="?pagina=reportes_controlador.php&accion=reportes_pdf" class="text-decoration-none text-black">
                                    <div class="card-header text-center bg-white border-bottom-0 p-0">
                                        <i class="bi bi-filetype-pdf" style="font-size: 5rem !important;"></i>
                                    </div>
                                    <div class="card-body text-center p-0 mb-3">
                                        <p class="card-title fw-bold">Reportes PDF</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                        
                    </div>
                    
                </main>
                <?php
                require_once "vista/componentes/footer.php";
                require_once "vista/componentes/script.php";
                ?>
            </div>
        </div>
    </div>
</body>

</html>