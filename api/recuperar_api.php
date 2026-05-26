<?php
use haydee\enums\HttpCodigo;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorTrafico;

$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $operacion = $_POST["operacion"] ?? '';

        if (empty($operacion)) {
            http_response_code(HttpCodigo::BAD_REQUEST->value);
            $respuesta = ['estatus' => false, 'mensaje' => 'No se especificó la Operación'];
        }

        // Asignacion de la cabecera (Datos generales del gasto)
        $gastos->set_id_gasto($_POST['id_gasto'] ?? null);
        $gastos->set_clasificacion($_POST['clasificacion'] ?? null);
        $gastos->set_descripcion_gasto($_POST['descripcion_gasto'] ?? null);
        $gastos->set_solicitud_id($_POST['solicitud_id'] ?? null);
        $gastos->set_tipo_gasto_id($_POST['tipo_gasto_id'] ?? null);
        $gastos->set_proveedor_id($_POST['proveedor_id'] ?? null);

        switch ($operacion) {
            // Dentro del bloque POST de tu API de autenticación (ej: login_api.php o recuperar_api.php)
            case 'solicitar_otp':
                $correo = $datosJSON['correo'] ?? '';
                if (empty($correo)) {
                    http_response_code(HttpCodigo::BAD_REQUEST->value);
                    echo json_encode(['estatus' => false, 'mensaje' => 'El correo es obligatorio.']);
                    exit;
                }

                $serviceRecuperar = new \haydee\servicios\Recuperacion();
                $resultado = $serviceRecuperar->enviarCorreoOTP($correo);

                echo json_encode($resultado);
                break;

            case 'restablecer_con_otp':
                $correo = $datosJSON['correo'] ?? '';
                $otp = $datosJSON['codigo'] ?? '';
                $contra = $datosJSON['contra'] ?? '';

                if (empty($correo) || empty($otp) || empty($contra)) {
                    http_response_code(HttpCodigo::BAD_REQUEST->value);
                    echo json_encode(['estatus' => false, 'mensaje' => 'Todos los campos son requeridos.']);
                    exit;
                }

                $serviceRecuperar = new \haydee\servicios\Recuperacion();
                $resultado = $serviceRecuperar->restablecerConOTP($correo, $otp, $contra);

                if (!$resultado['estatus']) http_response_code(HttpCodigo::BAD_REQUEST->value);
                echo json_encode($resultado);
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación POST no permitida'];
                break;
        }
    } 
    else {
        http_response_code(HttpCodigo::METODO_NO_PERMITIDO->value); 
        $respuesta = ['estatus' => false, 'mensaje' => 'metodo HTTP no soportado'];
    }

} catch (Exception $e) {
    error_log("Error en API Gastos: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    echo json_encode($respuesta);
}