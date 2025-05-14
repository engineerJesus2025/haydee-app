<?php
require_once "modelo/conexion.php";

class Roles_permisos extends Conexion
{
    private $id_rol_permiso;
    private $rol_id;
    private $permiso_usuario_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_rol_permiso($id_rol_permiso)
    {
        $this->id_rol_permiso = $id_rol_permiso;
    }

    public function get_id_rol_permiso()
    {
        return $this->id_rol_permiso;
    }

    public function set_rol_id($rol_id)
    {
        $this->rol_id = $rol_id;
    }

    public function get_rol_id()
    {
        return $this->rol_id;
    }

    public function set_permiso_usuario_id($permiso_usuario_id)
    {
        $this->permiso_usuario_id = $permiso_usuario_id;
    }

    public function get_permiso_usuario_id()
    {
        return $this->permiso_usuario_id;
    }

    public function consultar_roles_permisos(){
        $this->cambiar_db_seguridad();
        
        $sql = "SELECT * FROM roles_permisos WHERE rol_id = :rol";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":rol", $this->rol_id);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function registrar_permisos_roles()
    {
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarRolesPermisos(false);
        if(!($validaciones["estatus"])){return $validaciones;}

        $this->cambiar_db_seguridad();

        $sql = "INSERT INTO roles_permisos (rol_id, permiso_usuario_id) VALUES
        (:rol_id, :permiso_usuario_id)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":rol_id", $this->rol_id);
        $conexion->bindParam(":permiso_usuario_id", $this->permiso_usuario_id);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();

        if ($result) {
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este permiso de Rol"];
        }
    }

    public function eliminar_roles_permisos(){
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarRolesPermisos(true);
        if(!($validaciones["estatus"])){return $validaciones;}

        $this->cambiar_db_seguridad();

        $sql = "DELETE FROM roles_permisos WHERE rol_id = :rol_id";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":rol_id", $this->rol_id);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();

        if ($result) {
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este permiso de Rol"];
        }
    }

    private function validarRolesPermisos($eliminar = false)
    {   
        // Validamos el id rol/permiso en caso de eliminar porque en registrar no existe todavia

            if (!(isset($this->rol_id))) {return ["estatus"=>false,"mensaje"=>"El ID del rol no se recibio correctamente para modificar los permisos"];}

            if (empty($this->rol_id)) {return ["estatus"=>false,"mensaje"=>"El ID del Rol para modificar los permisos se envio vacio"];}

            if(is_numeric($this->rol_id)){
                if (!($this->validarClaveForanea("roles","id_rol",$this->rol_id,true))) {
                    return ["estatus"=>false,"mensaje"=>"El Rol seleccionado para modificar permisos no existe"];
                }
                if ($eliminar) {return ["estatus"=>true,"mensaje"=>"OK"];}
            }
            else{return ["estatus"=>false,"mensaje"=>"El id del Rol para modificar permisos tiene debe ser un valor numerico entero"];}

            if ($eliminar) {return ["estatus"=>true,"mensaje"=>"OK"];}
        // Validamos que los campos enviados si existan
        if (!(isset($this->permiso_usuario_id))) {return ["estatus"=>false,"mensaje"=>"El ID del usuario no se recibio correctamente"];}

        // Validamos que los campos enviados no esten vacios        
        if (empty($this->permiso_usuario_id)) {return ["estatus"=>false,"mensaje"=>"El ID del usuario esta vacio"];}

        // Verificamos si los valores tienen los datos que deberian
        
        if(is_numeric($this->permiso_usuario_id)){
            if (!($this->validarClaveForanea("permisos_usuarios","id_permiso_usuario",$this->permiso_usuario_id,true))) {
                return ["estatus"=>false,"mensaje"=>"El ID de Usuario seleccionado para modificar permisos no existe"];
            }
        }
        else{
            return ["estatus"=>false,"mensaje"=>"El id del Usuario para modificar permisos tiene debe ser un valor numerico entero"];
        }
        
        return ["estatus"=>true,"mensaje"=>"OK"];
    }
    //esta funcion es para revisar si una clave foranea existe, porque sino dara error la consulta
    private function validarClaveForanea($tabla,$nombreClave,$valor,$seguridad = false)
    {
        if ($seguridad) {
            $this->cambiar_db_seguridad();
        }
        $sql="SELECT * FROM $tabla WHERE $nombreClave =:valor";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        $result = $conexion->fetch(PDO::FETCH_ASSOC);
        if ($seguridad) {
            $this->cambiar_db_negocio();
        }
        return ($result)?true:false;        
    }
}
