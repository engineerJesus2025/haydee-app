<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\TipoBaseDatos;
use haydee\enums\HttpCodigo;
use haydee\excepciones\NegocioException;

class TipoGasto extends Conexion
{
    private $id_tipo_gasto;
    private $nombre_tipo_gasto;
    private $activo;

    private $conceptos_temp = [];

    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
            'id_tipo_gasto' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'tipo_gasto', 'campo' => 'id_tipo_gasto']
            ],
            'nombre_tipo_gasto' => [
                'regex' => '/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]+$/u'
            ]
        ];

        $camposPorOperacion = [
            'registrar_tipo_gasto' => ['nombre_tipo_gasto'],
            'modificar_tipo_gasto' => ['id_tipo_gasto', 'nombre_tipo_gasto'],
            'eliminar_tipo_gasto'  => ['id_tipo_gasto'],
            'consultar_tipo_gasto' => ['id_tipo_gasto']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    public static function obtenerReglasConceptos() {
        return [
            'id_concepto' => [
                'regex' => '/^\d*$/',
                'opcional' => true
            ],
            'nombre_concepto' => [
                'regex' => '/^[a-zA-Z0-9\sñÑáéíóúÁÉÍÓÚ.,-]+$/u'
            ]
        ];
    }

    public function set_id_tipo_gasto($id) { $this->id_tipo_gasto = $id; }
    public function get_id_tipo_gasto() { return $this->id_tipo_gasto; }
    public function set_nombre_tipo_gasto($nombre) { $this->nombre_tipo_gasto = $nombre; }
    public function get_nombre_tipo_gasto() { return $this->nombre_tipo_gasto; }
    public function set_activo($activo) { $this->activo = $activo; }
    public function get_activo() { return $this->activo; }
    public function setConceptosTemp($conceptos) { $this->conceptos_temp = $conceptos; }

    public function get_detalles() 
    { 
        return $this->conceptos_temp; 
    }

    public function resumirDetalles(?array $conceptos): array
    {
        if (empty($conceptos)) {
            return [
                'cantidad_conceptos'  => 0,
                'conceptos_asociados' => 'Ninguno'
            ];
        }

        $nombres = [];
        foreach ($conceptos as $c) {
            if (!empty($c['nombre_concepto'])) {
                $nombres[] = trim($c['nombre_concepto']);
            }
        }

        // alfabéticamente para que reordenar renglones no genere un falso diff
        sort($nombres, SORT_NATURAL | SORT_FLAG_CASE);

        return [
            'cantidad_conceptos'  => count($conceptos),
            'conceptos_asociados' => empty($nombres) ? 'Ninguno' : implode(', ', $nombres)
        ];
    }

    private function _consultar_auditoria()
    {
        $respuesta = $this->_consultar_tipo_gasto();
        if ($respuesta['estatus']) {
            $datos = $respuesta['datos'];
            $datos['detalles'] = $datos['conceptos'] ?? [];
            return ['estatus' => true, 'datos' => $datos];
        }
        return $respuesta;
    }

    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            throw new NegocioException("La acción '$accion' no está implementada.", HttpCodigo::BAD_REQUEST->value);
        }
        return $this->$metodo();
    }
    
    private function _consultar()
    {
        $sql = "SELECT id_tipo_gasto, nombre_tipo_gasto 
                FROM tipo_gasto 
                WHERE activo = 1 
                ORDER BY id_tipo_gasto DESC";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_tipo_gasto()
    {
        $con = $this->get_conex(TipoBaseDatos::NEGOCIO);
        
        $sqlHead = "SELECT * FROM tipo_gasto WHERE id_tipo_gasto = :id AND activo = 1";
        $stmtH = $con->prepare($sqlHead);
        $stmtH->execute([':id' => $this->id_tipo_gasto]);
        $datos = $stmtH->fetch(PDO::FETCH_ASSOC);
        
        if (!$datos) {
            throw new NegocioException('Tipo de gasto no encontrado.', HttpCodigo::NO_ENCONTRADO->value);
        }

        $sqlDet = "SELECT id_concepto, nombre_concepto FROM conceptos_gasto WHERE tipo_gasto_id = :id AND activo = 1";
        $stmtD = $con->prepare($sqlDet);
        $stmtD->execute([':id' => $this->id_tipo_gasto]);
        $datos['conceptos'] = $stmtD->fetchAll(PDO::FETCH_ASSOC);

        return ['estatus' => true, 'datos' => $datos];
    }

    private function _consultar_conceptos()
    {
        $sql = "SELECT tg.id_tipo_gasto, tg.nombre_tipo_gasto, cg.id_concepto, cg.nombre_concepto 
                FROM tipo_gasto tg
                LEFT JOIN conceptos_gasto cg ON tg.id_tipo_gasto = cg.tipo_gasto_id AND cg.activo = 1
                WHERE tg.activo = 1 
                ORDER BY tg.id_tipo_gasto, cg.id_concepto";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _registrar_tipo_gasto()
    {
        $con = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $con->beginTransaction();

            $sqlHead = "INSERT INTO tipo_gasto (nombre_tipo_gasto, activo) VALUES (:nombre, 1)";
            $stmtH = $con->prepare($sqlHead);
            $stmtH->execute([':nombre' => $this->nombre_tipo_gasto]);
            $id_tipo_gasto = $con->lastInsertId();

            if (!empty($this->conceptos_temp)) {
                $sqlDet = "INSERT INTO conceptos_gasto (nombre_concepto, tipo_gasto_id, activo) VALUES (:nombre_c, :id_tipo, 1)";
                $stmtD = $con->prepare($sqlDet);
                
                foreach ($this->conceptos_temp as $concepto) {
                    $stmtD->execute([
                        ':nombre_c' => $concepto['nombre_concepto'],
                        ':id_tipo'  => $id_tipo_gasto
                    ]);
                }
            }

            $con->commit();
            return ['estatus' => true, 'mensaje' => 'Tipo de gasto y conceptos registrados correctamente', 'lastId' => $id_tipo_gasto];
        } catch (\Throwable $e) {
            if ($con->inTransaction()) { 
                $con->rollBack(); 
            }
            throw $e;
        }
    }

    private function _modificar_tipo_gasto()
    {
        $con = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $con->beginTransaction();

            $sqlHead = "UPDATE tipo_gasto SET nombre_tipo_gasto = :nombre WHERE id_tipo_gasto = :id";
            $stmtH = $con->prepare($sqlHead);
            $stmtH->execute([
                ':nombre' => $this->nombre_tipo_gasto,
                ':id'     => $this->id_tipo_gasto
            ]);

            $ids_recibidos = array_filter(array_column($this->conceptos_temp, 'id_concepto'));
            
            if (!empty($ids_recibidos)) {
                $placeholders = implode(',', array_fill(0, count($ids_recibidos), '?'));
                $sqlDel = "UPDATE conceptos_gasto SET activo = 0 WHERE tipo_gasto_id = ? AND id_concepto NOT IN ($placeholders)";
                $stmtDel = $con->prepare($sqlDel);
                $paramsDel = array_merge([$this->id_tipo_gasto], $ids_recibidos);
                $stmtDel->execute($paramsDel);
            } else {
                $stmtDelTodos = $con->prepare("UPDATE conceptos_gasto SET activo = 0 WHERE tipo_gasto_id = ?");
                $stmtDelTodos->execute([$this->id_tipo_gasto]);
            }

            if (!empty($this->conceptos_temp)) {
                $sqlInsert = "INSERT INTO conceptos_gasto (nombre_concepto, tipo_gasto_id, activo) VALUES (:nombre_c, :id_tipo, 1)";
                $stmtInsert = $con->prepare($sqlInsert);

                $sqlUpdate = "UPDATE conceptos_gasto SET nombre_concepto = :nombre_c, activo = 1 WHERE id_concepto = :id_c AND tipo_gasto_id = :id_tipo";
                $stmtUpdate = $con->prepare($sqlUpdate);
                
                foreach ($this->conceptos_temp as $concepto) {
                    if (!empty($concepto['id_concepto'])) {
                        $stmtUpdate->execute([
                            ':nombre_c' => $concepto['nombre_concepto'],
                            ':id_c'     => $concepto['id_concepto'],
                            ':id_tipo'  => $this->id_tipo_gasto
                        ]);
                    } else {
                        $stmtInsert->execute([
                            ':nombre_c' => $concepto['nombre_concepto'],
                            ':id_tipo'  => $this->id_tipo_gasto
                        ]);
                    }
                }
            }

            $con->commit();
            return ['estatus' => true, 'mensaje' => 'Catálogo actualizado correctamente'];
        } catch (\Throwable $e) {
            if ($con->inTransaction()) { 
                $con->rollBack(); 
            }
            throw $e;
        }
    }

    private function _eliminar_tipo_gasto()
    {
        $con = $this->get_conex(TipoBaseDatos::NEGOCIO);
        try {
            $con->beginTransaction();

            $sqlH = "UPDATE tipo_gasto SET activo = 0 WHERE id_tipo_gasto = :id";
            $stmtH = $con->prepare($sqlH);
            $stmtH->execute([':id' => $this->id_tipo_gasto]);

            $sqlD = "UPDATE conceptos_gasto SET activo = 0 WHERE tipo_gasto_id = :id";
            $stmtD = $con->prepare($sqlD);
            $stmtD->execute([':id' => $this->id_tipo_gasto]);

            $con->commit();
            return ['estatus' => true, 'mensaje' => 'Catálogo eliminado correctamente'];
        } catch (\Throwable $e) {
            if ($con->inTransaction()) { 
                $con->rollBack(); 
            }
            throw $e;
        }
    }
}