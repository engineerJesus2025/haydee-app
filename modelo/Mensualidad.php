<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\modelo\Notificaciones;

class Mensualidad extends Conexion
{
    // ====================================================================
    // PROPIEDADES (Tabla mensualidad)
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

    private $datos_apartamentos = [];
    // Propiedad para almacenar los IDs de mensualidad (como string separado por comas)
    private $ids_mensualidades;

    // ====================================================================
    // REGLAS DE VALIDACIÓN CENTRALIZADAS
    // ====================================================================
    private $reglas = [
        'id_mensualidad' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'mensualidad', 'campo' => 'id_mensualidad']
        ],
        'monto' => [
            'regex' => '/^\d+(\.\d{1,2})?$/',
            'min' => 0
        ],
        'tasa_dolar' => [
            'regex' => '/^\d+(\.\d{1,2})?$/',
            'min' => 0
        ],
        'mes' => [
            'regex' => '/^(0?[1-9]|1[0-2])$/',
            'min' => 1,
            'max' => 12
        ],
        'anio' => [
            'regex' => '/^\d{4}$/',
            'min' => 2000,
            'max' => 2100
        ],
        'apartamento_id' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'apartamentos', 'campo' => 'id_apartamento']
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
    // VALIDACIÓN CENTRALIZADA
    // ====================================================================
    private function validar($campos, $contexto = [])
    {
        foreach ($campos as $campo) {
            if (!isset($this->reglas[$campo])) {
                return [
                    'estatus' => false,
                    'mensaje' => "No hay reglas de validación definidas para el campo '$campo'."
                ];
            }
            $regla = $this->reglas[$campo];

            $getter = 'get_' . $campo;
            if (!method_exists($this, $getter)) {
                return ['estatus' => false, 'mensaje' => "Error interno: getter no encontrado para $campo."];
            }
            $valor = $this->$getter();

            // Requerido (a menos que sea opcional)
            $requerido = !(isset($regla['opcional']) && $regla['opcional'] === true);
            if ($requerido) {
                if ($valor === null) {
                    return ['estatus' => false, 'mensaje' => "El campo '$campo' es requerido y no se ha establecido."];
                }
                if (is_string($valor) && trim($valor) === '') {
                    return ['estatus' => false, 'mensaje' => "El campo '$campo' no puede estar vacío."];
                }
            } else {
                // Si es opcional y está vacío, saltamos validaciones adicionales
                if ($valor === null || (is_string($valor) && trim($valor) === '')) {
                    continue;
                }
            }

            // Validar expresión regular
            if (isset($regla['regex']) && !preg_match($regla['regex'], (string)$valor)) {
                return ['estatus' => false, 'mensaje' => "El campo '$campo' no tiene un formato válido."];
            }

            // Validar mínimo/máximo
            if (isset($regla['min']) && $valor < $regla['min']) {
                return ['estatus' => false, 'mensaje' => "El campo '$campo' debe ser mayor o igual a " . $regla['min']];
            }
            if (isset($regla['max']) && $valor > $regla['max']) {
                return ['estatus' => false, 'mensaje' => "El campo '$campo' debe ser menor o igual a " . $regla['max']];
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

    private function validarApartamento($datos)
    {
        // Campos requeridos
        if (!isset($datos['id_apartamento']) || empty($datos['id_apartamento'])) {
            return ['estatus' => false, 'mensaje' => 'Falta el id del apartamento.'];
        }
        if (!isset($datos['monto']) || !is_numeric($datos['monto']) || $datos['monto'] <= 0) {
            return ['estatus' => false, 'mensaje' => 'Monto inválido para el apartamento ' . $datos['id_apartamento']];
        }
        if (!isset($datos['id_presupuestos']) || !is_array($datos['id_presupuestos'])) {
            return ['estatus' => false, 'mensaje' => 'Falta la lista de presupuestos para el apartamento ' . $datos['id_apartamento']];
        }

        // Validar existencia del apartamento
        if (!$this->existeEnTabla('apartamentos', 'id_apartamento', $datos['id_apartamento'])) {
            return ['estatus' => false, 'mensaje' => 'El apartamento ID ' . $datos['id_apartamento'] . ' no existe.'];
        }

        // Validar existencia de cada presupuesto
        foreach ($datos['id_presupuestos'] as $id_p) {
            if (!$this->existeEnTabla('detalles_presupuesto', 'id_detalle_presupuesto', $id_p)) {
                return ['estatus' => false, 'mensaje' => 'El detalle de presupuesto ID ' . $id_p . ' no existe.'];
            }
        }

        // Si es edición, validar que la mensualidad exista
        if (isset($datos['id_mensualidad']) && !empty($datos['id_mensualidad'])) {
            if (!$this->existeEnTabla('mensualidad', 'id_mensualidad', $datos['id_mensualidad'])) {
                return ['estatus' => false, 'mensaje' => 'La mensualidad ID ' . $datos['id_mensualidad'] . ' no existe.'];
            }
        }

        return ['estatus' => true];
    }

    /**
     * Verifica existencia de un valor en una tabla (negocio).
     */
    private function existeEnTabla($tabla, $campo, $valor)
    {
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = :valor";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':valor', $valor);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en existeEnTabla: " . $e->getMessage());
            return false;
        }
    }

    // ====================================================================
    // VALIDACIÓN AUXILIAR PARA MES/AÑO
    // ====================================================================
    private function validarMesAnio()
    {
        $v = $this->validar(['mes', 'anio']);
        if (!$v['estatus']) {
            return $v;
        }
        return ['estatus' => true];
    }

    // ====================================================================
    // MÉTODOS PRIVADOS (ACCIONES)
    // ====================================================================

    /**
     * Verifica qué meses tienen presupuesto pero no mensualidad.
     */
    private function _verificarMeses()
    {
        $sql = "SELECT MONTH(p.fecha) as mes_presupuesto, YEAR(p.fecha) as anio_presupuesto 
                FROM presupuesto p
                LEFT JOIN mensualidad m ON CAST(CONCAT(m.anio, '-', m.mes, '-01') AS DATE) = p.fecha
                WHERE m.id_mensualidad IS NULL";
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

    /**
     * Consulta mensualidades agrupadas por mes/año con totales.
     */
    private function _consultarPorMeses()
    {
        $sql = "SELECT 
                    GROUP_CONCAT(m.id_mensualidad) as ids,
                    GROUP_CONCAT(m.apartamento_id) as ids_apartamentos,
                    SUM(m.monto) as monto,
                    m.tasa_dolar,
                    m.mes,
                    m.anio,
                    SUM(LEAST(m.monto, COALESCE(pagos.total_pagado, 0))) as pagado,
                    m.porcentaje_interes,
                    m.limite_mensualidad
                FROM mensualidad m
                LEFT JOIN (
                    SELECT pm.mensualidad_id, SUM(dp.monto) as total_pagado
                    FROM detalles_pagos dp
                    JOIN pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
                    GROUP BY pm.mensualidad_id
                ) as pagos ON m.id_mensualidad = pagos.mensualidad_id
                WHERE m.activo = 1
                GROUP BY m.mes, m.anio";
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

    /**
     * Consulta mensualidades de un mes/año específico con detalles de apartamento y propietario.
     */
    private function _consultar_mensualidad_apartamentos()
    {
        $val = $this->validarMesAnio();
        if (!$val['estatus']) return $val;

        $mesInt = (int)$this->mes;
        $anioInt = (int)$this->anio;

        $sql = "SELECT id_mensualidad, id_apartamento, mensualidad.mes, mensualidad.anio,
                       apartamentos.nro_apartamento,
                       habitantes.nombre, habitantes.apellido,
                       mensualidad.monto, mensualidad.tasa_dolar,
                       COALESCE((SELECT SUM(dp.monto) FROM detalles_pagos dp
                                 INNER JOIN pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
                                 WHERE pm.mensualidad_id = mensualidad.id_mensualidad),0) as pagado,
                       COALESCE((SELECT SUM(dp.monto_dolar) FROM detalles_pagos dp
                                 INNER JOIN pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
                                 WHERE pm.mensualidad_id = mensualidad.id_mensualidad),0) as pagado_dolar
                FROM mensualidad
                INNER JOIN apartamentos ON mensualidad.apartamento_id = apartamentos.id_apartamento
                INNER JOIN habitantes_apartamentos ON habitantes_apartamentos.apartamento_id = apartamentos.id_apartamento
                INNER JOIN habitantes ON habitantes_apartamentos.habitante_id = habitantes.id_habitante
                WHERE mensualidad.mes = :mes AND mensualidad.anio = :anio
                  AND habitantes_apartamentos.tipo_vinculo = 'Propietario'
                  AND mensualidad.activo = 1";
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

    /**
     * Consulta los presupuestos asociados a las mensualidades cuyos IDs se han seteado.
     * @return array ['estatus' => bool, 'datos' => array, 'mensaje' => string]
     */
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
                // Extraer solo los IDs
                $resultados[] = array_column($filas, 'detalle_presupuesto_id');
            }
            return ['estatus' => true, 'datos' => $resultados];
        } catch (PDOException $e) {
            error_log("Error en _consultar_presupuestos_asociados: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar presupuestos asociados.' . $e->getMessage()];
        }
    }

    /**
     * Registrar masivo (unificado)
     */
    private function _registrar()
    {
        if (empty($this->datos_apartamentos)) {
            return ['estatus' => false, 'mensaje' => 'No hay datos de apartamentos para registrar.'];
        }

        // Validar cada elemento del array
        foreach ($this->datos_apartamentos as $item) {
            $validacion = $this->validarApartamento($item);
            if (!$validacion['estatus']) {
                return $validacion;
            }
        }

        $id_mensualidad;

        $con = $this->get_conex('negocio');
        try {
            $con->beginTransaction();

            $sqlM = "INSERT INTO mensualidad (monto, tasa_dolar, mes, anio, apartamento_id, porcentaje_interes, limite_mensualidad)
                     VALUES (:monto, :tasa_dolar, :mes, :anio, :apartamento_id, :porcentaje_interes, :limite_mensualidad)";
            $stmtM = $con->prepare($sqlM);

            $sqlP = "INSERT INTO presupuesto_mensualidad (detalle_presupuesto_id, mensualidad_id) VALUES (:det_id, :men_id)";
            $stmtP = $con->prepare($sqlP);

            foreach ($this->datos_apartamentos as $item) {
                // Insertar mensualidad
                $stmtM->execute([
                    ':monto' => $item['monto'],
                    ':tasa_dolar' => $this->tasa_dolar,
                    ':mes' => $this->mes,
                    ':anio' => $this->anio,
                    ':apartamento_id' => $item['id_apartamento'],
                    ':porcentaje_interes' => $this->porcentaje_interes,
                    ':limite_mensualidad' => $this->limite_mensualidad
                ]);
                $id_mensualidad = $con->lastInsertId();

                // Insertar relaciones en tabla puente
                foreach ($item['id_presupuestos'] as $id_detalle) {
                    $stmtP->execute([
                        ':det_id' => $id_detalle,
                        ':men_id' => $id_mensualidad
                    ]);
                }
            }

            $con->commit();

            // Notificar a propietarios
            $notif = new Notificaciones();
            $notif->set_titulo("Nueva mensualidad disponible");
            $notif->set_descripcion("Se han generado las mensualidades para el mes {$this->mes} del año {$this->anio}.");
            $notif->set_tabla_origen('mensualidad');
            $notif->set_id_registro_origen($id_mensualidad); // o el ID de la primera mensualidad si se desea
            $notif->set_tipo_evento('NUEVA_MENSUALIDAD');
            $notif->set_rol_nombre('Propietario');
            $notif->realizar_consulta('notificar_por_rol');

            return ['estatus' => true, 'mensaje' => 'Todas las mensualidades se registraron correctamente.'];
        } catch (Exception $e) {
            $con->rollBack();
            error_log("Error en _registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar: ' . $e->getMessage()];
        }
    }

    /**
     * modificar masivo (unificado)
     */
    private function _modificar()
    {
        if (empty($this->datos_apartamentos)) {
            return ['estatus' => false, 'mensaje' => 'No hay datos de apartamentos para modificar.'];
        }

        // Validar cada elemento
        foreach ($this->datos_apartamentos as $item) {
            $validacion = $this->validarApartamento($item);
            if (!$validacion['estatus']) {
                return $validacion;
            }
        }

        $con = $this->get_conex('negocio');
        try {
            $con->beginTransaction();

            $sqlUpdate = "UPDATE mensualidad SET 
                            monto = :monto,
                            tasa_dolar = :tasa_dolar,
                            mes = :mes,
                            anio = :anio,
                            apartamento_id = :apartamento_id,
                            porcentaje_interes = :porcentaje_interes,
                            limite_mensualidad = :limite_mensualidad
                          WHERE id_mensualidad = :id_mensualidad";
            $stmtUpdate = $con->prepare($sqlUpdate);

            $sqlInsert = "INSERT INTO mensualidad (monto, tasa_dolar, mes, anio, apartamento_id, porcentaje_interes, limite_mensualidad)
                          VALUES (:monto, :tasa_dolar, :mes, :anio, :apartamento_id, :porcentaje_interes, :limite_mensualidad)";
            $stmtInsert = $con->prepare($sqlInsert);

            $sqlDeletePuente = "DELETE FROM presupuesto_mensualidad WHERE mensualidad_id = :men_id";
            $stmtDelete = $con->prepare($sqlDeletePuente);

            $sqlInsertPuente = "INSERT INTO presupuesto_mensualidad (detalle_presupuesto_id, mensualidad_id) VALUES (:det_id, :men_id)";
            $stmtInsertPuente = $con->prepare($sqlInsertPuente);

            foreach ($this->datos_apartamentos as $item) {
                if (!empty($item['id_mensualidad'])) {
                    // Actualizar existente
                    $stmtUpdate->execute([
                        ':id_mensualidad' => $item['id_mensualidad'],
                        ':monto' => $item['monto'],
                        ':tasa_dolar' => $this->tasa_dolar,
                        ':mes' => $this->mes,
                        ':anio' => $this->anio,
                        ':apartamento_id' => $item['id_apartamento'],
                        ':porcentaje_interes' => $this->porcentaje_interes,
                        ':limite_mensualidad' => $this->limite_mensualidad
                    ]);
                    $id_mensualidad = $item['id_mensualidad'];
                } else {
                    // Insertar nuevo
                    $stmtInsert->execute([
                        ':monto' => $item['monto'],
                        ':tasa_dolar' => $this->tasa_dolar,
                        ':mes' => $this->mes,
                        ':anio' => $this->anio,
                        ':apartamento_id' => $item['id_apartamento'],
                        ':porcentaje_interes' => $this->porcentaje_interes,
                        ':limite_mensualidad' => $this->limite_mensualidad
                    ]);
                    $id_mensualidad = $con->lastInsertId();
                }

                // Reemplazar relaciones en tabla puente
                $stmtDelete->execute([':men_id' => $id_mensualidad]);

                // Validar que exista el array de presupuestos
                if (isset($item['id_presupuestos']) && is_array($item['id_presupuestos'])) {
                    foreach ($item['id_presupuestos'] as $id_detalle) {
                        $stmtInsertPuente->execute([
                            ':det_id' => $id_detalle,
                            ':men_id' => $id_mensualidad
                        ]);
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

    /**
     * Elimina (soft delete) mensualidades de un mes/año.
     */
    private function _eliminar()
    {
        $val = $this->validarMesAnio();
        if (!$val['estatus']) return $val;

        $mesInt = (int)$this->mes;
        $anioInt = (int)$this->anio;

        $sql = "UPDATE mensualidad SET activo = 0 WHERE mes = :mes AND anio = :anio";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':mes', $mesInt, PDO::PARAM_INT);
            $stmt->bindParam(':anio', $anioInt, PDO::PARAM_INT);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Mensualidades eliminadas'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar mensualidades'];
        }
    }

    /**
     * Estadísticas para la página de inicio (morosos, totales).
     */
    private function _consultar_estadisticas_inicio()
    {
        $sql = "
            WITH TotalFacturado AS (
                SELECT apartamento_id, SUM(monto) AS monto_total_facturado
                FROM mensualidad
                GROUP BY apartamento_id
            ),
            TotalPagado AS (
                SELECT m.apartamento_id, SUM(dp.monto) AS monto_total_pagado
                FROM detalles_pagos dp
                JOIN pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
                JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
                GROUP BY m.apartamento_id
            ),
            SaldosFinales AS (
                SELECT f.apartamento_id,
                       (COALESCE(f.monto_total_facturado, 0) - COALESCE(p.monto_total_pagado, 0)) AS saldo,
                       COALESCE(p.monto_total_pagado, 0) AS pagado_individual
                FROM TotalFacturado f
                LEFT JOIN TotalPagado p ON f.apartamento_id = p.apartamento_id
                UNION
                SELECT p.apartamento_id,
                       (COALESCE(f.monto_total_facturado, 0) - COALESCE(p.monto_total_pagado, 0)) AS saldo,
                       COALESCE(p.monto_total_pagado, 0) AS pagado_individual
                FROM TotalFacturado f
                RIGHT JOIN TotalPagado p ON f.apartamento_id = p.apartamento_id
                WHERE f.apartamento_id IS NULL
            )
            SELECT
                COUNT(CASE WHEN saldo > 0.01 THEN 1 END) AS accion,
                SUM(CASE WHEN saldo > 0.01 THEN saldo ELSE 0 END) AS valor,
                'morosos' as elemento
            FROM SaldosFinales
            UNION
            SELECT
                COUNT(CASE WHEN saldo <= 0.01 THEN 1 END) AS accion,
                SUM(CASE WHEN saldo <= 0.01 THEN pagado_individual ELSE 0 END) AS valor,
                'sin deuda' as elemento
            FROM SaldosFinales
            UNION
            SELECT 'total_egresos' as accion, SUM(dg.monto) as valor, 'total egresos' as elemento
            FROM detalles_gastos dg
            WHERE dg.fecha BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND CURDATE()
            UNION
            SELECT 'total_ingresos' as accion, SUM(dp.monto) as valor, 'total ingresos' as elemento
            FROM detalles_pagos dp
            WHERE dp.fecha BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND CURDATE()";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_estadisticas_inicio: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar estadísticas'];
        }
    }

    /**
     * Consulta los meses y años para los que existen mensualidades.
     */
    private function _consultar_meses_mensualidad()
    {
        $sql = "SELECT mes, anio FROM mensualidad GROUP BY anio, mes ORDER BY anio, mes DESC";
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

    /**
     * Consulta la tasa de dólar de un mes/año (la máxima registrada).
     */
    private function _consultar_tasa_dolar_mensualidades()
    {
        $val = $this->validarMesAnio();
        if (!$val['estatus']) return $val;

        $sql = "SELECT MAX(mes) as mes, MAX(anio) as anio, MAX(tasa_dolar) as tasa_dolar
                FROM mensualidad WHERE anio = :anio AND mes = :mes";
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

    /**
     * Consulta el estado de deuda de todos los apartamentos (histórico).
     */
    private function _consultar_mensualidades_pendientes()
    {
        $sql = "WITH FacturacionMensual AS (
                    SELECT apartamento_id, anio, mes, SUM(monto) AS total_facturado
                    FROM mensualidad
                    GROUP BY apartamento_id, anio, mes
                ),
                PagosMensuales AS (
                    SELECT m.apartamento_id, m.anio, m.mes, SUM(dp.monto) AS total_pagado
                    FROM detalles_pagos dp
                    JOIN pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
                    JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
                    GROUP BY m.apartamento_id, m.anio, m.mes
                ),
                BalanceDelMes AS (
                    SELECT f.apartamento_id, f.anio, f.mes,
                           (COALESCE(f.total_facturado, 0) - COALESCE(p.total_pagado, 0)) AS cambio_neto_mes
                    FROM FacturacionMensual f
                    LEFT JOIN PagosMensuales p ON f.apartamento_id = p.apartamento_id AND f.anio = p.anio AND f.mes = p.mes
                    UNION
                    SELECT p.apartamento_id, p.anio, p.mes,
                           (COALESCE(f.total_facturado, 0) - COALESCE(p.total_pagado, 0)) AS cambio_neto_mes
                    FROM FacturacionMensual f
                    RIGHT JOIN PagosMensuales p ON f.apartamento_id = p.apartamento_id AND f.anio = p.anio AND f.mes = p.mes
                    WHERE f.apartamento_id IS NULL
                )
                SELECT a.nro_apartamento, b.anio, b.mes, b.cambio_neto_mes,
                       SUM(b.cambio_neto_mes) OVER (PARTITION BY b.apartamento_id ORDER BY b.anio, b.mes) AS deuda_acumulada
                FROM BalanceDelMes b
                JOIN apartamentos a ON b.apartamento_id = a.id_apartamento
                ORDER BY a.nro_apartamento, b.anio, b.mes";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_mensualidades_pendientes: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar pendientes'];
        }
    }

    // ====================================================================
    // MÉTODO AUXILIAR (API de tasa de dólar) - se mantiene público si es necesario
    // ====================================================================
    public function obtenerTasaDolarAPI()
    {
        $apiUrl = "https://pydolarve.org/api/v2/tipo-cambio?currency=usd";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response === false) return 0;
        $data = json_decode($response, true);
        return (json_last_error() === JSON_ERROR_NONE && isset($data['price'])) ? (float) $data['price'] : 0;
    }

    /**
     * Genera la estructura de datos lista para el reporte PDF del Cuadro de Pagos.
     * @param string $mes_limite Mes tope (ej. '08')
     * @param string $anio_limite Año tope (ej. '2024')
     */
    public function generarEstructuraCuadroPagos($mes_limite, $anio_limite)
    {
        // 1. Obtener todas las deudas históricas
        $resp_deudas = $this->_consultar_mensualidades_pendientes();
        if (!$resp_deudas['estatus']) {
            return $resp_deudas;
        }
        $deudas_globales = $resp_deudas['datos'];

        $meses_nombres = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];

        // 2. Recolectar y filtrar períodos
        $periodos = [];
        foreach ($deudas_globales as $deuda) {
            $periodo = $deuda['anio'] . '-' . str_pad($deuda['mes'], 2, '0', STR_PAD_LEFT);
            // Filtrar hasta el límite
            if ($deuda['anio'] < $anio_limite || ($deuda['anio'] == $anio_limite && $deuda['mes'] <= $mes_limite)) {
                $periodos[$periodo] = ['anio' => $deuda['anio'], 'mes' => $deuda['mes']];
            }
        }

        // Ordenar períodos
        uasort($periodos, function($a, $b) {
            if ($a['anio'] == $b['anio']) return $a['mes'] - $b['mes'];
            return $a['anio'] - $b['anio'];
        });

        // 3. Construir Cabecera
        $cabecera_tabla = [];
        foreach ($periodos as $per) {
            $cabecera_tabla[] = $meses_nombres[$per['mes'] - 1] . ' ' . $per['anio'];
        }

        // 4. Construir Cuerpo y Totales
        $cuerpo_tabla = [];
        $total_mensual = array_fill(0, count($periodos), 0);

        // Reindexar periodos para búsqueda rápida
        $periodos_indexados = array_values($periodos);

        foreach ($deudas_globales as $deuda) {
            $anio = $deuda['anio'];
            $mes = $deuda['mes'];
            $apto = $deuda['nro_apartamento'];
            $deuda_acum = $deuda['deuda_acumulada'];

            if ($anio < $anio_limite || ($anio == $anio_limite && $mes <= $mes_limite)) {
                if (!isset($cuerpo_tabla[$apto])) {
                    $cuerpo_tabla[$apto] = array_fill(0, count($periodos_indexados), 0);
                }
                
                // Buscar índice del periodo
                foreach ($periodos_indexados as $idx => $per) {
                    if ($per['anio'] == $anio && $per['mes'] == $mes) {
                        $cuerpo_tabla[$apto][$idx] = $deuda_acum;
                        $total_mensual[$idx] += $deuda_acum; // Sumar al total general
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
}
?>