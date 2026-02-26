<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Modulos extends Conexion
{
    private $id_modulo;
    private $nombre;
    private $activo;

    // Reglas de validación (aunque no se usen en este modelo, las dejamos por consistencia)
    private $reglas = [
        'id_modulo' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'modulos', 'campo' => 'id_modulo']
        ],
        'nombre' => [
            'regex' => '/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/'
        ]
    ];

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

    // -----------------------------------------------------------------
    // Método de validación (por si se necesita en el futuro)
    // -----------------------------------------------------------------
    private function validar($campos)
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

            // Validar existencia en otra tabla
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
        }
        return ['estatus' => true];
    }

    /**
     * Verifica si un valor existe en una tabla específica (usa BD seguridad).
     */
    private function existeEnTabla($tabla, $campo, $valor)
    {
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = :valor";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->bindParam(':valor', $valor);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en existeEnTabla: " . $e->getMessage());
            return false;
        }
    }

    // ====================================================================
    // MÉTODOS PRIVADOS (CRUD)
    // ====================================================================
    
    /**
     * Consulta todos los módulos.
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
     */
    private function _registrar()
    {
        $validacion = $this->validar(['nombre']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

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
     */
    private function _modificar()
    {
        $campos = ['id_modulo', 'nombre'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

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
     */
    private function _eliminar()
    {
        $validacion = $this->validar(['id_modulo']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

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
     */
    private function _consultar_unico()
    {
        $validacion = $this->validar(['id_modulo']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

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