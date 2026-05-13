<?php
use haydee\servicios\Autenticacion;
use haydee\servicios\Criptografia;
use haydee\modelo\SeguridadIP;

$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // React Native (Axios) suele enviar los datos en formato JSON crudo
        // Intentamos leer JSON primero, y si no, caemos en el $_POST tradicional
        $inputJSON = file_get_contents('php://input');
        $datosJSON = json_decode($inputJSON, true);
        
        $correo = $datosJSON['correo'] ?? $_POST['correo'] ?? '';
        $contra = $datosJSON['contra'] ?? $_POST['contra'] ?? '';

        if (empty($correo) || empty($contra)) {
            http_response_code(400);
            echo json_encode(['estatus' => false, 'mensaje' => 'El correo y la contraseña son obligatorios.']);
            exit;
        }

        // ANTI-FUERZA BRUTA (Rate Limit)
        $seguridadIP = new SeguridadIP();
        $seguridadIP->set_ip($_SERVER['REMOTE_ADDR']);

        $rateLimit = $seguridadIP->verificarRateLimit();
        if (!$rateLimit['estatus']) {
            $seguridadIP->registrarFallo(); // Castigar insistencia
            http_response_code($rateLimit['codigo_http']);
            echo json_encode(['estatus' => false, 'mensaje' => $rateLimit['mensaje']]);
            exit;
        }

        $auth = new Autenticacion();
        try {
            // El tercer parámetro 'true' fuerza la generación y guardado del Token en tokens_seguridad
            $resultado = $auth->login($correo, $contra, false, true);
            
            if ($resultado['estatus']) {
                $seguridadIP->limpiarFallo(); // Limpiamos IP

                http_response_code(200);
                $respuesta = [
                    'estatus' => true,
                    'mensaje' => 'Inicio de sesión exitoso',
                    'datos' => [
                        'id_usuario' => $resultado['datos']['id_usuario'] ?? '',
                        'usuario' => $resultado['datos']['nombre_completo'] ?? '',
                        'rol' => $resultado['datos']['rol'] ?? '',
                        'correo' => $resultado['datos']['correo'] ?? ''
                    ],
                    'token' => $resultado['token_jwt']
                ];

                if (isset($_POST['_temp_disp']) && isset($_POST['_temp_aes'])) {
                    Criptografia::vincularDispositivoUsuario($_POST['_temp_disp'], $resultado['datos']['id_usuario'], $_POST['_temp_aes']);
                }
            } else {
                $seguridadIP->registrarFallo(); // Castigamos a la IP

                // Credenciales incorrectas, usuario inactivo, etc.
                http_response_code(401); 
                $respuesta = ['estatus' => false, 'mensaje' => $resultado['mensaje'] ?? 'Credenciales incorrectas.'];
            }
        } finally {
            $auth->cerrar();
        }

    } else {
        http_response_code(405);
        $respuesta = ['estatus' => false, 'mensaje' => 'Método HTTP no soportado. Use POST.'];
    }

} catch (Exception $e) {
    error_log("Error en API Login: " . $e->getMessage());
    http_response_code(500);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
}

echo json_encode($respuesta);