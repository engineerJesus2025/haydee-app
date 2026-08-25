<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\TipoCuenta;
use haydee\enums\TipoBaseDatos;
use haydee\enums\HttpCodigo;
use haydee\excepciones\NegocioException;

class CuentasCondominio extends Conexion
{
    private $id_cuenta;
    private $banco_id;
    private $tipo_cuenta;
    private $numero_cuenta;
    private $telefono_afiliado;
    private $rif;
    private $activo;

    public static function obtenerReglas($operacion) {
        $tiposCuentaValidos = implode('|', array_column(TipoCuenta::cases(), 'value'));
        
        $reglasGenerales = [
            'id_cuenta' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'cuentas_condominio', 'campo' => 'id_cuenta']
            ],
            'banco_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'bancos', 'campo' => 'id_banco']
            ],
            'numero_cuenta' => [
                'regex' => '/^\d{18,30}$/',
                'unique' => ['tabla' => 'cuentas_condominio', 'campo' => 'numero_cuenta', 'exclude_field' => 'id_cuenta']
            ],
            'tipo_cuenta' => [
                'regex' => "/^($tiposCuentaValidos)$/"
            ],
            'telefono_afiliado' => [
                'regex' => '/^\d{11}$/'
            ],
            'rif' => [
                'regex' => '/^[VEJG]{1}[0-9]{7,10}$/'
            ]
        ];

        $camposPorOperacion = [
            'registrar_cuenta'  => ['banco_id', 'numero_cuenta', 'tipo_cuenta', 'telefono_afiliado', 'rif'],
            'modificar_cuenta'  => ['id_cuenta', 'banco_id', 'numero_cuenta', 'tipo_cuenta', 'telefono_afiliado', 'rif'],
            'eliminar_cuenta'   => ['id_cuenta'],
            'consultar_cuenta'  => ['id_cuenta']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    public function set_id_cuenta($id) { $this->id_cuenta = $id; }
    public function get_id_cuenta() { return $this->id_cuenta; }
    public function set_banco_id($banco_id) { $this->banco_id = $banco_id; }
    public function get_banco_id() { return $this->banco_id; }
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

    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            throw new NegocioException("La acción '$accion' no está implementada.", HttpCodigo::BAD_REQUEST->value);
        }
        return $this->$metodo();
    }

    private function _consultar()
    {
        $sql = "SELECT c.*, b.nombre_banco, b.codigo 
                FROM cuentas_condominio c 
                JOIN bancos b ON c.banco_id = b.id_banco 
                WHERE c.activo = 1 
                ORDER BY c.id_cuenta";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_cuenta()
    {
        $sql = "SELECT * FROM cuentas_condominio WHERE id_cuenta = :id_cuenta AND activo = 1";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':id_cuenta', $this->id_cuenta);
        $stmt->execute();
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datos) {
            throw new NegocioException('Cuenta no encontrada.', HttpCodigo::NO_ENCONTRADO->value);
        }

        return ['estatus' => true, 'datos' => $datos];
    }

    private function _contarDependencias($id_cuenta)
    {
        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM ingresos_bancarios WHERE cuenta_id = :id_ingreso) +
                (SELECT COUNT(*) FROM egresos_bancarios WHERE cuenta_id = :id_egreso) AS usos
        ";
        
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':id_ingreso', $id_cuenta, PDO::PARAM_INT);
        $stmt->bindParam(':id_egreso', $id_cuenta, PDO::PARAM_INT);
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
    }

    public function verificarNumeroEnUso($numero_cuenta, $id_cuenta = null)
    {
        $db = $this->get_conex(TipoBaseDatos::NEGOCIO);

        $sqlActivo = "SELECT id_cuenta FROM cuentas_condominio WHERE numero_cuenta = :numero AND activo = 1";
        if ($id_cuenta) $sqlActivo .= " AND id_cuenta != :id";
        $stmtActivo = $db->prepare($sqlActivo);
        $stmtActivo->bindParam(':numero', $numero_cuenta);
        if ($id_cuenta) $stmtActivo->bindParam(':id', $id_cuenta);
        $stmtActivo->execute();

        if ($stmtActivo->fetch(PDO::FETCH_ASSOC)) return true;

        $sqlInactivo = "SELECT id_cuenta FROM cuentas_condominio WHERE numero_cuenta = :numero AND activo = 0";
        if ($id_cuenta) $sqlInactivo .= " AND id_cuenta != :id";
        $stmtInactivo = $db->prepare($sqlInactivo);
        $stmtInactivo->bindParam(':numero', $numero_cuenta);
        if ($id_cuenta) $stmtInactivo->bindParam(':id', $id_cuenta);
        $stmtInactivo->execute();
        $cuentaInactiva = $stmtInactivo->fetch(PDO::FETCH_ASSOC);

        if ($cuentaInactiva && $this->_contarDependencias($cuentaInactiva['id_cuenta']) > 0) {
            return true; 
        }

        return false;
    }

    private function _registrar_cuenta()
    {
        $db = $this->get_conex(TipoBaseDatos::NEGOCIO);

        $sqlCheck = "SELECT id_cuenta, activo FROM cuentas_condominio WHERE numero_cuenta = :numero LIMIT 1";
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->bindParam(':numero', $this->numero_cuenta);
        $stmtCheck->execute();
        $cuentaExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        try {
            $db->beginTransaction();

            if ($cuentaExistente) {
                if ($cuentaExistente['activo'] == 1) {
                    throw new NegocioException('Este número de cuenta ya se encuentra registrado y activo.', HttpCodigo::BAD_REQUEST->value);
                }

                if ($this->_contarDependencias($cuentaExistente['id_cuenta']) > 0) {
                    throw new NegocioException('El número pertenece a una cuenta inactiva con historial contable y no puede duplicarse.', HttpCodigo::BAD_REQUEST->value);
                } else {
                    $sqlHardDelete = "DELETE FROM cuentas_condominio WHERE id_cuenta = :id_inactiva";
                    $stmtHardDelete = $db->prepare($sqlHardDelete);
                    $stmtHardDelete->bindParam(':id_inactiva', $cuentaExistente['id_cuenta']);
                    $stmtHardDelete->execute();
                }
            }

            $sql = "INSERT INTO cuentas_condominio (banco_id, numero_cuenta, tipo_cuenta, telefono_afiliado, rif)
                    VALUES (:banco_id, :numero_cuenta, :tipo_cuenta, :telefono_afiliado, :rif)";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':banco_id', $this->banco_id);
            $stmt->bindParam(':numero_cuenta', $this->numero_cuenta);
            $stmt->bindParam(':tipo_cuenta', $this->tipo_cuenta);
            $stmt->bindParam(':telefono_afiliado', $this->telefono_afiliado);
            $stmt->bindParam(':rif', $this->rif);
            $stmt->execute();
            
            $lastId = $db->lastInsertId();
            $db->commit();
            return ['estatus' => true, 'mensaje' => 'Cuenta registrada correctamente.', 'lastId' => $lastId];

        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    private function _modificar_cuenta()
    {
        $db = $this->get_conex(TipoBaseDatos::NEGOCIO);

        if ($this->verificarNumeroEnUso($this->numero_cuenta, $this->id_cuenta)) {
            throw new NegocioException('El número de cuenta ingresado ya está asignado o bloqueado por otra entidad.', HttpCodigo::BAD_REQUEST->value);
        }

        $sqlCheckInactivo = "SELECT id_cuenta FROM cuentas_condominio WHERE numero_cuenta = :numero AND id_cuenta != :id AND activo = 0 LIMIT 1";
        $stmtCheckInactivo = $db->prepare($sqlCheckInactivo);
        $stmtCheckInactivo->bindParam(':numero', $this->numero_cuenta);
        $stmtCheckInactivo->bindParam(':id', $this->id_cuenta);
        $stmtCheckInactivo->execute();
        $cuentaInactiva = $stmtCheckInactivo->fetch(PDO::FETCH_ASSOC);

        try {
            $db->beginTransaction();

            if ($cuentaInactiva && $this->_contarDependencias($cuentaInactiva['id_cuenta']) === 0) {
                $sqlHardDelete = "DELETE FROM cuentas_condominio WHERE id_cuenta = :id_inactiva";
                $stmtHardDelete = $db->prepare($sqlHardDelete);
                $stmtHardDelete->bindParam(':id_inactiva', $cuentaInactiva['id_cuenta']);
                $stmtHardDelete->execute();
            }

            $sql = "UPDATE cuentas_condominio SET 
                        banco_id = :banco_id, numero_cuenta = :numero_cuenta,
                        tipo_cuenta = :tipo_cuenta, telefono_afiliado = :telefono_afiliado, rif = :rif
                    WHERE id_cuenta = :id_cuenta";
                    
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_cuenta', $this->id_cuenta);
            $stmt->bindParam(':banco_id', $this->banco_id);
            $stmt->bindParam(':numero_cuenta', $this->numero_cuenta);
            $stmt->bindParam(':tipo_cuenta', $this->tipo_cuenta);
            $stmt->bindParam(':telefono_afiliado', $this->telefono_afiliado);
            $stmt->bindParam(':rif', $this->rif);
            $stmt->execute();
            
            $db->commit();
            return ['estatus' => true, 'mensaje' => 'Cuenta actualizada correctamente.'];

        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    private function _eliminar_cuenta()
    {
        $db = $this->get_conex(TipoBaseDatos::NEGOCIO);

        $sqlPagosPendientes = "
            SELECT COUNT(*) FROM ingresos_bancarios ib 
            JOIN detalles_pagos dp ON ib.detalle_pago_id = dp.id_detalle_pago
            JOIN pagos p ON dp.pago_id = p.id_pago 
            WHERE ib.cuenta_id = :id_cuenta AND p.estado = 'PENDIENTE' AND p.activo = 1
        ";
        $stmtPendientes = $db->prepare($sqlPagosPendientes);
        $stmtPendientes->bindParam(':id_cuenta', $this->id_cuenta);
        $stmtPendientes->execute();

        if ((int)$stmtPendientes->fetchColumn() > 0) {
            throw new NegocioException('No se puede desactivar la cuenta porque tiene pagos de residentes pendientes por verificar. Procéselos o rechácelos primero.', HttpCodigo::BAD_REQUEST->value);
        }

        $sql = "UPDATE cuentas_condominio SET activo = 0 WHERE id_cuenta = :id_cuenta";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_cuenta', $this->id_cuenta);
        $stmt->execute();
        
        return ['estatus' => true, 'mensaje' => 'Cuenta eliminada correctamente.'];
    }
}