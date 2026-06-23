<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-HTTP-Method-Override");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "../vendor/autoload.php";
use haydee\enums\HttpCodigo;
use haydee\servicios\GestorTrafico;
use haydee\servicios\Endpoints; 
use haydee\servicios\Sesiones;

$endpoint = $_GET['endpoint'] ?? '';

if (!array_key_exists($endpoint, Endpoints::MAPA_API)) {
    GestorTrafico::abortarConCifrado(
        ["estatus" => false, "mensaje" => "Endpoint no autorizado o inexistente."], 
        HttpCodigo::NO_ENCONTRADO->value
    );
}

$configRuta = Endpoints::MAPA_API[$endpoint];
$rutaCompleta = ROOT_PATH . "/api/" . $configRuta[Endpoints::CONF_ARCHIVO];

try {
    // ESCUDOS Y AUTORIZACION
    Sesiones::autorizarAccesoAPI($configRuta);

    // DESCIFRADO CRIPTOGRAFICO
    GestorTrafico::interceptarEntrada($endpoint);

    // Identidad
    $identidad = Sesiones::$usuarioLogueado;
    $rolUsuario = strtolower($identidad['rol'] ?? $identidad['nombre_rol'] ?? '');
    $esPropietario = ($rolUsuario === 'propietario');
    $esAdministrador = ($rolUsuario === 'administrador');
    $correoUsuario = $identidad['correo'] ?? '';

    // Protocolo y Método
    $metodoHttp = $_SERVER['REQUEST_METHOD'];
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $metodoSobreescrito = $headers['X-HTTP-Method-Override'] ?? $_POST['_method'] ?? $_GET['_method'] ?? null;
    
    if (!empty($metodoSobreescrito)) {
        $metodoHttp = strtoupper($metodoSobreescrito);
    }

    // Payload Unificado
    $datosPeticion = ($metodoHttp === 'GET') ? $_GET : $_POST;

    // Soporte para Application/JSON 
    if (empty($datosPeticion) && in_array($metodoHttp, ['POST', 'PUT', 'DELETE'])) {
        $jsonCrudo = file_get_contents('php://input');
        $datosDecodificados = json_decode($jsonCrudo, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($datosDecodificados)) {
            $datosPeticion = $datosDecodificados;
        }
    }

    $operacion = $datosPeticion['operacion'] ?? '';

    // CONTROLADOR Y CIFRADO
    ob_start();
    require_once $rutaCompleta;
    $respuestaLimpia = ob_get_clean();

    echo GestorTrafico::interceptarSalida($respuestaLimpia);

} catch (\Exception $e) {
    if (ob_get_level() > 0) ob_end_clean();

    $mensajeOriginal = $e->getMessage();
    $codigoHttp = $e->getCode() ?: HttpCodigo::ERROR_INTERNO->value;
    error_log("Colapso Critico en API Gateway: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine() . ". HTTP: " . $codigoHttp);

    // Intentamos decodificar el mensaje por si viene serializado desde el Validador
    $datosDecodificados = json_decode($mensajeOriginal, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($datosDecodificados)) {
        // Es una excepcion estructurada compleja (trae sub-errores de campos)
        $payloadError = array_merge(['estatus' => false], $datosDecodificados);
    } else {
        // Es una excepción de texto plano comun
        $payloadError = [
            'estatus' => false,
            'mensaje' => $mensajeOriginal
        ];
    }

    // El Gateway centraliza la salida criptográfica del error
    GestorTrafico::abortarConCifrado($payloadError, $codigoHttp);
} catch (\Throwable $e) {
    if (ob_get_level() > 0) ob_end_clean();
    error_log("Colapso Critico en API Gateway: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
    GestorTrafico::abortarConCifrado(
        ["estatus" => false, "mensaje" => "Ocurrio un error interno en el servidor."], 
        HttpCodigo::ERROR_INTERNO->value
    );
} finally {
    GestorTrafico::limpiarArchivosTemporales();
    exit;
}