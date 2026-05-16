<?php
namespace haydee\servicios;

use PDO;
use PDOException;
use haydee\modelo\Conexion;
use haydee\enums\TipoVinculo;
use haydee\enums\MetodoPago;
use haydee\enums\ClasificacionGasto;
use haydee\enums\TipoBalance;
use haydee\enums\FiltroTiempo;
use haydee\enums\TipoBaseDatos;

class Reportes extends Conexion
{
    
    // PROPIEDADES (Filtros de Reportes)
    private $balance;
    private $metodo_pago;
    private $tipo_gasto;
    private $fecha_inicio;
    private $fecha_fin;
    
    private $mes;
    private $anio;
    private $anio_limite; // Para cuadro de pagos
    private $mes_limite;  // Para cuadro de pagos

    private $id_habitante;
    private $rango_edades;
    private $edad_minima;
    private $edad_maxima;
    private $tipo_residente;
    private $servicios; // Array o JSON
    private $filtro_tiempo; // Para habitantes (mes, trimestre, año, personalizado)
    private $id_pago; // Para recibo de pago


    // VALIDACIONES CENTRALIZADAS
    public static function obtenerReglas($operacion) {
        $balances = implode('|', array_column(TipoBalance::cases(), 'value')) . '|todos';
        $metodos = implode('|', array_column(MetodoPago::cases(), 'value')) . '|todos';
        $tiempos = implode('|', array_column(FiltroTiempo::cases(), 'value'));
        $vinculos = "propietarios|habitantes|todos";

        $reglasGenerales = [
            'balance' => ['regex' => "/^($balances)$/i", 'opcional' => true],
            'metodo_pago' => ['regex' => "/^($metodos)$/i", 'opcional' => true],
            'tipo_gasto' => ['regex' => '/^\\d+|todos$/', 'opcional' => true],
            'fecha_inicio' => ['regex' => '/^\\d{4}-\\d{2}-\\d{2}$/', 'opcional' => true],
            'fecha_fin' => ['regex' => '/^\\d{4}-\\d{2}-\\d{2}$/', 'opcional' => true],
            'mes' => ['regex' => '/^(0?[1-9]|1[0-2])$/', 'opcional' => true],
            'anio' => ['regex' => '/^\\d{4}$/', 'opcional' => true],
            'tipo_residente' => ['regex' => "/^($vinculos)$/i", 'opcional' => true],
            'filtro_tiempo' => ['regex' => "/^($tiempos)$/i", 'opcional' => true],         
            'mes_limite' => ['regex' => '/^(0?[1-9]|1[0-2])$/', 'opcional' => true],
            'anio_limite' => ['regex' => '/^\d{4}$/', 'opcional' => true],
            'id_habitante' => ['regex' => '/^\d+$/', 'opcional' => true],
            'id_pago' => ['regex' => '/^\d+$/', 'opcional' => true],
            'rango_edades' => ['regex' => '/^(todos|jovenes|adultos|mayores|personalizado)$/i', 'opcional' => true],
        ];

        // Mapeo de reglas por operación
        $camposPorOperacion = [
            'reporte_ingresos_egresos_completo' => ['balance', 'metodo_pago', 'tipo_gasto', 'fecha_inicio', 'fecha_fin'],
            'obtener_datos_reporte_mensual' => ['mes', 'anio'],
            'consultar_habitante' => ['id_habitante'],
            'cuadro_pagos' => ['mes_limite', 'anio_limite'],
            'consultar_habitantes' => ['rango_edades', 'tipo_residente', 'filtro_tiempo', 'fecha_inicio', 'fecha_fin'],
            'recibo_pago' => ['id_pago']
        ];
        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    // ====================================================================
    // GETTERS Y SETTERS
    // ====================================================================
    public function set_balance($val) { $this->balance = $val; }
    public function set_metodo_pago($val) { $this->metodo_pago = $val; }
    public function set_tipo_gasto($val) { $this->tipo_gasto = $val; }
    public function set_fecha_inicio($val) { $this->fecha_inicio = $val; }
    public function set_fecha_fin($val) { $this->fecha_fin = $val; }
    
    public function set_mes($val) { $this->mes = $val; }
    public function set_anio($val) { $this->anio = $val; }
    public function set_mes_limite($val) { $this->mes_limite = $val; }
    public function set_anio_limite($val) { $this->anio_limite = $val; }
    
    public function set_id_habitante($val) { $this->id_habitante = $val; }
    public function set_rango_edades($val) { $this->rango_edades = $val; }
    public function set_edad_minima($val) { $this->edad_minima = $val; }
    public function set_edad_maxima($val) { $this->edad_maxima = $val; }
    public function set_tipo_residente($val) { $this->tipo_residente = $val; }
    public function set_servicios($val) { $this->servicios = $val; }
    public function set_filtro_tiempo($val) { $this->filtro_tiempo = $val; }
    public function set_id_pago($val) { $this->id_pago = $val; }

    // ====================================================================
    // ENRUTADOR CON MANEJO DE EXCEPCIONES
    // ====================================================================
    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            return ['estatus' => false, 'mensaje' => "El reporte '$accion' no está implementado."];
        }

        try {
            return $this->$metodo();
        } catch (\Exception $e) {
            error_log("Error en Gestor de Reportes ($accion): " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Ocurrió un error interno al generar el reporte.'];
        }
    }

    // ====================================================================
    // MÉTODOS MIGRADOS DESDE GASTOS
    // ====================================================================
    
    private function _listar_meses_con_gastos()
    {
        $sql = "SELECT DISTINCT YEAR(dg.fecha) as anio, MONTH(dg.fecha) as mes 
                FROM detalles_gastos dg
                INNER JOIN gastos g ON dg.gasto_id = g.id_gasto
                WHERE g.activo = 1 ORDER BY anio DESC, mes DESC";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute();
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            return ['estatus' => false, 'mensaje' => 'Error al listar meses con gastos'];
        }
    }

    private function _obtener_datos_reporte_mensual()
    {
        $sql = "SELECT g.clasificacion, dg.descripcion_detalle_gasto as concepto, dg.monto
                FROM detalles_gastos dg
                INNER JOIN gastos g ON dg.gasto_id = g.id_gasto
                WHERE g.activo = 1 AND YEAR(dg.fecha) = :anio AND MONTH(dg.fecha) = :mes";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([':anio' => $this->anio, ':mes' => $this->mes]);
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            return ['estatus' => false, 'mensaje' => 'Error al obtener datos del reporte mensual'];
        }
    }

    private function _reporte_ingresos_egresos_completo()
    {
        // (El código de este método es el mismo que establecimos en el paso anterior, lo mantienes igual)
        $balance = $this->balance ?? 'todos';
        $metodo_pago = strtolower($this->metodo_pago ?? 'todos');
        $tipo_gasto = strtolower($this->tipo_gasto ?? 'todos');
        $fecha_inicio = $this->fecha_inicio ?? '';
        $fecha_fin = $this->fecha_fin ?? '';

        $condicionesEgresos = ["g.activo = 1"];
        $condicionesIngresos = ["p.activo = 1"];
        $paramsEgresos = [];
        $paramsIngresos = [];

        if (!empty($fecha_inicio) && !empty($fecha_fin)) {
            $condicionesEgresos[] = "dg.fecha BETWEEN :fecha_ini_egreso AND :fecha_fin_egreso";
            $condicionesIngresos[] = "dp.fecha BETWEEN :fecha_ini_ingreso AND :fecha_fin_ingreso";
            $paramsEgresos[':fecha_ini_egreso'] = $fecha_inicio;
            $paramsEgresos[':fecha_fin_egreso'] = $fecha_fin;
            $paramsIngresos[':fecha_ini_ingreso'] = $fecha_inicio;
            $paramsIngresos[':fecha_fin_ingreso'] = $fecha_fin;
        }

        if ($metodo_pago !== 'todos') {
            $condicionesEgresos[] = "LOWER(dg.metodo_pago) = LOWER(:metodo_pago_egreso)";
            $condicionesIngresos[] = "LOWER(dp.tipo_pago) = LOWER(:metodo_pago_ingreso)";
            $paramsEgresos[':metodo_pago_egreso'] = $metodo_pago;
            $paramsIngresos[':metodo_pago_ingreso'] = $metodo_pago;
        }

        if ($tipo_gasto !== 'todos' && $balance !== 'Ingresos') {
            $condicionesEgresos[] = "LOWER(g.clasificacion) = LOWER(:tipo_gasto)";
            $paramsEgresos[':tipo_gasto'] = $tipo_gasto;
        }

        $whereEgresos = !empty($condicionesEgresos) ? " WHERE " . implode(" AND ", $condicionesEgresos) : "";
        $whereIngresos = !empty($condicionesIngresos) ? " WHERE " . implode(" AND ", $condicionesIngresos) : "";

        $sqlEgresosDetalle = "SELECT 'Egreso' as balance, dg.fecha, dg.monto, dg.metodo_pago, g.descripcion_gasto as concepto, tg.nombre_tipo_gasto as tipo FROM detalles_gastos dg INNER JOIN gastos g ON dg.gasto_id = g.id_gasto LEFT JOIN tipo_gasto tg ON g.tipo_gasto_id = tg.id_tipo_gasto $whereEgresos";
        $sqlIngresosDetalle = "SELECT 'Ingreso' as balance, dp.fecha, dp.monto, dp.tipo_pago as metodo_pago, CONCAT('Pago mes ', pm_per.mes, '/', pm_per.anio, ' - Apto ', a.nro_apartamento) as concepto, NULL as tipo FROM detalles_pagos dp INNER JOIN pagos_mensualidad pm_rel ON dp.id_detalle_pago = pm_rel.detalle_pago_id INNER JOIN mensualidad m ON pm_rel.mensualidad_id = m.id_mensualidad INNER JOIN periodos_mensualidad pm_per ON m.periodo_id = pm_per.id_periodo INNER JOIN apartamentos a ON m.apartamento_id = a.id_apartamento INNER JOIN pagos p ON dp.pago_id = p.id_pago $whereIngresos";

        if ($balance === 'Ingresos') {
            $sqlGrafico = $sqlIngresosDetalle; $paramsGrafico = $paramsIngresos;
        } elseif ($balance === 'Egresos') {
            $sqlGrafico = $sqlEgresosDetalle; $paramsGrafico = $paramsEgresos;
        } else {
            $sqlGrafico = "($sqlEgresosDetalle) UNION ALL ($sqlIngresosDetalle) ORDER BY fecha DESC";
            $paramsGrafico = array_merge($paramsEgresos, $paramsIngresos);
        }

        try {
            $con = $this->get_conex(TipoBaseDatos::NEGOCIO);
            $stmt = $con->prepare($sqlGrafico);
            $stmt->execute($paramsGrafico);
            $datosGrafico = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['estatus' => false, 'mensaje' => 'Error al obtener datos del gráfico'];
        }

        $resultadosEstadisticas = [];

        if ($balance !== 'Ingresos') {
            $sqlEgresosTotal = "SELECT 'total_gastos' as indicador, SUM(dg.monto) as valor FROM detalles_gastos dg INNER JOIN gastos g ON dg.gasto_id = g.id_gasto LEFT JOIN tipo_gasto tg ON g.tipo_gasto_id = tg.id_tipo_gasto $whereEgresos";
            $sqlEgresosPorMetodo = "SELECT CONCAT('gastos_', LOWER(REPLACE(dg.metodo_pago, ' ', '_'))) as indicador, SUM(dg.monto) as valor FROM detalles_gastos dg INNER JOIN gastos g ON dg.gasto_id = g.id_gasto LEFT JOIN tipo_gasto tg ON g.tipo_gasto_id = tg.id_tipo_gasto $whereEgresos GROUP BY dg.metodo_pago";

            try {
                $stmt = $con->prepare($sqlEgresosTotal);
                $stmt->execute($paramsEgresos);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && $row['valor'] !== null) $resultadosEstadisticas[] = $row;

                $stmt = $con->prepare($sqlEgresosPorMetodo);
                $stmt->execute($paramsEgresos);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $resultadosEstadisticas[] = $row;
            } catch (PDOException $e) {
                return ['estatus' => false, 'mensaje' => 'Error al obtener estadísticas de egresos'];
            }
        }

        if ($balance !== 'Egresos') {
            $sqlIngresosTotal = "SELECT 'total_pagos' as indicador, SUM(dp.monto) as valor FROM detalles_pagos dp INNER JOIN pagos p ON dp.pago_id = p.id_pago $whereIngresos";
            $sqlIngresosPorMetodo = "SELECT CONCAT('pagos_', LOWER(REPLACE(dp.tipo_pago, ' ', '_'))) as indicador, SUM(dp.monto) as valor FROM detalles_pagos dp INNER JOIN pagos p ON dp.pago_id = p.id_pago $whereIngresos GROUP BY dp.tipo_pago";

            try {
                $stmt = $con->prepare($sqlIngresosTotal);
                $stmt->execute($paramsIngresos);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && $row['valor'] !== null) $resultadosEstadisticas[] = $row;

                $stmt = $con->prepare($sqlIngresosPorMetodo);
                $stmt->execute($paramsIngresos);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $resultadosEstadisticas[] = $row;
            } catch (PDOException $e) {
                return ['estatus' => false, 'mensaje' => 'Error al obtener estadísticas de ingresos'];
            }
        }

        return ['estatus' => true, 'datos' => ['grafico' => $datosGrafico, 'estadisticas' => $resultadosEstadisticas]];
    }

    // ====================================================================
    // MÉTODOS MIGRADOS DESDE HABITANTES
    // ====================================================================

    private function _consultar_personas_solvencia()
    {
        $sql = "SELECT h.*, a.nro_apartamento 
                FROM habitantes h
                INNER JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
                INNER JOIN apartamentos a ON ha.apartamento_id = a.id_apartamento
                WHERE a.id_apartamento IN (
                    SELECT apartamentos.id_apartamento 
                    FROM mensualidad 
                    INNER JOIN apartamentos ON mensualidad.apartamento_id = apartamentos.id_apartamento
                    WHERE (SELECT SUM(mensualidad.monto) 
                           FROM mensualidad 
                           WHERE mensualidad.apartamento_id = apartamentos.id_apartamento) 
                          <= (SELECT SUM(detalles_pagos.monto) 
                              FROM detalles_pagos 
                              INNER JOIN pagos_mensualidad ON pagos_mensualidad.detalle_pago_id = detalles_pagos.id_detalle_pago 
                              INNER JOIN mensualidad ON mensualidad.id_mensualidad = pagos_mensualidad.mensualidad_id 
                              WHERE mensualidad.apartamento_id = apartamentos.id_apartamento)
                )";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute();
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            return ['estatus' => false, 'mensaje' => 'Error al consultar personas solventes'];
        }
    }

    private function _consultar_propietarios()
    {
        $vinculo = TipoVinculo::PROPIETARIO->value; // 'Propietario'
        $sql = "SELECT h.*, a.nro_apartamento 
                FROM habitantes h
                INNER JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
                INNER JOIN apartamentos a ON ha.apartamento_id = a.id_apartamento
                WHERE ha.tipo_vinculo = :vinculo AND h.activo = 1";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([':vinculo' => $vinculo]);
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar_propietarios: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener propietarios'];
        }
    }

    private function _obtener_datos_habitantes()
    {
        $servicios_array = is_string($this->servicios) ? json_decode($this->servicios, true) : ($this->servicios ?? []);

        $sql = "SELECT h.sexo, ha.tipo_vinculo, TIMESTAMPDIFF(YEAR, h.fecha_nacimiento, CURDATE()) AS edad
                FROM habitantes h
                JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
                JOIN apartamentos a ON ha.apartamento_id = a.id_apartamento
                WHERE 1=1";

        $params = [];

        // Filtro Tiempo
        if ($this->filtro_tiempo !== 'todo') {
            switch ($this->filtro_tiempo) {
                case 'mes': $sql .= " AND MONTH(h.fecha_registro) = MONTH(CURDATE()) AND YEAR(h.fecha_registro) = YEAR(CURDATE())"; break;
                case 'trimestre': $sql .= " AND h.fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)"; break;
                case 'año': $sql .= " AND YEAR(h.fecha_registro) = YEAR(CURDATE())"; break;
                case 'personalizado':
                    if (!empty($this->fecha_inicio) && !empty($this->fecha_fin)) {
                        $sql .= " AND DATE(h.fecha_registro) BETWEEN :fecha_inicio AND :fecha_fin";
                        $params[':fecha_inicio'] = $this->fecha_inicio;
                        $params[':fecha_fin'] = $this->fecha_fin;
                    }
                    break;
            }
        }

        // Filtro Edades
        if ($this->rango_edades != 'todos') {
            switch ($this->rango_edades) {
                case 'jovenes': $sql .= " AND TIMESTAMPDIFF(YEAR, h.fecha_nacimiento, CURDATE()) BETWEEN 18 AND 35"; break;
                case 'adultos': $sql .= " AND TIMESTAMPDIFF(YEAR, h.fecha_nacimiento, CURDATE()) BETWEEN 36 AND 59"; break;
                case 'mayores': $sql .= " AND TIMESTAMPDIFF(YEAR, h.fecha_nacimiento, CURDATE()) >= 60"; break;
                case 'personalizado':
                    if ($this->edad_minima !== null && $this->edad_maxima !== null) {
                        $sql .= " AND TIMESTAMPDIFF(YEAR, h.fecha_nacimiento, CURDATE()) BETWEEN :edad_min AND :edad_max";
                        $params[':edad_min'] = $this->edad_minima;
                        $params[':edad_max'] = $this->edad_maxima;
                    }
                    break;
            }
        }

        // Filtro Residente
        if ($this->tipo_residente == 'propietarios') {
            $sql .= " AND ha.tipo_vinculo = '" . TipoVinculo::PROPIETARIO->value . "'";
        } elseif ($this->tipo_residente == 'arrendatarios') {
            $sql .= " AND ha.tipo_vinculo = '" . TipoVinculo::HABITANTE->value . "'";
        }

        // Filtro Servicios
        if (is_array($servicios_array)) {
            if (in_array('agua', $servicios_array)) $sql .= " AND a.agua = 1";
            if (in_array('gas', $servicios_array)) $sql .= " AND a.gas = 1";
        }

        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute($params);
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            return ['estatus' => false, 'mensaje' => 'Error al obtener datos de habitantes'];
        }
    }

    // ====================================================================
    // MÉTODOS MIGRADOS DESDE PAGOS
    // ====================================================================

    private function _consultar_recibo_pago()
    {
        $trans = MetodoPago::TRANSFERENCIA->value;
        $pmov = MetodoPago::PAGO_MOVIL->value;
        $efec = MetodoPago::EFECTIVO->value;
        $prop = TipoVinculo::PROPIETARIO->value;

        $sql = "SELECT 
                    h.nombre, h.apellido, a.nro_apartamento,
                    MAX(dp.fecha) as fecha_pago, pm_per.mes, pm_per.anio, p.id_pago,
                    SUM(dp.monto) as total,
                    COUNT(CASE WHEN dp.tipo_pago = '$trans' THEN 1 END) as count_transferencia,
                    COUNT(CASE WHEN dp.tipo_pago = '$pmov' THEN 1 END) as count_pago_movil,
                    COUNT(CASE WHEN dp.tipo_pago = '$efec' THEN 1 END) as count_efectivo,
                    GROUP_CONCAT(DISTINCT b.nombre_banco SEPARATOR ', ') as bancos,
                    GROUP_CONCAT(DISTINCT ib.referencia SEPARATOR ', ') as referencias
                FROM pagos p
                // ... (JOINs)
                WHERE p.id_pago = :id_pago AND ha.tipo_vinculo = :vinculo
                GROUP BY p.id_pago, pm_per.mes, pm_per.anio, h.nombre, h.apellido, a.nro_apartamento";
        
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([':id_pago' => $this->id_pago, ':vinculo' => $prop]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$datos) {
                return ['estatus' => false, 'mensaje' => 'No se encontraron datos para el recibo'];
            }
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            return ['estatus' => false, 'mensaje' => 'Error al consultar recibo'];
        }
    }

    // ====================================================================
    // MÉTODOS MIGRADOS DESDE MENSUALIDAD (Cuadro de Pagos)
    // ====================================================================

    // Helper interno para el cuadro de pagos (antes en Mensualidad.php)
    private function consultarMensualidadesPendientes()
    {
        $sql = "WITH FacturacionMensual AS (
                    SELECT m.apartamento_id, pm.anio, pm.mes, SUM(m.monto) AS total_facturado
                    FROM mensualidad m JOIN periodos_mensualidad pm ON m.periodo_id = pm.id_periodo
                    WHERE m.activo = 1 GROUP BY m.apartamento_id, pm.anio, pm.mes
                ),
                PagosMensuales AS (
                    SELECT m.apartamento_id, pm.anio, pm.mes, SUM(dp.monto) AS total_pagado
                    FROM detalles_pagos dp JOIN pagos_mensualidad p_m ON dp.id_detalle_pago = p_m.detalle_pago_id
                    JOIN mensualidad m ON p_m.mensualidad_id = m.id_mensualidad JOIN periodos_mensualidad pm ON m.periodo_id = pm.id_periodo
                    GROUP BY m.apartamento_id, pm.anio, pm.mes
                ),
                BalanceDelMes AS (
                    SELECT f.apartamento_id, f.anio, f.mes, (COALESCE(f.total_facturado, 0) - COALESCE(p.total_pagado, 0)) AS cambio_neto_mes
                    FROM FacturacionMensual f LEFT JOIN PagosMensuales p ON f.apartamento_id = p.apartamento_id AND f.anio = p.anio AND f.mes = p.mes
                    UNION
                    SELECT p.apartamento_id, p.anio, p.mes, (COALESCE(f.total_facturado, 0) - COALESCE(p.total_pagado, 0)) AS cambio_neto_mes
                    FROM FacturacionMensual f RIGHT JOIN PagosMensuales p ON f.apartamento_id = p.apartamento_id AND f.anio = p.anio AND f.mes = p.mes
                    WHERE f.apartamento_id IS NULL
                )
                SELECT a.nro_apartamento, b.anio, b.mes, b.cambio_neto_mes, SUM(b.cambio_neto_mes) OVER (PARTITION BY b.apartamento_id ORDER BY b.anio, b.mes) AS deuda_acumulada
                FROM BalanceDelMes b JOIN apartamentos a ON b.apartamento_id = a.id_apartamento
                ORDER BY a.nro_apartamento, b.anio, b.mes";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute();
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            return ['estatus' => false, 'mensaje' => 'Error al consultar pendientes'];
        }
    }

    private function _cuadro_pagos()
    {
        $mes_limite = $this->mes_limite;
        $anio_limite = $this->anio_limite;

        if (!$mes_limite || !$anio_limite) {
            return ['estatus' => false, 'mensaje' => 'Parámetros límite inválidos.'];
        }

        $resp_deudas = $this->consultarMensualidadesPendientes();
        if (!$resp_deudas['estatus']) return $resp_deudas;
        
        $deudas_globales = $resp_deudas['datos'];
        $meses_nombres = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];

        $periodos = [];
        foreach ($deudas_globales as $deuda) {
            $periodo = $deuda['anio'] . '-' . str_pad($deuda['mes'], 2, '0', STR_PAD_LEFT);
            if ($deuda['anio'] < $anio_limite || ($deuda['anio'] == $anio_limite && $deuda['mes'] <= $mes_limite)) {
                $periodos[$periodo] = ['anio' => $deuda['anio'], 'mes' => $deuda['mes']];
            }
        }

        uasort($periodos, function($a, $b) {
            if ($a['anio'] == $b['anio']) return $a['mes'] - $b['mes'];
            return $a['anio'] - $b['anio'];
        });

        $cabecera_tabla = [];
        foreach ($periodos as $per) {
            $cabecera_tabla[] = $meses_nombres[$per['mes'] - 1] . ' ' . $per['anio'];
        }

        $cuerpo_tabla = [];
        $total_mensual = array_fill(0, count($periodos), 0);
        $periodos_indexados = array_values($periodos);

        foreach ($deudas_globales as $deuda) {
            $anio = $deuda['anio'];
            $mes = $deuda['mes'];
            $apto = $deuda['nro_apartamento'];
            $deuda_acum = $deuda['deuda_acumulada'];

            if ($anio < $anio_limite || ($anio == $anio_limite && $mes <= $mes_limite)) {
                if (!isset($cuerpo_tabla[$apto])) $cuerpo_tabla[$apto] = array_fill(0, count($periodos_indexados), 0);
                
                foreach ($periodos_indexados as $idx => $per) {
                    if ($per['anio'] == $anio && $per['mes'] == $mes) {
                        $cuerpo_tabla[$apto][$idx] = $deuda_acum;
                        $total_mensual[$idx] += $deuda_acum; 
                        break;
                    }
                }
            }
        }

        return [
            'estatus' => true,
            'datos' => [
                'cabecera' => $cabecera_tabla,
                'cuerpo' => $cuerpo_tabla,
                'totales' => $total_mensual
            ]
        ];
    }

    /**
     * Obtiene y procesa los datos para el reporte mensual de gastos,
     * clasificándolos en Fijos, Variables y Gas.
     *
     */
    private function _generar_data_reporte_gastos_mensual()
    {
        $respuestaRaw = $this->_obtener_datos_reporte_mensual();
        if (!$respuestaRaw['estatus']) return $respuestaRaw;

        $detalles = $respuestaRaw['datos'];
        $gastos_fijos = [];
        $gastos_variables = [];
        $total_gas_bs = 0;

        foreach ($detalles as $row) {
            $monto = (float)($row['monto'] ?? 0);
            
            // Usamos el Enum para la clasificación
            if (($row['clasificacion'] ?? '') === ClasificacionGasto::FIJO->value) {
                $gastos_fijos[] = ['descripcion_gasto' => $row['concepto'], 'monto' => $monto];
            } else {
                $gastos_variables[] = ['descripcion_gasto' => $row['concepto'], 'monto' => $monto];
            }
        }

        return [
            'estatus' => true,
            'datos' => [
                'gastos_fijos' => $gastos_fijos,
                'gastos_variables' => $gastos_variables,
                'gasto_gas' => ['monto' => $total_gas_bs]
            ]
        ];
    }
}
