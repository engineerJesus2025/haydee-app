<!DOCTYPE html>
<html>
<head>
    <title>Tipos de Gastos | Inicio</title>
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

                <main class="col ps-md-2 pt-2 mb-5">
                    <div class="page-header pt-3">
                        <h2>GESTIONAR TIPOS DE GASTOS</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <div class="row">
                                    <div class="col-12 col-sm-6 mb-4">
                                    <?php if ($permisosVista['registrar']) : ?>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-tooltip="true" title="Registrar Nuevo Tipo de Gasto" data-bs-target="#modal_tipo_gasto">Nuevo Tipo de Gasto</button>
                                    <?php endif; ?>
                                    </div>
                                    <div class="col-12 col-sm-6 mb-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" id="busqueda_global" data-tooltip="true" title="Buscar Registro" class="form-control" placeholder="Buscar tipo de gasto...">
                                        </div>
                                    </div>
                                </div>
                                <div id="tabla_tipo_gasto" class="tabla-sistema-haydee"></div>
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
        require_once ROOT_PATH . "/vista/tipo_gasto/tipo_gasto_modal.php";
        require_once ROOT_PATH . "/vista/tipo_gasto/tipo_gasto_detalles.php";
    ?>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/tipo_gasto_validar.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/tipo_gasto_ajax.js"></script>
</body>
</html>