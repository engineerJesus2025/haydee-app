<?php
use haydee\enums\HttpCodigo;
use haydee\ayuda\Validador;
use haydee\modelo\SuscripcionPush;
use haydee\servicios\Sesiones;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];
    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []]; 
    $codigoExito = HttpCodigo::OK->value;
        // Validacion
        $reglas = SuscripcionPush::obtenerReglas($operacion);

        if (!empty($reglas)) {
            $validador = new Validador();
            $validador->validarConjunto($_POST, $reglas);

            if ($validador->tieneErrores()) {
                $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
                throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
            }
        }

        // Instancia del modelo y Asignacion de datos
        $suscripcion = new SuscripcionPush();
        $suscripcion->set_usuario_id($_SESSION['id_usuario'] ?? null);
        $suscripcion->set_endpoint($_POST['endpoint'] ?? '');
        $suscripcion->set_p256dh($_POST['p256dh'] ?? '');
        $suscripcion->set_auth($_POST['auth'] ?? '');

    // Ejecutar Operación
    switch ($operacion) {
        case 'registrar_suscripcion':
            $respuesta = $suscripcion->realizar_consulta($operacion);
            break;
        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($suscripcion)) {$suscripcion->cerrar();}
    Bitacora::cerrarConexionBitacora();

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}
