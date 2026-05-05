<?php
use haydee\modelo\Pagos;
use haydee\ayuda\ConstructorDetalles;

// ======================================================================
// (Futuro) Validación de Token JWT y extracción de datos del usuario
// ======================================================================
// Por ahora, simularemos los datos del usuario leyendo un parámetro opcional.
// Si la app móvil envía es_propietario=1, actuaremos como un residente.
$esPropietario = isset($_REQUEST['es_propietario']) && $_REQUEST['es_propietario'] == '1';
$correoUsuario = $_REQUEST['correo'] ?? ''; 

$pagos = new Pagos();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];

try {
    // ======================================================================
    // 1. PETICIONES GET: CONSULTAS (Lectura)
    // ======================================================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        $operacion = $_GET["operacion"] ?? 'consulta'; 

        switch ($operacion) {
            case 'consulta':
                // Si es propietario, ve solo sus pagos. Si es admin, ve todos.
                if ($esPropietario) {
                    $pagos->set_correo($correoUsuario);
                    $respuesta = $pagos->realizar_consulta('consultar_por_correo');
                } else {
                    $respuesta = $pagos->realizar_consulta('consultar');
                }
                break;

            case 'consultar_mensualidades':
                // Requiere: ?endpoint=pagos&operacion=consultar_mensualidades&apartamento_id=5
                $pagos->set_apartamento_id($_GET['apartamento_id'] ?? null);
                $respuesta = $pagos->realizar_consulta('consultarMensualidadPendiente');
                break;

            case 'consultar_pago':
                $pagos->set_id_pago($_GET['id_pago'] ?? null);
                $respuesta = $pagos->realizar_consulta('consultar_pago');
                break;

            default:
                http_response_code(400); 
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación GET no permitida'];
                break;
        }
    } 
    // ======================================================================
    // 2. PETICIONES POST: REGISTRO Y MODIFICACIÓN (Escritura)
    // ======================================================================
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $operacion = $_POST["operacion"] ?? '';

        if (empty($operacion)) {
            http_response_code(400);
            echo json_encode(['estatus' => false, 'mensaje' => 'No se especificó la operación']);
            exit;
        }

        // Asignación de la cabecera del pago
        $pagos->set_id_pago($_POST['id_pago'] ?? null);
        $pagos->set_observacion($_POST['observacion'] ?? null);
        $pagos->set_apartamento_id($_POST['apartamento_id'] ?? null);
        $pagos->set_mensualidad_id($_POST['mensualidad_id'] ?? null);
        
        // Si el admin registra, el pago ya nace PROCESADO. Si es el residente, nace PENDIENTE.
        $estadoPorDefecto = $esPropietario ? 'PENDIENTE' : 'PROCESADO';
        $pagos->set_estado($_POST['estado'] ?? $estadoPorDefecto);

        if ($esPropietario) {
            $pagos->set_correo($correoUsuario);
        }

        switch ($operacion) {
            case 'registrar_pago':
                // 1. Usamos el Helper específico para pagos
                $detalles = ConstructorDetalles::ConstruirDetallesPagos($_POST, $_FILES, false);
                
                if (empty($detalles)) {
                    http_response_code(400);
                    echo json_encode(['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un detalle de pago.']);
                    exit;
                }

                // 2. Pasamos los detalles al modelo
                $pagos->set_detalles($detalles);
                
                // 3. Ejecutamos la inserción
                $respuesta = $pagos->realizar_consulta('registrar_pago');
                break;

            case 'modificar_pago':
                if ($esPropietario) {
                    http_response_code(403); // Prohibido
                    echo json_encode(['estatus' => false, 'mensaje' => 'No autorizado para modificar pagos']);
                    exit;
                }
                // Lógica de modificación similar...
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
    error_log("Error en API Pagos: " . $e->getMessage());
    http_response_code(500);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    $pagos->cerrar();
    echo json_encode($respuesta);
}
?>