<?php 
$mensaje = $mensajeErrorSeguridad ?? 'Su acceso ha sido suspendido por políticas de seguridad.';

$tiempoRestante = 0;
$tiempoTotal = 3600; 
$esPermanente = false;
$fechaExpiracionRaw = '';

if (isset($_SESSION['rate_limit_expiracion'])) {
    if ($_SESSION['rate_limit_expiracion'] === 'permanente') {
        $esPermanente = true;
    } else {
        $tiempoRestante = max(1, (int)$_SESSION['rate_limit_expiracion'] - time());
        $tiempoTotal = ($tiempoRestante > 3600) ? 86400 : 3600; 
        // Guardamos la fecha formateada en formato MySQL para el helper de JS
        $fechaExpiracionRaw = date('Y-m-d H:i:s', (int)$_SESSION['rate_limit_expiracion']);
    }
}

// Generación de ID de Incidente Seguro
$ipToken = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$incidenteId = substr(hash('sha256', $ipToken . date('Y-m-d H:i')), 0, 8);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>IP Bloqueada | Firewall 403</title>
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
        .progress-bar-fluid { transition: width 1s linear !important; }
    </style>
</head>
<body id="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <div class="col d-flex flex-column min-vh-100 gris">
                <main class="col ps-md-2 pt-2 mb-5 d-flex flex-column justify-content-center align-items-center" style="min-height: 80vh;">
                    
                    <div class="text-center animate__animated animate__headShake">
                        <i class="bi bi-shield-lock-fill text-danger" style="font-size: 7rem; line-height: 1;"></i>
                        
                        <h1 class="display-4 fw-bold text-dark mt-3">Acceso Suspendido</h1>
                        <h2 class="mb-3 text-danger">Bloqueo de Red (IP)</h2>
                        
                        <div class="alert alert-danger-blur border-start border-4 border-danger bg-light shadow-sm d-inline-block text-start mb-4 px-4 py-3" style="max-width: 600px;">
                            <p class="mb-0 text-dark fw-medium" id="texto-notificacion">
                                <i class="bi bi-exclamation-octagon-fill text-danger me-2"></i>
                                <?php if (!$esPermanente && !empty($fechaExpiracionRaw)): ?>
                                    Acceso denegado. Su red está suspendida temporalmente hasta <span id="fecha-bloqueo" class="fw-bold text-danger" data-raw="<?php echo $fechaExpiracionRaw; ?>"><?php echo $fechaExpiracionRaw; ?></span> por políticas de seguridad.
                                <?php else: ?>
                                    <?php echo htmlspecialchars($mensaje); ?>
                                <?php endif; ?>
                            </p>
                        </div>

                        <?php if ($esPermanente): ?>
                            <div class="mt-3 p-3 bg-danger bg-opacity-10 rounded border border-danger">
                                <p class="text-danger fs-5 fw-bold mb-0">
                                    <i class="bi bi-x-circle-fill me-2"></i> Bloqueo Permanente. Contacte al administrador.
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="mt-2">
                                <p class="text-muted fs-5">
                                    La restricción se levantará automáticamente en <strong id="contador" class="text-danger">--</strong>...
                                </p>
                                <div class="progress" style="height: 6px; max-width: 300px; margin: 0 auto;">
                                    <div id="barra-progreso" class="progress-bar progress-bar-fluid bg-danger progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!$esPermanente): ?>
                        <div class="d-flex gap-3 justify-content-center mt-4">
                            <button onclick="window.location.reload();" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="bi bi-arrow-clockwise me-2"></i>
                                Recargar estado
                            </button>
                        </div>
                        <?php endif; ?>

                        <div class="mt-5">
                            <p class="text-muted small" style="font-size: 0.8rem; opacity: 0.7;">
                                <i class="bi bi-shield-check me-1"></i> Ref: <code class="text-secondary"><?php echo $incidenteId; ?></code>
                            </p>
                        </div>
                    </div>

                </main>
            </div>
        </div>
    </div>
    
    <?php require_once ROOT_PATH . "/vista/componentes/footer.php"; ?>

    <script src="<?php echo URL_BASE; ?>recursos/js/ayuda/FormatoFechas.js"></script>

    <?php if (!$esPermanente): ?>
    <script>
        let tiempoRestante = <?php echo $tiempoRestante; ?>;
        const tiempoTotalInicial = <?php echo $tiempoTotal; ?>;
        const elementoContador = document.getElementById('contador');
        const barraProgreso = document.getElementById('barra-progreso');
        const elementoFecha = document.getElementById('fecha-bloqueo');

        //  Formatear la fecha de la alerta usando tu helper personalizado
        if (elementoFecha) {
            const fechaRaw = elementoFecha.getAttribute('data-raw');
            if (FormatoFechas.esValida(fechaRaw)) {
                // Aplicamos tu máscara personalizada con texto estructurado entre corchetes
                elementoFecha.textContent = FormatoFechas.formatear(fechaRaw, 'DD [de] MMMM [del] YYYY [a las] hh:mm A');
            }
        }
        
        function actualizarInterfaz() {
            elementoContador.textContent = FormatoFechas.formatearDuracion(tiempoRestante);
            const porcentaje = Math.max(0, (tiempoRestante / tiempoTotalInicial) * 100);
            barraProgreso.style.width = porcentaje + '%';
        }

        actualizarInterfaz();

        const intervalo = setInterval(() => {
            tiempoRestante--;
            actualizarInterfaz();

            if (tiempoRestante <= 0) {
                clearInterval(intervalo);
                elementoContador.textContent = "Verificando...";
                window.location.reload(); 
            }
        }, 1000);
    </script>
    <?php endif; ?>
</body>
</html>