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
    }
}