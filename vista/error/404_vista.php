<?php 
require_once __DIR__ . '/../../config/config.php'; 
// Generación de ID de Incidente Seguro (para rastrear enlaces rotos en los logs)
$ipToken = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$incidenteId = substr(hash('sha256', $ipToken . date('Y-m-d H:i')), 0, 8);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Página no encontrada | Error 404</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?php echo URL_BASE; ?>recursos/img/utils/logo-haydee.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap-icons/bootstrap-icons.min.css">
    <style>
        .gris { background-color: #f8f9fa; }
        .alert-primary-blur {
            background-color: rgba(13, 110, 253, 0.06) !important;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border: 1px solid rgba(13, 110, 253, 0.3) !important;
        }
    </style>
</head>
<body id="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <div class="col d-flex flex-column min-vh-100 gris">
                <main class="col ps-md-2 pt-2 mb-5 d-flex flex-column justify-content-center align-items-center" style="min-height: 80vh;">
                    
                    <div class="text-center animate__animated animate__fadeIn">
                        <i class="bi bi-question-diamond-fill text-primary" style="font-size: 7rem; line-height: 1;"></i>
                        
                        <h1 class="display-4 fw-bold text-dark mt-3">Error 404</h1>
                        <h2 class="mb-3 text-primary">¡Ups! Página no encontrada</h2>
                        
                        <div class="alert alert-primary-blur border-start border-4 border-primary bg-light shadow-sm d-inline-block text-start mb-4 px-4 py-3" style="max-width: 600px;">
                            <p class="mb-0 text-dark fw-medium">
                                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                Parece que la página que estás buscando no existe, se ha movido o el enlace es incorrecto.
                            </p>
                        </div>

                        <div class="d-flex gap-3 justify-content-center">
                            <button onclick="window.history.back();" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="bi bi-arrow-left me-2"></i>
                                Volver atrás
                            </button>
                            <a href="<?php echo URL_BASE; ?>?pagina=inicio&accion=inicio" class="btn btn-primary btn-lg px-4">
                                <i class="bi bi-house-door-fill me-2"></i>
                                Ir al Inicio
                            </a>
                        </div>

                        <div class="mt-5">
                            <p class="text-muted small" style="font-size: 0.8rem; opacity: 0.7;">
                                <i class="bi bi-bug me-1"></i> Ref: <code class="text-secondary"><?php echo $incidenteId; ?></code>
                            </p>
                        </div>
                    </div>

                </main>
            </div>
        </div>
    </div>
    <?php require_once ROOT_PATH . "/vista/componentes/footer.php"; ?>
</body>
</html>