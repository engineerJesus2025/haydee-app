<?php
namespace haydee\modelo;
use haydee\modelo\Conexion;
use PDO;

class PresupuestoMensualidad extends Conexion
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
            case 'consultar_presupuestos_asociados':
                $respuesta = $this->consultar_presupuestos_asociados();

                if ($respuesta["resultado"] == true) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }
            case 'registrar':
                $validaciones = $this->validarDatos('registrar');
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->registrar();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar asignar las mensualidades con los presupuestos"];
                }
            case 'registrar_presupuesto_mensualidad':
                $validaciones = $this->validarDatos();
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->registrar_presupuesto_mensualidad();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar asignar las mensualidades con los presupuestos"];
                }  
            case 'editar':
                $validaciones = $this->validarDatos('editar');
                if(!($validaciones["estatus"])){return $validaciones;}
                
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

    private function consultar_presupuestos_asociados()
    {
        $sql = "SELECT id_detalle_presupuesto, id_mensualidad FROM detalles_presupuesto INNER JOIN presupuesto_mensualidad ON detalles_presupuesto.id_detalle_presupuesto = presupuesto_mensualidad.presupuesto_id INNER JOIN mensualidad ON presupuesto_mensualidad.mensualidad_id = mensualidad.id_mensualidad WHERE presupuesto_mensualidad.mensualidad_id = :mensualidad_id";

        $conexion = $this->get_conex()->prepare($sql); 
        $conexion->bindParam(":mensualidad_id", $this->mensualidad_id);
        
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);        

        return ["resultado"=>$result,"datos"=>$datos];
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
    
    private function editar()
    {
        $sql = "CALL sp_sincronizar_presupuestos_mensualidad(:mensualidad_id,:presupuesto_id)";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mensualidad_id", $this->mensualidad_id);
        $conexion->bindParam(":presupuesto_id", $this->presupuesto_id);
        
        $result = $conexion->execute();   

        return $result;
    }

    private function validarDatos($consulta = '')
    {
        if ($consulta == "editar") {
            foreach (explode(",",$this->presupuesto_id) as $detalle_presupuesto) {
                if (!(isset($detalle_presupuesto))) {return ["estatus"=>false,"mensaje"=>"El id del detalle del presupuesto requerido no se recibio correctamente"];}

                if (empty($detalle_presupuesto)) {return ["estatus"=>false,"mensaje"=>"El id del detalle del presupuesto requerido esta vacio"];}

                if(is_numeric($detalle_presupuesto)){
                    if (!($this->validarClaveForanea("detalles_presupuesto","id_detalle_presupuesto",$detalle_presupuesto))) {
                            return ["estatus"=>false,"mensaje"=>"El detalle de presupuesto seleccionado no existe"];
                    }
                }
                else{return ["estatus"=>false,"mensaje"=>"El id del detalle presupuesto debe ser un valor numerico entero"];}
            }
        }
        else{
            if (!(isset($this->presupuesto_id))) {return ["estatus"=>false,"mensaje"=>"El id del Presupuesto requerido no se recibio correctamente"];}

            if (empty($this->presupuesto_id)) {return ["estatus"=>false,"mensaje"=>"El id del Presupuesto requerido esta vacio"];}

            if(is_numeric($this->presupuesto_id)){
                if ($consulta == 'registrar') {
                    if (!($this->validarClaveForanea("detalles_presupuesto","presupuesto_id",$this->presupuesto_id))) {
                        return ["estatus"=>false,"mensaje"=>"El presupuesto seleccionado no existe"];
                    }                
                }
                else if (!($this->validarClaveForanea("detalles_presupuesto","id_detalle_presupuesto",$this->presupuesto_id))) {
                    return ["estatus"=>false,"mensaje"=>"El detalle de presupuesto seleccionado no existe"];
                }
            }
            else{return ["estatus"=>false,"mensaje"=>"El id del presupuesto debe ser un valor numerico entero"];}      
        }

        if (empty($this->mensualidad_id)) {return ["estatus"=>false,"mensaje"=>"El id de la Mensualidad requerida esta vacio"];}

        if (!(isset($this->mensualidad_id))) {return ["estatus"=>false,"mensaje"=>"El id de la Mensualidad requerida no se recibio correctamente"];}

        if(is_numeric($this->mensualidad_id)){
            if (!($this->validarClaveForanea("mensualidad","id_mensualidad",$this->mensualidad_id))) {
                return ["estatus"=>false,"mensaje"=>"La mensualidad seleccionada no existe"];
            }            
        }
        else{return ["estatus"=>false,"mensaje"=>"El id de la mensualidad debe ser un valor numerico entero"];}      
        
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