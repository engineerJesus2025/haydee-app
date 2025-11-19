<?php
use haydee\ayuda\Sesiones;
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_REPORTES, CONSULTAR);

use haydee\modelo\Habitantes;
use haydee\modelo\Gastos;
use haydee\modelo\Mensualidad;

use Dompdf\Dompdf;
// use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$gastos_obj = new Gastos();

$habitantes_obj = new Habitantes(); 

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
    elseif ($operacion == "consultar_meses_con_gastos") {
        echo json_encode($gastos_obj->listar_meses_con_gastos());
        exit;
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

if (isset($_POST["validar"])) {
    $validar = $_POST["validar"];

    if ($validar == "validar_clave_foranea") {
        $gastos_obj = new Gastos();

        $tabla = $_POST["tabla"];
        $nombre_clave = $_POST["nombre_clave"];
        $valor = $_POST["valor"];
        
        $resultado = $gastos_obj->realizar_consulta('validar_clave_foranea',["tabla"=>$tabla,"nombre_clave"=>$nombre_clave,"valor"=>$valor]);
        
        echo json_encode($resultado);
    }
    exit;
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

    $registro_porpietario = $habitantes_obj->realizar_consulta('consulta_especifica');
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
    $registro_porpietario = $habitantes_obj->realizar_consulta('consulta_especifica');
    
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
                    if (array_search($meses_nombres[$deuda["mes"]-1], $meses_seleccionados) === false) {
                        array_push($meses_seleccionados, $meses_nombres[$deuda["mes"]-1]);
                    }
                }
            }
        }
    }

    $meses_apartamento = [];
    $incluido = false;
    $apartamentos = [];
    foreach ($deudas_globales as $indice_deudas_globales => $deuda){
        if (!(array_key_exists($deuda["nro_apartamento"],$apartamentos))) {
            $apartamentos[$deuda["nro_apartamento"]] = [];
        }
        array_push($apartamentos[$deuda["nro_apartamento"]], [$meses_nombres[$deuda["mes"]-1],$indice_deudas_globales,$deuda["anio"]]);
    }
    $cantidad_incluida = 0;
    foreach ($meses_seleccionados as $mes) {
        foreach ($apartamentos as $nro_apartamento => $apartamento) {
            $meses_apartamento = array_column($apartamento, 0);
            $indice_apartamento = array_search($mes, $meses_apartamento);

            if ($indice_apartamento === false) {
                $array_nuevo = [
                    "nro_apartamento" => $nro_apartamento,
                    "anio"=>$apartamento[0][2],
                    "mes"=> strval(array_search($mes, $meses_nombres) + 1),
                    "cambio_neto_mes"=>0,
                    "deuda_acumulada"=>0,                    
                ];
                $apartamento[0][1] = $apartamento[0][1] + $cantidad_incluida;
                $cantidad_incluida++;

                array_splice($deudas_globales, $apartamento[0][1],0,[$array_nuevo]);
            }
        }
    }
    
    foreach ($deudas_globales as $deuda) {
        if (intval($deuda["anio"]) <= intval($anio_limite)){
            if (intval($deuda["anio"]) == intval($anio_limite)) {
                if (intval($deuda["mes"]) <= intval($mes_limite)){

                    if (!(array_key_exists($deuda["nro_apartamento"],$deudas_filtradas))) {
                        $deudas_filtradas[$deuda["nro_apartamento"]] = [];
                    }
                    
                    array_push($deudas_filtradas[$deuda["nro_apartamento"]], $deuda["deuda_acumulada"]);                    
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

    $mensualidad_obj->set_mes($mes_limite);
    $mensualidad_obj->set_anio($anio_limite);
    
    $tasa_dolar = $mensualidad_obj->realizar_consulta('consultar_tasa_dolar_mensualidades');

    $total_mensual = [];

    $cantidadParaAgrupar = 5;

    $cebecera_tabla = [];
    $cuerpo_tabla = [];

    if (count($meses_seleccionados) > $cantidadParaAgrupar){        
        $mesesAGrupar = array_slice($meses_seleccionados, 0, $cantidadParaAgrupar);

        if (!empty($mesesAGrupar)) {
            // 2. **Crear la Agrupación Única**
            $mesInicio = reset($mesesAGrupar); // Obtiene el primer elemento
            $mesFin = end($mesesAGrupar);       // Obtiene el último elemento

            if ($mesInicio === $mesFin) {
                // Caso de agrupar solo 1 elemento
                $arregloResultante[] = $mesInicio;
            } else {
                // Agrupación normal de N > 1 elementos
                $agrupacion = "{$mesInicio} / {$mesFin}";
                $arregloResultante[] = $agrupacion;
            }
        }

        $mesesRestantes = array_slice($meses_seleccionados, $cantidadParaAgrupar);

        $cebecera_tabla = array_merge($arregloResultante, $mesesRestantes);

        $meses_sin_agrupar = count($cebecera_tabla) - 1;
        
        foreach ($deudas_filtradas as $nro_apartamento => $apartamento) {            
            if (!(array_key_exists($nro_apartamento,$cuerpo_tabla))) {
                $cuerpo_tabla[$nro_apartamento] = [];
            }
            if (!(array_key_exists("grupo",$cuerpo_tabla[$nro_apartamento]))) {
                $cuerpo_tabla[$nro_apartamento]["grupo"] = 0;
            }
            // var_dump($deudas_filtradas);
            // for ($i=0; $i < count($apartamento)-($meses_sin_agrupar); $i++) {
            //     // echo $apartamento[$i] . "<br><br>";
            //     $cuerpo_tabla[$nro_apartamento]["grupo"] += $apartamento[$i];
            // }
            $cuerpo_tabla[$nro_apartamento]["grupo"] += $apartamento[count($apartamento)-($meses_sin_agrupar)-1];
            for ($i= count($apartamento)-($meses_sin_agrupar); $i < count($apartamento); $i++) { 
                // echo $apartamento[$i] . "<br><br>";
                array_push($cuerpo_tabla[$nro_apartamento], $apartamento[$i]);
            }
        }
    }

    // var_dump($cuerpo_tabla);echo "<br><br>";
    // var_dump($cebecera_tabla);

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
    $tasa_dolar = isset($_POST['tasa_dolar']) ? (float)$_POST['tasa_dolar'] : 0;


    $datos_reporte = $gastos_obj->obtenerDatosReporteMensual($mes, $anio);

    if ($datos_reporte === null || $tasa_dolar == 0) {

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
    $selecion = $_POST["mostrar_datos_input"];    

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