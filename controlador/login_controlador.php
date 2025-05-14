<?php
    require_once "modelo/usuario_modelo.php";
    require_once "modelo/bitacora_modelo.php";
    require_once "modelo/notificaciones_modelo.php";
    require_once "modelo/permisos_usuarios_modelo.php";
    require_once "ayuda/ayuda.php";
    
    if (isset($_POST["operacion"])) {
        $operacion = $_POST["operacion"];
        if ($operacion == "entrar") {
            if (session_status() == PHP_SESSION_ACTIVE) {
              session_destroy();
            }
            $usuario_obj = new Usuario();

            $usuario_obj->set_correo($_POST["usuario"]);
            $contrasenia = $_POST["contra"];

            $resultado = $usuario_obj->validar_usuario();

            if ($resultado) {
                
                if (!password_verify($contrasenia, $resultado["contrasenia"])) {
                    echo json_encode(["estatus"=>false,"mensaje"=>"Contraseña incorrecta"]);
                    exit();
                }

                // $notificaciones_obj = new Notificaciones();
                $permisos_usuarios_obj = new Permisos_usuarios();

                session_start();
                $_SESSION["id_usuario"] = $resultado["id_usuario"];
                $_SESSION["usuario"] = $resultado["correo"];
                $_SESSION["nombre_completo"] = $resultado["nombre_usuario"];
                $_SESSION["rol"] = $resultado["nombre_rol"];
                $_SESSION["permisos"] = $permisos_usuarios_obj->consultar_permisos_por_usuario($resultado["id_rol"]);

                //$_SESSION["notificaciones"] = $notificaciones_obj->consultar_notificaciones($resultado["id_usuario"]);

                echo json_encode(["estatus"=>true,"mensaje"=>"OK"]);
                exit();
            }else{
                echo json_encode(["estatus"=>false,"mensaje"=>"Usuario no encontrado"]);
                exit();
            }
        }
    }
    
    if($accion == "inicio"){
        require_once "vista/login/login_vista.php";
        unset($_SESSION["mensaje"]);
        session_destroy();
    }
    if ($accion == "cerrar") {
        if (!(session_status() == PHP_SESSION_ACTIVE)) {
              session_start();
            }
        
        $usuario = new Usuario();
        $usuario->registrar_bitacora(CERRAR_SESION,GESTIONAR_USUARIOS,"NINGUNO");
        session_destroy();
        header("Location:?pagina=login_controlador.php&accion=inicio");
    }

?>