<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\enums\TipoBaseDatos;

class Proveedores extends Conexion
{
    private $id_proveedor;
    private $nombre_proveedor;
    private $servicio;
    private $rif;
    private $direccion;
    private $activo;

    // ====================================================================
    // VALIDACIONES CENTRALIZADAS
    // ====================================================================
    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
            'id_proveedor' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'proveedores', 'campo' => 'id_proveedor']
            ],
            'nombre_proveedor' => [
                'regex' => '/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/',
                'unique' => ['tabla' => 'proveedores', 'campo' => 'nombre_proveedor', 'exclude_field' => 'id_proveedor']
            ],
            'servicio' => [
                'regex' => '/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/'
            ],
            'rif' => [
                'regex' => '/^[VEJG]{1}[0-9]{7,10}$/',
                'unique' => ['tabla' => 'proveedores', 'campo' => 'rif', 'exclude_field' => 'id_proveedor']
            ],
            'direccion' => [
                'regex' => '/^[A-Za-z0-9áéíóúÁÉÍÓÚñÑ\s.,:\/-]{5,255}$/'
            ]
        ];

        $camposPorOperacion = [
            'registrar_proveedor' => ['nombre_proveedor', 'servicio', 'rif', 'direccion'],
            'modificar_proveedor' => ['id_proveedor', 'nombre_proveedor', 'servicio', 'rif', 'direccion'],
            'eliminar_proveedor'  => ['id_proveedor'],
            'consultar_proveedor' => ['id_proveedor']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    // Getters y Setters
    public function set_id_proveedor($id) { $this->id_proveedor = $id; }
    public function get_id_proveedor() { return $this->id_proveedor; }
    public function set_nombre_proveedor($nombre) { $this->nombre_proveedor = $nombre; }
    public function get_nombre_proveedor() { return $this->nombre_proveedor; }
    public function set_servicio($servicio) { $this->servicio = $servicio; }
    public function get_servicio() { return $this->servicio; }
    public function set_rif($rif) { $this->rif = $rif; }
    public function get_rif() { return $this->rif; }
    public function set_direccion($direccion) { $this->direccion = $direccion; }
    public function get_direccion() { return $this->direccion; }
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
        $sql = "SELECT id_proveedor, nombre_proveedor, servicio, rif, direccion 
                FROM proveedores 
                WHERE activo = 1";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar proveedores'];
        }
    }

    // SE USA EN EL MODULO
    private function _consultar_proveedor()
    {
        $sql = "SELECT id_proveedor, nombre_proveedor, servicio, rif, direccion 
                FROM proveedores 
                WHERE id_proveedor = :id_proveedor AND activo = 1";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->bindParam(':id_proveedor', $this->id_proveedor);
            $stmt->execute();
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$datos) {
                return ['estatus' => false, 'mensaje' => 'Proveedor no encontrado'];
            }
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_proveedor: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar el proveedor'];
        }
    }

    // SE USA EN EL MODULO
    private function _registrar_proveedor()
    {
        $sql = "INSERT INTO proveedores (nombre_proveedor, servicio, rif, direccion) 
                VALUES (:nombre_proveedor, :servicio, :rif, :direccion)";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->bindParam(':nombre_proveedor', $this->nombre_proveedor);
            $stmt->bindParam(':servicio', $this->servicio);
            $stmt->bindParam(':rif', $this->rif);
            $stmt->bindParam(':direccion', $this->direccion);
            $stmt->execute();
            $lastId = $this->get_conex(TipoBaseDatos::NEGOCIO)->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Proveedor registrado correctamente', 'lastId' => $lastId];
        } catch (PDOException $e) {
            error_log("Error en _registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar el proveedor'];
        }
    }

    // SE USA EN EL MODULO
    private function _modificar_proveedor()
    {
        $sql = "UPDATE proveedores SET 
                    nombre_proveedor = :nombre_proveedor,
                    servicio = :servicio,
                    rif = :rif,
                    direccion = :direccion
                WHERE id_proveedor = :id_proveedor";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->bindParam(':id_proveedor', $this->id_proveedor);
            $stmt->bindParam(':nombre_proveedor', $this->nombre_proveedor);
            $stmt->bindParam(':servicio', $this->servicio);
            $stmt->bindParam(':rif', $this->rif);
            $stmt->bindParam(':direccion', $this->direccion);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Proveedor modificado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _modificar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al modificar el proveedor'];
        }
    }

    // SE USA EN EL MODULO
    private function _eliminar_proveedor()
    {
        $sql = "UPDATE proveedores SET activo = 0 WHERE id_proveedor = :id_proveedor";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->bindParam(':id_proveedor', $this->id_proveedor);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Proveedor eliminado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar el proveedor'];
        }
    }

}
