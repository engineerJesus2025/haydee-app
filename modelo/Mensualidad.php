<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Mensualidad extends Conexion
{
    // ====================================================================
    // PROPIEDADES
    // ====================================================================
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

    // ====================================================================
    // REGLAS DE VALIDACIÓN (Para el Helper Validador)
    // ====================================================================
    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
            'fecha' => [
                'regex' => '/^\d{4}-\d{2}-\d{2}$/'
            ],
            'mes' => [
                'regex' => '/^(0?[1-9]|1[0-2])$/'
            ],
            'anio' => [
                'regex' => '/^\d{4}$/',
                'min' => 2000,
                'max' => 2100
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
            ]
        ];

        $camposPorOperacion = [
            'registrar_mensualidad' => ['mes', 'anio', 'tasa_dolar', 'porcentaje_interes', 'limite_mensualidad'],
            'modificar_mensualidad' => ['mes', 'anio', 'tasa_dolar', 'porcentaje_interes', 'limite_mensualidad'],
            'eliminar_mensualidad'  => ['fecha'],
            'consultar_mensualidades_apartamentos' => ['fecha'],
            'consultar_tasa_dolar'  => ['mes', 'anio']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
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
            ]
        ];
    }

    // ====================================================================
    // GETTERS Y SETTERS
    // ====================================================================
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

    // ====================================================================
    // ENRUTADOR CON MANEJO DE EXCEPCIONES
    // ====================================================================
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

    // ====================================================================
    // MÉTODOS PRIVADOS (ACCIONES)
    // ====================================================================

    // SE USA EN EL MODULO
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
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _verificarMeses: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al verificar meses'];
        }
    }

    // SE USA EN EL MODULO
    private function _consultarPorMeses()
    {
        $sql = "SELECT 
                    GROUP_CONCAT(m.id_mensualidad) as ids,
                    GROUP_CONCAT(m.apartamento_id) as ids_apartamentos,
                    SUM(m.monto) as monto,
                    pm.tasa_dolar,
                    pm.mes,
                    pm.anio,
                    SUM(LEAST(m.monto, COALESCE(pagos.total_pagado, 0))) as pagado,
                    m.porcentaje_interes,
                    m.limite_mensualidad
                FROM mensualidad m
                INNER JOIN periodos_mensualidad pm ON m.periodo_id = pm.id_periodo
                LEFT JOIN (
                    SELECT p_m.mensualidad_id, SUM(dp.monto) as total_pagado
                    FROM detalles_pagos dp
                    JOIN pagos_mensualidad p_m ON dp.id_detalle_pago = p_m.detalle_pago_id
                    GROUP BY p_m.mensualidad_id
                ) as pagos ON m.id_mensualidad = pagos.mensualidad_id
                WHERE m.activo = 1 AND pm.activo = 1
                GROUP BY pm.id_periodo, pm.mes, pm.anio, pm.tasa_dolar, m.porcentaje_interes, m.limite_mensualidad";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultarPorMeses: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar por meses'];
        }
    }

    // SE USA EN EL MODULO
    private function _consultar_mensualidad_apartamentos()
    {
        $mesInt = (int)$this->mes;
        $anioInt = (int)$this->anio;

        $sql = "SELECT m.id_mensualidad, m.apartamento_id, pm.mes, pm.anio,
                       a.nro_apartamento,
                       h.nombre, h.apellido,
                       m.monto, pm.tasa_dolar,
                       COALESCE((SELECT SUM(dp.monto) FROM detalles_pagos dp
                                 INNER JOIN pagos_mensualidad p_m ON dp.id_detalle_pago = p_m.detalle_pago_id
                                 WHERE p_m.mensualidad_id = m.id_mensualidad),0) as pagado,
                       COALESCE((SELECT SUM(dp.monto_dolar) FROM detalles_pagos dp
                                 INNER JOIN pagos_mensualidad p_m ON dp.id_detalle_pago = p_m.detalle_pago_id
                                 WHERE p_m.mensualidad_id = m.id_mensualidad),0) as pagado_dolar
                FROM mensualidad m
                INNER JOIN periodos_mensualidad pm ON m.periodo_id = pm.id_periodo
                INNER JOIN apartamentos a ON m.apartamento_id = a.id_apartamento
                INNER JOIN habitantes_apartamentos ha ON ha.apartamento_id = a.id_apartamento
                INNER JOIN habitantes h ON ha.habitante_id = h.id_habitante
                WHERE pm.mes = :mes AND pm.anio = :anio
                  AND ha.tipo_vinculo = 'Propietario'
                  AND m.activo = 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':mes', $mesInt, PDO::PARAM_INT);
            $stmt->bindParam(':anio', $anioInt, PDO::PARAM_INT);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_mensualidad_apartamentos: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar mensualidad por apartamentos'];
        }
    }

    // SE USA EN EL MODULO
    private function _consultar_presupuestos_asociados()
    {
        if (empty($this->ids_mensualidades)) {
            return ['estatus' => false, 'mensaje' => 'No se proporcionaron IDs de mensualidad.'];
        }

        $ids = explode(',', $this->ids_mensualidades);
        $resultados = [];

        try {
            foreach ($ids as $id) {
                $sql = "SELECT detalle_presupuesto_id 
                        FROM presupuesto_mensualidad 
                        WHERE mensualidad_id = :id";
                $stmt = $this->get_conex('negocio')->prepare($sql);
                $stmt->execute([':id' => $id]);
                $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $resultados[] = array_column($filas, 'detalle_presupuesto_id');
            }
            return ['estatus' => true, 'datos' => $resultados];
        } catch (PDOException $e) {
            error_log("Error en _consultar_presupuestos_asociados: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar presupuestos asociados.'];
        }
    }

    // SE USA EN EL MODULO
    private function _consultar_cabecera_mensualidad()
    {
        $sql = "SELECT pm.tasa_dolar, pm.mes, pm.anio, m.porcentaje_interes, m.limite_mensualidad 
                FROM mensualidad m
                INNER JOIN periodos_mensualidad pm ON m.periodo_id = pm.id_periodo
                WHERE pm.mes = :mes AND pm.anio = :anio AND m.activo = 1 
                LIMIT 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':mes', $this->mes, PDO::PARAM_INT);
            $stmt->bindParam(':anio', $this->anio, PDO::PARAM_INT);
            $stmt->execute();
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$datos) {
                return ['estatus' => false, 'mensaje' => 'Mensualidad no encontrada para este mes y año'];
            }
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_cabecera_mensualidad: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar cabecera de la mensualidad'];
        }
    }

    // SE USA EN EL MODULO
    private function _registrar()
    {
        $id_mensualidad = null;
        $con = $this->get_conex('negocio');
        
        try {
            $con->beginTransaction();

            $sqlBuscaPeriodo = "SELECT id_periodo FROM periodos_mensualidad WHERE mes = :mes AND anio = :anio LIMIT 1";
            $stmtBusca = $con->prepare($sqlBuscaPeriodo);
            $stmtBusca->execute([':mes' => $this->mes, ':anio' => $this->anio]);
            $periodo = $stmtBusca->fetch(PDO::FETCH_ASSOC);

            if ($periodo) {
                $id_periodo_actual = $periodo['id_periodo'];
            } else {
                $sqlInsertaPeriodo = "INSERT INTO periodos_mensualidad (mes, anio, tasa_dolar, activo) VALUES (:mes, :anio, :tasa_dolar, 1)";
                $stmtInsertaPer = $con->prepare($sqlInsertaPeriodo);
                $stmtInsertaPer->execute([
                    ':mes' => $this->mes, 
                    ':anio' => $this->anio, 
                    ':tasa_dolar' => $this->tasa_dolar
                ]);
                $id_periodo_actual = $con->lastInsertId();
            }

            $sqlM = "INSERT INTO mensualidad (monto, periodo_id, apartamento_id, porcentaje_interes, limite_mensualidad)
                     VALUES (:monto, :periodo_id, :apartamento_id, :porcentaje_interes, :limite_mensualidad)";
            $stmtM = $con->prepare($sqlM);

            $sqlP = "INSERT INTO presupuesto_mensualidad (detalle_presupuesto_id, mensualidad_id) VALUES (:det_id, :men_id)";
            $stmtP = $con->prepare($sqlP);

            foreach ($this->datos_apartamentos as $item) {
                $stmtM->execute([
                    ':monto' => $item['monto'],
                    ':periodo_id' => $id_periodo_actual, 
                    ':apartamento_id' => $item['id_apartamento'],
                    ':porcentaje_interes' => $this->porcentaje_interes,
                    ':limite_mensualidad' => $this->limite_mensualidad
                ]);
                $id_mensualidad = $con->lastInsertId();

                foreach ($item['id_presupuestos'] as $id_detalle) {
                    $stmtP->execute([':det_id' => $id_detalle, ':men_id' => $id_mensualidad]);
                }
            }

            $con->commit();

            return ['estatus' => true, 'mensaje' => 'Todas las mensualidades se registraron correctamente.','lastId' => $id_mensualidad];
        } catch (Exception $e) {
            $con->rollBack();
            error_log("Error en _registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar: ' . $e->getMessage()];
        }
    }

    // SE USA EN EL MODULO
    private function _modificar()
    {
        $con = $this->get_conex('negocio');
        try {
            $con->beginTransaction();

            $sqlUpdatePeriodo = "UPDATE periodos_mensualidad SET tasa_dolar = :tasa_dolar WHERE mes = :mes AND anio = :anio";
            $stmtUpdPer = $con->prepare($sqlUpdatePeriodo);
            $stmtUpdPer->execute([
                ':tasa_dolar' => $this->tasa_dolar, 
                ':mes' => $this->mes, 
                ':anio' => $this->anio
            ]);

            $stmtBusca = $con->prepare("SELECT id_periodo FROM periodos_mensualidad WHERE mes = :mes AND anio = :anio");
            $stmtBusca->execute([':mes' => $this->mes, ':anio' => $this->anio]);
            $id_periodo_actual = $stmtBusca->fetchColumn();

            $sqlUpdate = "UPDATE mensualidad SET 
                            monto = :monto,
                            apartamento_id = :apartamento_id,
                            porcentaje_interes = :porcentaje_interes,
                            limite_mensualidad = :limite_mensualidad
                          WHERE id_mensualidad = :id_mensualidad";
            $stmtUpdate = $con->prepare($sqlUpdate);

            $sqlInsert = "INSERT INTO mensualidad (monto, periodo_id, apartamento_id, porcentaje_interes, limite_mensualidad)
                          VALUES (:monto, :periodo_id, :apartamento_id, :porcentaje_interes, :limite_mensualidad)";
            $stmtInsert = $con->prepare($sqlInsert);

            $sqlDeletePuente = "DELETE FROM presupuesto_mensualidad WHERE mensualidad_id = :men_id";
            $stmtDelete = $con->prepare($sqlDeletePuente);

            $sqlInsertPuente = "INSERT INTO presupuesto_mensualidad (detalle_presupuesto_id, mensualidad_id) VALUES (:det_id, :men_id)";
            $stmtInsertPuente = $con->prepare($sqlInsertPuente);

            foreach ($this->datos_apartamentos as $item) {
                if (!empty($item['id_mensualidad'])) {
                    $stmtUpdate->execute([
                        ':id_mensualidad' => $item['id_mensualidad'],
                        ':monto' => $item['monto'],
                        ':apartamento_id' => $item['id_apartamento'],
                        ':porcentaje_interes' => $this->porcentaje_interes,
                        ':limite_mensualidad' => $this->limite_mensualidad
                    ]);
                    $id_mensualidad = $item['id_mensualidad'];
                } else {
                    $stmtInsert->execute([
                        ':monto' => $item['monto'],
                        ':periodo_id' => $id_periodo_actual,
                        ':apartamento_id' => $item['id_apartamento'],
                        ':porcentaje_interes' => $this->porcentaje_interes,
                        ':limite_mensualidad' => $this->limite_mensualidad
                    ]);
                    $id_mensualidad = $con->lastInsertId();
                }

                $stmtDelete->execute([':men_id' => $id_mensualidad]);

                if (isset($item['id_presupuestos']) && is_array($item['id_presupuestos'])) {
                    foreach ($item['id_presupuestos'] as $id_detalle) {
                        $stmtInsertPuente->execute([':det_id' => $id_detalle, ':men_id' => $id_mensualidad]);
                    }
                }
            }

            $con->commit();
            return ['estatus' => true, 'mensaje' => 'Mensualidades actualizadas correctamente.'];
        } catch (Exception $e) {
            $con->rollBack();
            error_log("Error en _modificar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al modificar: ' . $e->getMessage()];
        }
    }

    // SE USA EN EL MODULO
    private function _eliminar()
    {
        $sql = "UPDATE periodos_mensualidad pm 
                SET pm.activo = 0 
                WHERE pm.mes = :mes AND pm.anio = :anio";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':mes', $this->mes, PDO::PARAM_INT);
            $stmt->bindParam(':anio', $this->anio, PDO::PARAM_INT);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Mensualidades eliminadas'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar mensualidades'];
        }
    }

    // SE USA EN EL MODULO
    private function _consultar_meses_mensualidad()
    {
        $sql = "SELECT pm.mes, pm.anio 
                FROM periodos_mensualidad pm
                INNER JOIN mensualidad m ON m.periodo_id = pm.id_periodo
                WHERE m.activo = 1
                GROUP BY pm.anio, pm.mes 
                ORDER BY pm.anio DESC, pm.mes DESC";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_meses_mensualidad: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar meses'];
        }
    }

    // SE USA EN REPORTES
    private function _consultar_tasa_dolar_mensualidades()
    {
        $sql = "SELECT MAX(mes) as mes, MAX(anio) as anio, MAX(tasa_dolar) as tasa_dolar
                FROM periodos_mensualidad 
                WHERE anio = :anio AND mes = TRIM(LEADING '0' FROM :mes)";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':mes', $this->mes);
            $stmt->bindParam(':anio', $this->anio);
            $stmt->execute();
            $dato = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $dato];
        } catch (PDOException $e) {
            error_log("Error en _consultar_tasa_dolar_mensualidades: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar tasa de dólar'];
        }
    }

    // SE USA EN EL INICIO
    private function _consultar_estadisticas_inicio()
    {
        try {
            $pdo = $this->get_conex('negocio');
            $sql = "
                SELECT 
                    SUM(CASE WHEN deuda_pendiente > 0 THEN 1 ELSE 0 END) as aptos_morosos,
                    SUM(CASE WHEN deuda_pendiente = 0 OR deuda_pendiente IS NULL THEN 1 ELSE 0 END) as aptos_solventes
                FROM (
                    SELECT a.id_apartamento, COALESCE(SUM(v.deuda_pendiente), 0) as deuda_pendiente
                    FROM apartamentos a
                    LEFT JOIN vw_estado_cuentas_mensualidad v 
                        ON a.nro_apartamento = v.nro_apartamento 
                        AND CAST(v.estado_pago AS CHAR) = 'Pendiente'
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
        } catch (PDOException $e) {
            error_log("Error en _consultar_estadisticas_inicio: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar las estadísticas'];
        }
    }

    // SE USA EN EL INICIO
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
                     WHERE estado_pago = 'Pendiente') AS recibos_pendientes,
                    (SELECT COALESCE(SUM(deuda_pendiente), 0) 
                     FROM vw_estado_cuentas_mensualidad 
                     WHERE CAST(estado_pago AS CHAR) = 'Pendiente') AS deuda_total";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_kpis_inicio: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar los KPIs del inicio'];
        }
    }

    /**
     * Consulta los indicadores clave (KPIs) para el resumen financiero de la App.
     */
    private function _consultar_kpis()
    {
        $sql = "SELECT 
                    (SELECT COALESCE(SUM(deuda_pendiente), 0) 
                     FROM vw_estado_cuentas_mensualidad 
                     WHERE CAST(estado_pago AS CHAR) = 'Pendiente') AS deuda_total,
                     
                    (SELECT COALESCE(SUM(dp.monto), 0) 
                     FROM detalles_pagos dp 
                     JOIN pagos p ON dp.pago_id = p.id_pago 
                     WHERE p.activo = 1 
                       AND MONTH(dp.fecha) = MONTH(CURDATE()) 
                       AND YEAR(dp.fecha) = YEAR(CURDATE())) AS recaudado_mes";
                       
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_kpis: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al calcular indicadores financieros'];
        }
    }

    private function _consultar_desglose()
    {
        if (empty($this->id_mensualidad)) {
            return ['estatus' => false, 'mensaje' => 'ID de mensualidad no proporcionado.'];
        }
        $sql = "SELECT 
                    dp.id_detalle_presupuesto, 
                    dp.nombre_detalle AS concepto, 
                    dp.monto, 
                    dp.monto_dolar 
                FROM presupuesto_mensualidad pm
                INNER JOIN detalles_presupuesto dp ON pm.detalle_presupuesto_id = dp.id_detalle_presupuesto
                WHERE pm.mensualidad_id = :id_mensualidad";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':id_mensualidad' => $this->id_mensualidad]);
            $datos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return ['estatus' => true, 'datos' => $datos];
        } catch (\PDOException $e) {
            error_log("Error en _consultar_desglose: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar el desglose de la mensualidad.'];
        }
    }

}
?>