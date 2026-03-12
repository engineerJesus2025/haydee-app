<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Modulos;
use haydee\modelo\Bitacora;

Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_MODULOS, CONSULTAR); // Ajusta según tus constantes

$obj_modulo = new Modulos();

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];
    $obj_modulo->set_id_modulo($_POST['id_modulo'] ?? null);
    $obj_modulo->set_nombre($_POST['nombre'] ?? null);

    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $obj_modulo->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(CONSULTAR, GESTIONAR_obj_moduloS);
                }
                break;

            case 'consultar_unico':
                $respuesta = $obj_modulo->realizar_consulta('consultar_unico');
                break;

            case 'registrar':
                $respuesta = $obj_modulo->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $nuevos = ['nombre' => $obj_modulo->get_nombre()];
                    Bitacora::registrar(REGISTRAR, GESTIONAR_obj_moduloS, null, null, $nuevos);
                }
                break;

            case 'modificar':
                // Obtener anteriores
                $tempobj_modulo = new Modulos;
                $tempobj_modulo->set_id_modulo($obj_modulo->get_id_modulo());
                $datosAnteriores = $tempobj_modulo->realizar_consulta('consultar_unico');
                $anterior = $datosAnteriores['estatus'] ? $datosAnteriores['datos'] : [];

                $respuesta = $obj_modulo->realizar_consulta('modificar');
                if ($respuesta['estatus']) {
                    $nuevo = ['nombre' => $obj_modulo->get_nombre()];
                    Bitacora::registrar(MODIFICAR, GESTIONAR_obj_moduloS, null, $anterior, $nuevo);
                }
                break;

            case 'eliminar':
                // Obtener datos anteriores
                $tempobj_modulo = new Modulos;
                $tempobj_modulo->set_id_modulo($obj_modulo->get_id_modulo());
                $datosAnteriores = $tempobj_modulo->realizar_consulta('consultar_unico');
                $anterior = $datosAnteriores['estatus'] ? $datosAnteriores['datos'] : [];

                $respuesta = $obj_modulo->realizar_consulta('eliminar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_obj_moduloS, null, $anterior, null);
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

// Cargar vista (si existe)
require_once "vista/modulos/modulos_vista.php";