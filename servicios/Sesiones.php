<?php
declare(strict_types=1);

namespace haydee\servicios;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
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
     * Orquestador de seguridad de entrada (Middleware)
     */
    public static function autorizarAcceso($configRuta, $metodoActual)
    {
        // Validar el método HTTP
        self::validarMetodoHTTP($configRuta[Endpoints::CONF_METODOS], $metodoActual);

        // Control de red e IP
        self::verificarAccesoRed();

        // Control de autenticación si la ruta lo exige
        if ($configRuta[Endpoints::CONF_REQUIERE_AUTH] !== true) {
            return;
        }
            
        self::verificarSesion();

        $usuarioId = (int)($_SESSION['id_usuario'] ?? 0);

        // Anti-Flood en Base de Datos (Delegado al modelo SeguridadIP)
        self::verificarInundacionBD($usuarioId);

        // Control de permisos por módulo
        if ($configRuta[Endpoints::CONF_MODULO] !== null) {
            self::verificarPermiso($configRuta[Endpoints::CONF_MODULO], Accion::CONSULTAR);
        }
    }

    public static function autorizarAccesoAPI($configRuta)
    {
        // Capa de Protocolo 
        self::validarMetodoHTTP($configRuta[Endpoints::CONF_METODOS], $_SERVER['REQUEST_METHOD']);

        // Capa de Red Perimetral (WAF / Castigos de IP)
        self::verificarAccesoRed();

        // Salida temprana para rutas públicas (Login, Recuperar, Handshake)
        if ($configRuta[Endpoints::CONF_REQUIERE_AUTH] !== true) {
            return;
        }

        // Capa de Identidad (Decodificación JWT)
        self::$usuarioLogueado = self::validarAutenticacionJWT();
        $usuario = self::$usuarioLogueado;

        // Capa de Comportamiento (Anti-Flood por Usuario)
        self::verificarInundacionBD((int)$usuario['id_usuario']);

        // Capa de Autorización (RBAC - Derecho de Entrada al Módulo)
        $modulo = $configRuta[Endpoints::CONF_MODULO];
        if ($modulo !== null) {
            if (!self::tienePermiso($modulo, Accion::CONSULTAR)) {
                throw new Exception("Acceso denegado: No tienes privilegios suficientes para entrar a este módulo.", HttpCodigo::PROHIBIDO->value);
            }
        }
    }

    /**
     * Verifica accesos de red y listas de IP
     */
    public static function verificarAccesoRed()
    {
        $ipCliente = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $seguridad = new SeguridadIP();
        $seguridad->set_ip($ipCliente);
        
        $acceso = $seguridad->verificarListaAcceso();
        if (!$acceso['estatus']) {
            error_log("[Firewall IP] IP bloqueada intento ingresar: {$ipCliente}");
            throw new Exception($acceso['mensaje'], $acceso['codigo_http'] ?? 403);
        }

        $rateLimitIP = $seguridad->verificarRateLimitGlobal();
        if (!$rateLimitIP['estatus']) {
            error_log("[Firewall Rate-Limit] IP saturando el servidor: {$ipCliente}");
            throw new Exception($rateLimitIP['mensaje'], $rateLimitIP['codigo_http'] ?? 429);
        }
    }

    /**
     * Anti-Flood delegando la lógica al modelo especializado de Seguridad IP
     */
    private static function verificarInundacionBD(int $usuarioId)
    {
        $seguridadModelo = new SeguridadIP();
        $resultado = $seguridadModelo->verificarRateLimitUsuario($usuarioId, self::MAX_PETICIONES_MINUTO);

        if (!$resultado['estatus']) {
            error_log("[Anti-Flood] Usuario ID {$usuarioId} supero el límite de peticiones por minuto (" . self::MAX_PETICIONES_MINUTO . ")");
            throw new Exception($resultado['mensaje'], HttpCodigo::DEMASIADAS_PETICIONES->value);
        }
    }

    /**
     * Valida si existe sesión activa o intenta recuperarla por Cookie
     */
    private static function verificarSesion()
    {
        if (isset($_SESSION["usuario"])) {
            return;
        }

        // Si no hay sesión pero existen las cookies, intentamos recordar de forma segura
        if (isset($_COOKIE['token']) && isset($_COOKIE['correo_usuario'])) {
            if (self::procesarTokenRecuerdame()) {
                return;
            }
        }

        throw new Exception("Su sesion ha expirado. Por favor, inicie sesión de nuevo.", HttpCodigo::NO_AUTORIZADO->value);
    }

    /**
     * Procesa y valida el token de la cookie "Recuérdame"
     */
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
            
            // Si el token es inválido, limpiamos las cookies sospechosas
            setcookie('token', '', time() - self::EXPIRACION_PASADO, '/');
            setcookie('correo_usuario', '', time() - self::EXPIRACION_PASADO, '/');
            return false;
        } catch (Exception $e) {
            error_log("Error procesando cookie Recuerdame: " . $e->getMessage());
            return false;
        } finally {
            if ($autenticacion) {
                $autenticacion->cerrar();
            }
        }
    }

    /**
     * Crea las cookies en el cliente para mantener la sesión persistente
     */
    public static function recordar(string $correo, string $token)
    {
        $tiempoExpiracion = time() + (self::DIAS_RECORDAR_SESION * self::SEGUNDOS_POR_DIA);
        
        setcookie('token', $token, $tiempoExpiracion, '/', '', false, true);
        setcookie('correo_usuario', $correo, $tiempoExpiracion, '/', '', false, true);
    }

    /**
     * Valida el método HTTP de la solicitud actual
     */
    public static function validarMetodoHTTP(array $metodosPermitidos, string $metodoActual)
    {
        $metodosValidos = array_map(fn($m) => $m->value ?? $m, $metodosPermitidos);

        if (!in_array($metodoActual, $metodosValidos, true)) {
            error_log("[Protocolo HTTP] Metodo no permitido: '{$metodoActual}'. Metodos esperados: " . implode(', ', $metodosValidos));
            header('Allow: ' . implode(', ', $metodosValidos));
            throw new Exception("El metodo HTTP {$metodoActual} no esta soportado por esta ruta.", HttpCodigo::METODO_NO_PERMITIDO->value);
        }
    }

    /**
     * Inicializa las variables de sesión de PHP
     */
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
        
        // Mantener persistencia si venían notificaciones activas del backend
        if (isset($datosUsuario["notificaciones"]) && is_array($datosUsuario["notificaciones"])) {
            $_SESSION["notificaciones"] = array_filter($datosUsuario["notificaciones"], function($n) {
                return (int)($n['leido'] ?? 1) === 0;
            });
        }
    }

    /**
     * Evalúa si el usuario tiene asignada una acción en un módulo específico
     */
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

    /**
     * Aplica restricción estricta de permisos
     */
    private static function verificarPermiso(Modulo $modulo, Accion $permiso)
    {
        if (!self::tienePermiso($modulo, $permiso)) {
            error_log("[RBAC] Acceso Prohibido. Usuario ID: " . ($_SESSION['id_usuario'] ?? 0) . " intentó " . $permiso->name . " en el Modulo " . $modulo->name);
            throw new Exception("Acceso denegado: No tienes privilegios suficientes para este modulo.", HttpCodigo::PROHIBIDO->value);
        }
    }

    /**
     * Destruye de forma segura la sesión y las cookies del cliente
     */
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
                    throw new Exception($mensaje, HttpCodigo::PROHIBIDO->value);
                } else {
                    http_response_code(HttpCodigo::PROHIBIDO->value); 
                    header('Content-Type: application/json');
                    echo json_encode(['estatus' => false, 'mensaje' => $mensaje]);
                    exit;
                }
            }
        }
    }

    public static function validarAutenticacionJWT()
    {
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            throw new Exception("Falta el token de seguridad JWT.", HttpCodigo::NO_AUTORIZADO->value);
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
            // El token es genuino pero su tiempo de vida de 1 hora se agotó
            $errorData = [
                'mensaje' => 'El token de acceso ha expirado.',
                'codigo_interno' => 'JWT_EXPIRADO' 
            ];
            throw new Exception(json_encode($errorData), HttpCodigo::NO_AUTORIZADO->value);
        } catch (\Exception $e) {
            // El token fue manipulado, mal formado o la firma no coincide (Posible ataque)
            $errorData = [
                'mensaje' => 'Token de seguridad inválido o corrupto.',
                'codigo_interno' => 'JWT_INVALIDO'
            ];
            throw new Exception(json_encode($errorData), HttpCodigo::NO_AUTORIZADO->value);
        }
    }
    
}