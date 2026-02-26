<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Permisos;
use haydee\modelo\Bitacora;

Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_PERMISOS, CONSULTAR); // Ajusta según tus constantes

$permiso = new Permisos();

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];
    $permiso->set_id_permiso($_POST['id_permiso'] ?? null);
    $permiso->set_accion($_POST['accion'] ?? null);

    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $permiso->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(CONSULTAR, GESTIONAR_PERMISOS, 'Consulta general de módulos');
                    echo json_encode(['datos' => $respuesta['datos']]);
                } else {
                    echo json_encode(['datos' => [], 'error' => $respuesta['mensaje']]);
                }
                exit;

            case 'consultar_unico':
                $respuesta = $permiso->realizar_consulta('consultar_unico');
                break;

            case 'registrar':
                $respuesta = $permiso->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $nuevos = ['accion' => $permiso->get_accion()];
                    Bitacora::registrar(REGISTRAR, GESTIONAR_PERMISOS, '', null, null, $nuevos);
                }
                break;

            case 'modificar':
                // Obtener anteriores
                $tempPermiso = new Permisos();
                $tempPermiso->set_id_permiso($permiso->get_id_permiso());
                $datosAnteriores = $tempPermiso->realizar_consulta('consultar_unico');
                $anterior = $datosAnteriores['estatus'] ? $datosAnteriores['datos'] : [];

                $respuesta = $permiso->realizar_consulta('modificar');
                if ($respuesta['estatus']) {
                    $nuevo = ['accion' => $permiso->get_accion()];
                    Bitacora::registrar(MODIFICAR, GESTIONAR_PERMISOS, '', null, $anterior, $nuevo);
                }
                break;

            case 'eliminar':
                // Obtener datos anteriores
                $tempPermiso = new Permisos();
                $tempPermiso->set_id_permiso($permiso->get_id_permiso());
                $datosAnteriores = $tempPermiso->realizar_consulta('consultar_unico');
                $anterior = $datosAnteriores['estatus'] ? $datosAnteriores['datos'] : [];

                $respuesta = $permiso->realizar_consulta('eliminar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_PERMISOS, '', null, $anterior, null);
                }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    echo json_encode($respuesta);
    exit;
}

// Cargar vista (si existe)
require_once "vista/permisos/permisos_vista.php";