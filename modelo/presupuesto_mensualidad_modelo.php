<?php

require_once "modelo/conexion.php";

class Presupuesto_mensualidad extends Conexion
{
    private $id_presupuesto_mensualidad;
    private $presupuesto_id;
    private $mensualidad_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_presupuesto_mensualidad($id_presupuesto_mensualidad)
    {
        $this->id_presupuesto_mensualidad = $id_presupuesto_mensualidad;
    }

    public function get_id_presupuesto_mensualidad()
    {
        return $this->id_presupuesto_mensualidad;
    }

    public function set_presupuesto_id($presupuesto_id)
    {
        $this->presupuesto_id = $presupuesto_id;
    }

    public function get_presupuesto_id()
    {
        return $this->presupuesto_id;
    }

    public function set_mensualidad_id($mensualidad_id)
    {
        $this->mensualidad_id = $mensualidad_id;
    }

    public function get_mensualidad_id()
    {
        return $this->mensualidad_id;
    }

    public function realizar_consulta($accion){
        switch ($accion) {
            case 'registrar':
                $respuesta = $this->registrar();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar asignar las mensualidades con los presupuestos"];
                }

            case 'registrar_presupuesto_mensualidad':
                $respuesta = $this->registrar_presupuesto_mensualidad();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar asignar las mensualidades con los presupuestos"];
                }

            case 'consultar_presupuestos_asociados':
                $respuesta = $this->consultar_presupuestos_asociados();

                if ($respuesta["resultado"] == true) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'editar':
                $respuesta = $this->editar();

                if ($respuesta) {                    
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar asignar las mensualidades con los presupuestos"];
                }
                
            default:
                return ["estatus"=>false,"mensaje"=>"A ocurrido un error en la consulta"];
                break;
        }
    }

    private function registrar()
    {
        $sql = "INSERT INTO presupuesto_mensualidad (presupuesto_mensualidad.presupuesto_id,presupuesto_mensualidad.mensualidad_id)
            SELECT detalles_presupuesto.id_detalle_presupuesto, :mensualidad_id FROM detalles_presupuesto WHERE detalles_presupuesto.presupuesto_id = :presupuesto_id";
        
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mensualidad_id", $this->mensualidad_id);
        $conexion->bindParam(":presupuesto_id", $this->presupuesto_id);

        $result = $conexion->execute();

        return $result;
    }

    private function registrar_presupuesto_mensualidad()
    {
        $sql = "INSERT INTO presupuesto_mensualidad (presupuesto_mensualidad.presupuesto_id,presupuesto_mensualidad.mensualidad_id) VALUES (:presupuesto_id,:mensualidad_id)";
        
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mensualidad_id", $this->mensualidad_id);
        $conexion->bindParam(":presupuesto_id", $this->presupuesto_id);

        $result = $conexion->execute();

        return $result;
    }

    private function consultar_presupuestos_asociados()
    {
        $sql = "SELECT id_detalle_presupuesto, id_mensualidad FROM detalles_presupuesto INNER JOIN presupuesto_mensualidad ON detalles_presupuesto.id_detalle_presupuesto = presupuesto_mensualidad.presupuesto_id INNER JOIN mensualidad ON presupuesto_mensualidad.mensualidad_id = mensualidad.id_mensualidad WHERE presupuesto_mensualidad.mensualidad_id = :mensualidad_id";

        $conexion = $this->get_conex()->prepare($sql); 
        $conexion->bindParam(":mensualidad_id", $this->mensualidad_id);
        
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);        

        return ["resultado"=>$result,"datos"=>$datos];
    }
    
    private function editar()
    {
        $sql = "CALL sp_sincronizar_presupuestos_mensualidad(:mensualidad_id,:presupuesto_id)";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mensualidad_id", $this->mensualidad_id);
        $conexion->bindParam(":presupuesto_id", $this->presupuesto_id);
        
        $result = $conexion->execute();   

        return $result;
    }

}
?>