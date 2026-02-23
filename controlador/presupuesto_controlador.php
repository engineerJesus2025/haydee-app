<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Presupuesto;
use haydee\modelo\TipoGasto;
use haydee\modelo\Bitacora;

Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_PRESUPUESTO, CONSULTAR);

$presupuesto = new Presupuesto();

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');

    // Asignación masiva de propiedades comunes
    $presupuesto->set_id_presupuesto($_POST['id_presupuesto'] ?? null);
    $presupuesto->set_fecha($_POST['fecha'] ?? null);
    $presupuesto->set_cuota_reserva($_POST['cuota_reserva'] ?? null);
    $presupuesto->set_observacion($_POST['observacion'] ?? null);
    $presupuesto->set_tasa_dolar($_POST['tasa_dolar'] ?? 1); // Tasa recibida del frontend

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try {
        switch ($operacion) {
            case 'consulta':
                $respuesta = $presupuesto->realizar_consulta('consultar_general');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(CONSULTAR, GESTIONAR_PRESUPUESTO, 'Consulta general de presupuestos');
                    echo json_encode(['datos' => $respuesta['datos']]);
                } else {
                    echo json_encode(['datos' => [], 'error' => $respuesta['mensaje']]);
                }
                exit;

            case 'consultar_meses_faltantes':
                $respuesta = $presupuesto->realizar_consulta('consultar_meses_faltantes');
               
                echo json_encode($respuesta);
                
                exit;

            case 'consultar_tipo_gastos':
                $tipoGasto = new TipoGasto();
                $respuesta = $tipoGasto->realizar_consulta('consultar');
                
                echo json_encode($respuesta);
                
                exit;

            case 'consulta_especifica':
                $respuesta = $presupuesto->realizar_consulta('consultar_unico');
                // Se devuelve con estatus/datos (para el formulario de edición)
                break;

            case 'consultar_detalles_presupuestos':
                // En realidad, consultar_unico ya trae los detalles, pero si se necesita solo detalles, se puede usar otro método.
                // Por simplicidad, reutilizamos consultar_unico.
                $respuesta = $presupuesto->realizar_consulta('consultar_unico');
                break;

            case 'registrar_masivo':
                $datos = json_decode($_POST['datos_presupuesto'], true);
                if (json_last_error() !== JSON_ERROR_NONE) throw new Exception('Error JSON');
                $presupuesto->set_fecha($datos['fecha']);
                $presupuesto->set_cuota_reserva($datos['cuota_reserva']);
                $presupuesto->set_observacion($datos['observacion'] ?? '');
                $presupuesto->setDetallesTemp($datos['detalles']);
                $presupuesto->set_tasa_dolar($_POST['tasa_dolar'] ?? 1);

                $respuesta = $presupuesto->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $nuevos = [
                        'fecha'          => $presupuesto->get_fecha(),
                        'cuota_reserva'  => $presupuesto->get_cuota_reserva(),
                        'observacion'    => $presupuesto->get_observacion(),
                        'cant_detalles'  => count($datos['detalles'])
                    ];
                    Bitacora::registrar(REGISTRAR, GESTIONAR_PRESUPUESTO, '', null, null, $nuevos);
                }
                break;

            case 'editar_masivo':
                $datos = json_decode($_POST['datos_presupuesto'], true);
                if (json_last_error() !== JSON_ERROR_NONE) throw new Exception('Error JSON');

                // Obtener anteriores
                $tempPresupuesto = new Presupuesto();
                $tempPresupuesto->set_id_presupuesto($datos['id_presupuesto']);
                $datosAnteriores = $tempPresupuesto->realizar_consulta('consultar_unico');
                $anterior = $datosAnteriores['estatus'] ? $datosAnteriores['datos'] : [];
                $anteriorResumen = [
                    'fecha'         => $anterior['fecha'] ?? '',
                    'cuota_reserva' => $anterior['cuota_reserva'] ?? 0,
                    'observacion'   => $anterior['observacion'] ?? '',
                    'cant_detalles' => count($anterior['detalles'] ?? [])
                ];

                $presupuesto->set_id_presupuesto($datos['id_presupuesto']);
                $presupuesto->set_fecha($datos['fecha']);
                $presupuesto->set_cuota_reserva($datos['cuota_reserva']);
                $presupuesto->set_observacion($datos['observacion'] ?? '');
                $presupuesto->setDetallesTemp($datos['detalles']);

                $respuesta = $presupuesto->realizar_consulta('editar');
                if ($respuesta['estatus']) {
                    $nuevo = [
                        'fecha'         => $presupuesto->get_fecha(),
                        'cuota_reserva' => $presupuesto->get_cuota_reserva(),
                        'observacion'   => $presupuesto->get_observacion(),
                        'cant_detalles' => count($datos['detalles'])
                    ];
                    Bitacora::registrar(MODIFICAR, GESTIONAR_PRESUPUESTO, '', null, $anteriorResumen, $nuevo);
                }
                break;

            case 'eliminar':
                // Obtener datos anteriores
                $tempPresupuesto = new Presupuesto();
                $tempPresupuesto->set_id_presupuesto($presupuesto->get_id_presupuesto());
                $datosAnteriores = $tempPresupuesto->realizar_consulta('consultar_unico');
                $anterior = $datosAnteriores['estatus'] ? $datosAnteriores['datos'] : [];

                $respuesta = $presupuesto->realizar_consulta('eliminar_presupuesto');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_PRESUPUESTO, '', null, $anterior, null);
                }
                break;

            case 'ultimo_id':
                $respuesta = $presupuesto->realizar_consulta('lastId');
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador presupuesto: " . $e->getMessage());
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
        $presupuesto->set_fecha($fecha);
        $resp = $presupuesto->realizar_consulta('consultar_presupuestos_mensualidades');
        $existe = $resp['estatus'] && !empty($resp['datos']);
        echo json_encode(['estatus' => $existe]);
        exit;
    }
}

// Cargar vista
require_once "vista/presupuesto_mensual/presupuesto_vista.php";