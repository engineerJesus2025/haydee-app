<?php
namespace haydee\servicios;

use haydee\enums\TipoToken;
use haydee\enums\Accion;
use haydee\enums\Modulo;
use haydee\enums\HttpCodigo;
use haydee\modelo\Usuario;
use haydee\modelo\Rol;
use haydee\modelo\Notificaciones;
use haydee\modelo\Bitacora;
use Firebase\JWT\JWT;

class Autenticacion
{
    private const TIEMPO_RECORDAR_DIAS = 30;
    private const SEGUNDOS_POR_DIA = 86400; // 24 * 60 * 60
    private const JWT_ALGORITMO = 'HS256';
    private const JWT_TIEMPO_EXPIRACION = 3600;

    // CONSTANTES DE CONFIGURACIÓN 
    private const LONGITUD_BYTES_TOKEN = 32;
    private const JWT_ISSUER = 'haydee_api';
    private const JWT_AUDIENCE = 'haydee_app';

    private $usuarioModelo;

    public function __construct()
    {
        $this->usuarioModelo = new Usuario();
    }

    /**
     * Intenta autenticar un usuario.
     */
    public function login($correo, $password, $recordar = false, $generarJWT = false)
    {
        $this->usuarioModelo->set_correo($correo);
        
        // Obtener credenciales
        $resultadoUsuario = $this->usuarioModelo->realizar_consulta('obtener_credenciales_por_correo');
        
        if (!$resultadoUsuario['estatus']) {
            // Mitigación de Timing Attack (Fake hash processing)
            password_verify('password_falsa', '$2y$10$yhesusestuvoaquihashyhesushashyhesushashyhesushash');

            return ['estatus' => false, 'mensaje' => 'Credenciales incorrectas', 'codigo_http' => HttpCodigo::NO_AUTORIZADO->value];
        }

        $usuario = $resultadoUsuario['datos'];
        $this->usuarioModelo->set_id_usuario($usuario['id_usuario']);

        // Verificar si la cuenta esta bloqueada internamente (Anti-Brute Force de Cuenta)
        $estadoBloqueo = $this->usuarioModelo->realizar_consulta('verificar_bloqueo_cuenta');
        if (!$estadoBloqueo['estatus']) {
            return [
                'estatus' => false, 
                'mensaje' => $estadoBloqueo['mensaje'], 
                'codigo_http' => HttpCodigo::NO_AUTORIZADO->value
            ];
        }

        // Validar la contraseña en la capa de servicio
        if (!password_verify($password, $usuario['contrasenia'])) {
            // Registrar el fallo
            $this->usuarioModelo->realizar_consulta('registrar_intento_fallido');
            return ['estatus' => false, 'mensaje' => 'Credenciales incorrectas', 'codigo_http' => HttpCodigo::NO_AUTORIZADO->value];
        }

        // Limpiar historial penal de la cuenta
        $this->usuarioModelo->realizar_consulta('limpiar_intentos_fallidos');

        Bitacora::registrar(Accion::INICIAR_SESION, Modulo::GESTIONAR_USUARIOS, $usuario['id_usuario']);

        // Eliminamos el hash de la contraseña de $usuario por seguridad en memoria
        unset($usuario['contrasenia']);

        // Gestión del token persistente (Web o Móvil)
        if ($recordar || $generarJWT) {
            $token = bin2hex(random_bytes(self::LONGITUD_BYTES_TOKEN));
            $segundosExpiracion = self::TIEMPO_RECORDAR_DIAS * self::SEGUNDOS_POR_DIA;
            
            $tipoToken = $generarJWT ? TipoToken::REFRESH_MOVIL->value : TipoToken::RECUERDAME->value;
            
            $this->usuarioModelo->set_token($token);
            $this->usuarioModelo->set_token_expiracion(date('Y-m-d H:i:s', time() + $segundosExpiracion));
            $this->usuarioModelo->set_token_tipo($tipoToken);

            $resToken = $this->usuarioModelo->realizar_consulta('registrar_token');
            if ($resToken['estatus']) {
                $usuario['token_persistente'] = $token;
            }
        } else {
            // Login web normal: limpiamos sesión web anterior.
            $this->eliminarTokenPorTipo($usuario['id_usuario'], TipoToken::RECUERDAME->value);
        }

        $jwt = null;
        
        // JSON Web Token para la App Móvil
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
     * Valida un token de recordar sesion (WEB).
     */
    public function validarTokenRecuerdame($correo, $token)
    {
        $this->usuarioModelo->set_correo($correo);
        $usuarioRes = $this->usuarioModelo->realizar_consulta('existe_correo');
        
        if (!$usuarioRes['estatus']) {
            return $usuarioRes;
        }

        $usuarioDatos = $usuarioRes['datos'];

        $this->usuarioModelo->set_id_usuario($usuarioDatos['id_usuario']);
        $this->usuarioModelo->set_token($token);
        
        // Bloqueado estrictamente a formato WEB
        $this->usuarioModelo->set_token_tipo(TipoToken::RECUERDAME->value);
        
        $tokenValido = $this->usuarioModelo->realizar_consulta('validar_token');
        if (!$tokenValido['estatus']) {
            return $tokenValido;
        }

        $permisos = $this->obtenerPermisos($usuarioDatos['rol_id']);
        $notificaciones = $this->obtenerNotificaciones($usuarioDatos['id_usuario']);

        $datosSesion = $this->normalizarDatosUsuario($usuarioDatos, $permisos, $notificaciones);

        return ['estatus' => true, 'datos' => $datosSesion];
    }

    /**
     * Verifica un refresh token valido y genera un nuevo JWT. Usado para la app
     */
    public function renovarTokenJWT($idUsuario, $refreshToken)
    {
        $this->usuarioModelo->set_id_usuario($idUsuario);
        $this->usuarioModelo->set_token($refreshToken);
        $this->usuarioModelo->set_token_tipo(TipoToken::REFRESH_MOVIL->value); 

        $resultado = $this->usuarioModelo->realizar_consulta('validar_token_jwt');

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
        $this->usuarioModelo->set_id_usuario($usuarioId);
        $this->usuarioModelo->set_token_tipo($tipoToken);
        $this->usuarioModelo->realizar_consulta('eliminar_token');
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
        if ($this->usuarioModelo !== null) {
            $this->usuarioModelo->cerrar();
        }
    }
}