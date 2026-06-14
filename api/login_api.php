<?php
use haydee\enums\HttpCodigo;
use haydee\ayuda\Validador;
use haydee\modelo\Usuario;
use haydee\servicios\Autenticacion;
use haydee\servicios\Criptografia;

// Normalizacion del payload heredado del index.php
$datosPeticion['usuario'] = $datosPeticion['correo'] ?? '';
$operacion = $operacion ?: 'entrar';

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
    $validador->validarConjunto($datosPeticion, $reglas, ['skip_unique' => true]);
    if ($validador->tieneErrores()) {
        throw new \Exception(json_encode([
            'mensaje' => 'Formato de credenciales inválido.',
            'errores' => $validador->obtenerErrores()
        ]), HttpCodigo::BAD_REQUEST->value);
    }
}

$correo = $datosPeticion['correo'] ?? '';
$contra = $datosPeticion['contra'] ?? '';
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];
$auth = null;

try {
    $auth = new Autenticacion();
    $resultado = $auth->login($correo, $contra, true, true);

    if ($resultado['estatus']) {
        $modulosApp = ['GESTIONAR_PAGOS', 'GESTIONAR_GASTOS', 'GESTIONAR_MENSUALIDAD', 'GESTIONAR_CARTELERA_VIRTUAL'];
        $permisosFiltrados = array_values(array_filter($resultado['datos']['permisos'] ?? [], function($p) use ($modulosApp) {
            return in_array($p['modulo'], $modulosApp);
        }));

        $respuesta = [
            'estatus' => true,
            'mensaje' => 'Inicio de sesión exitoso',
            'datos' => [
                'id_usuario' => $resultado['datos']['id_usuario'] ?? '',
                'usuario'    => $resultado['datos']['nombre_completo'] ?? '',
                'rol'        => $resultado['datos']['rol'] ?? '',
                'correo'     => $resultado['datos']['correo'] ?? '',
                'permisos'   => $permisosFiltrados
            ],
            'token_jwt'     => $resultado['token_jwt'] ?? '',
            'refresh_token' => $resultado['refresh_token'] ?? ''
        ];

        if (isset($datosPeticion['_temp_disp'], $datosPeticion['_temp_aes'])) {
            Criptografia::vincularDispositivoUsuario(
                $datosPeticion['_temp_disp'],
                $resultado['datos']['id_usuario'],
                $datosPeticion['_temp_aes']
            );
        }
    } else {
        http_response_code($resultado['codigo_http'] ?? HttpCodigo::NO_AUTORIZADO->value);
        $respuesta = ['estatus' => false, 'mensaje' => $resultado['mensaje'] ?? 'Credenciales incorrectas.'];
    }

    if ($respuesta['estatus']) {
        http_response_code(HttpCodigo::OK->value);
    } else {
        if (http_response_code() === 200) http_response_code(HttpCodigo::BAD_REQUEST->value);
    }

} catch (Exception $e) {
    error_log("Error en API Login: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    if ($auth) $auth->cerrar();
    echo json_encode($respuesta);
}