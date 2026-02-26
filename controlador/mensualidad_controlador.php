<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Mensualidad;
use haydee\modelo\Presupuesto;
use haydee\modelo\Apartamento;
use haydee\modelo\Bitacora;

Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_MENSUALIDAD, CONSULTAR);

$mensualidad = new Mensualidad();

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');

    // Asignación masiva de campos comunes
    $mensualidad->set_id_mensualidad($_POST['id_mensualidad'] ?? null);
    $mensualidad->set_monto($_POST['monto'] ?? null);
    $mensualidad->set_tasa_dolar($_POST['tasa_dolar'] ?? null);
    $mensualidad->set_mes($_POST['mes'] ?? null);
    $mensualidad->set_anio($_POST['anio'] ?? null);
    $mensualidad->set_apartamento_id($_POST['apartamento_id'] ?? null);
    $mensualidad->set_porcentaje_interes($_POST['porcentaje_interes'] ?? null);
    $mensualidad->set_limite_mensualidad($_POST['limite_mensualidad'] ?? null);

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try {
        switch ($operacion) {
            // =========================================================
            // CONSULTAS
            // =========================================================
            case 'verificar_meses':
                $respuesta = $mensualidad->realizar_consulta('verificarMeses');
                // Se devuelve tal cual (estatus/datos)
                break;

            case 'consultar_mensualidades_mes':
                $respuesta = $mensualidad->realizar_consulta('consultarPorMeses');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(CONSULTAR, GESTIONAR_MENSUALIDAD, 'Consulta de mensualidades por mes');
                    echo json_encode(['datos' => $respuesta['datos']]);
                } else {
                    echo json_encode(['datos' => [], 'error' => $respuesta['mensaje']]);
                }
                exit;

            case 'consultar_mensualidades_apartamentos':
                $fecha = $_POST["fecha"] ?? '';

                list($anio, $mes, $dia) = explode('-', $fecha);
                $mensualidad->set_mes($mes);
                $mensualidad->set_anio($anio);
                
                $respuesta = $mensualidad->realizar_consulta('consultar_mensualidad_apartamentos');

                echo json_encode($respuesta);

                exit;

            case 'consultar_presupuestos_asociados':
                $mensualidad->set_ids_mensualidades($_POST['ids_mensualidades'] ?? '');
                $respuesta = $mensualidad->realizar_consulta('consultar_presupuestos_asociados');
                    echo json_encode($respuesta);
                exit;

            case 'consultar_presupuestos_mensualidades':
                $presupuesto = new Presupuesto();
                $fecha = $_POST["fecha"] ?? '';
                $presupuesto->set_fecha($fecha);
                $respuesta = $presupuesto->realizar_consulta('consultar_presupuestos_mensualidades');
                echo json_encode($respuesta);
                exit;

            case 'consultar_meses_mensualidad':
                $respuesta = $mensualidad->realizar_consulta('consultar_meses_mensualidad');
                if ($respuesta['estatus']) {
                    echo json_encode(['datos' => $respuesta['datos']]);
                } else {
                    echo json_encode(['datos' => [], 'error' => $respuesta['mensaje']]);
                }
                exit;

            case 'consultar_tasa_dolar':
                $respuesta = $mensualidad->realizar_consulta('consultar_tasa_dolar_mensualidades');
                // Se espera un objeto con datos
                echo json_encode($respuesta);
                exit;

            // =========================================================
            // OPERACIONES MASIVAS (REGISTRAR/modificar)
            // =========================================================
            case 'registrar_masivo':
                // Decodificar array de apartamentos
                $datos_apartamentos = json_decode($_POST['datos_apartamentos'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Error en el formato de datos JSON');
                }
                $mensualidad->set_datos_apartamentos($datos_apartamentos);

                $respuesta = $mensualidad->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(REGISTRAR, GESTIONAR_MENSUALIDAD,
                        "Registro masivo de mensualidades para periodo {$_POST['mes']}/{$_POST['anio']}");
                }
                break;

            case 'modificar_masivo':
                $datos_apartamentos = json_decode($_POST['datos_apartamentos'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Error en el formato de datos JSON');
                }
                $mensualidad->set_datos_apartamentos($datos_apartamentos);

                $respuesta = $mensualidad->realizar_consulta('modificar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(MODIFICAR, GESTIONAR_MENSUALIDAD,
                        "Edición masiva de mensualidades para periodo {$_POST['mes']}/{$_POST['anio']}");
                }
                break;

            // =========================================================
            // ELIMINACIÓN (por mes/año)
            // =========================================================
            case 'eliminar_mensualidad':
                $fecha = $_POST["fecha"] ?? '';
                list($anio, $mes, $dia) = explode('-', $fecha);
                $mensualidad->set_mes($mes);
                $mensualidad->set_anio($anio);
                $respuesta = $mensualidad->realizar_consulta('eliminar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_MENSUALIDAD,
                        "Mensualidades del mes $mes del $anio eliminadas");
                }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador mensualidad: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => $e->getMessage()];
    }

    echo json_encode($respuesta);
    exit;
}

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
require_once 'vista/mensualidad/mensualidad_vista.php';