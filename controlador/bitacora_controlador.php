<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
 
use haydee\servicios\Sesiones;
use haydee\modelo\Bitacora;

// Verificaciones de seguridad
Sesiones::autorizarAcceso(Modulo::GESTIONAR_SEGURIDAD, Accion::CONSULTAR);

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
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
                break;
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en controlador Bitacora: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explicitamente
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

