<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\TipoGasto;
use haydee\modelo\Bitacora;
use haydee\servicios\GestorAuditoria;

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

    // Instanciamos el auditor
    $auditor = new GestorAuditoria($tipoGasto, GESTIONAR_TIPO_GASTO);
    
    try {
        switch ($operacion) {
            case 'consulta':
                $respuesta = $tipoGasto->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'registrar':
                $respuesta = $tipoGasto->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            case 'consulta_especifica':
                $respuesta = $tipoGasto->realizar_consulta('consultar_tipo_gasto');
                break;

            case 'modificar':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_tipo_gasto');

                $respuesta = $tipoGasto->realizar_consulta('modificar');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('modificar'); 
                }
                break;

            case 'eliminar':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_tipo_gasto');

                $respuesta = $tipoGasto->realizar_consulta('eliminar');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('eliminar'); 
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_TIPO_GASTO);
}

// Cargar la vista
require_once "vista/tipo_gasto/tipo_gasto_vista.php";