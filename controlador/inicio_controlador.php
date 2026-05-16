<?php
use haydee\enums\HttpCodigo;
use haydee\servicios\Sesiones;
use haydee\modelo\Mensualidad;
use haydee\modelo\CarteleraVirtual;
use haydee\modelo\Apartamento;
use haydee\servicios\Autenticacion;
use haydee\modelo\Bitacora;
use haydee\servicios\GestorAuditoria;

// Proteccion basica (Red, HTTP, sesion iniciada)
Sesiones::autorizarAcceso();

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try{
        switch ($operacion) {
            case 'consulta_inicio':
                $cartelera = new CarteleraVirtual();
                $limite = $_POST["limite"] ?? 0;

                $respuesta = $cartelera->consultar_inicio($limite);
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;

            case 'consulta_inicio_grafico':
                $mensualidad = new Mensualidad();
                $respuesta = $mensualidad->realizar_consulta('consultar_estadisticas_inicio');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;

            case 'consultar_tarjetas_resumen':
                $mensualidad = new Mensualidad();
                $respuesta = $mensualidad->realizar_consulta('consultar_tarjetas_resumen');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;

            case 'consulta_apartamentos':
                $apartamento = new Apartamento();
                $respuesta = $apartamento->realizar_consulta('consultar_estado_inicio');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;

            case 'consulta_actividad':
                $bitacora = new Bitacora();
                $respuesta = $bitacora->realizar_consulta('consultar_actividad_dashboard');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;

            case 'consulta_widget_publicaciones':
                $cartelera = new CarteleraVirtual();
                $respuesta = $cartelera->consultar_widget_dashboard();
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en controlador: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explicitamente
            if (isset($cartelera)) { $cartelera->cerrar(); }
            if (isset($mensualidad)) { $mensualidad->cerrar(); }
            if (isset($apartamento)) { $apartamento->cerrar(); }
            if (isset($bitacora)) { $bitacora->cerrar(); }

            echo json_encode($respuesta);
            exit;
        }
    }
}

// Cargar la vista del inicio
require_once "vista/inicio/inicio_vista.php";

