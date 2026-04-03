<!DOCTYPE html>
<html>
<head>
    <title>Bitácora | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once ROOT_PATH . "/vista/componentes/estilos.php"; ?>
    <!-- Estilos personalizados para las acciones -->
    <link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>recursos/css/bitacora.css">
</head>
<body id="body-pd" class="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php require_once ROOT_PATH . "/vista/componentes/navbar.php"; ?>
            <div class="col d-flex flex-column min-vh-100 gris">
                <?php require_once ROOT_PATH . "/vista/componentes/header.php"; ?>
                <main class="col ps-md-2 pt-2">
                    <div class="page-header pt-3">
                        <h2>BITÁCORA</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4" style="max-width: 300px;">
                                    <?php require_once ROOT_PATH . "/vista/componentes/buscador_global.php"; ?>
                                </div>
                                <div id="tabla_bitacora" class="tabla-sistema-haydee"></div>
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
        require_once ROOT_PATH . "/vista/bitacora/bitacora_modal.php";
    ?>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/bitacora_ajax.js"></script>
</body>
</html>