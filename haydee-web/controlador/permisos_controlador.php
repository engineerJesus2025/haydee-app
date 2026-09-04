<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\modelo\Permisos;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_PERMISOS, $operacion);
    
    // VALIDACION
    $reglas = Permisos::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }
    
    $permiso = new Permisos();
    $permiso->set_id_permiso($_POST['id_permiso'] ?? null);
    $permiso->set_accion($_POST['accion'] ?? null);

    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];
    $auditor = new GestorAuditoria($permiso, Modulo::GESTIONAR_PERMISOS);
    $codigoExito = HttpCodigo::OK->value;
    switch ($operacion) {
        case 'consultar':
            $respuesta = $permiso->realizar_consulta('consultar');
            
            if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::CONSULTAR); }
            break;

        case 'consultar_permiso':
            $respuesta = $permiso->realizar_consulta('consultar_permiso');
            http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
            break;

        case 'registrar_permiso':
            $respuesta = $permiso->realizar_consulta('registrar_permiso');

            if ($respuesta['estatus']) { 
                $auditor->registrarAuditoria(Accion::REGISTRAR);
                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        case 'modificar_permiso':
            $auditor->capturarDatosAnteriores('consultar_permiso');
            $respuesta = $permiso->realizar_consulta('modificar_permiso');

            if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::MODIFICAR); }
            break;

        case 'eliminar_permiso':
            $auditor->capturarDatosAnteriores('consultar_permiso');
            $respuesta = $permiso->realizar_consulta('eliminar_permiso');

            
            if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::ELIMINAR); }
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($permiso)) {$permiso->cerrar();}
    Bitacora::cerrarConexionBitacora();

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_PERMISOS);
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_PERMISOS);
$btn_nuevo = [
    'target'  => '#modal_permiso',
    'texto'   => 'Nuevo Permiso',
    'tooltip' => 'Registrar Nuevo Permiso'
];
$placeholder_buscar = "Buscar permiso...";

require_once "vista/permisos/permisos_vista.php";

