<?php 
require_once "modelo/mensualidad_modelo.php";

$mensualidad_obj = new Mensualidad();

if (isset($_POST["operacion"])){
    $operacion = $_POST["operacion"];

    if ($operacion == "consultar"){       
        echo json_encode($mensualidad_obj->consultar());
    }

    else if ($operacion == "consultar_mensualidad"){        
        $set_gasto_mes_id = $_POST["gasto_mes_id"];

        $mensualidad_obj->set_gasto_mes_id($set_gasto_mes_id);

        echo json_encode($mensualidad_obj->consultar_mensualidad());
    }
    else if ($operacion == "consultar_gastos"){
        $gasto_mes_id = $_POST["gasto_mes_id"];        

        $mensualidad_obj->set_gasto_mes_id($gasto_mes_id);

        echo json_encode($mensualidad_obj->consultar_gastos());
    }
    else if ($operacion == "consultar_apartamentos"){
        echo json_encode($mensualidad_obj->consultar_apartamentos());
    }
    else if($operacion == "registrar"){
        $monto = $_POST["monto"];
        $tasa_dolar = $_POST["tasa_dolar"];
        $mes = date("m");
        $anio = date("Y");
        $apartamento_id = $_POST["apartamento_id"];

        $mensualidad_obj->set_mes($mes);
        $mensualidad_obj->set_anio($anio);

        $gasto_mes_id = $mensualidad_obj->consultar_gasto_mes();


        $mes = "03";

        $mensualidad_obj->set_monto($monto);
        $mensualidad_obj->set_tasa_dolar($tasa_dolar);
        
        $mensualidad_obj->set_apartamento_id($apartamento_id);
        $mensualidad_obj->set_gasto_mes_id($gasto_mes_id["id_gasto_mes"]);

        echo json_encode($mensualidad_obj->registrar());
    }
    //Despues de cada echo se regresa al javascript como respuesta en json
    exit;//es salida en ingles... No puede faltar
}


require_once 'vista/mensualidad/mensualidad_vista.php';
?>