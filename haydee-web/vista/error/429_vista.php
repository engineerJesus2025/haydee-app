<?php 
$mensaje = $mensajeErrorSeguridad ?? 'Se ha detectado actividad inusual en su cuenta. Por favor, espere un momento.';

// --- LÓGICA DE TIEMPO DINÁMICO (Exclusivo Anti-Flood 60s) ---
$tiempoTotal = 60;
$tiempoRestante = $tiempoTotal; // Fallback seguro

if (isset($_SESSION['rate_limit_expiracion']) && $_SESSION['rate_limit_expiracion'] !== 'permanente') {
    $tiempoRestante = max(1, (int)$_SESSION['rate_limit_expiracion'] - time());
}

// --- GENERACIÓN DEL ID DE INCIDENTE SEGURO ---
$ipToken = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$incidenteId = substr(hash('sha256', $ipToken . date('Y-m-d H:i')), 0, 8);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Demasiadas Peticiones | Error 429</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?php echo URL_BASE; ?>recursos/img/utils/logo-haydee.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap-icons/bootstrap-icons.min.css">
    
    <style>
        .gris { background-color: #f8f9fa; }
        .alert-warning-blur {
            background-color: rgba(255, 193, 7, 0.06) !important;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 193, 7, 0.3) !important;
        }
        .progress-bar-fluid { transition: width 1s linear !important; }
        /* Animación meditativa de respiración */
        @keyframes respiracion {
            0%   { transform: scale(0.9); opacity: 0.7; }
            50%  { transform: scale(1.15); opacity: 1; text-shadow: 0 0 20px rgba(255,193,7,0.4); }
            100% { transform: scale(0.9); opacity: 0.7; }
        }
        .icono-respirar {
            display: inline-block;
            animation: respiracion 5s ease-in-out infinite;
        }
    </style>
</head>
<body id="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <div class="col d-flex flex-column min-vh-100 gris">
                <main class="col ps-md-2 pt-2 mb-5 d-flex flex-column justify-content-center align-items-center" style="min-height: 80vh;">
                    
                    <div class="text-center animate__animated animate__fadeIn">
                        <!-- <i class="bi bi-hourglass-split text-warning" style="font-size: 7rem; line-height: 1;"></i> -->
                        <i class="bi bi-hourglass-split text-warning icono-respirar" style="font-size: 7rem; line-height: 1;"></i>

                        <h1 class="display-4 fw-bold text-dark mt-3">Error 429</h1>
                        <h2 class="mb-3 text-warning">Tráfico Excedido</h2>
                        
                        <div class="alert alert-warning-blur border-start border-4 border-warning bg-light shadow-sm d-inline-block text-start mb-4 px-4 py-3" style="max-width: 600px;">
                            <p class="mb-0 text-dark fw-medium">
                                <i class="bi bi-info-circle-fill text-warning me-2"></i>
                                <?php echo htmlspecialchars($mensaje); ?>
                            </p>
                        </div>

                        <div class="mt-2">
                            <p class="text-muted fs-5">
                                Reintentando automáticamente en <strong id="contador" class="text-danger">--</strong>...
                            </p>
                            <div class="progress" style="height: 6px; max-width: 300px; margin: 0 auto;">
                                <div id="barra-progreso" class="progress-bar progress-bar-fluid bg-warning progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 justify-content-center mt-4">
                            <button onclick="window.location.reload();" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="bi bi-arrow-clockwise me-2"></i>
                                Recargar ahora
                            </button>
                            <a href="<?php echo URL_BASE; ?>?pagina=inicio&accion=inicio" class="btn btn-primary btn-lg px-4">
                                <i class="bi bi-house-door-fill me-2"></i>
                                Ir al Inicio
                            </a>
                        </div>

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

    <script>
        let tiempoRestante = <?php echo $tiempoRestante; ?>;
        const tiempoTotalInicial = <?php echo $tiempoTotal; ?>;
        
        const elementoContador = document.getElementById('contador');
        const barraProgreso = document.getElementById('barra-progreso');

        function actualizarInterfaz() {
            // Invocación limpia usando el nuevo método del helper
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
</body>
</html>