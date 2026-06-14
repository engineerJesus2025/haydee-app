<?php
use haydee\enums\HttpCodigo;
use haydee\ayuda\Validador;

$operacion = $operacion ?: 'obtener_llave';

// ==================== REGLAS Y FIREWALL DE PROTOCOLO HTTP ====================
$reglas = [
    '__metodo_http_permitido__' => ['GET']
];
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    $datosError = [
        'mensaje' => 'Protocolo HTTP denegado para esta operación.',
        'errores' => $validador->obtenerErrores()
    ];
    throw new \Exception(json_encode($datosError), HttpCodigo::METODO_NO_PERMITIDO->value);
}

// ==================== PROCESAR OPERACIÓN (FLUJO LINEAL) ====================
$respuesta = ['estatus' => false, 'mensaje' => 'No se pudo obtener la configuración de seguridad.'];

try {
    switch ($operacion) {
        case 'obtener_llave':
            $rutaLlave = ROOT_PATH . '/config/llave_servidor_publica.pem';

            if (!file_exists($rutaLlave)) {
                http_response_code(HttpCodigo::ERROR_INTERNO->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Configuración de seguridad incompleta en el servidor.'];
                break;
            }

            $llavePublica = file_get_contents($rutaLlave); // Petición exitosa y legítima, reseteamos contador

            $respuesta = [
                'estatus' => true,
                'mensaje' => 'Llave pública lista',
                'public_key' => $llavePublica
            ];
            break;

        default:
            http_response_code(HttpCodigo::BAD_REQUEST->value);
            $respuesta = ['estatus' => false, 'mensaje' => 'Operación no reconocida o implementada.'];
            break;
    }

    // ==================== ASIGNACIÓN DE CÓDIGOS HTTP (MATCH) ====================
    if ($respuesta['estatus']) {
        http_response_code(HttpCodigo::OK->value);
    } else {
        if (http_response_code() === 200) {
            http_response_code(HttpCodigo::BAD_REQUEST->value);
        }
    }

} catch (Exception $e) {
    error_log('Error en Handshake API: ' . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {    
    echo json_encode($respuesta);
}