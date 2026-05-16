<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\enums\TipoBaseDatos;

class TipoGasto extends Conexion
{
    private $id_tipo_gasto;
    private $nombre_tipo_gasto;
    private $activo;

    // ====================================================================
    // VALIDACIONES CENTRALIZADAS
    // ====================================================================
    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
            'id_tipo_gasto' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'tipo_gasto', 'campo' => 'id_tipo_gasto']
            ],
            'nombre_tipo_gasto' => [
                'regex' => '/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]+$/u',
                'unique' => ['tabla' => 'tipo_gasto', 'campo' => 'nombre_tipo_gasto', 'exclude_field' => 'id_tipo_gasto']
            ]
        ];

        $camposPorOperacion = [
            'registrar_tipo_gasto' => ['nombre_tipo_gasto'],
            'modificar_tipo_gasto' => ['id_tipo_gasto', 'nombre_tipo_gasto'],
            'eliminar_tipo_gasto'  => ['id_tipo_gasto'],
            'consultar_tipo_gasto' => ['id_tipo_gasto']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    // Getters y Setters
    public function set_id_tipo_gasto($id) { $this->id_tipo_gasto = $id; }
    public function get_id_tipo_gasto() { return $this->id_tipo_gasto; }
    public function set_nombre_tipo_gasto($nombre) { $this->nombre_tipo_gasto = $nombre; }
    public function get_nombre_tipo_gasto() { return $this->nombre_tipo_gasto; }
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

    // -----------------------------------------------------------------
    // Métodos privados (acciones)
    // -----------------------------------------------------------------

    // SE USA EN EL MODULO
    private function _consultar()
    {
        $sql = "SELECT * FROM tipo_gasto WHERE activo = 1 ORDER BY id_tipo_gasto";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar tipos de gasto'];
        }
    }
    // SE USA EN EL MODULO
    private function _consultar_tipo_gasto()
    {
        $sql = "SELECT * FROM tipo_gasto WHERE id_tipo_gasto = :id_tipo_gasto AND activo = 1";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->bindParam(':id_tipo_gasto', $this->id_tipo_gasto);
            $stmt->execute();
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$datos) {
                return ['estatus' => false, 'mensaje' => 'Tipo de gasto no encontrado'];
            }
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_tipo_gasto: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar el tipo de gasto'];
        }
    }
    // SE USA EN EL MODULO
    private function _registrar_tipo_gasto()
    {
        $sql = "INSERT INTO tipo_gasto (nombre_tipo_gasto) VALUES (:nombre_tipo_gasto)";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->bindParam(':nombre_tipo_gasto', $this->nombre_tipo_gasto);
            $stmt->execute();
            $lastId = $this->get_conex(TipoBaseDatos::NEGOCIO)->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Tipo de gasto registrado correctamente', 'lastId' => $lastId];
        } catch (PDOException $e) {
            error_log("Error en _registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar el tipo de gasto'];
        }
    }
    // SE USA EN EL MODULO
    private function _modificar_tipo_gasto()
    {
        $sql = "UPDATE tipo_gasto SET nombre_tipo_gasto = :nombre_tipo_gasto WHERE id_tipo_gasto = :id_tipo_gasto";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->bindParam(':id_tipo_gasto', $this->id_tipo_gasto);
            $stmt->bindParam(':nombre_tipo_gasto', $this->nombre_tipo_gasto);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Tipo de gasto actualizado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _modificar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar el tipo de gasto'];
        }
    }
    // SE USA EN EL MODULO
    private function _eliminar_tipo_gasto()
    {
        $sql = "UPDATE tipo_gasto SET activo = 0 WHERE id_tipo_gasto = :id_tipo_gasto";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->bindParam(':id_tipo_gasto', $this->id_tipo_gasto);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Tipo de gasto eliminado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar el tipo de gasto'];
        }
    }
}
