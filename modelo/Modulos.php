<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Modulos extends Conexion
{
    private $id_modulo;
    private $nombre;
    private $activo;

    // ====================================================================
    // VALIDACIONES CENTRALIZADAS
    // ====================================================================
    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
            'id_modulo' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'modulos', 'campo' => 'id_modulo']
            ],
            'nombre' => [
                'regex' => '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ _\s]{3,30}$/'
            ]
        ];

        // Nombres estandarizados
        $camposPorOperacion = [
            'registrar_modulo' => ['nombre'],
            'modificar_modulo' => ['id_modulo', 'nombre'],
            'eliminar_modulo'  => ['id_modulo'],
            'consultar_modulo' => ['id_modulo']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    // Getters y Setters
    public function set_id_modulo($id) { $this->id_modulo = $id; }
    public function get_id_modulo() { return $this->id_modulo; }
    public function set_nombre($nombre) { $this->nombre = $nombre; }
    public function get_nombre() { return $this->nombre; }
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
     * Consulta todos los módulos.
     // SE USA EN EL MODULO
     */
    private function _consultar()
    {
        $sql = "SELECT * FROM modulos WHERE activo = 1";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar módulos'];
        }
    }
    /**
     * Registrar un nuevo módulo.
     // SE USA EN EL MODULO
     */
    private function _registrar_modulo()
    {
        $sql = "INSERT INTO modulos (nombre, activo) VALUES (:nombre, 1)";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':nombre' => $this->nombre]);
            $lastId = $this->get_conex('seguridad')->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Módulo registrado correctamente', 'id' => $lastId];
        } catch (PDOException $e) {
            error_log("Error en _registrar (Modulos): " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar el módulo'];
        }
    }

    /**
     * modificar un módulo existente.
     // SE USA EN EL MODULO
     */
    private function _modificar_modulo()
    {
        $sql = "UPDATE modulos SET nombre = :nombre WHERE id_modulo = :id";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([
                ':nombre' => $this->nombre,
                ':id'     => $this->id_modulo
            ]);
            return ['estatus' => true, 'mensaje' => 'Módulo actualizado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _modificar (Modulos): " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar el módulo'];
        }
    }

    /**
     * Eliminar (soft delete) un módulo.
     // SE USA EN EL MODULO
     */
    private function _eliminar_modulo()
    {
        $sql = "UPDATE modulos SET activo = 0 WHERE id_modulo = :id";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':id' => $this->id_modulo]);
            return ['estatus' => true, 'mensaje' => 'Módulo eliminado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar (Modulos): " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar el módulo'];
        }
    }

    /**
     * Consultar un módulo específico por ID.
     // SE USA EN EL MODULO
     */
    private function _consultar_modulo()
    {
        $sql = "SELECT * FROM modulos WHERE id_modulo = :id AND activo = 1";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':id' => $this->id_modulo]);
            $dato = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$dato) {
                return ['estatus' => false, 'mensaje' => 'Módulo no encontrado'];
            }
            return ['estatus' => true, 'datos' => $dato];
        } catch (PDOException $e) {
            error_log("Error en _consultar_unico (Modulos): " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar el módulo'];
        }
    }
}
?>