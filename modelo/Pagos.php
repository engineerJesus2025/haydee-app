<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Pagos extends Conexion
{
    // Propiedades (mapeo de tablas)
    private $id_pago;
    private $estado;      // PENDIENTE, PROCESADO, RECHAZADO, ANULADO
    private $observacion;
    private $activo;

    private $id_detalle_pago;
    private $fecha;
    private $monto;
    private $monto_dolar;
    private $tipo_pago;   // Transferencia, Pago Movil, Efectivo, Divisa

    private $referencia;
    private $imagen;
    private $banco_id;

    private $mensualidad_id;
    private $apartamento_id; // Para consultas por apartamento
    private $correo;

    private $detalles = [];
    private $monto_mensualidad; // Añadido para mantener registro si es necesario

    // ====================================================================
    // VALIDACIONES CENTRALIZADAS
    // ====================================================================
    
    /**
     * Reglas para la tabla principal (Cabecera del Pago)
     */
    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
            'id_pago' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'pagos', 'campo' => 'id_pago']
            ],
            'apartamento_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'apartamentos', 'campo' => 'id_apartamento']
            ],
            'observacion' => [
                'regex' => '/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s.,:\/-]{0,255}$/',
                'opcional' => true
            ],
            'estado' => [
                'regex' => '/^(PENDIENTE|PROCESADO|RECHAZADO|ANULADO)$/',
            ],
            'mensualidad_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'mensualidad', 'campo' => 'id_mensualidad']
            ],
        ];

        // Estandarización de nombres aplicada (registrar_pago, modificar_pago...)
        $camposPorOperacion = [
            'registrar_pago'           => ['apartamento_id', 'observacion','mensualidad_id','estado'],
            'modificar_pago'           => ['id_pago', 'apartamento_id', 'observacion','mensualidad_id','estado'],
            'eliminar_pago'            => ['id_pago'],
            'actualizar_estado_pago'   => ['id_pago', 'estado', 'observacion'], // Usado por los admins (ya no)
            'consultar_pago' => ['id_pago']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    /**
     * Reglas para cada fila de la tabla de detalles (Detalles Pagos)
     */
    public static function obtenerReglasDetalles() {
        return [
            'fecha_pago' => [ 
                'regex' => '/^\d{4}-\d{2}-\d{2}$/'
            ],
            'monto' => [
                'regex' => '/^\d+(\.\d{1,2})?$/',
                'min' => 0.01
            ],
            'monto_dolar' => [
                'regex' => '/^\d+(\.\d{1,2})?$/',
                'opcional' => true
            ],
            'tipo_pago' => [
                'regex' => '/^(Efectivo|Pago Movil|Transferencia|Divisa)$/'
            ],
            'referencia' => [
                'regex' => '/^[a-zA-Z0-9-]{4,20}$/',
                'opcional' => true,
                'requerido_si' => ['tipo_pago' => ['Transferencia', 'Pago Movil']]
            ],

            'imagen' => [
                'regex' => '/^[a-zA-Z0-9_.-]+\.(jpg|jpeg|png|gif)$/i',
                'opcional' => true,
                'requerido_si' => ['tipo_pago' => ['Transferencia', 'Pago Movil']]
            ],
            'banco_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'bancos', 'campo' => 'id_banco'],
                'opcional' => true,
                'requerido_si' => ['tipo_pago' => ['Transferencia', 'Pago Movil']]
            ]
        ];
    }

    // Getters y Setters
    public function set_id_pago($id) { $this->id_pago = $id; }
    public function get_id_pago() { return $this->id_pago; }
    public function set_id_detalle_pago($id) { $this->id_detalle_pago = $id; }
    public function get_id_detalle_pago() { return $this->id_detalle_pago; }
    public function set_estado($e) { $this->estado = $e; }
    public function get_estado() { return $this->estado; }
    public function set_observacion($o) { $this->observacion = $o; }
    public function get_observacion() { return $this->observacion; }
    public function set_fecha($f) { $this->fecha = $f; }
    public function get_fecha() { return $this->fecha; }
    public function set_monto($m) { $this->monto = $m; }
    public function get_monto() { return $this->monto; }
    public function set_monto_dolar($md) { $this->monto_dolar = $md; }
    public function get_monto_dolar() { return $this->monto_dolar; }
    public function set_tipo_pago($t) { $this->tipo_pago = $t; }
    public function get_tipo_pago() { return $this->tipo_pago; }
    public function set_referencia($r) { $this->referencia = $r; }
    public function get_referencia() { return $this->referencia; }
    public function set_imagen($i) { $this->imagen = $i; }
    public function get_imagen() { return $this->imagen; }
    public function set_banco_id($b) { $this->banco_id = $b; }
    public function get_banco_id() { return $this->banco_id; }
    public function set_mensualidad_id($m) { $this->mensualidad_id = $m; }
    public function get_mensualidad_id() { return $this->mensualidad_id; }
    public function set_apartamento_id($a) { $this->apartamento_id = $a; }
    public function get_apartamento_id() { return $this->apartamento_id; }
    public function set_correo($correo) { $this->correo = $correo; }
    public function get_correo() { return $this->correo; }
    public function set_detalles($detalles) { $this->detalles = $detalles; }
    public function get_detalles() { return $this->detalles; }
    public function set_monto_mensualidad($m) { $this->monto_mensualidad = $m; }
    public function get_monto_mensualidad() { return $this->monto_mensualidad; }

    /**
     * Enrutador de acciones con soporte para parámetros
     */
    public function realizar_consulta($accion, $param = null)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            return ['estatus' => false, 'mensaje' => "La acción '$accion' no está implementada."];
        }

        try {
            if ($param !== null) {
                return $this->$metodo($param);
            } else {
                return $this->$metodo();
            }
        } catch (\Exception $e) {
            error_log("Error en realizar_consulta ($accion): " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Ocurrió un error interno en el servidor.'];
        }
    }

    // ====================================================================
    // REGLAS DE NEGOCIO Y VALIDACIONES COMPLEJAS
    // ====================================================================

    /**
     * Regla de negocio: Verifica que las referencias bancarias no estén duplicadas 
     * en el mismo formulario ni registradas en otros pagos.
     */
    private function _validar_referencias_unicas() {
        $pdo = $this->get_conex('negocio');
        $refsUsadas = [];
        
        foreach ($this->detalles as $idx => $det) {
            if (!empty($det['referencia'])) {
                $ref = trim($det['referencia']);
                
                // 1. Evitar duplicados en los renglones del mismo formulario
                if (in_array($ref, $refsUsadas)) {
                    return ['estatus' => false, 'mensaje' => "La referencia '$ref' está repetida en el renglón " . ($idx + 1) . "."];
                }
                $refsUsadas[] = $ref;

                // 2. Verificar contra la base de datos mediante un JOIN
                $sql = "SELECT dp.pago_id FROM ingresos_bancarios ib 
                        JOIN detalles_pagos dp ON ib.detalle_pago_id = dp.id_detalle_pago 
                        WHERE ib.referencia = :ref LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':ref' => $ref]);
                $pago_id_bd = $stmt->fetchColumn();

                if ($pago_id_bd) {
                    // Si existe, comprobamos si pertenece a otro pago distinto al actual
                    if (empty($this->id_pago) || $pago_id_bd != $this->id_pago) {
                        return ['estatus' => false, 'mensaje' => "La referencia bancaria '$ref' (Renglón " . ($idx + 1) . ") ya se encuentra registrada en otro pago."];
                    }
                }
            }
        }
        return ['estatus' => true];
    }

    /**
     * Utilidad para el AJAX: Verifica si la referencia está libre.
     * Ignora la referencia si pertenece al mismo pago que se está modificando.
     */
    public function verificarReferenciaDisponible($referencia, $id_pago_actual = null) {
        $sql = "SELECT dp.pago_id FROM ingresos_bancarios ib 
                JOIN detalles_pagos dp ON ib.detalle_pago_id = dp.id_detalle_pago 
                WHERE ib.referencia = :ref LIMIT 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':ref' => $referencia]);
            $pago_id_bd = $stmt->fetchColumn();

            if ($pago_id_bd) {
                // Si la referencia existe y NO es de este mismo pago, está OCUPADA (true)
                if (empty($id_pago_actual) || $pago_id_bd != $id_pago_actual) {
                    return true; 
                }
            }
            return false; // Está DISPONIBLE (false)
        } catch (\PDOException $e) {
            return false;
        }
    }

    // ====================================================================
    // MÉTODOS PÚBLICOS AUXILIARES (mantener compatibilidad)
    // ====================================================================

    /**
     * Consulta mensualidades pendientes de un apartamento (original)
     // SE USA EN EL MODULO
     */
    public function _consultarMensualidadPendiente()
    {
        $sql = "SELECT 
                    m.id_mensualidad,
                    m.monto,
                    pm_per.tasa_dolar,
                    pm_per.mes,
                    pm_per.anio,
                    m.porcentaje_interes,
                    m.limite_mensualidad,
                    COALESCE(SUM(dp.monto), 0) AS total_pagado,
                    (m.monto - COALESCE(SUM(dp.monto), 0)) AS pendiente
                FROM mensualidad m
                INNER JOIN periodos_mensualidad pm_per ON m.periodo_id = pm_per.id_periodo
                LEFT JOIN pagos_mensualidad p_m ON m.id_mensualidad = p_m.mensualidad_id
                LEFT JOIN detalles_pagos dp ON p_m.detalle_pago_id = dp.id_detalle_pago
                WHERE m.apartamento_id = :id_apartamento AND m.activo = 1
                GROUP BY m.id_mensualidad
                HAVING pendiente > 0
                ORDER BY pm_per.anio, pm_per.mes";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_apartamento', $this->apartamento_id, PDO::PARAM_INT);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en consultarMensualidadPendiente: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar mensualidades pendientes'];
        }
    }

    // ====================================================================
    // MÉTODOS PRIVADOS (acciones)
    // ====================================================================

    // -------------------- CONSULTAS --------------------
    // SE USA EN EL MODULO
    private function _consultar()
    {
        $sql = "SELECT 
                p.id_pago,
                p.estado,
                MAX(dp.fecha) AS ultima_fecha,
                SUM(dp.monto) AS monto_total,
                (SELECT tipo_pago FROM detalles_pagos WHERE pago_id = p.id_pago ORDER BY fecha DESC LIMIT 1) AS tipo_pago_predominante,
                CASE WHEN COUNT(DISTINCT a.nro_apartamento) = 1 THEN MAX(a.nro_apartamento) ELSE 'Varios' END AS apartamento,
                GROUP_CONCAT(DISTINCT CONCAT(pm_per.mes, '/', pm_per.anio) ORDER BY pm_per.anio, pm_per.mes SEPARATOR ', ') AS periodos
            FROM pagos p
            JOIN detalles_pagos dp ON p.id_pago = dp.pago_id
            LEFT JOIN ingresos_bancarios ib ON dp.id_detalle_pago = ib.detalle_pago_id
            LEFT JOIN pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
            LEFT JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
            LEFT JOIN periodos_mensualidad pm_per ON m.periodo_id = pm_per.id_periodo
            LEFT JOIN apartamentos a ON m.apartamento_id = a.id_apartamento
            WHERE p.activo = 1
            GROUP BY p.id_pago
            ORDER BY ultima_fecha DESC;";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_pagos: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar pagos'];
        }
    }

    // SE USA EN EL MODULO
    private function _consultar_por_correo()
    {
        // Obtener correo de la sesión
        $correo = $_SESSION['usuario'] ?? null;
        if (!$correo) {
            return ['estatus' => false, 'mensaje' => 'Correo no disponible en sesión'];
        }

        $sql = "SELECT 
                    p.id_pago,
                    p.estado,
                    MAX(dp.fecha) AS ultima_fecha,
                    SUM(dp.monto) AS monto_total,
                    (SELECT tipo_pago FROM detalles_pagos WHERE pago_id = p.id_pago ORDER BY fecha DESC LIMIT 1) AS tipo_pago_predominante,
                    CASE WHEN COUNT(DISTINCT a.nro_apartamento) = 1 THEN MAX(a.nro_apartamento) ELSE 'Varios' END AS apartamento,
                    GROUP_CONCAT(DISTINCT CONCAT(pm_per.mes, '/', pm_per.anio) ORDER BY pm_per.anio, pm_per.mes SEPARATOR ', ') AS periodos
                FROM pagos p
                JOIN detalles_pagos dp ON p.id_pago = dp.pago_id
                LEFT JOIN ingresos_bancarios ib ON dp.id_detalle_pago = ib.detalle_pago_id
                LEFT JOIN pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
                LEFT JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
                LEFT JOIN periodos_mensualidad pm_per ON m.periodo_id = pm_per.id_periodo
                LEFT JOIN apartamentos a ON m.apartamento_id = a.id_apartamento
                WHERE p.activo = 1
                  AND EXISTS (
                      SELECT 1
                      FROM pagos_mensualidad pm2
                      JOIN mensualidad m2 ON pm2.mensualidad_id = m2.id_mensualidad
                      JOIN apartamentos a2 ON m2.apartamento_id = a2.id_apartamento
                      JOIN habitantes_apartamentos ha2 ON a2.id_apartamento = ha2.apartamento_id
                      JOIN habitantes h2 ON ha2.habitante_id = h2.id_habitante
                      WHERE pm2.detalle_pago_id IN (SELECT id_detalle_pago FROM detalles_pagos WHERE pago_id = p.id_pago)
                        AND h2.correo = :correo
                  )
                GROUP BY p.id_pago
                ORDER BY ultima_fecha DESC";

        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':correo' => $this->correo]);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_por_correo: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar pagos por correo'];
        }
    }

    // SE USA EN EL MODULO
    private function _consultar_pago()
    {
        try {
            // 1. Cabecera del pago
            $sqlHead = "SELECT
                    p.*,
                    pm.mensualidad_id,
                    m.apartamento_id,
                    m.monto AS monto_mensualidad,
                    a.nro_apartamento
                FROM
                    pagos p
                LEFT JOIN detalles_pagos dp ON
                    p.id_pago = dp.pago_id
                LEFT JOIN pagos_mensualidad pm ON
                    dp.id_detalle_pago = pm.detalle_pago_id
                LEFT JOIN mensualidad m ON
                    pm.mensualidad_id = m.id_mensualidad
                LEFT JOIN apartamentos a ON a.id_apartamento = m.apartamento_id
                        WHERE p.id_pago = :id AND p.activo = 1 LIMIT 1";
            $stmtH = $this->get_conex('negocio')->prepare($sqlHead);
            $stmtH->execute([':id' => $this->id_pago]);
            $cabecera = $stmtH->fetch(PDO::FETCH_ASSOC);

            if (!$cabecera) return ['estatus' => false, 'mensaje' => 'Pago no encontrado'];

            // 2. Detalles del pago
            $sqlDet = "SELECT dp.*, ib.referencia, ib.banco_id, ib.imagen, b.nombre_banco
                       FROM detalles_pagos dp
                       LEFT JOIN ingresos_bancarios ib ON dp.id_detalle_pago = ib.detalle_pago_id
                       LEFT JOIN bancos b ON ib.banco_id = b.id_banco
                       WHERE dp.pago_id = :id";
            $stmtD = $this->get_conex('negocio')->prepare($sqlDet);
            $stmtD->execute([':id' => $this->id_pago]);
            $detalles = $stmtD->fetchAll(PDO::FETCH_ASSOC);

            $cabecera['detalles'] = $detalles;

            return ['estatus' => true, 'datos' => $cabecera];
        } catch (PDOException $e) {
            error_log("Error en _consultar_pago_unico: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar el pago'];
        }
    }

    /**
     * Consulta plana solo de la cabecera del pago para la bitácora de auditoría.
     * Actualizado para consultar mensualidad_id y apartamento_id,
     * permitiendo al GestorAuditoria registrar diferencias exactas.
     // SE USA EN EL MODULO
     */
    private function _consultar_cabecera_pago()
    {
        // Añadimos mensualidad_id y apartamento_id a la consulta plana
        $sql = "SELECT 
                    p.estado, 
                    p.observacion,
                    pm.mensualidad_id,
                    m.apartamento_id
                FROM pagos p
                LEFT JOIN detalles_pagos dp ON p.id_pago = dp.pago_id
                LEFT JOIN pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
                LEFT JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
                WHERE p.id_pago = :id_pago AND p.activo = 1
                LIMIT 1";
        
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':id_pago' => $this->id_pago]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$datos) {
                return ['estatus' => false, 'mensaje' => 'Pago no encontrado'];
            }
            
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_cabecera_pago: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar cabecera del pago'];
        }
    }


    // -------------------- OPERACIONES DE PAGO --------------------
    // SE USA EN EL MODULO
    private function _registrar_pago()
    {
        if (empty($this->detalles) || !is_array($this->detalles)) {
            return ['estatus' => false, 'mensaje' => 'No se recibieron detalles para el pago'];
        }

        // Validar que las referencias sean únicas
        $valRef = $this->_validar_referencias_unicas();
        if (!$valRef['estatus']) return $valRef;

        $pdo = $this->get_conex('negocio');
        try {
            $pdo->beginTransaction();

            // 1. Insertar Cabecera
            $sqlHead = "INSERT INTO pagos (estado, observacion, activo) VALUES (:est, :obs, 1)";
            $stmtHead = $pdo->prepare($sqlHead);
            $estado = $this->estado ?? 'PENDIENTE';
            $obs = $this->observacion ?? 'Pago registrado por sistema';
            $stmtHead->execute([':est' => $estado, ':obs' => $obs]);
            $id_pago = $pdo->lastInsertId();

            // 2. Preparar consultas para los detalles
            $sqlDet = "INSERT INTO detalles_pagos (fecha, monto, monto_dolar, tipo_pago, pago_id) 
                       VALUES (:fecha, :monto, :md, :tipo, :pago_id)";
            $stmtDet = $pdo->prepare($sqlDet);

            $sqlBanco = "INSERT INTO ingresos_bancarios (referencia, imagen, detalle_pago_id, banco_id) 
                         VALUES (:ref, :img, :det_id, :banco)";
            $stmtBanco = $pdo->prepare($sqlBanco);

            $sqlRel = "INSERT INTO pagos_mensualidad (detalle_pago_id, mensualidad_id) VALUES (:det_id, :mens_id)";
            $stmtRel = $pdo->prepare($sqlRel);

            // 3. Iterar e insertar
            foreach ($this->detalles as $det) {
                $stmtDet->execute([
                    ':fecha' => $det['fecha_pago'],
                    ':monto' => $det['monto'],
                    ':md'    => $det['monto_dolar'] ?? 0,
                    ':tipo'  => $det['tipo_pago'],
                    ':pago_id' => $id_pago
                ]);
                $id_detalle = $pdo->lastInsertId();

                // Si requiere comprobante bancario
                $tipo = strtolower(trim($det['tipo_pago']));
                if (in_array($tipo, ['transferencia', 'pago movil', 'pago_movil'])) {
                    $stmtBanco->execute([
                        ':ref'   => $det['referencia'] ?? '',
                        ':img'   => $det['imagen'] ?? 'default.png',
                        ':det_id'=> $id_detalle,
                        ':banco' => $det['banco_id'] ?? null
                    ]);
                }

                // Relacionar con la mensualidad
                $stmtRel->execute([
                    ':det_id'  => $id_detalle,
                    ':mens_id' => $this->mensualidad_id
                ]);
            }

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Pago registrado con éxito', 'id' => $id_pago];
        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log("Error en _registrar pago: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error del servidor'];
        }
    }

    /**
     * Edita un pago completo borrando los detalles viejos y registrando los nuevos
     // SE USA EN EL MODULO
     */
    private function _modificar_pago()
    {
        if (empty($this->detalles) || !is_array($this->detalles)) {
            return ['estatus' => false, 'mensaje' => 'No se recibieron detalles para el pago'];
        }

        // Validar que las referencias sean únicas
        $valRef = $this->_validar_referencias_unicas();
        if (!$valRef['estatus']) return $valRef;

        $pdo = $this->get_conex('negocio');
        try {
            $pdo->beginTransaction();

            // 1. Actualizar Cabecera
            $sqlHead = "UPDATE pagos SET estado = :estado, observacion = :obs WHERE id_pago = :id";
            $stmtHead = $pdo->prepare($sqlHead);
            $stmtHead->execute([
                ':estado' => $this->estado,
                ':obs'    => $this->observacion,
                ':id'     => $this->id_pago
            ]);

            // 2. Gestionar las imágenes para no borrar las que aún se usan
            $imagenesAConservar = array_column($this->detalles, 'imagen');

            $sqlGetDet = "SELECT id_detalle_pago FROM detalles_pagos WHERE pago_id = :id";
            $stmtGet = $pdo->prepare($sqlGetDet);
            $stmtGet->execute([':id' => $this->id_pago]);
            $detallesViejos = $stmtGet->fetchAll(PDO::FETCH_COLUMN);

            foreach ($detallesViejos as $id_det) {
                $imgAnterior = $this->obtenerNombreImagenPorDetalle($id_det);
                if ($imgAnterior && $imgAnterior !== 'default.png') {
                    if (!in_array($imgAnterior, $imagenesAConservar)) {
                        \haydee\ayuda\GestorImagenes::eliminar($imgAnterior, 'pagos');
                    }
                }
            }

            // 3. Eliminar relaciones y detalles viejos de la BD
            $pdo->prepare("DELETE FROM ingresos_bancarios WHERE detalle_pago_id IN (SELECT id_detalle_pago FROM detalles_pagos WHERE pago_id = ?)")->execute([$this->id_pago]);
            $pdo->prepare("DELETE FROM pagos_mensualidad WHERE detalle_pago_id IN (SELECT id_detalle_pago FROM detalles_pagos WHERE pago_id = ?)")->execute([$this->id_pago]);
            $pdo->prepare("DELETE FROM detalles_pagos WHERE pago_id = ?")->execute([$this->id_pago]);

            // 4. Insertar nuevos detalles (igual que en registrar)
            $sqlDet = "INSERT INTO detalles_pagos (fecha, monto, monto_dolar, tipo_pago, pago_id) VALUES (:fecha, :monto, :md, :tipo, :pago_id)";
            $stmtDet = $pdo->prepare($sqlDet);

            $sqlBanco = "INSERT INTO ingresos_bancarios (referencia, imagen, detalle_pago_id, banco_id) VALUES (:ref, :img, :det_id, :banco)";
            $stmtBanco = $pdo->prepare($sqlBanco);

            $sqlRel = "INSERT INTO pagos_mensualidad (detalle_pago_id, mensualidad_id) VALUES (:det_id, :mens_id)";
            $stmtRel = $pdo->prepare($sqlRel);

            foreach ($this->detalles as $det) {
                $stmtDet->execute([
                    ':fecha' => $det['fecha_pago'],
                    ':monto' => $det['monto'],
                    ':md'    => $det['monto_dolar'] ?? 0,
                    ':tipo'  => $det['tipo_pago'],
                    ':pago_id' => $this->id_pago
                ]);
                $id_detalle = $pdo->lastInsertId();

                $tipo = strtolower(trim($det['tipo_pago']));
                if (in_array($tipo, ['transferencia', 'pago movil', 'pago_movil'])) {
                    $stmtBanco->execute([
                        ':ref'   => $det['referencia'] ?? '',
                        ':img'   => $det['imagen'] ?? 'default.png',
                        ':det_id'=> $id_detalle,
                        ':banco' => $det['banco_id'] ?? null
                    ]);
                }

                $stmtRel->execute([
                    ':det_id'  => $id_detalle,
                    ':mens_id' => $this->mensualidad_id
                ]);
            }

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Pago actualizado con éxito'];
        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log("Error en _modificar pago: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar pago: ' . $e->getMessage()];
        }
    }

    // SE USA EN EL MODULO
    private function _eliminar_pago()
    {
        $sql = "UPDATE pagos SET activo = 0, estado = 'ANULADO' WHERE id_pago = :id";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':id' => $this->id_pago]);
            return ['estatus' => true, 'mensaje' => 'Pago anulado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar_pago: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al anular: ' . $e->getMessage()];
        }
    }

    // SE USA EN REPORTES
    private function _consultarReciboPago()
    {
        $sql = "SELECT 
                    h.nombre, h.apellido, a.nro_apartamento,
                    MAX(dp.fecha) as fecha_pago, pm_per.mes, pm_per.anio, p.id_pago,
                    SUM(dp.monto) as total,
                    COUNT(CASE WHEN dp.tipo_pago = 'Transferencia' THEN 1 END) as count_transferencia,
                    COUNT(CASE WHEN dp.tipo_pago = 'Pago Movil' THEN 1 END) as count_pago_movil,
                    COUNT(CASE WHEN dp.tipo_pago = 'Efectivo' THEN 1 END) as count_efectivo,
                    GROUP_CONCAT(DISTINCT b.nombre_banco SEPARATOR ', ') as bancos,
                    GROUP_CONCAT(DISTINCT ib.referencia SEPARATOR ', ') as referencias
                FROM pagos p
                JOIN detalles_pagos dp ON p.id_pago = dp.pago_id
                JOIN pagos_mensualidad p_m ON dp.id_detalle_pago = p_m.detalle_pago_id
                JOIN mensualidad m ON p_m.mensualidad_id = m.id_mensualidad
                JOIN periodos_mensualidad pm_per ON m.periodo_id = pm_per.id_periodo
                JOIN apartamentos a ON m.apartamento_id = a.id_apartamento
                JOIN habitantes_apartamentos ha ON a.id_apartamento = ha.apartamento_id
                JOIN habitantes h ON ha.habitante_id = h.id_habitante
                LEFT JOIN ingresos_bancarios ib ON dp.id_detalle_pago = ib.detalle_pago_id
                LEFT JOIN bancos b ON ib.banco_id = b.id_banco
                WHERE p.id_pago = :id_pago AND ha.tipo_vinculo = 'Propietario'
                GROUP BY p.id_pago, pm_per.mes, pm_per.anio, h.nombre, h.apellido, a.nro_apartamento";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':id_pago' => $this->id_pago]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$datos) {
                return ['estatus' => false, 'mensaje' => 'No se encontraron datos para el recibo'];
            }
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultarReciboPago: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar recibo'];
        }
    }

    // -----------------------------------------------------------------
    // Helpers de archivos (privados)
    // -----------------------------------------------------------------
    private function obtenerNombreImagenPorDetalle($idDetalle)
    {
        $sql = "SELECT imagen FROM ingresos_bancarios WHERE detalle_pago_id = :id";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':id' => $idDetalle]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en obtenerNombreImagenPorDetalle: " . $e->getMessage());
            return false;
        }
    }
}