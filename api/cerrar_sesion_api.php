<?php
use haydee\enums\HttpCodigo;
use haydee\servicios\Criptografia;
use haydee\servicios\Autenticacion;

// Extraemos el identificador único del dispositivo móvil
$dispositivoId = $_SERVER['HTTP_X_DISPOSITIVO_ID'] ?? $headers['X-Dispositivo-Id'] ?? $headers['x-dispositivo-id'] ?? null;

if (!$dispositivoId) {
    throw new \Exception('Identificador de dispositivo ausente.', HttpCodigo::BAD_REQUEST->value);
}

$idUsuario = (int)$identidad['id_usuario'];
$auth = null;

try {
    // Destruimos los tokens de sesión (Refresh Token) en la base de datos
    $auth = new Autenticacion();
    $auth->logout($idUsuario, true);

    // Rompemos la vinculación criptográfica E2E (Destrucción de la clave AES compartida)
    Criptografia::desvincularDispositivo($dispositivoId);

    http_response_code(HttpCodigo::OK->value);
    echo json_encode([
        'estatus' => true, 
        'mensaje' => 'Sesión cerrada, tokens invalidados y claves destruidas correctamente.'
    ]);

} catch (\Exception $e) {
    error_log("Error crítico en API Logout para el Usuario ID {$idUsuario}: " . $e->getMessage());
    
    throw new \Exception('Error interno al procesar el cierre de sesión.', HttpCodigo::ERROR_INTERNO->value);
} finally {
    if ($auth) {
        $auth->cerrar();
    }
}