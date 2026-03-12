<?php
namespace haydee\servicios;

use haydee\modelo\Usuario;
use haydee\modelo\Rol;
use haydee\modelo\Notificaciones;
use haydee\modelo\CajaChica;
use haydee\modelo\AnioFiscal;
use haydee\modelo\Bitacora;

class Autenticacion
{
    private $usuarioModel;
    private $rolModel;
    private $notificacionesModel;
    private $cajaModel;
    private $anioFiscalModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->rolModel = new Rol();
        $this->notificacionesModel = new Notificaciones();
        $this->cajaModel = new CajaChica();
        $this->anioFiscalModel = new AnioFiscal();
    }

    /**
     * Intenta autenticar un usuario.
     * @param string $correo
     * @param string $password
     * @param bool $recordar
     * @return array ['estatus' => bool, 'mensaje' => string, 'datos' => array|null]
     *         'datos' contiene la información del usuario lista para la sesión.
     */
    public function login($correo, $password, $recordar = false)
    {
        $this->usuarioModel->set_correo($correo);
        $this->usuarioModel->set_contra($password);
        $resultado = $this->usuarioModel->realizar_consulta('validar_usuario');
        if (!$resultado['estatus']) {
            return $resultado;
        }

        $usuario = $resultado['datos']; // Sin hash

        // Registrar bitácora de inicio de sesión
        Bitacora::registrar(INICIAR_SESION, GESTIONAR_USUARIOS, $usuario['id_usuario']);

        // Si recordar, generar token de larga duración
        if ($recordar) {
            $token = bin2hex(random_bytes(32));
            $expiracion = time() + (30 * 24 * 60 * 60); // 30 días
            $fechaExpiracion = date('Y-m-d H:i:s', $expiracion);

            $this->usuarioModel->set_id_usuario($usuario['id_usuario']);
            $this->usuarioModel->set_token($token);
            $this->usuarioModel->set_token_expiracion($fechaExpiracion);
            $this->usuarioModel->set_token_tipo('RECORDAR_CONTRASENIA');

            $resToken = $this->usuarioModel->realizar_consulta('registrar_token');
            
            if ($resToken['estatus']) {
                // Devolvemos el token para que el controlador lo guarde en cookie
                $usuario['token_recordar'] = $token;
            }
        } else {
            // Eliminar token existente
            $this->usuarioModel->set_id_usuario($usuario['id_usuario']);
            $this->usuarioModel->set_token_tipo('RECORDAR_CONTRASENIA');
            $this->usuarioModel->realizar_consulta('eliminar_token');
        }

        // Obtener permisos del rol
        $this->rolModel->set_id_rol($usuario['id_rol']);
        $permisos = $this->rolModel->realizar_consulta('consultar_permisos_asignados');
        $usuario['permisos'] = $permisos['datos'] ?? [];

        // Obtener notificaciones no leídas
        $this->notificacionesModel->set_usuario_id($usuario['id_usuario']);
        $notificaciones = $this->notificacionesModel->realizar_consulta('consultar_mis_notificaciones');
        $usuario['notificaciones'] = $notificaciones['datos'] ?? [];

        // Ejecutar procesos automáticos
        // $this->cajaModel->realizar_consulta('verificar_caja_mes');
        // $this->anioFiscalModel->realizar_consulta('verificar_anio_fiscal');


        // Devolver datos para la sesión (sin el token si no se usó)
        $datosSesion = $this->normalizarDatosUsuario($usuario, $usuario['permisos'], $usuario['notificaciones']);

        return ['estatus' => true, 'mensaje' => 'Login exitoso', 'datos' => $datosSesion, 'token' => $usuario['token_recordar'] ?? null];
    }

    /**
     * Valida un token de recordar sesión.
     * @param string $correo
     * @param string $token
     * @return array ['estatus' => bool, 'mensaje' => string, 'datos' => array|null]
     */
    public function validarTokenRecuerdame($correo, $token)
    {
        $this->usuarioModel->set_correo($correo);
        $usuario = $this->usuarioModel->realizar_consulta('existe_correo');
        if (!$usuario['estatus']) {
            return $usuario;
        }

        $this->usuarioModel->set_id_usuario($usuario['datos']['id_usuario']);
        $this->usuarioModel->set_token($token);
        $this->usuarioModel->set_token_tipo('RECORDAR_CONTRASENIA');
        $tokenValido = $this->usuarioModel->realizar_consulta('validar_token');
        if (!$tokenValido['estatus']) {
            return $tokenValido;
        }

        // Obtener permisos y notificaciones
        $rolModel = new Rol();
        $rolModel->set_id_rol($usuario['datos']['rol_id']);
        $permisos = $rolModel->realizar_consulta('consultar_permisos_asignados');
        $usuario['datos']['permisos'] = $permisos['datos'] ?? [];

        $notificacionesModel = new Notificaciones();
        $notificacionesModel->set_usuario_id($usuario['datos']['id_usuario']);
        $notificaciones = $notificacionesModel->realizar_consulta('consultar_mis_notificaciones');
        $usuario['datos']['notificaciones'] = $notificaciones['datos'] ?? [];

        // Normalizar datos
        $datosSesion = $this->normalizarDatosUsuario(
            $usuario['datos'],
            $usuario['datos']['permisos'],
            $usuario['datos']['notificaciones']
        );

        return ['estatus' => true, 'datos' => $datosSesion];
    }

    /**
     * Cierra la sesión: elimina token de recordar (si existe) y limpia cookies (pero no la sesión).
     * @param int $usuarioId
     */
    public function logout($usuarioId)
    {
        $this->usuarioModel->set_id_usuario($usuarioId);
        $this->usuarioModel->set_token_tipo('RECORDAR_CONTRASENIA');
        $this->usuarioModel->realizar_consulta('eliminar_token');

        Bitacora::registrar(CERRAR_SESION, GESTIONAR_USUARIOS, $usuarioId);
    }

    /**
     * Normaliza los datos del usuario para la sesión.
     * @param array $usuario Con al menos: id_usuario, nombre, apellido, correo, rol_id
     * @param array $permisos (opcional) Lista de permisos
     * @param array $notificaciones (opcional) Lista de notificaciones
     * @return array
     */
    private function normalizarDatosUsuario($usuario, $permisos = [], $notificaciones = [])
    {
        // Obtener nombre del rol
        $rolModel = new Rol();
        $rolModel->set_id_rol($usuario['rol_id'] ?? $usuario['id_rol']);
        $rolInfo = $rolModel->realizar_consulta('consultar_rol');
        $nombreRol = $rolInfo['estatus'] ? ($rolInfo['datos']['nombre'] ?? '') : '';

        return [
            'id_usuario'   => $usuario['id_usuario'],
            'correo'       => $usuario['correo'],
            'nombre_completo' => trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')),
            'rol'          => $nombreRol,
            'permisos'     => $permisos,
            'notificaciones' => $notificaciones
        ];
    }

    /**
     * Cierra explícitamente las conexiones de todos los modelos internos.
     */
    public function cerrar()
    {
        $modelos = [
            $this->usuarioModel,
            $this->rolModel,
            $this->notificacionesModel,
            $this->cajaModel,
            $this->anioFiscalModel
        ];
        foreach ($modelos as $modelo) {
            if ($modelo !== null) {
                $modelo->cerrar();
            }
        }
    }
}