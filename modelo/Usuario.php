<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Usuario extends Conexion
{
    // ====================================================================
    // PROPIEDADES (Usuario)
    // ====================================================================
    private $id_usuario;
    private $apellido;
    private $nombre;
    private $correo;
    private $contra;
    private $rol_id;
    private $activo;

    // ====================================================================
    // PROPIEDADES (Token de Seguridad)
    // ====================================================================
    private $token;
    private $token_expiracion;
    private $token_tipo; // 'Recuperacion', 'Recuerdame', etc.

    // ====================================================================
    // VALIDACIONES CENTRALIZADAS
    // ====================================================================
    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
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

        // Definimos los campos exactos requeridos por cada operación (tanto de usuario como de perfil)
        $camposPorOperacion = [
            'registrar_usuario' => ['nombre', 'apellido', 'correo', 'contra', 'rol_id'],
            'modificar_usuario' => ['id_usuario', 'nombre', 'apellido', 'correo', 'rol_id'],
            'eliminar_usuario' => ['id_usuario'],
            'consultar_usuario' => ['id_usuario'],
            'restablecer_contrasenia' => ['id_usuario'],
            'modificar_perfil' => ['id_usuario', 'nombre', 'apellido', 'correo'],
            'cambiar_contrasenia' => ['id_usuario', 'contra'],
            'entrar' => ['correo', 'contra'],
            'recuperar_contrasenia' => ['correo'],
            'guardar_contrasenia' => ['contra']
        ];


        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    // ====================================================================
    // GETTERS Y SETTERS
    // ====================================================================
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

    // ====================================================================
    // ENRUTADOR
    // ====================================================================
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

    // ====================================================================
    // LÓGICA DE USUARIOS (CRUD y Auth)
    // ====================================================================

    // SE USA EN EL SERVICIO AUTENTICACION
    private function _validar_usuario()
    {
        $sql = "SELECT u.id_usuario, u.correo, u.nombre, u.apellido, u.contrasenia, 
                       r.id_rol, r.nombre as nombre_rol
                FROM usuarios u 
                INNER JOIN roles r ON u.rol_id = r.id_rol 
                WHERE u.correo = :correo AND u.activo = 1";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':correo' => $this->correo]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$datos) return ['estatus' => false, 'mensaje' => 'Usuario no encontrado'];

            if (password_verify($this->contra, $datos['contrasenia'])) {
                unset($datos['contrasenia']); // Eliminamos el hash por seguridad
                return ['estatus' => true, 'datos' => $datos];
            } else {
                return ['estatus' => false, 'mensaje' => 'Contraseña incorrecta'];
            }
        } catch (PDOException $e) {
            error_log("Error en _validar_usuario: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al validar usuario'];
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
            $stmt = $this->get_conex('seguridad')->prepare($sql);
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
            $stmt = $this->get_conex('seguridad')->prepare($sql);
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
        $sql = "SELECT 
                    u.nombre as nombre_usuario, 
                    u.apellido, 
                    u.correo, 
                    r.nombre as nombre_rol,
                    COALESCE(
                        (SELECT b.fecha_hora 
                         FROM bitacora b 
                         WHERE b.usuario_id = u.id_usuario 
                         AND b.accion = 'iniciar sesion' 
                         ORDER BY b.fecha_hora DESC 
                         LIMIT 1 OFFSET 1),
                        (SELECT b.fecha_hora 
                         FROM bitacora b 
                         WHERE b.usuario_id = u.id_usuario 
                         AND b.accion = 'iniciar sesion' 
                         ORDER BY b.fecha_hora DESC 
                         LIMIT 1)
                    ) as ultima_vez
                FROM usuarios u 
                INNER JOIN roles r ON u.rol_id = r.id_rol 
                WHERE u.id_usuario = :usuario";

        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':usuario' => $this->id_usuario]);
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
        $sql = "SELECT id_usuario, nombre, apellido, correo, rol_id FROM usuarios WHERE correo = :correo AND activo = 1";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
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
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([
                ':ape' => $this->apellido,
                ':nom' => $this->nombre,
                ':cor' => $this->correo,
                ':con' => $hash,
                ':rol' => $this->rol_id
            ]);
            $lastId = $this->get_conex('seguridad')->lastInsertId();
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
            $this->get_conex('seguridad')->prepare($sql)->execute($params);
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
            $stmt = $this->get_conex('seguridad')->prepare($sql);
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
            $this->get_conex('seguridad')->prepare($sql)->execute([':id' => $this->id_usuario]);
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
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':con' => $hash, ':cor' => $this->correo]);
            if ($stmt->rowCount() == 0) return ['estatus' => false, 'mensaje' => 'Correo no encontrado o inactivo'];
            return ['estatus' => true, 'mensaje' => 'Contraseña actualizada'];
        } catch (PDOException $e) {
            error_log("Error en _cambiar_contrasenia: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error en la base de datos'];
        }
    }

    // ====================================================================
    // LÓGICA DE TOKENS (Integrada)
    // ====================================================================

    // SE USA EN EL SERVICIO AUTENTICACION
    private function _registrar_token()
    {
        try {
            $sql = "CALL sp_insertar_token(:uid, :tipo, :token, :exp)";
            $stmt = $this->get_conex('seguridad')->prepare($sql);
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
        $sql = "SELECT u.id_usuario, u.correo 
                FROM tokens_seguridad t
                JOIN usuarios u ON t.usuario_id = u.id_usuario
                WHERE t.token = :token AND t.tipo = :tipo 
                AND DATE_ADD(t.fecha_expiracion, INTERVAL 10 MINUTE) > NOW()";

        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':token' => $this->token, ':tipo' => $this->token_tipo]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$datos) return ['estatus' => false, 'mensaje' => 'Token inválido o expirado'];
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _validar_token: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al validar token'];
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
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':uid' => $this->id_usuario, ':tipo' => $this->token_tipo]);
            return ['estatus' => true, 'mensaje' => 'Token eliminado'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar_token: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar token'];
        }
    }
}
?>