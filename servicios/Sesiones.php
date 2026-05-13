<?php

namespace haydee\servicios;

use haydee\servicios\Autenticacion;
use haydee\modelo\SeguridadIP;
use haydee\modelo\Rol;
use haydee\modelo\Notificaciones;
use haydee\modelo\AnioFiscal;
use haydee\modelo\CajaChica;
use haydee\enums\Accion;
use haydee\enums\Modulo;

class Sesiones
{
    private const MAX_PETICIONES_MINUTO = 60;
    private const VENTANA_TIEMPO_SEGUNDOS = 60;

    /**
     * Inicia la sesión con los datos del usuario.
     */
    public static function iniciar($datosUsuario)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION["id_usuario"] = $datosUsuario['id_usuario'];
        $_SESSION["usuario"] = $datosUsuario['correo'];
        $_SESSION["nombre_completo"] = $datosUsuario['nombre_completo'];
        $_SESSION["rol"] = $datosUsuario['rol'];
        $_SESSION["permisos"] = $datosUsuario['permisos'];
        $_SESSION["notificaciones"] = array_filter($datosUsuario["notificaciones"],function($n){return $n['leido'] == 0;});

        // Verificar año fiscal y caja (proximamente proceso automático -_-)
        try {
            $anioFiscalModel = new AnioFiscal();
            $anioFiscalModel->realizar_consulta('verificar_anio_fiscal');

            $caja = new CajaChica();
            $caja->realizar_consulta('verificar_caja_mes');
        } finally {
            $anioFiscalModel->cerrar();
        }
    }

    /**
     * Método centralizado para autorizar el acceso a un controlador.
     * Ejecuta secuencialmente: IP -> Método HTTP -> Sesión -> Permisos.
     */
    public static function autorizarAcceso(?Modulo $modulo = null, ?Accion $permiso = null, $metodos = ['GET', 'POST'])
    {
        // Capa de Red (Firewall IP)
        self::verificarAccesoRed();

        // Capa de Protocolo (Metodos permitidos)
        self::validarMetodoHTTP($metodos);

        // Capa de Identidad (Quien es)
        self::verificarSesion();

        // Capa de Comportamiento (Anti-Flood)
        self::verificarInundacion();

        // Capa de Autorización (Que puede hacer)
        // Solo verifica permisos si el controlador se los exigió explícitamente
        if ($modulo !== null && $permiso !== null) {
            self::verificarPermiso($modulo, $permiso);
        }
    }

    /**
     * Verifica si la sesión está iniciada; si no, intenta con cookies de recordar.
     * Redirige al login si no hay sesión ni cookies válidas.
     */
    public static function verificarSesion()
    {
        if (self::estaLogueado()) {
            return true;
        }

        // Si hay cookies de "recuérdame", intentamos recuperar la sesión
        if (isset($_COOKIE['token']) && isset($_COOKIE['correo_usuario'])) {
            return self::procesarTokenRecuerdame();
        }

        // Si llegamos aquí, no hay sesión.
        // Si es una petición POST (asumimos AJAX), devolvemos 401.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'estatus' => false, 
                'mensaje' => 'Su sesión ha expirado. Por favor, inicie sesión de nuevo.'
            ]);
            exit;
        }

        // Si es una petición normal (GET), redirigimos al login
        self::redirigirALogin();
        return false;
    }

    /**
     * Procesa el token de "recordar sesión" usando el servicio Autenticacion.
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
            } else {
                // Token inválido, eliminar cookies
                setcookie('token', '', time() - 3600, '/');
                setcookie('correo_usuario', '', time() - 3600, '/');
                self::redirigirALogin();
                return false;
            }
        } finally {
            if ($autenticacion) {
                $autenticacion->cerrar();
            }
        }
    }

    /**
     * Establece las cookies para recordar la sesión.
     * @param string $correo
     * @param string $token Token sin hashear
     * @param int $dias Duración en días (por defecto 30)
     */
    public static function recordar($correo, $token, $dias = 30)
    {
        $expiracion = time() + ($dias * 24 * 60 * 60);
        // Usar cookies seguras (HttpOnly, Secure en producción)
        $secure = defined('ENTORNO') && ENTORNO === 'produccion'; // Ajusta según tu entorno
        setcookie('token', $token, $expiracion, '/', '', $secure, true);
        setcookie('correo_usuario', $correo, $expiracion, '/', '', $secure, true);
    }

    /**
     * Cierra la sesión actual (destruye sesión y elimina cookies).
     */
    public static function cerrarSesion()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        setcookie('token', '', time() - 3600, '/');
        setcookie('correo_usuario', '', time() - 3600, '/');

        self::redirigirALogin();
    }

    /**
     * Redirige al login y termina la ejecución.
     */
    private static function redirigirALogin()
    {
        header("Location: ?pagina=login&accion=inicio");
        exit;
    }

    /**
     * Verifica si el usuario está logueado.
     */
    public static function estaLogueado()
    {
        return isset($_SESSION["usuario"]);
    }

    /**
     * Verifica si el usuario tiene permiso para un módulo y acción.
     * @param int $moduloId   ID del módulo (constante definida globalmente)
     * @param int $permisoId  ID del permiso (constante definida globalmente)
     * @return bool
     */
    public static function tienePermiso(Modulo $modulo, Accion $permiso)
    {
        if (!isset($_SESSION["permisos"]) || !is_array($_SESSION["permisos"])) {
            return false;
        }

        foreach ($_SESSION["permisos"] as $p) {
            if ($p["modulo_id"] == $modulo->value && $p["permiso"] == $permiso->value) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica permiso y si no lo tiene, muestra error 403 y detiene la ejecución.
     */
    public static function verificarPermiso(Modulo $modulo, Accion $permiso)
    {
        self::verificarSesion();

        if (!self::tienePermiso($modulo, $permiso)) {
            http_response_code(403);
            require_once "vista/error/403_vista.php";
            exit;
        }
    }

    /**
     * Obtiene todos los permisos de un módulo estructurados en un arreglo para la vista.
     * Asume que las constantes CONSULTAR, REGISTRAR, MODIFICAR y ELIMINAR son globales.
     * * @param int $moduloId ID del módulo a consultar
     * @return array Arreglo asociativo con los permisos booleanos
     */
    public static function obtenerPermisosVista(Modulo $modulo)
    {
        return [
            'consultar' => self::tienePermiso($modulo, Accion::CONSULTAR),
            'registrar' => self::tienePermiso($modulo, Accion::REGISTRAR),
            'modificar' => self::tienePermiso($modulo, Accion::MODIFICAR),
            'eliminar'  => self::tienePermiso($modulo, Accion::ELIMINAR)
        ];
    }

    /**
     * Verifica los permisos para operaciones que se ejecutan vía AJAX.
     * Evalúa el nombre de la operación para requerir el permiso adecuado automáticamente.
     * Si no tiene permisos, devuelve un JSON con estatus false y termina la ejecución.
     */
    public static function verificarPermisoAccion(Modulo $modulo, $operacion, $mapaExtra = [])
    {
        $permisoRequerido = null;
        $operacionNormalizada = strtolower($operacion);

        if (array_key_exists($operacionNormalizada, $mapaExtra)) {
            $permisoRequerido = $mapaExtra[$operacionNormalizada]; 
        } else {
            // Asignamos las instancias del Enum, no sus valores
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
                http_response_code(403); 
                header('Content-Type: application/json');
                echo json_encode([
                    'estatus' => false, 
                    'mensaje' => 'No tiene permisos suficientes para realizar esta acción.'
                ]);
                exit;
            }
        }
    }


    public static function validarMetodoHTTP($metodosPermitidos = ['GET', 'POST'])
    {
        $metodoActual = $_SERVER['REQUEST_METHOD'];
        if (!in_array($metodoActual, $metodosPermitidos)) {
            http_response_code(405);
            header('Allow: ' . implode(', ', $metodosPermitidos));
            header('Content-Type: application/json');
            echo json_encode([
                'estatus' => false, 
                'mensaje' => "El método $metodoActual no está permitido para este recurso."
            ]);
            exit;
        }
    }

    /**
     * Verifica que la IP del cliente no esté bloqueada en la Lista Negra.
     * Actúa como Firewall (WAF) a nivel de aplicación.
     */
    public static function verificarAccesoRed()
    {
        $ipCliente = $_SERVER['REMOTE_ADDR'];
        
        $seguridad = new SeguridadIP();
        $seguridad->set_ip($ipCliente); // Usando el setter exigido
        
        $acceso = $seguridad->verificarListaAcceso();

        if (!$acceso['estatus']) {
            // Si es una petición AJAX/POST
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                http_response_code($acceso['codigo_http']);
                header('Content-Type: application/json');
                echo json_encode([
                    'estatus' => false, 
                    'mensaje' => $acceso['mensaje']
                ]);
                exit;
            } else {
                // Si es navegación normal GET
                http_response_code($acceso['codigo_http']);
                require_once "vista/error/403_vista.php"; 
                exit;
            }
        }
    }

    /**
     * Previene que un usuario autenticado sature el sistema con peticiones masivas (Anti-DoS).
     * Permite un máximo de 60 peticiones por minuto por usuario.
     */
    public static function verificarInundacion()
    {
        if (!isset($_SESSION["id_usuario"])) return; 

        $idUsuario = $_SESSION["id_usuario"];

        if (!isset($_SESSION['flood_control'])) {
            $_SESSION['flood_control'] = [];
        }

        if (!isset($_SESSION['flood_control'][$idUsuario])) {
            $_SESSION['flood_control'][$idUsuario] = ['peticiones' => 1, 'inicio' => time()];
        } else {
            $_SESSION['flood_control'][$idUsuario]['peticiones']++;
            $tiempoTranscurrido = time() - $_SESSION['flood_control'][$idUsuario]['inicio'];

            // USAMOS LAS CONSTANTES DE CLASE
            if ($tiempoTranscurrido < self::VENTANA_TIEMPO_SEGUNDOS) {
                if ($_SESSION['flood_control'][$idUsuario]['peticiones'] > self::MAX_PETICIONES_MINUTO) {
                    http_response_code(429); 
                    header('Content-Type: application/json');
                    echo json_encode(['estatus' => false, 'mensaje' => 'Se ha detectado actividad inusual. Ha superado el límite de operaciones por minuto. Por favor, espere.']);
                    exit;
                }
            } else {
                $_SESSION['flood_control'][$idUsuario] = ['peticiones' => 1, 'inicio' => time()];
            }
        }
    }
}