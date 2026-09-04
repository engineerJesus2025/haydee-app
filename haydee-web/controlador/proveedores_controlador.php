<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\modelo\Proveedores;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_PROVEEDORES, $operacion);
    
    // VALIDACION
    $reglas = Proveedores::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        // Le pasamos el ID en el contexto para que ignore su propio registro al validar RIF/Nombre
        $contexto = ['exclude_id' => $_POST['id_proveedor'] ?? null];
        $validador->validarConjunto($_POST, $reglas, $contexto);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    $proveedor = new Proveedores();
    
    $proveedor->set_id_proveedor($_POST['id_proveedor'] ?? null);
    $proveedor->set_nombre_proveedor($_POST['nombre_proveedor'] ?? null);
    $proveedor->set_servicio($_POST['servicio'] ?? null);
    $proveedor->set_rif($_POST['rif'] ?? null);
    $proveedor->set_direccion($_POST['direccion'] ?? null);

    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];
    $auditor = new GestorAuditoria($proveedor, Modulo::GESTIONAR_PROVEEDORES);
    $codigoExito = HttpCodigo::OK->value;
    switch ($operacion) {
        case 'consultar':
            $respuesta = $proveedor->realizar_consulta('consultar');
            if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::CONSULTAR); }
            break;

        case 'consultar_proveedor':
            $respuesta = $proveedor->realizar_consulta('consultar_proveedor');
            http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
            break;

        case 'registrar_proveedor':
            $respuesta = $proveedor->realizar_consulta('registrar_proveedor');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::REGISTRAR);
                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        case 'modificar_proveedor':
            $auditor->capturarDatosAnteriores('consultar_proveedor');
            $respuesta = $proveedor->realizar_consulta('modificar_proveedor');
            if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::MODIFICAR); }
            break;

        case 'eliminar_proveedor':
            $auditor->capturarDatosAnteriores('consultar_proveedor');
            $respuesta = $proveedor->realizar_consulta('eliminar_proveedor');
            if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::ELIMINAR); }
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($proveedor)) {$proveedor->cerrar();}
    Bitacora::cerrarConexionBitacora();

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

// VALIDACIONES AJAX 
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');
    $validar = $_POST["validar"];
    $validadorBD = new ValidadorBD();

    switch ($validar) {
        case 'validar_clave_foranea':
            $tabla = $_POST['tabla'] ?? '';
            $campo = $_POST['nombre_clave'] ?? '';
            $valor = $_POST['valor'] ?? '';

            if (empty($tabla) || empty($campo) || empty($valor)) {
                throw new HaydeeException('Faltan parámetros de validación', HttpCodigo::BAD_REQUEST->value);
            }

            $existe = $validadorBD->existe($tabla, $campo, $valor);
            $respuesta = ['estatus' => $existe, 'mensaje' => $existe ? 'OK' : 'No existe'];
            break;
            
        case 'rif':
            $rif = $_POST["rif"] ?? '';
            $id_proveedor = $_POST["id_proveedor"] ?? null;
            
            $existe = !$validadorBD->esUnico('proveedores', 'rif', $rif, 'id_proveedor', $id_proveedor);
            $respuesta = ['estatus' => true, 'existe' => $existe];
            break;

        default:
            throw new HaydeeException('Validación no reconocida', HttpCodigo::BAD_REQUEST->value);
    }

    http_response_code(HttpCodigo::OK->value);
    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_PROVEEDORES);
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_PROVEEDORES);
$btn_nuevo = [
    'target'  => '#modal_proveedores',
    'texto'   => 'Nuevo Proveedor',
    'tooltip' => 'Registrar Nuevo Proveedor'
];
$placeholder_buscar = "Buscar proveedor...";

require_once "vista/proveedores/proveedores_vista.php";

