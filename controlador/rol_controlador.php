<?php
    
    require_once("modelo/rol_modelo.php");
    require_once "ayuda/ayuda.php";
    require_once "vista/componentes/sesion.php";
    
    $rol = new Rol();

    if (isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){
            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($rol->consultar());
            // la hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
        //Despues de cada echo se regresa al javascript como respuesta en json

        elseif ($operacion == "registrar") {
            //se guardan las variables a registrar
            $nombre = $_POST["nombre"];  
            $permisos = explode(",",$_POST["permisos"]);

            //se usan los setters correspondientes            
            $rol->set_nombre($nombre);

            //se ejecuta la funcion:
            $resultado_registro = $rol->registrar();
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
            if ($resultado_registro["estatus"]) {
                $rol_id = $rol->lastId();
                // $rol->registrar_bitacora(REGISTRAR, GESTIONAR_ROLES);                
                foreach($permisos as $permiso){
                    $resultado_permisos = $rol->registrar_permisos_roles($rol_id["mensaje"], $permiso);
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
            $rol->set_id_rol($id_rol);

            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($rol->consultar_rol());
            // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
        elseif ($operacion == "consulta_permisos"){
            //se guardan el id para buscar
            $id_rol = $_POST["id_rol"];

            //se usan el setter correspondientes
            $rol->set_id_rol($id_rol);

            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($rol->consultar_roles_permisos());
            // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
        elseif ($operacion == "modificar") {
            //se guardan las variables a modificar
            $id_rol = $_POST["id_rol"];
            $nombre = $_POST["nombre"];  
            $permisos = explode(",",$_POST["permisos"]);
            //se usan los setters correspondientes
            $rol->set_id_rol($id_rol);
            $rol->set_nombre($nombre);
            
            //se ejecuta la funcion:
            $result = $rol->editar_rol();
            if (!$result["estatus"]) {
                echo json_encode($result);
                exit();
            }
            $resultado_permisos = $rol->eliminar_roles_permisos();
            if (!$resultado_permisos["estatus"]) {
                echo json_encode($result);
                exit();
            }
            foreach($permisos as $permiso){
                $resultado_permisos = $rol->registrar_permisos_roles($id_rol, $permiso);
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
            $rol->set_id_rol($id_rol);

            //se ejecuta la funcion:
            echo  json_encode($rol->eliminar_rol());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }elseif ($operacion == "ultimo_id"){
            echo json_encode($rol->lastId());
        }

        exit;//es salida en ingles... No puede faltar
    }
    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"]; //Esto es igual pero para las validaciones
        if ($validar == "nombre"){
            $rol->set_nombre($_POST["nombre"]);
            echo  json_encode($rol->verificar_nombre());
        }
        exit;
    }

    if ($accion == "inicio") {
        
        $registros = $rol->consultar();
        $registros_modulos = $rol->consultar_modulos();
        $registros_permisos_usuarios = $rol->consultar_permisos_usuarios();
        //$registros_roles_permisos = $rol->consultar_roles_permisos();
        require_once("vista/roles/rol_vista.php");
        unset($_SESSION["mensaje"]);
        unset($_SESSION["estado_consulta"]);
    }

    if ($accion == "guardar") {
    
        
        $rol->set_nombre($_POST["nombre"]);

        $nombre_duplicado = $rol->verificar_nombre();
        
        if($nombre_duplicado){
            $_SESSION["mensaje"] = "Ya existe un rol registrado con este nombre";
            header("Location:?pagina=rol_controlador.php&accion=inicio");

        }

        $result = $rol->registrar();
        $rol_id = $rol->get_tabla_id();
        // $rol->registrar_bitacora(REGISTRAR, GESTIONAR_ROLES);

        
        foreach($_POST["permisos"] as $permiso){
            $resultado_permisos = $rol->registrar_permisos_roles($rol_id["last_id"], $permiso);
            if($resultado_permisos["estatus"] == false){
                $_SESSION["mensaje"] = $resultado_permisos["mensaje"];
                header("Location:?pagina=rol_controlador.php&accion=inicio");
                exit();
            }
        }


        if($result["estatus"] == true){
            $_SESSION["estado_consulta"] = $result["mensaje"];
            header("Location:?pagina=rol_controlador.php&accion=inicio");
        }
        else{
            $_SESSION["mensaje"] = $result["mensaje"];
            header("Location:?pagina=rol_controlador.php&accion=inicio");
        }
    }

    if($accion == "editar"){
        
        $rol->set_id_rol($_POST["id_rol"]);
        $rol->set_nombre($_POST["nombre"]);
        
        $result=$rol->editar_rol();

        $resultado_permisos = $rol->eliminar_roles_permisos();
        
        if($resultado_permisos["estatus"] == false){
            $_SESSION["mensaje"] = $resultado_permisos["mensaje"];
            header("Location:?pagina=rol_controlador.php&accion=inicio");
            exit();
        }
        foreach($_POST["permisos"] as $permiso){
            $resultado_permisos = $rol->registrar_permisos_roles($_POST["id_rol"], "sadad");
            if($resultado_permisos["estatus"] == false){
            $_SESSION["mensaje"] = $resultado_permisos["mensaje"];
                header("Location:?pagina=rol_controlador.php&accion=inicio");
                exit();
            }
        }   

        if($result["estatus"] == true){
            $_SESSION["estado_consulta"] = $result["mensaje"];
            header("Location:?pagina=rol_controlador.php&accion=inicio");
        }
        else{
            $_SESSION["mensaje"] = $result["mensaje"];
            header("Location:?pagina=rol_controlador.php&accion=inicio");
        }
    }

    if($accion == "eliminar"){
        
        $rol->set_id_rol($_GET["id_rol"]);
        $result=$rol->eliminar_rol();

        if($result["estatus"] == true){
            $_SESSION["estado_consulta"] = $result["mensaje"];
            header("Location:?pagina=rol_controlador.php&accion=inicio");
        }
        else{
            $_SESSION["mensaje"] = $result["mensaje"];
            header("Location:?pagina=rol_controlador.php&accion=inicio");
        }
    }
?>