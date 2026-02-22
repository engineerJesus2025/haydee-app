<?php
namespace haydee\modelo;

use PDO;
use PDOException;

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

    // Reglas de validación centralizadas
    private $reglas = [
        'id_solicitud' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'solicitudes_gasto', 'campo' => 'id_solicitud']
        ],
        'fecha_reporte' => [
            'regex' => '/^\d{4}-\d{2}-\d{2}$/',
            'custom' => 'validarFecha'
        ],
        'descripcion_necesidad' => [
            'regex' => '/^[A-Za-z0-9áéíóúÁÉÍÓÚñÑ\s,.;:()\-]{3,255}$/'
        ],
        'nombre_solicitante' => [
            'regex' => '/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]{3,50}$/'
        ],
        'monto_estimado' => [
            'regex' => '/^\d+(\.\d{1,2})?$/'
        ],
        'estado' => [
            'regex' => '/^(pendiente|aprobado|rechazado|asignado)$/i'
        ],
        'presupuesto_id' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'presupuesto', 'campo' => 'id_presupuesto']
        ],
        'prioridad' => [
            'regex' => '/^(1|2|3)$/i'
        ]
    ];

    // Getters y Setters
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

    /**
     * Enruta la acción al método privado correspondiente.
     */
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

    // -----------------------------------------------------------------
    // Método de validación centralizado
    // -----------------------------------------------------------------

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
                return [
                    'estatus' => false,
                    'mensaje' => "El campo '$campo' no tiene un getter definido."
                ];
            }
            $valor = $this->$getter();

            // Requerido
            if ($valor === null) {
                return [
                    'estatus' => false,
                    'mensaje' => "El campo '$campo' es requerido y no se ha establecido."
                ];
            }
            if (is_string($valor) && trim($valor) === '') {
                return [
                    'estatus' => false,
                    'mensaje' => "El campo '$campo' no puede estar vacío."
                ];
            }

            // Validar con expresión regular
            if (isset($regla['regex'])) {
                if (!preg_match($regla['regex'], (string)$valor)) {
                    return [
                        'estatus' => false,
                        'mensaje' => "El campo '$campo' no tiene un formato válido."
                    ];
                }
            }

            // Validación personalizada
            if (isset($regla['custom']) && method_exists($this, $regla['custom'])) {
                if (!$this->{$regla['custom']}($valor)) {
                    return [
                        'estatus' => false,
                        'mensaje' => "El campo '$campo' no es válido."
                    ];
                }
            }

            // Validar existencia en otra tabla (foránea)
            if (isset($regla['exists'])) {
                $tabla = $regla['exists']['tabla'];
                $campoFor = $regla['exists']['campo'] ?? $campo;
                if (!$this->existeEnTabla($tabla, $campoFor, $valor)) {
                    return [
                        'estatus' => false,
                        'mensaje' => "El valor del campo '$campo' no existe en la tabla $tabla."
                    ];
                }
            }
        }

        // Validaciones adicionales que dependen de varios campos
        if (in_array('monto_estimado', $campos) && in_array('presupuesto_id', $campos)) {
            $disponible = $this->calcularDisponiblePresupuesto($contexto);
            if ($disponible !== null && $this->monto_estimado > $disponible) {
                return [
                    'estatus' => false,
                    'mensaje' => "El monto solicitado excede el presupuesto disponible ("
                        . number_format($disponible, 2, ',', '.') . " Bs)."
                ];
            }
        }

        return ['estatus' => true];
    }

    /**
     * Verifica si un valor existe en una tabla específica (usa BD negocio).
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

    /**
     * Validación personalizada para fecha.
     */
    private function validarFecha($fecha)
    {
        $valores = explode('-', $fecha);
        return count($valores) == 3 && checkdate((int)$valores[1], (int)$valores[2], (int)$valores[0]);
    }

    /**
     * Calcula el presupuesto disponible para un presupuesto dado.
     * @param array $contexto Puede contener 'id_solicitud' para excluir la solicitud actual en edición.
     * @return float|null Monto disponible o null si no se pudo calcular.
     */
    private function calcularDisponiblePresupuesto($contexto = [])
    {
        $sql = "SELECT 
                    (SELECT SUM(dp.monto) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = p.id_presupuesto) AS total_presupuesto,
                    COALESCE((
                        SELECT SUM(sg.monto_estimado) 
                        FROM solicitudes_gasto sg 
                        WHERE sg.presupuesto_id = p.id_presupuesto 
                          AND sg.estado IN ('pendiente', 'aprobado')
                    ), 0) AS total_solicitado
                FROM presupuesto p
                WHERE p.id_presupuesto = :id_presupuesto";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_presupuesto', $this->presupuesto_id);
            $stmt->execute();
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$datos || $datos['total_presupuesto'] === null) {
                return null;
            }
            $disponible = $datos['total_presupuesto'] - $datos['total_solicitado'];
            // Si estamos editando, hay que sumar el monto original de esta solicitud (porque ya está incluido en total_solicitado)
            if (isset($contexto['id_solicitud']) && $contexto['id_solicitud']) {
                $sql_original = "SELECT monto_estimado FROM solicitudes_gasto WHERE id_solicitud = :id_solicitud";
                $stmt_orig = $this->get_conex('negocio')->prepare($sql_original);
                $stmt_orig->bindParam(':id_solicitud', $contexto['id_solicitud']);
                $stmt_orig->execute();
                $monto_original = $stmt_orig->fetchColumn();
                if ($monto_original !== false) {
                    $disponible += $monto_original;
                }
            }
            return $disponible;
        } catch (PDOException $e) {
            error_log("Error en calcularDisponiblePresupuesto: " . $e->getMessage());
            return null;
        }
    }

    // -----------------------------------------------------------------
    // Métodos privados (acciones)
    // -----------------------------------------------------------------

    private function _consultar()
    {
        $sql = "SELECT * FROM solicitudes_gasto WHERE activo = 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar solicitudes'];
        }
    }

    private function _consultar_solicitud_id()
    {
        $validacion = $this->validar(['id_solicitud']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

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
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_solicitud', $this->id_solicitud);
            $stmt->execute();
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$datos) {
                return ['estatus' => false, 'mensaje' => 'Solicitud no encontrada'];
            }
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_solicitud_id: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar la solicitud'];
        }
    }

    private function _registrar()
    {
        $campos = ['fecha_reporte', 'descripcion_necesidad', 'nombre_solicitante', 'monto_estimado', 'estado', 'presupuesto_id', 'prioridad'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "INSERT INTO solicitudes_gasto (fecha_reporte, descripcion_necesidad, nombre_solicitante, monto_estimado, estado, presupuesto_id, prioridad)
                VALUES (:fecha_reporte, :descripcion_necesidad, :nombre_solicitante, :monto_estimado, :estado, :presupuesto_id, :prioridad)";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':fecha_reporte', $this->fecha_reporte);
            $stmt->bindParam(':descripcion_necesidad', $this->descripcion_necesidad);
            $stmt->bindParam(':nombre_solicitante', $this->nombre_solicitante);
            $stmt->bindParam(':monto_estimado', $this->monto_estimado);
            $stmt->bindParam(':estado', $this->estado);
            $stmt->bindParam(':presupuesto_id', $this->presupuesto_id);
            $stmt->bindParam(':prioridad', $this->prioridad);
            $stmt->execute();
            $lastId = $this->get_conex('negocio')->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Solicitud registrada correctamente', 'lastId' => $lastId];
        } catch (PDOException $e) {
            error_log("Error en _registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar la solicitud'];
        }
    }

    private function _modificar()
    {
        $campos = ['id_solicitud', 'fecha_reporte', 'descripcion_necesidad', 'nombre_solicitante', 'monto_estimado', 'estado', 'presupuesto_id', 'prioridad'];
        $contexto = ['id_solicitud' => $this->id_solicitud];
        $validacion = $this->validar($campos, $contexto);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "UPDATE solicitudes_gasto SET
                    fecha_reporte = :fecha_reporte,
                    descripcion_necesidad = :descripcion_necesidad,
                    nombre_solicitante = :nombre_solicitante,
                    monto_estimado = :monto_estimado,
                    estado = :estado,
                    presupuesto_id = :presupuesto_id,
                    prioridad = :prioridad
                WHERE id_solicitud = :id_solicitud";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
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
        } catch (PDOException $e) {
            error_log("Error en _modificar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar la solicitud'];
        }
    }

    private function _eliminar()
    {
        $validacion = $this->validar(['id_solicitud']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "UPDATE solicitudes_gasto SET activo = 0 WHERE id_solicitud = :id_solicitud";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_solicitud', $this->id_solicitud);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Solicitud eliminada correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar la solicitud'];
        }
    }

    private function _lastId()
    {
        $sql = "SELECT MAX(id_solicitud) as last_id FROM solicitudes_gasto";
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

    // -----------------------------------------------------------------
    // Métodos públicos auxiliares (reportes, consultas adicionales)
    // -----------------------------------------------------------------

    public function consultar_solicitudes_por_mes($mes, $anio)
    {
        $sql = "SELECT * FROM solicitudes_gasto WHERE MONTH(fecha_reporte) = :mes AND YEAR(fecha_reporte) = :anio";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':mes', $mes, PDO::PARAM_INT);
            $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en consultar_solicitudes_por_mes: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar solicitudes por mes'];
        }
    }

    public function consultar_presupuesto($fecha)
    {
        $partes = explode('-', $fecha);
        if (count($partes) != 2) {
            return ['estatus' => false, 'mensaje' => 'Formato de fecha inválido. Use YYYY-MM'];
        }
        $anio = $partes[0];
        $mes = $partes[1];

        $sql = "SELECT 
                    p.id_presupuesto, 
                    p.observacion,
                    (SELECT SUM(dp.monto) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = p.id_presupuesto) AS monto_presupuesto_total,
                    ((SELECT SUM(dp.monto) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = p.id_presupuesto) - (
                        SELECT IFNULL(SUM(sg.monto_estimado), 0)
                        FROM solicitudes_gasto sg 
                        WHERE sg.presupuesto_id = p.id_presupuesto 
                        AND sg.estado IN ('pendiente', 'aprobado')
                    )) AS disponible
                FROM presupuesto p
                WHERE YEAR(p.fecha) = :anio AND MONTH(p.fecha) = :mes
                LIMIT 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':mes', $mes, PDO::PARAM_INT);
            $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt->execute();
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$datos) {
                return ['estatus' => false, 'mensaje' => 'No hay presupuesto para la fecha indicada'];
            }
            return ['estatus' => true] + $datos;
        } catch (PDOException $e) {
            error_log("Error en consultar_presupuesto: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar presupuesto'];
        }
    }

    public function listar_solicitud_mes()
    {
        $sql = "SELECT DISTINCT MONTH(fecha_reporte) as mes, YEAR(fecha_reporte) as anio
                FROM solicitudes_gasto
                ORDER BY anio DESC, mes DESC";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en listar_solicitud_mes: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al listar meses'];
        }
    }

    public function filtrar_por_mes()
    {
        if (!$this->fecha_reporte) {
            return ['estatus' => false, 'mensaje' => 'Fecha no establecida'];
        }
        $partes = explode('-', $this->fecha_reporte);
        if (count($partes) != 2) {
            return ['estatus' => false, 'mensaje' => 'Formato de fecha inválido. Use YYYY-MM'];
        }
        $anio = $partes[0];
        $mes = $partes[1];

        $sql = "SELECT 
                    sg.id_solicitud, sg.fecha_reporte, sg.descripcion_necesidad, sg.nombre_solicitante,
                    sg.monto_estimado, sg.estado, sg.prioridad,
                    (SELECT SUM(dp.monto) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = sg.presupuesto_id) AS monto_presupuesto_total
                FROM solicitudes_gasto sg
                WHERE MONTH(sg.fecha_reporte) = :mes AND YEAR(sg.fecha_reporte) = :anio";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':mes', $mes, PDO::PARAM_INT);
            $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en filtrar_por_mes: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al filtrar solicitudes por mes'];
        }
    }

    public function consultar_presupuesto_disponible()
    {
        $validacion = $this->validar(['presupuesto_id']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $disponible = $this->calcularDisponiblePresupuesto();
        if ($disponible === null) {
            return ['estatus' => false, 'mensaje' => 'No se pudo calcular el presupuesto disponible'];
        }
        return ['estatus' => true, 'disponible' => $disponible];
    }

    public function listar_meses_anios_con_presupuesto()
    {
        $sql = "SELECT DISTINCT MONTH(fecha) as mes, YEAR(fecha) as anio 
                FROM presupuesto
                ORDER BY anio DESC, mes DESC";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en listar_meses_anios_con_presupuesto: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al listar meses con presupuesto'];
        }
    }

    public function cambiar_estado_asignado()
    {
        $validacion = $this->validar(['id_solicitud']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "UPDATE solicitudes_gasto SET estado = 'asignado' WHERE id_solicitud = :id_solicitud";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_solicitud', $this->id_solicitud);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return ['estatus' => true, 'mensaje' => 'Estado actualizado a "asignado"'];
            } else {
                return ['estatus' => false, 'mensaje' => 'No se encontró la solicitud o ya estaba asignada'];
            }
        } catch (PDOException $e) {
            error_log("Error en cambiar_estado_asignado: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar estado'];
        }
    }
}
?>