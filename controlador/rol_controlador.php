<?php
    
    require_once("modelo/rol_modelo.php");
    require_once "ayuda/ayuda.php";
    require_once "vista/componentes/sesion.php";
    
    $rol = new Rol();

    if ($accion == "inicio") {
        
        $registros = $rol->consultar();
        $registros_modulos = $rol->consultar_modulos();
        $registros_permisos_usuarios = $rol->consultar_permisos_usuarios();
        $registros_roles_permisos = $rol->consultar_roles_permisos();
        require_once("vista/roles/rol_inicio_vista.php");
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
        
        //$result=$rol->editar_rol();

        $resultado_permisos = $rol->eliminar_roles_permisos($_POST["id_rol"]);
        
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