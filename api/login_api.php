<?php
use haydee\servicios\Autenticacion;

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

        $auth = new Autenticacion();
        try {
            // El tercer parámetro 'true' fuerza la generación y guardado del Token en tokens_seguridad
            $resultado = $auth->login($correo, $contra, true);
            
            if ($resultado['estatus']) {
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
                    'token' => $resultado['token'] 
                ];
            } else {
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