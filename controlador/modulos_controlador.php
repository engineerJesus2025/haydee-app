<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Modulos;
use haydee\modelo\Bitacora;
use haydee\servicios\GestorAuditoria;

Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_MODULOS, CONSULTAR); // Ajusta según tus constantes

$obj_modulo = new Modulos();

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];
    $obj_modulo->set_id_modulo($_POST['id_modulo'] ?? null);
    $obj_modulo->set_nombre($_POST['nombre'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    // Instanciamos el auditor
    $auditor = new GestorAuditoria($obj_modulo, GESTIONAR_MODULOS);
    
    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $obj_modulo->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'consultar_unico':
                $respuesta = $obj_modulo->realizar_consulta('consultar_unico');
                break;

            case 'registrar':
                $respuesta = $obj_modulo->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            case 'modificar':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_unico');

                $respuesta = $obj_modulo->realizar_consulta('modificar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('modificar');
                }
                break;

            case 'eliminar':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_unico');

                $respuesta = $obj_modulo->realizar_consulta('eliminar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('eliminar');
                }
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
            if (isset($obj_modulo)) {
                $obj_modulo->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexión de seguridad

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_MODULOS);
}

// Cargar vista (si existe)
require_once "vista/modulos/modulos_vista.php";