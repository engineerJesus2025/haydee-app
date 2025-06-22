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

    //hace lo que dice
    public function consultar()
    {
        $sql = "SELECT * FROM anio_fiscal";

        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if ($result == true) {
            $this->registrar_bitacora(CONSULTAR, GESTIONAR_ANIO_FISCAL, "TODOS LOS AÑOS FISCALES");//registra cuando se entra al modulo de ususario

            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function consultar_anio_fiscal()
    {
        $sql = "SELECT * FROM anio_fiscal WHERE id_anio_fiscal = :id_anio_fiscal";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":id_anio_fiscal", $this->id_anio_fiscal);

        $result = $conexion->execute();        
        
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function registrar()
    {
        //Validamos los datos obtenidos del controlador (validaciones back-end)
        $validaciones = $this->validarDatos();
        if(!($validaciones["estatus"])){return $validaciones;}

        $sql = "INSERT INTO anio_fiscal(fecha_inicio,fecha_cierre,estado,descripcion) VALUES (:fecha_inicio,:fecha_cierre,:estado,:descripcion)";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":fecha_inicio", $this->fecha_inicio);
        $conexion->bindParam(":fecha_cierre", $this->fecha_cierre);
        $conexion->bindParam(":estado", $this->estado);
        $conexion->bindParam(":descripcion", $this->descripcion);

        $result = $conexion->execute();

        if ($result) {
            $this->registrar_bitacora(REGISTRAR, GESTIONAR_ANIO_FISCAL, $this->fecha_inicio . " - " . $this->estado);//registramos en la bitacora

            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este Año Fiscal"];
        }
    }

    public function editar()
    {
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarDatos("editar");
        if(!($validaciones["estatus"])){return $validaciones;}                

        $sql = "UPDATE anio_fiscal SET fecha_inicio=:fecha_inicio, fecha_cierre=:fecha_cierre, estado=:estado, descripcion = :descripcion WHERE id_anio_fiscal=:id_anio_fiscal";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_anio_fiscal", $this->id_anio_fiscal);
        $conexion->bindParam(":fecha_inicio", $this->fecha_inicio);
        $conexion->bindParam(":fecha_cierre", $this->fecha_cierre);
        $conexion->bindParam(":estado", $this->estado);
        $conexion->bindParam(":descripcion", $this->descripcion);

        $result = $conexion->execute();
        
        if ($result) {
            $this->registrar_bitacora(MODIFICAR, GESTIONAR_ANIO_FISCAL, $this->fecha_inicio . " - " . $this->estado);//registramos en la bitacora

            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este Año Fiscal"];
        }
    }

    public function eliminar()
    {
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarDatos("eliminar");
        if(!($validaciones["estatus"])){return $validaciones;}

        $sql = "DELETE FROM anio_fiscal WHERE id_anio_fiscal = :id_anio_fiscal";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_anio_fiscal", $this->id_anio_fiscal);
        $result = $conexion->execute();
        
        if ($result) {
            $this->registrar_bitacora(ELIMINAR, GESTIONAR_ANIO_FISCAL, $this->fecha_inicio . " - " . $this->estado);//registramos en la bitacora            
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este Año Fiscal"];
        }
    }
    
    public function lastId()
    {
        $sql = "SELECT MAX(id_anio_fiscal) as last_id FROM anio_fiscal";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return ["estatus"=>true,"mensaje"=>$datos["last_id"]];
        } else {
            return ["estatus"=>false,"mensaje"=>"Error en la consulta"];
        } 
    }
    // validaciones back end (se llaman al registrar o modificar)
    private function validarDatos($consulta = "registrar")
    {   
        // Validamos el id_anio_fiscal en caso de editar o eliminar porque en registrar no existe todavia
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
        // Validamos que los campos enviados si existan

        if (!(isset($this->fecha_inicio) && isset($this->estado) && isset($this->descripcion))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

        // Validamos que los campos enviados no esten vacios

        if (empty($this->fecha_inicio) ||empty($this->estado) || empty($this->descripcion)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

        // Verificamos si los valores tienen los datos que deberian
        
        if(!(is_string($this->fecha_inicio)) || !($this->validarFecha($this->fecha_inicio))){
            return ["estatus"=>false,"mensaje"=>"El campo 'fecha de inicio' no posee un valor valido"];
        }
        
        if(!(is_string($this->estado)) || !(preg_match("/^[A-Za-z]*$/",$this->estado))){
            return ["estatus"=>false,"mensaje"=>"El campo 'estado' no posee un valor valido"];
        }
        if(!(is_string($this->descripcion)) || !(preg_match("/^[A-Za-z0-9,. ñÑ\b]{0,50}$/",$this->descripcion))){
            return ["estatus"=>false,"mensaje"=>"El campo 'descripcion' no posee un valor valido"];
        }

        if ($this->fecha_cierre != null) {
            if(!(is_string($this->fecha_cierre)) || !($this->validarFecha($this->fecha_cierre))){
                return ["estatus"=>false,"mensaje"=>"El campo 'fecha de cierre' no posee un valor valido"];
            }
        }

        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    //esta funcion es para revisar si una clave foranea existe, porque sino dara error la consulta
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