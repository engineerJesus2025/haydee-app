<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Permisos extends Conexion
{
    private $id_permiso;
    private $accion;
    private $activo;

    // ====================================================================
    // VALIDACIONES CENTRALIZADAS
    // ====================================================================
    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
            'id_permiso' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'permisos', 'campo' => 'id_permiso']
            ],
            'accion' => [
                'regex' => '/^[A-Za-z_]+$/'
            ]
        ];

        $camposPorOperacion = [
            'registrar_permiso' => ['accion'],
            'modificar_permiso' => ['id_permiso', 'accion'],
            'eliminar_permiso'  => ['id_permiso'],
            'consultar_permiso' => ['id_permiso']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    // Getters y Setters
    public function set_id_permiso($id) { $this->id_permiso = $id; }
    public function get_id_permiso() { return $this->id_permiso; }
    public function set_accion($accion) { $this->accion = $accion; }
    public function get_accion() { return $this->accion; }
    public function set_activo($activo) { $this->activo = $activo; }
    public function get_activo() { return $this->activo; }

    /**
     * Enruta la acción al método privado correspondiente.
     */
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
            return ['estatus' => false, 'mensaje' => 'Ocurrió un error interno en el servidor.'];
        }
    }

    // ====================================================================
    // MÉTODOS PRIVADOS (CRUD)
    // ====================================================================

    /**
     * Consulta todos los permisos.
     // SE USA EN EL MODULO
     */
    private function _consultar()
    {
        $sql = "SELECT * FROM permisos WHERE activo = 1";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar permisos'];
        }
    }

    /**
     * Valida que un array de IDs de permisos existan en la base de datos.
     * La propiedad id_permiso debe contener el array de IDs a validar.
     // SE USA EN ROLES
     */
    private function _validar_permisos_usuarios()
    {
        $ids = $this->id_permiso; // Se espera que sea un array

        if (!is_array($ids) || empty($ids)) {
            return [
                'estatus' => false,
                'mensaje' => 'Datos inválidos: se esperaba un arreglo de IDs.'
            ];
        }

        // Crear placeholders para consulta IN
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT id_permiso FROM permisos WHERE id_permiso IN ($placeholders)";

        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute($ids);
            $encontrados = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $faltantes = array_diff($ids, $encontrados);

            if (empty($faltantes)) {
                return [
                    'estatus' => true,
                    'mensaje' => 'Todos los permisos existen.'
                ];
            } else {
                return [
                    'estatus' => false,
                    'mensaje' => 'Se detectaron permisos inexistentes.',
                    'ids_no_encontrados' => array_values($faltantes)
                ];
            }
        } catch (PDOException $e) {
            error_log("Error en _validar_permisos_usuarios: " . $e->getMessage());
            return [
                'estatus' => false,
                'mensaje' => 'Error interno al validar permisos.'
            ];
        }
    }


    /**
     * Registrar un nuevo permiso.
     // SE USA EN EL MODULO
     */
    private function _registrar_permiso()
    {
        $sql = "INSERT INTO permisos (accion, activo) VALUES (:accion, 1)";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':accion' => $this->accion]);
            $lastId = $this->get_conex('seguridad')->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Permiso registrado correctamente', 'id' => $lastId];
        } catch (PDOException $e) {
            error_log("Error en _registrar (Permisos): " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar el permiso'];
        }
    }

    /**
     * modificar un permiso existente.
     // SE USA EN EL MODULO
     */
    private function _modificar_permiso()
    {

        $sql = "UPDATE permisos SET accion = :accion WHERE id_permiso = :id";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([
                ':accion' => $this->accion,
                ':id'     => $this->id_permiso
            ]);
            return ['estatus' => true, 'mensaje' => 'Permiso actualizado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _modificar (Permisos): " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar el permiso'];
        }
    }

    /**
     * Eliminar (soft delete) un permiso.
     // SE USA EN EL MODULO
     */
    private function _eliminar_permiso()
    {

        $sql = "UPDATE permisos SET activo = 0 WHERE id_permiso = :id";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':id' => $this->id_permiso]);
            return ['estatus' => true, 'mensaje' => 'Permiso eliminado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar (Permisos): " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar el permiso'];
        }
    }

    /**
     * Consultar un permiso específico por ID.
     // SE USA EN EL MODULO
     */
    private function _consultar_permiso()
    {
        $sql = "SELECT * FROM permisos WHERE id_permiso = :id AND activo = 1";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':id' => $this->id_permiso]);
            $dato = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$dato) {
                return ['estatus' => false, 'mensaje' => 'Permiso no encontrado'];
            }
            return ['estatus' => true, 'datos' => $dato];
        } catch (PDOException $e) {
            error_log("Error en _consultar_unico (Permisos): " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar el permiso'];
        }
    }
}
?>