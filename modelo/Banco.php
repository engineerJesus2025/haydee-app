<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Banco extends Conexion
{
    private $id_banco;
    private $nombre_banco;
    private $codigo;
    private $tipo_cuenta;
    private $numero_cuenta;
    private $telefono_afiliado;
    private $rif;
    private $activo;

    // ====================================================================
    // VALIDACIONES CENTRALIZADAS
    // ====================================================================
    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
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
            'tipo_cuenta' => [
                'regex' => '/^(Ahorro|Corriente)$/'
            ],
            'telefono_afiliado' => [
                'regex' => '/^\d{11}$/'
            ],
            'rif' => [
                'regex' => '/^[VEJG]{1}[0-9]{7,10}$/'
            ]
        ];

        // Estandarización de nombres aplicada aquí
        $camposPorOperacion = [
            'registrar_banco'  => ['nombre_banco', 'codigo', 'numero_cuenta', 'tipo_cuenta', 'telefono_afiliado', 'rif'],
            'modificar_banco'  => ['id_banco', 'nombre_banco', 'codigo', 'numero_cuenta', 'tipo_cuenta', 'telefono_afiliado', 'rif'],
            'eliminar_banco'   => ['id_banco'],
            'consultar_banco' => ['id_banco']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    // Getters y Setters
    public function set_id_banco($id) { $this->id_banco = $id; }
    public function get_id_banco() { return $this->id_banco; }
    public function set_nombre_banco($nombre) { $this->nombre_banco = $nombre; }
    public function get_nombre_banco() { return $this->nombre_banco; }
    public function set_codigo($codigo) { $this->codigo = $codigo; }
    public function get_codigo() { return $this->codigo; }
    public function set_tipo_cuenta($tipo_cuenta) { $this->tipo_cuenta = $tipo_cuenta; }
    public function get_tipo_cuenta() { return $this->tipo_cuenta; }
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
    // Métodos privados (acciones)
    // -----------------------------------------------------------------

    /**
     * Lista todos los bancos activos.
     // SE USA EN EL MODULO
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
     // SE USA EN EL MODULO
     */
    private function _consultar_banco()
    {
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
     // SE USA EN EL MODULO
     */
    private function _registrar_banco()
    {
        $sql = "INSERT INTO bancos (nombre_banco, codigo, numero_cuenta, tipo_cuenta, telefono_afiliado, rif)
                VALUES (:nombre_banco, :codigo, :numero_cuenta, :tipo_cuenta, :telefono_afiliado, :rif)";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':nombre_banco', $this->nombre_banco);
            $stmt->bindParam(':codigo', $this->codigo);
            $stmt->bindParam(':numero_cuenta', $this->numero_cuenta);
            $stmt->bindParam(':tipo_cuenta', $this->tipo_cuenta);
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
     // SE USA EN EL MODULO
     */
    private function _modificar_banco()
    {
        $sql = "UPDATE bancos SET 
                    nombre_banco = :nombre_banco,
                    codigo = :codigo,
                    numero_cuenta = :numero_cuenta,
                    tipo_cuenta = :tipo_cuenta,
                    telefono_afiliado = :telefono_afiliado,
                    rif = :rif
                WHERE id_banco = :id_banco";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_banco', $this->id_banco);
            $stmt->bindParam(':nombre_banco', $this->nombre_banco);
            $stmt->bindParam(':codigo', $this->codigo);
            $stmt->bindParam(':numero_cuenta', $this->numero_cuenta);
            $stmt->bindParam(':tipo_cuenta', $this->tipo_cuenta);
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
     // SE USA EN EL MODULO
     */
    private function _eliminar_banco()
    {
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
}
?>