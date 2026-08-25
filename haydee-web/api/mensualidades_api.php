<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\modelo\Mensualidad;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

$operacion = $operacion ?? '';
if (empty($operacion)) {
    throw new HaydeeException('No se especificó la operación.', HttpCodigo::BAD_REQUEST->value);
}

Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_MENSUALIDAD, $operacion, [], true);

$reglas = Mensualidad::obtenerReglas($operacion);
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    throw new HaydeeException('Protocolo HTTP denegado para esta operación.', HttpCodigo::METODO_NO_PERMITIDO->value);
}

if (!empty($reglas)) {
    $validador->validarConjunto($datosPeticion, $reglas);
    if ($validador->tieneErrores()) {
        $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
        throw new ValidacionException('Datos de formulario inválidos o incompletos.', $validador->obtenerErrores(), $codigoHttp);
    }
}

$mensualidad = new Mensualidad();
$auditor = new GestorAuditoria($mensualidad, Modulo::GESTIONAR_MENSUALIDAD);

$mensualidad->set_id_mensualidad($datosPeticion['id_mensualidad'] ?? null);
$mensualidad->set_monto($datosPeticion['monto'] ?? null);
$mensualidad->set_tasa_dolar($datosPeticion['tasa_dolar'] ?? null);
$mensualidad->set_porcentaje_interes($datosPeticion['porcentaje_interes'] ?? null);
$mensualidad->set_limite_mensualidad($datosPeticion['limite_mensualidad'] ?? null);
$mensualidad->set_apartamento_id($datosPeticion['apartamento_id'] ?? null);

$fecha = $datosPeticion['fecha'] ?? null;
if ($fecha && strpos($fecha, '-') !== false) {
    list($anio, $mes, $dia) = explode('-', $fecha);
    $mensualidad->set_mes((int)$mes);
    $mensualidad->set_anio((int)$anio);
} else {
    $mensualidad->set_mes(isset($datosPeticion['mes']) ? (int)$datosPeticion['mes'] : null);
    $mensualidad->set_anio(isset($datosPeticion['anio']) ? (int)$datosPeticion['anio'] : null);
}

switch ($operacion) {
    case 'consulta':
    case 'consultarPorMeses':
        $respuesta = $mensualidad->realizar_consulta('consultarPorMeses');
        if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::CONSULTAR);
        break;

    case 'verificar_meses':
        $respuesta = $mensualidad->realizar_consulta('verificarMeses');
        break;

    case 'consultar_mensualidad_apartamentos':
        $respuesta = $mensualidad->realizar_consulta('consultar_mensualidad_apartamentos');
        if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::CONSULTAR);
        break;

    case 'consultar_meses_mensualidad':
        $respuesta = $mensualidad->realizar_consulta('consultar_meses_mensualidad');
        break;

    case 'consultar_tasa_dolar':
        $respuesta = $mensualidad->realizar_consulta('consultar_tasa_dolar_mensualidades');
        break;

    case 'consultar_kpis':
        if ($esPropietario) $mensualidad->set_correo($correoUsuario);
        $respuesta = $mensualidad->realizar_consulta('consultar_kpis');
        break;

    case 'consultar_desglose':
        $respuesta = $mensualidad->realizar_consulta('consultar_desglose');
        if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::CONSULTAR);
        break;

    case 'registrar_mensualidad':
    case 'modificar_mensualidad':
        $datosApartamentos = $datosPeticion['datos_apartamentos'] ?? null;
        if (is_string($datosApartamentos)) {
            $datosApartamentos = json_decode($datosApartamentos, true);
        }
        
        if (empty($datosApartamentos) || json_last_error() !== JSON_ERROR_NONE) {
            throw new HaydeeException('Formato de datos de apartamentos inválido o vacío.', HttpCodigo::BAD_REQUEST->value);
        }
        $mensualidad->set_datos_apartamentos($datosApartamentos);
        
        $metodoModelo = ($operacion === 'registrar_mensualidad') ? 'registrar' : 'modificar';
        
        if ($operacion === 'modificar_mensualidad') {
            $auditor->capturarDatosAnteriores('consultar_desglose');
        }
        
        $respuesta = $mensualidad->realizar_consulta($metodoModelo);
        if ($respuesta['estatus']) {
            $auditor->registrarAuditoria(($operacion === 'registrar_mensualidad') ? Accion::REGISTRAR : Accion::MODIFICAR);
        }
        break;

    case 'eliminar_mensualidad':
        $auditor->capturarDatosAnteriores('consultar_desglose');
        $respuesta = $mensualidad->realizar_consulta('eliminar');
        if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::ELIMINAR);
        break;

    default:
        throw new HaydeeException('Operación no reconocida o implementada.', HttpCodigo::BAD_REQUEST->value);
}

if (!$respuesta['estatus']) {
    throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
}

$codigoExito = ($operacion === 'registrar_mensualidad') ? HttpCodigo::CREADO->value : HttpCodigo::OK->value;
http_response_code($codigoExito);
echo json_encode($respuesta);