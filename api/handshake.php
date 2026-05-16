<?php
use haydee\enums\HttpCodigo;
$respuesta = ['estatus' => false, 'mensaje' => 'No se pudo obtener la llave pública'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        $rutaLlave = ROOT_PATH . '/config/llave_servidor_publica.pem';
        
        if (!file_exists($rutaLlave)) {
            throw new Exception("Configuración de seguridad incompleta en el servidor.");
        }
        
        $llavePublica = file_get_contents($rutaLlave);

        http_response_code(HttpCodigo::OK->value);
        $respuesta = [
            'estatus' => true,
            'mensaje' => 'Llave pública lista',
            'public_key' => $llavePublica
        ];
        
    } else {
        http_response_code(HttpCodigo::METODO_NO_PERMITIDO->value);
        $respuesta = ['estatus' => false, 'mensaje' => 'metodo no permitido. Use GET.'];
    }
} catch (Exception $e) {
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => $e->getMessage()];
}

echo json_encode($respuesta);
