<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\TipoGasto;
use haydee\modelo\Bitacora;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_TIPO_GASTO, CONSULTAR);

// Instancia del modelo
$tipoGasto = new TipoGasto();

if (isset($_POST["operacion"])) {
    // Asignación masiva de campos que pueden llegar
    $tipoGasto->set_id_tipo_gasto($_POST['id_tipo_gasto'] ?? null);
    $tipoGasto->set_nombre_tipo_gasto($_POST['nombre_tipo_gasto'] ?? null);

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    try {
        switch ($operacion) {
            case 'consulta':
                $respuesta = $tipoGasto->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(CONSULTAR, GESTIONAR_TIPO_GASTO);
                }
                break;

            case 'registrar':
                $respuesta = $tipoGasto->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $nuevos = ['nombre_tipo_gasto' => $tipoGasto->get_nombre_tipo_gasto()];
                    Bitacora::registrar(REGISTRAR, GESTIONAR_TIPO_GASTO, null, null, $nuevos);
                }
                break;

            case 'consulta_especifica':
                $respuesta = $tipoGasto->realizar_consulta('consultar_tipo_gasto');
                break;

            case 'modificar':
                // Obtener datos anteriores
                $tempTipo = new TipoGasto();
                $tempTipo->set_id_tipo_gasto($tipoGasto->get_id_tipo_gasto());
                $datosAnteriores = $tempTipo->realizar_consulta('consultar_tipo_gasto');
                $anterior = $datosAnteriores['estatus'] ? ['nombre_tipo_gasto' => $datosAnteriores['datos']['nombre_tipo_gasto'] ?? ''] : [];

                $respuesta = $tipoGasto->realizar_consulta('modificar');
                if ($respuesta['estatus']) {
                    $nuevo = ['nombre_tipo_gasto' => $tipoGasto->get_nombre_tipo_gasto()];
                    Bitacora::registrar(MODIFICAR, GESTIONAR_TIPO_GASTO, null, $anterior, $nuevo);
                }
                break;

            case 'eliminar':
                // Obtener datos anteriores
                $tempTipo = new TipoGasto();
                $tempTipo->set_id_tipo_gasto($tipoGasto->get_id_tipo_gasto());
                $datosTipo = $tempTipo->realizar_consulta('consultar_tipo_gasto');
                $anterior = $datosTipo['estatus'] ? ['nombre_tipo_gasto' => $datosTipo['datos']['nombre_tipo_gasto'] ?? ''] : [];

                $respuesta = $tipoGasto->realizar_consulta('eliminar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_TIPO_GASTO, null, $anterior, null);
                }
                break;

            case 'lastId':
                $respuesta = $tipoGasto->realizar_consulta('lastId');
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explícitamente
            if (isset($tipoGasto)) {
                $tipoGasto->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexión de seguridad

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

// Cargar la vista
require_once "vista/tipo_gasto/tipo_gasto_vista.php";