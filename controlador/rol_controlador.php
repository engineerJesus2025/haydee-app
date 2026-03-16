<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Rol;
use haydee\modelo\Permisos; // Solo para validación de permisos (temporal)
use haydee\modelo\Bitacora;
use haydee\servicios\GestorAuditoria;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_ROLES, CONSULTAR);

// Instancia del modelo principal
$rol = new Rol();

// Obtener datos para la vista (módulos y permisos)
$matriz = $rol->realizar_consulta('consultar_matriz_permisos');
$registros_modulos = $matriz['estatus'] ? $matriz['datos']['modulos'] : [];
$registros_permisos_usuarios = $matriz['estatus'] ? $matriz['datos']['permisos'] : [];

if (isset($_POST["operacion"])) {
    // Asignación masiva de campos que pueden llegar
    $rol->set_id_rol($_POST['id_rol'] ?? null);
    $rol->set_nombre($_POST['nombre'] ?? null);
    // Los permisos vienen como JSON en 'permisos'
    $permisosJson = $_POST['permisos'] ?? '[]';
    $rol->set_permisos_asignados(json_decode($permisosJson, true) ?: []);

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    // Instanciamos el auditor
    $auditor = new GestorAuditoria($rol, GESTIONAR_ROLES);

    try {
        switch ($operacion) {
            case 'consulta':
                $respuesta = $rol->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'consulta_especifica':
                $respuesta = $rol->realizar_consulta('consultar_rol');
                break;

            case 'consulta_permisos':
                $respuesta = $rol->realizar_consulta('consultar_permisos_asignados');
                break;

            case 'registrar_rol':
                $respuesta = $rol->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $idRol = $respuesta['lastId'];
                    $permisosAsignados = $rol->get_permisos_asignados();
                    $nuevos = [
                        'nombre' => $rol->get_nombre(),
                        'cantidad_permisos' => count($permisosAsignados)
                    ];
                    if (!empty($permisosAsignados)) {
                        $rol->set_id_rol($idRol);
                        $resPermisos = $rol->realizar_consulta('sincronizar_permisos');
                        if (!$resPermisos['estatus']) {
                            $respuesta = $resPermisos;
                            break;
                        }
                    }
                    if ($respuesta['estatus']) {
                        $auditor->registrarAuditoria('registrar');
                    }
                }
                break;

            case 'modificar':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_rol');

                $respuesta = $rol->realizar_consulta('modificar');
                if ($respuesta['estatus']) {
                    $resPermisos = $rol->realizar_consulta('sincronizar_permisos');
                    if (!$resPermisos['estatus']) {
                        $respuesta = $resPermisos;
                        break;
                    }
                    $auditor->registrarAuditoria('modificar');
                }
                break;

            case 'eliminar':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_rol');

                $respuesta = $rol->realizar_consulta('eliminar');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('eliminar'); 
                }
                break;

            case 'ultimo_id':
                $respuesta = $rol->realizar_consulta('lastId');
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador roles: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explícitamente
            if (isset($rol)) {
                $rol->cerrar();
            }
            if (isset($permisos)) {
                $permisos->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexión de seguridad

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

// Validaciones AJAX (separadas)
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');

    $validar = $_POST["validar"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Validación no válida'];

    try {
        switch ($validar) {
            case 'nombre':
                $rol->set_nombre($_POST["nombre"] ?? '');
                $respuesta = $rol->realizar_consulta('verificar_nombre');
                break;

            case 'validar_permisos_usuarios':
                $permisos = new Permisos();
                $arreglo_id_permisos = $_POST["valor"] ?? [];
                $permisos->set_id_permiso($arreglo_id_permisos);
                $respuesta = $permisos->realizar_consulta('validar_permisos_usuarios');
                break;
        }
    } catch (Exception $e) {
        error_log("Error en validación AJAX: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_ROLES);
}

// Cargar la vista
require_once "vista/roles/rol_vista.php";