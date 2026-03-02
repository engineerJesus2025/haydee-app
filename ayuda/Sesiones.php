<?php

namespace haydee\ayuda;

use haydee\servicios\Autenticacion;
use haydee\modelo\Rol;
use haydee\modelo\Notificaciones;
use haydee\modelo\AnioFiscal;

class Sesiones
{
    /**
     * Inicia la sesión con los datos del usuario.
     * @param array $datosUsuario Debe contener id_usuario, correo, nombre_completo, rol, permisos, notificaciones.
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

        // Verificar año fiscal (proximamente proceso automático -_-)
        $anioFiscalModel = new AnioFiscal();
        try {
            $anioFiscalModel->realizar_consulta('verificar_anio_fiscal');
        } finally {
            $anioFiscalModel->cerrar();
        }
    }

    /**
     * Verifica si la sesión está iniciada; si no, intenta con cookies de recordar.
     * Redirige al login si no hay sesión ni cookies válidas.
     */
    public static function verificarSesion()
    {
        if (isset($_SESSION["usuario"])) {
            return true;
        }

        if (isset($_COOKIE['token']) && isset($_COOKIE['correo_usuario'])) {
            return self::procesarTokenRecuerdame();
        }

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
    public static function tienePermiso($moduloId, $permisoId)
    {
        if (!isset($_SESSION["permisos"]) || !is_array($_SESSION["permisos"])) {
            return false;
        }

        foreach ($_SESSION["permisos"] as $permiso) {
            if ($permiso["modulo_id"] == $moduloId && $permiso["permiso"] == $permisoId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica permiso y si no lo tiene, muestra error 403 y detiene la ejecución.
     */
    public static function verificarPermiso($moduloId, $permisoId)
    {
        self::verificarSesion();

        if (!self::tienePermiso($moduloId, $permisoId)) {
            http_response_code(403);
            require_once "vista/error/403_vista.php";
            exit;
        }
    }
}