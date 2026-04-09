<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Modulos;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;

Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_MODULOS, CONSULTAR);

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(GESTIONAR_MODULOS, $operacion);
    
    // 1. VALIDACIÓN
    $reglas = Modulos::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // 2. LAZY LOADING Y ASIGNACIÓN
    $obj_modulo = new Modulos();
    $obj_modulo->set_id_modulo($_POST['id_modulo'] ?? null);
    $obj_modulo->set_nombre($_POST['nombre'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($obj_modulo, GESTIONAR_MODULOS);
    
    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $obj_modulo->realizar_consulta('consultar');
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('consultar'); }
                break;

            case 'consultar_modulo':
                $respuesta = $obj_modulo->realizar_consulta('consultar_modulo');
                break;

            case 'registrar_modulo':
                $respuesta = $obj_modulo->realizar_consulta('registrar_modulo');
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('registrar'); }
                break;

            case 'modificar_modulo':
                $auditor->capturarDatosAnteriores('consultar_modulo');
                $respuesta = $obj_modulo->realizar_consulta('modificar_modulo');
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('modificar'); }
                break;

            case 'eliminar_modulo':
                $auditor->capturarDatosAnteriores('consultar_modulo');
                $respuesta = $obj_modulo->realizar_consulta('eliminar_modulo');
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('eliminar'); }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador modulos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($obj_modulo)) { $obj_modulo->cerrar(); }
            Bitacora::cerrarConexionBitacora();

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

// Bloque de vista...
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_MODULOS);
}
$permisosVista = Sesiones::obtenerPermisosVista(GESTIONAR_MODULOS);
require_once "vista/modulos/modulos_vista.php";