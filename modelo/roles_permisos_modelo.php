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

    public function realizar_consulta($accion){
        $this->cambiar_db_seguridad();

        switch ($accion) {
            case 'consultar_roles_permisos':
                $respuesta = $this->consultar_roles_permisos();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar_permisos_por_usuario':
                $respuesta = $this->consultar_permisos_por_usuario();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'registrar_permisos_roles':
                $validaciones = $this->validarRolesPermisos(false);
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->registrar_permisos_roles();

                $this->cambiar_db_negocio();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar asignar los permisos a los roles"];
                }

            case 'eliminar_roles_permisos':
                $validaciones = $this->validarRolesPermisos(true);
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->eliminar_roles_permisos();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {
                    if ($respuesta["fila_afectada"] < 1) {
                        return ["estatus"=>false,"mensaje"=>"No se modificó ningún registro"];
                    }

                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar los permisos de este rol"];
                }

            default:
                return ["estatus"=>false,"mensaje"=>"A ocurrido un error en la consulta"];
                break;
        }
    }

    private function consultar_roles_permisos(){
        $sql = "SELECT * FROM roles_permisos WHERE rol_id = :rol";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":rol", $this->rol_id);

        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultar_permisos_por_usuario()
    {
        $sql = "SELECT modulos.id_modulo, permisos_usuarios.nombre_accion AS nombre_permiso
        FROM roles_permisos INNER JOIN permisos_usuarios ON permisos_usuarios.id_permiso_usuario = 
        roles_permisos.permiso_usuario_id INNER JOIN  modulos ON modulos.id_modulo = permisos_usuarios.modulo_id
        WHERE roles_permisos.rol_id = :rol_permiso";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":rol_permiso", $this->rol_id);

        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function registrar_permisos_roles()
    {
        $sql = "INSERT INTO roles_permisos (rol_id, permiso_usuario_id) VALUES
        (:rol_id, :permiso_usuario_id)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":rol_id", $this->rol_id);
        $conexion->bindParam(":permiso_usuario_id", $this->permiso_usuario_id);
        $result = $conexion->execute();

        return $result;
    }

    private function eliminar_roles_permisos(){
        $sql = "DELETE FROM roles_permisos WHERE rol_id = :rol_id";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":rol_id", $this->rol_id);

        $result = $conexion->execute();
        $filas_afectadas = $conexion->rowCount();
        
        return ["resultado"=>$result,"fila_afectada"=>$filas_afectadas];
    }

    private function validarRolesPermisos($eliminar = false)
    {   
        if (!(isset($this->rol_id))) {return ["estatus"=>false,"mensaje"=>"El ID del rol no se recibio correctamente para modificar los permisos"];}

        if (empty($this->rol_id)) {return ["estatus"=>false,"mensaje"=>"El ID del Rol para modificar los permisos se envio vacio"];}

        if(is_numeric($this->rol_id)){
           if (!($this->validarClaveForanea("roles","id_rol",$this->rol_id))) {
               return ["estatus"=>false,"mensaje"=>"El Rol seleccionado para modificar permisos no existe"];
           }
           if ($eliminar) {return ["estatus"=>true,"mensaje"=>"OK"];}
        }
        else{return ["estatus"=>false,"mensaje"=>"El id del Rol para modificar permisos tiene debe ser un valor numerico entero"];}
        
        if (!(isset($this->permiso_usuario_id))) {return ["estatus"=>false,"mensaje"=>"El ID del usuario no se recibio correctamente"];}

        if (empty($this->permiso_usuario_id)) {return ["estatus"=>false,"mensaje"=>"El ID del usuario esta vacio"];}
        
        if(is_numeric($this->permiso_usuario_id)){
            if (!($this->validarClaveForanea("permisos_usuarios","id_permiso_usuario",$this->permiso_usuario_id))) {
                return ["estatus"=>false,"mensaje"=>"El ID de Usuario seleccionado para modificar permisos no existe"];
            }
        }
        else{
            return ["estatus"=>false,"mensaje"=>"El id del Usuario para modificar permisos tiene debe ser un valor numerico entero"];
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
}
