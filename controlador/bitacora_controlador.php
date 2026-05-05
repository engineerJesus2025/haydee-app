<?php 
use haydee\servicios\Sesiones;
use haydee\modelo\Bitacora;

// Verificaciones de seguridad
Sesiones::validarMetodoHTTP(['GET', 'POST']);
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_SEGURIDAD, CONSULTAR);

// Validamos si es una petición AJAX (POST)
if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];
    
    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];

    // Instancia del modelo
    $bitacora = new Bitacora();

    try {
        switch ($operacion) {
            case 'consulta':
                $respuesta = $bitacora->realizar_consulta('consultar');
                http_response_code($respuesta['estatus'] ? 200 : 400);
                break;

            default:
                http_response_code(400);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
                break;
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error en controlador Bitacora: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explícitamente
            if (isset($bitacora)) {
                $bitacora->cerrar();
            }

            echo json_encode($respuesta);
            exit;
        }
    }
}

$placeholder_buscar = "Buscar registro en bitácora...";

// Si no es una petición POST, cargamos la vista
require_once "vista/bitacora/bitacora_vista.php";