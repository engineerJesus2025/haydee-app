<?php
    use haydee\ayuda\Sesiones;
    Sesiones::verificarSesion();

    use haydee\modelo\Rol;
    use haydee\modelo\Usuario;
    use haydee\modelo\Notificaciones;

    $rol_obj = new Rol();
    $roles = $rol_obj->realizar_consulta('consultar_roles'); 

    if (isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consultar_perfil_usuario") {
            $usuario_obj = new Usuario();

            $id_usuario = $_SESSION["id_usuario"];
            
            $usuario_obj->set_id_usuario($id_usuario);            
            echo  json_encode($usuario_obj->realizar_consulta('consultar_perfil_usuario'));
        }
        elseif ($operacion == "consultar_notificaciones_usuario") {
            $notificaciones_obj = new Notificaciones();

            $id_usuario = $_SESSION["id_usuario"];
            
            $notificaciones_obj->set_usuario_id($id_usuario);
            echo  json_encode($notificaciones_obj->realizar_consulta('consultar_notificaciones_usuario'));
        }
        elseif ($operacion == "editar_perfil") {
            $usuario_obj = new Usuario();

            $id_usuario = $_SESSION["id_usuario"];
            $apellido = $_POST["apellido"];
            $nombre = $_POST["nombre"];  
            $correo = $_POST["correo"];  

            $usuario_obj->set_id_usuario($id_usuario);
            $usuario_obj->set_apellido($apellido);
            $usuario_obj->set_nombre($nombre);
            $usuario_obj->set_correo($correo);

            $resultado = $usuario_obj->realizar_consulta("editar_perfil");

            if ($resultado["estatus"]) {
                $_SESSION["nombre_completo"] = $nombre;
            }

            echo  json_encode($usuario_obj->realizar_consulta('editar_perfil'));
        }
        elseif ($operacion == "cambiar_contrasenia") {
            $usuario_obj = new Usuario();

            $contrasenia = $_POST["contra"];
            $correo = $_POST["correo"];

            $usuario_obj->set_correo($correo);
            $usuario_obj->set_contra($contrasenia);

            echo  json_encode($usuario_obj->realizar_consulta('cambiar_contrasenia'));        
        }
        exit;
    }
    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"];

        if ($validar == "correo"){
            $usuario_obj = new Usuario();

            $usuario_obj->set_correo($_POST["correo"]);
            echo  json_encode($usuario_obj->realizar_consulta('verificar_correo'));
        }
        elseif ($validar == "contra"){
            $usuario_obj = new Usuario();

            $contra = $_POST["contra"];

            $usuario_obj->set_id_usuario($_POST["id_usuario"]);

            $usuario_validar = $usuario_obj->realizar_consulta('consultar_usuario');

            echo json_encode(password_verify($contra, $usuario_validar["contrasenia"]));
        }
        elseif ($validar == "contra_perfil"){
            $usuario_obj = new Usuario();

            $contra = $_POST["contra"];

            $usuario_obj->set_id_usuario($_SESSION["id_usuario"]);

            $usuario_validar = $usuario_obj->realizar_consulta('consultar_usuario');

            echo json_encode(password_verify($contra, $usuario_validar["contrasenia"]));
        }
        elseif ($validar == "validar_clave_foranea") {
            $usuario_obj = new Usuario();

            $tabla = $_POST["tabla"];
            $nombre_clave = $_POST["nombre_clave"];
            $valor = $_POST["valor"];
            
            $resultado = $usuario_obj->realizar_consulta('validar_clave_foranea',["tabla"=>$tabla,"nombre_clave"=>$nombre_clave,"valor"=>$valor]);
            
            echo json_encode($resultado);
        }
        exit;
    }
    if ($accion == "perfil") {
        $usuario_obj = new Usuario();

        $id_usuario = $_SESSION["id_usuario"];

        $usuario_obj->set_id_usuario($id_usuario);

        $usuario = $usuario_obj->realizar_consulta('consultar_usuario');        
        require_once "vista/usuarios/usuario_perfil.php";
    }
    if ($accion == "inicio") {
        require_once "vista/usuarios/usuario_vista.php";
    }
?>