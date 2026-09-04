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

    public static function obtenerReglas(string $operacion): array
    {
        $reglasGenerales = [
            'id_proveedor' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'proveedores', 'campo' => 'id_proveedor']
            ],
            'nombre_proveedor' => [
                'regex' => '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ()\s]+$/',
                'unique' => ['tabla' => 'proveedores', 'campo' => 'nombre_proveedor', 'exclude_field' => 'id_proveedor']
            ],
            'servicio' => ['regex' => '/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/'],
            'rif' => [
                'regex' => '/^[VEJG]{1}[0-9]{7,10}$/',
                'unique' => ['tabla' => 'proveedores', 'campo' => 'rif', 'exclude_field' => 'id_proveedor']
            ],
            'direccion' => ['regex' => '/^[A-Za-z0-9áéíóúÁÉÍÓÚñÑ\s.,:\/-]{3,255}$/']
        ];

        $camposPorOperacion = [
            'registrar_proveedor' => ['nombre_proveedor', 'servicio', 'rif', 'direccion'],
            'modificar_proveedor' => ['id_proveedor', 'nombre_proveedor', 'servicio', 'rif', 'direccion'],
            'eliminar_proveedor'  => ['id_proveedor'],
            'consultar_proveedor' => ['id_proveedor']
        ];

        return isset($camposPorOperacion[$operacion]) 
            ? array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion])) 
            : [];
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

    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            throw new NegocioException("La acción '$accion' no está implementada.", HttpCodigo::BAD_REQUEST->value);
        }
        return $this->$metodo();
    }

    // ACCIONES PRIVADAS
    private function _consultar(): array
    {
        $sql = "SELECT id_proveedor, nombre_proveedor, servicio, rif, direccion FROM proveedores WHERE activo = 1";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute();
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar proveedores'];
        }
    }

    private function _consultar_proveedor(): array
    {
        $sql = "SELECT id_proveedor, nombre_proveedor, servicio, rif, direccion FROM proveedores WHERE id_proveedor = :id_proveedor AND activo = 1";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([':id_proveedor' => $this->id_proveedor]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);

            return $datos ? ['estatus' => true, 'datos' => $datos] : ['estatus' => false, 'mensaje' => 'Proveedor no encontrado'];
        } catch (PDOException $e) {
            error_log("Error en _consultar_proveedor: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar el proveedor'];
        }
    }

    private function _registrar_proveedor(): array
    {
        $sql = "INSERT INTO proveedores (nombre_proveedor, servicio, rif, direccion) VALUES (:nombre_proveedor, :servicio, :rif, :direccion)";
        try {
            $db = $this->get_conex(TipoBaseDatos::NEGOCIO);
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':nombre_proveedor' => $this->nombre_proveedor,
                ':servicio'         => $this->servicio,
                ':rif'              => $this->rif,
                ':direccion'        => $this->direccion
            ]);
            return ['estatus' => true, 'mensaje' => 'Proveedor registrado correctamente', 'lastId' => $db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Error en _registrar_proveedor: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar el proveedor'];
        }
    }

    private function _modificar_proveedor(): array
    {
        $sql = "UPDATE proveedores SET 
                    nombre_proveedor = :nombre_proveedor,
                    servicio = :servicio,
                    rif = :rif,
                    direccion = :direccion
                WHERE id_proveedor = :id_proveedor";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([
                ':id_proveedor'     => $this->id_proveedor,
                ':nombre_proveedor' => $this->nombre_proveedor,
                ':servicio'         => $this->servicio,
                ':rif'              => $this->rif,
                ':direccion'        => $this->direccion
            ]);
            return ['estatus' => true, 'mensaje' => 'Proveedor modificado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _modificar_proveedor: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al modificar el proveedor'];
        }
    }

    private function _eliminar_proveedor(): array
    {
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare("UPDATE proveedores SET activo = 0 WHERE id_proveedor = :id_proveedor");
            $stmt->execute([':id_proveedor' => $this->id_proveedor]);
            return ['estatus' => true, 'mensaje' => 'Proveedor eliminado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar_proveedor: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar el proveedor'];
        }
    }
}