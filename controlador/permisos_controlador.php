<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Permisos;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;

Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_PERMISOS, CONSULTAR);

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];
    
    // 1. VALIDACIÓN
    $reglas = Permisos::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // 2. LAZY LOADING Y ASIGNACIÓN
    $permiso = new Permisos();
    $permiso->set_id_permiso($_POST['id_permiso'] ?? null);
    $permiso->set_accion($_POST['accion'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($permiso, GESTIONAR_PERMISOS);

    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $permiso->realizar_consulta('consultar');
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('consultar'); }
                break;

            case 'consultar_permiso':
                $respuesta = $permiso->realizar_consulta('consultar_permiso');
                break;

            case 'registrar_permiso':
                $respuesta = $permiso->realizar_consulta('registrar_permiso');
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('registrar'); }
                break;

            case 'modificar_permiso':
                $auditor->capturarDatosAnteriores('consultar_permiso');
                $respuesta = $permiso->realizar_consulta('modificar_permiso');
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('modificar'); }
                break;

            case 'eliminar_permiso':
                $auditor->capturarDatosAnteriores('consultar_permiso');
                $respuesta = $permiso->realizar_consulta('eliminar_permiso');
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('eliminar'); }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador permisos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($permiso)) { $permiso->cerrar(); }
            Bitacora::cerrarConexionBitacora();

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_PERMISOS);
}
$permisosVista = Sesiones::obtenerPermisosVista(GESTIONAR_PERMISOS);
require_once "vista/permisos/permisos_vista.php";