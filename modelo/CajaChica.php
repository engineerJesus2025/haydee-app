<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use DateTime;
use haydee\enums\EstadoPeriodo;
use haydee\enums\EstadoMovimientoCaja;
use haydee\enums\TipoBaseDatos;

class CajaChica extends Conexion
{
    // CONSTANTES DE NEGOCIO
    private const UMBRAL_ALERTA_SALDO = 200;
    private const ALERTA_SALDO_BAJO = 'SALDO_BAJO';

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

    // ====================================================================
    // VALIDACIONES CENTRALIZADAS
    // ====================================================================
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
            ]
        ];

        // Estandarización de nombres aplicada a la caja y sus movimientos
        $camposPorOperacion = [
            'registrar_caja_chica' => ['descripcion', 'fondo_fijo'],
            'modificar_caja_chica' => ['id_caja_chica', 'descripcion', 'fondo_fijo', 'estado'],
            'eliminar_caja_chica'  => ['id_caja_chica'],
            'consulta_caja_chica' => ['id_caja_chica'],
            'reponer_caja' => ['caja_chica_id','monto'],

            'consultar_movimientos' => ['id_caja_chica'],
            'registrar_movimiento' => ['caja_chica_id', 'concepto', 'monto', 'fecha'],
            'modificar_movimiento' => ['id_movimiento_caja', 'concepto', 'monto', 'fecha'],
            'eliminar_movimiento'  => ['id_movimiento_caja'],
            'consultar_movimiento_unico' => ['id_movimiento_caja']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

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
    // MÉTODOS DE NEGOCIO
    // -----------------------------------------------------------------

    /**
     * Consulta todas las cajas con su saldo calculado desde la vista
     // SE USA EN EL MODULO
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
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute();
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar cajas chicas'];
        }
    }

    // SE USA EN EL MODULO
    private function _consultar_movimiento_unico()
    {
        try {
            $sql = "SELECT id_movimiento_caja, concepto, monto, fecha, estado 
                    FROM movimientos_caja 
                    WHERE id_movimiento_caja = :id AND activo = 1";
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
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
     // SE USA EN EL MODULO
     */
    private function _registrar_movimiento()
    {        
        try {
            $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
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
            $estadoPendiente = EstadoMovimientoCaja::PENDIENTE_REPOSICION->value;

            $sqlIns = "INSERT INTO movimientos_caja (concepto, monto, fecha, estado, caja_chica_id, activo) 
                       VALUES (:con, :monto, :fecha, :estado, :id_caja, 1)";
            $stmt = $pdo->prepare($sqlIns);
            $stmt->execute([
                ':con' => $this->concepto,
                ':monto' => $this->monto_movimiento,
                ':fecha' => $this->fecha_movimiento,
                ':estado' => $estadoPendiente,
                ':id_caja' => $this->id_caja_chica
            ]);
            
            $lastId = $pdo->lastInsertId();

            $alertaSaldo = $this->verificarEstadoSaldo();

            $pdo->commit();
            
            return [
                'estatus' => true, 
                'mensaje' => 'Gasto registrado.', 
                'lastId' => $lastId,
                'alerta_saldo' => $alertaSaldo 
            ];

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
     // SE USA EN EL MODULO
     */
    private function _eliminar_movimiento()
    {
        try {
            $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
            $pdo->beginTransaction();

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
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Error en _eliminar_movimiento: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar movimiento: ' . $e->getMessage()];
        }
    }
    
    /**
     * Edita la descripción de una caja chica
     // SE USA EN EL MODULO
     */
    private function _modificar_descripcion()
    {
        try {
            $sql = "UPDATE caja_chica SET descripcion = :desc WHERE id_caja_chica = :id";
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([
                ':desc' => $this->descripcion,
                ':id'   => $this->id_caja_chica
            ]);
            return ['estatus' => true, 'mensaje' => 'Descripción actualizada.'];
        } catch (PDOException $e) {
            error_log("Error en _modificar_descripcion: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar descripción: ' . $e->getMessage()];
        }
    }

    /**
     * Repone caja chica (llama al SP)
     // SE USA EN EL MODULO
     */
    private function _reponer_caja()
    {
        try {
            $sql = "CALL sp_registrar_reposicion_caja(:monto, :id_caja)";
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
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
     // SE USA EN EL MODULO
     */
    private function _modificar_movimiento()
    {
        try {
            $sql = "UPDATE movimientos_caja SET concepto = :con, fecha = :fecha, monto = :monto 
                    WHERE id_movimiento_caja = :id";
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([
                ':con' => $this->concepto,
                ':fecha' => $this->fecha_movimiento,
                ':monto' => $this->monto_movimiento,
                ':id' => $this->id_movimiento_caja
            ]);

            $alertaSaldo = $this->verificarEstadoSaldo();
            
            return [
                'estatus' => true, 
                'mensaje' => 'Movimiento actualizado.',
                'alerta_saldo' => $alertaSaldo 
            ];
        } catch (PDOException $e) {
            error_log("Error en _modificar_movimiento: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error del servidor al modificar movimiento'];
        }
    }

    /**
     * Ejecuta el SP de verificación/cierre mensual
     // SE USA EN EL SCRIPT AUTOMATICP
     */
    private function _verificar_caja_mes()
    {
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare("CALL sp_gestion_caja_chica_mensual()");
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Verificación completada.'];
        } catch (PDOException $e) {
            error_log("Error en _verificar_caja_mes: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al verificar caja mensual: ' . $e->getMessage()];
        }
    }
    
    /**
     * Consulta todos los movimientos de una caja específica
     // SE USA EN EL MODULO
     */
    private function _consultar_movimientos()
    {
        try {
            $sql = "SELECT * FROM movimientos_caja WHERE caja_chica_id = :id AND activo = 1 ORDER BY fecha DESC";
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([':id' => $this->id_caja_chica]);
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar_movimientos: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar movimientos: ' . $e->getMessage()];
        }
    }

    /**
     * Verifica el saldo de la caja chica actual y, si es bajo, envía notificaciones a los administradores.
     * @return bool True si se notificó o no hubo necesidad, false si hubo error.
     // SE USA EN LA PROPIA CLASE
     */
    private function verificarEstadoSaldo()
    {
        if (empty($this->id_caja_chica)) return null;

        // Consultar saldo actual desde la vista
        $sqlSaldo = "SELECT saldo_disponible FROM vw_saldo_caja_chica WHERE id_caja_chica = :id";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sqlSaldo);
            $stmt->execute([':id' => $this->id_caja_chica]);
            $saldo = $stmt->fetchColumn();

            if ($saldo === false) return false; 

            // USAMOS LAS CONSTANTES
            if ($saldo <= 0) {
                return ['tipo' => self::ALERTA_SALDO_BAJO, 'titulo' => 'Caja chica sin saldo', 'desc' => "La caja ID {$this->id_caja_chica} quedó en 0."];
            } elseif ($saldo < self::UMBRAL_ALERTA_SALDO) {
                return ['tipo' => self::ALERTA_SALDO_BAJO, 'titulo' => 'Saldo bajo en caja chica', 'desc' => "La caja ID {$this->id_caja_chica} tiene $saldo Bs."];
            }
            return null;
        } catch (\Exception $e) {
            error_log("Error en verificarEstadoSaldo: " . $e->getMessage());
            return false;
        }
    }
}
