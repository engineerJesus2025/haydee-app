<?php
use haydee\enums\HttpCodigo;
use haydee\modelo\Apartamento;

$apartamento = new Apartamento();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $operacion = $_GET["operacion"] ?? 'consulta'; 
        $esPropietario = isset($_GET['es_propietario']) && $_GET['es_propietario'] == '1';

        if ($operacion === 'consulta') {
            if ($esPropietario) {
                // Filtramos por su correo
                $apartamento->set_correo($_GET['correo'] ?? '');
                $respuesta = $apartamento->realizar_consulta('obtener_apartamentos_por_correo');
            } else {
                // Lista completa
                $respuesta = $apartamento->realizar_consulta('consultar_listado');
            }
        } else {
            http_response_code(HttpCodigo::BAD_REQUEST->value); 
            $respuesta = ['estatus' => false, 'mensaje' => 'Operación GET no permitida'];
        }
    } else {
        http_response_code(HttpCodigo::METODO_NO_PERMITIDO->value); 
        $respuesta = ['estatus' => false, 'mensaje' => 'metodo HTTP no soportado'];
    }
} catch (Exception $e) {
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
} finally {
    $apartamento->cerrar();
    echo json_encode($respuesta);
}
