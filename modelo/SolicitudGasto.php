<?php
namespace haydee\modelo;
use haydee\modelo\Conexion;
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

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_solicitud($id_solicitud)
    {
        $this->id_solicitud = $id_solicitud;
    }

    public function get_id_solicitud()
    {
        return $this->id_solicitud;
    }
    public function set_fecha_reporte($fecha_reporte)
    {
        $this->fecha_reporte = $fecha_reporte;
    }

    public function get_fecha_reporte()
    {
        return $this->fecha_reporte;
    }
    public function set_descripcion_necesidad($descripcion_necesidad)
    {
        $this->descripcion_necesidad = $descripcion_necesidad;
    }

    public function get_descripcion_necesidad()
    {
        return $this->descripcion_necesidad;
    }
    public function set_nombre_solicitante($nombre_solicitante)
    {
        $this->nombre_solicitante = $nombre_solicitante;
    }

    public function get_nombre_solicitante()
    {
        return $this->nombre_solicitante;
    }
    public function set_monto_estimado($monto_estimado)
    {
        $this->monto_estimado = $monto_estimado;
    }

    public function get_monto_estimado()
    {
        return $this->monto_estimado;
    }
    public function set_estado($estado)
    {
        $this->estado = $estado;
    }

    public function get_estado()
    {
        return $this->estado;
    }
    public function set_presupuesto_id($presupuesto_id)
    {
        $this->presupuesto_id = $presupuesto_id;
    }

    public function get_presupuesto_id()
    {
        return $this->presupuesto_id;
    }
    public function set_prioridad($prioridad)
    {
        $this->prioridad = $prioridad;
    }
    public function get_prioridad()
    {
        return $this->prioridad;
    }

    public function realizar_consulta($accion)
    {
        switch ($accion) {
            case 'consultar':
                $respuesta = $this->consultar();

                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                } else {
                    return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
                }

            case 'consultar_solicitud_id':
                $respuesta = $this->consultar_solicitud_id();
                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                } else {
                    return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
                }

            case 'registrar':
                $validacion = $this->validar_datos('registrar');
                if (!$validacion["estatus"]) {
                    return $validacion; // Si la validación falla, retorna el mensaje de error
                }
                $respuesta = $this->registrar();

                if ($respuesta) {
                    return ["estatus" => true, "mensaje" => "OK"];
                } else {
                    return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar registrar la solicitud"];
                }

            case 'modificar':
                $validacion = $this->validar_datos('modificar');
                if (!$validacion["estatus"]) {
                    return $validacion; // Si la validación falla, retorna el mensaje de error
                }
                $respuesta = $this->editar_solicitud();

                if ($respuesta) {
                    return ["estatus" => true, "mensaje" => "OK"];
                } else {
                    return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar editar la solicitud"];
                }

            case 'eliminar':
                 $validacion = $this->validar_datos('eliminar');
                if (!$validacion["estatus"]) {
                    return $validacion; // Si la validación falla, retorna el mensaje de error
                }
                $respuesta = $this->eliminar_solicitud();

                if ($respuesta) {
                    $this->registrar_bitacora(ELIMINAR, GESTIONAR_SOLICITUD_GASTO, "Solicitud de " . $this->nombre_solicitante . " por " . $this->monto_estimado);
                    return ["estatus" => true, "mensaje" => "OK"];
                } else {
                    return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar eliminar la solicitud"];
                }

            case 'lastId':
                $respuesta = $this->lastId();

                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                } else {
                    return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
                }

            

            default:
                return ["resultado" => false, "mensaje" => "Acción no reconocida"];
        }

    }

    private function consultar()
    {
        $sql = "SELECT * FROM solicitudes_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return ["resultado" => $result, "datos" => $datos];

    }

    private function consultar_solicitud_id()
    {
        $sql = "SELECT 
                sg.id_solicitud, sg.fecha_reporte, sg.descripcion_necesidad, sg.nombre_solicitante,
                sg.monto_estimado, sg.monto_estimado AS monto_original, sg.estado, sg.prioridad, sg.presupuesto_id,
                p.fecha AS fecha_presupuesto,
                MONTH(p.fecha) AS mes,
                YEAR(p.fecha) AS anio,
                (SELECT SUM(dp.monto_detalle) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = p.id_presupuesto) AS monto_presupuesto_total,
                ((SELECT SUM(dp.monto_detalle) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = p.id_presupuesto) - (
                    SELECT IFNULL(SUM(sg2.monto_estimado), 0)
                    FROM solicitudes_gasto sg2
                    WHERE sg2.presupuesto_id = p.id_presupuesto
                    AND sg2.estado IN ('pendiente', 'aprobado')
                )) AS disponible
            FROM solicitudes_gasto sg
            JOIN presupuesto p ON sg.presupuesto_id = p.id_presupuesto
            WHERE sg.id_solicitud = :id
            LIMIT 1";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id", $this->id_solicitud);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);
        return ["resultado" => $result, "datos" => $datos];
    }

    private function registrar()
    {
        $sql = "INSERT INTO solicitudes_gasto(fecha_reporte,descripcion_necesidad,nombre_solicitante,monto_estimado,estado,presupuesto_id,prioridad)
                VALUES(:fecha_reporte,:descripcion_necesidad,:nombre_solicitante,:monto_estimado,:estado,:presupuesto_id,:prioridad)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":fecha_reporte", $this->fecha_reporte);
        $conexion->bindParam(":descripcion_necesidad", $this->descripcion_necesidad);
        $conexion->bindParam(":nombre_solicitante", $this->nombre_solicitante);
        $conexion->bindParam(":monto_estimado", $this->monto_estimado);
        $conexion->bindParam(":estado", $this->estado);
        $conexion->bindParam(":presupuesto_id", $this->presupuesto_id);
        $conexion->bindParam(":prioridad", $this->prioridad);
        $result = $conexion->execute();
        return $result;
    }

    private function editar_solicitud()
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
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_solicitud", $this->id_solicitud);
        $conexion->bindParam(":fecha_reporte", $this->fecha_reporte);
        $conexion->bindParam(":descripcion_necesidad", $this->descripcion_necesidad);
        $conexion->bindParam(":nombre_solicitante", $this->nombre_solicitante);
        $conexion->bindParam(":monto_estimado", $this->monto_estimado);
        $conexion->bindParam(":estado", $this->estado);
        $conexion->bindParam(":presupuesto_id", $this->presupuesto_id);
        $conexion->bindParam(":prioridad", $this->prioridad);
        $result = $conexion->execute();
        return $result;
    }

    private function eliminar_solicitud()
    {
        $sql = "DELETE FROM solicitudes_gasto WHERE id_solicitud = :id_solicitud";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_solicitud", $this->id_solicitud);
        $result = $conexion->execute();
        return $result;
    }

    private function lastId()
    {
        $sql = "SELECT MAX(id_solicitud) as last_id FROM solicitudes_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);
        return ["resultado" => $result, "datos" => $datos];
    }

    private function validarClaveForanea($tabla, $nombreClave, $valor)
    {
        $sql = "SELECT * FROM $tabla WHERE $nombreClave =:valor";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        $result = $conexion->fetch(PDO::FETCH_ASSOC);
        return ($result) ? true : false;
    }

    public function consultar_solicitudes_por_mes($mes, $anio)
    {
        $sql = "SELECT * FROM solicitudes_gasto WHERE MONTH(fecha_reporte) = :mes AND YEAR(fecha_reporte) = :anio";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mes", $mes);
        $conexion->bindParam(":anio", $anio);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        if ($result) {
            return $datos;
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
        }
    }


    // PARA CONSULTAR EL PRESUPUESTO MENSUAL (CAMBIAR HASTA QUE SE HAGA ESE MODULO)
    public function consultar_presupuesto($fecha)
    {
        list($anio, $mes) = explode('-', $fecha);

        // Se añaden alias (sg, dp) a las subconsultas para evitar ambigüedad.
        $sql = "SELECT 
                    p.id_presupuesto, 
                    p.observacion,
                    (SELECT SUM(dp.monto_detalle) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = p.id_presupuesto) AS monto_presupuesto_total,
                    ((SELECT SUM(dp.monto_detalle) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = p.id_presupuesto) - (
                        SELECT IFNULL(SUM(sg.monto_estimado), 0)
                        FROM solicitudes_gasto sg 
                        WHERE sg.presupuesto_id = p.id_presupuesto 
                        AND sg.estado IN ('pendiente', 'aprobado')
                    )) AS disponible
                FROM presupuesto p
                WHERE YEAR(p.fecha) = :anio AND MONTH(p.fecha) = :mes
                LIMIT 1";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mes", $mes);
        $conexion->bindParam(":anio", $anio);
        $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        if ($datos && $datos['id_presupuesto']) {
            return ["estatus" => true] + $datos;
        } else {
            return ["estatus" => false, "mensaje" => "No hay presupuesto registrado para esa fecha."];
        }
    }

    public function listar_solicitud_mes()
    {
        //Cambie la consulta y ahora solo usa el propio gasto
        $sql = "SELECT sg.fecha , MONTH(sg.fecha) as mes, YEAR(sg.fecha) as anio
            FROM solicitudes_gasto sg    
            GROUP BY MONTH(sg.fecha), YEAR(sg.fecha)
            ORDER BY YEAR(sg.fecha) DESC,  MONTH(sg.fecha) DESC";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->execute();
        return $conexion->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filtrar_por_mes()
    {
        list($anio_buscar, $mes_buscar) = explode('-', $this->fecha_reporte);
        $sql = "SELECT 
                    sg.id_solicitud, sg.fecha_reporte, sg.descripcion_necesidad, sg.nombre_solicitante,
                    sg.monto_estimado, sg.estado, sg.prioridad,
                    (SELECT SUM(dp.monto_detalle) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = sg.presupuesto_id) AS monto_presupuesto_total
                FROM solicitudes_gasto sg
                WHERE MONTH(sg.fecha_reporte) = :mes AND YEAR(sg.fecha_reporte) = :anio";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mes", $mes_buscar);
        $conexion->bindParam(":anio", $anio_buscar);
        $conexion->execute();
        return $conexion->fetchAll(PDO::FETCH_ASSOC);
    }

    public function consultar_presupuesto_disponible()
    {
        $sql = "SELECT 
                    (SELECT SUM(dp.monto_detalle) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = p.id_presupuesto) AS monto_presupuesto_total, 
                    IFNULL(SUM(sg.monto_estimado), 0) AS total_usado,
                    ((SELECT SUM(dp.monto_detalle) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = p.id_presupuesto) - IFNULL(SUM(sg.monto_estimado), 0)) AS disponible
                FROM presupuesto p
                LEFT JOIN solicitudes_gasto sg 
                    ON sg.presupuesto_id = p.id_presupuesto 
                    AND sg.estado IN ('pendiente', 'aprobado')
                WHERE p.id_presupuesto = :id_presupuesto
                GROUP BY p.id_presupuesto";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_presupuesto", $this->presupuesto_id);
        $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        return $datos ? ["estatus" => true] + $datos : ["estatus" => false, "mensaje" => "No se encontró el presupuesto."];
    }

    public function listar_meses_anios_con_presupuesto()
    {
        $sql = "SELECT DISTINCT MONTH(fecha) as mes, YEAR(fecha) as anio 
            FROM presupuesto
            ORDER BY anio DESC, mes DESC";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->execute();

        return $conexion->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cambiar_estado_asignado()
    {
        try {
            $sql = "UPDATE solicitudes_gasto SET estado = 'Asignado' WHERE id_solicitud = :id";
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id", $this->id_solicitud);
            $conexion->execute();

            if ($conexion->rowCount() > 0) {
                return ["estatus" => true, "mensaje" => "Estado actualizado a 'asignado'"];
            } else {
                return ["estatus" => false, "mensaje" => "No se encontró la solicitud o ya estaba asignada"];
            }
        } catch (PDOException $e) {
            return ["estatus" => false, "mensaje" => "Error al actualizar estado: " . $e->getMessage()];
        }
    }

    public function validar_datos($accion)
    {
        if ($accion == "modificar" || $accion == "eliminar") {
            
            // Validación 1.1: ID vacío
            if (empty(trim($this->id_solicitud))) {
                return ["estatus" => false, "mensaje" => "El id de la solicitud requerida esta vacio"];
            }

            // Validación 1.2: ID no es numérico
            if (!is_numeric($this->id_solicitud)) {
                 return ["estatus"=>false, "mensaje"=>"El id de la solicitud debe ser un valor numerico"];
            }

            // Validación 1.3: ID no existe en la BD
            if (!($this->validarClaveForanea("solicitudes_gasto", "id_solicitud", $this->id_solicitud))) {
                return ["estatus" => false, "mensaje" => "La solicitud seleccionada no existe"];
            }

            // Si la acción es 'eliminar' y pasó las validaciones de ID, terminamos.
            if ($accion == "eliminar") {
                return ["estatus" => true, "mensaje" => "OK"];
            }
        }


        if (empty(trim($this->descripcion_necesidad))) {
            return ["estatus" => false, "mensaje" => "La descripción de la necesidad no puede estar vacía."];
        }
        if (empty(trim($this->nombre_solicitante))) {
            return ["estatus" => false, "mensaje" => "El nombre del solicitante no puede estar vacío."];
        }
        if (empty($this->fecha_reporte)) {
            return ["estatus" => false, "mensaje" => "Debe seleccionar una fecha de reporte."];
        }
        if (empty($this->presupuesto_id)) {
            return ["estatus" => false, "mensaje" => "Debe seleccionar un presupuesto."];
        }
        if (!is_numeric($this->monto_estimado) || $this->monto_estimado <= 0) {
            return ["estatus" => false, "mensaje" => "El monto estimado debe ser un número mayor a cero."];
        }

        // Validar que el presupuesto exista
        if (!$this->validarClaveForanea('presupuesto', 'id_presupuesto', $this->presupuesto_id)) {
            return ["estatus" => false, "mensaje" => "El presupuesto seleccionado no es válido."];
        }

        // Validar que el monto no exceda el presupuesto disponible
        $presupuesto = $this->consultar_presupuesto_disponible_para_validacion($accion);
        if (!$presupuesto['estatus']) {
            return ["estatus" => false, "mensaje" => $presupuesto['mensaje']];
        }

        if ($this->monto_estimado > $presupuesto['disponible']) {
            return ["estatus" => false, "mensaje" => "El monto solicitado excede el presupuesto disponible de " . number_format($presupuesto['disponible'], 2, ',', '.') . " Bs."];
        }

        return ["estatus" => true];
    }

    private function consultar_presupuesto_disponible_para_validacion($accion)
    {
        // Se obtiene el monto original de la solicitud que se está editando
        $monto_original = 0;
        if ($accion === 'modificar') {
            $sql_monto_original = "SELECT monto_estimado FROM solicitudes_gasto WHERE id_solicitud = :id_solicitud";
            $conexion_monto = $this->get_conex()->prepare($sql_monto_original);
            $conexion_monto->bindParam(":id_solicitud", $this->id_solicitud);
            $conexion_monto->execute();
            $monto_original = $conexion_monto->fetchColumn();
        }

        // Se calcula el presupuesto disponible, sumando el monto original de la solicitud
        // en caso de una modificación para no contarlo dos veces.
        $sql = "SELECT 
                    (SELECT SUM(dp.monto_detalle) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = p.id_presupuesto) AS monto_total,
                    (IFNULL((SELECT SUM(sg.monto_estimado) FROM solicitudes_gasto sg WHERE sg.presupuesto_id = p.id_presupuesto AND sg.estado IN ('pendiente', 'aprobado')), 0) - :monto_original) AS total_usado,
                    ((SELECT SUM(dp.monto_detalle) FROM detalles_presupuesto dp WHERE dp.presupuesto_id = p.id_presupuesto) - IFNULL((SELECT SUM(sg.monto_estimado) FROM solicitudes_gasto sg WHERE sg.presupuesto_id = p.id_presupuesto AND sg.estado IN ('pendiente', 'aprobado')), 0) + :monto_original) AS disponible
                FROM presupuesto p
                WHERE p.id_presupuesto = :id_presupuesto";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_presupuesto", $this->presupuesto_id);
        $conexion->bindParam(":monto_original", $monto_original);
        $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        return $datos ? ["estatus" => true] + $datos : ["estatus" => false, "mensaje" => "No se encontró el presupuesto para la validación."];
    }

}