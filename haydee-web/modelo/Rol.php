<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\enums\TipoBaseDatos;

class Rol extends Conexion
{
    private $id_rol;
    private $nombre;
    private $activo;
    private array $permisos_asignados = []; 

    public static function obtenerReglas(string $operacion): array
    {
        $reglasGenerales = [
            'id_rol' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'roles', 'campo' => 'id_rol']
            ],
            'nombre' => [
                'regex' => '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,50}$/',
                'unique' => ['tabla' => 'roles', 'campo' => 'nombre', 'exclude_field' => 'id_rol']
            ],
            'permisos' => [
                'regex' => '/^\[.*\]$/s',
                'opcional' => true
            ]
        ];

        $camposPorOperacion = [
            'registrar_rol'                => ['nombre', 'permisos'],
            'modificar_rol'                => ['id_rol', 'nombre', 'permisos'],
            'eliminar_rol'                 => ['id_rol'],
            'consultar_rol'                => ['id_rol'],
            'consultar_permisos_asignados' => ['id_rol'],
        ];

        return isset($camposPorOperacion[$operacion]) 
            ? array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion])) 
            : [];
    }

    // GETTERS Y SETTERS
    public function set_id_rol($id) { $this->id_rol = $id; }
    public function get_id_rol() { return $this->id_rol; }
    
    public function set_nombre($nombre) { $this->nombre = $nombre; }
    public function get_nombre() { return $this->nombre; }
    
    public function set_activo($activo) { $this->activo = $activo; }
    public function get_activo() { return $this->activo; }

    public function set_permisos_asignados(array $permisos) { $this->permisos_asignados = $permisos; }
    public function get_permisos_asignados(): array { return $this->permisos_asignados; }

    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            throw new NegocioException("La acción '$accion' no está implementada.", HttpCodigo::BAD_REQUEST->value);
        }
        return $this->$metodo();
    }

    private function _verificar_nombre(): array
    {
        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare("SELECT id_rol FROM roles WHERE nombre = :nombre AND activo = 1 LIMIT 1");
            $stmt->execute([':nombre' => $this->nombre]);
            return ['estatus' => true, 'existe' => (bool)$stmt->fetchColumn()];
        } catch (PDOException $e) {
            error_log("Error en _verificar_nombre: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al verificar el nombre del rol'];
        }
    }

    private function _consultar(): array
    {
        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare("SELECT * FROM roles WHERE activo = 1 ORDER BY id_rol");
            $stmt->execute();
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar roles'];
        }
    }

    private function _consultar_rol(): array
    {
        try {
            $db = $this->get_conex(TipoBaseDatos::SEGURIDAD);

            $stmtRol = $db->prepare("SELECT * FROM roles WHERE id_rol = :id AND activo = 1");
            $stmtRol->execute([':id' => $this->id_rol]);
            $rol = $stmtRol->fetch(PDO::FETCH_ASSOC);

            if (!$rol) {
                return ['estatus' => false, 'mensaje' => 'Rol no encontrado'];
            }

            $stmtPermisos = $db->prepare("SELECT modulo_id, permiso_id FROM asignacion_permisos WHERE rol_id = :id");
            $stmtPermisos->execute([':id' => $this->id_rol]);

            return [
                'estatus' => true,
                'datos'   => [
                    'rol'      => $rol,
                    'permisos' => $stmtPermisos->fetchAll(PDO::FETCH_ASSOC)
                ]
            ];
        } catch (PDOException $e) {
            error_log("Error en _consultar_rol: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar el rol'];
        }
    }

    // OPERACIONES CRUD
    private function _registrar_rol(): array
    {
        $pdo = $this->get_conex(TipoBaseDatos::SEGURIDAD);
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO roles (nombre, activo) VALUES (:nombre, 1)");
            $stmt->execute([':nombre' => $this->nombre]);

            $this->id_rol = $pdo->lastInsertId();
            $this->_sincronizar_permisos($pdo);

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Rol registrado exitosamente'];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en _registrar_rol: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar el rol'];
        }
    }

    private function _modificar_rol(): array
    {
        $pdo = $this->get_conex(TipoBaseDatos::SEGURIDAD);
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE roles SET nombre = :nombre WHERE id_rol = :id");
            $stmt->execute([':nombre' => $this->nombre, ':id' => $this->id_rol]);

            $this->_sincronizar_permisos($pdo);

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Rol actualizado correctamente'];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en _modificar_rol: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar el rol'];
        }
    }

    private function _eliminar_rol(): array
    {
        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare("UPDATE roles SET activo = 0 WHERE id_rol = :id");
            $stmt->execute([':id' => $this->id_rol]);
            return ['estatus' => true, 'mensaje' => 'Rol eliminado'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar_rol: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar el rol'];
        }
    }

    /**
     * Sincronización optimizada mediante inserción masiva por lotes (Batch Insert)
     */
    private function _sincronizar_permisos(PDO $pdo): void
    {
        $pdo->prepare("DELETE FROM asignacion_permisos WHERE rol_id = :id")->execute([':id' => $this->id_rol]);

        if (empty($this->permisos_asignados)) {
            return;
        }

        $valores = [];
        $params = [];

        foreach ($this->permisos_asignados as $index => $permiso) {
            if (isset($permiso['modulo_id'], $permiso['permiso_id'])) {
                $valores[] = "(:rol_{$index}, :modulo_{$index}, :permiso_{$index})";
                $params[":rol_{$index}"]     = $this->id_rol;
                $params[":modulo_{$index}"]  = $permiso['modulo_id'];
                $params[":permiso_{$index}"] = $permiso['permiso_id'];
            }
        }

        if (!empty($valores)) {
            $sqlInsert = "INSERT INTO asignacion_permisos (rol_id, modulo_id, permiso_id) VALUES " . implode(', ', $valores);
            $pdo->prepare($sqlInsert)->execute($params);
        }
    }

    private function _consultar_permisos_asignados(): array
    {
        $sql = "SELECT ap.modulo_id, ap.permiso_id, m.nombre as modulo, p.accion as permiso
                FROM asignacion_permisos ap
                JOIN modulos m ON ap.modulo_id = m.id_modulo
                JOIN permisos p ON ap.permiso_id = p.id_permiso
                WHERE ap.rol_id = :id";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([':id' => $this->id_rol]);
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar_permisos_asignados: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar permisos'];
        }
    }

    private function _consultar_matriz_permisos(): array
    {
        try {
            $db = $this->get_conex(TipoBaseDatos::SEGURIDAD);
            return [
                'estatus' => true,
                'datos'   => [
                    'modulos'  => $db->query("SELECT * FROM modulos WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC),
                    'permisos' => $db->query("SELECT * FROM permisos WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC)
                ]
            ];
        } catch (PDOException $e) {
            error_log("Error en _consultar_matriz_permisos: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al cargar matriz de permisos'];
        }
    }
}