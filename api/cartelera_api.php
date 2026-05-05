<?php
use haydee\modelo\CarteleraVirtual;
use haydee\ayuda\GestorImagenes;

// (Futuro) Aquí validaremos el Token de seguridad de la app móvil.

$cartelera = new CarteleraVirtual();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];

try {
    // ======================================================================
    // PETICIONES GET: Solo para CONSULTAR datos (Lectura)
    // ======================================================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        $operacion = $_GET["operacion"] ?? 'consulta'; // Por defecto consulta general

        switch ($operacion) {
            case 'consulta':
                $respuesta = $cartelera->realizar_consulta('consultar');
                break;

            case 'consultar_cartelera':
                // Requiere que envíen el ID por la URL: ?endpoint=cartelera&operacion=consultar_cartelera&id=5
                $cartelera->set_id_cartelera($_GET['id'] ?? null);
                $respuesta = $cartelera->realizar_consulta('consultar_cartelera');
                break;

            default:
                http_response_code(400); // Bad Request
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación GET no permitida en la API'];
                break;
        }
    } 
    // ======================================================================
    // PETICIONES POST: Para CREAR o MODIFICAR datos (Escritura)
    // ======================================================================
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $operacion = $_POST["operacion"] ?? '';

        if (empty($operacion)) {
            http_response_code(400);
            echo json_encode(['estatus' => false, 'mensaje' => 'No se especificó la operación POST']);
            exit; // Cortamos ejecución
        }

        // Asignación masiva (Solo necesaria para registrar/modificar)
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

            case 'modificar_cartelera':
                // ... lógica de modificación
                break;

            default:
                http_response_code(400);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación POST no permitida en la API'];
                break;
        }
    } 
    // ======================================================================
    // MÉTODOS NO SOPORTADOS
    // ======================================================================
    else {
        http_response_code(405); // Method Not Allowed
        $respuesta = ['estatus' => false, 'mensaje' => 'Método HTTP no soportado'];
    }

} catch (Exception $e) {
    error_log("Error en API Cartelera: " . $e->getMessage());
    http_response_code(500);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    $cartelera->cerrar();
    echo json_encode($respuesta);
}
?>