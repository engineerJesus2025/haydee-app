<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\SolicitudGasto;
use haydee\modelo\Presupuesto;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_SOLICITUD_GASTO, CONSULTAR);

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    // =========================================================
    // 0. NORMALIZACIÓN DE VARIABLES (Frontend -> Backend)
    // =========================================================
    if (isset($_POST['fecha'])) $_POST['fecha_reporte']  = $_POST['fecha'];
    if (isset($_POST['descripcion'])) $_POST['descripcion_necesidad'] = $_POST['descripcion'];
    if (isset($_POST['nombre'])) $_POST['nombre_solicitante'] = $_POST['nombre'];
    if (isset($_POST['monto'])) $_POST['monto_estimado'] = $_POST['monto'];

    // =========================================================
    // 1. VALIDACIÓN CENTRALIZADA
    // =========================================================
    $reglas = SolicitudGasto::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // Instancia del modelo principal
    $solicitud = new SolicitudGasto();
    // Asignación masiva de campos que pueden llegar
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
    $auditor = new GestorAuditoria($solicitud, GESTIONAR_SOLICITUD_GASTO);

    try {
        switch ($operacion) {
            case 'consulta':
                $respuesta = $solicitud->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'consultar_solicitud':
                $respuesta = $solicitud->realizar_consulta('consultar_solicitud');
                break;

            case 'consultar_presupuesto':
                // Este método es público y no usa realizar_consulta, pero igual podemos manejarlo
                $respuesta = $solicitud->consultar_presupuesto_disponible();
                break;

            case 'meses_anios_con_presupuesto':
                $respuesta = $solicitud->listar_meses_anios_con_presupuesto();
                // Adaptamos la respuesta al formato esperado por el frontend
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
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            case 'modificar_solicitud':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_solicitud');

                $respuesta = $solicitud->realizar_consulta('modificar_solicitud');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('modificar'); 
                }
                break;

            case 'eliminar_solicitud':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_solicitud');

                $respuesta = $solicitud->realizar_consulta('eliminar_solicitud');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('eliminar'); 
                }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador solicitud_gasto: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explícitamente
            if (isset($notificaciones)) {
                $notificaciones->cerrar();
            }
            if (isset($presupuesto)) {
                $presupuesto->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexión de seguridad

            header('Content-Type: application/json');
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
        }
    } catch (Exception $e) {
        error_log("Error en validación AJAX Solicitud: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    } finally {
        $presupuesto->cerrar();
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
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_SOLICITUD_GASTO);

    $solicitud = new SolicitudGasto();
    $presupuestos = $solicitud->consultar_presupuesto($fecha_actual);
}
$permisosVista = Sesiones::obtenerPermisosVista(GESTIONAR_SOLICITUD_GASTO);
require_once "vista/solicitud_gasto/solicitud_gasto_vista.php";