<?php
use haydee\enums\HttpCodigo;
use haydee\ayuda\Validador;
use haydee\modelo\Usuario;
use haydee\servicios\Autenticacion;
use haydee\servicios\Criptografia;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;
use haydee\excepciones\SeguridadException;

$operacion = $operacion ?: 'refrescar_token';

$reglas = Usuario::obtenerReglas($operacion);
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    throw new HaydeeException('Protocolo HTTP denegado para esta operación.', HttpCodigo::METODO_NO_PERMITIDO->value);
}

if (!empty($reglas)) {
    $validador->validarConjunto($datosPeticion, $reglas);
    if ($validador->tieneErrores()) {
        throw new ValidacionException('Errores de validación en credenciales de refresco.', $validador->obtenerErrores(), HttpCodigo::BAD_REQUEST->value);
    }
}

if ($operacion !== 'refrescar_token') {
    throw new HaydeeException('Operación no reconocida.', HttpCodigo::BAD_REQUEST->value);
}

$idUsuarioAutenticado = $datosPeticion['id_usuario'];
$refreshToken = $datosPeticion['token'] ?? '';

$auth = new Autenticacion();
$respuesta = $auth->renovarTokenJWT($idUsuarioAutenticado, $refreshToken);
$auth->cerrar();

if (!$respuesta['estatus']) {
    throw new SeguridadException('Token de refresco inválido o expirado.', HttpCodigo::NO_AUTORIZADO->value);
}

if (!empty($_POST['_temp_aes']) && !empty($_POST['_temp_disp'])) {
    Criptografia::vincularDispositivoUsuario($_POST['_temp_disp'], $idUsuarioAutenticado, $_POST['_temp_aes']);
}

http_response_code(HttpCodigo::OK->value);
echo json_encode($respuesta);