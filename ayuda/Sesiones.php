<?php

/**
 * Manejo de sesiones de usuario
 * Lo cambie asi para quitar el require_once y usar use en su lugar
 * aparte de que asi queda mas ordenado el codigo
 */

namespace haydee\ayuda;

use haydee\modelo\Usuario;
use haydee\modelo\AnioFiscal;
use haydee\modelo\RolesPermisos;
use haydee\modelo\Notificaciones;

class Sesiones
{
    public static function verificarSesion()
    {
        // Si ya hay sesión activa, no hacemos nada
        if (isset($_SESSION["usuario"])) {
            return true;
        }

        // Si no hay sesión pero hay cookies de "recuérdame"
        if (isset($_COOKIE['token_recuerdame']) && isset($_COOKIE['correo_usuario'])) {
            return self::procesarTokenRecuerdame();
        }

        // Si no hay sesión ni cookies, redirigir al login
        self::redirigirALogin();
        return false;
    }

    private static function procesarTokenRecuerdame()
    {
        $usuario_obj = new Usuario();
        $token_recuerdame = $_COOKIE['token_recuerdame'];
        $correo_usuario = $_COOKIE['correo_usuario'];
        
        $usuario_obj->set_correo($correo_usuario);
        $resultado = $usuario_obj->realizar_consulta('validar_token_recuerdame');

        // Verificar que la consulta fue exitosa
        if (!$resultado || !isset($resultado['token_recuerdame']) || !isset($resultado['duracion_token_recuerdame'])) {
            self::eliminarTokenYRedirigir($usuario_obj);
            return false;
        }

        // Verificar que el token no haya expirado
        $fecha_expiracion = strtotime($resultado['duracion_token_recuerdame']);
        if ($fecha_expiracion === false || $fecha_expiracion <= time()) {
            self::eliminarTokenYRedirigir($usuario_obj);
            return false;
        }

        // Verificar el token con password_verify
        if (!password_verify($token_recuerdame, $resultado['token_recuerdame'])) {
            self::eliminarTokenYRedirigir($usuario_obj);
            return false;
        }

        // Token válido, crear sesión
        return self::crearSesionDesdeToken($resultado, $usuario_obj);
    }

    private static function crearSesionDesdeToken($resultado, Usuario $usuario_obj)
    {
        $notificaciones_obj = new Notificaciones();
        $roles_permisos_obj = new RolesPermisos();

        // Iniciar sesión si no está activa
        if (!(session_status() == PHP_SESSION_ACTIVE)) {
            session_start();
        }

        // Validar que existan todos los datos necesarios
        if (!isset($resultado["id_usuario"]) || !isset($resultado["correo"]) || 
            !isset($resultado["nombre_usuario"]) || !isset($resultado["nombre_rol"]) || 
            !isset($resultado["id_rol"])) {
            
            self::eliminarTokenYRedirigir($usuario_obj);
            return false;
        }

        // Configurar datos de sesión
        $_SESSION["id_usuario"] = $resultado["id_usuario"];
        $_SESSION["usuario"] = $resultado["correo"];
        $_SESSION["nombre_completo"] = $resultado["nombre_usuario"];
        $_SESSION["rol"] = $resultado["nombre_rol"];

        // Configurar permisos
        $roles_permisos_obj->set_rol_id($resultado["id_rol"]);
        $_SESSION["permisos"] = $roles_permisos_obj->realizar_consulta('consultar_permisos_por_usuario');

        // Configurar notificaciones
        $notificaciones_obj->set_usuario_id($resultado["id_usuario"]);
        $_SESSION["notificaciones"] = $notificaciones_obj->realizar_consulta('consultar_notificaciones_usuario');

        // Verificar año fiscal
        $anio_fiscal_obj = new AnioFiscal();
        $anio_fiscal_obj->realizar_consulta("verificar_anio_fiscal");

        return true;
    }

    private static function eliminarTokenYRedirigir(Usuario $usuario_obj)
    {
        $usuario_obj->realizar_consulta("eliminar_token_recuerdame");
        
        // Eliminar cookies
        setcookie('token_recuerdame', '', time() - 3600, '/');
        setcookie('correo_usuario', '', time() - 3600, '/');
        
        self::redirigirALogin();
    }

    private static function redirigirALogin()
    {
        header("Location:?pagina=login_controlador.php&accion=inicio");
        exit;
    }

    // Método adicional útil: para cerrar sesión
    public static function cerrarSesion()
    {
        session_destroy();
        // También podrías eliminar las cookies de "recuérdame" aquí
        setcookie('token_recuerdame', '', time() - 3600, '/');
        setcookie('correo_usuario', '', time() - 3600, '/');
        
        self::redirigirALogin();
    }

    // Método para verificar si el usuario está logueado espoiler: nunca lo use
    public static function estaLogueado()
    {
        return isset($_SESSION["usuario"]);
    }

    public static function verificarPermiso($modulo, $accion)
    {
        // Primero aseguramos que haya sesión iniciada
        self::verificarSesion();

        // Si no tiene el permiso, lo mandamos al 403
        if (!self::tienePermiso($modulo, $accion)) {
            // Opción A: Redirección (si usas .htaccess con redirección)
            // header("Location: vista/403_vista.php");
            
            // Opción B: Carga directa (recomendada si usas rutas absolutas en estilos)
            // Esto mantiene la URL original pero muestra el error
            http_response_code(403);
            require_once "vista/error/403_vista.php"; 
            exit(); // ¡Importante! Matar el script aquí para que no cargue el resto del controlador
        }
        
        // Si tiene permiso, el código sigue ejecutándose normalmente
    }

    public static function tienePermiso($modulo, $accion)
    {
        // Si no hay permisos cargados en sesión, denegar
        if (!isset($_SESSION["permisos"]) || !is_array($_SESSION["permisos"])) {
            return false;
        }

        // Recorremos el array de permisos de la sesión
        foreach ($_SESSION["permisos"] as $permiso) {
            // Ajusta las claves ('id_modulo', 'nombre_permiso') según tu base de datos real
            if ($permiso["id_modulo"] == $modulo && $permiso["nombre_permiso"] == $accion) {
                return true;
            }
        }

        return false;
    }
}