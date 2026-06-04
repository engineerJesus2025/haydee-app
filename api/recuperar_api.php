<?php
use haydee\enums\HttpCodigo;
use haydee\ayuda\Validador;
use haydee\servicios\Recuperacion;
use haydee\modelo\Usuario;
use haydee\modelo\SeguridadIP;

// ==================== DETECCIÓN DE PROTOCOLO Y PAYLOAD ====================
$metodoHttp = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();
$metodoSobreescrito = $headers['X-HTTP-Method-Override'] ?? $_POST['_method'] ?? $_GET['_method'] ?? null;

if (!empty($metodoSobreescrito)) {
    $metodoHttp = strtoupper($metodoSobreescrito);
}

$datosPeticion = ($metodoHttp === 'GET') ? $_GET : $_POST;

if (empty($datosPeticion) && in_array($metodoHttp, ['POST', 'PUT', 'DELETE'])) {
    $jsonCrudo = file_get_contents('php://input');
    $datosDecodificados = json_decode($jsonCrudo, true);
    
    if (json_last_error() === JSON_ERROR_NONE && is_array($datosDecodificados)) {
        $datosPeticion = $datosDecodificados;
    }
}
// =======================================================

$operacion = $datosPeticion['operacion'] ?? '';

if (empty($operacion)) {
    http_response_code(HttpCodigo::BAD_REQUEST->value);
    echo json_encode(['estatus' => false, 'mensaje' => 'No se especificó la operación.']);
    exit;
}

// ==================== REGLAS Y FIREWALL DE PROTOCOLO HTTP ====================
$reglas = Usuario::obtenerReglas($operacion);
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    http_response_code(HttpCodigo::METODO_NO_PERMITIDO->value);
    echo json_encode(['estatus' => false, 'mensaje' => 'Método HTTP no soportado para esta operación.']);
    exit;
}

// ==================== VALIDACIÓN DE DATOS ====================
if (!empty($reglas)) {
    $validador->validarConjunto($datosPeticion, $reglas, ['skip_unique' => true, 'skip_exists' => true]);

    if ($validador->tieneErrores()) {
        http_response_code(HttpCodigo::BAD_REQUEST->value);
        echo json_encode([
            'estatus' => false,
            'errores' => $validador->obtenerErrores(),
            'mensaje' => 'Datos de solicitud inválidos.'
        ]);
        exit;
    }
}

// ==================== PROCESAR OPERACIÓN ====================
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];
$serviceRecuperar = null;
$seguridadIP = null; 

try {
    // ==================== RATE LIMITING PROTEGIDO ====================
    $seguridadIP = new SeguridadIP();
    $seguridadIP->set_ip($_SERVER['REMOTE_ADDR']);

    $rateLimit = $seguridadIP->verificarRateLimit();
    if (!$rateLimit['estatus']) {
        $seguridadIP->registrarFallo();
        http_response_code($rateLimit['codigo_http'] ?? HttpCodigo::DEMASIADAS_SOLICITUDES->value);
        $respuesta = ['estatus' => false, 'mensaje' => $rateLimit['mensaje']];
    } else {
        
        // El perímetro está limpio, instanciamos el servicio de negocio
        $serviceRecuperar = new Recuperacion();

        switch ($operacion) {
            case 'solicitar_otp':
                $correo = $datosPeticion['correo'] ?? '';
                if (empty($correo)) {
                    http_response_code(HttpCodigo::BAD_REQUEST->value);
                    $respuesta = ['estatus' => false, 'mensaje' => 'El correo es obligatorio.'];
                    break;
                }
                $respuesta = $serviceRecuperar->enviarCorreoOTP($correo);
                
                // Si el correo no existe o el envío falla, sumamos penalización de IP
                if ($respuesta['estatus']) {
                    $seguridadIP->limpiarFallo();
                } else {
                    $seguridadIP->registrarFallo();
                }
                break;

            case 'restablecer_con_otp':
                $correo = $datosPeticion['correo'] ?? '';
                $otp = $datosPeticion['codigo'] ?? '';
                $contra = $datosPeticion['contra'] ?? '';

                if (empty($correo) || empty($otp) || empty($contra)) {
                    http_response_code(HttpCodigo::BAD_REQUEST->value);
                    $respuesta = ['estatus' => false, 'mensaje' => 'Todos los campos son requeridos para el restablecimiento.'];
                    break;
                }
                $respuesta = $serviceRecuperar->restablecerConOTP($correo, $otp, $contra);
                
                // Si el OTP es incorrecto, penalizamos de inmediato para mitigar fuerza bruta
                if ($respuesta['estatus']) {
                    $seguridadIP->limpiarFallo();
                } else {
                    $seguridadIP->registrarFallo();
                }
                break;

            case 'validar_otp':
                $correo = $datosPeticion['correo'] ?? '';
                $otp = $datosPeticion['codigo'] ?? '';

                if (empty($correo) || empty($otp)) {
                    http_response_code(HttpCodigo::BAD_REQUEST->value);
                    $respuesta = ['estatus' => false, 'mensaje' => 'El correo y el código son requeridos.'];
                    break;
                }
                
                $respuesta = $serviceRecuperar->validarOTP($correo, $otp);
                
                if ($respuesta['estatus']) {
                    $seguridadIP->limpiarFallo();
                } else {
                    $seguridadIP->registrarFallo(); // Penaliza intentos de fuerza bruta al OTP
                }
                break;

            case 'restablecer_con_token':
                $correo = $datosPeticion['correo'] ?? '';
                $tokenAutorizacion = $datosPeticion['token_autorizacion'] ?? '';
                $contra = $datosPeticion['contra'] ?? '';

                if (empty($correo) || empty($tokenAutorizacion) || empty($contra)) {
                    http_response_code(HttpCodigo::BAD_REQUEST->value);
                    $respuesta = ['estatus' => false, 'mensaje' => 'Faltan credenciales de autorización.'];
                    break;
                }
                
                $respuesta = $serviceRecuperar->restablecerConToken($correo, $tokenAutorizacion, $contra);
                
                if ($respuesta['estatus']) {
                    $seguridadIP->limpiarFallo();
                } else {
                    $seguridadIP->registrarFallo(); 
                }
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no reconocida o implementada.'];
                break;
        }
    }

    // ==================== ASIGNACIÓN DE CÓDIGOS HTTP (MATCH) ====================
    if ($respuesta['estatus']) {
        http_response_code(HttpCodigo::OK->value);
    } else {
        if (http_response_code() === 200) {
            http_response_code(HttpCodigo::BAD_REQUEST->value);
        }
    }

} catch (Exception $e) {
    error_log("Error en API Recuperar: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    // Cierre y liberación estricta de conexiones en memoria
    if ($serviceRecuperar && method_exists($serviceRecuperar, 'cerrar')) {
        $serviceRecuperar->cerrar();
    }
    if ($seguridadIP) {
        $seguridadIP->cerrar();
    }
    
    echo json_encode($respuesta);
    exit;
}