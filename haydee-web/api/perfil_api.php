<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\modelo\Usuario;
use haydee\servicios\GestorAuditoria;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

if (empty($operacion)) {
    throw new HaydeeException('No se especificó la operación.', HttpCodigo::BAD_REQUEST->value);
}

$reglas = Usuario::obtenerReglas($operacion);
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    throw new HaydeeException('Protocolo HTTP denegado para esta operación.', HttpCodigo::METODO_NO_PERMITIDO->value);
}

if (!empty($reglas)) {
    $contexto = ['exclude_id' => $identidad['id_usuario']]; 
    $validador->validarConjunto($datosPeticion, $reglas, $contexto);
    if ($validador->tieneErrores()) {
        $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
        throw new ValidacionException('Datos de formulario inválidos o incompletos.', $validador->obtenerErrores(), $codigoHttp);
    }
}

$usuario = new Usuario();
$auditor = new GestorAuditoria($usuario, Modulo::GESTIONAR_USUARIOS); 

$usuario->set_id_usuario($identidad['id_usuario']);

if ($metodoHttp === 'POST' || $metodoHttp === 'PUT') {
    $usuario->set_nombre($datosPeticion['nombre'] ?? null);
    $usuario->set_apellido($datosPeticion['apellido'] ?? null);
    $usuario->set_correo($datosPeticion['correo'] ?? null);
}

switch ($operacion) {
    case 'consultar_perfil':
        $respuesta = $usuario->realizar_consulta('consultar_perfil_usuario');
        if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::CONSULTAR);
        break;

    case 'actualizar_perfil':
        $auditor->capturarDatosAnteriores('consultar_perfil_usuario');
        $respuesta = $usuario->realizar_consulta('modificar_perfil_personal');
        if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::MODIFICAR);
        break;

    default:
        throw new HaydeeException('Operación no reconocida o implementada.', HttpCodigo::BAD_REQUEST->value);
}

$usuario->cerrar();

if (!$respuesta['estatus']) {
    throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
}

http_response_code(HttpCodigo::OK->value);
echo json_encode($respuesta);