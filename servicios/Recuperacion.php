<?php
namespace haydee\servicios;

use haydee\modelo\Usuario;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Recuperacion
{
    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Genera un token de recuperación y envía el correo.
     * @param string $correo
     * @return array ['estatus' => bool, 'mensaje' => string]
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
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->usuarioModel->set_id_usuario($usuario['id_usuario']);
        $this->usuarioModel->set_token($token);
        $this->usuarioModel->set_token_expiracion($expiracion);
        $this->usuarioModel->set_token_tipo('RECUPERAR_CONTRASENIA');

        $resToken = $this->usuarioModel->realizar_consulta('registrar_token');
        if (!$resToken['estatus']) {
            return ['estatus' => false, 'mensaje' => 'Error al generar token de recuperación.'];
        }

        // Enviar correo
        $url = $this->generarUrlRecuperacion($token);
        $enviado = $this->enviarCorreo($usuario, $url);
        if (!$enviado) {
            return ['estatus' => false, 'mensaje' => 'Operacion completada'];
        }

        return ['estatus' => true, 'mensaje' => 'Operacion completada'];
    }

    /**
     * Valida un token de recuperación.
     * @param string $token
     * @return array ['estatus' => bool, 'mensaje' => string, 'datos' => array|null]
     */
    public function validarTokenRecuperacion($token)
    {
        $this->usuarioModel->set_token($token);
        $this->usuarioModel->set_token_tipo('RECUPERAR_CONTRASENIA');
        $resultado = $this->usuarioModel->realizar_consulta('validar_token');
        if (!$resultado['estatus']) {
            return ['estatus' => false, 'mensaje' => 'Token inválido o expirado.'];
        }
        return $resultado;
    }

    /**
     * Cambia la contraseña y elimina el token usado.
     * @param string $correo
     * @param string $nuevaContra
     * @param int $usuarioId
     * @return array
     */
    public function cambiarContrasenia($correo, $nuevaContra, $usuarioId)
    {
        $this->usuarioModel->set_correo($correo);
        $this->usuarioModel->set_contra($nuevaContra);
        $this->usuarioModel->set_id_usuario($usuarioId);
        $res = $this->usuarioModel->realizar_consulta('cambiar_contrasenia');
        if (!$res['estatus']) {
            return $res;
        }

        // Eliminar token usado
        $this->usuarioModel->set_token_tipo('RECUPERAR_CONTRASENIA');
        $this->usuarioModel->realizar_consulta('eliminar_token');

        return ['estatus' => true, 'mensaje' => 'Contraseña actualizada.'];
    }

    private function generarUrlRecuperacion($token)
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $pos = strpos($url, '?');
        if ($pos !== false) {
            $url = substr($url, 0, $pos);
        }
        return $url . '?pagina=login_controlador.php&accion=recuperar_contrasenia&t=' . $token;
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
}