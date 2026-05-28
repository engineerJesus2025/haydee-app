<?php
use haydee\modelo\Usuario;
use haydee\servicios\Sesiones;
use haydee\ayuda\Validador;
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\servicios\GestorAuditoria;
use haydee\modelo\Bitacora;

// ==================== IDENTIDAD Y PERMISOS ====================
// El perfil personal no requiere un módulo ni acción específica para ser consultado
$identidad = Sesiones::autorizarAccesoAPI(null, null, ['GET', 'POST', 'PUT']);

$idUsuarioAutenticado = $identidad['id_usuario'] ?? null;
$rolUsuario = strtolower($identidad['rol'] ?? '');
$correoUsuario = $identidad['correo'] ?? '';

if (empty($idUsuarioAutenticado)) {
    http_response_code(HttpCodigo::NO_AUTORIZADO->value);
    echo json_encode(['estatus' => false, 'mensaje' => 'Sesión inválida o expirada.']);
    exit;
}

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

// ==================== REGLAS Y FIREWALL DE PROTOCOLO HTTP ====================
$reglas = Usuario::obtenerReglas($operacion);
$validador = new Validador();

// Llamada unificada directa (el validador ya sabe qué hacer si $reglas está vacío)
if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    http_response_code(HttpCodigo::METODO_NO_PERMITIDO->value);
    echo json_encode([
        'estatus' => false,
        'errores' => $validador->obtenerErrores(),
        'mensaje' => 'Protocolo HTTP denegado para esta operación.'
    ]);
    exit;
}

// ==================== VALIDACIÓN DE DATOS ====================
if (!empty($reglas)) {
    // Contexto estratégico para omitir la validación UNIQUE sobre el correo del propio usuario
    $contexto = ['exclude_id' => $idUsuarioAutenticado]; 
    $validador->validarConjunto($datosPeticion, $reglas, $contexto);
    if ($validador->tieneErrores()) {
        $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::BAD_REQUEST->value;
        http_response_code($codigoHttp);
        echo json_encode([
            'estatus' => false,
            'errores' => $validador->obtenerErrores(),
            'mensaje' => 'Datos inválidos.'
        ]);
        exit;
    }
}

// ==================== INSTANCIACIÓN DE MODELOS Y AUDITOR ====================
$usuario = new Usuario();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];
$auditor = new GestorAuditoria($usuario, Modulo::GESTIONAR_USUARIOS); 

try {
    // ==================== ASIGNACIÓN MASIVA PROTEGIDA ====================
    // Forzamos el ID del token JWT por seguridad inmutable
    $usuario->set_id_usuario($idUsuarioAutenticado);

    if ($metodoHttp === 'POST' || $metodoHttp === 'PUT') {
        $usuario->set_nombre($datosPeticion['nombre'] ?? null);
        $usuario->set_apellido($datosPeticion['apellido'] ?? null);
        $usuario->set_correo($datosPeticion['correo'] ?? null);
    }

    switch ($operacion) {

        // ==================== CONSULTAS (GET) ====================
        case 'consultar_perfil':
            $respuesta = $usuario->realizar_consulta('consultar_perfil_usuario');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::CONSULTAR);
            }
            break;

        // ==================== ESCRITURA (PUT / POST) ====================
        case 'actualizar_perfil':
            $auditor->capturarDatosAnteriores('consultar_perfil_usuario');
            $respuesta = $usuario->realizar_consulta('modificar_perfil_personal');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::MODIFICAR);
            }
            break;

        default:
            http_response_code(HttpCodigo::BAD_REQUEST->value);
            $respuesta = ['estatus' => false, 'mensaje' => 'Operación no reconocida o implementada.'];
            break;
    }

    // ==================== CÓDIGOS DE ÉXITO (MATCH) ====================
    if ($respuesta['estatus']) {
        http_response_code(HttpCodigo::OK->value); // Ambos casos exitosos retornan 200 OK
    } else {
        if (http_response_code() === 200) {
            http_response_code(HttpCodigo::BAD_REQUEST->value);
        }
    }

} catch (Exception $e) {
    error_log("Error en API Perfil: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    if ($usuario) $usuario->cerrar();
    Bitacora::cerrarConexionBitacora();
    echo json_encode($respuesta);
    exit;
}