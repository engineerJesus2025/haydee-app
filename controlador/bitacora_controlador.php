<?php 
use haydee\ayuda\Sesiones;
use haydee\modelo\Bitacora;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_SEGURIDAD, CONSULTAR);

// Validamos si es una petición AJAX (POST)
if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];
    
    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];

    // Instancia del modelo
    $bitacora = new Bitacora();

    try {
        switch ($operacion) {
            case 'consulta':
                $respuesta = $bitacora->realizar_consulta('consultar');
                break;

            default:
                $respuesta['mensaje'] = 'Operación no implementada';
                break;
        }
    } catch (Exception $e) {
        error_log("Error en controlador Bitacora: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explícitamente
            if (isset($bitacora)) {
                $bitacora->cerrar();
            }

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

// Si no es una petición POST, cargamos la vista
require_once "vista/bitacora/bitacora_vista.php";