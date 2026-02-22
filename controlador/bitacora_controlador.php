<?php 
use haydee\ayuda\Sesiones;
use haydee\modelo\Bitacora;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_SEGURIDAD, CONSULTAR);

// Instancia del modelo
$bitacora = new Bitacora();

if (isset($_POST["operacion"])){
    header('Content-Type: application/json');

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try{
        switch ($operacion) {
            case 'consulta':
                $respuesta = $bitacora->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    echo json_encode(['datos' => $respuesta['datos']]);
                } else {
                    echo json_encode(['datos' => [], 'error' => $respuesta['mensaje']]);
                }
                exit;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
                echo json_encode($respuesta);
                exit;
        }
    } catch (Exception $e) {
        error_log("Error en controlador: " . $e->getMessage());
        echo json_encode(['estatus' => false, 'mensaje' => 'Error interno del servidor']);
        exit;
    }
}
require_once "vista/bitacora/bitacora_vista.php";
?>