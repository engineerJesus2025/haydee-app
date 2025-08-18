<?php 
require_once "modelo/caja_chica_modelo.php";

$caja_obj = new Caja_chica();

// $res =$caja_obj->verificar_caja_mes();

// // var_dump($res);

if (isset($_POST["operacion"])){
    $operacion = $_POST["operacion"];

    if ($operacion == "consultar_cajas_chicas"){
        echo  json_encode($caja_obj->consultar());
    }
    else if ($operacion == "buscar_mes"){
        $fecha = strtotime($_POST["fecha"]);
        
        $caja_obj->set_fecha_apertura($fecha);

        echo  json_encode($caja_obj->buscar_mes());  
    }
    else if ($operacion == "editar_observacion"){
        $observaciones = $_POST["observaciones"];
        $id_caja = $_POST["id_caja"];
        
        $caja_obj->set_observaciones($observaciones);
        $caja_obj->set_id_caja_chica($id_caja);

        echo  json_encode($caja_obj->editar_observacion());
    }

    exit;
}

if($accion == "inicio"){    
    require_once "vista/caja_chica/caja_chica_vista.php";
}

//Aqui habian 183 lineas de codigo
?>
