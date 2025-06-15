<?php

    require_once "modelo/usuario_modelo.php";
    require_once 'modelo/rol_modelo.php';

    $usuario_obj = new Usuario(); //objeto usuario  
    $rol_obj = new Rol(); // objeto roles
    $roles = $rol_obj->consultar(); 

    if (isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){
            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($usuario_obj->consultar());
            // la hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
        //Despues de cada echo se regresa al javascript como respuesta en json

        elseif ($operacion == "registrar") {
            //se guardan las variables a registrar
            $apellido = $_POST["apellido"];
            $nombre = $_POST["nombre"];  
            $correo = $_POST["correo"];  
            $contra = $_POST["contra"]; 
            $rol = $_POST["rol"];              

            //se usan los setters correspondientes
            $usuario_obj->set_apellido($apellido);
            $usuario_obj->set_nombre($nombre);
            $usuario_obj->set_correo($correo);
            $usuario_obj->set_contra($contra);
            $usuario_obj->set_rol_id($rol);

            //se ejecuta la funcion:
            echo  json_encode($usuario_obj->registrar());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }
        elseif ($operacion == "consulta_especifica"){
            //se guardan el id para buscar
            $id_usuario = $_POST["id_usuario"];

            //se usan el setter correspondientes
            $usuario_obj->set_id_usuario($id_usuario);

            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($usuario_obj->consultar_usuario());
            // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }

        elseif ($operacion == "modificar") {
            //se guardan las variables a modificar
            $id_usuario = $_POST["id_usuario"];
            $apellido = $_POST["apellido"];
            $nombre = $_POST["nombre"];  
            $correo = $_POST["correo"];  
            $contra = $_POST["contra"];  
            $rol = $_POST["rol"];
            // ...

            //se usan los setters correspondientes
            $usuario_obj->set_id_usuario($id_usuario);
            $usuario_obj->set_apellido($apellido);
            $usuario_obj->set_nombre($nombre);
            $usuario_obj->set_correo($correo);
            $usuario_obj->set_contra($contra);
            $usuario_obj->set_rol_id($rol);
            // ....
            
            //se ejecuta la funcion:
            echo  json_encode($usuario_obj->editar_usuario());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }

        elseif ($operacion == "eliminar") {
            //se guardan el id de la variable a eliminar
            $id_usuario = $_POST["id_usuario"];

            //se usan el setter correspondientes
            $usuario_obj->set_id_usuario($id_usuario);

            //se ejecuta la funcion:
            echo  json_encode($usuario_obj->eliminar_usuario());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }elseif ($operacion == "ultimo_id"){
            echo json_encode($usuario_obj->lastId());
        }

        exit;//es salida en ingles... No puede faltar
    }
    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"]; //Esto es igual pero para las validaciones
        if ($validar == "correo"){
            $usuario_obj->set_correo($_POST["correo"]);
            echo  json_encode($usuario_obj->verificar_correo());
        }
        if ($validar == "contra"){
            $contra = $_POST["contra"];

            $usuario_obj->set_id_usuario($_POST["id_usuario"]);

            $usuario_validar = $usuario_obj->consultar_usuario();

            echo json_encode(password_verify($contra, $usuario_validar["contrasenia"]));
        }
        exit;
    }
    //FIN de AJAX
    require_once "vista/usuarios/usuario_vista.php";

?>