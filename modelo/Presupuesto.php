<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Presupuesto extends Conexion
{
    // ====================================================================
    // PROPIEDADES (Mapeo de 3 tablas)
    // ====================================================================

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
    // REGLAS DE VALIDACIÓN CENTRALIZADAS
    // ====================================================================
    private $reglas = [
        // Cabecera
        'id_presupuesto' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'presupuesto', 'campo' => 'id_presupuesto']
        ],
        'fecha' => [
            'regex' => '/^\d{4}-\d{2}-\d{2}$/'
        ],
        'cuota_reserva' => [
            'regex' => '/^\d+(\.\d{1,2})?$/',
            'opcional' => true
        ],
        'observacion' => [
            'regex' => '/^[a-zA-Z0-9áéíóúñÁÉÍÓÚÑ\s.,-]{0,255}$/',
            'opcional' => true
        ],

        // Detalles
        'id_detalle_presupuesto' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'detalles_presupuesto', 'campo' => 'id_detalle_presupuesto']
        ],
        'monto_detalle' => [
            'regex' => '/^\d+(\.\d{1,2})?$/',
            'min' => 0.01
        ],
        'nombre_detalle' => [
            'regex' => '/^[a-zA-Z0-9áéíóúñÁÉÍÓÚÑ\s.,-]{2,100}$/'
        ],
        'tipo_gasto_id' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'tipo_gasto', 'campo' => 'id_tipo_gasto']
        ],

        // Relación Mensualidad
        'mensualidad_id' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'mensualidad', 'campo' => 'id_mensualidad']
        ]
    ];

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
    // VALIDACIÓN CENTRALIZADA (MEJORADA)
    // ====================================================================
    private function validar($campos)
    {
        foreach ($campos as $campo) {
            if (!isset($this->reglas[$campo])) {
                continue;
            }
            $regla = $this->reglas[$campo];

            $getter = 'get_' . $campo;
            if (!method_exists($this, $getter)) {
                return ['estatus' => false, 'mensaje' => "Error interno: getter no encontrado para $campo."];
            }
            $valor = $this->$getter();

            // Determinar si el campo es requerido (por defecto sí, a menos que sea opcional)
            $requerido = !(isset($regla['opcional']) && $regla['opcional'] === true);

            if ($requerido) {
                if ($valor === null) {
                    return ['estatus' => false, 'mensaje' => "El campo '$campo' es requerido y no se ha establecido."];
                }
                if (is_string($valor) && trim($valor) === '') {
                    return ['estatus' => false, 'mensaje' => "El campo '$campo' no puede estar vacío."];
                }
            } else {
                // Si es opcional y está vacío (considerando que 0 es válido), saltamos validaciones adicionales
                if ($valor === null || (is_string($valor) && trim($valor) === '')) {
                    continue;
                }
            }

            // Validar expresión regular
            if (isset($regla['regex']) && !preg_match($regla['regex'], (string)$valor)) {
                return ['estatus' => false, 'mensaje' => "El campo '$campo' no tiene un formato válido."];
            }

            // Validar valor mínimo
            if (isset($regla['min']) && $valor < $regla['min']) {
                return ['estatus' => false, 'mensaje' => "El campo '$campo' debe ser mayor o igual a " . $regla['min']];
            }

            // Validar existencia en otra tabla (foránea)
            if (isset($regla['exists'])) {
                $tabla = $regla['exists']['tabla'];
                $campoFor = $regla['exists']['campo'] ?? $campo;
                if (!$this->existeEnTabla($tabla, $campoFor, $valor)) {
                    return ['estatus' => false, 'mensaje' => "El valor del campo '$campo' no existe en la tabla $tabla."];
                }
            }
        }
        return ['estatus' => true];
    }

    /**
     * Verifica existencia de un valor en una tabla, considerando 'activo' si existe.
     */
    private function existeEnTabla($tabla, $campo, $valor)
    {
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = :valor";
        // Si la tabla tiene columna 'activo', filtramos por ella.
        $tablasConActivo = ['presupuesto', 'tipo_gasto', 'mensualidad', 'detalles_presupuesto'];
        if (in_array($tabla, $tablasConActivo)) {
            $sql .= " AND activo = 1";
        }
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':valor', $valor);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en existeEnTabla: " . $e->getMessage());
            return false;
        }
    }

    // ====================================================================
    // LÓGICA DE NEGOCIO
    // ====================================================================

    /**
     * Registro completo de presupuesto (cabecera + detalles) y generación de mensualidades.
     * Requiere que estén seteadas: fecha, cuota_reserva, observacion, detalles_temp, tasa_dolar.
     */
    private function _registrar()
    {
        // Validar cabecera
        $val = $this->validar(['fecha']);
        if (!$val['estatus']) return $val;

        if (empty($this->detalles_temp) || !is_array($this->detalles_temp)) {
            return ['estatus' => false, 'mensaje' => 'No hay detalles para el presupuesto'];
        }

        $con = $this->get_conex('negocio');
        try {
            $con->beginTransaction();

            // 1. Insertar cabecera
            $sqlHead = "INSERT INTO presupuesto (fecha, cuota_reserva, observacion, activo) 
                        VALUES (:fecha, :cuota, :obs, 1)";
            $stmtH = $con->prepare($sqlHead);
            $stmtH->execute([
                ':fecha' => $this->fecha,
                ':cuota' => $this->cuota_reserva ?? 0,
                ':obs'   => $this->observacion
            ]);
            $id_presupuesto = $con->lastInsertId();

            // 2. Insertar detalles y acumular monto total
            $total_monto = floatval($this->cuota_reserva);
            $ids_detalles = [];
            $sqlDet = "INSERT INTO detalles_presupuesto (monto, nombre_detalle, presupuesto_id, tipo_gasto_id) 
                       VALUES (:monto, :nombre, :id_pre, :id_tipo)";
            $stmtD = $con->prepare($sqlDet);

            foreach ($this->detalles_temp as $det) {
                // Validar cada detalle
                if (!isset($det['monto'], $det['nombre'], $det['tipo_gasto_id'])) {
                    throw new \Exception('Detalle incompleto');
                }
                $stmtD->execute([
                    ':monto'   => $det['monto'],
                    ':nombre'  => $det['nombre'],
                    ':id_pre'  => $id_presupuesto,
                    ':id_tipo' => $det['tipo_gasto_id']
                ]);
                $ids_detalles[] = $con->lastInsertId();
                $total_monto += floatval($det['monto']);
            }

            // 3. Obtener apartamentos activos
            $stmtApt = $con->query("SELECT id_apartamento, porcentaje_participacion FROM apartamentos WHERE activo = 1");
            $apartamentos = $stmtApt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($apartamentos)) {
                throw new \Exception('No hay apartamentos activos para generar mensualidades');
            }

            // 4. Generar mensualidades y asociar detalles
            $sqlMens = "INSERT INTO mensualidad (monto, tasa_dolar, mes, anio, apartamento_id, porcentaje_interes, limite_mensualidad, activo) 
                        VALUES (:monto, :tasa, :mes, :anio, :apt_id, 10, 15, 1)";
            $stmtMens = $con->prepare($sqlMens);

            $sqlPuente = "INSERT INTO presupuesto_mensualidad (mensualidad_id, detalle_presupuesto_id) VALUES (:m_id, :dp_id)";
            $stmtPuente = $con->prepare($sqlPuente);

            list($anio, $mes) = explode('-', $this->fecha);

            foreach ($apartamentos as $apt) {
                $monto_apt = round(($total_monto * $apt['porcentaje_participacion']) / 100, 2);
                $stmtMens->execute([
                    ':monto'  => $monto_apt,
                    ':tasa'   => $this->tasa_dolar,
                    ':mes'    => $mes,
                    ':anio'   => $anio,
                    ':apt_id' => $apt['id_apartamento']
                ]);
                $id_mensualidad = $con->lastInsertId();

                foreach ($ids_detalles as $id_det) {
                    $stmtPuente->execute([':m_id' => $id_mensualidad, ':dp_id' => $id_det]);
                }
            }

            $con->commit();
            return ['estatus' => true, 'mensaje' => 'Presupuesto y mensualidades registrados con éxito', 'id' => $id_presupuesto];
        } catch (\Exception $e) {
            $con->rollBack();
            error_log("Error en _registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar: ' . $e->getMessage()];
        }
    }

    /**
     * Edición de presupuesto: actualiza cabecera y reemplaza detalles.
     * No regenera mensualidades.
     */
    private function _editar()
    {
        $val = $this->validar(['id_presupuesto', 'fecha']);
        if (!$val['estatus']) return $val;

        $con = $this->get_conex('negocio');
        try {
            $con->beginTransaction();

            // 1. Actualizar cabecera
            $sqlHead = "UPDATE presupuesto SET fecha = :fecha, cuota_reserva = :cuota, observacion = :obs WHERE id_presupuesto = :id";
            $stmtH = $con->prepare($sqlHead);
            $stmtH->execute([
                ':fecha' => $this->fecha,
                ':cuota' => $this->cuota_reserva ?? 0,
                ':obs'   => $this->observacion,
                ':id'    => $this->id_presupuesto
            ]);

            // 2. Eliminar detalles antiguos y sus relaciones en tabla puente
            $con->prepare("DELETE FROM presupuesto_mensualidad WHERE detalle_presupuesto_id IN (SELECT id_detalle_presupuesto FROM detalles_presupuesto WHERE presupuesto_id = ?)")
                 ->execute([$this->id_presupuesto]);
            $con->prepare("DELETE FROM detalles_presupuesto WHERE presupuesto_id = ?")->execute([$this->id_presupuesto]);

            // 3. Insertar nuevos detalles
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
                }
            }

            $con->commit();
            return ['estatus' => true, 'mensaje' => 'Presupuesto actualizado correctamente'];
        } catch (\Exception $e) {
            $con->rollBack();
            error_log("Error en _editar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al editar: ' . $e->getMessage()];
        }
    }

    /**
     * ELIMINAR DETALLE ESPECÍFICO
     */
    private function _eliminar_detalle()
    {
        $validacion = $this->validar(['id_detalle_presupuesto']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        try {
            $sql = "DELETE FROM detalles_presupuesto WHERE id_detalle_presupuesto = :id";
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':id' => $this->id_detalle_presupuesto]);
            return ['estatus' => true, 'mensaje' => 'Detalle eliminado'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar_detalle: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar detalle: ' . $e->getMessage()];
        }
    }

    /**
     * ELIMINAR PRESUPUESTO (Soft delete)
     */
    private function _eliminar_presupuesto()
    {
        $validacion = $this->validar(['id_presupuesto']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        try {
            $sql = "UPDATE presupuesto SET activo = 0 WHERE id_presupuesto = :id";
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':id' => $this->id_presupuesto]);
            return ['estatus' => true, 'mensaje' => 'Presupuesto eliminado'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar_presupuesto: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar presupuesto: ' . $e->getMessage()];
        }
    }

    // ====================================================================
    // GESTIÓN DE RELACIÓN CON MENSUALIDAD
    // ====================================================================

    /**
     * Sincroniza detalles de presupuesto con una mensualidad usando SP.
     * Requiere: mensualidad_id y ids_detalles_string (string de IDs separados por comas)
     */
    private function _sincronizar_mensualidad()
    {
        $validacion = $this->validar(['mensualidad_id']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        if (empty($this->ids_detalles_string)) {
            return ['estatus' => false, 'mensaje' => 'Faltan los IDs de detalles a sincronizar'];
        }

        // Validar que los IDs existan (opcional, pero recomendable)
        $ids = array_filter(array_map('trim', explode(',', $this->ids_detalles_string)));
        if (empty($ids)) {
            return ['estatus' => false, 'mensaje' => 'La lista de IDs está vacía o es inválida'];
        }

        // Verificar existencia de cada ID en detalles_presupuesto
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sqlCheck = "SELECT COUNT(*) FROM detalles_presupuesto WHERE id_detalle_presupuesto IN ($placeholders) AND activo = 1";
        $stmtCheck = $this->get_conex('negocio')->prepare($sqlCheck);
        $stmtCheck->execute($ids);
        if ($stmtCheck->fetchColumn() != count($ids)) {
            return ['estatus' => false, 'mensaje' => 'Uno o más IDs de detalles no existen o están inactivos'];
        }

        try {
            $sql = "CALL sp_sincronizar_presupuestos_mensualidad(:mens_id, :lista_ids)";
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([
                ':mens_id'   => $this->mensualidad_id,
                ':lista_ids' => $this->ids_detalles_string
            ]);
            return ['estatus' => true, 'mensaje' => 'Presupuesto sincronizado con la mensualidad'];
        } catch (PDOException $e) {
            error_log("Error en _sincronizar_mensualidad: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al sincronizar: ' . $e->getMessage()];
        }
    }

    /**
     * Consultar detalles de presupuesto asociados a una mensualidad
     */
    private function _consultar_asociados_mensualidad()
    {
        $validacion = $this->validar(['mensualidad_id']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        try {
            $sql = "SELECT dp.*, p.fecha, tg.nombre_tipo_gasto
                    FROM detalles_presupuesto dp
                    JOIN presupuesto p ON dp.presupuesto_id = p.id_presupuesto
                    JOIN tipo_gasto tg ON dp.tipo_gasto_id = tg.id_tipo_gasto
                    JOIN presupuesto_mensualidad pm ON dp.id_detalle_presupuesto = pm.detalle_presupuesto_id
                    WHERE pm.mensualidad_id = :id";
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':id' => $this->mensualidad_id]);
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar_asociados_mensualidad: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar asociados: ' . $e->getMessage()];
        }
    }

    /**
     * Verifica si existe al menos un presupuesto para el mes dado.
     * Se espera que la propiedad $fecha contenga el número de mes (1-12).
     * @return array ['estatus' => bool, 'datos' => bool, 'mensaje' => string]
     */
    private function _consultar_mes_presupuesto()
    {
        $mes = (int)$this->fecha;
        if ($mes < 1 || $mes > 12) {
            return ['estatus' => false, 'mensaje' => 'Mes inválido'];
        }
        $sql = "SELECT 1 FROM presupuesto WHERE MONTH(fecha) = :mes AND activo = 1 LIMIT 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
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
     */
    private function _consultar_anio_presupuesto()
    {
        $anio = (int)$this->fecha;
        if ($anio < 2000 || $anio > 2100) {
            return ['estatus' => false, 'mensaje' => 'Año inválido'];
        }
        $sql = "SELECT 1 FROM presupuesto WHERE YEAR(fecha) = :anio AND activo = 1 LIMIT 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
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
     * Consulta los presupuestos agrupados por tipo de gasto para una fecha específica.
     * Devuelve el nombre del tipo, el monto total y los IDs de los detalles de presupuesto asociados.
     */
    private function _consultar_presupuestos_mensualidades()
    {

        $validacion = $this->validar(['fecha']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "SELECT 
                    tg.nombre_tipo_gasto AS nombre, 
                    SUM(dp.monto) AS monto, 
                    GROUP_CONCAT(dp.id_detalle_presupuesto) AS id_presupuestos_asociados
                FROM tipo_gasto tg
                INNER JOIN detalles_presupuesto dp ON dp.tipo_gasto_id = tg.id_tipo_gasto
                INNER JOIN presupuesto p ON dp.presupuesto_id = p.id_presupuesto
                WHERE p.fecha = :fecha AND p.activo = 1
                GROUP BY tg.nombre_tipo_gasto";

        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':fecha' => $this->fecha]);
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

    private function _consultar_general()
    {
        try {
            $sql = "SELECT p.*, 
                           (SELECT SUM(monto) FROM detalles_presupuesto WHERE presupuesto_id = p.id_presupuesto) as total_estimado,
                           COUNT(dp.id_detalle_presupuesto) as cantidad_detalles
                    FROM presupuesto p
                    LEFT JOIN detalles_presupuesto dp ON p.id_presupuesto = dp.presupuesto_id
                    WHERE p.activo = 1
                    GROUP BY p.id_presupuesto
                    ORDER BY p.fecha DESC";
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar_general: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar presupuestos'];
        }
    }

    /**
     * Consulta los meses (y años) para los que no existe un presupuesto.
     * @return array ['estatus' => bool, 'datos' => array, 'mensaje' => string]
     */
    private function _consultar_meses_faltantes()
    {
        // Generamos una serie de meses desde el primer presupuesto hasta la fecha actual
        // Usamos una CTE recursiva
        $sql = "
            WITH RECURSIVE MesesDelCalendario (anio, mes) AS (
                SELECT 
                    CASE 
                        WHEN EXISTS (SELECT 1 FROM presupuesto) 
                        THEN (SELECT YEAR(MIN(fecha)) FROM presupuesto)
                        ELSE YEAR(CURDATE())
                    END,
                    CASE 
                        WHEN EXISTS (SELECT 1 FROM presupuesto) 
                        THEN (SELECT MONTH(MIN(fecha)) FROM presupuesto)
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
            LEFT JOIN presupuesto p ON c.anio = YEAR(p.fecha) AND c.mes = MONTH(p.fecha)
            WHERE p.id_presupuesto IS NULL
            ORDER BY anio_faltante, mes_faltante
        ";

        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_meses_faltantes: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar meses faltantes'];
        }
    }

    private function _consultar_unico()
    {
        $validacion = $this->validar(['id_presupuesto']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        try {
            // Cabecera
            $sqlHead = "SELECT * FROM presupuesto WHERE id_presupuesto = :id AND activo = 1";
            $stmt = $this->get_conex('negocio')->prepare($sqlHead);
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
            $stmtD = $this->get_conex('negocio')->prepare($sqlDet);
            $stmtD->execute([':id' => $this->id_presupuesto]);
            $data['detalles'] = $stmtD->fetchAll(PDO::FETCH_ASSOC);

            return ['estatus' => true, 'datos' => $data];
        } catch (PDOException $e) {
            error_log("Error en _consultar_unico: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar presupuesto'];
        }
    }
}
?>