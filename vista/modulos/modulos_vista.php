<!DOCTYPE html>
<html>
<head>
    <title>Módulos | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once ROOT_PATH . "/vista/componentes/estilos.php";
    ?>
</head>

<body id="body-pd" class="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap ">
            <?php require_once ROOT_PATH . "/vista/componentes/navbar.php"; ?>
            <div class="col d-flex flex-column min-vh-100 gris">
                <?php require_once ROOT_PATH . "/vista/componentes/header.php"; ?>
                <main class="col ps-md-2 pt-2 mb-5">
                    <div class="page-header pt-3">
                        <h2>GESTIONAR MÓDULOS</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <div class="row">
                                    <div class="col-12 col-sm-6 mb-4">
                                    <?php if ($permisosVista['registrar']) : ?>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-tooltip="true" title="Registrar Nuevo Módulo" data-bs-target="#modal_modulo">Nuevo Módulo</button>
                                    <?php endif; ?>
                                    </div>
                                    <div class="col-12 col-sm-6 mb-4">
                                        <?php require_once ROOT_PATH . "/vista/componentes/buscador_global.php"; ?>
                                    </div>
                                </div>
                                <div id="tabla_modulos" class="tabla-sistema-haydee"></div>
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
        require_once ROOT_PATH . "/vista/modulos/modulos_modal.php";
        require_once ROOT_PATH . "/vista/modulos/modulos_detalles.php";
    ?>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/modulos_validar.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/modulos_ajax.js"></script>
</body>
</html>