<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\enums\EstadoPeriodo;
use haydee\enums\EstadoMovimientoCaja;
use haydee\enums\TipoBaseDatos;
use haydee\excepciones\NegocioException;

class CajaChica extends Conexion
{
    // CONSTANTES DE NEGOCIO
    private const UMBRAL_ALERTA_SALDO = 200;
    private const ALERTA_SALDO_BAJO = 'SALDO_BAJO';
    private const CAJA_ABIERTA = 'Abierto';
    private const CAJA_CERRADA = 'Cerrada';
    private const ESTADO_ACTIVO = 1;

    // Propiedades de la Caja
    private $id_caja_chica;
    private $fondo_fijo;
    private $estado;
    private $descripcion;
    private $fecha_creacion;
    private $anio_fiscal_id;
    private $activo;

    // Propiedades de Movimientos
    private $id_movimiento_caja;
    private $concepto;
    private $monto_movimiento;
    private $fecha_movimiento;
    private $estado_movimiento;
    private $tasa_dolar;

    // VALIDACIONES CENTRALIZADAS
    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
            // Reglas de la Caja Chica
            'id_caja_chica' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'caja_chica', 'campo' => 'id_caja_chica']
            ],
            'caja_chica_id' => [ // Para cuando se registra un movimiento
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'caja_chica', 'campo' => 'id_caja_chica']
            ],
            'descripcion' => [
                'regex' => '/^[a-zA-Z0-9 áéíóúÁÉÍÓÚñÑ\.,-]{0,255}$/',
                'opcional' => true
            ],
            'fondo_fijo' => [
                'regex' => '/^\d+(\.\d{1,2})?$/',
                'min' => 1
            ],
            'estado' => [
                'regex' => '/^(ABIERTO|CERRADO)$/',
                'opcional' => true
            ],

            // Reglas de los Movimientos
            'id_movimiento_caja' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'movimientos_caja', 'campo' => 'id_movimiento_caja']
            ],
            'concepto' => [
                'regex' => '/^[a-zA-Z0-9 áéíóúÁÉÍÓÚñÑ\.,-]{3,255}$/'
            ],
            'monto' => [
                'regex' => '/^\d+(\.\d{1,2})?$/',
                'min' => 0.01
            ],
            'fecha' => [
                'regex' => '/^\d{4}-\d{2}-\d{2}$/'
            ],
            'tasa_dolar' => [
                'regex' => '/^\d+(\.\d{1,2})?$/',
                'min' => 0.01
            ],
        ];

        // Estandarización de nombres aplicada a la caja y sus movimientos
        $camposPorOperacion = [
            'registrar_caja_chica' => ['descripcion', 'fondo_fijo'],
            'modificar_caja_chica' => ['id_caja_chica', 'descripcion', 'fondo_fijo', 'estado'],
            'eliminar_caja_chica'  => ['id_caja_chica'],
            'consulta_caja_chica' => ['id_caja_chica'],
            'reponer_caja' => ['caja_chica_id','monto'],

            'consultar_movimientos' => ['id_caja_chica'],
            'registrar_movimiento' => ['caja_chica_id', 'concepto', 'monto', 'fecha', 'tasa_dolar'],
            'modificar_movimiento' => ['id_movimiento_caja', 'concepto', 'monto', 'fecha', 'tasa_dolar'],
            'eliminar_movimiento'  => ['id_movimiento_caja'],
            'consultar_movimiento_unico' => ['id_movimiento_caja']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    // Getters y Setters
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
    public function set_tasa_dolar($tasa) { $this->tasa_dolar = $tasa; }
    public function get_tasa_dolar() { return $this->tasa_dolar; }

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

    private function _consultar()
    {
        $estadoRepuesto = EstadoMovimientoCaja::REPUESTO->value; 

        $sql = "SELECT 
                    cc.id_caja_chica, cc.fondo_fijo, cc.estado, cc.descripcion, cc.fecha_creacion, cc.anio_fiscal_id,
                    (cc.fondo_fijo - COALESCE(SUM(
                        CASE WHEN mc.estado <> :estado_repuesto AND mc.activo = " . self::ESTADO_ACTIVO . " 
                        THEN mc.monto ELSE 0 END
                    ), 0)) as saldo_calculado
                FROM caja_chica cc
                LEFT JOIN movimientos_caja mc ON cc.id_caja_chica = mc.caja_chica_id
                WHERE cc.activo = " . self::ESTADO_ACTIVO . "
                GROUP BY cc.id_caja_chica
                ORDER BY cc.fecha_creacion DESC";
        
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':estado_repuesto' => $estadoRepuesto]);
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_movimiento_unico()
    {
        $sql = "SELECT id_movimiento_caja, concepto, monto, tasa_dolar, fecha, estado 
                FROM movimientos_caja 
                WHERE id_movimiento_caja = :id 
                  AND activo = " . self::ESTADO_ACTIVO;
                  
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':id' => $this->id_movimiento_caja]);
        $dato = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$dato) {
            throw new HaydeeException('Movimiento no encontrado', 404);
        }
        return ['estatus' => true, 'datos' => $dato];
    }

    private function _registrar_movimiento()
    {
        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $pdo->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $pdo->beginTransaction();

            $stmtLock = $pdo->prepare("SELECT estado FROM caja_chica WHERE id_caja_chica = :id AND activo = 1 FOR UPDATE");
            $stmtLock->execute([':id' => $this->id_caja_chica]);
            $estadoCaja = $stmtLock->fetchColumn();

            if (!$estadoCaja) {
                throw new HaydeeException('La caja chica no existe o fue eliminada.', 404);
            }

            if (strtoupper($estadoCaja) !== EstadoPeriodo::ABIERTO->value) {
                throw new HaydeeException('No se pueden registrar movimientos en una caja chica cerrada.', 400);
            }

            $sqlSaldo = "SELECT (cc.fondo_fijo - COALESCE(SUM(
                            CASE WHEN mc.estado <> :estado_rep AND mc.activo = 1 
                            THEN mc.monto ELSE 0 END
                         ), 0))
                         FROM caja_chica cc
                         LEFT JOIN movimientos_caja mc ON cc.id_caja_chica = mc.caja_chica_id
                         WHERE cc.id_caja_chica = :id
                         GROUP BY cc.id_caja_chica";
                         
            $stmtS = $pdo->prepare($sqlSaldo);
            $stmtS->execute([
                ':id' => $this->id_caja_chica,
                ':estado_rep' => EstadoMovimientoCaja::REPUESTO->value
            ]);
            $saldoActual = (float)$stmtS->fetchColumn();

            if ($saldoActual < $this->monto_movimiento) {
                throw new HaydeeException("Fondos insuficientes. Disponible: " . number_format($saldoActual, 2, ',', '.'), 400);
            }

            $estadoPendiente = EstadoMovimientoCaja::PENDIENTE->value;
            $sqlIns = "INSERT INTO movimientos_caja (concepto, monto, tasa_dolar, fecha, estado, caja_chica_id, activo) 
                       VALUES (:con, :monto, :tasa, :fecha, :estado, :id_caja, 1)";
            $stmt = $pdo->prepare($sqlIns);
            $stmt->execute([
                ':con' => $this->concepto,
                ':monto' => $this->monto_movimiento,
                ':tasa' => $this->tasa_dolar,
                ':fecha' => $this->fecha_movimiento,
                ':estado' => $estadoPendiente,
                ':id_caja' => $this->id_caja_chica
            ]);
            
            $lastId = $pdo->lastInsertId();
            $alertaSaldo = $this->verificarEstadoSaldo();

            $pdo->commit();
            
            return [
                'estatus' => true, 
                'mensaje' => 'Gasto registrado correctamente.', 
                'lastId' => $lastId,
                'alerta_saldo' => $alertaSaldo 
            ];

        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function _modificar_movimiento()
    {
        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $pdo->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $pdo->beginTransaction();

            $stmtCaja = $pdo->prepare("SELECT caja_chica_id, estado FROM movimientos_caja WHERE id_movimiento_caja = :id FOR UPDATE");
            $stmtCaja->execute([':id' => $this->id_movimiento_caja]);
            $movimiento = $stmtCaja->fetch(PDO::FETCH_ASSOC);

            if (!$movimiento) {
                 throw new HaydeeException("Movimiento no encontrado.", 404);
            }

            if ($movimiento['estado'] === EstadoMovimientoCaja::REPUESTO->value) {
                throw new HaydeeException('Error de Auditoría: No se puede modificar un gasto que ya ha sido repuesto y conciliado en la contabilidad principal.', 400);
            }

            $id_caja = $movimiento['caja_chica_id'];
            $pdo->prepare("SELECT id_caja_chica FROM caja_chica WHERE id_caja_chica = ? FOR UPDATE")->execute([$id_caja]);

            $sql = "UPDATE movimientos_caja SET concepto = :con, fecha = :fecha, monto = :monto, tasa_dolar = :tasa 
                    WHERE id_movimiento_caja = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':con' => $this->concepto,
                ':fecha' => $this->fecha_movimiento,
                ':monto' => $this->monto_movimiento,
                ':tasa' => $this->tasa_dolar,
                ':id' => $this->id_movimiento_caja
            ]);

            $sqlSaldo = "SELECT (cc.fondo_fijo - COALESCE(SUM(
                            CASE WHEN mc.estado <> :estado_rep AND mc.activo = 1 
                            THEN mc.monto ELSE 0 END
                         ), 0))
                         FROM caja_chica cc
                         LEFT JOIN movimientos_caja mc ON cc.id_caja_chica = mc.caja_chica_id
                         WHERE cc.id_caja_chica = :id
                         GROUP BY cc.id_caja_chica";
                         
            $stmtS = $pdo->prepare($sqlSaldo);
            $stmtS->execute([
                ':id' => $this->id_caja_chica,
                ':estado_rep' => EstadoMovimientoCaja::REPUESTO->value
            ]);
            $saldoActual = (float)$stmtS->fetchColumn();

            if ($saldoActual < 0) {
                throw new HaydeeException('El nuevo monto excede los fondos disponibles en esta Caja Chica.', 400);
            }

            $this->id_caja_chica = $id_caja; 
            $alertaSaldo = $this->verificarEstadoSaldo();
            
            $pdo->commit();
            
            return [
                'estatus' => true, 
                'mensaje' => 'Movimiento actualizado correctamente.',
                'alerta_saldo' => $alertaSaldo 
            ];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function _eliminar_movimiento()
    {
        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $pdo->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $pdo->beginTransaction();

            $stmtCaja = $pdo->prepare("SELECT caja_chica_id, estado FROM movimientos_caja WHERE id_movimiento_caja = :id FOR UPDATE");
            $stmtCaja->execute([':id' => $this->id_movimiento_caja]);
            $movimiento = $stmtCaja->fetch(PDO::FETCH_ASSOC);

            if (!$movimiento) {
                 throw new HaydeeException("Movimiento no encontrado.", 404);
            }

            if ($movimiento['estado'] === EstadoMovimientoCaja::REPUESTO->value) {
                throw new HaydeeException('Error de Auditoría: No se puede eliminar un gasto que ya ha sido repuesto y conciliado en la contabilidad principal.', 400);
            }

            $sql = "UPDATE movimientos_caja SET activo = 0 WHERE id_movimiento_caja = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $this->id_movimiento_caja]);

            $pdo->commit();

            $alertaSaldo = $this->verificarEstadoSaldo();

            return [
                'estatus' => true, 
                'mensaje' => 'Movimiento eliminado (anulado) correctamente.',
                'alerta_saldo' => $alertaSaldo 
            ];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function _registrar_caja_chica()
    {
        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        
        $stmtCheck = $pdo->query("SELECT COUNT(*) FROM caja_chica WHERE estado = 'Abierto' AND activo = 1");
        if ($stmtCheck->fetchColumn() > 0) {
            throw new HaydeeException('Operación denegada. Ya existe una caja chica activa en el sistema.', 400);
        }

        $stmtAnio = $pdo->query("SELECT id_anio_fiscal FROM anio_fiscal WHERE estado = 'Abierto' AND activo = 1 LIMIT 1");
        $idAnioActivo = $stmtAnio->fetchColumn();

        if (!$idAnioActivo) {
            throw new HaydeeException('Debe existir un Año Fiscal abierto para poder inicializar la Caja Chica.', 400);
        }

        $sql = "INSERT INTO caja_chica (fondo_fijo, estado, descripcion, anio_fiscal_id, activo) 
                VALUES (:fondo, 'Abierto', :desc, :anio, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':fondo' => $this->fondo_fijo,
            ':desc' => $this->descripcion ?: 'Fondo inicial de apertura',
            ':anio' => $idAnioActivo
        ]);

        return ['estatus' => true, 'mensaje' => 'Fondo inicial establecido correctamente. El sistema de Caja Chica ya está operativo.', 'lastId' => $pdo->lastInsertId()];
    }
    
    private function _modificar_descripcion()
    {
        $sql = "UPDATE caja_chica SET descripcion = :desc WHERE id_caja_chica = :id";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([
            ':desc' => $this->descripcion,
            ':id'   => $this->id_caja_chica
        ]);
        return ['estatus' => true, 'mensaje' => 'Descripción actualizada.'];
    }

    private function _reponer_caja()
    {
        $sql = "CALL sp_registrar_reposicion_caja(:monto, :id_caja, :tasa_dolar)";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([
            ':monto' => $this->monto_movimiento,
            ':id_caja' => $this->id_caja_chica,
            ':tasa_dolar' => 1,
        ]);

        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return ['estatus' => true, 'mensaje' => $res['mensaje'] ?? 'Reposición procesada.'];
    }
    
    private function _consultar_movimientos()
    {
        $sql = "SELECT id_movimiento_caja, concepto, monto, fecha, tasa_dolar, estado 
                FROM movimientos_caja 
                WHERE caja_chica_id = :id 
                  AND activo = " . self::ESTADO_ACTIVO . " 
                ORDER BY fecha DESC";
                
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':id' => $this->id_caja_chica]);
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function verificarEstadoSaldo()
    {
        if (empty($this->id_caja_chica)) return null;

        $estadoRepuesto = EstadoMovimientoCaja::REPUESTO->value;

        $sqlSaldo = "SELECT (cc.fondo_fijo - COALESCE(SUM(
                         CASE WHEN mc.estado <> :estado_rep AND mc.activo = " . self::ESTADO_ACTIVO . " THEN mc.monto ELSE 0 END
                     ), 0))
                     FROM caja_chica cc
                     LEFT JOIN movimientos_caja mc ON cc.id_caja_chica = mc.caja_chica_id
                     WHERE cc.id_caja_chica = :id_caja AND cc.activo = " . self::ESTADO_ACTIVO . "
                     GROUP BY cc.id_caja_chica";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sqlSaldo);
        $stmt->execute([
            ':id_caja' => $this->id_caja_chica,
            ':estado_rep' => $estadoRepuesto
        ]);
        $saldo = $stmt->fetchColumn();

        if ($saldo === false) return false; 

        if ($saldo <= 0) {
            return ['tipo' => self::ALERTA_SALDO_BAJO, 'titulo' => 'Caja chica sin saldo', 'desc' => "La caja ID {$this->id_caja_chica} quedó en 0."];
        } elseif ($saldo < self::UMBRAL_ALERTA_SALDO) {
            return ['tipo' => self::ALERTA_SALDO_BAJO, 'titulo' => 'Saldo bajo en caja chica', 'desc' => "La caja ID {$this->id_caja_chica} tiene $saldo Bs."];
        }
        return null;
    }
}
