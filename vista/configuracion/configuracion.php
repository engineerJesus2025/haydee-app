<!DOCTYPE html>
<html>

<head>
    <title>Configuracion | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body>

    <div class="container-fluid">
        <div class="row flex-nowrap ">
            <?php
            require_once "vista/componentes/sesion.php";
            require_once "vista/componentes/header.php";
            ?>

            <div class="col d-flex flex-column  min-vh-100 gris">

                <?php
                require_once "vista/componentes/navbar.php";
                ?>

                <main class="col ps-md-2 pt-2">

                    <div class="page-header pt-3">
                        <h2>CONFIGURACION</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row">
                    <?php if (Conexion::tiene_permiso(GESTIONAR_MENSUALIDAD, CONSULTAR)) : ?>
                        <div class="col-md-3 col-12">
                            <div class="card mb-3 shadow" title="Gestionar las mensualidades de los apartamentos">
                                <a href="?pagina=mensualidad_controlador.php&accion=inicio" class="text-decoration-none text-black">
                                    <div class="card-header text-center bg-white border-bottom-0 p-0">
                                        <i class="bi bi-calendar3" style="font-size: 5rem !important;"></i>
                                    </div>
                                    <div class="card-body text-center p-0 mb-3">
                                        <p class="card-title fw-bold">Mensualidad</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
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