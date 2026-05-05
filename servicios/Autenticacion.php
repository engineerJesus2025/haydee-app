<?php
namespace haydee\servicios;

use haydee\modelo\Usuario;
use haydee\modelo\Rol;
use haydee\modelo\Notificaciones;
use haydee\modelo\Bitacora;

class Autenticacion
{
    private $usuarioModel;

    public function __construct()
    {
        // Solo instanciamos lo estrictamente necesario para arrancar
        $this->usuarioModel = new Usuario();
    }

    /**
     * Intenta autenticar un usuario.
     */
    public function login($correo, $password, $recordar = false)
    {
        $this->usuarioModel->set_correo($correo);
        $this->usuarioModel->set_contra($password);
        $resultado = $this->usuarioModel->realizar_consulta('validar_usuario');
        
        if (!$resultado['estatus']) {
            return $resultado;
        }

        $usuario = $resultado['datos']; // Ya incluye 'nombre_rol' gracias al JOIN en Usuario.php

        // Registrar bitácora
        Bitacora::registrar(INICIAR_SESION, GESTIONAR_USUARIOS, $usuario['id_usuario']);

        // Gestión del token "Recuérdame"
        if ($recordar) {
            $token = bin2hex(random_bytes(32));
            $this->usuarioModel->set_id_usuario($usuario['id_usuario']);
            $this->usuarioModel->set_token($token);
            $this->usuarioModel->set_token_expiracion(date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)));
            $this->usuarioModel->set_token_tipo('RECORDAR_CONTRASENIA');

            $resToken = $this->usuarioModel->realizar_consulta('registrar_token');
            if ($resToken['estatus']) {
                $usuario['token_recordar'] = $token;
            }
        } else {
            $this->eliminarTokenRecordar($usuario['id_usuario']);
        }

        // Cargar permisos y notificaciones
        $permisos = $this->obtenerPermisos($usuario['id_rol']);
        $notificaciones = $this->obtenerNotificaciones($usuario['id_usuario']);

        // Normalizamos los datos (ahora es súper rápido, sin BD)
        $datosSesion = $this->normalizarDatosUsuario($usuario, $permisos, $notificaciones);

        return [
            'estatus' => true, 
            'mensaje' => 'Login exitoso', 
            'datos' => $datosSesion, 
            'token' => $usuario['token_recordar'] ?? null
        ];
    }

    /**
     * Valida un token de recordar sesión.
     */
    public function validarTokenRecuerdame($correo, $token)
    {
        $this->usuarioModel->set_correo($correo);
        $usuarioRes = $this->usuarioModel->realizar_consulta('existe_correo');
        
        if (!$usuarioRes['estatus']) {
            return $usuarioRes;
        }

        $usuarioDatos = $usuarioRes['datos']; // Ya incluye 'nombre_rol' por la mejora en _existe_correo

        $this->usuarioModel->set_id_usuario($usuarioDatos['id_usuario']);
        $this->usuarioModel->set_token($token);
        $this->usuarioModel->set_token_tipo('RECORDAR_CONTRASENIA');
        
        $tokenValido = $this->usuarioModel->realizar_consulta('validar_token');
        if (!$tokenValido['estatus']) {
            return $tokenValido;
        }

        $permisos = $this->obtenerPermisos($usuarioDatos['rol_id']);
        $notificaciones = $this->obtenerNotificaciones($usuarioDatos['id_usuario']);

        $datosSesion = $this->normalizarDatosUsuario($usuarioDatos, $permisos, $notificaciones);

        return ['estatus' => true, 'datos' => $datosSesion];
    }

    /**
     * Cierra la sesión: elimina token y registra en bitácora.
     */
    public function logout($usuarioId)
    {
        $this->eliminarTokenRecordar($usuarioId);
        Bitacora::registrar(CERRAR_SESION, GESTIONAR_USUARIOS, $usuarioId);
    }

    /**
     * Centraliza la eliminación del token.
     */
    private function eliminarTokenRecordar($usuarioId)
    {
        $this->usuarioModel->set_id_usuario($usuarioId);
        $this->usuarioModel->set_token_tipo('RECORDAR_CONTRASENIA');
        $this->usuarioModel->realizar_consulta('eliminar_token');
    }

    /**
     * Instancia el modelo Rol solo cuando se necesita y obtiene los permisos.
     */
    private function obtenerPermisos($rolId)
    {
        $rolModel = new Rol();
        $rolModel->set_id_rol($rolId);
        $resultado = $rolModel->realizar_consulta('consultar_permisos_asignados');
        $rolModel->cerrar(); // Cerramos conexión inmediatamente
        return $resultado['datos'] ?? [];
    }

    /**
     * Instancia el modelo Notificaciones solo cuando se necesita y obtiene las alertas.
     */
    private function obtenerNotificaciones($usuarioId)
    {
        $notificacionesModel = new Notificaciones();
        $notificacionesModel->set_usuario_id($usuarioId);
        $resultado = $notificacionesModel->realizar_consulta('consultar_mis_notificaciones');
        $notificacionesModel->cerrar(); // Cerramos conexión inmediatamente
        return $resultado['datos'] ?? [];
    }

    /**
     * Normaliza los datos del usuario para la sesión.
     */
    private function normalizarDatosUsuario($usuario, $permisos = [], $notificaciones = [])
    {
        return [
            'id_usuario'      => $usuario['id_usuario'],
            'correo'          => $usuario['correo'],
            'nombre_completo' => trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')),
            'rol'             => $usuario['nombre_rol'] ?? '', // Lo tomamos directo del arreglo
            'permisos'        => $permisos,
            'notificaciones'  => $notificaciones
        ];
    }

    /**
     * Cierra explícitamente las conexiones internas.
     */
    public function cerrar()
    {
        if ($this->usuarioModel !== null) {
            $this->usuarioModel->cerrar();
        }
    }
}