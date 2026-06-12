<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-HTTP-Method-Override");

// FIREWALL PREFLIGHT
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "../vendor/autoload.php";
use haydee\enums\HttpCodigo;
use haydee\servicios\GestorTrafico;
use haydee\modelo\SeguridadIP;

$endpoint = $_GET['endpoint'] ?? '';
$rutas = RUTAS_API;

if (!array_key_exists($endpoint, $rutas) || !is_file(ROOT_PATH . "/api/" . $rutas[$endpoint])) {
    http_response_code(HttpCodigo::NO_ENCONTRADO->value);
    echo json_encode(["estatus" => false, "mensaje" => "Endpoint no encontrado"]);
    exit;
}

$seguridad = null;

try {
    // ==================== ESCUDO DE LISTA NEGRA ====================
    $ipCliente = $_SERVER['REMOTE_ADDR'];
    $seguridad = new SeguridadIP();
    $seguridad->set_ip($ipCliente);
    $acceso = $seguridad->verificarListaAcceso();

    if (!$acceso['estatus']) {
        http_response_code($acceso['codigo_http']);
        echo json_encode(["estatus" => false, "mensaje" => $acceso['mensaje']]);
        exit;
    }

    // INTERCEPTA Y DESCIFRA LA ENTRADA
    GestorTrafico::interceptarEntrada($endpoint);

    // ==================== SALIDA ====================
    ob_start();
    require_once ROOT_PATH . "/api/" . $rutas[$endpoint];
    $respuestaLimpia = ob_get_clean();

    // EL ESCUDO INTERCEPTA Y CIFRA LA SALIDA EXITOSA
    echo GestorTrafico::interceptarSalida($respuestaLimpia);

} catch (\Throwable $e) {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    error_log("Colapso Crítico en API Gateway: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    
    $errorJson = json_encode(["estatus" => false, "mensaje" => "Ocurrió un error crítico en el Gateway de la API."]);
    echo GestorTrafico::interceptarSalida($errorJson);

} finally {
    if ($seguridad) {
        $seguridad->cerrar();
    }
    GestorTrafico::limpiarArchivosTemporales();
    exit;
}