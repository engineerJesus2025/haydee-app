<?php
use haydee\enums\HttpCodigo;
use haydee\ayuda\Validador;
use haydee\modelo\SuscripcionPush;
use haydee\servicios\Sesiones;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];
    $respuesta = null; 

    try {
        // Validación de reglas
        $reglas = SuscripcionPush::obtenerReglas($operacion);

        if (!empty($reglas)) {
            $validador = new Validador();
            $validador->validarConjunto($_POST, $reglas);

            if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::BAD_REQUEST->value;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
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
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;
            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }

    } catch (Exception $e) {
        if (!isset($respuesta['errores'])) { // Si no fue un error de validación
            http_response_code(HttpCodigo::ERROR_INTERNO->value);
            error_log("Error en controlador suscripcion_push: " . $e->getMessage());
            $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
        }
    } finally {
        if ($respuesta !== null) {
            if (isset($suscripcion)) { $suscripcion->cerrar(); }
            echo json_encode($respuesta);
            exit;
        }
    }
}
