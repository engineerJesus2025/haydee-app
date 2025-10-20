<?php 
	if(!(isset($_SESSION["usuario"]))){
		if (isset($_COOKIE['token_recuerdame']) && isset($_COOKIE['correo_usuario'])){
			require_once "modelo/usuario_modelo.php";
			$usuario_obj = new Usuario();

			$token_recuerdame = $_COOKIE['token_recuerdame'];
        	$correo_usuario = $_COOKIE['correo_usuario'];

        	$usuario_obj->set_correo($correo_usuario);

        	$resultado = $usuario_obj->realizar_consulta('validar_token_recuerdame');

        	if ($resultado && $resultado['token_recuerdame'] && strtotime($resultado['duracion_token_recuerdame']) > time()) {
	            if (password_verify($token_recuerdame, $resultado['token_recuerdame'])) {
	                // Token válido, crear sesión
	                require_once "modelo/notificaciones_modelo.php";
	                require_once "modelo/roles_permisos_modelo.php";
	                require_once "modelo/anio_fiscal_modelo.php";

	                $notificaciones_obj = new Notificaciones();
	                $roles_permisos_obj = new Roles_permisos();

	                if (!(session_status() == PHP_SESSION_ACTIVE)) {
              			session_start();
        			}

	                $_SESSION["id_usuario"] = $resultado["id_usuario"];
	                $_SESSION["usuario"] = $resultado["correo"];
	                $_SESSION["nombre_completo"] = $resultado["nombre_usuario"];
	                $_SESSION["rol"] = $resultado["nombre_rol"];

	                $roles_permisos_obj->set_rol_id($resultado["id_rol"]);

	                $_SESSION["permisos"] = $roles_permisos_obj->realizar_consulta('consultar_permisos_por_usuario');

	                $notificaciones_obj->set_usuario_id($resultado["id_usuario"]);

	                $_SESSION["notificaciones"] = $notificaciones_obj->realizar_consulta('consultar_notificaciones_usuario');

	                $anio_fiscal_obj = new Anio_fiscal();
                	$result_anio = $anio_fiscal_obj->realizar_consulta("verificar_anio_fiscal");
	            }
	        }
	        else{
	        	$resultado_token = $usuario_obj->realizar_consulta("eliminar_token_recuerdame");
	        	header("Location:?pagina=login_controlador.php&accion=inicio");
	        }
		}
		else{
			header("Location:?pagina=login_controlador.php&accion=inicio");
		}		
	}
?>