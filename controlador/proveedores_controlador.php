<?php
use haydee\servicios\Sesiones;
use haydee\modelo\Proveedores;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;

// Verificaciones de seguridad
Sesiones::validarMetodoHTTP(['GET', 'POST']);
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_PROVEEDORES, CONSULTAR);

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(GESTIONAR_PROVEEDORES, $operacion);
    
    // =========================================================
    // 1. VALIDACIÓN CENTRALIZADA
    // =========================================================
    $reglas = Proveedores::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        // Le pasamos el ID en el contexto para que ignore su propio registro al validar RIF/Nombre
        $contexto = ['exclude_id' => $_POST['id_proveedor'] ?? null];
        $validador->validarConjunto($_POST, $reglas, $contexto);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? 404 : 400;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    $proveedor = new Proveedores();
    
    $proveedor->set_id_proveedor($_POST['id_proveedor'] ?? null);
    $proveedor->set_nombre_proveedor($_POST['nombre_proveedor'] ?? null);
    $proveedor->set_servicio($_POST['servicio'] ?? null);
    $proveedor->set_rif($_POST['rif'] ?? null);
    $proveedor->set_direccion($_POST['direccion'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($proveedor, GESTIONAR_PROVEEDORES);

    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $proveedor->realizar_consulta('consultar');
                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('consultar'); }
                break;

            case 'consultar_proveedor':
                $respuesta = $proveedor->realizar_consulta('consultar_proveedor');
                http_response_code($respuesta['estatus'] ? 200 : 404);
                break;

            case 'registrar_proveedor':
                $respuesta = $proveedor->realizar_consulta('registrar_proveedor');

                http_response_code($respuesta['estatus'] ? 201 : 400);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('registrar'); }
                break;

            case 'modificar_proveedor':
                $auditor->capturarDatosAnteriores('consultar_proveedor');
                $respuesta = $proveedor->realizar_consulta('modificar_proveedor');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('modificar'); }
                break;

            case 'eliminar_proveedor':
                $auditor->capturarDatosAnteriores('consultar_proveedor');
                $respuesta = $proveedor->realizar_consulta('eliminar_proveedor');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('eliminar'); }
                break;

            default:
                http_response_code(400);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error en controlador proveedores: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($proveedor)) { $proveedor->cerrar(); }
            Bitacora::cerrarConexionBitacora();

            echo json_encode($respuesta);
            exit;
        }
    }
}

// Bloque AJAX Revisar 
// ...

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_PROVEEDORES);
}
$permisosVista = Sesiones::obtenerPermisosVista(GESTIONAR_PROVEEDORES);
$btn_nuevo = [
    'target'  => '#modal_proveedores',
    'texto'   => 'Nuevo Proveedor',
    'tooltip' => 'Registrar Nuevo Proveedor'
];
$placeholder_buscar = "Buscar proveedor...";

require_once "vista/proveedores/proveedores_vista.php";