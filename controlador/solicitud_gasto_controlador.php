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
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::BAD_REQUEST->value;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
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
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    // Instanciamos el auditor
    $auditor = new GestorAuditoria($solicitud, Modulo::GESTIONAR_SOLICITUD_GASTO);

    try {
        switch ($operacion) {
            case 'consulta':
                $respuesta = $solicitud->realizar_consulta('consultar');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
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
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;

            case 'meses_anios_con_presupuesto':
                $respuesta = $solicitud->listar_meses_anios_con_presupuesto();
                
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $respuesta = ['estatus' => true, 'data' => $respuesta['datos']];
                }
                break;

            case 'buscar_presupuesto_por_mes_anio':
                $mes = $_POST["mes"] ?? '';
                $anio = $_POST["anio"] ?? '';
                $fecha = "$anio-$mes";
                $respuesta = $solicitud->consultar_presupuesto($fecha);
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;

            case 'registrar_solicitud':
                $respuesta = $solicitud->realizar_consulta('registrar_solicitud');

                http_response_code($respuesta['estatus'] ? HttpCodigo::CREADO->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria(Accion::REGISTRAR);
                }
                break;

            case 'modificar_solicitud':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_solicitud');

                $respuesta = $solicitud->realizar_consulta('modificar_solicitud');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria(Accion::MODIFICAR); 
                }
                break;

            case 'eliminar_solicitud':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_solicitud');

                $respuesta = $solicitud->realizar_consulta('eliminar_solicitud');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria(Accion::ELIMINAR); 
                }
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en controlador solicitud_gasto: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explicitamente
            if (isset($notificaciones)) {
                $notificaciones->cerrar();
            }
            if (isset($presupuesto)) {
                $presupuesto->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexion de seguridad

            echo json_encode($respuesta);
            exit;
        }
    }
}

// =========================================================
// VALIDACIONES AJAX (Quedan igual)
// =========================================================
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');
    $validar = $_POST["validar"];
    $presupuesto = new Presupuesto();
    $respuesta = ['estatus' => false, 'mensaje' => 'Validación no válida'];

    try {
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
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en Validación AJAX Solicitud: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    } finally {
        $presupuesto->cerrar();
    }

    if ($respuesta['estatus'] === true || isset($respuesta['existe'])) {
        http_response_code(HttpCodigo::OK->value);
    }

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

