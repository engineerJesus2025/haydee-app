<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\enums\Accion;
use haydee\enums\TipoToken;
use haydee\enums\TipoBaseDatos;
use haydee\enums\HttpCodigo;

class Usuario extends Conexion
{
    private const MAX_INTENTOS_LOGIN = 3;
    private const TIEMPO_BLOQUEO_MINUTOS = 15;
    private const MARGEN_EXPIRACION_TOKEN_MINUTOS = 10;
    
    // PROPIEDADES (Usuario)
    private $id_usuario;
    private $apellido;
    private $nombre;
    private $correo;
    private $contra;
    private $rol_id;
    private $activo;

    // PROPIEDADES (Token de Seguridad)
    private $token;
    private $token_expiracion;
    private $token_tipo; // 'Recuperacion', 'Recuerdame', etc.

    // VALIDACIONES CENTRALIZADAS
    public static function obtenerReglas($operacion) {
    // Reglas base de cada campo
    $reglasCampos = [
        'id_usuario' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'usuarios', 'campo' => 'id_usuario']
        ],
        'apellido' => [
            'regex' => '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,20}$/'
        ],
        'nombre' => [
            'regex' => '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,20}$/'
        ],
        'correo' => [
            'regex' => '/^[-A-Za-z0-9_.]{3,35}@[A-Za-z0-9]{3,10}\.[A-Za-z]{2,3}$/',
            'unique' => ['tabla' => 'usuarios', 'campo' => 'correo', 'exclude_field' => 'id_usuario']
        ],
        'contra' => [
            'regex' => '/^[A-Za-z0-9_.+*$#%&@-]{5,100}$/'
        ],
        'rol_id' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'roles', 'campo' => 'id_rol']
        ],
        'token' => [
            'regex' => '/^[a-f0-9]{64}$/'
        ],
        'token_expiracion' => [
            'regex' => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'
        ],
        'token_tipo' => [
            'regex' => '/^[A-Za-z]+$/'
        ]
    ];

    $configPorOperacion = [
        'entrar' => [
            'metodo_http' => ['POST'],
            'campos' => ['correo', 'contra']
        ],
        'recuperar_contrasenia' => [
            'metodo_http' => ['POST'],
            'campos' => ['correo']
        ],
        'guardar_contrasenia' => [
            'metodo_http' => ['POST'],
            'campos' => ['contra']
        ],
        'registrar_usuario' => [
            'metodo_http' => ['POST'],
            'campos' => ['nombre', 'apellido', 'correo', 'contra', 'rol_id']
        ],
        'modificar_usuario' => [
            'metodo_http' => ['PUT', 'POST'],
            'campos' => ['id_usuario', 'nombre', 'apellido', 'correo', 'rol_id']
        ],
        'eliminar_usuario' => [
            'metodo_http' => ['DELETE', 'POST'],
            'campos' => ['id_usuario']
        ],
        'consultar' => [
            'metodo_http' => ['GET'],
            'campos' => []   
        ],
        'consultar_usuario' => [
            'metodo_http' => ['GET'],
            'campos' => ['id_usuario']
        ],
        'consultar_perfil_usuario' => [
            'metodo_http' => ['GET'],
            'campos' => []   
        ],
        'modificar_perfil' => [
            'metodo_http' => ['POST', 'PUT'],
            'campos' => ['id_usuario', 'nombre', 'apellido', 'correo']
        ],
        'cambiar_contrasenia' => [
            'metodo_http' => ['POST'],
            'campos' => ['id_usuario', 'contra']
        ],
        'restablecer_contrasenia' => [
            'metodo_http' => ['POST'],
            'campos' => ['id_usuario']
        ],
        'existe_correo' => [
            'metodo_http' => ['GET', 'POST'],
            'campos' => ['correo']
        ],
        'registrar_token' => [
            'metodo_http' => ['POST'],
            'campos' => ['id_usuario', 'token', 'token_tipo', 'token_expiracion']
        ],
        'validar_token' => [
            'metodo_http' => ['POST'],
            'campos' => ['token', 'token_tipo']
        ],
        'obtener_token' => [
            'metodo_http' => ['GET', 'POST'],
            'campos' => ['id_usuario', 'token_tipo']
        ],
        'eliminar_token' => [
            'metodo_http' => ['DELETE', 'POST'],
            'campos' => ['id_usuario', 'token_tipo']
        ],
        'refrescar_token' => [
            'metodo_http' => ['POST'],
            'campos' => ['id_usuario', 'token']
        ]
    ];

    if (isset($configPorOperacion[$operacion])) {
        $config = $configPorOperacion[$operacion];
        $reglasFiltradas = array_intersect_key($reglasCampos, array_flip($config['campos']));
        $reglasFiltradas['__metodo_http_permitido__'] = $config['metodo_http'];
        return $reglasFiltradas;
    }

    // Si la operación no está definida, se devuelve array vacío (sin reglas)
    return [];
}

    // GETTERS Y SETTERS
    public function set_id_usuario($id) { $this->id_usuario = $id; }
    public function get_id_usuario() { return $this->id_usuario; }
    public function set_apellido($a) { $this->apellido = $a; }
    public function get_apellido() { return $this->apellido; }
    public function set_nombre($n) { $this->nombre = $n; }
    public function get_nombre() { return $this->nombre; }
    public function set_correo($c) { $this->correo = $c; }
    public function get_correo() { return $this->correo; }
    public function set_contra($c) { $this->contra = $c; }
    public function get_contra() { return $this->contra; }
    public function set_rol_id($r) { $this->rol_id = $r; }
    public function get_rol_id() { return $this->rol_id; }
    public function set_activo($a) { $this->activo = $a; }
    public function get_activo() { return $this->activo; }

    // Token Setters
    public function set_token($t) { $this->token = $t; }
    public function get_token() { return $this->token; }
    public function set_token_expiracion($e) { $this->token_expiracion = $e; }
    public function get_token_expiracion() { return $this->token_expiracion; }
    public function set_token_tipo($t) { $this->token_tipo = $t; }
    public function get_token_tipo() { return $this->token_tipo; }

    // ENRUTADOR
    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            return ['estatus' => false, 'mensaje' => "La acción '$accion' no está implementada."];
        }

        try {
            return $this->$metodo();
        } catch (\Exception $e) {
            error_log("Error en realizar_consulta ($accion): " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error interno: ' . $e->getMessage()];
        }
    }
    
    // SE USA EN EL SERVICIO AUTENTICACION
    private function _validar_usuario()
    {
        try {
            $db = $this->get_conex(TipoBaseDatos::SEGURIDAD);
            $db->beginTransaction();

            $sqlUsuario = "SELECT u.id_usuario, u.correo, u.nombre, u.apellido, u.contrasenia, 
                           r.id_rol, r.nombre as nombre_rol
                    FROM usuarios u 
                    INNER JOIN roles r ON u.rol_id = r.id_rol 
                    WHERE u.correo = :correo AND u.activo = 1";
            
            $stmtU = $db->prepare($sqlUsuario);
            $stmtU->execute([':correo' => $this->correo]);
            $datos = $stmtU->fetch(PDO::FETCH_ASSOC);

            if (!$datos) {
                $db->rollBack();
                return ['estatus' => false, 'mensaje' => 'Credenciales incorrectas'];
            }

            $id_usuario = $datos['id_usuario'];

            $sqlIntentos = "SELECT intentos, TIMESTAMPDIFF(MINUTE, ultimo_intento, NOW()) as minutos_transcurridos 
                            FROM intentos_login WHERE usuario_id = :id FOR UPDATE";
            $stmtI = $db->prepare($sqlIntentos);
            $stmtI->execute([':id' => $id_usuario]);
            $registroIntento = $stmtI->fetch(PDO::FETCH_ASSOC);

            if ($registroIntento) {
                $intentos = (int)$registroIntento['intentos'];
                $minutos_transcurridos = (int)$registroIntento['minutos_transcurridos'];

                // USAMOS LAS CONSTANTES DE CLASE AQUÍ
                if ($intentos >= self::MAX_INTENTOS_LOGIN && $minutos_transcurridos < self::TIEMPO_BLOQUEO_MINUTOS) {
                    $tiempo_restante = self::TIEMPO_BLOQUEO_MINUTOS - $minutos_transcurridos;
                    $db->rollBack();
                    return [
                        'estatus' => false, 
                        'mensaje' => "Cuenta bloqueada temporalmente por seguridad. Intente en $tiempo_restante min.", 
                        'codigo_http' => HttpCodigo::DEMASIADAS_PETICIONES->value
                    ];
                }

                if ($intentos >= self::MAX_INTENTOS_LOGIN && $minutos_transcurridos >= self::TIEMPO_BLOQUEO_MINUTOS) {
                    $db->prepare("DELETE FROM intentos_login WHERE usuario_id = :id")
                       ->execute([':id' => $id_usuario]);
                }
            }

            // Validar la contraseña
            if (password_verify($this->contra, $datos['contrasenia'])) {
                
                // Limpiamos intentos y confirmamos cambios
                $db->prepare("DELETE FROM intentos_login WHERE usuario_id = :id")
                   ->execute([':id' => $id_usuario]);

                $db->commit(); 

                unset($datos['contrasenia']);
                return ['estatus' => true, 'datos' => $datos];
                
            } else {
                
                // Fallo: Registramos el intento fallido
                $sqlFallo = "INSERT INTO intentos_login (usuario_id, intentos, ultimo_intento) 
                             VALUES (:id, 1, NOW()) 
                             ON DUPLICATE KEY UPDATE intentos = intentos + 1, ultimo_intento = NOW()";
                $db->prepare($sqlFallo)->execute([':id' => $id_usuario]);

                $db->commit(); 
                return ['estatus' => false, 'mensaje' => 'Credenciales incorrectas', 'codigo_http' => HttpCodigo::BAD_REQUEST->value];

            }

        } catch (PDOException $e) {
            // Si algo falla en cualquier punto, revertimos todo para evitar datos inconsistentes
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error en _validar_usuario con transacción: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error de seguridad en el sistema'];
        }
    }



    // SE USA EN EL MODULO
    private function _consultar()
    {
        $sql = "SELECT u.id_usuario, u.apellido, u.nombre, u.correo, r.nombre as nombre_rol, u.activo
                FROM usuarios u 
                INNER JOIN roles r ON u.rol_id = r.id_rol 
                WHERE u.activo = 1 
                ORDER BY u.id_usuario";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute();
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar usuarios'];
        }
    }

    // SE USA EN EL MODULO
    private function _consultar_usuario()
    {
        $sql = "SELECT u.*, r.nombre as nombre_rol 
                FROM usuarios u 
                INNER JOIN roles r ON u.rol_id = r.id_rol 
                WHERE u.id_usuario = :id AND u.activo = 1";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([':id' => $this->id_usuario]);
            $dato = $stmt->fetch(PDO::FETCH_ASSOC);

            return $dato ? ['estatus' => true, 'datos' => $dato] : ['estatus' => false, 'mensaje' => 'Usuario no encontrado'];
        } catch (PDOException $e) {
            error_log("Error en _consultar_usuario: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar usuario'];
        }
    }

    // SE USA EN EL MODULO (perfil)
    private function _consultar_perfil_usuario()
    {
        $accionLogin = Accion::INICIAR_SESION->value;

        $sql = "SELECT 
                    u.nombre as nombre_usuario, 
                    u.apellido, 
                    u.correo, 
                    r.nombre as nombre_rol,
                    COALESCE(
                        (SELECT b.fecha_hora 
                         FROM bitacora b 
                         WHERE b.usuario_id = u.id_usuario 
                         AND b.accion = :accion 
                         ORDER BY b.fecha_hora DESC 
                         LIMIT 1 OFFSET 1),
                        (SELECT b.fecha_hora 
                         FROM bitacora b 
                         WHERE b.usuario_id = u.id_usuario 
                         AND b.accion = :accion2 
                         ORDER BY b.fecha_hora DESC 
                         LIMIT 1)
                    ) as ultima_vez
                FROM usuarios u 
                INNER JOIN roles r ON u.rol_id = r.id_rol 
                WHERE u.id_usuario = :usuario";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([
                ':accion' => $accionLogin,
                ':accion2' => $accionLogin,
                ':usuario' => $this->id_usuario
            ]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$datos) {
                return ['estatus' => false, 'mensaje' => 'Perfil no encontrado'];
            }

            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_perfil_usuario: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener el perfil'];
        }
    }

    // SE USA EN EL SERVICIO AUTENTICACION (repasar)    
    private function _existe_correo()
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.rol_id, r.nombre as nombre_rol 
                FROM usuarios u 
                INNER JOIN roles r ON u.rol_id = r.id_rol 
                WHERE u.correo = :correo AND u.activo = 1";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([':correo' => $this->correo]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$datos) {
                return ['estatus' => false, 'mensaje' => 'Correo no encontrado'];
            }
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _existe_correo: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al verificar correo'];
        }
    }

    // SE USA EN EL MODULO
    private function _registrar_usuario()
    {
        $hash = password_hash($this->contra, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO usuarios (apellido, nombre, correo, contrasenia, rol_id) 
                    VALUES (:ape, :nom, :cor, :con, :rol)";
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([
                ':ape' => $this->apellido,
                ':nom' => $this->nombre,
                ':cor' => $this->correo,
                ':con' => $hash,
                ':rol' => $this->rol_id
            ]);
            $lastId = $this->get_conex(TipoBaseDatos::SEGURIDAD)->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Usuario registrado', 'lastId' => $lastId];
        } catch (PDOException $e) {
            error_log("Error en _registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar: ' . $e->getMessage()];
        }
    }

    // SE USA EN EL MODULO
    private function _modificar_usuario()
    {
        // Construir consulta dinámica
        $sql = "UPDATE usuarios SET apellido=:a, nombre=:n, correo=:c, rol_id=:r WHERE id_usuario=:id";
        $params = [
            ':a' => $this->apellido,
            ':n' => $this->nombre,
            ':c' => $this->correo,
            ':r' => $this->rol_id,
            ':id' => $this->id_usuario
        ];

        if (!empty($this->contra)) {
            $sql = "UPDATE usuarios SET apellido=:a, nombre=:n, correo=:c, rol_id=:r, contrasenia=:p WHERE id_usuario=:id";
            $params[':p'] = password_hash($this->contra, PASSWORD_DEFAULT);
        }

        try {
            $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql)->execute($params);
            return ['estatus' => true, 'mensaje' => 'Usuario actualizado'];
        } catch (PDOException $e) {
            error_log("Error en _modificar_usuario: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al modificar: ' . $e->getMessage()];
        }
    }

    // SE USA EN EL MODULO (perfil)
    private function _modificar_perfil()
    { 
        $sql = "UPDATE usuarios SET apellido = :ape, nombre = :nom, correo = :cor 
                WHERE id_usuario = :id";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $resultado = $stmt->execute([
                ':ape' => $this->apellido,
                ':nom' => $this->nombre,
                ':cor' => $this->correo,
                ':id'  => $this->id_usuario
            ]);

            return [
                'estatus' => $resultado, 
                'mensaje' => $resultado ? 'Perfil actualizado correctamente' : 'No se realizaron cambios'
            ];
        } catch (PDOException $e) {
            error_log("Error en _modificar_perfil: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar el perfil'];
        }
    }

    // SE USA EN EL MODULO
    private function _eliminar_usuario()
    {
        try {
            $sql = "UPDATE usuarios SET activo = 0 WHERE id_usuario = :id";
            $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql)->execute([':id' => $this->id_usuario]);
            return ['estatus' => true, 'mensaje' => 'Usuario eliminado'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar_usuario: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar'];
        }
    }

    // SE USA EN EL MODULO (perfil)
    private function _cambiar_contrasenia()
    {
        $hash = password_hash($this->contra, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET contrasenia = :con WHERE correo = :cor AND activo = 1";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([':con' => $hash, ':cor' => $this->correo]);
            if ($stmt->rowCount() == 0) return ['estatus' => false, 'mensaje' => 'Correo no encontrado o inactivo'];
            return ['estatus' => true, 'mensaje' => 'Contraseña actualizada'];
        } catch (PDOException $e) {
            error_log("Error en _cambiar_contrasenia: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error en la base de datos'];
        }
    }
    
    // SE USA EN EL SERVICIO AUTENTICACION
    private function _registrar_token()
    {
        try {
            $sql = "CALL sp_insertar_token(:uid, :tipo, :token, :exp)";
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([
                ':uid' => $this->id_usuario,
                ':tipo' => $this->token_tipo,
                ':token' => $this->token,
                ':exp' => $this->token_expiracion
            ]);
            return ['estatus' => true, 'mensaje' => 'Token generado'];
        } catch (PDOException $e) {
            error_log("Error en _registrar_token: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al generar token: ' . $e->getMessage()];
        }
    }

    // SE USA EN EL SERVICIO AUTENTICACION
    private function _validar_token()
    {
        if (empty($this->token) || empty($this->token_tipo)) {
            return ['estatus' => false, 'mensaje' => 'Datos de validación incompletos'];
        }

        $margen = self::MARGEN_EXPIRACION_TOKEN_MINUTOS;

        $sql = "SELECT u.id_usuario, u.correo, u.nombre 
                FROM tokens_seguridad t
                INNER JOIN usuarios u ON t.usuario_id = u.id_usuario
                WHERE t.token = :token 
                AND t.tipo = :tipo 
                AND DATE_ADD(t.fecha_expiracion, INTERVAL $margen MINUTE) > NOW()";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([
                ':token' => $this->token, 
                ':tipo' => $this->token_tipo 
            ]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$datos) return ['estatus' => false, 'mensaje' => 'El enlace ha expirado o es inválido'];
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _validar_token: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error de seguridad al validar acceso'];
        }
    }

    // SE USA EN EL SERVICIO AUTENTICACION
    private function _eliminar_token()
    {
        if (empty($this->id_usuario) || empty($this->token_tipo)) {
            return ['estatus' => false, 'mensaje' => 'Faltan datos para eliminar token'];
        }

        $sql = "DELETE FROM tokens_seguridad WHERE usuario_id = :uid AND tipo = :tipo";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([':uid' => $this->id_usuario, ':tipo' => $this->token_tipo]);
            return ['estatus' => true, 'mensaje' => 'Token eliminado'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar_token: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar token'];
        }
    }

    // SE USA PARA VERIFICAR HASHES DE OTP (En Recuperacion.php)
    private function _obtener_token()
    {
        if (empty($this->id_usuario) || empty($this->token_tipo)) {
            return ['estatus' => false, 'mensaje' => 'Datos incompletos para buscar token'];
        }

        // Buscamos el token más reciente que aún no haya superado su fecha de expiración
        $sql = "SELECT token, fecha_expiracion 
                FROM tokens_seguridad 
                WHERE usuario_id = :uid 
                AND tipo = :tipo 
                AND fecha_expiracion > NOW()
                ORDER BY fecha_expiracion DESC LIMIT 1";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([
                ':uid' => $this->id_usuario, 
                ':tipo' => $this->token_tipo
            ]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$datos) return ['estatus' => false, 'mensaje' => 'No hay códigos válidos o han expirado'];
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _obtener_token: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener el código de seguridad'];
        }
    }

    /**
     * Valida el token de refresco y extrae los datos del usuario en un solo flujo transaccional. SE USA EN AUTENTICACION
     */
    private function _validar_token_jwt()
    {
        $usuarioId = $this->id_usuario;
        $token = $this->token;
        $tipoToken = $this->token_tipo;

        if (empty($usuarioId) || empty($token) || empty($tipoToken)) {
            return ['estatus' => false, 'mensaje' => 'Datos insuficientes para la validación.'];
        }

        try {
            $conexSeguridad = $this->get_conex(TipoBaseDatos::SEGURIDAD);
            $conexSeguridad->beginTransaction();

            // Buscar el token activo y válido en tokens_seguridad
            $sqlToken = "SELECT token, fecha_expiracion 
                         FROM tokens_seguridad 
                         WHERE usuario_id = :uid 
                         AND tipo = :tipo 
                         AND fecha_expiracion > NOW()
                         ORDER BY fecha_expiracion DESC LIMIT 1";

            $stmtToken = $conexSeguridad->prepare($sqlToken);
            $stmtToken->execute([
                ':uid'  => $usuarioId,
                ':tipo' => $tipoToken
            ]);
            $datosToken = $stmtToken->fetch(PDO::FETCH_ASSOC);

            if (!$datosToken || $datosToken['token'] !== $token) {
                $conexSeguridad->rollBack();
                return ['estatus' => false, 'mensaje' => 'Token de refresco inválido o expirado.'];
            }

            // Obtener los datos del perfil del usuario y su respectivo rol
            $sqlUsuario = "SELECT u.id_usuario, u.correo, u.nombre, u.apellido, u.rol_id, r.nombre as nombre_rol 
                           FROM usuarios u
                           LEFT JOIN roles r ON u.rol_id = r.id_rol
                           WHERE u.id_usuario = :uid AND u.activo = 1 LIMIT 1";

            $stmtUser = $conexSeguridad->prepare($sqlUsuario);
            $stmtUser->execute([':uid' => $usuarioId]);
            $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                $conexSeguridad->rollBack();
                return ['estatus' => false, 'mensaje' => 'Usuario no encontrado o inactivo.'];
            }

            $conexSeguridad->commit();

            return [
                'estatus' => true,
                'datos' => $usuario
            ];

        } catch (PDOException $e) {
            if ($conexSeguridad->inTransaction()) {
                $conexSeguridad->rollBack();
            }
            error_log("Error en validarTokenYObtenerUsuario: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error interno al validar credenciales de sesión.'];
        }
    }
    
}
