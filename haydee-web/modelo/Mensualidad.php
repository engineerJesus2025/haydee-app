<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\TipoBaseDatos;
use haydee\enums\EstadoPago;
use haydee\enums\TipoVinculo;
use haydee\enums\HttpCodigo;
use haydee\excepciones\NegocioException;

class Mensualidad extends Conexion
{
    private const ANIO_MINIMO_PERMITIDO = 2000;
    private const ANIO_MAXIMO_PERMITIDO = 2100;

    private $id_mensualidad;
    private $monto;
    private $tasa_dolar;
    private $mes;
    private $anio;
    private $apartamento_id;
    private $porcentaje_interes;
    private $limite_mensualidad;
    private $activo;
    private $periodo_id;

    private $datos_apartamentos = [];
    private $ids_mensualidades;
    private $correo;

    public static function obtenerReglas($operacion) {
        $reglasCampos = [
            'id_mensualidad' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'mensualidad', 'campo' => 'id_mensualidad']
            ],
            'mes' => [
                'regex' => '/^(0?[1-9]|1[0-2])$/'
            ],
            'anio' => [
                'regex' => '/^\d{4}$/',
                'min' => self::ANIO_MINIMO_PERMITIDO,
                'max' => self::ANIO_MAXIMO_PERMITIDO
            ],
            'tasa_dolar' => [
                'regex' => '/^\d+(\.\d{1,4})?$/',
                'min' => 0
            ],
            'porcentaje_interes' => [
                'regex' => '/^\d+(\.\d{1,2})?$/',
                'min' => 0,
                'opcional' => true
            ],
            'limite_mensualidad' => [
                'regex' => '/^\d+$/',
                'min' => 0,
                'opcional' => true
            ],
            'apartamento_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'apartamentos', 'campo' => 'id_apartamento']
            ],
            'periodo_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'periodos_mensualidad', 'campo' => 'id_periodo']
            ],
        ];

        $configPorOperacion = [
            'consultarPorMeses' => [
                'metodo_http' => ['GET'],
                'campos' => [] 
            ],
            'verificarMeses' => [
                'metodo_http' => ['GET'],
                'campos' => []
            ],
            'consultar_mensualidad_apartamentos' => [
                'metodo_http' => ['GET'],
                'campos' => ['mes', 'anio']
            ],
            'consultar_meses_mensualidad' => [
                'metodo_http' => ['GET'],
                'campos' => []
            ],
            'consultar_tasa_dolar_mensualidades' => [
                'metodo_http' => ['GET'],
                'campos' => ['mes', 'anio']
            ],
            'consultar_kpis' => [
                'metodo_http' => ['GET'],
                'campos' => []
            ],
            'consultar_desglose' => [
                'metodo_http' => ['GET'],
                'campos' => ['id_mensualidad']
            ],
            'consultar_presupuestos_asociados' => [
                'metodo_http' => ['GET'],
                'campos' => ['periodo_id']
            ],
            'consultar_cabecera_mensualidad' => [
                'metodo_http' => ['GET'],
                'campos' => ['mes', 'anio']
            ],
            'consultar_estadisticas_inicio' => [
                'metodo_http' => ['GET'],
                'campos' => []
            ],
            'consultar_tarjetas_resumen' => [
                'metodo_http' => ['GET'],
                'campos' => []
            ],
            'registrar' => [
                'metodo_http' => ['POST'],
                'campos' => ['mes', 'anio', 'tasa_dolar', 'porcentaje_interes', 'limite_mensualidad']
            ],
            'modificar' => [
                'metodo_http' => ['PUT', 'POST'],
                'campos' => ['periodo_id', 'tasa_dolar', 'porcentaje_interes', 'limite_mensualidad']
            ],
            'eliminar' => [
                'metodo_http' => ['DELETE', 'POST'],
                'campos' => ['periodo_id']
            ]
        ];

        if (isset($configPorOperacion[$operacion])) {
            $config = $configPorOperacion[$operacion];
            $reglasFiltradas = array_intersect_key($reglasCampos, array_flip($config['campos']));
            if (in_array('ids_mensualidades', $config['campos'])) {
                $reglasFiltradas['ids_mensualidades'] = [
                    'regex' => '/^[\d,]+$/'
                ];
            }
            $reglasFiltradas['__metodo_http_permitido__'] = $config['metodo_http'];
            return $reglasFiltradas;
        }

        return [];
    }

    public static function obtenerReglasDetalles() {
        return [
            'id_apartamento' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'apartamentos', 'campo' => 'id_apartamento']
            ],
            'monto' => [
                'regex' => '/^\d+(\.\d{1,2})?$/',
                'min' => 0.01
            ],
            'id_mensualidad' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'mensualidad', 'campo' => 'id_mensualidad'],
                'opcional' => true
            ],
            'descuento' => ['regex' => '/^\d+(\.\d{1,2})?$/', 'min' => 0]
        ];
    }

    public function set_id_mensualidad($id) { $this->id_mensualidad = $id; }
    public function get_id_mensualidad() { return $this->id_mensualidad; }
    public function set_monto($m) { $this->monto = $m; }
    public function get_monto() { return $this->monto; }
    public function set_tasa_dolar($t) { $this->tasa_dolar = $t; }
    public function get_tasa_dolar() { return $this->tasa_dolar; }
    public function set_mes($m) { $this->mes = $m; }
    public function get_mes() { return $this->mes; }
    public function set_anio($a) { $this->anio = $a; }
    public function get_anio() { return $this->anio; }
    public function set_apartamento_id($id) { $this->apartamento_id = $id; }
    public function get_apartamento_id() { return $this->apartamento_id; }
    public function set_porcentaje_interes($p) { $this->porcentaje_interes = $p; }
    public function get_porcentaje_interes() { return $this->porcentaje_interes; }
    public function set_limite_mensualidad($l) { $this->limite_mensualidad = $l; }
    public function get_limite_mensualidad() { return $this->limite_mensualidad; }
    public function set_activo($a) { $this->activo = $a; }
    public function get_activo() { return $this->activo; }
    public function set_periodo_id($id) { $this->periodo_id = $id; }
    public function get_periodo_id() { return $this->periodo_id; }
    public function set_datos_apartamentos($datos) { $this->datos_apartamentos = $datos; }
    public function get_datos_apartamentos() { return $this->datos_apartamentos; }
    public function set_ids_mensualidades($ids) { $this->ids_mensualidades = $ids; }
    public function get_ids_mensualidades() { return $this->ids_mensualidades; }
    public function set_correo($correo) { $this->correo = $correo; }
    public function get_correo() { return $this->correo; }

    public function get_detalles() { 
        return $this->datos_apartamentos; 
    }

    public function resumirDetalles(?array $detalles): array
    {
        if (empty($detalles)) {
            return ['cantidad_apartamentos' => 0, 'monto_total_generado' => '0.00', 'descuentos_aplicados' => '0.00'];
        }

        $total = 0.0;
        $descuento = 0.0;
        foreach ($detalles as $det) {
            $total += (float) ($det['monto'] ?? 0);
            $descuento += (float) ($det['descuento'] ?? 0);
        }

        return [
            'cantidad_apartamentos' => count($detalles),
            'monto_total_generado'  => number_format($total, 2, '.', ''),
            'descuentos_aplicados'  => number_format($descuento, 2, '.', '')
        ];
    }

    private function _consultar_auditoria()
    {
        // Recuperamos la cabecera (tasa, periodo, etc.)
        $cabecera = $this->_consultar_cabecera_mensualidad()['datos'];
        
        // Recuperamos el estado actual de los apartamentos asociados a ese periodo
        $sql = "SELECT apartamento_id as id_apartamento, monto, descuento 
                FROM mensualidad 
                WHERE periodo_id = :periodo_id AND activo = 1";
        $stmt = $this->get_conex(\haydee\enums\TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':periodo_id' => $this->periodo_id]);
        
        // Lo inyectamos en la clave 'detalles' para que el auditor lo extraiga
        $cabecera['detalles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ['estatus' => true, 'datos' => $cabecera];
    }

    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            throw new NegocioException("La acción '$accion' no está implementada.", HttpCodigo::BAD_REQUEST->value);
        }
        return $this->$metodo();
    }

    private function _verificarMeses()
    {
        $sql = "SELECT DISTINCT MONTH(p.fecha) as mes_presupuesto, YEAR(p.fecha) as anio_presupuesto 
                FROM presupuesto p
                WHERE NOT EXISTS (
                    SELECT 1 
                    FROM periodos_mensualidad pm 
                    WHERE pm.mes = MONTH(p.fecha) 
                      AND pm.anio = YEAR(p.fecha)
                      AND pm.activo = 1
                ) AND p.activo = 1
                ORDER BY anio_presupuesto ASC, mes_presupuesto ASC";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultarPorMeses()
    {
        $sql = "SELECT 
                    pm.id_periodo,
                    SUM(v.monto_cuota) as monto,
                    pm.tasa_dolar,
                    v.mes,
                    v.anio,
                    SUM(LEAST(v.monto_cuota, v.total_abonado)) as pagado,
                    MAX(m.porcentaje_interes) as porcentaje_interes,
                    MAX(m.limite_mensualidad) as limite_mensualidad
                FROM vw_estado_cuentas_mensualidad v
                INNER JOIN periodos_mensualidad pm ON pm.mes = v.mes AND pm.anio = v.anio
                INNER JOIN mensualidad m ON m.id_mensualidad = v.id_mensualidad
                WHERE pm.activo = 1
                GROUP BY pm.id_periodo, v.mes, v.anio, pm.tasa_dolar";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_mensualidad_apartamentos()
    {
        $mesInt = (int)$this->mes;
        $anioInt = (int)$this->anio;
        $propietario = TipoVinculo::PROPIETARIO->value;

        $sql = "SELECT v.id_mensualidad, v.apartamento_id, v.mes, v.anio,
                       v.nro_apartamento,
                       COALESCE(h.nombre, 'Sin Propietario') AS nombre, 
                       COALESCE(h.apellido, '') AS apellido,
                       v.monto_cuota AS monto, pm.tasa_dolar,
                       v.total_abonado AS pagado,
                       ROUND(v.total_abonado / pm.tasa_dolar, 2) AS pagado_dolar
                FROM vw_estado_cuentas_mensualidad v
                INNER JOIN periodos_mensualidad pm ON pm.mes = v.mes AND pm.anio = v.anio
                LEFT JOIN habitantes_apartamentos ha ON ha.apartamento_id = v.apartamento_id AND ha.tipo_vinculo = :propietario
                LEFT JOIN habitantes h ON ha.habitante_id = h.id_habitante
                WHERE v.mes = :mes AND v.anio = :anio
                  AND pm.activo = 1";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':mes', $mesInt, PDO::PARAM_INT);
        $stmt->bindParam(':anio', $anioInt, PDO::PARAM_INT);
        $stmt->bindParam(':propietario', $propietario);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_presupuestos_asociados()
    {
        if (empty($this->periodo_id)) {
            throw new NegocioException('ID de período no proporcionado.', HttpCodigo::BAD_REQUEST->value);
        }

        $sql = "SELECT m.apartamento_id, m.id_mensualidad, m.descuento,
                       GROUP_CONCAT(pm.detalle_presupuesto_id) as presupuestos
                FROM mensualidad m
                LEFT JOIN presupuesto_mensualidad pm ON m.id_mensualidad = pm.mensualidad_id
                WHERE m.periodo_id = :periodo_id AND m.activo = 1
                GROUP BY m.id_mensualidad, m.apartamento_id";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':periodo_id' => $this->periodo_id]);
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_cabecera_mensualidad()
    {
        $sql = "SELECT pm.tasa_dolar, pm.mes, pm.anio, m.porcentaje_interes, m.limite_mensualidad 
                FROM mensualidad m
                INNER JOIN periodos_mensualidad pm ON m.periodo_id = pm.id_periodo
                WHERE pm.id_periodo = :periodo_id AND m.activo = 1 LIMIT 1";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':periodo_id', $this->periodo_id, PDO::PARAM_INT);
        $stmt->execute();
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$datos) {
            throw new NegocioException('Mensualidad no encontrada para este mes y año.', HttpCodigo::NO_ENCONTRADO->value);
        }
        return ['estatus' => true, 'datos' => $datos];
    }

    private function _registrar()
    {
        $id_mensualidad = null;
        $con = $this->get_conex(TipoBaseDatos::NEGOCIO);

        try {
            $con->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $con->beginTransaction();

            $sqlPeriodo = "INSERT INTO periodos_mensualidad (mes, anio, tasa_dolar, activo) 
                           VALUES (:mes, :anio, :tasa_dolar, 1)
                           ON DUPLICATE KEY UPDATE 
                               activo = 1, 
                               tasa_dolar = VALUES(tasa_dolar)";
            $stmtPer = $con->prepare($sqlPeriodo);
            $stmtPer->execute([
                ':mes' => $this->mes,
                ':anio' => $this->anio,
                ':tasa_dolar' => $this->tasa_dolar
            ]);

            $stmtBusca = $con->prepare("SELECT id_periodo FROM periodos_mensualidad WHERE mes = :mes AND anio = :anio");
            $stmtBusca->execute([':mes' => $this->mes, ':anio' => $this->anio]);
            $id_periodo_actual = $stmtBusca->fetchColumn();

            if (!$id_periodo_actual) {
                throw new NegocioException("Error al obtener el ID del periodo fiscal.", HttpCodigo::ERROR_INTERNO->value);
            }

            $sqlUpsert = "INSERT INTO mensualidad (monto, descuento, periodo_id, apartamento_id, porcentaje_interes, limite_mensualidad, activo)
                          VALUES (:monto, :descuento, :periodo_id, :apartamento_id, :porcentaje_interes, :limite_mensualidad, 1)
                          ON DUPLICATE KEY UPDATE 
                              monto = VALUES(monto),
                              descuento = VALUES(descuento),
                              porcentaje_interes = VALUES(porcentaje_interes),
                              limite_mensualidad = VALUES(limite_mensualidad),
                              activo = 1";
            $stmtM = $con->prepare($sqlUpsert);

            $stmtGetId = $con->prepare("SELECT id_mensualidad FROM mensualidad WHERE periodo_id = :periodo_id AND apartamento_id = :apartamento_id");
            $stmtDeletePuente = $con->prepare("DELETE FROM presupuesto_mensualidad WHERE mensualidad_id = :men_id");
            $stmtP = $con->prepare("INSERT INTO presupuesto_mensualidad (detalle_presupuesto_id, mensualidad_id) VALUES (:det_id, :men_id)");

            foreach ($this->datos_apartamentos as $item) {
                $stmtM->execute([
                    ':monto' => $item['monto'],
                    ':descuento' => $item['descuento'] ?? 0.00,
                    ':periodo_id' => $id_periodo_actual, 
                    ':apartamento_id' => $item['id_apartamento'],
                    ':porcentaje_interes' => $this->porcentaje_interes,
                    ':limite_mensualidad' => $this->limite_mensualidad
                ]);

                $stmtGetId->execute([
                    ':periodo_id' => $id_periodo_actual,
                    ':apartamento_id' => $item['id_apartamento']
                ]);
                $id_mensualidad = $stmtGetId->fetchColumn();

                $stmtDeletePuente->execute([':men_id' => $id_mensualidad]);

                if (!empty($item['id_presupuestos']) && is_array($item['id_presupuestos'])) {
                    foreach ($item['id_presupuestos'] as $id_detalle) {
                        $stmtP->execute([':det_id' => $id_detalle, ':men_id' => $id_mensualidad]);
                    }
                }
            }

            $con->commit();
            return ['estatus' => true, 'mensaje' => 'Todas las mensualidades se registraron/reactivaron correctamente.', 'lastId' => $id_mensualidad];

        } catch (\Throwable $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }
            throw $e;
        }
    }

    private function _modificar()
    {
        $con = $this->get_conex(TipoBaseDatos::NEGOCIO);

        try {
            $con->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $con->beginTransaction();

            if ($this->_tiene_pagos_registrados($con)) {
                throw new NegocioException("No se puede alterar ni eliminar esta mensualidad porque posee pagos en estado de revisión o ya procesados.", HttpCodigo::BAD_REQUEST->value);
            }

            $sqlUpdatePeriodo = "UPDATE periodos_mensualidad SET tasa_dolar = :tasa_dolar WHERE id_periodo = :periodo_id";
            $stmtUpdPer = $con->prepare($sqlUpdatePeriodo);
            $stmtUpdPer->execute([':tasa_dolar' => $this->tasa_dolar, ':periodo_id' => $this->periodo_id]);

            $stmtBusca = $con->prepare("SELECT id_periodo FROM periodos_mensualidad WHERE id_periodo = :periodo_id FOR UPDATE");
            $stmtBusca->execute([':periodo_id' => $this->periodo_id]);
            $id_periodo_actual = $stmtBusca->fetchColumn();

            if (!$id_periodo_actual) {
                throw new NegocioException("El periodo fiscal no existe o no se pudo bloquear.", HttpCodigo::NO_ENCONTRADO->value);
            }

            $sqlUpsert = "INSERT INTO mensualidad (monto, descuento, periodo_id, apartamento_id, porcentaje_interes, limite_mensualidad, activo)
                          VALUES (:monto, :descuento, :periodo_id, :apartamento_id, :porcentaje_interes, :limite_mensualidad, 1)
                          ON DUPLICATE KEY UPDATE 
                              monto = VALUES(monto),
                              descuento = VALUES(descuento),
                              porcentaje_interes = VALUES(porcentaje_interes),
                              limite_mensualidad = VALUES(limite_mensualidad),
                              activo = 1";
            $stmtUpsert = $con->prepare($sqlUpsert);

            $stmtGetId = $con->prepare("SELECT id_mensualidad FROM mensualidad WHERE periodo_id = :periodo_id AND apartamento_id = :apartamento_id");
            $stmtDeletePuente = $con->prepare("DELETE FROM presupuesto_mensualidad WHERE mensualidad_id = :men_id");
            $stmtInsertPuente = $con->prepare("INSERT INTO presupuesto_mensualidad (detalle_presupuesto_id, mensualidad_id) VALUES (:det_id, :men_id)");

            foreach ($this->datos_apartamentos as $item) {
                $stmtUpsert->execute([
                    ':monto' => $item['monto'],
                    ':descuento' => $item['descuento'] ?? 0.00,
                    ':periodo_id' => $id_periodo_actual,
                    ':apartamento_id' => $item['id_apartamento'],
                    ':porcentaje_interes' => $this->porcentaje_interes,
                    ':limite_mensualidad' => $this->limite_mensualidad
                ]);

                $stmtGetId->execute([
                    ':periodo_id' => $id_periodo_actual,
                    ':apartamento_id' => $item['id_apartamento']
                ]);
                $id_mensualidad = $stmtGetId->fetchColumn();

                $stmtDeletePuente->execute([':men_id' => $id_mensualidad]);

                if (!empty($item['id_presupuestos']) && is_array($item['id_presupuestos'])) {
                    foreach ($item['id_presupuestos'] as $id_detalle) {
                        $stmtInsertPuente->execute([':det_id' => $id_detalle, ':men_id' => $id_mensualidad]);
                    }
                }
            }

            $con->commit();
            return ['estatus' => true, 'mensaje' => 'Mensualidades sincronizadas correctamente preservando el historial financiero.'];

        } catch (\Throwable $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }
            throw $e;
        }
    }

    private function _eliminar()
    {
        $con = $this->get_conex(TipoBaseDatos::NEGOCIO);

        try {
            $con->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $con->beginTransaction();

            if ($this->_tiene_pagos_registrados($con)) {
                throw new NegocioException("No se puede alterar ni eliminar esta mensualidad porque posee pagos en estado de revisión o ya procesados.", HttpCodigo::BAD_REQUEST->value);
            }

            $sqlPeriodo = "UPDATE periodos_mensualidad SET activo = 0 WHERE id_periodo = :periodo_id";
            $stmtP = $con->prepare($sqlPeriodo);
            $stmtP->execute([':periodo_id' => $this->periodo_id]);

            $sqlMensualidad = "UPDATE mensualidad SET activo = 0 WHERE periodo_id = :periodo_id";
            $stmtM = $con->prepare($sqlMensualidad);
            $stmtM->execute([':periodo_id' => $this->periodo_id]);

            $con->commit();
            return ['estatus' => true, 'mensaje' => 'Periodo y mensualidades desactivados correctamente'];

        } catch (\Throwable $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }
            throw $e;
        }
    }

    private function _tiene_pagos_registrados($con)
    {
        if (!empty($this->id_mensualidad)) {
            $sqlPagos = "SELECT COUNT(*) FROM pagos_mensualidad pm 
                         JOIN pagos p ON pm.pago_id = p.id_pago
                         WHERE pm.mensualidad_id = :id_mensualidad 
                           AND p.activo = 1 
                           AND p.estado NOT IN (:rechazado, :anulado)";
                           
            $stmtCheck = $con->prepare($sqlPagos);
            $stmtCheck->execute([
                ':id_mensualidad' => $this->id_mensualidad,
                ':rechazado' => EstadoPago::RECHAZADO->value,
                ':anulado' => EstadoPago::ANULADO->value     
            ]);
            
            return $stmtCheck->fetchColumn() > 0;
        }

        if (!empty($this->periodo_id)) {
            $sqlPagos = "SELECT COUNT(*) FROM pagos_mensualidad pm 
                         JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad 
                         JOIN pagos p ON pm.pago_id = p.id_pago
                         WHERE m.periodo_id = :periodo_id 
                           AND p.activo = 1 
                           AND p.estado NOT IN (:rechazado, :anulado)";
                           
            $stmtCheck = $con->prepare($sqlPagos);
            $stmtCheck->execute([
                ':periodo_id' => $this->periodo_id,
                ':rechazado' => EstadoPago::RECHAZADO->value,
                ':anulado' => EstadoPago::ANULADO->value     
            ]);
            
            return $stmtCheck->fetchColumn() > 0;
        }

        if (!empty($this->mes) && !empty($this->anio)) {
            $sqlPagos = "SELECT COUNT(*) FROM pagos_mensualidad pm 
                         JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad 
                         JOIN periodos_mensualidad per ON m.periodo_id = per.id_periodo
                         JOIN pagos p ON pm.pago_id = p.id_pago
                         WHERE per.mes = :mes AND per.anio = :anio
                           AND p.activo = 1 
                           AND p.estado NOT IN (:rechazado, :anulado)";
                           
            $stmtCheck = $con->prepare($sqlPagos);
            $stmtCheck->execute([
                ':mes' => $this->mes,
                ':anio' => $this->anio,
                ':rechazado' => EstadoPago::RECHAZADO->value,
                ':anulado' => EstadoPago::ANULADO->value     
            ]);
            
            return $stmtCheck->fetchColumn() > 0;
        }

        return false;
    }

    private function _consultar_meses_mensualidad()
    {
        $sql = "SELECT pm.mes, pm.anio 
                FROM periodos_mensualidad pm
                INNER JOIN mensualidad m ON m.periodo_id = pm.id_periodo
                WHERE m.activo = 1
                GROUP BY pm.anio, pm.mes 
                ORDER BY pm.anio DESC, pm.mes DESC";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_tasa_dolar_mensualidades()
    {
        $sql = "SELECT MAX(mes) as mes, MAX(anio) as anio, MAX(tasa_dolar) as tasa_dolar
                FROM periodos_mensualidad 
                WHERE anio = :anio AND mes = TRIM(LEADING '0' FROM :mes)";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':mes', $this->mes);
        $stmt->bindParam(':anio', $this->anio);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetch(PDO::FETCH_ASSOC)];
    }

    private function _consultar_estadisticas_inicio()
    {
        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        $sql = "
            SELECT 
                SUM(CASE WHEN deuda_pendiente > 0 THEN 1 ELSE 0 END) as aptos_morosos,
                SUM(CASE WHEN deuda_pendiente = 0 OR deuda_pendiente IS NULL THEN 1 ELSE 0 END) as aptos_solventes
            FROM (
                SELECT a.id_apartamento, COALESCE(SUM(v.deuda_pendiente), 0) as deuda_pendiente
                FROM apartamentos a
                LEFT JOIN vw_estado_cuentas_mensualidad v 
                    ON a.nro_apartamento = v.nro_apartamento 
                    AND CAST(v.estado_pago AS CHAR) = '" . EstadoPago::PENDIENTE->value . "' 
                WHERE a.activo = 1
                GROUP BY a.id_apartamento
            ) as estado_aptos
        ";
        $stmt1 = $pdo->prepare($sql);
        $stmt1->execute();
        $datos_grafico_1 = $stmt1->fetch(PDO::FETCH_ASSOC);

        $meses_nombres = ['01'=>'Ene', '02'=>'Feb', '03'=>'Mar', '04'=>'Abr', '05'=>'May', '06'=>'Jun', '07'=>'Jul', '08'=>'Ago', '09'=>'Sep', '10'=>'Oct', '11'=>'Nov', '12'=>'Dic'];
        $grafico_2 = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $fecha_calculo = strtotime("-$i months");
            $llave_mes = date('Y-m', $fecha_calculo); 
            $nombre_mes = $meses_nombres[date('m', $fecha_calculo)] . ' ' . date('y', $fecha_calculo);
            $grafico_2[$llave_mes] = ['etiqueta' => $nombre_mes, 'ingresos' => 0, 'gastos' => 0];
        }
        
        $fecha_inicio_filtro = date('Y-m-01', strtotime("-5 months"));

        $sql_ingresos = "SELECT DATE_FORMAT(dp.fecha, '%Y-%m') as mes_anio, SUM(dp.monto) as total
                         FROM detalles_pagos dp
                         JOIN pagos p ON dp.pago_id = p.id_pago
                         WHERE p.activo = 1 AND LOWER(p.estado) = 'procesado' AND dp.fecha >= :fecha_inicio
                         GROUP BY mes_anio";
        $stmt_in = $pdo->prepare($sql_ingresos);
        $stmt_in->execute([':fecha_inicio' => $fecha_inicio_filtro]);
        
        while ($row = $stmt_in->fetch(PDO::FETCH_ASSOC)) {
            if (isset($grafico_2[$row['mes_anio']])) {
                $grafico_2[$row['mes_anio']]['ingresos'] = (float)$row['total'];
            }
        }

        $sql_gastos = "SELECT DATE_FORMAT(dg.fecha, '%Y-%m') as mes_anio, SUM(dg.monto) as total
                       FROM detalles_gastos dg
                       JOIN gastos g ON dg.gasto_id = g.id_gasto
                       WHERE g.activo = 1 AND dg.fecha >= :fecha_inicio
                       GROUP BY mes_anio";
        $stmt_out = $pdo->prepare($sql_gastos);
        $stmt_out->execute([':fecha_inicio' => $fecha_inicio_filtro]);
        
        while ($row = $stmt_out->fetch(PDO::FETCH_ASSOC)) {
            if (isset($grafico_2[$row['mes_anio']])) {
                $grafico_2[$row['mes_anio']]['gastos'] = (float)$row['total'];
            }
        }

        return [
            'estatus' => true, 
            'datos' => [
                'grafico_deudas' => $datos_grafico_1,
                'grafico_ingresos_gastos' => array_values($grafico_2) 
            ]
        ];
    }

    private function _consultar_tarjetas_resumen()
    {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM apartamentos WHERE activo = 1) AS total_apartamentos,
                    (SELECT COUNT(DISTINCT ha.apartamento_id) 
                     FROM habitantes_apartamentos ha 
                     JOIN habitantes h ON ha.habitante_id = h.id_habitante 
                     WHERE h.activo = 1) AS apartamentos_ocupados,
                    (SELECT COUNT(*) FROM habitantes WHERE activo = 1) AS residentes_activos,
                    (SELECT COALESCE(SUM(dp.monto), 0) 
                     FROM detalles_pagos dp 
                     JOIN pagos p ON dp.pago_id = p.id_pago 
                     WHERE p.activo = 1 
                       AND MONTH(dp.fecha) = MONTH(CURDATE()) 
                       AND YEAR(dp.fecha) = YEAR(CURDATE())) AS recaudado_mes,
                    (SELECT COUNT(*) 
                     FROM vw_estado_cuentas_mensualidad 
                     WHERE estado_pago = 'PENDIENTE') AS recibos_pendientes,
                    (SELECT COALESCE(SUM(deuda_pendiente), 0) 
                     FROM vw_estado_cuentas_mensualidad 
                     WHERE CAST(estado_pago AS CHAR) = 'PENDIENTE') AS deuda_total";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetch(PDO::FETCH_ASSOC)];
    }

    private function _consultar_kpis()
    {
        $condicionDeuda = "";
        $condicionPago = "";
        $params = [];

        $estadoPendiente = EstadoPago::PENDIENTE->value;
        $estadoProcesado = EstadoPago::PROCESADO->value;

        if (!empty($this->correo)) {
            $condicionDeuda = " AND nro_apartamento IN (
                SELECT a.nro_apartamento FROM apartamentos a
                JOIN habitantes_apartamentos ha ON a.id_apartamento = ha.apartamento_id
                JOIN habitantes h ON ha.habitante_id = h.id_habitante
                WHERE h.correo = :correo
            )";
            
            $condicionPago = " AND p.id_pago IN (
                SELECT pm2.pago_id FROM pagos_mensualidad pm2
                JOIN mensualidad m2 ON pm2.mensualidad_id = m2.id_mensualidad
                JOIN apartamentos a2 ON m2.apartamento_id = a2.id_apartamento
                JOIN habitantes_apartamentos ha2 ON a2.id_apartamento = ha2.apartamento_id
                JOIN habitantes h2 ON ha2.habitante_id = h2.id_habitante
                WHERE h2.correo = :correo2
            )";
            
            $params[':correo'] = $this->correo;
            $params[':correo2'] = $this->correo;
        }

        $sql = "SELECT 
                    (SELECT COALESCE(SUM(deuda_pendiente), 0) 
                     FROM vw_estado_cuentas_mensualidad 
                     WHERE UPPER(estado_pago) = '" . strtoupper($estadoPendiente) . "' $condicionDeuda) AS deuda_total,
                     
                    (SELECT COALESCE(SUM(dp.monto), 0) 
                     FROM detalles_pagos dp 
                     JOIN pagos p ON dp.pago_id = p.id_pago 
                     WHERE p.activo = 1 
                       AND UPPER(p.estado) = '" . strtoupper($estadoProcesado) . "'
                       AND MONTH(dp.fecha) = MONTH(CURDATE()) 
                       AND YEAR(dp.fecha) = YEAR(CURDATE())
                       $condicionPago) AS recaudado_mes,
                       
                    (SELECT COALESCE(SUM(dg.monto), 0) 
                     FROM detalles_gastos dg 
                     JOIN gastos g ON dg.gasto_id = g.id_gasto 
                     WHERE g.activo = 1 
                       AND MONTH(dg.fecha) = MONTH(CURDATE()) 
                       AND YEAR(dg.fecha) = YEAR(CURDATE())) AS gastado_mes";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute($params);
        return ['estatus' => true, 'datos' => $stmt->fetch(PDO::FETCH_ASSOC)];
    }

    private function _consultar_desglose()
    {
        if (empty($this->id_mensualidad)) {
            throw new NegocioException('ID de mensualidad no proporcionado.', HttpCodigo::BAD_REQUEST->value);
        }

        $sql = "SELECT 
                    dp.id_detalle_presupuesto, 
                    dp.nombre_detalle AS concepto, 
                    dp.monto, 
                    dp.tasa_dolar 
                FROM presupuesto_mensualidad pm
                INNER JOIN detalles_presupuesto dp ON pm.detalle_presupuesto_id = dp.id_detalle_presupuesto
                WHERE pm.mensualidad_id = :id_mensualidad";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':id_mensualidad' => $this->id_mensualidad]);
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }
}