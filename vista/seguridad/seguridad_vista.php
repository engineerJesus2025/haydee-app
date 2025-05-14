<!DOCTYPE html>
<html>

<head>
    <title>Seguridad | Inicio</title>
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

                    <div class="container-fluid justify-content-end">
                        <a href="#" data-bs-target="#sidebar" data-bs-toggle="collapse" class="border rounded-3 p-1 text-decoration-none"><i class="bi bi-list bi-lg py-2 p-1"></i></a>
                    </div>

                    <div class="page-header pt-3">
                        <h2>SEGURIDAD</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row">
                    <?php if (Conexion::tiene_permiso(GESTIONAR_BITACORA, CONSULTAR)) : ?>
                        <div class="col-md-3 col-12">
                            <div class="card mb-3 shadow" title="Gestionar los movimientos de los Usuarios">
                                <a href="?pagina=bitacora_controlador.php&accion=inicio" class="text-decoration-none text-black">
                                    <div class="card-header text-center bg-white border-bottom-0 p-0">
                                        <i class="bi bi-arrows-move" style="font-size: 5rem !important;"></i>
                                    </div>
                                    <div class="card-body text-center p-0 mb-3">
                                        <p class="card-title fw-bold">Bitacora</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (Conexion::tiene_permiso(GESTIONAR_ROLES, CONSULTAR)) : ?>
                        <div class="col-md-3 col-12">
                            <div class="card mb-3 shadow" title="Gestionar los roles y sus accesos en el sistema">
                                <a href="?pagina=rol_controlador.php&accion=inicio" class="text-decoration-none text-black">
                                    <div class="card-header text-center bg-white border-bottom-0 p-0">
                                        <i class="bi bi-person-gear" style="font-size: 5rem !important;"></i>
                                    </div>
                                    <div class="card-body text-center p-0 mb-3">
                                        <p class="card-title fw-bold">Gestionar roles</p>
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