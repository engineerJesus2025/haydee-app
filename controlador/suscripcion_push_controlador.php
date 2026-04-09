<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\SuscripcionPush;
use haydee\ayuda\Validador;

Sesiones::verificarSesion();

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];
    $respuesta = null; // Inicializamos nulo para el bloque finally

    try {
        // Validación de reglas
        $reglas = SuscripcionPush::obtenerReglas($operacion);

        if (!empty($reglas)) {
            $validador = new Validador();
            $validador->validarConjunto($_POST, $reglas);

            if ($validador->tieneErrores()) {
                $respuesta = ['estatus' => false, 'errores' => $validador->obtenerErrores()];
                throw new Exception("Errores de validación");
            }
        }

        // Instancia del modelo y asignación de datos
        $suscripcion = new SuscripcionPush();
        $suscripcion->set_usuario_id($_SESSION['id_usuario'] ?? null);
        $suscripcion->set_endpoint($_POST['endpoint'] ?? '');
        $suscripcion->set_p256dh($_POST['p256dh'] ?? '');
        $suscripcion->set_auth($_POST['auth'] ?? '');

        // Ejecutar operación
        switch ($operacion) {
            case 'registrar_suscripcion':
                $respuesta = $suscripcion->realizar_consulta($operacion);
                break;
            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }

    } catch (Exception $e) {
        if (!isset($respuesta['errores'])) { // Si no fue un error de validación
            error_log("Error en controlador suscripcion_push: " . $e->getMessage());
            $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
        }
    } finally {
        if ($respuesta !== null) {
            if (isset($suscripcion)) { $suscripcion->cerrar(); }
            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}