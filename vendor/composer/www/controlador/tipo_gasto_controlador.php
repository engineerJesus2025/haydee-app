<?php
    require_once("modelo/tipo_gasto_modelo.php");

    $obj_tipo_gasto = new Tipo_gasto(); // Objeto tipo_gasto

    if(isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){
            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($obj_tipo_gasto->consultar());
            // la hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
        //Despues de cada echo se regresa al javascript como respuesta en json

        elseif ($operacion == "registrar") {
            //se guardan las variables a registrar
            $nombre_tipo_gasto = $_POST["nombre_tipo_gasto"];

            //se usan los setters correspondientes
            $obj_tipo_gasto->set_nombre_tipo_gasto($nombre_tipo_gasto);

            //se ejecuta la funcion:
            echo  json_encode($obj_tipo_gasto->registrar_tipo_gasto());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }
        elseif ($operacion == "consulta_especifica"){
            //se guardan el id para buscar
            $id_tipo_gasto = $_POST["id_tipo_gasto"];

            //se usan el setter correspondientes
            $obj_tipo_gasto->set_id_tipo_gasto($id_tipo_gasto);

            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($obj_tipo_gasto->consultar_tipo_gasto());
            // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }

        elseif ($operacion == "modificar") {
            //se guardan las variables a modificar
            $id_tipo_gasto = $_POST["id_tipo_gasto"];
            $nombre_tipo_gasto = $_POST["nombre_tipo_gasto"];  

            //se usan los setters correspondientes
            $obj_tipo_gasto->set_id_tipo_gasto($id_tipo_gasto);
            $obj_tipo_gasto->set_nombre_tipo_gasto($nombre_tipo_gasto);

            //se ejecuta la funcion:
            echo  json_encode($obj_tipo_gasto->editar_tipo_gasto());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }

        elseif ($operacion == "eliminar") {
            //se guardan el id de la variable a eliminar
            $id_tipo_gasto = $_POST["id_tipo_gasto"];

            //se usan el setter correspondientes
            $obj_tipo_gasto->set_id_tipo_gasto($id_tipo_gasto);

            //se ejecuta la funcion:
            echo  json_encode($obj_tipo_gasto->eliminar_tipo_gasto());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }elseif ($operacion == "ultimo_id"){
            echo json_encode($obj_tipo_gasto->lastId());
        }

        exit;//es salida en ingles... No puede faltar
    }


    //FIN de AJAX
    require_once "vista/tipo_gasto/tipo_gasto_vista.php";
?>