<?php
use haydee\modelo\CarteleraVirtual;
use haydee\servicios\Sesiones;
use haydee\ayuda\Validador;
use haydee\ayuda\GestorImagenes;
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;

// IDENTIDAD Y PERMISOS
$identidad = Sesiones::autorizarAccesoAPI(Modulo::GESTIONAR_CARTELERA_VIRTUAL, Accion::CONSULTAR, ['GET', 'POST', 'PUT']);

// UNIFICACIÓN DEL PAYLOAD
$metodoHttp = $_SERVER['REQUEST_METHOD'];
$datosPeticion = ($metodoHttp === 'GET') ? $_GET : $_POST;
$operacion = $datosPeticion['operacion'] ?? '';

if (empty($operacion)) {
    http_response_code(HttpCodigo::BAD_REQUEST->value);
    echo json_encode(['estatus' => false, 'mensaje' => 'No se especificó la operación.']);
    exit;
}

Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_CARTELERA_VIRTUAL, $operacion);

// VALIDACIÓN 
$reglas = CarteleraVirtual::obtenerReglas($operacion);

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

$cartelera = new CarteleraVirtual();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];

try {
    // PETICIONES GET:
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        $operacion = $_GET["operacion"] ?? 'consulta'; // Por defecto consulta general

        switch ($operacion) {
            case 'consulta':
                // Verificamos si la petición solicita paginación (App Móvil)
                if (isset($_GET['pagina']) && isset($_GET['limite'])) {
                    $pagina = (int)$_GET['pagina'];
                    $limite = (int)$_GET['limite'];
                    
                    // Calculamos desde dónde empezar a traer registros
                    $offset = ($pagina - 1) * $limite;
                    
                    $cartelera->set_limite_paginacion($limite);
                    $cartelera->set_offset_paginacion($offset);
                    
                    $respuesta = $cartelera->realizar_consulta('consultar_paginada');
                } else {
                    // Fallback para consultas completas normales
                    $respuesta = $cartelera->realizar_consulta('consultar');
                }
                break;

            case 'consultar_cartelera':
                // Requiere que envíen el ID por la URL: ?endpoint=cartelera&operacion=consultar_cartelera&id=5
                $cartelera->set_id_cartelera($_GET['id'] ?? null);
                $respuesta = $cartelera->realizar_consulta('consultar_cartelera');
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value); // Bad Request
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación GET no permitida en la API'];
                break;
        }
    } 
    // ======================================================================
    // PETICIONES POST
    // ======================================================================
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $operacion = $_POST["operacion"] ?? '';

        if (empty($operacion)) {
            http_response_code(HttpCodigo::BAD_REQUEST->value);
            echo json_encode(['estatus' => false, 'mensaje' => 'No se especificó la Operación POST']);
            return; // Cortamos ejecución
        }

        // Asignacion masiva (Solo necesaria para registrar/modificar)
        $cartelera->set_id_cartelera($_POST['id_cartelera'] ?? null);
        $cartelera->set_titulo($_POST['titulo'] ?? null);
        $cartelera->set_descripcion($_POST['descripcion'] ?? null);
        $cartelera->set_prioridad($_POST['prioridad'] ?? null);
        
        // Asignamos un usuario por defecto temporalmente hasta implementar el Token JWT
        $cartelera->set_usuario_id($_POST['usuario_id'] ?? 1); 

        switch ($operacion) {
            case 'registrar_cartelera':
                $nombreImagen = '';
                // Lógica idéntica al controlador web para procesar la imagen
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $nombreImagen = GestorImagenes::subir($_FILES['imagen'], 'cartelera_virtual');
                    if ($nombreImagen === false) {
                        throw new Exception('Error al procesar la imagen enviada desde el móvil.');
                    }
                }
                
                $cartelera->set_imagen($nombreImagen);
                $respuesta = $cartelera->realizar_consulta('registrar_cartelera');

                if ($respuesta['estatus']) {
                    // Agregamos el nombre de la imagen a la respuesta
                    $respuesta['nombre_imagen'] = $nombreImagen; 
                }
                
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación POST no permitida en la API'];
                break;
        }
    } 
    // METODOS NO SOPORTADOS
    else {
        http_response_code(HttpCodigo::METODO_NO_PERMITIDO->value); // Method Not Allowed
        $respuesta = ['estatus' => false, 'mensaje' => 'metodo HTTP no soportado'];
    }

} catch (Exception $e) {
    error_log("Error en API Cartelera: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    $cartelera->cerrar();
    echo json_encode($respuesta);
}
