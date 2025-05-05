<?php

    require_once "modelo/usuario_modelo.php";

    $usuario = new Usuario();    

    if (isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){
            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($usuario->consultar());
            // la hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
        //Despues de cada echo se regresa al javascript como respuesta en json

        elseif ($operacion == "registrar") {
            //se guardan las variables a registrar
            $cedula = $_POST["tipo_cedula"]. $_POST["cedula"];
            $apellido = $_POST["apellido"];
            $nombre = $_POST["nombre"];  
            $correo = $_POST["correo"];  
            $contra = $_POST["contra"];  
            $rol = $_POST["rol"];              
            // ...


            //se usan los setters correspondientes
            $usuario->set_cedula($cedula);
            $usuario->set_apellido($apellido);
            $usuario->set_nombre($nombre);
            $usuario->set_correo($correo);
            $usuario->set_contra($contra);
            $usuario->set_rol_id($rol);
            // ... 

            //se ejecuta la funcion:
            echo  json_encode($usuario->registrar());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }
        elseif ($operacion == "consulta_especifica"){
            //se guardan el id para buscar
            $id_usuario = $_POST["id_usuario"];

            //se usan el setter correspondientes
            $usuario->set_id_usuario($id_usuario);

            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($usuario->consultar_usuario());
            // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }

        elseif ($operacion == "modificar") {
            //se guardan las variables a modificar
            $id_usuario = $_POST["id_usuario"];
            $cedula = $_POST["tipo_cedula"]. $_POST["cedula"];
            $apellido = $_POST["apellido"];
            $nombre = $_POST["nombre"];  
            $correo = $_POST["correo"];  
            $contra = $_POST["contra"];  
            $rol = $_POST["rol"];
            // ...

            //se usan los setters correspondientes
            $usuario->set_id_usuario($id_usuario);
            $usuario->set_cedula($cedula);
            $usuario->set_apellido($apellido);
            $usuario->set_nombre($nombre);
            $usuario->set_correo($correo);
            $usuario->set_contra($contra);
            $usuario->set_rol_id($rol);
            // ....
            
            //se ejecuta la funcion:
            echo  json_encode($usuario->editar_usuario());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }

        elseif ($operacion == "eliminar") {
            //se guardan el id de la variable a eliminar
            $id_usuario = $_POST["id_usuario"];

            //se usan el setter correspondientes
            $usuario->set_id_usuario($id_usuario);

            //se ejecuta la funcion:
            echo  json_encode($usuario->eliminar_usuario());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }

        exit;//es salida en ingles... No puede faltar
    }
    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"];
        if ($validar == "cedula") {
            $usuario->set_cedula($_POST["tipo_cedula"] . $_POST["cedula"]);
            echo  json_encode($usuario->verificar_cedula());
        }
        elseif ($validar == "correo"){
            $usuario->set_correo($_POST["correo"]);
            echo  json_encode($usuario->verificar_correo());
        }
        exit;
    }
    //FIN de AJAX

    if($accion == "inicio"){
        // unset($_SESSION["mensaje"]);
        $roles = $usuario->consultar_roles();

        require_once "vista/usuarios/usuario_inicio_vista.php";
        unset($_SESSION["mensaje"]);
        unset($_SESSION["estado_consulta"]);
    }

?>