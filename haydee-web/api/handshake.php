<?php
use haydee\enums\HttpCodigo;
use haydee\ayuda\Validador;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

$operacion = $operacion ?: 'obtener_llave';

$reglas = ['__metodo_http_permitido__' => ['GET']];
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    throw new HaydeeException('Protocolo HTTP denegado para esta operación.', HttpCodigo::METODO_NO_PERMITIDO->value);
}

if ($operacion !== 'obtener_llave') {
    throw new HaydeeException('Operación no reconocida o implementada.', HttpCodigo::BAD_REQUEST->value);
}

$rutaLlave = ROOT_PATH . '/config/llave_servidor_publica.pem';

if (!file_exists($rutaLlave)) {
    throw new HaydeeException('Configuración de seguridad incompleta en el servidor.', HttpCodigo::ERROR_INTERNO->value);
}

http_response_code(HttpCodigo::OK->value);
echo json_encode([
    'estatus' => true,
    'mensaje' => 'Llave pública lista',
    'public_key' => file_get_contents($rutaLlave)
]);