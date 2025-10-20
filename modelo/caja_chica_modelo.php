<?php

require_once "modelo/conexion.php";

class Caja_chica extends Conexion
{
    private $id_caja_chica;
    private $fondo_fijo;
    private $saldo_actual;
    private $estado;
    private $descripcion;
    private $fecha_creacion;
    private $anio_fiscal_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_caja_chica($id_caja_chica){$this->id_caja_chica = $id_caja_chica;}

    public function get_id_caja_chica(){return $this->id_caja_chica;}

    public function set_fondo_fijo($fondo_fijo){$this->fondo_fijo = $fondo_fijo;}

    public function get_fondo_fijo(){return $this->fondo_fijo;}

    public function set_saldo_actual($saldo_actual){$this->saldo_actual = $saldo_actual;}

    public function get_saldo_actual(){return $this->saldo_actual;}

    public function set_estado($estado){$this->estado = $estado;}

    public function get_estado(){return $this->estado;}

    public function set_descripcion($descripcion){$this->descripcion = $descripcion;}

    public function get_descripcion(){return $this->descripcion;}

    public function set_fecha_creacion($fecha_creacion){$this->fecha_creacion = $fecha_creacion;}

    public function get_fecha_creacion(){return $this->fecha_creacion;}

    public function set_anio_fiscal_id($anio_fiscal_id){$this->anio_fiscal_id = $anio_fiscal_id;}

    public function get_anio_fiscal_id(){return $this->anio_fiscal_id;}

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

            case 'editar_descripcion':
                $validaciones = $this->validarDatos();
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->editar_descripcion();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar esta descripcion"];
                }

            default:
                return ["estatus"=>false,"mensaje"=>"La operacion fue infructuosa"];
                break;
        }
    }

    private function consultar()
    {
        $sql = "SELECT * FROM caja_chica ORDER BY caja_chica.id_caja_chica";

        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return ["resultado"=>$result,"datos"=>$datos];
    }    

    private function editar_descripcion()
    {
        $sql = "UPDATE caja_chica SET descripcion=:descripcion WHERE id_caja_chica=:id_caja_chica";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":descripcion", $this->descripcion);
        $conexion->bindParam(":id_caja_chica", $this->id_caja_chica);

        $result = $conexion->execute();

        return $result;
    }

    private function validarDatos()
    {   
        if (!(isset($this->id_caja_chica))) {return ["estatus"=>false,"mensaje"=>"El ID requerido no se recibio correctamente"];}

        if (empty($this->id_caja_chica)) {return ["estatus"=>false,"mensaje"=>"El ID requerido esta vacío"];}

        if(is_numeric($this->id_caja_chica)){
            if (!($this->validarClaveForanea("caja_chica","id_caja_chica",$this->id_caja_chica))) {
                return ["estatus"=>false,"mensaje"=>"La Caja seleccionada no existe"];
            }
        }
        else{return ["estatus"=>false,"mensaje"=>"La Caja seleccionada tiene debe ser un valor numerico entero"];}

        if (!(isset($this->descripcion))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}


        if (empty($this->descripcion)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

        
        if(!(is_string($this->descripcion)) || !(preg_match("/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]{0,100}$/",$this->descripcion))){
            return ["estatus"=>false,"mensaje"=>"El campo 'descripcion' no posee un valor valido"];
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