<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\ClasificacionGasto;
use haydee\enums\MetodoPago;
use haydee\enums\TipoBaseDatos;
use haydee\enums\HttpCodigo;
use haydee\ayuda\GestorImagenes;
use haydee\excepciones\NegocioException;

class Gastos extends Conexion
{
    private $id_gasto;
    private $clasificacion;
    private $descripcion_gasto;
    private $presupuesto_id;
    private $concepto_id;
    private $proveedor_id;
    private $activo;

    private $id_detalle_gasto;
    private $fecha;
    private $monto;
    private $tasa_dolar;
    private $metodo_pago;

    private $detalles = [];

    private $referencia;
    private $imagen;
    private $cuenta_id;

    public static function obtenerReglas($operacion) {
        $ClasificacionesValidas = implode('|', array_column(ClasificacionGasto::cases(), 'value'));
        $reglasCampos = [
            'id_gasto' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'gastos', 'campo' => 'id_gasto']
            ],
            'clasificacion' => [
                'regex' => "/^($ClasificacionesValidas)$/"
            ],
            'descripcion_gasto' => [
                'regex' => '/^[a-zA-Z0-9áéíóúñÁÉÍÓÚÑ\s.,:\/-]{3,255}$/'
            ],
            'tasa_dolar' => [
                'regex' => '/^\d+(\.\d{1,2})?$/'
            ],
            'presupuesto_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'presupuesto', 'campo' => 'id_presupuesto']
            ],
            'concepto_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'conceptos_gasto', 'campo' => 'id_concepto']
            ],
            'proveedor_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'proveedores', 'campo' => 'id_proveedor']
            ],
            'id_detalle_gasto' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'detalles_gastos', 'campo' => 'id_detalle_gasto']
            ]
        ];

        $configPorOperacion = [
            'consultar' => [
                'metodo_http' => ['GET'],
                'campos' => []
            ],
            'consultar_gasto' => [
                'metodo_http' => ['GET'],
                'campos' => ['id_gasto']
            ],
            'consultar_cabecera_gasto' => [
                'metodo_http' => ['GET'],
                'campos' => ['id_gasto']
            ],
            'consultar_detalles_por_gasto' => [
                'metodo_http' => ['GET'],
                'campos' => ['id_gasto']
            ],
            'consultar_detalle_unico' => [
                'metodo_http' => ['GET'],
                'campos' => ['id_detalle_gasto']  
            ],
            'listar_gastos_mes' => [
                'metodo_http' => ['GET'],
                'campos' => []  
            ],
            'obtener_periodos_activos' => [
                'metodo_http' => ['GET'],
                'campos' => []
            ],
            'total_por_metodo_pago' => [
                'metodo_http' => ['GET'],
                'campos' => []
            ],
            'registrar_gasto' => [
                'metodo_http' => ['POST'],
                'campos' => ['clasificacion', 'descripcion_gasto', 'presupuesto_id', 'concepto_id', 'proveedor_id', 'tasa_dolar']
            ],
            'modificar_gasto' => [
                'metodo_http' => ['PUT', 'POST'],
                'campos' => ['id_gasto', 'clasificacion', 'descripcion_gasto', 'presupuesto_id', 'concepto_id', 'proveedor_id', 'tasa_dolar']
            ],
            'eliminar_gasto' => [
                'metodo_http' => ['DELETE', 'POST'],
                'campos' => ['id_gasto']
            ]
        ];

        if (isset($configPorOperacion[$operacion])) {
            $config = $configPorOperacion[$operacion];
            $reglasFiltradas = array_intersect_key($reglasCampos, array_flip($config['campos']));
            $reglasFiltradas['__metodo_http_permitido__'] = $config['metodo_http'];
            return $reglasFiltradas;
        }

        return [];
    }

    public static function obtenerReglasDetalles() {
        $metodosValidos = implode('|', array_column(MetodoPago::cases(), 'value'));

        return [
            'fecha_detalle' => [
                'regex' => '/^\d{4}-\d{2}-\d{2}$/'
            ],
            'monto' => [
                'regex' => '/^\d+(\.\d{1,2})?$/',
                'min' => 0.01
            ],
            'metodo_pago' => [
                'regex' => "/^($metodosValidos)$/"
            ],
            'referencia' => [
                'regex' => '/^[a-zA-Z0-9-]{4,20}$/',
                'opcional' => true,
                'requerido_si' => ['metodo_pago' => [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value]]
            ],
            'imagen' => [
                'regex' => '/^[a-zA-Z0-9_.-]+\.(jpg|jpeg|png|gif)$/i',
                'opcional' => true,
                'requerido_si' => ['metodo_pago' => [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value]]
            ],
            'cuenta_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'cuentas_condominio', 'campo' => 'id_cuenta'],
                'opcional' => true,
                'requerido_si' => ['metodo_pago' => [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value]]
            ]
        ];
    }

    public function set_id_gasto($id) { $this->id_gasto = $id; }
    public function get_id_gasto() { return $this->id_gasto; }
    public function set_clasificacion($c) { $this->clasificacion = $c; }
    public function get_clasificacion() { return $this->clasificacion; }
    public function set_descripcion_gasto($d) { $this->descripcion_gasto = $d; }
    public function get_descripcion_gasto() { return $this->descripcion_gasto; }
    public function set_presupuesto_id($presupuesto_id) { $this->presupuesto_id = $presupuesto_id; }
    public function get_presupuesto_id() { return $this->presupuesto_id; }
    public function set_concepto_id($t) { $this->concepto_id = $t; }
    public function get_concepto_id() { return $this->concepto_id; }
    public function set_proveedor_id($p) { $this->proveedor_id = $p; }
    public function get_proveedor_id() { return $this->proveedor_id; }

    public function set_id_detalle_gasto($id) { $this->id_detalle_gasto = $id; }
    public function get_id_detalle_gasto() { return $this->id_detalle_gasto; }
    public function set_fecha($f) { $this->fecha = $f; }
    public function get_fecha() { return $this->fecha; }
    public function set_monto($m) { $this->monto = $m; }
    public function get_monto() { return $this->monto; }
    public function set_tasa_dolar($md) { $this->tasa_dolar = $md; }
    public function get_tasa_dolar() { return $this->tasa_dolar; }
    public function set_metodo_pago($mp) { $this->metodo_pago = $mp; }
    public function get_metodo_pago() { return $this->metodo_pago; }

    public function set_referencia($r) { $this->referencia = $r; }
    public function get_referencia() { return $this->referencia; }
    public function set_imagen($i) { $this->imagen = $i; }
    public function get_imagen() { return $this->imagen; }
    public function set_cuenta_id($b) { $this->cuenta_id = $b; }
    public function get_cuenta_id() { return $this->cuenta_id; }

    public function set_activo($activo) { $this->activo = $activo; }
    public function get_activo() { return $this->activo; }

    public function set_detalles($detalles) { $this->detalles = $detalles; }
    public function get_detalles() { return $this->detalles; }

    private function _consultar_auditoria()
    {
        $respuesta = $this->_consultar_gasto();
        if ($respuesta['estatus']) {
            $datos = $respuesta['datos']['gasto'];
            $datos['detalles'] = $respuesta['datos']['detalles'];
            return ['estatus' => true, 'datos' => $datos];
        }
        return $respuesta;
    }

    public function resumirDetalles(?array $detalles): array
    {
        if (empty($detalles)) {
            return ['cantidad_renglones' => 0, 'monto_total' => '0.00', 'metodos' => 'Ninguno'];
        }

        $total = 0.0;
        $metodos = [];
        foreach ($detalles as $det) {
            $total += (float) ($det['monto'] ?? 0);
            if (!empty($det['metodo_pago'])) {
                $metodos[] = strtoupper(trim($det['metodo_pago']));
            }
        }

        $metodosUnicos = array_values(array_unique($metodos));
        sort($metodosUnicos);

        return [
            'cantidad_renglones' => count($detalles),
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

    private function _validar_referencias_unicas() {
        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        $refsUsadas = [];
        
        foreach ($this->detalles as $idx => $det) {
            if (!empty($det['referencia'])) {
                $ref = trim($det['referencia']);
                
                if (in_array($ref, $refsUsadas)) {
                    throw new NegocioException("La referencia '$ref' está repetida en el renglón " . ($idx + 1) . ".", HttpCodigo::BAD_REQUEST->value);
                }
                $refsUsadas[] = $ref;

                $sql = "SELECT dg.gasto_id FROM egresos_bancarios eb 
                        JOIN detalles_gastos dg ON eb.detalle_gasto_id = dg.id_detalle_gasto 
                        WHERE eb.referencia = :ref LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':ref' => $ref]);
                $gasto_id_bd = $stmt->fetchColumn();

                if ($gasto_id_bd) {
                    if (empty($this->id_gasto) || $gasto_id_bd != $this->id_gasto) {
                        throw new NegocioException("La referencia bancaria '$ref' (Renglón " . ($idx + 1) . ") ya se encuentra registrada en otro gasto.", HttpCodigo::BAD_REQUEST->value);
                    }
                }
            }
        }
        return true;
    }

    public function verificarReferenciaDisponible($referencia, $id_gasto_actual = null) {
        $sql = "SELECT dg.gasto_id FROM egresos_bancarios eb 
                JOIN detalles_gastos dg ON eb.detalle_gasto_id = dg.id_detalle_gasto 
                WHERE eb.referencia = :ref LIMIT 1";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':ref' => $referencia]);
        $gasto_id_bd = $stmt->fetchColumn();

        if ($gasto_id_bd) {
            if (empty($id_gasto_actual) || $gasto_id_bd != $id_gasto_actual) {
                return true; 
            }
        }
        return false; 
    }

    private function _registrar_gasto()
    {
        if (empty($this->detalles)) {
            throw new NegocioException('Debe proporcionar al menos un detalle de gasto.', HttpCodigo::BAD_REQUEST->value);
        }

        $this->_validar_referencias_unicas();

        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $pdo->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $pdo->beginTransaction();

            $sqlCab = "INSERT INTO gastos (clasificacion, descripcion_gasto, presupuesto_id, concepto_id, proveedor_id, tasa_dolar, activo) 
                       VALUES (:clas, :desc, :presupuesto_id, :concepto_id, :prov_id, :tasa, 1)";
            $stmtCab = $pdo->prepare($sqlCab);
            $stmtCab->execute([
                ':clas'    => $this->clasificacion,
                ':desc'    => $this->descripcion_gasto,
                ':presupuesto_id' => $this->presupuesto_id,
                ':concepto_id' => $this->concepto_id,
                ':prov_id' => $this->proveedor_id ?: null,
                ':tasa'    => $this->tasa_dolar ?? 1
            ]);
            $id_gasto = $pdo->lastInsertId();

            $this->_guardar_detalles($pdo, $id_gasto);

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Gasto registrado con éxito', 'id' => $id_gasto];

        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    private function _modificar_gasto()
    {
        if (empty($this->detalles)) {
            throw new NegocioException('Debe proporcionar al menos un detalle de gasto.', HttpCodigo::BAD_REQUEST->value);
        }

        $this->_validar_referencias_unicas();

        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $pdo->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
            $pdo->beginTransaction();

            $tasa_transaccion = $this->tasa_dolar ?? 1;

            $sqlCab = "UPDATE gastos SET 
                        clasificacion = :clas,
                        descripcion_gasto = :desc,
                        presupuesto_id = :presupuesto_id,
                        concepto_id = :concepto_id,
                        proveedor_id = :prov_id,
                        tasa_dolar = :tasa
                      WHERE id_gasto = :id";
            $stmtCab = $pdo->prepare($sqlCab);
            $stmtCab->execute([
                ':clas' => $this->clasificacion,
                ':desc' => $this->descripcion_gasto,
                ':presupuesto_id' => $this->presupuesto_id,
                ':concepto_id' => $this->concepto_id,
                ':prov_id' => $this->proveedor_id ?: null,
                ':tasa' => $tasa_transaccion, 
                ':id' => $this->id_gasto
            ]);

            $sqlOldDet = "SELECT id_detalle_gasto FROM detalles_gastos WHERE gasto_id = :id";
            $stmtOld = $pdo->prepare($sqlOldDet);
            $stmtOld->execute([':id' => $this->id_gasto]);
            $oldDetalles = $stmtOld->fetchAll(PDO::FETCH_COLUMN);

            $pdo->prepare("DELETE FROM egresos_bancarios WHERE detalle_gasto_id IN (SELECT id_detalle_gasto FROM detalles_gastos WHERE gasto_id = ?)")->execute([$this->id_gasto]);
            $pdo->prepare("DELETE FROM detalles_gastos WHERE gasto_id = ?")->execute([$this->id_gasto]);

            $this->_guardar_detalles($pdo, $this->id_gasto);

            $sqlNewImages = "SELECT eb.imagen 
                             FROM egresos_bancarios eb 
                             INNER JOIN detalles_gastos dg ON eb.detalle_gasto_id = dg.id_detalle_gasto 
                             WHERE dg.gasto_id = :gasto_id";
            $stmtNew = $pdo->prepare($sqlNewImages);
            $stmtNew->execute([':gasto_id' => $this->id_gasto]);
            $nuevasImagenes = $stmtNew->fetchAll(PDO::FETCH_COLUMN);

            foreach ($oldDetalles as $idDet) {
                $imgAnterior = $this->obtenerImagenPorDetalle($idDet);
                if ($imgAnterior && $imgAnterior !== 'default.png' && !in_array($imgAnterior, $nuevasImagenes)) {
                    GestorImagenes::eliminar($imgAnterior, 'gastos');
                }
            }

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Gasto actualizado con éxito'];

        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    private function _guardar_detalles($pdo, $id_gasto)
    {
        $sqlDet = "INSERT INTO detalles_gastos (fecha, monto, metodo_pago, gasto_id) 
                   VALUES (:fecha, :monto, :metodo, :gasto_id)";
        $stmtDet = $pdo->prepare($sqlDet);

        $sqlBan = "INSERT INTO egresos_bancarios (referencia, imagen, cuenta_id, detalle_gasto_id) 
                   VALUES (:ref, :img, :cuenta, :det_id)";
        $stmtBan = $pdo->prepare($sqlBan);

        $metodosBancarios = [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value];

        foreach ($this->detalles as $det) {
            $stmtDet->execute([
                ':fecha' => $det['fecha_detalle'],
                ':monto' => $det['monto'],
                ':metodo' => $det['metodo_pago'],
                ':gasto_id' => $id_gasto
            ]);
            
            $id_detalle = $pdo->lastInsertId();
            $metodoFormateado = strtoupper(trim($det['metodo_pago']));

            if (in_array($metodoFormateado, $metodosBancarios)) {
                $stmtBan->execute([
                    ':ref'    => $det['referencia'] ?? '',
                    ':img'    => $det['imagen'] ?? 'default.png',
                    ':cuenta'  => $det['cuenta_id'] ?? null,
                    ':det_id' => $id_detalle
                ]);
            }
        }
    }

    private function _eliminar_gasto()
    {
        $sql = "UPDATE gastos SET activo = 0 WHERE id_gasto = :id";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':id', $this->id_gasto);
        $stmt->execute();
        return ['estatus' => true, 'mensaje' => 'Gasto eliminado correctamente'];
    }

    private function _consultar()
    {
        $sql = "SELECT 
            g.id_gasto, 
            g.descripcion_gasto, 
            g.clasificacion, 
            SUM(dg.monto) as monto_total,
            MAX(dg.fecha) as ultima_fecha,
            p.nombre_proveedor as proveedor, 
            cg.nombre_concepto as concepto,
            tg.nombre_tipo_gasto as tipo,
            SUBSTRING_INDEX(GROUP_CONCAT(dg.metodo_pago ORDER BY dg.fecha DESC SEPARATOR ','), ',', 1) as metodo_pago_predominante
        FROM gastos g
        LEFT JOIN detalles_gastos dg ON g.id_gasto = dg.gasto_id
        LEFT JOIN proveedores p ON g.proveedor_id = p.id_proveedor
        LEFT JOIN conceptos_gasto cg ON g.concepto_id = cg.id_concepto
        LEFT JOIN tipo_gasto tg ON cg.tipo_gasto_id = tg.id_tipo_gasto
        WHERE g.activo = 1
        GROUP BY g.id_gasto
        ORDER BY ultima_fecha DESC";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_gasto()
    {
        $sqlCabecera = "SELECT 
                            g.id_gasto,
                            g.clasificacion,
                            g.descripcion_gasto,
                            g.presupuesto_id, 
                            g.concepto_id,
                            g.proveedor_id,
                            g.activo,
                            p.nombre_proveedor,
                            cg.nombre_concepto,
                            tg.nombre_tipo_gasto
                        FROM gastos g
                        LEFT JOIN proveedores p ON g.proveedor_id = p.id_proveedor
                        LEFT JOIN conceptos_gasto cg ON g.concepto_id = cg.id_concepto
                        LEFT JOIN tipo_gasto tg ON cg.tipo_gasto_id = tg.id_tipo_gasto
                        WHERE g.id_gasto = :id_gasto AND g.activo = 1";
        
        $stmtCab = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sqlCabecera);
        $stmtCab->execute([':id_gasto' => $this->id_gasto]);
        $cabecera = $stmtCab->fetch(PDO::FETCH_ASSOC);
        
        if (!$cabecera) {
            throw new NegocioException('Gasto no encontrado.', HttpCodigo::NO_ENCONTRADO->value);
        }

        $sqlDetalles = "SELECT 
                            dg.id_detalle_gasto,
                            dg.fecha,
                            dg.monto,
                            g.tasa_dolar, 
                            dg.metodo_pago,
                            eb.referencia,
                            eb.imagen,
                            eb.cuenta_id,
                            CONCAT(b.nombre_banco, ' - ', cc.numero_cuenta) AS nombre_cuenta
                        FROM detalles_gastos dg
                        JOIN gastos g ON dg.gasto_id = g.id_gasto 
                        LEFT JOIN egresos_bancarios eb ON dg.id_detalle_gasto = eb.detalle_gasto_id
                        LEFT JOIN cuentas_condominio cc ON eb.cuenta_id = cc.id_cuenta
                        LEFT JOIN bancos b ON cc.banco_id = b.id_banco
                        WHERE dg.gasto_id = :id_gasto
                        ORDER BY dg.fecha DESC";
        
        $stmtDet = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sqlDetalles);
        $stmtDet->execute([':id_gasto' => $this->id_gasto]);
        $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        return [
            'estatus' => true,
            'datos' => [
                'gasto' => $cabecera,
                'detalles' => $detalles
            ]
        ];
    }

    private function _consultar_cabecera_gasto()
    {
        $sql = "SELECT clasificacion, descripcion_gasto, presupuesto_id, concepto_id, proveedor_id 
                FROM gastos WHERE id_gasto = :id_gasto AND activo = 1";
        
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':id_gasto' => $this->id_gasto]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datos) {
            throw new NegocioException('Cabecera de gasto no encontrada.', HttpCodigo::NO_ENCONTRADO->value);
        }

        return ['estatus' => true, 'datos' => $datos];
    }

    private function _consultar_detalles_por_gasto()
    {
       $sql = "SELECT 
                    dg.id_detalle_gasto,
                    dg.fecha,
                    dg.monto,
                    g.tasa_dolar,
                    dg.metodo_pago,
                    eb.referencia,
                    eb.imagen,
                    eb.cuenta_id,
                    CONCAT(b.nombre_banco, ' - ', cc.numero_cuenta) AS nombre_cuenta
                FROM detalles_gastos dg
                JOIN gastos g ON dg.gasto_id = g.id_gasto 
                LEFT JOIN egresos_bancarios eb ON dg.id_detalle_gasto = eb.detalle_gasto_id
                LEFT JOIN cuentas_condominio cc ON eb.cuenta_id = cc.id_cuenta
                LEFT JOIN bancos b ON cc.banco_id = b.id_banco
                WHERE dg.gasto_id = :id_gasto
                ORDER BY dg.fecha DESC";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':id_gasto' => $this->id_gasto]);
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_detalle_unico()
    {
        $sql = "SELECT 
                    dg.id_detalle_gasto,
                    dg.fecha,
                    dg.monto,
                    g.tasa_dolar,
                    dg.metodo_pago,
                    dg.gasto_id,
                    eb.referencia,
                    eb.imagen,
                    eb.cuenta_id,
                    CONCAT(b.nombre_banco, ' - ', cc.numero_cuenta) AS nombre_cuenta
                FROM detalles_gastos dg
                JOIN gastos g ON dg.gasto_id = g.id_gasto 
                LEFT JOIN egresos_bancarios eb ON dg.id_detalle_gasto = eb.detalle_gasto_id
                LEFT JOIN cuentas_condominio cc ON eb.cuenta_id = cc.id_cuenta
                LEFT JOIN bancos b ON cc.banco_id = b.id_banco
                WHERE dg.id_detalle_gasto = :id_detalle";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':id_detalle' => $this->id_detalle_gasto]);
        $dato = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$dato) {
            throw new NegocioException('Detalle de gasto no encontrado.', HttpCodigo::NO_ENCONTRADO->value);
        }
        
        return ['estatus' => true, 'datos' => $dato];
    }

    private function _listar_gastos_mes($params)
    {
        $sql = "SELECT 
                    g.id_gasto, 
                    g.descripcion_gasto, 
                    g.clasificacion, 
                    SUM(dg.monto) as monto_total,
                    MAX(dg.fecha) as ultima_fecha,
                    p.nombre_proveedor as proveedor, 
                    cg.nombre_concepto as concepto,
                    tg.nombre_tipo_gasto as tipo,
                    SUBSTRING_INDEX(GROUP_CONCAT(dg.metodo_pago ORDER BY dg.fecha DESC SEPARATOR ','), ',', 1) as metodo_pago_predominante
                FROM gastos g
                LEFT JOIN detalles_gastos dg ON g.id_gasto = dg.gasto_id
                LEFT JOIN proveedores p ON g.proveedor_id = p.id_proveedor
                LEFT JOIN conceptos_gasto cg ON g.concepto_id = cg.id_concepto
                LEFT JOIN tipo_gasto tg ON cg.tipo_gasto_id = tg.id_tipo_gasto
                WHERE g.activo = 1 
                  AND MONTH(dg.fecha) = :mes 
                  AND YEAR(dg.fecha) = :anio
                GROUP BY g.id_gasto
                ORDER BY ultima_fecha DESC";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':mes' => $params['mes'], ':anio' => $params['anio']]);
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _obtener_periodos_activos()
    {
        $sql = "SELECT DISTINCT MONTH(dg.fecha) AS mes, YEAR(dg.fecha) AS anio
                FROM detalles_gastos dg
                INNER JOIN gastos g ON dg.gasto_id = g.id_gasto
                WHERE g.activo = 1
                ORDER BY anio DESC, mes DESC";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }
    
    private function obtenerImagenPorDetalle($idDetalle)
    {
        $sql = "SELECT imagen FROM egresos_bancarios WHERE detalle_gasto_id = :id";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':id', $idDetalle);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}