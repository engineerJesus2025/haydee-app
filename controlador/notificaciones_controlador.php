<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Notificaciones;
use haydee\modelo\Bitacora;

Sesiones::verificarSesion();

// Instancia del modelo
$notificaciones = new Notificaciones();

if (isset($_POST["operacion"])) {
    // Asignación masiva de campos que pueden llegar
    $notificaciones->set_id_notificacion($_POST['id'] ?? null);      // El frontend envía 'id' para marcar una
    $notificaciones->set_usuario_id($_SESSION['id_usuario'] ?? null); // Siempre usamos el de sesión

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $notificaciones->realizar_consulta('consultar_mis_notificaciones');
                break;

            case 'marcar_como_leido':
                $id = $notificaciones->get_id_notificacion();
                if (!$id) {
                    throw new Exception('ID de notificación no proporcionado');
                }
                $respuesta = $notificaciones->realizar_consulta('marcar_leida');
                if ($respuesta['estatus']) {
                    // Eliminar la notificación de la sesión
                    if (isset($_SESSION['notificaciones']) && is_array($_SESSION['notificaciones'])) {
                        foreach ($_SESSION['notificaciones'] as $index => $n) {
                            if (isset($n['id_notificacion']) && $n['id_notificacion'] == $id) {
                                unset($_SESSION['notificaciones'][$index]);
                                break;
                            }
                        }
                        // Reindexar array. no era necesario pero me ahorraba codigo en js
                        $_SESSION['notificaciones'] = array_values($_SESSION['notificaciones']);
                    }
                }
                break;

            case 'marcar_todas_leidas':
                $respuesta = $notificaciones->realizar_consulta('marcar_todas_leidas');
                if ($respuesta['estatus']) {
                    // Vaciar las notificaciones de la sesión
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
            // Cerrar conexiones explícitamente
            if (isset($notificaciones)) {
                $notificaciones->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexión de seguridad

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

// Carga de vistas
if ($accion == "inicio") {
    require_once "vista/notificaciones/notificaciones_vista.php";
}