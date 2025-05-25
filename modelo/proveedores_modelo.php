<?php
require_once ("modelo/conexion.php");

class Proveedores extends Conexion{
    private $id_proveedor;
    private $nombre_proveedor;
    private $servicio;
    private $rif;
    private $direccion;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_proveedor($id_proveedor){
        $this->id_proveedor = $id_proveedor;
    }
    public function get_id_proveedor(){
        return $this->id_proveedor;
    }
    public function set_nombre_proveedor($nombre_proveedor){
        $this->nombre_proveedor = $nombre_proveedor;
    }
    public function get_nombre_proveedor(){
        return $this->nombre_proveedor;
    }
    public function set_servicio($servicio){
        $this->servicio = $servicio;
    }
    public function get_servicio(){
        return $this->servicio;
    }
    public function set_rif($rif){
        $this->rif = $rif;
    }
    public function get_rif(){
        return $this->rif;
    }
    public function set_direccion($direccion){
        $this->direccion = $direccion;
    }
    public function get_direccion(){
        return $this->direccion;
    }

    public function consultar(){
        $sql = "SELECT id_proveedor,nombre_proveedor,servicio,rif,direccion FROM proveedores";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        //$this->registrar_bitacora(CONSULTAR, GESTIONAR_PROPIETARIOS, "TODOS LOS USUARIOS");//registra cuando se entra al modulo de propietarios
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            return $datos;
        } else {
            return ["estatus"=>false, "mensaje"=>"Error al consultar los proveedores"];
        }
    }

    public function consultar_proveedor(){
        $sql = "SELECT id_proveedor,nombre_proveedor,servicio,rif, direccion FROM proveedores WHERE id_proveedor = :id_proveedor";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_proveedor", $this->id_proveedor);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false, "mensaje"=>"Error al consultar el proveedor"];
        }
        }

    public function registrar(){
        //
        $sql = "INSERT INTO proveedores (nombre_proveedor, servicio, rif, direccion) VALUES (:nombre_proveedor, :servicio, :rif, :direccion)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":nombre_proveedor", $this->nombre_proveedor);
        $conexion->bindParam(":servicio", $this->servicio);
        $conexion->bindParam(":rif", $this->rif);
        $conexion->bindParam(":direccion", $this->direccion);
        $result = $conexion->execute();
        //$this->registrar_bitacora(REGISTRAR, GESTIONAR_PROPIETARIOS, "Propietario: ".$this->nombre." ".$this->apellido);

        if($result == true){
            return ["estatus"=>true, "mensaje"=>"Proveedor registrado correctamente"];
        } else{
            return ["estatus"=>false, "mensaje"=>"Error al registrar el proveedor"];
        }

    }

    public function editar_proveedor(){
        $sql = "UPDATE proveedores SET nombre_proveedor = :nombre_proveedor, servicio = :servicio, rif = :rif, direccion = :direccion WHERE id_proveedor = :id_proveedor";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_proveedor", $this->id_proveedor);
        $conexion->bindParam(":nombre_proveedor", $this->nombre_proveedor);
        $conexion->bindParam(":servicio", $this->servicio);
        $conexion->bindParam(":rif", $this->rif);
        $conexion->bindParam(":direccion", $this->direccion);
        $result = $conexion->execute();

        if($result == true){
            return ["estatus"=>true, "mensaje"=>"Proveedor actualizado correctamente"];
        } else{
            return ["estatus"=>false, "mensaje"=>"Error al actualizar el proveedor"];
        }
    }
    public function eliminar_proveedor(){
        $sql = "DELETE FROM proveedores WHERE id_proveedor = :id_proveedor";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_proveedor", $this->id_proveedor);
        $result = $conexion->execute();
        if($result == true){
            return ["estatus"=>true, "mensaje"=>"Proveedor eliminado correctamente"];
        } else{
            return ["estatus"=>false, "mensaje"=>"Error al eliminar el proveedor"];
        }
    }

    public function lastId(){
        $sql = "SELECT MAX(id_proveedor) as last_id FROM proveedores";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if($result == true){
            return ["estatus"=>true, "mensaje"=>$datos[0]["last_id"]];
        } else {
            return ["estatus"=>false, "mensaje"=>"Error al consultar el último id"];
        }
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