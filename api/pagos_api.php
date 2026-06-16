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
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;
use haydee\servicios\GestorNotificaciones;

if (empty($operacion)) {
    throw new Exception('No se especificó la operación.', HttpCodigo::BAD_REQUEST->value);
}

Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_PAGOS, $operacion, [], true);

// ==================== REGLAS Y FIREWALL DE PROTOCOLO HTTP ====================
$reglas = Pagos::obtenerReglas($operacion);
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    $datosError = [
        'mensaje' => 'Protocolo HTTP denegado para esta operación.',
        'errores' => $validador->obtenerErrores()
    ];
    throw new \Exception(json_encode($datosError), HttpCodigo::METODO_NO_PERMITIDO->value);
}

// ==================== VALIDACIÓN DE DATOS ====================
if (!empty($reglas)) {
    $validador->validarConjunto($datosPeticion, $reglas);
    if ($validador->tieneErrores()) {
        $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::BAD_REQUEST->value;
        $datosError = [
            'mensaje' => 'Datos de formulario inválidos o incompletos.',
            'errores' => $validador->obtenerErrores()
        ];
        throw new \Exception(json_encode($datosError), $codigoHttp);
    }
}

// ==================== PROCESAMIENTO DE NEGOCIO ====================
$pagos = new Pagos();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];
$auditor = new GestorAuditoria($pagos, Modulo::GESTIONAR_PAGOS);
$apartamentoAux = null;
$bancoAux = null;

try {
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
        // ==================== CONSULTAS (GET) ====================
        case 'consulta':
            if ($esPropietario) {
                $respuesta = $pagos->realizar_consulta('consultar_por_correo');
            } else {
                $respuesta = $pagos->realizar_consulta('consultar');
            }
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
            if ($esPropietario) {
                $pagos->set_correo($correoUsuario);
            }
            $respuesta = $pagos->realizar_consulta('consultar_estado_cuenta');
            break;

        // ==================== ESCRITURA (POST) ====================
        case 'registrar_pago':
            // Construimos los renglones
            $detalles = $datosPeticion['detalles'] ?? ConstructorDetalles::ConstruirDetallesPagos($_POST, $_FILES, false);

            if (empty($detalles)) {
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un detalle de pago.'];
                break; 
            }

            $reglasDetalle = Pagos::obtenerReglasDetalles();
            $erroresDetalles = [];

            // ITERAMOS Y AUDITAMOS CADA RENGLÓN
            foreach ($detalles as $index => $detalle) {
                $validadorTemp = new Validador();
                
                // Mapeo adaptativo
                if (isset($detalle['fecha']) && !isset($detalle['fecha_pago'])) {
                    $detalle['fecha_pago'] = $detalle['fecha'];
                }

                // Si el método de pago exige imagen, verificamos que el archivo físico llegó al servidor
                $metodosConComprobante = [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value];
                if (in_array($detalle['tipo_pago'] ?? '', $metodosConComprobante)) {
                    $nombreInputFile = "imagen_{$index}"; // Ej: 'imagen_0'
                    
                    // Si no llegó archivo físico a la RAM de PHP, o llegó con error...
                    if (!isset($_FILES[$nombreInputFile]) || $_FILES[$nombreInputFile]['error'] !== UPLOAD_ERR_OK) {
                        // Ahora el Validador atrapará el campo vacío y bloqueará la API.
                        $detalle['imagen'] = ''; 
                    }
                }
                // =======================================================

                // Ejecutamos la validación ahora sí con datos crudos y reales
                $validadorTemp->validarConjunto($detalle, $reglasDetalle);
                
                if ($validadorTemp->tieneErrores()) {
                    foreach($validadorTemp->obtenerErrores() as $campo => $mensajes) {
                        $erroresDetalles["detalle_{$index}_{$campo}"] = $mensajes; 
                    }
                }
            }

            // BLOQUEO DE SEGURIDAD
            if (!empty($erroresDetalles)) {
                error_log("[Validación Pagos] Renglones rebotados: " . print_r($erroresDetalles, true));

                // Estructurar el error y lanzarlo al Gateway
                $datosError = [
                    'mensaje' => 'Datos inválidos. Faltan comprobantes o referencias requeridas.',
                    'errores' => $erroresDetalles
                ];
                throw new \Exception(json_encode($datosError), HttpCodigo::BAD_REQUEST->value);
            }

            // ==================== INICIO DE DEBUG ====================
            error_log("=== DEBUG MÓVIL - PAYLOAD PAGOS ===");
            error_log("POST Recibido: " . print_r($_POST, true));
            error_log("FILES Recibidos: " . print_r($_FILES, true));
            error_log("Detalles Construidos: " . print_r($detalles, true));
            error_log("===========================================");
            // ==================== FIN DE DEBUG ====================

            // Si pasa todo, guardamos en la base de datos
            $pagos->set_detalles($detalles);
            $respuesta = $pagos->realizar_consulta('registrar_pago');
            
            if ($respuesta['estatus']) {
                $pagos->set_detalles(null);
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

        // ==================== MODIFICACIONES (PUT) ====================
        case 'cambiar_estado_pago':
            if ($esPropietario) {
                http_response_code(HttpCodigo::PROHIBIDO->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Acción no autorizada para residentes.'];
                break;
            }
            $auditor->capturarDatosAnteriores('consultar_cabecera_pago');
            $respuesta = $pagos->realizar_consulta('cambiar_estado_pago'); 
            if ($respuesta['estatus']) {
                $pagos->set_detalles(null);
                $auditor->registrarAuditoria(Accion::MODIFICAR);
            }
            break;

        default:
            http_response_code(HttpCodigo::BAD_REQUEST->value);
            $respuesta = ['estatus' => false, 'mensaje' => 'Operación no reconocida o implementada.'];
            break;
    }

    // ==================== MANEJO MODERNO DE RESPUESTAS HTTP (MATCH) ====================
    if ($respuesta['estatus']) {
        $codigoExito = match ($operacion) {
            'registrar_pago' => HttpCodigo::CREADO->value,
            default          => HttpCodigo::OK->value,
        };
        http_response_code($codigoExito);
    } else {
        if (http_response_code() === 200) {
            http_response_code(HttpCodigo::BAD_REQUEST->value);
        }
    }

} catch (Exception $e) {
    error_log("Error en API Pagos: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    if ($pagos) $pagos->cerrar();
    if ($apartamentoAux) $apartamentoAux->cerrar();
    if ($bancoAux) $bancoAux->cerrar();
    Bitacora::cerrarConexionBitacora();
    echo json_encode($respuesta);
}