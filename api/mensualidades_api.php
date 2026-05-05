<?php
use haydee\modelo\Mensualidad;

$mensualidad = new Mensualidad();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];

try {
    // ======================================================================
    // 1. PETICIONES GET: CONSULTAS (Lectura)
    // ======================================================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        $operacion = $_GET["operacion"] ?? 'consulta'; 

        // por mi dislexia a veces envío 'fecha', 
        // o envio 'mes' y 'anio' directamente. Cubrimos ambos casos aquí.
        if (isset($_GET['fecha']) && strpos($_GET['fecha'], '-') !== false) {
            list($anio, $mes, $dia) = explode('-', $_GET['fecha']);
            $mensualidad->set_mes((int)$mes);
            $mensualidad->set_anio((int)$anio);
        } else {
            $mensualidad->set_mes($_GET['mes'] ?? null);
            $mensualidad->set_anio($_GET['anio'] ?? null);
        }

        switch ($operacion) {
            case 'consulta':
            case 'consultarPorMeses':
                $respuesta = $mensualidad->realizar_consulta('consultarPorMeses');
                break;

            case 'verificar_meses':
                $respuesta = $mensualidad->realizar_consulta('verificarMeses');
                break;

            case 'consultar_mensualidades_apartamentos':
                $respuesta = $mensualidad->realizar_consulta('consultar_mensualidad_apartamentos');
                break;

            case 'consultar_meses_mensualidad':
                $respuesta = $mensualidad->realizar_consulta('consultar_meses_mensualidad');
                break;

            case 'consultar_tasa_dolar':
                $respuesta = $mensualidad->realizar_consulta('consultar_tasa_dolar_mensualidades');
                break;

            case 'consultar_kpis':
                $respuesta = $mensualidad->realizar_consulta('consultar_kpis');
                break;

            case 'consultar_desglose':
                $id = $_GET['id_mensualidad'] ?? null;
                $mensualidad->set_id_mensualidad($id);
                
                $respuesta = $mensualidad->realizar_consulta('consultar_desglose');
                break;

            default:
                http_response_code(400); 
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación GET no permitida'];
                break;
        }
    } 
    // ======================================================================
    // 2. PETICIONES POST: REGISTRO Y MODIFICACIÓN (Escritura Admin)
    // ======================================================================
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $operacion = $_POST["operacion"] ?? '';

        if (empty($operacion)) {
            http_response_code(400);
            echo json_encode(['estatus' => false, 'mensaje' => 'No se especificó la operación']);
            exit;
        }

        // Asignación de cabecera general
        $mensualidad->set_id_mensualidad($_POST['id_mensualidad'] ?? null);
        $mensualidad->set_monto($_POST['monto'] ?? null);
        $mensualidad->set_tasa_dolar($_POST['tasa_dolar'] ?? null);
        $mensualidad->set_porcentaje_interes($_POST['porcentaje_interes'] ?? null);
        $mensualidad->set_limite_mensualidad($_POST['limite_mensualidad'] ?? null);
        $mensualidad->set_apartamento_id($_POST['apartamento_id'] ?? null);

        if (isset($_POST['fecha']) && strpos($_POST['fecha'], '-') !== false) {
            list($anio, $mes, $dia) = explode('-', $_POST['fecha']);
            $mensualidad->set_mes((int)$mes);
            $mensualidad->set_anio((int)$anio);
        } else {
            $mensualidad->set_mes($_POST['mes'] ?? null);
            $mensualidad->set_anio($_POST['anio'] ?? null);
        }

        switch ($operacion) {
            case 'registrar_mensualidad':
            case 'modificar_mensualidad':
                // Extraemos y decodificamos el JSON masivo de apartamentos que envía tu JS
                $datos_apartamentos = json_decode($_POST['datos_apartamentos'] ?? '[]', true);
                
                if (json_last_error() !== JSON_ERROR_NONE || empty($datos_apartamentos)) {
                    http_response_code(400);
                    echo json_encode(['estatus' => false, 'mensaje' => 'Formato de datos de apartamentos inválido o vacío.']);
                    exit;
                }
                
                $mensualidad->set_datos_apartamentos($datos_apartamentos);
                
                $metodoModelo = ($operacion === 'registrar_mensualidad') ? 'registrar' : 'modificar';
                $respuesta = $mensualidad->realizar_consulta($metodoModelo);
                break;

            case 'eliminar_mensualidad':
                $respuesta = $mensualidad->realizar_consulta('eliminar');
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
    error_log("Error en API Mensualidad: " . $e->getMessage());
    http_response_code(500);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    $mensualidad->cerrar();
    echo json_encode($respuesta);
}
?>