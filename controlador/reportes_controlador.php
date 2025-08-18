<?php
require_once "modelo/gastos_modelo.php";
require_once "modelo/propietario_modelo.php";
require_once "modelo/mensualidad_modelo.php";
require_once "modelo/habitantes_modelo.php";

require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$gastos_obj = new Gastos();

$habitantes_obj = new Habitantes(); // Objeto habitante

$propietarios_obj = new Propietario();//Ojo

if (isset($_POST["operacion"])){
    $operacion = $_POST["operacion"];
    if ($operacion == "consultar_ingresos_egresos"){
        $balance = $_POST["balance"];
        $metodo_pago = $_POST["metodo_pago"];
        $tipo_gasto = $_POST["tipo_gasto"];
        $filtro = $_POST["filtro"];
        $fecha_inicio = $_POST["fecha_inicio"];
        $fecha_fin = $_POST["fecha_fin"];
        
        echo  json_encode($gastos_obj->obtenerIngresosYEgresos($balance,$metodo_pago,$tipo_gasto,$filtro,$fecha_inicio,$fecha_fin));
    } 
    if ($operacion == "consultar_estadisticas_ingresos_egresos"){
        $balance = $_POST["balance"];
        $metodo_pago = $_POST["metodo_pago"];
        $tipo_gasto = $_POST["tipo_gasto"];
        $filtro = $_POST["filtro"];
        $fecha_inicio = $_POST["fecha_inicio"];
        $fecha_fin = $_POST["fecha_fin"];
        
        echo  json_encode($gastos_obj->estadisticasIngresosYEgresos($balance,$metodo_pago,$tipo_gasto,$filtro,$fecha_inicio,$fecha_fin));
    }
    else if ($operacion == "consultar_personas_solvencia"){
        echo json_encode($habitantes_obj->consultar_personas_solvencia());
    }
    else if ($operacion == "consultar_personas_residencia"){
        echo  json_encode($habitantes_obj->consultar_propietarios());
    }
    else if ($operacion == "consultar_meses_mensualidad"){
        $mensualidad_obj = new Mensualidad();
        echo  json_encode($mensualidad_obj->realizar_consulta('consultar_meses_mensualidad'));
    }
    else if ($operacion == "consultar_habitantes") {
        // Recolectar datos del POST
        $rango_edades = $_POST['rango_edades'] ?? 'todos';
        $edad_minima = $_POST['edad_minima'] ?? null;
        $edad_maxima = $_POST['edad_maxima'] ?? null;
        $tipo_residente = $_POST['tipo_residente'] ?? 'todos';
        $servicios = $_POST['servicios'] ?? [];

        $resultado = $habitantes_obj->obtenerDatosHabitantes($rango_edades, $edad_minima, $edad_maxima, $tipo_residente, $servicios);

        // Devolver los datos como JSON
        header('Content-Type: application/json');
        echo json_encode($resultado['mensaje']);
    }

exit();
}

if ($accion == "reportes_pdf") {
    $mensualidad_obj = new Mensualidad();
    $mensualidad_obj->registrar_bitacora(CONSULTAR, GESTIONAR_REPORTES, "TODOS LOS REPORTES PDF");
    require_once "vista/reportes/reportes_pdf/reportes_pdf_vista.php";
}
if ($accion == "solvencia") {
    $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $id_habitante = $_POST["select_reporte"];

    $habitantes_obj->set_id_habitante($id_habitante);

    $registro_porpietario = $habitantes_obj->consultar_habitante();
    $fecha = new DateTime();
    $fecha->modify("+1 month");
    $mes_fin = $fecha->format("n");
    $anio_fin = $fecha->format("Y");

    ob_start();
    require_once "vista/reportes/reportes_pdf/pdf/reporte_solvencia_pdf.php";

    $html = ob_get_clean();

    $dompdf = new Dompdf(array('enable_remote' => true));

    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream("solvencia_" . $registro_porpietario["nombre"] . "_" . $registro_porpietario["apellido"]);
}
if ($accion == "residencia") {
    $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $id_habitante = $_POST["select_reporte"];

    $habitantes_obj->set_id_habitante($id_habitante);
    $registro_porpietario = $habitantes_obj->consultar_habitante();

    ob_start();
    require_once "vista/reportes/reportes_pdf/pdf/reporte_residencia_pdf.php";

    $html = ob_get_clean();

    $dompdf = new Dompdf(array('enable_remote' => true));

    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream("constancia_residencia_" . $registro_porpietario["nombre"] . "_" . $registro_porpietario["apellido"]);
}

if ($accion == "cuadro_pagos"){
    $mensualidad_obj = new Mensualidad();
    $meses_nombres = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

    $mes_limite = explode("-", $_POST["select_reporte"])[0];
    $anio_limite = explode("-", $_POST["select_reporte"])[1];    

    $deudas_filtradas =[];
    $meses_seleccionados = [];

    $deudas_globales = $mensualidad_obj->realizar_consulta('consultar_mensualidades_pendientes');

    foreach ($deudas_globales as $deuda) {        
        if (intval($deuda["anio"]) <= intval($anio_limite)){
            if (intval($deuda["anio"]) == intval($anio_limite)) {
                if (intval($deuda["mes"]) <= intval($mes_limite)){

                    if (!(array_key_exists($deuda["nro_apartamento"],$deudas_filtradas))) {
                        $deudas_filtradas[$deuda["nro_apartamento"]] = [];
                    }
                    
                    array_push($deudas_filtradas[$deuda["nro_apartamento"]], $deuda["deuda_acumulada"]);

                    if (array_search($meses_nombres[$deuda["mes"]-1], $meses_seleccionados) === false) {                        
                        array_push($meses_seleccionados, $meses_nombres[$deuda["mes"]-1]);
                    }
                }
            }
            else{
                if (!(array_key_exists($deuda["nro_apartamento"],$deudas_filtradas))) {
                    $deudas_filtradas[$deuda["nro_apartamento"]] = [];
                }
                    
                array_push($deudas_filtradas[$deuda["nro_apartamento"]], $deuda["deuda_acumulada"]);
            }
        }        
    }
    
    $tasa_dolar = $mensualidad_obj->realizar_consulta('consultar_monto_dolar_mensualidades');

    $total_mensual = [];

    ob_start();
    require_once "vista/reportes/reportes_pdf/pdf/cuadro_pagos_pdf.php";

    $html = ob_get_clean();

    $dompdf = new Dompdf(array('enable_remote' => true));
    
    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream("Cuadro de Pagos_" . $tasa_dolar["mes"] . "-" . $tasa_dolar["anio"]);

    echo json_encode(["status"=>true,"mensaje"=>"OK"]);
    exit();
}


if ($accion == "gastos_mensual") {
    require_once "vista/reportes/reportes_pdf/reporte_gastos_mensual_vista.php";
}
if ($accion == "generar_reporte_gastos_mensual") {
    $mes = $_POST['mes'];
    $anio = $_POST['anio'];
    $datos_reporte = $gastos_obj->obtenerDatosReporteMensual($mes, $anio);

    if ($datos_reporte === null || $datos_reporte['tasa_dolar'] == 0) {
        echo "No se encontraron datos para generar el reporte.";
        exit;
    }

    ob_start();
    require_once "vista/reportes/reportes_pdf/pdf/reporte_gastos_mensual_pdf.php";
    $html = ob_get_clean();

    $dompdf = new Dompdf(['enable_remote' => true]);
    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream("relacion_gastos_".$mes."_".$anio.".pdf",);
}

// Estadisticos
if ($accion == "reportes_estadisticos") {
    $mensualidad_obj = new Mensualidad();
    $mensualidad_obj->registrar_bitacora(CONSULTAR, GESTIONAR_REPORTES, "TODOS LOS REPORTES ESTADISTICOS");
    require_once "vista/reportes/reportes_estadisticos/reportes_estadisticos_vista.php";
}

if ($accion == "ingreso_egreso") {
    require_once "vista/reportes/reportes_estadisticos/reporte_ingresos_egresos/reporte_ingreso_egreso_vista.php";
}
if ($accion == "generar_reporte_ingresos_egresos") {
    $barra = $_POST["barra"];
    $fecha = $_POST["fecha_grafico_input"];

    $total_pagos = $_POST["total_pagos_input"];
    $total_gastos = $_POST["total_gastos_input"];
    $gastos_efectivo = $_POST["gastos_efectivo_input"];
    $gastos_transferencia = $_POST["gastos_transferencia_input"];
    $gastos_pago_movil = $_POST["gastos_pago_movil_input"];
    $pagos_efectivo = $_POST["pagos_efectivo_input"];
    $pagos_transferencia = $_POST["pagos_transferencia_input"];
    $pagos_pago_movil = $_POST["pagos_pago_movil_input"];
    $fecha_pagos = $_POST["fecha_pagos_input"];
    $fecha_gastos = $_POST["fecha_gastos_input"];

    ob_start();
    require_once "vista/reportes/reportes_estadisticos/reporte_ingresos_egresos/reporte_ingresos_egreso_pdf.php";


    $html = ob_get_clean();

    $dompdf = new Dompdf(array('enable_remote' => true));

    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream("reporte_ingreso_egreso");
}

if ($accion == "habitantes") {
    require_once "vista/reportes/reportes_estadisticos/reporte_habitantes/reporte_habitantes_vista.php";
}

?>