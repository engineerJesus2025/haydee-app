<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

require_once "../vendor/autoload.php";
use haydee\servicios\GestorTrafico;

$endpoint = $_GET['endpoint'] ?? '';
$rutas = RUTAS_API; 

if (!array_key_exists($endpoint, $rutas) || !is_file(ROOT_PATH . "/api/" . $rutas[$endpoint])) {
    http_response_code(404);
    echo json_encode(["estatus" => false, "mensaje" => "Endpoint no encontrado"]);
    exit;
}

// EL ESCUDO INTERCEPTA LA ENTRADA 
GestorTrafico::interceptarEntrada($endpoint);

// SECUESTRO DE SALIDA
ob_start();
require_once ROOT_PATH . "/api/" . $rutas[$endpoint];
$respuestaLimpia = ob_get_clean();

// EL ESCUDO CIFRA LA SALIDA
echo GestorTrafico::interceptarSalida($respuestaLimpia);
exit;