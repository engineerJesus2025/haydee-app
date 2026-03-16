<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\SolicitudGasto;
use haydee\modelo\Presupuesto;
use haydee\modelo\Bitacora;
use haydee\servicios\GestorAuditoria;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_SOLICITUD_GASTO, CONSULTAR);

// Instancia del modelo principal
$solicitud = new SolicitudGasto();

// Variables para la vista (solo si no hay operación POST)
$fecha_actual = date("Y-m");
$presupuestos = [];

if (isset($_POST["operacion"])) {
    // Asignación masiva de campos que pueden llegar
    $solicitud->set_id_solicitud($_POST['id_solicitud'] ?? null);
    $solicitud->set_fecha_reporte($_POST['fecha'] ?? null);      // El name en el form es "fecha"
    $solicitud->set_descripcion_necesidad($_POST['descripcion'] ?? null);
    $solicitud->set_nombre_solicitante($_POST['nombre'] ?? null);
    $solicitud->set_monto_estimado($_POST['monto'] ?? null);
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

            case 'consulta_especifica':
                $respuesta = $solicitud->realizar_consulta('consultar_solicitud_id');
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

            case 'registrar':
                $respuesta = $solicitud->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            case 'modificar':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_solicitud_id');

                $respuesta = $solicitud->realizar_consulta('modificar');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('modificar'); 
                }
                break;

            case 'eliminar':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_solicitud_id');

                $respuesta = $solicitud->realizar_consulta('eliminar');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('eliminar'); 
                }
                break;

            case 'ultimo_id':
                $respuesta = $solicitud->realizar_consulta('lastId');
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

// Validaciones AJAX
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
        error_log("Error en validación AJAX: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_SOLICITUD_GASTO);
}

// Si no hay POST, cargar los presupuestos para la vista
$presupuestos = $solicitud->consultar_presupuesto($fecha_actual);
require_once "vista/solicitud_gasto/solicitud_gasto_vista.php";