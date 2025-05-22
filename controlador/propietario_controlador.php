<?php 

require_once "modelo/propietario_modelo.php";
$propietario = new Propietario();

if (isset($_POST["operacion"])){
    $operacion = $_POST["operacion"];

    if ($operacion == "consulta"){
        // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
        echo  json_encode($propietario->consultar());
        // la hice para que retorne un arreglo, si sale vacio solo mandara un array con false
    }
    //Despues de cada echo se regresa al javascript como respuesta en json

    elseif ($operacion == "registrar") {
        //se guardan las variables a registrar
        $nombre = $_POST["nombre"];
        $apellido = $_POST["apellido"];  
        $cedula = $_POST["cedula"];  
        $telefono = $_POST["telefono"];  
        $correo = $_POST["correo"];              
        
        //se usan los setters correspondientes
        $propietario->set_nombre($nombre);
        $propietario->set_apellido($apellido);
        $propietario->set_cedula($cedula);
        $propietario->set_telefono($telefono);
        $propietario->set_correo($correo);
        //se ejecuta la funcion:    
        echo  json_encode($propietario->registrar());
    }
        //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        elseif ($operacion == "consulta_especifica"){
            //se guardan el id para buscar
            $id_propietario = $_POST["id_propietario"];

            //se usan el setter correspondientes
            $propietario->set_id_propietario($id_propietario);

            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($propietario->consultar_propietario());
            // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }

        elseif ($operacion == "modificar") {
            //se guardan las variables a modificar
            $id_propietario = $_POST["id_propietario"];
            $nombre = $_POST["nombre"];
            $apellido = $_POST["apellido"];  
            $cedula = $_POST["cedula"];  
            $telefono = $_POST["telefono"];  
            $correo = $_POST["correo"];  
            
            //se usan los setters correspondientes
            $propietario->set_id_propietario($id_propietario);
            $propietario->set_nombre($nombre);
            $propietario->set_apellido($apellido);
            $propietario->set_cedula($cedula);
            $propietario->set_telefono($telefono);
            $propietario->set_correo($correo);
            
            //se ejecuta la funcion:    
            echo  json_encode($propietario->editar_propietario());
        }

        elseif ($operacion == "eliminar") {
            //se guardan el id para eliminar
            $id_propietario = $_POST["id_propietario"];

            //se usan el setter correspondientes
            $propietario->set_id_propietario($id_propietario);

            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($propietario->eliminar_propietario());
        }elseif ($operacion == "ultimo_id"){
            echo  json_encode($propietario->lastId());
        }

        exit;

}
        require_once "vista/propietarios/propietario_vista.php";
?>