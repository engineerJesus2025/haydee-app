<?php
namespace haydee\modelo;
use haydee\modelo\Conexion;
use PDO;

class PermisosUsuarios extends Conexion
{
    private $id_permiso_usuario;
    private $nombre_accion;
    private $modulo_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_permiso_usuario($id_permiso_usuario)
    {
        $this->id_permiso_usuario = $id_permiso_usuario;
    }

    public function get_id_permiso_usuario()
    {
        return $this->id_permiso_usuario;
    }

    public function set_nombre_accion($nombre_accion)
    {
        $this->nombre_accion = $nombre_accion;
    }

    public function get_nombre_accion()
    {
        return $this->nombre_accion;
    }

    public function set_modulo_id($modulo_id)
    {
        $this->modulo_id = $modulo_id;
    }

    public function get_modulo_id()
    {
        return $this->modulo_id;
    }

    public function realizar_consulta($accion){
        $this->cambiar_db_seguridad();
        switch ($accion) {
            case 'consultar':
                $respuesta = $this->consultar();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"] == true) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }
            default:
                return ["estatus"=>false,"mensaje"=>"A ocurrido un error en la consulta"];
                break;
        }
    }

    public function consultar()
    {        
        $sql = "SELECT * FROM permisos_usuarios";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];        
    }

}
?>