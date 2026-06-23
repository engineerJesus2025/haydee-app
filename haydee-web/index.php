<?php
// declare(strict_types=1); // Debatible

use haydee\enums\HttpCodigo;
use haydee\servicios\Sesiones;
use haydee\servicios\Endpoints;

require_once __DIR__ . "/vendor/autoload.php";

// Inicialización segura de la sesión web nativa
if (session_status() === PHP_SESSION_NONE) {session_start();}

$modulo = $_GET['pagina'] ?? 'login';
$accion = $_GET['accion'] ?? 'inicio';
$metodo = $_SERVER['REQUEST_METHOD'];

if (!array_key_exists($modulo, Endpoints::MAPA)) {
    error_log("[Router] [404] Modulo no definido en el mapa: '{$modulo}' | IP: " . ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'));
    http_response_code(HttpCodigo::NO_ENCONTRADO->value);
    require_once ROOT_PATH . "/vista/error/404_vista.php";
    exit;
}

$configRuta = Endpoints::MAPA[$modulo];

try {
    // Ejecuta de manera secuencial: Firewall, Autenticación Web, Anti-Flood y Método HTTP
    Sesiones::autorizarAcceso($configRuta, $metodo);
} catch (\Exception $e) {
    $codigoError = $e->getCode() ?: HttpCodigo::NO_AUTORIZADO->value;
    $mensajeErrorSeguridad = $e->getMessage();
    http_response_code($codigoError);
    
    // Misma fórmula exacta que la vista: IP + Año-Mes-Día Hora-Minuto
    $ipToken = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $incidenteId = substr(hash('sha256', $ipToken . date('Y-m-d H:i')), 0, 8);
    
    // REGISTRO DE TRÁFICO ANÓMALO CON EL ID ASOCIADO
    error_log("[REF: #{$incidenteId}] [{$codigoError}] Solicitud bloqueada. Motivo: {$mensajeErrorSeguridad} | Modulo: '{$modulo}' | Método: {$metodo} | IP: {$ipToken}");
    
    // Verificar si la petición web proviene de AJAX/Fetch
    $esAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
              (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($esAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'estatus' => false, 
            'mensaje' => $mensajeErrorSeguridad,
            'ref' => $incidenteId
        ]);
        exit;
    }

    switch ($codigoError) {
        case 401:
            $_SESSION['error_flash'] = $mensajeErrorSeguridad;
            header("Location: index.php?pagina=login");
            exit;
            break;
        case 403:
            // Limpiar la variable de sesión si el castigo ya expiró para evitar falsos positivos
            if (isset($_SESSION['rate_limit_expiracion']) && $_SESSION['rate_limit_expiracion'] !== 'permanente') {
                if (time() > (int)$_SESSION['rate_limit_expiracion']) {
                    unset($_SESSION['rate_limit_expiracion']);
                }
            }

            // Si hay un castigo activo en sesión, es un bloqueo del Firewall (Strikes)
            if (isset($_SESSION['rate_limit_expiracion'])) {
                if (file_exists(ROOT_PATH . "/vista/error/403_firewall_vista.php")) {
                    require_once ROOT_PATH . "/vista/error/403_firewall_vista.php";
                    exit;
                }
            } else {
                // Si no hay castigo, es un simple error de permisos (RBAC / Módulos)
                if (file_exists(ROOT_PATH . "/vista/error/403_vista.php")) {
                    require_once ROOT_PATH . "/vista/error/403_vista.php";
                    exit;
                }
            }
            break;
        case 429:
            if (file_exists(ROOT_PATH . "/vista/error/429_vista.php")) {
                require_once ROOT_PATH . "/vista/error/429_vista.php";
                exit;
            }
            break;
        case 404:
            if (file_exists(ROOT_PATH . "/vista/error/404_vista.php")) {
                require_once ROOT_PATH . "/vista/error/404_vista.php";
                exit;
            }
            break;
    }
    
    // Comportamiento para navegación tradicional (Vistas de error visuales)
    require_once ROOT_PATH . "/vista/error/error.php";
    
    exit;
}

$archivoControlador = ROOT_PATH . "/controlador/" . $configRuta[Endpoints::CONF_ARCHIVO];

if (is_file($archivoControlador)) {
    require_once $archivoControlador;
} else {
    error_log("[Router] [CRÍTICO] El archivo del controlador mapeado no existe fisicamente: '{$archivoControlador}'");
    http_response_code(HttpCodigo::NO_ENCONTRADO->value);
    require_once ROOT_PATH . "/vista/error/404_vista.php";
}