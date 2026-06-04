<?php
use haydee\modelo\Pagos;
use haydee\modelo\Apartamento;
use haydee\modelo\Banco;
use haydee\enums\HttpCodigo;
use haydee\ayuda\ConstructorDetalles;
use haydee\ayuda\Validador;
use haydee\servicios\Sesiones;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\servicios\GestorAuditoria;
use haydee\modelo\Bitacora;

// ==================== IDENTIDAD Y PERMISOS ====================
$identidad = Sesiones::autorizarAccesoAPI(Modulo::GESTIONAR_PAGOS, Accion::CONSULTAR, ['GET', 'POST', 'PUT']);

$rolUsuario = strtolower($identidad['rol'] ?? '');
$esPropietario = ($rolUsuario === 'propietario');
$correoUsuario = $identidad['correo'] ?? '';

// ==================== DETECCIÓN DE PROTOCOLO Y PAYLOAD ====================
$metodoHttp = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();
$metodoSobreescrito = $headers['X-HTTP-Method-Override'] ?? $_POST['_method'] ?? $_GET['_method'] ?? null;

if (!empty($metodoSobreescrito)) {
    $metodoHttp = strtoupper($metodoSobreescrito);
}

$datosPeticion = ($metodoHttp === 'GET') ? $_GET : $_POST;

$operacion = $datosPeticion['operacion'] ?? '';

if (empty($operacion)) {
    http_response_code(HttpCodigo::BAD_REQUEST->value);
    echo json_encode(['estatus' => false, 'mensaje' => 'No se especificó la operación.']);
    exit;
}

Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_PAGOS, $operacion);

// ==================== REGLAS Y FIREWALL DE PROTOCOLO HTTP ====================
$reglas = Pagos::obtenerReglas($operacion);
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    http_response_code(HttpCodigo::METODO_NO_PERMITIDO->value);
    echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores(), 'mensaje' => 'Protocolo HTTP denegado.']);
    exit;
}

// ==================== VALIDACIÓN DE DATOS ====================
if (!empty($reglas)) {
    $validador->validarConjunto($datosPeticion, $reglas);
    if ($validador->tieneErrores()) {
        $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::BAD_REQUEST->value;
        http_response_code($codigoHttp);
        echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores(), 'mensaje' => 'Datos inválidos.']);
        exit;
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
                if (in_array($detalle['tipo_pago'] ?? '', ['Transferencia', 'Pago Movil'])) {
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
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                echo json_encode([
                    'estatus' => false, 
                    'errores' => $erroresDetalles, 
                    'mensaje' => 'Faltan comprobantes o referencias requeridas.'
                ]);
                exit; // <-- Crucial: Matamos el proceso para que no guarde en BD
            }

            // Si pasa todo, guardamos en la base de datos
            $pagos->set_detalles($detalles);
            $respuesta = $pagos->realizar_consulta('registrar_pago');
            
            if ($respuesta['estatus']) {
                $pagos->set_detalles(null);
                $auditor->registrarAuditoria(Accion::REGISTRAR);
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
    exit;
}