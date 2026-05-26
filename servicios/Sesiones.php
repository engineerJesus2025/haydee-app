<?php

namespace haydee\servicios;
use haydee\enums\HttpCodigo;

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
    private const DIAS_RECORDAR_SESION = 30;
    private const SEGUNDOS_POR_DIA = 86400;
    private const EXPIRACION_PASADO = 3600; // Segundos a restar para destruir cookies
    // Memoria temporal para los permisos cuando la petición viene por JWT (API)
    public static $permisosAPI = null;
    /**
     * Inicia la sesion con los datos del usuario.
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

        // Verificar año fiscal y caja (proximamente proceso automatico -_-)
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
     * metodo centralizado para autorizar el acceso a un controlador.
     * Ejecuta secuencialmente: IP -> metodo HTTP -> sesion -> Permisos.
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
        // Solo verifica permisos si el controlador se los exige
        if ($modulo !== null && $permiso !== null) {
            self::verificarPermiso($modulo, $permiso);
        }
    }

    /**
     * Valida permisos, inundación y devuelve la identidad estructurada para las APIs REST (JWT).
     */
    public static function autorizarAccesoAPI(?Modulo $modulo = null, ?Accion $permiso = null, $metodos = ['GET', 'POST', 'PUT', 'DELETE'])
    {
        // Capa de Protocolo (Verifica si es GET, POST, etc.)
        self::validarMetodoHTTP($metodos);

        // Capa de Identidad (Ya lo hace GestorTrafico, pero lo dejo porsia...)
        $usuario = GestorTrafico::$usuarioLogueado;
        if (!$usuario) {
            http_response_code(HttpCodigo::NO_AUTORIZADO->value);
            echo json_encode(['estatus' => false, 'mensaje' => 'Identidad no verificada.']);
            exit;
        }

        // Capa de Autorización (Permisos)
        if ($modulo !== null && $permiso !== null) {
            if (!self::tienePermiso($modulo, $permiso)) {
                http_response_code(HttpCodigo::PROHIBIDO->value);
                echo json_encode(['estatus' => false, 'mensaje' => 'Acceso denegado: No tienes permisos para este módulo.']);
                exit;
            }
        }

        // Capa Anti-Inundación (Flood Control)
        // Como las APIs REST no usan cookies de sesión por defecto, iniciamos sesión 
        // temporalmente en PHP para que verificarInundacion() tenga dónde guardar su contador.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Temporizamos el ID del usuario en la sesión global para que verificarInundacion lo lea
        $idTemporal = $_SESSION["id_usuario"] ?? null;
        $_SESSION["id_usuario"] = $usuario['id_usuario'];
        
        self::verificarInundacion();
        
        // Restauramos el estado original para mantener la limpieza de la memoria
        if ($idTemporal === null) {
            unset($_SESSION["id_usuario"]);
        } else {
            $_SESSION["id_usuario"] = $idTemporal;
        }

        // Devolvemos el usuario que la API la use directamente
        return [
            'id_usuario'    => $usuario['id_usuario'],
            'correo'        => $usuario['correo'],
            'rol'           => strtolower($usuario['rol'] ?? ''),
            'esPropietario' => (strtolower($usuario['rol'] ?? '') === 'propietario')
        ];
    }

    /**
     * Verifica si la sesion esta iniciada; si no, intenta con cookies de recordar.
     * Redirige al login si no hay sesion ni cookies validas.
     */
    public static function verificarSesion()
    {
        if (self::estaLogueado()) {
            return true;
        }

        // Si hay cookies de "recuerdame", intentamos recuperar la sesion
        if (isset($_COOKIE['token']) && isset($_COOKIE['correo_usuario'])) {
            return self::procesarTokenRecuerdame();
        }

        // Si llegamos aqui, no hay sesion.
        // Si es una petición POST (asumimos AJAX), devolvemos 401.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            http_response_code(HttpCodigo::NO_AUTORIZADO->value);
            header('Content-Type: application/json');
            echo json_encode([
                'estatus' => false, 
                'mensaje' => 'Su sesion ha expirado. Por favor, inicie sesion de nuevo.'
            ]);
            exit;
        }

        // Si es una peticion normal (GET), redirigimos al login
        self::redirigirALogin();
        return false;
    }

    /**
     * Procesa el token de "recordar sesion" usando el servicio Autenticacion.
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
                // Token invalido, eliminar cookies
                setcookie('token', '', time() - self::EXPIRACION_PASADO, '/');
                setcookie('correo_usuario', '', time() - self::EXPIRACION_PASADO, '/');
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
     * Establece las cookies para recordar la sesion.
     */
    public static function recordar($correo, $token, $dias = self::DIAS_RECORDAR_SESION)
    {
        $expiracion = time() + ($dias * self::SEGUNDOS_POR_DIA);
        // Usar cookies seguras (HttpOnly, Secure en producción)
        $secure = defined('ENTORNO') && ENTORNO === 'produccion';
        setcookie('token', $token, $expiracion, '/', '', $secure, true);
        setcookie('correo_usuario', $correo, $expiracion, '/', '', $secure, true);
    }

    /**
     * Cierra la sesion actual (destruye sesion y elimina cookies).
     */
    public static function cerrarSesion()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        setcookie('token', '', time() - self::EXPIRACION_PASADO, '/');
        setcookie('correo_usuario', '', time() - self::EXPIRACION_PASADO, '/');

        self::redirigirALogin();
    }

    /**
     * Redirige al login y termina la ejecucion.
     */
    private static function redirigirALogin()
    {
        header("Location: ?pagina=login&accion=inicio");
        exit;
    }

    /**
     * Verifica si el usuario esta logueado.
     */
    public static function estaLogueado()
    {
        return isset($_SESSION["usuario"]);
    }

    /**
     * Verifica si el usuario tiene permiso para un modulo y accion.

     */
    public static function tienePermiso(Modulo $modulo, Accion $permiso)
    {
        // Buscamos de dónde sacar los permisos (Prioridad: API -> Web)
        $listaPermisos = self::$permisosAPI ?? $_SESSION["permisos"] ?? null;

        if (!$listaPermisos || !is_array($listaPermisos)) {
            return false;
        }

        foreach ($listaPermisos as $p) {
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
            http_response_code(HttpCodigo::PROHIBIDO->value);
            require_once "vista/error/403_vista.php";
            exit;
        }
    }

    /**
     * Obtiene todos los permisos de un modulo estructurados en un arreglo para la vista.

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
     * Verifica los permisos para operaciones que se ejecutan via AJAX.
     * Evalua el nombre de la operacion para requerir el permiso adecuado automaticamente.
     * Si no tiene permisos, devuelve un JSON con estatus false y termina la ejecuciin.
     */
    public static function verificarPermisoAccion(Modulo $modulo, $operacion, $mapaExtra = [])
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
                http_response_code(HttpCodigo::PROHIBIDO->value); 
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
            http_response_code(HttpCodigo::METODO_NO_PERMITIDO->value);
            header('Allow: ' . implode(', ', $metodosPermitidos));
            header('Content-Type: application/json');
            echo json_encode([
                'estatus' => false, 
                'mensaje' => "El metodo $metodoActual no esta permitido para este recurso."
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
            // Si es una peticion AJAX/POST
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                http_response_code($acceso['codigo_http']);
                header('Content-Type: application/json');
                echo json_encode([
                    'estatus' => false, 
                    'mensaje' => $acceso['mensaje']
                ]);
                exit;
            } else {
                // Si es navegacion normal GET
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
                    http_response_code(HttpCodigo::DEMASIADAS_PETICIONES->value); 
                    header('Content-Type: application/json');
                    echo json_encode(['estatus' => false, 'mensaje' => 'Se ha detectado actividad inusual. Ha superado el limite de operaciones por minuto. Por favor, espere.']);
                    exit;
                }
            } else {
                $_SESSION['flood_control'][$idUsuario] = ['peticiones' => 1, 'inicio' => time()];
            }
        }
    }
}
