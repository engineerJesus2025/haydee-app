<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class TipoGasto extends Conexion
{
    private $id_tipo_gasto;
    private $nombre_tipo_gasto;
    private $activo;

    // Reglas de validación centralizadas
    private $reglas = [
        'id_tipo_gasto' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'tipo_gasto', 'campo' => 'id_tipo_gasto']
        ],
        'nombre_tipo_gasto' => [
            'regex' => '/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]+$/u',
            'unique' => ['tabla' => 'tipo_gasto', 'campo' => 'nombre_tipo_gasto', 'exclude_field' => 'id_tipo_gasto']
        ]
    ];

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
     * Verifica si un valor existe en una tabla específica (usa BD negocio).
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
        $sql = "SELECT * FROM tipo_gasto WHERE activo = 1 ORDER BY id_tipo_gasto";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar tipos de gasto'];
        }
    }

    private function _consultar_tipo_gasto()
    {
        $validacion = $this->validar(['id_tipo_gasto']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "SELECT * FROM tipo_gasto WHERE id_tipo_gasto = :id_tipo_gasto AND activo = 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
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

    private function _registrar()
    {
        $validacion = $this->validar(['nombre_tipo_gasto']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "INSERT INTO tipo_gasto (nombre_tipo_gasto) VALUES (:nombre_tipo_gasto)";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':nombre_tipo_gasto', $this->nombre_tipo_gasto);
            $stmt->execute();
            $lastId = $this->get_conex('negocio')->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Tipo de gasto registrado correctamente', 'lastId' => $lastId];
        } catch (PDOException $e) {
            error_log("Error en _registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar el tipo de gasto'];
        }
    }

    private function _modificar()
    {
        $campos = ['id_tipo_gasto', 'nombre_tipo_gasto'];
        $contexto = ['exclude_id' => $this->id_tipo_gasto];
        $validacion = $this->validar($campos, $contexto);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "UPDATE tipo_gasto SET nombre_tipo_gasto = :nombre_tipo_gasto WHERE id_tipo_gasto = :id_tipo_gasto";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_tipo_gasto', $this->id_tipo_gasto);
            $stmt->bindParam(':nombre_tipo_gasto', $this->nombre_tipo_gasto);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Tipo de gasto actualizado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _modificar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar el tipo de gasto'];
        }
    }

    private function _eliminar()
    {
        $validacion = $this->validar(['id_tipo_gasto']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "UPDATE tipo_gasto SET activo = 0 WHERE id_tipo_gasto = :id_tipo_gasto";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_tipo_gasto', $this->id_tipo_gasto);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Tipo de gasto eliminado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar el tipo de gasto'];
        }
    }

    private function _lastId()
    {
        $sql = "SELECT MAX(id_tipo_gasto) as last_id FROM tipo_gasto";
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