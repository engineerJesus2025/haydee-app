<?php
    require_once "vista/componentes/sesion.php";
    require_once("modelo/rol_modelo.php");
    require_once "ayuda/ayuda.php";
    require_once "modelo/permisos_usuarios_modelo.php";
    require_once "modelo/modulos_modelo.php";
    require_once "modelo/roles_permisos_modelo.php";
    require_once "vista/componentes/sesion.php";

    $modulo_obj = new Modulos();
    $registros_modulos = $modulo_obj->realizar_consulta('consultar');

    $permisos_usuarios_obj = new Permisos_usuarios();
    $registros_permisos_usuarios = $permisos_usuarios_obj->realizar_consulta('consultar');

    if (isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){
            $rol_obj = new Rol();

            $rol_obj->registrar_bitacora(CONSULTAR, GESTIONAR_ROLES, "Todos los roles de usuario");

            echo  json_encode($rol_obj->realizar_consulta('consultar'));            
        }

        elseif ($operacion == "registrar_rol") {
            $rol_obj = new Rol();

            $nombre = $_POST["nombre"];  
            $permisos = explode(",",$_POST["permisos"]);

            $rol_obj->set_nombre($nombre);
 
            $resultado_registro = $rol_obj->realizar_consulta('registrar');

            if ($resultado_registro["estatus"]) {
                $roles_permisos_obj = new Roles_permisos();

                $rol_id = $rol_obj->realizar_consulta('lastId');

                $roles_permisos_obj->set_rol_id($rol_id["last_id"]);

                foreach($permisos as $permiso){
                    $roles_permisos_obj->set_permiso_usuario_id($permiso);

                    $resultado_permisos = $roles_permisos_obj->realizar_consulta('registrar_permisos_roles');

                    if($resultado_permisos["estatus"] == false){
                        echo json_encode($resultado_permisos);
                        exit();
                    }
                }

                $rol_obj->registrar_bitacora(REGISTRAR, GESTIONAR_ROLES, "Rol '" . $nombre . "' guardado");

                echo json_encode($resultado_permisos);
                exit();
            } 
            else {
                echo json_encode($resultado_registro);
                exit();
            }
        }

        elseif ($operacion == "consulta_especifica"){
            $rol_obj = new Rol();

            $id_rol = $_POST["id_rol"];

            $rol_obj->set_id_rol($id_rol);

            echo  json_encode($rol_obj->realizar_consulta('consultar_rol'));
        }

        elseif ($operacion == "consulta_permisos"){
            $roles_permisos_obj = new Roles_permisos();

            $id_rol = $_POST["id_rol"];

            $roles_permisos_obj->set_rol_id($id_rol);

            echo  json_encode($roles_permisos_obj->realizar_consulta('consultar_roles_permisos'));
        }
        elseif ($operacion == "modificar") {
            $rol_obj = new Rol();

            $id_rol = $_POST["id_rol"];
            $nombre = $_POST["nombre"];  
            $permisos = explode(",",$_POST["permisos"]);
            
            $rol_obj->set_id_rol($id_rol);
            $rol_obj->set_nombre($nombre);
            
            $result = $rol_obj->realizar_consulta('editar_rol');
            if (!$result["estatus"]) {
                echo json_encode($result);
                exit();
            }

            $roles_permisos_obj = new Roles_permisos();

            $roles_permisos_obj->set_rol_id($id_rol);

            $resultado_permisos = $roles_permisos_obj->realizar_consulta('eliminar_roles_permisos');
            if (!$resultado_permisos["estatus"]) {
                echo json_encode($result);
                exit();
            }

            foreach($permisos as $permiso){
                $roles_permisos_obj->set_permiso_usuario_id($permiso);
                $resultado_permisos = $roles_permisos_obj->realizar_consulta('registrar_permisos_roles');
                if(!$resultado_permisos["estatus"]){
                    echo json_encode($resultado_permisos);
                    exit();
                }
            }

            $rol_obj->registrar_bitacora(MODIFICAR, GESTIONAR_ROLES, "Rol '" . $nombre . "' cambiado");

            echo json_encode($resultado_permisos);
            exit();
        }

        elseif ($operacion == "eliminar") {
            $rol_obj = new Rol();

            $id_rol = $_POST["id_rol"];

            $rol_obj->set_id_rol($id_rol);

            $rol_alterado = $rol_obj->realizar_consulta('consultar_rol');

            $resultado = $rol_obj->realizar_consulta("eliminar_rol");

            if ($resultado["estatus"]){
                if ($rol_alterado) {
                    $rol_obj->registrar_bitacora(ELIMINAR, GESTIONAR_ROLES, "Rol '" . $rol_alterado["nombre"] . "' eliminado");
                }
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta para la bitácora"];
                }
            }            

            echo  json_encode($resultado);
        }
        elseif ($operacion == "ultimo_id"){
            $rol_obj = new Rol();
            echo json_encode($rol_obj->realizar_consulta('lastId'));
        }

        exit;
    }
    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"];

        if ($validar == "nombre"){
            $rol_obj = new Rol();

            $rol_obj->set_nombre($_POST["nombre"]);
            echo  json_encode($rol_obj->realizar_consulta('verificar_nombre'));
        }
        exit;
    }

    require_once("vista/roles/rol_vista.php");
?>