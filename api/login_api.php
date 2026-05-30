<?php
use haydee\enums\HttpCodigo;
use haydee\servicios\Autenticacion;
use haydee\servicios\Criptografia;
use haydee\modelo\SeguridadIP;
use haydee\ayuda\Validador;
use haydee\modelo\Usuario;

// ==================== DETECCIÓN DE PROTOCOLO Y PAYLOAD ====================
$metodoHttp = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();
$metodoSobreescrito = $headers['X-HTTP-Method-Override'] ?? $_POST['_method'] ?? $_GET['_method'] ?? null;

if (!empty($metodoSobreescrito)) {
    $metodoHttp = strtoupper($metodoSobreescrito);
}

$datosPeticion = ($metodoHttp === 'GET') ? $_GET : $_POST;

// Normalización estricta del payload para el modelo de Usuarios
if (isset($datosPeticion['correo'])) {
    $datosPeticion['usuario'] = $datosPeticion['correo'];
}
$operacion = $datosPeticion['operacion'] ?? 'entrar';

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
    // Saltamos la validación UNIQUE en BD porque el login solo inspecciona coincidencia
    $validador->validarConjunto($datosPeticion, $reglas, ['skip_unique' => true]);

    if ($validador->tieneErrores()) {
        http_response_code(HttpCodigo::BAD_REQUEST->value);
        echo json_encode([
            'estatus' => false,
            'errores' => $validador->obtenerErrores(),
            'mensaje' => 'Formato de credenciales inválido.'
        ]);
        exit;
    }
}

// Preparación de variables esenciales de autenticación
$correo = $datosPeticion['correo'] ?? '';
$contra = $datosPeticion['contra'] ?? '';

$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];
$auth = null;
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
        
        // ==================== PROCESO DE AUTENTICACIÓN ====================
        $auth = new Autenticacion();
        $resultado = $auth->login($correo, $contra, true, true);

        if ($resultado['estatus']) {
            $seguridadIP->limpiarFallo();

            // Filtrado estricto de seguridad para módulos visibles en la app Expo
            $modulosApp = ['GESTIONAR_PAGOS', 'GESTIONAR_GASTOS', 'GESTIONAR_MENSUALIDAD', 'GESTIONAR_CARTELERA_VIRTUAL'];
            $permisosFiltrados = array_values(array_filter($resultado['datos']['permisos'] ?? [], function($p) use ($modulosApp) {
                return in_array($p['modulo'], $modulosApp);
            }));

            $respuesta = [
                'estatus' => true,
                'mensaje' => 'Inicio de sesión exitoso',
                'datos' => [
                    'id_usuario' => $resultado['datos']['id_usuario'] ?? '',
                    'usuario'    => $resultado['datos']['nombre_completo'] ?? '',
                    'rol'        => $resultado['datos']['rol'] ?? '',
                    'correo'     => $resultado['datos']['correo'] ?? '',
                    'permisos'   => $permisosFiltrados
                ],
                'token_jwt'     => $resultado['token_jwt'] ?? '',
                'refresh_token' => $resultado['refresh_token'] ?? ''
            ];

            // Vinculación de dispositivo criptográfico (App Móvil)
            if (isset($datosPeticion['_temp_disp'], $datosPeticion['_temp_aes'])) {
                Criptografia::vincularDispositivoUsuario(
                    $datosPeticion['_temp_disp'],
                    $resultado['datos']['id_usuario'],
                    $datosPeticion['_temp_aes']
                );
            }
        } else {
            $seguridadIP->registrarFallo();
            $codigoError = $resultado['codigo_http'] ?? HttpCodigo::NO_AUTORIZADO->value;
            http_response_code($codigoError);
            $respuesta = ['estatus' => false, 'mensaje' => $resultado['mensaje'] ?? 'Credenciales incorrectas.'];
        }
    }

    // ==================== ASIGNACIÓN DE CÓDIGOS HTTP (MATCH) ====================
    if ($respuesta['estatus']) {
        http_response_code(HttpCodigo::OK->value);
    } else {
        // Si el flujo no asignó un código específico antes, forzamos un BAD_REQUEST
        if (http_response_code() === 200) {
            http_response_code(HttpCodigo::BAD_REQUEST->value);
        }
    }

} catch (Exception $e) {
    error_log("Error en API Login: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    // Cierre seguro y liberación de hilos en memoria
    if ($auth) $auth->cerrar();
    if ($seguridadIP) $seguridadIP->cerrar();
    
    echo json_encode($respuesta);
    exit;
}