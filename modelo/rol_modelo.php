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

    public function consultar()
    {
        $this->cambiar_db_seguridad();
        
        $sql = "SELECT * FROM roles";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        
        // $this->registrar_bitacora(CONSULTAR, GESTIONAR_ROLES);
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function consultar_rol()
    {
        $this->cambiar_db_seguridad();

        $sql = "SELECT id_rol, nombre FROM roles WHERE id_rol = :rol";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":rol", $this->id_rol);

        $result = $conexion->execute();        
        
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function consultar_permisos_usuarios()
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

    public function consultar_modulos()
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

    public function consultar_roles_permisos(){
        $this->cambiar_db_seguridad();
        
        $sql = "SELECT * FROM roles_permisos WHERE rol_id = :rol";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":rol", $this->id_rol);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function registrar()
    {
        $this->cambiar_db_seguridad();
        
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarDatos();
        if(!($validaciones["estatus"])){return $validaciones;}

        $sql = "INSERT INTO roles(nombre) VALUES (:nombre)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":nombre", $this->nombre);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();
        
        // $this->registrar_bitacora(REGISTRAR, GESTIONAR_ROLES);
        
        if ($result) {
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este Rol"];
        }
    }

    public function registrar_permisos_roles($rol_id, $permiso_usuario_id)
    {
        $this->cambiar_db_seguridad();
        
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarRolesPermisos(false,$rol_id, $permiso_usuario_id);
        if(!($validaciones["estatus"])){return $validaciones;}

        $sql = "INSERT INTO roles_permisos (rol_id, permiso_usuario_id) VALUES
        (:rol_id, :permiso_usuario_id)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":rol_id", $rol_id);
        $conexion->bindParam(":permiso_usuario_id", $permiso_usuario_id);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();

        if ($result) {
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este permiso de Rol"];
        }
    }

    public function editar_rol()
    {
        $this->cambiar_db_seguridad();
        
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarDatos("editar");
        if(!($validaciones["estatus"])){return $validaciones;}

        $sql = "UPDATE roles SET nombre = :nombre WHERE id_rol = :id_rol";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_rol", $this->id_rol);
        $conexion->bindParam(":nombre", $this->nombre);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();

        
        // $this->registrar_bitacora(MODIFICAR, GESTIONAR_ROLES);
        
        if ($result) {
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este Rol"];
        }
    }
    public function eliminar_rol()
    {
        $this->cambiar_db_seguridad();
        
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarDatos("eliminar");
        if(!($validaciones["estatus"])){return $validaciones;}

        $sql = "DELETE FROM roles WHERE id_rol = :id_rol";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_rol", $this->id_rol);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();

        
        // $this->registrar_bitacora(ELIMINAR, GESTIONAR_ROLES);
        
        if ($result) {
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este Rol"];
        }
    }

    public function verificar_nombre()
    {
        $this->cambiar_db_seguridad();
        
        $sql = "SELECT * FROM roles WHERE nombre = :nombre";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":nombre", $this->nombre);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if (isset($datos["nombre"])) {
            $r["estatus"] = true;
            $r["busqueda"] = "nombre";
            return $r;
        } else {
            $r["estatus"] = false;
            $r["busqueda"] = "nombre";
            return $r;
        }
    }

    

    public function eliminar_roles_permisos(){
        $this->cambiar_db_seguridad();
        
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarRolesPermisos(true,$this->id_rol);
        if(!($validaciones["estatus"])){return $validaciones;}

        $sql = "DELETE FROM roles_permisos WHERE rol_id = :rol_id";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":rol_id", $this->id_rol);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();

        if ($result) {
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este permiso de Rol"];
        }
    }

    public function lastId()
    {
        $this->cambiar_db_seguridad();
        $sql = "SELECT MAX(id_rol) as last_id FROM roles";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);
        $this->cambiar_db_negocio();

        if ($result) {
            return ["estatus"=>true,"mensaje"=>$datos["last_id"]];
        } else {
            return ["estatus"=>false,"mensaje"=>"Error en la consulta"];
        } 
    }

    private function validarDatos($consulta = "registrar")
    {   
        // Validamos el id rol en caso de editar o eliminar porque en registrar no existe todavia
        if ($consulta == "editar" || $consulta == "eliminar") {
            if (!(isset($this->id_rol))) {return ["estatus"=>false,"mensaje"=>"El ID del rol no se recibio correctamente"];}

            if (empty($this->id_rol)) {return ["estatus"=>false,"mensaje"=>"El ID del Rol se envio vacio"];}

            if(is_numeric($this->id_rol)){
                if (!($this->validarClaveForanea("roles","id_rol",$this->id_rol))) {
                    return ["estatus"=>false,"mensaje"=>"El Rol seleccionado no existe"];
                }
                if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
            }
            else{return ["estatus"=>false,"mensaje"=>"El id del Rol tiene debe ser un valor numerico entero"];}
        }
        // Validamos que los campos enviados si existan

        if (!(isset($this->nombre))) {return ["estatus"=>false,"mensaje"=>"El campos 'nombre' no se recibio correctamente"];}

        // Validamos que los campos enviados no esten vacios        
        if (empty($this->nombre)) {return ["estatus"=>false,"mensaje"=>"El campos 'nombre' esta vacio"];}

        // Verificamos si los valores tienen los datos que deberian
        
        if(!(is_string($this->nombre)) || !(preg_match("/^[A-Za-z \b]*$/",$this->nombre))){
            return ["estatus"=>false,"mensaje"=>"El campo 'nombre' no posee un valor valido"];
        }
        
        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    private function validarRolesPermisos($eliminar = false,$rol_id = '', $permiso_usuario_id='')
    {   
        // Validamos el id rol/permiso en caso de eliminar porque en registrar no existe todavia

            if (!(isset($rol_id))) {return ["estatus"=>false,"mensaje"=>"El ID del rol no se recibio correctamente para modificar los permisos"];}

            if (empty($rol_id)) {return ["estatus"=>false,"mensaje"=>"El ID del Rol para modificar los permisos se envio vacio"];}

            if(is_numeric($rol_id)){
                if (!($this->validarClaveForanea("roles","id_rol",$rol_id))) {
                    return ["estatus"=>false,"mensaje"=>"El Rol seleccionado para modificar permisos no existe"];
                }
                if ($eliminar) {return ["estatus"=>true,"mensaje"=>"OK"];}
            }
            else{return ["estatus"=>false,"mensaje"=>"El id del Rol para modificar permisos tiene debe ser un valor numerico entero"];}

            if ($eliminar) {return ["estatus"=>true,"mensaje"=>"OK"];}
        // Validamos que los campos enviados si existan
        if (!(isset($permiso_usuario_id))) {return ["estatus"=>false,"mensaje"=>"El ID del usuario no se recibio correctamente"];}

        // Validamos que los campos enviados no esten vacios        
        if (empty($permiso_usuario_id)) {return ["estatus"=>false,"mensaje"=>"El ID del usuario esta vacio"];}

        // Verificamos si los valores tienen los datos que deberian
        
        if(is_numeric($permiso_usuario_id)){
            if (!($this->validarClaveForanea("permisos_usuarios","id_permiso_usuario",$permiso_usuario_id))) {
                return ["estatus"=>false,"mensaje"=>"El ID de Usuario seleccionado para modificar permisos no existe"];
            }
        }
        else{
            return ["estatus"=>false,"mensaje"=>"El id del Usuario para modificar permisos tiene debe ser un valor numerico entero"];
        }
        
        return ["estatus"=>true,"mensaje"=>"OK"];
    }
    //esta funcion es para revisar si una clave foranea existe, porque sino dara error la consulta
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
