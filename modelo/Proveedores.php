<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Proveedores extends Conexion
{
    private $id_proveedor;
    private $nombre_proveedor;
    private $servicio;
    private $rif;
    private $direccion;
    private $activo;

    // Reglas de validación centralizadas
    private $reglas = [
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
            'regex' => '/^[A-Za-z0-9áéíóúÁÉÍÓÚñÑ\s,.#\-]+$/'
        ]
    ];

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
    // Método de validación centralizado
    // -----------------------------------------------------------------

    private function validar($campos, $contexto = [])
    {
        foreach ($campos as $campo) {
            if (!isset($this->reglas[$campo])) {
                return [
                    'estatus' => false,
                    'mensaje' => "No hay reglas de validación definidas para el campo '$campo'."
                ];
            }
            $regla = $this->reglas[$campo];

            $getter = 'get_' . $campo;
            if (!method_exists($this, $getter)) {
                return [
                    'estatus' => false,
                    'mensaje' => "El campo '$campo' no tiene un getter definido."
                ];
            }
            $valor = $this->$getter();

            // Requerido
            if ($valor === null) {
                return [
                    'estatus' => false,
                    'mensaje' => "El campo '$campo' es requerido y no se ha establecido."
                ];
            }
            if (is_string($valor) && trim($valor) === '') {
                return [
                    'estatus' => false,
                    'mensaje' => "El campo '$campo' no puede estar vacío."
                ];
            }

            // Validar con expresión regular
            if (isset($regla['regex'])) {
                if (!preg_match($regla['regex'], (string)$valor)) {
                    return [
                        'estatus' => false,
                        'mensaje' => "El campo '$campo' no tiene un formato válido."
                    ];
                }
            }

            // Validar existencia en otra tabla (foránea)
            if (isset($regla['exists'])) {
                $tabla = $regla['exists']['tabla'];
                $campoFor = $regla['exists']['campo'] ?? $campo;
                if (!$this->existeEnTabla($tabla, $campoFor, $valor)) {
                    return [
                        'estatus' => false,
                        'mensaje' => "El valor del campo '$campo' no existe en la tabla $tabla."
                    ];
                }
            }

            // Validar unicidad
            if (isset($regla['unique'])) {
                $tabla = $regla['unique']['tabla'];
                $campoUnico = $regla['unique']['campo'] ?? $campo;
                $excludeField = $regla['unique']['exclude_field'] ?? null;
                $excludeValue = $contexto['exclude_id'] ?? null;
                if (!$this->esUnico($tabla, $campoUnico, $valor, $excludeField, $excludeValue)) {
                    return [
                        'estatus' => false,
                        'mensaje' => "El valor del campo '$campo' ya está registrado."
                    ];
                }
            }
        }
        return ['estatus' => true];
    }

    /**
     * Verifica si un valor existe en una tabla específica.
     */
    private function existeEnTabla($tabla, $campo, $valor)
    {
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = :valor";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':valor', $valor);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en existeEnTabla: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si un valor es único (exceptuando un ID dado).
     */
    private function esUnico($tabla, $campo, $valor, $excludeField = null, $excludeValue = null)
    {
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = :valor";
        if ($excludeField && $excludeValue !== null) {
            $sql .= " AND $excludeField != :exclude_val";
        }
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':valor', $valor);
            if ($excludeField && $excludeValue !== null) {
                $stmt->bindParam(':exclude_val', $excludeValue);
            }
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila['total'] == 0;
        } catch (PDOException $e) {
            error_log("Error en esUnico: " . $e->getMessage());
            return false;
        }
    }

    // -----------------------------------------------------------------
    // Métodos privados (acciones)
    // -----------------------------------------------------------------

    private function _consultar()
    {
        $sql = "SELECT id_proveedor, nombre_proveedor, servicio, rif, direccion 
                FROM proveedores 
                WHERE activo = 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar proveedores'];
        }
    }

    private function _consultar_proveedor()
    {
        $validacion = $this->validar(['id_proveedor']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "SELECT id_proveedor, nombre_proveedor, servicio, rif, direccion 
                FROM proveedores 
                WHERE id_proveedor = :id_proveedor AND activo = 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
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

    private function _registrar()
    {
        $campos = ['nombre_proveedor', 'servicio', 'rif', 'direccion'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "INSERT INTO proveedores (nombre_proveedor, servicio, rif, direccion) 
                VALUES (:nombre_proveedor, :servicio, :rif, :direccion)";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':nombre_proveedor', $this->nombre_proveedor);
            $stmt->bindParam(':servicio', $this->servicio);
            $stmt->bindParam(':rif', $this->rif);
            $stmt->bindParam(':direccion', $this->direccion);
            $stmt->execute();
            $lastId = $this->get_conex('negocio')->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Proveedor registrado correctamente', 'lastId' => $lastId];
        } catch (PDOException $e) {
            error_log("Error en _registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar el proveedor'];
        }
    }

    private function _modificar()
    {
        $campos = ['id_proveedor', 'nombre_proveedor', 'servicio', 'rif', 'direccion'];
        $contexto = ['exclude_id' => $this->id_proveedor];
        $validacion = $this->validar($campos, $contexto);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "UPDATE proveedores SET 
                    nombre_proveedor = :nombre_proveedor,
                    servicio = :servicio,
                    rif = :rif,
                    direccion = :direccion
                WHERE id_proveedor = :id_proveedor";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
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

    private function _eliminar()
    {
        $validacion = $this->validar(['id_proveedor']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "UPDATE proveedores SET activo = 0 WHERE id_proveedor = :id_proveedor";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_proveedor', $this->id_proveedor);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Proveedor eliminado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar el proveedor'];
        }
    }

    private function _lastId()
    {
        $sql = "SELECT MAX(id_proveedor) as last_id FROM proveedores";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $dato = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $dato];
        } catch (PDOException $e) {
            error_log("Error en _lastId: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener último ID'];
        }
    }
}
?>