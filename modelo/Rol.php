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

    // Propiedades para la asignación de permisos
    private $permisos_asignados = []; 

    // ====================================================================
    // VALIDACIONES CENTRALIZADAS
    // ====================================================================
    public static function obtenerReglas($operacion) {
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
            'registrar_rol' => ['nombre','permisos'],
            'modificar_rol' => ['id_rol', 'nombre','permisos'],
            'eliminar_rol'  => ['id_rol'],
            'consultar_rol' => ['id_rol'],
            'consultar_permisos_asignados' => ['id_rol'],
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

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
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
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
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute();
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar roles'];
        }
    }

    /**
     * Consultar un rol específico y sus permisos asignados
     */
    private function _consultar_rol()
    {
        try {
            // Buscamos los datos básicos del rol
            $sqlRol = "SELECT * FROM roles WHERE id_rol = :id AND activo = 1";
            $stmtRol = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sqlRol);
            $stmtRol->execute([':id' => $this->id_rol]);
            $rol = $stmtRol->fetch(PDO::FETCH_ASSOC);

            if (!$rol) {
                return ['estatus' => false, 'mensaje' => 'Rol no encontrado'];
            }

            // Buscamos los permisos asociados a este rol
            $sqlPermisos = "SELECT modulo_id, permiso_id FROM asignacion_permisos WHERE rol_id = :id";
            $stmtPermisos = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sqlPermisos);
            $stmtPermisos->execute([':id' => $this->id_rol]);
            $permisos = $stmtPermisos->fetchAll(PDO::FETCH_ASSOC);

            // 3. Devolvemos la estructura exacta que espera el JavaScript
            return [
                'estatus' => true,
                'datos' => [
                    'rol' => $rol,
                    'permisos' => $permisos
                ]
            ];
        } catch (PDOException $e) {
            error_log("Error en _consultar_rol: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar el rol'];
        }
    }

    // ====================================================================
    // MÉTODOS CRUD CON TRANSACCIONES
    // ====================================================================

    private function _registrar_rol()
    {
        $pdo = $this->get_conex(TipoBaseDatos::SEGURIDAD);
        
        try {
            $pdo->beginTransaction();

            // 1. Insertamos el rol
            $sql = "INSERT INTO roles (nombre, activo) VALUES (:nombre, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':nombre' => $this->nombre]);

            // 2. Capturamos el ID recién generado para usarlo en los permisos
            $this->id_rol = $pdo->lastInsertId();

            // 3. Delegamos la inserción a nuestro método auxiliar
            $this->_sincronizar_permisos($pdo);

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Rol registrado exitosamente'];

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error en _registrar_rol: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar el rol'];
        }
    }

    private function _modificar_rol()
    {
        $pdo = $this->get_conex(TipoBaseDatos::SEGURIDAD);
        
        try {
            $pdo->beginTransaction();

            // 1. Actualizamos el nombre del rol
            $sql = "UPDATE roles SET nombre = :nombre WHERE id_rol = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $this->nombre,
                ':id' => $this->id_rol
            ]);

            // 2. Delegamos la sincronización de permisos al método auxiliar
            $this->_sincronizar_permisos($pdo);

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Rol actualizado correctamente'];

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error en _modificar_rol: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar el rol'];
        }
    }

    private function _eliminar_rol()
    {
        $sql = "UPDATE roles SET activo = 0 WHERE id_rol = :id";
        try {
            $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql)->execute([':id' => $this->id_rol]);
            return ['estatus' => true, 'mensaje' => 'Rol eliminado'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar: ' . $e->getMessage()];
        }
    }

    // ====================================================================
    // MÉTODOS AUXILIARES
    // ====================================================================

    /**
     * Sincroniza los permisos en la tabla puente.
     * Al recibir $pdo como parámetro, se acopla a la transacción activa.
     */
    private function _sincronizar_permisos($pdo)
    {
        //  Limpiamos cualquier permiso anterior (Útil tanto para modificar como para evitar basura)
        $sqlDelete = "DELETE FROM asignacion_permisos WHERE rol_id = :id";
        $stmtDelete = $pdo->prepare($sqlDelete);
        $stmtDelete->execute([':id' => $this->id_rol]);

        // Si hay nuevos permisos en el arreglo, los insertamos
        if (!empty($this->permisos_asignados)) {
            $sqlInsert = "INSERT INTO asignacion_permisos (rol_id, modulo_id, permiso_id) 
                          VALUES (:rol_id, :modulo_id, :permiso_id)";
            $stmtInsert = $pdo->prepare($sqlInsert);

            foreach ($this->permisos_asignados as $permisoId) {
                // Verificamos que las llaves existan en tu JSON
                if (isset($permisoId['modulo_id']) && isset($permisoId['permiso_id'])) {
                    $stmtInsert->execute([
                        ':rol_id' => $this->id_rol,
                        ':modulo_id' => $permisoId['modulo_id'],
                        ':permiso_id' => $permisoId['permiso_id']
                    ]);
                }
            }
        }
    }

    // ====================================================================
    // LÓGICA DE NEGOCIO (PERMISOS) - INTEGRADA
    // ====================================================================

    /**
     * Valida que un array de asignaciones de permisos sea correcto.
     * Verifica que cada elemento tenga modulo_id y un array de permisos, y que existan.
     */
    private function validarAsignacionesPermisos($asignaciones)
    {
        if (!is_array($asignaciones)) {
            return ['estatus' => false, 'mensaje' => 'Los permisos asignados deben ser un array.'];
        }

        // Obtener todos los módulos y permisos existentes (activos) de una vez para optimizar
        try {
            $modulosExistentes = $this->get_conex(TipoBaseDatos::SEGURIDAD)
                ->query("SELECT id_modulo FROM modulos WHERE activo = 1")
                ->fetchAll(PDO::FETCH_COLUMN);
            $permisosExistentes = $this->get_conex(TipoBaseDatos::SEGURIDAD)
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
     * Obtiene la lista de permisos asignados a un rol.
     */
    private function _consultar_permisos_asignados()
    {
        $sql = "SELECT ap.modulo_id, ap.permiso_id, m.nombre as modulo, p.accion as permiso
                FROM asignacion_permisos ap
                JOIN modulos m ON ap.modulo_id = m.id_modulo
                JOIN permisos p ON ap.permiso_id = p.id_permiso
                WHERE ap.rol_id = :id";
        
        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([':id' => $this->id_rol]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $resultados];
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
            $modulos = $this->get_conex(TipoBaseDatos::SEGURIDAD)
                ->query("SELECT * FROM modulos WHERE activo = 1")
                ->fetchAll(PDO::FETCH_ASSOC);
            $permisos = $this->get_conex(TipoBaseDatos::SEGURIDAD)
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
