<?php 
require_once "modelo/caja_chica_modelo.php";

$caja_obj = new Caja_chica();

if (isset($_POST["operacion"])){
    $operacion = $_POST["operacion"];

    if ($operacion == "consultar_cajas_chicas"){
        echo  json_encode($caja_obj->realizar_consulta('consultar'));
    }
    else if ($operacion == "buscar_mes"){
        $id_caja = $_POST["id_caja"];
        
        $caja_obj->set_id_caja_chica($id_caja);

        echo  json_encode($caja_obj->realizar_consulta('buscar_mes'));
    }
    else if ($operacion == "editar_observacion"){
        $observaciones = $_POST["observaciones"];
        $id_caja = $_POST["id_caja"];
        
        $caja_obj->set_observaciones($observaciones);
        $caja_obj->set_id_caja_chica($id_caja);

        echo  json_encode($caja_obj->realizar_consulta('editar_observacion'));
    }

    exit;
}

if($accion == "inicio"){    
    require_once "vista/caja_chica/caja_chica_vista.php";
}
?>
