<?php
use haydee\modelo\Gastos;
use haydee\modelo\Banco;
use haydee\modelo\Proveedores;
use haydee\modelo\SolicitudGasto;
use haydee\modelo\TipoGasto;
use haydee\servicios\Sesiones;
use haydee\ayuda\Validador;
use haydee\ayuda\ConstructorDetalles;
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;

// IDENTIDAD Y PERMISOS
$identidad = Sesiones::autorizarAccesoAPI(Modulo::GESTIONAR_GASTOS, Accion::CONSULTAR, ['GET', 'POST', 'PUT']);

// UNIFICACIÓN DEL PAYLOAD
$metodoHttp = $_SERVER['REQUEST_METHOD'];
$datosPeticion = ($metodoHttp === 'GET') ? $_GET : $_POST;
$operacion = $datosPeticion['operacion'] ?? '';

if (empty($operacion)) {
    http_response_code(HttpCodigo::BAD_REQUEST->value);
    echo json_encode(['estatus' => false, 'mensaje' => 'No se especificó la operación.']);
    exit;
}

Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_GASTOS, $operacion);

// VALIDACIÓN
$reglas = Gastos::obtenerReglas($operacion); 

if (!empty($reglas)) {
    $validador = new Validador();
    $validador->validarConjunto($datosPeticion, $reglas);

    if ($validador->tieneErrores()) {
        $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::BAD_REQUEST->value;
        http_response_code($codigoHttp);
        echo json_encode([
            'estatus' => false, 
            'errores' => $validador->obtenerErrores(),
            'mensaje' => 'Datos inválidos o manipulados. La petición ha sido bloqueada.'
        ]);
        exit;
    }
}

$gastos = new Gastos();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];

try {
    // PETICIONES GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        $operacion = $_GET["operacion"] ?? 'consulta'; 

        switch ($operacion) {
            case 'consulta':
                // Lista general de gastos para la pantalla principal
                $respuesta = $gastos->realizar_consulta('consultar');
                break;

            case 'consultar_gasto':
                // Trae la cabecera y TODOS los detalles de un gasto 
                // Requiere: ?endpoint=gastos&operacion=consultar_gasto&id_gasto=X
                $gastos->set_id_gasto($_GET['id_gasto'] ?? null);
                $respuesta = $gastos->realizar_consulta('consultar_gasto');
                break;

            // Carga masiva de catálogos para el formulario móvil
            case 'obtener_catalogos':
                $banco = new Banco();
                $proveedor = new Proveedores();
                $solicitudGasto = new SolicitudGasto();
                $tipoGasto = new TipoGasto();

                $respuesta = [
                    'estatus' => true,
                    'datos' => [
                        'proveedores' => $proveedor->realizar_consulta('consultar')['datos'] ?? [],
                        'bancos'      => $banco->realizar_consulta('consultar')['datos'] ?? [],
                        'solicitudes' => $solicitudGasto->realizar_consulta('consultar')['datos'] ?? [],
                        'tipos_gasto' => $tipoGasto->realizar_consulta('consultar')['datos'] ?? []
                    ]
                ];
                break;

            case 'listar_gastos_mes':
                $parametros = [
                    'mes' => $_GET['mes'] ?? date('m'),
                    'anio' => $_GET['anio'] ?? date('Y')
                ];
                $respuesta = $gastos->realizar_consulta('listar_gastos_mes', $parametros);
                break;

            case 'obtener_periodos':
                $respuesta = $gastos->realizar_consulta('obtener_periodos_activos');
                break;

            case 'totales_metodo_pago':
                $respuesta = $gastos->realizar_consulta('total_por_metodo_pago');
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value); 
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación GET no permitida'];
                break;
        }
    } 

    // PETICIONES POST
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $operacion = $_POST["operacion"] ?? '';

        if (empty($operacion)) {
            http_response_code(HttpCodigo::BAD_REQUEST->value);
            $respuesta = ['estatus' => false, 'mensaje' => 'No se especificó la Operación'];
        }

        // Asignacion de la cabecera (Datos generales del gasto)
        $gastos->set_id_gasto($_POST['id_gasto'] ?? null);
        $gastos->set_clasificacion($_POST['clasificacion'] ?? null);
        $gastos->set_descripcion_gasto($_POST['descripcion_gasto'] ?? null);
        $gastos->set_solicitud_id($_POST['solicitud_id'] ?? null);
        $gastos->set_tipo_gasto_id($_POST['tipo_gasto_id'] ?? null);
        $gastos->set_proveedor_id($_POST['proveedor_id'] ?? null);

        switch ($operacion) {
            case 'registrar_gasto':
                $detalles = ConstructorDetalles::ConstruirDetallesGastos($_POST, $_FILES, false);
                
                if (empty($detalles)) {
                    http_response_code(HttpCodigo::BAD_REQUEST->value);
                    $respuesta = ['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un detalle.'];
                    break;
                }

                $gastos->set_detalles($detalles);
                
                $respuesta = $gastos->realizar_consulta('registrar_gasto');
                break;

            case 'modificar_gasto':
                $detalles = ConstructorDetalles::ConstruirDetallesGastos($_POST, $_FILES, true);
                if (empty($detalles)) {
                    http_response_code(HttpCodigo::BAD_REQUEST->value);
                    $respuesta = ['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un detalle.'];
                    break;
                }
                $gastos->set_detalles($detalles);
                $respuesta = $gastos->realizar_consulta('modificar_gasto');
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación POST no permitida'];
                break;
        }
    } 
    else {
        http_response_code(HttpCodigo::METODO_NO_PERMITIDO->value); 
        $respuesta = ['estatus' => false, 'mensaje' => 'metodo HTTP no soportado'];
    }

} catch (Exception $e) {
    error_log("Error en API Gastos: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    if (isset($gastos)) { $gastos->cerrar(); }
    if (isset($banco)) { $banco->cerrar(); }
    if (isset($proveedor)) { $proveedor->cerrar(); }
    if (isset($solicitudGasto)) { $solicitudGasto->cerrar(); }
    if (isset($tipoGasto)) { $tipoGasto->cerrar(); }
    
    echo json_encode($respuesta);
}