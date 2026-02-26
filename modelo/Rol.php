<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Rol extends Conexion
{
    // ====================================================================
    // PROPIEDADES
    // ====================================================================
    private $id_rol;
    private $nombre;
    private $activo;

    // Propiedades para la asignación de permisos
    private $permisos_asignados = []; 

    // ====================================================================
    // VALIDACIONES
    // ====================================================================
    private $reglas = [
        'id_rol' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'roles', 'campo' => 'id_rol']
        ],
        'nombre' => [
            'regex' => '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,50}$/',
            'unique' => ['tabla' => 'roles', 'campo' => 'nombre', 'exclude_field' => 'id_rol']
        ]
    ];

    // ====================================================================
    // GETTERS Y SETTERS
    // ====================================================================
    public function set_id_rol($id) { $this->id_rol = $id; }
    public function get_id_rol() { return $this->id_rol; }
    
    public function set_nombre($nombre) { $this->nombre = $nombre; }
    public function get_nombre() { return $this->nombre; }
    
    public function set_activo($activo) { $this->activo = $activo; }
    public function get_activo() { return $this->activo; }

    public function set_permisos_asignados($permisos) { 
        $this->permisos_asignados = $permisos; 
    }
    public function get_permisos_asignados() { 
        return $this->permisos_asignados; 
    }

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
    // VALIDACIÓN CENTRALIZADA (Con conexión a Seguridad)
    // ====================================================================
    private function validar($campos, $contexto = [])
    {
        foreach ($campos as $campo) {
            if (!isset($this->reglas[$campo])) continue;
            $regla = $this->reglas[$campo];
            $getter = 'get_' . $campo;
            $valor = $this->$getter();

            // Requerido
            if ($valor === null || (is_string($valor) && trim($valor) === '')) {
                return ['estatus' => false, 'mensaje' => "El campo '$campo' es obligatorio."];
            }

            // Regex
            if (isset($regla['regex']) && !preg_match($regla['regex'], (string)$valor)) {
                return ['estatus' => false, 'mensaje' => "Formato inválido para '$campo'."];
            }

            // Existencia (BD Seguridad)
            if (isset($regla['exists'])) {
                if (!$this->existeEnTabla($regla['exists']['tabla'], $regla['exists']['campo'], $valor)) {
                    return ['estatus' => false, 'mensaje' => "El valor de '$campo' no existe."];
                }
            }

            // Unicidad (BD Seguridad)
            if (isset($regla['unique'])) {
                $excludeValue = $contexto['exclude_id'] ?? null;
                if (!$this->esUnico($regla['unique']['tabla'], $regla['unique']['campo'], $valor, $regla['unique']['exclude_field'] ?? null, $excludeValue)) {
                    return ['estatus' => false, 'mensaje' => "El '$campo' ya está registrado."];
                }
            }
        }
        return ['estatus' => true];
    }

    private function existeEnTabla($tabla, $campo, $valor) {
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = :valor";
        $stmt = $this->get_conex('seguridad')->prepare($sql);
        $stmt->execute([':valor' => $valor]);
        return $stmt->fetchColumn() > 0;
    }

    private function esUnico($tabla, $campo, $valor, $excludeField = null, $excludeValue = null) {
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = :valor";
        if ($excludeField && $excludeValue) $sql .= " AND $excludeField != :exclude_val";
        
        $stmt = $this->get_conex('seguridad')->prepare($sql);
        $stmt->bindParam(':valor', $valor);
        if ($excludeField && $excludeValue) $stmt->bindParam(':exclude_val', $excludeValue);
        
        $stmt->execute();
        return $stmt->fetchColumn() == 0;
    }

    // ====================================================================
    // LÓGICA DE NEGOCIO (ROLES)
    // ====================================================================

    private function _verificar_nombre()
    {
        $v = $this->validar(['nombre']);
        if (!$v['estatus']) {
            return $v;
        }

        $sql = "SELECT id_rol FROM roles WHERE nombre = :nombre AND activo = 1 LIMIT 1";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':nombre' => $this->nombre]);
            $existe = $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
            return ['estatus' => true, 'existe' => $existe];
        } catch (PDOException $e) {
            error_log("Error en _verificar_nombre: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al verificar el nombre del rol'];
        }
    }

    private function _consultar()
    {
        $sql = "SELECT * FROM roles WHERE activo = 1 ORDER BY id_rol";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute();
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar roles'];
        }
    }

    private function _consultar_rol()
    {
        $v = $this->validar(['id_rol']);
        if (!$v['estatus']) return $v;

        $sql = "SELECT * FROM roles WHERE id_rol = :id AND activo = 1";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':id' => $this->id_rol]);
            $dato = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dato) return ['estatus' => false, 'mensaje' => 'Rol no encontrado'];
            return ['estatus' => true, 'datos' => $dato];
        } catch (PDOException $e) {
            error_log("Error en _consultar_rol: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar rol'];
        }
    }

    private function _registrar()
    {
        $v = $this->validar(['nombre']);
        if (!$v['estatus']) return $v;

        $sql = "INSERT INTO roles (nombre, activo) VALUES (:nombre, 1)";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':nombre' => $this->nombre]);
            $lastId = $this->get_conex('seguridad')->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Rol creado', 'lastId' => $lastId];
        } catch (PDOException $e) {
            error_log("Error en _registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar rol: ' . $e->getMessage()];
        }
    }

    private function _modificar()
    {
        $v = $this->validar(['id_rol', 'nombre'], ['exclude_id' => $this->id_rol]);
        if (!$v['estatus']) return $v;

        $sql = "UPDATE roles SET nombre = :nombre WHERE id_rol = :id";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':nombre' => $this->nombre, ':id' => $this->id_rol]);
            return ['estatus' => true, 'mensaje' => 'Rol actualizado'];
        } catch (PDOException $e) {
            error_log("Error en _modificar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar: ' . $e->getMessage()];
        }
    }

    private function _eliminar()
    {
        $v = $this->validar(['id_rol']);
        if (!$v['estatus']) return $v;

        $sql = "UPDATE roles SET activo = 0 WHERE id_rol = :id";
        try {
            $this->get_conex('seguridad')->prepare($sql)->execute([':id' => $this->id_rol]);
            return ['estatus' => true, 'mensaje' => 'Rol eliminado'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar: ' . $e->getMessage()];
        }
    }

    // ====================================================================
    // LÓGICA DE NEGOCIO (PERMISOS) - INTEGRADA
    // ====================================================================

    /**
     * Valida que un array de asignaciones de permisos sea correcto.
     * Verifica que cada elemento tenga modulo_id y un array de permisos, y que existan.
     * @param array $asignaciones
     * @return array ['estatus' => bool, 'mensaje' => string]
     */
    private function validarAsignacionesPermisos($asignaciones)
    {
        if (!is_array($asignaciones)) {
            return ['estatus' => false, 'mensaje' => 'Los permisos asignados deben ser un array.'];
        }

        // Obtener todos los módulos y permisos existentes (activos) de una vez para optimizar
        try {
            $modulosExistentes = $this->get_conex('seguridad')
                ->query("SELECT id_modulo FROM modulos WHERE activo = 1")
                ->fetchAll(PDO::FETCH_COLUMN);
            $permisosExistentes = $this->get_conex('seguridad')
                ->query("SELECT id_permiso FROM permisos WHERE activo = 1")
                ->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error al cargar módulos/permisos para validación: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error interno al validar permisos.'];
        }

        foreach ($asignaciones as $index => $asignacion) {
            // Validar que tenga modulo_id
            if (!isset($asignacion['modulo_id'])) {
                return ['estatus' => false, 'mensaje' => "La asignación #$index no tiene modulo_id."];
            }
            // Validar que el módulo exista
            if (!in_array($asignacion['modulo_id'], $modulosExistentes)) {
                return ['estatus' => false, 'mensaje' => "El módulo ID {$asignacion['modulo_id']} no existe o está inactivo."];
            }

            // Validar que tenga permisos (array)
            if (!isset($asignacion['permisos']) || !is_array($asignacion['permisos'])) {
                return ['estatus' => false, 'mensaje' => "La asignación #$index no tiene un array de permisos válido."];
            }

            // Validar cada permiso individualmente
            foreach ($asignacion['permisos'] as $permisoId) {
                if (!in_array($permisoId, $permisosExistentes)) {
                    return ['estatus' => false, 'mensaje' => "El permiso ID $permisoId no existe o está inactivo."];
                }
            }
        }
        return ['estatus' => true];
    }

    /**
     * Sincroniza los permisos de un rol (Borrar anteriores -> Insertar nuevos)
     */
    private function _sincronizar_permisos()
    {
        $v = $this->validar(['id_rol']);
        if (!$v['estatus']) return $v;

        // Validar que permisos_asignados sea un array (puede ser vacío)
        if (!is_array($this->permisos_asignados)) {
            return ['estatus' => false, 'mensaje' => 'Los permisos asignados deben ser un array.'];
        }

        // Validar cada asignación si hay elementos
        if (!empty($this->permisos_asignados)) {
            $val = $this->validarAsignacionesPermisos($this->permisos_asignados);
            if (!$val['estatus']) return $val;
        }

        $pdo = $this->get_conex('seguridad');
        
        try {
            $pdo->beginTransaction();

            // 1. Eliminar permisos anteriores
            $sqlDel = "DELETE FROM asignacion_permisos WHERE rol_id = :rol_id";
            $stmtDel = $pdo->prepare($sqlDel);
            $stmtDel->execute([':rol_id' => $this->id_rol]);

            // 2. Insertar nuevos permisos
            if (!empty($this->permisos_asignados)) {
                $sqlIns = "INSERT INTO asignacion_permisos (rol_id, modulo_id, permiso_id) VALUES (:rol, :mod, :per)";
                $stmtIns = $pdo->prepare($sqlIns);

                foreach ($this->permisos_asignados as $asignacion) {
                    $modulo_id = $asignacion['modulo_id'];
                    // $asignacion['permisos'] es un ARRAY de IDs
                    foreach ($asignacion['permisos'] as $permiso_id) {
                        $stmtIns->execute([
                            ':rol' => $this->id_rol,
                            ':mod' => $modulo_id,
                            ':per' => $permiso_id
                        ]);
                    }
                }
            }

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Permisos actualizados correctamente'];

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Error en _sincronizar_permisos: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al asignar permisos: ' . $e->getMessage()];
        }
    }

    /**
     * Obtiene la lista de permisos asignados a un rol.
     */
    private function _consultar_permisos_asignados()
    {
        $v = $this->validar(['id_rol']);
        if (!$v['estatus']) return $v;

        $sql = "SELECT ap.modulo_id, ap.permiso_id, m.nombre as modulo, p.accion as permiso
                FROM asignacion_permisos ap
                JOIN modulos m ON ap.modulo_id = m.id_modulo
                JOIN permisos p ON ap.permiso_id = p.id_permiso
                WHERE ap.rol_id = :id";
        
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':id' => $this->id_rol]);
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar_permisos_asignados: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar permisos'];
        }
    }

    /**
     * Consulta auxiliar para obtener TODOS los módulos y permisos disponibles
     */
    private function _consultar_matriz_permisos()
    {
        try {
            $modulos = $this->get_conex('seguridad')
                ->query("SELECT * FROM modulos WHERE activo = 1")
                ->fetchAll(PDO::FETCH_ASSOC);
            $permisos = $this->get_conex('seguridad')
                ->query("SELECT * FROM permisos WHERE activo = 1")
                ->fetchAll(PDO::FETCH_ASSOC);

            return [
                'estatus' => true,
                'datos' => [
                    'modulos' => $modulos,
                    'permisos' => $permisos
                ]
            ];
        } catch (PDOException $e) {
            error_log("Error en _consultar_matriz_permisos: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al cargar matriz de permisos'];
        }
    }
}
?>