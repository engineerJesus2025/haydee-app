<?php 
use haydee\ayuda\Sesiones;
use haydee\modelo\Bitacora;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_SEGURIDAD, CONSULTAR);

// Instancia del modelo
$bitacora = new Bitacora();

// Validamos si es una petición AJAX (POST)
if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];
    
    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];

    try {
        switch ($operacion) {
            case 'consulta':
                $resultado = $bitacora->realizar_consulta('consultar');
                if ($resultado['estatus']) {
                    $respuesta = [
                        'estatus' => true, 
                        'mensaje' => 'Consulta exitosa',
                        'datos' => $resultado['datos']
                    ];
                } else {
                    $respuesta['mensaje'] = $resultado['mensaje'];
                }
                break;

            default:
                $respuesta['mensaje'] = 'Operación no implementada';
                break;
        }
    } catch (Exception $e) {
        error_log("Error en controlador Bitacora: " . $e->getMessage());
        $respuesta['mensaje'] = 'Error interno del servidor';
    }

    // ÚNICO PUNTO DE SALIDA PARA AJAX
    echo json_encode($respuesta);
    exit;
}

// Si no es una petición POST, cargamos la vista
require_once "vista/bitacora/bitacora_vista.php";