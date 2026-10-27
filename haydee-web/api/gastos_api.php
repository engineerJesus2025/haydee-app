<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\enums\MetodoPago;
use haydee\ayuda\Validador;
use haydee\ayuda\ConstructorDetalles;
use haydee\modelo\Gastos;
use haydee\modelo\CuentasCondominio;
use haydee\modelo\Proveedores;
use haydee\modelo\TipoGasto;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;
use haydee\servicios\GestorTasa;
use haydee\servicios\EscanerComprobantes;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

$operacion = $operacion ?? '';
if (empty($operacion)) {
    throw new HaydeeException('No se especificó la operación.', HttpCodigo::BAD_REQUEST->value);
}

Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_GASTOS, $operacion, [], true);

$reglas = Gastos::obtenerReglas($operacion);
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    throw new HaydeeException('Protocolo HTTP denegado para esta operación.', HttpCodigo::METODO_NO_PERMITIDO->value);
}

if (!empty($reglas)) {
    $validador->validarConjunto($datosPeticion, $reglas);
    if ($validador->tieneErrores()) {
        $codigoHttp = $validador->tieneError404()
            ? HttpCodigo::NO_ENCONTRADO->value
            : HttpCodigo::NO_PROCESABLE->value;
        throw new ValidacionException('Datos de formulario inválidos o incompletos.', $validador->obtenerErrores(), $codigoHttp);
    }
}

$gastos  = new Gastos();
$auditor = new GestorAuditoria($gastos, Modulo::GESTIONAR_GASTOS);

if ($metodoHttp === 'POST' || $metodoHttp === 'PUT') {
    $gastos->set_clasificacion($datosPeticion['clasificacion'] ?? null);
    $gastos->set_descripcion_gasto($datosPeticion['descripcion_gasto'] ?? null);
    $gastos->set_presupuesto_id($datosPeticion['presupuesto_id'] ?? null);
    $gastos->set_concepto_id(
        $datosPeticion['concepto_id']
        ?? $datosPeticion['tipo_gasto_id']
        ?? null
    );
    $gastos->set_proveedor_id($datosPeticion['proveedor_id'] ?? null);
}

if (isset($datosPeticion['id_gasto'])) {
    $gastos->set_id_gasto($datosPeticion['id_gasto']);
}
if (isset($datosPeticion['id_detalle_gasto'])) {
    $gastos->set_id_detalle_gasto($datosPeticion['id_detalle_gasto']);
}
if (isset($datosPeticion['fecha'])) {
    $gastos->set_fecha($datosPeticion['fecha']);
}

// Tasa del dólar: solo en operaciones que crean/modifican registros
$operacionesMonetarias = ['registrar_gasto', 'modificar_gasto'];
if (in_array($operacion, $operacionesMonetarias, true)) {
    $gastos->set_tasa_dolar(GestorTasa::obtener());
}

// ==================== VALIDACIÓN DE DETALLES ====================
$operacionesConDetalles = ['registrar_gasto', 'modificar_gasto'];

if (in_array($operacion, $operacionesConDetalles, true)) {
    $esModificacion = ($operacion === 'modificar_gasto');

    $detalles = $datosPeticion['detalles']
        ?? ConstructorDetalles::ConstruirDetallesGastos($_POST, $_FILES, $esModificacion);

    if (empty($detalles)) {
        throw new HaydeeException('Debe proporcionar al menos un detalle de gasto.', HttpCodigo::BAD_REQUEST->value);
    }

    $reglasDetalle   = Gastos::obtenerReglasDetalles();
    $erroresDetalles = [];

    foreach ($detalles as $index => $detalle) {
        $validadorTemp = new Validador();

        // Compatibilidad: "fecha" -> "fecha_detalle"
        if (isset($detalle['fecha']) && !isset($detalle['fecha_detalle'])) {
            $detalle['fecha_detalle'] = $detalle['fecha'];
        }

        $metodosConComprobante = [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value];

        if (in_array($detalle['metodo_pago'] ?? '', $metodosConComprobante, true)) {
            $nombreInputFile = "imagen_{$index}";
            $hayNuevaImagen  = isset($_FILES[$nombreInputFile])
                && $_FILES[$nombreInputFile]['error'] === UPLOAD_ERR_OK;

            if (!$hayNuevaImagen) {
                $imagenExistente = '';
                if ($esModificacion) {
                    $imagenExistente = $datosPeticion['imagen_existente'][$index]
                        ?? ($_POST['imagen_existente'][$index] ?? '');
                }
                $detalle['imagen'] = empty($imagenExistente) ? '' : $imagenExistente;
            }
        }

        $validadorTemp->validarConjunto($detalle, $reglasDetalle);

        if ($validadorTemp->tieneErrores()) {
            foreach ($validadorTemp->obtenerErrores() as $campo => $mensajes) {
                $erroresDetalles["detalle_{$index}_{$campo}"] = $mensajes;
            }
        }
    }

    if (!empty($erroresDetalles)) {
        throw new ValidacionException(
            'Datos inválidos. Faltan comprobantes o referencias requeridas.',
            $erroresDetalles,
            HttpCodigo::BAD_REQUEST->value
        );
    }

    $gastos->set_detalles($detalles);
}

switch ($operacion) {
    // ==================== CONSULTAS ====================
    case 'consulta':
        $respuesta = $gastos->realizar_consulta('consultar');
        if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::CONSULTAR);
        break;

    case 'consultar_gasto':
        $gastos->set_id_gasto($datosPeticion['id_gasto'] ?? null);
        $respuesta = $gastos->realizar_consulta('consultar_gasto');
        if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::CONSULTAR);
        break;

    case 'obtener_catalogos':
        $proveedor = new Proveedores();
        $cuenta    = new CuentasCondominio();
        $tipoGasto = new TipoGasto();

        $respuesta = [
            'estatus' => true,
            'datos'   => [
                'proveedores'    => $proveedor->realizar_consulta('consultar')['datos'] ?? [],
                'cuentas'        => $cuenta->realizar_consulta('consultar')['datos'] ?? [],
                'tipos_gasto'    => $tipoGasto->realizar_consulta('consultar')['datos'] ?? [],
                'conceptos'      => $tipoGasto->realizar_consulta('consultar_conceptos')['datos'] ?? [],
            ]
        ];
        break;

    case 'listar_gastos_mes':
        $parametros = [
            'mes'  => $datosPeticion['mes']  ?? date('m'),
            'anio' => $datosPeticion['anio'] ?? date('Y')
        ];
        $respuesta = $gastos->realizar_consulta('listar_gastos_mes', $parametros);
        if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::CONSULTAR);
        break;

    case 'obtener_periodos':
        $respuesta = $gastos->realizar_consulta('obtener_periodos_activos');
        break;

    case 'totales_metodo_pago':
        $respuesta = $gastos->realizar_consulta('total_por_metodo_pago');
        break;

    // ==================== REGISTRO ====================
    case 'registrar_gasto':
        $respuesta = $gastos->realizar_consulta('registrar_gasto');

        if ($respuesta['estatus']) {
            $auditor->registrarAuditoria(Accion::REGISTRAR);
        }
        break;

    // ==================== MODIFICAR ====================
    case 'modificar_gasto':
        $auditor->capturarDatosAnteriores('consultar_auditoria');

        $respuesta = $gastos->realizar_consulta('modificar_gasto');

        if ($respuesta['estatus']) {
            $auditor->registrarAuditoria(Accion::MODIFICAR);
        }
        break;

    // ==================== ELIMINAR ====================
    case 'eliminar_gasto':
        $auditor->capturarDatosAnteriores('consultar_auditoria');

        $respuesta = $gastos->realizar_consulta('eliminar_gasto');

        if ($respuesta['estatus']) {
            $auditor->registrarAuditoria(Accion::ELIMINAR);
        }
        break;

    case 'escanear_comprobante':
        $respuesta = EscanerComprobantes::procesarPeticion($_FILES['comprobante'] ?? null);
        break;

    default:
        throw new HaydeeException('Operación no reconocida o implementada.', HttpCodigo::BAD_REQUEST->value);
}

if (!$respuesta['estatus']) {
    throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
}

$operacionesCreado = ['registrar_gasto', 'modificar_gasto', 'eliminar_gasto'];
$codigoExito = in_array($operacion, $operacionesCreado, true)
    ? HttpCodigo::CREADO->value
    : HttpCodigo::OK->value;

http_response_code($codigoExito);
echo json_encode($respuesta);