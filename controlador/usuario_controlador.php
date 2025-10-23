<?php
    require_once "vista/componentes/sesion.php";
    require_once "modelo/usuario_modelo.php";
    require_once 'modelo/rol_modelo.php';

    $rol_obj = new Rol(); 
    $roles = $rol_obj->realizar_consulta('consultar_roles'); 

    if (isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){
            $usuario_obj = new Usuario();

            $usuario_obj->registrar_bitacora(CONSULTAR, GESTIONAR_USUARIOS, "TODOS LOS USUARIOS");

            echo  json_encode($usuario_obj->realizar_consulta('consultar'));            
        }        
        elseif ($operacion == "registrar") {
            $usuario_obj = new Usuario();

            $apellido = $_POST["apellido"];
            $nombre = $_POST["nombre"];  
            $correo = $_POST["correo"];  
            $contra = $_POST["contra"];
            $rol = $_POST["rol"];              

            $usuario_obj->set_apellido($apellido);
            $usuario_obj->set_nombre($nombre);
            $usuario_obj->set_correo($correo);
            $usuario_obj->set_contra($contra);
            $usuario_obj->set_rol_id($rol);

            $resultado = $usuario_obj->realizar_consulta("registrar");

            if ($resultado["estatus"]) {
                $usuario_obj->registrar_bitacora(REGISTRAR, GESTIONAR_USUARIOS, $nombre . " " . $apellido);
            }
            
            echo  json_encode($resultado);
        }
        elseif ($operacion == "consulta_especifica"){
            $usuario_obj = new Usuario();

            $id_usuario = $_POST["id_usuario"];

            $usuario_obj->set_id_usuario($id_usuario);

            echo  json_encode($usuario_obj->realizar_consulta('consultar_usuario'));
        }
        elseif ($operacion == "editar_usuario") {
            $usuario_obj = new Usuario();

            $id_usuario = $_POST["id_usuario"];
            $apellido = $_POST["apellido"];
            $nombre = $_POST["nombre"];  
            $correo = $_POST["correo"];  
            $contra = $_POST["contra"];
            $rol = $_POST["rol"];

            $usuario_obj->set_id_usuario($id_usuario);
            $usuario_obj->set_apellido($apellido);
            $usuario_obj->set_nombre($nombre);
            $usuario_obj->set_correo($correo);
            $usuario_obj->set_contra($contra);
            $usuario_obj->set_rol_id($rol);        

            $resultado = $usuario_obj->realizar_consulta("editar_usuario");

            if ($resultado["estatus"]) {
                $usuario_obj->registrar_bitacora(MODIFICAR, GESTIONAR_USUARIOS, $nombre . " " . $apellido);
            }
            
            echo  json_encode($resultado);
        }

        elseif ($operacion == "eliminar") {
            $usuario_obj = new Usuario();

            $id_usuario = $_POST["id_usuario"];

            $usuario_obj->set_id_usuario($id_usuario);

            $usuario_alterado = $usuario_obj->realizar_consulta('consultar_usuario');

            $resultado = $usuario_obj->realizar_consulta("eliminar_usuario");

            if ($resultado["estatus"]){
                if ($usuario_alterado) {
                    $usuario_obj->registrar_bitacora(ELIMINAR, GESTIONAR_USUARIOS, $usuario_alterado["nombre_usuario"] . " " . $usuario_alterado["apellido"]);
                }
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta para la bitácora"];
                }
            }            

            echo  json_encode($resultado);
        }
        elseif ($operacion == "ultimo_id"){
            $usuario_obj = new Usuario();
            echo json_encode($usuario_obj->realizar_consulta('lastId'));
        }
        elseif ($operacion == "consultar_perfil_usuario") {
            $usuario_obj = new Usuario();

            $id_usuario = $_SESSION["id_usuario"];
            
            $usuario_obj->set_id_usuario($id_usuario);            
            echo  json_encode($usuario_obj->realizar_consulta('consultar_perfil_usuario'));
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