<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\enums\MetodoPago;
use haydee\ayuda\Validador;
use haydee\ayuda\ConstructorDetalles;
use haydee\modelo\Gastos;
use haydee\modelo\Banco;
use haydee\modelo\Proveedores;
use haydee\modelo\SolicitudGasto;
use haydee\modelo\TipoGasto;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;
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
        $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
        throw new ValidacionException('Datos de formulario inválidos o incompletos.', $validador->obtenerErrores(), $codigoHttp);
    }
}

$gastos = new Gastos();
$auditor = new GestorAuditoria($gastos, Modulo::GESTIONAR_GASTOS);

if ($metodoHttp === 'POST' || $metodoHttp === 'PUT') {
    $gastos->set_id_gasto($datosPeticion['id_gasto'] ?? null);
    $gastos->set_clasificacion($datosPeticion['clasificacion'] ?? null);
    $gastos->set_descripcion_gasto($datosPeticion['descripcion_gasto'] ?? null);
    $gastos->set_solicitud_id($datosPeticion['solicitud_id'] ?? null);
    $gastos->set_tipo_gasto_id($datosPeticion['tipo_gasto_id'] ?? null);
    $gastos->set_proveedor_id($datosPeticion['proveedor_id'] ?? null);
}

switch ($operacion) {
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
        $banco = new Banco();
        $proveedor = new Proveedores();
        $solicitudGasto = new SolicitudGasto();
        $tipoGasto = new TipoGasto();

        $respuesta = [
            'estatus' => true,
            'datos' => [
                'proveedores' => $proveedor->realizar_consulta('consultar')['datos'] ?? [],
                'bancos'      => $banco->realizar_consulta('consultar')['datos'] ?? [],
                'solicitudes' => $solicitudGasto->realizar_consulta('consultar')['datos'] ?? [],
                'tipos_gasto' => $tipoGasto->realizar_consulta('consultar')['datos'] ?? []
            ]
        ];
        break;

    case 'listar_gastos_mes':
        $parametros = [
            'mes' => $datosPeticion['mes'] ?? date('m'),
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

    case 'registrar_gasto':
        $detalles = $datosPeticion['detalles'] ?? ConstructorDetalles::ConstruirDetallesGastos($_POST, $_FILES, false);
        
        if (empty($detalles)) throw new HaydeeException('Debe proporcionar al menos un detalle de gasto.', HttpCodigo::BAD_REQUEST->value);

        $reglasDetalle = Gastos::obtenerReglasDetalles();
        $erroresDetalles = [];

        foreach ($detalles as $index => $detalle) {
            $validadorTemp = new Validador();
            $metodosConComprobante = [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value];
            
            if (in_array($detalle['metodo_pago'] ?? '', $metodosConComprobante)) {
                $nombreInputFile = "imagen_{$index}";
                if (!isset($_FILES[$nombreInputFile]) || $_FILES[$nombreInputFile]['error'] !== UPLOAD_ERR_OK) {
                    $detalle['imagen'] = ''; 
                }
            }

            $validadorTemp->validarConjunto($detalle, $reglasDetalle);
            if ($validadorTemp->tieneErrores()) {
                foreach($validadorTemp->obtenerErrores() as $campo => $mensajes) {
                    $erroresDetalles["detalle_{$index}_{$campo}"] = $mensajes; 
                }
            }
        }

        if (!empty($erroresDetalles)) {
            throw new ValidacionException('Datos inválidos. Faltan comprobantes o referencias requeridas.', $erroresDetalles, HttpCodigo::BAD_REQUEST->value);
        }

        $gastos->set_detalles($detalles);
        $respuesta = $gastos->realizar_consulta('registrar_gasto');
        
        if ($respuesta['estatus']) {
            $auditor->registrarAuditoria(Accion::REGISTRAR);
        }
        break;

    case 'modificar_gasto':
        $detalles = ConstructorDetalles::ConstruirDetallesGastos($_POST, $_FILES, true);
        if (empty($detalles)) throw new HaydeeException('Debe proporcionar al menos un detalle de gasto.', HttpCodigo::BAD_REQUEST->value);

        $reglasDetalle = Gastos::obtenerReglasDetalles();
        $erroresDetalles = [];

        foreach ($detalles as $index => $detalle) {
            $validadorTemp = new Validador();
            $metodosConComprobante = [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value];
            
            if (in_array($detalle['metodo_pago'] ?? '', $metodosConComprobante)) {
                $nombreInputFile = "imagen_{$index}";
                $imagenExistente = $_POST["imagen_existente_{$index}"] ?? '';
                
                if (empty($imagenExistente)) {
                    if (!isset($_FILES[$nombreInputFile]) || $_FILES[$nombreInputFile]['error'] !== UPLOAD_ERR_OK) {
                        $detalle['imagen'] = ''; 
                    }
                }
            }

            $validadorTemp->validarConjunto($detalle, $reglasDetalle);
            if ($validadorTemp->tieneErrores()) {
                foreach($validadorTemp->obtenerErrores() as $campo => $mensajes) {
                    $erroresDetalles["detalle_{$index}_{$campo}"] = $mensajes; 
                }
            }
        }

        if (!empty($erroresDetalles)) {
            throw new ValidacionException('Datos inválidos. Faltan comprobantes o referencias requeridas.', $erroresDetalles, HttpCodigo::BAD_REQUEST->value);
        }

        $auditor->capturarDatosAnteriores('consultar_gasto');
        $gastos->set_detalles($detalles);
        $respuesta = $gastos->realizar_consulta('modificar_gasto');
        
        if ($respuesta['estatus']) {
            $auditor->registrarAuditoria(Accion::MODIFICAR);
        }
        break;

    default:
        throw new HaydeeException('Operación no reconocida o implementada.', HttpCodigo::BAD_REQUEST->value);
}

if (!$respuesta['estatus']) {
    throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
}

$codigoExito = ($operacion === 'registrar_gasto') ? HttpCodigo::CREADO->value : HttpCodigo::OK->value;
http_response_code($codigoExito);
echo json_encode($respuesta);