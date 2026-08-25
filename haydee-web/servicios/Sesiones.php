<?php
declare(strict_types=1);

namespace haydee\servicios;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use haydee\excepciones\SeguridadException;
use haydee\excepciones\ValidacionException; 
use haydee\excepciones\HaydeeException;
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\modelo\SeguridadIP;
use haydee\modelo\Rol;
use haydee\servicios\Autenticacion;
use haydee\servicios\Endpoints;

class Sesiones
{
    private const MAX_PETICIONES_MINUTO = 60;
    private const DIAS_RECORDAR_SESION = 30;
    private const SEGUNDOS_POR_DIA = 86400;
    private const EXPIRACION_PASADO = 3600;

    public static $permisosAPI = null;
    public static $usuarioLogueado = null;

    /**
     * Middleware Web
     */
    public static function autorizarAcceso($configRuta, $metodoActual)
    {
        self::validarMetodoHTTP($configRuta[Endpoints::CONF_METODOS], $metodoActual);
        self::verificarAccesoRed();

        if ($configRuta[Endpoints::CONF_REQUIERE_AUTH] !== true) {
            return;
        }
            
        self::verificarSesion();

        $usuarioId = (int)($_SESSION['id_usuario'] ?? 0);

        self::verificarInundacionBD($usuarioId);

        if ($configRuta[Endpoints::CONF_MODULO] !== null) {
            self::verificarPermiso($configRuta[Endpoints::CONF_MODULO], Accion::CONSULTAR);
        }
    }

    /**
     * Middleware API
     */
    public static function autorizarAccesoAPI($configRuta)
    {
        self::validarMetodoHTTP($configRuta[Endpoints::CONF_METODOS], $_SERVER['REQUEST_METHOD']);
        self::verificarAccesoRed();

        if ($configRuta[Endpoints::CONF_REQUIERE_AUTH] !== true) {
            return;
        }

        self::$usuarioLogueado = self::validarAutenticacionJWT();
        $usuario = self::$usuarioLogueado;

        self::verificarInundacionBD((int)$usuario['id_usuario']);

        $modulo = $configRuta[Endpoints::CONF_MODULO];
        if ($modulo !== null) {
            if (!self::tienePermiso($modulo, Accion::CONSULTAR)) {
                throw new SeguridadException("Acceso denegado: No tienes privilegios suficientes para entrar a este módulo.", HttpCodigo::PROHIBIDO->value);
            }
        }
    }

    public static function verificarAccesoRed()
    {
        $ipCliente = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $seguridad = new SeguridadIP();
        $seguridad->set_ip($ipCliente);
        
        $acceso = $seguridad->verificarListaAcceso();
        if (!$acceso['estatus']) {
            throw new SeguridadException($acceso['mensaje'], $acceso['codigo_http'] ?? 403);
        }

        $rateLimitIP = $seguridad->verificarRateLimitGlobal();
        if (!$rateLimitIP['estatus']) {
            throw new SeguridadException($rateLimitIP['mensaje'], $rateLimitIP['codigo_http'] ?? 429);
        }
    }

    private static function verificarInundacionBD(int $usuarioId)
    {
        $seguridadModelo = new SeguridadIP();
        $resultado = $seguridadModelo->verificarRateLimitUsuario($usuarioId, self::MAX_PETICIONES_MINUTO);

        if (!$resultado['estatus']) {
            error_log("[Anti-Flood] Usuario ID {$usuarioId} superó límite de peticiones.");
            throw new SeguridadException($resultado['mensaje'], HttpCodigo::DEMASIADAS_PETICIONES->value);
        }
    }

    private static function verificarSesion()
    {
        if (isset($_SESSION["usuario"])) {
            return;
        }

        if (isset($_COOKIE['token']) && isset($_COOKIE['correo_usuario'])) {
            if (self::procesarTokenRecuerdame()) {
                return;
            }
        }

        throw new SeguridadException("Su sesión ha expirado. Por favor, inicie sesión de nuevo.", HttpCodigo::NO_AUTORIZADO->value);
    }

    private static function procesarTokenRecuerdame()
    {
        $autenticacion = null;
        try {
            $token = $_COOKIE['token'];
            $correo = $_COOKIE['correo_usuario'];

            $autenticacion = new Autenticacion();
            $resultado = $autenticacion->validarTokenRecuerdame($correo, $token);
            
            if ($resultado['estatus']) {
                self::iniciar($resultado['datos']);
                return true;
            }
            
            setcookie('token', '', time() - self::EXPIRACION_PASADO, '/');
            setcookie('correo_usuario', '', time() - self::EXPIRACION_PASADO, '/');
            return false;
        } catch (\Throwable $e) {
            error_log("Error procesando cookie Recuérdame: " . $e->getMessage());
            return false;
        } finally {
            if ($autenticacion) {
                $autenticacion->cerrar();
            }
        }
    }

    public static function recordar(string $correo, string $token)
    {
        $tiempoExpiracion = time() + (self::DIAS_RECORDAR_SESION * self::SEGUNDOS_POR_DIA);
        setcookie('token', $token, $tiempoExpiracion, '/', '', false, true);
        setcookie('correo_usuario', $correo, $tiempoExpiracion, '/', '', false, true);
    }

    public static function validarMetodoHTTP(array $metodosPermitidos, string $metodoActual)
    {
        $metodosValidos = array_map(fn($m) => $m->value ?? $m, $metodosPermitidos);

        if (!in_array($metodoActual, $metodosValidos, true)) {
            header('Allow: ' . implode(', ', $metodosValidos));
            throw new HaydeeException("El método HTTP {$metodoActual} no está soportado por esta ruta.", HttpCodigo::METODO_NO_PERMITIDO->value);
        }
    }

    public static function iniciar(array $datosUsuario)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION["id_usuario"] = (int)$datosUsuario['id_usuario'];
        $_SESSION["usuario"] = $datosUsuario['correo'];
        $_SESSION["nombre_completo"] = $datosUsuario['nombre_completo'];
        $_SESSION["rol"] = $datosUsuario['rol'];
        $_SESSION["permisos"] = $datosUsuario['permisos'];
        
        if (isset($datosUsuario["notificaciones"]) && is_array($datosUsuario["notificaciones"])) {
            $_SESSION["notificaciones"] = array_filter($datosUsuario["notificaciones"], function($n) {
                return (int)($n['leido'] ?? 1) === 0;
            });
        }
    }

    public static function tienePermiso(Modulo $modulo, Accion $permiso)
    {
        $listaPermisos = self::$permisosAPI ?? $_SESSION["permisos"] ?? null;
        if (!$listaPermisos || !is_array($listaPermisos)) {
            return false;
        }

        foreach ($listaPermisos as $permisoArr) {
            if ((int)$permisoArr["modulo_id"] === $modulo->value 
                && $permisoArr["permiso"] === $permiso->value) {
                return true;
            }
        }
        return false;
    }

    private static function verificarPermiso(Modulo $modulo, Accion $permiso)
    {
        if (!self::tienePermiso($modulo, $permiso)) {
            throw new SeguridadException("Acceso denegado: No tienes privilegios suficientes para este módulo.", HttpCodigo::PROHIBIDO->value);
        }
    }

    public static function cerrarSesion()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        setcookie('token', '', time() - self::EXPIRACION_PASADO, '/');
        setcookie('correo_usuario', '', time() - self::EXPIRACION_PASADO, '/');
        header("Location: index.php?pagina=login");
        exit;
    }

    public static function obtenerPermisosVista(Modulo $modulo)
    {
        return [
            'consultar' => self::tienePermiso($modulo, Accion::CONSULTAR),
            'registrar' => self::tienePermiso($modulo, Accion::REGISTRAR),
            'modificar' => self::tienePermiso($modulo, Accion::MODIFICAR),
            'eliminar'  => self::tienePermiso($modulo, Accion::ELIMINAR)
        ];
    }

    public static function verificarPermisoAccion(Modulo $modulo, $operacion, $mapaExtra = [], $esApi = false)
    {
        $permisoRequerido = null;
        $operacionNormalizada = strtolower($operacion);

        if (array_key_exists($operacionNormalizada, $mapaExtra)) {
            $permisoRequerido = $mapaExtra[$operacionNormalizada]; 
        } else {
            if (strpos($operacionNormalizada, 'registrar') !== false) {
                $permisoRequerido = Accion::REGISTRAR;
            } elseif (strpos($operacionNormalizada, 'modificar') !== false) {
                $permisoRequerido = Accion::MODIFICAR;
            } elseif (strpos($operacionNormalizada, 'eliminar') !== false) {
                $permisoRequerido = Accion::ELIMINAR;
            }
        }

        if ($permisoRequerido !== null) {
            if (!self::tienePermiso($modulo, $permisoRequerido)) {
                $mensaje = 'No tienes permisos suficientes para realizar esta acción.';
                
                if ($esApi) {
                    throw new SeguridadException($mensaje, HttpCodigo::PROHIBIDO->value);
                } else {
                    throw new SeguridadException($mensaje, HttpCodigo::PROHIBIDO->value);
                }
            }
        }
    }

    public static function validarAutenticacionJWT()
    {
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            throw new SeguridadException("Falta el token de seguridad JWT.", HttpCodigo::NO_AUTORIZADO->value);
        }

        try {
            $decoded = JWT::decode($matches[1], new Key(JWT_SECRET, 'HS256'));
            $usuario = (array) $decoded->data;

            $rolModel = new Rol();
            $rolModel->set_id_rol($usuario['rol_id']);
            $resPermisos = $rolModel->realizar_consulta('consultar_permisos_asignados');
            self::$permisosAPI = $resPermisos['datos'] ?? [];

            return $usuario;
        } catch (ExpiredException $e) {
            throw new SeguridadException("El token de acceso ha expirado.", HttpCodigo::NO_AUTORIZADO->value, ['codigo_interno' => 'JWT_EXPIRADO']);
        } catch (\Exception $e) {
            throw new SeguridadException("Token de seguridad inválido o corrupto.", HttpCodigo::NO_AUTORIZADO->value, ['codigo_interno' => 'JWT_INVALIDO']);
        }
    }
}