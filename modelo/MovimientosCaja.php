<?php
namespace haydee\modelo;
use haydee\modelo\Conexion;
use PDO;

class MovimientosCaja extends Conexion
{
    private $id_movimiento_caja;
    private $concepto;
    private $monto;
    private $fecha;
    private $estado;
    private $caja_chica_id;
    private $gasto_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_movimiento_caja($id_movimiento_caja){$this->id_movimiento_caja = $id_movimiento_caja;}

    public function get_id_movimiento_caja(){return $this->id_movimiento_caja;}

    public function set_concepto($concepto){$this->concepto = $concepto;}

    public function get_concepto(){return $this->concepto;}

    public function set_monto($monto){$this->monto = $monto;}

    public function get_monto(){return $this->monto;}

    public function set_fecha($fecha){$this->fecha = $fecha;}

    public function get_fecha(){return $this->fecha;}

    public function set_estado($estado){$this->estado = $estado;}

    public function get_estado(){return $this->estado;}

    public function set_caja_chica_id($caja_chica_id){$this->caja_chica_id = $caja_chica_id;}

    public function get_caja_chica_id(){return $this->caja_chica_id;}

    public function set_gasto_id($gasto_id){$this->gasto_id = $gasto_id;}

    public function get_gasto_id(){return $this->gasto_id;}

    public function realizar_consulta($accion,$prueba = false){
        switch ($accion) {
            case 'consultar_movimientos_caja':
                $respuesta = $this->consultar_movimientos_caja();

                if ($respuesta["resultado"] == true) {
                    if (!$prueba) {
                        // $this->registrar_bitacora(CONSULTAR, GESTIONAR_CAJA_CHICA, "TODOS LAS CAJAS");
                    }
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar_movimiento':
                $respuesta = $this->consultar_movimiento();

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

                if ($respuesta["resultado"]) {
                    return ["estatus"=>true,"mensaje"=>"OK","lastId"=>$respuesta["lastId"]];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este movimiento"];
                }

            case 'editar':
                $validaciones = $this->validarDatos("editar");
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->editar();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este movimiento"];
                }

            case 'eliminar':
                $validaciones = $this->validarDatos("eliminar");
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->eliminar();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este movimiento"];
                }

            case 'reponer_caja':
                
                if(!(is_string($this->monto)) || !(preg_match("/^[0-9]{0,12}[,.]{0,1}[0-9]{0,2}$/",$this->monto))){
                    return ["estatus"=>false,"mensaje"=>"El campo 'monto' no posee un valor valido"];
                }

                $respuesta = $this->reponer_caja();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar reponer caja"];
                }

            default:
                return ["estatus"=>false,"mensaje"=>"La operacion fue infructuosa"];
                break;
        }
    }

    private function consultar_movimientos_caja()
    {
        $sql = "SELECT * FROM movimientos_caja WHERE movimientos_caja.caja_chica_id = :caja_chica_id ORDER BY CASE estado WHEN 'Pendiente por reposicion' THEN 1 WHEN 'Reposado' THEN 2 ELSE 3 END, estado ASC;";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":caja_chica_id", $this->caja_chica_id);

        $result = $conexion->execute();

        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultar_movimiento()
    {
        $sql = "SELECT * FROM movimientos_caja WHERE movimientos_caja.id_movimiento_caja = :id_movimiento_caja";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":id_movimiento_caja", $this->id_movimiento_caja);

        $result = $conexion->execute();

        $datos = $conexion->fetch(PDO::FETCH_ASSOC);
        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function registrar()
    {
        $sql = "INSERT INTO movimientos_caja(concepto,monto,fecha,estado,caja_chica_id,gasto_id) VALUES (:concepto,:monto,:fecha,:estado,:caja_chica_id,:gasto_id)";
        
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":concepto", $this->concepto);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":estado", $this->estado);
        $conexion->bindParam(":caja_chica_id", $this->caja_chica_id);
        $conexion->bindParam(":gasto_id", $this->gasto_id);
        $result = $conexion->execute();

        $conexion = $this->get_conex();//otra conexion para buscar el ultimo id
        $res = $conexion->lastInsertId();

        return ["resultado"=>$result,"lastId"=>$res];
    }

    private function editar()
    {
        $sql = "UPDATE movimientos_caja SET concepto=:concepto, monto=:monto, fecha=:fecha WHERE id_movimiento_caja=:id_movimiento_caja";

        $conexion = $this->get_conex()->prepare($sql);        
        $conexion->bindParam(":concepto", $this->concepto);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":fecha", $this->fecha);

        $conexion->bindParam(":id_movimiento_caja", $this->id_movimiento_caja);

        $result = $conexion->execute();
        
        return $result;
    }

    private function eliminar()
    {
        $sql = "DELETE FROM movimientos_caja WHERE id_movimiento_caja = :id_movimiento_caja";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_movimiento_caja", $this->id_movimiento_caja);
        $result = $conexion->execute();
 
        return $result;
    }

    private function reponer_caja()
    {
        $sql = "START TRANSACTION;
            SET @total_pendiente = (SELECT SUM(monto) FROM movimientos_caja WHERE movimientos_caja.caja_chica_id = :id_caja_chica AND estado = 'Pendiente por reposicion');

            UPDATE caja_chica SET caja_chica.fondo_fijo = caja_chica.fondo_fijo + GREATEST(0, :monto - @total_pendiente), caja_chica.saldo_actual = caja_chica.saldo_actual + :monto
            WHERE caja_chica.id_caja_chica = :id_caja_chica;

            UPDATE movimientos_caja SET movimientos_caja.gasto_id = :id_gasto, movimientos_caja.estado = 'Reposado'
            WHERE movimientos_caja.caja_chica_id = :id_caja_chica AND movimientos_caja.estado = 'Pendiente por reposicion' AND movimientos_caja.gasto_id IS NULL;
            COMMIT;";

        $conexion = $this->get_conex()->prepare($sql);        
        $conexion->bindParam(":id_caja_chica", $this->caja_chica_id);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":id_gasto", $this->gasto_id);

        $result = $conexion->execute();

        return $result;
    }

    private function validarDatos($consulta = "registrar")
    {
        if ($consulta == "editar" || $consulta == "eliminar") {
            if (!(isset($this->id_movimiento_caja))) {return ["estatus"=>false,"mensaje"=>"El ID requerido no se recibio correctamente"];}

            if (empty($this->id_movimiento_caja)) {return ["estatus"=>false,"mensaje"=>"El ID requerido esta vacío"];}

            if(is_numeric($this->id_movimiento_caja)){
                if (!($this->validarClaveForanea("movimientos_caja","id_movimiento_caja",$this->id_movimiento_caja))) {
                    return ["estatus"=>false,"mensaje"=>"EL movimiento de la caja seleccionada no existe"];
                }
                if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
            }
            else{return ["estatus"=>false,"mensaje"=>"EL movimiento de la caja seleccionada tiene debe ser un valor numerico entero"];}            
        }

        if (!(isset($this->concepto) && isset($this->monto) && isset($this->fecha))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

        if ($consulta == "registrar") {
            if (!(isset($this->estado) && isset($this->caja_chica_id))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente para el registro"];}
        }
        // Validamos que los campos enviados no esten vacios

        if (empty($this->concepto) || empty($this->monto) || empty($this->fecha)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

        if ($consulta == "registrar") {
            if (empty($this->estado) || empty($this->caja_chica_id)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios para el registro"];}
        }
        
        if(!(is_string($this->concepto)) || !(preg_match("/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]{1,100}$/",$this->concepto))){
            return ["estatus"=>false,"mensaje"=>"El campo 'concepto' no posee un valor valido"];
        }
        
        if(!(is_string($this->monto)) || !(preg_match("/^[0-9]{0,12}[,.]{0,1}[0-9]{0,2}$/",$this->monto))){
            return ["estatus"=>false,"mensaje"=>"El campo 'monto' no posee un valor valido"];
        }

        if(!(is_string($this->fecha)) || !($this->validarFecha($this->fecha))){
            return ["estatus"=>false,"mensaje"=>"El campo 'fecha' no posee un valor valido"];
        }

        if ($consulta == "registrar") {
            if(!(is_string($this->estado)) || !(preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{1,100}$/",$this->estado))){
                return ["estatus"=>false,"mensaje"=>"El campo 'estado' no posee un valor valido"];
            }

            if(is_numeric($this->caja_chica_id)){
                if (!($this->validarClaveForanea("caja_chica","id_caja_chica",$this->caja_chica_id))) {
                    return ["estatus"=>false,"mensaje"=>"El id de la caja chica seleccionada no existe"];
                }            
            }
            else{
                return ["estatus"=>false,"mensaje"=>"El id de la caja chica seleccionada no posee un valor valido"];
            } 
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