<?php
use haydee\modelo\Usuario;

$usuario = new Usuario();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

try {
    // 1. Extracción del Token del Header (Estándar Bearer)
    $headers = apache_request_headers();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);

    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['estatus' => false, 'mensaje' => 'Token de seguridad requerido']);
        exit;
    }

    // 2. Configuración del modelo
    $usuario->set_token($token);

    // 3. Enrutamiento de operaciones (GET para consulta, POST para cambios)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Al llamar a realizar_consulta, el enrutador dinámico de tu modelo
        // llamará automáticamente a _consultar_por_token()
        $respuesta = $usuario->realizar_consulta('consultar_por_token');
    } 
    else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $inputJSON = file_get_contents('php://input');
        $datos = json_decode($inputJSON, true) ?? $_POST;
        $operacion = $datos['operacion'] ?? '';

        if ($operacion === 'actualizar_perfil') {
            // Primero validamos el token para saber qué ID de usuario cargar
            $validacion = $usuario->realizar_consulta('consultar_por_token');
            if ($validacion['estatus']) {
                $usuario->set_id_usuario($validacion['datos']['id_usuario']);
                $usuario->set_nombre($datos['nombre'] ?? '');
                $usuario->set_apellido($datos['apellido'] ?? '');
                $respuesta = $usuario->realizar_consulta('modificar');
            } else {
                $respuesta = $validacion;
            }
        }
    }

} catch (Exception $e) {
    error_log("Error en Perfil API: " . $e->getMessage());
    http_response_code(500);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error en el servidor de perfil'];
}

echo json_encode($respuesta);