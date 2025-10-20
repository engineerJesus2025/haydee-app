<?php

require_once "modelo/conexion.php";

class Detalles_presupuesto extends Conexion
{
    private $id_detalle_presupuesto;
    private $monto_detalle;
    private $nombre_detalle;    
    private $presupuesto_id;
    private $tipo_gasto_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_detalle_presupuesto($id_detalle_presupuesto)
    {
        $this->id_detalle_presupuesto = $id_detalle_presupuesto;
    }

    public function get_id_detalle_presupuesto()
    {
        return $this->id_detalle_presupuesto;
    }

    public function set_monto_detalle($monto_detalle)
    {
        $this->monto_detalle = $monto_detalle;
    }

    public function get_monto_detalle()
    {
        return $this->monto_detalle;
    }

    public function set_nombre_detalle($nombre_detalle)
    {
        $this->nombre_detalle = $nombre_detalle;
    }

    public function get_nombre_detalle()
    {
        return $this->nombre_detalle;
    }

    public function set_presupuesto_id($presupuesto_id)
    {
        $this->presupuesto_id = $presupuesto_id;
    }

    public function get_presupuesto_id()
    {
        return $this->presupuesto_id;
    }

    public function set_tipo_gasto_id($tipo_gasto_id)
    {
        $this->tipo_gasto_id = $tipo_gasto_id;
    }

    public function get_tipo_gasto_id()
    {
        return $this->tipo_gasto_id;
    }

    public function realizar_consulta($accion){
        switch ($accion) {
            case 'consultar':
                $respuesta = $this->consultar();

                if ($respuesta["resultado"] == true) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar_detalles_presupuestos':
                $respuesta = $this->consultar_detalles_presupuestos();

                if ($respuesta["resultado"] == true) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'registrar':
                $validaciones = $this->validarDatos();
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->registrar();
                if ($respuesta) {                    
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este presupuesto"];
                }

            case 'eliminar':
                $validaciones = $this->validarDatos("eliminar");
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->eliminar();

                if ($respuesta["resultado"]) {
                    if ($respuesta["fila_afectada"] < 1) {
                        return ["estatus"=>false,"mensaje"=>"No se eliminó ningún registro"];
                    }
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este presupuesto"];
                }
            default:
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error en la consulta"];
                break;
        }
    }

    public function consultar()
    {
        $sql = "SELECT * FROM detalles_presupuesto";

        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return ["resultado"=>$result,"datos"=>$datos];        
    }

    public function consultar_detalles_presupuestos()
    {
        $sql = "SELECT * FROM detalles_presupuesto WHERE presupuesto_id = :presupuesto_id";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":presupuesto_id", $this->presupuesto_id);

        $result = $conexion->execute();        
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return ["resultado"=>$result,"datos"=>$datos];        
    }

    public function registrar()
    {
        $sql = "INSERT INTO detalles_presupuesto(monto_detalle,nombre_detalle,presupuesto_id, tipo_gasto_id) VALUES (:monto_detalle,:nombre_detalle,:presupuesto_id, :tipo_gasto_id)";
            
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":monto_detalle", $this->monto_detalle);
        $conexion->bindParam(":nombre_detalle", $this->nombre_detalle);        
        $conexion->bindParam(":presupuesto_id", $this->presupuesto_id);
        $conexion->bindParam(":tipo_gasto_id", $this->tipo_gasto_id);
        $result = $conexion->execute();

        $conexion = $this->get_conex();//otra conexion para buscar el ultimo id
        $res = $conexion->lastInsertId();

        return $result;
    }

    public function eliminar()
    {
        $sql = "DELETE FROM detalles_presupuesto WHERE presupuesto_id = :presupuesto_id";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":presupuesto_id", $this->presupuesto_id);
        $result = $conexion->execute();
        $filas_afectadas = $conexion->rowCount();
        
        return ["resultado"=>$result,"fila_afectada"=>$filas_afectadas];
    }
    
    private function validarDatos($consulta = "registrar")
    {   
        if ($consulta == "eliminar") {
            if (empty($this->presupuesto_id)){
                return ["estatus"=>false,"mensaje"=>"El id del presupuesto se envio vacío"];
            }
            if (!($this->validarClaveForanea("presupuesto","id_presupuesto",$this->presupuesto_id))) {
                return ["estatus"=>false,"mensaje"=>"El id del presupuesto seleccionado no existe"];
            }
            return ["estatus"=>true,"mensaje"=>"OK"];
        }
        if (!(isset($this->monto_detalle) && isset($this->nombre_detalle) && isset($this->presupuesto_id) && isset($this->tipo_gasto_id))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente","var"=>[$this->monto_detalle,$this->nombre_detalle,$this->presupuesto_id]];}

        if (empty($this->nombre_detalle) || empty($this->presupuesto_id) || empty($this->tipo_gasto_id)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

        if(!(is_string($this->monto_detalle)) || !(preg_match("/^[0-9]{0,12}[,.]{0,1}[0-9]{0,2}$/",$this->monto_detalle))){
            return ["estatus"=>false,"mensaje"=>"El campo 'monto' de uno de los presupuestos no posee un valor valido"];
        }
        
        if(!(is_string($this->nombre_detalle)) || !(preg_match("/^[A-Za-z áéíóúÑñ \b]*$/",$this->nombre_detalle))){
            return ["estatus"=>false,"mensaje"=>"El campo 'nombre' de uno de los presupuestos no posee un valor valido"];
        }
        if(is_numeric($this->presupuesto_id)){
            if (!($this->validarClaveForanea("presupuesto","id_presupuesto",$this->presupuesto_id))) {
                return ["estatus"=>false,"mensaje"=>"El id del presupuesto seleccionado no existe"];
            }            
        }
        else{
            return ["estatus"=>false,"mensaje"=>"El id del presupuesto seleccionado no posee un valor valido"];
        }

        if(is_numeric($this->tipo_gasto_id)){
            if (!($this->validarClaveForanea("tipo_gasto","id_tipo_gasto",$this->tipo_gasto_id))) {
                return ["estatus"=>false,"mensaje"=>"El id del tipo del gasto seleccionado no existe"];
            }            
        }
        else{
            return ["estatus"=>false,"mensaje"=>"El id del tipo del gasto seleccionado no posee un valor valido"];
        }
        
        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    private function validarClaveForanea($tabla,$nombreClave,$valor)
    {

        $sql="SELECT * FROM $tabla WHERE $nombreClave =:valor";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        $result = $conexion->fetch(PDO::FETCH_ASSOC);

        return ($result)?true:false;
    }
}
?>