<?php
use haydee\enums\HttpCodigo;
use haydee\modelo\Mensualidad;
use haydee\modelo\CarteleraVirtual;
use haydee\modelo\Apartamento;
use haydee\modelo\Bitacora;
use haydee\excepciones\HaydeeException;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];
    
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];
    $codigoExito = HttpCodigo::OK->value;  

    switch ($operacion) {
        case 'consulta_inicio':
            $cartelera = new CarteleraVirtual();
            $limite = $_POST["limite"] ?? 0;
            $respuesta = $cartelera->consultar_inicio($limite);
            $cartelera->cerrar();
            break;

        case 'consulta_inicio_grafico':
            $mensualidad = new Mensualidad();
            $respuesta = $mensualidad->realizar_consulta('consultar_estadisticas_inicio');
            $mensualidad->cerrar();
            break;

        case 'consultar_tarjetas_resumen':
            $mensualidad = new Mensualidad();
            $respuesta = $mensualidad->realizar_consulta('consultar_tarjetas_resumen');
            $mensualidad->cerrar();
            break;

        case 'consulta_apartamentos':
            $apartamento = new Apartamento();
            $respuesta = $apartamento->realizar_consulta('consultar_estado_inicio');
            $apartamento->cerrar();
            break;

        case 'consulta_actividad':
            $bitacora = new Bitacora();
            $respuesta = $bitacora->realizar_consulta('consultar_actividad_dashboard');
            $bitacora->cerrar();
            break;

        case 'consulta_widget_publicaciones':
            $cartelera = new CarteleraVirtual();
            $respuesta = $cartelera->consultar_widget_dashboard();
            $cartelera->cerrar();
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    Bitacora::cerrarConexionBitacora();

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

require_once "vista/inicio/inicio_vista.php";