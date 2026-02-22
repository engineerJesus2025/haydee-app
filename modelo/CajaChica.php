<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use DateTime;

class CajaChica extends Conexion
{
    // Propiedades de la Caja
    private $id_caja_chica;
    private $fondo_fijo;
    private $estado;
    private $descripcion;
    private $fecha_creacion;
    private $anio_fiscal_id;
    private $activo;

    // Propiedades de Movimientos (Integradas)
    private $id_movimiento_caja;
    private $concepto;
    private $monto_movimiento;
    private $fecha_movimiento;
    private $estado_movimiento;

    // -----------------------------------------------------------------
    // Reglas de Validación (mejoradas con soporte opcional)
    // -----------------------------------------------------------------
    private $reglas = [
        // Caja
        'id_caja_chica' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'caja_chica', 'campo' => 'id_caja_chica']
        ],
        'descripcion' => [
            'regex' => '/^[a-zA-Z0-9 áéíóúÁÉÍÓÚñÑ\.,-]{0,255}$/',
            'opcional' => true
        ],
        'fondo_fijo' => [
            'regex' => '/^\d+(\.\d{1,2})?$/',
            'min' => 0
        ],
        
        // Movimientos
        'id_movimiento_caja' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'movimientos_caja', 'campo' => 'id_movimiento_caja']
        ],
        'concepto' => [
            'regex' => '/^[a-zA-Z0-9 áéíóúÁÉÍÓÚñÑ\.,-]{1,100}$/'
        ],
        'monto_movimiento' => [
            'regex' => '/^\d+(\.\d{1,2})?$/',
            'min' => 0.01
        ],
        'fecha_movimiento' => [
            'type' => 'date'
        ]
    ];

    // -----------------------------------------------------------------
    // Getters y Setters (igual)
    // -----------------------------------------------------------------
    public function set_id_caja_chica($id) { $this->id_caja_chica = $id; }
    public function get_id_caja_chica() { return $this->id_caja_chica; }
    public function set_fondo_fijo($fondo) { $this->fondo_fijo = $fondo; }
    public function get_fondo_fijo() { return $this->fondo_fijo; }
    public function set_estado($estado) { $this->estado = $estado; }
    public function get_estado() { return $this->estado; }
    public function set_descripcion($desc) { $this->descripcion = $desc; }
    public function get_descripcion() { return $this->descripcion; }
    public function set_fecha_creacion($fecha) { $this->fecha_creacion = $fecha; }
    public function get_fecha_creacion() { return $this->fecha_creacion; }
    public function set_anio_fiscal_id($id) { $this->anio_fiscal_id = $id; }
    public function get_anio_fiscal_id() { return $this->anio_fiscal_id; }
    public function set_activo($activo) { $this->activo = $activo; }
    public function get_activo() { return $this->activo; }

    public function set_id_movimiento_caja($id) { $this->id_movimiento_caja = $id; }
    public function get_id_movimiento_caja() { return $this->id_movimiento_caja; }
    public function set_concepto($concepto) { $this->concepto = $concepto; }
    public function get_concepto() { return $this->concepto; }
    public function set_monto_movimiento($monto) { $this->monto_movimiento = $monto; }
    public function get_monto_movimiento() { return $this->monto_movimiento; }
    public function set_fecha_movimiento($fecha) { $this->fecha_movimiento = $fecha; }
    public function get_fecha_movimiento() { return $this->fecha_movimiento; }

    /**
     * Enrutador con manejo de excepciones
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
            return ['estatus' => false, 'mensaje' => 'Error interno: ' . $e->getMessage()];
        }
    }

    // -----------------------------------------------------------------
    // Validación Genérica (con soporte para opcionales)
    // -----------------------------------------------------------------
    private function validar($campos)
    {
        foreach ($campos as $campo) {
            if (!isset($this->reglas[$campo])) continue;
            $regla = $this->reglas[$campo];
            $getter = 'get_' . $campo;
            
            if (!method_exists($this, $getter)) {
                return ['estatus' => false, 'mensaje' => "Getter no encontrado para $campo."];
            }
            $valor = $this->$getter();

            // Determinar si es opcional
            $opcional = isset($regla['opcional']) && $regla['opcional'] === true;

            // Si es opcional y está vacío (null o string vacío), saltamos validaciones
            if ($opcional && ($valor === null || (is_string($valor) && trim($valor) === ''))) {
                continue;
            }

            // Validar requerido (si no es opcional y está vacío)
            if (!$opcional) {
                if ($valor === null) {
                    return ['estatus' => false, 'mensaje' => "El campo '$campo' es requerido y no se ha establecido."];
                }
                if (is_string($valor) && trim($valor) === '') {
                    return ['estatus' => false, 'mensaje' => "El campo '$campo' no puede estar vacío."];
                }
            }

            // Validar regex
            if (isset($regla['regex']) && !preg_match($regla['regex'], (string)$valor)) {
                return ['estatus' => false, 'mensaje' => "El formato de '$campo' no es válido."];
            }

            // Validar mínimo
            if (isset($regla['min']) && $valor < $regla['min']) {
                return ['estatus' => false, 'mensaje' => "El '$campo' debe ser mayor o igual a " . $regla['min']];
            }

            // Validar tipo fecha
            if (isset($regla['type']) && $regla['type'] === 'date') {
                $d = DateTime::createFromFormat('Y-m-d', $valor);
                if (!($d && $d->format('Y-m-d') === $valor)) {
                    return ['estatus' => false, 'mensaje' => "Fecha inválida en '$campo'."];
                }
            }

            // Validar existencia en otra tabla
            if (isset($regla['exists'])) {
                if (!$this->existeEnTabla($regla['exists']['tabla'], $regla['exists']['campo'], $valor)) {
                    return ['estatus' => false, 'mensaje' => "El valor de '$campo' no existe en el sistema."];
                }
            }
        }
        return ['estatus' => true];
    }

    private function existeEnTabla($tabla, $campo, $valor)
    {
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = :valor AND activo = 1";
        $stmt = $this->get_conex('negocio')->prepare($sql);
        $stmt->execute([':valor' => $valor]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;
    }

    /**
     * Método auxiliar para validaciones AJAX del frontend
     */
    public function validarExistenciaExterna($tabla, $campo, $valor)
    {
        $tablasPermitidas = ['caja_chica', 'gastos', 'movimientos_caja'];
        if (!in_array($tabla, $tablasPermitidas)) {
            return false;
        }
        return $this->existeEnTabla($tabla, $campo, $valor);
    }

    // -----------------------------------------------------------------
    // MÉTODOS DE NEGOCIO
    // -----------------------------------------------------------------

    /**
     * Consulta todas las cajas con su saldo calculado desde la vista
     */
    private function _consultar()
    {
        $sql = "SELECT cc.*, 
                       COALESCE(vw.saldo_disponible, cc.fondo_fijo) as saldo_calculado 
                FROM caja_chica cc 
                LEFT JOIN vw_saldo_caja_chica vw ON cc.id_caja_chica = vw.id_caja_chica
                WHERE cc.activo = 1 
                ORDER BY cc.fecha_creacion DESC";
        
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar cajas chicas'];
        }
    }

    private function _consultar_movimiento_unico()
    {
        $v = $this->validar(['id_movimiento_caja']);
        if (!$v['estatus']) return $v;

        try {
            $sql = "SELECT id_movimiento_caja, concepto, monto, fecha, estado 
                    FROM movimientos_caja 
                    WHERE id_movimiento_caja = :id AND activo = 1";
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':id' => $this->id_movimiento_caja]);
            $dato = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$dato) {
                return ['estatus' => false, 'mensaje' => 'Movimiento no encontrado'];
            }
            return ['estatus' => true, 'datos' => $dato];
        } catch (PDOException $e) {
            error_log("Error en _consultar_movimiento_unico: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar movimiento'];
        }
    }

    /**
     * Registra un nuevo movimiento (gasto) en caja chica
     */
    private function _registrar_movimiento()
    {
        // Validar que la caja exista
        $valCaja = $this->validar(['id_caja_chica']);
        if (!$valCaja['estatus']) {
            return $valCaja;
        }

        // Validar datos del movimiento
        $valMov = $this->validar(['concepto', 'monto_movimiento', 'fecha_movimiento']);
        if (!$valMov['estatus']) {
            return $valMov;
        }

        $pdo = $this->get_conex('negocio');
        
        try {
            $pdo->beginTransaction();

            // Consultar saldo disponible desde la vista
            $sqlSaldo = "SELECT saldo_disponible FROM vw_saldo_caja_chica WHERE id_caja_chica = :id";
            $stmtS = $pdo->prepare($sqlSaldo);
            $stmtS->execute([':id' => $this->id_caja_chica]);
            $saldoActual = $stmtS->fetchColumn();

            // Si la vista no devuelve nada, la caja no tiene movimientos, el saldo es el fondo fijo
            if ($saldoActual === false) {
                // Obtener fondo fijo de la caja
                $sqlFondo = "SELECT fondo_fijo FROM caja_chica WHERE id_caja_chica = :id AND activo = 1";
                $stmtF = $pdo->prepare($sqlFondo);
                $stmtF->execute([':id' => $this->id_caja_chica]);
                $fondo = $stmtF->fetchColumn();
                if ($fondo === false) {
                    $pdo->rollBack();
                    return ['estatus' => false, 'mensaje' => 'La caja chica no existe o está inactiva.'];
                }
                $saldoActual = $fondo;
            }

            if ($saldoActual < $this->monto_movimiento) {
                $pdo->rollBack();
                return ['estatus' => false, 'mensaje' => "Fondos insuficientes. Disponible: " . number_format($saldoActual, 2, ',', '.')];
            }

            // Insertar movimiento
            $sqlIns = "INSERT INTO movimientos_caja (concepto, monto, fecha, estado, caja_chica_id, activo) 
                       VALUES (:con, :monto, :fecha, 'Pendiente por reposicion', :id_caja, 1)";
            $stmt = $pdo->prepare($sqlIns);
            $stmt->execute([
                ':con' => $this->concepto,
                ':monto' => $this->monto_movimiento,
                ':fecha' => $this->fecha_movimiento,
                ':id_caja' => $this->id_caja_chica
            ]);
            
            $lastId = $pdo->lastInsertId();

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Gasto registrado correctamente.', 'lastId' => $lastId];

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en _registrar_movimiento: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Elimina (anula) un movimiento (soft delete)
     */
    private function _eliminar_movimiento()
    {
        $v = $this->validar(['id_movimiento_caja']);
        if (!$v['estatus']) return $v;

        try {
            $pdo = $this->get_conex('negocio');
            $pdo->beginTransaction();

            $sql = "UPDATE movimientos_caja SET activo = 0 WHERE id_movimiento_caja = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $this->id_movimiento_caja]);

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Movimiento eliminado (anulado) correctamente.'];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Error en _eliminar_movimiento: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar movimiento: ' . $e->getMessage()];
        }
    }
    
    /**
     * Edita la descripción de una caja chica
     */
    private function _editar_descripcion()
    {
        $v = $this->validar(['id_caja_chica', 'descripcion']);
        if (!$v['estatus']) return $v;

        try {
            $sql = "UPDATE caja_chica SET descripcion = :desc WHERE id_caja_chica = :id";
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([
                ':desc' => $this->descripcion,
                ':id'   => $this->id_caja_chica
            ]);
            return ['estatus' => true, 'mensaje' => 'Descripción actualizada.'];
        } catch (PDOException $e) {
            error_log("Error en _editar_descripcion: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar descripción: ' . $e->getMessage()];
        }
    }

    /**
     * Repone caja chica (llama al SP)
     */
    private function _reponer_caja()
    {
        $v = $this->validar(['id_caja_chica', 'monto_movimiento']);
        if (!$v['estatus']) return $v;

        try {
            $sql = "CALL sp_registrar_reposicion_caja(:monto, :id_caja)";
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([
                ':monto' => $this->monto_movimiento,
                ':id_caja' => $this->id_caja_chica
            ]);

            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return ['estatus' => true, 'mensaje' => $res['mensaje'] ?? 'Reposición procesada.'];
        } catch (PDOException $e) {
            error_log("Error en _reponer_caja: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al reponer caja: ' . $e->getMessage()];
        }
    }

    /**
     * Edita concepto y fecha de un movimiento (no el monto por seguridad)
     */
    private function _editar_movimiento()
    {
        $v = $this->validar(['id_movimiento_caja', 'concepto', 'fecha_movimiento']);
        if (!$v['estatus']) return $v;

        try {
            $sql = "UPDATE movimientos_caja SET concepto = :con, fecha = :fecha 
                    WHERE id_movimiento_caja = :id";
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([
                ':con' => $this->concepto,
                ':fecha' => $this->fecha_movimiento,
                ':id' => $this->id_movimiento_caja
            ]);
            return ['estatus' => true, 'mensaje' => 'Movimiento actualizado (solo concepto y fecha).'];
        } catch (PDOException $e) {
            error_log("Error en _editar_movimiento: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al editar movimiento: ' . $e->getMessage()];
        }
    }

    /**
     * Ejecuta el SP de verificación/cierre mensual
     */
    private function _verificar_caja_mes()
    {
        try {
            $stmt = $this->get_conex('negocio')->prepare("CALL sp_gestion_caja_chica_mensual()");
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Verificación completada.'];
        } catch (PDOException $e) {
            error_log("Error en _verificar_caja_mes: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al verificar caja mensual: ' . $e->getMessage()];
        }
    }
    
    /**
     * Consulta todos los movimientos de una caja específica
     */
    private function _consultar_movimientos()
    {
        $v = $this->validar(['id_caja_chica']);
        if (!$v['estatus']) return $v;

        try {
            $sql = "SELECT * FROM movimientos_caja WHERE caja_chica_id = :id AND activo = 1 ORDER BY fecha DESC";
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':id' => $this->id_caja_chica]);
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar_movimientos: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar movimientos: ' . $e->getMessage()];
        }
    }
}
?>