<?php
    require_once "modelo/usuario_modelo.php";
    require_once "modelo/bitacora_modelo.php";
    require_once "modelo/notificaciones_modelo.php";
    require_once "ayuda/ayuda.php";
    
    if($accion == "inicio"){
        require_once "vista/login/login_vista.php";
        unset($_SESSION["mensaje"]);
        session_destroy();
    }
    if ($accion == "entrar") {
        if (session_status() == PHP_SESSION_ACTIVE) {
          session_destroy();
        }          
        $usuario = new Usuario();
        $notificaciones = new Notificaciones();

        $usuario->set_correo($_POST["usuario"]);
        $contrasenia = $_POST["contra"];

        $resultado = $usuario->validar_usuario();
        if ($resultado) {
            //var_dump(password_verify($contrasenia, $resultado["contrasenia"]));
            // if (!password_verify($contrasenia, $resultado["contrasenia"])) {
            //     $_SESSION["mensaje"] = " Usuario o contraseña invalido";
            //     header("Location:?pagina=login_controlador.php&accion=inicio");
            //     exit();
            // }

            session_start();
            $_SESSION["id_usuario"] = $resultado["id_usuario"];
            $_SESSION["usuario"] = $resultado["correo"];
            $_SESSION["nombre_completo"] = $resultado["nombre_usuario"];
            $_SESSION["rol"] = $resultado["nombre_rol"];
            $_SESSION["permisos"] = $usuario->consultar_permisos_por_usuario($resultado["id_rol"]);


            //$_SESSION["notificaciones"] = $notificaciones->consultar_notificaciones($resultado["id_usuario"]);

            Ayuda::tiene_permiso("tes","test");
            header("Location:?pagina=inicio_controlador.php&accion=inicio");
        }else{
            $_SESSION["mensaje"] = " Usuario o contraseña invalido";
            header("Location:?pagina=login_controlador.php&accion=inicio");
        }
    }
    if ($accion == "cerrar") {
        session_start();
        $usuario = new Usuario();
        $usuario->registrar_bitacora(CERRAR_SESION,GESTIONAR_USUARIOS);
        session_destroy();
        header("Location:?pagina=login_controlador.php&accion=inicio");
    }

?>