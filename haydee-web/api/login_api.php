<?php
use haydee\enums\HttpCodigo;
use haydee\ayuda\Validador;
use haydee\modelo\Usuario;
use haydee\servicios\Autenticacion;
use haydee\servicios\Criptografia;
use haydee\excepciones\ValidacionException;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\SeguridadException;

$datosPeticion['usuario'] = $datosPeticion['correo'] ?? '';
$operacion = $operacion ?: 'entrar';

$reglas = Usuario::obtenerReglas($operacion);
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    throw new HaydeeException('Protocolo HTTP denegado.', HttpCodigo::METODO_NO_PERMITIDO->value);
}

if (!empty($reglas)) {
    $validador->validarConjunto($datosPeticion, $reglas, ['skip_unique' => true]);
    if ($validador->tieneErrores()) {
        throw new ValidacionException('Formato de credenciales inválido.', $validador->obtenerErrores(), HttpCodigo::BAD_REQUEST->value);
    }
}

$correo = $datosPeticion['correo'] ?? '';
$contra = $datosPeticion['contra'] ?? '';

$auth = new Autenticacion();
$resultado = $auth->login($correo, $contra, true, true);
$auth->cerrar();

if (!$resultado['estatus']) {
    throw new SeguridadException($resultado['mensaje'] ?? 'Credenciales incorrectas.', $resultado['codigo_http'] ?? HttpCodigo::NO_AUTORIZADO->value);
}

$modulosApp = ['GESTIONAR_PAGOS', 'GESTIONAR_GASTOS', 'GESTIONAR_MENSUALIDAD', 'GESTIONAR_CARTELERA_VIRTUAL'];
$permisosFiltrados = array_values(array_filter($resultado['datos']['permisos'] ?? [], function($p) use ($modulosApp) {
    return in_array($p['modulo'], $modulosApp);
}));

if (isset($datosPeticion['_temp_disp'], $datosPeticion['_temp_aes'])) {
    Criptografia::vincularDispositivoUsuario(
        $datosPeticion['_temp_disp'],
        $resultado['datos']['id_usuario'],
        $datosPeticion['_temp_aes']
    );
}

http_response_code(HttpCodigo::OK->value);
echo json_encode([
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
]);