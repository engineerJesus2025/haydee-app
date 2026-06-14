<?php
use haydee\enums\HttpCodigo;
use haydee\ayuda\Validador;
use haydee\modelo\Usuario;
use haydee\servicios\Recuperacion;

$operacion = $operacion ?: 'solicitar_otp';

// REGLAS Y VALIDACION
$reglas = Usuario::obtenerReglas($operacion);
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    throw new \Exception(json_encode([
        'mensaje' => 'Protocolo HTTP denegado.',
        'errores' => $validador->obtenerErrores()
    ]), HttpCodigo::METODO_NO_PERMITIDO->value);
}

if (!empty($reglas)) {
    $validador->validarConjunto($datosPeticion, $reglas, ['skip_unique' => true, 'skip_exists' => true]);
    if ($validador->tieneErrores()) {
        throw new \Exception(json_encode([
            'mensaje' => 'Datos inválidos.',
            'errores' => $validador->obtenerErrores()
        ]), HttpCodigo::BAD_REQUEST->value);
    }
}

$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];
$serviceRecuperar = null;

try {
    $serviceRecuperar = new Recuperacion();

    switch ($operacion) {
        case 'solicitar_otp':
            $correo = $datosPeticion['correo'] ?? '';
            if (empty($correo)) {
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'El correo es obligatorio.'];
                break;
            }
            $respuesta = $serviceRecuperar->enviarCorreoOTP($correo);
            break;

        case 'restablecer_con_otp':
            $correo = $datosPeticion['correo'] ?? '';
            $otp = $datosPeticion['codigo'] ?? '';
            $contra = $datosPeticion['contra'] ?? '';

            if (empty($correo) || empty($otp) || empty($contra)) {
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Todos los campos son requeridos.'];
                break;
            }
            $respuesta = $serviceRecuperar->restablecerConOTP($correo, $otp, $contra);
            break;

        case 'validar_otp':
            $correo = $datosPeticion['correo'] ?? '';
            $otp = $datosPeticion['codigo'] ?? '';

            if (empty($correo) || empty($otp)) {
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'El correo y el código son requeridos.'];
                break;
            }
            $respuesta = $serviceRecuperar->validarOTP($correo, $otp);
            break;

        case 'restablecer_con_token':
            $correo = $datosPeticion['correo'] ?? '';
            $tokenAutorizacion = $datosPeticion['token_autorizacion'] ?? '';
            $contra = $datosPeticion['contra'] ?? '';

            if (empty($correo) || empty($tokenAutorizacion) || empty($contra)) {
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Faltan credenciales de autorización.'];
                break;
            }
            $respuesta = $serviceRecuperar->restablecerConToken($correo, $tokenAutorizacion, $contra);
            break;

        default:
            http_response_code(HttpCodigo::BAD_REQUEST->value);
            $respuesta = ['estatus' => false, 'mensaje' => 'Operación no reconocida.'];
            break;
    }

    if ($respuesta['estatus']) {
        http_response_code(HttpCodigo::OK->value);
    } else {
        if (http_response_code() === 200) http_response_code(HttpCodigo::BAD_REQUEST->value);
    }

} catch (Exception $e) {
    error_log("Error en API Recuperar: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    if ($serviceRecuperar && method_exists($serviceRecuperar, 'cerrar')) $serviceRecuperar->cerrar();
    echo json_encode($respuesta);
}