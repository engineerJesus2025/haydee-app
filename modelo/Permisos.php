<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Permisos extends Conexion
{
    private $id_permiso;
    private $accion;
    private $activo;

    // Reglas de validación (por si se usan en el futuro)
    private $reglas = [
        'id_permiso' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'permisos', 'campo' => 'id_permiso']
        ],
        'accion' => [
            'regex' => '/^[A-Za-z_]+$/'
        ]
    ];

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

    // -----------------------------------------------------------------
    // Método de validación (por si se necesita)
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

            if (isset($regla['regex'])) {
                if (!preg_match($regla['regex'], (string)$valor)) {
                    return [
                        'estatus' => false,
                        'mensaje' => "El campo '$campo' no tiene un formato válido."
                    ];
                }
            }

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
     * Consulta todos los permisos.
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
     */
    private function _registrar()
    {
        $validacion = $this->validar(['accion']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

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
     * Editar un permiso existente.
     */
    private function _editar()
    {
        $campos = ['id_permiso', 'accion'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "UPDATE permisos SET accion = :accion WHERE id_permiso = :id";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([
                ':accion' => $this->accion,
                ':id'     => $this->id_permiso
            ]);
            return ['estatus' => true, 'mensaje' => 'Permiso actualizado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _editar (Permisos): " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar el permiso'];
        }
    }

    /**
     * Eliminar (soft delete) un permiso.
     */
    private function _eliminar()
    {
        $validacion = $this->validar(['id_permiso']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

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
     */
    private function _consultar_unico()
    {
        $validacion = $this->validar(['id_permiso']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

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