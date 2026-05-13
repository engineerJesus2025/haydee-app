<?php
use haydee\enums\Modulo;
use haydee\enums\Accion;

use haydee\servicios\Sesiones;
use haydee\modelo\Permisos;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;

// Verificaciones de seguridad
Sesiones::autorizarAcceso(Modulo::GESTIONAR_PERMISOS, Accion::CONSULTAR);

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_PERMISOS, $operacion);
    
    // 1. VALIDACIÃ“N
    $reglas = Permisos::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? 404 : 400;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // 2. LAZY LOADING Y ASIGNACIÃ“N
    $permiso = new Permisos();
    $permiso->set_id_permiso($_POST['id_permiso'] ?? null);
    $permiso->set_accion($_POST['accion'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($permiso, Modulo::GESTIONAR_PERMISOS);

    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $permiso->realizar_consulta('consultar');
                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::CONSULTAR); }
                break;

            case 'consultar_permiso':
                $respuesta = $permiso->realizar_consulta('consultar_permiso');
                http_response_code($respuesta['estatus'] ? 200 : 404);
                break;

            case 'registrar_permiso':
                $respuesta = $permiso->realizar_consulta('registrar_permiso');

                http_response_code($respuesta['estatus'] ? 201 : 400);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::REGISTRAR); }
                break;

            case 'modificar_permiso':
                $auditor->capturarDatosAnteriores('consultar_permiso');
                $respuesta = $permiso->realizar_consulta('modificar_permiso');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::MODIFICAR); }
                break;

            case 'eliminar_permiso':
                $auditor->capturarDatosAnteriores('consultar_permiso');
                $respuesta = $permiso->realizar_consulta('eliminar_permiso');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::ELIMINAR); }
                break;

            default:
                http_response_code(400);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error en controlador permisos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($permiso)) { $permiso->cerrar(); }
            Bitacora::cerrarConexionBitacora();

            echo json_encode($respuesta);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_PERMISOS);
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_PERMISOS);
$btn_nuevo = [
    'target'  => '#modal_permiso',
    'texto'   => 'Nuevo Permiso',
    'tooltip' => 'Registrar Nuevo Permiso'
];
$placeholder_buscar = "Buscar permiso...";

require_once "vista/permisos/permisos_vista.php";
