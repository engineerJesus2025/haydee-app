<!DOCTYPE html>
<html>

<head>
    <title>Configuración | Inicio</title>
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
                        <h2>Configuración</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row">
                        <div class="col-md-3 col-12">
                            <div class="card mb-3 shadow" title="Gestionar los Proveedores">
                                <a href="?pagina=proveedores_controlador.php&accion=inicio" class="text-decoration-none text-black">
                                    <div class="card-header text-center bg-white border-bottom-0 p-0">
                                        <i class="bi bi-truck" style="font-size: 5rem !important;"></i>
                                    </div>
                                    <div class="card-body text-center p-0 mb-3">
                                        <p class="card-title fw-bold">Gestionar Proveedores</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="card mb-3 shadow" title="Gestionar los Apartamentos">
                                <a href="?pagina=apartamentos_controlador.php&accion=inicio" class="text-decoration-none text-black">
                                    <div class="card-header text-center bg-white border-bottom-0 p-0">
                                        <i class="bi bi-building" style="font-size: 5rem !important;"></i>
                                    </div>
                                    <div class="card-body text-center p-0 mb-3">
                                        <p class="card-title fw-bold">Gestionar Apartamentos</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="card mb-3 shadow" title="Gestionar los Bancos">
                                <a href="?pagina=bancos_controlador.php&accion=inicio" class="text-decoration-none text-black">
                                    <div class="card-header text-center bg-white border-bottom-0 p-0">
                                        <i class="bi bi-piggy-bank" style="font-size: 5rem !important;"></i>
                                    </div>
                                    <div class="card-body text-center p-0 mb-3">
                                        <p class="card-title fw-bold">Gestionar Bancos</p>
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