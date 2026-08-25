<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\EstadoSolicitud;
use haydee\enums\NivelPrioridad;
use haydee\enums\TipoBaseDatos;
use haydee\enums\HttpCodigo;
use haydee\excepciones\NegocioException;

class SolicitudGasto extends Conexion
{
    private $id_solicitud;
    private $fecha_reporte;
    private $descripcion_necesidad;
    private $nombre_solicitante;
    private $monto_estimado;
    private $estado;
    private $presupuesto_id;
    private $prioridad;
    private $activo;

    public static function obtenerReglas($operacion) {
        $estadosValidos = implode('|', array_column(EstadoSolicitud::cases(), 'value'));
        $prioridadesValidas = implode('|', array_column(NivelPrioridad::cases(), 'value'));
        
        $reglasGenerales = [
            'id_solicitud' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'solicitudes_gasto', 'campo' => 'id_solicitud']
            ],
            'fecha_reporte' => [
                'regex' => '/^\d{4}-\d{2}-\d{2}$/'
            ],
            'descripcion_necesidad' => [
                'regex' => '/^[A-Za-z0-9áéíóúÁÉÍÓÚñÑ\s,.;:()\-]{3,255}$/'
            ],
            'nombre_solicitante' => [
                'regex' => '/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]{3,50}$/'
            ],
            'monto_estimado' => [
                'regex' => '/^\d+(\.\d{1,2})?$/',
                'min' => 0.01
            ],
            'estado' => [
                'regex' => "/^($estadosValidos)$/",
            ],
            'presupuesto_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'presupuesto', 'campo' => 'id_presupuesto'],
            ],
            'prioridad' => [
                'regex' => "/^($prioridadesValidas)$/"
            ]
        ];

        $camposPorOperacion = [
            'registrar_solicitud' => ['fecha_reporte', 'descripcion_necesidad', 'nombre_solicitante', 'monto_estimado', 'prioridad'],
            'modificar_solicitud' => ['id_solicitud', 'fecha_reporte', 'descripcion_necesidad', 'nombre_solicitante', 'monto_estimado', 'prioridad'],
            'eliminar_solicitud'  => ['id_solicitud'],
            'actualizar_estado'   => ['id_solicitud', 'estado', 'presupuesto_id'],
            'consultar_solicitud' => ['id_solicitud']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    public function set_id_solicitud($id) { $this->id_solicitud = $id; }
    public function get_id_solicitud() { return $this->id_solicitud; }
    public function set_fecha_reporte($f) { $this->fecha_reporte = $f; }
    public function get_fecha_reporte() { return $this->fecha_reporte; }
    public function set_descripcion_necesidad($d) { $this->descripcion_necesidad = $d; }
    public function get_descripcion_necesidad() { return $this->descripcion_necesidad; }
    public function set_nombre_solicitante($n) { $this->nombre_solicitante = $n; }
    public function get_nombre_solicitante() { return $this->nombre_solicitante; }
    public function set_monto_estimado($m) { $this->monto_estimado = $m; }
    public function get_monto_estimado() { return $this->monto_estimado; }
    public function set_estado($e) { $this->estado = $e; }
    public function get_estado() { return $this->estado; }
    public function set_presupuesto_id($id) { $this->presupuesto_id = $id; }
    public function get_presupuesto_id() { return $this->presupuesto_id; }
    public function set_prioridad($p) { $this->prioridad = $p; }
    public function get_prioridad() { return $this->prioridad; }
    public function set_activo($activo) { $this->activo = $activo; }
    public function get_activo() { return $this->activo; }

    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            throw new NegocioException("La acción '$accion' no está implementada.", HttpCodigo::BAD_REQUEST->value);
        }
        return $this->$metodo();
    }

    private function calcularDisponiblePresupuesto($contexto = [])
    {
        $estadoPendiente = EstadoSolicitud::PENDIENTE->value;
        $estadoAprobada = EstadoSolicitud::APROBADA->value;

        $sql = "SELECT 
                    (SELECT SUM(dp.monto) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = p.id_presupuesto) AS total_presupuesto,
                    COALESCE((
                        SELECT SUM(sg.monto_estimado) 
                        FROM solicitudes_gasto sg 
                        WHERE sg.presupuesto_id = p.id_presupuesto 
                          AND sg.estado IN (:estado_pen, :estado_apr)
                    ), 0) AS total_solicitado
                FROM presupuesto p
                WHERE p.id_presupuesto = :id_presupuesto";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':id_presupuesto', $this->presupuesto_id);
        $stmt->bindValue(':estado_pen', $estadoPendiente);
        $stmt->bindValue(':estado_apr', $estadoAprobada);
        $stmt->execute();
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datos || $datos['total_presupuesto'] === null) {
            return null;
        }

        $disponible = $datos['total_presupuesto'] - $datos['total_solicitado'];

        if (isset($contexto['id_solicitud']) && $contexto['id_solicitud']) {
            $sql_original = "SELECT monto_estimado FROM solicitudes_gasto WHERE id_solicitud = :id_solicitud";
            $stmt_orig = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql_original);
            $stmt_orig->bindParam(':id_solicitud', $contexto['id_solicitud']);
            $stmt_orig->execute();
            $monto_original = $stmt_orig->fetchColumn();
            if ($monto_original !== false) {
                $disponible += $monto_original;
            }
        }
        return $disponible;
    }

    private function _consultar()
    {
        $sql = "SELECT * FROM solicitudes_gasto WHERE activo = 1";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_solicitud()
    {
        $sql = "SELECT 
                    sg.id_solicitud, sg.fecha_reporte, sg.descripcion_necesidad, sg.nombre_solicitante,
                    sg.monto_estimado, sg.estado, sg.prioridad, sg.presupuesto_id,
                    p.fecha AS fecha_presupuesto,
                    MONTH(p.fecha) AS mes,
                    YEAR(p.fecha) AS anio,
                    (SELECT SUM(dp.monto) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = p.id_presupuesto) AS monto_presupuesto_total
                FROM solicitudes_gasto sg
                JOIN presupuesto p ON sg.presupuesto_id = p.id_presupuesto
                WHERE sg.id_solicitud = :id_solicitud AND sg.activo = 1";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':id_solicitud', $this->id_solicitud);
        $stmt->execute();
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datos) {
            throw new NegocioException('Solicitud no encontrada.', HttpCodigo::NO_ENCONTRADO->value);
        }

        return ['estatus' => true, 'datos' => $datos];
    }

    private function _registrar_solicitud()
    {
        $sql = "INSERT INTO solicitudes_gasto (fecha_reporte, descripcion_necesidad, nombre_solicitante, monto_estimado, estado, presupuesto_id, prioridad)
                VALUES (:fecha_reporte, :descripcion_necesidad, :nombre_solicitante, :monto_estimado, :estado, :presupuesto_id, :prioridad)";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':fecha_reporte', $this->fecha_reporte);
        $stmt->bindParam(':descripcion_necesidad', $this->descripcion_necesidad);
        $stmt->bindParam(':nombre_solicitante', $this->nombre_solicitante);
        $stmt->bindParam(':monto_estimado', $this->monto_estimado);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':presupuesto_id', $this->presupuesto_id);
        $stmt->bindParam(':prioridad', $this->prioridad);
        $stmt->execute();

        $lastId = $this->get_conex(TipoBaseDatos::NEGOCIO)->lastInsertId();
        return ['estatus' => true, 'mensaje' => 'Solicitud registrada correctamente', 'lastId' => $lastId];
    }

    private function _modificar_solicitud()
    {
        $sql = "UPDATE solicitudes_gasto SET
                    fecha_reporte = :fecha_reporte,
                    descripcion_necesidad = :descripcion_necesidad,
                    nombre_solicitante = :nombre_solicitante,
                    monto_estimado = :monto_estimado,
                    estado = :estado,
                    presupuesto_id = :presupuesto_id,
                    prioridad = :prioridad
                WHERE id_solicitud = :id_solicitud";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':id_solicitud', $this->id_solicitud);
        $stmt->bindParam(':fecha_reporte', $this->fecha_reporte);
        $stmt->bindParam(':descripcion_necesidad', $this->descripcion_necesidad);
        $stmt->bindParam(':nombre_solicitante', $this->nombre_solicitante);
        $stmt->bindParam(':monto_estimado', $this->monto_estimado);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':presupuesto_id', $this->presupuesto_id);
        $stmt->bindParam(':prioridad', $this->prioridad);
        $stmt->execute();

        return ['estatus' => true, 'mensaje' => 'Solicitud actualizada correctamente'];
    }

    private function _eliminar_solicitud()
    {
        $sql = "UPDATE solicitudes_gasto SET activo = 0 WHERE id_solicitud = :id_solicitud";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':id_solicitud', $this->id_solicitud);
        $stmt->execute();

        return ['estatus' => true, 'mensaje' => 'Solicitud eliminada correctamente'];
    }

    public function consultar_presupuesto($fecha)
    {
        $partes = explode('-', $fecha);
        if (count($partes) != 2) {
            throw new NegocioException('Formato de fecha inválido. Use YYYY-MM.', HttpCodigo::BAD_REQUEST->value);
        }
        $anio = $partes[0];
        $mes = $partes[1];

        $estadoPendiente = EstadoSolicitud::PENDIENTE->value;
        $estadoAprobada = EstadoSolicitud::APROBADA->value;

        $sql = "SELECT 
                    p.id_presupuesto, 
                    p.observacion,
                    (SELECT SUM(dp.monto) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = p.id_presupuesto) AS monto_presupuesto_total,
                    ((SELECT SUM(dp.monto) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = p.id_presupuesto) - (
                        SELECT IFNULL(SUM(sg.monto_estimado), 0)
                        FROM solicitudes_gasto sg 
                        WHERE sg.presupuesto_id = p.id_presupuesto 
                        AND sg.estado IN (:estado_pen, :estado_apr)
                    )) AS disponible
                FROM presupuesto p
                WHERE YEAR(p.fecha) = :anio AND MONTH(p.fecha) = :mes
                LIMIT 1";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':mes', $mes, PDO::PARAM_INT);
        $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
        $stmt->bindValue(':estado_pen', $estadoPendiente);
        $stmt->bindValue(':estado_apr', $estadoAprobada);
        $stmt->execute();
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datos) {
            throw new NegocioException('No hay presupuesto para la fecha indicada.', HttpCodigo::NO_ENCONTRADO->value);
        }

        return ['estatus' => true] + $datos;
    }

    public function consultar_presupuesto_disponible()
    {
        $disponible = $this->calcularDisponiblePresupuesto();
        if ($disponible === null) {
            throw new NegocioException('No se pudo calcular el presupuesto disponible.', HttpCodigo::BAD_REQUEST->value);
        }
        return ['estatus' => true, 'disponible' => $disponible];
    }

    public function listar_meses_anios_con_presupuesto()
    {
        $sql = "SELECT DISTINCT MONTH(fecha) as mes, YEAR(fecha) as anio 
                FROM presupuesto
                ORDER BY anio DESC, mes DESC";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }
}