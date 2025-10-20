<?php

require_once "modelo/conexion.php";

class Anio_fiscal extends Conexion
{
    private $id_anio_fiscal;
    private $fecha_inicio;
    private $fecha_cierre;
    private $estado;
    private $descripcion;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_anio_fiscal($id_anio_fiscal)
    {
        $this->id_anio_fiscal = $id_anio_fiscal;
    }

    public function get_id_anio_fiscal()
    {
        return $this->id_anio_fiscal;
    }

    public function set_fecha_inicio($fecha_inicio)
    {
        $this->fecha_inicio = $fecha_inicio;
    }

    public function get_fecha_inicio()
    {
        return $this->fecha_inicio;
    }

    public function set_fecha_cierre($fecha_cierre)
    {
        $this->fecha_cierre = $fecha_cierre;
    }

    public function get_fecha_cierre()
    {
        return $this->fecha_cierre;
    }

    public function set_estado($estado)
    {
        $this->estado = $estado;
    }

    public function get_estado()
    {
        return $this->estado;
    }

    public function set_descripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    }

    public function get_descripcion()
    {
        return $this->descripcion;
    }

    public function realizar_consulta($accion){
        switch ($accion) {
            case 'consultar':
                $respuesta = $this->consultar();

                if ($respuesta["resultado"]) {                        
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar_anio_fiscal':
                $respuesta = $this->consultar_anio_fiscal();

                if ($respuesta["resultado"]) {
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
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este Año Fiscal"];
                }

            case 'editar':
                $validaciones = $this->validarDatos("editar");
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->editar();

                if ($respuesta) {                        
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este Año Fiscal"];
                }

            case 'eliminar':
                $validaciones = $this->validarDatos("eliminar");
                if(!($validaciones["estatus"])){return $validaciones;}                

                $respuesta = $this->eliminar();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este Año Fiscal"];
                }

            case 'verificar_anio_fiscal':
                $respuesta = $this->verificar_anio_fiscal();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            default:
                return ["estatus"=>false,"mensaje"=>"A ocurrido un error en la consulta"];
                break;
        }
    }

    private function consultar()
    {
        $sql = "SELECT * FROM anio_fiscal";

        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultar_anio_fiscal()
    {
        $sql = "SELECT * FROM anio_fiscal WHERE id_anio_fiscal = :id_anio_fiscal";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":id_anio_fiscal", $this->id_anio_fiscal);

        $result = $conexion->execute();        
        
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];        
    }

    private function registrar()
    {
        $sql = "INSERT INTO anio_fiscal(fecha_inicio,fecha_cierre,estado,descripcion) VALUES (:fecha_inicio,:fecha_cierre,:estado,:descripcion)";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":fecha_inicio", $this->fecha_inicio);
        $conexion->bindParam(":fecha_cierre", $this->fecha_cierre);
        $conexion->bindParam(":estado", $this->estado);
        $conexion->bindParam(":descripcion", $this->descripcion);

        $result = $conexion->execute();
        return $result;
    }

    private function editar()
    {
        $sql = "UPDATE anio_fiscal SET fecha_inicio=:fecha_inicio, fecha_cierre=:fecha_cierre, estado=:estado, descripcion = :descripcion WHERE id_anio_fiscal=:id_anio_fiscal";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_anio_fiscal", $this->id_anio_fiscal);
        $conexion->bindParam(":fecha_inicio", $this->fecha_inicio);
        $conexion->bindParam(":fecha_cierre", $this->fecha_cierre);
        $conexion->bindParam(":estado", $this->estado);
        $conexion->bindParam(":descripcion", $this->descripcion);

        $result = $conexion->execute();
        
        return $result;
    }

    private function eliminar()
    {
        $sql = "DELETE FROM anio_fiscal WHERE id_anio_fiscal = :id_anio_fiscal";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_anio_fiscal", $this->id_anio_fiscal);

        $result = $conexion->execute();
 
        return $result;
    }

    private function verificar_anio_fiscal()
    {
        $sql = "CALL gestionar_anio_fiscal();";

        $conexion = $this->get_conex()->prepare($sql);        

        $result = $conexion->execute();   

        return $result;            
    }
        
    private function validarDatos($consulta = "registrar")
    {
        if ($consulta == "editar" || $consulta == "eliminar") {
            if (!(isset($this->id_anio_fiscal))) {return ["estatus"=>false,"mensaje"=>"El id del Año Fiscal requerido no se recibio correctamente"];}

            if (empty($this->id_anio_fiscal)) {return ["estatus"=>false,"mensaje"=>"El id del Año Fiscal requerido esta vacio"];}

            if(is_numeric($this->id_anio_fiscal)){
                if (!($this->validarClaveForanea("anio_fiscal","id_anio_fiscal",$this->id_anio_fiscal))) {
                    return ["estatus"=>false,"mensaje"=>"El Año Fiscal seleccionado no existe"];
                }
                if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
            }
            else{return ["estatus"=>false,"mensaje"=>"El id del Año Fiscal tiene debe ser un valor numerico entero"];}
        }

        if (!(isset($this->fecha_inicio) && isset($this->estado))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}


        if (empty($this->fecha_inicio) ||empty($this->estado)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

        
        if(!(is_string($this->fecha_inicio)) || !($this->validarFecha($this->fecha_inicio))){
            return ["estatus"=>false,"mensaje"=>"El campo 'fecha de inicio' no posee un valor valido"];
        }
        
        if(!(is_string($this->fecha_cierre)) || !($this->validarFecha($this->fecha_cierre))){
            return ["estatus"=>false,"mensaje"=>"El campo 'fecha de cierre' no posee un valor valido"];
        }
        
        if(!(is_string($this->estado)) || !(preg_match("/^[A-Za-z]*$/",$this->estado))){
            return ["estatus"=>false,"mensaje"=>"El campo 'estado' no posee un valor valido"];
        }
        if(!(is_string($this->descripcion)) || !(preg_match("/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ,.\s]{0,50}$/",$this->descripcion))){
            return ["estatus"=>false,"mensaje"=>"El campo 'descripcion' no posee un valor valido"];
        }

        $fecha_inicio = strtotime($this->fecha_inicio);
        $fecha_cierre = strtotime($this->fecha_cierre);

        if ($fecha_inicio >= $fecha_cierre) {
            return ["estatus"=>false,"mensaje"=>"La fecha de inicio debe ser inferior a la fecha de cierre"];            
        }

        $fecha_inicio = new DateTime($this->fecha_inicio);
        $fecha_cierre = new DateTime($this->fecha_cierre);

        $intervalo = $fecha_inicio->diff($fecha_cierre);

        if($intervalo->format("%a") < 364 || $intervalo->format("%a") > 366){
            return ["estatus"=>false,"mensaje"=>"El periodo debe ser de 1 año (364-366 dias)","var"=>$intervalo->format("%a")];
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

    private function validarFecha($fecha){
        $valores = explode('-', $fecha);
        if(count($valores) == 3 && checkdate($valores[1], $valores[2], $valores[0])){
            return true;
        }
        return false;
    }
}
?>