<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\enums\TipoBaseDatos;
use haydee\enums\EstadoPago;

class Presupuesto extends Conexion
{
    private const ANIO_MINIMO_PERMITIDO = 2000;
    private const ANIO_MAXIMO_PERMITIDO = 2100;

    private const INTERES_MORA_DEFECTO = 10;
    private const LIMITE_DIAS_MENSUALIDAD = 15;
    private const ESTADO_ACTIVO = 1;

    // Tabla: presupuesto (Cabecera)
    private $id_presupuesto;
    private $fecha;
    private $cuota_reserva;
    private $observacion;
    private $activo;

    // Tabla: detalles_presupuesto (Renglones)
    private $id_detalle_presupuesto;
    private $monto_detalle;
    private $nombre_detalle;
    private $tipo_gasto_id;

    // Tabla: presupuesto_mensualidad (Relación N:M)
    private $mensualidad_id;

    // Propiedad auxiliar para pasar lista de detalles (usada en _registrar_completo y _sincronizar_mensualidad)
    private $detalles_temp = [];        // Array de arrays con claves 'monto', 'nombre', 'tipo_gasto_id'
    private $ids_detalles_string = '';  // String de IDs separados por comas para sincronización
    private $tasa_dolar;

    // ====================================================================
    // VALIDACIONES CENTRALIZADAS
    // ====================================================================
    
    /**
     * Reglas para la Cabecera del Presupuesto
     */
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

        // Estandarización de nombres (registrar_presupuesto, etc.)
        $camposPorOperacion = [
            'registrar_presupuesto' => ['fecha', 'cuota_reserva', 'observacion', 'tasa_dolar'],
            'modificar_presupuesto' => ['id_presupuesto', 'fecha', 'cuota_reserva', 'observacion', 'tasa_dolar'],
            'eliminar_presupuesto'  => ['id_presupuesto'],
            'consultar_presupuesto'   => ['id_presupuesto']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    /**
     * Reglas para cada fila (Renglón) del Presupuesto
     * Basado en las claves de tu arreglo detalles_temp (monto, nombre, tipo_gasto_id)
     */
    public static function obtenerReglasDetalles() {
        return [
            'nombre' => [
                'regex' => '/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s.,:\/-]{3,100}$/'
            ],
            'monto' => [
                'regex' => '/^\d+(\.\d{1,2})?$/',
                'min' => 0.01
            ],
            'tipo_gasto_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'tipo_gasto', 'campo' => 'id_tipo_gasto']
            ]
        ];
    }

    // ====================================================================
    // GETTERS Y SETTERS
    // ====================================================================
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
    public function set_nombre_detalle($n) { $this->nombre_detalle = $n; }
    public function get_nombre_detalle() { return $this->nombre_detalle; }
    public function set_tipo_gasto_id($t) { $this->tipo_gasto_id = $t; }
    public function get_tipo_gasto_id() { return $this->tipo_gasto_id; }

    public function set_mensualidad_id($m) { $this->mensualidad_id = $m; }
    public function get_mensualidad_id() { return $this->mensualidad_id; }

    // Setters para propiedades auxiliares
    public function setDetallesTemp($detalles) { $this->detalles_temp = $detalles; }
    public function setIdsDetallesString($ids) { $this->ids_detalles_string = $ids; }
    public function set_tasa_dolar($t) { $this->tasa_dolar = $t; }
    public function get_tasa_dolar() { return $this->tasa_dolar; }

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
    // LÓGICA DE NEGOCIO
    // ====================================================================

    /**
     * Registro completo de presupuesto (cabecera + detalles) y generación de mensualidades.
     * Requiere que estén seteadas: fecha, cuota_reserva, observacion, detalles_temp, tasa_dolar.
     // SE USA EN EL MODULO
     */
    private function _registrar_presupuesto()
    {
        if (empty($this->detalles_temp) || !is_array($this->detalles_temp)) {
            return ['estatus' => false, 'mensaje' => 'No hay detalles para el presupuesto'];
        }

        try {
            $con = $this->get_conex(TipoBaseDatos::NEGOCIO);
            $con->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $con->beginTransaction();

            // Insertar Cabecera
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

            // Insertar Detalles
            $total_monto = floatval($this->cuota_reserva);
            $ids_detalles = [];
            $sqlDet = "INSERT INTO detalles_presupuesto (monto, nombre_detalle, presupuesto_id, tipo_gasto_id) 
                       VALUES (:monto, :nombre, :id_pre, :id_tipo)";
            $stmtD = $con->prepare($sqlDet);

            foreach ($this->detalles_temp as $det) {
                $stmtD->execute([
                    ':monto'   => $det['monto'],
                    ':nombre'  => $det['nombre'],
                    ':id_pre'  => $id_presupuesto,
                    ':id_tipo' => $det['tipo_gasto_id']
                ]);
                $ids_detalles[] = $con->lastInsertId();
                $total_monto += floatval($det['monto']);
            }

            // Gestionar Periodo
            list($anio, $mes) = explode('-', $this->fecha);
            $stmtBuscaPer = $con->prepare("SELECT id_periodo FROM periodos_mensualidad WHERE mes = :mes AND anio = :anio LIMIT 1");
            $stmtBuscaPer->execute([':mes' => $mes, ':anio' => $anio]);
            $periodo_id = $stmtBuscaPer->fetchColumn();

            if (!$periodo_id) {
                $stmtInsertaPer = $con->prepare("INSERT INTO periodos_mensualidad (mes, anio, tasa_dolar, activo) VALUES (:mes, :anio, :tasa, 1)");
                $stmtInsertaPer->execute([':mes' => $mes, ':anio' => $anio, ':tasa' => $this->tasa_dolar]);
                $periodo_id = $con->lastInsertId();
            }

            // Sincronizar y generar cascada
            $this->_sincronizar_mensualidades($con, $periodo_id, $total_monto, $ids_detalles);

            $con->commit();
            return ['estatus' => true, 'mensaje' => 'Presupuesto y mensualidades registrados con éxito', 'id' => $id_presupuesto];
        } catch (\Exception $e) {
            if (isset($con) && $con->inTransaction()) {
                $con->rollBack();
            }
            error_log("Error en _registrar_presupuesto: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al procesar el presupuesto.'];
        }
    }

    /**
     * Edición de presupuesto: actualiza cabecera y reemplaza detalles.
     * No regenera mensualidades.
     * SE USA EN EL MODULO
     */
    private function _modificar_presupuesto()
    {
        $con = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $con->beginTransaction();

            if ($this->_tiene_pagos_registrados($con, $this->id_presupuesto)) {
                throw new \Exception("No se puede modificar este presupuesto porque ya existen residentes que han registrado pagos para este periodo.");
            }

            // Actualizar cabecera
            $sqlHead = "UPDATE presupuesto SET fecha = :fecha, cuota_reserva = :cuota, observacion = :obs, tasa_dolar = :tasa WHERE id_presupuesto = :id";
            $stmtH = $con->prepare($sqlHead);
            $stmtH->execute([
                ':fecha' => $this->fecha,
                ':cuota' => $this->cuota_reserva ?? 0,
                ':obs'   => $this->observacion,
                ':tasa'  => $this->tasa_dolar,
                ':id'    => $this->id_presupuesto
            ]);

            // Eliminar detalles antiguos y sus relaciones en tabla puente
            $con->prepare("DELETE FROM presupuesto_mensualidad WHERE detalle_presupuesto_id IN (SELECT id_detalle_presupuesto FROM detalles_presupuesto WHERE presupuesto_id = ?)")
                 ->execute([$this->id_presupuesto]);
            $con->prepare("DELETE FROM detalles_presupuesto WHERE presupuesto_id = ?")->execute([$this->id_presupuesto]);

            // Insertar nuevos detalles y acumular el nuevo total
            $total_monto = floatval($this->cuota_reserva);
            $ids_detalles = [];
            
            if (!empty($this->detalles_temp) && is_array($this->detalles_temp)) {
                $sqlDet = "INSERT INTO detalles_presupuesto (monto, nombre_detalle, presupuesto_id, tipo_gasto_id) 
                           VALUES (:monto, :nombre, :id_pre, :id_tipo)";
                $stmtD = $con->prepare($sqlDet);
                foreach ($this->detalles_temp as $det) {
                    $stmtD->execute([
                        ':monto'   => $det['monto'],
                        ':nombre'  => $det['nombre'],
                        ':id_pre'  => $this->id_presupuesto,
                        ':id_tipo' => $det['tipo_gasto_id']
                    ]);
                    $ids_detalles[] = $con->lastInsertId();
                    $total_monto += floatval($det['monto']);
                }
            }

            // Sincronizar el nuevo presupuesto con los recibos de los residentes
            list($anio, $mes) = explode('-', $this->fecha);
            $stmtBuscaPer = $con->prepare("SELECT id_periodo FROM periodos_mensualidad WHERE mes = :mes AND anio = :anio LIMIT 1");
            $stmtBuscaPer->execute([':mes' => $mes, ':anio' => $anio]);
            $periodo_id = $stmtBuscaPer->fetchColumn();

            if ($periodo_id) {
                $this->_sincronizar_mensualidades($con, $periodo_id, $total_monto, $ids_detalles);
            }

            $con->commit();
            return ['estatus' => true, 'mensaje' => 'Presupuesto actualizado correctamente'];
        } catch (\Exception $e) {
            if (isset($con) && $con->inTransaction()) {
                $con->rollBack();
            }
            error_log("Error en _modificar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al modificar: ' . $e->getMessage()];
        }
    }

    /**
     * ELIMINAR PRESUPUESTO (Soft delete)
     // SE USA EN EL MODULO
     */
    private function _eliminar_presupuesto()
    {
        $con = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $con->beginTransaction();

            // BLOQUEO DE AUDITORÍA: Verificamos pagos
            if ($this->_tiene_pagos_registrados($con, $this->id_presupuesto)) {
                throw new \Exception("Auditoría: No se puede eliminar el presupuesto porque ya existen pagos procesados o pendientes para este mes.");
            }

            // Si pasa la prueba, obtenemos el periodo para desactivar las mensualidades
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

            // Borrado lógico del presupuesto
            $sql = "UPDATE presupuesto SET activo = 0 WHERE id_presupuesto = :id";
            $stmt = $con->prepare($sql);
            $stmt->execute([':id' => $this->id_presupuesto]);

            $con->commit();
            return ['estatus' => true, 'mensaje' => 'Presupuesto y recibos pendientes eliminados correctamente.'];
        } catch (\Exception $e) {
            if (isset($con) && $con->inTransaction()) {
                $con->rollBack();
            }
            error_log("Error en _eliminar_presupuesto: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => $e->getMessage()];
        }
    }

    /**
     * Sincroniza el presupuesto con los recibos de los residentes.
     * Calcula la cuota por apartamento y reconstruye la tabla puente.
     * SE USA EN LOS METODOS PROPIOS DE LA CLASE
     */
    private function _sincronizar_mensualidades($con, $periodo_id, $total_monto, $ids_detalles)
    {
        $stmtApt = $con->query("SELECT id_apartamento, porcentaje_participacion FROM apartamentos WHERE activo = 1 FOR UPDATE");
        $apartamentos = $stmtApt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($apartamentos)) {
            throw new \Exception('No hay apartamentos activos para generar mensualidades');
        }

        // UPSERT para las mensualidades
       $sqlMens = "INSERT INTO mensualidad (monto, periodo_id, apartamento_id, porcentaje_interes, limite_mensualidad, activo) 
                    VALUES (:monto, :periodo_id, :apt_id, " . self::INTERES_MORA_DEFECTO . ", " . self::LIMITE_DIAS_MENSUALIDAD . ", " . self::ESTADO_ACTIVO . ")
                    ON DUPLICATE KEY UPDATE 
                        monto = VALUES(monto), 
                        activo = " . self::ESTADO_ACTIVO;
        $stmtMens = $con->prepare($sqlMens);

        $stmtGetId = $con->prepare("SELECT id_mensualidad FROM mensualidad WHERE periodo_id = :periodo_id AND apartamento_id = :apt_id");
        $stmtDelPuente = $con->prepare("DELETE FROM presupuesto_mensualidad WHERE mensualidad_id = :m_id");
        
        // Inserción en la tabla puente
        $sqlPuente = "INSERT INTO presupuesto_mensualidad (mensualidad_id, detalle_presupuesto_id) VALUES (:m_id, :dp_id)";
        $stmtPuente = $con->prepare($sqlPuente);

        foreach ($apartamentos as $apt) {
            $monto_apt = round(($total_monto * $apt['porcentaje_participacion']) / 100, 2);
            
            $stmtMens->execute([
                ':monto'  => $monto_apt,
                ':periodo_id' => $periodo_id,
                ':apt_id' => $apt['id_apartamento']
            ]);
            
            // Obtener ID real
            $stmtGetId->execute([':periodo_id' => $periodo_id, ':apt_id' => $apt['id_apartamento']]);
            $id_mensualidad = $stmtGetId->fetchColumn();

            // Limpiar y repoblar detalles (Tabla Puente)
            if ($id_mensualidad) {
                $stmtDelPuente->execute([':m_id' => $id_mensualidad]);
                foreach ($ids_detalles as $id_det) {
                    $stmtPuente->execute([':m_id' => $id_mensualidad, ':dp_id' => $id_det]);
                }
            }
        }
    }

    /**
     * Verifica si un presupuesto ya tiene pagos procesados o pendientes de aprobación
     * Retorna TRUE si hay pagos, FALSE si el periodo está limpio.
     */
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

    // GESTIÓN DE RELACIÓN CON MENSUALIDAD

    /**
     * Consulta los presupuestos agrupados por tipo de gasto para una fecha específica.
     * Devuelve el nombre del tipo, el monto total y los IDs de los detalles de presupuesto asociados.
     // SE USA EN MENSUALIDAD
     */
    private function _consultar_presupuestos_mensualidades()
    {
        // Extraemos el mes y el año de la fecha recibida
        $mes = date('m', strtotime($this->fecha));
        $anio = date('Y', strtotime($this->fecha));

        //  Usamos dos parámetros distintos: :mes y :anio
        $sql = "SELECT 
                    tg.nombre_tipo_gasto AS nombre, 
                    SUM(dp.monto) AS monto, 
                    GROUP_CONCAT(dp.id_detalle_presupuesto) AS id_presupuestos_asociados
                FROM tipo_gasto tg
                INNER JOIN detalles_presupuesto dp ON dp.tipo_gasto_id = tg.id_tipo_gasto
                INNER JOIN presupuesto p ON dp.presupuesto_id = p.id_presupuesto
                WHERE MONTH(p.fecha) = :mes 
                  AND YEAR(p.fecha) = :anio 
                  AND p.activo = 1
                GROUP BY tg.nombre_tipo_gasto";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            
            // Pasamos ambos parámetros al execute
            $stmt->execute([
                ':mes' => $mes,
                ':anio' => $anio
            ]);
            
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_presupuestos_mensualidades: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar presupuestos mensuales.'];
        }
    }

    // ====================================================================
    // CONSULTAS GENERALES
    // ====================================================================

    // SE USA EN EL MODULO
    private function _consultar()
    {
        try {
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
        } catch (PDOException $e) {
            error_log("Error en _consultar_general: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar presupuestos'];
        }
    }

    /**
     * Consulta los meses (y años) para los que no existe un presupuesto activo.
     * @return array ['estatus' => bool, 'datos' => array, 'mensaje' => string]
     */
    private function _consultar_meses_faltantes()
    {
        // Generamos una serie de meses desde el primer presupuesto ACTIVO hasta la fecha actual
        // Usamos una CTE recursiva
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

        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_meses_faltantes: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar meses faltantes'];
        }
    }

    // SE USA EN EL MODULO
    private function _consultar_presupuesto()
    {
        try {
            // Cabecera
            $sqlHead = "SELECT * FROM presupuesto WHERE id_presupuesto = :id AND activo = 1";
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sqlHead);
            $stmt->execute([':id' => $this->id_presupuesto]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$data) {
                return ['estatus' => false, 'mensaje' => 'Presupuesto no encontrado'];
            }

            // Detalles
            $sqlDet = "SELECT dp.*, tg.nombre_tipo_gasto 
                       FROM detalles_presupuesto dp 
                       LEFT JOIN tipo_gasto tg ON dp.tipo_gasto_id = tg.id_tipo_gasto
                       WHERE dp.presupuesto_id = :id";
            $stmtD = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sqlDet);
            $stmtD->execute([':id' => $this->id_presupuesto]);
            $data['detalles'] = $stmtD->fetchAll(PDO::FETCH_ASSOC);

            return ['estatus' => true, 'datos' => $data];
        } catch (PDOException $e) {
            error_log("Error en _consultar_unico: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar presupuesto'];
        }
    }

    /**
     * Consulta plana solo de la cabecera del presupuesto para la bitácora de auditoría.
     // SE USA EN EL MODULO
     */
    private function _consultar_cabecera_presupuesto()
    {
        try {
            $sql = "SELECT fecha, cuota_reserva, observacion FROM presupuesto WHERE id_presupuesto = :id AND activo = 1";
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([':id' => $this->id_presupuesto]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$datos) {
                return ['estatus' => false, 'mensaje' => 'Presupuesto no encontrado'];
            }

            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_cabecera_presupuesto: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar cabecera del presupuesto'];
        }
    }

    /**
     * Verifica si existe al menos un presupuesto para el mes dado.
     * Se espera que la propiedad $fecha contenga el número de mes (1-12).
     * @return array ['estatus' => bool, 'datos' => bool, 'mensaje' => string]
     // SE USA EN SOLICITUD GASTO
     */
    private function _consultar_mes_presupuesto()
    {
        $mes = (int)$this->fecha;
        if ($mes < 1 || $mes > 12) {
            return ['estatus' => false, 'mensaje' => 'Mes inválido'];
        }
        $sql = "SELECT 1 FROM presupuesto WHERE MONTH(fecha) = :mes AND activo = 1 LIMIT 1";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->bindParam(':mes', $mes, PDO::PARAM_INT);
            $stmt->execute();
            $existe = $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
            return ['estatus' => true, 'datos' => $existe];
        } catch (PDOException $e) {
            error_log("Error en _consultar_mes_presupuesto: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar mes'];
        }
    }

    /**
     * Verifica si existe al menos un presupuesto para el año dado.
     * Se espera que la propiedad $fecha contenga el año (ej. 2025).
     * @return array ['estatus' => bool, 'datos' => bool, 'mensaje' => string]
     // SE USA EN SOLICITUD GASTO
     */
    private function _consultar_anio_presupuesto()
    {
        $anio = (int)$this->fecha;

        // USAMOS LAS CONSTANTES DE CLASE
        if ($anio < self::ANIO_MINIMO_PERMITIDO || $anio > self::ANIO_MAXIMO_PERMITIDO) {
            return ['estatus' => false, 'mensaje' => 'Año inválido'];
        }

        $sql = "SELECT 1 FROM presupuesto WHERE YEAR(fecha) = :anio AND activo = 1 LIMIT 1";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt->execute();
            $existe = $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
            return ['estatus' => true, 'datos' => $existe];
        } catch (PDOException $e) {
            error_log("Error en _consultar_anio_presupuesto: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar año'];
        }
    }

    /**
     * Consulta el estado real de ejecución del presupuesto (Presupuestado vs Gastado).
     * para una tarjeta de resumen o gráficas analíticas. 
     * PROXIMAMENTE (si me da el tiempo -_-)
     */
    private function _consultar_ejecucion_anual()
    {
        // Traemos el año actual por defecto, o el que se haya seteado en $this->fecha
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

        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([':anio' => $anioFiltro]);
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar_ejecucion_anual: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al calcular la ejecución presupuestaria'];
        }
    }

}
