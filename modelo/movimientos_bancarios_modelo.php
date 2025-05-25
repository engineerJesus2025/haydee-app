<?php

require_once "modelo/conexion.php";

class Movimientos_bancarios extends Conexion
{
    private $id_movimiento;
    private $fecha;
    private $monto;
    private $referencia;    
    private $tipo_movimiento;
    private $banco_id;
    private $monto_diferencia;
    private $tipo_diferencia;
    private $conciliacion_id;
    private $pago_id;
    private $gasto_id;    

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_movimiento($id_movimiento){$this->id_movimiento = $id_movimiento;}

    public function get_id_movimiento(){return $this->id_movimiento;}

    public function set_fecha($fecha){$this->fecha = $fecha;}

    public function get_fecha(){return $this->fecha;}

    public function set_monto($monto){$this->monto = $monto;}

    public function get_monto(){return $this->monto;}

    public function set_referencia($referencia){$this->referencia = $referencia;}

    public function get_referencia(){return $this->referencia;}

    public function set_tipo_movimiento($tipo_movimiento){$this->tipo_movimiento = $tipo_movimiento;}

    public function get_tipo_movimiento(){return $this->tipo_movimiento;}

    public function set_banco_id($banco_id){$this->banco_id = $banco_id;}

    public function get_banco_id(){return $this->banco_id;}

    public function set_monto_diferencia($monto_diferencia){$this->monto_diferencia = $monto_diferencia;}

    public function get_monto_diferencia(){return $this->monto_diferencia;}

    public function set_tipo_diferencia($tipo_diferencia){$this->tipo_diferencia = $tipo_diferencia;}

    public function get_tipo_diferencia(){return $this->tipo_diferencia;}

    public function set_conciliacion_id($conciliacion_id){$this->conciliacion_id = $conciliacion_id;}

    public function get_conciliacion_id(){return $this->conciliacion_id;}

    public function set_pago_id($pago_id){$this->pago_id = $pago_id;}

    public function get_pago_id(){return $this->pago_id;}

    public function set_gasto_id($gasto_id){$this->gasto_id = $gasto_id;}

    public function get_gasto_id(){return $this->gasto_id;}

    //HAsta aqui todo normal

    public function verificar_referencia()
    {
        $sql = "SELECT * FROM movimientos_bancarios WHERE referencia = :referencia";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":referencia", $this->referencia);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        if (isset($datos["referencia"])) {
            $r["estatus"] = true;
            $r["busqueda"] = "referencia_movimiento";
            return $r;
        } else {
            $r["estatus"] = false;
            $r["busqueda"] = "referencia_movimiento";
            return $r;
        }
    }

    public function consultar_movimientos()
    {
        $mes_buscar = date("m",$this->fecha);
        $anio_buscar = date("Y",$this->fecha);
    
        $sql = "SELECT * FROM movimientos_bancarios WHERE MONTH(movimientos_bancarios.fecha) = :mes && YEAR(movimientos_bancarios.fecha) = :anio ORDER BY fecha";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":anio", $anio_buscar);
        $conexion->bindParam(":mes", $mes_buscar);

        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function buscar_movimiento()
    {
        $sql = "SELECT * FROM movimientos_bancarios WHERE id_movimiento = :id_movimiento";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":id_movimiento", $this->id_movimiento);

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

        $sql = "INSERT INTO movimientos_bancarios(fecha,monto,referencia,tipo_movimiento,banco_id,monto_diferencia,tipo_diferencia,conciliacion_id,pago_id,gasto_id) VALUES (:fecha,:monto,:referencia,:tipo_movimiento,:banco_id,:monto_diferencia,:tipo_diferencia,:conciliacion_id,:pago_id,:gasto_id)";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":referencia", $this->referencia);
        $conexion->bindParam(":tipo_movimiento", $this->tipo_movimiento);
        $conexion->bindParam(":banco_id", $this->banco_id);
        $conexion->bindParam(":monto_diferencia", $this->monto_diferencia);
        $conexion->bindParam(":tipo_diferencia", $this->tipo_diferencia);
        $conexion->bindParam(":conciliacion_id", $this->conciliacion_id);
        $conexion->bindParam(":pago_id", $this->pago_id);
        $conexion->bindParam(":gasto_id", $this->gasto_id);
        $result = $conexion->execute();

        if ($result) {
            $this->registrar_bitacora(REGISTRAR, GESTIONAR_CONCILIACION_BANCARIA, "Movimiento Registrado de: " . $this->monto . "Bs.");
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este Movimiento Bancario"];
        }
    }

    public function editar()
    {
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarDatos("editar");
        if(!($validaciones["estatus"])){return $validaciones;}                

        $sql = "UPDATE movimientos_bancarios SET fecha=:fecha,monto=:monto,referencia=:referencia,tipo_movimiento=:tipo_movimiento,banco_id=:banco_id,monto_diferencia=:monto_diferencia,tipo_diferencia=:tipo_diferencia WHERE id_movimiento=:id_movimiento";
        
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":fecha", $this->fecha);    
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":referencia", $this->referencia);
        $conexion->bindParam(":tipo_movimiento", $this->tipo_movimiento);
        $conexion->bindParam(":banco_id", $this->banco_id);
        $conexion->bindParam(":monto_diferencia", $this->monto_diferencia);
        $conexion->bindParam(":tipo_diferencia", $this->tipo_diferencia);
        $conexion->bindParam(":id_movimiento", $this->id_movimiento);

        $result = $conexion->execute();
        
        if ($result) {
            $this->registrar_bitacora(MODIFICAR, GESTIONAR_CONCILIACION_BANCARIA, "Movimiento Editado de: " . $this->monto . "Bs.");
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este Movimiento Bancario"];
        }
    }

    public function eliminar()
    {
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarDatos("eliminar");
        if(!($validaciones["estatus"])){return $validaciones;}

        $sql = "DELETE FROM movimientos_bancarios WHERE id_movimiento = :id_movimiento";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_movimiento", $this->id_movimiento);
        $result = $conexion->execute();

        if ($result) {
            $movimiento_alterado = $this->buscar_movimiento();
            $this->registrar_bitacora(ELIMINAR, GESTIONAR_CONCILIACION_BANCARIA, "Movimiento Eliminado de: " . $movimiento_alterado["monto"] . "Bs.");

            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este Movimiento Bancario"];
        }
    }

    // validaciones back end (se llaman al registrar o modificar)
    private function validarDatos($consulta = "registrar")
    {   
        // Validamos el id usuario en caso de editar o eliminar porque en registrar no existe todavia
        if ($consulta == "editar" || $consulta == "eliminar") {
            if (!(isset($this->id_movimiento))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

            if (empty($this->id_movimiento)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

            if(is_numeric($this->id_movimiento)){
                if (!($this->validarClaveForanea("movimientos_bancarios","id_movimiento",$this->id_movimiento))) {
                    return ["estatus"=>false,"mensaje"=>"El Movimento seleccionado no existe"];
                }
                if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
            }
            else{return ["estatus"=>false,"mensaje"=>"El id del Movimiento tiene debe ser un valor numerico entero"];}
        }
        // Validamos que los campos enviados si existan

        if (!(isset($this->fecha) && isset($this->monto) && isset($this->referencia) && isset($this->tipo_movimiento) && isset($this->banco_id) && isset($this->monto_diferencia) && isset($this->tipo_diferencia) && isset($this->conciliacion_id))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

        // Validamos que los campos enviados no esten vacios

        if (empty($this->fecha) || empty($this->monto) ||empty($this->referencia) || empty($this->tipo_movimiento) || empty($this->banco_id) || empty($this->tipo_diferencia) ||empty($this->conciliacion_id)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

        // Verificamos si los valores tienen los datos que deberian
        
        if(!(is_string($this->fecha)) || !($this->validarFecha($this->fecha))){
            return ["estatus"=>false,"mensaje"=>"El campo 'Fecha' no posee un valor valido"];
        }
        if(!(is_numeric($this->monto))){
            return ["estatus"=>false,"mensaje"=>"El campo 'Monto' no posee un valor valido"];
        }
        if(!(is_string($this->referencia)) || !(preg_match("/^[A-Za-z0-9\b]*$/",$this->referencia))){
            return ["estatus"=>false,"mensaje"=>"El campo 'Referencia' no posee un valor valido"];
        }

        if(!(is_string($this->tipo_movimiento)) || !(preg_match("/^[A-Za-z\b]*$/",$this->tipo_movimiento))){
            return ["estatus"=>false,"mensaje"=>"El campo 'Tipo de Movimiento' no posee un valor valido"];
        }   

        if(is_numeric($this->banco_id)){
            if (!($this->validarClaveForanea("bancos","id_banco",$this->banco_id))) {
                return ["estatus"=>false,"mensaje"=>"El campo 'Banco' seleccionado no existe"];
            }
        }
        else{
            return ["estatus"=>false,"mensaje"=>"El campo 'Banco' no posee un valor valido"];
        }

        if(!(is_numeric($this->monto_diferencia))){
            return ["estatus"=>false,"mensaje"=>"El Monto en 'Resumen' no posee un valor valido"];
        }

        if(!(is_string($this->tipo_diferencia)) || !(preg_match("/^[A-Za-z \b]*$/",$this->tipo_diferencia))){
            return ["estatus"=>false,"mensaje"=>"El Nombre del Tipo de Diferencia en 'Resumen' no posee un valor valido"];
        }

        if(is_numeric($this->conciliacion_id)){
            if (!($this->validarClaveForanea("conciliacion_bancaria","id_conciliacion",$this->conciliacion_id))) {
                return ["estatus"=>false,"mensaje"=>"La 'Conciliacion' seleccionada no existe"];
            }
        }
        else{
            return ["estatus"=>false,"mensaje"=>"La 'Conciliacion' seleccionada no posee un valor valido"];
        }

        if (isset($this->pago_id)) {
            if(is_numeric($this->pago_id)){
                if (!($this->validarClaveForanea("pagos","id_pago",$this->pago_id))) {
                    return ["estatus"=>false,"mensaje"=>"El 'Pago de Mensualidad' seleccionado no existe"];
                }
            }
            else{
                return ["estatus"=>false,"mensaje"=>"El 'Pago de Mensualidad' seleccionado no posee un valor valido"];
            }
        }
        if (isset($this->gasto_id)) {
            if(is_numeric($this->gasto_id)){
                if (!($this->validarClaveForanea("gastos","id_gasto",$this->gasto_id))) {
                    return ["estatus"=>false,"mensaje"=>"El 'Pago de Servicio' seleccionado no existe"];
                }
            }
            else{
                return ["estatus"=>false,"mensaje"=>"El 'Pago de Servicio' seleccionado no posee un valor valido"];
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
    //aqui revisamos so la fecha tiene el formato adecuado
    private function validarFecha($fecha){
        $valores = explode('-', $fecha);
        if(count($valores) == 3 && checkdate($valores[1], $valores[2], $valores[0])){
            return true;
        }
        return false;
    }
}
?>