<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Gastos;
use haydee\modelo\Banco;
use haydee\modelo\Proveedores;
use haydee\modelo\SolicitudGasto;
use haydee\modelo\TipoGasto;
use haydee\modelo\Bitacora;
use haydee\ayuda\GestorImagenes;
use haydee\ayuda\ConstructorDetalles;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_GASTOS, CONSULTAR);

// Instancia del modelo principal
$gastos = new Gastos();

// Modelos auxiliares para selects (solo para cargar datos en la vista)
$banco = new Banco();
$proveedor = new Proveedores();
$solicitudGasto = new SolicitudGasto();
$tipoGasto = new TipoGasto();

if (isset($_POST["operacion"])) {
    // =========================================================
    // ASIGNACIÓN MASIVA DE CAMPOS ESCALARES
    // =========================================================
    $gastos->set_id_gasto($_POST['id_gasto'] ?? null);
    $gastos->set_clasificacion($_POST['clasificacion'] ?? null);
    $gastos->set_descripcion_gasto($_POST['descripcion_gasto'] ?? null);
    $gastos->set_solicitud_id($_POST['solicitud'] ?? null);
    $gastos->set_tipo_gasto_id($_POST['tipo_gasto'] ?? null);
    $gastos->set_proveedor_id($_POST['proveedor'] ?? null);
    $gastos->set_id_detalle_gasto($_POST['id_detalle_gasto'] ?? null);
    $gastos->set_fecha($_POST['fecha'] ?? null);

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try {
        switch ($operacion) {
            // =========================================================
            // CONSULTAS
            // =========================================================
            case 'consulta':
                $respuesta = $gastos->realizar_consulta('consultar_gastos');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(CONSULTAR, GESTIONAR_GASTOS, 'Consulta general de gastos');
                }
                break;

            case 'consulta_especifica':
                $respuesta = $gastos->realizar_consulta('consultar_gasto_unico');
                // Se devuelve con estatus/datos (para el formulario de edición)
                break;

            case 'consultar_detalles':
                $respuesta = $gastos->realizar_consulta('consultar_detalles_por_gasto');
                break;

            case 'consulta_especifica_detalles':
                $respuesta = $gastos->realizar_consulta('consultar_detalle_unico');
                break;

            // =========================================================
            // REGISTRO Y EDICIÓN UNIFICADOS
            // =========================================================
            case 'registrar':
                $detalles = ConstructorDetalles::ConstruirDetallesGastos($_POST, $_FILES);
                $gastos->set_detalles($detalles);

                // Los campos de cabecera ya fueron asignados masivamente

                $respuesta = $gastos->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $nuevos = [
                        'clasificacion'      => $gastos->get_clasificacion(),
                        'descripcion_gasto'  => $gastos->get_descripcion_gasto(),
                        'tipo_gasto_id'      => $gastos->get_tipo_gasto_id(),
                        'proveedor_id'       => $gastos->get_proveedor_id(),
                        'cantidad_detalles'  => count($detalles),
                        'monto_total'        => array_sum(array_column($detalles, 'monto'))
                    ];
                    Bitacora::registrar(REGISTRAR, GESTIONAR_GASTOS, '', null, null, $nuevos);
                }
                break;

            case 'modificar':
                $id_gasto = $_POST['id_gasto'] ?? null;
                if (!$id_gasto) throw new Exception('ID de gasto no proporcionado');
                $gastos->set_id_gasto($id_gasto);

                // Obtener datos anteriores
                $tempGastos = new Gastos();
                $tempGastos->set_id_gasto($id_gasto);
                $datosAnteriores = $tempGastos->realizar_consulta('consultar_gasto_unico');
                $anterior = $datosAnteriores['estatus'] ? $datosAnteriores['datos'] : [];
                // Resumir anteriores
                $anteriorResumen = [
                    'clasificacion'      => $anterior['gasto']['clasificacion'] ?? '',
                    'descripcion_gasto'  => $anterior['gasto']['descripcion_gasto'] ?? '',
                    'tipo_gasto_id'      => $anterior['gasto']['tipo_gasto_id'] ?? '',
                    'proveedor_id'       => $anterior['gasto']['proveedor_id'] ?? '',
                    'cantidad_detalles'  => count($anterior['detalles'] ?? []),
                    'monto_total'        => array_sum(array_column($anterior['detalles'] ?? [], 'monto'))
                ];

                $detalles = ConstructorDetalles::ConstruirDetallesGastos($_POST, $_FILES, true);
                $gastos->set_detalles($detalles);

                // Los campos de cabecera ya fueron asignados masivamente

                $respuesta = $gastos->realizar_consulta('modificar');
                if ($respuesta['estatus']) {
                    $nuevo = [
                        'clasificacion'      => $gastos->get_clasificacion(),
                        'descripcion_gasto'  => $gastos->get_descripcion_gasto(),
                        'tipo_gasto_id'      => $gastos->get_tipo_gasto_id(),
                        'proveedor_id'       => $gastos->get_proveedor_id(),
                        'cantidad_detalles'  => count($detalles),
                        'monto_total'        => array_sum(array_column($detalles, 'monto'))
                    ];
                    Bitacora::registrar(MODIFICAR, GESTIONAR_GASTOS, '', null, $anteriorResumen, $nuevo);
                }
                break;

            // =========================================================
            // ELIMINACIÓN
            // =========================================================
            case 'eliminar':
                $gastos->set_id_gasto($_POST['id_gasto'] ?? null);
                $tempGastos = new Gastos();
                $tempGastos->set_id_gasto($gastos->get_id_gasto());
                $datosGasto = $tempGastos->realizar_consulta('consultar_gasto_unico');
                $anterior = $datosGasto['estatus'] ? $datosGasto['datos'] : [];
                $anteriorResumen = [
                    'clasificacion'      => $anterior['gasto']['clasificacion'] ?? '',
                    'descripcion_gasto'  => $anterior['gasto']['descripcion_gasto'] ?? '',
                    'tipo_gasto_id'      => $anterior['gasto']['tipo_gasto_id'] ?? '',
                    'proveedor_id'       => $anterior['gasto']['proveedor_id'] ?? '',
                    'cantidad_detalles'  => count($anterior['detalles'] ?? [])
                ];

                $respuesta = $gastos->realizar_consulta('eliminar_gasto');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_GASTOS, '', null, $anteriorResumen, null);
                }
                break;

            // =========================================================
            // REPORTES Y OTROS (opcional)
            // =========================================================
            case 'listar_gastos_mes':
                $respuesta = $gastos->realizar_consulta('listar_gastos_mes');
                break;

            case 'filtrar_gastos_mes':
                $respuesta = $gastos->realizar_consulta('filtrar_por_mes');
                break;

            case 'totales_metodo_pago':
                $respuesta = $gastos->realizar_consulta('total_por_metodo_pago');
                break;

            case 'ultimo_id':
                $respuesta = $gastos->realizar_consulta('lastId');
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador gastos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explícitamente
            if (isset($gastos)) {
                $gastos->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexión de seguridad

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
    $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];

    try {
        switch ($validar) {
            case 'validar_clave_foranea':
                if (isset($_POST['tabla'], $_POST['nombre_clave'], $_POST['valor'])) {
                    $existe = $gastos->validarExistenciaExterna(
                        $_POST['tabla'],
                        $_POST['nombre_clave'],
                        $_POST['valor']
                    );
                    echo json_encode(['estatus' => $existe]);
                } else {
                    echo json_encode(['estatus' => false, 'mensaje' => 'Faltan parámetros']);
                }
                exit;

            default:
                echo json_encode(['estatus' => false, 'mensaje' => 'Validación no implementada']);
                exit;
        }
    } catch (Exception $e) {
        error_log("Error en validación AJAX: " . $e->getMessage());
        echo json_encode(['estatus' => false, 'mensaje' => 'Error interno del servidor']);
        exit;
    }
}

// =========================================================
// CARGA DE DATOS PARA LA VISTA
// =========================================================
$proveedores = $proveedor->realizar_consulta('consultar');
$bancos = $banco->realizar_consulta('consultar');
$solicitudes_gasto = $solicitudGasto->realizar_consulta('consultar');
$tipos_gasto = $tipoGasto->realizar_consulta('consultar');

require_once "vista/gastos/gastos_vista.php";
?>