<?php
use haydee\enums\HttpCodigo;
use haydee\ayuda\Validador;
use haydee\modelo\Usuario;
use haydee\servicios\Autenticacion;
use haydee\servicios\Criptografia;

$operacion = $operacion ?: 'refrescar_token';

// REGLAS Y VALIDACION 
$reglas = Usuario::obtenerReglas($operacion);
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    throw new \Exception(json_encode([
        'mensaje' => 'Protocolo HTTP denegado para esta operación.',
        'errores' => $validador->obtenerErrores()
    ]), HttpCodigo::METODO_NO_PERMITIDO->value);
}

if (!empty($reglas)) {
    $validador->validarConjunto($datosPeticion, $reglas);
    if ($validador->tieneErrores()) {
        throw new \Exception(json_encode([
            'mensaje' => 'Errores de validación en credenciales de refresco.',
            'errores' => $validador->obtenerErrores()
        ]), HttpCodigo::BAD_REQUEST->value);
    }
}

$idUsuarioAutenticado = $datosPeticion['id_usuario'];
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no reconocida.'];
$auth = null;

try {
    $auth = new Autenticacion();

    switch ($operacion) {
        case 'refrescar_token':
            $refreshToken = $datosPeticion['token'] ?? '';
            $respuesta = $auth->renovarTokenJWT($idUsuarioAutenticado, $refreshToken);
            
            if ($respuesta['estatus']) {
                if (!empty($_POST['_temp_aes']) && !empty($_POST['_temp_disp'])) {
                    Criptografia::vincularDispositivoUsuario(
                        $_POST['_temp_disp'], 
                        $idUsuarioAutenticado, 
                        $_POST['_temp_aes']
                    );
                }
            } else {
                http_response_code(HttpCodigo::NO_AUTORIZADO->value);
            }
            break;
        default:
            http_response_code(HttpCodigo::BAD_REQUEST->value);
            break;
    }

    if ($respuesta['estatus']) {
        http_response_code(HttpCodigo::OK->value);
    } else {
        if (http_response_code() === 200) http_response_code(HttpCodigo::BAD_REQUEST->value);
    }

} catch (Exception $e) {
    error_log("Error en API Refresh: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    if ($auth) $auth->cerrar();
    echo json_encode($respuesta);
}