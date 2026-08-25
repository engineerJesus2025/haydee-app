<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\Accion;
use haydee\enums\TipoToken;
use haydee\enums\TipoBaseDatos;
use haydee\enums\HttpCodigo;
use haydee\excepciones\NegocioException;

class Usuario extends Conexion
{
    private const MAX_INTENTOS_LOGIN = 3;
    private const TIEMPO_BLOQUEO_MINUTOS = 15;
    private const MARGEN_EXPIRACION_TOKEN_MINUTOS = 10;

    private const ESTADO_ACTIVO = 1;
    private const ESTADO_INACTIVO = 0;
    
    private $id_usuario;
    private $apellido;
    private $nombre;
    private $correo;
    private $contra;
    private $rol_id;
    private $activo;

    private $token;
    private $token_expiracion;
    private $token_tipo;

    public static function obtenerReglas($operacion) {
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

        return [];
    }

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

    public function set_token($t) { $this->token = $t; }
    public function get_token() { return $this->token; }
    public function set_token_expiracion($e) { $this->token_expiracion = $e; }
    public function get_token_expiracion() { return $this->token_expiracion; }
    public function set_token_tipo($t) { $this->token_tipo = $t; }
    public function get_token_tipo() { return $this->token_tipo; }

    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            throw new NegocioException("La acción '$accion' no está implementada.", HttpCodigo::BAD_REQUEST->value);
        }
        return $this->$metodo();
    }
    
    private function _obtener_credenciales_por_correo() {
        $sql = "SELECT id_usuario, correo, nombre, apellido, contrasenia, rol_id as id_rol, nombre_rol 
                FROM vw_perfiles_usuarios WHERE correo = :correo AND activo = " . self::ESTADO_ACTIVO;
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([':correo' => $this->correo]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $datos ? ['estatus' => true, 'datos' => $datos] : ['estatus' => false];
    }

    private function _verificar_bloqueo_cuenta() {
        $sql = "SELECT intentos, TIMESTAMPDIFF(MINUTE, ultimo_intento, NOW()) as min_transcurridos 
                FROM intentos_login WHERE usuario_id = :id";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([':id' => $this->id_usuario]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($registro && $registro['intentos'] >= self::MAX_INTENTOS_LOGIN) {
            if ($registro['min_transcurridos'] < self::TIEMPO_BLOQUEO_MINUTOS) {
                $tiempo_restante = self::TIEMPO_BLOQUEO_MINUTOS - $registro['min_transcurridos'];
                throw new NegocioException("Cuenta bloqueada temporalmente. Intente en $tiempo_restante min.", HttpCodigo::NO_AUTORIZADO->value);
            } else {
                $this->_limpiar_intentos_fallidos();
            }
        }
        return ['estatus' => true];
    }

    private function _registrar_intento_fallido() {
        $sql = "INSERT INTO intentos_login (usuario_id, intentos, ultimo_intento) 
                VALUES (:id, 1, NOW()) 
                ON DUPLICATE KEY UPDATE intentos = intentos + 1, ultimo_intento = NOW()";
        $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql)->execute([':id' => $this->id_usuario]);
        return ['estatus' => true];
    }

    private function _limpiar_intentos_fallidos() {
        $sql = "DELETE FROM intentos_login WHERE usuario_id = :id";
        $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql)->execute([':id' => $this->id_usuario]);
        return ['estatus' => true];
    }

    private function _consultar()
    {
        $sql = "SELECT id_usuario, apellido, nombre, correo, nombre_rol, activo
                FROM vw_perfiles_usuarios 
                WHERE activo = " . self::ESTADO_ACTIVO . " 
                ORDER BY id_usuario";

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_usuario()
    {
        $sql = "SELECT * FROM vw_perfiles_usuarios 
                WHERE id_usuario = :id AND activo = " . self::ESTADO_ACTIVO;

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([':id' => $this->id_usuario]);
        $dato = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dato) {
            throw new NegocioException('Usuario no encontrado.', HttpCodigo::NO_ENCONTRADO->value);
        }

        return ['estatus' => true, 'datos' => $dato];
    }

    private function _consultar_perfil_usuario()
    {
        $pdo = $this->get_conex(TipoBaseDatos::SEGURIDAD);
        
        $sqlUser = "SELECT nombre as nombre_usuario, apellido, correo, nombre_rol 
                    FROM vw_perfiles_usuarios 
                    WHERE id_usuario = :usuario AND activo = " . self::ESTADO_ACTIVO;
        $stmtU = $pdo->prepare($sqlUser);
        $stmtU->execute([':usuario' => $this->id_usuario]);
        $perfil = $stmtU->fetch(PDO::FETCH_ASSOC);

        if (!$perfil) {
            throw new NegocioException('Perfil de usuario no encontrado.', HttpCodigo::NO_ENCONTRADO->value);
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

        $perfil['ultima_vez'] = $historial[1] ?? ($historial[0] ?? 'Primer inicio de sesión');

        return ['estatus' => true, 'datos' => $perfil];
    }

    private function _existe_correo()
    {
        $sql = "SELECT id_usuario, nombre, apellido, correo, rol_id, nombre_rol 
                FROM vw_perfiles_usuarios 
                WHERE correo = :correo AND activo = " . self::ESTADO_ACTIVO;

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([':correo' => $this->correo]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$datos) {
            throw new NegocioException('Correo no encontrado.', HttpCodigo::NO_ENCONTRADO->value);
        }
        return ['estatus' => true, 'datos' => $datos];
    }

    private function _registrar_usuario()
    {
        $hash = password_hash($this->contra, PASSWORD_DEFAULT);

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
        return ['estatus' => true, 'mensaje' => 'Usuario registrado correctamente', 'lastId' => $lastId];
    }

    private function _modificar_usuario()
    {
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

        $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql)->execute($params);
        return ['estatus' => true, 'mensaje' => 'Usuario actualizado correctamente'];
    }

    private function _modificar_perfil()
    { 
        $sql = "UPDATE usuarios SET apellido = :ape, nombre = :nom, correo = :cor 
                WHERE id_usuario = :id";

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
    }

    private function _eliminar_usuario()
    {
        $db = $this->get_conex(TipoBaseDatos::SEGURIDAD);
        try {
            $db->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $db->beginTransaction();

            $sql = "UPDATE usuarios SET activo = " . self::ESTADO_INACTIVO . " WHERE id_usuario = :id";
            $db->prepare($sql)->execute([':id' => $this->id_usuario]);

            $db->prepare("DELETE FROM tokens_seguridad WHERE usuario_id = :id")->execute([':id' => $this->id_usuario]);
            $db->prepare("DELETE FROM claves_sesion WHERE usuario_id = :id")->execute([':id' => $this->id_usuario]);

            $db->commit();
            return ['estatus' => true, 'mensaje' => 'Usuario eliminado y sesiones cerradas con éxito.'];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    private function _cambiar_contrasenia()
    {
        $hash = password_hash($this->contra, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET contrasenia = :con WHERE correo = :cor AND activo = " . self::ESTADO_ACTIVO;

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([':con' => $hash, ':cor' => $this->correo]);
        
        if ($stmt->rowCount() == 0) {
            throw new NegocioException('Correo no encontrado o inactivo.', HttpCodigo::NO_ENCONTRADO->value);
        }
        return ['estatus' => true, 'mensaje' => 'Contraseña actualizada correctamente'];
    }
    
    private function _registrar_token()
    {
        $sql = "CALL sp_insertar_token(:uid, :tipo, :token, :exp)";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([
            ':uid' => $this->id_usuario,
            ':tipo' => $this->token_tipo,
            ':token' => $this->token,
            ':exp' => $this->token_expiracion
        ]);
        return ['estatus' => true, 'mensaje' => 'Token generado'];
    }

    private function _validar_token()
    {
        if (empty($this->token) || empty($this->token_tipo)) {
            throw new NegocioException('Datos de validación incompletos.', HttpCodigo::BAD_REQUEST->value);
        }

        $margen = self::MARGEN_EXPIRACION_TOKEN_MINUTOS;

        $sql = "SELECT u.id_usuario, u.correo, u.nombre 
                FROM tokens_seguridad t
                INNER JOIN usuarios u ON t.usuario_id = u.id_usuario
                WHERE t.token = :token 
                AND t.tipo = :tipo 
                AND DATE_ADD(t.fecha_expiracion, INTERVAL $margen MINUTE) > NOW()";

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([
            ':token' => $this->token, 
            ':tipo' => $this->token_tipo 
        ]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datos) {
            throw new NegocioException('El enlace ha expirado o es inválido.', HttpCodigo::NO_AUTORIZADO->value);
        }
        return ['estatus' => true, 'datos' => $datos];
    }

    private function _eliminar_token()
    {
        if (empty($this->id_usuario) || empty($this->token_tipo)) {
            throw new NegocioException('Faltan datos para eliminar el token.', HttpCodigo::BAD_REQUEST->value);
        }

        $sql = "DELETE FROM tokens_seguridad WHERE usuario_id = :uid AND tipo = :tipo";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([':uid' => $this->id_usuario, ':tipo' => $this->token_tipo]);
        return ['estatus' => true, 'mensaje' => 'Token eliminado'];
    }

    private function _obtener_token()
    {
        if (empty($this->id_usuario) || empty($this->token_tipo)) {
            throw new NegocioException('Datos incompletos para buscar token.', HttpCodigo::BAD_REQUEST->value);
        }

        $sql = "SELECT token, fecha_expiracion 
                FROM tokens_seguridad 
                WHERE usuario_id = :uid 
                AND tipo = :tipo 
                AND fecha_expiracion > NOW()
                ORDER BY fecha_expiracion DESC LIMIT 1";

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([
            ':uid' => $this->id_usuario, 
            ':tipo' => $this->token_tipo
        ]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datos) {
            throw new NegocioException('No hay códigos válidos o han expirado.', HttpCodigo::NO_AUTORIZADO->value);
        }
        return ['estatus' => true, 'datos' => $datos];
    }

    private function _validar_token_jwt()
    {
        $usuarioId = $this->id_usuario;
        $token = $this->token;
        $tipoToken = $this->token_tipo;

        if (empty($usuarioId) || empty($token) || empty($tipoToken)) {
            throw new NegocioException('Datos insuficientes para la validación.', HttpCodigo::BAD_REQUEST->value);
        }

        $conexSeguridad = $this->get_conex(TipoBaseDatos::SEGURIDAD);
        try {
            $conexSeguridad->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $conexSeguridad->beginTransaction();

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
                throw new NegocioException('Token de refresco inválido o expirado.', HttpCodigo::NO_AUTORIZADO->value);
            }

            $sqlUsuario = "SELECT id_usuario, correo, nombre, apellido, rol_id, nombre_rol 
                           FROM vw_perfiles_usuarios
                           WHERE id_usuario = :uid AND activo = " . self::ESTADO_ACTIVO . " LIMIT 1";

            $stmtUser = $conexSeguridad->prepare($sqlUsuario);
            $stmtUser->execute([':uid' => $usuarioId]);
            $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                throw new NegocioException('Usuario no encontrado o inactivo.', HttpCodigo::NO_AUTORIZADO->value);
            }

            $conexSeguridad->commit();

            return [
                'estatus' => true,
                'datos' => $usuario
            ];

        } catch (\Throwable $e) {
            if ($conexSeguridad->inTransaction()) {
                $conexSeguridad->rollBack();
            }
            throw $e;
        }
    }

    private function _consumir_token_recuperacion()
    {
        if (empty($this->correo) || empty($this->token) || empty($this->contra) || empty($this->token_tipo)) {
            throw new NegocioException('Datos insuficientes para procesar el restablecimiento.', HttpCodigo::BAD_REQUEST->value);
        }

        $db = $this->get_conex(TipoBaseDatos::SEGURIDAD);
        try {
            $db->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $db->beginTransaction(); 

            $sqlUsuario = "SELECT id_usuario FROM usuarios WHERE correo = :correo AND activo = " . self::ESTADO_ACTIVO . " FOR UPDATE";

            $stmtU = $db->prepare($sqlUsuario);
            $stmtU->execute([':correo' => $this->correo]);
            $usuario = $stmtU->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                throw new NegocioException('Solicitud no válida o usuario inexistente.', HttpCodigo::BAD_REQUEST->value);
            }

            $idUsuario = $usuario['id_usuario'];

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

            $tokenHashEsperado = hash('sha256', $this->token);

            if (!$datosToken || $datosToken['token'] !== $tokenHashEsperado) {
                throw new NegocioException('La sesión de recuperación es inválida o ha expirado.', HttpCodigo::NO_AUTORIZADO->value);
            }

            $nuevoHashPassword = password_hash($this->contra, PASSWORD_DEFAULT);

            $sqlCambio = "UPDATE usuarios SET contrasenia = :con WHERE id_usuario = :uid";
            $db->prepare($sqlCambio)->execute([
                ':con' => $nuevoHashPassword,
                ':uid' => $idUsuario
            ]);

            $sqlEliminar = "DELETE FROM tokens_seguridad WHERE usuario_id = :uid AND tipo = :tipo";
            $db->prepare($sqlEliminar)->execute([':uid' => $idUsuario, ':tipo' => $this->token_tipo]);

            $db->prepare("DELETE FROM claves_sesion WHERE usuario_id = :uid")->execute([':uid' => $idUsuario]);
            $db->prepare("DELETE FROM tokens_seguridad WHERE usuario_id = :uid AND tipo = '" . TipoToken::REFRESH_MOVIL->value . "'")->execute([':uid' => $idUsuario]);

            $db->commit();
            return ['estatus' => true, 'mensaje' => 'Contraseña actualizada con éxito.'];

        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}