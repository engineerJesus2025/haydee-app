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
        $sql = "SELECT id_proveedor,nombre_proveedor,servicio,rif FROM proveedores WHERE id_proveedor = :id_proveedor";
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
    }
}