<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\TipoBaseDatos;
use haydee\enums\EstadoPago;
use haydee\enums\HttpCodigo;
use haydee\excepciones\NegocioException;

class Presupuesto extends Conexion
{
    private const ANIO_MINIMO_PERMITIDO = 2000;
    private const ANIO_MAXIMO_PERMITIDO = 2100;

    private const INTERES_MORA_DEFECTO = 10;
    private const LIMITE_DIAS_MENSUALIDAD = 15;
    private const ESTADO_ACTIVO = 1;

    private $id_presupuesto;
    private $fecha;
    private $cuota_reserva;
    private $observacion;
    private $activo;

    private $id_detalle_presupuesto;
    private $monto_detalle;
    private $concepto_id;

    private $mensualidad_id;

    private $detalles_temp = [];
    private $ids_detalles_string = '';
    private $tasa_dolar;

    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
            'id_presupuesto' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'presupuesto', 'campo' => 'id_presupuesto']
            ],
            'fecha' => [
                'regex' => '/^\d{4}-\d{2}-\d{2}$/'
            ],
            'cuota_reserva' => [
                'regex' => '/^\d+(\.\d{1,2})?$/',
                'min' => 0
            ],
            'observacion' => [
                'regex' => '/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s.,:\/-]{0,255}$/',
                'opcional' => true
            ],
            'tasa_dolar' => [
                'regex' => '/^\d+(\.\d{1,4})?$/',
                'opcional' => true
            ]
        ];

        $camposPorOperacion = [
            'registrar_presupuesto' => ['fecha', 'cuota_reserva', 'observacion', 'tasa_dolar'],
            'modificar_presupuesto' => ['id_presupuesto', 'fecha', 'cuota_reserva', 'observacion', 'tasa_dolar'],
            'eliminar_presupuesto'  => ['id_presupuesto'],
            'consultar_presupuesto' => ['id_presupuesto']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    public static function obtenerReglasDetalles() {
        return [
            'monto' => [
                'regex' => '/^\d+(\.\d{1,2})?$/',
                'min' => 0.01
            ],
            'concepto_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'conceptos_gasto', 'campo' => 'id_concepto']
            ]
        ];
    }

    public function set_id_presupuesto($id) { $this->id_presupuesto = $id; }
    public function get_id_presupuesto() { return $this->id_presupuesto; }
    public function set_fecha($f) { $this->fecha = $f; }
    public function get_fecha() { return $this->fecha; }
    public function set_cuota_reserva($c) { $this->cuota_reserva = $c; }
    public function get_cuota_reserva() { return $this->cuota_reserva; }
    public function set_observacion($o) { $this->observacion = $o; }
    public function get_observacion() { return $this->observacion; }
    public function set_activo($a) { $this->activo = $a; }
    public function get_activo() { return $this->activo; }

    public function set_id_detalle_presupuesto($id) { $this->id_detalle_presupuesto = $id; }
    public function get_id_detalle_presupuesto() { return $this->id_detalle_presupuesto; }
    public function set_monto_detalle($m) { $this->monto_detalle = $m; }
    public function get_monto_detalle() { return $this->monto_detalle; }
    public function set_concepto_id($concepto_id) { $this->concepto_id = $concepto_id; }
    public function get_concepto_id() { return $this->concepto_id; }

    public function set_mensualidad_id($m) { $this->mensualidad_id = $m; }
    public function get_mensualidad_id() { return $this->mensualidad_id; }

    public function setDetallesTemp($detalles) { $this->detalles_temp = $detalles; }
    public function setIdsDetallesString($ids) { $this->ids_detalles_string = $ids; }
    public function set_tasa_dolar($t) { $this->tasa_dolar = $t; }
    public function get_tasa_dolar() { return $this->tasa_dolar; }

    public function get_detalles() { 
        return $this->detalles_temp; 
    }

    public function resumirDetalles(?array $detalles): array
    {
        if (empty($detalles)) {
            return ['cantidad_renglones' => 0, 'monto_total' => '0.00'];
        }

        $total = 0.0;
        foreach ($detalles as $det) {
            $total += (float) ($det['monto'] ?? 0);
        }

        return [
            'cantidad_renglones' => count($detalles),
            'monto_total'        => number_format($total, 2, '.', '')
        ];
    }

    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            throw new NegocioException("La acción '$accion' no está implementada.", HttpCodigo::BAD_REQUEST->value);
        }
        return $this->$metodo();
    }

    private function _registrar_presupuesto()
    {
        if (empty($this->detalles_temp) || !is_array($this->detalles_temp)) {
            throw new NegocioException('No hay detalles para el presupuesto.', HttpCodigo::BAD_REQUEST->value);
        }

        $con = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $con->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $con->beginTransaction();

            $sqlHead = "INSERT INTO presupuesto (fecha, cuota_reserva, observacion, tasa_dolar, activo) 
                        VALUES (:fecha, :cuota, :obs, :tasa, 1)";
            $stmtH = $con->prepare($sqlHead);
            $stmtH->execute([
                ':fecha' => $this->fecha,
                ':cuota' => $this->cuota_reserva ?? 0,
                ':obs'   => $this->observacion,
                ':tasa'  => $this->tasa_dolar
            ]);
            $id_presupuesto = $con->lastInsertId();

            $total_monto = floatval($this->cuota_reserva);
            $ids_detalles = [];
            $sqlDet = "INSERT INTO detalles_presupuesto (monto, presupuesto_id, concepto_id) 
                       VALUES (:monto, :id_pre, :id_concepto)";
            $stmtD = $con->prepare($sqlDet);

            foreach ($this->detalles_temp as $det) {
                $stmtD->execute([
                    ':monto'       => $det['monto'],
                    ':id_pre'      => $id_presupuesto,
                    ':id_concepto' => $det['concepto_id']
                ]);
                $ids_detalles[] = $con->lastInsertId();
                $total_monto += floatval($det['monto']);
            }

            list($anio, $mes) = explode('-', $this->fecha);
            $stmtBuscaPer = $con->prepare("SELECT id_periodo FROM periodos_mensualidad WHERE mes = :mes AND anio = :anio LIMIT 1");
            $stmtBuscaPer->execute([':mes' => $mes, ':anio' => $anio]);
            $periodo_id = $stmtBuscaPer->fetchColumn();

            if (!$periodo_id) {
                $stmtInsertaPer = $con->prepare("INSERT INTO periodos_mensualidad (mes, anio, tasa_dolar, activo) VALUES (:mes, :anio, :tasa, 1)");
                $stmtInsertaPer->execute([':mes' => $mes, ':anio' => $anio, ':tasa' => $this->tasa_dolar]);
                $periodo_id = $con->lastInsertId();
            }

            $this->_sincronizar_mensualidades($con, $periodo_id, $total_monto, $ids_detalles);

            $con->commit();
            return ['estatus' => true, 'mensaje' => 'Presupuesto y mensualidades registrados con éxito', 'id' => $id_presupuesto];
        } catch (\Throwable $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }
            throw $e;
        }
    }

    private function _modificar_presupuesto()
    {
        $con = $this->get_conex(TipoBaseDatos::NEGOCIO);

        if ($this->_tiene_pagos_registrados($con, $this->id_presupuesto)) {
            throw new NegocioException("No se puede modificar este presupuesto porque ya existen residentes que han registrado pagos para este periodo.", HttpCodigo::BAD_REQUEST->value);
        }

        try {
            $con->beginTransaction();

            $sqlHead = "UPDATE presupuesto SET fecha = :fecha, cuota_reserva = :cuota, observacion = :obs, tasa_dolar = :tasa WHERE id_presupuesto = :id";
            $stmtH = $con->prepare($sqlHead);
            $stmtH->execute([
                ':fecha' => $this->fecha,
                ':cuota' => $this->cuota_reserva ?? 0,
                ':obs'   => $this->observacion,
                ':tasa'  => $this->tasa_dolar,
                ':id'    => $this->id_presupuesto
            ]);

            $con->prepare("DELETE FROM presupuesto_mensualidad WHERE detalle_presupuesto_id IN (SELECT id_detalle_presupuesto FROM detalles_presupuesto WHERE presupuesto_id = ?)")
                 ->execute([$this->id_presupuesto]);
            $con->prepare("DELETE FROM detalles_presupuesto WHERE presupuesto_id = ?")->execute([$this->id_presupuesto]);

            $total_monto = floatval($this->cuota_reserva);
            $ids_detalles = [];
            
            if (!empty($this->detalles_temp) && is_array($this->detalles_temp)) {
                $sqlDet = "INSERT INTO detalles_presupuesto (monto, presupuesto_id, concepto_id) 
                   VALUES (:monto, :id_pre, :id_concepto)";
                $stmtD = $con->prepare($sqlDet);
                foreach ($this->detalles_temp as $det) {
                    $stmtD->execute([
                        ':monto'       => $det['monto'],
                        ':id_pre'      => $this->id_presupuesto,
                        ':id_concepto' => $det['concepto_id']
                    ]);
                    $ids_detalles[] = $con->lastInsertId();
                    $total_monto += floatval($det['monto']);
                }
            }

            list($anio, $mes) = explode('-', $this->fecha);
            $stmtBuscaPer = $con->prepare("SELECT id_periodo FROM periodos_mensualidad WHERE mes = :mes AND anio = :anio LIMIT 1");
            $stmtBuscaPer->execute([':mes' => $mes, ':anio' => $anio]);
            $periodo_id = $stmtBuscaPer->fetchColumn();

            if ($periodo_id) {
                $this->_sincronizar_mensualidades($con, $periodo_id, $total_monto, $ids_detalles);
            }

            $con->commit();
            return ['estatus' => true, 'mensaje' => 'Presupuesto actualizado correctamente'];
        } catch (\Throwable $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }
            throw $e;
        }
    }

    private function _eliminar_presupuesto()
    {
        $con = $this->get_conex(TipoBaseDatos::NEGOCIO);

        if ($this->_tiene_pagos_registrados($con, $this->id_presupuesto)) {
            throw new NegocioException("Auditoría: No se puede eliminar el presupuesto porque ya existen pagos procesados o pendientes para este mes.", HttpCodigo::BAD_REQUEST->value);
        }

        try {
            $con->beginTransaction();

            $stmtBusca = $con->prepare("SELECT MONTH(fecha) as mes, YEAR(fecha) as anio FROM presupuesto WHERE id_presupuesto = :id");
            $stmtBusca->execute([':id' => $this->id_presupuesto]);
            $fechaPre = $stmtBusca->fetch(PDO::FETCH_ASSOC);

            if ($fechaPre) {
                $stmtPer = $con->prepare("SELECT id_periodo FROM periodos_mensualidad WHERE mes = :mes AND anio = :anio");
                $stmtPer->execute([':mes' => $fechaPre['mes'], ':anio' => $fechaPre['anio']]);
                $id_periodo = $stmtPer->fetchColumn();

                if ($id_periodo) {
                    $stmtDesactivarMens = $con->prepare("UPDATE mensualidad SET activo = 0 WHERE periodo_id = :periodo_id");
                    $stmtDesactivarMens->execute([':periodo_id' => $id_periodo]);
                    $stmtDesactivarPer = $con->prepare("UPDATE periodos_mensualidad SET activo = 0 WHERE id_periodo = :periodo_id");
                    $stmtDesactivarPer->execute([':periodo_id' => $id_periodo]);
                }
            }

            $sql = "UPDATE presupuesto SET activo = 0 WHERE id_presupuesto = :id";
            $stmt = $con->prepare($sql);
            $stmt->execute([':id' => $this->id_presupuesto]);

            $con->commit();
            return ['estatus' => true, 'mensaje' => 'Presupuesto y recibos pendientes eliminados correctamente.'];
        } catch (\Throwable $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }
            throw $e;
        }
    }

    private function _sincronizar_mensualidades($con, $periodo_id, $total_monto, $ids_detalles)
    {
        $stmtApt = $con->query("SELECT id_apartamento, porcentaje_participacion FROM apartamentos WHERE activo = 1 FOR UPDATE");
        $apartamentos = $stmtApt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($apartamentos)) {
            throw new NegocioException('No hay apartamentos activos para generar mensualidades.', HttpCodigo::BAD_REQUEST->value);
        }

        $sqlMens = "INSERT INTO mensualidad (monto, periodo_id, apartamento_id, porcentaje_interes, limite_mensualidad, activo) 
                    VALUES (:monto, :periodo_id, :apt_id, " . self::INTERES_MORA_DEFECTO . ", " . self::LIMITE_DIAS_MENSUALIDAD . ", " . self::ESTADO_ACTIVO . ")
                    ON DUPLICATE KEY UPDATE 
                        monto = VALUES(monto), 
                        activo = " . self::ESTADO_ACTIVO;
        $stmtMens = $con->prepare($sqlMens);

        $stmtGetId = $con->prepare("SELECT id_mensualidad FROM mensualidad WHERE periodo_id = :periodo_id AND apartamento_id = :apt_id");
        $stmtDelPuente = $con->prepare("DELETE FROM presupuesto_mensualidad WHERE mensualidad_id = :m_id");
        $stmtPuente = $con->prepare("INSERT INTO presupuesto_mensualidad (mensualidad_id, detalle_presupuesto_id) VALUES (:m_id, :dp_id)");

        foreach ($apartamentos as $apt) {
            $monto_apt = round(($total_monto * $apt['porcentaje_participacion']) / 100, 2);
            
            $stmtMens->execute([
                ':monto'  => $monto_apt,
                ':periodo_id' => $periodo_id,
                ':apt_id' => $apt['id_apartamento']
            ]);
            
            $stmtGetId->execute([':periodo_id' => $periodo_id, ':apt_id' => $apt['id_apartamento']]);
            $id_mensualidad = $stmtGetId->fetchColumn();

            if ($id_mensualidad) {
                $stmtDelPuente->execute([':m_id' => $id_mensualidad]);
                foreach ($ids_detalles as $id_det) {
                    $stmtPuente->execute([':m_id' => $id_mensualidad, ':dp_id' => $id_det]);
                }
            }
        }
    }

    private function _tiene_pagos_registrados($con, $id_presupuesto)
    {
        $stmtBusca = $con->prepare("SELECT MONTH(fecha) as mes, YEAR(fecha) as anio FROM presupuesto WHERE id_presupuesto = :id");
        $stmtBusca->execute([':id' => $id_presupuesto]);
        $fechaPre = $stmtBusca->fetch(PDO::FETCH_ASSOC);

        if ($fechaPre) {
            $stmtPer = $con->prepare("SELECT id_periodo FROM periodos_mensualidad WHERE mes = :mes AND anio = :anio");
            $stmtPer->execute([':mes' => $fechaPre['mes'], ':anio' => $fechaPre['anio']]);
            $id_periodo = $stmtPer->fetchColumn();

            if ($id_periodo) {
                $sqlPagos = "SELECT COUNT(*) FROM pagos_mensualidad pm 
                             JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad 
                             JOIN pagos p ON pm.pago_id = p.id_pago
                             WHERE m.periodo_id = :periodo_id 
                               AND p.activo = 1 
                               AND p.estado NOT IN (:rechazado, :anulado)";
                               
                $stmtCheck = $con->prepare($sqlPagos);
                $stmtCheck->execute([
                    ':periodo_id' => $id_periodo,
                    ':rechazado'  => EstadoPago::RECHAZADO->value,
                    ':anulado'    => EstadoPago::ANULADO->value   
                ]);
                
                return $stmtCheck->fetchColumn() > 0;
            }
        }
        return false;
    }

    private function _consultar_presupuestos_mensualidades()
    {
        $mes = date('m', strtotime($this->fecha));
        $anio = date('Y', strtotime($this->fecha));

        $sql = "SELECT 
                    tg.nombre_tipo_gasto AS nombre, 
                    SUM(dp.monto) AS monto, 
                    GROUP_CONCAT(dp.id_detalle_presupuesto) AS id_presupuestos_asociados
                FROM tipo_gasto tg
                INNER JOIN conceptos_gasto cg ON tg.id_tipo_gasto = cg.tipo_gasto_id
                INNER JOIN detalles_presupuesto dp ON dp.concepto_id = cg.id_concepto
                INNER JOIN presupuesto p ON dp.presupuesto_id = p.id_presupuesto
                WHERE MONTH(p.fecha) = :mes 
                  AND YEAR(p.fecha) = :anio 
                  AND p.activo = 1
                GROUP BY tg.nombre_tipo_gasto";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([
            ':mes' => $mes,
            ':anio' => $anio
        ]);
        
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar()
    {
        $sql = "SELECT p.*, 
                   COALESCE(SUM(dp.monto), 0) as total_estimado,
                   COUNT(dp.id_detalle_presupuesto) as cantidad_detalles
            FROM presupuesto p
            LEFT JOIN detalles_presupuesto dp ON p.id_presupuesto = dp.presupuesto_id
            WHERE p.activo = " . self::ESTADO_ACTIVO . "
            GROUP BY p.id_presupuesto
            ORDER BY p.fecha DESC";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_meses_faltantes()
    {
        $sql = "
            WITH RECURSIVE MesesDelCalendario (anio, mes) AS (
                SELECT 
                    CASE 
                        WHEN EXISTS (SELECT 1 FROM presupuesto WHERE activo = 1) 
                        THEN (SELECT YEAR(MIN(fecha)) FROM presupuesto WHERE activo = 1)
                        ELSE YEAR(CURDATE())
                    END,
                    CASE 
                        WHEN EXISTS (SELECT 1 FROM presupuesto WHERE activo = 1) 
                        THEN (SELECT MONTH(MIN(fecha)) FROM presupuesto WHERE activo = 1)
                        ELSE 1
                    END
                UNION ALL
                SELECT
                    CASE WHEN mes = 12 THEN anio + 1 ELSE anio END,
                    CASE WHEN mes = 12 THEN 1 ELSE mes + 1 END
                FROM MesesDelCalendario
                WHERE anio < YEAR(CURDATE()) OR (anio = YEAR(CURDATE()) AND mes < MONTH(CURDATE()))
            )
            SELECT
                c.anio AS anio_faltante,
                c.mes AS mes_faltante
            FROM MesesDelCalendario c
            LEFT JOIN presupuesto p ON c.anio = YEAR(p.fecha) AND c.mes = MONTH(p.fecha) AND p.activo = 1
            WHERE p.id_presupuesto IS NULL
            ORDER BY c.anio, c.mes
        ";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_presupuesto()
    {
        $sqlHead = "SELECT * FROM presupuesto WHERE id_presupuesto = :id AND activo = 1";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sqlHead);
        $stmt->execute([':id' => $this->id_presupuesto]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new NegocioException('Presupuesto no encontrado.', HttpCodigo::NO_ENCONTRADO->value);
        }

        $sqlDet = "SELECT dp.*, cg.nombre_concepto, tg.nombre_tipo_gasto 
                        FROM detalles_presupuesto dp 
                        LEFT JOIN conceptos_gasto cg ON dp.concepto_id = cg.id_concepto
                        LEFT JOIN tipo_gasto tg ON cg.tipo_gasto_id = tg.id_tipo_gasto
                        WHERE dp.presupuesto_id = :id";
        $stmtD = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sqlDet);
        $stmtD->execute([':id' => $this->id_presupuesto]);
        $data['detalles'] = $stmtD->fetchAll(PDO::FETCH_ASSOC);

        return ['estatus' => true, 'datos' => $data];
    }

    private function _consultar_cabecera_presupuesto()
    {
        $sql = "SELECT fecha, cuota_reserva, observacion FROM presupuesto WHERE id_presupuesto = :id AND activo = 1";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':id' => $this->id_presupuesto]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$datos) {
            throw new NegocioException('Presupuesto no encontrado.', HttpCodigo::NO_ENCONTRADO->value);
        }

        return ['estatus' => true, 'datos' => $datos];
    }

    private function _consultar_mes_presupuesto()
    {
        $mes = (int)$this->fecha;
        if ($mes < 1 || $mes > 12) {
            throw new NegocioException('Mes inválido.', HttpCodigo::BAD_REQUEST->value);
        }
        $sql = "SELECT 1 FROM presupuesto WHERE MONTH(fecha) = :mes AND activo = 1 LIMIT 1";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':mes', $mes, PDO::PARAM_INT);
        $stmt->execute();
        $existe = $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
        return ['estatus' => true, 'datos' => $existe];
    }

    private function _consultar_anio_presupuesto()
    {
        $anio = (int)$this->fecha;

        if ($anio < self::ANIO_MINIMO_PERMITIDO || $anio > self::ANIO_MAXIMO_PERMITIDO) {
            throw new NegocioException('Año inválido.', HttpCodigo::BAD_REQUEST->value);
        }

        $sql = "SELECT 1 FROM presupuesto WHERE YEAR(fecha) = :anio AND activo = 1 LIMIT 1";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
        $stmt->execute();
        $existe = $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
        return ['estatus' => true, 'datos' => $existe];
    }

    private function _consultar_ejecucion_anual()
    {
        $anioFiltro = $this->fecha ? date('Y', strtotime($this->fecha)) : date('Y');

        $sql = "SELECT 
                    partida,
                    SUM(monto_presupuestado) as total_presupuestado,
                    SUM(monto_ejecutado) as total_ejecutado,
                    SUM(disponible) as total_disponible
                FROM vw_ejecucion_presupuesto
                WHERE anio_presupuesto = :anio
                GROUP BY partida
                ORDER BY total_presupuestado DESC";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':anio' => $anioFiltro]);
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }
}