<?php
    require_once("modelo/apartamentos_modelo.php");

    $obj_apartamento = new Apartamento(); // Objeto Apartamento
 
    if(isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){

            echo  json_encode($obj_apartamento->consultar());

        }elseif ($operacion == "registrar") {
            $nro_apartamento = $_POST["nro_apartamento"];  
            $porcentaje_apartamento = $_POST["porcentaje_apartamento"];
            $gas = $_POST["gas"]; 
            $agua = $_POST["agua"];
            $alquilado = $_POST["alquilado"];
            $propietario_id = $_POST["propietario_id"];

            $obj_apartamento->set_nro_apartamento($nro_apartamento);
            $obj_apartamento->set_porcentaje_participacion($porcentaje_apartamento);
            $obj_apartamento->set_gas($gas);
            $obj_apartamento->set_agua($agua);
            $obj_apartamento->set_alquilado($alquilado);

            echo  json_encode($obj_apartamento->registrar_apartamento());
        }elseif ($operacion == "consulta_especifica"){
            $id_apartamento = $_POST["id_apartamento"];

            $obj_apartamento->set_id_apartamento($id_apartamento);

            echo  json_encode($obj_apartamento->consultar_apartamento());
        }elseif ($operacion == "modificar") {
            $id_apartamento = $_POST["id_apartamento"];
            $nro_apartamento = $_POST["nro_apartamento"];  
            $porcentaje_apartamento = $_POST["porcentaje_apartamento"];
            $gas = $_POST["gas"]; 
            $agua = $_POST["agua"];
            $alquilado = $_POST["alquilado"];
            $propietario_id = $_POST["propietario_id"];

            $obj_apartamento->set_id_apartamento($id_apartamento);
            $obj_apartamento->set_nro_apartamento($nro_apartamento);
            $obj_apartamento->set_porcentaje_participacion($porcentaje_apartamento);
            $obj_apartamento->set_gas($gas);
            $obj_apartamento->set_agua($agua);
            $obj_apartamento->set_alquilado($alquilado);
            
            echo  json_encode($obj_apartamento->editar_apartamento());
        }elseif ($operacion == "eliminar") {
            $id_apartamento = $_POST["id_apartamento"];

            $obj_apartamento->set_id_apartamento($id_apartamento);

            echo  json_encode($obj_apartamento->eliminar_apartamento());
        }elseif ($operacion == "ultimo_id"){
            echo json_encode($obj_apartamento->lastId());
        }

        exit;
    }

    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"];
        if ($validar == "nro_apartamento"){
            $obj_apartamento->set_nro_apartamento($_POST["nro_apartamento"]);
            echo  json_encode($obj_apartamento->verificar_apartamento());
        }
        
        exit;
    }

    require_once "vista/apartamentos/apartamentos_vista.php";
?>