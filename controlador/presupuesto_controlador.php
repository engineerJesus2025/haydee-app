<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Presupuesto;
use haydee\modelo\TipoGasto;
use haydee\modelo\Bitacora;
use haydee\servicios\GestorAuditoria;

Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_PRESUPUESTO, CONSULTAR);

$presupuesto = new Presupuesto();

if (isset($_POST["operacion"])) {
    // Asignación masiva de propiedades comunes (lo que venga fuera del JSON)
    $presupuesto->set_id_presupuesto($_POST['id_presupuesto'] ?? null);
    $presupuesto->set_fecha($_POST['fecha'] ?? null);
    $presupuesto->set_cuota_reserva($_POST['cuota_reserva'] ?? null);
    $presupuesto->set_observacion($_POST['observacion'] ?? null);
    $presupuesto->set_tasa_dolar($_POST['tasa_dolar'] ?? 1); 

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    // Instanciamos el auditor
    $auditor = new GestorAuditoria($presupuesto, GESTIONAR_PRESUPUESTO);

    try {
        switch ($operacion) {
            case 'consulta':
                $respuesta = $presupuesto->realizar_consulta('consultar_general');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'consultar_meses_faltantes':
                $respuesta = $presupuesto->realizar_consulta('consultar_meses_faltantes');
                break;

            case 'consultar_tipo_gastos':
                $tipoGasto = new TipoGasto();
                $respuesta = $tipoGasto->realizar_consulta('consultar');
                $tipoGasto->cerrar(); 
                break;

            case 'consulta_especifica':
            case 'consultar_detalles_presupuestos':
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
                    // TRUCO: Ocultamos el arreglo al auditor
                    $presupuesto->setDetallesTemp(null);
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            case 'modificar_masivo':
                $datos = json_decode($_POST['datos_presupuesto'], true);
                if (json_last_error() !== JSON_ERROR_NONE) throw new Exception('Error JSON');

                // 1. Asignar el ID que viene en el JSON para que el auditor sepa a quién buscar
                $presupuesto->set_id_presupuesto($datos['id_presupuesto']);

                // 2. Obtener datos anteriores (consulta plana)
                $auditor->capturarDatosAnteriores('consultar_cabecera_presupuesto');

                // 3. Asignar el resto de los nuevos datos
                $presupuesto->set_fecha($datos['fecha']);
                $presupuesto->set_cuota_reserva($datos['cuota_reserva']);
                $presupuesto->set_observacion($datos['observacion'] ?? '');
                $presupuesto->setDetallesTemp($datos['detalles']);

                // 4. Ejecutar y auditar
                $respuesta = $presupuesto->realizar_consulta('modificar');
                if ($respuesta['estatus']) { 
                    // TRUCO: Ocultamos el arreglo al auditor
                    $presupuesto->setDetallesTemp(null);
                    $auditor->registrarAuditoria('modificar'); 
                }
                break;

            case 'eliminar':
                // Utilizamos la consulta plana
                $auditor->capturarDatosAnteriores('consultar_cabecera_presupuesto');

                $respuesta = $presupuesto->realizar_consulta('eliminar_presupuesto');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('eliminar'); 
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
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($presupuesto)) { $presupuesto->cerrar(); }
            Bitacora::cerrarConexionBitacora();

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_PRESUPUESTO);
}

// Cargar vista
require_once "vista/presupuesto_mensual/presupuesto_vista.php";