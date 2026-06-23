<?php
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\enums\HttpCodigo;
use haydee\ayuda\Validador;
use haydee\modelo\SuscripcionPushMovil;
use haydee\servicios\Sesiones;

if (empty($operacion)) {
    throw new Exception('No se especificó la operación.', HttpCodigo::BAD_REQUEST->value);
}

// Reglas y Validador HTTP
$reglas = SuscripcionPushMovil::obtenerReglas($operacion);
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    $datosError = [
        'mensaje' => 'Protocolo HTTP denegado para esta operación.',
        'errores' => $validador->obtenerErrores()
    ];
    throw new \Exception(json_encode($datosError), HttpCodigo::METODO_NO_PERMITIDO->value);
}

// Validación de Datos
if (!empty($reglas)) {
    $validador->validarConjunto($datosPeticion, $reglas);
    if ($validador->tieneErrores()) {
        $codigoHttp = HttpCodigo::BAD_REQUEST->value;
        $datosError = [
            'mensaje' => 'Token inválidos o incompletos.',
            'errores' => $validador->obtenerErrores()
        ];
        throw new \Exception(json_encode($datosError), $codigoHttp);
    }
}

// Negocio
$pushMovil = new SuscripcionPushMovil();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];

try {
    switch ($operacion) {
        case 'registrar_suscripcion_movil':
            // Tomamos el ID del usuario del JWT validado, NO de lo que mande la app
            $pushMovil->set_usuario_id($identidad['id_usuario']); 
            $pushMovil->set_expo_token($datosPeticion['expo_token']);
            $pushMovil->set_plataforma($datosPeticion['plataforma']);
            
            $respuesta = $pushMovil->realizar_consulta('registrar_suscripcion');
            
            http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
            break;

        default:
            http_response_code(HttpCodigo::BAD_REQUEST->value);
            $respuesta = ['estatus' => false, 'mensaje' => 'Operación no reconocida'];
    }
} catch (Exception $e) {
    error_log("Error en API Push Móvil: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    if ($pushMovil) $pushMovil->cerrar();
    echo json_encode($respuesta);
}