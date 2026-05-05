<?php
// api/gastos_api.php
use haydee\modelo\Gastos;
use haydee\ayuda\ConstructorDetalles; // Necesario si a futuro vas a registrar gastos desde la app

// (Futuro) Validación del Token JWT

$gastos = new Gastos();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];

try {
    // ======================================================================
    // 1. PETICIONES GET: Solo para CONSULTAR datos (Lectura)
    // ======================================================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        $operacion = $_GET["operacion"] ?? 'consulta'; 

        switch ($operacion) {
            case 'consulta':
                // Lista general de gastos para la pantalla principal
                $respuesta = $gastos->realizar_consulta('consultar');
                break;

            case 'consultar_gasto':
                // Trae la cabecera y TODOS los detalles de un gasto específico
                // Requiere: ?endpoint=gastos&operacion=consultar_gasto&id_gasto=X
                $gastos->set_id_gasto($_GET['id_gasto'] ?? null);
                $respuesta = $gastos->realizar_consulta('consultar_gasto');
                break;

            // (Opcional) endpoints estadísticos que vi en tu controlador web
            case 'listar_gastos_mes':
                $respuesta = $gastos->realizar_consulta('listar_gastos_mes');
                break;

            case 'totales_metodo_pago':
                $respuesta = $gastos->realizar_consulta('total_por_metodo_pago');
                break;

            default:
                http_response_code(400); 
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación GET no permitida'];
                break;
        }
    } 
    // ======================================================================
    // 2. PETICIONES POST: Para CREAR o MODIFICAR (Escritura)
    // ======================================================================
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $operacion = $_POST["operacion"] ?? '';

        if (empty($operacion)) {
            http_response_code(400);
            echo json_encode(['estatus' => false, 'mensaje' => 'No se especificó la operación']);
            exit;
        }

        // Asignación de la cabecera (Datos generales del gasto)
        $gastos->set_id_gasto($_POST['id_gasto'] ?? null);
        $gastos->set_clasificacion($_POST['clasificacion'] ?? null);
        $gastos->set_descripcion_gasto($_POST['descripcion_gasto'] ?? null);
        $gastos->set_solicitud_id($_POST['solicitud_id'] ?? null);
        $gastos->set_tipo_gasto_id($_POST['tipo_gasto_id'] ?? null);
        $gastos->set_proveedor_id($_POST['proveedor_id'] ?? null);

        switch ($operacion) {
            case 'registrar_gasto':
                // 1. Usamos tu helper web para armar los renglones desde el FormData de la app
                $detalles = ConstructorDetalles::ConstruirDetallesGastos($_POST, $_FILES, false);
                
                if (empty($detalles)) {
                    http_response_code(400);
                    echo json_encode(['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un detalle.']);
                    exit;
                }

                // 2. Pasamos los detalles al modelo
                $gastos->set_detalles($detalles);
                
                // 3. Ejecutamos la inserción
                $respuesta = $gastos->realizar_consulta('registrar_gasto');
                break;

            case 'modificar_gasto':
                // Lógica similar de modificación...
                break;

            default:
                http_response_code(400);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación POST no permitida'];
                break;
        }
    } 
    // ======================================================================
    // 3. MÉTODOS NO SOPORTADOS
    // ======================================================================
    else {
        http_response_code(405); 
        $respuesta = ['estatus' => false, 'mensaje' => 'Método HTTP no soportado'];
    }

} catch (Exception $e) {
    error_log("Error en API Gastos: " . $e->getMessage());
    http_response_code(500);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    $gastos->cerrar();
    echo json_encode($respuesta);
}
?>