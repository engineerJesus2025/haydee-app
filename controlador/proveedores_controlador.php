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
                    Bitacora::registrar(CONSULTAR, GESTIONAR_PROVEEDORES);
                }
                break;

            case 'consultar_proveedor':
                $respuesta = $proveedor->realizar_consulta('consultar_proveedor');
                // Bitácora opcional, se podría omitir o agregar
                break;

            case 'registrar':
                $respuesta = $proveedor->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $nuevos = [
                        'nombre_proveedor' => $proveedor->get_nombre_proveedor(),
                        'servicio' => $proveedor->get_servicio(),
                        'rif' => $proveedor->get_rif(),
                        'direccion' => $proveedor->get_direccion()
                    ];
                    Bitacora::registrar(REGISTRAR, GESTIONAR_PROVEEDORES, null, null, $nuevos);
                }
                break;

            case 'modificar':
                // Obtener datos anteriores
                $tempProveedor = new Proveedores();
                $tempProveedor->set_id_proveedor($proveedor->get_id_proveedor());
                $datosAnteriores = $tempProveedor->realizar_consulta('consultar_proveedor');
                $anterior = $datosAnteriores['estatus'] ? [
                    'nombre_proveedor' => $datosAnteriores['datos']['nombre_proveedor'] ?? '',
                    'servicio' => $datosAnteriores['datos']['servicio'] ?? '',
                    'rif' => $datosAnteriores['datos']['rif'] ?? '',
                    'direccion' => $datosAnteriores['datos']['direccion'] ?? ''
                ] : [];

                $respuesta = $proveedor->realizar_consulta('modificar');
                if ($respuesta['estatus']) {
                    $nuevo = [
                        'nombre_proveedor' => $proveedor->get_nombre_proveedor(),
                        'servicio' => $proveedor->get_servicio(),
                        'rif' => $proveedor->get_rif(),
                        'direccion' => $proveedor->get_direccion()
                    ];
                    Bitacora::registrar(MODIFICAR, GESTIONAR_PROVEEDORES, null, $anterior, $nuevo);
                }
                break;

            case 'eliminar':
                // Obtener datos anteriores
                $tempProveedor = new Proveedores();
                $tempProveedor->set_id_proveedor($proveedor->get_id_proveedor());
                $datosProveedor = $tempProveedor->realizar_consulta('consultar_proveedor');
                $anterior = $datosProveedor['estatus'] ? [
                    'nombre_proveedor' => $datosProveedor['datos']['nombre_proveedor'] ?? '',
                    'servicio' => $datosProveedor['datos']['servicio'] ?? '',
                    'rif' => $datosProveedor['datos']['rif'] ?? '',
                    'direccion' => $datosProveedor['datos']['direccion'] ?? ''
                ] : [];

                $respuesta = $proveedor->realizar_consulta('eliminar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_PROVEEDORES, null, $anterior, null);
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
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explícitamente
            if (isset($proveedor)) {
                $proveedor->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexión de seguridad

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

// Cargar la vista
require_once "vista/proveedores/proveedores_vista.php";