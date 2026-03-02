<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\ayuda\GestorImagenes;

class Gastos extends Conexion
{
    // ====================================================================
    // PROPIEDADES (Mapeo de 4 tablas)
    // ====================================================================

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
    private $monto_dolar;
    private $metodo_pago;       // 'Efectivo', 'Pago Movil', 'Transferencia', 'Divisa'
    private $descripcion_detalle;

    private $detalles = [];  // Array de detalles (cada detalle es un array asociativo)

    // Tabla: egresos_bancarios (solo si método bancario)
    private $referencia;
    private $imagen;
    private $banco_id;

    private $filtros_reporte = [];

    // ====================================================================
    // REGLAS DE VALIDACIÓN CENTRALIZADAS
    // ====================================================================
    private $reglas = [
        'id_gasto' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'gastos', 'campo' => 'id_gasto']
        ],
        'id_detalle_gasto' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'detalles_gastos', 'campo' => 'id_detalle_gasto']
        ],
        'clasificacion' => [
            'regex' => '/^(Fijo|Variable|Reposicion)$/i'
        ],
        'descripcion_gasto' => [
            'regex' => '/^[a-zA-Z0-9áéíóúñÁÉÍÓÚÑ\s.,:\/-]{3,255}$/'
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
            'opcional' => true
        ],
        'fecha' => [
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
        'metodo_pago' => [
            'regex' => '/^(Efectivo|Pago Movil|Transferencia|Divisa)$/'
        ],
        'descripcion_detalle' => [
            'regex' => '/^[a-zA-Z0-9áéíóúñÁÉÍÓÚÑ\s.,:\/-]{0,255}$/',
            'opcional' => true
        ],
        'referencia' => [
            'regex' => '/^[a-zA-Z0-9]{4,20}$/',
            'opcional' => true,
            'requerido_si' => ['metodo_pago' => ['Transferencia', 'Pago Movil']]
        ],
        'imagen' => [
            'regex' => '/^[a-zA-Z0-9_.-]+\.(jpg|jpeg|png|gif)$/i',
            'opcional' => true
        ],
        'banco_id' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'bancos', 'campo' => 'id_banco'],
            'opcional' => true,
            'requerido_si' => ['metodo_pago' => ['Transferencia', 'Pago Movil']]
        ]
    ];

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
    public function set_monto_dolar($md) { $this->monto_dolar = $md; }
    public function get_monto_dolar() { return $this->monto_dolar; }
    public function set_metodo_pago($mp) { $this->metodo_pago = $mp; }
    public function get_metodo_pago() { return $this->metodo_pago; }
    public function set_descripcion_detalle($dd) { $this->descripcion_detalle = $dd; }
    public function get_descripcion_detalle() { return $this->descripcion_detalle; }

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

    public function set_filtros_reporte($filtros) {
        $this->filtros_reporte = $filtros;
    }
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
    // VALIDACIÓN CENTRALIZADA (CONDICIONAL Y CON EXISTS)
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
                // Si es opcional y está vacío, saltamos validaciones adicionales
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
     * Verifica existencia de un valor en una tabla
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

    private function validarDetalle($datos)
    {
        // Normalizar nombres de campos (aceptar alias)
        $alias = [
            'fecha_detalle' => 'fecha',
            'descripcion_detalle_gasto' => 'descripcion_detalle'
        ];
        foreach ($alias as $origen => $destino) {
            if (isset($datos[$origen]) && !isset($datos[$destino])) {
                $datos[$destino] = $datos[$origen];
            }
        }
        // var_dump($datos);
        $camposDetalle = ['fecha', 'monto', 'monto_dolar', 'metodo_pago', 'descripcion_detalle', 'referencia', 'banco_id', 'imagen'];
        foreach ($camposDetalle as $campo) {
            if (!isset($this->reglas[$campo])) continue;
            $regla = $this->reglas[$campo];
            $valor = $datos[$campo] ?? null;

            // Determinar si es requerido (considerando condicionales)
            $requerido = !(isset($regla['opcional']) && $regla['opcional'] === true);
            if (isset($regla['requerido_si'])) {
                foreach ($regla['requerido_si'] as $campoCond => $valoresCond) {
                    if (in_array($datos[$campoCond] ?? null, $valoresCond)) {
                        $requerido = true;
                        break;
                    }
                }
            }

            if ($requerido) {
                if ($valor === null) {
                    return ['estatus' => false, 'mensaje' => "El campo '$campo' es requerido en el detalle."];
                }
                if (is_string($valor) && trim($valor) === '') {
                    return ['estatus' => false, 'mensaje' => "El campo '$campo' no puede estar vacío."];
                }
            } else {
                if ($valor === null || (is_string($valor) && trim($valor) === '')) {
                    continue;
                }
            }

            // Validar regex
            if (isset($regla['regex']) && !preg_match($regla['regex'], (string)$valor)) {
                return ['estatus' => false, 'mensaje' => "El campo '$campo' del detalle tiene formato inválido."];
            }

            // Validar mínimo
            if (isset($regla['min']) && $valor < $regla['min']) {
                return ['estatus' => false, 'mensaje' => "El campo '$campo' debe ser mayor o igual a " . $regla['min']];
            }

            // Validar existencia en otra tabla (si aplica)
            if (isset($regla['exists'])) {
                $tabla = $regla['exists']['tabla'];
                $campoFor = $regla['exists']['campo'] ?? $campo;
                if (!$this->existeEnTabla($tabla, $campoFor, $valor)) {
                    return ['estatus' => false, 'mensaje' => "El valor de '$campo' no existe en $tabla."];
                }
            }
        }
        return ['estatus' => true];
    }

    public function validarExistenciaExterna($tabla, $campo, $valor)
    {
        // Lista de tablas permitidas (por seguridad)
        $tablasPermitidas = ['tipo_gasto', 'proveedores', 'solicitudes_gasto', 'bancos'];
        if (!in_array($tabla, $tablasPermitidas)) {
            return false;
        }
        return $this->existeEnTabla($tabla, $campo, $valor);
    }

    // ====================================================================
    // LÓGICA DE NEGOCIO
    // ====================================================================

    private function _registrar()
    {
        // Validar cabecera
        $camposCabecera = ['clasificacion', 'descripcion_gasto', 'tipo_gasto_id'];
        $validacion = $this->validar($camposCabecera);
        if (!$validacion['estatus']) return $validacion;

        // Validar que haya al menos un detalle
        if (empty($this->detalles)) {
            return ['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un detalle de gasto.'];
        }

        // Validar cada detalle
        foreach ($this->detalles as $idx => $det) {
            $valDet = $this->validarDetalle($det);
            if (!$valDet['estatus']) {
                return ['estatus' => false, 'mensaje' => "Error en detalle #" . ($idx+1) . ": " . $valDet['mensaje']];
            }
        }

        $pdo = $this->get_conex('negocio');
        try {
            $pdo->beginTransaction();

            // Insertar cabecera
            $sqlCab = "INSERT INTO gastos (clasificacion, descripcion_gasto, solicitud_id, tipo_gasto_id, proveedor_id, activo) 
                       VALUES (:clas, :desc, :sol_id, :tipo_id, :prov_id, 1)";
            $stmtCab = $pdo->prepare($sqlCab);
            $stmtCab->execute([
                ':clas' => $this->clasificacion,
                ':desc' => $this->descripcion_gasto,
                ':sol_id' => $this->solicitud_id ?: null,
                ':tipo_id' => $this->tipo_gasto_id,
                ':prov_id' => $this->proveedor_id ?: null
            ]);
            $id_gasto = $pdo->lastInsertId();

            // Insertar detalles y sus bancarios
            foreach ($this->detalles as $det) {
                $sqlDet = "INSERT INTO detalles_gastos (fecha, monto, monto_dolar, metodo_pago, descripcion_detalle_gasto, gasto_id) 
                           VALUES (:fecha, :monto, :md, :metodo, :desc_det, :gasto_id)";
                $stmtDet = $pdo->prepare($sqlDet);
                $stmtDet->execute([
                    ':fecha' => $det['fecha_detalle'],
                    ':monto' => $det['monto'],
                    ':md' => $det['monto_dolar'] ?? 0,
                    ':metodo' => $det['metodo_pago'],
                    ':desc_det' => $det['descripcion_detalle'] ?? $this->descripcion_gasto,
                    ':gasto_id' => $id_gasto
                ]);
                $id_detalle = $pdo->lastInsertId();

                // Si el método de pago requiere registro bancario
                if (in_array($det['metodo_pago'], ['Transferencia', 'Pago Movil'])) {
                    // Procesar imagen si se subió
                    $imagen = $det['imagen'] ?? 'default.png';
                    // Si hay imagen nueva, se debe haber procesado antes (en el controlador) y asignado el nombre
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
            error_log("Error en _registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    private function _modificar()
    {
        // Validar ID del gasto
        $validacion = $this->validar(['id_gasto']);
        if (!$validacion['estatus']) return $validacion;

        // Validar cabecera (los campos editables)
        $camposCabecera = ['clasificacion', 'descripcion_gasto', 'tipo_gasto_id', 'proveedor_id'];
        $validacion = $this->validar($camposCabecera);
        if (!$validacion['estatus']) return $validacion;

        // Validar detalles (si se proporcionan)
        if (!empty($this->detalles)) {
            foreach ($this->detalles as $idx => $det) {
                $valDet = $this->validarDetalle($det);
                if (!$valDet['estatus']) {
                    return ['estatus' => false, 'mensaje' => "Error en detalle #" . ($idx+1) . ": " . $valDet['mensaje']];
                }
            }
        } else {
            return ['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un detalle para actualizar.'];
        }

        $pdo = $this->get_conex('negocio');
        try {
            $pdo->beginTransaction();

            // Actualizar cabecera
            $sqlCab = "UPDATE gastos SET 
                        clasificacion = :clas,
                        descripcion_gasto = :desc,
                        solicitud_id = :sol_id,
                        tipo_gasto_id = :tipo_id,
                        proveedor_id = :prov_id
                      WHERE id_gasto = :id";
            $stmtCab = $pdo->prepare($sqlCab);
            $stmtCab->execute([
                ':clas' => $this->clasificacion,
                ':desc' => $this->descripcion_gasto,
                ':sol_id' => $this->solicitud_id ?: null,
                ':tipo_id' => $this->tipo_gasto_id,
                ':prov_id' => $this->proveedor_id ?: null,
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
                $sqlDet = "INSERT INTO detalles_gastos (fecha, monto, monto_dolar, metodo_pago, descripcion_detalle_gasto, gasto_id) 
                           VALUES (:fecha, :monto, :md, :metodo, :desc_det, :gasto_id)";
                $stmtDet = $pdo->prepare($sqlDet);
                $stmtDet->execute([
                    ':fecha' => $det['fecha_detalle'],
                    ':monto' => $det['monto'],
                    ':md' => $det['monto_dolar'] ?? 0,
                    ':metodo' => $det['metodo_pago'],
                    ':desc_det' => $det['descripcion_detalle'] ?? $this->descripcion_gasto,
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

            // Obtener las imágenes de los nuevos detalles (las que realmente quedaron)
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

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Gasto actualizado con éxito'];

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error en _modificar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * ELIMINAR GASTO (soft delete)
     */
    private function _eliminar_gasto()
    {
        $validacion = $this->validar(['id_gasto']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "UPDATE gastos SET activo = 0 WHERE id_gasto = :id";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
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

    private function _consultar_gastos()
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
            $stmt = $this->get_conex('negocio')->prepare($sql);
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
     */
    private function _consultar_gasto_unico()
    {
        $validacion = $this->validar(['id_gasto']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

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
            
            $stmtCab = $this->get_conex('negocio')->prepare($sqlCabecera);
            $stmtCab->execute([':id_gasto' => $this->id_gasto]);
            $cabecera = $stmtCab->fetch(PDO::FETCH_ASSOC);
            
            if (!$cabecera) {
                return ['estatus' => false, 'mensaje' => 'Gasto no encontrado'];
            }

            // 2. Obtener todos los detalles del gasto con sus datos bancarios
            $sqlDetalles = "SELECT 
                                dg.id_detalle_gasto,
                                dg.fecha,
                                dg.monto,
                                dg.monto_dolar,
                                dg.metodo_pago,
                                dg.descripcion_detalle_gasto,
                                eb.referencia,
                                eb.imagen,
                                eb.banco_id,
                                b.nombre_banco
                            FROM detalles_gastos dg
                            LEFT JOIN egresos_bancarios eb ON dg.id_detalle_gasto = eb.detalle_gasto_id
                            LEFT JOIN bancos b ON eb.banco_id = b.id_banco
                            WHERE dg.gasto_id = :id_gasto
                            ORDER BY dg.fecha DESC";
            
            $stmtDet = $this->get_conex('negocio')->prepare($sqlDetalles);
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
     * Consulta todos los detalles de un gasto específico, incluyendo datos bancarios si existen
     */
    private function _consultar_detalles_por_gasto()
    {
        $validacion = $this->validar(['id_gasto']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "SELECT 
                    dg.id_detalle_gasto,
                    dg.fecha,
                    dg.monto,
                    dg.monto_dolar,
                    dg.metodo_pago,
                    dg.descripcion_detalle_gasto,
                    eb.referencia,
                    eb.imagen,
                    b.nombre_banco,
                    eb.banco_id
                FROM detalles_gastos dg
                LEFT JOIN egresos_bancarios eb ON dg.id_detalle_gasto = eb.detalle_gasto_id
                LEFT JOIN bancos b ON eb.banco_id = b.id_banco
                WHERE dg.gasto_id = :id_gasto
                ORDER BY dg.fecha DESC";

        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
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
     */
    private function _consultar_detalle_unico()
    {
        $validacion = $this->validar(['id_detalle_gasto']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "SELECT 
                    dg.id_detalle_gasto,
                    dg.fecha,
                    dg.monto,
                    dg.monto_dolar,
                    dg.metodo_pago,
                    dg.descripcion_detalle_gasto,
                    dg.gasto_id,
                    eb.referencia,
                    eb.imagen,
                    eb.banco_id,
                    b.nombre_banco
                FROM detalles_gastos dg
                LEFT JOIN egresos_bancarios eb ON dg.id_detalle_gasto = eb.detalle_gasto_id
                LEFT JOIN bancos b ON eb.banco_id = b.id_banco
                WHERE dg.id_detalle_gasto = :id_detalle";

        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
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
    
    // ====================================================================
    // METODOS PARA REPORTES
    // ====================================================================    

    /**
     * Consulta ingresos y egresos con filtros.
     * @param array $filtros Asociativo con claves: balance, metodo_pago, tipo_gasto, filtro, fecha_inicio, fecha_fin
     * @return array ['estatus' => bool, 'datos' => array, 'mensaje' => string]
     */
   private function _consultar_ingresos_egresos()
{
    $f = $this->filtros_reporte;
    $balance = $f['balance'] ?? 'todos';
    $metodo_pago = $f['metodo_pago'] ?? 'todos';
    $tipo_gasto = $f['tipo_gasto'] ?? 'todos';
    $filtro = $f['filtro'] ?? '';
    $fecha_inicio = $f['fecha_inicio'] ?? '';
    $fecha_fin = $f['fecha_fin'] ?? '';

    // Parámetros para egresos e ingresos (los separamos para evitar conflictos)
    $paramsEgresos = [];
    $paramsIngresos = [];

    // Condiciones comunes (fecha y método de pago)
    $whereEgresos = [];
    $whereIngresos = [];

    // Filtro por fecha
    if ($filtro === 'Otro' && $fecha_inicio && $fecha_fin) {
        $whereEgresos[] = "dg.fecha BETWEEN :fecha_ini AND :fecha_fin";
        $whereIngresos[] = "dp.fecha BETWEEN :fecha_ini AND :fecha_fin";
        $paramsEgresos[':fecha_ini'] = $fecha_inicio;
        $paramsEgresos[':fecha_fin'] = $fecha_fin;
        $paramsIngresos[':fecha_ini'] = $fecha_inicio;
        $paramsIngresos[':fecha_fin'] = $fecha_fin;
    } elseif ($filtro === 'mes') {
        $whereEgresos[] = "dg.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        $whereIngresos[] = "dp.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    } elseif ($filtro === 'trimestre') {
        $whereEgresos[] = "dg.fecha >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
        $whereIngresos[] = "dp.fecha >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
    } elseif ($filtro === 'semestre') {
        $whereEgresos[] = "dg.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
        $whereIngresos[] = "dp.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
    } elseif ($filtro === 'año') {
        $whereEgresos[] = "dg.fecha >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        $whereIngresos[] = "dp.fecha >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
    }

    // Filtro por método de pago
    if ($metodo_pago !== 'Todos') {
        $whereEgresos[] = "dg.metodo_pago = :metodo_pago_egreso";
        $whereIngresos[] = "dp.tipo_pago = :metodo_pago_ingreso";
        $paramsEgresos[':metodo_pago_egreso'] = $metodo_pago;
        $paramsIngresos[':metodo_pago_ingreso'] = $metodo_pago;
    }

    // Filtro por tipo de gasto (solo para egresos)
    if ($tipo_gasto !== 'Todos' && $balance !== 'Ingresos') {
        $whereEgresos[] = "tg.id_tipo_gasto = :tipo_gasto";
        $paramsEgresos[':tipo_gasto'] = $tipo_gasto;
    }

    // Construir consultas base
    $sqlEgresos = "SELECT 
                    'Egreso' as balance,
                    dg.fecha,
                    dg.monto,
                    dg.metodo_pago as metodo_pago,
                    g.descripcion_gasto as concepto,
                    tg.nombre_tipo_gasto as tipo
                   FROM detalles_gastos dg
                   INNER JOIN gastos g ON dg.gasto_id = g.id_gasto
                   LEFT JOIN tipo_gasto tg ON g.tipo_gasto_id = tg.id_tipo_gasto
                   WHERE g.activo = 1";

    $sqlIngresos = "SELECT 
                    'Ingreso' as balance,
                    dp.fecha,
                    dp.monto,
                    dp.tipo_pago as metodo_pago,
                    CONCAT('Pago de mensualidad - Apto ', a.nro_apartamento) as concepto,
                    NULL as tipo
                   FROM detalles_pagos dp
                   INNER JOIN pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
                   INNER JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
                   INNER JOIN apartamentos a ON m.apartamento_id = a.id_apartamento
                   INNER JOIN pagos p ON dp.pago_id = p.id_pago
                   WHERE p.activo = 1";

    // Aplicar condiciones WHERE a cada consulta
    if (!empty($whereEgresos)) {
        $sqlEgresos .= " AND " . implode(' AND ', $whereEgresos);
    }
    if (!empty($whereIngresos)) {
        $sqlIngresos .= " AND " . implode(' AND ', $whereIngresos);
    }

    // Determinar la consulta final y los parámetros
    if ($balance === 'Ingresos') {
        $sql = $sqlIngresos;
        $params = $paramsIngresos;
    } elseif ($balance === 'Egresos') {
        $sql = $sqlEgresos;
        $params = $paramsEgresos;
    } else {
        $sql = "($sqlEgresos) UNION ALL ($sqlIngresos) ORDER BY fecha DESC";
        $params = array_merge($paramsEgresos, $paramsIngresos);
    }

    try {
        $stmt = $this->get_conex('negocio')->prepare($sql);
        $stmt->execute($params);
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['estatus' => true, 'datos' => $datos, 'sql'=>$sql, 'p'=>$params];
    } catch (PDOException $e) {
        error_log("Error en _consultar_ingresos_egresos: " . $e->getMessage() . " SQL: " . $sql);
        return ['estatus' => false, 'mensaje' => 'Error al consultar ingresos/egresos: ' . $e->getMessage()];
    }
}

    /**
     * Estadísticas de ingresos y egresos según filtros.
     * @param array $filtros Mismos parámetros que _consultar_ingresos_egresos
     * @return array ['estatus' => bool, 'datos' => array, 'mensaje' => string]
     */
    private function _estadisticas_ingresos_egresos()
{
    $f = $this->filtros_reporte;
    $balance = $f['balance'] ?? 'todos';
    $metodo_pago = $f['metodo_pago'] ?? 'todos';
    $tipo_gasto = $f['tipo_gasto'] ?? 'todos';
    $filtro = $f['filtro'] ?? '';
    $fecha_inicio = $f['fecha_inicio'] ?? '';
    $fecha_fin = $f['fecha_fin'] ?? '';

    $resultados = [];

    // --- EG RESOS (gastos) ---
    if ($balance !== 'Ingresos') {
        // Construir condiciones para egresos
        $whereEgresos = ["g.activo = 1"];
        $paramsEgresos = [];

        // Filtro por método de pago (en detalles_gastos)
        if ($metodo_pago !== 'todos') {
            $whereEgresos[] = "dg.metodo_pago = :metodo_pago_egreso";
            $paramsEgresos[':metodo_pago_egreso'] = $metodo_pago;
        }

        // Filtro por tipo de gasto (requiere JOIN con tipo_gasto)
        if ($tipo_gasto !== 'todos') {
            $whereEgresos[] = "tg.id_tipo_gasto = :tipo_gasto";
            $paramsEgresos[':tipo_gasto'] = $tipo_gasto;
            // Asegurar JOIN con tipo_gasto en las consultas
            $joinTipo = "LEFT JOIN tipo_gasto tg ON g.tipo_gasto_id = tg.id_tipo_gasto";
        } else {
            $joinTipo = ""; // No necesario
        }

        // Filtro por fecha
        if ($filtro === 'Otro' && $fecha_inicio && $fecha_fin) {
            $whereEgresos[] = "dg.fecha BETWEEN :fecha_ini_egreso AND :fecha_fin_egreso";
            $paramsEgresos[':fecha_ini_egreso'] = $fecha_inicio;
            $paramsEgresos[':fecha_fin_egreso'] = $fecha_fin;
        } elseif ($filtro === 'mes') {
            $whereEgresos[] = "dg.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        } elseif ($filtro === 'trimestre') {
            $whereEgresos[] = "dg.fecha >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
        } elseif ($filtro === 'semestre') {
            $whereEgresos[] = "dg.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
        } elseif ($filtro === 'año') {
            $whereEgresos[] = "dg.fecha >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        }

        $whereClauseEgresos = " WHERE " . implode(" AND ", $whereEgresos);

        // Consulta total de egresos
        $sqlEgresosTotal = "SELECT 
                                'total_gastos' as indicador,
                                SUM(dg.monto) as valor
                             FROM detalles_gastos dg
                             INNER JOIN gastos g ON dg.gasto_id = g.id_gasto
                             $joinTipo
                             $whereClauseEgresos";

        // Consulta egresos por método de pago
        $sqlEgresosPorMetodo = "SELECT 
                                    CONCAT('gastos_', LOWER(REPLACE(dg.metodo_pago, ' ', '_'))) as indicador,
                                    SUM(dg.monto) as valor
                                 FROM detalles_gastos dg
                                 INNER JOIN gastos g ON dg.gasto_id = g.id_gasto
                                 $joinTipo
                                 $whereClauseEgresos
                                 GROUP BY dg.metodo_pago";

        try {
            $con = $this->get_conex('negocio');

            // Total
            $stmt = $con->prepare($sqlEgresosTotal);
            $stmt->execute($paramsEgresos);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['valor'] !== null) {
                $resultados[] = $row;
            }

            // Por método
            $stmt = $con->prepare($sqlEgresosPorMetodo);
            $stmt->execute($paramsEgresos);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $resultados[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error en estadísticas egresos: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener estadísticas de egresos: ' . $e->getMessage()];
        }
    }

    // --- INGRESOS (pagos) ---
    if ($balance !== 'Egresos') {
        // Construir condiciones para ingresos
        $whereIngresos = ["p.activo = 1"];
        $paramsIngresos = [];

        // Filtro por método de pago (en detalles_pagos se llama tipo_pago)
        if ($metodo_pago !== 'todos') {
            $whereIngresos[] = "dp.tipo_pago = :metodo_pago_ingreso";
            $paramsIngresos[':metodo_pago_ingreso'] = $metodo_pago;
        }

        // Filtro por fecha
        if ($filtro === 'Otro' && $fecha_inicio && $fecha_fin) {
            $whereIngresos[] = "dp.fecha BETWEEN :fecha_ini_ingreso AND :fecha_fin_ingreso";
            $paramsIngresos[':fecha_ini_ingreso'] = $fecha_inicio;
            $paramsIngresos[':fecha_fin_ingreso'] = $fecha_fin;
        } elseif ($filtro === 'mes') {
            $whereIngresos[] = "dp.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        } elseif ($filtro === 'trimestre') {
            $whereIngresos[] = "dp.fecha >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
        } elseif ($filtro === 'semestre') {
            $whereIngresos[] = "dp.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
        } elseif ($filtro === 'año') {
            $whereIngresos[] = "dp.fecha >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        }

        $whereClauseIngresos = " WHERE " . implode(" AND ", $whereIngresos);

        // Consulta total de ingresos
        $sqlIngresosTotal = "SELECT 
                                'total_pagos' as indicador,
                                SUM(dp.monto) as valor
                             FROM detalles_pagos dp
                             INNER JOIN pagos p ON dp.pago_id = p.id_pago
                             $whereClauseIngresos";

        // Consulta ingresos por método de pago
        $sqlIngresosPorMetodo = "SELECT 
                                    CONCAT('pagos_', LOWER(REPLACE(dp.tipo_pago, ' ', '_'))) as indicador,
                                    SUM(dp.monto) as valor
                                 FROM detalles_pagos dp
                                 INNER JOIN pagos p ON dp.pago_id = p.id_pago
                                 $whereClauseIngresos
                                 GROUP BY dp.tipo_pago";

        try {
            $con = $this->get_conex('negocio');

            $stmt = $con->prepare($sqlIngresosTotal);
            $stmt->execute($paramsIngresos);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['valor'] !== null) {
                $resultados[] = $row;
            }

            $stmt = $con->prepare($sqlIngresosPorMetodo);
            $stmt->execute($paramsIngresos);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $resultados[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error en estadísticas ingresos: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener estadísticas de ingresos: ' . $e->getMessage()];
        }
    }

    return ['estatus' => true, 'datos' => $resultados];
}

    /**
     * Lista los meses y años con gastos registrados.
     * @return array ['estatus' => bool, 'datos' => array, 'mensaje' => string]
     */
    private function _listar_meses_con_gastos()
    {
        $sql = "SELECT DISTINCT 
                   YEAR(dg.fecha) as anio, 
                   MONTH(dg.fecha) as mes 
                FROM detalles_gastos dg
                INNER JOIN gastos g ON dg.gasto_id = g.id_gasto
                WHERE g.activo = 1
                ORDER BY anio DESC, mes DESC";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _listar_meses_con_gastos: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al listar meses con gastos'];
        }
    }

    /**
     * Obtiene datos para el reporte mensual de gastos.
     * @param int $mes
     * @param int $anio
     * @return array ['estatus' => bool, 'datos' => array, 'mensaje' => string]
     */
    private function _obtener_datos_reporte_mensual()
    {
        $f = $this->filtros_reporte;
        $mes = $f['mes'] ?? 0;
        $anio = $f['anio'] ?? 0;

        $sql = "SELECT 
                    g.clasificacion,
                    dg.descripcion_detalle_gasto as concepto,
                    dg.monto
                FROM detalles_gastos dg
                INNER JOIN gastos g ON dg.gasto_id = g.id_gasto
                WHERE g.activo = 1 
                  AND YEAR(dg.fecha) = :anio 
                  AND MONTH(dg.fecha) = :mes";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':anio' => $anio, ':mes' => $mes]);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _obtener_datos_reporte_mensual: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener datos del reporte mensual'];
        }
    }

    // ====================================================================
    // UTILIDADES (IMÁGENES)
    // ====================================================================

    private function obtenerImagenPorDetalle($idDetalle)
    {
        $sql = "SELECT imagen FROM egresos_bancarios WHERE detalle_gasto_id = :id";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id', $idDetalle);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en obtenerImagenPorDetalle: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Método público para eliminar imagen asociada a un detalle (útil desde controlador)
     */
    public function borrarImagenAsociada($id_detalle_gasto)
    {
        $nombre = $this->obtenerImagenPorDetalle($id_detalle_gasto);
        return GestorImagenes::eliminar($nombre, 'gastos');
    }
}
?>