<?php
use haydee\enums\HttpCodigo;
use haydee\servicios\Autenticacion;
use haydee\modelo\SeguridadIP;
use haydee\modelo\Usuario;
use haydee\ayuda\Validador;
use haydee\servicios\Criptografia;

// ==================== DETECCIÓN DE PROTOCOLO Y PAYLOAD ====================
$metodoHttp = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();
$metodoSobreescrito = $headers['X-HTTP-Method-Override'] ?? $_POST['_method'] ?? $_GET['_method'] ?? null;

if (!empty($metodoSobreescrito)) {
    $metodoHttp = strtoupper($metodoSobreescrito);
}

$datosPeticion = ($metodoHttp === 'GET') ? $_GET : $_POST;
$operacion = $datosPeticion['operacion'] ?? 'refrescar_token';

// ==================== REGLAS Y FIREWALL ====================
$reglas = Usuario::obtenerReglas($operacion);
$validador = new Validador();

// Validar que el verbo HTTP sea el correcto (POST)
if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    http_response_code(HttpCodigo::METODO_NO_PERMITIDO->value);
    echo json_encode(['estatus' => false, 'mensaje' => 'Método HTTP no soportado para esta operación.']);
    exit;
}

// Validar la estructura de datos completa 
$validador->validarConjunto($datosPeticion, $reglas); 

if ($validador->tieneErrores()) {
    http_response_code(HttpCodigo::BAD_REQUEST->value);
    echo json_encode([
        'estatus' => false, 
        'mensaje' => 'Errores de validación en credenciales de refresco.',
        'errores' => $validador->obtenerErrores()
    ]);
    exit;
}

$idUsuarioAutenticado = $datosPeticion['id_usuario'];
$tokenRefresco = $datosPeticion['token']; // Unificado con las reglas de Usuario.php

$respuesta = ['estatus' => false, 'mensaje' => 'Operación no reconocida o implementada.'];
$auth = null;
$seguridadIP = null;

try {
    // ==================== CONTROL DE SEGURIDAD PERIMETRAL ====================
    $seguridadIP = new SeguridadIP();
    $seguridadIP->set_ip($_SERVER['REMOTE_ADDR']);

    $rateLimit = $seguridadIP->verificarRateLimit();
    if (!$rateLimit['estatus']) {
        $seguridadIP->registrarFallo();
        http_response_code($rateLimit['codigo_http'] ?? HttpCodigo::DEMASIADAS_PETICIONES->value);
        $respuesta = ['estatus' => false, 'mensaje' => $rateLimit['mensaje']];
    } else {
        
        $auth = new Autenticacion();

        // ==================== PROCESAMIENTO DE OPERACIONES ====================
        switch ($operacion) {
            case 'refrescar_token':
                $refreshToken = $datosPeticion['token'] ?? ''; // Recuerda que lo unificamos a 'token'

                $respuesta = $auth->renovarTokenJWT($idUsuarioAutenticado, $refreshToken);
                
                if ($respuesta['estatus']) {
                    $seguridadIP->limpiarFallo();
                    
                    //  VINCULAR LA NUEVA CLAVE AES DE RESTAURACIÓN
                    if (!empty($_POST['_temp_aes']) && !empty($_POST['_temp_disp'])) {
                        Criptografia::vincularDispositivoUsuario(
                            $_POST['_temp_disp'], 
                            $idUsuarioAutenticado, 
                            $_POST['_temp_aes']
                        );
                    }
                } else {
                    $seguridadIP->registrarFallo();
                    http_response_code(HttpCodigo::NO_AUTORIZADO->value);
                }
                break;
            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no reconocida o implementada.'];
                break;
        }
    }

    // ==================== MANEJO DE RESPUESTAS HTTP ====================
    if ($respuesta['estatus']) {
        http_response_code(HttpCodigo::OK->value);
    } else {
        if (http_response_code() === 200) {
            http_response_code(HttpCodigo::BAD_REQUEST->value);
        }
    }

} catch (Exception $e) {
    error_log("Error en API Refresh: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    if ($auth) $auth->cerrar();
    if ($seguridadIP) $seguridadIP->cerrar();
    
    echo json_encode($respuesta);
    exit;
}