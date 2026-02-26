<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Banco extends Conexion
{
    private $id_banco;
    private $nombre_banco;
    private $codigo;
    private $numero_cuenta;
    private $telefono_afiliado;
    private $rif;
    private $activo;

    // Reglas de validación centralizadas
    private $reglas = [
        'id_banco' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'bancos', 'campo' => 'id_banco']
        ],
        'nombre_banco' => [
            'regex' => '/^[A-Za-z ]{3,30}$/'
        ],
        'codigo' => [
            'regex' => '/^\d{4}$/'
        ],
        'numero_cuenta' => [
            'regex' => '/^\d{18,30}$/',
            'unique' => ['tabla' => 'bancos', 'campo' => 'numero_cuenta', 'exclude_field' => 'id_banco']
        ],
        'telefono_afiliado' => [
            'regex' => '/^\d{11}$/'
        ],
        'rif' => [
            'regex' => '/^[VEJG]{1}[0-9]{7,10}$/'
        ]
    ];

    // Getters y Setters
    public function set_id_banco($id) { $this->id_banco = $id; }
    public function get_id_banco() { return $this->id_banco; }
    public function set_nombre_banco($nombre) { $this->nombre_banco = $nombre; }
    public function get_nombre_banco() { return $this->nombre_banco; }
    public function set_codigo($codigo) { $this->codigo = $codigo; }
    public function get_codigo() { return $this->codigo; }
    public function set_numero_cuenta($num) { $this->numero_cuenta = $num; }
    public function get_numero_cuenta() { return $this->numero_cuenta; }
    public function set_telefono_afiliado($tel) { $this->telefono_afiliado = $tel; }
    public function get_telefono_afiliado() { return $this->telefono_afiliado; }
    public function set_rif($rif) { $this->rif = $rif; }
    public function get_rif() { return $this->rif; }
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

            // Requerido (todos lo son en este modelo)
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

    /**
     * Verifica si ya existe un banco con el mismo número de cuenta (para validación rápida).
     */
    private function _validar()
    {
        $validacion = $this->validar(['numero_cuenta']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "SELECT id_banco FROM bancos WHERE numero_cuenta = :numero_cuenta AND activo = 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':numero_cuenta', $this->numero_cuenta);
            $stmt->execute();
            $existe = $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
            return ['estatus' => true, 'existe' => $existe];
        } catch (PDOException $e) {
            error_log("Error en _validar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al verificar banco'];
        }
    }

    /**
     * Lista todos los bancos activos.
     */
    private function _consultar()
    {
        $sql = "SELECT * FROM bancos WHERE activo = 1 ORDER BY id_banco";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar bancos'];
        }
    }

    /**
     * Consulta un banco específico por ID.
     */
    private function _consultar_banco()
    {
        $validacion = $this->validar(['id_banco']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "SELECT * FROM bancos WHERE id_banco = :id_banco AND activo = 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_banco', $this->id_banco);
            $stmt->execute();
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$datos) {
                return ['estatus' => false, 'mensaje' => 'Banco no encontrado'];
            }
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_banco: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar el banco'];
        }
    }

    /**
     * Registra un nuevo banco.
     */
    private function _registrar()
    {
        $campos = ['nombre_banco', 'codigo', 'numero_cuenta', 'telefono_afiliado', 'rif'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "INSERT INTO bancos (nombre_banco, codigo, numero_cuenta, telefono_afiliado, rif)
                VALUES (:nombre_banco, :codigo, :numero_cuenta, :telefono_afiliado, :rif)";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':nombre_banco', $this->nombre_banco);
            $stmt->bindParam(':codigo', $this->codigo);
            $stmt->bindParam(':numero_cuenta', $this->numero_cuenta);
            $stmt->bindParam(':telefono_afiliado', $this->telefono_afiliado);
            $stmt->bindParam(':rif', $this->rif);
            $stmt->execute();
            $lastId = $this->get_conex('negocio')->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Banco registrado correctamente', 'lastId' => $lastId];
        } catch (PDOException $e) {
            error_log("Error en _registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar el banco'];
        }
    }

    /**
     * Actualiza un banco existente.
     */
    private function _modificar()
    {
        $campos = ['id_banco', 'nombre_banco', 'codigo', 'numero_cuenta', 'telefono_afiliado', 'rif'];
        $contexto = ['exclude_id' => $this->id_banco];
        $validacion = $this->validar($campos, $contexto);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "UPDATE bancos SET 
                    nombre_banco = :nombre_banco,
                    codigo = :codigo,
                    numero_cuenta = :numero_cuenta,
                    telefono_afiliado = :telefono_afiliado,
                    rif = :rif
                WHERE id_banco = :id_banco";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_banco', $this->id_banco);
            $stmt->bindParam(':nombre_banco', $this->nombre_banco);
            $stmt->bindParam(':codigo', $this->codigo);
            $stmt->bindParam(':numero_cuenta', $this->numero_cuenta);
            $stmt->bindParam(':telefono_afiliado', $this->telefono_afiliado);
            $stmt->bindParam(':rif', $this->rif);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Banco actualizado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _modificar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar el banco'];
        }
    }

    /**
     * Elimina un banco (soft delete).
     */
    private function _eliminar()
    {
        $validacion = $this->validar(['id_banco']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "UPDATE bancos SET activo = 0 WHERE id_banco = :id_banco";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_banco', $this->id_banco);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Banco eliminado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar el banco'];
        }
    }

    /**
     * Último ID insertado en bancos.
     */
    private function _lastId()
    {
        $sql = "SELECT MAX(id_banco) as last_id FROM bancos";
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