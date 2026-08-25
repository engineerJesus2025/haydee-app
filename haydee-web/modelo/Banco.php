<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\TipoBaseDatos;
use haydee\enums\HttpCodigo;
use haydee\excepciones\NegocioException;

class Banco extends Conexion
{
    private $id_banco;
    private $nombre_banco;
    private $codigo;
    private $activo;

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
        ];

        $camposPorOperacion = [
            'registrar_banco'  => ['nombre_banco', 'codigo'],
            'modificar_banco'  => ['id_banco', 'nombre_banco', 'codigo'],
            'eliminar_banco'   => ['id_banco'],
            'consultar_banco'  => ['id_banco']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    public function set_id_banco($id) { $this->id_banco = $id; }
    public function get_id_banco() { return $this->id_banco; }
    public function set_nombre_banco($nombre) { $this->nombre_banco = $nombre; }
    public function get_nombre_banco() { return $this->nombre_banco; }
    public function set_codigo($codigo) { $this->codigo = $codigo; }
    public function get_codigo() { return $this->codigo; }
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

    public function verificarCodigoEnUso($codigo, $id_banco = null)
    {
        $db = $this->get_conex(TipoBaseDatos::NEGOCIO);

        $sqlActivo = "SELECT id_banco FROM bancos WHERE codigo = :codigo AND activo = 1";
        if ($id_banco) {
            $sqlActivo .= " AND id_banco != :id_banco";
        }
        $stmtActivo = $db->prepare($sqlActivo);
        $stmtActivo->bindParam(':codigo', $codigo);
        if ($id_banco) {
            $stmtActivo->bindParam(':id_banco', $id_banco);
        }
        $stmtActivo->execute();

        if ($stmtActivo->fetch(PDO::FETCH_ASSOC)) {
            return true;
        }

        $sqlInactivo = "SELECT id_banco FROM bancos WHERE codigo = :codigo AND activo = 0";
        if ($id_banco) {
            $sqlInactivo .= " AND id_banco != :id_banco";
        }
        $stmtInactivo = $db->prepare($sqlInactivo);
        $stmtInactivo->bindParam(':codigo', $codigo);
        if ($id_banco) {
            $stmtInactivo->bindParam(':id_banco', $id_banco);
        }
        $stmtInactivo->execute();
        $bancoInactivo = $stmtInactivo->fetch(PDO::FETCH_ASSOC);

        if ($bancoInactivo) {
            if ($this->_contarDependencias($bancoInactivo['id_banco']) > 0) {
                return true; 
            }
        }

        return false;
    }

    private function _consultar()
    {
        $sql = "SELECT * FROM bancos WHERE activo = 1 ORDER BY id_banco";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_banco()
    {
        $sql = "SELECT * FROM bancos WHERE id_banco = :id_banco AND activo = 1";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':id_banco', $this->id_banco);
        $stmt->execute();
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datos) {
            throw new NegocioException('Banco no encontrado', HttpCodigo::NO_ENCONTRADO->value);
        }

        return ['estatus' => true, 'datos' => $datos];
    }

    private function _registrar_banco()
    {
        $db = $this->get_conex(TipoBaseDatos::NEGOCIO);

        $sqlCheck = "SELECT id_banco, activo FROM bancos WHERE codigo = :codigo LIMIT 1";
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->bindParam(':codigo', $this->codigo);
        $stmtCheck->execute();
        $bancoExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        try {
            $db->beginTransaction();

            if ($bancoExistente) {
                if ($bancoExistente['activo'] == 1) {
                    throw new NegocioException('El código bancario ya se encuentra registrado y activo.', HttpCodigo::BAD_REQUEST->value);
                }

                if ($this->_contarDependencias($bancoExistente['id_banco']) > 0) {
                    throw new NegocioException('El código pertenece a un banco inactivo con historial contable y no puede duplicarse.', HttpCodigo::BAD_REQUEST->value);
                } else {
                    $sqlHardDelete = "DELETE FROM bancos WHERE id_banco = :id_inactivo";
                    $stmtHardDelete = $db->prepare($sqlHardDelete);
                    $stmtHardDelete->bindParam(':id_inactivo', $bancoExistente['id_banco']);
                    $stmtHardDelete->execute();
                }
            }

            $sqlInsert = "INSERT INTO bancos (nombre_banco, codigo) VALUES (:nombre_banco, :codigo)";
            $stmtInsert = $db->prepare($sqlInsert);
            $stmtInsert->bindParam(':nombre_banco', $this->nombre_banco);
            $stmtInsert->bindParam(':codigo', $this->codigo);
            $stmtInsert->execute();
            $lastId = $db->lastInsertId();

            $db->commit();
            return ['estatus' => true, 'mensaje' => 'Banco registrado correctamente.', 'lastId' => $lastId];

        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    private function _modificar_banco()
    {
        $db = $this->get_conex(TipoBaseDatos::NEGOCIO);

        $sqlCheckActivo = "SELECT id_banco FROM bancos WHERE codigo = :codigo AND id_banco != :id_banco AND activo = 1 LIMIT 1";
        $stmtCheckActivo = $db->prepare($sqlCheckActivo);
        $stmtCheckActivo->bindParam(':codigo', $this->codigo);
        $stmtCheckActivo->bindParam(':id_banco', $this->id_banco);
        $stmtCheckActivo->execute();

        if ($stmtCheckActivo->fetch(PDO::FETCH_ASSOC)) {
            throw new NegocioException('El código bancario ingresado ya está asignado a otra entidad activa.', HttpCodigo::BAD_REQUEST->value);
        }

        $sqlCheckInactivo = "SELECT id_banco FROM bancos WHERE codigo = :codigo AND id_banco != :id_banco AND activo = 0 LIMIT 1";
        $stmtCheckInactivo = $db->prepare($sqlCheckInactivo);
        $stmtCheckInactivo->bindParam(':codigo', $this->codigo);
        $stmtCheckInactivo->bindParam(':id_banco', $this->id_banco);
        $stmtCheckInactivo->execute();
        $bancoInactivo = $stmtCheckInactivo->fetch(PDO::FETCH_ASSOC);

        try {
            $db->beginTransaction();

            if ($bancoInactivo) {
                if ($this->_contarDependencias($bancoInactivo['id_banco']) > 0) {
                    throw new NegocioException('El código pertenece a un banco inactivo con historial. No puede ser reutilizado.', HttpCodigo::BAD_REQUEST->value);
                } else {
                    $sqlHardDelete = "DELETE FROM bancos WHERE id_banco = :id_inactivo";
                    $stmtHardDelete = $db->prepare($sqlHardDelete);
                    $stmtHardDelete->bindParam(':id_inactivo', $bancoInactivo['id_banco']);
                    $stmtHardDelete->execute();
                }
            }

            $sql = "UPDATE bancos SET nombre_banco = :nombre_banco, codigo = :codigo WHERE id_banco = :id_banco";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_banco', $this->id_banco);
            $stmt->bindParam(':nombre_banco', $this->nombre_banco);
            $stmt->bindParam(':codigo', $this->codigo);
            $stmt->execute();
            
            $db->commit();
            return ['estatus' => true, 'mensaje' => 'Banco actualizado correctamente.'];

        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    private function _contarDependencias($id_banco)
    {
        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM cuentas_condominio WHERE banco_id = :id_cuenta) +
                (SELECT COUNT(*) FROM ingresos_bancarios WHERE banco_id = :id_ingreso) AS usos
        ";
        
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':id_cuenta', $id_banco, PDO::PARAM_INT);
        $stmt->bindParam(':id_ingreso', $id_banco, PDO::PARAM_INT);
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
    }

    private function _eliminar_banco()
    {
        $db = $this->get_conex(TipoBaseDatos::NEGOCIO);

        $sqlCheckCuentas = "SELECT COUNT(*) FROM cuentas_condominio WHERE banco_id = :id_banco AND activo = 1";
        $stmtCheck = $db->prepare($sqlCheckCuentas);
        $stmtCheck->bindParam(':id_banco', $this->id_banco);
        $stmtCheck->execute();
        
        if ((int)$stmtCheck->fetchColumn() > 0) {
            throw new NegocioException('No se puede desactivar el banco porque tiene cuentas corrientes activas asociadas al condominio. Desactive las cuentas primero.', HttpCodigo::BAD_REQUEST->value);
        }

        $sql = "UPDATE bancos SET activo = 0 WHERE id_banco = :id_banco";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_banco', $this->id_banco);
        $stmt->execute();
        
        return ['estatus' => true, 'mensaje' => 'Banco desactivado correctamente.'];
    }
}