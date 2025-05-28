<?php

require_once "modelo/conexion.php";

class Gastos_mensualidades extends Conexion
{
    private $id_gasto_mensualidad;
    private $gasto_id;
    private $mensualidad_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_gasto_mensualidad($id_gasto_mensualidad)
    {
        $this->id_gasto_mensualidad = $id_gasto_mensualidad;
    }

    public function get_id_gasto_mensualidad()
    {
        return $this->id_gasto_mensualidad;
    }

    public function set_gasto_id($gasto_id)
    {
        $this->gasto_id = $gasto_id;
    }

    public function get_gasto_id()
    {
        return $this->gasto_id;
    }

    public function set_mensualidad_id($mensualidad_id)
    {
        $this->mensualidad_id = $mensualidad_id;
    }

    public function get_mensualidad_id()
    {
        return $this->mensualidad_id;
    }

    public function registrar()
    {
        $sql = "INSERT INTO gastos_mensualidades(gasto_id,mensualidad_id) VALUES (:gasto_id,:mensualidad_id)";
        
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":gasto_id", $this->gasto_id);
        $conexion->bindParam(":mensualidad_id", $this->mensualidad_id);

        $result = $conexion->execute();

        if ($result) {            
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este usuario"];
        }
    }

    public function consultar()
    {
        $sql = "SELECT * FROM gastos_mensualidades WHERE gastos_mensualidades.mensualidad_id = :mensualidad_id";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mensualidad_id", $this->mensualidad_id);
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);        

        if ($result == true) {            
            return ["estatus"=>true,"mensaje"=>$datos];
        } 
        else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }
    
    public function eliminar()
    {
        $sql = "DELETE FROM gastos_mensualidades WHERE mensualidad_id = :mensualidad_id";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mensualidad_id", $this->mensualidad_id);
        $result = $conexion->execute();     

        if ($result) {
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este Rol"];
        }
    }
}
?>