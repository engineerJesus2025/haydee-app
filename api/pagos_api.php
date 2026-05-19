<?php
use haydee\modelo\Pagos;
use haydee\modelo\Apartamento; // Importamos el modelo
use haydee\modelo\Banco;       // Importamos el modelo
use haydee\enums\HttpCodigo;
use haydee\enums\EstadoPago;
use haydee\ayuda\ConstructorDetalles;

// (Futuro) Validación de Token JWT y extracción de datos del usuario
$esPropietario = isset($_REQUEST['es_propietario']) && $_REQUEST['es_propietario'] == '1';
$correoUsuario = $_REQUEST['correo'] ?? ''; 

$pagos = new Pagos();
$respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida en API'];

try {
    // PETICIONES GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        $operacion = $_GET["operacion"] ?? 'consulta'; 

        switch ($operacion) {
            case 'consulta':
                if ($esPropietario) {
                    $pagos->set_correo($correoUsuario);
                    $respuesta = $pagos->realizar_consulta('consultar_por_correo');
                } else {
                    $respuesta = $pagos->realizar_consulta('consultar');
                }
                break;

            // Carga Inicial del Formulario
            case 'obtener_catalogos_base':
                $apartamento = new Apartamento();
                $banco = new Banco();

                // Lógica condicional para los apartamentos
                if ($esPropietario) {
                    $apartamento->set_correo($correoUsuario);
                    $resApartamentos = $apartamento->realizar_consulta('obtener_apartamentos_por_correo');
                } else {
                    $resApartamentos = $apartamento->realizar_consulta('consultar_listado');
                }

                $respuesta = [
                    'estatus' => true,
                    'datos' => [
                        'apartamentos' => $resApartamentos['datos'] ?? [],
                        'bancos'       => $banco->realizar_consulta('consultar')['datos'] ?? []
                    ]
                ];
                
                // Cerramos conexiones auxiliares inmediatamente
                $apartamento->cerrar();
                $banco->cerrar();
                break;

            // Carga Dinámica de mensualidades
            case 'consultar_mensualidades':
                $pagos->set_apartamento_id($_GET['apartamento_id'] ?? null);
                $respuesta = $pagos->realizar_consulta('consultarMensualidadPendiente');
                break;

            case 'consultar_pago':
                $pagos->set_id_pago($_GET['id_pago'] ?? null);
                $respuesta = $pagos->realizar_consulta('consultar_pago');
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación GET no permitida'];
                break;
        }
    } 
    // PETICIONES POST
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $operacion = $_POST["operacion"] ?? '';

        if (empty($operacion)) {
            http_response_code(HttpCodigo::BAD_REQUEST->value);
            $respuesta = ['estatus' => false, 'mensaje' => 'No se especificó la operación'];
        } else {
            // Asignación de la cabecera del pago
            $pagos->set_id_pago($_POST['id_pago'] ?? null);
            $pagos->set_observacion($_POST['observacion'] ?? null);
            $pagos->set_apartamento_id($_POST['apartamento_id'] ?? null);
            $pagos->set_mensualidad_id($_POST['mensualidad_id'] ?? null);
            
            $estadoPorDefecto = $esPropietario ? 'PENDIENTE' : 'PROCESADO';
            $pagos->set_estado($_POST['estado'] ?? $estadoPorDefecto);

            if ($esPropietario) {
                $pagos->set_correo($correoUsuario);
            }

            switch ($operacion) {
                case 'registrar_pago':
                    $detalles = ConstructorDetalles::ConstruirDetallesPagos($_POST, $_FILES, false);
                    
                    if (empty($detalles)) {
                        http_response_code(HttpCodigo::BAD_REQUEST->value);
                        $respuesta = ['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un detalle de pago.'];
                        break; 
                    }

                    $pagos->set_detalles($detalles);
                    $respuesta = $pagos->realizar_consulta('registrar_pago');
                    break;

                case 'modificar_pago':
                    if ($esPropietario) {
                        http_response_code(HttpCodigo::PROHIBIDO->value);
                        $respuesta = ['estatus' => false, 'mensaje' => 'No autorizado para modificar pagos'];
                        break;
                    }
                    // lógica de modificación si el admin la ejecuta... tal vez, no se 
                    break;

                default:
                    http_response_code(HttpCodigo::BAD_REQUEST->value);
                    $respuesta = ['estatus' => false, 'mensaje' => 'Operación POST no permitida'];
                    break;
            }
        }
    } 
    else {
        http_response_code(HttpCodigo::METODO_NO_PERMITIDO->value);
        $respuesta = ['estatus' => false, 'mensaje' => 'Método HTTP no soportado'];
    }

} catch (Exception $e) {
    error_log("Error en API Pagos: " . $e->getMessage());
    http_response_code(HttpCodigo::ERROR_INTERNO->value);
    $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor API'];
} finally {
    $pagos->cerrar();
    echo json_encode($respuesta);
}