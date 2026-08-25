<?php
use haydee\enums\HttpCodigo;
use haydee\ayuda\Validador;
use haydee\modelo\Usuario;
use haydee\servicios\Recuperacion;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

$operacion = $operacion ?: 'solicitar_otp';

$reglas = Usuario::obtenerReglas($operacion);
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    throw new HaydeeException('Protocolo HTTP denegado.', HttpCodigo::METODO_NO_PERMITIDO->value);
}

if (!empty($reglas)) {
    $validador->validarConjunto($datosPeticion, $reglas, ['skip_unique' => true, 'skip_exists' => true]);
    if ($validador->tieneErrores()) {
        throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), HttpCodigo::BAD_REQUEST->value);
    }
}

$serviceRecuperar = new Recuperacion();
$correo = $datosPeticion['correo'] ?? '';

switch ($operacion) {
    case 'solicitar_otp':
        if (empty($correo)) throw new HaydeeException('El correo es obligatorio.', HttpCodigo::BAD_REQUEST->value);
        $respuesta = $serviceRecuperar->enviarCorreoOTP($correo);
        break;

    case 'restablecer_con_otp':
        $otp = $datosPeticion['codigo'] ?? '';
        $contra = $datosPeticion['contra'] ?? '';
        if (empty($correo) || empty($otp) || empty($contra)) throw new HaydeeException('Todos los campos son requeridos.', HttpCodigo::BAD_REQUEST->value);
        $respuesta = $serviceRecuperar->restablecerConOTP($correo, $otp, $contra);
        break;

    case 'validar_otp':
        $otp = $datosPeticion['codigo'] ?? '';
        if (empty($correo) || empty($otp)) throw new HaydeeException('El correo y el código son requeridos.', HttpCodigo::BAD_REQUEST->value);
        $respuesta = $serviceRecuperar->validarOTP($correo, $otp);
        break;

    case 'restablecer_con_token':
        $tokenAutorizacion = $datosPeticion['token_autorizacion'] ?? '';
        $contra = $datosPeticion['contra'] ?? '';
        if (empty($correo) || empty($tokenAutorizacion) || empty($contra)) throw new HaydeeException('Faltan credenciales de autorización.', HttpCodigo::BAD_REQUEST->value);
        $respuesta = $serviceRecuperar->restablecerConToken($correo, $tokenAutorizacion, $contra);
        break;

    default:
        throw new HaydeeException('Operación no reconocida.', HttpCodigo::BAD_REQUEST->value);
}

$serviceRecuperar->cerrar();

if (!$respuesta['estatus']) {
    throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
}

http_response_code(HttpCodigo::OK->value);
echo json_encode($respuesta);