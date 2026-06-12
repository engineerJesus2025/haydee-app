<?php
use haydee\enums\HttpCodigo;
use haydee\servicios\Criptografia;
use haydee\servicios\Autenticacion;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorTrafico;

// ==================== IDENTIDAD ====================
$usuario = Sesiones::validarAutenticacionJWT();
$idUsuario = $usuario['id_usuario'] ?? null;

if (!$idUsuario) {
    $resultado = ["estatus" => false, "mensaje" => 'Sesión no identificada o expirada.'];
    $codigoHttp = HttpCodigo::NO_AUTORIZADO->value;
    GestorTrafico::abortarConCifrado($resultado, $codigoHttp);
}

// ==================== DETECCIÓN DE PROTOCOLO ====================
$metodoHttp = $_SERVER['REQUEST_METHOD'];

if ($metodoHttp !== 'POST') {
    $resultado = ["estatus" => false, "mensaje" =>  'Método no permitido.'];
    $codigoHttp = HttpCodigo::METODO_NO_PERMITIDO->value;
    GestorTrafico::abortarConCifrado($resultado, $codigoHttp);
}

// Compatibilidad multiplataforma para extracción de cabeceras (Apache/Nginx)
$headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
$dispositivoId = $_SERVER['HTTP_X_DISPOSITIVO_ID'] ?? $headers['X-Dispositivo-Id'] ?? $headers['x-dispositivo-id'] ?? null;

if (!$dispositivoId) {
    $resultado = ["estatus" => false, "mensaje" => 'Identificador de dispositivo ausente.'];
    $codigoHttp = HttpCodigo::BAD_REQUEST->value;
    GestorTrafico::abortarConCifrado($resultado, $codigoHttp);
}

// ==================== PROCESAMIENTO DE CIERRE ====================
$auth = null;

try {
    // Destruimos los tokens de sesión (Refresh Token) y registramos en Bitácora
    $auth = new Autenticacion();
    $auth->logout($idUsuario, true);

    // Rompemos la vinculación criptográfica E2E (Clave AES)
    Criptografia::desvincularDispositivo($dispositivoId);

    http_response_code(HttpCodigo::OK->value);
    echo json_encode([
        'estatus' => true, 
        'mensaje' => 'Sesión cerrada, tokens invalidados y claves destruidas correctamente.'
    ]);

} catch (Exception $e) {
    error_log("Error en API Logout: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    echo json_encode(['estatus' => false, 'mensaje' => 'Error interno al procesar el cierre de sesión.']);
} finally {
    if ($auth) {
        $auth->cerrar();
    }
}