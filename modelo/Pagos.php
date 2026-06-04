<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\enums\EstadoPago; 
use haydee\enums\MetodoPago;
use haydee\enums\TipoBaseDatos;

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
    private $tasa_dolar;
    private $tipo_pago;   // Transferencia, Pago Movil, Efectivo, Divisa

    private $referencia;
    private $imagen;
    private $banco_id;

    private $mensualidad_id;
    private $apartamento_id; // Para consultas por apartamento
    private $correo;

    private $detalles = [];
    private $monto_mensualidad; // Añadido para mantener registro si es necesario

    /**
     * Reglas para la tabla principal (Cabecera del Pago)
     */
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

        // Mapeo unificado de operaciones y verbos HTTP correspondientes
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

    /**
     * Reglas para cada fila de la tabla de detalles (Detalles Pagos)
     */
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
    public function set_tasa_dolar($md) { $this->tasa_dolar = $md; }
    public function get_tasa_dolar() { return $this->tasa_dolar; }
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
            return $param !== null ? $this->$metodo($param) : $this->$metodo();
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
        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        $refsUsadas = [];
        
        $sql = "SELECT dp.pago_id FROM ingresos_bancarios ib 
                JOIN detalles_pagos dp ON ib.detalle_pago_id = dp.id_detalle_pago 
                WHERE ib.referencia = :ref LIMIT 1";
        $stmt = $pdo->prepare($sql); 

        foreach ($this->detalles as $idx => $det) {
            if (!empty($det['referencia'])) {
                $ref = trim($det['referencia']);
                
                if (in_array($ref, $refsUsadas)) {
                    return ['estatus' => false, 'mensaje' => "La referencia '$ref' está repetida en el renglón " . ($idx + 1) . "."];
                }
                $refsUsadas[] = $ref;

                // 2. Dentro del ciclo SOLO ejecutas con el nuevo valor
                $stmt->execute([':ref' => $ref]); // <--- Reutiliza el plan de ejecución precompilado
                $pago_id_bd = $stmt->fetchColumn();

                if ($pago_id_bd) {
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
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
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

    // MÉTODOS PÚBLICOS AUXILIARES (mantener compatibilidad)

    /**
     * Consulta mensualidades pendientes de un apartamento
     // SE USA EN EL MODULO
     */
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
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->bindParam(':id_apartamento', $this->apartamento_id, PDO::PARAM_INT);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultarMensualidadPendiente: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar mensualidades pendientes'];
        }
    }

    // -------------------- CONSULTAS --------------------
    // SE USA EN EL MODULO
    private function _consultar()
    {
        $sql = "SELECT * FROM vw_historial_pagos ORDER BY ultima_fecha DESC";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) { 
            error_log("Error en _consultar_pagos: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar pagos'];
        }
    }

    // SE USA EN EL MODULO (WEB Y APP GENERAL)
    private function _consultar_por_correo()
    {
        // En peticiones GET, $this->correo suele ser nulo por el firewall.
        // Hacemos un fallback seguro para extraerlo de la sesión o del objeto inyectado.
        $correoFiltro = $this->correo ?? ($_SESSION['usuario'] ?? null);

        if (!$correoFiltro) {
            return ['estatus' => false, 'mensaje' => 'Correo no disponible en sesión o parámetros'];
        }

        // Aplicamos la misma arquitectura INNER JOIN segura para esta consulta
        $sql = "SELECT DISTINCT v.* FROM vw_historial_pagos v
                INNER JOIN pagos_mensualidad pm2 ON v.id_pago = pm2.pago_id
                INNER JOIN mensualidad m2 ON pm2.mensualidad_id = m2.id_mensualidad
                INNER JOIN habitantes_apartamentos ha2 ON m2.apartamento_id = ha2.apartamento_id
                INNER JOIN habitantes h2 ON ha2.habitante_id = h2.id_habitante
                WHERE h2.correo = :correo
                ORDER BY v.ultima_fecha DESC";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([':correo' => $correoFiltro]);
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
            // Cabecera del pago
            $sqlHead = "SELECT
                    p.*,
                    pm.mensualidad_id,
                    pm.monto_abonado,
                    m.apartamento_id,
                    m.monto AS monto_mensualidad,
                    a.nro_apartamento
                FROM pagos p
                LEFT JOIN pagos_mensualidad pm ON p.id_pago = pm.pago_id
                LEFT JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
                LEFT JOIN apartamentos a ON a.id_apartamento = m.apartamento_id
                WHERE p.id_pago = :id AND p.activo = 1 LIMIT 1";
            
            $stmtH = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sqlHead);
            $stmtH->execute([':id' => $this->id_pago]);
            $cabecera = $stmtH->fetch(PDO::FETCH_ASSOC);

            if (!$cabecera) return ['estatus' => false, 'mensaje' => 'Pago no encontrado'];

            // Detalles del pago
            $sqlDet = "SELECT dp.*, p.tasa_dolar, ib.referencia, ib.banco_id, ib.imagen, b.nombre_banco
                       FROM detalles_pagos dp
                       JOIN pagos p ON dp.pago_id = p.id_pago
                       LEFT JOIN ingresos_bancarios ib ON dp.id_detalle_pago = ib.detalle_pago_id
                       LEFT JOIN bancos b ON ib.banco_id = b.id_banco
                       WHERE dp.pago_id = :id";
            $stmtD = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sqlDet);
            $stmtD->execute([':id' => $this->id_pago]);
            $detalles = $stmtD->fetchAll(PDO::FETCH_ASSOC);

            $cabecera['detalles'] = $detalles;

            return ['estatus' => true, 'datos' => $cabecera];
        } catch (PDOException $e) {
            error_log("Error en _consultar_pago: " . $e->getMessage());
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
        
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
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

        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $pdo->beginTransaction();

            $tasa_transaccion = $this->tasa_dolar ?? 1;
            // Insertar Cabecera
            $sqlHead = "INSERT INTO pagos (estado, observacion, tasa_dolar, activo) VALUES (:est, :obs, :tasa, 1)";
            $stmtHead = $pdo->prepare($sqlHead);
            $estado = $this->estado ?? 'PENDIENTE';
            $obs = $this->observacion ?? 'Pago registrado por sistema';
            $stmtHead->execute([':est' => $estado, ':obs' => $obs, ':tasa' => $tasa_transaccion]);
            $id_pago = $pdo->lastInsertId();

            // Preparar consultas para los detalles físicos
            $sqlDet = "INSERT INTO detalles_pagos (fecha, monto, tipo_pago, pago_id) 
                       VALUES (:fecha, :monto, :tipo, :pago_id)";
            $stmtDet = $pdo->prepare($sqlDet);

            $sqlBanco = "INSERT INTO ingresos_bancarios (referencia, imagen, detalle_pago_id, banco_id) 
                         VALUES (:ref, :img, :det_id, :banco)";
            $stmtBanco = $pdo->prepare($sqlBanco);

            // Calcular el total del dinero físico ingresado para distribuirlo
            $total_abonado = 0;

            // Iterar e insertar los detalles físicos
            foreach ($this->detalles as $det) {
                $stmtDet->execute([
                    ':fecha' => $det['fecha_pago'],
                    ':monto' => $det['monto'],
                    ':tipo'  => $det['tipo_pago'],
                    ':pago_id' => $id_pago
                ]);
                $id_detalle = $pdo->lastInsertId();

                // Sumamos al total que el residente está abonando en este pago maestro
                $total_abonado += (float)$det['monto'];

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
            }

            // Distribución en Cascada (Waterfall)
            $remanente = $total_abonado;

            // Buscar la mensualidad seleccionada y los meses posteriores pendientes del mismo apartamento
            $sqlDeudas = "SELECT 
                            m.id_mensualidad, 
                            (m.monto - COALESCE((
                                SELECT SUM(pm.monto_abonado)
                                FROM pagos_mensualidad pm
                                JOIN pagos p ON pm.pago_id = p.id_pago
                                WHERE pm.mensualidad_id = m.id_mensualidad 
                                  AND p.activo = 1 
                                  AND UPPER(p.estado) = 'PROCESADO'
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
                ':mens_id_inicio' => $this->mensualidad_id
            ]);
            $meses_pendientes = $stmtDeudas->fetchAll(PDO::FETCH_ASSOC);

            $sqlRel = "INSERT INTO pagos_mensualidad (pago_id, mensualidad_id, monto_abonado) VALUES (:pago_id, :mens_id, :monto_abonado)";
            $stmtRel = $pdo->prepare($sqlRel);

            foreach ($meses_pendientes as $mes) {
                if ($remanente <= 0) break; // Si se acabó el dinero, detenemos la cascada

                $deuda = (float)$mes['deuda_actual'];
                
                // Si este mes ya está solvente, pasamos al siguiente
                if ($deuda <= 0) continue;

                // Definir cuánto le inyectamos a este mes
                $abono_aplicar = ($remanente >= $deuda) ? $deuda : $remanente;

                // Insertar en la tabla puente
                $stmtRel->execute([
                    ':pago_id' => $id_pago, 
                    ':mens_id' => $mes['id_mensualidad'],
                    ':monto_abonado' => $abono_aplicar
                ]);

                // Descontar del dinero disponible
                $remanente -= $abono_aplicar;
            }

            // Si el residente adelantó dinero de meses que aún no han sido creados por el administrador
            // Guardamos el remanente en el mes original mediante UPSERT para que le quede como "Saldo a Favor"
            if ($remanente > 0) {
                $sqlRemanente = "INSERT INTO pagos_mensualidad (pago_id, mensualidad_id, monto_abonado) 
                                 VALUES (:pago_id, :mens_id, :monto)
                                 ON DUPLICATE KEY UPDATE monto_abonado = monto_abonado + VALUES(monto_abonado)";
                
                $stmtRemanente = $pdo->prepare($sqlRemanente);
                $stmtRemanente->execute([
                    ':pago_id' => isset($id_pago) ? $id_pago : $this->id_pago, 
                    ':mens_id' => $this->mensualidad_id,
                    ':monto' => $remanente
                ]);
            }
            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Pago registrado con éxito', 'id' => $id_pago];
        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log("Error en _registrar pago: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error del servidor. Intente mas tarde'];
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

        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $pdo->beginTransaction();

            $tasa_transaccion = $this->tasa_dolar ?? 1;
            // Actualizar Cabecera
            $sqlHead = "UPDATE pagos SET estado = :estado, observacion = :obs, tasa_dolar = :tasa WHERE id_pago = :id";
            $stmtHead = $pdo->prepare($sqlHead);
            $stmtHead->execute([
                ':estado' => $this->estado,
                ':obs'    => $this->observacion,
                ':tasa'   => $tasa_transaccion,
                ':id'     => $this->id_pago
            ]);

            // Gestionar las imágenes
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

            // Eliminar relaciones y detalles viejos de la BD
            $pdo->prepare("DELETE FROM ingresos_bancarios WHERE detalle_pago_id IN (SELECT id_detalle_pago FROM detalles_pagos WHERE pago_id = ?)")->execute([$this->id_pago]);
            $pdo->prepare("DELETE FROM pagos_mensualidad WHERE pago_id = ?")->execute([$this->id_pago]); // <-- NUEVA LÓGICA DE BORRADO
            $pdo->prepare("DELETE FROM detalles_pagos WHERE pago_id = ?")->execute([$this->id_pago]);

            // Insertar nuevos detalles
            $sqlDet = "INSERT INTO detalles_pagos (fecha, monto, tipo_pago, pago_id) VALUES (:fecha, :monto, :tipo, :pago_id)";
            $stmtDet = $pdo->prepare($sqlDet);

            $sqlBanco = "INSERT INTO ingresos_bancarios (referencia, imagen, detalle_pago_id, banco_id) VALUES (:ref, :img, :det_id, :banco)";
            $stmtBanco = $pdo->prepare($sqlBanco);

            $total_abonado = 0; // ACUMULADOR

            foreach ($this->detalles as $det) {
                $stmtDet->execute([
                    ':fecha' => $det['fecha_pago'],
                    ':monto' => $det['monto'],
                    ':tipo'  => $det['tipo_pago'],
                    ':pago_id' => $this->id_pago
                ]);
                $id_detalle = $pdo->lastInsertId();

                $total_abonado += (float)$det['monto'];

                $tipo = strtolower(trim($det['tipo_pago']));
                if (in_array($tipo, ['transferencia', 'pago movil', 'pago_movil'])) {
                    $stmtBanco->execute([
                        ':ref'   => $det['referencia'] ?? '',
                        ':img'   => $det['imagen'] ?? 'default.png',
                        ':det_id'=> $id_detalle,
                        ':banco' => $det['banco_id'] ?? null
                    ]);
                }
            }

            // Distribución en Cascada (Waterfall)
            $remanente = $total_abonado;

            // Buscar la mensualidad seleccionada y los meses posteriores pendientes del mismo apartamento
            $sqlDeudas = "SELECT 
                            m.id_mensualidad, 
                            (m.monto - COALESCE((
                                SELECT SUM(pm.monto_abonado)
                                FROM pagos_mensualidad pm
                                JOIN pagos p ON pm.pago_id = p.id_pago
                                WHERE pm.mensualidad_id = m.id_mensualidad 
                                  AND p.activo = 1 
                                  AND UPPER(p.estado) = 'PROCESADO'
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
                ':mens_id_inicio' => $this->mensualidad_id
            ]);
            $meses_pendientes = $stmtDeudas->fetchAll(PDO::FETCH_ASSOC);

            $sqlRel = "INSERT INTO pagos_mensualidad (pago_id, mensualidad_id, monto_abonado) VALUES (:pago_id, :mens_id, :monto_abonado)";
            $stmtRel = $pdo->prepare($sqlRel);

            foreach ($meses_pendientes as $mes) {
                if ($remanente <= 0) break; // Si se acabó el dinero, detenemos la cascada

                $deuda = (float)$mes['deuda_actual'];
                
                // Si este mes ya está solvente, pasamos al siguiente
                if ($deuda <= 0) continue;

                // Definir cuánto le inyectamos a este mes
                $abono_aplicar = ($remanente >= $deuda) ? $deuda : $remanente;

                // Insertar en la tabla puente
                $stmtRel->execute([
                    ':pago_id' => $this->id_pago,
                    ':mens_id' => $mes['id_mensualidad'],
                    ':monto_abonado' => $abono_aplicar
                ]);

                // Descontar del dinero disponible
                $remanente -= $abono_aplicar;
            }

            // Si el residente adelantó dinero de meses que aún no han sido creados por el administrador
            // Guardamos el remanente en el mes original mediante UPSERT para que le quede como "Saldo a Favor"
            if ($remanente > 0) {
                $sqlRemanente = "INSERT INTO pagos_mensualidad (pago_id, mensualidad_id, monto_abonado) 
                                 VALUES (:pago_id, :mens_id, :monto)
                                 ON DUPLICATE KEY UPDATE monto_abonado = monto_abonado + VALUES(monto_abonado)";
                
                $stmtRemanente = $pdo->prepare($sqlRemanente);
                $stmtRemanente->execute([
                    ':pago_id' => isset($id_pago) ? $id_pago : $this->id_pago, 
                    ':mens_id' => $this->mensualidad_id,
                    ':monto' => $remanente
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
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([':id' => $this->id_pago]);
            return ['estatus' => true, 'mensaje' => 'Pago anulado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar_pago: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al anular: ' . $e->getMessage()];
        }
    }

    // EXCLUSIVO PARA LA APP:
    // EXCLUSIVO PARA LA APP:
    private function _consultar_por_mes_anio($params)
    {
        // Casteo estricto para sincronizar con la salida matemática de MONTH()
        $mesFiltro = (int)$params['mes'];
        $anioFiltro = (int)$params['anio'];
        $correoFiltro = $params['correo'] ?? null;

        $ejecucion = [
            ':mes' => $mesFiltro, 
            ':anio' => $anioFiltro
        ];

        if (!empty($correoFiltro)) {
            // Solución al bug del optimizador de MariaDB: 
            // Reemplazamos EXISTS por INNER JOIN directos y DISTINCT para evitar duplicados.
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
            // Consulta limpia para el administrador (sin filtro de correo)
            $sql = "SELECT v.* FROM vw_historial_pagos v 
                    WHERE MONTH(v.ultima_fecha) = :mes AND YEAR(v.ultima_fecha) = :anio 
                    ORDER BY v.ultima_fecha DESC, v.id_pago DESC";
        }

        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute($ejecucion);
            return ['estatus' => true, 'datos' => $stmt->fetchAll(\PDO::FETCH_ASSOC)];
        } catch (\PDOException $e) {
            error_log("Error en _consultar_por_mes_anio: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al filtrar pagos'];
        }
    }

    // REVISAR________
    private function _obtener_periodos_activos()
    {
        // Agrupamos para obtener solo combinaciones únicas, ordenadas del más reciente al más antiguo
        $sql = "SELECT DISTINCT MONTH(dp.fecha) AS mes, YEAR(dp.fecha) AS anio
                FROM detalles_pagos dp
                INNER JOIN pagos p ON dp.pago_id = p.id_pago
                WHERE p.activo = 1
                ORDER BY anio DESC, mes DESC";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (\PDOException $e) {
            error_log("Error en _obtener_periodos_activos (Gastos): " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar períodos'];
        }
    }

    // EXCLUSIVO PARA LA APP:
    private function _consultar_estado_cuenta()
    {
        $params = [];
        
        // Si hay un correo configurado (Propietario), filtramos su deuda personal con JOINs
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
                    WHERE vw.estado_pago = 'PENDIENTE' 
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
                    WHERE vw.estado_pago = 'PENDIENTE'
                    ORDER BY vw.anio ASC, vw.mes ASC";
        }
                
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute($params);
            $datos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (\PDOException $e) {
            error_log("Error en _consultar_estado_cuenta: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al calcular el estado de cuenta.'];
        }
    }

    // EXCLUSIVO PARA LA APP
    private function _cambiar_estado_pago()
    {
        if (empty($this->id_pago) || empty($this->estado)) {
            return ['estatus' => false, 'mensaje' => 'Faltan datos obligatorios (ID o Estado).'];
        }

        $sql = "UPDATE pagos 
                SET estado = :estado, 
                    observacion = :observacion 
                WHERE id_pago = :id_pago AND activo = 1";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([
                ':estado'      => $this->estado, // 'PROCESADO' o 'RECHAZADO'
                ':observacion' => $this->observacion ?? 'Estado actualizado desde la App Móvil',
                ':id_pago'     => $this->id_pago
            ]);

            return [
                'estatus' => true, 
                'mensaje' => "El pago ha sido marcado como " . strtolower($this->estado) . " con éxito."
            ];
        } catch (\PDOException $e) {
            error_log("Error en _cambiar_estado_pago: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error interno al actualizar el estado del pago.'];
        }
    }


    // -----------------------------------------------------------------
    // Helpers de archivos (privados)
    // -----------------------------------------------------------------
    private function obtenerNombreImagenPorDetalle($idDetalle)
    {
        $sql = "SELECT imagen FROM ingresos_bancarios WHERE detalle_pago_id = :id";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([':id' => $idDetalle]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en obtenerNombreImagenPorDetalle: " . $e->getMessage());
            return false;
        }
    }
}