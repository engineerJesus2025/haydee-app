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
use haydee\servicios\GestorAuditoria;
use haydee\modelo\Bitacora;

// ==================== IDENTIDAD Y PERMISOS ====================
$identidad = Sesiones::autorizarAccesoAPI(Modulo::GESTIONAR_GASTOS, Accion::CONSULTAR, ['GET', 'POST', 'PUT']);

$rolUsuario = strtolower($identidad['rol'] ?? '');
$esAdministrador = ($rolUsuario === 'administrador'); // o el rol que corresponda
$correoUsuario = $identidad['correo'] ?? '';

// ==================== DETECCIÓN DE PROTOCOLO Y PAYLOAD ====================
$metodoHttp = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();
$metodoSobreescrito = $headers['X-HTTP-Method-Override'] ?? $_POST['_method'] ?? $_GET['_method'] ?? null;

if (!empty($metodoSobreescrito)) {
    $metodoHttp = strtoupper($metodoSobreescrito);
}

$datosPeticion = ($metodoHttp === 'GET') ? $_GET : $_POST;

$operacion = $datosPeticion['operacion'] ?? '';

if (empty($operacion)) {
    http_response_code(HttpCodigo::BAD_REQUEST->value);
    echo json_encode(['estatus' => false, 'mensaje' => 'No se especificó la operación.']);
    exit;
}

Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_GASTOS, $operacion);

// ==================== REGLAS Y FIREWALL DE PROTOCOLO HTTP ====================
$reglas = Gastos::obtenerReglas($operacion);
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    http_response_code(HttpCodigo::METODO_NO_PERMITIDO->value);
    echo json_encode([
        'estatus' => false,
        'errores' => $validador->obtenerErrores(),
        'mensaje' => 'Protocolo HTTP denegado para esta operación.'
    ]);
    exit;
}

// ==================== VALIDACIÓN DE DATOS ====================
if (!empty($reglas)) {
    $validador->validarConjunto($datosPeticion, $reglas);
    if ($validador->tieneErrores()) {
        $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::BAD_REQUEST->value;
        http_response_code($codigoHttp);
        echo json_encode([
            'estatus' => false,
            'errores' => $validador->obtenerErrores(),
            'mensaje' => 'Datos inválidos.'
        ]);
        exit;
    }
}

// ==================== INSTANCIACIÓN DE MODELOS Y AUDITOR ====================
$gastos = new Gastos();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];
$auditor = new GestorAuditoria($gastos, Modulo::GESTIONAR_GASTOS);

// Modelos auxiliares (para catálogos)
$banco = null;
$proveedor = null;
$solicitudGasto = null;
$tipoGasto = null;

try {
    // ==================== ASIGNACIÓN MASIVA PARA ESCRITURA (POST/PUT) ====================
    if ($metodoHttp === 'POST' || $metodoHttp === 'PUT') {
        $gastos->set_id_gasto($datosPeticion['id_gasto'] ?? null);
        $gastos->set_clasificacion($datosPeticion['clasificacion'] ?? null);
        $gastos->set_descripcion_gasto($datosPeticion['descripcion_gasto'] ?? null);
        $gastos->set_solicitud_id($datosPeticion['solicitud_id'] ?? null);
        $gastos->set_tipo_gasto_id($datosPeticion['tipo_gasto_id'] ?? null);
        $gastos->set_proveedor_id($datosPeticion['proveedor_id'] ?? null);
    }
    switch ($operacion) {

        // ==================== CONSULTAS (GET) ====================
        case 'consulta':
            // Lista general de gastos
            $respuesta = $gastos->realizar_consulta('consultar');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::CONSULTAR);
            }
            break;

        case 'consultar_gasto':
            $gastos->set_id_gasto($datosPeticion['id_gasto'] ?? null);
            $respuesta = $gastos->realizar_consulta('consultar_gasto');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::CONSULTAR);
            }
            break;

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
            // No se audita porque es solo carga de catálogos
            break;

        case 'listar_gastos_mes':
            $parametros = [
                'mes' => $datosPeticion['mes'] ?? date('m'),
                'anio' => $datosPeticion['anio'] ?? date('Y')
            ];
            $respuesta = $gastos->realizar_consulta('listar_gastos_mes', $parametros);
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::CONSULTAR);
            }
            break;

        case 'obtener_periodos':
            $respuesta = $gastos->realizar_consulta('obtener_periodos_activos');
            break;

        case 'totales_metodo_pago':
            $respuesta = $gastos->realizar_consulta('total_por_metodo_pago');
            break;

        // ==================== ESCRITURA (POST) ====================
        case 'registrar_gasto':
            // Construir detalles desde $_POST/$_FILES (porque pueden venir archivos)
            $detalles = $datosPeticion['detalles'] ?? ConstructorDetalles::ConstruirDetallesGastos($_POST, $_FILES, false);;
            if (empty($detalles)) {
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un detalle de gasto.'];
                break;
            }
            $gastos->set_detalles($detalles);
            $respuesta = $gastos->realizar_consulta('registrar_gasto');
            if ($respuesta['estatus']) {
                $gastos->set_detalles(null); // limpiar para auditoría
                $auditor->registrarAuditoria(Accion::REGISTRAR);
            }
            break;

        case 'modificar_gasto':
            $detalles = ConstructorDetalles::ConstruirDetallesGastos($_POST, $_FILES, true);
            if (empty($detalles)) {
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un detalle de gasto.'];
                break;
            }
            $auditor->capturarDatosAnteriores('consultar_gasto');
            $gastos->set_detalles($detalles);
            $respuesta = $gastos->realizar_consulta('modificar_gasto');
            if ($respuesta['estatus']) {
                $gastos->set_detalles(null);
                $auditor->registrarAuditoria(Accion::MODIFICAR);
            }
            break;

        default:
            http_response_code(HttpCodigo::BAD_REQUEST->value);
            $respuesta = ['estatus' => false, 'mensaje' => 'Operación no reconocida o implementada.'];
            break;
    }

    // ==================== CÓDIGOS DE ÉXITO ====================
    if ($respuesta['estatus']) {
        $codigoExito = match ($operacion) {
            'registrar_gasto' => HttpCodigo::CREADO->value,
            default => HttpCodigo::OK->value,
        };
        http_response_code($codigoExito);
    } else {
        // No sobreescribir si ya se asignó un código específico (ej. 400, 403, 404)
        if (http_response_code() === 200) {
            http_response_code(HttpCodigo::BAD_REQUEST->value);
        }
    }

} catch (Exception $e) {
    error_log("Error en API Gastos: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    // Cierre de modelos principales y auxiliares
    if ($gastos) $gastos->cerrar();
    if ($banco) $banco->cerrar();
    if ($proveedor) $proveedor->cerrar();
    if ($solicitudGasto) $solicitudGasto->cerrar();
    if ($tipoGasto) $tipoGasto->cerrar();
    
    Bitacora::cerrarConexionBitacora();
    echo json_encode($respuesta);
    exit;
}