<?php
use haydee\enums\HttpCodigo;
use haydee\enums\TipoToken;
use haydee\ayuda\Recaptcha;
use haydee\ayuda\Validador;
use haydee\modelo\Usuario;
use haydee\modelo\SeguridadIP;
use haydee\servicios\Sesiones;
use haydee\servicios\Autenticacion;
use haydee\servicios\Recuperacion;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$recaptchaDeshabilitado = defined('ENTORNO') && ENTORNO === 'local';

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación desconocida'];

    if (isset($_POST['usuario'])) {
        $_POST['correo'] = $_POST['usuario'];
    }
    if (isset($_POST['correo_recuperar'])) {
        $_POST['correo'] = $_POST['correo_recuperar'];
    }

    // --- VALIDACION ---
    $reglas = Usuario::obtenerReglas($operacion);
    if (!empty($reglas)) {
        $validador = new Validador();
        
        // skip_unique para evitar que rebote por tener el correo registrado
        $validador->validarConjunto($_POST, $reglas, ['skip_unique' => true]);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::BAD_REQUEST->value;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // Extraer datos comunes
    $usuario = $_POST['usuario'] ?? '';
    $contra = $_POST['contra'] ?? '';
    $mantenerSesion = ($_POST['mantener_sesion'] ?? 'false') === 'true';
    $correoRecuperar = $_POST['correo_recuperar'] ?? '';

    try {
        switch ($operacion) {
            case 'entrar':
                $seguridadIP = new SeguridadIP();
                $seguridadIP->set_ip($_SERVER['REMOTE_ADDR']);

                // Validar reCAPTCHA
                $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
                $recaptcha = new Recaptcha(null, $recaptchaDeshabilitado);
                $validacion = $recaptcha->verificar($recaptchaResponse);
                
                // Si falla el reCAPTCHA, es comportamiento sospechoso (Bot)
                if (!$validacion['estatus']) {
                    $seguridadIP->registrarFalloCritico(); 
                    http_response_code(HttpCodigo::BAD_REQUEST->value); 
                    $respuesta = ['estatus' => false, 'mensaje' => $validacion['error']];
                    break;
                }

                // Intentar autenticación (Delegado a Autenticacion.php)
                $auth = new Autenticacion();
                try {
                    $resultado = $auth->login($usuario, $contra, $mantenerSesion);
                } finally {
                    $auth->cerrar();
                }

                if ($resultado['estatus']) {
                    $seguridadIP->limpiarFallo(); // Limpiamos historial penal 8-]

                    http_response_code(HttpCodigo::OK->value); 
                    if (isset($resultado['token'])) {
                        Sesiones::recordar($usuario, $resultado['token']);
                    }
                    Sesiones::iniciar($resultado['datos']);
                    session_regenerate_id(true);
                } else {
                    $codigoError = $resultado['codigo_http'] ?? 401;
                    // Si el error es 429, la cuenta ya fue congelada por el modelo Usuario.
                    if ($codigoError !== 429 && $codigoError !== HttpCodigo::DEMASIADAS_PETICIONES->value) {
                        $seguridadIP->registrarFalloCritico(); // Clave mala: Sumamos infracción
                    }
                    
                    http_response_code($codigoError);
                }
                
                $respuesta = $resultado;
                break;

            case 'enviar_notificacion':
                $seguridadIP = new SeguridadIP();
                $seguridadIP->set_ip($_SERVER['REMOTE_ADDR']);

                $recuperacion = new Recuperacion();
                try {
                    $respuesta = $recuperacion->enviarCorreoRecuperacion($correoRecuperar);

                    if (strpos($respuesta['mensaje'], 'Error') !== false) {
                        // Si ocurre un error grave (ej. intento de inyección en el correo)
                        $seguridadIP->registrarFalloCritico();
                        http_response_code(HttpCodigo::ERROR_INTERNO->value); 
                    } else {
                        http_response_code(HttpCodigo::OK->value); 
                    }
                } finally {
                    $recuperacion->cerrar();
                }
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en controlador: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            echo json_encode($respuesta);
            exit;
        }
    }
}

// Manejo de Vistas y Redirecciones (GET)
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
                'id'     => $resultado['datos']['id_usuario'],
                'token'  => $token 
            ];
            require_once "vista/login/login_recuperar.php";
        } else {
            header("Location: ?pagina=login&accion=inicio&err=2");
        }
        break;

    case 'guardar_contrasenia':
        if (empty($_SESSION['reset_temp']) || empty($_SESSION['reset_temp']['token'])) {
            header("Location: ?pagina=login&accion=inicio&err=4");
            exit;
        }

        $recuperacion = new Recuperacion();
        try {
            $res = $recuperacion->restablecerConToken(
                $_SESSION['reset_temp']['correo'],
                $_SESSION['reset_temp']['token'], 
                $_POST['contra'] ?? '',
                TipoToken::RECUPERACION->value    
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
