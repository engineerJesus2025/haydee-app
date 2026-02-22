<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Proveedores;
use haydee\modelo\Bitacora;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_PROVEEDORES, CONSULTAR);

// Instancia del modelo
$proveedor = new Proveedores();

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');

    // Asignación masiva de campos que pueden llegar
    $proveedor->set_id_proveedor($_POST['id_proveedor'] ?? null);
    $proveedor->set_nombre_proveedor($_POST['nombre_proveedor'] ?? null);
    $proveedor->set_servicio($_POST['servicio'] ?? null);
    $proveedor->set_rif($_POST['rif'] ?? null);
    $proveedor->set_direccion($_POST['direccion'] ?? null);

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try {
        switch ($operacion) {
            case 'consulta':
                $respuesta = $proveedor->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(CONSULTAR, GESTIONAR_PROVEEDORES, 'Consulta general de proveedores');
                }
                break;

            case 'registrar':
                $respuesta = $proveedor->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(REGISTRAR, GESTIONAR_PROVEEDORES,
                        $proveedor->get_nombre_proveedor() . ' - ' . $proveedor->get_rif()
                    );
                }
                break;

            case 'consultar_proveedor':
                $respuesta = $proveedor->realizar_consulta('consultar_proveedor');
                // Bitácora opcional, se podría omitir o agregar
                break;

            case 'modificar':
                $respuesta = $proveedor->realizar_consulta('modificar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(MODIFICAR, GESTIONAR_PROVEEDORES,
                        $proveedor->get_nombre_proveedor() . ' - ' . $proveedor->get_rif()
                    );
                }
                break;

            case 'eliminar':
                // Obtener datos para la bitácora antes de eliminar
                $copia = clone $proveedor;
                $datosProveedor = $copia->realizar_consulta('consultar_proveedor');
                $nombreRif = '';
                if ($datosProveedor['estatus']) {
                    $datos = $datosProveedor['datos'];
                    $nombreRif = ($datos['nombre_proveedor'] ?? '') . ' - ' . ($datos['rif'] ?? '');
                }

                $respuesta = $proveedor->realizar_consulta('eliminar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_PROVEEDORES, $nombreRif);
                }
                break;

            case 'lastId':
                $respuesta = $proveedor->realizar_consulta('lastId');
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    }

    echo json_encode($respuesta);
    exit;
}

// Cargar la vista
require_once "vista/proveedores/proveedores_vista.php";