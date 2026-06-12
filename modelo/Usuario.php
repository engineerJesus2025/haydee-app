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

    private const ESTADO_ACTIVO = 1;
    private const ESTADO_INACTIVO = 0;
    
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
        ],
        'consumir_token_recuperacion' => [
            'metodo_http' => ['POST'],
            'campos' => ['correo', 'token', 'contra', 'token_tipo']
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
            $db->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $db->beginTransaction();

            $sqlUsuario = "SELECT id_usuario, correo, nombre, apellido, contrasenia, 
                                  rol_id as id_rol, nombre_rol
                           FROM vw_perfiles_usuarios 
                           WHERE correo = :correo AND activo = " . self::ESTADO_ACTIVO;
            
            $stmtU = $db->prepare($sqlUsuario);
            $stmtU->execute([':correo' => $this->correo]);
            $datos = $stmtU->fetch(PDO::FETCH_ASSOC);

            if (!$datos) {
                $db->rollBack();
                // Simulamos la carga de CPU para enmascarar el tiempo de respuesta :O
                password_verify('password_falsa', '$2y$10$dummyhashdummyhashdummyhashdummyhashdummyhash');
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
                $db->prepare("DELETE FROM intentos_login WHERE usuario_id = :id")->execute([':id' => $id_usuario]);
                $db->commit(); 
                unset($datos['contrasenia']);
                return ['estatus' => true, 'datos' => $datos];
            } else {
                $sqlFallo = "INSERT INTO intentos_login (usuario_id, intentos, ultimo_intento) 
                             VALUES (:id, 1, NOW()) 
                             ON DUPLICATE KEY UPDATE intentos = intentos + 1, ultimo_intento = NOW()";
                $db->prepare($sqlFallo)->execute([':id' => $id_usuario]);
                $db->commit(); 
                return ['estatus' => false, 'mensaje' => 'Credenciales incorrectas', 'codigo_http' => HttpCodigo::BAD_REQUEST->value];
            }

        } catch (\Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error en _validar_usuario: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error de seguridad en el sistema'];
        }
    }



    // SE USA EN EL MODULO
    private function _consultar()
    {
        $sql = "SELECT id_usuario, apellido, nombre, correo, nombre_rol, activo
                FROM vw_perfiles_usuarios 
                WHERE activo = " . self::ESTADO_ACTIVO . " 
                ORDER BY id_usuario";
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
        $sql = "SELECT * FROM vw_perfiles_usuarios 
                WHERE id_usuario = :id AND activo = " . self::ESTADO_ACTIVO;
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
        try {
            $pdo = $this->get_conex(TipoBaseDatos::SEGURIDAD);
            
            //  Uso de la nueva Vista y la Constante
            $sqlUser = "SELECT nombre as nombre_usuario, apellido, correo, nombre_rol 
                        FROM vw_perfiles_usuarios 
                        WHERE id_usuario = :usuario AND activo = " . self::ESTADO_ACTIVO;
            $stmtU = $pdo->prepare($sqlUser);
            $stmtU->execute([':usuario' => $this->id_usuario]);
            $perfil = $stmtU->fetch(PDO::FETCH_ASSOC);

            if (!$perfil) {
                return ['estatus' => false, 'mensaje' => 'Perfil no encontrado'];
            }

            $sqlBitacora = "SELECT fecha_hora FROM bitacora 
                            WHERE usuario_id = :usuario AND accion = :accion 
                            ORDER BY fecha_hora DESC LIMIT 2";
            $stmtB = $pdo->prepare($sqlBitacora);
            $stmtB->execute([
                ':usuario' => $this->id_usuario,
                ':accion'  => Accion::INICIAR_SESION->value
            ]);
            $historial = $stmtB->fetchAll(PDO::FETCH_COLUMN);

            // Si hay 2 registros, el [1] es la sesión anterior. Si hay 1, es la actual.
            $perfil['ultima_vez'] = $historial[1] ?? ($historial[0] ?? 'Primer inicio de sesión');

            return ['estatus' => true, 'datos' => $perfil];
        } catch (PDOException $e) {
            error_log("Error en _consultar_perfil_usuario: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener el perfil'];
        }
    }

    // SE USA EN EL SERVICIO AUTENTICACION (repasar)    
    private function _existe_correo()
    {
        $sql = "SELECT id_usuario, nombre, apellido, correo, rol_id, nombre_rol 
                FROM vw_perfiles_usuarios 
                WHERE correo = :correo AND activo = " . self::ESTADO_ACTIVO;
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
            $db = $this->get_conex(TipoBaseDatos::SEGURIDAD);
            $db->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $db->beginTransaction();

            $sql = "UPDATE usuarios SET activo = " . self::ESTADO_INACTIVO . " WHERE id_usuario = :id";
            $db->prepare($sql)->execute([':id' => $this->id_usuario]);

            // CIERRE DE SESIÓN FORZADO (Revocación Activa)
            $db->prepare("DELETE FROM tokens_seguridad WHERE usuario_id = :id")->execute([':id' => $this->id_usuario]);
            $db->prepare("DELETE FROM claves_sesion WHERE usuario_id = :id")->execute([':id' => $this->id_usuario]);

            $db->commit();
            return ['estatus' => true, 'mensaje' => 'Usuario eliminado y sesiones cerradas con éxito.'];
        } catch (\Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error en _eliminar_usuario: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar. Intente más tarde.'];
        }
    }

    // SE USA EN EL MODULO (perfil)
    private function _cambiar_contrasenia()
    {
        $hash = password_hash($this->contra, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET contrasenia = :con WHERE correo = :cor AND activo = " . self::ESTADO_ACTIVO;

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
            $conexSeguridad->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
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
            $sqlUsuario = "SELECT id_usuario, correo, nombre, apellido, rol_id, nombre_rol 
                           FROM vw_perfiles_usuarios
                           WHERE id_usuario = :uid AND activo = " . self::ESTADO_ACTIVO . " LIMIT 1";

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
            if (isset($conexSeguridad) && $conexSeguridad->inTransaction()) {
                $conexSeguridad->rollBack();
            }
            error_log("Error en validarTokenYObtenerUsuario: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error interno al validar credenciales de sesión.'];
        }
    }


    /**
     * Valida el token de autorización largo con SHA-256,
     * actualiza la contraseña con Bcrypt y elimina el token de forma atómica.
     */
    private function _consumir_token_recuperacion()
    {
        if (empty($this->correo) || empty($this->token) || empty($this->contra) || empty($this->token_tipo)) {
            return ['estatus' => false, 'mensaje' => 'Datos insuficientes para procesar el restablecimiento.'];
        }

        try {
            $db = $this->get_conex(TipoBaseDatos::SEGURIDAD);
            $db->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $db->beginTransaction(); 

            // Buscamos el usuario y bloqueamos su información perimetral
            $sqlUsuario = "SELECT id_usuario FROM usuarios WHERE correo = :correo AND activo = " . self::ESTADO_ACTIVO . " FOR UPDATE";

            // Supuestamente es buena práctica no utilizar vistas cuando se hace un bloqueo de fila (FOR UPDATE)
            $stmtU = $db->prepare($sqlUsuario);
            $stmtU->execute([':correo' => $this->correo]);
            $usuario = $stmtU->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                $db->rollBack();
                return ['estatus' => false, 'mensaje' => 'Solicitud no válida o usuario inexistente.'];
            }

            $idUsuario = $usuario['id_usuario'];

            // Buscamos el Token de Autorización vigente y bloqueamos la fila
            $sqlToken = "SELECT token FROM tokens_seguridad 
                         WHERE usuario_id = :uid 
                         AND tipo = :tipo 
                         AND fecha_expiracion > NOW()
                         ORDER BY fecha_expiracion DESC LIMIT 1 FOR UPDATE";
            
            $stmtT = $db->prepare($sqlToken);
            $stmtT->execute([
                ':uid'  => $idUsuario,
                ':tipo' => $this->token_tipo
            ]);
            $datosToken = $stmtT->fetch(PDO::FETCH_ASSOC);

            // Verificamos el token aplicando la función SHA-256 
            $tokenHashEsperado = hash('sha256', $this->token);

            if (!$datosToken || $datosToken['token'] !== $tokenHashEsperado) {
                $db->rollBack();
                return ['estatus' => false, 'mensaje' => 'La sesión de recuperación es inválida o ha expirado.'];
            }

            // El token es legítimo: Generamos el hash Bcrypt para la contraseña en el Modelo
            $nuevoHashPassword = password_hash($this->contra, PASSWORD_DEFAULT);

            $sqlCambio = "UPDATE usuarios SET contrasenia = :con WHERE id_usuario = :uid";
            $db->prepare($sqlCambio)->execute([
                ':con' => $nuevoHashPassword,
                ':uid' => $idUsuario
            ]);

            // Quemamos el token de autorización inmediatamente para evitar ataques de repetición
            $sqlEliminar = "DELETE FROM tokens_seguridad WHERE usuario_id = :uid AND tipo = :tipo";
            $db->prepare($sqlEliminar)->execute([':uid'  => $idUsuario, ':tipo' => $this->token_tipo]);

            //  Revocar cualquier sesión activa antigua del usuario al cambiar la clave
            $db->prepare("DELETE FROM claves_sesion WHERE usuario_id = :uid")->execute([':uid' => $idUsuario]);
            $db->prepare("DELETE FROM tokens_seguridad WHERE usuario_id = :uid AND tipo = '" . TipoToken::REFRESH_MOVIL->value . "'")->execute([':uid' => $idUsuario]);

            $db->commit();
            return ['estatus' => true, 'mensaje' => 'Contraseña actualizada con éxito.'];

        } catch (PDOException $e) {
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error crítico en _consumir_token_recuperacion: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error de seguridad interna en el motor de base de datos.'];
        }
    }
    
}
