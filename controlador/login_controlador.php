<?php
use haydee\ayuda\Recaptcha;
use haydee\servicios\Autenticacion;
use haydee\servicios\Recuperacion;
use haydee\ayuda\Sesiones;

// Iniciar sesión de forma segura al principio del script
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración: deshabilitar reCAPTCHA en local (puedes moverlo a un archivo de configuración)
$recaptchaDeshabilitado = defined('ENTORNO') && ENTORNO === 'local';

// ====================================================================
// 1. Manejo de Peticiones AJAX (API) - Retornan JSON
// ====================================================================
if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación desconocida'];

    try {
        switch ($operacion) {
            case 'entrar':
                $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

                // Validar reCAPTCHA
                $recaptcha = new Recaptcha(null, $recaptchaDeshabilitado);
                $validacion = $recaptcha->verificar($recaptchaResponse);
                if (!$validacion['success']) {
                    throw new Exception($validacion['error']);
                }

                // Intentar login
                $auth = new Autenticacion();
                $resultado = $auth->login(
                    $_POST['usuario'] ?? '',
                    $_POST['contra'] ?? '',
                    ($_POST['mantener_sesion'] ?? 'false') === 'true'
                );
                
                if ($resultado['estatus']) {
                    if (isset($resultado['token'])) {
                        Sesiones::recordar($_POST['usuario'], $resultado['token']);
                    }
                    Sesiones::iniciar($resultado['datos']);
                    session_regenerate_id(true);
                }
                
                $respuesta = $resultado;
                break;

            case 'enviar_notificacion':
                $recuperacion = new Recuperacion();
                $respuesta = $recuperacion->enviarCorreoRecuperacion($_POST['correo_recuperar'] ?? '');
                break;

            default:
                throw new Exception('Operación no válida');
        }
    } catch (Exception $e) {
        error_log("Error en controlador: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor', 'err'=>$e->getMessage()];
    }

    echo json_encode($respuesta);
    exit;
}

// ====================================================================
// 2. Manejo de Vistas y Redirecciones (GET)
// ====================================================================
$accion = $_GET['accion'] ?? 'inicio';

switch ($accion) {
    case 'cerrar':
        $auth = new Autenticacion();
        // Eliminar token de la BD (y cookies)
        $auth->logout($_SESSION['id_usuario'] ?? 0);
        // Destruir la sesión y redirigir
        Sesiones::cerrarSesion();
        exit;

    case 'recuperar_contrasenia':
        $token = $_GET['t'] ?? '';
        $recuperacion = new Recuperacion();
        $resultado = $recuperacion->validarTokenRecuperacion($token);

        if ($resultado['estatus']) {
            // Guardar datos temporalmente en sesión (solo para este flujo)
            $_SESSION['reset_temp'] = [
                'correo' => $resultado['datos']['correo'],
                'id'     => $resultado['datos']['id_usuario']
            ];
            require_once "vista/login/login_recuperar.php";
        } else {
            // Token inválido, redirigir al login con error
            header("Location: ?pagina=login_controlador.php&accion=inicio&err=2");
        }
        break;

    case 'guardar_contrasenia':
        // Verificar que venimos del paso anterior
        if (empty($_SESSION['reset_temp'])) {
            header("Location: ?pagina=login_controlador.php&accion=inicio&err=4");
            exit;
        }

        $recuperacion = new Recuperacion();
        $res = $recuperacion->cambiarContrasenia(
            $_SESSION['reset_temp']['correo'],
            $_POST['contra'] ?? '',
            $_SESSION['reset_temp']['id']
        );

        // Limpiar datos temporales
        unset($_SESSION['reset_temp']);

        $redir = $res['estatus'] ? '?pagina=login_controlador.php&accion=inicio&err=1' : '?pagina=login_controlador.php&accion=inicio&err=3';
        header("Location: $redir");
        exit;

    case 'inicio':
    default:
        // Si ya está logueado, redirigir al dashboard (no mostrar el login)
        if (isset($_SESSION['id_usuario'])) {
            header("Location: ?pagina=inicio_controlador.php&accion=inicio");
            exit;
        }

        echo "<script>const RECAPTCHA_DESACTIVADO = " . ($recaptchaDeshabilitado ? 'true' : 'false') . ";</script>";
        
        require_once "vista/login/login_vista.php";
        break;
}