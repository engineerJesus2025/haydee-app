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
    header('Content-Type: application/json');

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
                    Bitacora::registrar(CONSULTAR, GESTIONAR_TIPO_GASTO, 'Consulta general de tipos de gasto');
                }
                break;

            case 'registrar':
                $respuesta = $tipoGasto->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(REGISTRAR, GESTIONAR_TIPO_GASTO, $tipoGasto->get_nombre_tipo_gasto());
                }
                break;

            case 'consulta_especifica':
                $respuesta = $tipoGasto->realizar_consulta('consultar_tipo_gasto');
                break;

            case 'modificar':
                $respuesta = $tipoGasto->realizar_consulta('modificar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(MODIFICAR, GESTIONAR_TIPO_GASTO, $tipoGasto->get_nombre_tipo_gasto());
                }
                break;

            case 'eliminar':
                // Primero obtenemos el nombre del tipo a eliminar para la bitácora
                $tipoEliminar = clone $tipoGasto; // Clon para no perder el ID después de la consulta
                $datosTipo = $tipoEliminar->realizar_consulta('consultar_tipo_gasto');
                $nombreTipo = $datosTipo['estatus'] ? ($datosTipo['datos']['nombre_tipo_gasto'] ?? '') : '';

                $respuesta = $tipoGasto->realizar_consulta('eliminar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_TIPO_GASTO, $nombreTipo);
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
    }

    echo json_encode($respuesta);
    exit;
}

// Cargar la vista
require_once "vista/tipo_gasto/tipo_gasto_vista.php";