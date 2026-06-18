<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\modelo\Usuario;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;

if (empty($operacion)) {
    throw new Exception('No se especificó la operación.', HttpCodigo::BAD_REQUEST->value);
}

// ==================== REGLAS Y FIREWALL DE PROTOCOLO HTTP ====================
$reglas = Usuario::obtenerReglas($operacion);
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    $datosError = [
        'mensaje' => 'Protocolo HTTP denegado para esta operación.',
        'errores' => $validador->obtenerErrores()
    ];
    throw new \Exception(json_encode($datosError), HttpCodigo::METODO_NO_PERMITIDO->value);
}

// ==================== VALIDACIÓN DE DATOS ====================
if (!empty($reglas)) {
    $contexto = ['exclude_id' => $identidad['id_usuario']]; 
    $validador->validarConjunto($datosPeticion, $reglas, $contexto);
    if ($validador->tieneErrores()) {
        $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
        $datosError = [
            'mensaje' => 'Datos de formulario inválidos o incompletos.',
            'errores' => $validador->obtenerErrores()
        ];
        throw new \Exception(json_encode($datosError), $codigoHttp);
    }
}

// ==================== INSTANCIACIÓN DE MODELOS Y AUDITOR ====================
$usuario = new Usuario();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];
$auditor = new GestorAuditoria($usuario, Modulo::GESTIONAR_USUARIOS); 

try {
    // ==================== ASIGNACIÓN MASIVA PROTEGIDA ====================
    // Forzamos el ID del token JWT por seguridad inmutable
    $usuario->set_id_usuario($identidad['id_usuario']);

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
}