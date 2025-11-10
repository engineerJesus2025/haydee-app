<?php
namespace haydee\modelo;
use haydee\modelo\Conexion;
use PDO;

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

    public function realizar_consulta($accion){
        $this->cambiar_db_seguridad();
        switch ($accion) {
            case 'consultar':
                return $this->consultar();
            default:
                return ["estatus"=>false,"mensaje"=>"A ocurrido un error en la consulta"];
                break;
        }
    }

    public function consultar()
    {        
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
