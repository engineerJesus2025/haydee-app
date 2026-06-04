<?php
namespace haydee\servicios;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use haydee\servicios\Criptografia;
use haydee\modelo\Usuario;
use haydee\enums\TipoToken;

class Recuperacion
{
    private const LONGITUD_TOKEN_BYTES = 32;
    private const TIEMPO_EXPIRACION = '+1 hour';
    private const OTP_LONGITUD = 6;
    private const OTP_TIEMPO_EXPIRACION_MINUTOS = 15;
    
    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Genera un token de recuperación y envía el correo.
     */
    public function enviarCorreoRecuperacion($correo)
    {
        $this->usuarioModel->set_correo($correo);
        $resultado = $this->usuarioModel->realizar_consulta('existe_correo');
        if (!$resultado['estatus']) {
            return ['estatus' => false, 'mensaje' => 'Operacion completada'];
        }
        $usuario = $resultado['datos'];

        // Generar token
        $tokenPlano = bin2hex(random_bytes(self::LONGITUD_TOKEN_BYTES));
        $expiracion = date('Y-m-d H:i:s', strtotime(self::TIEMPO_EXPIRACION));

        // Hasheamos el token antes de mandarlo al modelo
        $tokenCifrado = hash('sha256', $tokenPlano);

        $this->usuarioModel->set_id_usuario($usuario['id_usuario']);
        $this->usuarioModel->set_token($tokenCifrado); // Se guarda el HASH
        $this->usuarioModel->set_token_expiracion($expiracion);
        $this->usuarioModel->set_token_tipo(TipoToken::RECUPERACION->value);

        $resToken = $this->usuarioModel->realizar_consulta('registrar_token');
        if (!$resToken['estatus']) {
            return ['estatus' => false, 'mensaje' => 'Error al generar token de recuperación.'];
        }

        // Enviamos el token PLANO en la URL del correo
        $url = $this->generarUrlRecuperacion($tokenPlano);
        $enviado = $this->enviarCorreo($usuario, $url);
        
        return ['estatus' => true, 'mensaje' => 'Operacion completada'];
    }

    /**
     * Valida un token de recuperación.
     */
    public function validarTokenRecuperacion($tokenPlano)
    {
        // Hasheamos el token recibido por GET para poder compararlo en BD
        $tokenCifrado = hash('sha256', $tokenPlano);
        
        $this->usuarioModel->set_token($tokenCifrado);
        $this->usuarioModel->set_token_tipo(TipoToken::RECUPERACION->value);
        $resultado = $this->usuarioModel->realizar_consulta('validar_token');
        
        if (!$resultado['estatus']) {
            return ['estatus' => false, 'mensaje' => 'Token inválido o expirado.'];
        }
        return $resultado;
    }

    private function generarUrlRecuperacion($token)
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $pos = strpos($url, '?');
        if ($pos !== false) {
            $url = substr($url, 0, $pos);
        }
        return $url . '?pagina=login&accion=recuperar_contrasenia&t=' . $token;
    }

    private function enviarCorreo($usuario, $url)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom(PROVEEDOR_CORREO, 'Condominios Haydee');
            $mail->addAddress($usuario['correo'], $usuario['nombre'] . ' ' . ($usuario['apellido'] ?? ''));

            $mail->Subject = 'Recuperar contraseña';
            $mail->isHTML(true);

            $mensaje = file_get_contents(ROOT_PATH . '/vista/login/correo_recuperacion.html');
            $mensaje = str_replace('%usuario%', $usuario['nombre'], $mensaje);
            $mensaje = str_replace('%url%', $url, $mensaje);
            $mail->MsgHTML($mensaje);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Error enviando correo: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Genera un OTP numérico de 6 dígitos y lo envía por correo
     */
    public function enviarCorreoOTP($correo)
    {
        $this->usuarioModel->set_correo($correo);
        $resultado = $this->usuarioModel->realizar_consulta('existe_correo');
        
        // Anti-enumeración: Si no existe, simulamos éxito en el frontend
        if (!$resultado['estatus']) {
            return ['estatus' => true, 'mensaje' => 'Proceso iniciado']; 
        }
        
        $usuario = $resultado['datos'];

        $otp = Criptografia::generarOTP(self::OTP_LONGITUD);

        $tiempoAdicion = '+' . self::OTP_TIEMPO_EXPIRACION_MINUTOS . ' minutes';
        $expiracion = date('Y-m-d H:i:s', strtotime($tiempoAdicion));

        $this->usuarioModel->set_id_usuario($usuario['id_usuario']);
        $this->usuarioModel->set_token(password_hash($otp, PASSWORD_DEFAULT));
        $this->usuarioModel->set_token_expiracion($expiracion);
        $this->usuarioModel->set_token_tipo(TipoToken::RECUPERACION->value);
        
        $registroToken = $this->usuarioModel->realizar_consulta('registrar_token');
        if (!$registroToken['estatus']) {
            return ['estatus' => false, 'mensaje' => 'Error al generar código de seguridad.'];
        }

        return $this->enviarCorreoConCodigo($usuario, $otp);
    }

    private function enviarCorreoConCodigo($usuario, $otp)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom(PROVEEDOR_CORREO, 'Condominios Haydee');
            $mail->addAddress($usuario['correo'], $usuario['nombre'] . ' ' . ($usuario['apellido'] ?? ''));

            $mail->Subject = 'Código de Verificación - Haydee';
            $mail->isHTML(true);

            // Cargamos el nuevo template que creamos
            $mensaje = file_get_contents(ROOT_PATH . '/vista/login/correo_otp.html');
            
            // Inyectamos las variables dinámicas
            $mensaje = str_replace('%usuario%', $usuario['nombre'], $mensaje);
            $mensaje = str_replace('%codigo%', $otp, $mensaje);
            $mensaje = str_replace('%tiempo%', self::OTP_TIEMPO_EXPIRACION_MINUTOS, $mensaje);
            
            $mail->MsgHTML($mensaje);
            $mail->send();

            return ['estatus' => true, 'mensaje' => 'Operacion completada'];

        } catch (Exception $e) {
            error_log("Error al enviar correo OTP (PHPMailer): " . $mail->ErrorInfo);
            return ['estatus' => false, 'mensaje' => 'No se pudo enviar el correo. Intente más tarde.'];
        }
    }

    /**
     * Valida el OTP y cambia la contraseña de forma atómica
     */
    public function restablecerConOTP($correo, $otpCrudo, $nuevaContra)
    {
        $this->usuarioModel->set_correo($correo);
        $resUsuario = $this->usuarioModel->realizar_consulta('existe_correo');
        
        if (!$resUsuario['estatus']) {
            return ['estatus' => false, 'mensaje' => 'Código inválido o expirado.'];
        }
        
        $idUsuario = $resUsuario['datos']['id_usuario'];
        
        // Consultamos el token
        $this->usuarioModel->set_id_usuario($idUsuario);
        $this->usuarioModel->set_token_tipo(TipoToken::RECUPERACION->value);
        $resToken = $this->usuarioModel->realizar_consulta('obtener_token'); 
        
        if (!$resToken['estatus'] || !password_verify($otpCrudo, $resToken['datos']['token'])) {
            return ['estatus' => false, 'mensaje' => 'Código numérico inválido o ha expirado.'];
        }

        // El OTP coincide: Actualizamos la contraseña
        $this->usuarioModel->set_contra(password_hash($nuevaContra, PASSWORD_DEFAULT));
        $cambio = $this->usuarioModel->realizar_consulta('cambiar_contrasenia');
        
        if ($cambio['estatus']) {
            // Limpieza: Eliminamos el token de un solo uso
            $this->usuarioModel->realizar_consulta('eliminar_token');
            return ['estatus' => true, 'mensaje' => 'Contraseña actualizada con éxito.'];
        }

        return ['estatus' => false, 'mensaje' => 'No se pudo actualizar la contraseña.'];
    }


    /**
     * PASO 2: Valida el OTP, lo quema y emite un token de autorización para el cambio de clave.
     */
    public function validarOTP($correo, $otpCrudo)
    {
        $this->usuarioModel->set_correo($correo);
        $resUsuario = $this->usuarioModel->realizar_consulta('existe_correo');
        
        if (!$resUsuario['estatus']) {
            return ['estatus' => false, 'mensaje' => 'Código inválido o expirado.'];
        }
        
        $idUsuario = $resUsuario['datos']['id_usuario'];
        
        // Consultamos el token OTP actual
        $this->usuarioModel->set_id_usuario($idUsuario);
        $this->usuarioModel->set_token_tipo(TipoToken::RECUPERACION->value);
        $resToken = $this->usuarioModel->realizar_consulta('obtener_token'); 
        
        if (!$resToken['estatus'] || !password_verify($otpCrudo, $resToken['datos']['token'])) {
            return ['estatus' => false, 'mensaje' => 'Código numérico inválido o ha expirado.'];
        }

        // El OTP coincide: Quemar el token
        $this->usuarioModel->realizar_consulta('eliminar_token');

        $tokenAutorizacion = bin2hex(random_bytes(32)); 
        $expiracion = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $tokenCifradoRapido = hash('sha256', $tokenAutorizacion);

        $this->usuarioModel->set_token($tokenCifradoRapido);
        $this->usuarioModel->set_token_expiracion($expiracion);
        $this->usuarioModel->set_token_tipo(TipoToken::AUTORIZACION->value);
        
        $registroNuevoToken = $this->usuarioModel->realizar_consulta('registrar_token');

        if ($registroNuevoToken['estatus']) {
            // Devolvemos el token en claro SOLO esta vez para que React Native lo guarde en memoria
            return [
                'estatus' => true, 
                'mensaje' => 'Código validado correctamente.',
                'token_autorizacion' => $tokenAutorizacion
            ];
        }

        return ['estatus' => false, 'mensaje' => 'Error interno al generar autorización.'];
    }

    /**
     * Envía los parámetros limpios al modelo transaccional.
     */
    public function restablecerConToken($correo, $tokenPlano, $nuevaContra, $tipoToken = null)
    {
        // Si no se le pasa tipo, asume que es el de la APP Móvil por defecto
        $tipo = $tipoToken ?? TipoToken::AUTORIZACION->value;

        $this->usuarioModel->set_correo($correo);
        $this->usuarioModel->set_token($tokenPlano); // Pasa en plano, el modelo le aplicará SHA-256
        $this->usuarioModel->set_contra($nuevaContra); // Pasa en plano, el modelo le aplicará Bcrypt
        $this->usuarioModel->set_token_tipo($tipo);

        return $this->usuarioModel->realizar_consulta('consumir_token_recuperacion');
    }

    public function cerrar()
    {
        if ($this->usuarioModel) {
            $this->usuarioModel->cerrar();
        }
    }
}