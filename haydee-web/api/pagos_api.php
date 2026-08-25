<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\enums\MetodoPago;
use haydee\enums\TipoEventoNotificacion;
use haydee\ayuda\ConstructorDetalles;
use haydee\ayuda\Validador;
use haydee\modelo\Pagos;
use haydee\modelo\Apartamento;
use haydee\modelo\Banco;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;
use haydee\servicios\GestorNotificaciones;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;
use haydee\excepciones\SeguridadException;

$operacion = $operacion ?? '';
if (empty($operacion)) {
    throw new HaydeeException('No se especificó la operación.', HttpCodigo::BAD_REQUEST->value);
}

Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_PAGOS, $operacion, [], true);

$reglas = Pagos::obtenerReglas($operacion);
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

$pagos = new Pagos();
$auditor = new GestorAuditoria($pagos, Modulo::GESTIONAR_PAGOS);

if ($metodoHttp === 'POST' || $metodoHttp === 'PUT') {
    $pagos->set_id_pago($datosPeticion['id_pago'] ?? null);
    $pagos->set_observacion($datosPeticion['observacion'] ?? null);
    $pagos->set_apartamento_id($datosPeticion['apartamento_id'] ?? null);
    $pagos->set_mensualidad_id($datosPeticion['mensualidad_id'] ?? null);
    $pagos->set_tasa_dolar($datosPeticion['tasa_dolar'] ?? 1);
    
    $estadoPorDefecto = $esPropietario ? 'PENDIENTE' : 'PROCESADO';
    $pagos->set_estado($datosPeticion['estado'] ?? $estadoPorDefecto);

    if ($esPropietario) $pagos->set_correo($correoUsuario);
}

switch ($operacion) {
    case 'consulta':
        $respuesta = $esPropietario ? $pagos->realizar_consulta('consultar_por_correo') : $pagos->realizar_consulta('consultar');
        if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::CONSULTAR);
        break;

    case 'obtener_catalogos_base':
        $apartamentoAux = new Apartamento();
        $bancoAux = new Banco();
        if ($esPropietario) {
            $apartamentoAux->set_correo($correoUsuario);
            $resApartamentos = $apartamentoAux->realizar_consulta('obtener_apartamentos_por_correo');
        } else {
            $resApartamentos = $apartamentoAux->realizar_consulta('consultar_listado');
        }
        $respuesta = [
            'estatus' => true,
            'datos' => [
                'apartamentos' => $resApartamentos['datos'] ?? [],
                'bancos'       => $bancoAux->realizar_consulta('consultar')['datos'] ?? []
            ]
        ];
        break;

    case 'consultar_mensualidades':
        $pagos->set_apartamento_id($datosPeticion['apartamento_id'] ?? null);
        $respuesta = $pagos->realizar_consulta('consultarMensualidadPendiente');
        break;

    case 'consultar_pago':
        $pagos->set_id_pago($datosPeticion['id_pago'] ?? null);
        $respuesta = $pagos->realizar_consulta('consultar_pago');
        break;

    case 'listar_pagos_mes':
        $parametros = [
            'mes' => $datosPeticion['mes'] ?? date('m'),
            'anio' => $datosPeticion['anio'] ?? date('Y'),
            'correo' => $esPropietario ? $correoUsuario : null
        ];
        $respuesta = $pagos->realizar_consulta('consultar_por_mes_anio', $parametros);
        break;

    case 'obtener_periodos':
        $respuesta = $pagos->realizar_consulta('obtener_periodos_activos');
        break;

    case 'consultar_estado_cuenta':
        if ($esPropietario) $pagos->set_correo($correoUsuario);
        $respuesta = $pagos->realizar_consulta('consultar_estado_cuenta');
        break;

    case 'registrar_pago':
        $detalles = $datosPeticion['detalles'] ?? ConstructorDetalles::ConstruirDetallesPagos($_POST, $_FILES, false);

        if (empty($detalles)) throw new HaydeeException('Debe proporcionar al menos un detalle de pago.', HttpCodigo::BAD_REQUEST->value);

        $reglasDetalle = Pagos::obtenerReglasDetalles();
        $erroresDetalles = [];

        foreach ($detalles as $index => $detalle) {
            $validadorTemp = new Validador();
            
            if (isset($detalle['fecha']) && !isset($detalle['fecha_pago'])) {
                $detalle['fecha_pago'] = $detalle['fecha'];
            }

            $metodosConComprobante = [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value];
            if (in_array($detalle['tipo_pago'] ?? '', $metodosConComprobante)) {
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

        $pagos->set_detalles($detalles);
        $respuesta = $pagos->realizar_consulta('registrar_pago');
        
        if ($respuesta['estatus']) {
            $auditor->registrarAuditoria(Accion::REGISTRAR);

            $id_nuevo_pago = $respuesta['id'] ?? $respuesta['lastId'] ?? null;
            if ($id_nuevo_pago) {
                GestorNotificaciones::notificarAdmins(
                    "Nuevo Pago Registrado", 
                    "Requiere revisión y aprobación.", 
                    "pagos", 
                    $id_nuevo_pago, 
                    TipoEventoNotificacion::PAGO_RECIBIDO->value
                );
            }
        }
        break;

    case 'cambiar_estado_pago':
        if ($esPropietario) throw new SeguridadException('Acción no autorizada para residentes.', HttpCodigo::PROHIBIDO->value);
        
        $auditor->capturarDatosAnteriores('consultar_cabecera_pago');
        $respuesta = $pagos->realizar_consulta('cambiar_estado_pago'); 
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

$codigoExito = ($operacion === 'registrar_pago') ? HttpCodigo::CREADO->value : HttpCodigo::OK->value;
http_response_code($codigoExito);
echo json_encode($respuesta);