<?php
    require_once("modelo/rol_modelo.php");
    require_once "ayuda/ayuda.php";
    require_once "modelo/permisos_usuarios_modelo.php";
    require_once "modelo/modulos_modelo.php";
    require_once "modelo/roles_permisos_modelo.php";
    require_once "vista/componentes/sesion.php";
    
    $rol_obj = new Rol();
    $modulo_obj = new Modulos();
    $permisos_usuarios_obj = new Permisos_usuarios();
    $roles_permisos_obj = new Roles_permisos();

    $registros_modulos = $modulo_obj->consultar();
    $registros_permisos_usuarios = $permisos_usuarios_obj->consultar();

    if (isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){
            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($rol_obj->consultar());
            // la hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
        //Despues de cada echo se regresa al javascript como respuesta en json

        elseif ($operacion == "registrar") {
            //se guardan las variables a registrar
            $nombre = $_POST["nombre"];  
            $permisos = explode(",",$_POST["permisos"]);

            //se usan los setters correspondientes            
            $rol_obj->set_nombre($nombre);

            //se ejecuta la funcion:
            $resultado_registro = $rol_obj->registrar();
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
            if ($resultado_registro["estatus"]) {
                $rol_id = $rol_obj->lastId();

                $roles_permisos_obj->set_rol_id($rol_id["mensaje"]);

                foreach($permisos as $permiso){
                    $roles_permisos_obj->set_permiso_usuario_id($permiso);

                    $resultado_permisos = $roles_permisos_obj->registrar_permisos_roles();

                    if($resultado_permisos["estatus"] == false){
                        echo json_encode($resultado_permisos);
                        exit();
                    }
                }
                echo json_encode($resultado_permisos);
                exit();
            } else {
                echo json_encode($resultado_registro);
                exit();
            }
        }

        elseif ($operacion == "consulta_especifica"){
            //se guardan el id para buscar
            $id_rol = $_POST["id_rol"];

            //se usan el setter correspondientes
            $rol_obj->set_id_rol($id_rol);

            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($rol_obj->consultar_rol());
            // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }

        elseif ($operacion == "consulta_permisos"){
            //se guardan el id para buscar
            $id_rol = $_POST["id_rol"];

            //se usan el setter correspondientes
            $roles_permisos_obj->set_rol_id($id_rol);

            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($roles_permisos_obj->consultar_roles_permisos());
            // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
        elseif ($operacion == "modificar") {
            //se guardan las variables a modificar
            $id_rol = $_POST["id_rol"];
            $nombre = $_POST["nombre"];  
            $permisos = explode(",",$_POST["permisos"]);
            //se usan los setters correspondientes
            $rol_obj->set_id_rol($id_rol);
            $rol_obj->set_nombre($nombre);
            
            //se ejecuta la funcion:
            $result = $rol_obj->editar_rol();
            if (!$result["estatus"]) {
                echo json_encode($result);
                exit();
            }

            $roles_permisos_obj->set_rol_id($id_rol);

            //eliminamos los permisos
            $resultado_permisos = $roles_permisos_obj->eliminar_roles_permisos();
            if (!$resultado_permisos["estatus"]) {
                echo json_encode($result);
                exit();
            }



            // se loss volvemos a poner
            foreach($permisos as $permiso){
                $roles_permisos_obj->set_permiso_usuario_id($permiso);
                $resultado_permisos = $roles_permisos_obj->registrar_permisos_roles($permiso);
                if(!$resultado_permisos["estatus"]){
                    echo json_encode($resultado_permisos);
                    exit();
                }
            }

            echo json_encode($resultado_permisos);
            exit();
        }

        elseif ($operacion == "eliminar") {
            //se guardan el id de la variable a eliminar
            $id_rol = $_POST["id_rol"];

            //se usan el setter correspondientes
            $rol_obj->set_id_rol($id_rol);

            //se ejecuta la funcion:
            echo  json_encode($rol_obj->eliminar_rol());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }elseif ($operacion == "ultimo_id"){
            echo json_encode($rol_obj->lastId());
        }

        exit;//es salida en ingles... No puede faltar
    }
    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"]; //Esto es igual pero para las validaciones
        if ($validar == "nombre"){
            $rol_obj->set_nombre($_POST["nombre"]);
            echo  json_encode($rol_obj->verificar_nombre());
        }
        exit;
    }

    require_once("vista/roles/rol_vista.php");
?>