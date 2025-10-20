<?php
require_once "modelo/conexion.php";

class Rol extends Conexion
{

    private $id_rol;
    private $nombre;

    public function __construct()
    {
        parent::__construct();        
    }

    public function set_id_rol($id_rol)
    {
        $this->id_rol = $id_rol;
    }

    public function get_id_rol()
    {
        return $this->id_rol;
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
            case 'verificar_nombre':
                $respuesta = $this->verificar_nombre();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {                    
                    if (isset($respuesta["datos"]["nombre"])) {                        
                        return ["estatus"=>true,"busqueda"=>"nombre"];
                    } else {                        
                        return ["estatus"=>false,"busqueda"=>"nombre"];
                    }
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar':
                $respuesta = $this->consultar();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {                        
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar_roles':
                $respuesta = $this->consultar();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar_rol':
                $respuesta = $this->consultar_rol();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'registrar':
                $validaciones = $this->validarDatos();
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->registrar();

                $this->cambiar_db_negocio();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este Rol"];
                }

            case 'editar_rol':
                $validaciones = $this->validarDatos("editar");
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->editar_rol();

                $this->cambiar_db_negocio();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este Rol"];
                }

            case 'eliminar_rol':
                $validaciones = $this->validarDatos("eliminar");
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->eliminar_rol();

                $this->cambiar_db_negocio();

                if ($respuesta) {                        
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este Rol"];
                }

            case 'lastId':
                $respuesta = $this->lastId();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {
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

    private function verificar_nombre()
    {
        $sql = "SELECT * FROM roles WHERE nombre = :nombre";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":nombre", $this->nombre);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];   
    }

    private function consultar()
    {        
        $sql = "SELECT * FROM roles";
        $conexion = $this->get_conex()->prepare($sql);

        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultar_rol()
    {
        $sql = "SELECT id_rol, nombre FROM roles WHERE id_rol = :rol";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":rol", $this->id_rol);

        $result = $conexion->execute();        
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function registrar()
    {
        $sql = "INSERT INTO roles(nombre) VALUES (:nombre)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":nombre", $this->nombre);
        $result = $conexion->execute();

        return $result;
    }

    private function editar_rol()
    {
        $sql = "UPDATE roles SET nombre = :nombre WHERE id_rol = :id_rol";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_rol", $this->id_rol);
        $conexion->bindParam(":nombre", $this->nombre);

        $result = $conexion->execute();
        return $result;
    }
    private function eliminar_rol()
    {        
        $sql = "DELETE FROM roles WHERE id_rol = :id_rol";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_rol", $this->id_rol);

        $result = $conexion->execute();        
        
        return $result;
    }

    private function lastId()
    {
        $this->cambiar_db_seguridad();
        $sql = "SELECT MAX(id_rol) as last_id FROM roles";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function validarDatos($consulta = "registrar")
    {
        if ($consulta == "editar" || $consulta == "eliminar") {
            if (!(isset($this->id_rol))) {return ["estatus"=>false,"mensaje"=>"El ID del rol no se recibio correctamente"];}

            if (empty($this->id_rol)) {return ["estatus"=>false,"mensaje"=>"El ID del Rol se envio vacio"];}

            if(is_numeric($this->id_rol)){
                if (!($this->validarClaveForanea("roles","id_rol",$this->id_rol))) {
                    return ["estatus"=>false,"mensaje"=>"El Rol seleccionado no existe"];
                }
                
                if($this->id_rol == 1){return ["estatus"=>false,"mensaje"=>"No se puede Alterar el Rol del Administrador Global"];}
                
                if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
            }
            else{return ["estatus"=>false,"mensaje"=>"El id del Rol tiene debe ser un valor numerico entero"];}

            if($this->id_rol == 1){return ["estatus"=>false,"mensaje"=>"No se puede Alterar el Rol del Administrador Global"];}
        }

        if (!(isset($this->nombre))) {return ["estatus"=>false,"mensaje"=>"El campos 'nombre' no se recibio correctamente"];}

        if (empty($this->nombre)) {return ["estatus"=>false,"mensaje"=>"El campos 'nombre' esta vacio"];}
        
        if(!(is_string($this->nombre)) || !(preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/",$this->nombre))){
            return ["estatus"=>false,"mensaje"=>"El campo 'nombre' no posee un valor valido"];
        }        
        
        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    private function validarClaveForanea($tabla,$nombreClave,$valor,$seguridad = false)
    {
        $sql="SELECT * FROM $tabla WHERE $nombreClave =:valor";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        $result = $conexion->fetch(PDO::FETCH_ASSOC);

        return ($result)?true:false;        
    }
}
