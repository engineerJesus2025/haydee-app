<?php
    require_once "modelo/usuario_modelo.php";
    require_once "modelo/bitacora_modelo.php";
    require_once "modelo/notificaciones_modelo.php";
    require_once "modelo/roles_permisos_modelo.php";
    require_once "modelo/caja_chica_modelo.php";
    require_once "modelo/anio_fiscal_modelo.php";
    require_once "ayuda/ayuda.php";

    require 'vendor/autoload.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    $fecha_actual = date("Y-m-d H:i:s");

    if (isset($_POST["operacion"])) {
        $operacion = $_POST["operacion"];
        if ($operacion == "entrar") {
            if (session_status() == PHP_SESSION_ACTIVE) {
              session_destroy();
            }
            $usuario_obj = new Usuario();

            //validamos el reCAPTCHA
            $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

            if (empty($recaptchaResponse)) {
                echo json_encode(["estatus"=>false,"mensaje"=>"Por favor, completa el reCAPTCHA"]);                
                exit;
            }

            $resultadoRecaptcha = $usuario_obj->verificarRecaptcha($recaptchaResponse);

            if (!$resultadoRecaptcha['success']) {
                $errors = $resultadoRecaptcha['error-codes'];
                $mensaje_error = (in_array('timeout-or-duplicate', $errors))?"reCAPTCHA expirado, por favor inténtalo de nuevo.":"Ha ocurrido un error al tratar de validar el reCAPTCHA";

                echo json_encode(["estatus"=>false,"mensaje"=>$mensaje_error,"err"=>$resultadoRecaptcha['error-codes']]);                
                exit;
            }

            //Validamos el usuario

            $usuario_obj->set_correo($_POST["usuario"]);
            $contrasenia = $_POST["contra"];

            $resultado = $usuario_obj->realizar_consulta('validar_usuario');            

            if ($resultado) {
                if (!password_verify($contrasenia, $resultado["contrasenia"])) {
                    echo json_encode(["estatus"=>false,"mensaje"=>"Usuario o Contraseña incorrectos"]);
                    exit();
                }

                //Mantener Sesion
                $mantener_sesion = $_POST["mantener_sesion"];                
                if ($mantener_sesion === "true") {
                    $token = bin2hex(random_bytes(32));
                    $expiracion = time() + (30 * 24 * 60 * 60); // 30 días
                    
                    // Guardar token en los NUEVOS campos para "Recuérdame"
                    $usuario_obj->set_token_recuerdame($token);
                    $usuario_obj->set_duracion_token_recuerdame($expiracion);

                    $resultado_token = $usuario_obj->realizar_consulta("registrar_token_recuerdame");

                    if ($resultado_token["estatus"]) {
                        setcookie('token_recuerdame', $token, $expiracion, '/', '', true, true);
                        setcookie('correo_usuario', $_POST["usuario"], $expiracion, '/', '', true, true);
                    }
                }
                else {
                    // Si no marcó "Recuérdame", eliminar cualquier token existente
                    $resultado_token = $usuario_obj->realizar_consulta("eliminar_token_recuerdame");
                    if (!$resultado_token["estatus"]) {
                        echo json_encode(["estatus"=>false,"mensaje"=>$resultado_token["mensaje"]]);
                    }
                }
                //Notificaciones y datos de sesion

                $notificaciones_obj = new Notificaciones();
                $roles_permisos_obj = new Roles_permisos();

                session_start();

                $_SESSION["id_usuario"] = $resultado["id_usuario"];
                $_SESSION["usuario"] = $resultado["correo"];
                $_SESSION["nombre_completo"] = $resultado["nombre_usuario"];
                $_SESSION["rol"] = $resultado["nombre_rol"];

                $roles_permisos_obj->set_rol_id($resultado["id_rol"]);

                $_SESSION["permisos"] = $roles_permisos_obj->realizar_consulta('consultar_permisos_por_usuario');

                $notificaciones_obj->set_usuario_id($resultado["id_usuario"]);

                $_SESSION["notificaciones"] = $notificaciones_obj->realizar_consulta('consultar_notificaciones_usuario');

                $usuario_obj->registrar_bitacora(INICIAR_SESION,GESTIONAR_USUARIOS,"NINGUNO");
                // $caja_obj = new Caja_chica();
                // $caja_obj->realizar_consulta('verificar_caja_mes');
                
                $anio_fiscal_obj = new Anio_fiscal();
                $result_anio = $anio_fiscal_obj->realizar_consulta("verificar_anio_fiscal");

                echo json_encode(["estatus"=>true,"mensaje"=>"OK"]);

                exit();
            }
            else{
                echo json_encode(["estatus"=>false,"mensaje"=>"Usuario o Contraseña incorrectos"]);
                exit();
            }
        }
        if ($operacion == "enviar_notificacion") {

            $correo_recuperar = $_POST["correo_recuperar"];
            $token = $_POST["token"];

            $usuario_obj = new Usuario();

            $usuario_obj->set_correo($correo_recuperar);

            $usuario = $usuario_obj->realizar_consulta('validar_usuario');

            if ($usuario) {
                // $duracion_token = date("Y-m-d H:i:s");

                $usuario_obj->set_token($token);
                $usuario_obj->set_duracion_token($fecha_actual);

                $result = $usuario_obj->realizar_consulta('registrar_token');

                if (!$result) {
                    echo json_encode(["estatus"=>false,"mensaje"=>"Ha ocurrido un error al tratar de guardar el token de recuperacion"]);
                }
                                
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

                    $mail->Subject = 'Recuperar contraseña';
                    $mail->AltBody = 'Copie este enlace para recuperar contraseña' . $url;

                    // $mail->Body = $mensaje_html;

                    $mail->isHTML(true);

                    $mensaje = file_get_contents("vista/login/correo_recuperacion.html");

                    $mensaje = str_replace('%usuario%', $usuario['nombre_usuario'], $mensaje);
                    $mensaje = str_replace('%url%', $url, $mensaje);

                    $mail->MsgHTML($mensaje);

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
        
        $usuario_obj = new Usuario();
        $usuario_obj->set_correo($_SESSION["usuario"]);

        $resultado_token = $usuario_obj->realizar_consulta("eliminar_token_recuerdame");


        $usuario_obj->registrar_bitacora(CERRAR_SESION,GESTIONAR_USUARIOS,"NINGUNO");

        session_destroy();
        header("Location:?pagina=login_controlador.php&accion=inicio");
    }

    if ($accion == "recuperar_contrasenia") {
        $token = $_GET["t"];
        $duracion_token = date("Y-m-d H:i:s");

        $usuario_obj = new Usuario();

        $usuario_obj->set_duracion_token($duracion_token);
        $usuario_obj->set_token($token);

        $resultado = $usuario_obj->realizar_consulta('validar_token');        

        if ($resultado) {
            $usuario_obj->realizar_consulta('eliminar_token');
        }

        require_once "vista/login/login_recuperar.php";
    }
    if ($accion == "guardar_contrasenia") {
        $usuario_obj = new Usuario();

        $contrasenia = $_POST["contra"];
        $correo = $_POST["correo"];

        $usuario_obj->set_correo($correo);
        $usuario_obj->set_contra($contrasenia);

        $resultado = $usuario_obj->realizar_consulta('cambiar_contrasenia');
        
        if ($resultado) {
            header("Location:?pagina=login_controlador.php&accion=inicio&r=1");
        }
        else{
            header("Location:?pagina=login_controlador.php&accion=inicio&r=0");
        }
    }
?>
