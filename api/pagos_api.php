<?php
use haydee\modelo\Pagos;
use haydee\modelo\Apartamento;
use haydee\modelo\Banco;
use haydee\enums\HttpCodigo;
use haydee\enums\EstadoPago;
use haydee\ayuda\ConstructorDetalles;
use haydee\ayuda\Validador;
use haydee\servicios\Sesiones;
use haydee\enums\Modulo;
use haydee\enums\Accion;

// IDENTIDAD Y PERMISOS
$identidad = Sesiones::autorizarAccesoAPI(Modulo::GESTIONAR_PAGOS, Accion::CONSULTAR, ['GET', 'POST', 'PUT']);

$rolUsuario = strtolower($identidad['rol'] ?? '');
$esPropietario = ($rolUsuario === 'propietario');
$correoUsuario = $identidad['correo'] ?? '';

// UNIFICACIÓN DEL PAYLOAD
$metodoHttp = $_SERVER['REQUEST_METHOD'];

$datosPeticion = ($metodoHttp === 'GET') ? $_GET : $_POST;
$operacion = $datosPeticion['operacion'] ?? '';

if (empty($operacion)) {
    http_response_code(HttpCodigo::BAD_REQUEST->value);
    echo json_encode(['estatus' => false, 'mensaje' => 'No se especificó la operación.']);
    exit;
}

Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_PAGOS, $operacion);

// VALIDACIÓN
$reglasCabecera = Pagos::obtenerReglas($operacion);

if (!empty($reglasCabecera)) {
    $validador = new Validador();
    $validador->validarConjunto($datosPeticion, $reglasCabecera);

    if ($validador->tieneErrores()) {
        $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::BAD_REQUEST->value;
        http_response_code($codigoHttp);
        echo json_encode([
            'estatus' => false, 
            'errores' => $validador->obtenerErrores(),
            'mensaje' => 'Datos inválidos o manipulados. La petición ha sido bloqueada.'
        ]);
        exit;
    }
}

$pagos = new Pagos();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];

try {
    // --- PETICIONES GET ---
    if ($metodoHttp === 'GET') {
        switch ($operacion) {
            case 'consulta':
                if ($esPropietario) {
                    $pagos->set_correo($correoUsuario);
                    $respuesta = $pagos->realizar_consulta('consultar_por_correo');
                } else {
                    $respuesta = $pagos->realizar_consulta('consultar');
                }
                break;

            case 'obtener_catalogos_base':
                $apartamento = new Apartamento();
                $banco = new Banco();

                if ($esPropietario) {
                    $apartamento->set_correo($correoUsuario);
                    $resApartamentos = $apartamento->realizar_consulta('obtener_apartamentos_por_correo');
                } else {
                    $resApartamentos = $apartamento->realizar_consulta('consultar_listado');
                }

                $respuesta = [
                    'estatus' => true,
                    'datos' => [
                        'apartamentos' => $resApartamentos['datos'] ?? [],
                        'bancos'       => $banco->realizar_consulta('consultar')['datos'] ?? []
                    ]
                ];
                $apartamento->cerrar();
                $banco->cerrar();
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
                    $respuesta = $pagos->realizar_consulta('consultar_estado_cuenta');
                } else {
                    $respuesta = ['estatus' => true, 'datos' => []];
                }
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación GET no permitida'];
                break;
        }
    } 
    // --- PETICIONES POST ---
    elseif ($metodoHttp === 'POST') {
        
        $pagos->set_id_pago($datosPeticion['id_pago'] ?? null);
        $pagos->set_observacion($datosPeticion['observacion'] ?? null);
        $pagos->set_apartamento_id($datosPeticion['apartamento_id'] ?? null);
        $pagos->set_mensualidad_id($datosPeticion['mensualidad_id'] ?? null);
        
        $estadoPorDefecto = $esPropietario ? 'PENDIENTE' : 'PROCESADO';
        $pagos->set_estado($datosPeticion['estado'] ?? $estadoPorDefecto);

        if ($esPropietario) {
            $pagos->set_correo($correoUsuario);
        }

        switch ($operacion) {
            case 'registrar_pago':
                $detalles = ConstructorDetalles::ConstruirDetallesPagos($_POST, $_FILES, false);
                
                if (empty($detalles)) {
                    http_response_code(HttpCodigo::BAD_REQUEST->value);
                    $respuesta = ['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un detalle de pago.'];
                    break; 
                }

                // --- ESCUDO ZERO TRUST: VALIDACIÓN DE RENGLONES (Detalles) ---
                $reglasDetalle = Pagos::obtenerReglasDetalles();
                $erroresDetalles = [];

                foreach ($detalles as $index => $detalle) {
                    $validadorTemp = new Validador();
                    $validadorTemp->validarConjunto($detalle, $reglasDetalle);
                    
                    if ($validadorTemp->tieneErrores()) {
                        foreach($validadorTemp->obtenerErrores() as $campo => $mensajes) {
                            $erroresDetalles["detalle_" . $index . "_" . $campo] = $mensajes; 
                        }
                    }
                }

                if (!empty($erroresDetalles)) {
                    http_response_code(HttpCodigo::BAD_REQUEST->value);
                    echo json_encode([
                        'estatus' => false, 
                        'errores' => $erroresDetalles, 
                        'mensaje' => 'Se detectó manipulación en los renglones del pago.'
                    ]);
                    exit;
                }

                $pagos->set_detalles($detalles);
                $respuesta = $pagos->realizar_consulta('registrar_pago');
                break;

            case 'modificar_pago':
                if ($esPropietario) {
                    http_response_code(HttpCodigo::PROHIBIDO->value);
                    $respuesta = ['estatus' => false, 'mensaje' => 'No autorizado para modificar pagos'];
                    break;
                }
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación POST no permitida'];
                break;
        }
    }
    // --- PETICIONES PUT ---
    elseif ($metodoHttp === 'PUT') {
        
        if ($esPropietario) {
            http_response_code(HttpCodigo::PROHIBIDO->value);
            echo json_encode(['estatus' => false, 'mensaje' => 'Acción no autorizada para residentes.']);
            exit;
        }

        switch ($operacion) {
            case 'cambiar_estado_pago':
                $pagos->set_id_pago($datosPeticion['id_pago'] ?? null);
                $pagos->set_estado($datosPeticion['estado'] ?? null);
                $pagos->set_observacion($datosPeticion['observacion'] ?? null);

                $respuesta = $pagos->realizar_consulta('cambiar_estado_pago'); 
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación PUT no soportada o ausente.'];
                break;
        }
    }

} catch (Exception $e) {
    error_log("Error en API Pagos: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    $pagos->cerrar();
    echo json_encode($respuesta);
}