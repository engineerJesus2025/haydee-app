<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Notificaciones;
use haydee\ayuda\Validador;

Sesiones::verificarSesion();

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    // El frontend envía 'id', a 'id_notificacion' para que coincida con la regla
    if (isset($_POST['id'])) {
        $_POST['id_notificacion'] = $_POST['id'];
    }

    $reglas = Notificaciones::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    $notificaciones = new Notificaciones();

    // Asignación de campos seguros
    $notificaciones->set_id_notificacion($_POST['id_notificacion'] ?? null); 
    $notificaciones->set_usuario_id($_SESSION['id_usuario'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $notificaciones->realizar_consulta('consultar_mis_notificaciones');
                break;

            case 'marcar_como_leido':
                $respuesta = $notificaciones->realizar_consulta('marcar_leida');
                if ($respuesta['estatus']) {
                    // Eliminar la notificación de la sesión
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
                if ($respuesta['estatus']) {
                    $_SESSION['notificaciones'] = [];
                }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador notificaciones: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($notificaciones)) { $notificaciones->cerrar(); }

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

// Carga de vistas
if (isset($accion) && $accion == "inicio") {
    require_once "vista/notificaciones/notificaciones_vista.php";
}