<?php
use haydee\modelo\Banco;

$banco = new Banco();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $operacion = $_GET["operacion"] ?? 'consulta'; 

        if ($operacion === 'consulta') {
            $respuesta = $banco->realizar_consulta('consultar');
        } else {
            http_response_code(400); 
            $respuesta = ['estatus' => false, 'mensaje' => 'Operación GET no permitida'];
        }
    } else {
        http_response_code(405); 
        $respuesta = ['estatus' => false, 'mensaje' => 'Método HTTP no soportado'];
    }
} catch (Exception $e) {
    http_response_code(500);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
} finally {
    $banco->cerrar();
    echo json_encode($respuesta);
}
?>