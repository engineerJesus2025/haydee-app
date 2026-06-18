<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\ayuda\Validador;
use haydee\modelo\Notificaciones;
use haydee\servicios\Sesiones;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_ANIO_FISCAL, $operacion);

    // El frontend envia 'id', a 'id_notificacion' para que coincida con la regla
    if (isset($_POST['id'])) {
        $_POST['id_notificacion'] = $_POST['id'];
    }

    $reglas = Notificaciones::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    $notificaciones = new Notificaciones();

    // Asignacion de campos seguros
    $notificaciones->set_id_notificacion($_POST['id_notificacion'] ?? null); 
    $notificaciones->set_usuario_id($_SESSION['id_usuario'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $notificaciones->realizar_consulta('consultar_mis_notificaciones');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;

            case 'marcar_como_leido':
                $respuesta = $notificaciones->realizar_consulta('marcar_leida');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    // Eliminar la notificación de la sesion
                    if (isset($_SESSION['notificaciones']) && is_array($_SESSION['notificaciones'])) {
                        $id_marcado = $notificaciones->get_id_notificacion();
                        foreach ($_SESSION['notificaciones'] as $index => $notif) {
                            if ($notif['id_notificacion'] == $id_marcado) {
                                unset($_SESSION['notificaciones'][$index]);
                                break;
                            }
                        }
                        // Reindexar array
                        $_SESSION['notificaciones'] = array_values($_SESSION['notificaciones']);
                    }
                }
                break;

            case 'marcar_todas_leidas':
                $respuesta = $notificaciones->realizar_consulta('marcar_todas_leidas');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $_SESSION['notificaciones'] = [];
                }
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en controlador notificaciones: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($notificaciones)) { $notificaciones->cerrar(); }

            echo json_encode($respuesta);
            exit;
        }
    }
}

$placeholder_buscar = "Buscar notificación...";

// Carga de vistas
if (isset($accion) && $accion == "inicio") {
    require_once "vista/notificaciones/notificaciones_vista.php";
}

