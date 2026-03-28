<!DOCTYPE html>
<html>

<head>
    <title>Notificaciones | Inicio</title>
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
                        <h2>NOTIFICACIONES</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">                        
                                    <div class="input-group" style="max-width: 300px;">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" id="busqueda_global" class="form-control" placeholder="Buscar registro...">
                                    </div>
                                </div>
                                <div id="tabla_notificaciones" class="tabla-sistema-haydee"></div>
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
        // Modales
        require_once ROOT_PATH . "/vista/notificaciones/notificaciones_detalles.php";
    ?>

    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/notificaciones_ajax.js"></script>
</body>
</html>