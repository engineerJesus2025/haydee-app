<?php
require_once "modelo/gastos_modelo.php";
require_once "modelo/propietario_modelo.php";

require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$gastos_obj = new Gastos();
$propietarios_obj = new Propietario();

    if (isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consultar_ingresos_egresos"){
            $fecha_inicio = $_POST["fecha_inicio"];
            $fecha_fin = $_POST["fecha_fin"];
            $balance = $_POST["balance"];
            $metodo_pago = $_POST["metodo_pago"];
            $tipo_gasto = $_POST["tipo_gasto"];
            
            echo  json_encode($gastos_obj->obtenerIngresosYEgresos($fecha_inicio,$fecha_fin,$balance,$metodo_pago,$tipo_gasto));
        }

        exit();
    }

    if($accion == "inicio"){
        require_once "vista/reportes/reportes_vista.php";
    }
	if($accion == "reportes_pdf"){
        require_once "vista/reportes/reportes_pdf/reportes_pdf_vista.php";
    }
    if ($accion == "solvencia"){
        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $id_propietario = $_POST["select_reporte"];
        
        $propietarios_obj->set_id_propietario($id_propietario);

        $registro_porpietario = $propietarios_obj->consultar_propietario();
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
    if ($accion == "residencia"){
        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $id_propietario = $_POST["select_reporte"];
        
        $propietarios_obj->set_id_propietario($id_propietario);

        $registro_porpietario = $propietarios_obj->consultar_propietario();

        ob_start();
        require_once "vista/reportes/reportes_pdf/pdf/reporte_residencia_pdf.php";

        $html = ob_get_clean();

        $dompdf = new Dompdf(array('enable_remote' => true));
        
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("constancia_residencia_" . $registro_porpietario["nombre"] . "_" . $registro_porpietario["apellido"]);
    }

    // Estadisticos
    if($accion == "reportes_estadisticos"){
        require_once "vista/reportes/reportes_estadisticos/reportes_estadisticos_vista.php";
    }

    if($accion == "ingreso_egreso"){
        require_once "vista/reportes/reportes_estadisticos/reporte_ingresos_egresos/reporte_ingreso_egreso_vista.php";
    }
    if ($accion == "generar_reporte_ingresos_egresos"){
        $barra = $_POST["barra"];

        ob_start();
        require_once "vista/reportes/reportes_estadisticos/reporte_ingresos_egresos/reporte_ingresos_egreso_pdf.php";


        $html = ob_get_clean();

        $dompdf = new Dompdf(array('enable_remote' => true));
        
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("reporte_estadistico_sede");
    }


?>