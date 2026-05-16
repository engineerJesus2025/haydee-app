<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;

use haydee\servicios\Sesiones;
use haydee\modelo\Rol;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;

// Verificaciones de seguridad
Sesiones::autorizarAcceso(Modulo::GESTIONAR_ROLES, Accion::CONSULTAR);

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_ROLES, $operacion);
    
    $reglas = Rol::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        // ID para evitar choques del campo UNIQUE al modificar
        $contexto = ['exclude_id' => $_POST['id_rol'] ?? null];
        $validador->validarConjunto($_POST, $reglas, $contexto);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::BAD_REQUEST->value;
            http_response_code($codigoHttp);
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
    $auditor = new GestorAuditoria($rol, Modulo::GESTIONAR_ROLES);

    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $rol->realizar_consulta('consultar');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::CONSULTAR); }
                break;

            case 'consultar_rol':
                $respuesta = $rol->realizar_consulta('consultar_rol');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
                break;

            case 'registrar_rol':
                $respuesta = $rol->realizar_consulta('registrar_rol');

                http_response_code($respuesta['estatus'] ? HttpCodigo::CREADO->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::REGISTRAR); }
                break;

            case 'modificar_rol':
                $auditor->capturarDatosAnteriores('consultar_rol');
                $respuesta = $rol->realizar_consulta('modificar_rol');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::MODIFICAR); }
                break;

            case 'eliminar_rol':
                $auditor->capturarDatosAnteriores('consultar_rol');
                $respuesta = $rol->realizar_consulta('eliminar_rol');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::ELIMINAR); }
                break;
            case 'consultar_permisos_rol':
                $respuesta = $rol->realizar_consulta('consultar_permisos_asignados');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en controlador roles: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($rol)) { $rol->cerrar(); }
            Bitacora::cerrarConexionBitacora();

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

    $validadorBD = new ValidadorBD();

    try {
        switch ($validar) {
            case 'nombre':
                $nombre = $_POST["nombre"] ?? '';
                $id_rol = $_POST["id_rol"] ?? null;
                
                // Verificamos si el nombre existe, ignorando el rol actual
                $existe = !$validadorBD->esUnico('roles', 'nombre', $nombre, 'id_rol', $id_rol);
                $respuesta = ['estatus' => true, 'existe' => $existe];
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en Validación AJAX Bancos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    if ($respuesta['estatus'] === true || isset($respuesta['existe'])) {
        http_response_code(HttpCodigo::OK->value);
    }

    echo json_encode($respuesta);
    exit;
}

// =========================================================
// CARGA DE DATOS PARA LA VISTA
// =========================================================
$registros_modulos = [];
$registros_permisos_usuarios = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_ROLES);
    
    $rol = new Rol();
    $matriz = $rol->realizar_consulta('consultar_matriz_permisos');
    if ($matriz['estatus']) {
        $registros_modulos = $matriz['datos']['modulos'];
        $registros_permisos_usuarios = $matriz['datos']['permisos'];
    }
    $rol->cerrar();
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_ROLES);
$btn_nuevo = [
    'target'  => '#modal_roles',
    'texto'   => 'Nuevo Rol',
    'tooltip' => 'Registrar Nuevo Rol'
];
$placeholder_buscar = "Buscar rol...";

require_once "vista/roles/roles_vista.php";

