<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\modelo\SolicitudGasto;
use haydee\modelo\Presupuesto;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_SOLICITUD_GASTO, $operacion);

    // NORMALIZACIÓN DE VARIABLES (Frontend -> Backend)
    if (isset($_POST['fecha'])) $_POST['fecha_reporte']  = $_POST['fecha'];
    if (isset($_POST['descripcion'])) $_POST['descripcion_necesidad'] = $_POST['descripcion'];
    if (isset($_POST['nombre'])) $_POST['nombre_solicitante'] = $_POST['nombre'];
    if (isset($_POST['monto'])) $_POST['monto_estimado'] = $_POST['monto'];

    // VALIDACIÓN CENTRALIZADA
    $reglas = SolicitudGasto::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    // Instancia del modelo principal
    $solicitud = new SolicitudGasto();
    // Asignacion masiva de campos que pueden llegar
    $solicitud->set_id_solicitud($_POST['id_solicitud'] ?? null);
    $solicitud->set_fecha_reporte($_POST['fecha_reporte'] ?? null);      // El name en el form es "fecha"
    $solicitud->set_descripcion_necesidad($_POST['descripcion_necesidad'] ?? null);
    $solicitud->set_nombre_solicitante($_POST['nombre_solicitante'] ?? null);
    $solicitud->set_monto_estimado($_POST['monto_estimado'] ?? null);
    $solicitud->set_estado($_POST['estado'] ?? null);
    $solicitud->set_presupuesto_id($_POST['presupuesto_id'] ?? null);
    $solicitud->set_prioridad($_POST['prioridad'] ?? null);

    $operacion = $_POST["operacion"];
    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];

    // Instanciamos el auditor
    $auditor = new GestorAuditoria($solicitud, Modulo::GESTIONAR_SOLICITUD_GASTO);
    $codigoExito = HttpCodigo::OK->value;
    switch ($operacion) {
        case 'consulta':
            $respuesta = $solicitud->realizar_consulta('consultar');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::CONSULTAR);
            }
            break;

        case 'consultar_solicitud':
            $respuesta = $solicitud->realizar_consulta('consultar_solicitud');
            http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
            break;

        case 'consultar_presupuesto':
            // Este metodo es público y no usa realizar_consulta... PELIGRO
            $respuesta = $solicitud->consultar_presupuesto_disponible();
            
            break;

        case 'meses_anios_con_presupuesto':
            $respuesta = $solicitud->listar_meses_anios_con_presupuesto();
            if ($respuesta['estatus']) {
                $respuesta = ['estatus' => true, 'data' => $respuesta['datos']];
            }
            break;

        case 'buscar_presupuesto_por_mes_anio':
            $mes = $_POST["mes"] ?? '';
            $anio = $_POST["anio"] ?? '';
            $fecha = "$anio-$mes";
            $respuesta = $solicitud->consultar_presupuesto($fecha);
            
            break;

        case 'registrar_solicitud':
            $respuesta = $solicitud->realizar_consulta('registrar_solicitud');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::REGISTRAR);
                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        case 'modificar_solicitud':
            // Obtener datos anteriores
            $auditor->capturarDatosAnteriores('consultar_solicitud');

            $respuesta = $solicitud->realizar_consulta('modificar_solicitud');
            if ($respuesta['estatus']) { 
                $auditor->registrarAuditoria(Accion::MODIFICAR); 
            }
            break;

        case 'eliminar_solicitud':
            // Obtener datos anteriores
            $auditor->capturarDatosAnteriores('consultar_solicitud');

            $respuesta = $solicitud->realizar_consulta('eliminar_solicitud');
            if ($respuesta['estatus']) { 
                $auditor->registrarAuditoria(Accion::ELIMINAR); 
            }
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($notificaciones)) {$notificaciones->cerrar();}
    if (isset($presupuesto)) {$presupuesto->cerrar();}
    Bitacora::cerrarConexionBitacora();

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

// VALIDACIONES AJAX 
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');
    $validar = $_POST["validar"];
    $presupuesto = new Presupuesto();

    switch ($validar) {
        case 'validar_mes':
            $presupuesto->set_fecha($_POST["fecha"] ?? null);
            $respuesta = $presupuesto->realizar_consulta('consultar_mes_presupuesto');
            break;

        case 'validar_anio':
            $presupuesto->set_fecha($_POST["fecha"] ?? null);
            $respuesta = $presupuesto->realizar_consulta('consultar_anio_presupuesto');
            break;
            
        default:
            throw new HaydeeException('Validación no reconocida', HttpCodigo::BAD_REQUEST->value);
    }

    $presupuesto->cerrar();

    http_response_code(HttpCodigo::OK->value);
    echo json_encode($respuesta);
    exit;
}

// =========================================================
// CARGA DE DATOS PARA LA VISTA
// =========================================================
$fecha_actual = date("Y-m");
$presupuestos = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_SOLICITUD_GASTO);

    $solicitud = new SolicitudGasto();
    $presupuestos = $solicitud->consultar_presupuesto($fecha_actual);
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_SOLICITUD_GASTO);
$btn_nuevo = [
    'target'  => '#modal_solicitud_gasto',
    'texto'   => 'Nueva Solicitud',
    'tooltip' => 'Registrar Nueva Solicitud para Gasto'
];
$placeholder_buscar = "Buscar solicitud...";

require_once "vista/solicitud_gasto/solicitud_gasto_vista.php";

