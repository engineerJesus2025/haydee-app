<?php
use haydee\enums\HttpCodigo;
use haydee\ayuda\Validador;
use haydee\modelo\SeguridadIP;

// ==================== DETECCIÓN DE PROTOCOLO Y PAYLOAD ====================
$metodoHttp = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();
$metodoSobreescrito = $headers['X-HTTP-Method-Override'] ?? $_POST['_method'] ?? $_GET['_method'] ?? null;

if (!empty($metodoSobreescrito)) {
    $metodoHttp = strtoupper($metodoSobreescrito);
}

$datosPeticion = ($metodoHttp === 'GET') ? $_GET : $_POST;

$operacion = $datosPeticion['operacion'] ?? 'obtener_llave';

// ==================== REGLAS Y FIREWALL DE PROTOCOLO HTTP ====================
$reglas = [
    '__metodo_http_permitido__' => ['GET']
];
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    http_response_code(HttpCodigo::METODO_NO_PERMITIDO->value);
    echo json_encode(['estatus' => false, 'mensaje' => 'Método HTTP no soportado. Use GET para esta operación.']);
    exit;
}

// ==================== PROCESAR OPERACIÓN (FLUJO LINEAL) ====================
$respuesta = ['estatus' => false, 'mensaje' => 'No se pudo obtener la configuración de seguridad.'];
$seguridadIP = null;

try {
    // ==================== RATE LIMITING ====================
    $seguridadIP = new SeguridadIP();
    $seguridadIP->set_ip($_SERVER['REMOTE_ADDR']);

    $rateLimit = $seguridadIP->verificarRateLimit();
    if (!$rateLimit['estatus']) {
        $seguridadIP->registrarFallo();
        http_response_code($rateLimit['codigo_http'] ?? HttpCodigo::DEMASIADAS_SOLICITUDES->value);
        $respuesta = ['estatus' => false, 'mensaje' => $rateLimit['mensaje']];
    } else {
        
        switch ($operacion) {
            case 'obtener_llave':
                $rutaLlave = ROOT_PATH . '/config/llave_servidor_publica.pem';

                if (!file_exists($rutaLlave)) {
                    http_response_code(HttpCodigo::ERROR_INTERNO->value);
                    $respuesta = ['estatus' => false, 'mensaje' => 'Configuración de seguridad incompleta en el servidor.'];
                    break;
                }

                $llavePublica = file_get_contents($rutaLlave);
                $seguridadIP->limpiarFallo(); // Petición exitosa y legítima, reseteamos contador

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
    // Garantizamos la liberación segura de conexiones en el único punto de salida
    if ($seguridadIP) {
        $seguridadIP->cerrar();
    }
    
    echo json_encode($respuesta);
    exit;
}