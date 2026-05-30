<?php
namespace haydee\servicios;

use haydee\modelo\Usuario;
use haydee\modelo\Rol;
use haydee\modelo\Notificaciones;
use haydee\modelo\Bitacora;
use haydee\enums\TipoToken;
use haydee\enums\Accion;
use haydee\enums\Modulo;
use Firebase\JWT\JWT;

class Autenticacion
{
    private const TIEMPO_RECORDAR_DIAS = 30;
    private const SEGUNDOS_POR_DIA = 86400; // 24 * 60 * 60
    private const JWT_ALGORITMO = 'HS256';
    private const JWT_TIEMPO_EXPIRACION = 3600;

    // ==================== CONSTANTES DE CONFIGURACIÓN ====================
    private const LONGITUD_BYTES_TOKEN = 32;
    private const JWT_ISSUER = 'haydee_api';
    private const JWT_AUDIENCE = 'haydee_app';

    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Intenta autenticar un usuario.
     */
    public function login($correo, $password, $recordar = false, $generarJWT = false)
    {
        $this->usuarioModel->set_correo($correo);
        $this->usuarioModel->set_contra($password);
        $resultado = $this->usuarioModel->realizar_consulta('validar_usuario');
        
        if (!$resultado['estatus']) {
            return $resultado;
        }

        $usuario = $resultado['datos'];

        Bitacora::registrar(Accion::INICIAR_SESION, Modulo::GESTIONAR_USUARIOS, $usuario['id_usuario']);

        // Gestión del token persistente (Web o Móvil)
        if ($recordar || $generarJWT) {
            $token = bin2hex(random_bytes(self::LONGITUD_BYTES_TOKEN));
            $segundosExpiracion = self::TIEMPO_RECORDAR_DIAS * self::SEGUNDOS_POR_DIA;
            
            $tipoToken = $generarJWT ? TipoToken::REFRESH_MOVIL->value : TipoToken::RECUERDAME->value;
            
            $this->usuarioModel->set_id_usuario($usuario['id_usuario']);
            $this->usuarioModel->set_token($token);
            $this->usuarioModel->set_token_expiracion(date('Y-m-d H:i:s', time() + $segundosExpiracion));
            $this->usuarioModel->set_token_tipo($tipoToken);

            $resToken = $this->usuarioModel->realizar_consulta('registrar_token');
            if ($resToken['estatus']) {
                $usuario['token_persistente'] = $token;
            }
        } else {
            // Si es un login web sin recordar, borramos solo la sesión web anterior, 
            // protegiendo la sesión móvil.
            $this->eliminarTokenPorTipo($usuario['id_usuario'], TipoToken::RECUERDAME->value);
        }

        $jwt = null;
        
        if ($generarJWT) {
            $tiempoEmision = time();
            $tiempoExpiracion = $tiempoEmision + self::JWT_TIEMPO_EXPIRACION;

            $payloadJWT = [
                'iss'  => self::JWT_ISSUER,
                'aud'  => self::JWT_AUDIENCE,
                'iat'  => $tiempoEmision,
                'exp'  => $tiempoExpiracion,
                'data' => [
                    'id_usuario' => $usuario['id_usuario'],
                    'correo'     => $usuario['correo'],
                    'rol_id'     => $usuario['id_rol'],
                    'rol'        => $usuario['nombre_rol']
                ]
            ];

            $jwt = JWT::encode($payloadJWT, JWT_SECRET, self::JWT_ALGORITMO);
        }

        $permisos = $this->obtenerPermisos($usuario['id_rol']);
        $notificaciones = $this->obtenerNotificaciones($usuario['id_usuario']);

        $datosSesion = $this->normalizarDatosUsuario($usuario, $permisos, $notificaciones);

        return [
            'estatus' => true, 
            'mensaje' => 'Login exitoso', 
            'datos' => $datosSesion, 
            'refresh_token' => $usuario['token_persistente'] ?? null, 
            'token_jwt' => $jwt 
        ];
    }

    /**
     * Valida un token de recordar sesión (Exclusivo WEB).
     */
    public function validarTokenRecuerdame($correo, $token)
    {
        $this->usuarioModel->set_correo($correo);
        $usuarioRes = $this->usuarioModel->realizar_consulta('existe_correo');
        
        if (!$usuarioRes['estatus']) {
            return $usuarioRes;
        }

        $usuarioDatos = $usuarioRes['datos'];

        $this->usuarioModel->set_id_usuario($usuarioDatos['id_usuario']);
        $this->usuarioModel->set_token($token);
        
        // Bloqueado estrictamente a formato WEB
        $this->usuarioModel->set_token_tipo(TipoToken::RECUERDAME->value);
        
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
     * Verifica un refresh token válido y genera un nuevo JWT. Usado para la app
     */
    public function renovarTokenJWT($idUsuario, $refreshToken)
    {
        $this->usuarioModel->set_id_usuario($idUsuario);
        $this->usuarioModel->set_token($refreshToken);
        $this->usuarioModel->set_token_tipo(TipoToken::REFRESH_MOVIL->value); 

        $resultado = $this->usuarioModel->realizar_consulta('validar_token_jwt');

        if (!$resultado['estatus']) {
            return $resultado;
        }

        $usuario = $resultado['datos'];

        $tiempoEmision = time();
        $tiempoExpiracion = $tiempoEmision + self::JWT_TIEMPO_EXPIRACION;

        $payload = [
            'iss'  => self::JWT_ISSUER,
            'aud'  => self::JWT_AUDIENCE,
            'iat'  => $tiempoEmision,
            'exp'  => $tiempoExpiracion,
            'data' => [
                'id_usuario' => $usuario['id_usuario'],
                'correo'     => $usuario['correo'],
                'rol_id'     => $usuario['rol_id'],
                'rol'        => $usuario['nombre_rol']
            ]
        ];

        $nuevoJwt = JWT::encode($payload, JWT_SECRET, self::JWT_ALGORITMO);

        return [
            'estatus' => true,
            'nuevo_token_jwt' => $nuevoJwt
        ];
    }

    /**
     * Cierra la sesión: elimina token y registra en bitácora.
     */
    public function logout($usuarioId, $esMovil = false)
    {
        // Se selecciona dinámicamente qué token destruir
        $tipoToken = $esMovil ? TipoToken::REFRESH_MOVIL->value : TipoToken::RECUERDAME->value;
        
        $this->eliminarTokenPorTipo($usuarioId, $tipoToken);
        Bitacora::registrar(Accion::CERRAR_SESION, Modulo::GESTIONAR_USUARIOS, $usuarioId);
    }

    /**
     * Centraliza la eliminación del token.
     */
    private function eliminarTokenPorTipo($usuarioId, $tipoToken)
    {
        $this->usuarioModel->set_id_usuario($usuarioId);
        $this->usuarioModel->set_token_tipo($tipoToken);
        $this->usuarioModel->realizar_consulta('eliminar_token');
    }

    private function obtenerPermisos($rolId)
    {
        $rolModel = new Rol();
        $rolModel->set_id_rol($rolId);
        $resultado = $rolModel->realizar_consulta('consultar_permisos_asignados');
        $rolModel->cerrar();
        return $resultado['datos'] ?? [];
    }

    private function obtenerNotificaciones($usuarioId)
    {
        $notificacionesModel = new Notificaciones();
        $notificacionesModel->set_usuario_id($usuarioId);
        $resultado = $notificacionesModel->realizar_consulta('consultar_mis_notificaciones');
        $notificacionesModel->cerrar(); 
        return $resultado['datos'] ?? [];
    }

    private function normalizarDatosUsuario($usuario, $permisos = [], $notificaciones = [])
    {
        return [
            'id_usuario'      => $usuario['id_usuario'],
            'correo'          => $usuario['correo'],
            'nombre_completo' => trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')),
            'rol'             => $usuario['nombre_rol'] ?? '', 
            'permisos'        => $permisos,
            'notificaciones'  => $notificaciones
        ];
    }

    public function cerrar()
    {
        if ($this->usuarioModel !== null) {
            $this->usuarioModel->cerrar();
        }
    }
}