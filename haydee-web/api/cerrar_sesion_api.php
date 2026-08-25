<?php
use haydee\enums\HttpCodigo;
use haydee\servicios\Criptografia;
use haydee\servicios\Autenticacion;
use haydee\excepciones\SeguridadException;

$dispositivoId = $_SERVER['HTTP_X_DISPOSITIVO_ID'] ?? $headers['X-Dispositivo-Id'] ?? $headers['x-dispositivo-id'] ?? null;

if (!$dispositivoId) {
    throw new SeguridadException('Identificador de dispositivo ausente.', HttpCodigo::BAD_REQUEST->value);
}

$idUsuario = (int)$identidad['id_usuario'];

$auth = new Autenticacion();
$auth->logout($idUsuario, true);
$auth->cerrar();

Criptografia::desvincularDispositivo($dispositivoId);

http_response_code(HttpCodigo::OK->value);
echo json_encode([
    'estatus' => true, 
    'mensaje' => 'Sesión cerrada, tokens invalidados y claves destruidas correctamente.'
]);