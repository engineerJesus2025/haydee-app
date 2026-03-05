<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\CajaChica;
use haydee\modelo\Bitacora;

Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_CAJA_CHICA, CONSULTAR);

$caja = new CajaChica();

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    // Asignación masiva
    $caja->set_id_caja_chica($_POST['caja_chica_id'] ?? null);
    $caja->set_descripcion($_POST['descripcion'] ?? null);
    $caja->set_fondo_fijo($_POST['fondo_fijo'] ?? null);
    $caja->set_id_movimiento_caja($_POST['id_movimiento_caja'] ?? null);
    $caja->set_concepto($_POST['concepto'] ?? null);
    $caja->set_monto_movimiento($_POST['monto'] ?? null);
    $caja->set_fecha_movimiento($_POST['fecha'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try {
        switch ($operacion) {
            case 'consultar_cajas_chicas':
                $respuesta = $caja->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(CONSULTAR, GESTIONAR_CAJA_CHICA);
                    
                }
                break;

            case 'modificar_descripcion':
                // Obtener datos anteriores de la caja (necesitamos un método que devuelva la caja por ID)
                $tempCaja = new CajaChica();
                $tempCaja->set_id_caja_chica($caja->get_id_caja_chica());
                $datosCaja = $tempCaja->realizar_consulta('consultar_caja_unica'); // Asumo que existe
                $anterior = $datosCaja['estatus'] ? ['descripcion' => $datosCaja['datos']['descripcion']] : [];

                $respuesta = $caja->realizar_consulta('modificar_descripcion');
                if ($respuesta['estatus']) {
                    $nuevo = ['descripcion' => $caja->get_descripcion()];
                    Bitacora::registrar(MODIFICAR, GESTIONAR_CAJA_CHICA, null, $anterior, $nuevo);
                }
                break;

            case 'verificar_caja_mes':
                $respuesta = $caja->realizar_consulta('verificar_caja_mes');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(REGISTRAR, GESTIONAR_CAJA_CHICA);
                }
                break;

            case 'reponer_caja':
                $respuesta = $caja->realizar_consulta('reponer_caja');
                if ($respuesta['estatus']) {
                    $nuevos = [
                        'id_caja' => $caja->get_id_caja_chica(),
                        'monto'   => $caja->get_monto_movimiento()
                    ];
                    Bitacora::registrar(REGISTRAR, GESTIONAR_CAJA_CHICA, null, null, $nuevos);
                }
                break;

            case 'consultar_movimientos_caja':
                $respuesta = $caja->realizar_consulta('consultar_movimientos');
                break;

            case 'consultar_movimiento':
                $respuesta = $caja->realizar_consulta('consultar_movimiento_unico');
                // Se devuelve con estatus/datos (para el formulario de edición)
                break;

            case 'registrar_movimiento':
                $respuesta = $caja->realizar_consulta('registrar_movimiento');
                if ($respuesta['estatus']) {
                    $nuevos = [
                        'concepto' => $caja->get_concepto(),
                        'monto'    => $caja->get_monto_movimiento(),
                        'fecha'    => $caja->get_fecha_movimiento()
                    ];
                    Bitacora::registrar(REGISTRAR, GESTIONAR_CAJA_CHICA, null, null, $nuevos);
                }
                break;

            case 'modificar_movimiento':
                // Obtener datos anteriores
                $tempCaja = new CajaChica();
                $tempCaja->set_id_movimiento_caja($caja->get_id_movimiento_caja());
                $datosAnteriores = $tempCaja->realizar_consulta('consultar_movimiento_unico');
                $anterior = $datosAnteriores['estatus'] ? $datosAnteriores['datos'] : [];

                $respuesta = $caja->realizar_consulta('modificar_movimiento');
                if ($respuesta['estatus']) {
                    // Datos nuevos (lo que se asignó)
                    $nuevo = [
                        'concepto' => $caja->get_concepto(),
                        'fecha'    => $caja->get_fecha_movimiento()
                        // Nota: el método _modificar_movimiento no modifica monto, por eso no se incluye
                    ];
                    Bitacora::registrar(MODIFICAR, GESTIONAR_CAJA_CHICA, null, $anterior, $nuevo);
                }
                break;

            case 'eliminar_movimiento':
                // Obtener datos anteriores
                $tempCaja = new CajaChica();
                $tempCaja->set_id_movimiento_caja($caja->get_id_movimiento_caja());
                $datosAnteriores = $tempCaja->realizar_consulta('consultar_movimiento_unico');
                $anterior = $datosAnteriores['estatus'] ? $datosAnteriores['datos'] : [];

                $respuesta = $caja->realizar_consulta('eliminar_movimiento');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_CAJA_CHICA, null, $anterior, null);
                }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no reconocida'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explícitamente
            if (isset($caja)) {
                $caja->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexión de seguridad

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

if (isset($_POST["validar"])) {
    header('Content-Type: application/json');
    $validar = $_POST["validar"];

    if ($validar == "validar_clave_foranea") {
        if (isset($_POST['tabla'], $_POST['nombre_clave'], $_POST['valor'])) {
            $existe = $caja->validarExistenciaExterna($_POST['tabla'], $_POST['nombre_clave'], $_POST['valor']);
            echo json_encode(['estatus' => $existe]);
        } else {
            echo json_encode(['estatus' => false, 'mensaje' => 'Faltan parámetros']);
        }
    }
    exit;
}

require_once "vista/caja_chica/caja_chica_vista.php";
?>