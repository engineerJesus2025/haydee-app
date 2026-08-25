<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;

// Validamos si es una petición AJAX (POST)
if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];
    
    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];

    // Instancia del modelo
    $bitacora = new Bitacora();
    $codigoExito = HttpCodigo::OK->value;
    switch ($operacion) {
        case 'consulta':
            $respuesta = $bitacora->realizar_consulta('consultar');
            
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($bitacora)) {$bitacora->cerrar();}

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

$placeholder_buscar = "Buscar registro en bitácora...";

// Si no es una petición POST, cargamos la vista
require_once "vista/bitacora/bitacora_vista.php";

