<?php 
// Permite rastrear fallos relacionales o vacíos en los logs de auditoría
$ipToken = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$incidenteId = substr(hash('sha256', $ipToken . date('Y-m-d H:i')), 0, 8);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Error en Reporte | Sistema Haydee</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?php echo URL_BASE; ?>recursos/img/utils/logo-haydee.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap-icons/bootstrap-icons.min.css">
    <style>
        .gris { background-color: #f8f9fa; }
        .alert-danger-blur {
            background-color: rgba(220, 53, 69, 0.06) !important;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border: 1px solid rgba(220, 53, 69, 0.3) !important;
        }
    </style>
</head>
<body id="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <div class="col d-flex flex-column min-vh-100 gris">
                <main class="col ps-md-2 pt-2 mb-5 d-flex flex-column justify-content-center align-items-center" style="min-height: 80vh;">
                    
                    <div class="text-center animate__animated animate__fadeIn">
                        
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 7rem; line-height: 1;"></i>
                        
                        <h1 class="display-4 fw-bold text-dark mt-3">Error de Reporte</h1>
                        <h2 class="mb-3 text-danger"><?php echo htmlspecialchars($titulo); ?></h2>
                        
                        <div class="alert alert-danger-blur border-start border-4 border-danger bg-light shadow-sm d-inline-block text-start mb-4 px-4 py-3" style="max-width: 600px;">
                            <p class="mb-0 text-dark fw-medium">
                                <i class="bi bi-info-circle-fill text-danger me-2"></i>
                                <?php echo htmlspecialchars($mensaje); ?>
                            </p>
                        </div>

                        <div class="d-flex gap-3 justify-content-center">
                            <button onclick="window.close();" class="btn btn-dark btn-lg px-4">
                                <i class="bi bi-x-circle me-2"></i>
                                Cerrar pestaña
                            </button>
                            <button onclick="window.history.back();" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="bi bi-arrow-left me-2"></i>
                                Volver atrás
                            </button>
                        </div>
                        
                        <div class="mt-5">
                            <p class="text-muted small" style="font-size: 0.8rem; opacity: 0.7;">
                                <i class="bi bi-shield-slash me-1"></i> Ref: <code class="text-secondary"><?php echo $incidenteId; ?></code>
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