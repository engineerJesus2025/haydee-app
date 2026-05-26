<?php
use haydee\enums\HttpCodigo;
use haydee\servicios\Autenticacion;
use haydee\servicios\Criptografia;
use haydee\modelo\SeguridadIP;
use haydee\ayuda\Validador;
use haydee\modelo\Usuario;

$metodoHttp = $_SERVER['REQUEST_METHOD'];

if ($metodoHttp !== 'POST') {
    http_response_code(HttpCodigo::METODO_NO_PERMITIDO->value);
    echo json_encode(['estatus' => false, 'mensaje' => 'Método HTTP no soportado. Use POST.']);
    exit;
}

$datosPeticion = $_POST;

// Mapeamos 'correo' a 'usuario' para que coincida con las reglas del modelo web
if (isset($datosPeticion['correo'])) {
    $datosPeticion['usuario'] = $datosPeticion['correo'];
}
$operacion = $datosPeticion['operacion'] ?? 'entrar';

$reglas = Usuario::obtenerReglas($operacion);
if (!empty($reglas)) {
    $validador = new Validador();
    // Usamos skip_unique porque en el login no queremos validar si el correo ya existe en BD para rebotarlo
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

$correo = $datosPeticion['correo'] ?? '';
$contra = $datosPeticion['contra'] ?? '';

// Rate Limit
$seguridadIP = new SeguridadIP();
$seguridadIP->set_ip($_SERVER['REMOTE_ADDR']);

$rateLimit = $seguridadIP->verificarRateLimit();
if (!$rateLimit['estatus']) {
    $seguridadIP->registrarFallo(); // Castigar insistencia
    http_response_code($rateLimit['codigo_http']);
    echo json_encode(['estatus' => false, 'mensaje' => $rateLimit['mensaje']]);
    exit;
}

// PROCESO DE AUTENTICACIÓN
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
$auth = new Autenticacion();

try {
    // Forzamos la generación y guardado del Token en tokens_seguridad
    $resultado = $auth->login($correo, $contra, true, true);
    
    if ($resultado['estatus']) {
        $seguridadIP->limpiarFallo(); 

        // Filtramos los permisos exclusivamente para los módulos móviles
        $modulosApp = ['GESTIONAR_PAGOS', 'GESTIONAR_GASTOS', 'GESTIONAR_MENSUALIDAD', 'GESTIONAR_CARTELERA_VIRTUAL'];
        $permisosFiltrados = array_values(array_filter($resultado['datos']['permisos'], function($p) use ($modulosApp) {
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
            'token_jwt'     => $resultado['token_jwt'],
            'refresh_token' => $resultado['token']
        ];

        // Vinculación del túnel criptográfico
        if (isset($_POST['_temp_disp']) && isset($_POST['_temp_aes'])) {
            Criptografia::vincularDispositivoUsuario($_POST['_temp_disp'], $resultado['datos']['id_usuario'], $_POST['_temp_aes']);
        }
        
        http_response_code(HttpCodigo::OK->value);

    } else {
        $seguridadIP->registrarFallo(); 
        http_response_code($resultado['codigo_http'] ?? HttpCodigo::NO_AUTORIZADO->value);
        $respuesta = ['estatus' => false, 'mensaje' => $resultado['mensaje'] ?? 'Credenciales incorrectas.'];
    }
} catch (Exception $e) {
    error_log("Error en API Login: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
} finally {
    $auth->cerrar();
}

echo json_encode($respuesta);