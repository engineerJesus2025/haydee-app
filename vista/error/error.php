<?php 
// Valores por defecto por si la vista se invoca directamente por error
$codigo = $codigoError ?? 500;
$mensaje = $mensajeErrorSeguridad ?? 'El sistema ha interceptado una anomalía o un fallo interno inesperado.';

// Adaptamos los componentes visuales según la gravedad del código de error
switch ($codigo) {
    case 500:
        $colorClase = 'danger';
        $subtitulo = 'Fallo Interno del Servidor';
        $icono = 'bi-hdd-network-fill';
        break;
    case 400:
        $colorClase = 'warning';
        $subtitulo = 'Solicitud Incorrecta (Bad Request)';
        $icono = 'bi-patch-exclamation-fill';
        break;
    default:
        $colorClase = 'secondary';
        $subtitulo = 'Incidente Inesperado';
        $icono = 'bi-exclamation-triangle-fill';
        break;
}

// --- GENERACIÓN DEL ID DE INCIDENTE SEGURO ---
$ipToken = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$incidenteId = substr(hash('sha256', $ipToken . date('Y-m-d H:i')), 0, 8);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Fallo del Sistema | Error <?php echo htmlspecialchars((string)$codigo); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?php echo URL_BASE; ?>recursos/img/utils/logo-haydee.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap-icons/bootstrap-icons.min.css">
    <style>
        .gris { background-color: #f8f9fa; }
        /* Efecto blur adaptativo según la clase de peligro */
        .alert-blur {
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            background-color: rgba(var(--bs-<?php echo $colorClase; ?>-rgb), 0.06) !important;
            border: 1px solid rgba(var(--bs-<?php echo $colorClase; ?>-rgb), 0.3) !important;
        }
    </style>
</head>
<body id="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <div class="col d-flex flex-column min-vh-100 gris">
                <main class="col ps-md-2 pt-2 mb-5 d-flex flex-column justify-content-center align-items-center" style="min-height: 80vh;">
                    
                    <div class="text-center animate__animated animate__headShake">
                        
                        <i class="bi <?php echo $icono; ?> text-<?php echo $colorClase; ?>" style="font-size: 7rem; line-height: 1;"></i>
                        
                        <h1 class="display-4 fw-bold text-dark mt-3">Error <?php echo htmlspecialchars((string)$codigo); ?></h1>
                        <h2 class="mb-3 text-<?php echo $colorClase; ?>"><?php echo $subtitulo; ?></h2>
                        
                        <div class="alert alert-blur border-start border-4 border-<?php echo $colorClase; ?> bg-light shadow-sm d-inline-block text-start mb-4 px-4 py-3" style="max-width: 600px;">
                            <p class="mb-0 text-dark fw-medium">
                                <?php echo htmlspecialchars($mensaje); ?>
                            </p>
                        </div>

                        <div class="d-flex gap-3 justify-content-center">
                            <button class="btn btn-outline-secondary btn-lg px-4" onclick="window.history.back();">
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