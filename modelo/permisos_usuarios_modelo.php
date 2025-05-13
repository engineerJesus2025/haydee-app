<?php

require_once "modelo/conexion.php";

class Permisos_usuarios extends Conexion
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

    //HAsta aqui todo normal

    public function consultar()
    {
        $this->cambiar_db_seguridad();
        
        $sql = "SELECT * FROM permisos_usuarios";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();
        if ($result) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    //esta funcion se ejecuta en el login para ver los permisos
    public function consultar_permisos_por_usuario($rol_permiso)
    {
        $this->cambiar_db_seguridad();

        $sql = "SELECT modulos.id_modulo, permisos_usuarios.nombre_accion AS nombre_permiso
        FROM roles_permisos INNER JOIN permisos_usuarios ON permisos_usuarios.id_permiso_usuario = 
        roles_permisos.permiso_usuario_id INNER JOIN  modulos ON modulos.id_modulo = permisos_usuarios.modulo_id
        WHERE roles_permisos.rol_id = :rol_permiso";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":rol_permiso", $rol_permiso);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if ($result == true) {
            return $datos;
        } else {
            return "";
        }
    }

}
?>