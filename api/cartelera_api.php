<?php
use haydee\modelo\CarteleraVirtual;
use haydee\servicios\Sesiones;
use haydee\ayuda\Validador;
use haydee\ayuda\GestorImagenes;
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\servicios\GestorAuditoria;
use haydee\modelo\Bitacora;

// ==================== IDENTIDAD Y PERMISOS ====================
$identidad = Sesiones::autorizarAccesoAPI(Modulo::GESTIONAR_CARTELERA_VIRTUAL, Accion::CONSULTAR, ['GET', 'POST', 'PUT']);

$rolUsuario = strtolower($identidad['rol'] ?? '');
$esAdministrador = ($rolUsuario === 'administrador'); // o el rol que aplique
$correoUsuario = $identidad['correo'] ?? '';

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

Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_CARTELERA_VIRTUAL, $operacion);

// ==================== REGLAS Y FIREWALL DE PROTOCOLO HTTP ====================
$reglas = CarteleraVirtual::obtenerReglas($operacion);
$validador = new Validador();

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
    $validador->validarConjunto($datosPeticion, $reglas);
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
$cartelera = new CarteleraVirtual();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];
$auditor = new GestorAuditoria($cartelera, Modulo::GESTIONAR_CARTELERA_VIRTUAL);


try {
    // ==================== ASIGNACIÓN MASIVA PARA ESCRITURA (POST/PUT) ====================
    if ($metodoHttp === 'POST' || $metodoHttp === 'PUT') {
        $cartelera->set_id_cartelera($datosPeticion['id_cartelera'] ?? null);
        $cartelera->set_titulo($datosPeticion['titulo'] ?? null);
        $cartelera->set_descripcion($datosPeticion['descripcion'] ?? null);
        $cartelera->set_prioridad($datosPeticion['prioridad'] ?? null);
        $cartelera->set_usuario_id($datosPeticion['usuario_id'] ?? 1); 
    }
    switch ($operacion) {

        // ==================== CONSULTAS (GET) ====================
        case 'consulta':
            // Paginación para móvil
            if (isset($datosPeticion['pagina'], $datosPeticion['limite'])) {
                $pagina = (int)$datosPeticion['pagina'];
                $limite = (int)$datosPeticion['limite'];
                $offset = ($pagina - 1) * $limite;
                $cartelera->set_limite_paginacion($limite);
                $cartelera->set_offset_paginacion($offset);
                $respuesta = $cartelera->realizar_consulta('consultar_paginada');
            } else {
                $respuesta = $cartelera->realizar_consulta('consultar');
            }

            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::CONSULTAR);
            }
            break;

        case 'consultar_cartelera':
            $cartelera->set_id_cartelera($datosPeticion['id'] ?? null);
            $respuesta = $cartelera->realizar_consulta('consultar_cartelera');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::CONSULTAR);
            }
            break;

        // ==================== ESCRITURA (POST) ====================
        case 'registrar_cartelera':
            // Procesar imagen (viene en $_FILES, no en $datosPeticion)
            $nombreImagen = '';
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $nombreImagen = GestorImagenes::subir($_FILES['imagen'], 'cartelera_virtual');
                if ($nombreImagen === false) {
                    throw new Exception('Error al procesar la imagen enviada.');
                }
            }
            $cartelera->set_imagen($nombreImagen);
            $respuesta = $cartelera->realizar_consulta('registrar_cartelera');
            if ($respuesta['estatus']) {
                $respuesta['nombre_imagen'] = $nombreImagen;
                $auditor->registrarAuditoria(Accion::REGISTRAR);
            }
            break;

        // ==================== MODIFICACIONES (PUT) ====================
        case 'actualizar_cartelera':
            // Solo administradores pueden modificar
            if (!$esAdministrador) {
                http_response_code(HttpCodigo::PROHIBIDO->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Acción no autorizada.'];
                break;
            }
            $auditor->capturarDatosAnteriores('consultar_cartelera');
            $respuesta = $cartelera->realizar_consulta('actualizar_cartelera');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::MODIFICAR);
            }
            break;

        // ==================== ELIMINACIÓN (DELETE) ====================
        case 'eliminar_cartelera':
            if (!$esAdministrador) {
                http_response_code(HttpCodigo::PROHIBIDO->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Acción no autorizada.'];
                break;
            }
            $auditor->capturarDatosAnteriores('consultar_cartelera');
            $respuesta = $cartelera->realizar_consulta('eliminar_cartelera');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::ELIMINAR);
            }
            break;

        default:
            http_response_code(HttpCodigo::BAD_REQUEST->value);
            $respuesta = ['estatus' => false, 'mensaje' => 'Operación no reconocida o implementada.'];
            break;
    }

    // ==================== CÓDIGOS DE ÉXITO ====================
    if ($respuesta['estatus']) {
        $codigoExito = match ($operacion) {
            'registrar_cartelera' => HttpCodigo::CREADO->value,
            default => HttpCodigo::OK->value,
        };
        http_response_code($codigoExito);
    } else {
        // Si ya se asignó un código específico de error (403, 404, etc.) no se sobreescribe
        if (http_response_code() === 200) {
            http_response_code(HttpCodigo::BAD_REQUEST->value);
        }
    }

} catch (Exception $e) {
    error_log("Error en API Cartelera: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    if ($cartelera) $cartelera->cerrar();
    Bitacora::cerrarConexionBitacora();
    echo json_encode($respuesta);
    exit;
}