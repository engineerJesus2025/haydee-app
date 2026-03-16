<?php 
require_once __DIR__ . '/../../config/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Acceso Denegado | Error 403</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?php echo URL_BASE; ?>recursos/img/utils/logo-haydee.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>recursos/css/estilos_generales.css">
</head>
<body id="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap">

            <div class="col d-flex flex-column min-vh-100 gris">
                <main class="col ps-md-2 pt-2 mb-5 d-flex flex-column justify-content-center align-items-center" style="min-height: 80vh;">
                    
                    <div class="text-center animate__animated animate__pulse">
                        <h1 class="display-1 fw-bold text-danger" style="font-size: 8rem;">403</h1>
                        
                        <h2 class="mb-3">Acceso Restringido</h2>
                        
                        <p class="lead text-muted mb-4">
                            Lo sentimos, no tienes los permisos necesarios<br>
                            para acceder a esta sección del sistema.
                        </p>

                        <div class="d-flex gap-3 justify-content-center">
                            <a href="#" class="btn btn-outline-secondary btn-lg px-4" onclick="window.history.back();">
                                <i class="bi bi-escape me-2"></i>
                                Volver atrás
                            </a>
                            <a href="<?php echo URL_BASE; ?>?pagina=inicio&accion=inicio" class="btn btn-primary btn-lg px-4">
                                <i class="bi bi-house-door-fill me-2"></i>
                                Ir al Inicio
                            </a>
                        </div>
                    </div>

                </main>
                
            </div>
        </div>
    </div>
    <?php 
    // Mismo estándar que en el 404
    require_once ROOT_PATH . "/vista/componentes/footer.php"; 
    ?>
</body>
</html>