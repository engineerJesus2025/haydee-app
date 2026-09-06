<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\EstadoPago; 
use haydee\enums\MetodoPago;
use haydee\enums\TipoBaseDatos;
use haydee\enums\HttpCodigo;
use haydee\excepciones\NegocioException;

class Pagos extends Conexion
{
    private $id_pago;
    private $estado;
    private $observacion;
    private $activo;

    private $id_detalle_pago;
    private $fecha;
    private $monto;
    private $tasa_dolar;
    private $tipo_pago;

    private $referencia;
    private $imagen;
    private $banco_id;
    private $cuenta_id;

    private $mensualidad_id;
    private $apartamento_id;
    private $correo;

    private $detalles = [];
    private $monto_mensualidad;

    public static function obtenerReglas($operacion) {
        $estadosValidos = implode('|', array_column(EstadoPago::cases(), 'value'));

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
                'regex' => "/^($estadosValidos)$/",
            ],
            'tasa_dolar' => [
                'regex' => '/^\d+(\.\d{1,2})?$/'
            ],
            'mensualidad_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'mensualidad', 'campo' => 'id_mensualidad']
            ],
        ];

        $configPorOperacion = [
            'consulta'                => ['metodo_http' => ['GET'],  'campos' => []],
            'obtener_catalogos_base'  => ['metodo_http' => ['GET'],  'campos' => []],
            'consultar_mensualidades' => ['metodo_http' => ['GET'],  'campos' => ['apartamento_id']],
            'consultar_pago'          => ['metodo_http' => ['GET'],  'campos' => ['id_pago']],
            'listar_pagos_mes'        => ['metodo_http' => ['GET'],  'campos' => []],
            'obtener_periodos'        => ['metodo_http' => ['GET'],  'campos' => []],
            'consultar_estado_cuenta' => ['metodo_http' => ['GET'],  'campos' => []],
            'registrar_pago'          => ['metodo_http' => ['POST'], 'campos' => ['apartamento_id', 'observacion', 'mensualidad_id', 'estado', 'tasa_dolar']],
            'cambiar_estado_pago'     => ['metodo_http' => ['PUT'],  'campos' => ['id_pago', 'estado', 'observacion']],
            'eliminar_pago'           => ['metodo_http' => ['DELETE'], 'campos' => ['id_pago']],
            'modificar_pago'          => ['metodo_http' => ['POST'], 'campos' => ['id_pago', 'apartamento_id', 'observacion', 'mensualidad_id', 'estado', 'tasa_dolar']]
        ];

        if (isset($configPorOperacion[$operacion])) {
            $config = $configPorOperacion[$operacion];
            $reglasFiltradas = array_intersect_key($reglasGenerales, array_flip($config['campos']));
            
            if (isset($config['metodo_http'])) {
                $reglasFiltradas['__metodo_http_permitido__'] = $config['metodo_http'];
            }
            return $reglasFiltradas;
        }
        return [];
    }

    public static function obtenerReglasDetalles() {
        $metodosValidos = implode('|', array_column(MetodoPago::cases(), 'value'));

        return [
            'fecha_pago' => [ 
                'regex' => '/^\d{4}-\d{2}-\d{2}$/'
            ],
            'monto' => [
                'regex' => '/^\d+(\.\d{1,2})?$/',
                'min' => 0.01
            ],
            'tipo_pago' => [
                'regex' => "/^($metodosValidos)$/"
            ],
            'referencia' => [
                'regex' => '/^[a-zA-Z0-9-]{4,20}$/',
                'opcional' => true,
                'requerido_si' => ['tipo_pago' => [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value]]
            ],
            'imagen' => [
                'regex' => '/^[a-zA-Z0-9_.-]+\.(jpg|jpeg|png|gif)$/i',
                'opcional' => true,
                'requerido_si' => ['tipo_pago' => [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value]]
            ],
            'banco_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'bancos', 'campo' => 'id_banco'],
                'opcional' => true,
                'requerido_si' => ['tipo_pago' => [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value]]
            ],
            'cuenta_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'cuentas_condominio', 'campo' => 'id_cuenta'],
                'opcional' => true,
                'requerido_si' => ['tipo_pago' => [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value]]
            ]
        ];
    }

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
    public function set_tasa_dolar($md) { $this->tasa_dolar = $md; }
    public function get_tasa_dolar() { return $this->tasa_dolar; }
    public function set_tipo_pago($t) { $this->tipo_pago = $t; }
    public function get_tipo_pago() { return $this->tipo_pago; }
    public function set_referencia($r) { $this->referencia = $r; }
    public function get_referencia() { return $this->referencia; }
    public function set_imagen($imagen) { $this->imagen = $imagen; }
    public function get_imagen() { return $this->imagen; }
    public function set_banco_id($banco_id) { $this->banco_id = $banco_id; }
    public function get_banco_id() { return $this->banco_id; }
    public function set_cuenta_id($cuenta_id) { $this->cuenta_id = $cuenta_id; }
    public function get_cuenta_id() { return $this->cuenta_id; }
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

    public function resumirDetalles(?array $detalles): array
    {
        if (empty($detalles)) {
            return [
                'cantidad_renglones' => 0,
                'monto_total'        => '0.00',
                'metodos'            => 'Ninguno'
            ];
        }

        $total = 0.0;
        $metodos = [];

        foreach ($detalles as $det) {
            $total += (float) ($det['monto'] ?? 0);
            if (!empty($det['tipo_pago'])) {
                $metodos[] = strtoupper(trim($det['tipo_pago']));
            }
        }

        $metodosUnicos = array_values(array_unique($metodos));
        sort($metodosUnicos);

        return [
            'cantidad_renglones' => count($detalles),
            // Se usa number_format para que "101.00" y 101.00 se comparen como el mismo string
            'monto_total'        => number_format($total, 2, '.', ''),
            'metodos'            => empty($metodosUnicos) ? 'N/A' : implode(', ', $metodosUnicos)
        ];
    }

    public function realizar_consulta($accion, $param = null)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            throw new NegocioException("La acción '$accion' no está implementada.", HttpCodigo::BAD_REQUEST->value);
        }
        return $param !== null ? $this->$metodo($param) : $this->$metodo();
    }

    private function _validar_referencias_unicas(?PDO $pdo = null) 
    {
        $detallesConReferencia = array_filter($this->detalles, function($det) {
            return !empty($det['referencia']);
        });

        if (empty($detallesConReferencia)) {
            return true;
        }

        $pdo = $pdo ?? $this->get_conex(TipoBaseDatos::NEGOCIO);
        $refsUsadas = [];
        
        $sql = "SELECT dp.pago_id FROM ingresos_bancarios ib 
                JOIN detalles_pagos dp ON ib.detalle_pago_id = dp.id_detalle_pago 
                WHERE ib.referencia = :ref 
                LIMIT 1 
                FOR UPDATE";
        $stmt = $pdo->prepare($sql); 

        foreach ($detallesConReferencia as $idx => $det) {
            $ref = trim($det['referencia']);
            
            // Validacion en memoria contra renglones duplicados en la misma petición
            if (in_array($ref, $refsUsadas)) {
                throw new NegocioException("La referencia '$ref' está repetida en el renglón " . ($idx + 1) . ".", HttpCodigo::BAD_REQUEST->value);
            }
            $refsUsadas[] = $ref;

            $stmt->execute([':ref' => $ref]); 
            $pago_id_bd = $stmt->fetchColumn();

            if ($pago_id_bd) {
                if (empty($this->id_pago) || $pago_id_bd != $this->id_pago) {
                    throw new NegocioException("La referencia bancaria '$ref' (Renglón " . ($idx + 1) . ") ya se encuentra registrada en otro pago.", HttpCodigo::BAD_REQUEST->value);
                }
            }
        }
        return true;
    }

    public function verificarReferenciaDisponible($referencia, $id_pago_actual = null) {
        $sql = "SELECT dp.pago_id FROM ingresos_bancarios ib 
                JOIN detalles_pagos dp ON ib.detalle_pago_id = dp.id_detalle_pago 
                WHERE ib.referencia = :ref LIMIT 1";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':ref' => $referencia]);
        $pago_id_bd = $stmt->fetchColumn();
        
        if ($pago_id_bd) {
            if (empty($id_pago_actual) || $pago_id_bd != $id_pago_actual) {
                return true; 
            }
        }
        return false; 
    }

    public function _consultarMensualidadPendiente()
    {
        $sql = "SELECT 
                    id_mensualidad,
                    monto_cuota AS monto,
                    mes,
                    anio,
                    deuda_pendiente AS pendiente
                FROM vw_estado_cuentas_mensualidad
                WHERE apartamento_id = :id_apartamento 
                  AND UPPER(estado_pago) = 'PENDIENTE'
                ORDER BY anio ASC, mes ASC";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':id_apartamento', $this->apartamento_id, PDO::PARAM_INT);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar()
    {
        $sql = "SELECT * FROM vw_historial_pagos ORDER BY ultima_fecha DESC";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_por_correo()
    {
        $correoFiltro = $this->correo ?? ($_SESSION['usuario'] ?? null);

        if (!$correoFiltro) {
            throw new NegocioException('Correo no disponible en sesión o parámetros.', HttpCodigo::BAD_REQUEST->value);
        }

        $sql = "SELECT DISTINCT v.* FROM vw_historial_pagos v
                INNER JOIN pagos_mensualidad pm2 ON v.id_pago = pm2.pago_id
                INNER JOIN mensualidad m2 ON pm2.mensualidad_id = m2.id_mensualidad
                INNER JOIN habitantes_apartamentos ha2 ON m2.apartamento_id = ha2.apartamento_id
                INNER JOIN habitantes h2 ON ha2.habitante_id = h2.id_habitante
                WHERE h2.correo = :correo
                ORDER BY v.ultima_fecha DESC";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':correo' => $correoFiltro]);
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_pago()
    {
        $sqlHead = "SELECT
            p.*,
            pm.mensualidad_id,
            pm.monto_abonado,
            m.apartamento_id,
            m.monto AS monto_mensualidad,
            m.activo AS mensualidad_activa,
            per.id_periodo,
            per.mes,
            per.anio,
            a.nro_apartamento
        FROM pagos p
        LEFT JOIN pagos_mensualidad pm ON p.id_pago = pm.pago_id
        LEFT JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
        LEFT JOIN periodos_mensualidad per ON m.periodo_id = per.id_periodo
        LEFT JOIN apartamentos a ON a.id_apartamento = m.apartamento_id
        WHERE p.id_pago = :id AND p.activo = 1 LIMIT 1";
        
        $stmtH = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sqlHead);
        $stmtH->execute([':id' => $this->id_pago]);
        $cabecera = $stmtH->fetch(PDO::FETCH_ASSOC);

        if (!$cabecera) {
            throw new NegocioException('Pago no encontrado.', HttpCodigo::NO_ENCONTRADO->value);
        }

        if (empty($cabecera['mensualidad_id']) || (isset($cabecera['mensualidad_activa']) && $cabecera['mensualidad_activa'] == 0)) {
            throw new NegocioException('No se pueden preparar los datos de edición. La mensualidad asociada fue eliminada del historial del condominio.', HttpCodigo::BAD_REQUEST->value);
        }

        $sqlDet = "SELECT 
               dp.*, 
               p.tasa_dolar, 
               ib.referencia, 
               ib.banco_id, 
               ib.cuenta_id, 
               ib.imagen, 
               b_emisor.nombre_banco AS banco_emisor,
               CONCAT(b_receptor.nombre_banco, ' - ', cc.numero_cuenta) AS cuenta_receptora
           FROM detalles_pagos dp
           JOIN pagos p ON dp.pago_id = p.id_pago
           LEFT JOIN ingresos_bancarios ib ON dp.id_detalle_pago = ib.detalle_pago_id
           LEFT JOIN bancos b_emisor ON ib.banco_id = b_emisor.id_banco
           LEFT JOIN cuentas_condominio cc ON ib.cuenta_id = cc.id_cuenta
           LEFT JOIN bancos b_receptor ON cc.banco_id = b_receptor.id_banco
           WHERE dp.pago_id = :id";
        $stmtD = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sqlDet);
        $stmtD->execute([':id' => $this->id_pago]);
        $detalles = $stmtD->fetchAll(PDO::FETCH_ASSOC);

        $cabecera['detalles'] = $detalles;

        return ['estatus' => true, 'datos' => $cabecera];
    }

    private function _consultar_cabecera_pago()
    {
        $sql = "SELECT 
                    p.estado, 
                    p.observacion,
                    pm.mensualidad_id,
                    m.apartamento_id
                FROM pagos p
                LEFT JOIN pagos_mensualidad pm ON p.id_pago = pm.pago_id
                LEFT JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
                WHERE p.id_pago = :id_pago AND p.activo = 1
                LIMIT 1";
        
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':id_pago' => $this->id_pago]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$datos) {
            throw new NegocioException('Pago no encontrado.', HttpCodigo::NO_ENCONTRADO->value);
        }
        
        return ['estatus' => true, 'datos' => $datos];
    }

    private function _registrar_pago()
    {
        if (empty($this->detalles) || !is_array($this->detalles)) {
            throw new NegocioException('No se recibieron detalles para el pago.', HttpCodigo::BAD_REQUEST->value);
        }

        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $pdo->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $pdo->beginTransaction();

            $this->_validar_referencias_unicas($pdo);

            $tasa_transaccion = $this->tasa_dolar ?? 1;
            $sqlHead = "INSERT INTO pagos (estado, observacion, tasa_dolar, activo) VALUES (:est, :obs, :tasa, 1)";
            $stmtHead = $pdo->prepare($sqlHead);
            $estado = $this->estado ?? 'PENDIENTE';
            $obs = $this->observacion ?? 'Pago registrado por sistema';
            $stmtHead->execute([':est' => $estado, ':obs' => $obs, ':tasa' => $tasa_transaccion]);
            $id_pago = $pdo->lastInsertId();

            $sqlDet = "INSERT INTO detalles_pagos (fecha, monto, tipo_pago, pago_id) 
                       VALUES (:fecha, :monto, :tipo, :pago_id)";
            $stmtDet = $pdo->prepare($sqlDet);

            $sqlBanco = "INSERT INTO ingresos_bancarios (referencia, imagen, detalle_pago_id, banco_id, cuenta_id) 
             VALUES (:ref, :img, :det_id, :banco, :cuenta)";
            $stmtBanco = $pdo->prepare($sqlBanco);

            $total_abonado = 0;

            foreach ($this->detalles as $det) {
                $stmtDet->execute([
                    ':fecha' => $det['fecha_pago'],
                    ':monto' => $det['monto'],
                    ':tipo'  => $det['tipo_pago'],
                    ':pago_id' => $id_pago
                ]);
                $id_detalle = $pdo->lastInsertId();

                $total_abonado += (float)$det['monto'];

                $tipo = strtoupper(trim($det['tipo_pago']));
                $metodosBancarios = [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value];

                if (in_array($tipo, $metodosBancarios)) {
                    $stmtBanco->execute([
                        ':ref'    => $det['referencia'] ?? '',
                        ':img'    => $det['imagen'] ?? 'default.png',
                        ':det_id' => $id_detalle,
                        ':banco'  => $det['banco_id'] ?? null,
                        ':cuenta' => $det['cuenta_id'] ?? null
                    ]);
                }
            }

            $this->_distribuir_abono_cascada($pdo, $id_pago, $total_abonado);

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Pago registrado con éxito', 'id' => $id_pago];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    private function _modificar_pago()
    {
        if (empty($this->detalles) || !is_array($this->detalles)) {
            throw new NegocioException('No se recibieron detalles para el pago.', HttpCodigo::BAD_REQUEST->value);
        }

        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $pdo->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $pdo->beginTransaction();

            $sqlCheck = "SELECT estado FROM pagos WHERE id_pago = :id LIMIT 1 FOR UPDATE";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([':id' => $this->id_pago]);
            $estadoActual = $stmtCheck->fetchColumn();

            if (!$estadoActual) {
                throw new NegocioException('El pago que intenta modificar no existe.', HttpCodigo::NO_ENCONTRADO->value);
            }

            $this->_validar_referencias_unicas($pdo);

            if (strtoupper($estadoActual) === 'PROCESADO') {
                $sqlIntegridad = "SELECT COUNT(*) 
                                  FROM pagos_mensualidad pm
                                  JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
                                  WHERE pm.pago_id = :id_pago AND m.activo = 1";
                
                $stmtInt = $pdo->prepare($sqlIntegridad);
                $stmtInt->execute([':id_pago' => $this->id_pago]);
                if ($stmtInt->fetchColumn() == 0) {
                    throw new NegocioException('La mensualidad vinculada a este pago procesado ya no se encuentra activa en el historial.', HttpCodigo::BAD_REQUEST->value);
                }
            }

            $tasa_transaccion = $this->tasa_dolar ?? 1;
            $sqlHead = "UPDATE pagos SET estado = :estado, observacion = :obs, tasa_dolar = :tasa WHERE id_pago = :id";
            $stmtHead = $pdo->prepare($sqlHead);
            $stmtHead->execute([
                ':estado' => $this->estado,
                ':obs'    => $this->observacion,
                ':tasa'   => $tasa_transaccion,
                ':id'     => $this->id_pago
            ]);

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

            $pdo->prepare("DELETE FROM ingresos_bancarios WHERE detalle_pago_id IN (SELECT id_detalle_pago FROM detalles_pagos WHERE pago_id = ?)")->execute([$this->id_pago]);
            $pdo->prepare("DELETE FROM pagos_mensualidad WHERE pago_id = ?")->execute([$this->id_pago]);
            $pdo->prepare("DELETE FROM detalles_pagos WHERE pago_id = ?")->execute([$this->id_pago]);

            $sqlDet = "INSERT INTO detalles_pagos (fecha, monto, tipo_pago, pago_id) VALUES (:fecha, :monto, :tipo, :pago_id)";
            $stmtDet = $pdo->prepare($sqlDet);

            $sqlBanco = "INSERT INTO ingresos_bancarios (referencia, imagen, detalle_pago_id, banco_id, cuenta_id) 
             VALUES (:ref, :img, :det_id, :banco, :cuenta)";
            $stmtBanco = $pdo->prepare($sqlBanco);

            $total_abonado = 0;

            foreach ($this->detalles as $det) {
                $stmtDet->execute([
                    ':fecha' => $det['fecha_pago'],
                    ':monto' => $det['monto'],
                    ':tipo'  => $det['tipo_pago'],
                    ':pago_id' => $this->id_pago
                ]);
                $id_detalle = $pdo->lastInsertId();

                $total_abonado += (float)$det['monto'];

                $tipo = strtoupper(trim($det['tipo_pago']));
                $metodosBancarios = [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value];

                if (in_array($tipo, $metodosBancarios)) {
                    $stmtBanco->execute([
                        ':ref'    => $det['referencia'] ?? '',
                        ':img'    => $det['imagen'] ?? 'default.png',
                        ':det_id' => $id_detalle,
                        ':banco'  => $det['banco_id'] ?? null,
                        ':cuenta' => $det['cuenta_id'] ?? null 
                    ]);
                }
            }

            $this->_distribuir_abono_cascada($pdo, $this->id_pago, $total_abonado);

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Pago actualizado con éxito'];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    private function _distribuir_abono_cascada($pdo, $id_pago, $total_abonado)
    {
        $remanente = $total_abonado;
        $estadoProcesado = EstadoPago::PROCESADO->value;

        $sqlDeudas = "SELECT 
                        m.id_mensualidad, 
                        ((m.monto - m.descuento) - COALESCE((
                            SELECT SUM(pm.monto_abonado)
                            FROM pagos_mensualidad pm
                            JOIN pagos p ON pm.pago_id = p.id_pago
                            WHERE pm.mensualidad_id = m.id_mensualidad 
                              AND p.activo = 1 
                              AND UPPER(p.estado) = :estado_procesado
                        ), 0)) as deuda_actual
                    FROM mensualidad m
                    JOIN periodos_mensualidad per ON m.periodo_id = per.id_periodo
                    WHERE m.apartamento_id = :apt_id 
                      AND m.activo = 1
                      AND per.id_periodo >= (SELECT periodo_id FROM mensualidad WHERE id_mensualidad = :mens_id_inicio)
                    ORDER BY per.id_periodo ASC
                    FOR UPDATE;";
        
        $stmtDeudas = $pdo->prepare($sqlDeudas);
        $stmtDeudas->execute([
            ':apt_id' => $this->apartamento_id,
            ':mens_id_inicio' => $this->mensualidad_id,
            ':estado_procesado' => $estadoProcesado
        ]);
        
        $meses_pendientes = $stmtDeudas->fetchAll(PDO::FETCH_ASSOC);

        $stmtRel = $pdo->prepare("INSERT INTO pagos_mensualidad (pago_id, mensualidad_id, monto_abonado) VALUES (:pago_id, :mens_id, :monto_abonado)");

        foreach ($meses_pendientes as $mes) {
            if ($remanente <= 0) break; 

            $deuda = (float)$mes['deuda_actual'];
            if ($deuda <= 0) continue;

            $abono_aplicar = ($remanente >= $deuda) ? $deuda : $remanente;

            $stmtRel->execute([
                ':pago_id' => $id_pago, 
                ':mens_id' => $mes['id_mensualidad'],
                ':monto_abonado' => $abono_aplicar
            ]);

            $remanente -= $abono_aplicar;
        }

        if ($remanente > 0) {
            $stmtRemanente = $pdo->prepare("
                INSERT INTO pagos_mensualidad (pago_id, mensualidad_id, monto_abonado) 
                VALUES (:pago_id, :mens_id, :monto)
                ON DUPLICATE KEY UPDATE monto_abonado = monto_abonado + VALUES(monto_abonado)
            ");
            $stmtRemanente->execute([
                ':pago_id' => $id_pago, 
                ':mens_id' => $this->mensualidad_id,
                ':monto' => $remanente
            ]);
        }
    }

    private function _eliminar_pago()
    {
        $sql = "UPDATE pagos SET activo = 0, estado = '" . EstadoPago::ANULADO->value . "' WHERE id_pago = :id";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':id' => $this->id_pago]);
        return ['estatus' => true, 'mensaje' => 'Pago anulado correctamente'];
    }

    private function _consultar_por_mes_anio($params)
    {
        $mesFiltro = (int)$params['mes'];
        $anioFiltro = (int)$params['anio'];
        $correoFiltro = $params['correo'] ?? null;

        $ejecucion = [
            ':mes' => $mesFiltro, 
            ':anio' => $anioFiltro
        ];

        if (!empty($correoFiltro)) {
            $sql = "SELECT DISTINCT v.* FROM vw_historial_pagos v 
                    INNER JOIN pagos_mensualidad pm2 ON v.id_pago = pm2.pago_id
                    INNER JOIN mensualidad m2 ON pm2.mensualidad_id = m2.id_mensualidad
                    INNER JOIN habitantes_apartamentos ha2 ON m2.apartamento_id = ha2.apartamento_id
                    INNER JOIN habitantes h2 ON ha2.habitante_id = h2.id_habitante
                    WHERE MONTH(v.ultima_fecha) = :mes 
                      AND YEAR(v.ultima_fecha) = :anio 
                      AND h2.correo = :correo
                    ORDER BY v.ultima_fecha DESC, v.id_pago DESC";
            $ejecucion[':correo'] = $correoFiltro;
        } else {
            $sql = "SELECT v.* FROM vw_historial_pagos v 
                    WHERE MONTH(v.ultima_fecha) = :mes AND YEAR(v.ultima_fecha) = :anio 
                    ORDER BY v.ultima_fecha DESC, v.id_pago DESC";
        }

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute($ejecucion);
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _obtener_periodos_activos()
    {
        $sql = "SELECT DISTINCT MONTH(dp.fecha) AS mes, YEAR(dp.fecha) AS anio
                FROM detalles_pagos dp
                INNER JOIN pagos p ON dp.pago_id = p.id_pago
                WHERE p.activo = 1
                ORDER BY anio DESC, mes DESC";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_estado_cuenta()
    {
        $params = [];
        
        if (!empty($this->correo)) {
            $sql = "SELECT 
                        vw.id_mensualidad,
                        vw.monto_cuota AS monto_original,
                        vw.mes,
                        vw.anio,
                        vw.deuda_pendiente AS pendiente,
                        vw.nro_apartamento
                    FROM vw_estado_cuentas_mensualidad vw
                    INNER JOIN habitantes_apartamentos ha ON vw.apartamento_id = ha.apartamento_id
                    INNER JOIN habitantes h ON ha.habitante_id = h.id_habitante
                    WHERE vw.estado_pago = '" . EstadoPago::PENDIENTE->value . "'
                      AND h.correo = :correo
                    ORDER BY vw.anio ASC, vw.mes ASC";
            $params[':correo'] = $this->correo;
        } else {
            $sql = "SELECT 
                        vw.id_mensualidad,
                        vw.monto_cuota AS monto_original,
                        vw.mes,
                        vw.anio,
                        vw.nro_apartamento,
                        vw.deuda_pendiente AS pendiente
                    FROM vw_estado_cuentas_mensualidad vw
                    WHERE vw.estado_pago = '" . EstadoPago::PENDIENTE->value . "'
                    ORDER BY vw.anio ASC, vw.mes ASC";
        }
                
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute($params);
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _cambiar_estado_pago()
    {
        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        
        try {
            $pdo->beginTransaction();

            $sqlCheck = "SELECT estado FROM pagos WHERE id_pago = :id LIMIT 1 FOR UPDATE";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([':id' => $this->id_pago]);
            $estadoActual = $stmtCheck->fetchColumn();

            if (!$estadoActual) {
                throw new NegocioException('El pago especificado no existe.', HttpCodigo::NO_ENCONTRADO->value);
            }

            if (strtoupper($this->estado) === 'PROCESADO') {
                $sqlIntegridad = "SELECT COUNT(*) 
                                  FROM pagos_mensualidad pm
                                  JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
                                  WHERE pm.pago_id = :id_pago AND m.activo = 1";
                
                $stmtInt = $pdo->prepare($sqlIntegridad);
                $stmtInt->execute([':id_pago' => $this->id_pago]);
                $mensualidadesValidas = $stmtInt->fetchColumn();

                if ($mensualidadesValidas == 0) {
                    throw new NegocioException('No se puede procesar el pago porque la mensualidad a la que estaba vinculado fue removida o desactivada.', HttpCodigo::BAD_REQUEST->value);
                }
            }

            $sql = "UPDATE pagos SET estado = :estado, observacion = :observacion WHERE id_pago = :id_pago";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':estado'      => $this->estado,
                ':observacion' => $this->observacion ?? 'Estado actualizado desde la administración',
                ':id_pago'     => $this->id_pago
            ]);

            $pdo->commit();
            
            return [
                'estatus' => true, 
                'mensaje' => "El estado de la transacción ha sido actualizado a " . strtolower($this->estado) . " con éxito."
            ];
            
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    private function obtenerNombreImagenPorDetalle($idDetalle)
    {
        $sql = "SELECT imagen FROM ingresos_bancarios WHERE detalle_pago_id = :id";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':id' => $idDetalle]);
        return $stmt->fetchColumn();
    }
}