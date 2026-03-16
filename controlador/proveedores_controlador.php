<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Proveedores;
use haydee\modelo\Bitacora;
use haydee\servicios\GestorAuditoria;

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

    // Instanciamos el auditor
    $auditor = new GestorAuditoria($proveedor, GESTIONAR_PROVEEDORES);

    try {
        switch ($operacion) {
            case 'consulta':
                $respuesta = $proveedor->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'consultar_proveedor':
                $respuesta = $proveedor->realizar_consulta('consultar_proveedor');
                // Bitácora opcional, se podría omitir o agregar
                break;

            case 'registrar':
                $respuesta = $proveedor->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            case 'modificar':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_proveedor');

                $respuesta = $proveedor->realizar_consulta('modificar');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('modificar'); 
                }
                break;

            case 'eliminar':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_proveedor');

                $respuesta = $proveedor->realizar_consulta('eliminar');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('eliminar'); 
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_PROVEEDORES);
}

// Cargar la vista
require_once "vista/proveedores/proveedores_vista.php";