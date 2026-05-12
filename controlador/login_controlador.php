<?php
use haydee\ayuda\Recaptcha;
use haydee\modelo\Usuario;
use haydee\servicios\Sesiones;
use haydee\ayuda\Validador;
use haydee\servicios\Autenticacion;
use haydee\servicios\Recuperacion;

Sesiones::validarMetodoHTTP(['GET', 'POST']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$recaptchaDeshabilitado = defined('ENTORNO') && ENTORNO === 'local';

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación desconocida'];

    // --- NORMALIZACIÓN PARA EL VALIDADOR ---
    // Copiamos los inputs del frontend al nombre estándar ('correo') para poder validarlos
    if (isset($_POST['usuario'])) {
        $_POST['correo'] = $_POST['usuario'];
    }
    if (isset($_POST['correo_recuperar'])) {
        $_POST['correo'] = $_POST['correo_recuperar'];
    }

    // --- VALIDACIÓN CENTRALIZADA ---
    $reglas = Usuario::obtenerReglas($operacion);
    if (!empty($reglas)) {
        $validador = new Validador();
        
        // Usamos skip_unique para evitar que nos rebote por tener el correo registrado
        $validador->validarConjunto($_POST, $reglas, ['skip_unique' => true]);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? 404 : 400;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // Extraer datos comunes (Ya validados y seguros)
    $usuario = $_POST['usuario'] ?? '';
    $contra = $_POST['contra'] ?? '';
    $mantenerSesion = ($_POST['mantener_sesion'] ?? 'false') === 'true';
    $correoRecuperar = $_POST['correo_recuperar'] ?? '';

        try {
        switch ($operacion) {
            case 'entrar':
                $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

                // Validar reCAPTCHA
                $recaptcha = new Recaptcha(null, $recaptchaDeshabilitado);
                $validacion = $recaptcha->verificar($recaptchaResponse);
                
                // Si falla el reCAPTCHA, devolvemos 400 (Bad Request)
                if (!$validacion['estatus']) {
                    http_response_code(400); 
                    $respuesta = ['estatus' => false, 'mensaje' => $validacion['error']];
                    break;
                }

                $auth = new Autenticacion();
                try {
                    $resultado = $auth->login($usuario, $contra, $mantenerSesion);
                } finally {
                    $auth->cerrar();
                }

                if ($resultado['estatus']) {
                    http_response_code(200); // Login exitoso
                    if (isset($resultado['token'])) {
                        Sesiones::recordar($usuario, $resultado['token']);
                    }
                    Sesiones::iniciar($resultado['datos']);
                    session_regenerate_id(true);
                } else {
                    $codigoError = $resultado['codigo_http'] ?? 401;// 401 Unauthorized (Credenciales incorrectas)
                    http_response_code($codigoError);
                }
                
                $respuesta = $resultado;
                break;

            case 'enviar_notificacion':
                $recuperacion = new Recuperacion();
                try {
                    $respuesta = $recuperacion->enviarCorreoRecuperacion($correoRecuperar);

                    if (strpos($respuesta['mensaje'], 'Error') !== false) {
                        http_response_code(500); 
                    } else {
                        http_response_code(200); 
                    }
                } finally {
                    $recuperacion->cerrar();
                }
                break;

            default:
                http_response_code(400);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error en controlador: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            echo json_encode($respuesta);
            exit;
        }
    }
}

// ====================================================================
// 2. Manejo de Vistas y Redirecciones (GET)
// ====================================================================
$accion = $_GET['accion'] ?? 'inicio';

switch ($accion) {
    case 'cerrar':
        $auth = new Autenticacion();
        try {
            $auth->logout($_SESSION['id_usuario'] ?? 0);
        } finally {
            $auth->cerrar();
        }
        Sesiones::cerrarSesion();
        exit;

    case 'recuperar_contrasenia':
        $token = $_GET['t'] ?? '';
        $recuperacion = new Recuperacion();
        try {
            $resultado = $recuperacion->validarTokenRecuperacion($token);
        } finally {
            $recuperacion->cerrar();
        }

        if ($resultado['estatus']) {
            $_SESSION['reset_temp'] = [
                'correo' => $resultado['datos']['correo'],
                'id'     => $resultado['datos']['id_usuario']
            ];
            require_once "vista/login/login_recuperar.php";
        } else {
            header("Location: ?pagina=login&accion=inicio&err=2");
        }
        break;

    case 'guardar_contrasenia':
        if (empty($_SESSION['reset_temp'])) {
            header("Location: ?pagina=login&accion=inicio&err=4");
            exit;
        }

        $recuperacion = new Recuperacion();
        try {
            $res = $recuperacion->cambiarContrasenia(
                $_SESSION['reset_temp']['correo'],
                $_POST['contra'] ?? '',
                $_SESSION['reset_temp']['id']
            );
        } finally {
            $recuperacion->cerrar();
        }

        unset($_SESSION['reset_temp']);
        $redir = $res['estatus'] ? '?pagina=login&accion=inicio&err=1' : '?pagina=login&accion=inicio&err=3';
        header("Location: $redir");
        exit;

    case 'inicio':
    default:
        if (isset($_SESSION['id_usuario'])) {
            header("Location: ?pagina=inicio&accion=inicio");
            exit;
        }
        echo "<script>const RECAPTCHA_DESACTIVADO = " . ($recaptchaDeshabilitado ? 'true' : 'false') . ";</script>";
        require_once "vista/login/login_vista.php";
        break;
}