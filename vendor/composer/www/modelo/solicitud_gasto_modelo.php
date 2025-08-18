<?php
require_once "modelo/conexion.php";

class Solicitud_gasto extends Conexion{
    private $id_solicitud;
    private $fecha_reporte;
    private $descripcion_necesidad;
    private $nombre_solicitante;
    private $monto_estimado;
    private $estado;
    private $presupuesto_mensual_id;
    private $prioridad;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_solicitud($id_solicitud) {
        $this->id_solicitud = $id_solicitud;
    }

    public function get_id_solicitud() {
        return $this->id_solicitud;
    }
    public function set_fecha_reporte($fecha_reporte) {
        $this->fecha_reporte = $fecha_reporte;
    }

    public function get_fecha_reporte() {
        return $this->fecha_reporte;
    }
    public function set_descripcion_necesidad($descripcion_necesidad) {
        $this->descripcion_necesidad = $descripcion_necesidad;
    }

    public function get_descripcion_necesidad() {
        return $this->descripcion_necesidad;
    }
    public function set_nombre_solicitante($nombre_solicitante) {
        $this->nombre_solicitante = $nombre_solicitante;
    }

    public function get_nombre_solicitante() {
        return $this->nombre_solicitante;
    }
    public function set_monto_estimado($monto_estimado) {
        $this->monto_estimado = $monto_estimado;
    }

    public function get_monto_estimado() {
        return $this->monto_estimado;
    }
    public function set_estado($estado) {
        $this->estado = $estado;
    }

    public function get_estado() {
        return $this->estado;
    }
    public function set_presupuesto_mensual_id($presupuesto_mensual_id) {
        $this->presupuesto_mensual_id = $presupuesto_mensual_id;
    }

    public function get_presupuesto_mensual_id() {
        return $this->presupuesto_mensual_id;
    }
    public function set_prioridad($prioridad) {
        $this->prioridad = $prioridad;
    }
    public function get_prioridad() {
        return $this->prioridad;
    }

    public function consultar(){
        $sql = "SELECT * FROM solicitudes_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        if($result){
            $this->registrar_bitacora(CONSULTAR, GESTIONAR_SOLICITUD_GASTO, "TODOS LAS SOLICITUDES");
            return $datos;
        }else{
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
        }
    }

   public function consultar_solicitud_id() {
    $sql = "SELECT 
                sg.id_solicitud,
                sg.fecha_reporte,
                sg.descripcion_necesidad,
                sg.nombre_solicitante,
                sg.monto_estimado,
                sg.monto_estimado AS monto_original,
                sg.estado,
                sg.prioridad,
                sg.presupuesto_mensual_id,
                pm.monto_presupuesto,
                pm.mes,
                pm.anio,
                (pm.monto_presupuesto - (
                    SELECT IFNULL(SUM(sg2.monto_estimado), 0)
                    FROM solicitudes_gasto sg2
                    WHERE sg2.presupuesto_mensual_id = pm.id_presupuesto
                    AND sg2.estado IN ('pendiente', 'aprobado')
                )) AS disponible
            FROM solicitudes_gasto sg
            JOIN presupuestos_mensuales pm ON sg.presupuesto_mensual_id = pm.id_presupuesto
            WHERE sg.id_solicitud = :id
            LIMIT 1";

    $conexion = $this->get_conex()->prepare($sql);
    $conexion->bindParam(":id", $this->id_solicitud);
    $conexion->execute();

    $datos = $conexion->fetch(PDO::FETCH_ASSOC);

    if ($datos) {
        return $datos;
    } else {
        return ["estatus" => false, "mensaje" => "No se encontró la solicitud."];
    }
}

    public function registrar(){
        $sql = "INSERT INTO solicitudes_gasto(fecha_reporte,descripcion_necesidad,nombre_solicitante,monto_estimado,estado,presupuesto_mensual_id,prioridad)
                VALUES(:fecha_reporte,:descripcion_necesidad,:nombre_solicitante,:monto_estimado,:estado,:presupuesto_mensual_id,:prioridad)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":fecha_reporte", $this->fecha_reporte);
        $conexion->bindParam(":descripcion_necesidad", $this->descripcion_necesidad);
        $conexion->bindParam(":nombre_solicitante", $this->nombre_solicitante);
        $conexion->bindParam(":monto_estimado", $this->monto_estimado);
        $conexion->bindParam(":estado", $this->estado);
        $conexion->bindParam(":presupuesto_mensual_id", $this->presupuesto_mensual_id);
        $conexion->bindParam(":prioridad", $this->prioridad);
        $result = $conexion->execute();
        if($result){
            $this->registrar_bitacora(REGISTRAR, GESTIONAR_SOLICITUD_GASTO, "Solicitud de " . $this->nombre_solicitante . " por " . $this->monto_estimado);
            return ["estatus" => true, "mensaje" => "OK"];
        }else{
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar registrar la solicitud"];
        }
    }

    public function editar_solicitud(){
        $sql = "UPDATE solicitudes_gasto SET
                fecha_reporte = :fecha_reporte,
                descripcion_necesidad = :descripcion_necesidad,
                nombre_solicitante = :nombre_solicitante,
                monto_estimado = :monto_estimado,
                estado = :estado,
                presupuesto_mensual_id = :presupuesto_mensual_id,
                prioridad = :prioridad
                WHERE id_solicitud = :id_solicitud";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_solicitud", $this->id_solicitud);
        $conexion->bindParam(":fecha_reporte", $this->fecha_reporte);
        $conexion->bindParam(":descripcion_necesidad", $this->descripcion_necesidad);
        $conexion->bindParam(":nombre_solicitante", $this->nombre_solicitante);
        $conexion->bindParam(":monto_estimado", $this->monto_estimado);
        $conexion->bindParam(":estado", $this->estado);
        $conexion->bindParam(":presupuesto_mensual_id", $this->presupuesto_mensual_id);
        $conexion->bindParam(":prioridad", $this->prioridad);
        $result = $conexion->execute();
        if($result){
            $this->registrar_bitacora(MODIFICAR, GESTIONAR_SOLICITUD_GASTO, "Solicitud de " . $this->nombre_solicitante . " por " . $this->monto_estimado);
            return ["estatus" => true, "mensaje" => "OK"];
        }else{
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar editar la solicitud"];
        }
    }

    public function eliminar_solicitud(){
        $solicitud_eliminada = $this->consultar_solicitud_id();

        $sql = "DELETE FROM solicitudes_gasto WHERE id_solicitud = :id_solicitud";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_solicitud", $this->id_solicitud);
        $result = $conexion->execute();
        if($result){
            $this->registrar_bitacora(ELIMINAR, GESTIONAR_SOLICITUD_GASTO, "Solicitud de " . $solicitud_eliminada["nombre_solicitante"] . " por " . $solicitud_eliminada["monto_estimado"]);
            return ["estatus" => true, "mensaje" => "OK"];
        }else{
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar eliminar la solicitud"];
        }
    }

        public function lastId(){
        $sql = "SELECT MAX(id_solicitud) as last_id FROM solicitudes_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);
        if ($result == true) {
            return ["estatus" => true, "mensaje" => $datos["last_id"]];
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
        }
    }

        public function validarClaveForanea($tabla,$nombreClave,$valor){
        $sql="SELECT * FROM $tabla WHERE $nombreClave =:valor";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        $result = $conexion->fetch(PDO::FETCH_ASSOC);
        return ($result)?true:false;
    }

    public function consultar_solicitudes_por_mes($mes, $anio) {
        $sql = "SELECT * FROM solicitudes_gasto WHERE MONTH(fecha_reporte) = :mes AND YEAR(fecha_reporte) = :anio";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mes", $mes);
        $conexion->bindParam(":anio", $anio);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        if($result){
            return $datos;
        }else{
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
        }
    }


    // PARA CONSULTAR EL PRESUPUESTO MENSUAL (CAMBIAR HASTA QUE SE HAGA ESE MODULO)
public function consultar_presupuesto_mensual($mes, $anio) {
    $sql = "SELECT 
                id_presupuesto, 
                monto_presupuesto, 
                descripcion,
                (monto_presupuesto - (
                    SELECT IFNULL(SUM(monto_estimado), 0)
                    FROM solicitudes_gasto 
                    WHERE presupuesto_mensual_id = pm.id_presupuesto 
                    AND estado IN ('pendiente', 'aprobado')
                )) AS disponible
            FROM presupuestos_mensuales pm
            WHERE mes = :mes AND anio = :anio
            LIMIT 1";

    $conexion = $this->get_conex()->prepare($sql);
    $conexion->bindParam(":mes", $mes);
    $conexion->bindParam(":anio", $anio);
    $conexion->execute();

    $datos = $conexion->fetch(PDO::FETCH_ASSOC);

    if ($datos) {
        return ["estatus" => true] + $datos;
    } else {
        return ["estatus" => false, "mensaje" => "No hay presupuesto registrado para esa fecha."];
    }
}

    public function listar_solicitud_mes() {
    //Cambie la consulta y ahora solo usa el propio gasto
    $sql = "SELECT sg.fecha , MONTH(sg.fecha) as mes, YEAR(sg.fecha) as anio
            FROM solicitudes_gasto sg    
            GROUP BY MONTH(sg.fecha), YEAR(sg.fecha)
            ORDER BY YEAR(sg.fecha) DESC,  MONTH(sg.fecha) DESC";

    $conexion = $this->get_conex()->prepare($sql);
    $conexion->execute();
    return $conexion->fetchAll(PDO::FETCH_ASSOC);
}

public function filtrar_por_mes() {
    list($anio_buscar,$mes_buscar) = explode('-', $this->fecha_reporte);
    // esto de arriba saca el mes y año de la fecha y con eso buscamos
    $sql = "SELECT 
                sg.id_solicitud,
                sg.fecha_reporte,
                sg.descripcion_necesidad,
                sg.nombre_solicitante,
                sg.monto_estimado,
                sg.estado,
                sg.prioridad,
                pm.monto_presupuesto AS monto_presupuesto
            FROM solicitudes_gasto sg
            LEFT JOIN presupuestos_mensuales pm ON sg.presupuesto_mensual_id = pm.id_presupuesto_mensual
            WHERE MONTH(sg.fecha_reporte) = :mes && YEAR(sg.fecha_reporte) = :anio";

    $conexion = $this->get_conex()->prepare($sql);
    $conexion->bindParam(":mes", $mes_buscar);
    $conexion->bindParam(":anio", $anio_buscar);
    $conexion->execute();
    return $conexion->fetchAll(PDO::FETCH_ASSOC);
}

public function consultar_presupuesto_disponible() {
    try {
        $sql = "SELECT 
                    pm.monto_presupuesto, 
                    IFNULL(SUM(sg.monto_estimado), 0) AS total_usado,
                    (pm.monto_presupuesto - IFNULL(SUM(sg.monto_estimado), 0)) AS disponible
                FROM presupuestos_mensuales pm
                LEFT JOIN solicitudes_gasto sg 
                    ON sg.presupuesto_mensual_id = pm.id_presupuesto 
                    AND sg.estado IN ('pendiente', 'aprobado')
                WHERE pm.id_presupuesto = :id_presupuesto
                GROUP BY pm.monto_presupuesto";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_presupuesto", $this->presupuesto_mensual_id);
        $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        if ($datos) {
            return [
                "estatus" => true,
                "monto_presupuesto" => $datos["monto_presupuesto"],
                "total_usado" => $datos["total_usado"],
                "disponible" => $datos["disponible"]
            ];
        } else {
            return [
                "estatus" => false,
                "mensaje" => "No se encontró el presupuesto indicado."
            ];
        }
    } catch (PDOException $e) {
        return [
            "estatus" => false,
            "mensaje" => "Error al consultar presupuesto: " . $e->getMessage()
        ];
    }
}

public function listar_meses_anios_con_presupuesto() {
    $sql = "SELECT DISTINCT mes, anio 
            FROM presupuestos_mensuales 
            ORDER BY anio DESC, mes DESC";

    $conexion = $this->get_conex()->prepare($sql);
    $conexion->execute();

    return $conexion->fetchAll(PDO::FETCH_ASSOC);
}

public function cambiar_estado_asignado() {
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
    


}