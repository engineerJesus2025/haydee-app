<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\ayuda\GestorImagenes;
use haydee\enums\ClasificacionGasto;
use haydee\enums\MetodoPago;
use haydee\enums\TipoBaseDatos;

class Gastos extends Conexion
{
    // Tabla: gastos (Cabecera)
    private $id_gasto;
    private $clasificacion;     // 'Fijo' o 'Variable'
    private $descripcion_gasto;
    private $solicitud_id;      // Opcional (FK a solicitudes_gasto)
    private $tipo_gasto_id;     // FK a tipo_gasto
    private $proveedor_id;      // FK a proveedores (opcional)
    private $activo;

    // Tabla: detalles_gastos
    private $id_detalle_gasto;
    private $fecha;
    private $monto;
    private $tasa_dolar;
    private $metodo_pago;       // 'Efectivo', 'Pago Movil', 'Transferencia', 'Divisa'

    private $detalles = [];  // Array de detalles (cada detalle es un array asociativo)

    // Tabla: egresos_bancarios (solo si método bancario)
    private $referencia;
    private $imagen;
    private $banco_id;

    /**
     * Reglas para la tabla principal (Cabecera)
     */
    public static function obtenerReglas($operacion) {
        $clasificacionesValidas = implode('|', array_column(ClasificacionGasto::cases(), 'value'));

        $reglasGenerales = [
            'id_gasto' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'gastos', 'campo' => 'id_gasto']
            ],
            'clasificacion' => [
                'regex' => "/^($clasificacionesValidas)$/"
            ],
            'descripcion_gasto' => [
                'regex' => '/^[a-zA-Z0-9áéíóúñÁÉÍÓÚÑ\s.,:\/-]{3,255}$/'
            ],
            'tasa_dolar' => [
                'regex' => '/^\d+(\.\d{1,2})?$/'
            ],
            'solicitud_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'solicitudes_gasto', 'campo' => 'id_solicitud'],
                'opcional' => true
            ],
            'tipo_gasto_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'tipo_gasto', 'campo' => 'id_tipo_gasto']
            ],
            'proveedor_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'proveedores', 'campo' => 'id_proveedor'],
            ]
        ];

        $camposPorOperacion = [
            'registrar_gasto'           => ['clasificacion', 'descripcion_gasto', 'solicitud_id', 'tipo_gasto_id', 'proveedor_id'],
            'modificar_gasto'           => ['id_gasto', 'clasificacion', 'descripcion_gasto', 'solicitud_id', 'tipo_gasto_id', 'proveedor_id'],
            'eliminar_gasto'            => ['id_gasto'],
            'consulta_especifica_gasto' => ['id_gasto']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    /**
     * Reglas para cada fila de la tabla de detalles (Detalles Gastos)
     */
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
                'requerido_si' => ['metodo_pago' => ['Transferencia', 'Pago Movil']]
            ],
            'imagen' => [
                'regex' => '/^[a-zA-Z0-9_.-]+\.(jpg|jpeg|png|gif)$/i',
                'opcional' => true,
                'requerido_si' => ['metodo_pago' => ['Transferencia', 'Pago Movil']]
            ],
            'banco_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'bancos', 'campo' => 'id_banco'],
                'opcional' => true,
                'requerido_si' => ['metodo_pago' => ['Transferencia', 'Pago Movil']]
            ]
        ];
    }

    // ====================================================================
    // GETTERS Y SETTERS
    // ====================================================================
    public function set_id_gasto($id) { $this->id_gasto = $id; }
    public function get_id_gasto() { return $this->id_gasto; }
    public function set_clasificacion($c) { $this->clasificacion = $c; }
    public function get_clasificacion() { return $this->clasificacion; }
    public function set_descripcion_gasto($d) { $this->descripcion_gasto = $d; }
    public function get_descripcion_gasto() { return $this->descripcion_gasto; }
    public function set_solicitud_id($s) { $this->solicitud_id = $s; }
    public function get_solicitud_id() { return $this->solicitud_id; }
    public function set_tipo_gasto_id($t) { $this->tipo_gasto_id = $t; }
    public function get_tipo_gasto_id() { return $this->tipo_gasto_id; }
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
    public function set_banco_id($b) { $this->banco_id = $b; }
    public function get_banco_id() { return $this->banco_id; }

    public function set_activo($activo) { $this->activo = $activo; }
    public function get_activo() { return $this->activo; }

    public function set_detalles($detalles) { $this->detalles = $detalles; }
    public function get_detalles() { return $this->detalles; }

    // ====================================================================
    // ENRUTADOR CON MANEJO DE EXCEPCIONES
    // ====================================================================
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
     * Verifica que las referencias bancarias no estén duplicadas 
     * en el mismo formulario ni en otros gastos.
     */
    private function _validar_referencias_unicas() {
        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        $refsUsadas = [];
        
        foreach ($this->detalles as $idx => $det) {
            if (!empty($det['referencia'])) {
                $ref = trim($det['referencia']);
                
                // 1. Evitar duplicados en los renglones del mismo formulario
                if (in_array($ref, $refsUsadas)) {
                    return ['estatus' => false, 'mensaje' => "La referencia '$ref' está repetida en el renglón " . ($idx + 1) . "."];
                }
                $refsUsadas[] = $ref;

                // 2. Verificar contra la base de datos (egresos_bancarios y detalles_gastos)
                $sql = "SELECT dg.gasto_id FROM egresos_bancarios eb 
                        JOIN detalles_gastos dg ON eb.detalle_gasto_id = dg.id_detalle_gasto 
                        WHERE eb.referencia = :ref LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':ref' => $ref]);
                $gasto_id_bd = $stmt->fetchColumn();

                if ($gasto_id_bd) {
                    // Comprobamos si pertenece a otro gasto distinto al actual
                    if (empty($this->id_gasto) || $gasto_id_bd != $this->id_gasto) {
                        return ['estatus' => false, 'mensaje' => "La referencia bancaria '$ref' (Renglón " . ($idx + 1) . ") ya se encuentra registrada en otro gasto."];
                    }
                }
            }
        }
        return ['estatus' => true];
    }

    /**
     * Utilidad para el AJAX: Verifica si la referencia está libre.
     */
    public function verificarReferenciaDisponible($referencia, $id_gasto_actual = null) {
        $sql = "SELECT dg.gasto_id FROM egresos_bancarios eb 
                JOIN detalles_gastos dg ON eb.detalle_gasto_id = dg.id_detalle_gasto 
                WHERE eb.referencia = :ref LIMIT 1";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([':ref' => $referencia]);
            $gasto_id_bd = $stmt->fetchColumn();

            if ($gasto_id_bd) {
                if (empty($id_gasto_actual) || $gasto_id_bd != $id_gasto_actual) {
                    return true; // Ocupada
                }
            }
            return false; // Disponible
        } catch (\PDOException $e) {
            return false;
        }
    }

    // SE USA EN EL MODULO
    private function _registrar_gasto()
    {
        // Validar que haya al menos un detalle
        if (empty($this->detalles)) {
            return ['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un detalle de gasto.'];
        }

        // Validar que las referencias sean únicas
        $valRef = $this->_validar_referencias_unicas();
        if (!$valRef['estatus']) return $valRef;

        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $pdo->beginTransaction();

            $tasa_transaccion = $this->tasa_dolar ?? 1;

            // Insertar cabecera
            $sqlCab = "INSERT INTO gastos (clasificacion, descripcion_gasto, solicitud_id, tipo_gasto_id, proveedor_id, tasa_dolar, activo) 
                       VALUES (:clas, :desc, :sol_id, :tipo_id, :prov_id, :tasa, 1)";
            $stmtCab = $pdo->prepare($sqlCab);
            $stmtCab->execute([
                ':clas' => $this->clasificacion,
                ':desc' => $this->descripcion_gasto,
                ':sol_id' => $this->solicitud_id ?: null,
                ':tipo_id' => $this->tipo_gasto_id,
                ':prov_id' => $this->proveedor_id ?: null,
                ':tasa' => $tasa_transaccion 
            ]);
            $id_gasto = $pdo->lastInsertId();

            // Insertar detalles 
            foreach ($this->detalles as $det) {
                $sqlDet = "INSERT INTO detalles_gastos (fecha, monto, metodo_pago, gasto_id) 
                   VALUES (:fecha, :monto, :metodo, :gasto_id)";
                $stmtDet = $pdo->prepare($sqlDet);
                $stmtDet->execute([
                    ':fecha' => $det['fecha_detalle'],
                    ':monto' => $det['monto'],
                    ':metodo' => $det['metodo_pago'],
                    ':gasto_id' => $id_gasto
                ]);
                $id_detalle = $pdo->lastInsertId();

                // Si el método de pago requiere registro bancario
                if (in_array($det['metodo_pago'], ['Transferencia', 'Pago Movil'])) {
                    $imagen = $det['imagen'] ?? 'default.png';
                    $sqlBan = "INSERT INTO egresos_bancarios (referencia, imagen, banco_id, detalle_gasto_id) 
                               VALUES (:ref, :img, :banco, :det_id)";
                    $stmtBan = $pdo->prepare($sqlBan);
                    $stmtBan->execute([
                        ':ref' => $det['referencia'],
                        ':img' => $imagen,
                        ':banco' => $det['banco_id'],
                        ':det_id' => $id_detalle
                    ]);
                }
            }

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Gasto registrado con éxito', 'id' => $id_gasto];

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error en _registrar_gasto: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error del Servidor'];
        }
    }

    // SE USA EN EL MODULO
    private function _modificar_gasto()
    {
        // Validar que haya al menos un detalle
        if (empty($this->detalles)) {
            return ['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un detalle de gasto.'];
        }

        // Validar que las referencias sean únicas
        $valRef = $this->_validar_referencias_unicas();
        if (!$valRef['estatus']) return $valRef;

        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $pdo->beginTransaction();

            $tasa_transaccion = $this->tasa_dolar ?? 1;

            // Actualizar cabecera
            $sqlCab = "UPDATE gastos SET 
                        clasificacion = :clas,
                        descripcion_gasto = :desc,
                        solicitud_id = :sol_id,
                        tipo_gasto_id = :tipo_id,
                        proveedor_id = :prov_id,
                        tasa_dolar = :tasa
                      WHERE id_gasto = :id";
            $stmtCab = $pdo->prepare($sqlCab);
            $stmtCab->execute([
                ':clas' => $this->clasificacion,
                ':desc' => $this->descripcion_gasto,
                ':sol_id' => $this->solicitud_id ?: null,
                ':tipo_id' => $this->tipo_gasto_id,
                ':prov_id' => $this->proveedor_id ?: null,
                ':tasa' => $tasa_transaccion, 
                ':id' => $this->id_gasto
            ]);

            // Obtener los IDs de detalles antiguos (para luego eliminar imágenes)
            $sqlOldDet = "SELECT id_detalle_gasto FROM detalles_gastos WHERE gasto_id = :id";
            $stmtOld = $pdo->prepare($sqlOldDet);
            $stmtOld->execute([':id' => $this->id_gasto]);
            $oldDetalles = $stmtOld->fetchAll(PDO::FETCH_COLUMN);

            // Eliminar registros bancarios y detalles antiguos de la BD
            $pdo->prepare("DELETE FROM egresos_bancarios WHERE detalle_gasto_id IN (SELECT id_detalle_gasto FROM detalles_gastos WHERE gasto_id = ?)")->execute([$this->id_gasto]);
            $pdo->prepare("DELETE FROM detalles_gastos WHERE gasto_id = ?")->execute([$this->id_gasto]);

            // Insertar nuevos detalles
            foreach ($this->detalles as $det) {
                $sqlDet = "INSERT INTO detalles_gastos (fecha, monto, metodo_pago, gasto_id) 
                           VALUES (:fecha, :monto, :metodo, :gasto_id)";
                $stmtDet = $pdo->prepare($sqlDet);
                $stmtDet->execute([
                    ':fecha' => $det['fecha_detalle'],
                    ':monto' => $det['monto'],
                    ':metodo' => $det['metodo_pago'],
                    ':gasto_id' => $this->id_gasto
                ]);
                $id_detalle = $pdo->lastInsertId();

                if (in_array($det['metodo_pago'], ['Transferencia', 'Pago Movil'])) {
                    $imagen = $det['imagen'];
                    $sqlBan = "INSERT INTO egresos_bancarios (referencia, imagen, banco_id, detalle_gasto_id) 
                               VALUES (:ref, :img, :banco, :det_id)";
                    $stmtBan = $pdo->prepare($sqlBan);
                    $stmtBan->execute([
                        ':ref' => $det['referencia'],
                        ':img' => $imagen,
                        ':banco' => $det['banco_id'],
                        ':det_id' => $id_detalle
                    ]);
                }
            }

            // Obtener las imágenes de los nuevos detalles 
            $sqlNewImages = "SELECT eb.imagen 
                             FROM egresos_bancarios eb 
                             INNER JOIN detalles_gastos dg ON eb.detalle_gasto_id = dg.id_detalle_gasto 
                             WHERE dg.gasto_id = :gasto_id";
            $stmtNew = $pdo->prepare($sqlNewImages);
            $stmtNew->execute([':gasto_id' => $this->id_gasto]);
            $nuevasImagenes = $stmtNew->fetchAll(PDO::FETCH_COLUMN);

            // Eliminar imágenes físicas antiguas que no estén en las nuevas
            foreach ($oldDetalles as $idDet) {
                $imgAnterior = $this->obtenerImagenPorDetalle($idDet);
                if ($imgAnterior && $imgAnterior !== 'default.png' && !in_array($imgAnterior, $nuevasImagenes)) {
                    GestorImagenes::eliminar($imgAnterior, 'gastos');
                }
            }

            $pdo->prepare("UPDATE solicitudes_gasto SET estado = 'Procesado' WHERE id_solicitud = ?")->execute([$this->solicitud_id]);

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Gasto actualizado con éxito'];

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error en _modificar_gasto: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Ocurrió un error en el servidor.'];
        }
    }

    /**
     * ELIMINAR GASTO (soft delete)
     // SE USA EN EL MODULO
     */
    private function _eliminar_gasto()
    {
        $sql = "UPDATE gastos SET activo = 0 WHERE id_gasto = :id";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->bindParam(':id', $this->id_gasto);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Gasto eliminado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar_gasto: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar: ' . $e->getMessage()];
        }
    }

    // ====================================================================
    // CONSULTAS
    // ====================================================================

    // SE USA EN EL MODULO
    private function _consultar()
    {
        $sql = "SELECT 
                    g.id_gasto, 
                    g.descripcion_gasto, 
                    g.clasificacion, 
                    SUM(dg.monto) as monto_total,
                    MAX(dg.fecha) as ultima_fecha,
                    p.nombre_proveedor as proveedor, 
                    tg.nombre_tipo_gasto as tipo,
                    -- Método de pago del último detalle (para mostrar moneda)
                    (SELECT metodo_pago 
                     FROM detalles_gastos 
                     WHERE gasto_id = g.id_gasto 
                     ORDER BY fecha DESC 
                     LIMIT 1) as metodo_pago_predominante
                FROM gastos g
                LEFT JOIN detalles_gastos dg ON g.id_gasto = dg.gasto_id
                LEFT JOIN proveedores p ON g.proveedor_id = p.id_proveedor
                LEFT JOIN tipo_gasto tg ON g.tipo_gasto_id = tg.id_tipo_gasto
                WHERE g.activo = 1
                GROUP BY g.id_gasto
                ORDER BY ultima_fecha DESC";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_gastos: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar gastos'];
        }
    }

    /**
     * Consulta un gasto específico con todos sus detalles y datos bancarios asociados
     // SE USA EN EL MODULO
     */
    private function _consultar_gasto()
    {
        try {
            // 1. Obtener datos de la cabecera del gasto
            $sqlCabecera = "SELECT 
                                g.id_gasto,
                                g.clasificacion,
                                g.descripcion_gasto,
                                g.solicitud_id,
                                g.tipo_gasto_id,
                                g.proveedor_id,
                                g.activo,
                                p.nombre_proveedor,
                                tg.nombre_tipo_gasto
                            FROM gastos g
                            LEFT JOIN proveedores p ON g.proveedor_id = p.id_proveedor
                            LEFT JOIN tipo_gasto tg ON g.tipo_gasto_id = tg.id_tipo_gasto
                            WHERE g.id_gasto = :id_gasto AND g.activo = 1";
            
            $stmtCab = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sqlCabecera);
            $stmtCab->execute([':id_gasto' => $this->id_gasto]);
            $cabecera = $stmtCab->fetch(PDO::FETCH_ASSOC);
            
            if (!$cabecera) {
                return ['estatus' => false, 'mensaje' => 'Gasto no encontrado'];
            }

            //  Obtener todos los detalles del gasto con sus datos bancarios
            $sqlDetalles = "SELECT 
                                dg.id_detalle_gasto,
                                dg.fecha,
                                dg.monto,
                                g.tasa_dolar, 
                                dg.metodo_pago,
                                eb.referencia,
                                eb.imagen,
                                eb.banco_id,
                                b.nombre_banco
                            FROM detalles_gastos dg
                            JOIN gastos g ON dg.gasto_id = g.id_gasto 
                            LEFT JOIN egresos_bancarios eb ON dg.id_detalle_gasto = eb.detalle_gasto_id
                            LEFT JOIN bancos b ON eb.banco_id = b.id_banco
                            WHERE dg.gasto_id = :id_gasto
                            ORDER BY dg.fecha DESC";
            
            $stmtDet = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sqlDetalles);
            $stmtDet->execute([':id_gasto' => $this->id_gasto]);
            $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

            // 3. Estructurar la respuesta
            $resultado = [
                'gasto' => $cabecera,
                'detalles' => $detalles
            ];

            return ['estatus' => true, 'datos' => $resultado];

        } catch (PDOException $e) {
            error_log("Error en _consultar_gasto_unico: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar el gasto'];
        }
    }

    /**
     * Consulta plana solo de la cabecera del gasto para la bitácora de auditoría.
     // SE USA EN EL MODULO
     */
    private function _consultar_cabecera_gasto()
    {
        $sql = "SELECT clasificacion, descripcion_gasto, solicitud_id, tipo_gasto_id, proveedor_id 
                FROM gastos WHERE id_gasto = :id_gasto AND activo = 1";
        
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([':id_gasto' => $this->id_gasto]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_cabecera_gasto: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar cabecera'];
        }
    }

    /**
     * Consulta todos los detalles de un gasto específico, incluyendo datos bancarios si existen
     // SE USA EN EL MODULO
     */
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
                                eb.banco_id,
                                b.nombre_banco
                            FROM detalles_gastos dg
                            JOIN gastos g ON dg.gasto_id = g.id_gasto 
                            LEFT JOIN egresos_bancarios eb ON dg.id_detalle_gasto = eb.detalle_gasto_id
                            LEFT JOIN bancos b ON eb.banco_id = b.id_banco
                            WHERE dg.gasto_id = :id_gasto
                            ORDER BY dg.fecha DESC";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([':id_gasto' => $this->id_gasto]);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_detalles_por_gasto: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar los detalles del gasto'];
        }
    }

    /**
     * Consulta un detalle específico de gasto por su ID, incluyendo datos bancarios si existen
     // SE USA EN EL MODULO
     */
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
                    eb.banco_id,
                    b.nombre_banco
                FROM detalles_gastos dg
                JOIN gastos g ON dg.gasto_id = g.id_gasto 
                LEFT JOIN egresos_bancarios eb ON dg.id_detalle_gasto = eb.detalle_gasto_id
                LEFT JOIN bancos b ON eb.banco_id = b.id_banco
                WHERE dg.id_detalle_gasto = :id_detalle";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([':id_detalle' => $this->id_detalle_gasto]);
            $dato = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$dato) {
                return ['estatus' => false, 'mensaje' => 'Detalle de gasto no encontrado'];
            }
            
            return ['estatus' => true, 'datos' => $dato];
            
        } catch (PDOException $e) {
            error_log("Error en _consultar_detalle_unico: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar el detalle del gasto'];
        }
    }

    // EXCLUSIVO PARA LA APP:
    private function _listar_gastos_mes($params)
    {
        // params trae ['mes' => X, 'anio' => Y]
        $sql = "SELECT 
                    g.id_gasto, 
                    g.descripcion_gasto, 
                    g.clasificacion, 
                    SUM(dg.monto) as monto_total,
                    MAX(dg.fecha) as ultima_fecha,
                    p.nombre_proveedor as proveedor, 
                    tg.nombre_tipo_gasto as tipo,
                    (SELECT metodo_pago FROM detalles_gastos WHERE gasto_id = g.id_gasto ORDER BY fecha DESC LIMIT 1) as metodo_pago_predominante
                FROM gastos g
                LEFT JOIN detalles_gastos dg ON g.id_gasto = dg.gasto_id
                LEFT JOIN proveedores p ON g.proveedor_id = p.id_proveedor
                LEFT JOIN tipo_gasto tg ON g.tipo_gasto_id = tg.id_tipo_gasto
                WHERE g.activo = 1 
                  AND MONTH(dg.fecha) = :mes 
                  AND YEAR(dg.fecha) = :anio
                GROUP BY g.id_gasto
                ORDER BY ultima_fecha DESC";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute([':mes' => $params['mes'], ':anio' => $params['anio']]);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _listar_gastos_mes: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al filtrar gastos del mes'];
        }
    }

    private function _obtener_periodos_activos()
    {
        // Agrupamos para obtener solo combinaciones únicas, ordenadas del más reciente al más antiguo
        $sql = "SELECT DISTINCT MONTH(dg.fecha) AS mes, YEAR(dg.fecha) AS anio
                FROM detalles_gastos dg
                INNER JOIN gastos g ON dg.gasto_id = g.id_gasto
                WHERE g.activo = 1
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
    
    // ====================================================================
    // UTILIDADES (IMÁGENES)
    // ====================================================================

    // SE USA EN LA PROPIA CLASE (modificar)
    private function obtenerImagenPorDetalle($idDetalle)
    {
        $sql = "SELECT imagen FROM egresos_bancarios WHERE detalle_gasto_id = :id";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->bindParam(':id', $idDetalle);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en obtenerImagenPorDetalle: " . $e->getMessage());
            return false;
        }
    }
}
