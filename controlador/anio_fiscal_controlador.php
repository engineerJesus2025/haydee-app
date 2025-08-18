<?php
    require_once "modelo/anio_fiscal_modelo.php";    

    if (isset($_POST["operacion"])){        
        $operacion = $_POST["operacion"];

        if ($operacion == "consultar_anios_fiscales"){
            $anio_fiscal_obj = new Anio_fiscal();
            echo  json_encode($anio_fiscal_obj->realizar_consulta("consultar"));
        }

        elseif ($operacion == "registrar") {
            $anio_fiscal_obj = new Anio_fiscal();

            $fecha_inicio = $_POST["fecha_inicio"];
            $fecha_cierre = $_POST["fecha_cierre"];
            $estado = $_POST["estado"];
            $descripcion = $_POST["descripcion"];

            $anio_fiscal_obj->set_fecha_inicio($fecha_inicio);
            $anio_fiscal_obj->set_fecha_cierre($fecha_cierre);
            $anio_fiscal_obj->set_estado($estado);
            $anio_fiscal_obj->set_descripcion($descripcion);

            echo  json_encode($anio_fiscal_obj->realizar_consulta("registrar"));
        }
        elseif ($operacion == "consulta_especifica"){
            $anio_fiscal_obj = new Anio_fiscal();

            $id_anio_fiscal = $_POST["id_anio_fiscal"];

            $anio_fiscal_obj->set_id_anio_fiscal($id_anio_fiscal);

            echo  json_encode($anio_fiscal_obj->realizar_consulta("consultar_anio_fiscal"));
        }

        elseif ($operacion == "modificar") {
            $anio_fiscal_obj = new Anio_fiscal();

            $id_anio_fiscal = $_POST["id_anio_fiscal"];
            $fecha_inicio = $_POST["fecha_inicio"];
            $fecha_cierre = $_POST["fecha_cierre"];
            $estado = $_POST["estado"];
            $descripcion = $_POST["descripcion"];
            
            $anio_fiscal_obj->set_id_anio_fiscal($id_anio_fiscal);
            $anio_fiscal_obj->set_fecha_inicio($fecha_inicio);
            $anio_fiscal_obj->set_fecha_cierre($fecha_cierre);
            $anio_fiscal_obj->set_estado($estado);
            $anio_fiscal_obj->set_descripcion($descripcion);
            
            echo  json_encode($anio_fiscal_obj->realizar_consulta("editar"));            
        }

        elseif ($operacion == "eliminar") {
            $anio_fiscal_obj = new Anio_fiscal();
            
            $id_anio_fiscal = $_POST["id_anio_fiscal"];
            
            $anio_fiscal_obj->set_id_anio_fiscal($id_anio_fiscal);

            echo  json_encode($anio_fiscal_obj->realizar_consulta("eliminar"));
        }

        exit;
    }
    require_once "vista/anio_fiscal/anio_fiscal_vista.php";

?>