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
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;
use haydee\excepciones\SeguridadException;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$recaptchaDeshabilitado = defined('ENTORNO') && ENTORNO === 'local';

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    if (isset($_POST['usuario'])) {
        $_POST['correo'] = $_POST['usuario'];
    }
    if (isset($_POST['correo_recuperar'])) {
        $_POST['correo'] = $_POST['correo_recuperar'];
    }

    $reglas = Usuario::obtenerReglas($operacion);
    if (!empty($reglas)) {
        $validador = new Validador();
        
        $validador->validarConjunto($_POST, $reglas, ['skip_unique' => true]);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    $usuario = $_POST['usuario'] ?? '';
    $contra = $_POST['contra'] ?? '';
    $mantenerSesion = ($_POST['mantener_sesion'] ?? 'false') === 'true';
    $correoRecuperar = $_POST['correo_recuperar'] ?? '';
    
    $codigoExito = HttpCodigo::OK->value;
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];

    switch ($operacion) {
        case 'entrar':
            $seguridadIP = new SeguridadIP();
            $seguridadIP->set_ip($_SERVER['REMOTE_ADDR']);

            $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
            $recaptcha = new Recaptcha(null, $recaptchaDeshabilitado);
            $validacion = $recaptcha->verificar($recaptchaResponse);
            
            if (!$validacion['estatus']) {
                $seguridadIP->registrarFalloCritico(); 
                throw new SeguridadException($validacion['error'], HttpCodigo::BAD_REQUEST->value);
            }

            $auth = new Autenticacion();
            try {
                $resultado = $auth->login($usuario, $contra, $mantenerSesion);
            } finally {
                $auth->cerrar();
            }

            if ($resultado['estatus']) {
                $seguridadIP->limpiarFallo(); 
                
                if (isset($resultado['refresh_token'])) {
                    Sesiones::recordar($usuario, $resultado['refresh_token']);
                }
                Sesiones::iniciar($resultado['datos']);
                session_regenerate_id(true);
            } else {
                throw new SeguridadException($resultado['mensaje'], $resultado['codigo_http'] ??  HttpCodigo::NO_AUTORIZADO->value);
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
                    $seguridadIP->registrarFalloCritico();
                    throw new HaydeeException('Error al enviar recuperación', HttpCodigo::ERROR_INTERNO->value);
                } 
            } finally {
                $recuperacion->cerrar();
            }
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

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
        require_once "vista/login/login_vista.php";
        break;
}