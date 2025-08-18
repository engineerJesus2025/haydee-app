<?php
    require_once "modelo/usuario_modelo.php";
    require_once "modelo/bitacora_modelo.php";
    require_once "modelo/notificaciones_modelo.php";
    require_once "modelo/permisos_usuarios_modelo.php";
    require_once "modelo/caja_chica_modelo.php";
    require_once "modelo/anio_fiscal_modelo.php";
    require_once "ayuda/ayuda.php";

    require 'vendor/autoload.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
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

                $notificaciones_obj = new Notificaciones();
                $permisos_usuarios_obj = new Permisos_usuarios();

                session_start();
                $_SESSION["id_usuario"] = $resultado["id_usuario"];
                $_SESSION["usuario"] = $resultado["correo"];
                $_SESSION["nombre_completo"] = $resultado["nombre_usuario"];
                $_SESSION["rol"] = $resultado["nombre_rol"];
                $_SESSION["permisos"] = $permisos_usuarios_obj->consultar_permisos_por_usuario($resultado["id_rol"]);

                $_SESSION["notificaciones"] = $notificaciones_obj->consultar_notificaciones($resultado["id_usuario"]);

                echo json_encode(["estatus"=>true,"mensaje"=>"OK"]);

                $caja_obj = new Caja_chica();
                $caja_obj->verificar_caja_mes();
                
                $anio_fiscal_obj = new Anio_fiscal();
                $anio_fiscal_obj->verificar_anio_fiscal();

                exit();
            }else{
                echo json_encode(["estatus"=>false,"mensaje"=>"Usuario no encontrado"]);
                exit();
            }
        }
        if ($operacion == "enviar_notificacion") {

            $correo_recuperar = $_POST["correo_recuperar"];
            $token = $_POST["token"];

            $usuario_obj = new Usuario();

            $usuario_obj->set_correo($correo_recuperar);

            $usuario = $usuario_obj->validar_usuario();

            if ($usuario) {
                $duracion_token = date("Y-m-d H:i:s");

                $usuario_obj->set_token($token);
                $usuario_obj->set_duracion_token($duracion_token);

                $result = $usuario_obj->registrar_token();
                                
                // De momento no
                $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

                $pos = strpos($url, '?');

                if ($pos !== false) {
                  $url = substr($url, 0, $pos);
                }

                $url .= "?pagina=login_controlador.php&accion=recuperar_contrasenia&t=" . $token;

                $mensaje_html = "<h3>Saludos " . $usuario['nombre_usuario'] . "</h3><p>Abre este enlace para ir al formulario de cambio de contraseña:</p><a href='$url'>Cambiar Contraseña Haydee</a>";

                

                $mail = new PHPMailer(true);

                try{
                    $mail->CharSet = 'UTF-8';
                    $mail->Encoding = 'base64';

                    $mail->isSMTP();
                    $mail->Host = 'smtp-condominioshaydee.alwaysdata.net';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'condominioshaydee@alwaysdata.net';
                    $mail->Password = 'Haydee.2025';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('condominiohaydee@alwaysdata.net','Condominios Haydee');
                    $mail->addAddress($usuario['correo'],$usuario['nombre_usuario'] . " (" . $usuario["nombre_rol"] . ")");

                    $mail->isHTML(true);
                    $mail->Subject = 'Recuperar contraseña';
                    $mail->Body = $mensaje_html;
                    $mail->AltBody = 'Copie este enlace para recuperar contraseña' . $url;

                    $mail->send();

                    echo json_encode(["estatus"=>true,"mensaje"=>"Exito "]);
                } catch(Exception $e){
                    echo json_encode(["estatus"=>false,"mensaje"=>"Error" . $e->getMessage()]);
                    exit();
                }

            }
            else{
                echo json_encode(["estatus"=>false,"mensaje"=>"Error"]);
            }
            exit();
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

    if ($accion == "recuperar_contrasenia") {
        $token = $_GET["t"];
        $duracion_token = date("Y-m-d H:i:s");

        $usuario_obj = new Usuario();

        $usuario_obj->set_duracion_token($duracion_token);
        $usuario_obj->set_token($token);

        $resultado = $usuario_obj->validar_token();
        if ($resultado) {
            $usuario_obj->eliminar_token();
        }

        require_once "vista/login/login_recuperar.php";
    }
    if ($accion == "guardar_contrasenia") {
        $contrasenia = $_POST["contra"];
        $correo = $_POST["correo"];

        $usuario_obj = new Usuario();
        $usuario_obj->set_correo($correo);
        $usuario_obj->set_contra($contrasenia);

        $resultado = $usuario_obj->cambiar_contrasenia();
        var_dump($resultado);
        if ($resultado) {
            header("Location:?pagina=login_controlador.php&accion=inicio&r=1");
        }
        else{
            header("Location:?pagina=login_controlador.php&accion=inicio&r=0");
        }
    }



?>
