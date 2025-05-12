<?php 
require_once "modelo/conexion.php";

class Propietario extends Conexion{
    private $id_propietario;
    private $nombre;
    private $apellido;
    private $cedula;
    private $telefono;
    private $correo;

    public function __construct(){
        parent::__construct();
    }

    public function set_id_propietario($id_propietario){
        $this->id_propietario = $id_propietario;
    }
    public function get_id_usuario(){
        return $this->id_propietario;
    }

    public function set_nombre($nombre){
        $this->nombre = $nombre;
    }
    public function get_nombre(){
        return $this->nombre;
    }

    public function set_apellido($apellido){
        $this->apellido = $apellido;
    }   

    public function get_apellido(){
        return $this->apellido;
    }

    public function set_cedula($cedula){
        $this->cedula = $cedula;
    }
    public function get_cedula(){
        return $this->cedula;
    }

    public function set_telefono($telefono){
        $this->telefono = $telefono;
    }
    public function get_telefono(){
        return $this->telefono;
    }
    public function set_correo($correo){
        $this->correo = $correo;
    }
    public function get_correo(){
        return $this->correo;
    }

    public function consultar(){
        
    }
}
?>