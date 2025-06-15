<?php
    require_once("modelo/habitantes_modelo.php");
    require_once("modelo/apartamentos_modelo.php");

    $obj_apartamento = new Apartamento(); // Objeto apartamento
    $obj_habitante = new Habitantes(); // Objeto habitante
 
    $registro_apartamento = $obj_apartamento->consultar();

    if(isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){
            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($obj_habitante->consultar());
            // la hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
        //Despues de cada echo se regresa al javascript como respuesta en json

        elseif ($operacion == "registrar") {
            //se guardan las variables a registrar
            $cedula = $_POST["cedula"];  
            $nombre_habitante = $_POST["nombre_habitante"];  
            $apellido = $_POST["apellido"]; 
            $fecha_nacimiento = $_POST["fecha_nacimiento"];
            $sexo = $_POST["sexo"];
            $telefono = $_POST["telefono"];
            $apartamento_id = $_POST["apartamento_id"];              

            //se usan los setters correspondientes
            $obj_habitante->set_cedula($cedula);
            $obj_habitante->set_nombre_habitante($nombre_habitante);
            $obj_habitante->set_apellido($apellido);
            $obj_habitante->set_fecha_nacimiento($fecha_nacimiento);
            $obj_habitante->set_sexo($sexo);
            $obj_habitante->set_telefono($telefono);
            $obj_habitante->set_apartamento_id($apartamento_id);

            //se ejecuta la funcion:
            echo  json_encode($obj_habitante->registrar_habitante());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }
        elseif ($operacion == "consulta_especifica"){
            //se guardan el id para buscar
            $id_habitante = $_POST["id_habitante"];

            //se usan el setter correspondientes
            $obj_habitante->set_id_habitante($id_habitante);

            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($obj_habitante->consultar_habitante());
            // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }

        elseif ($operacion == "modificar") {
            //se guardan las variables a modificar
            $id_habitante = $_POST["id_habitante"];
            $cedula = $_POST["cedula"];  
            $nombre_habitante = $_POST["nombre_habitante"];  
            $apellido = $_POST["apellido"]; 
            $fecha_nacimiento = $_POST["fecha_nacimiento"];
            $sexo = $_POST["sexo"];
            $telefono = $_POST["telefono"];
            $apartamento_id = $_POST["apartamento_id"]; 
            // ...

            //se usan los setters correspondientes
            $obj_habitante->set_id_habitante($id_habitante);
            $obj_habitante->set_cedula($cedula);
            $obj_habitante->set_nombre_habitante($nombre_habitante);
            $obj_habitante->set_apellido($apellido);
            $obj_habitante->set_fecha_nacimiento($fecha_nacimiento);
            $obj_habitante->set_sexo($sexo);
            $obj_habitante->set_telefono($telefono);
            $obj_habitante->set_apartamento_id($apartamento_id);
            // ....
            
            //se ejecuta la funcion:
            echo  json_encode($obj_habitante->editar_habitante());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }

        elseif ($operacion == "eliminar") {
            //se guardan el id de la variable a eliminar
            $id_habitante = $_POST["id_habitante"];

            //se usan el setter correspondientes
            $obj_habitante->set_id_habitante($id_habitante);

            //se ejecuta la funcion:
            echo  json_encode($obj_habitante->eliminar_habitante());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }elseif ($operacion == "ultimo_id"){
            echo json_encode($obj_habitante->lastId());
        }

        exit;//es salida en ingles... No puede faltar
    }

    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"]; //Esto es igual pero para las validaciones
        if ($validar == "cedula"){
            $obj_habitante->set_cedula($_POST["cedula"]);
            echo  json_encode($obj_habitante->verificar_habitante());
        }
        
        exit;
    }
    //FIN de AJAX
    require_once "vista/habitantes/habitantes_vista.php";
?>