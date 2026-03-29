<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Rol;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;

Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_ROLES, CONSULTAR);

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];
    
    $reglas = Rol::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        // ID para evitar choques del campo UNIQUE al modificar
        $contexto = ['exclude_id' => $_POST['id_rol'] ?? null];
        $validador->validarConjunto($_POST, $reglas, $contexto);

        if ($validador->tieneErrores()) {
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    $rol = new Rol();
    $rol->set_id_rol($_POST['id_rol'] ?? null);
    $rol->set_nombre($_POST['nombre'] ?? null);
    
    // Decodificamos el JSON de permisos
    $permisosJson = $_POST['permisos'] ?? '[]';
    $rol->set_permisos_asignados(json_decode($permisosJson, true) ?: []);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($rol, GESTIONAR_ROLES);

    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $rol->realizar_consulta('consultar');
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('consultar'); }
                break;

            case 'consultar_rol':
                $respuesta = $rol->realizar_consulta('consultar_rol');
                break;

            case 'registrar_rol':
                $respuesta = $rol->realizar_consulta('registrar_rol');
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('registrar'); }
                break;

            case 'modificar_rol':
                $auditor->capturarDatosAnteriores('consultar_rol');
                $respuesta = $rol->realizar_consulta('modificar_rol');
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('modificar'); }
                break;

            case 'eliminar_rol':
                $auditor->capturarDatosAnteriores('consultar_rol');
                $respuesta = $rol->realizar_consulta('eliminar_rol');
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('eliminar'); }
                break;
            case 'consultar_permisos_rol':
                $respuesta = $rol->realizar_consulta('consultar_permisos_asignados');
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador roles: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($rol)) { $rol->cerrar(); }
            Bitacora::cerrarConexionBitacora();

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

    try {
        $validadorBD = new ValidadorBD();
        
        if ($validar === 'nombre') {
            $nombre = $_POST["nombre"] ?? '';
            $id_rol = $_POST["id_rol"] ?? null;
            
            // Verificamos si el nombre existe, ignorando el rol actual
            $existe = !$validadorBD->esUnico('roles', 'nombre', $nombre, 'id_rol', $id_rol);
            echo json_encode(['estatus' => true, 'existe' => $existe]);
            exit;
        } 

    } catch (Exception $e) {
        error_log("Error en validación AJAX Roles: " . $e->getMessage());
        echo json_encode(['estatus' => false, 'mensaje' => 'Error interno']);
        exit;
    }
}

// =========================================================
// CARGA DE DATOS PARA LA VISTA
// =========================================================
$registros_modulos = [];
$registros_permisos_usuarios = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_ROLES);
    
    $rol = new Rol();
    $matriz = $rol->realizar_consulta('consultar_matriz_permisos');
    if ($matriz['estatus']) {
        $registros_modulos = $matriz['datos']['modulos'];
        $registros_permisos_usuarios = $matriz['datos']['permisos'];
    }
    $rol->cerrar();
}
$permisosVista = Sesiones::obtenerPermisosVista(GESTIONAR_ROLES);
require_once "vista/roles/roles_vista.php";