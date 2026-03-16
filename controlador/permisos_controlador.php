<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Permisos;
use haydee\modelo\Bitacora;
use haydee\servicios\GestorAuditoria;

Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_PERMISOS, CONSULTAR); // Ajusta según tus constantes

$permiso = new Permisos();

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];
    $permiso->set_id_permiso($_POST['id_permiso'] ?? null);
    $permiso->set_accion($_POST['accion'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    // Instanciamos el auditor
    $auditor = new GestorAuditoria($permiso, GESTIONAR_PERMISOS);

    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $permiso->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'consultar_unico':
                $respuesta = $permiso->realizar_consulta('consultar_unico');
                break;

            case 'registrar':
                $respuesta = $permiso->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            case 'modificar':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_unico');

                $respuesta = $permiso->realizar_consulta('modificar');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('modificar'); 
                }
                break;

            case 'eliminar':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_unico');

                $respuesta = $permiso->realizar_consulta('eliminar');
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
            if (isset($permiso)) {
                $permiso->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexión de seguridad

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_PERMISOS);
}

// Cargar vista (si existe)
require_once "vista/permisos/permisos_vista.php";