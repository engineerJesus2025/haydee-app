<?php
//  Cabeceras estrictas para API (CORS y JSON)
header("Access-Control-Allow-Origin: *"); // cambiar en produccion
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// Requerir el autoload
require_once "../vendor/autoload.php";

// Capturar el endpoint solicitado
$endpoint = $_GET['endpoint'] ?? '';

$rutas = RUTAS_API; 

//  Verificamos si el endpoint existe en nuestro mapa
if (array_key_exists($endpoint, $rutas)) {
    
    $archivo_endpoint = ROOT_PATH . "/api/" . $rutas[$endpoint];
    
    // Verificamos que el archivo físico realmente exista
    if(is_file($archivo_endpoint)) {
        require_once $archivo_endpoint;
    } else {
        // El endpoint está en rutas_api.php, pero olvidaron crear el archivo físico
        http_response_code(500);
        echo json_encode([
            "estatus" => false, 
            "mensaje" => "Error interno: Archivo del endpoint faltante."
        ]);
    }

} else {
    // Intentaron acceder a un endpoint no registrado
    http_response_code(404);
    echo json_encode([
        "estatus" => false, 
        "mensaje" => "Endpoint no encontrado o no válido."
    ]);
}

// Finalizar la ejecución
exit;
?>