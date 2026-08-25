<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\modelo\Modulos;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_MODULOS, $operacion);
    
    $reglas = Modulos::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    $obj_modulo = new Modulos();
    $obj_modulo->set_id_modulo($_POST['id_modulo'] ?? null);
    $obj_modulo->set_nombre($_POST['nombre'] ?? null);

    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];
    $auditor = new GestorAuditoria($obj_modulo, Modulo::GESTIONAR_MODULOS);
    $codigoExito = HttpCodigo::OK->value;
    switch ($operacion) {
        case 'consultar':
            $respuesta = $obj_modulo->realizar_consulta('consultar');
            if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::CONSULTAR); }
            break;

        case 'consultar_modulo':
            $respuesta = $obj_modulo->realizar_consulta('consultar_modulo');
            http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
            break;

        case 'registrar_modulo':
            $respuesta = $obj_modulo->realizar_consulta('registrar_modulo');
            if ($respuesta['estatus']) { 
                $auditor->registrarAuditoria(Accion::REGISTRAR);
                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        case 'modificar_modulo':
            $auditor->capturarDatosAnteriores('consultar_modulo');
            $respuesta = $obj_modulo->realizar_consulta('modificar_modulo');
            if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::MODIFICAR); }
            break;

        case 'eliminar_modulo':
            $auditor->capturarDatosAnteriores('consultar_modulo');
            $respuesta = $obj_modulo->realizar_consulta('eliminar_modulo');
            if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::ELIMINAR); }
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($obj_modulo)) {$obj_modulo->cerrar();}
    Bitacora::cerrarConexionBitacora();

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

// Bloque de vista...
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_MODULOS);
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_MODULOS);
$btn_nuevo = [
    'target'  => '#modal_modulo',
    'texto'   => 'Nuevo Módulo',
    'tooltip' => 'Registrar Nuevo Módulo'
];
$placeholder_buscar = "Buscar módulo...";

require_once "vista/modulos/modulos_vista.php";

