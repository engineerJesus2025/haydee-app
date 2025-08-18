<?php

    require_once "modelo/presupuesto_modelo.php";
    require_once "modelo/caja_chica_modelo.php";

    $presupuesto_obj = new Presupuesto_mensual();
    $caja_obj = new Caja_chica(); 

    if (isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){            
            echo  json_encode($presupuesto_obj->consultar());            
        }  
        else if ($operacion == "consultar_ultima_caja"){            
            echo  json_encode($caja_obj->consultar_ultima_caja());            
        }
        elseif ($operacion == "registrar") {
            //se guardan las variables a registrar
            $mes = $_POST["mes"];
            $anio = $_POST["anio"];
            $monto_presupuesto = $_POST["monto"];
            $descripcion = $_POST["descripcion"];

            $presupuesto_obj->set_mes($mes);
            $presupuesto_obj->set_anio($anio);
            $presupuesto_obj->set_monto_presupuesto($monto_presupuesto);
            $presupuesto_obj->set_descripcion($descripcion);

            echo  json_encode($presupuesto_obj->registrar());
        }
        elseif ($operacion == "consulta_especifica"){
            $id_presupuesto = $_POST["id_presupuesto"];
            $presupuesto_obj->set_id_presupuesto($id_presupuesto);
            echo  json_encode($presupuesto_obj->consultar_presupuesto());
        }

        elseif ($operacion == "editar_presupuesto") {
            //se guardan las variables a modificar
            $id_presupuesto = $_POST["id_presupuesto"];
            $mes = $_POST["mes"];
            $anio = $_POST["anio"];
            $monto_presupuesto = $_POST["monto"];
            $descripcion = $_POST["descripcion"];            

            $presupuesto_obj->set_mes($mes);
            $presupuesto_obj->set_anio($anio);
            $presupuesto_obj->set_monto_presupuesto($monto_presupuesto);
            $presupuesto_obj->set_descripcion($descripcion);
            $presupuesto_obj->set_id_presupuesto($id_presupuesto);
                        
            echo  json_encode($presupuesto_obj->editar());            
        }

        elseif ($operacion == "eliminar") {
            $id_presupuesto = $_POST["id_presupuesto"];
            $presupuesto_obj->set_id_presupuesto($id_presupuesto);
            
            echo  json_encode($presupuesto_obj->eliminar());
        }
        elseif ($operacion == "ultimo_id"){
            echo json_encode($presupuesto_obj->lastId());
        }
        exit;
    }
    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"]; //Esto es igual pero para las validaciones
        if ($validar == "correo"){
            $presupuesto_obj->set_correo($_POST["correo"]);
            echo  json_encode($presupuesto_obj->verificar_correo());
        }
        
        exit;
    }        
    require_once "vista/presupuesto_mensual/presupuesto_vista.php";    
?>