<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Rol;
use haydee\modelo\Permisos; // Solo para validación de permisos (temporal)
use haydee\modelo\Bitacora;

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
    header('Content-Type: application/json');

    // Asignación masiva de campos que pueden llegar
    $rol->set_id_rol($_POST['id_rol'] ?? null);
    $rol->set_nombre($_POST['nombre'] ?? null);
    // Los permisos vienen como JSON en 'permisos'
    $permisosJson = $_POST['permisos'] ?? '[]';
    $rol->set_permisos_asignados(json_decode($permisosJson, true) ?: []);

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try {
        switch ($operacion) {
            case 'consulta':
                $respuesta = $rol->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(CONSULTAR, GESTIONAR_ROLES, 'Consulta general de roles');
                }
                break;

            case 'registrar_rol':
                // Registrar el rol
                $respuesta = $rol->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $idRol = $respuesta['lastId'];
                    // Asignar permisos si hay
                    if (!empty($rol->get_permisos_asignados())) {
                        $rol->set_id_rol($idRol);
                        $resPermisos = $rol->realizar_consulta('sincronizar_permisos');
                        if (!$resPermisos['estatus']) {
                            // Si falla la asignación de permisos, podríamos considerar eliminar el rol (rollback manual)
                            // Pero el modelo ya maneja transacción interna, así que si falla no se insertó nada.
                            $respuesta = $resPermisos;
                            break;
                        }
                    }
                    Bitacora::registrar(REGISTRAR, GESTIONAR_ROLES, 'Rol: ' . $rol->get_nombre());
                }
                break;

            case 'consulta_especifica':
                $respuesta = $rol->realizar_consulta('consultar_rol');
                break;

            case 'consulta_permisos':
                $respuesta = $rol->realizar_consulta('consultar_permisos_asignados');
                break;

            case 'modificar':
                // Editar rol
                $respuesta = $rol->realizar_consulta('editar');
                if ($respuesta['estatus']) {
                    // Sincronizar permisos (el modelo se encarga de reemplazar)
                    $resPermisos = $rol->realizar_consulta('sincronizar_permisos');
                    if (!$resPermisos['estatus']) {
                        $respuesta = $resPermisos;
                        break;
                    }
                    Bitacora::registrar(MODIFICAR, GESTIONAR_ROLES, 'Rol: ' . $rol->get_nombre());
                }
                break;

            case 'eliminar':
                // Obtener datos para bitácora
                $copia = clone $rol;
                $datosRol = $copia->realizar_consulta('consultar_rol');
                $nombreRol = $datosRol['estatus'] ? ($datosRol['datos']['nombre'] ?? '') : '';

                $respuesta = $rol->realizar_consulta('eliminar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_ROLES, 'Rol: ' . $nombreRol);
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
    }

    echo json_encode($respuesta);
    exit;
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
                $permisosModel = new Permisos();
                $arreglo_id_permisos = $_POST["valor"] ?? [];
                $permisosModel->set_id_permiso($arreglo_id_permisos);
                $respuesta = $permisosModel->realizar_consulta('validar_permisos_usuarios');
                break;
        }
    } catch (Exception $e) {
        error_log("Error en validación AJAX: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    echo json_encode($respuesta);
    exit;
}

// Cargar la vista
require_once "vista/roles/rol_vista.php";