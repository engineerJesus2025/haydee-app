<?php
use haydee\modelo\Usuario;
use haydee\servicios\Sesiones;
use haydee\ayuda\Validador;
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;

// IDENTIDAD Y PERMISOS
$identidad = Sesiones::autorizarAccesoAPI(null, null, ['GET', 'POST', 'PUT']);

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
$reglas = Usuario::obtenerReglas($operacion);

if (!empty($reglas)) {
    $validador = new Validador();
    $validador->validarConjunto($datosPeticion, $reglas);

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

$usuario = new Usuario();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

try {
    // Extraccion del Token del Header
    $headers = apache_request_headers();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);

    if (empty($token)) {
        http_response_code(HttpCodigo::NO_AUTORIZADO->value);
        echo json_encode(['estatus' => false, 'mensaje' => 'Token de seguridad requerido']);
        return;
    }

    // Configuración del modelo
    $usuario->set_token($token);

    // Enrutamiento de operaciones (GET para consulta, POST para cambios)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $respuesta = $usuario->realizar_consulta('consultar_por_token');
    } 
    else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $inputJSON = file_get_contents('php://input');
        $datos = json_decode($inputJSON, true) ?? $_POST;
        $operacion = $datos['operacion'] ?? '';

        if ($operacion === 'actualizar_perfil') {
            // Primero validamos el token para saber que ID de usuario cargar
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
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error en el servidor de perfil'];
}

echo json_encode($respuesta);
