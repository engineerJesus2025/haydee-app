<!DOCTYPE html>
<html>
<head>
    <title>Año Fiscal | Inicio</title>
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

            <div class="col d-flex flex-column gris min-vh-100">

                <?php
                require_once ROOT_PATH . "/vista/componentes/header.php";
                ?>

                <main class="col ps-md-2 pt-2 mb-5 ">
                    <div class="page-header pt-3">
                        <h2>GESTIONAR AÑOS FISCALES</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <div class="row">
                                    <div class="col-12 col-sm-6 mb-4">
                                    <?php if ($permisosVista['registrar']) : ?>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_anio_fiscal" data-tooltip="true" title="Registrar Nuevo Año Fiscal" data-tooltip="true">Nuevo Año Fiscal</button>
                                    <?php endif; ?>
                                    </div>
                                    <div class="col-12 col-sm-6 mb-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" id="busqueda_global" data-tooltip="true" title="Buscar Registro" class="form-control" placeholder="Buscar año...">
                                        </div>
                                    </div>
                                </div>
                                <div id="tabla_anio_fiscal" class="tabla-sistema-haydee"></div>
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
        require_once ROOT_PATH . "/vista/anio_fiscal/anio_fiscal_modal.php";
        require_once ROOT_PATH . "/vista/anio_fiscal/anio_fiscal_detalles.php";
    ?>

    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/anio_fiscal_validar.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/anio_fiscal_ajax.js"></script>
</body>

</html>