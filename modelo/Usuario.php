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
    private $reglas = [
        // Usuario
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
            'regex' => '/^[a-zA-Z0-9._+-]{3,35}@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/',
            'unique' => ['tabla' => 'usuarios', 'campo' => 'correo', 'exclude_field' => 'id_usuario']
        ],
        'contra' => [
            'regex' => '/^[A-Za-z0-9_.+*$#%&@ñÑ-]{5,100}$/'
        ],
        'rol_id' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'roles', 'campo' => 'id_rol']
        ],

        // Token
        'token' => [
            'regex' => '/^\S+$/'  // Acepta cualquier cadena sin espacios
        ],
        'token_tipo' => [
            'regex' => '/^[a-zA-Z0-9_]{3,30}$/'
        ],
        'token_expiracion' => [
            'type' => 'datetime'  // Validación personalizada
        ]
    ];

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
    // VALIDACIÓN (Usa BD Seguridad)
    // ====================================================================
    private function validar($campos, $contexto = [])
    {
        foreach ($campos as $campo) {
            if (!isset($this->reglas[$campo])) continue;
            $getter = 'get_' . $campo;
            $valor = $this->$getter();
            $regla = $this->reglas[$campo];

            // Requerido
            if ($valor === null || (is_string($valor) && trim($valor) === '')) {
                return ['estatus' => false, 'mensaje' => "El campo '$campo' es obligatorio."];
            }

            // Regex
            if (isset($regla['regex']) && !preg_match($regla['regex'], (string)$valor)) {
                return ['estatus' => false, 'mensaje' => "Formato inválido para '$campo'."];
            }

            // Validación de tipo datetime
            if (isset($regla['type']) && $regla['type'] === 'datetime') {
                $d = \DateTime::createFromFormat('Y-m-d H:i:s', $valor);
                if (!($d && $d->format('Y-m-d H:i:s') === $valor)) {
                    return ['estatus' => false, 'mensaje' => "Formato de fecha/hora inválido para '$campo'."];
                }
            }

            // Existencia
            if (isset($regla['exists'])) {
                if (!$this->existeEnTabla($regla['exists']['tabla'], $regla['exists']['campo'], $valor)) {
                    return ['estatus' => false, 'mensaje' => "El valor de '$campo' no existe."];
                }
            }

            // Unicidad - Se omite si en el contexto se indica 'skip_unique'
            if (isset($regla['unique']) && !isset($contexto['skip_unique'])) {
                $excludeValue = $contexto['exclude_id'] ?? null;
                if (!$this->esUnico($regla['unique']['tabla'], $regla['unique']['campo'], $valor, $regla['unique']['exclude_field'] ?? null, $excludeValue)) {
                    return ['estatus' => false, 'mensaje' => "El '$campo' ya está registrado."];
                }
            }
        }
        return ['estatus' => true];
    }

    private function existeEnTabla($tabla, $campo, $valor)
    {
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = :valor";
        $stmt = $this->get_conex('seguridad')->prepare($sql);
        $stmt->execute([':valor' => $valor]);
        return $stmt->fetchColumn() > 0;
    }

    private function esUnico($tabla, $campo, $valor, $excludeField = null, $excludeValue = null)
    {
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = :valor";
        if ($excludeField && $excludeValue) $sql .= " AND $excludeField != :exclude_val";

        $stmt = $this->get_conex('seguridad')->prepare($sql);
        $stmt->bindParam(':valor', $valor);
        if ($excludeField && $excludeValue) $stmt->bindParam(':exclude_val', $excludeValue);

        $stmt->execute();
        return $stmt->fetchColumn() == 0;
    }

    /**
     * Valida existencia en tablas externas (para validaciones AJAX desde controladores)
     */
    public function validarExistenciaExterna($tabla, $campo, $valor)
    {
        $tablasPermitidas = ['caja_chica', 'gastos', 'usuarios', 'roles'];
        if (!in_array($tabla, $tablasPermitidas)) return false;

        // Determinar qué conexión usar (Caja y Gastos son negocio, Usuarios y Roles son seguridad)
        $dbType = in_array($tabla, ['usuarios', 'roles']) ? 'seguridad' : 'negocio';

        $sql = "SELECT 1 FROM $tabla WHERE $campo = :valor LIMIT 1";
        $stmt = $this->get_conex($dbType)->prepare($sql);
        $stmt->execute([':valor' => $valor]);
        return $stmt->fetchColumn() ? true : false;
    }

    // ====================================================================
    // LÓGICA DE USUARIOS (CRUD y Auth)
    // ====================================================================

    private function _validar_usuario()
    {
        // Pasamos 'skip_unique' para que no valide que el correo sea único
        $v = $this->validar(['correo', 'contra'], ['skip_unique' => true]);
        if (!$v['estatus']) return $v;

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

    private function _consultar_usuario()
    {
        $v = $this->validar(['id_usuario']);
        if (!$v['estatus']) return $v;

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

    private function _consultar_perfil_usuario()
    {
        // Validamos que el ID de usuario esté presente
        $v = $this->validar(['id_usuario']);
        if (!$v['estatus']) return $v;

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

    private function _existe_correo()
    {
        $v = $this->validar(['correo'],['skip_unique' => true]);
        if (!$v['estatus']) return $v;

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

    /**
     * Verifica si un correo ya existe en la base de datos.
     * Utilizado principalmente para validaciones AJAX.
     */
    private function _verificar_correo()
    {
        // 1. Validamos que el formato del correo sea correcto según tus reglas
        $v = $this->validar(['correo'], ['skip_unique' => true]);
        if (!$v['estatus']) return $v;

        try {
            // 2. Usamos la función esUnico. 
            // Si esUnico devuelve true, significa que NO existe.
            // Por lo tanto, 'existe' será lo opuesto.
            $no_existe = $this->esUnico(
                $this->reglas['correo']['unique']['tabla'], 
                $this->reglas['correo']['unique']['campo'], 
                $this->correo
            );

            return [
                'estatus' => true,
                'existe'  => !$no_existe 
            ];
            
        } catch (PDOException $e) {
            error_log("Error en _verificar_correo: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al verificar disponibilidad'];
        }
    }

    private function _registrar()
    {
        $v = $this->validar(['apellido', 'nombre', 'correo', 'contra', 'rol_id']);
        if (!$v['estatus']) return $v;

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

    private function _editar_usuario()
    {
        // La contraseña es opcional. Validamos los campos obligatorios.
        $v = $this->validar(['id_usuario', 'apellido', 'nombre', 'correo', 'rol_id'], ['exclude_id' => $this->id_usuario]);
        if (!$v['estatus']) return $v;

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
            // Validar que la contraseña cumpla el formato si se envía
            $valPass = $this->validar(['contra']);
            if (!$valPass['estatus']) return $valPass;

            $sql = "UPDATE usuarios SET apellido=:a, nombre=:n, correo=:c, rol_id=:r, contrasenia=:p WHERE id_usuario=:id";
            $params[':p'] = password_hash($this->contra, PASSWORD_DEFAULT);
        }

        try {
            $this->get_conex('seguridad')->prepare($sql)->execute($params);
            return ['estatus' => true, 'mensaje' => 'Usuario actualizado'];
        } catch (PDOException $e) {
            error_log("Error en _editar_usuario: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al editar: ' . $e->getMessage()];
        }
    }

    private function _editar_perfil()
    {
        // Validamos campos obligatorios y la unicidad del correo
        // (excluyendo al usuario actual de la comprobación de duplicados)
        $v = $this->validar(
            ['id_usuario', 'apellido', 'nombre', 'correo'], 
            ['exclude_id' => $this->id_usuario]
        );
        
        if (!$v['estatus']) return $v;

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
            error_log("Error en _editar_perfil: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar el perfil'];
        }
    }

    private function _eliminar_usuario()
    {
        $v = $this->validar(['id_usuario']);
        if (!$v['estatus']) return $v;

        try {
            $sql = "UPDATE usuarios SET activo = 0 WHERE id_usuario = :id";
            $this->get_conex('seguridad')->prepare($sql)->execute([':id' => $this->id_usuario]);
            return ['estatus' => true, 'mensaje' => 'Usuario eliminado'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar_usuario: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar'];
        }
    }

    private function _cambiar_contrasenia()
    {
        // Se usa para recuperación ("Olvidé contraseña")
        $v = $this->validar(['correo', 'contra']);
        if (!$v['estatus']) return $v;

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

    private function _registrar_token()
    {
        $v = $this->validar(['id_usuario', 'token', 'token_tipo', 'token_expiracion']);
        if (!$v['estatus']) return $v;

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

    private function _validar_token()
    {
        $v = $this->validar(['token', 'token_tipo']);
        if (!$v['estatus']) return $v;

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

    private function _validar_token_recuerdame()
    {
        $v = $this->validar(['correo']);
        if (!$v['estatus']) return $v;

        $sql = "SELECT u.id_usuario, u.correo, u.nombre, r.id_rol, r.nombre as nombre_rol, t.token, t.fecha_expiracion
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id_rol
                JOIN tokens_seguridad t ON u.id_usuario = t.usuario_id
                WHERE u.correo = :correo AND t.tipo = 'Recuerdame'";

        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':correo' => $this->correo]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$datos) return ['estatus' => false, 'mensaje' => 'No hay token de recuérdame'];
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _validar_token_recuerdame: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al validar token'];
        }
    }

    // ====================================================================
    // UTILIDADES (Recaptcha)
    // ====================================================================
    public function verificarRecaptcha($respuestaRecaptcha)
    {
        if (empty($respuestaRecaptcha)) return false;

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $datos = [
            'secret' => defined('CLAVE_SECRETA_RECAPTCHA') ? CLAVE_SECRETA_RECAPTCHA : '',
            'response' => $respuestaRecaptcha,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];

        $opciones = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($datos)
            ]
        ];
        $contexto = stream_context_create($opciones);
        $resultado = @file_get_contents($url, false, $contexto);
        $json = json_decode($resultado, true);

        return ($json && isset($json['success']) && $json['success']);
    }
}
?>