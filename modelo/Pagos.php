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

    private $detalles_temp = [];
    private $monto_mensualidad; // Añadido para mantener registro si es necesario

    // Reglas de validación centralizadas
    private $reglas = [
        'id_pago' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'pagos', 'campo' => 'id_pago']
        ],
        'id_detalle_pago' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'detalles_pagos', 'campo' => 'id_detalle_pago']
        ],
        'estado' => [
            'regex' => '/^(PENDIENTE|PROCESADO|RECHAZADO|ANULADO)$/'
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
            'regex' => '/^(Transferencia|Pago Movil|Efectivo|Divisa)$/'
        ],
        'referencia' => [
            'regex' => '/^[a-zA-Z0-9]{4,20}$/',
            'opcional' => true,
            'requerido_si' => ['tipo_pago' => ['Transferencia', 'Pago Movil']]
        ],
        'banco_id' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'bancos', 'campo' => 'id_banco'],
            'opcional' => true,
            'requerido_si' => ['tipo_pago' => ['Transferencia', 'Pago Movil']]
        ],
        'mensualidad_id' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'mensualidad', 'campo' => 'id_mensualidad']
        ],
        'apartamento_id' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'apartamentos', 'campo' => 'id_apartamento']
        ]
    ];

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
    public function setDetallesTemp($d) { $this->detalles_temp = $d; }
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

    // -----------------------------------------------------------------
    // Validación centralizada
    // -----------------------------------------------------------------
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

            // Determinar si el campo es requerido
            $requerido = !(isset($regla['opcional']) && $regla['opcional'] === true);

            // Validación condicional (requerido_si)
            if (isset($regla['requerido_si'])) {
                foreach ($regla['requerido_si'] as $campoCond => $valoresCond) {
                    $getterCond = 'get_' . $campoCond;
                    $valorCond = $this->$getterCond();
                    if (in_array($valorCond, $valoresCond)) {
                        $requerido = true;
                        break;
                    }
                }
            }

            if ($requerido) {
                if ($valor === null) {
                    return ['estatus' => false, 'mensaje' => "El campo '$campo' es requerido y no se ha establecido."];
                }
                if (is_string($valor) && trim($valor) === '') {
                    return ['estatus' => false, 'mensaje' => "El campo '$campo' no puede estar vacío."];
                }
            } else {
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

            // Validar existencia en otra tabla
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
    // MÉTODOS PÚBLICOS AUXILIARES (mantener compatibilidad)
    // ====================================================================

    /**
     * Consulta mensualidades pendientes de un apartamento (original)
     */
    public function consultarMensualidadPendiente($id_apartamento)
    {
        $sql = "SELECT 
                    m.id_mensualidad,
                    m.monto,
                    m.tasa_dolar,
                    m.mes,
                    m.anio,
                    m.porcentaje_interes,
                    m.limite_mensualidad,
                    COALESCE(SUM(dp.monto), 0) AS total_pagado,
                    (m.monto - COALESCE(SUM(dp.monto), 0)) AS pendiente
                FROM mensualidad m
                LEFT JOIN pagos_mensualidad pm ON m.id_mensualidad = pm.mensualidad_id
                LEFT JOIN detalles_pagos dp ON pm.detalle_pago_id = dp.id_detalle_pago
                WHERE m.apartamento_id = :id_apartamento AND m.activo = 1
                GROUP BY m.id_mensualidad
                HAVING pendiente > 0
                ORDER BY m.anio, m.mes";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_apartamento', $id_apartamento, PDO::PARAM_INT);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en consultarMensualidadPendiente: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar mensualidades pendientes'];
        }
    }

    /**
     * Consulta una mensualidad específica (original)
     */
    public function consultarMensualidadEspecifica()
    {
        if (empty($this->mensualidad_id)) {
            return ['estatus' => false, 'mensaje' => 'ID de mensualidad no proporcionado'];
        }
        $sql = "SELECT 
                    m.id_mensualidad,
                    m.monto,
                    m.tasa_dolar,
                    m.mes,
                    m.anio,
                    m.porcentaje_interes,
                    m.limite_mensualidad,
                    COALESCE(SUM(dp.monto), 0) AS total_pagado,
                    (m.monto - COALESCE(SUM(dp.monto), 0)) AS pendiente
                FROM mensualidad m
                LEFT JOIN pagos_mensualidad pm ON m.id_mensualidad = pm.mensualidad_id
                LEFT JOIN detalles_pagos dp ON pm.detalle_pago_id = dp.id_detalle_pago
                WHERE m.id_mensualidad = :id_mensualidad AND m.activo = 1
                GROUP BY m.id_mensualidad";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_mensualidad', $this->mensualidad_id);
            $stmt->execute();
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$datos) {
                return ['estatus' => false, 'mensaje' => 'Mensualidad no encontrada'];
            }
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en consultarMensualidadEspecifica: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar mensualidad'];
        }
    }

    /**
     * Consulta pagos por correo del habitante (para propietarios)
     */
    public function consultarPorCorreo($correo)
    {
        $sql = "SELECT 
                    p.id_pago,
                    (SELECT SUM(dp.monto) FROM detalles_pagos dp WHERE dp.pago_id = p.id_pago) AS monto,
                    (SELECT MIN(dp.fecha) FROM detalles_pagos dp WHERE dp.pago_id = p.id_pago) AS fecha,
                    p.estado,
                    a.nro_apartamento AS apartamento,
                    CONCAT(
                        CASE m.mes
                            WHEN 1 THEN 'Enero' WHEN 2 THEN 'Febrero' WHEN 3 THEN 'Marzo'
                            WHEN 4 THEN 'Abril' WHEN 5 THEN 'Mayo' WHEN 6 THEN 'Junio'
                            WHEN 7 THEN 'Julio' WHEN 8 THEN 'Agosto' WHEN 9 THEN 'Septiembre'
                            WHEN 10 THEN 'Octubre' WHEN 11 THEN 'Noviembre' WHEN 12 THEN 'Diciembre'
                        END, ' ', m.anio
                    ) AS mensualidad
                FROM pagos p
                LEFT JOIN detalles_pagos dp ON dp.pago_id = p.id_pago
                LEFT JOIN pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
                LEFT JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
                LEFT JOIN apartamentos a ON m.apartamento_id = a.id_apartamento
                LEFT JOIN habitantes_apartamentos ha ON a.id_apartamento = ha.apartamento_id
                LEFT JOIN habitantes h ON ha.habitante_id = h.id_habitante
                WHERE h.correo = :correo AND p.activo = 1
                GROUP BY p.id_pago
                ORDER BY fecha DESC";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':correo', $correo);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en consultarPorCorreo: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar pagos por correo'];
        }
    }

    // ====================================================================
    // MÉTODOS PRIVADOS (acciones)
    // ====================================================================

    // -------------------- CONSULTAS --------------------

    private function _consultar_pagos()
    {
        $sql = "SELECT 
                p.id_pago,
                p.estado,
                MAX(dp.fecha) AS ultima_fecha,
                SUM(dp.monto) AS monto_total,
                -- Método de pago del último detalle (para mostrar moneda)
                (SELECT tipo_pago 
                 FROM detalles_pagos 
                 WHERE pago_id = p.id_pago 
                 ORDER BY fecha DESC 
                 LIMIT 1) AS tipo_pago_predominante,
                CASE WHEN COUNT(DISTINCT a.nro_apartamento) = 1 
                     THEN MAX(a.nro_apartamento) 
                     ELSE 'Varios' 
                END AS apartamento,
                -- Periodos de las mensualidades asociadas (concatenados)
                GROUP_CONCAT(DISTINCT CONCAT(m.mes, '/', m.anio) ORDER BY m.anio, m.mes SEPARATOR ', ') AS periodos
            FROM pagos p
            JOIN detalles_pagos dp ON p.id_pago = dp.pago_id
            LEFT JOIN ingresos_bancarios ib ON dp.id_detalle_pago = ib.detalle_pago_id
            LEFT JOIN pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
            LEFT JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
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

    private function _consultar_pago_unico()
    {
        $validacion = $this->validar(['id_pago']);
        if (!$validacion['estatus']) return $validacion;

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

    private function _consultar_detalle_unico()
    {
        $validacion = $this->validar(['id_detalle_pago']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "SELECT dp.*, ib.referencia, ib.banco_id, ib.imagen, pm.mensualidad_id
                FROM detalles_pagos dp
                LEFT JOIN ingresos_bancarios ib ON dp.id_detalle_pago = ib.detalle_pago_id
                LEFT JOIN pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
                WHERE dp.id_detalle_pago = :id_det";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_det', $this->id_detalle_pago);
            $stmt->execute();
            $dato = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$dato) {
                return ['estatus' => false, 'mensaje' => 'Detalle no encontrado'];
            }
            return ['estatus' => true, 'datos' => $dato];
        } catch (PDOException $e) {
            error_log("Error en _consultar_detalle_unico: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar detalle'];
        }
    }

    private function _consultar_por_correo()
    {
        $correo = $_SESSION['usuario'] ?? null;
        if (!$correo) {
            return ['estatus' => false, 'mensaje' => 'Correo no disponible en sesión'];
        }
        return $this->consultarPorCorreo($correo);
    }

    private function _consultar_mensualidades_pendientes_por_apartamento($apartamento_id)
    {
        return $this->consultarMensualidadPendiente($apartamento_id);
    }

    private function _consultar_mensualidad_especifica()
    {
        return $this->consultarMensualidadEspecifica();
    }

    // -------------------- OPERACIONES DE PAGO --------------------

    private function _registrar_pago_completo()
    {
        // ... (ya existente)
        $campos = ['monto', 'tipo_pago', 'mensualidad_id'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $pdo = $this->get_conex('negocio');
        try {
            $pdo->beginTransaction();

            $sql1 = "INSERT INTO pagos (estado, observacion, activo) VALUES ('PENDIENTE', :obs, 1)";
            $stmt1 = $pdo->prepare($sql1);
            $obs = $this->observacion ?? 'Pago registrado por sistema';
            $stmt1->execute([':obs' => $obs]);
            $id_pago_new = $pdo->lastInsertId();

            $sql2 = "INSERT INTO detalles_pagos (fecha, monto, monto_dolar, tipo_pago, pago_id) 
                     VALUES (:fecha, :monto, :md, :tipo, :pago_id)";
            $stmt2 = $pdo->prepare($sql2);
            $fecha = $this->fecha ?? date('Y-m-d');
            $md = $this->monto_dolar ?? 0;
            $stmt2->execute([
                ':fecha' => $fecha,
                ':monto' => $this->monto,
                ':md' => $md,
                ':tipo' => $this->tipo_pago,
                ':pago_id' => $id_pago_new
            ]);
            $id_detalle_new = $pdo->lastInsertId();

            if (in_array($this->tipo_pago, ['Transferencia', 'Pago Movil'])) {
                $sql3 = "INSERT INTO ingresos_bancarios (referencia, imagen, detalle_pago_id, banco_id) 
                         VALUES (:ref, :img, :det_id, :banco_id)";
                $stmt3 = $pdo->prepare($sql3);
                $img = $this->imagen ?? 'default.png';
                $stmt3->execute([
                    ':ref' => $this->referencia,
                    ':img' => $img,
                    ':det_id' => $id_detalle_new,
                    ':banco_id' => $this->banco_id
                ]);
            }

            $sql4 = "INSERT INTO pagos_mensualidad (detalle_pago_id, mensualidad_id) VALUES (:det_id, :mens_id)";
            $stmt4 = $pdo->prepare($sql4);
            $stmt4->execute([
                ':det_id' => $id_detalle_new,
                ':mens_id' => $this->mensualidad_id
            ]);

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Pago registrado con éxito', 'id' => $id_pago_new];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Error en _registrar_pago_completo: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    private function _editar_pago()
    {
        $valIds = $this->validar(['id_pago', 'id_detalle_pago']);
        if (!$valIds['estatus']) return $valIds;

        $campos = ['monto', 'tipo_pago', 'fecha', 'monto_dolar'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) return $validacion;

        $pdo = $this->get_conex('negocio');
        try {
            $pdo->beginTransaction();

            if ($this->observacion !== null) {
                $sql1 = "UPDATE pagos SET observacion = :obs WHERE id_pago = :id";
                $stmt1 = $pdo->prepare($sql1);
                $stmt1->execute([':obs' => $this->observacion, ':id' => $this->id_pago]);
            }

            $sql2 = "UPDATE detalles_pagos SET fecha = :fecha, monto = :monto, monto_dolar = :md 
                     WHERE id_detalle_pago = :id_det";
            $stmt2 = $pdo->prepare($sql2);
            $md = $this->monto_dolar ?? 0;
            $stmt2->execute([
                ':fecha' => $this->fecha,
                ':monto' => $this->monto,
                ':md' => $md,
                ':id_det' => $this->id_detalle_pago
            ]);

            if (in_array($this->tipo_pago, ['Transferencia', 'Pago Movil'])) {
                if (!empty($this->imagen) && $this->imagen !== 'default.png') {
                    $imgAnterior = $this->obtenerNombreImagenPorDetalle($this->id_detalle_pago);
                    if ($imgAnterior && $imgAnterior !== 'default.png') {
                        $this->eliminarArchivoFisico($imgAnterior);
                    }
                    $sql3 = "UPDATE ingresos_bancarios SET referencia = :ref, banco_id = :banco, imagen = :img 
                             WHERE detalle_pago_id = :det_id";
                } else {
                    $sql3 = "UPDATE ingresos_bancarios SET referencia = :ref, banco_id = :banco 
                             WHERE detalle_pago_id = :det_id";
                }
                $stmt3 = $pdo->prepare($sql3);
                $stmt3->bindParam(':ref', $this->referencia);
                $stmt3->bindParam(':banco', $this->banco_id);
                if (!empty($this->imagen) && $this->imagen !== 'default.png') {
                    $stmt3->bindParam(':img', $this->imagen);
                }
                $stmt3->bindParam(':det_id', $this->id_detalle_pago);
                $stmt3->execute();
            }

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Pago actualizado correctamente'];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Error en _editar_pago: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al editar: ' . $e->getMessage()];
        }
    }

    private function _registrar()
    {
        if (empty($this->detalles_temp) || !is_array($this->detalles_temp)) {
            return ['estatus' => false, 'mensaje' => 'No se recibieron detalles para el pago'];
        }

        $validacion = $this->validar(['apartamento_id', 'mensualidad_id']);
        if (!$validacion['estatus']) return $validacion;

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
            foreach ($this->detalles_temp as $det) {
                $stmtDet->execute([
                    ':fecha' => $det['fecha'],
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
            return ['estatus' => false, 'mensaje' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Edita un pago completo borrando los detalles viejos y registrando los nuevos
     */
    private function _editar()
    {
        if (empty($this->id_pago)) {
            return ['estatus' => false, 'mensaje' => 'ID de pago no proporcionado'];
        }

        if (empty($this->detalles_temp) || !is_array($this->detalles_temp)) {
            return ['estatus' => false, 'mensaje' => 'No se recibieron detalles para el pago'];
        }

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
            $imagenesAConservar = array_column($this->detalles_temp, 'imagen');

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

            foreach ($this->detalles_temp as $det) {
                $stmtDet->execute([
                    ':fecha' => $det['fecha'],
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
            error_log("Error en _editar pago: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar pago: ' . $e->getMessage()];
        }
    }




    private function _eliminar_pago()
    {
        $validacion = $this->validar(['id_pago']);
        if (!$validacion['estatus']) return $validacion;

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

    // -------------------- OPERACIONES DE DETALLES --------------------

    private function _registrar_detalle()
    {
        $campos = ['fecha', 'monto', 'tipo_pago', 'pago_id', 'mensualidad_id'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) return $validacion;

        $pdo = $this->get_conex('negocio');
        try {
            $pdo->beginTransaction();

            $sqlDet = "INSERT INTO detalles_pagos (fecha, monto, monto_dolar, tipo_pago, pago_id) 
                       VALUES (:fecha, :monto, :md, :tipo, :pago_id)";
            $stmtDet = $pdo->prepare($sqlDet);
            $md = $this->monto_dolar ?? 0;
            $stmtDet->execute([
                ':fecha' => $this->fecha,
                ':monto' => $this->monto,
                ':md' => $md,
                ':tipo' => $this->tipo_pago,
                ':pago_id' => $this->id_pago
            ]);
            $id_detalle = $pdo->lastInsertId();

            if (in_array($this->tipo_pago, ['Transferencia', 'Pago Movil'])) {
                $sqlBanco = "INSERT INTO ingresos_bancarios (referencia, imagen, detalle_pago_id, banco_id) 
                             VALUES (:ref, :img, :det_id, :banco_id)";
                $stmtBanco = $pdo->prepare($sqlBanco);
                $img = $this->imagen ?? 'default.png';
                $stmtBanco->execute([
                    ':ref' => $this->referencia,
                    ':img' => $img,
                    ':det_id' => $id_detalle,
                    ':banco_id' => $this->banco_id
                ]);
            }

            $sqlRel = "INSERT INTO pagos_mensualidad (detalle_pago_id, mensualidad_id) VALUES (:det_id, :mens_id)";
            $stmtRel = $pdo->prepare($sqlRel);
            $stmtRel->execute([
                ':det_id' => $id_detalle,
                ':mens_id' => $this->mensualidad_id
            ]);

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Detalle registrado', 'id_detalle' => $id_detalle];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Error en _registrar_detalle: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar detalle'];
        }
    }

    private function _editar_detalle()
    {
        $campos = ['id_detalle_pago', 'fecha', 'monto', 'tipo_pago', 'pago_id', 'mensualidad_id'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) return $validacion;

        $pdo = $this->get_conex('negocio');
        try {
            $pdo->beginTransaction();

            $sqlDet = "UPDATE detalles_pagos SET fecha = :fecha, monto = :monto, monto_dolar = :md, tipo_pago = :tipo 
                       WHERE id_detalle_pago = :id_det";
            $stmtDet = $pdo->prepare($sqlDet);
            $md = $this->monto_dolar ?? 0;
            $stmtDet->execute([
                ':fecha' => $this->fecha,
                ':monto' => $this->monto,
                ':md' => $md,
                ':tipo' => $this->tipo_pago,
                ':id_det' => $this->id_detalle_pago
            ]);

            if (in_array($this->tipo_pago, ['Transferencia', 'Pago Movil'])) {
                $sqlCheck = "SELECT id_ingreso_bancario FROM ingresos_bancarios WHERE detalle_pago_id = :det_id";
                $stmtCheck = $pdo->prepare($sqlCheck);
                $stmtCheck->execute([':det_id' => $this->id_detalle_pago]);
                $existe = $stmtCheck->fetchColumn();

                if ($existe) {
                    $sqlBanco = "UPDATE ingresos_bancarios SET referencia = :ref, imagen = :img, banco_id = :banco 
                                 WHERE detalle_pago_id = :det_id";
                } else {
                    $sqlBanco = "INSERT INTO ingresos_bancarios (referencia, imagen, detalle_pago_id, banco_id) 
                                 VALUES (:ref, :img, :det_id, :banco)";
                }
                $stmtBanco = $pdo->prepare($sqlBanco);
                $img = $this->imagen ?? 'default.png';
                $stmtBanco->execute([
                    ':ref' => $this->referencia,
                    ':img' => $img,
                    ':det_id' => $this->id_detalle_pago,
                    ':banco' => $this->banco_id
                ]);
            } else {
                $sqlDelBanco = "DELETE FROM ingresos_bancarios WHERE detalle_pago_id = :det_id";
                $stmtDel = $pdo->prepare($sqlDelBanco);
                $stmtDel->execute([':det_id' => $this->id_detalle_pago]);
            }

            $sqlRel = "UPDATE pagos_mensualidad SET mensualidad_id = :mens_id WHERE detalle_pago_id = :det_id";
            $stmtRel = $pdo->prepare($sqlRel);
            $stmtRel->execute([
                ':mens_id' => $this->mensualidad_id,
                ':det_id' => $this->id_detalle_pago
            ]);

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Detalle actualizado'];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Error en _editar_detalle: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al editar detalle'];
        }
    }

    private function _eliminar_detalle()
    {
        $validacion = $this->validar(['id_detalle_pago']);
        if (!$validacion['estatus']) return $validacion;

        $pdo = $this->get_conex('negocio');
        try {
            $pdo->beginTransaction();

            $sqlBanco = "DELETE FROM ingresos_bancarios WHERE detalle_pago_id = :det_id";
            $stmtBanco = $pdo->prepare($sqlBanco);
            $stmtBanco->execute([':det_id' => $this->id_detalle_pago]);

            $sqlRel = "DELETE FROM pagos_mensualidad WHERE detalle_pago_id = :det_id";
            $stmtRel = $pdo->prepare($sqlRel);
            $stmtRel->execute([':det_id' => $this->id_detalle_pago]);

            $sqlDet = "DELETE FROM detalles_pagos WHERE id_detalle_pago = :det_id";
            $stmtDet = $pdo->prepare($sqlDet);
            $stmtDet->execute([':det_id' => $this->id_detalle_pago]);

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Detalle eliminado'];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Error en _eliminar_detalle: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar detalle'];
        }
    }

    // -------------------- OPERACIONES DE PAGO CON MÚLTIPLES DETALLES --------------------

    private function _registrar_pago_con_detalles()
    {
        // Se espera que las propiedades fecha, monto, tipo_pago, etc. sean arrays
        if (!isset($this->fecha) || !is_array($this->fecha) || empty($this->fecha)) {
            return ['estatus' => false, 'mensaje' => 'No se recibieron detalles para el pago'];
        }

        $pdo = $this->get_conex('negocio');
        try {
            $pdo->beginTransaction();

            $sqlHead = "INSERT INTO pagos (estado, observacion, activo) VALUES ('PENDIENTE', :obs, 1)";
            $stmtHead = $pdo->prepare($sqlHead);
            $obs = $this->observacion ?? 'Pago registrado por sistema';
            $stmtHead->execute([':obs' => $obs]);
            $id_pago = $pdo->lastInsertId();

            $indice_imagen = 0;
            foreach ($this->fecha as $i => $fecha) {
                $monto = $this->monto[$i] ?? 0;
                $tipo = $this->tipo_pago[$i] ?? '';
                $monto_dolar = $this->monto_dolar[$i] ?? 0;
                $mensualidad_id = is_array($this->mensualidad_id) ? $this->mensualidad_id[$i] : $this->mensualidad_id;

                $sqlDet = "INSERT INTO detalles_pagos (fecha, monto, monto_dolar, tipo_pago, pago_id) 
                           VALUES (:fecha, :monto, :md, :tipo, :pago_id)";
                $stmtDet = $pdo->prepare($sqlDet);
                $stmtDet->execute([
                    ':fecha' => $fecha,
                    ':monto' => $monto,
                    ':md' => $monto_dolar,
                    ':tipo' => $tipo,
                    ':pago_id' => $id_pago
                ]);
                $id_detalle = $pdo->lastInsertId();

                if (in_array($tipo, ['Transferencia', 'Pago Movil'])) {
                    $referencia = $this->referencia[$i] ?? '';
                    $banco_id = $this->banco_id[$i] ?? null;
                    $imagen = '';
                    if (isset($this->imagen[$indice_imagen])) {
                        $imagen = $this->imagen[$indice_imagen];
                        $indice_imagen++;
                    }
                    $sqlBanco = "INSERT INTO ingresos_bancarios (referencia, imagen, detalle_pago_id, banco_id) 
                                 VALUES (:ref, :img, :det_id, :banco)";
                    $stmtBanco = $pdo->prepare($sqlBanco);
                    $stmtBanco->execute([
                        ':ref' => $referencia,
                        ':img' => $imagen ?: 'default.png',
                        ':det_id' => $id_detalle,
                        ':banco' => $banco_id
                    ]);
                }

                $sqlRel = "INSERT INTO pagos_mensualidad (detalle_pago_id, mensualidad_id) VALUES (:det_id, :mens_id)";
                $stmtRel = $pdo->prepare($sqlRel);
                $stmtRel->execute([
                    ':det_id' => $id_detalle,
                    ':mens_id' => $mensualidad_id
                ]);
            }

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Pago registrado con éxito', 'id' => $id_pago];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Error en _registrar_pago_con_detalles: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    private function _editar_pago_con_detalles()
    {
        if (empty($this->id_pago)) {
            return ['estatus' => false, 'mensaje' => 'ID de pago no proporcionado'];
        }

        $pdo = $this->get_conex('negocio');
        try {
            $pdo->beginTransaction();

            $sqlHead = "UPDATE pagos SET estado = :estado, observacion = :obs WHERE id_pago = :id";
            $stmtHead = $pdo->prepare($sqlHead);
            $stmtHead->execute([
                ':estado' => $this->estado,
                ':obs' => $this->observacion,
                ':id' => $this->id_pago
            ]);

            // Obtener detalles antiguos para eliminar imágenes
            $sqlGetDet = "SELECT id_detalle_pago FROM detalles_pagos WHERE pago_id = :id";
            $stmtGet = $pdo->prepare($sqlGetDet);
            $stmtGet->execute([':id' => $this->id_pago]);
            $detallesViejos = $stmtGet->fetchAll(PDO::FETCH_COLUMN);

            foreach ($detallesViejos as $id_det) {
                $img = $this->obtenerNombreImagenPorDetalle($id_det);
                if ($img && $img !== 'default.png') {
                    GestorImagenes::eliminar($img, 'pagos');
                }
            }

            // Eliminar relaciones y detalles
            $sqlDelBanco = "DELETE FROM ingresos_bancarios WHERE detalle_pago_id IN (SELECT id_detalle_pago FROM detalles_pagos WHERE pago_id = :id)";
            $pdo->prepare($sqlDelBanco)->execute([':id' => $this->id_pago]);
            $sqlDelRel = "DELETE FROM pagos_mensualidad WHERE detalle_pago_id IN (SELECT id_detalle_pago FROM detalles_pagos WHERE pago_id = :id)";
            $pdo->prepare($sqlDelRel)->execute([':id' => $this->id_pago]);
            $sqlDelDet = "DELETE FROM detalles_pagos WHERE pago_id = :id";
            $pdo->prepare($sqlDelDet)->execute([':id' => $this->id_pago]);

            // Insertar nuevos detalles
            if (!isset($this->fecha) || !is_array($this->fecha) || empty($this->fecha)) {
                throw new Exception('No se recibieron detalles para el pago');
            }

            $indice_imagen = 0;
            foreach ($this->fecha as $i => $fecha) {
                $monto = $this->monto[$i] ?? 0;
                $tipo = $this->tipo_pago[$i] ?? '';
                $monto_dolar = $this->monto_dolar[$i] ?? 0;
                $mensualidad_id = is_array($this->mensualidad_id) ? $this->mensualidad_id[$i] : $this->mensualidad_id;

                $sqlDet = "INSERT INTO detalles_pagos (fecha, monto, monto_dolar, tipo_pago, pago_id) 
                           VALUES (:fecha, :monto, :md, :tipo, :pago_id)";
                $stmtDet = $pdo->prepare($sqlDet);
                $stmtDet->execute([
                    ':fecha' => $fecha,
                    ':monto' => $monto,
                    ':md' => $monto_dolar,
                    ':tipo' => $tipo,
                    ':pago_id' => $this->id_pago
                ]);
                $id_detalle = $pdo->lastInsertId();

                if (in_array($tipo, ['Transferencia', 'Pago Movil'])) {
                    $referencia = $this->referencia[$i] ?? '';
                    $banco_id = $this->banco_id[$i] ?? null;
                    $imagen = '';
                    if (isset($this->imagen[$indice_imagen])) {
                        $imagen = $this->imagen[$indice_imagen];
                        $indice_imagen++;
                    }
                    $sqlBanco = "INSERT INTO ingresos_bancarios (referencia, imagen, detalle_pago_id, banco_id) 
                                 VALUES (:ref, :img, :det_id, :banco)";
                    $stmtBanco = $pdo->prepare($sqlBanco);
                    $stmtBanco->execute([
                        ':ref' => $referencia,
                        ':img' => $imagen ?: 'default.png',
                        ':det_id' => $id_detalle,
                        ':banco' => $banco_id
                    ]);
                }

                $sqlRel = "INSERT INTO pagos_mensualidad (detalle_pago_id, mensualidad_id) VALUES (:det_id, :mens_id)";
                $stmtRel = $pdo->prepare($sqlRel);
                $stmtRel->execute([
                    ':det_id' => $id_detalle,
                    ':mens_id' => $mensualidad_id
                ]);
            }

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Pago actualizado con éxito'];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Error en _editar_pago_con_detalles: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar pago: ' . $e->getMessage()];
        }
    }

    // -------------------- OTROS --------------------

    private function _validar_referencia()
    {
        if (empty($this->referencia)) {
            return ['estatus' => false, 'mensaje' => 'Referencia no proporcionada'];
        }
        $sql = "SELECT COUNT(*) as total FROM ingresos_bancarios WHERE referencia = :ref";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':ref' => $this->referencia]);
            $existe = $stmt->fetchColumn() > 0;
            return ['estatus' => true, 'existe' => $existe];
        } catch (PDOException $e) {
            error_log("Error en _validar_referencia: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al validar referencia'];
        }
    }

    private function _lastId()
    {
        $sql = "SELECT MAX(id_pago) as last_id FROM pagos";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $dato = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $dato];
        } catch (PDOException $e) {
            error_log("Error en _lastId: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener último ID'];
        }
    }

    private function _lastIdDetalle()
    {
        $sql = "SELECT MAX(id_detalle_pago) as last_id FROM detalles_pagos";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $dato = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $dato];
        } catch (PDOException $e) {
            error_log("Error en _lastIdDetalle: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener último ID de detalle'];
        }
    }

    private function _consultarReciboPago()
    {
        // Ya existente
        $validacion = $this->validar(['id_pago']);
        if (!$validacion['estatus']) return $validacion;

        $sql = "SELECT 
                    h.nombre, h.apellido, a.nro_apartamento,
                    MAX(dp.fecha) as fecha_pago, m.mes, m.anio, p.id_pago,
                    SUM(dp.monto) as total,
                    COUNT(CASE WHEN dp.tipo_pago = 'Transferencia' THEN 1 END) as count_transferencia,
                    COUNT(CASE WHEN dp.tipo_pago = 'Pago Movil' THEN 1 END) as count_pago_movil,
                    COUNT(CASE WHEN dp.tipo_pago = 'Efectivo' THEN 1 END) as count_efectivo,
                    GROUP_CONCAT(DISTINCT b.nombre_banco SEPARATOR ', ') as bancos,
                    GROUP_CONCAT(DISTINCT ib.referencia SEPARATOR ', ') as referencias
                FROM pagos p
                JOIN detalles_pagos dp ON p.id_pago = dp.pago_id
                JOIN pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
                JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
                JOIN apartamentos a ON m.apartamento_id = a.id_apartamento
                JOIN habitantes_apartamentos ha ON a.id_apartamento = ha.apartamento_id
                JOIN habitantes h ON ha.habitante_id = h.id_habitante
                LEFT JOIN ingresos_bancarios ib ON dp.id_detalle_pago = ib.detalle_pago_id
                LEFT JOIN bancos b ON ib.banco_id = b.id_banco
                WHERE p.id_pago = :id_pago AND ha.tipo_vinculo = 'Propietario'
                GROUP BY p.id_pago";
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

    public function borrarImagenAsociada($id_detalle_pago)
    {
        $nombre = $this->obtenerNombreImagenPorDetalle($id_detalle_pago);
         return GestorImagenes::eliminar($nombre, 'pagos');
    }
}