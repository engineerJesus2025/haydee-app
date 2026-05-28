<?php
use haydee\modelo\Mensualidad;
use haydee\servicios\Sesiones;
use haydee\ayuda\Validador;
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\servicios\GestorAuditoria;
use haydee\modelo\Bitacora;

// ==================== IDENTIDAD Y PERMISOS ====================
$identidad = Sesiones::autorizarAccesoAPI(Modulo::GESTIONAR_MENSUALIDAD, Accion::CONSULTAR, ['GET', 'POST', 'PUT']);

$rolUsuario = strtolower($identidad['rol'] ?? '');
$esPropietario = ($rolUsuario === 'propietario'); 
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

Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_MENSUALIDAD, $operacion);

// ==================== REGLAS Y FIREWALL DE PROTOCOLO HTTP ====================
$reglas = Mensualidad::obtenerReglas($operacion);
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
$mensualidad = new Mensualidad();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];
$auditor = new GestorAuditoria($mensualidad, Modulo::GESTIONAR_MENSUALIDAD);

try {
    // ==================== ASIGNACIÓN MASIVA  ====================
    $mensualidad->set_id_mensualidad($datosPeticion['id_mensualidad'] ?? null);
    $mensualidad->set_monto($datosPeticion['monto'] ?? null);
    $mensualidad->set_tasa_dolar($datosPeticion['tasa_dolar'] ?? null);
    $mensualidad->set_porcentaje_interes($datosPeticion['porcentaje_interes'] ?? null);
    $mensualidad->set_limite_mensualidad($datosPeticion['limite_mensualidad'] ?? null);
    $mensualidad->set_apartamento_id($datosPeticion['apartamento_id'] ?? null);
    
    // Parseo de fechas unificado y limpio (aplica para cualquier método HTTP)
    $fecha = $datosPeticion['fecha'] ?? null;
    if ($fecha && strpos($fecha, '-') !== false) {
        list($anio, $mes, $dia) = explode('-', $fecha);
        $mensualidad->set_mes((int)$mes);
        $mensualidad->set_anio((int)$anio);
    } else {
        $mensualidad->set_mes(isset($datosPeticion['mes']) ? (int)$datosPeticion['mes'] : null);
        $mensualidad->set_anio(isset($datosPeticion['anio']) ? (int)$datosPeticion['anio'] : null);
    }

    // ==================== SWITCH PLANO REAL ====================
    switch ($operacion) {

        // ==================== CONSULTAS (GET) ====================
        case 'consulta':
        case 'consultarPorMeses':
            $respuesta = $mensualidad->realizar_consulta('consultarPorMeses');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::CONSULTAR);
            }
            break;

        case 'verificar_meses':
            $respuesta = $mensualidad->realizar_consulta('verificarMeses');
            break;

        case 'consultar_mensualidad_apartamentos':
            $respuesta = $mensualidad->realizar_consulta('consultar_mensualidad_apartamentos');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::CONSULTAR);
            }
            break;

        case 'consultar_meses_mensualidad':
            $respuesta = $mensualidad->realizar_consulta('consultar_meses_mensualidad');
            break;

        case 'consultar_tasa_dolar':
            $respuesta = $mensualidad->realizar_consulta('consultar_tasa_dolar_mensualidades');
            break;

        case 'consultar_kpis':
            if ($esPropietario) {
                $mensualidad->set_correo($correoUsuario);
            }
            $respuesta = $mensualidad->realizar_consulta('consultar_kpis');
            break;

        case 'consultar_desglose':
            $respuesta = $mensualidad->realizar_consulta('consultar_desglose');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::CONSULTAR);
            }
            break;

        // ==================== ESCRITURA (POST / PUT) ====================
        case 'registrar_mensualidad':
        case 'modificar_mensualidad':
            // Soporte híbrido para el JSON de apartamentos (Venga de la web o de Expo)
            $datosApartamentos = $datosPeticion['datos_apartamentos'] ?? null;
            if (is_string($datosApartamentos)) {
                $datosApartamentos = json_decode($datosApartamentos, true);
            }
            
            if (empty($datosApartamentos) || json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Formato de datos de apartamentos inválido o vacío.'];
                break;
            }
            $mensualidad->set_datos_apartamentos($datosApartamentos);
            
            $metodoModelo = ($operacion === 'registrar_mensualidad') ? 'registrar' : 'modificar';
            
            if ($operacion === 'modificar_mensualidad') {
                $auditor->capturarDatosAnteriores('consultar_desglose');
            }
            
            $respuesta = $mensualidad->realizar_consulta($metodoModelo);
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(
                    ($operacion === 'registrar_mensualidad') ? Accion::REGISTRAR : Accion::MODIFICAR
                );
            }
            break;

        case 'eliminar_mensualidad':
            $auditor->capturarDatosAnteriores('consultar_desglose');
            $respuesta = $mensualidad->realizar_consulta('eliminar');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::ELIMINAR);
            }
            break;

        default:
            http_response_code(HttpCodigo::BAD_REQUEST->value);
            $respuesta = ['estatus' => false, 'mensaje' => 'Operación no reconocida o implementada.'];
            break;
    }

    // ==================== CÓDIGOS DE ÉXITO (MATCH) ====================
    if ($respuesta['estatus']) {
        $codigoExito = match ($operacion) {
            'registrar_mensualidad' => HttpCodigo::CREADO->value,
            default => HttpCodigo::OK->value,
        };
        http_response_code($codigoExito);
    } else {
        if (http_response_code() === 200) {
            http_response_code(HttpCodigo::BAD_REQUEST->value);
        }
    }

} catch (Exception $e) {
    error_log("Error en API Mensualidad: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    if ($mensualidad) $mensualidad->cerrar();
    Bitacora::cerrarConexionBitacora();
    echo json_encode($respuesta);
    exit;
}