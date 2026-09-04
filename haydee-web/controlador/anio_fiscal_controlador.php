<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\modelo\AnioFiscal;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria; 
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_ANIO_FISCAL, $operacion);

    $reglas = AnioFiscal::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];
    // Instancia del modelo
    $anioFiscal = new AnioFiscal();
    // Asignacion masiva
    $anioFiscal->set_id_anio_fiscal($_POST['id_anio_fiscal'] ?? null);
    $anioFiscal->set_fecha_inicio($_POST['fecha_inicio'] ?? null);
    $anioFiscal->set_fecha_cierre($_POST['fecha_cierre'] ?? null);
    $anioFiscal->set_estado($_POST['estado'] ?? null);
    $anioFiscal->set_descripcion($_POST['descripcion'] ?? null);

    // Instanciamos el auditor
    $auditor = new GestorAuditoria($anioFiscal, Modulo::GESTIONAR_ANIO_FISCAL);

    $codigoExito = HttpCodigo::OK->value;
    switch ($operacion) {
        case 'consultar_anios_fiscales':
            $respuesta = $anioFiscal->realizar_consulta('consultar');

            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::CONSULTAR);
            }
            break;

        case 'registrar':
            $auditor->capturarDatosAnteriores('consultar_anio_fiscal');
            $respuesta = $anioFiscal->realizar_consulta('registrar');

            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::REGISTRAR);
                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        case 'consulta_especifica':
            $respuesta = $anioFiscal->realizar_consulta('consultar_anio_fiscal');

            http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
            break;

        case 'modificar':
            $auditor->capturarDatosAnteriores('consultar_anio_fiscal');
            $respuesta = $anioFiscal->realizar_consulta('modificar');
            
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::MODIFICAR);
            }
            break;
        case 'eliminar':
            $auditor->capturarDatosAnteriores('consultar_anio_fiscal');
            $respuesta = $anioFiscal->realizar_consulta('eliminar');
            
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::ELIMINAR);
            }
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($anioFiscal)) {$anioFiscal->cerrar();}
    Bitacora::cerrarConexionBitacora();

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_ANIO_FISCAL);
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_ANIO_FISCAL);

$btn_nuevo = [
    'target'  => '#modal_anio_fiscal',
    'texto'   => 'Nuevo Año Fiscal',
    'tooltip' => 'Registrar Nuevo Año Fiscal'
];
$placeholder_buscar = "Buscar año fiscal...";

// Cargar la vista
require_once "vista/anio_fiscal/anio_fiscal_vista.php";

