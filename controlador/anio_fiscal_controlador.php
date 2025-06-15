<?php

    require_once "modelo/anio_fiscal_modelo.php";    

    $anio_fiscal_obj = new Anio_fiscal(); //objeto año fiscal  

    if (isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){
            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($anio_fiscal_obj->consultar());
            // la hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
        //Despues de cada echo se regresa al javascript como respuesta en json

        elseif ($operacion == "registrar") {
            //se guardan las variables a registrar
            $fecha_inicio = $_POST["fecha_inicio"];
            $fecha_cierre = $_POST["fecha_cierre"];
            $estado = $_POST["estado"];
            $descripcion = $_POST["descripcion"];

            //se usan los setters correspondientes
            $anio_fiscal_obj->set_fecha_inicio($fecha_inicio);
            $anio_fiscal_obj->set_fecha_cierre($fecha_cierre);
            $anio_fiscal_obj->set_estado($estado);
            $anio_fiscal_obj->set_descripcion($descripcion);

            //se ejecuta la funcion:
            echo  json_encode($anio_fiscal_obj->registrar());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }
        elseif ($operacion == "consulta_especifica"){
            //se guardan el id para buscar
            $id_anio_fiscal = $_POST["id_anio_fiscal"];

            //se usan el setter correspondientes
            $anio_fiscal_obj->set_id_anio_fiscal($id_anio_fiscal);

            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($anio_fiscal_obj->consultar_anio_fiscal());
            // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }

        elseif ($operacion == "modificar") {
            //se guardan las variables a modificar
            $id_anio_fiscal = $_POST["id_anio_fiscal"];
            $fecha_inicio = $_POST["fecha_inicio"];
            $fecha_cierre = $_POST["fecha_cierre"];
            $estado = $_POST["estado"];
            $descripcion = $_POST["descripcion"];
            // ...

            //se usan los setters correspondientes
            $anio_fiscal_obj->set_id_anio_fiscal($id_anio_fiscal);
            $anio_fiscal_obj->set_fecha_inicio($fecha_inicio);
            $anio_fiscal_obj->set_fecha_cierre($fecha_cierre);
            $anio_fiscal_obj->set_estado($estado);
            $anio_fiscal_obj->set_descripcion($descripcion);
            // ....
            
            //se ejecuta la funcion:
            echo  json_encode($anio_fiscal_obj->editar());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }

        elseif ($operacion == "eliminar") {
            //se guardan el id de la variable a eliminar
            $id_anio_fiscal = $_POST["id_anio_fiscal"];

            //se usan el setter correspondientes
            $anio_fiscal_obj->set_id_anio_fiscal($id_anio_fiscal);

            //se ejecuta la funcion:
            echo  json_encode($anio_fiscal_obj->eliminar());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }elseif ($operacion == "ultimo_id"){
            echo json_encode($anio_fiscal_obj->lastId());
        }

        exit;//es salida en ingles... No puede faltar
    }
    require_once "vista/anio_fiscal/anio_fiscal_vista.php";

?>