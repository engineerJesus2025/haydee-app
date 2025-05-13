<?php
require_once "modelo/conexion.php";

class Modulos extends Conexion
{

    private $id_modulo;
    private $nombre;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_modulo($id_modulo)
    {
        $this->id_modulo = $id_modulo;
    }

    public function get_id_modulo()
    {
        return $this->id_modulo;
    }

    public function set_nombre($nombre)
    {
        $this->nombre = $nombre;
    }

    public function get_nombre()
    {
        return $this->nombre;
    }

    public function consultar()
    {
        $this->cambiar_db_seguridad();
        
        $sql = "SELECT * FROM modulos";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();
        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }
}
