<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Mensualidad;
use haydee\modelo\Presupuesto;
use haydee\modelo\Apartamento;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;

Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_MENSUALIDAD, CONSULTAR);

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    // =========================================================
    // VALIDACIÓN CENTRALIZADA DE CABECERA
    // =========================================================
    $reglasCabecera = Mensualidad::obtenerReglas($operacion);

    if (!empty($reglasCabecera)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglasCabecera);

        if ($validador->tieneErrores()) {
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // =========================================================
    // VALIDACIÓN DE DETALLES (APARTAMENTOS Y PRESUPUESTOS)
    // =========================================================
    if (in_array($operacion, ['registrar_mensualidad', 'modificar_mensualidad'])) {
        $datos_apartamentos = json_decode($_POST['datos_apartamentos'], true);
        
        if (json_last_error() !== JSON_ERROR_NONE || empty($datos_apartamentos)) {
            echo json_encode(['estatus' => false, 'mensaje' => 'Error en el formato de datos o no hay apartamentos para procesar.']);
            exit;
        }

        $reglasDetalle = Mensualidad::obtenerReglasDetalles();
        $erroresDetalles = [];
        $validadorBD = new ValidadorBD();

        foreach ($datos_apartamentos as $index => $detalle) {
            $validadorTemp = new Validador();
            $validadorTemp->validarConjunto($detalle, $reglasDetalle);
            
            // Recoger errores base del detalle
            if ($validadorTemp->tieneErrores()) {
                $erroresFila = $validadorTemp->obtenerErrores();
                foreach($erroresFila as $campo => $mensajes) {
                    $erroresDetalles["detalle_" . $index . "_" . $campo] = $mensajes; 
                }
            }

            // Validación manual del sub-arreglo "id_presupuestos" (Ya que el Validador es para campos planos)
            if (empty($detalle['id_presupuestos']) || !is_array($detalle['id_presupuestos'])) {
                $erroresDetalles["detalle_" . $index . "_id_presupuestos"][] = "Falta la lista de presupuestos asociados.";
            } else {
                foreach ($detalle['id_presupuestos'] as $id_p) {
                    if (!$validadorBD->existe('detalles_presupuesto', 'id_detalle_presupuesto', $id_p)) {
                        $erroresDetalles["detalle_" . $index . "_id_presupuestos"][] = "Uno o más presupuestos seleccionados son inválidos.";
                        break; 
                    }
                }
            }
        }

        // Si hay errores en las filas, abortamos antes de tocar el modelo
        if (!empty($erroresDetalles)) {
            echo json_encode([
                'estatus' => false, 
                'errores' => $erroresDetalles, 
                'mensaje' => 'Existen errores en las cuotas de los apartamentos. Por favor, revíselos.'
            ]);
            exit;
        }
    }

    $mensualidad = new Mensualidad();

    // Asignación de detalles si existen
    if (isset($datos_apartamentos)) {
        $mensualidad->set_datos_apartamentos($datos_apartamentos);
    }

    // =========================================================
    // ASIGNACIÓN DE PROPIEDADES AL MODELO
    // =========================================================
    $mensualidad->set_id_mensualidad($_POST['id_mensualidad'] ?? null);
    $mensualidad->set_monto($_POST['monto'] ?? null);
    $mensualidad->set_tasa_dolar($_POST['tasa_dolar'] ?? null);
    $mensualidad->set_mes($_POST['mes'] ?? null);
    $mensualidad->set_anio($_POST['anio'] ?? null);
    $mensualidad->set_apartamento_id($_POST['apartamento_id'] ?? null);
    $mensualidad->set_porcentaje_interes($_POST['porcentaje_interes'] ?? null);
    $mensualidad->set_limite_mensualidad($_POST['limite_mensualidad'] ?? null);

    // Desglosar la fecha si viene en operaciones de consulta o eliminación
    if (isset($_POST['fecha']) && strpos($_POST['fecha'], '-') !== false) {
        list($anio, $mes, $dia) = explode('-', $_POST['fecha']);
        $mensualidad->set_mes($mes);
        $mensualidad->set_anio($anio);
    }

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($mensualidad, GESTIONAR_MENSUALIDAD);

    // =========================================================
    // EJECUCIÓN
    // =========================================================
    try {
        switch ($operacion) {
            case 'verificar_meses':
                $respuesta = $mensualidad->realizar_consulta('verificarMeses');
                break;

            case 'consultarPorMeses':
                $respuesta = $mensualidad->realizar_consulta('consultarPorMeses');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'consultar_mensualidades_apartamentos':
                $respuesta = $mensualidad->realizar_consulta('consultar_mensualidad_apartamentos');
                break;

            case 'consultar_presupuestos_asociados':
                $mensualidad->set_ids_mensualidades($_POST['ids_mensualidades'] ?? '');
                $respuesta = $mensualidad->realizar_consulta('consultar_presupuestos_asociados');
                break;

            case 'consultar_presupuestos_mensualidades':
                $presupuesto = new Presupuesto();
                $presupuesto->set_fecha($_POST["fecha"] ?? '');
                $respuesta = $presupuesto->realizar_consulta('consultar_presupuestos_mensualidades');
                break;

            case 'consultar_meses_mensualidad':
                $respuesta = $mensualidad->realizar_consulta('consultar_meses_mensualidad');
                break;

            case 'consultar_tasa_dolar':
                $respuesta = $mensualidad->realizar_consulta('consultar_tasa_dolar_mensualidades');
                break;

            case 'registrar_mensualidad':
                $respuesta = $mensualidad->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $mensualidad->set_datos_apartamentos(null); // Ocultar datos masivos para auditoría
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            case 'modificar_mensualidad':
                $auditor->capturarDatosAnteriores('consultar_cabecera_mensualidad');
                $respuesta = $mensualidad->realizar_consulta('modificar');
                if ($respuesta['estatus']) {
                    $mensualidad->set_datos_apartamentos(null); 
                    $auditor->registrarAuditoria('modificar');
                }
                break;

            case 'eliminar_mensualidad':
                $auditor->capturarDatosAnteriores('consultar_cabecera_mensualidad');
                $respuesta = $mensualidad->realizar_consulta('eliminar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('eliminar');
                }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador mensualidad: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($mensualidad)) { $mensualidad->cerrar(); }
            if (isset($presupuesto)) { $presupuesto->cerrar(); }
            if (isset($apartamento)) { $apartamento->cerrar(); }
            
            Bitacora::cerrarConexionBitacora();

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

// =========================================================
// VALIDACIONES AJAX 
// =========================================================
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');
    $validar = $_POST["validar"];
    if ($validar === 'validar_fecha_presupuesto') {
        $fecha = $_POST['fecha'] ?? '';
        $presupuesto = new Presupuesto();
        $presupuesto->set_fecha($fecha);
        $res = $presupuesto->realizar_consulta('consultar_presupuestos_mensualidades');
        $existe = $res['estatus'] && !empty($res['datos']);
        echo json_encode(['estatus' => $existe]);
        exit;
    }
}

// Cargar vista con datos de apartamentos
$apartamento = new Apartamento();
$registros_apartamentos = $apartamento->realizar_consulta('consultar_apartamentos_mensualidad');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_MENSUALIDAD);
}
$permisosVista = Sesiones::obtenerPermisosVista(GESTIONAR_MENSUALIDAD);
require_once 'vista/mensualidad/mensualidad_vista.php';